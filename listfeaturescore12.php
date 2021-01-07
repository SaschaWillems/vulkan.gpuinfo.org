<?php

/** 		
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2020 Sascha Willems (www.saschawillems.de)
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

PageGenerator::header("Core 1.2 features");
?>

<div class='header'>
	<?php echo "<h4>Core 1.2 feature coverage for".PageGenerator::platformInfo($platform) ?>
</div>
<div class="alert alert-info" role="alert" style="text-align: center">
	<b>Note:</b> Data is based on reports submitted or updated with version 3.0 or newer of the Hardware Capability Viewer and does not contain reports from earlier versions.
</div>

<center>
	<?php PageGenerator::platformNavigation('listfeaturescore12.php', $platform); ?>

	<div class='tablediv' style='width:auto; display: inline-block;'>
		<table id="features" class="table table-striped table-bordered table-hover responsive" style='width:auto;'>
			<thead>
				<tr>
					<th></th>
					<th colspan=3 style="text-align: center;">Device coverage</th>
				</tr>
				<th>Feature</th>
				<th style="text-align: center;"><img src='images/icons/check.png' width=16px></th>
				<th style="text-align: center;"><img src='images/icons/missing.png' width=16px></th>
				</tr>
			</thead>
			<tbody>
				<?php
				DB::connect();
				try {
					$deviceCount = DB::getCount("SELECT count(distinct(displayname)) from reports join devicefeatures12 dp on dp.reportid = id where ostype = :platform", ['platform' => ostype($platform)]);

					// Collect feature column names
					$sql = "SELECT COLUMN_NAME from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME = 'devicefeatures12' and COLUMN_NAME not in ('reportid')";
					$stmnt = DB::$connection->prepare($sql);
					$stmnt->execute();

					$features = [];
					$columns = [];
					while ($row = $stmnt->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
						$features[] = $row[0];
						$columns[] = "max(" . $row[0] . ") as $row[0]";
					}

					$supportedCounts = [];
					$stmnt = DB::$connection->prepare(
						"SELECT r.displayname as device, " . implode(',', $columns) . " FROM devicefeatures12 df join deviceproperties dp on dp.reportid = df.reportid join reports r on r.id = df.reportid where r.ostype = " . ostype($platform) . " group by device"
					);
					$stmnt->execute();
					while ($row = $stmnt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
						foreach ($row as $key => $value) {
							if (strcasecmp($key, 'device') != 0) {
								$supportedCounts[$key] += $value;
							}
						}
					}

					foreach ($features as $feature) {
						$coverageLink = "listdevicescoverage.php?core=1.2&feature=" . $feature . "&platform=$platform";
						$coverage = $deviceCount > 0 ? round($supportedCounts[$feature] / $deviceCount * 100, 1) : 0;
						echo "<tr>";
						echo "<td>" . $feature . "</td>";
						echo "<td class='text-center'><a class='supported' href=\"$coverageLink\">$coverage<span style='font-size:10px;'>%</span></a></td>";
						echo "<td class='text-center'><a class='na' href=\"$coverageLink&option=not\">" . round(100 - $coverage, 1) . "<span style='font-size:10px;'>%</span></a></td>";
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