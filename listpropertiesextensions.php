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
require './includes/constants.php';
require './includes/functions.php';

$platform = "windows";
if (isset($_GET['platform'])) {
	$platform = $_GET['platform'];
}

$extension = null;
if (isset($_GET['extension'])) {
	$extension = $_GET['extension'];
}

PageGenerator::header("Extension properties listing");
?>

<div class='header'>
	<?php
	if ($extension) {
		echo "<h4>Available extension properties for <code>$extension</code> on " . PageGenerator::platformInfo($platform);
	} else {
		echo "<h4>Extension device properties on " . PageGenerator::platformInfo($platform);
	}
	?>
</div>

<center>
	<?php PageGenerator::platformNavigation('listpropertiesextensions.php', $platform); ?>

	<div class='tablediv' style='width:auto; display: inline-block;'>
		<table id="properties" class="table table-striped table-bordered table-hover responsive" style='width:auto;'>
			<thead>
				<tr>
					<th></th>
					<th>Property</th>
					<th style="text-align: center;">Type</th>
					<th style="text-align: center;"></th>
				</tr>
			</thead>
			<tbody>
				<?php
				DB::connect();
				try {
					$ext_filter = null;
					$os_filter = null;
					$params = [];
					if ($platform !== 'all') {
						$params['ostype'] = ostype($platform);
						$os_filter = 'AND r.ostype = :ostype';
					}
					if ($extension) {
						$params['extension'] = $extension;
						$ext_filter = 'AND d2.extension = :extension';
					}
					// Get the total count of devices that have been submitted with a report version that has support for extension features (introduced with 1.4)
					$stmnt = DB::$connection->prepare("SELECT COUNT(DISTINCT IFNULL(r.displayname, dp.devicename)) FROM reports r JOIN deviceproperties dp ON r.id = dp.reportid WHERE r.version >= '1.4' $os_filter");
					$stmnt->execute($params);
					$device_count = $stmnt->fetchColumn();
					// Get property coverage
					$stmnt = DB::$connection->prepare(
						"SELECT extension, name, type, sum(supporteddevices) as supporteddevices FROM
							(
							SELECT 
								extension,
								name,
								'coverage' as type,
								COUNT(DISTINCT IFNULL(r.displayname, dp.devicename)) AS supporteddevices
							FROM
								deviceproperties2 d2
									JOIN
								reports r ON d2.reportid = r.id
									JOIN
								deviceproperties dp ON dp.reportid = r.id
							WHERE
								value = 'true' 
								$ext_filter
								$os_filter
							GROUP BY extension , name
							
							UNION
							
							SELECT 
								extension,
								name,
								'coverage' as type,
								0 as supporteddevices
							FROM
								deviceproperties2 d2
									JOIN
								reports r ON d2.reportid = r.id
									JOIN
								deviceproperties dp ON dp.reportid = r.id
							WHERE
								value = 'false' 
								$ext_filter
								$os_filter
							GROUP BY extension , name
							
							
							UNION
							
							SELECT 
								extension,
								name,
								'values' as type,
								0 as supporteddevices
							FROM
								deviceproperties2 d2
									JOIN
								reports r ON d2.reportid = r.id
									JOIN
								deviceproperties dp ON dp.reportid = r.id
							WHERE
								value not in ('true', 'false') 
								$ext_filter
								$os_filter
							GROUP BY extension , name
							) tbl
							GROUP BY extension, name, type
							ORDER BY extension ASC , name ASC"
					);
					$stmnt->execute($params);

					if ($stmnt->rowCount() > 0) {
						while ($property = $stmnt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
							echo "<tr>";
							echo "<td>" . $property['extension'] . "</td>";
							echo "<td class='subkey'>" . $property['name'] . "</td>";
							echo "<td class='text-center'>" . ucfirst($property['type']) . "</td>";
							if ($property['type'] == 'coverage') {
								$coverageLink = "listdevicescoverage.php?extensionname=".$property['extension']."&extensionproperty=".$property['name']."&platform=$platform";
								$coverage = round($property['supporteddevices'] / $device_count * 100, 1);
								echo "<td class='text-center'><a class='supported' href=\"$coverageLink\">$coverage<span style='font-size:10px;'>%</span></a></td>";
							} else {
								$link = "<a href='displayextensionproperty.php?extensionname=".$property['extension']."&extensionproperty=".$property['name']."&platform=$platform'>";
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
				"order": [],
				"columnDefs": [{
					"visible": false,
					"targets": 0
				}],
				"searchHighlight": true,
				"bAutoWidth": false,
				"sDom": <?= $extension ? "''" : "'flpt'" ?>,
				"deferRender": true,
				"processing": true,
				"drawCallback": function(settings) {
					var api = this.api();
					var rows = api.rows({
						page: 'current'
					}).nodes();
					var last = null;
					api.column(0, {
						page: 'current'
					}).data().each(function(group, i) {
						if (last !== group) {
							$(rows).eq(i).before(
								'<tr><td colspan="3" class="group">' + group + '</td></tr>'
							);
							last = group;
						}
					});
				}
			});
			<?php
			if ($search !== null) {
			?>
				table.search('\\b<?= $search ?>\\b', true, false).draw();
			<?php
			}
			?>
		});
	</script>

	<?php PageGenerator::footer(); ?>

</center>
</body>

</html>