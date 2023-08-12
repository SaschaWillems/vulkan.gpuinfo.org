<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2023 Sascha Willems (www.saschawillems.de)
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

include '../../includes/constants.php';

session_name(SESSION_NAME);
session_start();

include '../../database/database.class.php';
include '../../database/sqlrepository.php';
include '../../includes/functions.php';

DB::connect();

$start = microtime(true);

// Process the ordering, paging, searching, etc. parts of the ajax requests of a datatable

$paging = null;
$params = [];
$whereClause = null;
$platform = 'all';

// Paging
if (isset($_REQUEST['start']) && $_REQUEST['length'] != '-1') {
    $paging = "LIMIT " . $_REQUEST["length"] . " OFFSET " . $_REQUEST["start"];
}

// Platform (os)
if (isset($_REQUEST['filter']['platform']) && ($_REQUEST['filter']['platform'] != '')) {
    $platform = $_REQUEST['filter']['platform'];
    if ($platform !== "all") {
        $whereClause .= ($whereClause ? ' and ' : ' where ') . 'r.ostype = :ostype';
        $params['ostype'] = ostype($platform);
    }
}

// Minimum API version can be set in the session (global option)
if (isset($_SESSION['minversion'])) {
    $whereClause .= ($whereClause ? ' and ' : ' where ') . 'r.apiversion >= :apiversion';
    $params['apiversion'] =$_SESSION['minversion'];
}

$filteredCount = 0;
$stmnt = DB::$connection->prepare("select count(*) from extensions"); // @todo: whereClause?
$stmnt->execute();
$filteredCount = $totalCount = $stmnt->fetchColumn();

$sql = "SELECT count(distinct(ifnull(r.displayname, dp.devicename))) from reports r join deviceproperties dp on dp.reportid = r.id $whereClause";
$stmnt = DB::$connection->prepare($sql);
$stmnt->execute($params);
$deviceCount = $stmnt->fetch(PDO::FETCH_COLUMN);    

// To get additional features and/or properties for an extension, we fetch all extension names with features and/or properties into arrays so we can look them up
$sql = "SELECT distinct(extension) FROM devicefeatures2";
$stmnt = DB::$connection->prepare($sql);
$stmnt->execute();
$extensionFeatures = $stmnt->fetchAll(PDO::FETCH_COLUMN, 0);

$sql = "SELECT distinct(extension) FROM deviceproperties2 d";
$stmnt = DB::$connection->prepare($sql);
$stmnt->execute();
$extensionProperties = $stmnt->fetchAll(PDO::FETCH_COLUMN, 0);        

// Dates are stored per platform, so we need to fetch from the appropriate column
$dateColumn = 'date';
if ($platform !== 'all') {
    $dateColumn = 'date'.strtolower($platform);
}

// Fetch extensions with coverage based on unique device names from the database
$sql ="SELECT e.name as name, date(min(e.$dateColumn)) as date, count(distinct(r.displayname)) as coverage from extensions e 
        join deviceextensions de on de.extensionid = e.id 
        join reports r on r.id = de.reportid
        $whereClause
        group by name";
$stmnt = DB::$connection->prepare($sql);
$stmnt->execute($params);

$data = [];
while ($row = $stmnt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
    if (trim($row['name']) == '') {
        continue;
    }

    $coverageLink = "listdevicescoverage.php?extension=" . $row['name'] . "&platform=$platform";
    $manPageLink = "[<a href='".VULKAN_REGISTRY_URL.$row['name'].".html' target='_blank' title='Show manpage for this extension'>?</a>]";
    $coverage = round($row['coverage'] / $deviceCount * 100, 2);
    $ext = $row['name'];
    $feature_link = null;
    if (in_array($row['name'], $extensionFeatures)) {
        $feature_link = "<a href='listfeaturesextensions.php?extension=$ext&platform=$platform'><span class='glyphicon glyphicon-search' title='Display features for this extension'/></a";
    }
    $property_link = null;
    if (in_array($row['name'], $extensionProperties)) {
        $property_link = "<a href='listpropertiesextensions.php?extension=$ext&platform=$platform'><span class='glyphicon glyphicon-search' title='Display properties for this extension'/></a";
    }

    $data[] = [
        'name' => "$ext $manPageLink",
        'coverage' => "<a class='supported' href=\"$coverageLink\">$coverage<span style='font-size:10px;'>%</span></a>",
        'coverageunsupported' => "<a class='na' href=\"$coverageLink&option=not\">".round(100.0 - $coverage, 2)."<span style='font-size:10px;'>%</span></a>",
        'features' => $feature_link, 
        'properties' => $property_link,
        'date' => $row['date']
    ];
}        

// Return the data in a format suited for data tables AJAX requests
$results = array(
    "draw" => isset($_REQUEST['draw']) ? intval($_REQUEST['draw']) : 0,
    "recordsTotal" => intval($totalCount),
    "recordsFiltered" => intval($filteredCount),
    "data" => $data
);
echo json_encode($results);

$elapsed = (microtime(true) - $start) * 1000;

DB::log('api/external/extensions.php', $sql, $elapsed);

DB::disconnect();
