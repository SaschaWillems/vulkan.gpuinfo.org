<?php
	/** 		
	 *
	 * Vulkan hardware capability database server implementation
	 *	
	 * Copyright (C) 2016-2020 by Sascha Willems (www.saschawillems.de)
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

	include './../../dbconfig.php';
	
	header("Connection: keep-alive");

	DB::connect();	

	$params = [
		"devicename" => $_GET['devicename'],
		"driverversion" => $_GET['driverversion'],
		"osname" => $_GET['osname'],
		"osversion" => $_GET['osversion'],
		"osarchitecture" => $_GET['osarchitecture'],
		'apiversion' => $_GET['apiversion']
	];

	$sql = "SELECT id from reports where
		devicename = :devicename and 
		driverversion = :driverversion and
		osname = :osname and
		osversion = :osversion and
		osarchitecture = :osarchitecture and
		apiversion = :apiversion";
	$requires_platform_details = false;
	if (isset($_GET['androidproductmodel'])) {
		$requires_platform_details = true;
		$params["androidproductmodel"] = $_GET['androidproductmodel'];
	}
	if (isset($_GET['androidproductmanufacturer'])) {
		$requires_platform_details = true;
		$params["androidproductmanufacturer"] = $_GET['androidproductmanufacturer'];
	}
	if ($requires_platform_details) {
		$sql = "SELECT id from reports 
		where devicename = :devicename and 
		driverversion = :driverversion and
		osname = :osname and
		osversion = :osversion and
		osarchitecture = :osarchitecture and
		apiversion = :apiversion		
		and id in (select reportid from deviceplatformdetails where reportid = id and platformdetailid = 3 and value = :androidproductmanufacturer) 
		and id in (select reportid from deviceplatformdetails where reportid = id and platformdetailid = 4 and value = :androidproductmodel) 
		order by id desc";
	}

	try {
		$stmnt = DB::$connection->prepare($sql);
		$stmnt->execute($params);
		$row = $stmnt->fetch(PDO::FETCH_NUM);
		if ($stmnt->rowCount() > 0) {
			$reportid = $row[0];
			header('HTTP/1.1 200 report_present '.$reportid.'');
			echo $reportid;
		} else {
			header('HTTP/1.1 200 report_new');
			echo "-1";
		}	
	} catch (PDOException $e) {
		header('HTTP/1.1 500 error');
		header("Connection: keep-alive");
	}
			
	DB::disconnect();	
?>