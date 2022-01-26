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

// Combined listing of properties, limits and sparse properties as in VkPhysicalDeviceProperties
function insertCore10Properties($report_compare)
{
	$report_compare->beginTab('properties_core_10', true);
	$report_compare->beginTable('table_properties_core_10');
	$report_compare->insertTableHeader("Property", true);

	// Properties
	$compare_properties = $report_compare->fetchCoreProperties('1.0');
	for ($i = 0; $i < $compare_properties->count; $i++) {
		$caption = $compare_properties->captions[$i];
		if (strpos($caption, 'subgroupProperties') === 0) {
			continue;
		}
		// Check if row contains differing values
		$differing_values = false;
		for ($j = 1; $j < count($compare_properties->data); $j++) {
			if ($compare_properties->data[$j][$i] !== $compare_properties->data[0][$i]) {
				$differing_values = true;
				break;
			}
		}

		$row_class = "";
		if (!$report_compare->isHeaderColumn($caption)) {
			$row_class = $differing_values ? "" : "class='sameCaps'";
		};
		echo "<tr $row_class>";
		echo "<td class='subkey'>" . ($differing_values ? $report_compare->getDiffIcon() : "") . $caption . "</td>";
		echo "<td>Properties</td>";
		for ($j = 0; $j < count($compare_properties->data); $j++) {
			$value = $compare_properties->data[$j][$i];
			$displayvalue = ($value ? getPropertyDisplayValue($caption, $value) : "<span class='na'>n/a</span>");
			echo "<td>$displayvalue</td>";
		}
		echo "</tr>";
	}

	// Limits
	$compare_limits = $report_compare->fetchLimits();
	for ($i = 0; $i < $compare_limits->count; $i++) {
		// Check if row contains differing values
		$differing_values = false;
		for ($j = 1; $j < count($compare_limits->data); $j++) {
			if ($compare_limits->data[$j][$i] !== $compare_limits->data[0][$i]) {
				$differing_values = true;
				break;
			}
		}
		$row_class = $differing_values ? "" : "class='sameCaps'";
		echo "<tr $row_class>";
		echo "<td class='subkey'>" . ($differing_values ? $report_compare->getDiffIcon() : "") . $compare_limits->captions[$i] . "</td>";
		echo "<td>Limits</td>";
		for ($j = 0; $j < count($compare_limits->data); $j++) {
			$displayvalue = getPropertyDisplayValue($compare_limits->captions[$i], $compare_limits->data[$j][$i]);
			echo "<td>$displayvalue</td>";
		}
		echo "</tr>";
	}


	$report_compare->endTable();
	$report_compare->endTab();
}

function insertCoreProperties($report_compare, $version)
{
	$compare_properties = $report_compare->fetchCoreProperties($version);

	$report_compare->beginTab('properties_core_' . str_replace('.', '', $version), $version == '1.0');
	$report_compare->beginTable("table_properties_core_" . str_replace('.', '', $version));
	$report_compare->insertTableHeader("Property");

	for ($i = 0; $i < $compare_properties->count; $i++) {
		// Check if row contains differing values
		$differing_values = false;
		for ($j = 1; $j < count($compare_properties->data); $j++) {
			if (($compare_properties->data[$j][$i] !== null) && ($compare_properties->data[$j][$i] !== $compare_properties->data[0][$i])) {
				$differing_values = true;
				break;
			}
		}
		// @todo: comment
		if ($version = '1.3') {
			$compare_properties->captions[$i] = getFullFieldName($compare_properties->captions[$i]);
		}
		$row_class = $differing_values ? "" : "class='sameCaps'";
		echo "<tr $row_class>";
		echo "<td class='subkey'>" . ($differing_values ? $report_compare->getDiffIcon() : "") . $compare_properties->captions[$i] . "</td>";
		for ($j = 0; $j < count($compare_properties->data); $j++) {
			$value = $compare_properties->data[$j][$i];
			$displayvalue = (($value !== null) ? getPropertyDisplayValue($compare_properties->captions[$i], $value) : "<span class='na'>n/a</span>");
			echo "<td>$displayvalue</td>";
		}
		echo "</tr>";
	}

	$report_compare->endTable();
	$report_compare->endTab();
}

function insertExtensionProperties($report_compare)
{
	$report_compare->beginTab('properties_extensions');
	$report_compare->beginTable('table_properties_extensions');
	$report_compare->insertTableHeader("Property", true);
	$report_compare->insertDeviceInformation("Device");

	$extended_properties = [];
	$reports = [];
	if ($report_compare->fetchExtensionProperties($extended_properties, $reports)) {
		foreach ($extended_properties as $extension => $features) {
			foreach ($features as $feature) {
				$html = '';
				$diff = false;
				$last_val = null;
				foreach ($reports as $index => $report) {
					$ext_present = array_key_exists($extension, $report);
					if ($ext_present) {
						$curr_val = null;
						if (array_key_exists($extension, $report)) {
							foreach ($report[$extension] as $ext_report_property) {
								if ($ext_report_property['name'] == $feature['name']) {
									$curr_val = $ext_report_property['value'];
									break;
								}
							}
						}
						if (($index > 0) && ($curr_val !== null) && ($curr_val !== $last_val)) {
							$diff = true;
						}
						$last_val = $curr_val;
					};
					if ($curr_val !== null) {
						$displayvalue = getPropertyDisplayValue($feature['name'], $curr_val);
						$html .= "<td>$displayvalue</td>";
					} else {
						$html .= "<td class='na'>n.a.</td>";
					}
				}
				$html = "<tr class='" . ($diff ? "diff" : "same") . "'><td class='subkey'>" . ($diff ? $report_compare->getDiffIcon() : "") . $feature['name'] . "</td><td>" . $extension . "</td>" . $html . "</tr>";
				echo $html;
			}
		}
	}

	$report_compare->endTable();
	$report_compare->endTab();
}

$display_tabs = ($report_compare->flags->has_vulkan_1_1_properties || $report_compare->has_vulkan_1_2_properties || $report_compare->flags->has_extended_properties);
if ($display_tabs) {
	echo "<div>";
	echo "	<ul class='nav nav-tabs nav-level1'>";
	echo "		<li class='active'><a data-toggle='tab' href='#properties_core_10'>Core 1.0</a></li>";
	if ($report_compare->flags->has_vulkan_1_1_properties) {
		echo "<li><a data-toggle='tab' href='#properties_core_11'>Core 1.1</a></li>";
	}
	if ($report_compare->flags->has_vulkan_1_2_properties) {
		echo "<li><a data-toggle='tab' href='#properties_core_12'>Core 1.2</a></li>";
	}
	if ($report_compare->flags->has_vulkan_1_3_properties) {
		echo "<li><a data-toggle='tab' href='#properties_core_13'>Core 1.3</a></li>";
	}
	if ($report_compare->flags->has_extended_properties) {
		echo "<li><a data-toggle='tab' href='#properties_extensions'>Extensions</a></li>";
	}
	echo "	</ul>";
	echo "</div>";
	echo "<div class='tab-content'>";
}

insertCore10Properties($report_compare, '1.0');
if ($report_compare->flags->has_vulkan_1_1_properties) {
	insertCoreProperties($report_compare, '1.1');
}
if ($report_compare->flags->has_vulkan_1_2_properties) {
	insertCoreProperties($report_compare, '1.2');
}
if ($report_compare->flags->has_vulkan_1_3_properties) {
	insertCoreProperties($report_compare, '1.3');
}

if ($report_compare->flags->has_extended_properties) {
	insertExtensionProperties($report_compare);
}
?>
</div>