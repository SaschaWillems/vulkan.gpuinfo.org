<?php
	/* 		
		*
		* Vulkan hardware capability database server implementation
		*	
		* Copyright (C) 2016-2018 by Sascha Willems (www.saschawillems.de)
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
	
	// Table header
	echo "<thead><tr><td class='caption'>Extension</td>";
	foreach ($reportids as $reportId) {
		echo "<td class='caption'>Report $reportId</td>";
	}
	echo "</tr></thead><tbody>";
	reportCompareDeviceColumns($deviceinfo_captions, $deviceinfo_data, sizeof($reportids));

	// Gather all extensions supported by at least one of the reports
	try {
		$stmnt = DB::$connection->prepare("SELECT distinct Name from deviceextensions 
			left join extensions on extensions.ID = deviceextensions.extensionid 
			where deviceextensions.ReportID in (" . $repids . ")");
		$stmnt->execute();
	} catch (PDOException $e) {
		die("Could not fetch device extensions!");
	}	

	$extcaption = array();
	while($row = $stmnt->fetch(PDO::FETCH_NUM)) {	
		foreach ($row as $data) {
			$extcaption[] = $data;	  
		}
	}
	
	// Get extensions for each selected report into an array 
	$extarray = array(); 
	
	foreach ($reportids as $repid) {
		try {
			$stmnt = DB::$connection->prepare("SELECT name from extensions left join deviceextensions on extensions.id = deviceextensions.extensionid where deviceextensions.reportId = :reportid");
			$stmnt->execute(["reportid" => $repid]);
		} catch (PDOException $e) {
			die("Could not fetch device extension for single report!");
		}	
		$subarray = array();
		while($row = $stmnt->fetch(PDO::FETCH_NUM)) {	
			foreach ($row as $data) {
				$subarray[] = $data;	  
			}
		}
		$extarray[] = $subarray; 
	}
	
	// Generate table
	$colspan = count($reportids) + 1;	
	
	// Extension count 	
	echo "<tr class='firstrow'><td class='firstrow'>Extension count</td>"; 
	for ($i = 0, $arrsize = sizeof($extarray); $i < $arrsize; ++$i) { 	  
		echo "<td class='valuezeroleftdark'>".count($extarray[$i])."</td>";
	}
	echo "</tr>"; 		
	$rowindex++;
	
	foreach ($extcaption as $extension) {		
		// Check if missing it at least one report
		$missing = false;
		$index = 0;
		foreach ($reportids as $repid) {
			if (!in_array($extension, $extarray[$index])) {
				$missing = true;
			}
			$index++;
		}  			
		
		$add = '';
		if ($missing) {
			$add = 'color:#FF0000;';
		}
		$className = "same";
		$index = 0;
		foreach ($reportids as $repid) {
			if (!in_array($extension, $extarray[$index])) { 
				$className = "diff";
			}
			$index++;
		}
		echo "<tr style='$add' class='$className'><td class='firstrow'>$extension</td>\n";		 
		$index = 0;
		foreach ($reportids as $repid) {
			if (in_array($extension, $extarray[$index])) { 
				echo "<td class='valuezeroleftdark'><img src='icon_check.png' width=16px></td>";
				} else {
				echo "<td class='valuezeroleftdark'><img src='icon_missing.png' width=16px></td>";
			}	
			$index++;
		}  
		$rowindex++;
		echo "</tr>"; 
	}	  
?>