<?php 
	/* 		
		*
		* Vulkan hardware capability database server implementation
		*	
		* Copyright (C) by Sascha Willems (www.saschawillems.de)
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
	
	$platform = "windows";
	if (isset($_GET['platform'])) {
		$platform = $_GET['platform'];
	}		
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
	<?php echo "<h4>Device limits for <img src='images/".$platform."logo.png' height='14px' style='padding-right:5px'/>".ucfirst($platform); ?>	
</div>		

<center>	
	<div>
		<ul class='nav nav-tabs'>
			<li <?php if ($platform == "windows") { echo "class='active'"; } ?>> <a href='listlimits.php?platform=windows'><img src="images/windowslogo.png" height="14px" style="padding-right:5px">Windows</a> </li>
			<li <?php if ($platform == "linux")   { echo "class='active'"; } ?>> <a href='listlimits.php?platform=linux'><img src="images/linuxlogo.png" height="16px" style="padding-right:4px">Linux</a> </li>
			<li <?php if ($platform == "android") { echo "class='active'"; } ?>> <a href='listlimits.php?platform=android'><img src="images/androidlogo.png" height="16px" style="padding-right:4px">Android</a> </li>
		</ul>
	</div>

	<div class='tablediv' style='width:auto; display: inline-block;'>
	
	<table id="limits" class="table table-striped table-bordered table-hover responsive" style='width:auto;'>
		<thead>
			<tr>
				<th>Limit</th>
				<th style="text-align: center;">Min</th>
				<th style="text-align: center;">Max</th>
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
							$sql = "SELECT min(`".$limit[0]."`) as lower, max(`".$limit[0]."`) from devicelimits dl join reports r on r.id = dl.reportid where ";
							// Apply limit requirement if present
							if ($row[1] != null) {
								$sql .= " dl.reportid in (select distinct(reportid) from devicefeatures df where df.".$limit[1]." = 1) and";
							}
							// Fix for invalid reports reporting a supported feature as zero				
							$sql .= " `".$limit[0]."` <> 0";
							$sql .= " and r.ostype = :ostype";

							$stmnt = DB::$connection->prepare($sql);
							$stmnt->execute(['ostype' => ostype($platform)]);
							$range = $stmnt->fetch(PDO::FETCH_NUM);

							$limitInfo = ($limit[1] != null) ? " <span title=\"Requires feature $limit[1]\">[?]</span>" : "";
							echo "<tr>";
							echo "<td><a href='displaydevicelimit.php?name=".$limit[0]."&platform=$platform'>".$limit[0]."</a>$limitInfo</td>";
							echo "<td class='unsupported'>".round($range[0], 3)."</td>";
							echo "<td class='supported'>".round($range[1], 3)."</td>";
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