<?php
	/* 		
	*
	* Vulkan hardware capability database back-end
	*	
	* Copyright (C) 2011-2018 by Sascha Willems (www.saschawillems.de)
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

	// Return report as json (uploaded from client application)
	
	include './../../../database/database.class.php';
	
	DB::connect();

	if (!isset($_GET['id'])) {
		header('HTTP/ 400 missing_or');
		echo "No report id specified!";
		die();
	}

	$reportid = $_GET['id'];	
	$json_data = null;

	$sql = "SELECT rj.json,r.version FROM reportsjson rj JOIN reports r on r.id = rj.reportid WHERE rj.reportid = :reportid";
	try {
		$stmnt = DB::$connection->prepare($sql);
		$stmnt->execute(array(":reportid" => $reportid));
		if ($stmnt->rowCount() > 0) {			
			header('Content-Type: application/json');
			$row = $stmnt->fetch(PDO::FETCH_ASSOC);
			// if ($row['version'] < 1.4) {
			// 	echo json_encode(array('error', 'Report version < 1.4!'));
			// 	return;
			// } else {
				$json_data = $row['json'];
			// }
		} 
		else {
			header('HTTP/ 404 report_not_present');
			echo "report not present";
		}
	} catch (Exception $e) {
		header('HTTP/ 500 server_error');
		echo "Could not get report from database";
		die();
	}		

	$report_data = json_decode($json_data, true);
	
	$comments = array();
	$comments["info"] = "Vulkan Hardware Report generated via https://vulkan.gpuinfo.org";
	$comments["desc"] = "https://vulkan.gpuinfo.org/displayreport.php?id=".$reportid;
	$report_data = array_merge(array("comments" => $comments), $report_data);

	$report_data = array_merge(array("\$schema" => "https://schema.khronos.org/vulkan/devsim_1_1_0.json#"), $report_data);

	$report_data["VkPhysicalDeviceFeatures"] = $report_data["features"];
	unset($report_data["features"]);

	foreach ($report_data["properties"]["limits"] as &$prop) {
		if (is_string($prop)) {
			if (substr($prop, 0, 2) === "0x") {
				if (strtolower($prop) === "0xffffffffffffffff") {
					$prop = -1;
				} else {
					$prop = hexdec($prop);
				}
				continue;
			}
		}
		if (is_numeric($prop)) {
			$prop = $prop + 0;
		}
	}
	unset($report_data["properties"]["apiVersionText"]);
	unset($report_data["properties"]["deviceTypeText"]);
	unset($report_data["properties"]["driverVersionText"]);
	unset($report_data["properties"]["headerversion"]);
	
	$report_data["VkPhysicalDeviceProperties"] = $report_data["properties"];
	unset($report_data["properties"]);

	foreach ($report_data["memory"]["memoryHeaps"] as &$mheap) {
		$mheap["size"] = hexdec($mheap["size"]);
	}
	unset($report_data["memory"]["memoryHeapCount"]);
	unset($report_data["memory"]["memoryTypeCount"]);

	$report_data["VkPhysicalDeviceMemoryProperties"] = $report_data["memory"];
	unset($report_data["memory"]);

	$report_data["VkPhysicalDeviceSubgroupProperties"] = $report_data["VkPhysicalDeviceProperties"]["subgroupProperties"];
	unset($report_data["VkPhysicalDeviceProperties"]["subgroupProperties"]);	

	if (isset($report_data["VkPhysicalDeviceSubgroupProperties"]["quadOperationsInAllStages"])) {
		$report_data["VkPhysicalDeviceSubgroupProperties"]["quadOperationsInAllStages"] = ($report_data["VkPhysicalDeviceSubgroupProperties"]["quadOperationsInAllStages"] == "true") ? 1 : 0;
	}

	$report_data["ArrayOfDeviceSystemVkExtensionProperties"] = $report_data["extensions"];
	unset($report_data["extensions"]);

	foreach ($report_data["layers"] as &$layer) {
		if (isset($layer["extensions"])) {
			unset($layer["extensions"]);
		}
	}

	$report_data["ArrayOfInstanceVkLayerProperties"] = $report_data["layers"];
	unset($report_data["layers"]);

	foreach ($report_data["queues"] as &$q) {
		unset($q["supportsPresent"]);
	}		

	$report_data["ArrayOfVkQueueFamilyProperties"] = $report_data["queues"];
	unset($report_data["queues"]);	

	foreach ($report_data["formats"] as &$fmt) {
		$fmt["formatID"] = $fmt[0];
		$fmt["linearTilingFeatures"] = $fmt[1]["linearTilingFeatures"];
		$fmt["optimalTilingFeatures"] = $fmt[1]["optimalTilingFeatures"];
		$fmt["bufferFeatures"] = $fmt[1]["bufferFeatures"];
		unset($fmt[0]);
		unset($fmt[1]);
	}

	$report_data["ArrayOfVkFormatProperties"] = $report_data["formats"];
	unset($report_data["formats"]);	

	$filename = $report_data["VkPhysicalDeviceProperties"]["deviceName"];
	$filename = preg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $filename);
	$filename = preg_replace("([\.]{2,})", '', $filename);	
	$filename .= ".json";

	header("Content-type: application/json");
	header("Content-Disposition: attachment; filename=".strtolower($filename));

	echo json_encode($report_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

	DB::disconnect();
?>