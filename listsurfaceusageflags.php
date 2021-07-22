<?php

/** 		
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright 2016-2021 (C) by Sascha Willems (www.saschawillems.de)
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

include 'pagegenerator.php';
include './database/database.class.php';
include './includes/functions.php';
include './includes/constants.php';

$platform = 'all';
if (isset($_GET['platform'])) {
	$platform = GET_sanitized('platform');
}

PageGenerator::header("Surface usage flags");
?>

<div class='header'>
	<?php echo "<h4>Surface usage flag support on ".PageGenerator::platformInfo($platform); ?>
</div>

<center>
	<?php 
		PageGenerator::platformNavigation('listsurfaceusageflags.php', $platform, true); ?>

		<div class='tablediv' style='width:auto; display: inline-block;'>
		<table id="presentmodes" class="table table-striped table-bordered table-hover reporttable responsive with-platform-selection">
			<thead>
				<tr>
					<th></th>
					<th colspan=2 style="text-align: center;">Device coverage</th>
				</tr>
				<th>Mode</th>
				<th style="text-align: center;"><img src='images/icons/check.png' width=16px></th>
				<th style="text-align: center;"><img src='images/icons/missing.png' width=16px></th>
				</th>
			</thead>
			<tbody>
				<?php
				try {
					$os_filter = null;
					$params = [];
					if ($platform !== 'all') {
						$params['ostype'] = ostype($platform);
						$os_filter = 'AND r.ostype = :ostype';
					}					
					DB::connect();
					// Can't use getDeviceCount here, as some devices that should have no rows in devicesurfacecapabilities (probably custom builds)
					$deviceCount = DB::getCount("SELECT count(distinct(r.displayname)) from reports r join devicesurfacecapabilities d on d.reportid = r.id where r.version >= '1.2' $os_filter", $params);
					foreach ($surface_usage_flags as $enum => $flag_name) {
						$sql = "SELECT
							count(distinct(r.displayname)) as coverage
							from devicesurfacecapabilities dsf
							join reports r on r.id = dsf.reportid
							where supportedUsageFlags & $enum = $enum
							$os_filter";
						$stmnt = DB::$connection->prepare($sql);
						$stmnt->execute($params);
						$result = $stmnt->fetch(PDO::FETCH_ASSOC);
						$coverageLink = "listdevicescoverage.php?surfaceusageflag=$flag_name&platform=$platform";
						$coverage = $result['coverage'] / $deviceCount * 100.0;
						if ($coverage > 0) {
							echo "<tr>";
							echo "<td class='value'>$flag_name</td>";
							echo "<td class='text-center'><a class='supported' href='$coverageLink'>" . round($coverage, 1) . "<span style='font-size:10px;'>%</span></a></td>";
							echo "<td class='text-center'><a class='na' href='$coverageLink&option=not'>" . round(100 - $coverage, 1) . "<span style='font-size:10px;'>%</span></a></td>";
							echo "</tr>";
						}
					}

					// $sql = "SELECT
					// 	vkpm.name as mode,
					// 	count(distinct(ifnull(r.displayname, dp.devicename))) as coverage
					// 	from devicesurfacemodes dsm
					// 	join reports r on r.id = dsm.reportid
					// 	join VkPresentMode vkpm on vkpm.value = dsm.presentmode
					// 	join deviceproperties dp on dp.reportid = r.id						
					// 	$os_filter
					// 	group by mode";
					// $result = DB::$connection->prepare($sql);
					// $result->execute($params);
					// foreach ($result as $row) {
					// 	$coverageLink = "listdevicescoverage.php?" . $type . "surfacepresentmode=" . $row['mode'] . "&platform=$platform";
					// 	$coverage = $row['coverage'] / $deviceCount * 100.0;
					// 	echo "<tr>";
					// 	echo "<td class='value'>" . $row['mode'] . "</td>";
					// 	echo "<td class='value'><a class='supported' href='$coverageLink'>" . round($coverage, 1) . "<span style='font-size:10px;'>%</span></a></td>";
					// 	echo "<td class='value'><a class='na' href='$coverageLink&option=not'>" . round(100 - $coverage, 1) . "<span style='font-size:10px;'>%</span></a></td>";
					// 	echo "</tr>";
					// }
				} catch (PDOException $e) {
					echo "<b>Error while fetcthing data!</b><br>";
				}
				DB::disconnect();
				?>
			</tbody>
		</table>
	</div>

	<script>
		$(document).ready(function() {
			var table = $('#presentmodes').DataTable({
				"pageLength": -1,
				"paging": false,
				"stateSave": false,
				"searchHighlight": true,
				"dom": 'f',
				"bInfo": false,
				"order": [
					[0, "asc"]
				]
			});

			$("#searchbox").on("keyup search input paste cut", function() {
				table.search(this.value).draw();
			});

		});
	</script>

	<?php PageGenerator::footer(); ?>
</center>
</body>

</html>