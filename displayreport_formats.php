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

	$device_format_flags_tiling = [
		0x0001 => "SAMPLED_IMAGE",
		0x0002 => "STORAGE_IMAGE",
		0x0004 => "STORAGE_IMAGE_ATOMIC",
		0x0080 => "COLOR_ATTACHMENT",
		0x0100 => "COLOR_ATTACHMENT_BLEND",
		0x0200 => "DEPTH_STENCIL_ATTACHMENT",
		0x0400 => "BLIT_SRC",
		0x0800 => "BLIT_DST",
		0x1000 => "SAMPLED_IMAGE_FILTER_LINEAR",
		0x4000 => "TRANSFER_SRC",
		0x8000 => "TRANSFER_DST",
	];

	$device_format_flags_buffer = [
		0x0008 => "UNIFORM_TEXEL_BUFFER",
		0x0010 => "STORAGE_TEXEL_BUFFER",
		0x0020 => "STORAGE_TEXEL_BUFFER_ATOMIC",
		0x0040 => "VERTEX_BUFFER",
	];

	$format_data = $report->fetchFormats();

	function insertDeviceFormatTable($id, $format_data, $column, $flags) {
?>
	<table id='<?=$id?>' class='table table-striped table-bordered table-hover table-header-rotated format-table'>
		<thead>
			<tr>
				<th class='caption' style='border-right: 0px;'>Format</th>
	<?php			
				foreach($flags as $key => $value) {
					echo "<th class='caption rotate-45'><div><span>$value</span></div></th>";
				}
	?>			
			</tr>
		</thead>
		<tbody>
		<?php
			if ($format_data) {
				foreach($format_data as $format) {
					$supported = ($format[$column] > 0);
					$class = $supported ? 'default' : 'format-unsupported';
					echo "<tr>";
					echo "<td class='subkey' style='text-align: left;'><span class='$class'>".$format["format"]."</span></td>";
					foreach($flags as $flag_enum => $flag_name) {
						echo "<td class='format-table-support'>";
						if ($format[$column] & $flag_enum) {
							echo "<img src='icon_check.png' width=16px>";
						} else {
							echo "<img src='".($supported ? 'icon_missing.png' : 'icon_missing_unsupported.png')."' width=16px>";
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