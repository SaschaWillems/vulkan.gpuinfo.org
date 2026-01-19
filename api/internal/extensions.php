<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2025 Sascha Willems (www.saschawillems.de)
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

 /*
  * Back-end data source for extension listing
  */ 

include '../../database/database.class.php';
include '../../database/sqlrepository.php';
include '../../includes/functions.php';
include '../../includes/constants.php';

session_name(SESSION_NAME);
session_start();

DB::connect();

$start = microtime(true);

// Process the ordering, paging, searching, etc. parts of the ajax requests of a datatable

$paging = null;
$params = [];
$whereClause = null;
$orderByClause = null;
$platform = 'all';

// Ordering
if (isset($_REQUEST['order'])) {
    $columnNames = ['name', 'coverage', 'date', 'hasfeatures', 'hasproperties'];
    $orderByColumn = $columnNames[$_REQUEST['order'][0]['column']];
    $orderByDir = $_REQUEST['order'][0]['dir'];
    if (!empty($orderByColumn)) {
        $orderByClause = "order by ".$orderByColumn." ".$orderByDir;
    }
}

// Platform (os)
if (isset($_REQUEST['filter']['platform']) && ($_REQUEST['filter']['platform'] != '')) {
    $platform = $_REQUEST['filter']['platform'];
} else {
    // @todo: don't duplicate
    if (isset($_SESSION['default_os_selection'])) {
        $platform_setting = sanitize($_SESSION['default_os_selection']);
        if ($platform_setting !== null) {
            $platform = $platform_setting;
        }
    }
}
if ($platform !== "all") {
    SqlRepository::appendCondition($whereClause, 'r.ostype = :ostype');
    $params['ostype'] = ostype($platform);
}

// Global filters START
if (isset($_SESSION['minversion'])) {
    SqlRepository::appendCondition($whereClause, 'r.apiversion >= :apiversion');
    $params['apiversion'] = $_SESSION['minversion'];
}

$start_date = SqlRepository::getMinStartDate();
if ($start_date) {
    SqlRepository::appendCondition($whereClause, 'r.submissiondate >= :startdate');
    $params['startdate'] = $start_date;
}

$device_selection = SqlRepository::getDeviceTypeSelection();
if ($device_selection) {
    if ($device_selection == 'no_virtual') {
        SqlRepository::appendCondition($whereClause, 'r.devicetype != :devicetype');
        $params['devicetype'] = 3;
    }
    if ($device_selection == 'no_cpu') {
        SqlRepository::appendCondition($whereClause, 'r.devicetype != :devicetype');
        $params['devicetype'] = 4;
    }
    if ($device_selection == 'no_cpu_no_virtual') {
        SqlRepository::appendCondition($whereClause, 'r.devicetype < :devicetype');
        $params['devicetype'] = 3;
    }    
}

$layered_implementations = SqlRepository::getLayeredImplementationsOption();
if (!$layered_implementations) {
    SqlRepository::appendCondition($whereClause, "r.layered = 0");
}

// Global filters END

// Counts (required for pagination)
$filteredCount = 0;
$countParams = [];
$sql = "SELECT count(*) from extensions";
$stmnt = DB::$connection->prepare($sql);
$stmnt->execute($countParams);
$totalCount = $filteredCount = $stmnt->fetchColumn();
// Filter by extension name
if (isset($_REQUEST['search']) && $_REQUEST['search']['value'] != '') {
    SqlRepository::appendCondition($sql, 'name like :globalsearch');
    $countParams['globalsearch'] = '%'.$_REQUEST['search']['value'].'%';
    $stmnt = DB::$connection->prepare($sql);
    $stmnt->execute($countParams);
    $filteredCount = $stmnt->fetchColumn();
}

$sql = "SELECT count(distinct(ifnull(r.displayname, dp.devicename))) from reports r join deviceproperties dp on dp.reportid = r.id $whereClause";
$stmnt = DB::$connection->prepare($sql);
$stmnt->execute($params);
$deviceCount = $stmnt->fetch(PDO::FETCH_COLUMN);    

// Dates are stored per platform, so we need to fetch from the appropriate column
$dateColumn = 'date';
if ($platform !== 'all') {
    $dateColumn = 'date'.strtolower($platform);
}

// Some drivers wrongly report some instance extensions as device extensions
// To avoid confusion, those entries are hidden
SqlRepository::appendCondition($whereClause, 'name not in (select name from deviceextensions_blacklist)');

// Filter by extension name
if (isset($_REQUEST['search']) && $_REQUEST['search']['value'] != '') {
    SqlRepository::appendCondition($whereClause, 'name like :globalsearch');
    $params['globalsearch'] = '%'.$_REQUEST['search']['value'].'%';
}

// Paging
if (isset($_REQUEST['start']) && $_REQUEST['length'] != '-1') {
    $paging = "LIMIT " . $_REQUEST["length"] . " OFFSET " . $_REQUEST["start"];
}

// Fetch extensions with coverage based on unique device names from the database
$sql ="SELECT e.name as name, e.hasfeatures, e.hasproperties, date(e.$dateColumn) as date, count(distinct(ifnull(r.displayname, dp.devicename))) as coverage from extensions e 
        join deviceextensions de on de.extensionid = e.id 
        join reports r on r.id = de.reportid
        join deviceproperties dp on dp.reportid = r.id
        $whereClause
        group by name
        $orderByClause";
$stmnt = DB::$connection->prepare($sql." ".$paging);
$stmnt->execute($params);

$data = [];
while ($row = $stmnt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
    if (trim($row['name']) == '') {
        continue;
    }

    $ext = $row['name'];
    $coverageLink = "listdevicescoverage.php?extension=$ext&platform=$platform";
    $coverage = round($row['coverage'] / $deviceCount * 100, 2);
    $feature_link = null;
    if ($row['hasfeatures']) {
        $feature_link = "<a href='listfeaturesextensions.php?extension=$ext&platform=$platform'><span class='glyphicon glyphicon-search' title='Display features for this extension'/></a>";
    }
    $property_link = null;
    if ($row['hasproperties']) {
        $property_link = "<a href='listpropertiesextensions.php?extension=$ext&platform=$platform'><span class='glyphicon glyphicon-search' title='Display properties for this extension'/></a>";
    }
    $ext_url = "<a href=\"displayextensiondetail.php?extension=$ext\">$ext</a>";

    $data[] = [
        'name' => $ext_url,
        'coverage' => "<a class='supported' href=\"$coverageLink\">$coverage<span style='font-size:10px;'>%</span></a>",
        'features' => $feature_link, 
        'properties' => $property_link,
        'date' => $row['date']
    ];
}        

// Return the data in a format suited for data tables AJAX requests
$results = [
    "draw" => isset($_REQUEST['draw']) ? intval($_REQUEST['draw']) : 0,
    "recordsTotal" => intval($totalCount),
    "recordsFiltered" => intval($filteredCount),
    "data" => $data
];
echo json_encode($results);

$elapsed = (microtime(true) - $start) * 1000;

DB::log('api/internal/extensions.php', $sql, $elapsed);

DB::disconnect();
