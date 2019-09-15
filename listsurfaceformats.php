<?php 
	/* 		
		*
		* Vulkan hardware capability database server implementation
		*	
		* Copyright (C) 2016-2017 by Sascha Willems (www.saschawillems.de)
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

<div class='header'>
	<div class='alert alert-warning' role='alert' style='width:auto;'>
		<b>Note:</b> Surface format data only available for reports with version 1.2 (or higher)
	</div>
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
				<td>Format</td>
				<td>Reports</td>
			</tr>
		</thead>
		<tbody>
			<?php
				try {
					DB::connect();				
					$sql = "SELECT
						VkFormat(dsf.format) as formatname,
						count(distinct(reportid)) as coverage
						from devicesurfaceformats dsf
						join reports r on r.id = dsf.reportid
						where ostype = :ostype
						group by formatname";
					$modes = DB::$connection->prepare($sql);
					$modes->execute(['ostype' => ostype($platform)]);
					if ($modes->rowCount() > 0) { 		
						foreach ($modes as $mode) {
							echo "<tr>";						
							echo "<td class='value'><a href='listreports.php?surfaceformat=".$mode['formatname']."'>".$mode['formatname']."</a> (<a href='listreports.php?surfaceformat=".$mode['formatname']."&option=not'>not</a>)</td>";
							echo "<td class='value'>".$mode['coverage']."</td>";
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