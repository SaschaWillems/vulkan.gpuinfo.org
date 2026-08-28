<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2026 Sascha Willems (www.saschawillems.de)
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
require './includes/constants.php';
require './includes/filterlist.class.php';
require './includes/chart.php';

$filters = ['platform', 'age', 'apiversion'];
$filter_list = new FilterList($filters);

PageGenerator::header("API versions");
$platform = PageGenerator::getDefaultOSSelection();
PageGenerator::pageCaption("API version coverage");

try {
	DB::connect();
	$ostype = $filter_list->hasFilter('platform') ? ostype($filter_list->getFilter('platform')) : null;
	$age = $filter_list->hasFilter('age') ? $filter_list->getFilter('age') : null;
	$values = SqlRepository::listCoreVersionCoverage($ostype, $age);
} catch (PDOException $e) {
	PageGenerator::databaseErrorMessage();
} finally {
	DB::disconnect();
}
?>

<center>
	<?php PageGenerator::platformNavigation('listversions.php', $platform, true); ?>

	<div class='chart-div'>
		<div>
			<div class='table-options'>
				<?php $filter_list->addDefaultFilterOptions(['age']) ?>
			</div>
		</div>
		<div id="chart"></div>
		<div class='chart-table-div'>
			<table id="versions" class="table table-striped table-bordered table-hover reporttable" style='width: auto'>
				<thead>
					<tr>
						<th>API version</th>
						<th>Devices</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($values as $index => $value) {
						$color_style = "style='border-left: ".Chart::getColor($index)." 3px solid'";
						$coverage_link = $filter_list->applyDefaultUrlFilter("listdevicescoverage.php?apiversion=".$value['version']);
						echo "<tr>";
						echo "<td $color_style>".$value['version']."</td>";
						echo "<td><a href='$coverage_link'>".$value['count']."</a></td>";
						echo "</tr>";
					}
					?>
				</tbody>
			</table>
		</div>
	</div>

	<script type="text/javascript">
		$(document).ready(function() {
			var table = $('#versions').DataTable({
				"pageLength": -1,
				"paging": false,
				"stateSave": false,
				"searchHighlight": true,
				"dom": '',
				"bInfo": false,
				"order": [
					[0, "desc"]
				],				
			});
		});
		<?php
			Chart::draw($values, 'displayvalue', 'count');
		?>
	</script>

	<?php PageGenerator::footer(); ?>

</center>
</body>

</html>