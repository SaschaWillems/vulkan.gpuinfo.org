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

require 'pagegenerator.php';
require './database/database.class.php';
require './database/sqlrepository.class.php';
require './includes/functions.php';

$platform = "windows";
if (isset($_GET['platform'])) {
	$platform = $_GET['platform'];
}

PageGenerator::header("Formats");
$sql_repository = new SqlRepository($platform);
?>

<div class='header'>
	<?php echo "<h4>Image and buffer format support on <img src='images/" . $platform . "logo.png' height='14px' style='padding-right:5px'/>" . ucfirst($platform); ?>
</div>

<center>
	<?php
	$sql_repository->filterHeader();
	PageGenerator::platformNavigation('listformats.php', $platform);
	?>

	<div class='tablediv' style='width:auto; display: inline-block;'>
		<table id="formats" class="table table-striped table-bordered table-hover responsive" style='width:auto;'>
			<thead>
				<tr>
					<th></th>
					<th colspan=7 style="text-align: center;">Device coverage</th>
				</tr>
				<tr>
					<th></th>
					<th colspan=2 style="text-align: center;">Linear</th>
					<th colspan=2 style="text-align: center;">Optimal</th>
					<th colspan=2 style="text-align: center;">Buffer</th>
				</tr>
				<th>Format</th>
				<th style="text-align: center;"><img src='images/icons/check.png' width=16px></th>
				<th style="text-align: center;"><img src='images/icons/missing.png' width=16px></th>
				<th style="text-align: center;"><img src='images/icons/check.png' width=16px></th>
				<th style="text-align: center;"><img src='images/icons/missing.png' width=16px></th>
				<th style="text-align: center;"><img src='images/icons/check.png' width=16px></th>
				<th style="text-align: center;"><img src='images/icons/missing.png' width=16px></th>
				</tr>
			</thead>
			<tbody>
				<?php
				$formats = [];
				DB::connect();
				try {
					$device_count = $sql_repository->deviceCount();
					// Fetch formats into array as a base for creating the table
					foreach (['lineartilingfeatures', 'optimaltilingfeatures', 'bufferfeatures'] as $target) {
						$query = $sql_repository->formatList($target, ostype($platform), $database_settings);
						$result = $query->fetchAll(PDO::FETCH_NUM);
						foreach ($result as $row) {
							$formats[$row[0]][$target] = $row[1];
						}
					}
				} catch (PDOException $e) {
					echo "<b>Error while fetcthing data!</b><br>";
				}
				DB::disconnect();

				// Build table
				foreach ($formats as $key => $format) {
					echo "<tr>";
					echo "<td class='value'>" . $key . "</td>";
					$names = ['linearformat', 'optimalformat', 'bufferformat'];
					foreach (['lineartilingfeatures', 'optimaltilingfeatures', 'bufferfeatures'] as $index => $target) {
						$coverageLink = "listdevicescoverage.php?$names[$index]=$key&platform=$platform";
						$coverage = $format[$target] / $device_count * 100.0;
						echo "<td class='value' align=center><a class='supported' href='$coverageLink'>" . round($coverage, 2) . "<span style='font-size:10px;'>%</span></a></td>";
						echo "<td class='value' align=center><a class='na' href='$coverageLink&option=not'>" . round(100 - $coverage, 2) . "<span style='font-size:10px;'>%</span></a></td>";
					}
					echo "</tr>";
				}
				?>
			</tbody>
		</table>
	</div>

	<script>
		$(document).ready(function() {
			var table = $('#formats').DataTable({
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