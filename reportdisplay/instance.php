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

$instance_extensions = $report->fetchInstanceExtensions();
$instance_extensions_count = $instance_extensions ? count($instance_extensions) : 0;

$instance_layers = $report->fetchInstanceLayers();
$instance_layers_count = $instance_layers ? count($instance_layers) : 0;

?>
<div>
	<ul class='nav nav-tabs nav-level1'>
		<li class='active'><a data-toggle='tab' href='#instanceextensions'>Extensions <span class='badge'><?php echo $instance_extensions_count ?></span></a></li>
		<li><a data-toggle='tab' href='#instancelayers'>Layers <span class='badge'><?php echo $instance_layers_count ?></span></a></li>
	</ul>
</div>

<div class="tab-content">

	<div id='instanceextensions' class='tab-pane fade in active reportdiv'>
		<table id='deviceinstanceextensions' class='table table-striped table-bordered table-hover reporttable'>
			<thead>
				<tr>
					<td class='caption'>Extension</td>
					<td class='caption'>Version</td>
				</tr>
			</thead>
			<tbody>
				<?php
				if ($instance_extensions) {
					foreach ($instance_extensions as $ext) {
						echo "<tr><td><a href='listreports.php?instanceextension=" . $ext["name"] . "'>" . $ext["name"] . "</a></td>";
						echo "<td>" . versionToString($ext["specversion"]) . "</td>";
						echo "</tr>\n";
					}
				}
				?>
			</tbody>
		</table>
	</div>

	<div id='instancelayers' class='tab-pane reportdiv'>
		<table id='deviceinstancelayers' class='table table-striped table-bordered table-hover reporttable'>
			<thead>
				<tr>
					<td class='caption'>Layername</td>
					<td class='caption'>Spec</td>
					<td class='caption'>Implementation</td>
				</tr>
			</thead>
			<tbody>
				<?php
				if ($instance_layers) {
					foreach ($instance_layers as $layer) {
						echo "<tr><td><a href='listreports.php?instancelayer=" . $layer["name"] . "'>" . $layer["name"] . "</a></td>";
						echo "<td>" . versionToString($layer["specversion"]) . "</td>";
						echo "<td>" . versionToString($layer["implversion"]) . "</td>";
						echo "</tr>\n";
					}
				}
				?>
			</tbody>
		</table>
	</div>

</diV>