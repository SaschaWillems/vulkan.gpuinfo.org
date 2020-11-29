<?php
	/* 		
		*
		* Vulkan hardware capability database server implementation
		*	
		* Copyright (C) 2016-2020 by Sascha Willems (www.saschawillems.de)
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

	$report_compare->beginTable();
	$report_compare->insertTableHeader("Feature", true);
	$report_compare->insertDeviceInformation("Device");
	
	// Vulkan Core 1.0
	$columns = [];
	$captions = [];	
	$compare_features = $report_compare->fetchFeatures();
	foreach($compare_features as $index => $row) {
		$reportdata = [];				
		foreach ($row as $key => $data) {
			if ($key == "reportid") { continue; }
			$reportdata[] = $data;	  
			if ($index == 0) {
				$captions[] = $key;
			}
		} 	
		$columns[] = $reportdata; 	
	}   
	
	for ($i = 0; $i < count($columns[0]); $i++) { 	  
		// Check of row contains differing values
		$differing_values = false;
		for ($j = 1; $j < sizeof($columns); $j++) {
			if ($columns[$j][$i] !== $columns[0][$i]) {
				$differing_values = true;
				break;
			}
		}
		
		$row_class = "";
		if (!$report_compare->isHeaderColumn($captions[$i])) {
			$row_class = $differing_values ? "" : "class='sameCaps'";
		};
		echo "<tr $row_class>";
		echo "<td class='subkey'>".($differing_values ? $report_compare->getDiffIcon() : "").$captions[$i] ."</td>";
		echo "<td>Vulkan Core 1.0</td>";		
		for ($j = 0; $j < count($columns); $j++) {	 
			echo "<td>".displayBool($columns[$j][$i])."</td>";
		}
		echo "</tr>";
	}

	// @todo: Display additional core features if at least one report supports them

	$report_compare->endTable();
?>