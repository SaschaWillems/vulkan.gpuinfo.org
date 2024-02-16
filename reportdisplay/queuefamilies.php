<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2024 by Sascha Willems (www.saschawillems.de)
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
?>

<?php
$data = $report->fetchQueueFamilies();
?>

<table id='devicequeues' class='table table-striped table-bordered table-hover table-header-rotated queue-family-table'>
	<thead>
		<tr>
			<th class='caption' style='border-right: 0px;'>Queue family</th>
			<th class='caption rotate-45'>
				<div><span>Queue count</span></div>
			</th>
			<th class='caption rotate-45'>
				<div><span>timestampValidBits</span></div>
			</th>
			<th class='caption rotate-45'>
				<div><span>minImageTransferGranularity.width</span></div>
			</th>
			<th class='caption rotate-45'>
				<div><span>minImageTransferGranularity.height</span></div>
			</th>
			<th class='caption rotate-45'>
				<div><span>minImageTransferGranularity.depth</span></div>
			</th>
			<th class='caption rotate-45'>
				<div><span>Supports present</span></div>
			</th>
			<?php
			foreach ($queue_flag_bits as $key => $value) {
				echo "<th class='caption rotate-45'><div><span>$value</span></div></th>";
			}
			?>
		</tr>
	</thead>
	<tbody>
		<?php
		if ($data) {
			foreach ($data as $index => $queue_family) {
				echo "<tr>";
				echo "<td class='queue-family-name'>Queue family $index</td>";
				echo "<td class='rotated-table-column'>" . $queue_family["count"] . "</td>";
				echo "<td class='rotated-table-column'>" . $queue_family["timestampValidBits"] . "</td>";
				echo "<td class='rotated-table-column'>" . $queue_family["minImageTransferGranularity.width"] . "</td>";
				echo "<td class='rotated-table-column'>" . $queue_family["minImageTransferGranularity.height"] . "</td>";
				echo "<td class='rotated-table-column'>" . $queue_family["minImageTransferGranularity.depth"] . "</td>";
				echo "<td class='format-table-support'><img src='images/icons/" . ($queue_family["supportsPresent"] ? 'check' : 'missing') . ".png' width='16px'></td>";
				foreach ($queue_flag_bits as $flag_enum => $value) {
					echo "<td class='format-table-support'>";
					$icon = ($queue_family["flags"] & $flag_enum) ? 'check' : 'missing';
					$value = ($queue_family["flags"] & $flag_enum) ? 1 : 0;
					echo "<img src='/images/icons/$icon.png' width='16px'>";
					// Hidden span with value so column can be sorted
					echo "<span style='display:none;'>$value</span>";					
					echo "</td>";
				}
				echo "</tr>";
			}
		}
		?>
	</tbody>
</table>