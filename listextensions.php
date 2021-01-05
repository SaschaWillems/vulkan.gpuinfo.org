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

require 'page_generator.php';
require './database/database.class.php';
require './includes/functions.php';

$platform = "windows";
if (isset($_GET['platform'])) {
	$platform = $_GET['platform'];
}

PageGenerator::header("Extensions");
?>

<div class='header'>
	<?php echo "<h4>Extension coverage for <img src='images/" . $platform . "logo.png' height='14px' style='padding-right:5px'/>" . ucfirst($platform); ?>
</div>

<center>
	<div>
		<ul class='nav nav-tabs'>
			<li <?php if ($platform == "windows") {
					echo "class='active'";
				} ?>> <a href='listextensions.php?platform=windows'><img src="images/windowslogo.png" height="14px" style="padding-right:5px">Windows</a> </li>
			<li <?php if ($platform == "linux") {
					echo "class='active'";
				} ?>> <a href='listextensions.php?platform=linux'><img src="images/linuxlogo.png" height="16px" style="padding-right:4px">Linux</a> </li>
			<li <?php if ($platform == "android") {
					echo "class='active'";
				} ?>> <a href='listextensions.php?platform=android'><img src="images/androidlogo.png" height="16px" style="padding-right:4px">Android</a> </li>
		</ul>
	</div>

	<div class='tablediv' style='width:auto; display: inline-block;'>

		<table id="extensions" class="table table-striped table-bordered table-hover responsive" style='width:auto;'>
			<thead>
				<tr>
					<th></th>
					<th colspan=2 style="text-align: center;">Device coverage</th>
				</tr>
				<tr>
					<th>Extension</th>
					<th style="text-align: center;"><img src='images/icons/check.png' width=16px></th>
					<th style="text-align: center;"><img src='images/icons/missing.png' width=16px></th>
				</tr>
			</thead>
			<tbody>
				<?php
				DB::connect();
				try {
					$viewDeviceCount = DB::$connection->prepare("SELECT count(DISTINCT displayname) from reports where ostype = :ostype");
					$viewDeviceCount->execute(['ostype' => ostype($platform)]);
					$deviceCount = $viewDeviceCount->fetch(PDO::FETCH_COLUMN);

					// Fetch extension features and properties to highlight extensions with a detail page
					$stmnt = DB::$connection->prepare("SELECT distinct(extension) FROM devicefeatures2");
					$stmnt->execute(['ostype' => ostype($platform)]);
					$extensionFeatures = $stmnt->fetchAll(PDO::FETCH_COLUMN, 0);
					$stmnt = DB::$connection->prepare("SELECT distinct(extension) FROM deviceproperties2");
					$stmnt->execute(['ostype' => ostype($platform)]);
					$extensionProperties = $stmnt->fetchAll(PDO::FETCH_COLUMN, 0);

					$stmnt = DB::$connection->prepare(
						"SELECT e.name, count(distinct displayname) as coverage from extensions e 
						join deviceextensions de on de.extensionid = e.id 
						join reports r on r.id = de.reportid 
						where ostype = :ostype
						group by name"
					);
					$stmnt->execute(['ostype' => ostype($platform)]);
					$extensions = $stmnt->fetchAll(PDO::FETCH_ASSOC);

					foreach ($extensions as $extension) {
						$coverageLink = "listdevicescoverage.php?extension=" . $extension['name'] . "&platform=$platform";
						$coverage = round($extension['coverage'] / $deviceCount * 100, 1);
						// Generate link to detail page if extension has additional features or properties
						if ((in_array($extension['name'], $extensionFeatures) != false) || (in_array($extension['name'], $extensionProperties) != false)) {
							$detail_link = " <a href=\"displayextension.php?name=" . $extension['name'] . "\" title=\"Show additional features and properties for this extensions\">[?]</a>";
						} else {
							$detail_link = null;
						}
						echo "<tr>";
						echo "<td>" . $extension['name'] . $detail_link . "</td>";
						echo "<td class='text-center'><a class='supported' href=\"$coverageLink\">$coverage<span style='font-size:10px;'>%</span></a></td>";
						echo "<td class='text-center'><a class='na' href=\"$coverageLink&option=not\">" . round(100 - $coverage, 1) . "<span style='font-size:10px;'>%</span></a></td>";
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