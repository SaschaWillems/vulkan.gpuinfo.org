<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *
 * Copyright (C) 2016-2021 by Sascha Willems (www.saschawillems.de)
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

$report_compare->beginTable("table_queue_families");
$report_compare->insertTableHeader(null, true);

$compare_queue_families = $report_compare->fetchQueueFamilies();

$max_queue_family_count = 0;
foreach ($compare_queue_families->data as $report_queue_families) {
	if (count($report_queue_families) > $max_queue_family_count) {
		$max_queue_family_count = count($report_queue_families);
	}
}

for ($i = 0; $i < $max_queue_family_count; $i++) {
	$queue_family = "Queue family $i";
	// Queue counts
	echo "<tr><td class='subkey'>Count</td><td>$queue_family</td>";
	foreach ($compare_queue_families->data as $report_queue_families) {
		if (count($report_queue_families) > $i) {
			echo "<td>" . $report_queue_families[$i]->count . "</td>";
		} else {
			echo "<td><font color=#BABABA>n/a</font></td>";
		}
	}
	echo "</tr>";
	// Flags
	echo "<tr><td class='subkey'>Flags</td><td>$queue_family</td>";
	foreach ($compare_queue_families->data as $report_queue_families) {
		if (count($report_queue_families) > $i) {
			echo "<td>";
			$flags = getQueueFlags($report_queue_families[$i]->flags);
			if (sizeof($flags) > 0) {
				foreach ($flags as $flag) {
					echo $flag . "<br>";
				}
			} else {
				echo "none";
			}
			echo "</td>";
		} else {
			echo "<td><font color=#BABABA>n/a</font></td>";
		}
	}
	echo "</tr>";
}

$report_compare->endTable();