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
	
	DB::connect();				
?>

<style>
	.dataTables_filter {
		display: none;
	}
</style>

<script>
	$(document).ready(function() {
		var table = $('#surfaceformats').DataTable({
			"pageLength" : -1,
			"paging" : false,
			"stateSave": false, 
			"searchHighlight" : true,	
			"bInfo": false,	
			"order": [[ 1, "desc" ]]	
		});

		$("#searchbox").on("keyup search input paste cut", function() {
			table.search(this.value).draw();
		});  		

	} );	
</script>

<center>	
	<div class="tablediv">

	<div class='alert alert-warning' role='alert' style='width:auto;'>
		<b>Note:</b> Surface format data only available for reports with version 1.2 (or higher)
	</div>

	<?php include ("filter.php"); ?>

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
					$sql = "SELECT
						VkFormat(dsf.format) as formatname,
						count(distinct(reportid)) as coverage
						from devicesurfaceformats dsf
						group by formatname";
					$modes = DB::$connection->prepare($sql);
					$modes->execute($params);
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