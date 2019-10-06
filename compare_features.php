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
	
	ReportCompare::insertTableHeader("Feature", $deviceinfo_data, count($reportids));
	ReportCompare::insertDeviceColumns($deviceinfo_captions, $deviceinfo_data, count($reportids));
	
	$repids = implode(",", $reportids);   
	
	try {
		$stmnt = DB::$connection->prepare(
			"SELECT 
				features.*
			from reports r
			left join
				deviceproperties p on (p.reportid = r.id)
			left join
				devicefeatures features on (features.reportid = r.id)					
			where r.id in (" . $repids . ")");
			$stmnt->execute();
	} catch (PDOException $e) {
		die("Could not fetch device features!");
	}	

	$reportindex = 0;
	
	// Gather data into array
	$column    = array();
	$captions  = array();
	
	while ($row = $stmnt->fetch(PDO::FETCH_NUM)) {
		$colindex = 0;
		$reportdata = array();		
		
		foreach ($row as $data) {
			$meta = $stmnt->getColumnMeta($colindex);
			$caption = $meta["name"];
			if ($caption != "reportid") {
				$reportdata[] = $data;	  
				$captions[]   = $caption;
			}									
			
			$colindex++;
		} 
		
		$column[] = $reportdata; 
		
		$reportindex++;
	}   
	
	// Generate table from selected reports
	$index = 1;  
	for ($i = 0, $arrsize = sizeof($column[0]); $i < $arrsize; ++$i) { 	  
		// Get min and max for this capability
		if (is_numeric($column[0][$i])) {
			
			$minval = $column[0][$i];
			$maxval = $column[0][$i];
			
			for ($j = 0, $subarrsize = sizeof($column); $j < $subarrsize; ++$j) {	 			
				if ($column[$j][$i] < $minval) {
					$minval = $column[$j][$i];
				}
				if ($column[$j][$i] > $maxval) {
					$maxval = $column[$j][$i];
				}
			}
		}								
		
		// Report header
		$fontStyle = ($minval < $maxval) ? "style='color:#FF0000;'" : "";					
		$headerFields = array("device", "driverversion", "apiversion", "os");
		if (!in_array($captions[$i], $headerFields)) {
			$className = ($minval < $maxval) ? "" : "class='sameCaps'";
		} else {
			$className = "";
		}
		echo "<tr $className>\n";
		echo "<td class='firstrow' $fontStyle>". $captions[$i] ."</td>\n";									
		
		// Values
		for ($j = 0, $subarrsize = sizeof($column); $j < $subarrsize; ++$j) {	 
			$fontstyle = '';
			if ($column[$j][$i] < $maxval) {
				$fontstyle = "style='color:#FF0000;'";
			}

			echo "<td>";
			if (in_array($captions[$i], $headerFields)) {
				echo $column[$j][$i];				
			} else {
				// Features are bool only
				echo ($column[$j][$i] == 1) ? "<font color='green'>true</font>" : "<font color='red'>false</font>";
			}
			echo "</td>";			
		} 
		echo "</tr>\n";
		$index++;
	}   
?>