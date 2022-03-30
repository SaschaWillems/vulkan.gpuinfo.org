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

require 'pagegenerator.php';
require './database/database.class.php';
require './database/sqlrepository.php';
require './includes/functions.php';

$platform = 'all';
if (isset($_GET['platform'])) {
	$platform = GET_sanitized('platform');
}

PageGenerator::header("Profiles");
?>

<div class='header'>
	<?php echo "<h4>Profile coverage for ".PageGenerator::filterInfo($platform) ?>
</div>
<div class="alert alert-info" role="alert" style="text-align: center">
	<b>Note:</b> Data is based on reports submitted or updated with version 3.2 or newer of the Hardware Capability Viewer and does not contain reports from earlier versions.
</div>

<center>
	<?php PageGenerator::platformNavigation('listprofiles.php', $platform, true); ?>

	<div class='tablediv' style='width:auto; display: inline-block;'>
		<table id="profiles" class="table table-striped table-bordered table-hover responsive" style='width:auto;'>
			<thead>
				<tr>
					<th></th>
					<th colspan=2 style="text-align: center;">Device coverage</th>
				</tr>
				<tr>
					<th>Profile</th>
					<th style="text-align: center;"><img src='images/icons/check.png' width=16px></th>
					<th style="text-align: center;"><img src='images/icons/missing.png' width=16px></th>
				</tr>
			</thead>
			<tbody>
				<?php
					DB::connect();
					try {
						$profiles = SqlRepository::listProfiles();
						foreach ($profiles as $profile) {
							$coverageLink = "listdevicescoverage.php?profile=".$profile['name']."&platform=$platform";
							$coverage = $profile['coverage'];
							echo "<tr>";
							echo "<td>".$profile['name']."</td>";
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
			var table = $('#profiles').DataTable({
				"pageLength": -1,
				"paging": false,
				"stateSave": false,
				"searchHighlight": true,
				"dom": 'f',
				"bInfo": false,
				"fixedHeader": {
					"header": true,
					"headerOffset": 50
				},
				"order": [
					[0, "asc"]
				],
				"columnDefs": [{
					"targets": [1, 2],
				}]
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