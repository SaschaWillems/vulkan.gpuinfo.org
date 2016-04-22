<?php 
	/* 		
		*
		* Vulkan hardware capability database server implementation
		*	
		* Copyright (C) 2015 by Sascha Willems (www.saschawillems.de)
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
	
	$sqlResult = mysql_query("SELECT count(*) FROM VkFormat");
	$sqlCount = mysql_result($sqlResult, 0);
	echo "<div class='header'>";
		echo "<h4 style='margin-left:10px;'>Listing all available image formats ($sqlCount)</h4>";
	echo "</div>";				
?>

<center>	
	<div class="tablediv">
	<table id="formats" class="table table-striped table-bordered table-hover reporttable responsive" style="width:100%;" >
		<?php		
			$reportCount = mysql_result(mysql_query("select count(*) from reports"), 0);	
		
            $sqlstr = "select * from viewFormats";                
			$sqlresult = mysql_query($sqlstr) or die(mysql_error());  
			
			echo "<thead><tr>";  
			
			echo "<td class='caption'>Format</td>";		   
			echo "<td class='caption'>Linear</td>";		   
			echo "<td class='caption'>Optimal</td>";		   
			echo "<td class='caption'>Buffer</td>";		   
			echo "</tr>";
			echo "</thead><tbody>";

			while ($row = mysql_fetch_row($sqlresult))
            {
				echo "<tr>";						
				echo "<td class='value'>".$row[0]."</td>";
				echo "<td class='value' align=center><a href='listreports.php?linearformat=".$row[0]."'>".round(($row[1]/$reportCount*100.0), 2)."%</a></td>";
				echo "<td class='value' align=center><a href='listreports.php?optimalformat=".$row[0]."'>".round(($row[2]/$reportCount*100.0), 2)."%</a></td>";
				echo "<td class='value' align=center><a href='listreports.php?bufferformat=".$row[0]."'>".round(($row[3]/$reportCount*100.0), 2)."%</a></td>";						
				echo "</tr>";	    
            }            			
			dbDisconnect();	
		?>   
	</tbody>
</table>  

<script>
	$(document).ready(function() {
		$('#formats').DataTable({
			"pageLength" : -1,
			"paging" : false,
			"stateSave": false, 
			"searchHighlight" : true,
			"bInfo": false,	
			"sDom": 'flipt',	
		});
	} );	
</script>
</div>
	<?php include './footer.inc'; ?>
</center>
</body>
</html>