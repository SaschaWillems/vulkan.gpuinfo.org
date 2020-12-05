<?php 
	/* 		
		*
		* Vulkan hardware capability database server implementation
		*	
		* Copyright (C) 2016-2020 Sascha Willems (www.saschawillems.de)
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
	
	$platform = "windows";
	if (isset($_GET['platform'])) {
		$platform = $_GET['platform'];
	}

	PageGenerator::header("Features (extensions)");
?>
	
<div class='header'>
	<?php echo "<h4>Extension device feature coverage for <img src='images/".$platform."logo.png' height='14px' style='padding-right:5px'/>".ucfirst($platform); ?>	
</div>			

<center>
	<div>
		<ul class='nav nav-tabs'>
			<li <?php if ($platform == "windows") { echo "class='active'"; } ?>> <a href='list_features_extensions.php?platform=windows'><img src="images/windowslogo.png" height="14px" style="padding-right:5px">Windows</a> </li>
			<li <?php if ($platform == "linux")   { echo "class='active'"; } ?>> <a href='list_features_extensions.php?platform=linux'><img src="images/linuxlogo.png" height="16px" style="padding-right:4px">Linux</a> </li>
			<li <?php if ($platform == "android") { echo "class='active'"; } ?>> <a href='list_features_extensions.php?platform=android'><img src="images/androidlogo.png" height="16px" style="padding-right:4px">Android</a> </li>
		</ul>
	</div>


	<div class='tablediv' style='width:auto; display: inline-block;'>

		<table id="features" class="table table-striped table-bordered table-hover responsive" style='width:auto;'>
			<thead>
				<tr>			
					<th></th>
					<th>Feature</th>
					<th style="text-align: center;"><img src='icon_check.png' width=16px></th>
					<th style="text-align: center;"><img src='icon_missing.png' width=16px></th>
				</tr>			
			</thead>
			<tbody>		
				<?php	
					DB::connect();
					try {
						$os_type = ostype($platform);
						// Get the total count of devices that have been submitted with a report version that has support for extension features (introduced with 1.4)
						$stmnt =DB::$connection->prepare("SELECT COUNT(DISTINCT IFNULL(r.displayname, dp.devicename)) FROM reports r JOIN deviceproperties dp ON r.id = dp.reportid WHERE r.ostype = :ostype AND r.version >= '1.4'");
						$stmnt->execute(['ostype' => $os_type]);
						$device_count = $stmnt->fetchColumn();
	
						$stmnt = DB::$connection->prepare('SELECT 
								extension,
								name,
								COUNT(DISTINCT IFNULL(r.displayname, dp.devicename)) AS supporteddevices
							FROM
								devicefeatures2 df2
									JOIN
								reports r ON df2.reportid = r.id
									JOIN
								deviceproperties dp ON dp.reportid = r.id
							WHERE
								supported = 1 AND r.ostype = :ostype
							GROUP BY extension , name
							ORDER BY extension ASC , name ASC');
						$stmnt->execute(['ostype' => $os_type]);
	
						if ($stmnt->rowCount() > 0) { 
							while ($feature = $stmnt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
								$coverageLink = "listdevicescoverage.php?extensionname=".$feature['extension']."&extensionfeature=".$feature['name']."&platform=$platform";
								$coverage = round($feature['supporteddevices'] / $device_count * 100, 1);
								echo "<tr>";
								echo "<td>".$feature['extension']."</td>";
								echo "<td class='subkey'>".$feature['name']."</td>";
								echo "<td class='text-center'><a class='supported' href=\"$coverageLink\">$coverage<span style='font-size:10px;'>%</span></a></td>";
								echo "<td class='text-center'><a class='na' href=\"$coverageLink&option=not\">".round(100 - $coverage, 1)."<span style='font-size:10px;'>%</span></a></td>";
								echo "</tr>";
							}
						}
	
					} catch (PDOException $e) {
						echo "<b>Error while fetching data!</b><br>";
					}					
					DB::disconnect();			
				?>   
			</tbody>
		</table>  

	</div>

<script>
	$(document).ready(function() {
		var table = $('#features').DataTable({
			"pageLength" : -1,
			"paging" : false,
			"order": [], 
			"columnDefs": [
				{ "visible": false, "targets": 0 }
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
				api.column(0, {page:'current'} ).data().each( function ( group, i ) {
					if ( last !== group ) {
						$(rows).eq( i ).before(
							'<tr><td colspan="3" class="group">'+group+'</td></tr>'
						);
						last = group;
					}
				});
			}
		});
	});	
</script>

<?php PageGenerator::footer(); ?>
	
</center>
</body>
</html>