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

// @todo: mark rows to be deleted (toggle new column active and delete afterwards)
$stmnt = DB::$connection->prepare("TRUNCATE extension_stats");
$stmnt->execute();

$year = NULL;

try {
    $ostypes = [null, 0, 1, 2, 3, 4];
    $apiversions = ['1.0', '1.1', '1.2', '1.3', '1.4'];
    foreach ($ostypes as $ostype) {
        foreach ($apiversions as $apiversion) {
            echo $apiversion."</br>";
            DB::$connection->beginTransaction();
            $whereClause = "where left(apiversion, 3) >= '$apiversion'";
            if (!is_null($ostype)) {
                $whereClause .= " and ostype = $ostype";
            }
            $whereClause .= " and r.layered = 0";
            $sql = "INSERT into extension_stats (name, ostype, apiversion, age, coverage)
                    SELECT e.name, :ostype, :apiversion, :year, count(distinct(r.displayname)) as coverage
                    from deviceextensions de join extensions e on e.id = de.extensionid join reports r on r.id = de.reportid
                    $whereClause
                    group by e.name";
            $params = [
                "ostype" => $ostype,
                "year" => $year,
                "apiversion" => $apiversion
            ];
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute($params);
            DB::$connection->commit();
        }
    }
} catch (PDOException $e) {
    echo $e->getMessage();
}

$delta = (microtime(true) - $start) * 1000;
echo "Took $delta ms".PHP_EOL;

DB::disconnect();

 