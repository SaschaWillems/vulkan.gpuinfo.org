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

require 'page_generator.php';
require 'database/database.class.php';

$name = null;
if (isset($_GET['name'])) {
	$name = $_GET['name'];
}
$os = null;
$filter = null;
if (isset($_GET['os'])) {
	$os = $_GET['os'];
	if (!in_array($os, ['windows', 'android', 'linux', 'ios', 'osx'])) {
		$os = null;
	}
	if ($os) {
		if (in_array($os, ['windows', 'android', 'ios', 'osx'])) {
			$filter = "where reportid in (select id from reports where osname = '$os')";
		}
		if (in_array($os, ['linux'])) {
			$filter = "where reportid in (select id from reports where osname not in ('windows', 'android', 'ios', 'osx'))";
		}
	}
}

// Check if capability as valid and part of the selected table
DB::connect();
$result = DB::$connection->prepare("SELECT * from information_schema.columns where TABLE_NAME = 'devicelimits' and column_name = :columnname");
$result->execute([":columnname" => $name]);
DB::disconnect();
if ($result->rowCount() == 0) {
	PageGenerator::errorMessage("<strong>This is not the <strike>droid</strike> device limit you are looking for!</strong><br><br>You may have passed a wrong device limit name.");
}

PageGenerator::header($name);

$caption = "Value distribution for <code>$name</code>";

$platform = null;
if (isset($_GET['platform'])) {
	$platform = $_GET["platform"];
	if ($platform !== "all") {
		switch ($platform) {
			case 'windows':
				$ostype = 0;
				break;
			case 'linux':
				$ostype = 1;
				break;
			case 'android':
				$ostype = 2;
				break;
		}
		$filter .= "where reportid in (select id from reports where ostype = '" . $ostype . "')";
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
			"dom": '',
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
		<div class='tablediv' style='width:auto; display: inline-block;'>
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
					$result = DB::$connection->prepare("SELECT `$name` as value, count(0) as reports from devicelimits $filter group by 1 order by 1");
					$result->execute();
					$rows = $result->fetchAll(PDO::FETCH_ASSOC);
					foreach ($rows as $cap) {
						$link = "listreports.php?limit=$name&value=" . $cap["value"] . ($platform ? "&platform=$platform" : "");
						echo "<tr>";
						echo "<td>" . $cap["value"] . "</td>";
						echo "<td><a href='$link'>" . $cap["reports"] . "</a></td>";
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
			$result = DB::$connection->prepare("SELECT `$name` as value, count(0) as reports from devicelimits $filter group by 1 order by 2 desc");
			$result->execute();
			$rows = $result->fetchAll(PDO::FETCH_ASSOC);
			foreach ($rows as $row) {
				echo "['" . $row['value'] . "'," . $row['reports'] . "],";
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