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
require './dbconfig.php';
require './functions.php';
require './constants.php';
require './report.class.php';

$reportID = $_GET['id'];
if (!$reportID) {
	PageGenerator::errorMessage("<strong>Warning!</strong><br> No report ID set to display!");
}

$report = new Report($reportID);
$report->fetchData();

if (!$report->exists()) {
	PageGenerator::errorMessage("
		<strong>This is not the <strike>droid</strike> report you are looking for!</strong><br><br>
		Could not find report with ID <?php echo $reportID; ?> in database.<br>
		It may have been removed due to faulty data."
	);
}

PageGenerator::header($report->info->device_description);

// Counters
try {
	DB::connect();
	$extcount = DB::getCount("SELECT count(*) from deviceextensions where reportid = :reportid", [':reportid' => $reportID]);
	$formatcount = DB::getCount("SELECT count(*) from deviceformats where reportid = :reportid and (lineartilingfeatures > 0 or optimaltilingfeatures > 0 or bufferfeatures > 0)", [':reportid' => $reportID]);
	$queuecount = DB::getCount("SELECT count(*) from devicequeues where reportid = :reportid", [':reportid' => $reportID]);
	$memtypecount = DB::getCount("SELECT count(*) from devicememorytypes where reportid = :reportid", [':reportid' => $reportID]);
	$memheapcount = DB::getCount("SELECT count(*) from devicememoryheaps where reportid = :reportid", ["reportid" => $reportID]);
	$surfaceformatscount =  DB::getCount("SELECT count(*) from devicesurfaceformats where reportid = :reportid", [':reportid' => $reportID]);
	$surfacepresentmodescount =  DB::getCount("SELECT count(*) from devicesurfacemodes where reportid = :reportid", [':reportid' => $reportID]);
} catch (PDOException $e) {
	DB::disconnect();
	die("<b>Error while fetcthing report data!</b><br>");
}
echo "<center>";

// Header
$header = "Device report for " . $report->info->device_description;
if ($report->info->platform !== null) {
	$header .= " on <img src='images/" . $report->info->platform . "logo.png' height='14px' style='padding-right:5px'/>" . ucfirst($report->info->platform);
}
echo "<div class='header'>";
echo "<h4>$header</h4>";
echo "</div>";

?>
<div>
	<ul class='nav nav-tabs nav-report'>
		<li class='active'><a data-toggle='tab' href='#device'>Device</a></li>
		<li><a data-toggle='tab' href='#properties'>Properties</a></li>
		<li><a data-toggle='tab' href='#features'>Features</a></li>
		<li><a data-toggle='tab' href='#extensions'>Extensions <span class='badge'><?php echo $extcount ?></span></a></li>
		<li><a data-toggle='tab' href='#formats'>Formats <span class='badge'><?php echo $formatcount ?></span></a></a></li>
		<li><a data-toggle='tab' href='#queuefamilies'>Queue families <span class='badge'><?php echo $queuecount ?></span></a></li>
		<li><a data-toggle='tab' href='#memory'>Memory <span class='badge'><?php echo $memtypecount ?></span></a></a></li>
		<?php if ($report->flags->has_surface_caps) {
			echo "<li><a data-toggle='tab' href='#surface'>Surface</a></a></li>";
		} ?>
		<?php if ($report->flags->has_instance_data) {
			echo "<li><a data-toggle='tab' href='#instance'>Instance</a></li>";
		} ?>
	</ul>
</div>

