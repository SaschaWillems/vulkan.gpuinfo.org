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

$name = null;
if (isset($_GET['name'])) {
	$name = $_GET['name'];
}

// Check if capability is valid and part of the selected table
DB::connect();
$result = DB::$connection->prepare("SELECT * from information_schema.columns where TABLE_NAME = 'devicelimits' and column_name = :columnname");
$result->execute([":columnname" => $name]);
DB::disconnect();
if ($result->rowCount() == 0) {
	PageGenerator::errorMessage("<strong>This is not the <strike>droid</strike> device limit you are looking for!</strong><br><br>You may have passed a wrong device limit name.");
}

PageGenerator::header($name);

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
					DB::connect();
					try {
						$values = SqlRepository::listCoreLimitValues($name);
						foreach ($values as $index => $value) {
							$color_style = "style='border-left: ".Chart::getColor($index)." 3px solid'";
							$link = "listreports.php?limit=$name&value=".$value['value'].($platform ? "&platform=$platform" : "");
							echo "<tr>";
							echo "<td $color_style>".$value['value']."</td>";
							echo "<td><a href='$link'>".$value['count']."</a></td>";
							echo "</tr>";
						}
					} catch (PDOException $e) {
						echo "<b>Error while fetching data!</b><br>";
					} finally {				
						DB::disconnect();						
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