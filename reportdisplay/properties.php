<?php

/** 		
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2023 by Sascha Willems (www.saschawillems.de)
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
function insertCore10Properties($report)
{
	$report->beginTab('properties_core_10', true);
	$report->beginTable('table_properties_core_10', ['Property', 'Value', 'Category']);
	$core_properties = $report->fetchCoreProperties('1.0');
	if ($core_properties) {
		$category = 'Properties';
		foreach ($core_properties as $key => $value) {
			if ($key == 'reportid') {
				continue;
			}
			if (strpos($key, 'subgroupProperties') === 0) {
				continue;
			}
			if (strpos($key, 'residency') === 0) {
				$category = 'Sparse properties';
			}
			$displayvalue = getPropertyDisplayValue($key, $value);
			if (strpos($key, 'subgroupProperties') === 0) {
				$key = str_replace('subgroupProperties.', '', $key);
			}
			echo "<tr><td class='subkey'>$key</td>";
			echo "<td>$displayvalue</td>";
			echo "<td>$category</td>";
			echo "</tr>";
		}
	}
	// Core 1.0 limits
	$core_limits = $report->fetchLimits();
	if ($core_limits) {
		foreach($core_limits as $key => $value) {
			if ($key == 'reportid') {
				continue;
			}
			$displayvalue = getPropertyDisplayValue($key, $value);			
			echo "<tr><td class='subkey'>$key</td>";
			echo "<td>$displayvalue</td>";
			echo "<td>Limits</td>";
			echo "</tr>";			
		}
	}	
	$report->endTable();
	$report->endTab();
}

function insertCoreProperties($report, $version)
{
	$report->beginTab('properties_core_' . str_replace('.', '', $version), $version == '1.0');
	$report->beginTable('table_properties_core_' . str_replace('.', '', $version), ['Property', 'Value']);
	$core_properties = $report->fetchCoreProperties($version);
	if ($core_properties) {
		foreach ($core_properties as $key => $value) {
			if ($key == 'reportid') {
				continue;
			}
			// @todo: comment why the alias
			$display_key = $key;
			if (substr($key, 0, 3) == 'idp') {
				$display_key = 'integerDotProduct'.substr($display_key, 3);
			}
			$displayvalue = getPropertyDisplayValue($display_key, $value);
			echo "<tr><td class='subkey'>$display_key</td>";
			echo "<td>$displayvalue</td>";
			echo "</tr>";
		}
	} else {
		// If the device has no dedicated 1.1 properties (only available if Vulkan 1.2 is supported), display sub group properties
		if (($report->apiversion->major >= 1) && ($report->apiversion->minor >= 1)) {
			$subgroup_properties = $report->fetchSubgroupProperties();
			foreach ($subgroup_properties as $key => $value) {
				$displayvalue = getPropertyDisplayValue($key, $value);
				echo "<tr><td class='subkey'>$key</td>";
				echo "<td>$displayvalue</td>";
				echo "</tr>";
			}
		}
	}
	$report->endTable();
	$report->endTab();
}

function insertExtensionProperties($report)
{
	$report->beginTab('properties_extensions', false);
	$report->beginTable('table_properties_extensions', ['Property', 'Value', 'Extension']);
	$extension_properties = $report->fetchExtensionProperties();
	if ($extension_properties) {
		foreach ($extension_properties as $extension_property) {
			$key = $extension_property['name'];
			$value = $extension_property['value'];
			$displayvalue = $value;
			$displayvalue = getPropertyDisplayValue($key, $value);
			echo "<tr><td class='subkey'>$key</td><td>";
			echo $displayvalue;
			echo "<td>" . $extension_property['extension'] . "</td>";
			echo "</td></tr>";
		}
	}
	$report->endTable();
	$report->endTab();
}

$display_tabs = ($report->flags->has_vulkan_1_1_properties || $report->has_vulkan_1_2_properties || $report->flags->has_extended_properties);
if ($display_tabs) {
	echo "<div>";
	echo "	<ul class='nav nav-tabs nav-level1'>";
	echo "		<li class='active'><a data-toggle='tab' href='#properties_core_10'>Core 1.0</a></li>";
	if ($report->flags->has_vulkan_1_1_properties) {
		echo "<li><a data-toggle='tab' href='#properties_core_11'>Core 1.1</a></li>";
	}
	if ($report->flags->has_vulkan_1_2_properties) {
		echo "<li><a data-toggle='tab' href='#properties_core_12'>Core 1.2</a></li>";
	}
	if ($report->flags->has_vulkan_1_3_properties) {
		echo "<li><a data-toggle='tab' href='#properties_core_13'>Core 1.3</a></li>";
	}
	if ($report->flags->has_extended_properties) {
		echo "<li><a data-toggle='tab' href='#properties_extensions'>Extensions</a></li>";
	}
	echo "	</ul>";
	echo "</div>";
	echo "<div class='tab-content'>";
}

insertCore10Properties($report);
if ($report->flags->has_vulkan_1_1_properties) {
	insertCoreProperties($report, '1.1');
}
if ($report->flags->has_vulkan_1_2_properties) {
	insertCoreProperties($report, '1.2');
}
if ($report->flags->has_vulkan_1_3_properties) {
	insertCoreProperties($report, '1.3');
}
if ($report->flags->has_extended_properties) {
	insertExtensionProperties($report);
}

if ($display_tabs) {
	echo "</div>";
}
