	<script>
		function showDiffOnly() {
			$('.same').toggle()
		}
		function toggleDiffCaps() {
			$('.sameCaps').toggle()
		}	
	</script>
	<div>
		<?php
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
	include './header.inc';	
	include './functions.php';	

	dbConnect();
				
			$extDiffOnly = false;
			if (isset($_GET['extDiffOnly'])) 
			{
				$extDiffOnly = true;
			}
			
			// Use url parameter to enable diff only display
			$diff = false;
			if (isset($_GET['diff'])) 
			{
				$diff = (mysql_real_escape_string($_GET['diff']) == 1);
			}
			
			$headerFields = array("device", "driverversion", "apiversion", "os");		
			
			$reportids = array();
			$reportlimit = false;
			
			if ($_REQUEST['id']  == '')
			{
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
					
			foreach ($_REQUEST['id'] as $k => $v) 
			{
				$reportids[] = $k;	
				// Limit to 8 reports
				if (count($reportids) > 7) 
				{
					$reportlimit = true;	 
					break; 
				}
			}   
							
			echo "<div class='header'>";
				echo "<h4 style='margin-left:10px;'>Comparing ".count($reportids)." reports</h4>";
			echo "</div>";					
			
			if ($reportlimit) {echo "<b>Note : </b>You selected more than 8 reports to compare, only displaying the first 8 selected reports.\n"; }	
			
			echo "<center><div id='reportdiv'>";			
			
			sort($reportids, SORT_NUMERIC);
				
			// Gather device information (used in each compare table)
			$sqlresult = mysql_query("
			select 
				concat(VendorId(p.vendorid), ' ', p.devicename) as device,
				concat(p.driverversion, ' (', p.apiversion, ')') as version,
				concat(r.osname, ' ', r.osversion, ' (',  r.osarchitecture, ')') as os
			from reports r
			left join
				deviceproperties p on (p.reportid = r.id)
			where r.id in (" . implode(",", $reportids) . ")") or die(mysql_error());					
			
			$deviceinfo_captions = array();
			$deviceinfo_data = array();
			while($row = mysql_fetch_row($sqlresult)) 
			{
				$device_data = array();		
				
				$colindex = 0;
				foreach ($row as $data) 
				{
					$caption = mysql_field_name($sqlresult, $colindex);		  						
					$device_data[] = $data;	  
					$deviceinfo_captions[] = $caption;						
					$colindex++;
				} 					
				$deviceinfo_data[] = $device_data; 					
			}		
													
			// Header
			$colspan = count($reportids) + 1;	
							
			echo "<div id='tabs'>";
			echo "<ul class='nav nav-tabs'>";
			echo "	<li class='active'><a data-toggle='tab' href='#tab-devices'>Devices</a></li>";
			echo "	<li><a data-toggle='tab' href='#tab-features'>Features</a></li>";
			echo "	<li><a data-toggle='tab' href='#tab-limits'>Limits</a></li>";
			echo "	<li><a data-toggle='tab' href='#tab-extensions'>Extensions</a></li>";
			echo "	<li><a data-toggle='tab' href='#tab-formats'>Formats</a></li>";
			echo "	<li><a data-toggle='tab' href='#tab-queues'>Queue families</a></li>";
			echo "	<li><a data-toggle='tab' href='#tab-memory'>Memory</a></li>";
			echo "	<li><a data-toggle='tab' href='#tab-surface'>Surface</a></li>";
			echo "</ul>";				
			
			echo "<div class='tablediv tab-content' style='width:75%;'>";			

			// Devices
			echo "<div id='tab-devices' class='tab-pane fade in active reportdiv'>";
			echo "<button onclick='toggleDiffCaps();' class='btn btn-default'>Toggle all / diff only</button>";				
			echo "<table id='devices' width='100%' class='table table-striped table-bordered table-hover'>";
			
			include 'compare_devices.php';				
			
			echo "</tbody></table></div>";	
		
			
			// Features
			echo "<div id='tab-features' class='tab-pane fade reportdiv'>";
			echo "<button onclick='toggleDiffCaps();' class='btn btn-default'>Toggle all / diff only</button>";				
			echo "<table id='features' width='100%' class='table table-striped table-bordered table-hover'>";
			
			include 'compare_features.php';				
			
			echo "</tbody></table></div>";	
			
			// Limits 
			echo "<div id='tab-limits' class='tab-pane fade reportdiv'>";
			echo "<button onclick='toggleDiffCaps();' class='btn btn-default'>Toggle all / diff only</button>";				
			echo "<table id='limits' width='100%' class='table table-striped table-bordered table-hover'>";
			
			include 'compare_limits.php';				
			
			echo "</tbody></table></div>";			
			
			// Extensions
			echo "<div id='tab-extensions' class='tab-pane fade reportdiv'>";
			echo "<button onclick='showDiffOnly();' class='btn btn-default'>Toggle all / diff only</button>";			
			echo "<table id='extensions' width='100%' class='table table-striped table-bordered table-hover'>";
			
			include 'compare_extensions.php';					
			
			echo "</tbody></table></div>";			
			
			// Formats
			echo "<div id='tab-formats' class='tab-pane fade reportdiv'>";
			echo "<button onclick='showDiffOnly();' class='btn btn-default'>Toggle all / diff only</button>";						
			include 'compare_formats.php';		
			echo "</div>";

			// Queues
			echo "<div id='tab-queues' class='tab-pane fade reportdiv'>";
			include 'compare_queues.php';					
			echo "</div>";
			
			// Memory
			echo "<div id='tab-memory' class='tab-pane fade reportdiv'>";
			include 'compare_memory.php';					
			echo "</div>";

			// Surface
			echo "<div id='tab-surface' class='tab-pane fade reportdiv'>";
			include 'compare_surface.php';					
			echo "</div>";

			if ($extDiffOnly) 
			{
				?>
				<script>
					$('.same').hide();
				</script>
				<?php
			}
					
			if ($diff) 
			{
				?>
				<script>
					$('.same').hide();	
				</script>
				<?php
			}

	
			dbDisconnect();
		?>
		
	<script>
		$(document).ready(function() {
		
			var tableNames = ['devices', 'features', 'limits', 'extensions', 'formats-0', 'formats-1', 'formats-2', 'surface-1', 'surface-2', 'surface-3'];
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
		} );	
	</script>
		
	</div>

	<?php include './footer.inc'; ?>

</body>
</html>