<div class='tablediv tab-content' style='width:75%;'>

	<?php
	$views = [
		'device',
		'properties',
		'features',
		'extensions',
		'formats',
		'queuefamilies',
		'memory'
	];
	if ($report->flags->has_surface_caps) {
		$views[] = 'surface';
	}
	if ($report->flags->has_instance_data) {
		$views[] = 'instance';
	}
	foreach ($views as $index => $view) {
		echo "<div id='$view' class='tab-pane fade ".($index == 0 ? "in active" : null)." reportdiv'>";
			include "reportdisplay/$view.php";
		echo "</div>";
	}	

	if ($report->flags->has_update_history) {
		include 'reportdisplay/history.php';
	}
	?>

	<script>
		$(document).ready(
			function() {
				var tableNames = [
					'deviceextensions',
					'devicelayerextensions',
					'devicememoryheaps',
					'devicememorytypes',
					'devicesurfaceproperties',
					'deviceinstanceextensions',
					'deviceinstancelayers',
					'table_features_core_10',
					'table_features_core_11',
					'table_features_core_12',
					'table_properties_core_11',
					'table_properties_core_12'
				];
				for (var i = 0, arrlen = tableNames.length; i < arrlen; i++) {
					if (typeof $('#' + tableNames[i]) != undefined) {
						$('#' + tableNames[i]).dataTable({
							"pageLength": -1,
							"paging": false,
							"order": [],
							"searchHighlight": true,
							"bAutoWidth": false,
							"sDom": 'flpt',
							"deferRender": true,
							"processing": true
						});
					}
				}

				// Grouped tables
				tableNames = [
					'table_device',
					'table_properties_core_10',
					'table_features_extensions',
					'table_properties_extensions',
					'deviceproperties',
					'deviceproperties_extensions',
					'devicememory'
				];

				// Device properties table with grouping
				for (var i = 0, arrlen = tableNames.length; i < arrlen; i++) {
					if (typeof $('#' + tableNames[i]) != undefined) {
						$('#' + tableNames[i]).dataTable({
							"pageLength": -1,
							"paging": false,
							"order": [],
							"columnDefs": [{
								"visible": false,
								"targets": 2
							}],
							"searchHighlight": true,
							"bAutoWidth": false,
							"sDom": 'flpt',
							"deferRender": true,
							"processing": true,
							"drawCallback": function(settings) {
								var api = this.api();
								var rows = api.rows({
									page: 'current'
								}).nodes();
								var last = null;
								api.column(2, {
									page: 'current'
								}).data().each(function(group, i) {
									if (last !== group) {
										$(rows).eq(i).before(
											'<tr><td colspan="2" class="group">' + group + '</td></tr>'
										);
										last = group;
									}
								});
							}
						});
					}
				}

				// Feature tables
				tableNames = [
					'deviceformats_linear',
					'deviceformats_optimal',
					'deviceformats_buffer',
					'devicequeues'
				];
				for (var i = 0, arrlen = tableNames.length; i < arrlen; i++) {
					$('#' + tableNames[i]).dataTable({
						"pageLength": -1,
						"paging": false,
						"order": [],
						"searchHighlight": true,
						"bAutoWidth": false,
						"sDom": 'flpt',
						"deferRender": true,
						"processing": true,
						"ordering": false,
						"columnDefs": [{
							"orderable": true,
							"targets": 0
						}],
						"fixedHeader": {
							"header": true,
							"headerOffset": 50
						},
					});
				}

				// Extended features table with grouping
				$('#extended_features').dataTable({
					"pageLength": -1,
					"paging": false,
					"order": [],
					"columnDefs": [{
						"visible": false,
						"targets": 2
					}],
					"searchHighlight": true,
					"bAutoWidth": false,
					"sDom": 'flpt',
					"deferRender": true,
					"processing": true,
					"drawCallback": function(settings) {
						var api = this.api();
						var rows = api.rows({
							page: 'current'
						}).nodes();
						var last = null;
						api.column(2, {
							page: 'current'
						}).data().each(function(group, i) {
							if (last !== group) {
								$(rows).eq(i).before(
									'<tr><td colspan="2" class="group">' + group + '</td></tr>'
								);
								last = group;
							}
						});
					}
				});

				// Extended properties table with grouping
				$('#extended_properties').dataTable({
					"pageLength": -1,
					"paging": false,
					"order": [],
					"columnDefs": [{
						"visible": false,
						"targets": 2
					}],
					"searchHighlight": true,
					"bAutoWidth": false,
					"sDom": 'flpt',
					"deferRender": true,
					"processing": true,
					"drawCallback": function(settings) {
						var api = this.api();
						var rows = api.rows({
							page: 'current'
						}).nodes();
						var last = null;
						api.column(2, {
							page: 'current'
						}).data().each(function(group, i) {
							if (last !== group) {
								$(rows).eq(i).before(
									'<tr><td class="group" colspan="2">' + group + '</td></tr>'
								);
								last = group;
							}
						});
					}
				});

			});

		$(function() {
			var a = document.location.hash;
			if (a) {
				// Nested tabs, need to show parent tab too
				if ((a === '#features_core') || (a === '#features_extensions')) {
					$('.nav a[href=\\#features]').tab('show');
				}
				if ((a === '#properties_core') || (a === '#properties_extensions')) {
					$('.nav a[href=\\#properties]').tab('show');
				}
				if ((a === '#formats_linear') || (a === '#formats_optimal') || (a === '#formats_buffer')) {
					$('.nav a[href=\\#formats]').tab('show');
				}
				if ((a === '#instanceextensions') || (a === '#instancelayers')) {
					$('.nav a[href=\\#instance]').tab('show');
				}
				if ((a === '#surfaceproperties') || (a === '#surfaceformats') || (a === '#presentmodes')) {
					$('.nav a[href=\\#surface]').tab('show');
				}
				$('.nav a[href=\\' + a + ']').tab('show');
			}

			$('a[data-toggle="tab"]').on('show.bs.tab', function(e) {
				window.location.hash = e.target.hash;
			});
		});
	</script>
</div>

<?php PageGenerator::footer(); ?>

</center>

</body>

</html>