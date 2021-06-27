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

include 'pagegenerator.php';
include './includes/functions.php';
include './includes/filterlist.class.php';
include './database/database.class.php';

$filters = [
	'platform',
	'submitter',
	'devicename',
	'displayname',
	'limit',
	'property',
	'core',
	'value',
	'instanceextension',
	'instancelayer',
	'option'
];
$filter_list = new FilterList($filters);

$defaultHeader = true;
$pageTitle = null;
$inverted = false;
$platform = "all";

// Invert
$inverted = $filter_list->hasFilter('option') && ($filter_list->getFilter('option') == 'not');
// Submitter
if ($filter_list->hasFilter('submitter')) {
	$defaultHeader = false;
	$caption = "Reports submitted by <code>".$filter_list->getFilter('submitter')."</code>";
}
// List (and order) by limit
$limit = $filter_list->getFilter('limit');
$limitvalue = null;
if ($limit != '') {
	$defaultHeader = false;
	$caption = "Listing limits for <code>$limit</code>";
	// Check if a limit requirement rule has to be applied (see Table 36. of the specs)
	DB::connect();
	$sql = "select feature from limitrequirements where limitname = :limit";
	$reqs = DB::$connection->prepare($sql);
	$reqs->execute(array(":limit" => $limit));
	if ($reqs->rowCount() > 0) {
		$req = $reqs->fetch();
		$caption .= "<br>(Feature requirement " . $req["feature"] . " is applied)";
	}
	if ($filter_list->hasFilter('value')) {
		$limitvalue = $filter_list->getFilter('value');
		$link = "displaydevicelimit.php?name=" . $limit;
		$caption = "Reports with <a href=" . $link . ">" . $limit . "</a> = " . $limitvalue;
		$pageTitle = $limit . " = " . $limitvalue;
	}
	DB::disconnect();
}
// Device name
if ($filter_list->hasFilter('devicename')) {
	$defaultHeader = false;
	$caption = "Reports for <code>".$filter_list->getFilter('devicename')."</code>";
}
// Display name (Android devices)
if ($filter_list->hasFilter('displayname')) {
	$defaultHeader = false;
	$caption = "Reports for <code>".$filter_list->getFilter('displayname')."</code>";
}
// Instance extension
if ($filter_list->hasFilter('instanceextension')) {
	$instanceextension = $filter_list->getFilter('instanceextension');
	$defaultHeader = false;
	$caption = "Reports " . ($inverted ? "<b>not</b>" : "") . " supporting instance extension <code>$instanceextension</code>";
	$caption .= " (<a href='listreports.php?instanceextension=" . $instanceextension . ($inverted ? "" : "&option=not") . "'>toggle</a>)";
	$pageTitle = $instanceextension;
}
// Instance layer
if ($filter_list->hasFilter('instancelayer')) {
	$instancelayer = $filter_list->getFilter('instancelayer');
	$defaultHeader = false;
	$caption = "Reports " . ($inverted ? "<b>not</b>" : "") . " supporting instance layer <code>$instancelayer</code>";
	$caption .= " (<a href='listreports.php?instancelayer=" . $instancelayer . ($inverted ? "" : "&option=not") . "'>toggle</a>)";
	$pageTitle = $instancelayer;
}
// Core property
$coreproperty = $filter_list->getFilter('property');
$corepropertyvalue = null;
$coreversion = $filter_list->getFilter('core');
if (isset($coreproperty) && ($coreproperty != '')) {
	$defaultHeader = false;
	$corepropertyvalue = $filter_list->getFilter('value');
	$displayvalue = getPropertyDisplayValue($coreproperty, $corepropertyvalue);
	$caption = "Reports with <code>$coreproperty</code> = $displayvalue";
}
// Platform (os)
if ($filter_list->hasFilter('platform') && $filter_list->getFilter('platform') !== 'all') {
	$platform = $filter_list->getFilter('platform');
	$caption = "Listing " . ($caption ? lcfirst($caption) : "reports") . " on <img src='images/" . $platform . "logo.png' height='14px' style='padding-right:5px'/>" . ucfirst($platform);
	$defaultHeader = false;
}

PageGenerator::header($pageTitle == null ? "Reports" : "Reports for $pageTitle");

