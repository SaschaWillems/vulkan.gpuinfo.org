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
		join reports r on r.id = dp.reportid");
	$result->execute();
	$rows = $result->fetchAll(PDO::FETCH_ASSOC);
	foreach ($rows as $row) {
		if (in_array(strtolower($row["osname"]), ['osx', 'macos', 'unknown'])) {
			continue;
		}
		switch(strtolower($row["osname"])) {
			case "windows":
				if (!in_array($row["device"], $devicesWindows)) {
					$devicesWindows[] = $row["device"];
				}
				break;
			case "android":
				if (!in_array($row["device"], $devicesAndroid)) {
					$devicesAndroid[] = $row["device"];
				}
				break;
			default:
				if (!in_array($row["device"], $devicesLinux)) {
					$devicesLinux[] = $row["device"];
				}
				break;
		}   
	}     
	DB::disconnect();      			
?>

	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>	

	<div class='header'>
				<h4>Details for <?php echo $name ?></h4>
	</div>			

	<center>	
		<div class='tablediv' style='width:auto; display: inline-block;'>

			<div id="chart" style="padding-bottom:10px; display: hidden;"></div>

			<!-- Related features -->

			<?php
				DB::connect();			
				$result = DB::$connection->prepare("SELECT distinct(name) from devicefeatures2 where extension = :name");
				$result->execute([":name" => $name]);
				$rows = $result->fetchAll(PDO::FETCH_ASSOC);
			?>
					<div style="text-align:left;">
						<ul class="list-group">
							<li class="list-group-item disabled">Related features</li>
							<?php		
								if (count($rows) > 0) {
									foreach ($rows as $row) {
										echo "<li class=\"list-group-item\"><a href='listreports.php?extensionfeature=".$row["name"]."'>".$row["name"]."</a></li>";
									}     
								} else {
									echo "<li class=\"list-group-item\">none</li>";
								}
							?>   					
						</ul>
					</div>

			<!-- Related properties -->

			<?php
				DB::connect();			
				$result = DB::$connection->prepare("SELECT distinct(name) from deviceproperties2 where extension = :name");
				$result->execute([":name" => $name]);
				$rows = $result->fetchAll(PDO::FETCH_ASSOC);
			?>
					<div style="text-align:left;">	
						<ul class="list-group">
							<li class="list-group-item disabled">Related properties</li>
							<?php		
								if (count($rows) > 0) {
										foreach ($rows as $row) {
										echo "<li class=\"list-group-item\"><a href='displayextensionproperty.php?name=".$row["name"]."'>".$row["name"]."</a></li>";
									}								
								} else {
									echo "<li class=\"list-group-item\">none</li>";
								}
							?>   					
						</ul>
					</div>

	</center>

<?php
	DB::connect();			
	$viewDeviceCount =DB::$connection->prepare("SELECT * from viewDeviceCount");
	$viewDeviceCount->execute(); 
	$deviceCounts = $viewDeviceCount->fetch(PDO::FETCH_ASSOC);					
	$totalCountWindows = $deviceCounts["windows"];
	$totalCountLinux = $deviceCounts["linux"];
	$totalCountAndroid = $deviceCounts["android"];
	$extension = DB::$connection->prepare("SELECT windows, linux, android, features2, properties2 from viewExtensionsPlatforms where name = :name");
	$extension->execute(["name" => $name]);
	$extension = $extension->fetch(PDO::FETCH_ASSOC);
	$supportedCountWindows = $extension["windows"];
	$supportedCountLinux = $extension["linux"];
	$supportedCountAndroid = $extension["android"];
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
        width: 540,
        height: 300,
        legend: { position: 'bottom' },
        bar: { groupWidth: '75%' },
        isStacked: 'percent',
				colors: ['#14963c', '#dc3c14'],
				hAxis: {
          minValue: 0,
          ticks: [0, .25, .5, .75, 1]
        },
				chartArea:{
						width: '80%',
						height: '220',
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