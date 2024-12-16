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
 *
 */

require 'pagegenerator.php';
require './database/database.class.php';
require './database/sqlrepository.php';
require './includes/functions.php';
require './includes/constants.php';

PageGenerator::header("Surface composite alpha modes");
$platform = PageGenerator::getDefaultOSSelection();
PageGenerator::pageCaption("Surface composite alpha modes support");
PageGenerator::globalFilterText();
?>

<div class="centered">
	<?php PageGenerator::platformNavigation('listsurfacecompositealphamodes.php', $platform, true); ?>

	<div class='tablediv' style='width:auto; display: inline-block;'>
		<table id="transforms" class="table table-striped table-bordered table-hover reporttable responsive with-platform-selection">
			<thead>
				<tr>
					<th></th>
					<th colspan=2 class="centered">Device coverage</th>
				</tr>
				<tr>
					<th>Mode</th>
					<th class="centered"><img src='images/icons/check.png' width=16px></th>
					<th class="centered"><img src='images/icons/missing.png' width=16px></th>
				</tr>
			</thead>
			<tbody>
				<?php
				DB::connect();
				try {
					$alphamodes = SqlRepository::listSurfaceCompositeAlphaModes(SurfaceConstants::CompositeAlphaFlags);
					foreach ($alphamodes as $alphamode) {
						$coverageLink = "listdevicescoverage.php?surfacecompositealphamode=".$alphamode['name']."&platform=$platform";
						$coverage = $alphamode['coverage'];
						if ($coverage > 0) {
							echo "<tr>";
							echo "<td class='value'>" . $alphamode['name'] . "</td>";
							echo "<td class='centered'><a class='supported' href='$coverageLink'>" . round($coverage, 2) . "<span style='font-size:10px;'>%</span></a></td>";
							echo "<td class='centered'><a class='na' href='$coverageLink&option=not'>" . round(100 - $coverage, 2) . "<span style='font-size:10px;'>%</span></a></td>";
							echo "</tr>";
						}
					}
				} catch (PDOException $e) {
					echo "<b>Error while fetching data!</b><br>";
				}
				DB::disconnect();
				?>
			</tbody>
		</table>
	</div>

	<?php 
		PageGenerator::dataTablesScript('transforms');
		PageGenerator::footer();
	?>

</div>
</body>

</html>