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

$platform = 'all';
if (isset($_GET['platform'])) {
	$platform = GET_sanitized('platform');
}

PageGenerator::header("Core 1.1 properties");
?>

<div class='header'>
	<?php echo "<h4>Core 1.1 properties for ".PageGenerator::platformInfo($platform) ?>
</div>
<div class="alert alert-info" role="alert" style="text-align: center">
	<b>Note:</b> Data is based on reports submitted or updated with version 3.0 or newer of the Hardware Capability Viewer and does not contain reports from earlier versions.
</div>

<center>
	<?php PageGenerator::platformNavigation('listpropertiescore11.php', $platform, true); ?>

	<div class='tablediv' style='width:auto; display: inline-block;'>
		<table id="properties" class="table table-striped table-bordered table-hover responsive with-platform-selection">
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
					'deviceLUIDValid',
					'subgroupQuadOperationsInAllStages',
					'protectedNoFault'
				];
				$ignore_columns = [];
				DB::connect();
				try {
					$os_filter = null;
					$params = [];
					if ($platform !== 'all') {
						$params['ostype'] = ostype($platform);
						$os_filter = 'WHERE r.ostype = :ostype';
					}
					$deviceCount = DB::getCount("SELECT count(distinct(displayname)) from reports r join deviceproperties11 dp on dp.reportid = id $os_filter", $params);
					if ($deviceCount > 0) {
						// Collect coverage numbers
						$columns = [];
						foreach ($coverage_columns as $column) {
							$columns[] = "max(dp.`$column`) as `$column`";
						}

						$supportedCounts = [];
						$stmnt = DB::$connection->prepare(
							"SELECT r.displayname as device, " . implode(',', $columns) . " FROM deviceproperties11 dp join reports r on r.id = dp.reportid $os_filter group by device"
						);
						$stmnt->execute($params);
						while ($row = $stmnt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
							foreach ($row as $key => $value) {
								if (strcasecmp($key, 'device') != 0) {
									$supportedCounts[$key] += $value;
								}
							}
						}

						// Collect properties from column names
						$sql = "SELECT COLUMN_NAME from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME = 'deviceproperties11' and COLUMN_NAME not in ('reportid')";
						$stmnt = DB::$connection->prepare($sql);
						$stmnt->execute();

						while ($row = $stmnt->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
							if (in_array($row[0], $ignore_columns)) {
								continue;
							}
							$has_coverage = in_array($row[0], $coverage_columns);
							$link = "<a href='displaycoreproperty.php?core=1.1&name=" . $row[0] . "&platform=$platform'>";
							echo "<tr>";
							echo "<td>" . $row[0] . "</a></td>";
							echo "<td class='text-center'>" . ($has_coverage ? 'Coverage' : 'Values') . "</td>";
							if ($has_coverage) {
								$coverageLink = "listdevicescoverage.php?core=1.1&coreproperty=" . $row[0] . "&platform=$platform";
								$coverage = $deviceCount > 0 ? round($supportedCounts[$row[0]] / $deviceCount * 100, 1) : 0;
								echo "<td class='text-center'><a class='supported' href=\"$coverageLink\">$coverage<span style='font-size:10px;'>%</span></a></td>";
							} else {
								echo "<td class='text-center'>" . $link . "Listing</a></td>";
							}
							echo "</tr>";
						}
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
			var table = $('#properties').DataTable({
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