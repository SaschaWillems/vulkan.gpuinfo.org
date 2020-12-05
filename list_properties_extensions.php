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

	PageGenerator::header("Features (properties)");
?>
	
<div class='header'>
	<?php echo "<h4>Extension device properties for <img src='images/".$platform."logo.png' height='14px' style='padding-right:5px'/>".ucfirst($platform); ?>	
</div>			

<center>
	<div>
		<ul class='nav nav-tabs'>
			<li <?php if ($platform == "windows") { echo "class='active'"; } ?>> <a href='listpropertiesextensions.php?platform=windows'><img src="images/windowslogo.png" height="14px" style="padding-right:5px">Windows</a> </li>
			<li <?php if ($platform == "linux")   { echo "class='active'"; } ?>> <a href='listpropertiesextensions.php?platform=linux'><img src="images/linuxlogo.png" height="16px" style="padding-right:4px">Linux</a> </li>
			<li <?php if ($platform == "android") { echo "class='active'"; } ?>> <a href='listpropertiesextensions.php?platform=android'><img src="images/androidlogo.png" height="16px" style="padding-right:4px">Android</a> </li>
		</ul>
	</div>


	<div class='tablediv' style='width:auto; display: inline-block;'>

		<table id="features" class="table table-striped table-bordered table-hover responsive" style='width:auto;'>
			<thead>
				<tr>			
					<th></th>
					<th>Property</th>
					<th style="text-align: center;">Type</th>
					<th style="text-align: center;"></th>
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
	
						$stmnt = DB::$connection->prepare("SELECT 
								extension,
								name,
								'coverage' as type,
								COUNT(DISTINCT IFNULL(r.displayname, dp.devicename)) AS supporteddevices
							FROM
								deviceproperties2 d2
									JOIN
								reports r ON d2.reportid = r.id
									JOIN
								deviceproperties dp ON dp.reportid = r.id
							WHERE
								r.ostype = :ostype  and value in ('true', 'false')
							GROUP BY extension , name
							
							UNION
							
							SELECT 
								extension,
								name,
								'values' as type,
								0 as supporteddevices
							FROM
								deviceproperties2 d2
									JOIN
								reports r ON d2.reportid = r.id
									JOIN
								deviceproperties dp ON dp.reportid = r.id
							WHERE
								r.ostype = :ostype and value not in ('true', 'false')
							GROUP BY extension , name
							
							ORDER BY extension ASC , name ASC");
						$stmnt->execute(['ostype' => $os_type]);
	
						if ($stmnt->rowCount() > 0) { 
							while ($property = $stmnt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
								echo "<tr>";
								echo "<td>".$property['extension']."</td>";
								echo "<td class='subkey'>".$property['name']."</td>";
								echo "<td class='text-center'>".ucfirst($property['type'])."</td>";
								if ($property['type'] == 'coverage') {
									$coverageLink = "listdevicescoverage.php?extensionname=".$property['extension']."&extensionproperty=".$property['name']."&platform=$platform";
									$coverage = round($property['supporteddevices'] / $device_count * 100, 1);
									echo "<td class='text-center'><a class='supported' href=\"$coverageLink\">$coverage<span style='font-size:10px;'>%</span></a></td>";
								} else {
									$link = "<a href='displayextensionproperty.php?name=".$property['name']."&platform=$platform'>";
									echo "<td class='text-center'>".$link."Listing</a></td>";
								}
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