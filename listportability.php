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
include './database/database.class.php';

$pageTitle = null;
if (isset($_GET['platform'])) {
	$platform = $_GET['platform'];
}
PageGenerator::header($pageTitle == null ? "Reports" : "Reports for $pageTitle");
?>

<center>
	<div class='header'>
		<h4>Reports supporting the portability subset extension <code>VK_KHR_portability_subset</code></h4>
	</div>
	<div class='tablediv tab-content' style='display: inline-flex;'>
		<form method="get" action="compare.php?compare">
			<table id='reports' class='table table-striped table-bordered table-hover responsive' style='width:auto'>
				<thead>
					<tr>
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
						<th>Device</th>
						<th>OS</th>
						<th>Version</th>
						<th>Driver</th>
						<th>Api</th>
						<th>DevSim JSON</th>
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
				"targets": [0],
			}],
			"ajax": {
				url: "responses/listreports.php",
				data: {
					"filter": {
						'portability': 1,
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
				{
					data: 'device'
				},
				{
					data: 'osname'
				},
				{
					data: 'osversion'
				},
				{
					data: 'driver'
				},
				{
					data: 'api'
				},
				{
					data: 'devsim'
				},
			],
			// Pass order by column information to server side script
			fnServerParams: function(data) {
				data['order'].forEach(function(items, index) {
					data['order'][index]['column'] = data['columns'][items.column]['data'];
				});
			},
		});

		yadcf.init(table, [{
				column_number: 1,
				filter_type: "text",
				filter_delay: 500,
				style_class: "filter-240"
			},
			{
				column_number: 2,
				filter_type: "text",
				filter_delay: 500
			},
			{
				column_number: 3,
				filter_type: "text",
				filter_delay: 500
			},
			{
				column_number: 4,
				filter_type: "text",
				filter_delay: 500
			},
			{
				column_number: 5,
				filter_type: "text",
				filter_delay: 500
			},
			{
				column_number: 6,
				filter_type: "text",
				filter_delay: 500
			}
		], {
			filters_tr_index: 0
		});

	});
</script>

<?php PageGenerator::footer(); ?>

</body>

</html>