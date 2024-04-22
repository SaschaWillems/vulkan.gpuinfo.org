<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2024 by Sascha Willems (www.saschawillems.de)
 *	
 * This code is free software, you can redistribute it and/or
 * modify it under the terms of the GNU Affero General Public
 * License version 3 as published by the Free Software Foundation.
 *	
 * Please review the following information to ensure the GNU Lesser
 * General Public License version 3 requirements will be met:
 * http://www.gnu.org/licenses/agpl-3.0.de.html
 *	
 * The code is distributed WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
 * PURPOSE.  See the GNU AGPL 3.0 for more details.		
 *
 */

include '../../database/database.class.php';
include '../../database/sqlrepository.php';
include '../../includes/functions.php';
include '../../includes/constants.php';

session_name(SESSION_NAME);
session_start();

// PHP doesn't require this, but it makes the script easier to follow
$data = [];
$params = [];
$whereClause = '';
$core = '1.0';

// Check if a value is present in the request filter and not empty
function getRequestFilterValue($name) {
    if (isset($_REQUEST['filter'][$name]) && ($_REQUEST['filter'][$name] != '')) {
        return $_REQUEST['filter'][$name];    
    }
    return null;
}

// Used to setup the where clause for filtering the report listing SQL statement
function appendWhereClause($term, $parameters) {
    global $whereClause;
    if ($whereClause == '') {
        $whereClause = "where $term"; 
    } else {
        $whereClause .= " and $term";
    }
    global $params;
    foreach ($parameters as $key => $value) {
        $params[$key] = $value;
    }
};

$start = microtime(true);

DB::connect();

if (isset($_REQUEST['filter']['core'])) {
    $core = $_REQUEST['filter']['core'];
    if (empty($core)) {
        $core = '1.0';
    }
}

// Ordering
$orderByColumn = '';
$orderByDir = '';
if (isset($_REQUEST['order'])) {
    $orderByColumn = $_REQUEST['order'][0]['column'];
    $orderByDir = $_REQUEST['order'][0]['dir'];
    if (strcasecmp($orderByColumn, 'driver') == 0) {
        $orderByColumn = 'driverversionraw';
    }
    if (strcasecmp($orderByColumn, 'device') == 0) {
        $orderByColumn = 'devicename';
    }
}

// Paging
$paging = '';
if (isset($_REQUEST['start']) && $_REQUEST['length'] != '-1') {
    $paging = "LIMIT " . $_REQUEST["length"] . " OFFSET " . $_REQUEST["start"];
}

// Filtering
$searchClause = null;
$searchColumns = array('id');

// Dynamic limit column
$limit = $_REQUEST['filter']['devicelimit'];
if ($limit != '') {
    array_push($searchColumns, 'devicelimit');
}

array_push($searchColumns, 'devicename', 'p.driverversion', 'p.apiversion', 'vendor', 'p.devicetype', 'r.osname', 'r.osversion', 'r.osarchitecture');

if (isset($_REQUEST['filter']['portability'])) {
    if ($_REQUEST['filter']['portability']) {
        $searchColumns = ['id', 'devicename', 'r.osname', 'r.osversion', 'p.driverversion', 'p.apiversion'];
    }
}
// Per-column, filtering
$filters = array();
for ($i = 0; $i < count($_REQUEST['columns']); $i++) {
    $column = $_REQUEST['columns'][$i];
    if (($column['searchable'] == 'true') && ($column['search']['value'] != '')) {
        $filters[] = $searchColumns[$i] . ' like :filter_' . $i;
        $params['filter_' . $i] = '%' . $column['search']['value'] . '%';
    }
}
if (sizeof($filters) > 0) {
    $searchClause = 'having ' . implode(' and ', $filters);
}

$selectAddColumns = '';
$negate = false;
if (isset($_REQUEST['filter']['option'])) {
    if ($_REQUEST['filter']['option'] == 'not') {
        $negate = true;
    }
}

