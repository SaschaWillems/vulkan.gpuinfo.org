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

$format_data = $report->fetchFormats();

function insertDeviceFormatTable($id, $format_data, $column, $flags)
{
?>
	<table id='<?= $id ?>' class='table table-striped table-bordered table-hover table-header-rotated format-table'>
		<thead>
			<tr>
				<th class='caption' style='border-right: 0px;'>Format</th>
				<?php
				foreach ($flags as $key => $value) {
					echo "<th class='caption rotate-45'><div><span>$value</span></div></th>";
				}
				?>
			</tr>
		</thead>
		<tbody>
			<?php
			if ($format_data) {
				foreach ($format_data as $format) {
					$supported = ($format[$column] > 0);
					$class = $supported ? 'default' : 'format-unsupported';
					echo "<tr>";
					echo "<td class='format-name'><span class='$class'>" . $format["format"] . "</span></td>";
					foreach ($flags as $flag_enum => $flag_name) {
						echo "<td class='format-table-support'>";
						if ($format[$column] & $flag_enum) {
							echo "<img src='icon_check.png' width=16px>";
						} else {
							echo "<img src='" . ($supported ? 'icon_missing.png' : 'icon_missing_unsupported.png') . "' width=16px>";
						}
						echo "</td>";
					}
					echo "</tr>";
				}
			}
			?>
		</tbody>
	</table>
<?php
}
?>

<div>
	<ul class='nav nav-tabs nav-level1'>
		<li class='active'><a data-toggle='tab' href='#formats_linear'>Linear tiling</a></li>
		<li><a data-toggle='tab' href='#formats_optimal'>Optimal tiling</a></li>
		<li><a data-toggle='tab' href='#formats_buffer'>Buffer</a></li>
	</ul>
</div>

<div class='tab-content'>
	<!-- Linear tiling features -->
	<div id='formats_linear' class='tab-pane fade in active reportdiv'>
		<?php insertDeviceFormatTable('deviceformats_linear', $format_data, 'lineartilingfeatures', $device_format_flags_tiling); ?>
	</div>
	<!-- Optimal tiling features -->
	<div id='formats_optimal' class='tab-pane fade reportdiv'>
		<?php insertDeviceFormatTable('deviceformats_optimal', $format_data, 'optimaltilingfeatures', $device_format_flags_tiling); ?>
	</div>
	<!-- Buffer features -->
	<div id='formats_buffer' class='tab-pane fade reportdiv'>
		<?php insertDeviceFormatTable('deviceformats_buffer', $format_data, 'bufferfeatures', $device_format_flags_buffer); ?>
	</div>
</div>