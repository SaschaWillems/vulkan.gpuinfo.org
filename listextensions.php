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
	
	include './dbconfig.php';
	include './header.inc';	
	include './functions.php';

	$platform = "windows";
	if (isset($_GET['platform'])) {
		$platform = $_GET['platform'];
	}

?>

<script>
	$(document).ready(function() {
		var table = $('#extensions').DataTable({
			"pageLength" : -1,
			"paging" : false,
			"stateSave": false, 
			"searchHighlight" : true,	
			"dom": 'f',			
			"bInfo": false,	
			"order": [[ 0, "asc" ]],
			"columnDefs": [{
      			"targets": [ 1, 2 ],
			}]
		});

		$("#searchbox").on("keyup search input paste cut", function() {
			table.search(this.value).draw();
		});

	} );	
</script>

<div class='header'>
	<?php echo "<h4>Extension coverage for <img src='images/".$platform."logo.png' height='14px' style='padding-right:5px'/>".ucfirst($platform); ?>	
</div>			

<center>
	<div>
		<ul class='nav nav-tabs'>
			<li <?php if ($platform == "windows") { echo "class='active'"; } ?>> <a href='listextensions.php?platform=windows'><img src="images/windowslogo.png" height="14px" style="padding-right:5px">Windows</a> </li>
			<li <?php if ($platform == "linux")   { echo "class='active'"; } ?>> <a href='listextensions.php?platform=linux'><img src="images/linuxlogo.png" height="16px" style="padding-right:4px">Linux</a> </li>
			<li <?php if ($platform == "android") { echo "class='active'"; } ?>> <a href='listextensions.php?platform=android'><img src="images/androidlogo.png" height="16px" style="padding-right:4px">Android</a> </li>
		</ul>
	</div>

	<div class='tablediv' style='width:auto; display: inline-block;'>

	<table id="extensions" class="table table-striped table-bordered table-hover responsive" style='width:auto;'>
		<thead>
			<tr>			
				<th></th>
				<th colspan=2 style="text-align: center;">Device coverage</th>
			</tr>
			<tr>
				<th>Extension</th>
				<th style="text-align: center;"><img src='icon_check.png' width=16px></th>
				<th style="text-align: center;"><img src='icon_missing.png' width=16px></th>
			</tr>
		</thead>
		<tbody>		
			<?php		
				DB::connect();
				try {
					$viewDeviceCount =DB::$connection->prepare("SELECT * from viewDeviceCount");
					$viewDeviceCount->execute(); 
					$deviceCounts = $viewDeviceCount->fetch(PDO::FETCH_ASSOC);					

					$extensions = DB::$connection->prepare("SELECT 
						ext.name AS name,
						(SELECT COUNT(DISTINCT IFNULL(r.displayname, dp.devicename)) FROM deviceproperties dp JOIN reports r ON (r.id = dp.reportid) WHERE r.ostype = :ostype AND r.id IN (SELECT de.reportid FROM deviceextensions de WHERE (de.extensionid = ext.id))) AS extcount,
						(SELECT COUNT(DISTINCT df2.name) FROM devicefeatures2 df2 WHERE (df2.extension = ext.name)) AS features2,
						(SELECT COUNT(DISTINCT dp2.name) FROM deviceproperties2 dp2 WHERE (dp2.extension = ext.name)) AS properties2
						FROM extensions ext");
					$extensions->execute(['ostype' => ostype($platform)]);

					if ($extensions->rowCount() > 0) { 
						while ($extension = $extensions->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {								
							$link = ($extension['features2'] > 0 || $extension['properties2'] > 0) ? " <a href=\"displayextension.php?name=".$extension['name']."\" title=\"Show additional features and properties for this extensions\">[?]</a>" : "";
							$coverageLink = "listdevicescoverage.php?extension=".$extension['name']."&platform=$platform";
							$coverage = round($extension['extcount']/$deviceCounts[$platform]*100, 1);
							echo "<tr>";
							echo "<td>".$extension['name'].$link."</td>";
							echo "<td class='text-center'><a class='supported' href=\"$coverageLink\">$coverage<span style='font-size:10px;'>%</span></a></td>";
							echo "<td class='text-center'><a class='na' href=\"$coverageLink&option=not\">".round(100 - $coverage, 1)."<span style='font-size:10px;'>%</span></a></td>";
							echo "</tr>";
						}
					}

				} catch (PDOException $e) {
					echo "<b>Error while fetcthing data!</b><br>";
				}
				DB::disconnect();
			?>   
		</tbody>
	</table>  
</div>

<?php include './footer.inc'; ?>

</center>
</body>
</html>