<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2026 Sascha Willems (www.saschawillems.de)
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
include './includes/filterlist.class.php';

$filters = ['platform'];
$filter_list = new FilterList($filters);

PageGenerator::header("Extensions");
$platform = PageGenerator::getDefaultOSSelection();
PageGenerator::pageCaption("Extension coverage");
PageGenerator::globalFilterText();
?>

<center>
	<?php PageGenerator::platformNavigation('listextensions.php', $platform, true); ?>

	<div class='tablediv' style='width:auto; display: inline-block;'>
		<table id="extensions" class="table table-striped table-bordered table-hover responsive" style='width:auto;'>
			<thead>
				<tr>
					<th></th>
					<th class="centered" colspan=2>Device coverage</th>
					<th class="centered">First seen</th>
					<th class="centered" colspan=2>Additional</th>
				</tr>
				<tr>
					<th>Extension</th>
					<th class="centered"><img src='images/icons/check.png' width=16px></th>
					<th class="centered"><img src='images/icons/missing.png' width=16px></th>
					<th class="centered"><abbr title="Date at when the extension was first submitted to the database for the current platform selection">Date</abbr></th>
					<th><abbr title="Extension-related features">F.</abbr></th>
					<th><abbr title="Extension-related properties">P.</abbr></th>
				</tr>
			</thead>
			<tbody>
				<?php
				DB::connect();
				try {
$updated_at = null;
					$ostype = null;
					$apiversion = null;
$age = null;
					$namefilter = null;
					if ($filter_list->hasFilter('platform')) {
						$ostype = ostype($filter_list->getFilter('platform'));
					}
if ($filter_list->hasFilter('apiversion')) {
						$apiversion = $filter_list->getFilter('apiversion');
						if ($apiversion == 'all') {
							$apiversion = null;
						}
					}
					if ($filter_list->hasFilter('age')) {
						$age = $filter_list->getFilter('age') == 'recent' ? 1 : null;
					}
					if ($filter_list->hasFilter('namefilter')) {
						$namefilter = $filter_list->getFilter('namefilter');
					}
					$extensions = SqlRepository::listExtensionCoverage($ostype, $apiversion);
					foreach ($extensions as $extension) {
    					$extension_link = "displayextensiondetail.php?extension=".$extension['name'];
						$coverage_link = "listdevicescoverage.php?extension=".$extension['name']."&platform=$platform";
						$feature_link = null;
						if ($extension['hasfeatures']) {
							$feature_link = "<a href='listfeaturesextensions.php?extension=".$extension['name']."&platform=$platform'><span class='glyphicon glyphicon-search' title='Display features for this extension'/></a>";
						}
						$property_link = null;
						if ($extension['hasproperties']) {
							$property_link = "<a href='listpropertiesextensions.php?extension=".$extension['name']."&platform=$platform'><span class='glyphicon glyphicon-search' title='Display properties for this extension'/></a>";
						}				
						$coverage = $extension['coverage'];
						$class = null;
						if ($coverage > 75.0) {
							$class .= ' format-coverage-high';
						} elseif ($coverage > 50.0) {
							$class .= ' format-coverage-medium';
						} elseif ($coverage > 0.0) {
							$class .= ' format-coverage-low';
						}									
						echo "<tr>";
						echo "<td><a href=".$extension_link.">".$extension['name']."</a></td>";
						echo "<td class='centered'><a class='$class' href=\"$coverage_link\">$coverage<span style='font-size:10px;'>%</span></a></td>";
						echo "<td class='centered'><a class='na' href=\"$coverage_link&option=not\">".round(100.0 - $coverage, 2)."<span style='font-size:10px;'>%</span></a></td>";
						echo "<td class='centered'>".$extension['firstseen']."</td>";
						echo "<td class='centered'>$feature_link</td>";
						echo "<td class='centered'>$property_link</td>";			
						echo "</tr>";
					}
				} catch (PDOException $e) {
					echo "<b>Error while fetching data!</b><br>";
				}
				$updated_at = SqlRepository::getCacheInfo('extension_stats');
				DB::disconnect();
				// @todo: last updated
				?>
			</tbody>			
		</table>
	</div>
	<div><?= "Last updated at $updated_at" ?></div>

	<script>
		$(document).ready(function() {
			var table = $('#extensions').DataTable({
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