<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *
 * Copyright (C) 2016-2021 by Sascha Willems (www.saschawillems.de)
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

require 'pagegenerator.php';
require './database/database.class.php';
require './database/sqlrepository.class.php';
require './includes/functions.php';

$platform = "windows";
if (isset($_GET['platform'])) {
	$platform = $_GET['platform'];
}

PageGenerator::header("Memory");
$sql_repository = new SqlRepository($platform);
?>

<div class='header'>
	<?php echo "<h4>Memory types for <img src='images/" . $platform . "logo.png' height='14px' style='padding-right:5px'/>" . ucfirst($platform); ?>
</div>

<center>
	<?php
	$sql_repository->filterHeader();
	PageGenerator::platformNavigation('listmemory.php', $platform);
	?>

	<div class="tablediv" style="width:auto; display: inline-block;">
		<table id="limits" class="table table-striped table-bordered table-hover responsive" style="width:auto;">
			<thead>
				<tr>
					<th>Memory type</th>
					<th style="text-align: center;"><img src="images/icons/check.png" width="16px"></th>
					<th style="text-align: center;"><img src="images/icons/missing.png" width="16px"></th>
				</tr>
			</thead>
			<tbody>
				<?php
				try {
					DB::connect();
					$device_count = $sql_repository->deviceCount();
					$memory_types = $sql_repository->getMemoryTypeCoverage();
					foreach ($memory_types as $memory_type) {
						$coverageLink = "listdevicescoverage.php?" . "memorytype=" . $memory_type['memtype'] . "&platform=$platform";
						$coverage = $memory_type['coverage'] / $device_count * 100.0;
						$memoryFlags = join("<br>", getMemoryTypeFlags($memory_type['memtype']));
						if ($memoryFlags == "") $memoryFlags = "0";
						echo "<tr>";
						echo "<td class='value'>$memoryFlags</td>";
						echo "<td class='value'><a class='supported' href='$coverageLink'>" . round($coverage, 1) . "<span style='font-size:10px;'>%</span></a></td>";
						echo "<td class='value'><a class='na' href='$coverageLink&option=not'>" . round(100 - $coverage, 1) . "<span style='font-size:10px;'>%</span></a></td>";
						echo "</tr>";
					}
				} catch (Exception $e) {
					echo "<b>Error while fetcthing data</b><br>";
				}
				DB::disconnect();
				?>
			</tbody>
		</table>
	</div>

	<script>
		$(document).ready(function() {
			var table = $('#limits').DataTable({
				"pageLength": -1,
				"paging": false,
				"stateSave": false,
				"searchHighlight": true,
				"dom": 'f',
				"bInfo": false,
				"order": [
					[0, "asc"]
				]
			});
		});
	</script>

	<?php PageGenerator::footer(); ?>

</center>
</body>

</html>