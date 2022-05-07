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

include 'pagegenerator.php';
include './database/database.class.php';
require './database/sqlrepository.php';
require './includes/constants.php';
include './includes/functions.php';
include './includes/filterlist.class.php';

$filters = ['platform', 'extension'];
$filter_list = new FilterList($filters);

$extension = $filter_list->getFilter('extension');
$platform = 'all';
if ($filter_list->hasFilter('platform')) {
	$platform = $filter_list->getFilter('platform');
}

PageGenerator::header("Extension features listing");
?>

<div class='header'>
	<?php
	if ($extension) {
		echo "<h4>Available extension features for <code>$extension</code> on " . PageGenerator::filterInfo($platform);
	} else {
		echo "<h4>Extension device feature coverage for " . PageGenerator::filterInfo($platform);
	}
	?>
</div>

<center>
	<?php PageGenerator::platformNavigation('listfeaturesextensions.php', $platform, true, $filter_list->filters); ?>

	<div class='tablediv' style='width:auto; display: inline-block;'>
		<table id="features" class="table table-striped table-bordered table-hover responsive with-platform-selection">
			<thead>
				<tr>
					<th></th>
					<th>Feature</th>
					<th style="text-align: center;"><img src='images/icons/check.png' width=16px></th>
					<th style="text-align: center;"><img src='images/icons/missing.png' width=16px></th>
				</tr>
			</thead>
			<tbody>
				<?php
				DB::connect();
				try {
					$features = SqlRepository::listExtensionFeatures($extension);
					foreach($features as $feature) {
						$coverageLink = "listdevicescoverage.php?extensionname=" . $feature['extension'] . "&extensionfeature=" . $feature['name'] . "&platform=$platform";
						$coverage = $feature['coverage'];
						echo "<tr>";
						echo "<td>" . $feature['extension'] . "</td>";
						echo "<td class='subkey'>" . $feature['name'] . "</td>";
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
		});
	</script>

	<?php PageGenerator::footer(); ?>

</center>
</body>

</html>