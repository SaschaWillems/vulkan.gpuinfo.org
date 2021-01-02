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
	include './dbconfig.php';
	include './functions.php';

	$platform = "windows";
	if (isset($_GET['platform'])) {
		$platform = $_GET['platform'];
	}	

	PageGenerator::header("Surface formats");
?>

<div class='header'>
	<?php echo "<h4>Surface format support on <img src='images/".$platform."logo.png' height='14px' style='padding-right:5px'/>".ucfirst($platform); ?>
</div>		

<center>	
	<div>
		<ul class='nav nav-tabs'>
			<li <?php if ($platform == "windows") { echo "class='active'"; } ?>> <a href='listsurfaceformats.php?platform=windows'><img src="images/windowslogo.png" height="14px" style="padding-right:5px">Windows</a> </li>
			<li <?php if ($platform == "linux")   { echo "class='active'"; } ?>> <a href='listsurfaceformats.php?platform=linux'><img src="images/linuxlogo.png" height="16px" style="padding-right:4px">Linux</a> </li>
			<li <?php if ($platform == "android") { echo "class='active'"; } ?>> <a href='listsurfaceformats.php?platform=android'><img src="images/androidlogo.png" height="16px" style="padding-right:4px">Android</a> </li>
		</ul>
	</div>

	<div class='tablediv' style='width:auto; display: inline-block;'>

	<table id="surfaceformats" class="table table-striped table-bordered table-hover reporttable responsive" style='width:auto;'>
		<thead>
			<tr>			
				<th></th>
				<th colspan=2 style="text-align: center;">Device coverage</th>
			</tr>
				<th>Format</th>
				<th style="text-align: center;"><img src='images/icons/check.png' width=16px></th>
				<th style="text-align: center;"><img src='images/icons/missing.png' width=16px></th>
			</th>
		</thead>
		<tbody>
			<?php
				try {
					DB::connect();				
					$deviceCount = getDeviceCount($platform, 'and r.version >= \'1.2\'');
					$sql = "SELECT
						VkFormat(dsf.format) as format,
						count(distinct(ifnull(r.displayname, dp.devicename))) as coverage
						from reports r
						join devicesurfaceformats dsf on dsf.reportid = r.id
						join deviceproperties dp on dp.reportid = r.id
						where ostype = :ostype
						group by format";
					$result = DB::$connection->prepare($sql);
					$result->execute(['ostype' => ostype($platform)]);
					foreach ($result as $row) {
						$coverageLink = "listdevicescoverage.php?".$type."surfaceformat=".$row['format']."&platform=$platform";
						$coverage = $row['coverage'] / $deviceCount * 100.0;
						echo "<tr>";						
						echo "<td class='value'>".$row['format']."</td>";
						echo "<td class='value'><a class='supported' href='$coverageLink'>".round($coverage, 1)."<span style='font-size:10px;'>%</span></a></td>";
						echo "<td class='value'><a class='na' href='$coverageLink&option=not'>".round(100 - $coverage, 1)."<span style='font-size:10px;'>%</span></a></td>";
						echo "</tr>";	    
					}
				} catch (PDOException $e) {
					echo "<b>Error while fetcthing data!</b><br>";
				}
				DB::disconnect();				
			?>  
	</tbody>
</table>  


</div>

<script>
	$(document).ready(function() {
		var table = $('#surfaceformats').DataTable({
			"pageLength" : -1,
			"paging" : false,
			"stateSave": false, 
			"searchHighlight" : true,	
			"dom": 'f',
			"bInfo": false,	
			"order": [[ 1, "desc" ]]	
		});

		$("#searchbox").on("keyup search input paste cut", function() {
			table.search(this.value).draw();
		});  		

	} );	
</script>

<?php PageGenerator::footer(); ?>

</center>
</body>
</html>