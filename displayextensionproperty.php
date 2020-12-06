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

	PageGenerator::header($name);

	DB::connect();	
	$result = DB::$connection->prepare("SELECT * from deviceproperties2 where name = :name");
	$result->execute([":name" => $name]);
	$row = $result->fetch(PDO::FETCH_ASSOC);
	$extname = $row["extension"];
	if ($result->rowCount() == 0) {
		echo "<center>";
		?>
			<div class="alert alert-danger error">
			<strong>This is not the <strike>droid</strike> extension property you are looking for!</strong><br><br>
			You may have passed a wrong extension property name.
			</div>				
		<?php
		include "footer.html";
		echo "</center>";
		die();		
	}
	DB::disconnect();
	
?>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>	
	<script>
		$(document).ready(function() {
			var table = $('#extensions').DataTable({
				"pageLength" : -1,
				"paging" : false,
				"stateSave": false, 
				"searchHighlight" : true,	
				"dom": '',			
				"bInfo": false,	
				"order": [[ 0, "asc" ]]	
			});
		} );	
	</script>

	<div class='header'>
		<h4 class='headercaption'>Value distribution for <code><?=$name?></code> property of <code><?=$extname?></code></h4>
	</div>

	<center>	
		<div class='parentdiv'>
			<div id="chart"></div>
			<div class='tablediv' style='width:auto; display: inline-block;'>	
				<table id="extensions" class="table table-striped table-bordered table-hover reporttable" >
					<thead>
						<tr>				
							<th>Value</th>
							<th>Reports</th>
						</tr>
					</thead>
					<tbody>				
						<?php		
							DB::connect();			
							$result = DB::$connection->prepare("SELECT value, count(*) as reports from deviceproperties2 where name = :name group by 1 order by 1");
							$result->execute([":name" => $name]);
							$rows = $result->fetchAll(PDO::FETCH_ASSOC);
							foreach ($rows as $group) {
								$value = $group['value'];
								// Some values are stored as serialized arrays and need to be unserialized
								if (substr($value, 0, 2) == 'a:') {
									$value = unserialize($value);
									$value = '['.implode(',', $value).']';
								}
								$link ="listreports.php?extensionproperty=$name&value=".$group["value"];
								echo "<tr>";						
								echo "<td>$value</td>";
								echo "<td><a href='$link'>".$group["reports"]."</a></td>";
								echo "</tr>";	    
							}     
							DB::disconnect();       			
						?>   					
					</tbody>
				</table> 

			</div>
		</div>
	</center>
	
    <script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);
      function drawChart() {

		var data = google.visualization.arrayToDataTable([
			['Value', 'Reports'],
			<?php 
				DB::connect();										
				$result = DB::$connection->prepare("SELECT value, count(*) as reports from deviceproperties2 where name = :name group by 1 order by 2 desc");
				$result->execute([":name" => $name]);
				$rows = $result->fetchAll(PDO::FETCH_ASSOC);
				foreach ($rows as $row) {
					echo "['".$row['value']."',".$row['reports']."],";
				}     
				DB::disconnect();
			?>		
		]);

        var options = {
			legend: { position: 'bottom' },
			chartArea: { width:"80%", height:"80%" },
			height: 500,
			width: 500		  
        };

        var chart = new google.visualization.PieChart(document.getElementById('chart'));

        chart.draw(data, options);
	  }
	</script>

	<?php 
		include "footer.inc";
	?>

</body>
</html>