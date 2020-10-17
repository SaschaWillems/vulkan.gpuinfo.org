<?php
	/* 		
		*
		* Vulkan hardware capability database server implementation
		*	
		* Copyright (C) 2016-2020 by Sascha Willems (www.saschawillems.de)
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
			
	include 'page_generator.php';

	include './dbconfig.php';
	include './functions.php';
	include './constants.php';
	include './report.class.php';
	
	$reportID = $_GET['id']; 	
	if ($reportID == '') {
		PageGenerator::header();
		?>
		<div class="div-h-center">
			<div class="div-alert alert alert-danger error">
				<strong>Warning!</strong><br> No report ID set to display!
			</div>
		</div>
		<?php
		PageGenerator::footer();
		die();
	}

	$report = new Report($reportID);
	$report->fetchData();
	
	if (!$report->exists()) {
		PageGenerator::header();
		?>
		<div class="div-h-center">
			<div class="div-alert alert alert-danger error">
				<strong>This is not the <strike>droid</strike> report you are looking for!</strong><br><br>
				Could not find report with ID <?php echo $reportID; ?> in database.<br>
				It may have been removed due to faulty data.
			</div>
		</div>
		<?php
		PageGenerator::footer();
		die();
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
	$header = "Device report for ".$report->info->device_description;
	if ($report->info->platform !== null) {
		$header .= " on <img src='images/".$report->info->platform."logo.png' height='14px' style='padding-right:5px'/>".ucfirst($report->info->platform);
	}
	echo "<div class='header'>";
	echo "<h4>$header</h4>";
	echo "</div>";			

	// Nav ========================================================================================
?>			
	<div>
		<ul class='nav nav-tabs nav-report'>
			<li class='active'><a data-toggle='tab' href='#device'>Device</a></li>
			<li><a data-toggle='tab' href='#properties'>Properties</a></li>
			<li><a data-toggle='tab' href='#features'>Features</a></li>
			<li><a data-toggle='tab' href='#limits'>Limits</a></li>
			<li><a data-toggle='tab' href='#extensions'>Extensions <span class='badge'><?php echo $extcount ?></span></a></li>
			<li><a data-toggle='tab' href='#formats'>Formats <span class='badge'><?php echo $formatcount ?></span></a></a></li>
			<li><a data-toggle='tab' href='#queuefamilies'>Queue families <span class='badge'><?php echo $queuecount ?></span></a></li>
			<li><a data-toggle='tab' href='#memory'>Memory <span class='badge'><?php echo $memtypecount ?></span></a></a></li>
			<?php if ($report->flags->has_surface_caps) { echo "<li><a data-toggle='tab' href='#surface'>Surface</a></a></li>"; } ?>
			<?php if ($report->flags->has_instance_data) { echo "<li><a data-toggle='tab' href='#instance'>Instance</a></li>"; } ?>
		</ul>
	</div>
	
	<div class='tablediv tab-content' style='width:75%;'>

<?php					

	// Device information
	echo "<div id='device' class='tab-pane fade in active reportdiv'>";
	include './displayreport_deviceinfo.php';									
	echo "</div>";

	// Device properties
	echo "<div id='properties' class='tab-pane fade reportdiv'>";
	include 'displayreport_properties.php';
	echo "</div>";		

	// Device features
	echo "<div id='features' class='tab-pane fade reportdiv'>";
	include 'displayreport_features.php';									
	echo "</div>";			
	
	// Device limits
	echo "<div id='limits' class='tab-pane fade reportdiv'>";
	include 'displayreport_limits.php';
	echo "</div>";		

	// Extensions
	echo "<div id='extensions' class='tab-pane fade reportdiv'>";
	include './displayreport_extensions.php';					
	echo "</div>";	
	
	// Formats
	echo "<div id='formats' class='tab-pane fade reportdiv'>";
	include './displayreport_formats.php';		
	echo "</div>";		
				
	// Queues
	echo "<div id='queuefamilies' class='tab-pane fade reportdiv'>";			
	include './displayreport_queues.php';
	echo "</div>";
	
	// Memory properties
	echo "<div id='memory' class='tab-pane fade reportdiv'>";		
	include './displayreport_memory.php';
	echo "</div>";

	// Surface properties
	if ($report->flags->has_surface_caps)
	{	
		echo "<div id='surface' class='tab-pane fade reportdiv'>";
		include './displayreport_surface.php';
		echo "</div>";					
	}

	// Instance
	if ($report->flags->has_instance_data) {
		echo "<div id='instance' class='tab-pane fade reportdiv'>";
		include 'displayreport_instance.php';
		echo "</div>";	
	}						
?>


	<script>
		$(document).ready(
		function() {
			var tableNames = [ 
				'devicelimits', 
				'deviceextensions', 
				'deviceformats', 
				'devicelayers', 
				'devicequeues', 
				'devicememory', 
				'devicelayerextensions', 
				'devicememoryheaps', 
				'devicememorytypes', 
				'devicesurfaceproperties',
				'instanceextensions',
				'instancelayers'
			];
			for (var i = 0, arrlen = tableNames.length; i < arrlen; i++)
			{
					$('#'+tableNames[i]).dataTable(
						{
							"pageLength" : -1,
							"paging" : false,
							"order": [], 
							"searchHighlight": true,
							"bAutoWidth": false,
							"sDom": 'flpt',
							"deferRender": true,
							"processing": true			
						}
					);
			}

			// Grouped tables
			tableNames = 
			[
				'deviceinfo',
				'devicefeatures', 
				'devicefeatures_extensions',
				'deviceproperties',
				'deviceproperties_extensions',
			];

			// Device properties table with grouping
			for (var i = 0, arrlen = tableNames.length; i < arrlen; i++)
			{
					$('#'+tableNames[i]).dataTable(
						{
							"pageLength" : -1,
							"paging" : false,
							"order": [], 
							"columnDefs": [
								{ "visible": false, "targets": 2 }
							],				
							"searchHighlight": true,
							"bAutoWidth": false,
							"sDom": 'flpt',
							"deferRender": true,
							"processing": true,
							"drawCallback": function (settings) {
								var api = this.api();
								var rows = api.rows( {page:'current'} ).nodes();
								var last = null;
								api.column(2, {page:'current'} ).data().each( function ( group, i ) {
									if ( last !== group ) {
										$(rows).eq( i ).before(
											'<tr><td colspan="2" class="group">'+group+'</td></tr>'
										);
										last = group;
									}
								});
							}
						}
					);			
			}

			// Extended features table with grouping
			$('#extended_features').dataTable(
				{
					"pageLength" : -1,
					"paging" : false,
					"order": [], 
					"columnDefs": [
						{ "visible": false, "targets": 2 }
					],				
					"searchHighlight": true,
					"bAutoWidth": false,
					"sDom": 'flpt',
					"deferRender": true,
					"processing": true,
					"drawCallback": function (settings) {
						var api = this.api();
						var rows = api.rows( {page:'current'} ).nodes();
						var last = null;
						api.column(2, {page:'current'} ).data().each( function ( group, i ) {
							if ( last !== group ) {
								$(rows).eq( i ).before(
									'<tr><td colspan="2" class="group">'+group+'</td></tr>'
								);
								last = group;
							}
						});
					}
				}
			);			

			// Extended properties table with grouping
			$('#extended_properties').dataTable(
				{
					"pageLength" : -1,
					"paging" : false,
					"order": [], 
					"columnDefs": [
						{ "visible": false, "targets": 2 }
					],				
					"searchHighlight": true,
					"bAutoWidth": false,
					"sDom": 'flpt',
					"deferRender": true,
					"processing": true,
					"drawCallback": function (settings) {
						var api = this.api();
						var rows = api.rows( {page:'current'} ).nodes();
						var last = null;
						api.column(2, {page:'current'} ).data().each( function ( group, i ) {
							if ( last !== group ) {
								$(rows).eq( i ).before(
									'<tr><td class="group" colspan="2">'+group+'</td></tr>'
								);
								last = group;
							}
						});
					}
				}
			);							
					
			// Collapsible format flags
			var table = $('#deviceformats').DataTable();
			table.columns(5).visible(false);
			table.columns(6).visible(false);
			table.columns(7).visible(false);
	
			$('#deviceformats tbody').on('click', 'td.details-control', function () 
			{
				var tr = $(this).closest('tr');
				var table = $('#deviceformats').DataTable();
				var row = table.row( tr );
		 
				if ( row.child.isShown() ) 
				{
					// This row is already open - close it
					row.child.hide();
					tr.removeClass('shown');
				}
				else 
				{
					// Open this row
					row.child( formatFlags(row.data()) ).show();
					tr.addClass('shown');
				}
			} );			
			
			// Collapsible layer extensions
			var table = $('#devicelayers').DataTable();
			table.columns(4).visible(false);
			$('#devicelayers tbody').on('click', 'td.details-control', function () 
			{
				var tr = $(this).closest('tr');
				var table = $('#devicelayers').DataTable();
				var row = table.row( tr );
		 
				if ( row.child.isShown() ) 
				{
					row.child.hide();
					tr.removeClass('shown');
				}
				else 
				{
					// Open this row
					row.child( layerExtensions(row.data()) ).show();
					tr.addClass('shown');
				}
			} );						
					
		} );	
		
		function checkFlags(flags)
		{
			var formatEnums =  {
				"SAMPLED_IMAGE_BIT" : 0x0001,
				"STORAGE_IMAGE_BIT" : 0x0002,
				"STORAGE_IMAGE_ATOMIC_BIT" : 0x0004,
				"UNIFORM_TEXEL_BUFFER_BIT" : 0x0008,
				"STORAGE_TEXEL_BUFFER_BIT" : 0x0010,
				"STORAGE_TEXEL_BUFFER_ATOMIC_BIT" : 0x0020,
				"VERTEX_BUFFER_BIT" : 0x0040,
				"COLOR_ATTACHMENT_BIT" : 0x0080,
				"COLOR_ATTACHMENT_BLEND_BIT" : 0x0100,
				"DEPTH_STENCIL_ATTACHMENT_BIT" : 0x0200,
				"BLIT_SRC_BIT" : 0x0400,
				"BLIT_DST_BIT" : 0x0800,
				"SAMPLED_IMAGE_FILTER_LINEAR_BIT" : 0x1000,
				"TRANSFER_SRC_BIT" : 0x4000,
				"TRANSFER_DST_BIT" : 0x8000,
			};
			
			if (flags == 0)
			{
			return "<ul><li>none</li></ul>";
			}
			
			var features = "<ul>";
			for(var key in formatEnums) 
			{
				var flag = formatEnums[key];
				if ((flag &  flags) == flag) 
				{
					features += "<li>" + key + "</li>";
				}
			}			
			features += "</ul>";
			
			return features;
		}
		
		// Format flag detail row
		function formatFlags(d) 
		{
			// `d` is the original data object for the row
			return '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">'+
				'<tr>'+
					'<td><b>Linear tiling features</b></td>'+
				'</tr>'+
				'<tr>'+
					'<td>' + checkFlags(d[5]) + '</td>'+
				'</tr>' +
				'<tr>'+
					'<td><b>Optimal tiling features</b></td>'+
				'</tr>'+
				'<tr>'+
					'<td>' + checkFlags(d[6]) + '</td>'+
				'</tr>' +
				'<tr>'+
					'<td><b>Buffer features</b></td>'+
				'</tr>'+
				'<tr>'+
					'<td>' + checkFlags(d[7]) + '</td>'+
				'</tr>' +
			'</table>';
		}
		
		// Layer extensions detail row
		function layerExtensions(d) 
		{
			return '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">'+
				'<tr>'+
					'<td><b>Extensions</b></td>'+
				'</tr>'+
				'<tr>'+
					'<td>' + (d[4]) + '</td>'+
				'</tr>' +
			'</table>';
		}
		
		$(function() 
		{
			var a = document.location.hash;
			if (a) 
			{
				// Nested tabs, need to show parent tab too
				if ((a === '#memorytypes') || (a === '#memoryheaps')) {
					$('.nav a[href=\\#memory]').tab('show');
				}
				if ((a === '#features_core') || (a === '#features_extensions')) {
					$('.nav a[href=\\#features]').tab('show');
				}
				if ((a === '#properties_core') || (a === '#properties_extensions')) {
					$('.nav a[href=\\#properties]').tab('show');
				}
				$('.nav a[href=\\'+a+']').tab('show');
			}
		
			$('a[data-toggle="tab"]').on('show.bs.tab', function (e) 
			{
				window.location.hash = e.target.hash;
			});
		});

	   </script>	
	</div>
	
	<?php PageGenerator::footer(); ?>
	
	</center>
					
</body>
</html>