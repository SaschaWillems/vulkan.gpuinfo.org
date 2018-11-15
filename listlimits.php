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
	include './header.inc';	
?>

<script>
	$(document).ready(function() {
		var table = $('#limits').DataTable({
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
	<h4 style='margin-left:10px;'>Listing device limits</h4>
</div>

<center>	
	<div class='parentdiv'>
	<div class='tablediv' style='width:auto; display: inline-block;'>
	
	<table id="limits" class="table table-striped table-bordered table-hover responsive" style='width:auto;'>
		<thead>
			<tr>
				<td>Limit</td>
				<td>Min</td>
				<td>Max</td>
				<td>Requirement</td>
			</tr>
		</thead>
		<tbody>		
			<?php	
				DB::connect();
				try {
					$res =DB::$connection->prepare("select count(*) from reports"); 
					$res->execute(); 
					$reportCount = $res->fetchColumn(); 

					$sql = "SELECT COLUMN_NAME as name, (SELECT feature from limitrequirements where limitname = name) as requirement from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME = 'devicelimits' and COLUMN_NAME not in ('reportid')";
					$limits = DB::$connection->prepare($sql);
					$limits->execute();

					if ($limits->rowCount() > 0) { 
						while ($limit = $limits->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {								
							$sql = "select min(`".$limit[0]."`) as lower, max(`".$limit[0]."`) from devicelimits dl where ";
							// Apply limit requirement if present
							if ($row[1] != null) {
								$sql .= " dl.reportid in (select distinct(reportid) from devicefeatures df where df.".$limit[1]." = 1) and";
							}
							// Fix for invalid reports reporting a supported feature as zero				
							$sql .= " `".$limit[0]."` <> 0";

							$stmnt = DB::$connection->prepare($sql);
							$stmnt->execute();
							$range = $stmnt->fetch(PDO::FETCH_NUM);

							echo "<tr>";
							echo "<td><a href='displaydevicelimit.php?name=".$limit[0]."'>".$limit[0]."</a></td>";		
							echo "<td class='unsupported'>".round($range[0], 3)."</td>";
							echo "<td class='supported'>".round($range[1], 3)."</td>";
							echo "<td>".$limit[1]."</td>";
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