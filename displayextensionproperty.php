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

 /**
  * Device coverage based listing of extension property value distribution
  */

require './pagegenerator.php';
require './database/database.class.php';
require './includes/functions.php';
include './includes/filterlist.class.php';

$filters = ['platform', 'extensionname', 'extensionproperty'];
$filter_list = new FilterList($filters);
if ((!$filter_list->hasFilter('extensionname')) || (!$filter_list->hasFilter('extensionproperty'))) {
	PageGenerator::errorMessage("This is not the <strike>droid</strike> extension property you are looking for!</strong><br><br>You did not specify all required parameters.");
}

$ext_name = $filter_list->getFilter('extensionname');
$property_name = $filter_list->getFilter('extensionproperty');

DB::connect();
$result = DB::$connection->prepare("SELECT * from deviceproperties2 where name = :name and extension = :extension");
$result->execute([":name" => $property_name, ":extension" => $ext_name]);
$row = $result->fetch(PDO::FETCH_ASSOC);
if ($result->rowCount() == 0) {
	PageGenerator::errorMessage("This is not the <strike>droid</strike> extension property you are looking for!</strong><br><br>You may have passed a wrong extension property name.");
}
DB::disconnect();

$caption = "Value distribution for <code>$property_name</code> property of <code>$ext_name</code>";

$sql = 'SELECT value, count(distinct(r.displayname)) as `count` from deviceproperties2 dp2 join reports r on dp2.reportid = r.id where name = :name and extension = :extension';

$platform = null;
if ($filter_list->hasFilter('platform')) {
	$platform = $filter_list->getFilter('platform');
	$ostype = ostype($platform);
	if ($ostype !== null) {
		$sql .= " and r.ostype = $ostype";
		$caption .= " on <img src='images/" . $platform . "logo.png' height='14px' style='padding-right:5px'/>" . ucfirst($platform);
	}
}

$sql .= ' group by value';

PageGenerator::header($property_name);
?>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script>
	$(document).ready(function() {
		var table = $('#values').DataTable({
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
	<h4 class='headercaption'><?=$caption?></h4>
</div>

<center>
	<div class='parentdiv'>
		<div id="chart"></div>
		<div class='property-table'>
			<table id="values" class="table table-striped table-bordered table-hover reporttable">
				<thead>
					<tr>
						<th>Value</th>
						<th>Devices</th>
					</tr>
				</thead>
				<tbody>
					<?php
					DB::connect();
					$result = DB::$connection->prepare("$sql order by 1");
					$result->execute([":name" => $property_name, ":extension" => $ext_name]);
					$rows = $result->fetchAll(PDO::FETCH_ASSOC);
					foreach ($rows as $row) {
						$value = $row['value'];
						// Some values are stored as serialized arrays and need to be unserialized
						if (substr($value, 0, 2) == 'a:') {
							$value = unserialize($value);
							$value = '[' . implode(',', $value) . ']';
						}
						$link = "listdevicescoverage.php?extensionname=$ext_name&extensionproperty=$property_name&extensionpropertyvalue=$value";
						if ($platform) {
							$link .= "&platform=$platform";
						}
						echo "<tr>";
						echo "<td>$value</td>";
						echo "<td><a href='$link'>" . $row['count'] . "</a></td>";
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
			$result = DB::$connection->prepare("$sql order by 2 desc");
			$result->execute([":name" => $property_name, ":extension" => $ext_name]);
			$rows = $result->fetchAll(PDO::FETCH_ASSOC);
			foreach ($rows as $row) {
				$value = $row['value'];
				// Some values are stored as serialized arrays and need to be unserialized
				if (substr($value, 0, 2) == 'a:') {
					$value = unserialize($value);
					$value = '[' . implode(',', $value) . ']';
				}
				echo "['$value'," . $row['count'] . "],";
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

<?php
PageGenerator::footer();;
?>

</body>

</html>