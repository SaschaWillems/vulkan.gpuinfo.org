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

DB::connect();

$start = microtime(true);

$data = array();
$params = array();
$ostype = null;
$core = '1.0';

if (isset($_REQUEST["platform"])) {
    $ostype = ostype($_REQUEST["platform"]);
}
if (isset($_REQUEST['filter']['core'])) {
    $core = $_REQUEST['filter']['core'];
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
}

// Paging
$paging = '';
if (isset($_REQUEST['start']) && $_REQUEST['length'] != '-1') {
    $paging = "LIMIT " . $_REQUEST["length"] . " OFFSET " . $_REQUEST["start"];
}

// Filtering
$searchColumns = ['device', 'api', 'driverversion', 'reportcount'];
if ($ostype == 2) {
    array_splice($searchColumns, 1, 0, ['gpuname']);
}

$minversion = false;
if (isset($_REQUEST['minversion'])) {
    $minversion = true;
    $searchColumns = ['device', 'vendor', 'driverversion'];
}

// Per-column filtering
$filters = array();
for ($i = 0; $i < count($_REQUEST['columns']); $i++) {
    $column = $_REQUEST['columns'][$i];
    if (($column['searchable'] == 'true') && ($column['search']['value'] != '')) {
        if ($searchColumns[$i] == 'api') {
            $filters[] = 'VkVersion(api) like :filter_' . $i;
        } else {
            $filters[] = $searchColumns[$i] . ' like :filter_' . $i;
        }
        $params['filter_' . $i] = '%' . $column['search']['value'] . '%';
    }
}
$searchClause = null;
if (sizeof($filters) > 0) {
    $searchClause = 'having ' . implode(' and ', $filters);
}

$whereClause = '';
$selectAddColumns = '';
$negate = false;
if (isset($_REQUEST['filter']['option'])) {
    if ($_REQUEST['filter']['option'] == 'not') {
        $negate = true;
    }
}

// Store filters to narrow down the list of reports 
$report_filters = [];

// Operating system
// @todo: use in all places
$os_and_clause = null;
if (isset($_REQUEST["platform"])) {
    $platform = GET_sanitized('platform');
    if ($platform !== "all") {
        $os_and_clause = "AND r.ostype = '".ostype($platform)."'";
    }
}

$fnAddWhereClause = function($term) use (&$whereClause) {
    if ($whereClause == '') {
        $whereClause = "where $term"; 
    } else {
        $whereClause .= "and $term";
    }
};

