<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2022 Sascha Willems (www.saschawillems.de)
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

include 'pagegenerator.php';
include './database/database.class.php';
require './database/sqlrepository.php';
include './includes/constants.php';
include './includes/functions.php';

$platform = 'all';
if (isset($_GET['platform'])) {
	$platform = GET_sanitized('platform');
}

PageGenerator::header("Features");
?>

<div class='header'>
	<?php echo "<h4>Core 1.0 device feature coverage for ".PageGenerator::filterInfo($platform) ?>
</div>

<center>
	<?php PageGenerator::platformNavigation('listfeaturescore10.php', $platform, true); ?>

	<div class='tablediv' style='width:auto; display: inline-block;'>
		<table id="features" class="table table-striped table-bordered table-hover responsive with-platform-selection">
			<thead>
				<tr>
					<th></th>
					<th colspan=3 style="text-align: center;">Device coverage</th>
				</tr>
				<th>Feature</th>
				<th style="text-align: center;"><img src='images/icons/check.png' width=16px></th>
				<th style="text-align: center;"><img src='images/icons/missing.png' width=16px></th>
				</tr>
			</thead>
			<tbody>
				<?php
				DB::connect();
				try {
					$features = SqlRepository::listCoreFeatures(SqlRepository::VK_API_VERSION_1_0);
					foreach ($features as $feature => $coverage) {
						$coverageLink = "listdevicescoverage.php?feature=$feature&platform=$platform";
						echo "<tr>";
						echo "<td>" . $feature . "</td>";
						echo "<td class='text-center'><a class='supported' href=\"$coverageLink\">$coverage<span style='font-size:10px;'>%</span></a></td>";
						echo "<td class='text-center'><a class='na' href=\"$coverageLink&option=not\">" . round(100 - $coverage, 1) . "<span style='font-size:10px;'>%</span></a></td>";
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

	<script>
		$(document).ready(function() {
			var table = $('#features').DataTable({
				"pageLength": -1,
				"paging": false,
				"stateSave": false,
				"searchHighlight": true,
				"dom": 'f',
				"bInfo": false,
				"order": [
					[0, "asc"]
				],
				"columnDefs": [{
					"targets": [1, 2]
				}]
			});
		});
	</script>

	<?php PageGenerator::footer(); ?>

</center>
</body>

</html>