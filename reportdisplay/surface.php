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
<div>
	<ul class='nav nav-tabs nav-level1'>
		<li class='active'><a data-toggle='tab' href='#surfaceproperties'>Surface properties</a></li>
		<li><a data-toggle='tab' href='#surfaceformats'>Surface formats <span class='badge'><?php echo $surfaceformatscount ?></span></a></li>
		<li><a data-toggle='tab' href='#presentmodes'>Present modes <span class='badge'><?php echo $surfacepresentmodescount ?></span></a></li>
	</ul>
</div>
<div class='tab-content'>
	<!-- Surface properties -->
	<div id='surfaceproperties' class='tab-pane fade in active reportdiv'>
		<table id='devicesurfaceproperties' class='table table-striped table-bordered table-hover reporttable'>
			<thead>
				<tr>
					<td class='caption'>Property</td>
					<td class='caption'>Value</td>
				</tr>
			</thead>
			<tbody>
				<?php
				$surface_properties = $report->fetchSurfaceProperties();
				if ($surface_properties) {
					foreach ($surface_properties as $key => $value) {
						if ($key == "reportid")
							continue;
						echo "<tr><td class='key'>" . $key . "</td><td>";
						switch ($key) {
							case "supportedUsageFlags":
								listFlags(getImageUsageFlags($value));
								break;
							case "supportedTransforms":
								listFlags(getSurfaceTransformFlags($value));
								break;
							case "supportedCompositeAlpha":
								listFlags(getCompositeAlphaFlags($value));
								break;
							default:
								echo $value;
						}
					}
					echo "</td></tr>";
				}
				?>
			</tbody>
		</table>
	</div>

	<!-- Surface formats	 -->
	<div id='surfaceformats' class='tab-pane fade reportdiv'>
		<table id='devicesurfaceformats' class='table table-striped table-bordered table-hover reporttable'>
			<thead>
				<tr>
					<td class='caption'>Index</td>
					<td class='caption'>Format</td>
					<td class='caption'>Colorspace</td>
				</tr>
			</thead>
			<tbody>
				<?php
				$surface_formats = $report->fetchSurfaceFormats();
				if ($surface_formats) {
					foreach ($surface_formats as $index => $surface_format) {
						echo "<tr>";
						echo "<td>$index</td>";
						echo "<td>" . $surface_format['format'] . "</td>";
						echo "<td>" . getColorSpace($surface_format['colorspace']) . "</td>";
						echo "</tr>";
					}
				}
				?>
			</tbody>
		</table>
	</div>

	<!-- Present modes	 -->
	<div id='presentmodes' class='tab-pane fade reportdiv'>
		<table id='devicepresentmodes' class='table table-striped table-bordered table-hover reporttable'>
			<thead>
				<tr>
					<td class='caption'>Present mode</td>
				</tr>
			</thead>
			<tbody>
				<?php
				try {
					$stmnt = DB::$connection->prepare("SELECT presentmode from devicesurfacemodes where reportid = :reportid");
					$stmnt->execute(array(":reportid" => $reportID));
					while ($row = $stmnt->fetch(PDO::FETCH_NUM)) {
						echo "<tr>";
						echo "<td class='key'>" . getPresentMode($row[0]) . "</td>";
						echo "</tr>";
					}
				} catch (Exception $e) {
					die('Error while fetching report surface present modes');
					DB::disconnect();
				}
				?>
			</tbody>
		</table>
	</div>

</div>