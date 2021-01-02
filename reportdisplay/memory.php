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
?>
<div id='memorytypes' class='tab-pane fade in active reportdiv'>
	<table id='devicememory' class='table table-striped table-bordered table-hover reporttable'>
		<thead>
			<tr>
				<td class='caption'>Property</td>
				<td class='caption'>Value</td>
				<td></td>
			</tr>
		</thead>
		<tbody>
			<?php
			$data = $report->fetchMemoryHeaps();
			if ($data) {
				$heap_index = 0;
				foreach ($data as $memory_heap) {
					foreach ($memory_heap as $key => $value) {
						if ($key == 'id') {
							continue;
						}
						echo "<tr>";
						echo "<td class='subkey' style='width: 25%'>" . ucfirst($key) . "</td>";
						echo "<td>";
						switch ($key) {
							case 'size':
								echo number_format($value) . " bytes";
								break;
							case 'flags':
								$flags = getMemoryHeapFlags($value);
								if (sizeof($flags) > 0) {
									foreach ($flags as $flag) {
										echo $flag . "<br>";
									}
								} else {
									echo "none";
								}
								break;
							default:
								echo $value;
						}
						echo "</td>";
						echo "<td>Memory heap $heap_index</td>";
						echo "</tr>";
					}
					$memory_types = $report->fetchMemoryTypes($heap_index);
					if ($memory_types) {
						foreach ($memory_types as $memory_type_index => $memory_types) {
							echo "<tr>";
							echo "<td class='subkey' style='width: 25%'>Memory type $memory_type_index</td>";
							echo "<td>";
							$memoryFlags = getMemoryTypeFlags($memory_types['propertyflags']);
							if (sizeof($memoryFlags) > 0) {
								foreach ($memoryFlags as $flag) {
									echo $flag . "<br>";
								}
							} else {
								echo "none";
							}
							echo "</td>";
							echo "<td>Memory heap $heap_index</td>";
							echo "</tr>";
						}
					}
					$heap_index++;
				}
			}
			?>
			</tody>
	</table>
</div>