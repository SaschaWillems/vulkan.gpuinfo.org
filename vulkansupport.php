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
	
	echo "<div class='header'>";
		echo "<h4>Vulkan device support</h4>";
	echo "</div>";				
?>

<style>
	.dataTables_filter {
		display: none;
	}
</style>

<script>
	$(document).ready(function() {
		var table = $('#devices').DataTable({
			"pageLength" : -1,
			"paging" : false,
			"stateSave": false, 
			"searchHighlight" : true,	
			"bInfo": false,		
		});

		$("#searchbox").on("keyup search input paste cut", function() {
			table.search(this.value).draw();
		});  		

	} );	
</script>

<center>	
	<!--<div class="tablediv">-->

	<div>
		<ul class='nav nav-tabs'>
			<li class='active'><a data-toggle='tab' href='#windows'>Windows</a></li>
			<li><a data-toggle='tab' href='#linux'>Linux</a></li>
			<li><a data-toggle='tab' href='#android'>Android</a></li>
		</ul>
	</div>

	<div class='tablediv tab-content' style='width:50%;'>

		<?php		
			DB::connect();

			$targets = ["windows", "linux", "android"];
			$osfilter = ["and osname = 'windows'", "and osname not in ('windows', 'android')", "and osname = 'android'"];
			for ($i = 0; $i < sizeof($targets); $i++) {
				echo "<div id='".$targets[$i]."' class='tab-pane fade ".(($i == 0) ? "in active" : "")." reportdiv'>";
				$sql = "select dp.devicename, max(apiversionraw) as apiversion, vi.name as vendor
					from deviceproperties dp
					join vendorids vi on dp.vendorid = vi.id					
					join reports r on r.id = dp.reportid
					where not exists (select * from blacklist bl where bl.devicename = dp.devicename)
					".$osfilter[$i]."					
					group by devicename
					order by VendorId(vendorid) asc, devicename asc";

				$devices = DB::$connection->prepare($sql);
				$devices->execute();

				$lastVendor = '';

				foreach ($devices as $device) {
					if ($device["vendor"] != $lastVendor) {
						$lastVendor = $device["vendor"];
						echo "<h3>".$device["vendor"]."</h3>";
					}
					echo "<a href='listreports.php?devicename=".urlencode($device["devicename"])."'>".$device["devicename"]."</a> <span style='color:#ABABAB;'>(".versionToString($device["apiversion"]).")</span><br>";
				}					

				echo "</div>";				
			}

			DB::disconnect();			
		?>   
	</div>

	<script>
		$(function() {
			var a = document.location.hash;
			if (a) {
				$('.nav a[href=\\'+a+']').tab('show');
			}
		
			$('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
				window.location.hash = e.target.hash;
			});
		});	
	   </script>	

<?php include './footer.inc'; ?>

</center>
</body>
</html>