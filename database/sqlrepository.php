<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2022 by Sascha Willems (www.saschawillems.de)
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

/** Builder class for the SQL statements used on different pages */
class SqlRepository {

    public static function getMinApiVersion() {
        if (isset($_SESSION['minversion'])) {
            return $_SESSION['minversion'];
        }
        return null;
    }

    private static function getOSType() {
        if (isset($_GET['platform'])) {
            return ostype(GET_sanitized('platform'));
        }
        return null;
    }

    public static function appendCondition(&$sql, $condition) {
        if (strpos($sql, 'where') !== false) {
            $sql .= " and $condition";
        } else {
            $sql .= " where $condition";
        }
    }

    public static function deviceCount() {
        $sql = "SELECT count(DISTINCT displayname) from reports r";
        $ostype = self::getOSType();
        if ($ostype !== null) {
            self::appendCondition($sql, "r.ostype = :ostype");
            $params['ostype'] = $ostype;
        }
        $apiversion = self::getMinApiVersion();
        if ($apiversion) {
            self::appendCondition($sql, "r.apiversion >= :apiversion");
            $params['apiversion'] = $apiversion;
        }
        $stmnt= DB::$connection->prepare($sql);
        $stmnt->execute($params);
        $count = $stmnt->fetch(PDO::FETCH_COLUMN);        
        return $count;
    }

    /** Global extension listing */
    public static function listExtensions() {
        $params     = [];
        $sql ="SELECT e.name, count(distinct displayname) as coverage from extensions e 
            join deviceextensions de on de.extensionid = e.id 
            join reports r on r.id = de.reportid";
        $ostype = self::getOSType();
        if ($ostype !== null) {            
            self::appendCondition($sql, "ostype = :ostype");
            $params['ostype'] = $ostype;
        }
        $apiversion = self::getMinApiVersion();
        if ($apiversion) {
            self::appendCondition($sql, "r.apiversion >= :apiversion");
            $params['apiversion'] = $apiversion;
        }
        $sql .= " group by name";
        $stmnt = DB::$connection->prepare($sql);
        $stmnt->execute($params);
        return $stmnt->fetchAll(PDO::FETCH_ASSOC);
    }

}