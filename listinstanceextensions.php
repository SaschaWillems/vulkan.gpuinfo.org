<?php 
	/* 		
		*
		* Vulkan hardware capability database server implementation
		*	
		* Copyright (C) 2016-2018 by Sascha Willems (www.saschawillems.de)
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
	include './database/database.class.php';
	
	PageGenerator::header("Instance extensions");
?>

<div class='header'>
	<h4>Listing all available instance extensions</h4>
</div>			

<center>	
	<div class='parentdiv'>
	<div class='tablediv' style='width:auto; display: inline-block;'>

	<table id="extensions" class="table table-striped table-bordered table-hover responsive" style='width:auto;'>
		<thead>
			<tr>			
				<th>Extensions</th>
			</tr>
		</thead>
		<tbody>		
			<?php
				DB::connect();
				try {
					$res =DB::$connection->prepare("select count(*) from reports"); 
					$res->execute(); 
					$reportCount = $res->fetchColumn(); 

					$extensions = DB::$connection->prepare("SELECT name from instanceextensions");
					$extensions->execute($params);

					if ($extensions->rowCount() > 0) { 
						while ($extension = $extensions->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {								
							echo "<tr>";
							echo "<td class='value'><a href='listreports.php?instanceextension=".$extension[0]."'>".$extension[0]."</a> (<a href='listreports.php?instanceextension=".$extension[0]."&option=not'>not</a>)</td>";
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
</div>

<script>
	$(document).ready(function() {
		var table = $('#extensions').DataTable({
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

<?php PageGenerator::footer(); ?>

</center>
</body>
</html>