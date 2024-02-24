<?php

	/** 		
	 *
	 * Vulkan hardware capability database back-end
	 *	
	 * Copyright (C) 2016-2023 by Sascha Willems (www.saschawillems.de)
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

require '../database/database.class.php';
require '../includes/functions.php';

DB::connect();

// Populate from device and instance extensions
$coverage_list = [];

$sql ="SELECT name, 'device' as type from extensions union SELECT name, 'instance' as type from instanceextensions order by name"; 
$stmnt = DB::$connection->prepare($sql);
$stmnt->execute($params);
while ($row = $stmnt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
    if (trim($row['name']) == '') {
        continue;
    }    
    $coverage_list[$row['name']] = [
        $row['type'],
        0,  // Total
        0,  // Windows
        0,  // Android
        0,  // Linux
        0,  // MacOS
        0,  // iOS
    ];
}

// Loop for total coverage and each platform

// Device extensions
for ($i = 0; $i < 6; $i++) {

    // Device extensions

    if ($i == 0) {
        echo "Fetching device extension total coverage".PHP_EOL;
    } else {
        echo "Fetching device extension coverage for ".platformname($i - 1).PHP_EOL;
    };

    $whereClause = null;
    if ($i > 0) {
        $whereClause = " where ostype = ".$i-1;
    }

    $sql = "SELECT count(distinct(r.displayname)) from reports r join deviceproperties dp on dp.reportid = r.id $whereClause";
    $stmnt = DB::$connection->prepare($sql);
    $stmnt->execute($params);
    $deviceCount = $stmnt->fetch(PDO::FETCH_COLUMN);

    $sql ="SELECT e.name as name, count(distinct(r.displayname)) as coverage from extensions e 
            join deviceextensions de on de.extensionid = e.id 
            join reports r on r.id = de.reportid
            $whereClause
            group by name";
    $stmnt = DB::$connection->prepare($sql);
    $stmnt->execute($params);
    while ($row = $stmnt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
        if (trim($row['name']) == '') {
            continue;
        }
        $coverage = round($row['coverage'] / $deviceCount * 100, 2);
        // Coverage numbers start at index 1
        $coverage_list[$row['name']][$i + 1] = $coverage;
    }

    // Instance extensions

    if ($i == 0) {
        echo "Fetching instance extension total coverage".PHP_EOL;
    } else {
        echo "Fetching instance extension coverage for ".platformname($i - 1).PHP_EOL;
    };

    $whereClause = null;
    if ($i > 0) {
        $whereClause = " where ostype = ".$i-1;
    }

    $sql = "SELECT count(distinct(r.displayname)) from reports r join deviceproperties dp on dp.reportid = r.id $whereClause";
    $stmnt = DB::$connection->prepare($sql);
    $stmnt->execute($params);
    $deviceCount = $stmnt->fetch(PDO::FETCH_COLUMN);

    $sql ="SELECT e.name as name, count(distinct(r.displayname)) as coverage from instanceextensions e 
            join deviceinstanceextensions de on de.extensionid = e.id 
            join reports r on r.id = de.reportid
            $whereClause
            group by name";
    $stmnt = DB::$connection->prepare($sql);
    $stmnt->execute($params);
    while ($row = $stmnt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
        if (trim($row['name']) == '') {
            continue;
        }
        $coverage = round($row['coverage'] / $deviceCount * 100, 2);
        // Coverage numbers start at index 1
        $coverage_list[$row['name']][$i + 1] = $coverage;
    }    
}

// Save to csv
$fp = fopen('file.csv', 'w');
$csv = '';
fputcsv($fp, ['Name', 'Type', 'Total Coverage', 'Windows coverage', 'Linux coverage', 'Android coverage', 'MacOS coverage', 'iOS coverage']);
foreach ($coverage_list as $key => $coverage) {
    $row = $coverage;
    array_unshift($row, $key);
    fputcsv($fp, $row);
}
fclose($fp);

DB::disconnect();