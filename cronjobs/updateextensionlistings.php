<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2026 Sascha Willems (www.saschawillems.de)
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

 // Generate aggregated extension statistics

include '../database/database.class.php';
include '../database/sqlrepository.php';
include '../includes/functions.php';
include '../includes/constants.php';

set_time_limit(5000);

DB::connect();

$start = microtime(true);

// We don't need to update the stats if no reports have been uploaded since the last generation
$stmnt = DB::$connection->prepare("select TIMESTAMPDIFF(SECOND, max(r.submissiondate), (select date from cacheinfo where identifier = 'extension_stats')) from reports r");
$stmnt->execute();
$delta = $stmnt->fetchColumn(0);
if ($delta > 0) {
    logToFile("[Extension stats] Skipping generation, no new report since last generation run");
    exit;
}

try {

    DB::$connection->beginTransaction();
   
    // Update stats
    $ostypes = [null, 0, 1, 2, 3, 4];
    $apiversions = ['1.0', '1.1', '1.2', '1.3', '1.4'];
    $ages = [null, 1];
    foreach ($ages as $age) {
        foreach ($ostypes as $ostype) {
            foreach ($apiversions as $apiversion) {
                echo "apiversion = $apiversion / ostype = $ostype / age = $age</br>";
                $params = [
                    "ostype" => $ostype,
                    "age" => $age,
                    "apiversion" => $apiversion
                ];
                $whereClause = "where left(apiversion, 3) >= '$apiversion'";
                $dateColumn = 'date';
                if (!is_null($ostype)) {
                    $whereClause .= " and ostype = $ostype";
                    $dateColumn = 'date'.strtolower(platformname($ostype));
                }
                if (!is_null($age)) {
                    $whereClause .= " and date(r.submissiondate) >= DATE_ADD(CURDATE(), interval -$age YEAR)";
                }
                $whereClause .= " and r.layered = 0";
                $sql = "INSERT into extension_stats (name, ostype, apiversion, age, firstseen, hasfeatures, hasproperties, coverage, state)
                        SELECT e.name, :ostype, :apiversion, :age,  min(date(e.$dateColumn)), e.hasfeatures, e.hasproperties, count(distinct(r.displayname)) as coverage, 1
                        from deviceextensions de join extensions e on e.id = de.extensionid join reports r on r.id = de.reportid
                        $whereClause
                        group by e.name";
                $stmnt = DB::$connection->prepare($sql);
                $stmnt->execute($params);
            }   
        }
    }
    // Delete old rows
    echo "Deleting old rows<br/>";
    $stmnt = DB::$connection->prepare("DELETE FROM extension_stats where state = 0");
    $stmnt->execute();        

    // Mark new rows as active
    $stmnt = DB::$connection->prepare("UPDATE extension_stats set state = 0 where state = 1");
    $stmnt->execute();

    // Update cache info
    $stmnt = DB::$connection->prepare("REPLACE into cacheinfo (identifier, date) values ('extension_stats', now())");
    $stmnt->execute();

    DB::$connection->commit();         
} catch (PDOException $e) {
    echo $e->getMessage();
    logToFile("Extension stats] Error: ".$e->getMessage());
    exit();
}

$delta = (microtime(true) - $start) * 1000;
echo "Took $delta ms".PHP_EOL;
echo "success";

logToFile("Extension stats] Generating took $delta ms");

DB::disconnect();

 