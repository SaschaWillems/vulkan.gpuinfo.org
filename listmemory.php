<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *
 * Copyright (C) 2016-2024 by Sascha Willems (www.saschawillems.de)
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

PageGenerator::header("Memory");
$platform = PageGenerator::getDefaultOSSelection();
PageGenerator::pageCaption("Memory types");
PageGenerator::globalFilterText();
?>

<center>
	<?php PageGenerator::platformNavigation('listmemory.php', $platform, true); ?>

	<div class="tablediv" style="width:auto; display: inline-block;">
		<table id="limits" class="table table-striped table-bordered table-hover responsive with-platform-selection">
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
					$start = microtime(true);
					$memoryTypes = SqlRepository::listMemoryTypes();
					foreach ($memoryTypes as $memoryType) {
						$coverageLink = "listdevicescoverage.php?" . "memorytype=" . $memoryType['memtype'] . "&platform=$platform";
						$memoryFlags = join("<br>", getMemoryTypeFlags($memoryType['memtype']));
						if ($memoryFlags == "") $memoryFlags = "0";
						echo "<tr>";
						echo "<td class='value'>$memoryFlags</td>";
						echo "<td class='value'><a class='supported' href='$coverageLink'>" . $memoryType['coverage'] . "<span style='font-size:10px;'>%</span></a></td>";
						echo "<td class='value'><a class='na' href='$coverageLink&option=not'>".round(100.0 - $memoryType['coverage'], 2)."<span style='font-size:10px;'>%</span></a></td>";
						echo "</tr>";
					}
				} catch (PDOException $e) {
					echo "<b>Error while fetching data: " . $e->getMessage() . "</b><br>";
				}
				DB::log('listmemory.php', null, (microtime(true) - $start) * 1000);
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