// Filters
// Extension
$extension = getRequestFilterValue('extension');
if ($extension) {
    appendWhereClause("r.id " . ($negate ? "not" : "") . " in (select distinct(reportid) from deviceextensions de join extensions ext on de.extensionid = ext.id where ext.name = :filter_extension)", ['filter_extension' => $extension]);

}
// Submitter
$submitter = getRequestFilterValue('submitter');
if ($submitter) {
    appendWhereClause("r.submitter = :filter_submitter", ['filter_submitter' => $submitter]);
}
// Format feature support
$featureflagbit = getRequestFilterValue('featureflagbit');
if ($featureflagbit) {
    // For a specific feature flag
    $lineartilingformat = getRequestFilterValue('lineartilingformat');
    $optimaltilingformat = getRequestFilterValue('optimaltilingformat');
    $bufferformat = getRequestFilterValue('bufferformat');
    if ($lineartilingformat) {
        $featureflagbit_value = array_search($featureflagbit, FormatFeatureFlags::TilingFlags);
        assert($featureflagbit_value);
        appendWhereClause("r.id in (select reportid from deviceformats df join VkFormat vf on vf.value = df.formatid where vf.name = :filter_lineartilingformat and df.lineartilingfeatures & $featureflagbit_value = $featureflagbit_value)", ['filter_lineartilingformat' => $lineartilingformat]);
    }
    if ($optimaltilingformat) {
        $featureflagbit_value = array_search($featureflagbit, FormatFeatureFlags::TilingFlags);
        assert($featureflagbit_value);
        appendWhereClause("r.id in (select reportid from deviceformats df join VkFormat vf on vf.value = df.formatid where vf.name = :filter_optimaltilingformat and df.optimaltilingfeatures & $featureflagbit_value = $featureflagbit_value)", ['filter_optimaltilingformat' => $optimaltilingformat]);
    }    
    if ($bufferformat) {
        $featureflagbit_value = array_search($featureflagbit, FormatFeatureFlags::BufferFlags);
        assert($featureflagbit_value);
        appendWhereClause("r.id in (select reportid from deviceformats df join VkFormat vf on vf.value = df.formatid where vf.name = :filter_bufferformat and df.bufferfeatures & $featureflagbit_value = $featureflagbit_value)", ['filter_bufferformat' => $bufferformat]);
    }
} else {
    // Unspecific (just supported)
    $linearformatfeature = getRequestFilterValue('linearformat');
    $optimalformatfeature = getRequestFilterValue('optimalformat');
    $bufferformatfeature = getRequestFilterValue('bufferformat');
    if ($linearformatfeature) {
        $whereClause = "where id " . ($negate ? "not" : "") . " in (select reportid from deviceformats df join VkFormat vf on vf.value = df.formatid where vf.name = :filter_linearformatfeature and df.lineartilingfeatures > 0)";
        $params['filter_linearformatfeature'] = $linearformatfeature;
    }
    if ($optimalformatfeature) {
        $whereClause = "where id " . ($negate ? "not" : "") . " in (select reportid from deviceformats df join VkFormat vf on vf.value = df.formatid where vf.name = :filter_optimalformatfeature and df.optimaltilingfeatures > 0)";
        $params['filter_optimalformatfeature'] = $optimalformatfeature;
    }
    if ($bufferformatfeature) {
        $whereClause = "where id " . ($negate ? "not" : "") . " in (select reportid from deviceformats df join VkFormat vf on vf.value = df.formatid where vf.name = :filter_bufferformatfeature and df.bufferfeatures > 0)";
        $params['filter_bufferformatfeature'] = $bufferformatfeature;
    }
}
// Surface format	
$surfaceformat = $_REQUEST['filter']['surfaceformat'];
if ($surfaceformat != '') {
    $whereClause = "where r.version >= '1.2' and id " . ($negate ? "not" : "") . " in (select reportid from devicesurfaceformats dsf join VkFormat f on dsf.format = f.value where f.name = :filter_surfaceformat)";
    $params['filter_surfaceformat'] = $surfaceformat;
}
// Surface present mode	
$surfacepresentmode = $_REQUEST['filter']['surfacepresentmode'];
if ($surfacepresentmode != '') {
    $whereClause = "where r.version >= '1.2' and id " . ($negate ? "not" : "") . " in (select reportid from devicesurfacemodes dsp where dsp.presentmode = :filter_surfacepresentmode)";
    $params['filter_surfacepresentmode'] = $surfacepresentmode;
}
// Limit
$limit = getRequestFilterValue('devicelimit');;
if ($limit) {
    $limitvalue =  getRequestFilterValue('devicelimitvalue');
    $selectAddColumns = ",(select dl.`$limit` from devicelimits dl where dl.reportid = r.id) as devicelimit";
    appendWhereClause("r.id in (select reportid from devicelimits where cast(`$limit` as char) = '$limitvalue')", []);
}
// Devicename (or displayname)
$devicename = getRequestFilterValue('devicename');
if ($devicename) {
    appendWhereClause("(r.devicename = :filter_devicename or r.displayname = :filter_devicename)", ['filter_devicename' => $devicename]);
}
// Displayname only (Android devices)
$displayname = getRequestFilterValue('displayname');
if ($displayname) {
    appendWhereClause("r.displayname = :filter_displayname", ['filter_displayname' => $displayname]);
}
// Instance extension
$instanceextension = getRequestFilterValue('instanceextension');
if ($instanceextension) {
    appendWhereClause("r.id " . ($negate ? "not" : "") . " in (select distinct(reportid) from deviceinstanceextensions de join instanceextensions ext on de.extensionid = ext.id where ext.name = :filter_instanceextension)", ['filter_instanceextension' => $instanceextension]);
}
// Instance layer
$instancelayer = getRequestFilterValue('instancelayer');
if ($instancelayer) {
    appendWhereClause("r.id " . ($negate ? "not" : "") . " in (select distinct(reportid) from deviceinstancelayers de join instancelayers inst on de.layerid = inst.id where inst.name = :filter_instancelayer)", ['filter_instancelayer' => $instancelayer]);
}
// Extension property    
$extensionproperty = getRequestFilterValue('extensionproperty');
$extensionpropertyvalue = getRequestFilterValue('extensionpropertyvalue');
if ($extensionproperty && $extensionpropertyvalue) {
    appendWhereClause("r.id in (select reportid from deviceproperties2 where name = :filter_extensionpropertyname and cast(value as char) = :filter_extensionpropertyvalue)", ['filter_extensionpropertyname' => $extensionproperty, 'filter_extensionpropertyvalue' => $extensionpropertyvalue]);    
}
// Extension feature
$extensionname = getRequestFilterValue('extensionname');
$extensionfeature = getRequestFilterValue('extensionfeature');
if ($extensionname && $extensionfeature) {
    appendWhereClause("r.id " . ($negate ? "not" : "") . " in (select reportid from devicefeatures2 where extension = :filter_extensionname and name = :filter_extensionfeaturename and supported = 1)", ['filter_extensionfeaturename' => $extensionfeature, 'filter_extensionname' => $extensionname]);
}
// Core feature
$corefeature = getRequestFilterValue('feature');
if (!$corefeature) {
    $corefeature = getRequestFilterValue('corefeature');
}
if ($corefeature) {
    $tablename = SqlRepository::getDeviceFeaturesTable($core);
    appendWhereClause("r.id in (select reportid from $tablename df where df.$corefeature = ".($negate ? "0" : "1").")", []);
}
// Core property
if (isset($_REQUEST['filter']['coreproperty']) && ($_REQUEST['filter']['coreproperty'] != '')) {
    // Properties can be true/false and in such cases are treated as boolean values where checking for support needs to compare with 1
    $prop_value = $_REQUEST['filter']['corepropertyvalue'];
    if ($prop_value == "") {
        $prop_value = "1";
    }
    $tablename = SqlRepository::getDevicePropertiesTable($core);
    appendWhereClause("r.id in (select reportid from $tablename where cast(`" . $_REQUEST['filter']['coreproperty'] . "` as char) = :filter_corepropertyvalue)", ['filter_corepropertyvalue' => $prop_value]);
}
// Profile
$profile = getRequestFilterValue('profile');
if ($profile) {
    appendWhereClause("r.id in (select reportid from deviceprofiles dp join profiles on dp.profileid = profiles.id where profiles.name = :filter_profile and supported = 1)", ['filter_profile' => $profile]);
}

