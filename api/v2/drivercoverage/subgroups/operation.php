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

	$enum = $_GET["operation"];
	if(!$enum) {
		echo json_encode(["error" => "No subgroup operation specified"]);
		die();
	}

	function enumToValue(string $enum)
	{
		$mapping = array(
			0x00000001 => 'VK_SUBGROUP_FEATURE_BASIC_BIT',
			0x00000002 => 'VK_SUBGROUP_FEATURE_VOTE_BIT',
			0x00000004 => 'VK_SUBGROUP_FEATURE_ARITHMETIC_BIT',
			0x00000008 => 'VK_SUBGROUP_FEATURE_BALLOT_BIT',
			0x00000010 => 'VK_SUBGROUP_FEATURE_SHUFFLE_BIT',
			0x00000020 => 'VK_SUBGROUP_FEATURE_SHUFFLE_RELATIVE_BIT',
			0x00000040 => 'VK_SUBGROUP_FEATURE_CLUSTERED_BIT',
			0x00000080 => 'VK_SUBGROUP_FEATURE_QUAD_BIT',
			0x00000100 => 'VK_SUBGROUP_FEATURE_PARTITIONED_BIT_NV',
		);
		if (in_array($enum, $mapping)) {
			$key = array_search($enum, $mapping);
			return $key;
		};
		return null;
	}	

	DB::connect();	

	$enumValue = enumToValue($enum);
	if ($enumValue === null) {
		die(json_encode(["error" => "Unknown subgroup operation"]));
	}

	$ostype = null;
	if (isset($_GET["platform"])) {
		$ostype = ostype($_GET["platform"]);
		if ($ostype === null) {
			die(json_encode(["error" => "Unknown platform type"]));
		}
	}
	$params["operation"] = $enumValue;

	// Surface info is stored starting with report version 1.2, so ignore older reports
	$whereClause = "WHERE (`subgroupProperties.supportedOperations` & :operation) > 0";
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