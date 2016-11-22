<?php 
	/* 		
		*
		* Vulkan hardware capability database server implementation
		*	
		* Copyright (C) 2016 by Sascha Willems (www.saschawillems.de)
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
	
	dbConnect();	
	
	$sqlResult = mysql_query("SELECT count(distinct(presentmode)) FROM viewSurfacePresentModes");
	$sqlCount = mysql_result($sqlResult, 0);
	echo "<div class='header'>";
		echo "<h4>Listing all available surface present modes ($sqlCount)</h4>";
	echo "</div>";				
?>

<style>
	.dataTables_filter {
		display: none;
	}
</style>

<script>
	$(document).ready(function() {
		var table = $('#presentmodes').DataTable({
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
		<b>Note:</b> Surface present mode data only available for reports with version 1.2 (or higher)
	</div>

	<!-- <?php include ("filter.php"); ?> -->

	<table id="presentmodes" class="table table-striped table-bordered table-hover reporttable responsive" style='width:auto;'>
		<?php		
		
            $sqlstr = "select presentmode, coverage from viewSurfacePresentModes";                
			$sqlresult = mysql_query($sqlstr) or die(mysql_error());  
			
			$reportCount = mysql_result(mysql_query("SELECT count(*) from reports"), 0);
		
			echo "<thead><tr>";  
			
			echo "<td class='caption'>Mode</td>";		   
			echo "<td class='caption'>Reports</td>";		   
			echo "</tr></thead><tbody>";

			while ($row = mysql_fetch_row($sqlresult))
            {
				echo "<tr>";						
				echo "<td class='value'><a href='listreports.php?surfacepresentmode=".$row[0]."'>".getPresentMode($row[0])."</a> (<a href='listreports.php?surfacepresentmode=".$row[2]."&option=not'>not</a>)</td>";
				echo "<td class='value'>".$row[1]."</td>";
				echo "</tr>";	    
            }            			
			dbDisconnect();	
		?>   
	</tbody>
</table>  


</div>

<?php include './footer.inc'; ?>

</center>
</body>
</html>