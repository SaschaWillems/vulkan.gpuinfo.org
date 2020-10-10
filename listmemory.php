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
	 */
	
	include 'page_generator.php';
	include './dbconfig.php';
	include './functions.php';

	$platform = "windows";
	if (isset($_GET['platform'])) {
		$platform = $_GET['platform'];
	}

	PageGenerator::header("Memory");
?>

<div class='header'>
	<?php echo "<h4>Memory types for <img src='images/".$platform."logo.png' height='14px' style='padding-right:5px'/>".ucfirst($platform); ?>
</div>

<center>
	<div>
		<ul class='nav nav-tabs'>
			<li <?php if ($platform == "windows") { echo "class='active'"; } ?>> <a href='listmemory.php?platform=windows'><img src="images/windowslogo.png" height="14px" style="padding-right:5px">Windows</a> </li>
			<li <?php if ($platform == "linux")   { echo "class='active'"; } ?>> <a href='listmemory.php?platform=linux'><img src="images/linuxlogo.png" height="16px" style="padding-right:4px">Linux</a> </li>
			<li <?php if ($platform == "android") { echo "class='active'"; } ?>> <a href='listmemory.php?platform=android'><img src="images/androidlogo.png" height="16px" style="padding-right:4px">Android</a> </li>
		</ul>
	</div>

	<div class="tablediv" style="width:auto; display: inline-block;">

	<table id="limits" class="table table-striped table-bordered table-hover responsive" style="width:auto;">
		<thead>
			<tr>
				<th>Memory type</th>
				<th style="text-align: center;"><img src="icon_check.png" width="16px"></th>
				<th style="text-align: center;"><img src="icon_missing.png" width="16px"></th>
			</tr>
		</thead>
		<tbody>
			<?php
				try {
					DB::connect();
					$deviceCount = getDeviceCount($platform);
					$sql = "SELECT
						propertyflags as memtype, count(distinct(ifnull(r.displayname, dp.devicename))) as coverage
						from devicememorytypes dmt
						join reports r on r.id = dmt.reportid
						join deviceproperties dp on dp.reportid = r.id
						where ostype = :ostype
						group by memtype desc";
					$result = DB::$connection->prepare($sql);
					$result->execute(['ostype' => ostype($platform)]);

					foreach ($result as $row) {
						$coverageLink = "listdevicescoverage.php?"."memorytype=".$row['memtype']."&platform=$platform";
						$coverage = $row['coverage'] / $deviceCount * 100.0;
						$memoryFlags = join("<br>", getMemoryTypeFlags($row['memtype']));
						if ($memoryFlags == "") $memoryFlags = "0";
						echo "<tr>";
						echo "<td class='value'>$memoryFlags</td>";
						echo "<td class='value'><a class='supported' href='$coverageLink'>".round($coverage, 1)."<span style='font-size:10px;'>%</span></a></td>";
						echo "<td class='value'><a class='na' href='$coverageLink&option=not'>".round(100 - $coverage, 1)."<span style='font-size:10px;'>%</span></a></td>";
						echo "</tr>";
					}
				} catch (PDOException $e) {
					echo "<b>Error while fetcthing data: ".$e->getMessage( )."</b><br>";
				}
				DB::disconnect();
			?>
		</tbody>
	</table>

	</div>
	</div>


	<script>
	$(document).ready(function() {
		var table = $('#limits').DataTable({
			"pageLength" : -1,"paging" : false,
			"stateSave": false, 
			"searchHighlight" : true,
			"dom": 'f',
			"bInfo": false,
			"order": [[ 0, "asc" ]]
		});
	} );
	</script>

<?php PageGenerator::footer(); ?>

</center>
</body>
</html>