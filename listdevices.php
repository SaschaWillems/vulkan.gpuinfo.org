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

session_start();

include 'pagegenerator.php';
include './includes/functions.php';
include './includes/filterlist.class.php';
include './database/database.class.php';
include './database/sqlrepository.php';

$filters = [
	'platform',
	'extension',
	'submitter',
];
$filter_list = new FilterList($filters);

PageGenerator::header("Devices");

$platform = "all";
$caption = 'Listing all devices';
$showTabs = true;

if ($filter_list->hasFilter('platform')) {
	$platform = $filter_list->getFilter('platform');
	$caption = "Listing all <img src='images/" . $platform . "logo.png' height='14px' style='padding-right:5px'/>".PageGenerator::platformDisplayName($platform)." devices";
}
if ($filter_list->hasFilter('extension')) {
	$caption .= " supporting ".$filter_list->getFilter('extension');
	$showTabs = false;
}
if ($filter_list->hasFilter('submitter')) {
	$caption .= "Devices submitted by ".$filter_list->getFilter('submitter');
	$showTabs = false;
}
$minApiVersion = SqlRepository::getMinApiVersion();
if ($minApiVersion) {
	$caption .= " Vulkan $minApiVersion (and up)";
}
?>

<center>

	<div class='header'>
		<h4>
			<?= $caption; ?>
		</h4>
	</div>

	<!-- Compare block (only visible when at least one report is selected) -->
	<div id="compare-div" class="alert alert-info" role="alert" style="text-align: center; display: none;">
		<div class="compare-header">Selected devices for compare:</div>
		<span id="compare-info"></span>
		<div class="compare-footer">
			<Button onClick="clearCompare()"><span class='glyphicon glyphicon-button glyphicon-erase'></span> Clear</Button>
			<Button onClick="compare()"><span class='glyphicon glyphicon-button glyphicon-duplicate'></span> Compare</Button>
		</div>
	</div>	

	<?php
	if ($showTabs) {
		PageGenerator::platformNavigation('listdevices.php', $platform, true);
	}
	?>

	<div class='tablediv tab-content' style='display: inline-flex;'>
		<div id='devices_div' class='tab-pane fade in active'>
			<table id='devices' class='table table-striped table-bordered table-hover responsive' style='width:auto'>
				<thead>
					<tr>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
					</tr>
					<tr>
						<th>Device</th>
						<th>Max. API version</th>
						<th>Latest Driver version</th>
						<th>Last submission</th>
						<th>Count</th>
						<th>Compare</th>
					</tr>
				</thead>
			</table>
			<div id="errordiv" style="color:#D8000C;"></div>
		</div>
	</div>
</center>

<script>
	var comparerUrl = 'api/internal/devicecomparer.php',
	compareDevices = [];

	function clearCompare() {
		data =  {'action': 'clear' };
		$.post(comparerUrl, data, function (response) {
			displayCompare(null);
		});
    };

	function removeFromCompare(name) {
    	data =  {'action': 'remove', 'devicename': name };
    	$.post(comparerUrl, data, function (response) {
        	displayCompare(response);
    	});	
	}

	function addToCompare(devicename, ostype) {
		data = {'action': 'add', 'devicename': devicename, 'ostype': ostype};
		$.post(comparerUrl, data, function (response) {
			displayCompare(response);
		});
    }	

	function displayCompare(data) {
		elem = $('#compare-info');
		div = $('#compare-div'); 
		html = '';
		arr = JSON.parse(data);
		compareDevices = [];
		if (Array.isArray(arr)) {
			html = '';
			for (var i = 0; i < arr.length; i++) {
				var element = arr[i];
				var last = (i == arr.length - 1);
				var ostypes = ['Windows', 'Linux', 'Android', 'macOS', 'iOS'];
				var osname = (element.ostype !== null) ? ostypes[element.ostype] : 'All';
				html += element.name + ' (' + osname + ') <span onClick="removeFromCompare(\'' + element.name + '\');" class="glyphicon glyphicon-button glyphicon-trash report-remove-icon"></span> ' + (last ? '' : '- ');
				compareDevices.push(element);
			}
		}
		elem.html(html);
		compareDevices.length > 0 ? div.show() : div.hide();			
	}

	function compare() {
		var params = compareDevices.map((element) => (element.name + ':os=' + (element.ostype !== null ? element.ostype : '-1')) ).join();
		location.href = 'compare.php?devices=' + params;
	}

	$(document).on("keypress", "form", function(event) {
		return event.keyCode != 13;
	});

	$(document).ready(function() {
		
		$.get(comparerUrl, null, function (response) {
			displayCompare(response);
		});

		var table = $('#devices').DataTable({
			"processing": true,
			"serverSide": true,
			"paging": true,
			"searching": true,
			"lengthChange": false,
			"dom": 'lrtip',
			"pageLength": 25,
			"order": [
				[3, 'desc']
			],
			"columnDefs": [{
				"searchable": false,
				"targets": [3, 4, 5],
				"orderable": false,
				"targets": [5]
			}],
			"ajax": {
				url: "api/internal/devices.php?platform=<?php echo $platform ?>",
				data: {
					"filter": {
						'extension': 	'<?= $filter_list->getFilter('extension') ?>',
						'submitter':	'<?= $filter_list->getFilter('submitter') ?>',
					}
				},
				error: function(xhr, error, thrown) {
					$('#errordiv').html('Could not fetch data (' + error + ')');
					$('#devices_processing').hide();
				}
			},
			"columns": [{
					data: 'device'
				},
				{
					data: 'api'
				},
				{
					data: 'driver'
				},
				// { data: 'reportversion' },
				{
					data: 'submissiondate'
				},
				{
					data: 'reportcount'
				},
				{
					data: 'compare'
				}
			],
			// Pass order by column information to server side script
			fnServerParams: function(data) {
				data['order'].forEach(function(items, index) {
					data['order'][index]['column'] = data['columns'][items.column]['data'];
				});
			},
		});

		yadcf.init(table, [{
				column_number: 0,
				filter_type: "text",
				filter_delay: 500,
				style_class: "filter-240"
			},
			{
				column_number: 1,
				filter_type: "text",
				filter_delay: 500
			},
			{
				column_number: 2,
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