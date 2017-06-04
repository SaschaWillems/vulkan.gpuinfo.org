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
	<h4>Listing all available image and buffer formats</h4>
</div>			

<center>	
	<div class='parentdiv'>
	<div class='tablediv' style='width:auto; display: inline-block;'>

	<table id="formats" class="table table-striped table-bordered table-hover responsive" style='width:auto;'>
		<thead>
			<tr>			
				<th>Format</th>
				<th>Linear</th>
				<th>Optimal</th>
				<th>Buffer</th>
			</tr>
		</thead>
		<tbody>
			<?php
				DB::connect();
				try {
					$res =DB::$connection->prepare("select count(*) from reports"); 
					$res->execute(); 
					$reportCount = $res->fetchColumn(); 

					$formats = DB::$connection->prepare("select * from viewFormats");
					$formats->execute($params);

					if ($formats->rowCount() > 0) { 
						while ($format = $formats->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {								
							echo "<tr>";						
							echo "<td class='value'>".$format[0]."</td>";
							echo "<td class='value' align=center><a href='listreports.php?linearformat=".$format[0]."'>".round(($format[1]/$reportCount*100.0), 2)."%</a></td>";
							echo "<td class='value' align=center><a href='listreports.php?optimalformat=".$format[0]."'>".round(($format[2]/$reportCount*100.0), 2)."%</a></td>";
							echo "<td class='value' align=center><a href='listreports.php?bufferformat=".$format[0]."'>".round(($format[3]/$reportCount*100.0), 2)."%</a></td>";						
							echo "</tr>";	    
						}
					}

				} catch (PDOException $e) {
					echo "<b>Error while fetcthing data!</b><br>";
				}
				DB::disconnect();
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