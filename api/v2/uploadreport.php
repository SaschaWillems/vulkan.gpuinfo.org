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

	include "./../../functions.php";
	include './../../dbconfig.php';	
	
	// Check for valid file
	$path='./';
	
	// Reports are pretty small, so limit file size for upload (512 KByte will be more than enough)
	$MAX_FILESIZE = 512 * 1024;
	
	$file = $_FILES['data']['name'];

	// Check filesize
	if ($_FILES['data']['size'] > $MAX_FILESIZE)  {
		echo "File exceeds size limitation of 512 KByte!";    
		exit();  
	}
	
	// Check file extension 
	$ext = pathinfo($_FILES['data']['name'], PATHINFO_EXTENSION); 
	if ($ext != 'json') {
		echo "Report '$file' is not of file type json!";
		exit();  
	} 
	
	move_uploaded_file($_FILES['data']['tmp_name'], $path.$_FILES['data']['name']) or die(''); 

	function convertValue($val) {
		if (is_string($val)) {
			if (strpos($val, '0x') === 0) {
				return hexdec($val);
			}
		} else {
			return $val;
		}
	}

	DB::connect();
	
	$jsonFile = file_get_contents($file);	
	$json = json_decode($jsonFile, true);
	
	// Check report version
	$reportversion = floatval($json['environment']['reportversion']);
	if ($reportversion < 1.2)
	{
		echo "This version of the Vulkan Hardware Capability is no longer supported!\nPlease download a recent version from https://www.gpuinfo.org";
		DB::disconnect();
		exit();	  
	}		
	
	// VK 1.1 only with 1.9 or up
	$reportapiversion = $json['properties']['apiVersion'];
	$vkmajor = ($reportapiversion >> 22);
	$vkminor = (($reportapiversion >> 12) & 0x3ff);
	
	if (($vkmajor >= 1) && ($vkminor >= 1) && ($reportversion < 1.9)) {
		echo "This version of the Vulkan Hardware Capability is outdated.\nPlease download a recent version from https://www.gpuinfo.org";
		exit();	  
	}
	
	// Check if device is blacklisted
	try {
		$sql = "select * from blacklist where devicename = :devicename";
		$stmnt = DB::$connection->prepare($sql);
		$stmnt->execute(array(":devicename" => $json['properties']['deviceName']));
		if ($stmnt->rowCount() > 0) { 
			echo "Device ".$json['properties']['deviceName']." has been black-listed and can't be uploaded to the database!";
			DB::disconnect();
			exit();	  	
		}
	} catch (Exception $e) {
		die('Error while trying to upload report (error at black list check)');
	}		
		
	// Check if report is already present
	{
		$sql = "select id from reports where
			devicename = :devicename and 
			driverversion = :driverversion and
			apiversion = :apiversion and
			osname = :osname and
			osversion = :osversion and
			osarchitecture = :osarchitecture";
		$params = array(
			":devicename" => $json['properties']['deviceName'],
			":driverversion" => $json['properties']['driverVersion'],
			":apiversion" => $json['properties']['apiVersion'],
			":osname" => $json['environment']['name'],
			":osversion" => $json['environment']['version'],
			":osarchitecture" => $json['environment']['architecture'],
		);

		try {
			$stmnt = DB::$connection->prepare($sql);		
			$stmnt->execute($params);	
		} catch (Exception $e) {
			die('Error while trying to upload report (error at device present check)');
		}		
		
		if ($stmnt->rowCount() > 0) {
			$reportid = $stmnt->fetchColumn();
			$sql = "UPDATE reports SET counter = counter+1 WHERE id = :reportid";
			$stmnt = DB::$connection->prepare($sql);		
			$stmnt->execute(array(":reportid" => $reportid));			
			echo "Report already present!";
			DB::disconnect();
			exit();	  
		}	
	}
	
	DB::$connection->beginTransaction();
	
	// Report meta data	
	{
		$sql = 
			"INSERT INTO reports
				(submitter, devicename, driverversion, apiversion, osname, osversion, osarchitecture, version, description, counter)
			VALUES
				(:submitter, :devicename, :driverversion, :apiversion, :osname, :osversion, :osarchitecture, :version, :description, :counter)";

		$values = array(
			":submitter" => $json['environment']['submitter'],
			":devicename" => $json['properties']['deviceName'],
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
			die('Error while trying to upload report (error at report meta data)');
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
			die("Missing device property node!");
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
			die('Error while trying to upload report (error at device properties)');
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
			die('Error while trying to upload report (error at device limits)');
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
			die('Error while trying to upload report (error at device features)');
		}									
	}
	
	// Image and buffer formats
	{
		$jsonnode = $json['formats']; 
		if (!$jsonnode) {
			die('Report has no image or buffer formats!');
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
				die('Error while trying to upload report (error at device formats)');
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
				die('Error while trying to upload report (error at device extensions)');
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
				die('Error while trying to upload report (error at device queues)');
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
				die('Error while trying to upload report (error at device layer)');
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
						":name" => $layerext['extname'], 
						":specversion" => $layerext['specVersion']));				
				} catch (Exception $e) {
					die('Error while trying to upload report (error at device layer extension)');
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
					die('Error while trying to upload report (error at device memory heap)');
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
					die('Error while trying to upload report (error at device memory type)');
				}																
			}
		}
	}

	// Surface properties
	$hassurfacecaps = false;
	if (array_key_exists('surfacecapabilites', $json)) {
		$surfacecaps = $json['surfacecapabilites'];
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
				die('Error while trying to upload report (error at surface properties)');
			}																

			// Present modes
			$jsonnode = $json['surfacecapabilites']['presentmodes']; 
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
						die('Error while trying to upload report (error at surface present mode)');
					}																					
				}
			}	

			// Surface formats	 		
			$jsonnode = $json['surfacecapabilites']['surfaceformats']; 
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
						die('Error while trying to upload report (error at surface present format)');
					}																					
				}
			}	
		}
	}

	// Platform details
	{		
		$jsonnode = $json['platformdetails']; 
		$index = 0;
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
				die('Error while trying to upload report (error at platform details)');
			}														
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
					die('Error while trying to upload report (error at device extended device features)');
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
					$values = array(
						":reportid" => $reportid, 
						":name" => $property['name'], 
						":extension" => $property['extension'], 
						":value" => $property['value']);
					$stmnt = DB::$connection->prepare($sql);
					$stmnt->execute($values);
				} catch (Exception $e) {
					die('Error while trying to upload report (error at device extended device properties)');
				}							
			}		
		}		
	}

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
				die('Error while trying to upload report (error at instance extensions)');
			}															
		}	
		// Layers
		{		
			$jsonnode = $json['layers']; 
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
					$sql = "INSERT INTO deviceinstancelayers 
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
					die('Error while trying to upload report (error at instance layer)');
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
							":name" => $layerext['extname'], 
							":specversion" => $layerext['specVersion']));				
					} catch (Exception $e) {
						die('Error while trying to upload report (error at instance layer extension)');
					}																
				}
			}
		}			
	}	
	
	DB::$connection->commit();
		
	echo "res_uploaded";	  	
			
	$msgtitle = "New Vulkan report for ".$json['properties']['deviceName']." (".$json['properties']['driverVersionText'].")";
	if ($development_db) {
		$msgtitle = "[DEVELOPMENT] ".$msgtitle;
		$msg = "New Vulkan hardware report uploaded to the development database\n\n";
		$msg .= "Link : http://vulkan.gpuinfo.org/dev/displayreport.php?id=$reportid\n\n";
	} else {
		$msg = "New Vulkan hardware report uploaded to the database\n\n";
		$msg .= "Link : http://vulkan.gpuinfo.org/displayreport.php?id=$reportid\n\n";
	}
	
	$msg .= "Devicename = ".$json['properties']['deviceName']."\n";
	$msg .= "Driver version = ".$json['properties']['driverVersionText']."\n";
	$msg .= "API version = ".$json['properties']['apiVersionText']."\n";
	$msg .= "OS = ".$json['environment']['name']."\n";
	$msg .= "OS version = ".$json['environment']['version']."\n";
	$msg .= "OS arch = ".$json['environment']['architecture']."\n";
	$msg .= "Submitter = ".$json['environment']['submitter']."\n";
	$msg .= "Comment = ".$json['environment']['comment']."\n";
	$msg .= "Report version = ".$json['environment']['reportversion']."\n";
	
	mail($mailto, $msgtitle, $msg); 
	
	DB::disconnect();
?>