// Portability subset
$portabilitysubset = false;
if (isset($_REQUEST['filter']['portability'])) {
    if ($_REQUEST['filter']['portability']) {
        $portabilitysubset = true;
        $whereClause = "where r.id in (SELECT reportid from deviceproperties2 dp2 where dp2.extension = 'VK_KHR_portability_subset')";
    }
}

// Platform (os)
if (isset($_REQUEST['filter']['platform']) && ($_REQUEST['filter']['platform'] != '')) {
    $platform = $_REQUEST['filter']['platform'];
    if ($platform !== "all") {
        $whereClause .= (($whereClause != '') ? ' and ' : ' where ') . 'r.ostype = :ostype';
        $params['ostype'] = ostype($platform);
    }
}

// Min. api version
$minApiVersion = SqlRepository::getMinApiVersion();
if ($minApiVersion) {
    SqlRepository::appendCondition($whereClause, "r.apiversion >= :apiversion");
    $params['apiversion'] = $minApiVersion;
}

$orderBy = "order by " . $orderByColumn . " " . $orderByDir;

if ($orderByColumn == "api") {
    $orderBy = "order by length(" . $orderByColumn . ") " . $orderByDir . ", " . $orderByColumn . " " . $orderByDir;
}

$sql = sprintf(
    "SELECT
        r.id,
        r.displayname as devicename,
        ifnull(p.driverversionraw, p.driverversion) as driver,
        p.driverversion,
        p.vendorid,
        p.apiversion as api,
        v.name as vendor,
        p.devicetype,
        r.osname,
        r.osversion,
        r.osarchitecture,
        r.version
        %s
        from reports r
        left join deviceproperties p on p.reportid = r.id
        left join vendorids v on v.id = p.vendorid
        %s
        %s
        %s",
    $selectAddColumns, $whereClause, $searchClause, $orderBy);

