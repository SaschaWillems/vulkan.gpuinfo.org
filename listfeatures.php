<?php 
	/* 		
		*
		* Vulkan hardware capability database server implementation
		*	
		* Copyright (C) Sascha Willems (www.saschawillems.de)
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
			"order": [[ 0, "asc" ]],
			"columnDefs": [{
      			"targets": [ 1, 2, 3 ]
			}]			
		});
	} );	
</script>

<div class='header'>
	<h4 style='margin-left:10px;'>Listing device features</h4>
</div>

<center>	

	<div class='tablediv' style='width:auto; display: inline-block;'>

	<table id="features" class="table table-striped table-bordered table-hover responsive" style='width:auto;'>
		<thead>
			<tr>			
				<th></th>
				<th colspan=3 style="text-align: center;">Device coverage</th>
			</tr>
			<tr>				
				<td>Feature</td>
				<th style="text-align: center; width:90px;">Windows</th>
				<th style="text-align: center; width:90px;">Linux</th>
				<th style="text-align: center; width:90px;">Android</th>
			</tr>
		</thead>
		<tbody>		
			<?php	
				DB::connect();
				try {
					$res =DB::$connection->prepare("SELECT count(*) from reports"); 
					$res->execute(); 
					$reportCount = $res->fetchColumn(); 

					$viewDeviceCount =DB::$connection->prepare("SELECT * from viewDeviceCount");
					$viewDeviceCount->execute(); 
					$deviceCounts = $viewDeviceCount->fetch(PDO::FETCH_ASSOC);

					// Collect feature column names
					$sql = "SELECT COLUMN_NAME from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME = 'devicefeatures' and COLUMN_NAME not in ('reportid')";
					$stmnt = DB::$connection->prepare($sql);
					$stmnt->execute();

					$features = array();
					$sqlColumns = "";
					while ($row = $stmnt->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
						$features[] = $row[0];
						$sqlColumns .= "max(".$row[0].") as $row[0],";
					}

					// Get supported counts per platform
					$supportedCounts[0] = array();
					$supportedCounts[1] = array();
					$supportedCounts[2] = array();
					for ($i = 0; $i < 3; $i++) {
						$stmnt = DB::$connection->prepare(
							"SELECT ifnull(r.displayname, dp.devicename) as device, "
							.substr($sqlColumns, 0, -1).
							" FROM devicefeatures df join deviceproperties dp on dp.reportid = df.reportid join reports r on r.id = df.reportid where r.ostype = ".(integer)$i." group by device");
						$stmnt->execute();
						while ($row = $stmnt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
							foreach($row as $key => $value) {
								if (strcasecmp($key, 'device') != 0) {
									$supportedCounts[$i][$key] += $value;
								}
							}
						}
					}
					foreach($features as $feature) {
						echo "<tr>";
						echo "<td>".$feature."</td>";
						echo "<td class='text-center'><a href=\"listdevicescoverage.php?platform=windows&feature=$feature\">".round($supportedCounts[0][$feature]/$deviceCounts["windows"]*100, 2)."%</a></td>";
						echo "<td class='text-center'><a href=\"listdevicescoverage.php?platform=linux&feature=$feature\">".round($supportedCounts[1][$feature]/$deviceCounts["linux"]*100, 2)."%</a></td>";
						echo "<td class='text-center'><a href=\"listdevicescoverage.php?platform=android&feature=$feature\">".round($supportedCounts[2][$feature]/$deviceCounts["android"]*100, 2)."%</a></td>";
						echo "</tr>";
					}
				} catch (PDOException $e) {
					echo "<b>Error while fetching data!</b><br>";
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