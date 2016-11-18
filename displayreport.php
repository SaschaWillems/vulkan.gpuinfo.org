		<?php
			include './header.inc';			
			/* 		
				*
				* Vulkan hardware capability database server implementation
				*	
				* Copyright (C) 2016 by Sascha Willems (www.saschawillems.de)
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
			
			dbConnect();	
			
			// TODO : Param to only dislay report (centered, logo) without menu
			
			$reportID = mysql_real_escape_string($_GET['id']); 
			$reportDisplay = mysql_real_escape_string($_GET['display']);
			
			if ($reportID == '')
			{
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
			$sqlresult = mysql_query("select 
				p.devicename,
				p.driverversion,
				p.devicetype,
				VendorId(p.vendorid) as 'vendor',
				r.osname,
				r.osarchitecture,
				r.osversion
			from reports r
			left join
			deviceproperties p on (p.reportid = r.id)
			where r.id = $reportID") or die(mysql_error());			
			$row = mysql_fetch_assoc($sqlresult);
			$present = (mysql_num_rows($sqlresult) > 0);
			$devicedescription = $row['vendor']." ".$row['devicename'];
			
			if (!$present) 
			{
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
			$extcount = mysql_result(mysql_query("select count(*) from deviceextensions where reportid = $reportID"), 0);
			$formatcount = mysql_result(mysql_query("select count(*) from deviceformats where reportid = $reportID and (lineartilingfeatures > 0 or optimaltilingfeatures > 0 or bufferfeatures > 0)"), 0);			
			$queuecount = mysql_result(mysql_query("select count(*) from devicequeues where reportid = $reportID"), 0);			
			$memtypecount = mysql_result(mysql_query("select count(*) from devicememorytypes where reportid = $reportID"), 0);			
			$layercount = mysql_result(mysql_query("select count(*) from devicelayers where reportid = $reportID"), 0);			
			$hassurfacecaps = (mysql_result(mysql_query("select count(*) from devicesurfacecapabilities where reportid"), 0)) > 0;
		
			echo "<center>";				
		
			// Header =====================================================================================
			echo "<div class='header'>";
			if ($reportDisplay == 'reportonly')
				echo "<img src='./images/vulkan48.png' width='175px' style='padding-top:10px';><br>";				
			echo "Device report for $devicedescription";
			echo "</div>";			
		
			// Nav ========================================================================================
			echo "<div>";
			echo "<ul class='nav nav-tabs'>";
			echo "	<li class='active'><a data-toggle='tab' href='#device'>Device</a></li>";
			echo "	<li><a data-toggle='tab' href='#features'>Features</a></li>";
			echo "	<li><a data-toggle='tab' href='#limits'>Limits</a></li>";
			echo "	<li><a data-toggle='tab' href='#extensions'>Extensions <span class='badge'>$extcount</span></a></li>";
			echo "	<li><a data-toggle='tab' href='#formats'>Formats <span class='badge'>$formatcount</span></a></a></li>";
			echo "	<li><a data-toggle='tab' href='#queuefamilies'>Queue families <span class='badge'>$queuecount</span></a></li>";
			echo "	<li><a data-toggle='tab' href='#memory'>Memory <span class='badge'>$memtypecount</span></a></a></li>";
			if ($hassurfacecaps) 
			{
				echo "	<li><a data-toggle='tab' href='#surface'>Surface</a></a></li>";
			}
			echo "	<li><a data-toggle='tab' href='#layers'>Layers <span class='badge'>$layercount</span></a></li>";
			echo "</ul>";
			echo "</div>";
			
			echo "<div class='tablediv tab-content' style='width:75%;'>";
					
			// Device properites ============================================================================
			echo "<div id='device' class='tab-pane fade in active reportdiv'>";
			include './displayreport_properties.php';									
			echo "</div>";
			
			// Device features ==============================================================================
			echo "<div id='features' class='tab-pane fade reportdiv'>";
			echo "<table id='devicefeatures' class='table table-striped table-bordered table-hover responsive' style='width:100%;'>";
			echo "<thead><tr><td class='caption'>Feature</td><td class='caption'>Value</td></tr></thead><tbody>";
			
			$sqlresult = mysql_query("select * from devicefeatures where reportid = $reportID") or die(mysql_error());
			while($row = mysql_fetch_row($sqlresult))
			{
				for($i = 0; $i < count($row); $i++)
				{
					$fname = mysql_field_name($sqlresult, $i);		  			
					if ($fname == 'reportid')
						continue;					
					$value = $row[$i];
					echo "<tr><td class='key'>$fname</td><td>";					
					echo ($value == 1) ? "<font color='green'>true</font>" : "<font color='red'>false</font>";
					echo "</td></tr>\n";
				}				
			}
			
			echo "</tbody></table>";					
			echo "</div>";			
			
			// Device limits ================================================================================
			echo "<div id='limits' class='tab-pane fade reportdiv'>";
			echo "<table id='devicelimits' class='table table-striped table-bordered table-hover responsive' style='width:100%;'>";
			echo "<thead><tr><td class='caption'>Limit</td><td class='caption'>Value</td></tr></thead><tbody>";
			
			$sqlresult = mysql_query("select * from devicelimits where reportid = $reportID") or die(mysql_error());
			while($row = mysql_fetch_row($sqlresult))
			{
				for($i = 0; $i < count($row); $i++)
				{
					$fname = mysql_field_name($sqlresult, $i);		  			
					if ($fname == 'reportid')
						continue;
					echo "<tr><td class='key'>$fname</td>";
					if (strpos($fname, 'SampleCounts'))
					{
						$sampleCountflags = getSampleCountFlags($row[$i]);						
						if (count($sampleCountflags) > 0)
						{
							echo "<td>".implode(",", $sampleCountflags)."</td>";
						}
						else
						{
							echo "<td><font color='red'>none</font></td>";
						}
					}
					else
					{
						echo "<td>".$row[$i]."</td>";
					}
					echo "</td></tr>\n";
				}				
			}
			
			echo "</tbody></table>";					
			echo "</div>";					
			
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
			echo "<div id='layers' class='tab-pane fade reportdiv'>";
			include './displayreport_layers.php';
			echo "</div>";					
						
			
?>

	<script>
		$(document).ready(
		function() {
			var tableNames = ['deviceproperties', 'devicefeatures', 'devicelimits', 'deviceextensions', 'deviceformats', 'devicelayers', 'devicequeues', 'devicememory', 'devicelayerextensions', 'devicememoryheaps', 'devicememorytypes', 'devicesurfaceproperties'];
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
						}
					);
			}	
			
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
				"SAMPLED_IMAGE_FILTER_LINEAR_BIT" : 0x1000
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
				// Nested memory tabs, need to show parent tab too
				if ((a === '#memorytypes') || (a === '#memoryheaps'))
				{
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