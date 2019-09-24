<?php
			include './header.inc';			
			/* 		
				*
				* Vulkan hardware capability database server implementation
				*	
				* Copyright (C) 2016-2018 by Sascha Willems (www.saschawillems.de)
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
			
			include './dbconfig.php';
			include './functions.php';
			
			DB::connect();
			
			$reportID = $_GET['id']; 
			
			if ($reportID == '') {
				echo "<center>";
				?>
				<div class="alert alert-warning">
				  <strong>Warning!</strong><br> No report ID set to display!
				</div>				
				<?php
				include './footer.inc';
				echo "</center>";
				die();
			}
						
			// Descriptions 
			$sql = "SELECT
				p.devicename,
				p.driverversion,
				p.devicetype,
				VendorId(p.vendorid) as 'vendor',
				r.osname,
				r.osarchitecture,
				r.osversion,
				r.version as reportversion,
				r.ostype
				from reports r
				left join
				deviceproperties p on (p.reportid = r.id)
				where r.id = :reportid";	

			try {
				$stmnt = DB::$connection->prepare($sql); 
				$stmnt->execute([':reportid' => $reportID]); 
				$present = $stmnt->rowCount() > 0;
				$row = $stmnt->fetch(PDO::FETCH_ASSOC);
				$devicedescription = $row['vendor']." ".$row['devicename'];
				$devicename = $row['devicename'];
				$reportversion = $row['reportversion'];
				$ostype = $row['ostype'];
				$platform = platformname($ostype);
			} catch (PDOException $e) {
				echo "<b>Error while fetcthing report!</b><br>";
			}
		
			if (!$present) {
				echo "<center>";
				?>
				<div class="alert alert-danger error">
				  <strong>This is not the <strike>droid</strike> report you are looking for!</strong><br><br>
				  Could not find report with ID <?php echo $reportID ?> in database.<br>
				  It may have been removed due to faulty data.
				</div>				
				<?php
				include './footer.inc';
				echo "</center>";
				die();			
			}
		
			// Counters
			$extcount = DB::getCount("SELECT count(*) from deviceextensions where reportid = :reportid", [':reportid' => $reportID]);
			$formatcount = DB::getCount("SELECT count(*) from deviceformats where reportid = :reportid and (lineartilingfeatures > 0 or optimaltilingfeatures > 0 or bufferfeatures > 0)", [':reportid' => $reportID]);
			$queuecount = DB::getCount("SELECT count(*) from devicequeues where reportid = :reportid", [':reportid' => $reportID]);
			$memtypecount = DB::getCount("SELECT count(*) from devicememorytypes where reportid = :reportid", [':reportid' => $reportID]);
			$memheapcount = DB::getCount("SELECT count(*) from devicememoryheaps where reportid = :reportid", ["reportid" => $reportID]);
			$layercount = DB::getCount("SELECT count(*) from devicelayers where reportid = :reportid", [':reportid' => $reportID]);
			$surfaceformatscount =  DB::getCount("SELECT count(*) from devicesurfaceformats where reportid = :reportid", [':reportid' => $reportID]);
			$surfacepresentmodescount =  DB::getCount("SELECT count(*) from devicesurfacemodes where reportid = :reportid", [':reportid' => $reportID]);			
		
			$hassurfacecaps = DB::getCount("SELECT count(*) from devicesurfacecapabilities where reportid = :reportid", [':reportid' => $reportID]) > 0;
			$hasextended =  DB::getCount("SELECT (select count(*) from devicefeatures2 where reportid = :reportid) + (select count(*) from deviceproperties2 where reportid = :reportid)", [':reportid' => $reportID]) > 0;
			$hasinstance =  DB::getCount("SELECT (select count(*) from deviceinstanceextensions where reportid = :reportid) + (select count(*) from deviceinstancelayers where reportid = :reportid)", [':reportid' => $reportID]) > 0;
		
			echo "<center>";				
		
			// Header =====================================================================================
			$header = "Device report for $devicedescription";
			if ($platform !== null) {
				$header .= " on <img src='images/".$platform."logo.png' height='14px' style='padding-right:5px'/>".ucfirst($platform);
			}
			echo "<div class='header'>";
			echo "<h4>$header</h4>";
			if ($reportversion >= '1.4') {
				echo "<a href=\"api/v2/devsim/getreport.php?id=".$reportID."\" class=\"btn btn-default\" title=\"Download a Vulkan device simulation layer compatible JSON file\"><span class=\"glyphicon glyphicon-floppy-save\"></span> JSON</a>";
			}
			echo "</div>";			
		
			// Nav ========================================================================================
?>			
			<div>
				<ul class='nav nav-tabs'>
					<li class='active'><a data-toggle='tab' href='#device'>Device</a></li>
					<li><a data-toggle='tab' href='#features'>Features</a></li>
					<li><a data-toggle='tab' href='#limits'>Limits</a></li>
					<?php if ($hasextended) { echo "<li><a data-toggle='tab' href='#extended'>Extended</a></a></li>"; } ?>
					<li><a data-toggle='tab' href='#extensions'>Extensions <span class='badge'><?php echo $extcount ?></span></a></li>
					<li><a data-toggle='tab' href='#formats'>Formats <span class='badge'><?php echo $formatcount ?></span></a></a></li>
					<li><a data-toggle='tab' href='#queuefamilies'>Queue families <span class='badge'><?php echo $queuecount ?></span></a></li>
					<li><a data-toggle='tab' href='#memory'>Memory <span class='badge'><?php echo $memtypecount ?></span></a></a></li>
					<?php if ($hassurfacecaps) { echo "<li><a data-toggle='tab' href='#surface'>Surface</a></a></li>"; } ?>
					<!-- <li><a data-toggle='tab' href='#layers'>Layers <span class='badge'>$layercount</span></a></li>"; -->
					<?php if ($hasinstance) { echo "<li><a data-toggle='tab' href='#instance'>Instance</a></li>"; } ?>
				</ul>
			</div>
			
			<div class='tablediv tab-content' style='width:75%;'>

<?php					
			// Device properites ============================================================================
			echo "<div id='device' class='tab-pane fade in active reportdiv'>";
			include './displayreport_properties.php';									
			echo "</div>";
			
			// Device features ==============================================================================

			echo "<div id='features' class='tab-pane fade reportdiv'>";
			include 'displayreport_features.php';									
			echo "</div>";			
			
			// Device limits ================================================================================
			echo "<div id='limits' class='tab-pane fade reportdiv'>";
			include 'displayreport_limits.php';
			echo "</div>";		

			// Extended features and properites =============================================================
			if ($hasextended) {	
				echo "<div id='extended' class='tab-pane fade reportdiv'>";
				include './displayreport_extended.php';
				echo "</div>";					
			}		
			
			// Extensions ===================================================================================
			echo "<div id='extensions' class='tab-pane fade reportdiv'>";
			include './displayreport_extensions.php';					
			echo "</div>";	
			
			// Formats ======================================================================================
			echo "<div id='formats' class='tab-pane fade reportdiv'>";
			include './displayreport_formats.php';		
			echo "</div>";		
						
			// Queues =======================================================================================
			echo "<div id='queuefamilies' class='tab-pane fade reportdiv'>";			
			include './displayreport_queues.php';
			echo "</div>";
			
			// Memory properties ============================================================================
			echo "<div id='memory' class='tab-pane fade reportdiv'>";		
			include './displayreport_memory.php';
			echo "</div>";

			// Surface properties ============================================================================
			if ($hassurfacecaps)
			{	
				echo "<div id='surface' class='tab-pane fade reportdiv'>";
				include './displayreport_surface.php';
				echo "</div>";					
			}

			// Layers ========================================================================================
			// echo "<div id='layers' class='tab-pane fade reportdiv'>";
			// include './displayreport_layers.php';
			// echo "</div>";					

			// Instance ======================================================================================
			if ($hasinstance) {
				echo "<div id='instance' class='tab-pane fade reportdiv'>";
				include 'displayreport_instance.php';
				echo "</div>";	
			}						

			DB::disconnect();
?>

	<script>
		$(document).ready(
		function() {
			var tableNames = [ 
				'devicefeatures', 
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

			// Device properties table with grouping
			$('#deviceproperties').dataTable(
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
					console.log(a);
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
	
	<?php include './footer.inc'; ?>
	
	</center>
					
</body>
</html>