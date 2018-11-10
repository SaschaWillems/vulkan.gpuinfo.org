<?php
	/* 		
	*
	* Vulkan hardware capability database server implementation
	*	
	* Copyright (C) 2016-2018 by Sascha Willems (www.saschawillems.de)
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

	include './../dbconfig.php';
	
	header("Connection: keep-alive");

	DB::connect();	

	$params = [
		"devicename" => $_GET['devicename'],
		"driverversion" => $_GET['driverversion'],
		"osname" => $_GET['osname'],
		"osversion" => $_GET['osversion'],
		"osarchitecture" => $_GET['osarchitecture'],
	];

	$sql = "SELECT id from reports where
		devicename = :devicename and 
		driverversion = :driverversion and
		osname = :osname and
		osversion = :osversion and
		osarchitecture = :osarchitecture";	
	if (isset($_GET['apiversion'])) {
		$sql .= " and apiversion = :apiversion"; 
		$params["apiversion"] = $_GET['apiversion'];
	}
	if (isset($_GET['reportversion'])) {
		$sql .= " and version = :reportversion"; 
		$params["reportversion"] = $_GET['reportversion'];
	}

	try {
		$stmnt = DB::$connection->prepare($sql);
		$stmnt->execute($params);
		$row = $stmnt->fetch(PDO::FETCH_NUM);
		$reportid = $row[0];
		if ($stmnt->rowCount() > 0) {
			header('HTTP/ 200 report_present '.$reportid.'');
			echo $reportid;
		} else {
			header('HTTP/ 200 report_new');
			echo "-1";
		}	
	} catch (PDOException $e) {
		header('HTTP/ 500 error');
		header("Connection: keep-alive");
	}
			
	DB::disconnect();	
?>