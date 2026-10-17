<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2026 by Sascha Willems (www.saschawillems.de)
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

    public static function getReportDate($reportID)
    {
        $stmnt = DB::$connection->prepare("SELECT max(date) from reportupdatehistory where reportid = :reportid");
        $stmnt->execute(["reportid" => $reportID]);
        return $stmnt->fetchColumn();
    }

    // Returns true if the passed api key exists and is active
    public static function validateApiKey()
    {
        // Key can be provided via header or as an url argument
        $apikey = $_SERVER['HTTP_X_API_KEY'] ?? ($_GET['apikey'] ?? '');        
        if (!is_string($apikey) || $apikey === '') {
            return false;
        }
        DB::connect();
        try {
            $stmnt = DB::$connection->prepare("SELECT id FROM apikeys WHERE apikey = :apikey AND active = 1 AND (expires IS NULL OR expires > NOW())");
            $stmnt->execute(["apikey" => $apikey]);
            $id = $stmnt->fetchColumn();
            if ($id === false) {
                return false;
            }
            $upd = DB::$connection->prepare("UPDATE apikeys SET lastused = NOW(), requestcount = requestcount + 1 WHERE id = :id");
            $upd->execute(["id" => $id]);
        } catch (Exception $e) {
	 		DB::disconnect();
	 		http_response_code(500);
	 		die("Database error");
	 	}
	 	DB::disconnect();        
        return true;
    }

    public static function log($page, $statement, $executiontime)
    {
        $sql ="INSERT INTO log (page, statement, execution_time) VALUES (:page, :statement, :executiontime)";
        $stmnt = DB::$connection->prepare($sql);
        $stmnt->execute(['page' => $page, 'statement' => $statement, 'executiontime' => $executiontime]);
    }
}