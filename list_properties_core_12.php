<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2020 Sascha Willems (www.saschawillems.de)
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

require 'page_generator.php';
require './dbconfig.php';
require './functions.php';
require './constants.php';

$platform = "windows";
if (isset($_GET['platform'])) {
	$platform = $_GET['platform'];
}

PageGenerator::header("Core 1.2 properties");
?>

<div class='header'>
	<?php echo "<h4>Core 1.2 properties for <img src='images/" . $platform . "logo.png' height='14px' style='padding-right:5px'/>" . ucfirst($platform); ?>
</div>
<div class="alert alert-info" role="alert" style="text-align: center">
	<b>Note:</b> Data is based on reports submitted or updated with version 3.0 or newer of the Hardware Capability Viewer and does not contain reports from earlier versions.
</div>

<center>
	<div>
		<ul class='nav nav-tabs'>
			<?php
			foreach ($platforms as $navplatform) {
				$active = ($platform == $navplatform);
				echo "<li" . ($active ? ' class="active"' : null) . "><a href='list_properties_core_12.php?platform=$navplatform'><img src='images/" . $navplatform . "logo.png' height='14px' style='padding-right:5px'>" . ucfirst($navplatform) . "</a> </li>\n";
			}
			?>
		</ul>
	</div>

	<div class='tablediv' style='width:auto; display: inline-block;'>
		<table id="properties" class="table table-striped table-bordered table-hover responsive" style='width:auto;'>
			<thead>
				</tr>
				<th>Property</th>
				<th style="text-align: center;">Type</th>
				<th style="text-align: center;"></th>
				</tr>
			</thead>
			<tbody>
				<?php
				$coverage_columns = [
					'shaderSignedZeroInfNanPreserveFloat16',
					'shaderSignedZeroInfNanPreserveFloat32',
					'shaderSignedZeroInfNanPreserveFloat64',
					'shaderDenormPreserveFloat16',
					'shaderDenormPreserveFloat32',
					'shaderDenormPreserveFloat64',
					'shaderDenormFlushToZeroFloat16',
					'shaderDenormFlushToZeroFloat32',
					'shaderDenormFlushToZeroFloat64',
					'shaderRoundingModeRTEFloat16',
					'shaderRoundingModeRTEFloat32',
					'shaderRoundingModeRTEFloat64',
					'shaderRoundingModeRTZFloat16',
					'shaderRoundingModeRTZFloat32',
					'shaderRoundingModeRTZFloat64',
					'shaderUniformBufferArrayNonUniformIndexingNative',
					'shaderSampledImageArrayNonUniformIndexingNative',
					'shaderStorageBufferArrayNonUniformIndexingNative',
					'shaderStorageImageArrayNonUniformIndexingNative',
					'shaderInputAttachmentArrayNonUniformIndexingNative',
					'robustBufferAccessUpdateAfterBind',
					'quadDivergentImplicitLod',
					'independentResolveNone',
					'independentResolve',
					'filterMinmaxSingleComponentFormats',
					'filterMinmaxImageComponentMapping'
				];
				$ignore_columns = [];
				DB::connect();
				try {
					$deviceCount = DB::getCount("SELECT count(distinct(displayname)) from reports join deviceproperties11 dp on dp.reportid = id where ostype = :platform", ['platform' => ostype($platform)]);

					// Collect coverage numbers
					$columns = [];
					foreach ($coverage_columns as $column) {
						$columns[] = "max(dp.`$column`) as `$column`";
					}

					$supportedCounts = [];
					$stmnt = DB::$connection->prepare(
						"SELECT r.displayname as device, " . implode(',', $columns) . " FROM deviceproperties12 dp join reports r on r.id = dp.reportid where r.ostype = :ostype group by device"
					);
					$stmnt->execute(['ostype' => ostype($platform)]);
					while ($row = $stmnt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
						foreach ($row as $key => $value) {
							if (strcasecmp($key, 'device') != 0) {
								$supportedCounts[$key] += $value;
							}
						}
					}

					// Collect properties from column names
					$sql = "SELECT COLUMN_NAME from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME = 'deviceproperties12' and COLUMN_NAME not in ('reportid')";
					$stmnt = DB::$connection->prepare($sql);
					$stmnt->execute();

					while ($row = $stmnt->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
						if (in_array($row[0], $ignore_columns)) {
							continue;
						}
						$has_coverage = in_array($row[0], $coverage_columns);
						$link = "<a href='display_core_property.php?core=1.2&name=" . $row[0] . "&platform=$platform'>";
						echo "<tr>";
						echo "<td>" . $row[0] . "</a></td>";
						echo "<td class='text-center'>" . ($has_coverage ? 'Coverage' : 'Values') . "</td>";
						if ($has_coverage) {
							$coverageLink = "listdevicescoverage.php?coreproperty=" . $row[0] . "&platform=$platform";
							$coverage = round($supportedCounts[$row[0]] / $deviceCount * 100, 1);
							echo "<td class='text-center'><a class='supported' href=\"$coverageLink\">$coverage<span style='font-size:10px;'>%</span></a></td>";
						} else {
							echo "<td class='text-center'>" . $link . "Listing</a></td>";
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