if ($defaultHeader) {
	echo "<div class='header'>";
	echo "	<h4>Listing reports</h4>";
	echo "</div>";
}
?>
<center>
	<?php
	if (!$defaultHeader) {
		echo "<div class='header'><h4>";
		echo $caption ? $caption : "Listing available devices";
		echo "</h4></div>";
	}

	PageGenerator::platformNavigation('listreports.php', $platform, true, $filter_list->filters);
	?>
	<div class='tablediv tab-content' style='display: inline-flex;'>
		<form method="get" action="compare.php?compare">
			<table id='reports' class='table table-striped table-bordered table-hover responsive' style='width:auto'>
				<thead>
					<tr>
						<th></th>
						<?php if (isset($_GET["limit"])) echo "<th></th>" ?>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
					</tr>
					<tr>
						<th>id</th>
						<?php if (isset($_GET["limit"])) echo "<th>Limit</th>" ?>
						<th>Device</th>
						<th>Driver</th>
						<th>Api</th>
						<th>Vendor</th>
						<th>Type</th>
						<th>OS</th>
						<th>Version</th>
						<th>Platform</th>
						<th><input type='submit' name='compare' value='compare' class='button'></th>
					</tr>
				</thead>
			</table>
			<div id="errordiv" style="color:#D8000C;"></div>
		</form>
	</div>
</center>

<script>
	$(document).on("keypress", "form", function(event) {
		return event.keyCode != 13;
	});

	$(document).ready(function() {

		var table = $('#reports').DataTable({
			"processing": true,
			"serverSide": true,
			"paging": true,
			"searching": true,
			"lengthChange": false,
			"dom": 'lrtip',
			"pageLength": 25,
			"order": [
				[0, 'desc']
			],
			"columnDefs": [{
				"searchable": false,
				"targets": [0, <?php echo (isset($_GET["limit"])) ? "10" : "9" ?>],
				"orderable": false,
				"targets": <?php echo (isset($_GET["limit"])) ? "10" : "9" ?>,
			}],
			"ajax": {
				url: "responses/listreports.php",
				data: {
					"filter": {
						'submitter': 			'<?= $filter_list->getFilter("submitter") ?>',
						'devicelimit': 			'<?= $filter_list->getFilter('limit') ?>',
						'devicelimitvalue' : 	'<?= $filter_list->getFilter('value') ?>',
						'devicename': 			'<?= $filter_list->getFilter('devicename') ?>',
						'displayname': 			'<?= $filter_list->getFilter('displayname') ?>',
						'instanceextension': 	'<?= $filter_list->getFilter('instanceextension') ?>',
						'instancelayer': 		'<?= $filter_list->getFilter('instancelayer') ?>',
						'platform':				'<?= $filter_list->getFilter('platform') ?>',
						'coreproperty': 		'<?= $filter_list->getFilter('property') ?>',
						'corepropertyvalue': 	'<?= $filter_list->getFilter('value') ?>',
						'core':					'<?= $filter_list->getFilter('core') ?>',
						'option': 				'<?= $filter_list->getFilter('option') ?>',
					}
				},
				error: function(xhr, error, thrown) {
					$('#errordiv').html('Could not fetch data (' + error + ')');
					$('#reports_processing').hide();
				}
			},
			"columns": [{
					data: 'id'
				},
				<?php if (isset($_GET["limit"])) echo "{ data: 'devicelimit'},\n" ?> {
					data: 'device'
				},
				{
					data: 'driver'
				},
				{
					data: 'api'
				},
				{
					data: 'vendor'
				},
				{
					data: 'devicetype'
				},
				{
					data: 'osname'
				},
				{
					data: 'osversion'
				},
				{
					data: 'osarchitecture'
				},
				{
					data: 'compare'
				},
			],
			// Pass order by column information to server side script
			fnServerParams: function(data) {
				data['order'].forEach(function(items, index) {
					data['order'][index]['column'] = data['columns'][items.column]['data'];
				});
			},
		});

		yadcf.init(table, [
			<?php
			$coloffset = 0;
			if (isset($_GET["limit"])) {
				$coloffset = 1;
				echo
					'{
					column_number: 1,
					filter_type: "text",
					filter_delay: 500,
		   		},';
			}
			?> {
				column_number: <?php echo ($coloffset + 1) ?>,
				filter_type: "text",
				filter_delay: 500,
				style_class: "filter-240"
			},
			{
				column_number: <?php echo ($coloffset + 2) ?>,
				filter_type: "text",
				filter_delay: 500
			},
			{
				column_number: <?php echo ($coloffset + 3) ?>,
				filter_type: "text",
				filter_delay: 500
			},
			{
				column_number: <?php echo ($coloffset + 4) ?>,
				filter_type: "text",
				filter_delay: 500
			},
			{
				column_number: <?php echo ($coloffset + 5) ?>,
				filter_type: "text",
				filter_delay: 500
			},
			{
				column_number: <?php echo ($coloffset + 6) ?>,
				filter_type: "text",
				filter_delay: 500
			},
			{
				column_number: <?php echo ($coloffset + 7) ?>,
				filter_type: "text",
				filter_delay: 500
			},
		], {
			filters_tr_index: 0
		});

	});
</script>

<?php PageGenerator::footer(); ?>

</body>

</html>