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

 /**
  * Device coverage based listing of extension property value distribution
  */

require './pagegenerator.php';
require './database/database.class.php';
require 'database/sqlrepository.php';
require './includes/functions.php';
require './includes/filterlist.class.php';
require './includes/chart.php';

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

PageGenerator::header($property_name);
?>
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
					try {
						$values = SqlRepository::listExtensionPropertyValues($property_name, $ext_name);
						foreach ($values as $index => $value) {
							$color_style = "style='border-left: ".Chart::getColor($index)." 3px solid'";
							$link = "listdevicescoverage.php?extensionname=$ext_name&extensionproperty=$property_name&extensionpropertyvalue=".$value['value'].($platform ? "&platform=$platform" : "");
							if ($core) {
								$link .= "&core=$core";
							}
							echo "<tr>";
							echo "<td $color_style>".$value['value']."</td>";
							if ($value['count'] != null) {
								echo "<td><a href='$link'>".$value['count']."</a></td>";
							} else {
								echo "<td class='na'>".$value['count']."</td>";
							}
							echo "</tr>";
						}
					} catch (PDOException $e) {
						echo "<b>Error while fetching data!</b><br>";
						echo $e->getMessage();
					}
					DB::disconnect();
					?>
				</tbody>
			</table>

		</div>
	</div>
</center>

<script type="text/javascript">
	$(document).ready(function() {
		var table = $('#values').DataTable({
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

<?php
PageGenerator::footer();;
?>

</body>

</html>