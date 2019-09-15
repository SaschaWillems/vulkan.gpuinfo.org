<?php 
	/* 		
		*
		* Vulkan hardware capability database server implementation
		*	
		* Copyright (C) by Sascha Willems (www.saschawillems.de)
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
		var table = $('#formats').DataTable({
			"pageLength" : -1,
			"paging" : false,
			"stateSave": false, 
			"searchHighlight" : true,	
			"dom": 'f',			
			"bInfo": false,	
			"order": [[ 0, "asc" ]]	
		});

		$("#searchbox").on("keyup search input paste cut", function() {
			table.search(this.value).draw();
		});  		

	} );	
</script>

<div class='header'>
	<?php echo "<h4>Image and buffer format support on <img src='images/".$platform."logo.png' height='14px' style='padding-right:5px'/>".ucfirst($platform); ?>
</div>			

<center>	
	<div>
		<ul class='nav nav-tabs'>
			<li <?php if ($platform == "windows") { echo "class='active'"; } ?>> <a href='listformats.php?platform=windows'><img src="images/windowslogo.png" height="14px" style="padding-right:5px">Windows</a> </li>
			<li <?php if ($platform == "linux")   { echo "class='active'"; } ?>> <a href='listformats.php?platform=linux'><img src="images/linuxlogo.png" height="16px" style="padding-right:4px">Linux</a> </li>
			<li <?php if ($platform == "android") { echo "class='active'"; } ?>> <a href='listformats.php?platform=android'><img src="images/androidlogo.png" height="16px" style="padding-right:4px">Android</a> </li>
		</ul>
	</div>

	<div class='tablediv' style='width:auto; display: inline-block;'>

	<table id="formats" class="table table-striped table-bordered table-hover responsive" style='width:auto;'>
		<thead>
			<tr>			
				<th></th>
				<th colspan=7 style="text-align: center;">Device coverage</th>
			</tr>
			<tr>			
				<th></th>
				<th colspan=2 style="text-align: center;">Linear</th>
				<th colspan=2 style="text-align: center;">Optimal</th>
				<th colspan=2 style="text-align: center;">Buffer</th>
			</tr>
			<th>Format</th>
				<th style="text-align: center;"><img src='icon_check.png' width=16px></th>
				<th style="text-align: center;"><img src='icon_missing.png' width=16px></th>
				<th style="text-align: center;"><img src='icon_check.png' width=16px></th>
				<th style="text-align: center;"><img src='icon_missing.png' width=16px></th>
				<th style="text-align: center;"><img src='icon_check.png' width=16px></th>
				<th style="text-align: center;"><img src='icon_missing.png' width=16px></th>
			</tr>			
		</thead>
		<tbody>
			<?php
				$formats = [];
				try {
					DB::connect();
					$deviceCount = getDeviceCount($platform);
					// Fetch formats into array as a base for creating the table
					foreach(['lineartilingfeatures', 'optimaltilingfeatures', 'bufferfeatures'] as $target) {
						$sql = "SELECT vkf.name as name, count(distinct(r.devicename)) as coverage
							from reports r
							join deviceformats df on df.reportid = r.id and df.$target > 0
							join VkFormat vkf on vkf.value = df.formatid
							where r.ostype = :ostype
							group by name";
						$stmnt = DB::$connection->prepare($sql);
						$stmnt->execute(['ostype' => ostype($platform)]);
						$result = $stmnt->fetchAll(PDO::FETCH_NUM);
						foreach($result as $row) {
							$formats[$row[0]][$target] = $row[1];
						}
					}
				} catch (PDOException $e) {
					echo "<b>Error while fetcthing data!</b><br>";
				}
				DB::disconnect();

				// Build table
				foreach($formats as $key => $format) {
					echo "<tr>";			
					echo "<td class='value'>".$key."</td>";
					$names = ['linearformat', 'optimalformat', 'bufferformat'];
					foreach(['lineartilingfeatures', 'optimaltilingfeatures', 'bufferfeatures'] as $index => $target) {
						$coverageLink = "listdevicescoverage.php?$names[$index]=$key&platform=$platform";
						$coverage = $format[$target] / $deviceCount * 100.0;
						echo "<td class='value' align=center><a class='supported' href='$coverageLink'>".round($coverage, 2)."<span style='font-size:10px;'>%</span></a></td>";
						echo "<td class='value' align=center><a class='na' href='$coverageLink&option=not'>".round(100 - $coverage, 2)."<span style='font-size:10px;'>%</span></a></td>";
					}
					echo "</tr>";
				}
			?>
		</tbody>
	</tbody>
</table>  

</div>
</div>
	<?php include './footer.inc'; ?>
</center>
</body>
</html>