$devices = DB::$connection->prepare($sql." ".$paging);
$devices->execute($params);
if ($devices->rowCount() > 0) {
    foreach ($devices as $device) {
        $driver = getDriverVersion($device["driver"], "", $device["vendorid"], $device["osname"]);
        $data[] = array(
            'id' => $device["id"],
            'devicelimit' => ($limit != '') ? $device["devicelimit"] : null,
            'device' => '<a href="displayreport.php?id=' . $device["id"] . '">' . $device["devicename"] . '</a>',
            'driver' => $driver,
            'api' => $device["api"],
            'vendor' => $device["vendor"],
            'devicetype' => strtolower(str_replace('_GPU', '', $device["devicetype"])),
            'osname' => $device["osname"],
            'osversion' => $device["osversion"],
            'osarchitecture' => $device["osarchitecture"],
            'compare' => '<center><Button onClick="addToCompare('.$device['id'].',\''.$device['devicename'].'\')">Add</Button>',
            'profile' => ($portabilitysubset ? "<center><a href=\"api/v3/getprofile.php?id=".$device["id"]."\">Download</a></center>" : null)
        );
    }
}

$filteredCount = 0;
$stmnt = DB::$connection->prepare("select count(*) from reports");
$stmnt->execute();
$totalCount = $stmnt->fetchColumn();

$filteredCount = $totalCount;
if (($searchClause != '') or ($whereClause != '')) {
    $stmnt = DB::$connection->prepare($sql);
    $stmnt->execute($params);
    $filteredCount = $stmnt->rowCount();
}

$results = array(
    "draw" => isset($_REQUEST['draw']) ? intval($_REQUEST['draw']) : 0,
    "recordsTotal" => intval($totalCount),
    "recordsFiltered" => intval($filteredCount),
    "data" => $data
);

$elapsed = (microtime(true) - $start) * 1000;

DB::log('api/internal/reports.php', $sql, $elapsed);

DB::disconnect();

echo json_encode($results);
