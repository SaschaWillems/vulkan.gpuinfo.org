<?php

/** 		
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright 2016-2022 (C) by Sascha Willems (www.saschawillems.de)
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

require 'pagegenerator.php';
require './database/database.class.php';
require './database/sqlrepository.php';
require './includes/functions.php';
require './includes/constants.php';

$platform = 'all';
if (isset($_GET['platform'])) {
	$platform = GET_sanitized('platform');
}

PageGenerator::header("Surface usage flags");
?>

<div class='header'>
	<?php echo "<h4>Surface usage flag support on ".PageGenerator::filterInfo($platform); ?>
</div>

<center>
	<?php 
		PageGenerator::platformNavigation('listsurfaceusageflags.php', $platform, true); ?>

		<div class='tablediv' style='width:auto; display: inline-block;'>
		<table id="presentmodes" class="table table-striped table-bordered table-hover reporttable responsive with-platform-selection">
			<thead>
				<tr>
					<th></th>
					<th colspan=2 style="text-align: center;">Device coverage</th>
				</tr>
				<th>Mode</th>
				<th style="text-align: center;"><img src='images/icons/check.png' width=16px></th>
				<th style="text-align: center;"><img src='images/icons/missing.png' width=16px></th>
				</th>
			</thead>
			<tbody>
				<?php
				DB::connect();
				try {
					$surfaceusageflags = SqlRepository::listSurfaceUsageFlags($surface_usage_flags);
					foreach ($surfaceusageflags as $surfaceusageflag) {
						$coverageLink = "listdevicescoverage.php?surfaceusageflag=".$surfaceusageflag['name']."&platform=$platform";
						$coverage = $surfaceusageflag['coverage'];
						if ($coverage > 0) {
							echo "<tr>";
							echo "<td class='value'>".$surfaceusageflag['name']."</td>";
							echo "<td class='text-center'><a class='supported' href='$coverageLink'>" . round($coverage, 2) . "<span style='font-size:10px;'>%</span></a></td>";
							echo "<td class='text-center'><a class='na' href='$coverageLink&option=not'>" . round(100 - $coverage, 2) . "<span style='font-size:10px;'>%</span></a></td>";
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

	<script>
		$(document).ready(function() {
			var table = $('#presentmodes').DataTable({
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

			$("#searchbox").on("keyup search input paste cut", function() {
				table.search(this.value).draw();
			});

		});
	</script>

	<?php PageGenerator::footer(); ?>
</center>
</body>

</html>