// Filters
// Extension
$extension = null;
if (isset($_REQUEST['filter']['extension'])) {
    $extension = $_REQUEST['filter']['extension'];
    if ($extension != '') {
        if ($negate) {
            $whereClause = "where r.devicename not in (select r.devicename from reports r join deviceextensions de on de.reportid = r.id join extensions ext on de.extensionid = ext.id where ext.name = :filter_extension)";
        } else {
            $fnAddWhereClause("r.id in (select distinct(reportid) from deviceextensions de join extensions ext on de.extensionid = ext.id where ext.name = :filter_extension)");
            $report_filters=['extension' => $extension];
        }
        $params['filter_extension'] = $extension;
    }
}
// Feature
if (isset($_REQUEST['filter']['feature'])) {
    $feature = $_REQUEST['filter']['feature'];
    if ($feature != '') {
        switch ($core) {
            case '1.1':
                $tablename = 'devicefeatures11';
                break;
            case '1.2':
                $tablename = 'devicefeatures12';
                break;
            case '1.3':
                $tablename = 'devicefeatures13';
                break;
            default:
                $tablename = 'devicefeatures';
        }
        $whereClause = "where r.devicename " . ($negate ? "not" : "") . " in (select r.devicename from reports r join $tablename df on df.reportid = r.id where df.$feature = 1)";
    }
}
// Extension features
if (isset($_REQUEST['filter']['extensionfeature_name']) && isset($_REQUEST['filter']['extensionfeature_feature'])) {
    $ext_name = $_REQUEST['filter']['extensionfeature_name'];
    $ext_feature = $_REQUEST['filter']['extensionfeature_feature'];
    if (($ext_name != '') && ($ext_feature != '')) {
        $whereClause = "where r.id " . ($negate ? "not" : "") . " in (select r.id from reports r join devicefeatures2 df2 on df2.reportid = r.id where df2.extension = :filter_ext_name and df2.name = :filter_ext_feature and df2.supported = 1)";
        $params['filter_ext_name'] = $ext_name;
        $params['filter_ext_feature'] = $ext_feature;
    }
}
// Extension properties
if (isset($_REQUEST['filter']['extensionproperty_name']) && isset($_REQUEST['filter']['extensionproperty_property'])) {
    $ext_name = $_REQUEST['filter']['extensionproperty_name'];
    $ext_property = $_REQUEST['filter']['extensionproperty_property'];
    $ext_property_value = $_REQUEST['filter']['extensionproperty_value'];
    if (($ext_name != '') && ($ext_property != '') && ($ext_property_value != '')) {
        $whereClause = "where r.id " . ($negate ? "not" : "") . " in (select r.id from reports r join deviceproperties2 dp2 on dp2.reportid = r.id where dp2.extension = :filter_ext_name and dp2.name = :filter_ext_property and dp2.value = :filter_ext_property_value)";
        $params['filter_ext_name'] = $ext_name;
        $params['filter_ext_property'] = $ext_property;
        $params['filter_ext_property_value'] = $ext_property_value;
    }
}
// Core properties
if (isset($_REQUEST['filter']['coreproperty'])) {
    $property = $_REQUEST['filter']['coreproperty'];
    if ($property != '') {
        switch ($core) {
            case '1.1':
                $tablename = 'deviceproperties11';
                break;
            case '1.2':
                $tablename = 'deviceproperties12';
                break;
            case '1.3':
                $tablename = 'deviceproperties13';
                $property = getShortFieldName($property);
                break;
            default:
                $tablename = 'deviceproperties';
        }
        if (stripos($property, 'subgroupProperties.') !== false) {
            $property = "`$property`";
        }
        $whereClause = "where r.id " . ($negate ? "not" : "") . " in (select r.id from reports r join $tablename dp on dp.reportid = r.id where dp.$property = 1)";
    }
}
// Submitter
if (isset($_REQUEST['filter']['submitter'])) {
    $submitter = $_REQUEST['filter']['submitter'];
    if ($submitter != '') {
        $whereClause = "where r.submitter = :filter_submitter";
        $params['filter_submitter'] = $submitter;
    }
}
// Image format and buffer format flag support
$linear_tiling_format = $_REQUEST['filter']['lineartilingformat'];
$optimal_tiling_format = $_REQUEST['filter']['optimaltilingformat'];
$buffer_format = $_REQUEST['filter']['bufferformat'];
if ($linear_tiling_format != '' || $optimal_tiling_format != '' || $buffer_format != '') {
    $format_column = null;

    $featureflag = null;
    $featureflagbit = $_REQUEST['filter']['featureflagbit'];
    if (in_array($featureflagbit, $device_format_flags_tiling)) {
        $featureflag = array_search($featureflagbit , $device_format_flags_tiling);
    }
    if (!$featureflag) {
        $featureflag = array_search($featureflagbit , $device_format_flags_buffer);
    }
    assert($featureflag != null);

    if ($linear_tiling_format != '') {
        $format_column = 'lineartilingfeatures';
        $params['filter_format_name'] = $linear_tiling_format;
    }
    if ($optimal_tiling_format != '') {
        $format_column = 'optimaltilingfeatures';
        $params['filter_format_name'] = $optimal_tiling_format;
    }
    if ($buffer_format != '') {
        $format_column = 'bufferfeatures';
        $params['filter_format_name'] = $buffer_format;
    }

    $whereClause = "
        where r.displayname " . ($negate ? "not" : "") . " in
        (
            select r.displayname
            from reports r
            join deviceformats df on df.reportid = r.id
            join VkFormat vf on vf.value = df.formatid where 
            vf.name = :filter_format_name and df.$format_column & $featureflag = $featureflag
        )";    
}
// Memory type support
$memorytype = $_REQUEST['filter']['memorytype'];
if ($memorytype != '') {
    $whereClause =
        "where ifnull(r.displayname, r.devicename) " . ($negate ? "not" : "") . " in
			(
				select ifnull(r.displayname, r.devicename)
				from reports r
				join devicememorytypes dmt on dmt.reportid = r.id
				where dmt.propertyflags = :filter_memorytype
                $os_and_clause
			)
			and r.version >= '1.2'";
    $params['filter_memorytype'] = $memorytype;
}
// Surface format	
$surfaceformat = $_REQUEST['filter']['surfaceformat'];
$surfaceformat_colorspace = $_REQUEST['filter']['surfaceformatcolorspace'];
if ($surfaceformat != '') {
    $whereClause =
        "where ifnull(r.displayname, r.devicename) " . ($negate ? "not" : "") . " in
            (
                SELECT ifnull(r.displayname, r.devicename)
                from reports r
                join devicesurfaceformats dsf on dsf.reportid = r.id	
                join VkFormat f on dsf.format = f.value
                where f.name = :filter_surfaceformat";
    if ($surfaceformat_colorspace !== null) {
        $whereClause .= " and dsf.colorspace = :filter_surfacecolorspace";
        $params['filter_surfacecolorspace'] = $surfaceformat_colorspace;
    }                           
    $whereClause .= " $os_and_clause) and r.version >= '1.2'";
    $params['filter_surfaceformat'] = $surfaceformat;
}
// Surface present mode	
$surfacepresentmode = $_REQUEST['filter']['surfacepresentmode'];
if ($surfacepresentmode != '') {
    $whereClause =
        "where ifnull(r.displayname, r.devicename) " . ($negate ? "not" : "") . " in
            (
                select ifnull(r.displayname, r.devicename)
                from reports r
                join devicesurfacemodes dsm on dsm.reportid = r.id	
                join VkPresentMode m on dsm.presentmode = m.value 
                where m.name = :filter_surfacepresentmode
                $os_and_clause
            )
            and r.version >= '1.2'";
    $params['filter_surfacepresentmode'] = $surfacepresentmode;
}
// Surface usage flag
$surface_usage_flag = $_REQUEST['filter']['surfaceusageflag'];
if ($surface_usage_flag != '') {
    $surface_usage_flag_value = array_search($surface_usage_flag , SurfaceConstants::UsageFlags);
    $whereClause =
        "where ifnull(r.displayname, r.devicename) " . ($negate ? "not" : "") . " in
            (
                select ifnull(r.displayname, r.devicename)
                from reports r
                join devicesurfacecapabilities dsf on dsf.reportid = r.id
                where dsf.supportedUsageFlags & :filter_surface_usage_flag = :filter_surface_usage_flag
                $os_and_clause
            )
            and r.version >= '1.2'";
    $params['filter_surface_usage_flag'] = $surface_usage_flag_value;
}

// Surface transform mode
$surface_transform_mode = $_REQUEST['filter']['surfacetransformmode'];
if ($surface_transform_mode != '') {
    $surface_transform_mode_value = array_search($surface_transform_mode , SurfaceConstants::TransformFlags);
    $whereClause =
        "where ifnull(r.displayname, r.devicename) " . ($negate ? "not" : "") . " in
            (
                select ifnull(r.displayname, r.devicename)
                from reports r
                join devicesurfacecapabilities dsf on dsf.reportid = r.id
                where dsf.supportedTransforms & :filter_surface_transform_mode = :filter_surface_transform_mode
                $os_and_clause
            )
            and r.version >= '1.2'";
    $params['filter_surface_transform_mode'] = $surface_transform_mode_value;
}

// Surface composite alpha mode
$surface_composite_alpha_mode = $_REQUEST['filter']['surfacecompositealphamode'];
if ($surface_composite_alpha_mode != '') {
    $surface_composite_alpha_mode_value = array_search($surface_composite_alpha_mode , SurfaceConstants::CompositeAlphaFlags);
    $whereClause =
        "where ifnull(r.displayname, r.devicename) " . ($negate ? "not" : "") . " in
            (
                select ifnull(r.displayname, r.devicename)
                from reports r
                join devicesurfacecapabilities dsf on dsf.reportid = r.id
                where dsf.supportedCompositeAlpha & :filter_surface_composite_alpha_mode = :filter_surface_composite_alpha_mode
                $os_and_clause
            )
            and r.version >= '1.2'";
    $params['filter_surface_composite_alpha_mode'] = $surface_composite_alpha_mode_value;
}

// Limit
$limit = $_REQUEST['filter']['devicelimit'];
if ($limit != '') {
    $selectAddColumns = ",(select dl.`" . $limit . "` from devicelimits dl where dl.reportid = r.id) as devicelimit";
    // Check if a limit requirement rule has to be applied (see Table 36. of the specs)
    $sql = "select feature from limitrequirements where limitname = :limit";
    $reqs = DB::$connection->prepare($sql);
    $reqs->execute(array(":limit" => $limit));
    if ($reqs->rowCount() > 0) {
        $req = $reqs->fetch();
        $whereClause = "where r.id in (select distinct(reportid) from devicefeatures df where df." . $req["feature"] . " = 1)";
    }
}
// Devicename
if (isset($_REQUEST['filter']['devicename'])) {
    $devicename = $_REQUEST['filter']['devicename'];
    if ($devicename != '') {
        $fnAddWhereClause("r.devicename = :filter_devicename");
        $params['filter_devicename'] = $devicename;
    }
}
// Displayname (Android devices)
if (isset($_REQUEST['filter']['displayname'])) {
    $displayname = $_REQUEST['filter']['displayname'];
    if ($displayname != '') {
        $whereClause = "where r.displayname = :filter_displayname";
        $params['filter_displayname'] = $displayname;
    }
}
// Profile
if (isset($_REQUEST['filter']['profile'])) {
    $profile = $_REQUEST['filter']['profile'];
    if ($profile != '') {
        if ($negate) {
            $whereClause = "where r.version >= '3.2' and r.devicename not in (select r.devicename from deviceprofiles dp join profiles p on dp.profileid = p.id join reports r on r.id = dp.reportid where p.name = :filter_profile and dp.supported = 1)";
        } else {
            $whereClause = "where r.id in (select distinct(reportid) from deviceprofiles dp join profiles p on dp.profileid = p.id where p.name = :filter_profile and dp.supported = 1)";
        }
        $params['filter_profile'] = $profile;
    }
}
// Queue family flag combination
$queuefamilyflags = $_REQUEST['filter']['queuefamilyflags'];
if ($queuefamilyflags != '') {
    $whereClause =
        "where r.displayname " . ($negate ? "not" : "") . " in
			(
				select r.displayname
				from reports r
				join devicequeues dq on dq.reportid = r.id
				where dq.flags = :filter_queuefamilyflags
                $os_and_clause
			)";
    $params['filter_queuefamilyflags'] = $queuefamilyflags;
}

$orderBy = "order by " . $orderByColumn . " " . $orderByDir;

// TODO: Change to ostype
if (isset($_REQUEST["platform"])) {
    $platform = $_REQUEST["platform"];
    if ($platform !== "all") {
        if ($whereClause != '') {
            $whereClause .= ' and ';
        } else {
            $whereClause = ' where ';
        }
        $ostype = ostype($platform);
        $whereClause .= "r.ostype = '" . $ostype . "'";
    }
}

// Min. api version
$minApiVersion = SqlRepository::getMinApiVersion();
if ($minApiVersion) {
    SqlRepository::appendCondition($whereClause, "r.apiversion >= :apiversion");
    $params['apiversion'] = $minApiVersion;
}

if ($minversion) {
    // This statement is used for coverage based listings, e.g. extension support
    $sql = sprintf(
        "SELECT 
            r.displayname as device, 
            r.devicename as gpuname,
            min(dp.apiversionraw) as api,
            min(dp.driverversion) as driverversion,
            min(dp.driverversionraw) as driverversionraw, 
            0 as reportcount,
            min(submissiondate) as submissiondate,
            v.name as vendor,
            dp.vendorid as vendorid,
            date(min(submissiondate)) as submissiondate,
            r.osname as osname
            from reports r
            join deviceproperties dp on r.id = dp.reportid
            left join vendorids v on v.id = dp.vendorid
            %s
            group by device
            %s
            %s",
        $whereClause, $searchClause, $orderBy);
} else {
    // This statement is used for general device listsings
    $sql = sprintf(
        "SELECT 
            r.id,
            r.displayname as device, 
            r.devicename as gpuname,
            max(dp.apiversionraw) as api,
            max(dp.driverversion) as driverversion,
            max(dp.driverversionraw) as driverversionraw, 
            count(distinct r.id) as reportcount,
            v.name as vendor,
            dp.vendorid as vendorid,
            max(r.submissiondate) as submissiondate,
            r.osname as osname
            from deviceproperties dp
            join reports r on r.id = dp.reportid
            left join vendorids v on v.id = dp.vendorid          
            %s
            group by device
            %s
            %s",
        $whereClause, $searchClause, $orderBy);
}

$devices = DB::$connection->prepare($sql." ".$paging);
$devices->execute($params);
if ($devices->rowCount() > 0) {
    foreach ($devices as $device) {
        $url = 'listreports.php?devicename=' . urlencode($device["device"]);
        // Append additional filter criteria (e.g. when coming from the extension listing)
        foreach ($report_filters as $filter_key => $filter_value) {
            $url .= '&'.$filter_key.'='.urlencode($filter_value);
        }
        if ($platform !== 'all') {
            $url .= '&platform=' . $platform;
        }
        $data[] = array(
            'device' => '<a href="' . $url . '">' . $device["device"] . '</a>',
            'gpuname' => $device['gpuname'],
            'api' => versionToString($device["api"]),
            'driver' =>  getDriverVersion($device["driverversionraw"], "", $device["vendorid"], $device["osname"]),
            'reportcount' => $device["reportcount"],
            'submissiondate' => $device["submissiondate"],
            'vendor' => $device["vendor"],
            'compare' => '<center><Button onClick="addToCompare(\''.$device['device'].'\','.($ostype !== null ? $ostype : '').')">Add</Button>',
        );
    }
}

$filteredCount = 0;
$stmnt = DB::$connection->prepare($sql);
$stmnt->execute($params);
$totalCount = $stmnt->rowCount();

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

DB::log('api/internal/devices.php', $sql, $elapsed);

DB::disconnect();

echo json_encode($results);
