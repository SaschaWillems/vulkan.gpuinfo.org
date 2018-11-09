<?php
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
	include './header.inc';	
	include './functions.php';	

	DB::connect();

	$extDiffOnly = false;
	if (isset($_GET['extDiffOnly'])) {
		$extDiffOnly = true;
	}
	
	// Use url parameter to enable diff only display
	$diff = false;
	if (isset($_GET['diff'])) {
		$diff = (int)($_GET['diff']) == 1;
	}
	
	$headerFields = array("device", "driverversion", "apiversion", "os");		
	
	$reportids = array();
	$reportlimit = false;
	
	if ($_REQUEST['id']  == '') {
		echo "<center>";
		?>
		<div class="alert alert-warning">
			<strong>Warning!</strong><br> No report IDs set!
		</div>				
		<?php
		include './footer.inc';
		echo "</center>";
		die();
	}						
			
	foreach ($_REQUEST['id'] as $k => $v) {
		$reportids[] = $k;	
		// Limit to 8 reports
		if (count($reportids) > 7) 
		{
			$reportlimit = true;	 
			break; 
		}
	}   

?>
	<div class='header'>
		<h4 style='margin-left:10px;'>Comparing <?php count($reportids) ?> reports</h4>
		<label id="toggle-label" class="checkbox-inline" style="display:none;">
			<input id="toggle-event" type="checkbox" data-toggle="toggle" data-size="small" data-onstyle="success"> Display only different values
		</label>
	</div>

<?php						
	if ($reportlimit) {echo "<b>Note : </b>You selected more than 8 reports to compare, only displaying the first 8 selected reports.\n"; }	
	
	echo "<center><div id='reportdiv'>";			
	
	sort($reportids, SORT_NUMERIC);
		
	// Gather device information (used in each compare table)
	try {
		$stmnt = DB::$connection->prepare(
		"SELECT 
			concat(VendorId(p.vendorid), ' ', p.devicename) as device,
			concat(p.driverversion, ' (', p.apiversion, ')') as version,
			concat(r.osname, ' ', r.osversion, ' (',  r.osarchitecture, ')') as os
		from reports r
		left join
			deviceproperties p on (p.reportid = r.id)
		where r.id in (" . implode(",", $reportids) . ")");
		$stmnt->execute();
	} catch (PDOException $e) {
		die("Could not fetch report data!");
	}
	
	$deviceinfo_captions = array();
	$deviceinfo_data = array();
	while($row = $stmnt->fetch(PDO::FETCH_ASSOC)) {
		$device_data = array();						
		$colindex = 0;
		foreach ($row as $data) {
			$meta = $stmnt->getColumnMeta($colindex);
			$caption = $meta["name"];
			$device_data[] = $data;	  
			$deviceinfo_captions[] = $caption;						
			$colindex++;
		} 					
		$deviceinfo_data[] = $device_data; 					
	}
											
	// Header
	$colspan = count($reportids) + 1;	
?>			
							
		<div id='tabs'>
		<ul class='nav nav-tabs'>
			<li class='active'><a data-toggle='tab' href='#tab-devices'>Devices</a></li>
			<li><a data-toggle='tab' href='#tab-features'>Features</a></li>
			<li><a data-toggle='tab' href='#tab-limits'>Limits</a></li>
			<li><a data-toggle='tab' href='#tab-extensions'>Extensions</a></li>
			<li><a data-toggle='tab' href='#tab-formats'>Formats</a></li>
			<li><a data-toggle='tab' href='#tab-queues'>Queue families</a></li>
			<li><a data-toggle='tab' href='#tab-memory'>Memory</a></li>
			<li><a data-toggle='tab' href='#tab-surface'>Surface</a></li>
		</ul>		
		
		<div class='tablediv tab-content' style='width:75%;'>		

		<div id='tab-devices' class='tab-pane fade in active reportdiv'>

		<!-- Devices -->		
		<div id="overlay_devices"><center><h4>Fetching data...</h4><img src="./images/loading.gif"></center></div>
			<table id='devices' width='100%' class='table table-striped table-bordered table-hover' >
			<!-- style='display:none' -->
				<?php include 'compare_devices.php'; ?>
			</tbody></table>
		</div>

		
		<!-- Features -->
		<div id='tab-features' class='tab-pane fade reportdiv'>
			<table id='features' width='100%' class='table table-striped table-bordered table-hover'>			
				<?php include 'compare_features.php'; ?>
			</tbody></table>
		</div>
		
		<!-- Limits  -->
		<div id='tab-limits' class='tab-pane fade reportdiv'>
			<table id='limits' width='100%' class='table table-striped table-bordered table-hover'>			
				<?php include 'compare_limits.php'; ?>
			</tbody></table>
		</div>
		
		<!-- Extensions -->
		<div id='tab-extensions' class='tab-pane fade reportdiv'>
			<table id='extensions' width='100%' class='table table-striped table-bordered table-hover'>
				<?php include 'compare_extensions.php'; ?>
			</tbody></table>
		</div>
		
		<!-- Formats -->
		<div id='tab-formats' class='tab-pane fade reportdiv'>
			<?php include 'compare_formats.php'; ?>
		</div>

		<!-- Queues -->
		<div id='tab-queues' class='tab-pane fade reportdiv'>
			<?php include 'compare_queues.php'; ?>
		</div>
		
		<!-- Memory -->
		<div id='tab-memory' class='tab-pane fade reportdiv'>
			<?php include 'compare_memory.php'; ?>
		</div>

		<!-- Surface -->
		<div id='tab-surface' class='tab-pane fade reportdiv'>
			<?php include 'compare_surface.php'; ?>
		</div>

<?php 			
	if ($extDiffOnly) {
		?>
		<script>
			$('.same').hide();
		</script>
		<?php
	}
			
	if ($diff) {
		?>
		<script>
			$('.same').hide();	
		</script>
		<?php
	}

	DB::disconnect();
?>
		
	<script>
		$(document).ready(function() {
		
			var tableNames = ['features', 'limits', 'extensions', 'formats-0', 'formats-1', 'formats-2', 'surface-1', 'surface-2', 'surface-3'];
			for (var i = 0, arrlen = tableNames.length; i < arrlen; i++)
			{
					$('#'+tableNames[i]).dataTable(
						{
							"pageLength" : -1,
							"paging" : false,
							"order": [], 
							"searchHighlight": true,
							"sDom": 'flpt',
							"deferRender": true						
						}
					);
			}		

			// Device properties table with grouping
			$('#devices').dataTable(
				{
					"pageLength" : -1,
					"paging" : false,
					"order": [], 
					"columnDefs": [
						{ "visible": false, "targets": 1 }
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
						api.column(1, {page:'current'} ).data().each( function ( group, i ) {
							if ( last !== group ) {
								$(rows).eq( i ).before(
									'<tr><td colspan="'+api.columns().header().length+'" class="group">'+group+'</td></tr>'
								);
								last = group;
							}
						});
					}
				}
			);	

			$('#devices').show();
			$("#overlay_devices").hide();
			$("#toggle-label").show();
		} );	

		$('#toggle-event').change(function() {
			if ($(this).prop('checked')) {
				$('.same').hide();
				$('.sameCaps').hide();
			} else {
				$('.same').show();				
				$('.sameCaps').show();
			}
		} );
	</script>
		
	</div>

	<?php include './footer.inc'; ?>

</body>
</html>