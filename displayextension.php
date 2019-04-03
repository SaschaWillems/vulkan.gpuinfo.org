<?php
	/* 		
		*
		* Vulkan hardware capability database server implementation
		*	
		* Copyright (C) Sascha Willems (www.saschawillems.de)
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

	include 'header.inc';	
	include 'dbconfig.php';	
	
	$name = null;
	if (isset($_GET['name'])) {
		$name = $_GET['name'];
	}					
	$os = null;
	$filter = null;
	if (isset($_GET['os'])) {
		$os = $_GET['os'];
		if (!in_array($os, ['windows', 'android', 'linux', 'ios', 'osx'])) {
			$os = null;
		}
		if ($os) {
			if (in_array($os, ['windows', 'android', 'ios', 'osx'])) {
				$filter = "where reportid in (select id from reports where osname = '$os')";
			}
			if (in_array($os, ['linux'])) {
				$filter = "where reportid in (select id from reports where osname not in ('windows', 'android', 'ios', 'osx'))";
			}
		}
	}					

	$totalCountWindows = 0;
	$supportedCountWindows = 0;
	$totalCountLinux = 0;
	$supportedCountLinux = 0;
	$totalCountAndroid = 0;
	$supportedCountAndroid = 0;

	// Check if extension is available
	DB::connect();
	$result = DB::$connection->prepare("SELECT * from extensions where name = :name");
	$result->execute([":name" => $name]);
	DB::disconnect();
	if ($result->rowCount() == 0) {
		echo "<center>";
		?>
			<div class="alert alert-danger error">
			<strong>This is not the <strike>droid</strike> extension you are looking for!</strong><br><br>
			You may have passed a wrong extension name, or the extension is not yet present in the database.
			</div>				
		<?php
		include "footer.html";
		echo "</center>";
		die();		
	}

	// Fetch devices
	$devicesWindows = [];
	$devicesAndroid = [];
	$devicesLinux = [];

	DB::connect();			
	$result = DB::$connection->prepare("SELECT 
		r.id,
		ifnull(r.displayname, dp.devicename) as device, 
		r.osname
		from deviceproperties dp
		join reports r on r.id = dp.reportid
	where
		r.id in (select reportid from deviceextensions de join extensions ext on de.extensionid = ext.id where ext.name = :name)
		group by device");
	$result->execute([":name" => $name]);
	$rows = $result->fetchAll(PDO::FETCH_ASSOC);
	foreach ($rows as $row) {
		switch($row["osname"]) {
			case "windows":
				$devicesWindows[] = $row["device"];
				break;
			case "android":
				$devicesAndroid[] = $row["device"];
				break;
			default:
				$devicesLinux[] = $row["device"];
				break;
		}   
	}     
	DB::disconnect();       			
?>

	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>	
	<script>
		$(document).ready(function() {
			var tableNames = [ 
				'devices_windows', 
				'devices_linux', 
				'devices_android'
			];
			for (var i = 0, arrlen = tableNames.length; i < arrlen; i++) {
				$('#'+tableNames[i]).DataTable({
					"pageLength" : 25,
					"lengthChange": false,
					"paging" : false,
					"stateSave": false, 
					"searchHighlight" : true,	
					"dom": 'flrtip',			
					"bInfo": false,	
					"order": [[ 0, "asc" ]]	
				});
			}
		} );	
	</script>

	<div class='header'>
		<h4 class='headercaption'>Device support for <?php echo $name ?></h4>
	</div>

	<center>	
		<div class='parentdiv'>
			<div id="chart"></div>
			<div>
				<ul class='nav nav-tabs'>
					<li class="active"> <a data-toggle='tab' href='#windows'><img src="images/windowslogo.png" height="14px" style="padding-right:5px">Windows</a> </li>
					<li> <a data-toggle='tab' href='#linux'><img src="images/linuxlogo.png" height="16px" style="padding-right:4px">Linux</a> </li>
					<li> <a data-toggle='tab' href='#android'><img src="images/androidlogo.png" height="16px" style="padding-right:4px">Android</a> </li>
				</ul>
			</div>

			<div class='tab-content'>
				<!-- Windows -->
				<div id='windows' class='tab-pane fade in active'>
					<div class='tablediv' style='width:auto; display: inline-block;'>	
						<table id="devices_windows" class="table table-striped table-bordered table-hover" >
							<thead>
								<tr>				
									<th>Device</th>
								</tr>
							</thead>
							<tbody>				
								<?php		
									foreach ($devicesWindows as $device) {
										echo "<tr><td><a href='listreports.php?devicename=".$device."'>".$device."</a></td></tr>";
									}     
								?>   					
							</tbody>
						</table>
					</div>
				</div>
				<!-- Linux -->
				<div id='linux' class='tab-pane fade in'>
					<div class='tablediv' style='width:auto; display: inline-block;'>	
						<table id="devices_linux" class="table table-striped table-bordered table-hover" >
							<thead>
								<tr>				
									<th>Device</th>
								</tr>
							</thead>
							<tbody>				
								<?php		
									foreach ($devicesLinux as $device) {
										echo "<tr><td><a href='listreports.php?devicename=".$device."'>".$device."</a></td></tr>";
									}     
								?>   					
							</tbody>
						</table>
					</div>
				</div>
				<!-- Android -->
				<div id='android' class='tab-pane fade in'>
					<div class='tablediv' style='width:auto; display: inline-block;'>	
						<table id="devices_android" class="table table-striped table-bordered table-hover" >
							<thead>
								<tr>				
									<th>Device</th>
								</tr>
							</thead>
							<tbody>				
								<?php		
									foreach ($devicesAndroid as $device) {
										echo "<tr><td><a href='listreports.php?devicename=".$device."'>".$device."</a></td></tr>";
									}     
								?>   					
							</tbody>
						</table>
					</div>
				</div>
			</div>

	</center>

<?php
	DB::connect();			
	// @todo: if OS specified, only for that os!
	// @todo: indices!
	// Count devices supporting this extension
	// Windows
	$totalCountWindows = DB::getCount("SELECT count(distinct(ifnull(r.displayname, dp.devicename))) from reports r join deviceproperties dp on r.id = dp.reportid where osname = 'windows'", []);
	$supportedCountWindows = DB::getCount("SELECT count(distinct(ifnull(r.displayname, dp.devicename))) from deviceproperties dp join reports r on r.id = dp.reportid where
		r.id in (select reportid from deviceextensions de join extensions ext on de.extensionid = ext.id where ext.name = :name)
		and osname = 'windows'", [":name" => $name]);
	// Linux
	$totalCountLinux = DB::getCount("SELECT count(distinct(ifnull(r.displayname, dp.devicename))) from reports r join deviceproperties dp on r.id = dp.reportid where osname not in ('windows', 'android', 'ios', 'osx')", []);
	$supportedCountLinux = DB::getCount("SELECT count(distinct(ifnull(r.displayname, dp.devicename))) from deviceproperties dp join reports r on r.id = dp.reportid where
		r.id in (select reportid from deviceextensions de join extensions ext on de.extensionid = ext.id where ext.name = :name)
		and osname not in ('windows', 'android', 'ios', 'osx')", [":name" => $name]);
	// Android	
	$totalCountAndroid = DB::getCount("SELECT count(distinct(ifnull(r.displayname, dp.devicename))) from reports r join deviceproperties dp on r.id = dp.reportid where osname = 'android'", []);
	$supportedCountAndroid = DB::getCount("SELECT count(distinct(ifnull(r.displayname, dp.devicename))) from deviceproperties dp join reports r on r.id = dp.reportid where
		r.id in (select reportid from deviceextensions de join extensions ext on de.extensionid = ext.id where ext.name = :name)
		and osname = 'android'", [":name" => $name]);
	DB::disconnect();			
?>
	
	<script type="text/javascript">
		google.charts.load('current', {'packages':['corechart']});
		google.charts.setOnLoadCallback(drawChart);
		
		function drawChart() {

      var data = google.visualization.arrayToDataTable([
        ['Value', 'Supported', 'Not suppored', { role: 'annotation' } ],
        ['Windows', <?php echo $supportedCountWindows ?>, <?php echo $totalCountWindows - $supportedCountWindows ?>, ''],
        ['Linux', <?php echo $supportedCountLinux ?>, <?php echo $totalCountLinux - $supportedCountLinux ?>, ''],
        ['Android', <?php echo $supportedCountAndroid ?>, <?php echo $totalCountAndroid - $supportedCountAndroid ?>, '']
      ]);

      var options = {
        width: 600,
        height: 400,
        legend: { position: 'bottom' },
        bar: { groupWidth: '75%' },
        isStacked: 'percent',
				colors: ['#14963c', '#dc3c14'],
				hAxis: {
          minValue: 0,
          ticks: [0, .25, .5, .75, 1]
        }				
      };			
			
			// Bar Chart
			var chart = new google.visualization.BarChart(document.getElementById('chart'));
			chart.draw(data, options);
		}
	</script>

	<?php 
		include "footer.inc";
	?>

</body>
</html>