<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2022 Sascha Willems (www.saschawillems.de)
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
require './database/sqlrepository.php';
require './includes/functions.php';

$platform = 'all';
if (isset($_GET['platform'])) {
	$platform = GET_sanitized('platform');
}

PageGenerator::header("Extensions");
?>

<div class='header'>
	<?php echo "<h4>Extension coverage for ".PageGenerator::filterInfo() ?>
</div>

<center>
	<?php PageGenerator::platformNavigation('listextensions.php', $platform, true); ?>

	<div class='tablediv' style='width:auto; display: inline-block;'>
		<table id="extensions" class="table table-striped table-bordered table-hover responsive" style='width:auto;'>
			<thead>
				<tr>
					<th></th>
					<th colspan=2 style="text-align: center;">Device coverage</th>
					<th colspan=2>Additional</th>
				</tr>
				<tr>
					<th>Extension</th>
					<th style="text-align: center;"><img src='images/icons/check.png' width=16px></th>
					<th style="text-align: center;"><img src='images/icons/missing.png' width=16px></th>
					<th><abbr title="Extension-related features">F.</abbr></th>
					<th><abbr title="Extension-related properties">P.</abbr></th>
				</tr>
			</thead>
			<tbody>
				<?php
				DB::connect();
				try {
					$params = [];
					if ($platform !== 'all') {
						$params['ostype'] = ostype($platform);
					}			

					// Fetch extension features and properties to highlight extensions with a detail page
					$stmnt = DB::$connection->prepare("SELECT distinct(extension) FROM devicefeatures2");
					$stmnt->execute(['ostype' => ostype($platform)]);
					$extensionFeatures = $stmnt->fetchAll(PDO::FETCH_COLUMN, 0);
					$stmnt = DB::$connection->prepare("SELECT distinct(extension) FROM deviceproperties2");
					$stmnt->execute(['ostype' => ostype($platform)]);
					$extensionProperties = $stmnt->fetchAll(PDO::FETCH_COLUMN, 0);

					$deviceCount = SqlRepository::deviceCount();
					$extensions = SqlRepository::listExtensions();

					foreach ($extensions as $extension) {
						if (trim($extension['name']) == '') {
							continue;
						}
						$coverageLink = "listdevicescoverage.php?extension=" . $extension['name'] . "&platform=$platform";
						$coverage = round($extension['coverage'] / $deviceCount * 100, 1);
						$ext = $extension['name'];
						$feature_link = null;
						if (in_array($extension['name'], $extensionFeatures) != false) {
							$feature_link = "<a href='listfeaturesextensions.php?extension=$ext&platform=$platform'><span class='glyphicon glyphicon-search' title='Display features for this extension'/></a";
						}
						$property_link = null;
						if (in_array($extension['name'], $extensionProperties) != false) {
							$property_link = "<a href='listpropertiesextensions.php?extension=$ext&platform=$platform'><span class='glyphicon glyphicon-search' title='Display properties for this extension'/></a";
						}
						echo "<tr>";
						echo "<td>$ext</td>";
						echo "<td class='text-center'><a class='supported' href=\"$coverageLink\">$coverage<span style='font-size:10px;'>%</span></a></td>";
						echo "<td class='text-center'><a class='na' href=\"$coverageLink&option=not\">" . round(100 - $coverage, 1) . "<span style='font-size:10px;'>%</span></a></td>";
						echo "<td class='text-center' style='vertical-align: middle'>$feature_link</td>";
						echo "<td class='text-center' style='vertical-align: middle'>$property_link</td>";
						echo "</tr>";
					}
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
			var table = $('#extensions').DataTable({
				"pageLength": -1,
				"paging": false,
				"stateSave": false,
				"searchHighlight": true,
				"dom": 'f',
				"bInfo": false,
				"fixedHeader": {
					"header": true,
					"headerOffset": 50
				},
				"order": [
					[0, "asc"]
				],
				"columnDefs": [{
					"targets": [1, 2],
				}]
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