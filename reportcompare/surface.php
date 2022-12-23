<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *
 * Copyright (C) 2016-2022 by Sascha Willems (www.saschawillems.de)
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

$compare_surface_properties = $report_compare->fetchSurfaceProperties();
$compare_surface_formats = $report_compare->fetchSurfaceFormats();
$compare_surface_present_modes = $report_compare->fetchSurfacePresentModes();
?>

<div>
	<ul class='nav nav-tabs nav-level1'>
		<li class='active'><a data-toggle='tab' href='#surfaceproperties'>Properties</a></li>
		<li><a data-toggle='tab' href='#surfaceformats'>Formats</a></li>
		<li><a data-toggle='tab' href='#surfacepresentmodes'>Present modes</a></li>
	</ul>
</div>

<div class='tab-content'>

	<!-- Surface properties tab -->
	<div id='surfaceproperties' class='tab-pane fade in active reportdiv'>
		<?php
		$report_compare->beginTable("surface-caps");
		$report_compare->insertTableHeader("Surface property");
		foreach ($compare_surface_properties->captions as $dataIndex => $caption) {			
			$diff = false;
			$last_val = null;
			$className = 'same';
			// Check if values differ across reports
			for ($i = 0; $i < $report_compare->report_count; $i++) {
				$rid = $report_compare->report_ids[$i];
				$value = $compare_surface_properties->data[$caption][$rid];
				if (($i > 0) && ($value != $last_val)) {
					$diff = true;
					$className = 'diff';
				}
				$last_val = $value;
			}
			echo "<tr class='$className'><td class='subkey'>".($diff ? $report_compare->getDiffIcon() : "")."$caption</td>";
			for ($i = 0; $i < $report_compare->report_count; $i++) {				
				$reportid = $report_compare->report_ids[$i];
				echo "<td>";
				$value = $compare_surface_properties->data[$caption][$reportid];
				if ($value) {
					switch($caption) {
						case 'supportedUsageFlags':
							listFlags(getImageUsageFlags($value));
							break;
						case 'supportedTransforms':
							listFlags(getSurfaceTransformFlags($value));
							break;
						case 'supportedCompositeAlpha':
							listFlags(getCompositeAlphaFlags($value));
							break;
						default:
							echo $value;
					}
				} else {
					echo "<div class='na'>n/a</div>";
				}
				echo "</td>";
			}
		}
		$report_compare->endTable();
		?>
	</div>

	<!-- Surface formats tab -->
	<div id='surfaceformats' class='tab-pane fade in reportdiv'>
		<?php
		$report_compare->beginTable("surface-formats");
		$report_compare->insertTableHeader("Surface formats");
		foreach ($compare_surface_formats->captions as $caption) {			
			$diff = false;
			$last_val = null;
			$className = 'same';
			// Check if values differ across reports
			for ($i = 0; $i < $report_compare->report_count; $i++) {
				$rid = $report_compare->report_ids[$i];
		 		$value = $compare_surface_formats->data[$caption][$rid];
			 	if (($i > 0) && ($value != $last_val)) {
			 		$diff = true;
			 		$className = 'diff';
			 	}
			 	$last_val = $value;
			}
			echo "<tr class='$className'><td>".($diff ? $report_compare->getDiffIcon() : "")."$caption</td>";
			for ($i = 0; $i < $report_compare->report_count; $i++) {
				$rid = $report_compare->report_ids[$i];
				$value = $compare_surface_formats->data[$caption][$rid];
				if ($value == false) {
					echo "<td class='unsupported'>false</td>";
				} elseif ($value == true) {
					echo "<td class='supported'>true</td>";
				} else {
					echo "<td class='na'>n/a</td>";
				}
			}
		}
		$report_compare->endTable();
		?>
	</div>


	<!-- Surface present modes tab -->
	<div id='surfacepresentmodes' class='tab-pane fade in reportdiv'>
		<?php
		$report_compare->beginTable("surface-present-modes");
		$report_compare->insertTableHeader("Surface present modes");
		foreach ($compare_surface_present_modes->captions as $caption) {			
			$diff = false;
			$last_val = null;
			$className = 'same';
			// Check if values differ across reports
			for ($i = 0; $i < $report_compare->report_count; $i++) {
				$rid = $report_compare->report_ids[$i];
		 		$value = $compare_surface_present_modes->data[$caption][$rid];
			 	if (($i > 0) && ($value != $last_val)) {
			 		$diff = true;
			 		$className = 'diff';
			 	}
			 	$last_val = $value;
			}
			echo "<tr class='$className'><td>".($diff ? $report_compare->getDiffIcon() : "")."$caption</td>";
			for ($i = 0; $i < $report_compare->report_count; $i++) {
				$rid = $report_compare->report_ids[$i];
				$value = $compare_surface_present_modes->data[$caption][$rid];
				if ($value == false) {
					echo "<td class='unsupported'>false</td>";
				} elseif ($value == true) {
					echo "<td class='supported'>true</td>";
				} else {
					echo "<td class='na'>n/a</td>";
				}
			}
		}
		$report_compare->endTable();
		?>
	</div>

</div>