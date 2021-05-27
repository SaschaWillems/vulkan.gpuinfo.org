<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2021 by Sascha Willems (www.saschawillems.de)
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
require './includes/functions.php';

$name = null;
if (isset($_GET['name'])) {
	$name = $_GET['name'];
}
$filter = null;
$tablename = 'deviceproperties';
$core = null;
if (isset($_GET['core'])) {
	$core = $_GET['core'];
	switch ($_GET['core']) {
		case '1.1':
			$tablename = 'deviceproperties11';
			break;
		case '1.2':
			$tablename = 'deviceproperties12';
			break;
	}
}
// Check if property is valid and part of the selected table
DB::connect();
$result = DB::$connection->prepare("SELECT * from information_schema.columns where TABLE_NAME = :tablename and column_name = :columnname");
$result->execute(["tablename" => $tablename, "columnname" => $name]);
DB::disconnect();
if ($result->rowCount() == 0) {
	PageGenerator::errorMessage("<strong>This is not the <strike>droid</strike> device property you are looking for!</strong><br><br>You may have passed a wrong device property name.");
}

PageGenerator::header($name);

$caption = "Value distribution for <code>$name</code>";

$platform = null;
if (isset($_GET['platform'])) {
	$platform = $_GET["platform"];
	$ostype = ostype($platform);
	if ($ostype !== null) {
		$filter .= "where reportid in (select id from reports where ostype = $ostype)";
		$caption .= " on <img src='images/" . $platform . "logo.png' height='14px' style='padding-right:5px'/>" . ucfirst($platform);
	}
}

?>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
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
			]
		});
	});
</script>

<div class='header'>
	<h4 class='headercaption'><?php echo $caption; ?></h4>
</div>

<center>
	<div class='parentdiv'>
		<div id="chart"></div>
		<div class='tablediv' style='width:auto; display: inline-block; border: 0px;'>
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
					switch ($name) {
						case 'vendorid':
							$sql = "SELECT `$name`as value, VendorId(vendorid) as displayvalue, count(0) as reports from $tablename $filter group by 1 order by 1";
							break;
						default:
							$sql = "SELECT `$name` as value, null as displayvalue, count(0) as reports from $tablename $filter group by 1 order by 1";
					}
					$result = DB::$connection->prepare($sql);
					$result->execute();
					$rows = $result->fetchAll(PDO::FETCH_ASSOC);
					foreach ($rows as $cap) {
						$link = "listreports.php?property=$name&value=" . $cap["value"] . ($platform ? "&platform=$platform" : "");
						if ($core) {
							$link .= "&core=$core";
						}
						$value = getPropertyDisplayValue($name, $cap['value']);
						echo "<tr>";
						echo "<td>".($cap['displayvalue'] !== null ? $cap['displayvalue'] : $value)."</td>";
						if ($cap['value'] != null) {
							echo "<td><a href='$link'>" . $cap["reports"] . "</a></td>";
						} else {
							echo "<td class='na'>".$cap["reports"]."</td>";
						}
						echo "</tr>";
					}
					DB::disconnect();
					?>
				</tbody>
			</table>

		</div>
	</div>
</center>

<script type="text/javascript">
	google.charts.load('current', {
		'packages': ['corechart']
	});
	google.charts.setOnLoadCallback(drawChart);

	function drawChart() {

		var data = google.visualization.arrayToDataTable([
			['Value', 'Reports'],
			<?php
			DB::connect();
			switch ($name) {
				case 'vendorid':
					$sql = "SELECT VendorId(vendorid) as value, count(0) as reports from $tablename $filter group by 1 order by 2";
					break;
				default:
					$sql = "SELECT `$name` as value, count(0) as reports from $tablename $filter group by 1 order by 2";
			}
			$result = DB::$connection->prepare($sql);
			$result->execute();
			$rows = $result->fetchAll(PDO::FETCH_ASSOC);
			foreach ($rows as $row) {
				if ($row['value'] != null) {
					echo "['" . $row['value'] . "'," . $row['reports'] . "],";
				}
			}
			DB::disconnect();
			?>
		]);

		var options = {
			legend: {
				position: 'bottom'
			},
			chartArea: {
				width: "80%",
				height: "80%"
			},
			height: 500,
			width: 500
		};

		var chart = new google.visualization.PieChart(document.getElementById('chart'));

		chart.draw(data, options);
	}
</script>

<?php PageGenerator::footer(); ?>

</body>

</html>