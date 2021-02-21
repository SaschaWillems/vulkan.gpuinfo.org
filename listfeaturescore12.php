<?php

/** 		
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2021 Sascha Willems (www.saschawillems.de)
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
require './database/sqlrepository.class.php';
require './includes/functions.php';
require './includes/constants.php';

$platform = "windows";
if (isset($_GET['platform'])) {
	$platform = $_GET['platform'];
}

PageGenerator::header("Core 1.2 features");
$sql_repository = new SqlRepository($platform);
?>

<div class='header'>
	<?php echo "<h4>Core 1.2 feature coverage for".PageGenerator::platformInfo($platform) ?>
</div>
<div class="alert alert-info" role="alert" style="text-align: center">
	<b>Note:</b> Data is based on reports submitted or updated with version 3.0 or newer of the Hardware Capability Viewer and does not contain reports from earlier versions.
</div>

<center>
	<?php 
	$sql_repository->filterHeader();
	PageGenerator::platformNavigation('listfeaturescore12.php', $platform); 
	?>

	<div class='tablediv' style='width:auto; display: inline-block;'>
		<table id="features" class="table table-striped table-bordered table-hover responsive" style='width:auto;'>
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
					$device_count = $sql_repository->getFeatureCoverageDeviceCount(VK_API_VERSION_1_2);
					$coverages = $sql_repository->getFeatureCoverageCore(VK_API_VERSION_1_2);
					foreach ($coverages as $feature => $coverage) {
				 		$link = "listdevicescoverage.php?core=1.2&feature=$feature&platform=$platform";
						$value = ($device_count > 0) ? round($coverage / $device_count * 100, 1) : 0;
						echo "<tr>";
						echo "<td>$feature</td>";
						echo "<td class='text-center'><a class='supported' href=\"$link\">$value<span style='font-size:10px;'>%</span></a></td>";
						echo "<td class='text-center'><a class='na' href=\"$link&option=not\">" . round(100 - $value, 1) . "<span style='font-size:10px;'>%</span></a></td>";
						echo "</tr>";
					}
				} catch (Exception $e) {
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