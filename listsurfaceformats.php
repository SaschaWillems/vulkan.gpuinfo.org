<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2022 by Sascha Willems (www.saschawillems.de)
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

PageGenerator::header("Surface formats");
?>

<div class='header'>
	<?php echo "<h4>Surface format support on ".PageGenerator::filterInfo($platform); ?>
</div>

<center>
	<?php PageGenerator::platformNavigation('listsurfaceformats.php', $platform, true); ?>

	<div class='tablediv' style='width:auto; display: inline-block;'>
		<table id="surfaceformats" class="table table-striped table-bordered table-hover reporttable responsive with-platform-selection">
			<thead>
				<tr>
				<th></th>
					<th></th>
					<th colspan=2 class="centered">Device coverage</th>
				</tr>
				<tr>
					<th>Format</th>
					<th>Colorspace</th>
					<th class="centered"><img src='images/icons/check.png' width=16px></th>
					<th class="centered"><img src='images/icons/missing.png' width=16px></th>
				</tr>
			</thead>
			<tbody>
				<?php
				DB::connect();
				try {
					$surfaceformats = SqlRepository::listSurfaceFormats();
					foreach ($surfaceformats as $surfaceforamt) {
						$coverageLink = "listdevicescoverage.php?surfaceformat=".$surfaceforamt['format']."&surfaceformatcolorspace=".$surfaceforamt['colorspace']."&platform=$platform";
						$coverage = $surfaceforamt['coverage'];
						echo "<tr>";
						echo "<td class='value'>".$surfaceforamt['format']."</td>";
						echo "<td class='value'>".getColorSpace($surfaceforamt['colorspace'])."</td>";
						echo "<td class='centered'><a class='supported' href='$coverageLink'>" . round($coverage, 2) . "<span style='font-size:10px;'>%</span></a></td>";
						echo "<td class='centered'><a class='na' href='$coverageLink&option=not'>" . round(100 - $coverage, 2) . "<span style='font-size:10px;'>%</span></a></td>";
						echo "</tr>";
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
			var table = $('#surfaceformats').DataTable({
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