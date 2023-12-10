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

function insertDeviceFormatTable($report_compare, $id, $format_data, $flags)
{
?>
	<table id='<?= $id ?>' class='table table-striped table-bordered table-hover table-header-rotated format-table'>
		<thead>
			<tr>
				<th class='caption' style='border-left: 0px; border-right: 0px;'>Format</th>
				<?php
				foreach ($flags as $key => $value) {
					echo "<th class='caption rotate-45'><div><span>$value</span></div></th>";
				}
				?>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ($format_data as $format_name => $format_support) {
				$format_supported = false;
				// Check if format is supported by at least one report
				foreach ($report_compare->device_infos as $device_index => $device) {
					if ($format_support[$device_index] != 0) {
						$format_supported = true;
						break;
					}
				}
				if (!$format_supported) {
					continue;
				}
				echo "<tr>";
				$class = 'default';
				echo "<td colspan='".(count($flags)+1)."' class='format-table-format-name'><span class='$class'>$format_name</span></td>";
				echo "</tr>";
				// Display flags per device 
				foreach ($report_compare->device_infos as $device_index => $device) {
					echo "<tr>";
					echo "<td class='format-table-device'>$device->name</td>";
					foreach ($flags as $flag_enum => $flag_name) {
						$icon = 'unsupported';
						if ($format_support[$device_index] != 0) {
							$icon = ($format_support[$device_index] & $flag_enum) ? 'check' : 'missing';
						}
						echo "<td class='format-table-support'>";
						echo "<img src='images/icons/$icon.png' width=16px>";
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
		<li class='active'><a data-toggle='tab' href='#formats_optimal'>Optimal tiling</a></li>
		<li><a data-toggle='tab' href='#formats_linear'>Linear tiling</a></li>
		<li><a data-toggle='tab' href='#formats_buffer'>Buffer</a></li>
	</ul>
</div>
<div class='tab-content'>

	<?php

	$available_formats = $report_compare->fetchAvailableFormats();

	// Pre-initialize array with all available formats
	$format_support = new ReportCompareFormatData;
	foreach ($available_formats as $format) {
		$format_name = $format['name'];
		foreach ($report_compare->report_ids as $report_id) {
			$format_support->linear[$format_name][] = null;
			$format_support->optimal[$format_name][] = null;
			$format_support->buffer[$format_name][] = null;
		}
	}
	// Populate array with report format suppot
	foreach ($report_compare->report_ids as $index => $report_id) {
		try {
			$stmnt = DB::$connection->prepare("SELECT vkf.name, lineartilingfeatures, optimaltilingfeatures, bufferfeatures from deviceformats df join VkFormat vkf on df.formatid = vkf.value where reportid = :reportid order by reportid asc");
			$stmnt->execute(["reportid" => $report_id]);
		} catch (PDOException $e) {
			die("Could not fetch device formats!");
		}
		while ($row = $stmnt->fetch(PDO::FETCH_ASSOC)) {
			$format_name = $row['name'];
			$format_support->linear[$format_name][$index] = $row['lineartilingfeatures'];
			$format_support->optimal[$format_name][$index] = $row['optimaltilingfeatures'];
			$format_support->buffer[$format_name][$index] = $row['bufferfeatures'];
		
		}
	}

	?>

	<div id='formats_optimal' class='tab-pane fade reportdiv'>
		<?php insertDeviceFormatTable($report_compare, 'table_deviceformats_optimal', $format_support->optimal, $device_format_flags_tiling); ?>
	</div>
	<div id='formats_linear' class='tab-pane fade in active reportdiv'>
		<?php insertDeviceFormatTable($report_compare, 'table_deviceformats_linear', $format_support->linear, $device_format_flags_tiling); ?>
	</div>
	<div id='formats_buffer' class='tab-pane fade reportdiv'>
		<?php insertDeviceFormatTable($report_compare, 'table_deviceformats_buffer', $format_support->buffer, $device_format_flags_buffer); ?>
	</div>

	<?php

	// Generate tables
	$colspan = count($reportids) + 1;

	$tab_names = ['formats_linear', 'formats_optimal', 'formats_buffer'];
	$featurearrays = array($linearfeatures, $optimalfeatures, $bufferfeatures);
	for ($i = 0; $i < sizeof($featurearrays); $i++) {
		$featurearray = $featurearrays[$i];
	}
	echo "</div>";
	?>