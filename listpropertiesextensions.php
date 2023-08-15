<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2023 Sascha Willems (www.saschawillems.de)
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
require './includes/constants.php';
require './includes/functions.php';
require './includes/filterlist.class.php';

$filters = ['platform', 'extension'];
$filter_list = new FilterList($filters);

$extension = $filter_list->getFilter('extension');
$platform = 'all';
if ($filter_list->hasFilter('platform')) {
	$platform = $filter_list->getFilter('platform');
}

PageGenerator::header("Extension properties listing");
?>

<div class='header'>
	<?php
	if ($extension) {
		echo "<h4>Available extension properties for <code>$extension</code> on " . PageGenerator::filterInfo($platform);
	} else {
		echo "<h4>Extension device properties for " . PageGenerator::filterInfo($platform);
	}
	?>
</div>

<center>
	<?php PageGenerator::platformNavigation('listpropertiesextensions.php', $platform, true, $filter_list->filters); ?>

	<div class='tablediv' style='width:auto; display: inline-block;'>
		<table id="properties" class="table table-striped table-bordered table-hover responsive with-platform-selection">
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
				$start = microtime(true);
				try {
					$properties = SqlRepository::listExtensionProperties($extension);
					foreach($properties as $property) {
						echo "<tr>";
						echo "<td>".$property['extension']."</td>";
						echo "<td class='subkey'>".$property['name']."</td>";
						echo "<td class='text-center'>".ucfirst($property['type'])."</td>";
						if ($property['type'] == 'coverage') {
							$coverageLink = "listdevicescoverage.php?extensionname=".$property['extension']."&extensionproperty=".$property['name']."&extensionpropertyvalue=true"."&platform=$platform";
							echo "<td class='text-center'><a class='supported' href=\"$coverageLink\">".$property['coverage']."<span style='font-size:10px;'>%</span></a></td>";
						} else {
							$link = "<a href='displayextensionproperty.php?extensionname=".$property['extension']."&extensionproperty=".$property['name']."&platform=$platform'>";
							echo "<td class='text-center'>".$link."Listing</a></td>";
						}
						echo "</tr>";
					}
				} catch (PDOException $e) {
					echo "<b>Error while fetching data!</b><br>";
				}
				DB::log('api/listpropertiesextensions.php', null, (microtime(true) - $start) * 1000);
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