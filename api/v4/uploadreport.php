<?php
	/** 		
	 *
	 * Vulkan hardware capability database back-end
	 *	
	 * Copyright (C) 2016-2024 by Sascha Willems (www.saschawillems.de)
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

	// Note: Currently used for manual uploads

	include "./../../includes/functions.php";
	include './../../database/database.class.php';	
	
	// File validty check

	$file = $_FILES['data']['name'];

	// Reports are pretty small, so limit file size for upload to 512 KByte (should be more than enough)
	if ($_FILES['data']['size'] > 512 * 1024)  {
		echo "File exceeds size limitation of 512 KByte!";
		exit();  
	}

	if ($_FILES['data']['size'] == 0) {
		echo "Provided report file is empty, check client write permissions!";
		exit();  
	}	
	
	// Check file extension 
	$ext = pathinfo($file, PATHINFO_EXTENSION); 
	if ($ext != 'json') {
		echo "Report '$file' is not of file type json!";
		exit();  
	}

	// Make sure it's really a text file
	$finfo = new finfo(FILEINFO_MIME);
	$mime_type = $finfo->file($_FILES['data']['tmp_name']);
	$mime_check = ((stripos($mime_type, 'text') !== false) || (stripos($mime_type, 'json') !== false));
	if ((!$mime_type) || (!$mime_check)) {
		echo "Uploaded file looks like a binary file!";
		exit();
	}
	
	move_uploaded_file($_FILES['data']['tmp_name'], './'.$_FILES['data']['name']) or die(''); 

	// Use a closure to exit the script that ensures an uploaded file is always deleted
	$exitScript = function($message = null) use ($file) {
		if (file_exists($file)) {
			unlink($file);
		}
		exit($message);
	};

	function convertValue($val) {
		if (is_string($val)) {
			if (strpos($val, '0x') === 0) {
				return hexdec($val);
			}
		} else {
			return $val;
		}

	}

	function reportError($error) {
		http_response_code(500);
		die($error);
	}

	function importCore11Data($json, $reportid) {
		if (!array_key_exists('core11', $json)) {
			return;
		}
		// Features
		if (array_key_exists('features', $json['core11'])) {
			$jsonnode = $json['core11']['features'];
			$columns = ['reportid'];
			$params = [':reportid'];
			$values = [':reportid' => $reportid];
			foreach($jsonnode as $key => $value) {
				$columns[] = $key;
				$params[] = ":$key";
				$values[":$key"] = $value;
			}
			$sql = sprintf("INSERT INTO devicefeatures11 (%s) VALUES (%s)", implode(",", $columns), implode(",", $params));
			try {
				$stmnt = DB::$connection->prepare($sql);
				$stmnt->execute($values);	
			} catch (Exception $e) {
				reportError('Error while trying to upload report (error at core 1.1 device features)');
			}
		}
		// Properties
		if (array_key_exists('features', $json['core11'])) {
			$jsonnode = $json['core11']['properties'];
			$columns = ['reportid'];
			$params = [':reportid'];
			$values = [':reportid' => $reportid];
			foreach($jsonnode as $key => $value) {
				$columns[] = $key;
				$params[] = ":$key";
				if (is_array($value)) {
					// UUIDs etc. need to be serialized
					$values[":$key"] = serialize($value);
				} else {
					$values[":$key"] = $value;
				}
			}
			$sql = sprintf("INSERT INTO deviceproperties11 (%s) VALUES (%s)", implode(",", $columns), implode(",", $params));
			try {
				$stmnt = DB::$connection->prepare($sql);
				$stmnt->execute($values);	
			} catch (Exception $e) {
				reportError('Error while trying to upload report (error at core 1.1 device properties)');
			}
		}
	}

	function importCore12Data($json, $reportid) {
		if (!array_key_exists('core12', $json)) {
			return;
		}
		// Features
		if (array_key_exists('features', $json['core12'])) {
			$jsonnode = $json['core12']['features'];
			$columns = ['reportid'];
			$params = [':reportid'];
			$values = [':reportid' => $reportid];
			foreach($jsonnode as $key => $value) {
				$columns[] = $key;
				$params[] = ":$key";
				$values[":$key"] = $value;
			}
			$sql = sprintf("INSERT INTO devicefeatures12 (%s) VALUES (%s)", implode(",", $columns), implode(",", $params));
			try {
				$stmnt = DB::$connection->prepare($sql);
				$stmnt->execute($values);	
			} catch (Exception $e) {
				reportError('Error while trying to upload report (error at core 1.2 device features)');
			}
		}
		// Properties
		if (array_key_exists('features', $json['core12'])) {
			$jsonnode = $json['core12']['properties'];
			$columns = ['reportid'];
			$params = [':reportid'];
			$values = [':reportid' => $reportid];
			foreach($jsonnode as $key => $value) {
				$columns[] = $key;
				$params[] = ":$key";
				if (is_array($value)) {
					// UUIDs etc. need to be serialized
					$values[":$key"] = serialize($value);
				} else {
					$values[":$key"] = $value;
				}
			}
			$sql = sprintf("INSERT INTO deviceproperties12 (%s) VALUES (%s)", implode(",", $columns), implode(",", $params));
			try {
				$stmnt = DB::$connection->prepare($sql);
				$stmnt->execute($values);	
			} catch (Exception $e) {
				reportError('Error while trying to upload report (error at core 1.2 device properties)');
			}
		}
	}

	function importCore13Data($json, $reportid) {
		if (!array_key_exists('core13', $json)) {
			return;
		}
		// Features
		if (array_key_exists('features', $json['core13'])) {
			$jsonnode = $json['core13']['features'];
			$columns = ['reportid'];
			$params = [':reportid'];
			$values = [':reportid' => $reportid];
			foreach($jsonnode as $key => $value) {
				$columns[] = $key;
				$params[] = ":$key";
				$values[":$key"] = $value;
			}
			$sql = sprintf("INSERT INTO devicefeatures13 (%s) VALUES (%s)", implode(",", $columns), implode(",", $params));
			try {
				$stmnt = DB::$connection->prepare($sql);
				$stmnt->execute($values);	
			} catch (Exception $e) {
				reportError('Error while trying to upload report (error at core 1.3 device features)');
			}
		}
		// Properties
		if (array_key_exists('features', $json['core13'])) {
			$jsonnode = $json['core13']['properties'];
			$columns = ['reportid'];
			$params = [':reportid'];
			$values = [':reportid' => $reportid];
			foreach($jsonnode as $key => $value) {
				// MySQL column names can be 64 chars at max, so some properties starting with integerDotProduct have to be shortened
				if (strpos($key, 'integerDotProduct') == 0) {				
					$columns[] = str_replace('integerDotProduct', 'idp', $key);
				} else {
					$columns[] = $key;
				}
				$params[] = ":$key";
				if (is_array($value)) {
					// UUIDs etc. need to be serialized
					$values[":$key"] = serialize($value);
				} else {
					$values[":$key"] = $value;
				}
			}
			$sql = sprintf("INSERT INTO deviceproperties13 (%s) VALUES (%s)", implode(",", $columns), implode(",", $params));
			try {
				$stmnt = DB::$connection->prepare($sql);
				$stmnt->execute($values);	
			} catch (Exception $e) {
				reportError('Error while trying to upload report (error at core 1.3 device properties)');
			}
		}
	}

	function importCore14Data($json, $reportid) {
		if (!array_key_exists('core14', $json)) {
			return;
		}
		// Features
		if (array_key_exists('features', $json['core14'])) {
			$jsonnode = $json['core14']['features'];
			$columns = ['reportid'];
			$params = [':reportid'];
			$values = [':reportid' => $reportid];
			foreach($jsonnode as $key => $value) {
				$columns[] = $key;
				$params[] = ":$key";
				$values[":$key"] = $value;
			}
			$sql = sprintf("INSERT INTO devicefeatures14 (%s) VALUES (%s)", implode(",", $columns), implode(",", $params));
			try {
				$stmnt = DB::$connection->prepare($sql);
				$stmnt->execute($values);	
			} catch (Exception $e) {
				reportError('Error while trying to upload report (error at core 1.4 device features)');
			}
		}
		// Properties
		if (array_key_exists('features', $json['core14'])) {
			$jsonnode = $json['core14']['properties'];
			$columns = ['reportid'];
			$params = [':reportid'];
			$values = [':reportid' => $reportid];
			foreach($jsonnode as $key => $value) {
				$columns[] = $key;
				$params[] = ":$key";
				if (is_array($value)) {
					// UUIDs etc. need to be serialized
					$values[":$key"] = serialize($value);
				} else {
					$values[":$key"] = $value;
				}
			}
			$sql = sprintf("INSERT INTO deviceproperties14 (%s) VALUES (%s)", implode(",", $columns), implode(",", $params));
			try {
				$stmnt = DB::$connection->prepare($sql);
				$stmnt->execute($values);	
			} catch (Exception $e) {
				reportError('Error while trying to upload report (error at core 1.4 device properties)');
			}
		}
	}

	function importProfiles($json, $reportid) {
		if (!array_key_exists('profiles', $json)) {
			return;
		}
		$jsonnode = $json['profiles'];
		foreach ($jsonnode as $profile) {
			// Add to global mapping table (if not already present)
			$sql = "INSERT IGNORE INTO profiles (name) VALUES (:name)";
			$stmnt = DB::$connection->prepare($sql);
			$stmnt->execute(array(":name" => $profile['profileName']));	
			// Get profile mapping id
			$sql = "SELECT id FROM profiles WHERE name = :name";
			$stmnt = DB::$connection->prepare($sql);
			$stmnt->execute(array(":name" => $profile['profileName']));
			$profileid = $stmnt->fetchColumn();
			if ($profileid == null) {
				throw new Exception("Could not get lookup entry for profile ".$profile['profileName']);
			}
			// Insert
			$sql = "INSERT INTO deviceprofiles (reportid, profileid, specversion, supported) VALUES (:reportid, :profileid, :specversion, :supported)";
			try {
				$stmnt = DB::$connection->prepare($sql);
				$stmnt->execute(array(":reportid" => $reportid, ":profileid" => $profileid, ":specversion" => $profile['specVersion'], ":supported" => $profile['supported']));
			} catch (Exception $e) {
				reportError('Error while trying to upload report (error at device profiles)');
			}															
		}
	}

	DB::connect();
	
	$jsonFile = file_get_contents($file);
	$json = json_decode($jsonFile, true, 512, JSON_BIGINT_AS_STRING);
	if (json_last_error() !== JSON_ERROR_NONE) {
		exit('Could not decode JSON Input file');
	}

	// Check report version
	$reportversion = floatval($json['environment']['reportversion']);
	if ($reportversion < 3.0)
	{
		echo "This version of the Vulkan Hardware Capability is no longer supported!\nPlease download a recent version from https://www.gpuinfo.org";
		DB::disconnect();
		exit();	  
	}		
	
	// Check if device is blacklisted
	try {
		$sql = "SELECT * from blacklist where devicename = :devicename";
		$stmnt = DB::$connection->prepare($sql);
		$stmnt->execute(array(":devicename" => $json['properties']['deviceName']));
		if ($stmnt->rowCount() > 0) { 
			echo "Device ".$json['properties']['deviceName']." has been black-listed and can't be uploaded to the database!";
			DB::disconnect();
			exit();	  	
		}
		// Special cases
		if ((strcasecmp($json['properties']['displayName'], 'Google Pixel 5') == 0) && ($json['properties']['deviceType'] == 2)) {
			echo "This device and type combination has been black-listed and can't be uploaded to the database!";
			mailError("Pixel 5 with discrete upload denied", $jsonFile);
			DB::disconnect();
			exit();	  	
		}
	} catch (Exception $e) {
		reportError('Error while trying to upload report (error at black list check)');
	}		
		
	// Check if report is already present
	$sql = "SELECT id from reports where
		devicename = :devicename and 
		driverversion = :driverversion and
		apiversion = :apiversion and
		osname = :osname and
		osversion = :osversion and
		osarchitecture = :osarchitecture";
	$params = array(
		":devicename" => $json['properties']['deviceName'],
		":driverversion" => $json['properties']['driverVersionText'],
		":apiversion" => $json['properties']['apiVersionText'],
		":osname" => $json['environment']['name'],
		":osversion" => $json['environment']['version'],
		":osarchitecture" => $json['environment']['architecture'],
	);

	try {
		$stmnt = DB::$connection->prepare($sql);		
		$stmnt->execute($params);	
	} catch (Exception $e) { 
		reportError('Error while trying to upload report (error at device present check)');
	}		
	
	if ($stmnt->rowCount() > 0) {
		$reportid = $stmnt->fetchColumn();
		$sql = "UPDATE reports SET counter = counter+1 WHERE id = :reportid";
		$stmnt = DB::$connection->prepare($sql);		
		$stmnt->execute(array(":reportid" => $reportid));			
		echo "Report already present!";
		DB::disconnect();
		$exitScript();
	}	

	DB::$connection->beginTransaction();
	
	// Report meta data	
	{
		$sql = 
			"INSERT INTO reports
				(submitter, devicename, devicetype, displayname, driverversion, apiversion, osname, osversion, osarchitecture, version, description, counter)
			VALUES
				(:submitter, :devicename, :devicetype, :displayname, :driverversion, :apiversion, :osname, :osversion, :osarchitecture, :version, :description, :counter)";

		$values = array(
			":submitter" => $json['environment']['submitter'],
			":devicename" => $json['properties']['deviceName'],
			":devicetype" => $json['properties']['deviceType'],
			":displayname" => $json['properties']['displayName'],
			":driverversion" => $json['properties']['driverVersionText'],
			":apiversion" => $json['properties']['apiVersionText'],
			":osname" => $json['environment']['name'],
			":osversion" => $json['environment']['version'],
			":osarchitecture" => $json['environment']['architecture'],
			":version" => $json['environment']['reportversion'],
			":description" => $json['environment']['comment'],
			":counter" => 0
		);

		try {
			$stmnt = DB::$connection->prepare($sql);
			$stmnt->execute($values);			
		} catch (Exception $e) {
			reportError('Error while trying to upload report (error at report meta data)');
		}				
	}

	// Get id of inserted report (auto increment)
	$reportid = DB::$connection->lastInsertId();
	
	// Store json for api calls (or later reference)
	{
		$sql = "INSERT INTO reportsjson (reportid, json) VALUES (:reportid, :json)";
		$stmnt = DB::$connection->prepare($sql);
		$stmnt->execute(array(":reportid" => $reportid, ":json" => $jsonFile));			
	}

	// Properties
	{
		$jsonnode = $json["properties"]; 
		if (!$jsonnode) {
			reportError("Missing device property node!");
		}

		$sql = 
			"INSERT INTO deviceproperties
			(
				reportid,
				vendorid,
				apiversion,
				apiversionraw,
				deviceid,
				devicename,
				devicetype,
				driverversion,
				driverversionraw,
				residencyAlignedMipSize,
				residencyNonResidentStrict,
				residencyStandard2DBlockShape,
				residencyStandard2DMultisampleBlockShape,
				residencyStandard3DBlockShape,
				headerversion,
				pipelineCacheUUID,
				`subgroupProperties.subgroupSize`,
				`subgroupProperties.supportedStages`,
				`subgroupProperties.supportedOperations`,
				`subgroupProperties.quadOperationsInAllStages`				
			)
			VALUES
			(
				:reportid,
				:vendorid,
				:apiversion,
				:apiversionraw,
				:deviceid,
				:devicename,
				:devicetype,
				:driverversion,
				:driverversionraw,
				:residencyAlignedMipSize,
				:residencyNonResidentStrict,
				:residencyStandard2DBlockShape,
				:residencyStandard2DMultisampleBlockShape,
				:residencyStandard3DBlockShape,
				:headerversion,
				:pipelineCacheUUID,
				:subgroupProperties_subgroupSize,
				:subgroupProperties_supportedStages,
				:subgroupProperties_supportedOperations,
				:subgroupProperties_quadOperationsInAllStages
			)";

		$values = array(
			":reportid" => $reportid,
			":vendorid" => $jsonnode['vendorID'],
			":apiversion" => $jsonnode['apiVersionText'],
			":apiversionraw" => $jsonnode['apiVersion'],
			":deviceid" => $jsonnode['deviceID'],
			":devicename" => $jsonnode['deviceName'],
			":devicetype" => $jsonnode['deviceTypeText'],
			":driverversion" => $jsonnode['driverVersionText'],
			":driverversionraw" => $jsonnode['driverVersion'],
			":headerversion" => $jsonnode['headerversion'],
			":pipelineCacheUUID" => serialize($jsonnode['pipelineCacheUUID']),
			":residencyAlignedMipSize" => $jsonnode['sparseProperties']['residencyAlignedMipSize'],
			":residencyNonResidentStrict" => $jsonnode['sparseProperties']['residencyNonResidentStrict'],
			":residencyStandard2DBlockShape" => $jsonnode['sparseProperties']['residencyStandard2DBlockShape'],
			":residencyStandard2DMultisampleBlockShape" => $jsonnode['sparseProperties']['residencyStandard2DMultisampleBlockShape'],
			":residencyStandard3DBlockShape" => $jsonnode['sparseProperties']['residencyStandard3DBlockShape']
		);

		if (array_key_exists('subgroupProperties', $jsonnode)) {
			$values[":subgroupProperties_subgroupSize"] = $jsonnode['subgroupProperties']["subgroupSize"];
			$values[":subgroupProperties_supportedStages"] = $jsonnode['subgroupProperties']["supportedStages"];
			$values[":subgroupProperties_supportedOperations"] = $jsonnode['subgroupProperties']["supportedOperations"];
			$values[":subgroupProperties_quadOperationsInAllStages"] = $jsonnode['subgroupProperties']["quadOperationsInAllStages"];
		} else {
			$values[":subgroupProperties_subgroupSize"] = null;
			$values[":subgroupProperties_supportedStages"] = null;
			$values[":subgroupProperties_supportedOperations"] = null;
			$values[":subgroupProperties_quadOperationsInAllStages"] = null;
		}

		try {
			$stmnt = DB::$connection->prepare($sql);
			$stmnt->execute($values);	
		} catch (Exception $e) {
			reportError('Error while trying to upload report (error at device properties)');
		}				
	}

	// Limits
	{
		$jsonnode = $json['properties']['limits']; 
		$keys = array();
		$values = array();
		$params = array();
		$keys[] = 'reportid'; $params[] = ':reportid'; $values[':reportid'] = $reportid;
		foreach ($jsonnode as $key => $value) {
			if (is_array($value)) {
				$i = 0;
				foreach($value as $arrval) {
					$keys[] = "`".$key."[".$i."]`";
					$params[] = ":".$key.$i;
					$values[":".$key.$i] = $arrval;
					$i++;
				}
			} else {
				$keys[] = $key;
				$params[] = ":".$key;
				$values[":".$key] = convertValue($value);
			}
		}			
		$sql = "INSERT INTO devicelimits (".implode(",", $keys).") VALUES (".implode(",", $params).")";
		try {
			$stmnt = DB::$connection->prepare($sql);
			$stmnt->execute($values);					 
		} catch (Exception $e) {
			reportError('Error while trying to upload report (error at device limits)');
		}							
	}	
	
	// Features
	{
		$jsonnode = $json['features']; 
		$keys = array();
		$values = array();
		$params = array();
		$keys[] = 'reportid'; $params[] = ':reportid'; $values[':reportid'] = $reportid;
		foreach ($jsonnode as $key => $value) {
			$keys[] = $key;
			$params[] = ":".$key;
			$values[":".$key] = $value;
		}	
		$sql = "INSERT INTO devicefeatures (".implode(",", $keys).") VALUES (".implode(",", $params).")";
		try {
			$stmnt = DB::$connection->prepare($sql);
			$stmnt->execute($values);					 
		} catch (Exception $e) {
			reportError('Error while trying to upload report (error at device features)');
		}									
	}
	
	// Image and buffer formats
	{
		$jsonnode = $json['formats']; 
		if (!$jsonnode) {
			reportError('Report has no image or buffer formats!');
		}
		foreach ($jsonnode as $format) {
			$sql = "INSERT INTO deviceformats 
						(reportid, formatid, lineartilingfeatures, optimaltilingfeatures, bufferfeatures, supported) 
					VALUES
						(:reportid, :formatid, :lineartilingfeatures, :optimaltilingfeatures, :bufferfeatures, :supported)";
			$values = array(
				':reportid' => $reportid,
				':formatid' => $format[0],
				':lineartilingfeatures' => $format[1]['linearTilingFeatures'],
				':optimaltilingfeatures' => $format[1]['optimalTilingFeatures'],
				':bufferfeatures' => $format[1]['bufferFeatures'],
				':supported' => $format[1]['supported']
			);
			try {
				$stmnt = DB::$connection->prepare($sql);
				$stmnt->execute($values);	
			} catch (Exception $e) {
				reportError('Error while trying to upload report (error at device formats)');
			}												
		}	
	}
	
	// Extensions
	{
		$jsonnode = $json['extensions']; 
		foreach ($jsonnode as $ext) {
			// Add to global mapping table (if not already present)
			$sql = "INSERT IGNORE INTO extensions (name) VALUES (:name)";
			$stmnt = DB::$connection->prepare($sql);
			$stmnt->execute(array(":name" => $ext['extensionName']));	
			// Device
			// Get extension id
			$sql = "SELECT id FROM extensions WHERE name = :name";
			$stmnt = DB::$connection->prepare($sql);
			$stmnt->execute(array(":name" => $ext['extensionName']));
			$extensionid = $stmnt->fetchColumn();
			// Insert
			$sql = "INSERT INTO deviceextensions (reportid, extensionid, specversion) VALUES (:reportid, :extensionid, :specversion)";
			try {
				$stmnt = DB::$connection->prepare($sql);
				$stmnt->execute(array(":reportid" => $reportid, ":extensionid" => $extensionid, ":specversion" => $ext['specVersion']));
			} catch (Exception $e) {
				mailError("Error at device extensions: ".$e->getMessage(), $jsonFile);
				reportError('Error while trying to upload report (error at device extensions)');
			}															
		}	
	}

	// Queues
	{
		$jsonnode = $json['queues']; 
		$index = 0;
		foreach ($jsonnode as $queue) {	
			$sql = 
				"INSERT INTO devicequeues 
				(
					reportid, 
					id, 
					count, 
					flags, 
					supportsPresent,
					timestampValidBits, 
					`minImageTransferGranularity.width`, 
					`minImageTransferGranularity.height`, 
					`minImageTransferGranularity.depth`
				) 
				VALUES
				(
					:reportid, 
					:id, 
					:count, 
					:flags, 
					:supportsPresent,
					:timestampValidBits, 
					:minImageTransferGranularity_width, 
					:minImageTransferGranularity_height, 
					:minImageTransferGranularity_depth
				)";
			$values = array(
				':reportid' => $reportid,
				':id' => $index,
				':count' => $queue['queueCount'],
				':flags' => $queue['queueFlags'],
				':supportsPresent' => $queue['supportsPresent'],
				':timestampValidBits' => $queue['timestampValidBits'],
				':minImageTransferGranularity_width' => $queue['minImageTransferGranularity']['width'],
				':minImageTransferGranularity_height' => $queue['minImageTransferGranularity']['height'],
				':minImageTransferGranularity_depth' => $queue['minImageTransferGranularity']['depth']
			);
			try {
				$stmnt = DB::$connection->prepare($sql);
				$stmnt->execute($values);	
			} catch (Exception $e) {
				reportError('Error while trying to upload report (error at device queues)');
			}															

			$index++;
		}	
	}

	// Device layers (and extensions)
	{		
		$jsonnode = $json['layers']; 
		$index = 0;
		foreach ($jsonnode as $layer) {
			try {
				// Add to global mapping table (if not already present)
				$sql = "INSERT IGNORE INTO layers (name) VALUES (:name)";
				$stmnt = DB::$connection->prepare($sql);
				$stmnt->execute(array(":name" => $layer['layerName']));				
				// Device
				// Get layer id
				$sql = "SELECT id FROM layers WHERE name = :name";
				$stmnt = DB::$connection->prepare($sql);
				$stmnt->execute(array(":name" => $layer['layerName']));
				$layerid = $stmnt->fetchColumn();			
				// Insert
				$sql = "INSERT INTO devicelayers 
							(reportid, layerid, implversion, specversion) 
						VALUES 
							(:reportid, :layerid, :implversion, :specversion)";
				$stmnt = DB::$connection->prepare($sql);
				$stmnt->execute(array(
					":reportid" => $reportid, 
					":layerid" => $layerid, 
					":implversion" => $layer['implementationVersion'], 
					":specversion" => $layer['specVersion']));		
			} catch (Exception $e) {
				reportError('Error while trying to upload report (error at device layer)');
			}												
			
			// Layer extensions
			$layerextnode = $layer['extensions']; 
			foreach ($layerextnode as $layerext) {
				$sql = "INSERT INTO devicelayerextensions 
							(reportid, devicelayerid, name, specversion) 
						VALUES 
							(:reportid, :devicelayerid, :name, :specversion)";
				try {
					$stmnt = DB::$connection->prepare($sql);
					$stmnt->execute(array(
						":reportid" => $reportid, 
						":devicelayerid" => $layerid, 
						":name" => $layerext['extensionName'], 
						":specversion" => $layerext['specVersion']));				
				} catch (Exception $e) {
					reportError('Error while trying to upload report (error at device layer extension)');
				}																
			}
		}
	}

	// Device memory properties 
	// Heaps
	{
		$jsonnode = $json['memory']['memoryHeaps']; 
		if (is_array($jsonnode)) {
			foreach ($jsonnode as $memheap) {
				$sql = "INSERT INTO devicememoryheaps
							(reportid, flags, size)
						VALUES
							(:reportid, :flags, :size)";
				try {
					$stmnt = DB::$connection->prepare($sql);
					$stmnt->execute(array(
						":reportid" => $reportid, 
						":flags" => $memheap['flags'], 
						":size" => convertValue($memheap['size'])));				
				} catch (Exception $e) {
					reportError('Error while trying to upload report (error at device memory heap)');
				}																
			}
		}
	}
	// Types
	{
		$jsonnode = $json['memory']['memoryTypes']; 
		if (is_array($jsonnode)) {
			foreach ($jsonnode as $memtype) {
				$sql = "INSERT INTO devicememorytypes
							(reportid, heapindex, propertyflags)
						VALUES
							(:reportid, :heapindex, :propertyflags)";
				try {
					$stmnt = DB::$connection->prepare($sql);
					$stmnt->execute(array(
						":reportid" => $reportid, 
						":heapindex" => $memtype['heapIndex'], 
						":propertyflags" => $memtype['propertyFlags']));				
				} catch (Exception $e) {
					reportError('Error while trying to upload report (error at device memory type)');
				}																
			}
		}
	}

	// Surface properties
	$hassurfacecaps = false;
	if (array_key_exists('surfacecapabilites', $json) || array_key_exists('surfaceCapabilities', $json)) {
		if (array_key_exists('surfacecapabilites', $json)) {
			$surfacecaps = $json['surfacecapabilites'];
		} else {
			$surfacecaps = $json['surfaceCapabilities'];
		}
		$hassurfacecaps = ($surfacecaps['validSurface'] == 1);
		if ($hassurfacecaps) {
			// Caps
			$sql = 
				"INSERT INTO devicesurfacecapabilities
				(
					reportid, 
					minImageCount, 
					maxImageCount, 
					maxImageArrayLayers, 
					`minImageExtent.width`, 
					`minImageExtent.height`, 
					`maxImageExtent.width`, 
					`maxImageExtent.height`, 
					supportedUsageFlags, 
					supportedTransforms, 
					supportedCompositeAlpha, 
					surfaceExtension
				)
				VALUES
				(
					:reportid,
					:minImageCount, 
					:maxImageCount, 
					:maxImageArrayLayers, 
					:minImageExtent_width, 
					:minImageExtent_height, 
					:maxImageExtent_width, 
					:maxImageExtent_height, 
					:supportedUsageFlags, 
					:supportedTransforms, 
					:supportedCompositeAlpha, 
					:surfaceExtension			
				)";							
			$values = array(
				":reportid" => $reportid,
				":minImageCount" => $surfacecaps['minImageCount'], 
				":maxImageCount" => $surfacecaps['maxImageCount'], 
				":maxImageArrayLayers" => $surfacecaps['maxImageArrayLayers'], 
				":minImageExtent_width" => $surfacecaps['minImageExtent']['width'], 
				":minImageExtent_height" => $surfacecaps['minImageExtent']['height'], 
				":maxImageExtent_width" => $surfacecaps['maxImageExtent']['width'], 
				":maxImageExtent_height" => $surfacecaps['maxImageExtent']['height'], 
				":supportedUsageFlags" => $surfacecaps['supportedUsageFlags'], 
				":supportedTransforms" => $surfacecaps['supportedTransforms'], 
				":supportedCompositeAlpha" => $surfacecaps['supportedCompositeAlpha'],
				":surfaceExtension" => $surfacecaps['surfaceExtension']								
			);
			try {			
				$stmnt = DB::$connection->prepare($sql);
				$stmnt->execute($values);				
			} catch (Exception $e) {
				reportError('Error while trying to upload report (error at surface properties)');
			}																

			// Present modes
			$jsonnode = $surfacecaps['presentmodes']; 
			if (is_array($jsonnode)) {
				foreach ($jsonnode as $presentmode) {
					$sql = "INSERT INTO devicesurfacemodes
								(reportid, presentmode)
							VALUES
								(:reportid, :presentmode)";
					try {
						$stmnt = DB::$connection->prepare($sql);
						$stmnt->execute(array(":reportid" => $reportid, ":presentmode" => $presentmode));				
					} catch (Exception $e) {
						reportError('Error while trying to upload report (error at surface present mode)');
					}																					
				}
			}	

			// Surface formats	 		
			$jsonnode = $surfacecaps['surfaceformats']; 
			if (is_array($jsonnode)) {
				foreach ($jsonnode as $surfaceformat) {
					$sql = "INSERT INTO devicesurfaceformats
								(reportid, format, colorspace)
							VALUES
								(:reportid, :format, :colorspace)";
					try {
						$stmnt = DB::$connection->prepare($sql);
						$stmnt->execute(array(":reportid" => $reportid, ":format" => $surfaceformat['format'], ":colorspace" => $surfaceformat['colorSpace']));				
					} catch (Exception $e) {
						reportError('Error while trying to upload report (error at surface present format)');
					}																					
				}
			}	
		}
	}

	// Platform details
	$display_name = null;
	if (array_key_exists('platformdetails', $json)) {
		$jsonnode = $json['platformdetails']; 
		$index = 0;
		$platform_model = null;
		$platform_manufacturer = null;
		foreach ($jsonnode as $key => $value) {
			try {
				// Add to global mapping table (if not already present)
				$sql = "INSERT IGNORE INTO platformdetails (name) VALUES (:name)";
				$stmnt = DB::$connection->prepare($sql);
				$stmnt->execute(array(":name" => $key));				
				// Device
				$sql = "SELECT id FROM platformdetails WHERE name = :name";
				$stmnt = DB::$connection->prepare($sql);
				$stmnt->execute(array(":name" => $key));
				$id = $stmnt->fetchColumn();			
				// Insert
				$sql = "INSERT INTO deviceplatformdetails 
							(reportid, platformdetailid, value) 
						VALUES 
							(:reportid, :platformdetailid, :value)";
				$stmnt = DB::$connection->prepare($sql);
				$stmnt->execute(array(
					":reportid" => $reportid, 
					":platformdetailid" => $id, 
					":value" => $value));
			} catch (Exception $e) {
				reportError('Error while trying to upload report (error at platform details)');
			}													
		}
		// Construct display name for Anroid devices, device name only contains GPU name
		try {
			if ($jsonnode) {
				$display_name_parts = [];
				if (array_key_exists('android.ProductManufacturer', $jsonnode)) {
					$display_name_parts[] = ucfirst($jsonnode['android.ProductManufacturer']);
				}
				if (array_key_exists('android.ProductModel', $jsonnode)) {
					$display_name_parts[] = $jsonnode['android.ProductModel'];
				}
				if (count($display_name_parts) > 0 ) {
					$display_name = implode(' ', $display_name_parts);				
					$sql = "UPDATE reports set displayname = :displayname where reportid = :reportid and displayname is null";
					$stmnt = DB::$connection->prepare($sql);
					$stmnt->execute([":reportid" => $reportid, ":displayname" => $display_name]);
				}
			}
		} catch (Exception $e) {
			// Don't fail here, as it's not critical
		}
	}	

	// Extended feature set
	if (array_key_exists('extended', $json)) {
		$extended = $json['extended'];
		// Device features
		if (array_key_exists('devicefeatures2', $extended)) {
			foreach ($extended['devicefeatures2'] as $feature) {
				$sql = "INSERT INTO devicefeatures2
							(reportid, name, extension, supported)
						VALUES
							(:reportid, :name, :extension, :supported)";
				try {
					$values = array(
						":reportid" => $reportid, 
						":name" => $feature['name'], 
						":extension" => $feature['extension'], 
						":supported" => $feature['supported']);
					$stmnt = DB::$connection->prepare($sql);
					$stmnt->execute($values);
				} catch (Exception $e) {
					reportError('Error while trying to upload report (error at device extended device features)');
				}
				// Mark extension to have additional features
				try {
					$stmnt = DB::$connection->prepare("UPDATE extensions set hasfeatures = 1 where hasfeatures is null and name = :extension");
					$stmnt->execute(['extension' => $feature['extension']]);
				} catch (Exception $e) {
					mailError("Error at marking extension to have additional features: ".$e->getMessage(), $jsonFile);
				}				
			}
		}
		// Device properties			
		if (array_key_exists('deviceproperties2', $extended)) {
			foreach ($extended['deviceproperties2'] as $property) {
				$sql = "INSERT INTO deviceproperties2
							(reportid, name, extension, value)
						VALUES
							(:reportid, :name, :extension, :value)";
				try {
					if (is_array($property['value'])) {
						$value = serialize($property['value']);
					} else {
						$value = $property['value'];
					}
					$values = array(
						":reportid" => $reportid, 
						":name" => $property['name'], 
						":extension" => $property['extension'], 
						":value" => $value);
					$stmnt = DB::$connection->prepare($sql);
					$stmnt->execute($values);
				} catch (Exception $e) {
					reportError('Error while trying to upload report (error at device extended device properties)');
				}
				// Mark extension to have additional properties
				try {
					$stmnt = DB::$connection->prepare("UPDATE extensions set hasproperties = 1 where hasproperties is null and name = :extension");
					$stmnt->execute(['extension' => $feature['extension']]);
				} catch (Exception $e) {
					mailError("Error at marking extension to have additional properties: ".$e->getMessage(), $jsonFile);
				}				
			}		
		}		
	}

	// Core features
	importCore11Data($json, $reportid);
	importCore12Data($json, $reportid);
	importCore13Data($json, $reportid);
	importCore14Data($json, $reportid);

	importProfiles($json, $reportid);

	/*
		Instance
	*/
	$instancenode = $json['instance']; 
	// Extensions
	if ($instancenode) {		
		$jsonnode = $instancenode['extensions']; 
		foreach ($jsonnode as $ext) {
			// Add to global mapping table (if not already present)
			$sql = "INSERT IGNORE INTO instanceextensions (name) VALUES (:name)";
			$stmnt = DB::$connection->prepare($sql);
			$stmnt->execute(array(":name" => $ext['extensionName']));	
			// Get extension id
			$sql = "SELECT id FROM instanceextensions WHERE name = :name";
			$stmnt = DB::$connection->prepare($sql);
			$stmnt->execute(array(":name" => $ext['extensionName']));
			$extensionid = $stmnt->fetchColumn();
			// Insert
			$sql = "INSERT INTO deviceinstanceextensions (reportid, extensionid, specversion) VALUES (:reportid, :extensionid, :specversion)";
			try {
				$stmnt = DB::$connection->prepare($sql);
				$stmnt->execute(array(":reportid" => $reportid, ":extensionid" => $extensionid, ":specversion" => $ext['specVersion']));
			} catch (Exception $e) {
				mailError("Error at instance extensions: ".$e->getMessage(), $jsonFile);
				reportError('Error while trying to upload report (error at instance extensions)');
			}															
		}	
		// Layers
		{		
			$jsonnode = $instancenode['layers']; 
			$index = 0;
			foreach ($jsonnode as $layer) {
				try {
					// Add to global mapping table (if not already present)
					$sql = "INSERT IGNORE INTO instancelayers (name) VALUES (:name)";
					$stmnt = DB::$connection->prepare($sql);
					$stmnt->execute(array(":name" => $layer['layerName']));				
					// Get layer id
					$sql = "SELECT id FROM instancelayers WHERE name = :name";
					$stmnt = DB::$connection->prepare($sql);
					$stmnt->execute(array(":name" => $layer['layerName']));
					$layerid = $stmnt->fetchColumn();			
					// Insert
					$sql = "INSERT IGNORE INTO deviceinstancelayers 
								(reportid, layerid, implversion, specversion) 
							VALUES 
								(:reportid, :layerid, :implversion, :specversion)";
					$stmnt = DB::$connection->prepare($sql);
					$stmnt->execute(array(
						":reportid" => $reportid, 
						":layerid" => $layerid, 
						":implversion" => $layer['implementationVersion'], 
						":specversion" => $layer['specVersion']));		
				} catch (Exception $e) {
					reportError('Error while trying to upload report (error at instance layer)');
				}												
				
				// Layer extensions
				$layerextnode = $layer['extensions']; 
				foreach ($layerextnode as $layerext) {
					$sql = "INSERT INTO deviceinstancelayerextensions 
								(reportid, devicelayerid, name, specversion) 
							VALUES 
								(:reportid, :devicelayerid, :name, :specversion)";
					try {
						$stmnt = DB::$connection->prepare($sql);
						$stmnt->execute(array(
							":reportid" => $reportid, 
							":devicelayerid" => $layerid, 
							":name" => $layerext['extensionName'], 
							":specversion" => $layerext['specVersion']));				
					} catch (Exception $e) {
						reportError('Error while trying to upload report (error at instance layer extension)');
					}																
				}
			}
		}			
	}	
	
	DB::$connection->commit();
	DB::disconnect();
		
	echo "res_uploaded";	  	

	if ($mailto) {
		try {
			$msgtitle = "New Vulkan report for ".$json['properties']['deviceName']." (".$json['properties']['driverVersionText'].")";
			$msg = "New Vulkan hardware report uploaded to the database\n\n";
			$msg .= "Link : https://vulkan.gpuinfo.org/displayreport.php?id=$reportid\n\n";	
			$msg .= "Devicename = ".$json['properties']['deviceName']."\n";
			if ($display_name !== null) {
				$msg .= "Displayname = ".$display_name."\n";
			}
			$msg .= "Driver version = ".$json['properties']['driverVersionText']."\n";
			$msg .= "API version = ".$json['properties']['apiVersionText']."\n";
			$msg .= "OS = ".$json['environment']['name']."\n";
			$msg .= "OS version = ".$json['environment']['version']."\n";
			$msg .= "OS arch = ".$json['environment']['architecture']."\n";
			$msg .= "Submitter = ".$json['environment']['submitter']."\n";
			$msg .= "Comment = ".$json['environment']['comment']."\n";
			$msg .= "Report version = ".$json['environment']['reportversion']."\n";
			if (array_key_exists('appvariant', $json['environment'])) {
				$msg .= "App variant = ".$json['environment']['appvariant']."\n";
			}			
			mail($mailto, $msgtitle, $msg);
		} catch (Exception $e) {
			// Failure to mail is not critical
		}	
	}
?>