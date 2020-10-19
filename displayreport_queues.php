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
?>
<table id='devicequeues' class='table table-striped table-bordered table-hover reporttable'>
	<thead>
		<tr>
			<td class='caption'>Property</td>
			<td class='caption'>Value</td>
			<td></td>
		</tr>
	</thead>
	<tbody>
	<?php		
		$data = $report->fetchQueueFamilies();
		$index = 0;
		if ($data) {	
			foreach($data as $queue_family) {
				foreach($queue_family as $key => $value) {
					if (in_array($key, ['id', 'reportid'])) {
						continue;
					}
					echo "<tr>";
					if ($key == 'count') {
						$key = 'queueCount';
					}
					echo "<td style='width: 25%;' class='subkey'>$key</td>";
					if ($key == 'flags') {
						echo "<td>";
						$flags = getQueueFlags($value);
						listFlags($flags);
						echo "</td>";
					} else {
						echo "<td>$value</td>";
					}
					echo "<td>Queue family $index</td>";
					echo "</tr>";
				}
				$index++;				
			}		
		}
	?>
	</tbody>
</table>