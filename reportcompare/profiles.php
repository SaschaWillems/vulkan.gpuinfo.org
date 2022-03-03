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

$compare_profiles = $report_compare->fetchProfiles();

foreach ($compare_profiles->captions as $profile) {
	// Check for the diff toggle: If the profile is supported by at least one reportt and less than the total count
	$supported_count = 0;
	$supported = false;
	foreach ($reportids as $index => $repid) {
	 	if (in_array($profile, $compare_profiles->data[$index])) {
	 		$supported_count++;
			$supported = true;
		}
	}
	$diff = $supported && ($supported_count < sizeof(($reportids)));
	$className = $diff ? 'diff' : 'same';
	echo "<tr class='$className'><td class='subkey'>".($diff ? $report_compare->getDiffIcon() : "")."$profile</td>";
	$index = 0;
	foreach ($reportids as $repid) {
		$icon = 'missing';
		if (in_array($profile, $compare_profiles->data[$index])) {
			$icon = 'check';
		}
		echo "<td><img src='images/icons/$icon.png' width=16px></td>";
		$index++;
	}
	echo "</tr>";
}

$report_compare->endTable();
