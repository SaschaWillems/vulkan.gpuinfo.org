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
	
	ReportCompare::insertTableHeader("Limit", $deviceinfo_data, count($reportids));
	ReportCompare::insertDeviceColumns($deviceinfo_captions, $deviceinfo_data, count($reportids));
	
	$repids = implode(",", $reportids);   
	try {
		$stmnt = DB::$connection->prepare(
			"SELECT
				lim.*
			from reports r
			left join
				deviceproperties p on (p.reportid = r.id)
			left join
				devicelimits lim on (lim.reportid = r.id)					
			where r.id in (" . $repids . ")");
		$stmnt->execute();
	} catch (PDOException $e) {
		die("Could not fetch device limits!");
	}	
	
	// Gather data into array
	$column    = array();
	$captions  = array();
	
	while($row = $stmnt->fetch(PDO::FETCH_ASSOC)) {
		$reportdata = array();
		foreach ($row as $colname => $data) {
			if ($colname != "reportid") {
				$reportdata[] = $data;	  
				$captions[]   = $colname;
			}												
		} 		
		$column[] = $reportdata; 		
	}   
	
	// Generate table from selected reports
	for ($i = 0; $i < count($column[0]); $i++) { 	  
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

		$fontStyle = ($minval < $maxval) ? "style='color:#FF0000;'" : "";					
		$headerFields = array("device", "driverversion", "apiversion", "os");
		$rowClass = "";
		// Comparison for min values is flipped
		$flipCompare = (stripos($captions[$i], 'min') !== false);		
		if (!in_array($captions[$i], $headerFields)) {
			if ($flipCompare) {
				$rowClass = ($maxval < $minval) ? "" : "class='sameCaps'";
			} else {
				$rowClass = ($minval < $maxval) ? "" : "class='sameCaps'";
			}
		}
		echo "<tr $rowClass>\n";
		echo "<td class='firstrow'>". $captions[$i] ."</td>\n";					

		// Values
		for ($j = 0; $j < count($column); $j++) {
			$fontClass = '';
			if (is_numeric($column[$j][$i]) ) {									
				if ($flipCompare) {
					$fontClass = ($column[$j][$i] > $minval) ? "unsupported" : null;
				} else {
					$fontClass = ($column[$j][$i] < $maxval) ? "unsupported" : null;
				}
				echo "<td ".($fontClass ? ("class='$fontClass'") : "").">".$column[$j][$i]."</td>";
			} else {
				echo "<td>".$column[$j][$i]."</td>";
			}
		} 
		echo "</tr>\n";
	} 
?>