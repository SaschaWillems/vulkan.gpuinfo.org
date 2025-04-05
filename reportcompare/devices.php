<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2025 by Sascha Willems (www.saschawillems.de)
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

$device_info_field_aliases = [
	'displayname' => 'Name',
	'devicetype' => 'Type',
	'driverversion' => 'Driver version',
	'apiversion' => 'API Version',
	'osname' => 'Name',
	'osarchitecture' => 'Architecture',
	'osversion' => 'Version'
];

// Gather data into arrays
$column = $captions = $groups = [];

$compare_device_infos = $report_compare->fetchDeviceInfo();

for ($i = 0; $i < count($compare_device_infos); $i++) {
	$row = $compare_device_infos[$i];
	$reportdata = [];
	$group = "Device";
	foreach ($row as $key => $value) {
		$display_key = $key;
		$display_value = $value;
		switch ($key) {
			case 'vendorid':
			case 'deviceid':
				continue 2;
			case 'osname':
				$group = 'Platform';
				$display_value = ucfirst($value);
				break;
			case 'submitter':
				$display_key = 'Submitted by';
				$display_value = '<a href="listreports.php?submitter=' . $value . '">' . $value . '</a>';
				$group = 'Report';
				break;
			case 'submissiondate':
				$display_key = 'Submitted at';
				break;
			case 'displayname':
				$display_value = '<a href="listreports.php?displayname=' . $value . '">' . $value . '</a>';
				break;
			case 'lastupdate':
				$display_key = 'Last update at';
				break;
		}
		if (array_key_exists($key, $device_info_field_aliases)) {
			$display_key = $device_info_field_aliases[$key];
		}
		$reportdata[] = $display_value;
		$captions[] = ucfirst($display_key);
		$groups[] = $group;
	}
	$column[] = $reportdata;
}

$report_compare->beginTable('comparedevices');
$report_compare->insertTableHeader('', true, false);

for ($i = 0; $i < count($column[0]); $i++) {
	$empty_row = true;
	for ($j = 0; $j < count($column); $j++) {
		if ($column[$j][$i] !== '') {
			$empty_row = false;
			break;
		}
	}
	if ($empty_row) {
		continue;
	}
	echo "<tr>";
	echo "<td class='subkey'>".$captions[$i]."</td>";
	echo "<td>". $groups[$i]."</td>";
	for ($j = 0; $j < sizeof($column); $j++) {
		echo "<td>".$column[$j][$i]."</td>";
	}
	echo "</tr>";
}

$report_compare->endTable();
