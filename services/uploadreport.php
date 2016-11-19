<?php
	/* 		
		*
		* Vulkan hardware capability database server implementation
		*	
		* Copyright (C) 2011-2016 by Sascha Willems (www.saschawillems.de)
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

	// Connect to DB 
	include './../dbconfig.php';
	
	dbConnect();		
	
	$jsonFile = file_get_contents($file);	
	$json = json_decode($jsonFile, true);
	
	// Check report version
	$reportversion = floatval($json['environment']['reportversion']);
	if ($reportversion < 1.2)
	{
		echo "This version of the Vulkan Hardware Capability is no longer supported!\nPlease download a recent version from http://www.gpuinfo.org";
		dbDisconnect();
		exit();	  
	}			
	
	// Check if report is already present
	$deviceselector = "
		devicename = '".$json['deviceproperties']['devicename']."' and 
		driverversion = '".$json['deviceproperties']['driverversion']."' and
		apiversion = '".$json['deviceproperties']['apiversion']."' and
		osname = '".$json['environment']['name']."' and
		osversion = '".$json['environment']['version']."' and
		osarchitecture = '".$json['environment']['architecture']."'";		
	$sqlstr = "select id from reports where $deviceselector";
	$sqlresult = mysql_query($sqlstr) or die(mysql_error());

	if (mysql_num_rows($sqlresult) > 0) 
	{
		$reportid = mysql_result($sqlresult, 0);
 		$sqlresult = mysql_query("update reports set counter = counter+1 where id = $reportid") or die(mysql_error());
		echo "Report already present!";
		dbDisconnect();
		exit();	  
	}	
	
	// Reports must at least contain one supported format
	if (count($json['formats']) == 0) 
	{
		echo "The uploaded report does not contain any supported formats, report rejected!";
		dbDisconnect();
		exit();	  
	}
	
	mysql_query("START TRANSACTION");	
	
	// Report meta data	
	$sqlstr = "
		INSERT INTO reports
			(submitter, devicename, driverversion, apiversion, osname, osversion, osarchitecture, version, description, counter)
		VALUES
			('".$json['environment']['submitter']."',".
			"'".$json['deviceproperties']['devicename']."',".
			"'".$json['deviceproperties']['driverversion']."',".
			"'".$json['deviceproperties']['apiversion']."',".
			"'".$json['environment']['name']."',".
			"'".$json['environment']['version']."',".
			"'".$json['environment']['architecture']."',".		
			"'".$json['environment']['reportversion']."',".		
			"'".$json['environment']['comment']."',".					
			"0)";
				
	$sqlresult = mysql_query($sqlstr) or die(mysql_error()); 

	// Get id of inserted report (auto increment)
	$reportid = mysql_insert_id($db_link);
	
	// Store json for api calls (or later reference)
	$sqlstr = "
		insert into reportsjson 
			(reportid, json)
		values
			(".$reportid.", '".mysql_real_escape_string($jsonFile)."')";
	$sqlresult = mysql_query($sqlstr) or die(mysql_error()); 	
	
	// Device properties
	$jsonnode = $json['deviceproperties']; 
	$keys = array();
	$values = array();
	foreach ($jsonnode as $key => $value)
	{
		$keys[] = $key;
		$values[] = '"'.$value.'"';
	}	
	$sqlstr = "
		insert into deviceproperties 
			(reportid,".implode(",", $keys).")
		values
			(".$reportid.",".implode(",", $values).")";
	$sqlresult = mysql_query($sqlstr) or die(mysql_error()); 
	
	// Device features
	$jsonnode = $json['devicefeatures']; 
	$keys = array();
	$values = array();
	foreach ($jsonnode as $key => $value)
	{
		$keys[] = "`".$key."`";
		$values[] = '"'.$value.'"';
	}	
	$sqlstr = "
		insert into devicefeatures 
			(reportid,".implode(",", $keys).")
		values
			(".$reportid.",".implode(",", $values).")";
	$sqlresult = mysql_query($sqlstr) or die(mysql_error()); 
	
	// Device limits (all values are stored as serialized text for easy array handling)
	$jsonnode = $json['devicelimits']; 
	$keys = array();
	$values = array();
	foreach ($jsonnode as $key => $value)
	{
		$keys[] = "`".$key."`";
		$values[] = '"'.$value.'"';
	}	
	$sqlstr = "
		insert into devicelimits 
			(reportid,".implode(",", $keys).")
		values
			(".$reportid.",".implode(",", $values).")";
	$sqlresult = mysql_query($sqlstr) or die(mysql_error()); 
	
	// Extensions
	$jsonnode = $json['extensions']; 
	$extarray = array();
	foreach ($jsonnode as $ext)
	{
		// Add to global mapping table (if not already present)
		$sqlstr = "insert ignore into extensions (name) values ('".$ext['extname']."')";
		$sqlresult = mysql_query($sqlstr) or die(mysql_error()); 
		// Device
		$sqlresult = mysql_query("select id from extensions where name = '".$ext['extname']."'") or die(mysql_error()); 
		$extensionid = mysql_result($sqlresult, 0);
		$sqlstr = "insert into deviceextensions (reportid, extensionid, specversion) values (".$reportid.",".$extensionid.",".$ext['specversion'].")";
		$sqlresult = mysql_query($sqlstr) or die(mysql_error()); 
	}	
	
	// Device formats
	$jsonnode = $json['formats']; 
	foreach ($jsonnode as $format)
	{
		$sqlstr = "
			insert into deviceformats 
				(reportid, formatid, lineartilingfeatures, optimaltilingfeatures, bufferfeatures, supported) 
			values 
				(".
					$reportid.",".
					$format['format'].",".
					$format['lineartilingfeatures'].",".
					$format['optimaltilingfeatures'].",".
					$format['bufferfeatures'].",".
					$format['supported'].
				")";
		$sqlresult = mysql_query($sqlstr) or die(mysql_error()); 
	}	
	
	// Device queues
	$jsonnode = $json['queues']; 
	$index = 0;
	foreach ($jsonnode as $queue)
	{	
		$sqlstr = "
			insert into devicequeues 
				(reportid, id, count, 
				flags, 
				timestampValidBits, 
				`minImageTransferGranularity.width`, 
				`minImageTransferGranularity.height`, 
				`minImageTransferGranularity.depth`) 
			values 
				(".
					$reportid.",".$index.",".$queue['count'].",".
					$queue['flags'].",".
					$queue['timestampValidBits'].",".
					$queue['minImageTransferGranularity.width'].",".
					$queue['minImageTransferGranularity.height'].",".
					$queue['minImageTransferGranularity.depth'].
				")";
		$sqlresult = mysql_query($sqlstr) or die(mysql_error()); 
		$index++;
	}	
	
	// Device layers (and extensions)
	$jsonnode = $json['layers']; 
	$index = 0;
	foreach ($jsonnode as $layer)
	{
		// Add to global mapping table (if not already present)
		$sqlstr = "insert ignore into layers (name) values ('".$layer['layername']."')";
		$sqlresult = mysql_query($sqlstr) or die("insert layer ".mysql_error()); 
		// Layers
		$sqlresult = mysql_query("select id from layers where name = '".$layer['layername']."'") or die(mysql_error()); 
		$layerid = mysql_result($sqlresult, 0);
		$sqlstr = "
			insert ignore into devicelayers 
				(reportid, layerid, implversion, specversion) 
			values 
				(".$reportid.",".$layerid.",".$layer['specversion'].", ".$layer['implversion'].")";
		$sqlresult = mysql_query($sqlstr) or die(mysql_error()); 
		// Layer extensions
		$layerextnode = $layer['extensions']; 
		foreach ($layerextnode as $layerext) 
		{
			$sqlstr = "
				insert into devicelayerextensions
					(reportid, devicelayerid, name, specversion)
				values	
					($reportid, $layerid, '".$layerext['extname']."', ".$layerext['specversion'].")";
			$sqlresult = mysql_query($sqlstr) or die(mysql_error());			
		}
	}	
	
	// Device memory properties 
	// Heaps
	$jsonnode = $json['memoryproperties']['heaps']; 
	if (is_array($jsonnode)) {
		foreach ($jsonnode as $memoryheap)
		{
			$sqlstr = "
				insert into devicememoryheaps
					(reportid, flags, size)
				values	
					($reportid, ".$memoryheap['flags'].", ".$memoryheap['size'].")";
			$sqlresult = mysql_query($sqlstr) or die(mysql_error());		
		}
	}
	// Types
	$jsonnode = $json['memoryproperties']['memorytypes']; 
	if (is_array($jsonnode)) {
		foreach ($jsonnode as $memoryheap)
		{
			$sqlstr = "
				insert into devicememorytypes
					(reportid, heapindex, propertyflags)
				values	
					($reportid, ".$memoryheap['heapindex'].", ".$memoryheap['propertyflags'].")";
			$sqlresult = mysql_query($sqlstr) or die(mysql_error());		
		}
	}

	// Surface properties
	$hassurfacecaps = false;
	if (array_key_exists('surfacecapabilites', $json))
	{
		$surfacecaps = $json['surfacecapabilites'];
		$hassurfacecaps = ($surfacecaps['validSurface'] == 1);
		if ($hassurfacecaps)
		{
			// Caps
			$sqlstr = "
				INSERT INTO devicesurfacecapabilities
					(reportid, minImageCount, maxImageCount, maxImageArrayLayers, `minImageExtent.width`, `minImageExtent.height`, 
					`maxImageExtent.width`, `maxImageExtent.height`, supportedUsageFlags, supportedTransforms, supportedCompositeAlpha, surfaceExtension)
				VALUES
					(".
						$reportid.",".
						$surfacecaps['minImageCount'].",".
						$surfacecaps['maxImageCount'].",".
						$surfacecaps['maxImageArrayLayers'].",".
						$surfacecaps['minImageExtent.width'].",".
						$surfacecaps['minImageExtent.height'].",".
						$surfacecaps['maxImageExtent.width'].",".
						$surfacecaps['maxImageExtent.height'].",".
						$surfacecaps['supportedUsageFlags'].",".
						$surfacecaps['supportedTransforms'].",".
						$surfacecaps['supportedCompositeAlpha'].",".
						"'".$surfacecaps['surfaceExtension']."'".
					")";							
			$sqlresult = mysql_query($sqlstr) or die(mysql_error()."\n".$sqlstr);
			// Present modes
			$jsonnode = $json['surfacecapabilites']['presentmodes']; 
			if (is_array($jsonnode)) {
				foreach ($jsonnode as $presentmode)
				{
					$sqlstr = "
						insert into devicesurfacemodes
							(reportid, presentmode)
						values	
							($reportid, ".$presentmode['presentMode'].")";
					$sqlresult = mysql_query($sqlstr) or die(mysql_error());		
				}
			}	
			// Surface formats	 		
			$jsonnode = $json['surfacecapabilites']['surfaceformats']; 
			if (is_array($jsonnode)) {
				foreach ($jsonnode as $surfaceformat)
				{
					$sqlstr = "
						insert into devicesurfaceformats
							(reportid, format, colorSpace)
						values	
							($reportid, ".$surfaceformat['format'].", ".$surfaceformat['colorSpace'].")";
					$sqlresult = mysql_query($sqlstr) or die(mysql_error());		
				}
			}	
		}
	}
		
	mysql_query("COMMIT");
		
	echo "res_uploaded";	  	
			
	$msgtitle = "New Vulkan report for ".$json['deviceproperties']['devicename']." (".$json['deviceproperties']['driverversion'].")";
	
	$msg = "New Vulkan hardware report uploaded to the database\n\n";
	$msg .= "Link : http://vulkan.gpuinfo.org/displayreport.php?id=$reportid\n\n";
	$msg .= "Devicename = ".$json['deviceproperties']['devicename']."\n";
	$msg .= "Driver version = ".$json['deviceproperties']['driverversion']."\n";
	$msg .= "API version = ".$json['deviceproperties']['apiversion']."\n";
	$msg .= "OS = ".$json['environment']['name']."\n";
	$msg .= "OS version = ".$json['environment']['version']."\n";
	$msg .= "OS arch = ".$json['environment']['architecture']."\n";
	$msg .= "Submitter = ".$json['environment']['submitter']."\n";
	$msg .= "Comment = ".$json['environment']['comment']."\n";
	$msg .= "Report version = ".$json['environment']['reportversion']."\n";
	
	mail($mailto, $msgtitle, $msg); 
	
	dbDisconnect();	 
?>