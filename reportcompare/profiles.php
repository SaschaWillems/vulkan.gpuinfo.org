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

$report_compare->beginTable("compareprofiles");
$report_compare->insertTableHeader("Profiles");

$compare_extensions = $report_compare->fetchProfiles();

$colspan = count($reportids) + 1;

foreach ($compare_extensions->captions as $extension) {
	// Check if missing in at least one report
	$missing = false;
	$index = 0;
	foreach ($reportids as $repid) {
		if (!in_array($extension, $compare_extensions->data[$index])) {
			$missing = true;
		}
		$index++;
	}
	$className = "same";
	$index = 0;
	$diff = false;
	foreach ($reportids as $repid) {
		if (!in_array($extension, $compare_extensions->data[$index])) {
			$className = "diff";
		}
		$index++;
	}
	echo "<tr class='$className'><td class='subkey'>".($missing ? $report_compare->getDiffIcon() : "")."$extension</td>";
	$index = 0;
	foreach ($reportids as $repid) {
		$icon = 'missing';
		if (in_array($extension, $compare_extensions->data[$index])) {
			$icon = 'check';
		}
		echo "<td><img src='images/icons/$icon.png' width=16px></td>";
		$index++;
	}
	echo "</tr>";
}

$report_compare->endTable();
