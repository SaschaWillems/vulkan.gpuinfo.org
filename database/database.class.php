<?php

/**
 *
 * Vulkan hardware capability database server implementation
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

require "database.config.php";

class DB
{
    public static $connection = null;

    public static function connect()
    {
        $DBNAME = DBNAME;
        $DBHOST = DBHOST;
        try {
            DB::$connection = new PDO("mysql:dbname=$DBNAME;host=$DBHOST", DBUSER, DBPASSWORD);
            DB::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            DB::$connection = null;
            die($e->getMessage());
        }
    }
    public static function disconnect()
    {
        DB::$connection = null;
    }
    public static function getCount($statement, $params = null)
    {
        $stmnt = DB::$connection->prepare($statement);
        $stmnt->execute($params);
        return $stmnt->fetchColumn();
    }

    public static function log($page, $statement, $executiontime)
    {
        $sql ="INSERT INTO log (page, statement, execution_time) VALUES (:page, :statement, :executiontime)";
        $stmnt = DB::$connection->prepare($sql);
        $stmnt->execute(['page' => $page, 'statement' => $statement, 'executiontime' => $executiontime]);
    }
}