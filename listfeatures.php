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
		var table = $('#features').DataTable({
			"pageLength" : -1,
			"paging" : false,
			"stateSave": false, 
			"searchHighlight" : true,	
			"dom": 'f',			
			"bInfo": false,	
			"order": [[ 0, "asc" ]]	
		});
	} );	
</script>

<div class='header'>
	<h4 style='margin-left:10px;'>Listing device features</h4>
</div>

<center>	

	<div class='parentdiv'>
	<div class='tablediv' style='width:auto; display: inline-block;'>

	<table id="features" class="table table-striped table-bordered table-hover responsive" style='width:auto;'>
		<thead>
			<tr>				
				<td>Feature</td>
				<td>Supported</td>
				<td>Unsupported</td>
			</tr>
		</thead>
		<tbody>		
			<?php	
				DB::connect();
				try {
					$res =DB::$connection->prepare("select count(*) from reports"); 
					$res->execute(); 
					$reportCount = $res->fetchColumn(); 

					$sql = "select COLUMN_NAME from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME = 'devicefeatures' and COLUMN_NAME not in ('reportid')";
					$features = DB::$connection->prepare($sql);
					$features->execute();

					if ($features->rowCount() > 0) { 
						while ($feature = $features->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {								
							$stmnt = DB::$connection->prepare("select count(*) from devicefeatures where `".$feature[0]."` = 1");
							$stmnt->execute();
							$count = $stmnt->fetchColumn();
							$supported = number_format((($count/$reportCount)*100.0), 0);
							$unsupported = number_format(100.0 - (($count/$reportCount)*100.0), 0);
							echo "<tr>";
							echo "<td>".$feature[0]."</td>";
							echo "<td><a class='supported' href='listreports.php?feature=".$feature[0]."'>".$supported."%</a></td>";
							echo "<td><a class='unsupported' href='listreports.php?feature=".$feature[0]."&option=not'>".$unsupported."%</a></td>";
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
	
<?php include './footer.inc'; ?>
	
</center>
</body>
</html>