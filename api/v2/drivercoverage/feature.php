<?php
	/* 		
	*
	* Vulkan hardware capability database server implementation
	*	
	* Copyright (C) by Sascha Willems (www.saschawillems.de)
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

	include './../../../dbconfig.php';
	include './../../../functions.php';

	header('Content-Type: application/json');

	$feature = $_GET["feature"];
	if(!$feature) {
		echo json_encode(["error" => "No feature specified"]);
		die();
	}

	DB::connect();	

	$count = DB::getCount("SELECT count(*) from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME = 'devicefeatures' and COLUMN_NAME = :feature", ["feature" => $feature]);
	if ($count == 0) {
		die(json_encode(["error" => "Unknown feature"]));
	}

	$ostype = null;
	if (isset($_GET["platform"])) {
		$ostype = ostype($_GET["platform"]);
		if ($ostype === null) {
			die(json_encode(["error" => "Unknown platform type"]));
		}
	}
	$params = [];

	$whereClause = "WHERE r.id in (select distinct(reportid) from devicefeatures df where df.$feature)";
	if ($ostype !== null) {
		$whereClause .= " AND r.ostype = :ostype";
	 	$params["ostype"]  = $ostype;
	}
		
	try {
		$sql = 
		"SELECT 
			ifnull(r.displayname, dp.devicename) as device, 
			min(dp.apiversionraw) as api,
			min(dp.driverversion) as driverversion,
			min(dp.driverversionraw) as driverversionraw, 
			min(submissiondate) as submissiondate,
			VendorId(dp.vendorid) as vendor,
			date(min(submissiondate)) as submissiondate,
			r.osname as platform
			FROM reports r
			JOIN deviceproperties dp on r.id = dp.reportid
			$whereClause
			GROUP BY device
			ORDER by platform, device";

		$stmnt = DB::$connection->prepare($sql);
		$stmnt->execute($params);
	
		if ($stmnt->rowCount() > 0) {		
			$rows = array();
			while ($row = $stmnt->fetch(PDO::FETCH_ASSOC)) {
				$rows[] = $row;
			}
			echo _format_json(json_encode($rows), false);			
		} 
		else {
			echo json_encode(["error" => "Request did not yield any result"]);
		}
	} catch (Exception $e) {
		echo json_encode(["error" => "Server error while fetching report list"]);
	}

	DB::disconnect();
?>