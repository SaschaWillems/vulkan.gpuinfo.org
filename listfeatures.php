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
	
	echo "<div class='header'>";
		echo "<h4 style='margin-left:10px;'>Listing device features</h4>";
	echo "</div>";				
?>

<center>	
	<div class="tablediv">
	
	<table id="features" class="table table-striped table-bordered table-hover responsive" style='width:100%;'>
		<thead>
			<tr>
				<td class="caption">Feature</td>
				<td class="caption">Coverage</td>
			</tr>
		</thead>
		<tbody>
		
		<?php		
			$reportCount = mysql_result(mysql_query("select count(*) from reports"), 0);
			$sqlresult = mysql_query("select COLUMN_NAME from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME = 'devicefeatures' and COLUMN_NAME not in ('reportid')") or die(mysql_error());  	
			$columns = array();
			while($row = mysql_fetch_row($sqlresult))
			{
				$count = mysql_result(mysql_query("select count(*) from devicefeatures where `".$row[0]."` = 1"), 0);  	
				echo "<tr>";
				echo "<td width='25%'><a href='listreports.php?feature=".$row[0]."'>".$row[0]."</a> (<a href='listreports.php?feature=".$row[0]."&option=not'>not</a>)</td>";
				echo "<td>".number_format((($count/$reportCount)*100.0), 0)."%</td>";
				echo "</tr>";
			}
						
			dbDisconnect();	
		?>   
		
		</tbody>
	</table>  

	<script>
		$(document).ready(function() 
		{
			var table = $('#features').DataTable({
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