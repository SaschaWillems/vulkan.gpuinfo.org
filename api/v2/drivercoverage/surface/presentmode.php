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

	include './../../../../dbconfig.php';
	include './../../../../functions.php';

	header('Content-Type: application/json');

	$enum = $_GET["presentmode"];
	if(!$enum) {
		echo json_encode(["error" => "No presentmode specified"]);
		die();
	}

	function enumToValue(string $enum)
	{
		$modes = array(
			0 => "VK_PRESENT_MODE_IMMEDIATE_KHR",
			1 => "VK_PRESENT_MODE_MAILBOX_KHR",
			2 => "VK_PRESENT_MODE_FIFO_KHR",
			3 => "VK_PRESENT_MODE_FIFO_RELAXED_KHR",
			1000111000 => "VK_PRESENT_MODE_SHARED_DEMAND_REFRESH_KHR",
			1000111001 => "VK_PRESENT_MODE_SHARED_CONTINUOUS_REFRESH_KHR",
		);		
		if (in_array($enum, $modes)) {
			$key = array_search($enum, $modes);
			return $key;
		};
		return null;
	}	

	DB::connect();	

	$enumValue = enumToValue($enum);
	if ($enumValue === null) {
		die(json_encode(["error" => "Unknown surface present mode"]));
	}

	$ostype = null;
	if (isset($_GET["platform"])) {
		$ostype = ostype($_GET["platform"]);
		if ($ostype === null) {
			die(json_encode(["error" => "Unknown platform type"]));
		}
	}
	$params["presentmode"] = $enumValue;

	// Surface info is stored starting with report version 1.2, so ignore older reports
	$whereClause = "WHERE r.version >= '1.2' and r.id in (select reportid from devicesurfacemodes dsp where dsp.presentmode = :presentmode)";
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