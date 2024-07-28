<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2024by Sascha Willems (www.saschawillems.de)
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

 /**
  * Device extension detail page
  */

require './pagegenerator.php';
require './database/database.class.php';
require 'database/sqlrepository.php';
require './includes/functions.php';
require './includes/filterlist.class.php';
require './includes/chart.php';
require './includes/constants.php';

$filters = ['extension'];
$filter_list = new FilterList($filters);
$extension_name = $filter_list->getFilter('extension');

PageGenerator::header($extension_name);

try {
	DB::connect();

	if (!SqlRepository::extensionExists($extension_name)) {
		PageGenerator::errorMessage("This is not the <strike>droid</strike> extension you are looking for!</strong><br><br>You may have passed a wrong extension name.");
		DB::disconnect();
	}

	$extension_coverage = SqlRepository::getExtensionCoverage($extension_name);

	// Link to the manpage
	// Note: Some extensions don't have a man page and may lead to a 404
    $man_page_link = "<a href='".VULKAN_REGISTRY_URL.$extension_name.".html' target='_blank'>Vulkan registry manpage</a>";
	$extension_detail = "<div style='margin-top: 10px;' class='subcaption-level-2'>$man_page_link</div>";

	// Display links to additional device features and or properties, so users can easily access them
	$stmnt = DB::$connection->prepare("SELECT
		(SELECT COUNT(DISTINCT df2.name) FROM devicefeatures2 df2 WHERE (df2.extension = ext.name)) AS features2,
		(SELECT COUNT(DISTINCT dp2.name) FROM deviceproperties2 dp2 WHERE (dp2.extension = ext.name)) AS properties2				
		FROM extensions ext where ext.name = :extension");
	$stmnt->execute(['extension' => $extension_name]);
	$res = $stmnt->fetch(PDO::FETCH_ASSOC);
	if ($res) {
		if ($res['features2'] > 0 || $res['properties2'] > 0) {
			$links = [];
			if ($res['features2'] > 0) {
				$links[] = "<a href='listfeaturesextensions.php?extension=$extension_name'>features</a>";
			}
			if ($res['properties2'] > 0) {
				$links[] = "<a href='listpropertiesextensions.php?extension=$extension_name'>properties</a>";
			}
			$link_info = implode(' and ', $links);
			$extension_detail .= "<div style='margin-top: 10px;' class='subcaption-level-1'>This extension has additional $link_info</div>";
		}
	}

	// Display when extension was first submitted to the database
	$stmnt = DB::$connection->prepare("SELECT date(min(date)) as date FROM extensions ext where ext.name = :extension");
	$stmnt->execute(['extension' => $extension_name]);
	$res = $stmnt->fetch(PDO::FETCH_ASSOC);
	if ($res['date']) {
		$extension_detail .= "<div style='margin-top: 10px;' class='subcaption-level-2'>Extension was first submitted at ".$res['date']."</div>";
	}
} catch (PDOException $e) {
	echo "<b>Error while fetching data!</b><br>";
} finally {
	DB::disconnect();
}

$caption = "Device coverage for <code>$extension_name</code>";
$minApiVersion = SqlRepository::getMinApiVersion();
if ($minApiVersion) {
	$caption .= " Vulkan $minApiVersion (and up)";
}
?>

<div class='header'>
	<h4 class='headercaption'>
		<?=$caption?>
		<?=$extension_detail ? "<br>$extension_detail" : ""?>
	</h4>
</div>

<center>
	<div class='parentdiv'>
		<div id="chart" style="padding-top: 20px; padding-bottom: 20px;"></div>
		<div class='property-table'>
			<?php
				$total_count = 0;
				foreach ($values as $value) {
					$total_count += $value[$count_key];
				}
				// Counts below a certain threshold will be grouped into a single "others" slice
				$lower_limit = $total_count * 0.0015;
				$others_count = 0;
				$chart_labels = [];
				$chart_series = [];
				$chart_colors = [];
				foreach ($extension_coverage as $i => $coverage) {
					$chart_labels[] = $coverage['platform'];
					$chart_series[] = $coverage['coverage'];
					$chart_colors[] = Chart::platformColors[$i];
				}
			?>
			<table id="values" class="table table-striped table-bordered table-hover reporttable">
				<thead>
					<tr>
						<th>Platform</th>
						<th>Coverage</th>
					</tr>
				</thead>
				<tbody>
				<?php
					foreach ($extension_coverage as $index => $coverage) {
						$color_style = "style='border-left: ".Chart::getColor($index)." 3px solid'";
						$link = "listdevicescoverage.php?extension=$extension_name&platform=".strtolower($coverage['platform']);
						echo "<tr>";
						echo "<td $color_style>".$coverage['platform']."</td>";
						echo "<td><a href='$link'>".$coverage['coverage']."<span style='font-size:10px;'>%</span></a></td>";						
						echo "</tr>";
					}
				?>
				</tbody>
			</table>
		</div>
	</div>
</center>

<script type="text/javascript">

var options = {
	series: [{
	name: 'Device coverage per OS',
	data: [<?= implode(',', $chart_series) ?>]
			}],
			chart: {
				type: 'bar',
				height: 400,
				width: 600,
				toolbar: {
					show: false
				}
			},
			colors: ['<?= implode("','", $chart_colors) ?>'],
			plotOptions: {
				bar: {
					horizontal: false,
					columnWidth: '55%',
					endingShape: 'rounded',
					distributed: true
				},
			},
			dataLabels: {
				enabled: false
			},
			xaxis: {
				categories: ['<?= str_replace('\\n', '<br/>', implode("','", $chart_labels)) ?>'],
			},
			yaxis: {
				max: 100,
				title: {
					text: 'Device coverage (%)'
				}
			},
			fill: {
				opacity: 1
			},
		};

		var chart = new ApexCharts(document.querySelector("#chart"), options);
		chart.render();	
</script>

<?php
PageGenerator::footer();;
?>

</body>

</html>