<?php
	/* 		
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

	// Return list of all available reports as json
	
	include './../../database/database.class.php';
	include './../../includes/functions.php';

	$validKey = DB::validateApiKey();
	if (!$validKey) {
		http_response_code(401);
		header('WWW-Authenticate: ApiKey');
		exit("Missing, invalid or inactive api key. Please contact me if you need api access.");
	}

	$startdate = $_GET['startdate'] ?? null;
	if ($startdate !== null) {
		if (!is_string($startdate) || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/D', $startdate, $dateParts) || !checkdate((int)$dateParts[2], (int)$dateParts[3], (int)$dateParts[1])) {
			http_response_code(400);
			exit("Invalid startdate. Please provide a valid date in YYYY-MM-DD format.");
		}
	}
	
	DB::connect();	
			
	try {
		$sql ="SELECT 
			concat('0x', hex(cast(dp.vendorid as unsigned))) as vendorid,
			concat('0x', hex(cast(dp.deviceid as unsigned))) as deviceid,
			VendorID(dp.vendorid) as vendorname,
			dp.devicename,
			dp.devicetype,
			dp.apiversion,
			dp.driverversionraw,
			dp.driverversion,
			r.osname,
			r.osversion,
			r.osarchitecture,
			r.headerversion,
			r.version as reportversion,
			concat('https://vulkan.gpuinfo.org/api/v2/getreport.php?id=', dp.reportid) as url
			from deviceproperties dp
			join reports r on r.id = dp.reportid
			where r.version >= '1.4'";

		$params = [];
		if ($startdate !== null) {
			$sql .= " and r.submissiondate >= :startdate";
			$params['startdate'] = $startdate;
		}
		$sql .= " order by vendorid, devicename, apiversion desc, driverversionraw";

		$stmnt = DB::$connection->prepare($sql);
		$stmnt->execute($params);
	
		if ($stmnt->rowCount() > 0) {		
			$rows = array();

			while ($row = $stmnt->fetch(PDO::FETCH_ASSOC)) {
				$rows[] = $row;
			}

			header('Content-type:application/json;charset=utf-8');
			echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
			logToFile("[API] JSON report listing api called (geterportjson.php)");
		} else {
			header('HTTP/ 404 empty_response');
			if ($startdate !== null) {
				echo json_encode(array("info", "No reports found for the specified startdate"));
			} else {
				echo json_encode(array("info", "No reports with report version > 1.4"));
			}
		}
	} catch (Exception $e) {
		header('HTTP/ 500 server_error');
		echo "Server error while fetching report list";	
	}

	DB::disconnect();
?>