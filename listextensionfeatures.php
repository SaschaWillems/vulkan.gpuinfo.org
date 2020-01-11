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
	
	include './dbconfig.php';
	include 'page_generator.php';
	PageGenerator::header('Extension feature listing');
?>
	
<script>
	$(document).ready(function() {
		var table = $('#features').DataTable({
			"pageLength" : -1,
			"paging" : false,
			"stateSave": false, 
			"searchHighlight" : true,	
			"dom": 'f',			
			"bInfo": false,	
			"order": [],
			"columnDefs": [
				{ "visible": false, "targets": 1 }
			],				
			"drawCallback": function (settings) {
				var api = this.api();
				var rows = api.rows( {page:'current'} ).nodes();
				var last = null;
				api.column(1, {page:'current'} ).data().each( function ( group, i ) {
					if ( last !== group ) {
						$(rows).eq( i ).before(
							'<tr><td class="group" colspan="4">'+group+'</td></tr>'
						);
						last = group;
					}
				});
			}
		});
	} );	
</script>

<div class='header'>
	<h4>Listing extension device features</h4>
</div>

<center>	

	<div class='parentdiv'>
	<div class='tablediv' style='width:auto; display: inline-block;'>

	<table id="features" class="table table-striped table-bordered table-hover responsive" style='width:auto;'>
		<thead>
			<tr>				
				<td>Feature</td>
				<td>Extension</td>
			</tr>
		</thead>
		<tbody>		
			<?php	
				DB::connect();
				try {
					$sql = "SELECT distinct(name), extension from devicefeatures2 order by extension";
					$features = DB::$connection->prepare($sql);
					$features->execute();

					while ($feature = $features->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {								
						echo "<tr>";
						echo "<td class='subkey'><a href='listreports.php?extensionfeature=".$feature[0]."'>".$feature[0]."</a></td>";
						echo "<td>".$feature[1]."</td>";
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
	</div>
	
<?php PageGenerator::footer(); ?>
	
</center>
</body>
</html>