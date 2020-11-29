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

	$report_compare->beginTable("comparefeatures");
	$report_compare->insertTableHeader("Feature", true);
	$report_compare->insertDeviceInformation("Device");
	
	// Vulkan Core 1.0
	$compare_features = $report_compare->fetchFeatures();
	
	for ($i = 0; $i < $compare_features->count; $i++) { 	  
		// Check if row contains differing values
		$differing_values = false;
		for ($j = 1; $j < count($compare_features->data); $j++) {
			if ($compare_features->data[$j][$i] !== $compare_features->data[0][$i]) {
				$differing_values = true;
				break;
			}
		}
		
		$row_class = "";
		if (!$report_compare->isHeaderColumn($compare_features->captions[$i])) {
			$row_class = $differing_values ? "" : "class='sameCaps'";
		};
		echo "<tr $row_class>";
		echo "<td class='subkey'>".($differing_values ? $report_compare->getDiffIcon() : "").$compare_features->captions[$i] ."</td>";
		echo "<td>Vulkan Core 1.0</td>";		
		for ($j = 0; $j < count($compare_features->data); $j++) {	 
			echo "<td>".displayBool($compare_features->data[$j][$i])."</td>";
		}
		echo "</tr>";
	}

	// @todo: Display additional core features if at least one report supports them

	$report_compare->endTable();
?>