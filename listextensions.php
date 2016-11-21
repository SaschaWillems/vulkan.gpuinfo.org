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
	
	dbConnect();	
	
	$sqlResult = mysql_query("SELECT count(*) FROM extensions");
	$sqlCount = mysql_result($sqlResult, 0);
	echo "<div class='header'>";
		echo "<h4>Listing all available extensions ($sqlCount)</h4>";
	echo "</div>";				
?>

<style>
	.dataTables_filter {
		display: none;
	}
</style>

<script>
	$(document).ready(function() {
		var table = $('#extensions').DataTable({
			"pageLength" : -1,
			"paging" : false,
			"stateSave": false, 
			"searchHighlight" : true,	
			"bInfo": false,		
		});

		$("#searchbox").on("keyup search input paste cut", function() {
			table.search(this.value).draw();
		});  		

	} );	
</script>

<center>	
	<div class="tablediv">

	<?php include ("filter.php"); ?>

	<table id="extensions" class="table table-striped table-bordered table-hover reporttable responsive" style='width:auto;'>
		<?php		
		
            $sqlstr = "select name, coverage from viewExtensions";                
			$sqlresult = mysql_query($sqlstr) or die(mysql_error());  
			
			$reportCount = mysql_result(mysql_query("SELECT count(*) from reports"), 0);
		
			echo "<thead><tr>";  
			
			echo "<td class='caption'>Extensions</td>";		   
			echo "<td class='caption'>Coverage</td>";		   
			echo "</tr></thead><tbody>";

			while ($row = mysql_fetch_row($sqlresult))
            {
				echo "<tr>";						
				echo "<td class='value'><a href='listreports.php?extension=".$row[0]."'>".$row[0]."</a> (<a href='listreports.php?extension=".$row[0]."&option=not'>not</a>)</td>";
				echo "<td class='value'>".round(($row[1]/$reportCount*100), 2)."%</td>";
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