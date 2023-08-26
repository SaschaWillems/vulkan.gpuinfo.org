<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *
 * Copyright (C) 2016-2023 by Sascha Willems (www.saschawillems.de)
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
require './database/sqlrepository.php';
require './includes/functions.php';

$platform = 'all';
if (isset($_GET['platform'])) {
	$platform = GET_sanitized('platform');
}

PageGenerator::header("Queue families");
?>

<div class='header'>
	<?php echo "<h4>Queue families for " . PageGenerator::filterInfo()?>
</div>

<center>
	<?php PageGenerator::platformNavigation('listqueuefamilies.php', $platform, true); ?>

	<div class="tablediv" style="width:auto; display: inline-block;">
		<table id="limits" class="table table-striped table-bordered table-hover responsive with-platform-selection">
			<thead>
				<tr>
					<th>Queue family flags</th>
					<th style="text-align: center;"><img src="images/icons/check.png" width="16px"></th>
					<th style="text-align: center;"><img src="images/icons/missing.png" width="16px"></th>
				</tr>
			</thead>
			<tbody>
				<?php
				try {
					DB::connect();
					$start = microtime(true);
					$queueFamilies = SqlRepository::listQueueFamilies();
					foreach ($queueFamilies as $row) {
						$coverageLink = "listdevicescoverage.php?queuefamilyflags=".$row['flags']."&platform=$platform";
						$queueFamilyFlags = join("<br>", getQueueFlags($row['flags']));
						if ($queueFamilyFlags == "") {
							$queueFamilyFlags = "[none]";
						}
						echo "<tr>";
						echo "<td class='value'>$queueFamilyFlags</td>";
						echo "<td class='value'><a class='supported' href='$coverageLink'>".$row['coverage']."<span style='font-size:10px;'>%</span></a></td>";
						echo "<td class='value'><a class='na' href='$coverageLink&option=not'>".round(100.0 - $row['coverage'], 2)."<span style='font-size:10px;'>%</span></a></td>";
						echo "</tr>";
					}
				} catch (PDOException $e) {
					echo "<b>Error while fetching data: " . $e->getMessage() . "</b><br>";
				}
				// DB::log('listqueuefamilies.php', null, (microtime(true) - $start) * 1000);
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