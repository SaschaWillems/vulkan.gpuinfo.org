<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2022 by Sascha Willems (www.saschawillems.de)
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
require 'database/database.class.php';
require 'database/sqlrepository.php';
require './includes/functions.php';
require './includes/chart.php';

$name = SqlRepository::getGetValue('name');
$core = SqlRepository::getGetValue('core');

PageGenerator::header($name);

try {
	DB::connect();
	if (!SqlRepository::corePropertyExists($core, $name)) {
		PageGenerator::errorMessage("<strong>This is not the <strike>droid</strike> device property you are looking for!</strong><br><br>You may have passed a wrong device property name.");
	}
	$values = SqlRepository::listCorePropertyValues($core, $name);
} catch (PDOException $e) {
	PageGenerator::databaseErrorMessage();
} finally {
	DB::disconnect();
}

$caption = "Value distribution for <code>$name</code> ".PageGenerator::filterInfo();
?>

<div class='header'>
	<h4 class='headercaption'><?= $caption; ?></h4>
</div>

<center>
	<div class='chart-div'>
		<div id="chart"></div>
		<div class='chart-table-div'>
			<table id="extensions" class="table table-striped table-bordered table-hover reporttable">
				<thead>
					<tr>
						<th>Value</th>
						<th>Reports</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($values as $index => $value) {
						$color_style = "style='border-left: ".Chart::getColor($index)." 3px solid'";
						$link = "listreports.php?property=$name&value=".$value['value'].($platform ? "&platform=$platform" : "");
						if ($core) {
							$link .= "&core=$core";
						}
						echo "<tr>";
						echo "<td $color_style>".$value['displayvalue']."</td>";
						if ($value['count'] != null) {
							echo "<td><a href='$link'>".$value['count']."</a></td>";
						} else {
							echo "<td class='na'>".$value['count']."</td>";
						}
						echo "</tr>";
					}
					?>
				</tbody>
			</table>

		</div>
	</div>
</center>

<script type="text/javascript">
	$(document).ready(function() {
		var table = $('#extensions').DataTable({
			"pageLength": -1,
			"paging": false,
			"stateSave": false,
			"searchHighlight": true,
			"dom": '',
			"bInfo": false,
			"order": [
				[0, "asc"]
			]
		});
	});
	<?php
		Chart::draw($values, 'value', 'count');
	?>
</script>

<?php PageGenerator::footer(); ?>

</body>

</html>