<?php
	/* 		
	*
	* Vulkan hardware capability database back-end
	*	
	* Copyright (C) 2011-2020 by Sascha Willems (www.saschawillems.de)
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

	function exitScript($message) {
		header('HTTP/ 400 missing_or');
		exit($message);
	}

	if (!isset($_GET['id'])) {
		exitScript("No report id specified!");
	}

	if (!isset($_GET['extension'])) {
		exitScript("No extension schema selected");
	}

	$reportid = $_GET['id'];
	$extension = $_GET['extension'];

	// Check if requested extension has a JSON schema
	if (in_array($extension, ['VK_KHR_portability_subset']) == false) {
		exitScript("Unknown extension selected");
	}

	function checkExtensionsPresent($report, $extension) {
		foreach ($report["extensions"] as $ext) {
			if (strcasecmp($extension, $ext['extensionName']) == 0) {
				return true;
			}
		}		
		return false;
	}

	function getDeviceFeature2($report, $extension, $required_feature) {
		$features2 = $report['extended']['devicefeatures2'];
		assert($features2);
		foreach($features2 as $feature) {
			if ((strcasecmp($feature['extension'], $extension) == 0) && (strcasecmp($feature['name'], $required_feature) == 0)) {
				return $feature['supported'] == true ? 1 : 0;
			}
		}
		return null;
	}

	function getDeviceProperty2($report, $extension, $required_property) {
		$properties2 = $report['extended']['deviceproperties2'];
		assert($properties2);
		foreach($properties2 as $prop) {
			if ((strcasecmp($prop['extension'], $extension) == 0) && (strcasecmp($prop['name'], $required_property) == 0)) {
				return intval($prop['value']);
			}
		}
		return null;
	}

	function VK_KHR_portability_subset($report, $reportid) {
		$extension = 'VK_KHR_portability_subset';
		if (!checkExtensionsPresent($report, $extension)) {
			exitScript("Report does not support $extension");
		}

		$filename = $report['VkPhysicalDeviceProperties']['deviceName']."_VK_KHR_portability";
		$filename = preg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $filename);
		$filename = preg_replace("([\.]{2,})", '', $filename);	
		$filename .= ".json";


		header('Content-Type: application/json');
		header("Content-Disposition: attachment; filename=".strtolower($filename));
	 
		$report_output['$schema'] = 'https://schema.khronos.org/vulkan/devsim_VK_KHR_portability_subset-provisional-1.json#';

		$required_properties = [
			'minVertexInputBindingStrideAlignment'
		];

		$required_features = [
			'constantAlphaColorBlendFactors',
			'events',
			'imageViewFormatReinterpretation',
			'imageViewFormatSwizzle',
			'imageView2DOn3DImage',
			'multisampleArrayImage',
			'mutableComparisonSamplers',
			'pointPolygons',
			'samplerMipLodBias',
			'separateStencilMaskRef',
			'shaderSampleRateInterpolationFunctions',
			'tessellationIsolines',
			'tessellationPointMode',
			'triangleFans',
			'vertexAttributeAccessBeyondStride'	
		];

		foreach($required_features as $required_feature) {
			$report_output['VkPhysicalDevicePortabilitySubsetFeaturesKHR'][$required_feature] = getDeviceFeature2($report, $extension, $required_feature);
		}
		
		foreach($required_properties as $required_property) {
			$report_output['VkPhysicalDevicePortabilitySubsetPropertiesKHR'][$required_property] = getDeviceProperty2($report, $extension, $required_property);
		}

		$report_output['properties'] = null;
		$report_output['features'] = null;
		$report_output['comments']['info'] = 'JSON generated via https://vulkan.gpuinfo.org';
		$report_output['comments']['device']['name'] = $report['properties']['deviceName'];
		$report_output['comments']['device']['type'] = $report['properties']['deviceTypeText'];
		$report_output['comments']['device']['driver'] = $report['properties']['driverVersionText'];
		$report_output['comments']['report']['id'] = intval($reportid);
		$report_output['comments']['report']['url'] = "https://vulkan.gpuinfo.org/displayreport.php?id=".$reportid;
		$report_output['comments']['environment']['name'] = $report['environment']['name'];
		$report_output['comments']['environment']['version'] = $report['environment']['version'];
		$report_output['comments']['environment']['architecture'] = $report['environment']['architecture'];
		$report_output['comments']['environment']['comment'] = $report['environment']['comment'];

		echo json_encode($report_output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);		
	}

	// Fetch report
	DB::connect();
	$sql = "SELECT rj.json,r.version FROM reportsjson rj JOIN reports r on r.id = rj.reportid WHERE rj.reportid = :reportid";
	try {
		$stmnt = DB::$connection->prepare($sql);
		$stmnt->execute(array(":reportid" => $reportid));
		if ($stmnt->rowCount() > 0) {			
			$row = $stmnt->fetch(PDO::FETCH_ASSOC);
			$report_json = json_decode($row['json'], true);
		} 
		else {
			exitScript("Report not present");
		}
	} catch (Exception $e) {
		exitScript("Could not get report from database");
	}		
	DB::disconnect();


	if (strcasecmp($extension, 'VK_KHR_portability_subset') == 0) {
		VK_KHR_portability_subset($report_json, $reportid);
	}
?>