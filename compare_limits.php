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
	
	$report_compare->beginTable("comparelimits");
	$report_compare->insertTableHeader("Limit", true);
	$report_compare->insertDeviceInformation("Device");

	$compare_limits = $report_compare->fetchLimits();
	
	// Generate table from selected reports
	for ($i = 0; $i < $compare_limits->count; $i++) {  
		// Check if row contains differing values
		$differing_values = false;
		for ($j = 1; $j < count($compare_limits->data); $j++) {
			if ($compare_limits->data[$j][$i] !== $compare_limits->data[0][$i]) {
				$differing_values = true;
				break;
			}
		}

		$row_class = "";
		if (!$report_compare->isHeaderColumn($compare_limits->captions[$i])) {
			$row_class = $differing_values ? "" : "class='sameCaps'";
		};

		echo "<tr $row_class>";
		echo "<td class='subkey'>".($differing_values ? $report_compare->getDiffIcon() : "").$compare_limits->captions[$i] ."</td>";
		echo "<td>Limit</td>";

		// Values
		for ($j = 0; $j < count($column); $j++) {
			$fontClass = '';
			$value = $compare_limits->data[$j][$i];
			if (strpos($compare_limits->captions[$i], 'SampleCounts')) {				
				$sampleCountflags = getSampleCountFlags($value);
				if (count($sampleCountflags) > 0) {
					echo "<td>".listSampleCountFlags($value)."</td>";
				} else {
					echo "<td><font color='red'>none</font></td>";
				}
			} else {
				echo "<td>".$value."</td>";
			}
		} 
		echo "</tr>";
	}

	$report_compare->endTable();
?>