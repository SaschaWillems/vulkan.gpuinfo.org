<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2024 Sascha Willems (www.saschawillems.de)
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

PageGenerator::header("Core 1.4 features");
$platform = PageGenerator::getDefaultOSSelection();
PageGenerator::pageCaption("Core 1.4 device feature coverage");
PageGenerator::globalFilterText();
?>

<center>
	<?php PageGenerator::platformNavigation('listpropertiescore14.php', $platform, true); ?>

	<div class='tablediv' style='width:auto; display: inline-block;'>
		<table id="properties" class="table table-striped table-bordered table-hover responsive with-platform-selection">
			<thead>
				</tr>
				<th>Property</th>
				<th style="text-align: center;">Type</th>
				<th style="text-align: center;"></th>
				</tr>
			</thead>
			<tbody>
				<?php
				DB::connect();
				try {
					$properties = SqlRepository::listCoreProperties(SqlRepository::VK_API_VERSION_1_4);
					foreach ($properties as $property => $coverage) {
						$has_coverage = is_numeric($coverage);
						$field_name = getFullFieldName($property);
						echo "<tr>";
						echo "<td>$field_name</a></td>";
						echo "<td class='text-center'>".($has_coverage ? 'Coverage' : 'Values')."</td>";
						if ($has_coverage) {
							$link = "listdevicescoverage.php?core=1.4&coreproperty=$field_name&platform=$platform";
							echo "<td class='text-center'><a class='supported' href=\"$link\">$coverage<span style='font-size:10px;'>%</span></a></td>";
						} else {
							$link = "<a href='displaycoreproperty.php?core=1.4&name=$field_name&platform=$platform'>";
							echo "<td class='text-center'>".$link."Listing</a></td>";
						}
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
			var table = $('#properties').DataTable({
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