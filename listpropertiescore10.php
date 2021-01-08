<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2021 Sascha Willems (www.saschawillems.de)
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

require 'pagegenerator.php';
require './database/database.class.php';
require './includes/functions.php';
require './includes/constants.php';

$platform = "windows";
if (isset($_GET['platform'])) {
	$platform = $_GET['platform'];
}

PageGenerator::header("Core 1.0 properties");
?>

<div class='header'>
	<?php echo "<h4>Core 1.0 properties for ".PageGenerator::platformInfo($platform) ?>
</div>

<center>
	<?php PageGenerator::platformNavigation('listpropertiescore10.php', $platform); ?>

	<div class='tablediv' style='width:auto; display: inline-block;'>
		<table id="features" class="table table-striped table-bordered table-hover responsive" style='width:auto;'>
			<thead>
				</tr>
				<th>Property</th>
				<th style="text-align: center;">Type</th>
				<th style="text-align: center;"></th>
				</tr>
			</thead>
			<tbody>
				<?php
				$coverage_columns = [
					'residencyAlignedMipSize',
					'residencyNonResidentStrict',
					'residencyStandard2DBlockShape',
					'residencyStandard2DMultisampleBlockShape',
					'residencyStandard3DBlockShape',
					'subgroupProperties.quadOperationsInAllStages',
				];
				$ignore_columns = [
					'headerversion',
					'driverversionraw',
					'pipelineCacheUUID',
					'apiversionraw',
					'productManufacturer',
					'productModel'
				];
				DB::connect();
				try {
					$viewDeviceCount = DB::$connection->prepare("SELECT * from viewDeviceCount");
					$viewDeviceCount->execute();
					$deviceCounts = $viewDeviceCount->fetch(PDO::FETCH_ASSOC);

					// Collect coverage numbers
					$sqlColumns = '';
					foreach ($coverage_columns as $column) {
						$sqlColumns .= "max(dp.`$column`) as `$column`,";
					}

					$supportedCounts = [];
					$stmnt = DB::$connection->prepare(
						"SELECT ifnull(r.displayname, dp.devicename) as device, "
							. substr($sqlColumns, 0, -1) .
							" FROM deviceproperties dp join reports r on r.id = dp.reportid where r.ostype = :ostype group by device"
					);
					$stmnt->execute(['ostype' => ostype($platform)]);
					while ($row = $stmnt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
						foreach ($row as $key => $value) {
							if (strcasecmp($key, 'device') != 0) {
								$supportedCounts[$key] += $value;
							}
						}
					}

					// Collect properties from column names
					$sql = "SELECT COLUMN_NAME from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME = 'deviceproperties' and COLUMN_NAME not in ('reportid')";
					$stmnt = DB::$connection->prepare($sql);
					$stmnt->execute();

					while ($row = $stmnt->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
						if (in_array($row[0], $ignore_columns)) {
							continue;
						}
						$has_coverage = in_array($row[0], $coverage_columns);
						$sqlColumns .= "max(" . $row[0] . ") as `$row[0]`," . PHP_EOL;
						$link = "<a href='displaycoreproperty.php?name=" . $row[0] . "&platform=$platform'>";
						echo "<tr>";
						echo "<td>" . $row[0] . "</a></td>";
						echo "<td class='text-center'>" . ($has_coverage ? 'Coverage' : 'Values') . "</td>";
						if ($has_coverage) {
							$coverageLink = "listdevicescoverage.php?coreproperty=" . $row[0] . "&platform=$platform";
							$coverage = round($supportedCounts[$row[0]] / $deviceCounts[$platform] * 100, 1);
							echo "<td class='text-center'><a class='supported' href=\"$coverageLink\">$coverage<span style='font-size:10px;'>%</span></a></td>";
						} else {
							echo "<td class='text-center'>" . $link . "Listing</a></td>";
						}
						echo "</tr>";
					}

					// Collect limits
					$sql = "SELECT COLUMN_NAME as name, (SELECT feature from limitrequirements where limitname = name) as requirement from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME = 'devicelimits' and COLUMN_NAME not in ('reportid')";
					$limits = DB::$connection->prepare($sql);
					$limits->execute();
					while ($limit = $limits->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
						echo "<tr>";
						echo "<td>$limit[0]</td>";
						echo "<td class='text-center'>Limit</td>";
						echo "<td><a href='displaydevicelimit.php?name=" . $limit[0] . "&platform=$platform'>Listing</a></td>";
						echo "</tr>";
					}
				} catch (PDOException $e) {
					echo "<b>Error while fetching data!</b><br>";
				}
				DB::disconnect();
				?>
			</tbody>
		</table>
	</div>

	<script>
		$(document).ready(function() {
			var table = $('#features').DataTable({
				"pageLength": -1,
				"paging": false,
				"stateSave": false,
				"searchHighlight": true,
				"dom": 'f',
				"bInfo": false,
				"order": [
					[0, "asc"]
				],
				"columnDefs": [{
					"targets": [1, 2]
				}]
			});
		});
	</script>

	<?php PageGenerator::footer(); ?>

</center>
</body>

</html>