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

include 'pagegenerator.php';
include './includes/functions.php';
include './includes/constants.php';
include './includes/filterlist.class.php';
include './database/database.class.php';
include './database/sqlrepository.php';

// Supported filter values for this listing
$filters = [
	'platform',
	'submitter',
	'devicename',
	'displayname',
	'feature',
	'limit',
	'extension',
	'extensionname',
	'extensionfeature',
	'extensionproperty',
	'extensionpropertyvalue',
	'core',
	'coreproperty',
	'linearformat',
	'optimalformat',
	// 'bufferformat',
	'memorytype',
	'surfaceformat',
	'surfaceformatcolorspace',
	'surfacepresentmode',
	'surfacetransformmode',
	'surfacecompositealphamode',
	'option',
	'lineartilingformat',
	'optimaltilingformat',
	'bufferformat',
	'featureflagbit',
	'surfaceusageflag',
	'profile',
	'queuefamilyflags'
];
$filter_list = new FilterList($filters);

$platform = "all";
$caption = null;
$pageTitle = null;
$subcaption = null;

// Invert
$inverted = $filter_list->hasFilter('option') && ($filter_list->getFilter('option') == 'not');
// Feature support
if ($filter_list->hasFilter('feature')) {
	$feature = $filter_list->getFilter('feature');
	$info = "<code>$feature</code>";
	$caption = $inverted ? "Listing devices <span style='color:red;'>not</span> supporting for $info" : "Listing first known driver version support for $info";
	$pageTitle = $feature;
}
// Extension feature support
if ($filter_list->hasFilter('extensionname') && $filter_list->hasFilter('extensionfeature')) {
	$ext_name = $filter_list->getFilter('extensionname');
	$ext_feature = $filter_list->getFilter('extensionfeature');
	$info = "<code>$ext_feature</code> for <code>$ext_name </code>";
	$caption = $inverted ? "Listing devices <span style='color:red;'>not</span> supporting $info" : "Listing first known driver version support for $info";
	$pageTitle = $ext_feature;
}
// Extension property support
if ($filter_list->hasFilter('extensionname') && $filter_list->hasFilter('extensionproperty')) {
	$ext_name =  $filter_list->getFilter('extensionname');
	$ext_property =  $filter_list->getFilter('extensionproperty');
	$ext_property_value =  $filter_list->getFilter('extensionpropertyvalue');
	$info = "value <code>$ext_property_value</code> in <code>$ext_property</code> for <code>$ext_name</code>";
	$caption = $inverted ?  "Listing devices <span style='color:red;'>not</span> supporting $info" : "Listing first known driver version support for $info";
	$pageTitle = $ext_property;
}
// Core property support
if ($filter_list->hasFilter('coreproperty')) {
	$property = $filter_list->getFilter('coreproperty');
	$info = "<code>$property</code>";
	$caption = $inverted ?  "Listing devices <span style='color:red;'>not</span> supporting $info" : "Listing first known driver version support for $info";
	$pageTitle = $property;
}
// Image and buffer format flag support
if ($filter_list->hasFilter('lineartilingformat')) {
	$format_feature_flag_bit = $filter_list->getFilter('featureflagbit');
	$format_name = $filter_list->getFilter('lineartilingformat');
	$caption = "Listing first known driver version for <code>$format_feature_flag_bit</code> support on linear tiling format <code>$format_name</code>";
}
if ($filter_list->hasFilter('optimaltilingformat')) {
	$format_feature_flag_bit = $filter_list->getFilter('featureflagbit');
	$format_name = $filter_list->getFilter('optimaltilingformat');
	$caption = "Listing first known driver version for <code>$format_feature_flag_bit</code> support on optimal tiling format <code>$format_name</code>";
}
if ($filter_list->hasFilter('bufferformat')) {
	$format_feature_flag_bit = $filter_list->getFilter('featureflagbit');
	$format_name = $filter_list->getFilter('bufferformat');
	$caption = "Listing first known driver version for <code>$format_feature_flag_bit</code> support on buffer format <code>$format_name</code>";
}
// Memory type
if ($filter_list->hasFilter('memorytype')) {
	$memoryFlags = join(" | ", getMemoryTypeFlags($filter_list->getFilter('memorytype')));
	if ($memoryFlags == "") $memoryFlags = "0";
	$caption = $inverted ?
		"Listing devices <span style='color:red;'>not</span> supporting memory type <code>$memoryFlags</code>"
		:
		"Listing first known driver version support for memory type <code>$memoryFlags</code>";
	$pageTitle = "Memory type $memoryFlags";
}
// Surface format
if ($filter_list->hasFilter('surfaceformat')) {
	$surface_format = $filter_list->getFilter('surfaceformat');
	$caption = $inverted ?
		"Listing devices <span style='color:red;'>not</span> supporting surface format <code>$surface_format</code>"
		:
		"Listing first known driver version support for surface format <code>$surface_format</code>";
	$pageTitle = "Surface format $surface_format";
	// Color space for the surface format
	if ($filter_list->hasFilter('surfaceformatcolorspace')) {
		$surface_format_colorspace = $filter_list->getFilter('surfaceformatcolorspace');
		$caption .= " and color space <code>".getColorSpace($surface_format_colorspace)."</code>";
		$pageTitle .= " color space ".getColorSpace($surface_format_colorspace);
	}
}
// Surface present mode
if ($filter_list->hasFilter('surfacepresentmode')) {
	$surface_present_mode = $filter_list->getFilter('surfacepresentmode');
	$caption = $inverted ?
		"Listing devices <span style='color:red;'>not</span> supporting surface present mode <code>$surface_present_mode</code>"
		:
		"Listing first known driver version support for surface present mode <code>$surface_present_mode</code>";
	$pageTitle = "Surface present mode $surface_present_mode";
}
// Surface usage flag
if ($filter_list->hasFilter('surfaceusageflag')) {
	$surface_usage_flag = $filter_list->getFilter('surfaceusageflag');
	$caption = $inverted ?
		"Listing devices <span style='color:red;'>not</span> supporting surface usage flag <code>$surface_usage_flag</code>"
		:
		"Listing first known driver version support for surface usage flag <code>$surface_usage_flag</code>";
	$pageTitle = "Surface present mode $surface_usage_flag";
}
// Surface transform mode
if ($filter_list->hasFilter('surfacetransformmode')) {
	$surface_transform_mode = $filter_list->getFilter('surfacetransformmode');
	$caption = $inverted ?
		"Listing devices <span style='color:red;'>not</span> supporting surface transform mode <code>$surface_transform_mode</code>"
		:
		"Listing first known driver version support for surface transform mode <code>$surface_transform_mode</code>";
	$pageTitle = "Surface transform mode $surface_transform_mode";
}
// Surface composite alpha mode flag
if ($filter_list->hasFilter('surfacecompositealphamode')) {
	$surface_composite_alpha_mode = $filter_list->getFilter('surfacecompositealphamode');
	$caption = $inverted ?
		"Listing devices <span style='color:red;'>not</span> supporting surface composite alpha mode <code>$surface_composite_alpha_mode</code>"
		:
		"Listing first known driver version support for surface composite alpha mode <code>$surface_composite_alpha_mode</code>";
	$pageTitle = "Surface composite alpha mode $surface_composite_alpha_mode";
}
// Profile 
if ($filter_list->hasFilter('profile')) {
	$profile_name = $filter_list->getFilter('profile');
	$caption = $inverted ? "Listing devices <span style='color:red;'>not</span> supporting profile <code>$profile_name</code>" : "Listing first known driver version support for profile <code>$profile_name</code>";
	$pageTitle = $profile_name;
}
// Queue family flags
if ($filter_list->hasFilter('queuefamilyflags')) {
	$flags = join(" | ", getQueueFlags($filter_list->getFilter('queuefamilyflags')));
	if ($flags == "") {
		$flags = "0 [none]";
	}
	$caption = $inverted ?
		"Listing devices <span style='color:red;'>not</span> supporting queue family flag combination <code>$flags</code>"
		:
		"Listing first known driver version support for queue family flag combination <code>$flags</code>";
	$pageTitle = "Queue family flags $flags";
}
// Submitter
if ($filter_list->hasFilter('submitter')) {
	$submitter = $filter_list->getFilter('submitter');
	$caption .= "<br/>Devices submitted by $submitter";
	$pageTitle = "Devices by $submitter";
}
// Platform
if ($filter_list->hasFilter('platform')) {
	$platform = $filter_list->getFilter('platform');
	$caption .= " on " . PageGenerator::platformInfo($platform);
	if ($pageTitle) {
		$pageTitle .= " on " . PageGenerator::platformInfo($platform);
	}
}
// Extension support
if ($filter_list->hasFilter('extension')) {
	$ext = $filter_list->getFilter('extension');
	$caption = $inverted ? "Listing devices <span style='color:red;'>not</span> supporting <b>$ext</b>" : "Listing first known driver version support for <code>$ext</code>";
	$pageTitle = $ext;
	// Visualize additional extension data that may be of interest in this view
	DB::connect();
	try {
		// Display links to additional device features and or properties, so users can easily access them
		$stmnt = DB::$connection->prepare("SELECT
				(SELECT COUNT(DISTINCT df2.name) FROM devicefeatures2 df2 WHERE (df2.extension = ext.name)) AS features2,
				(SELECT COUNT(DISTINCT dp2.name) FROM deviceproperties2 dp2 WHERE (dp2.extension = ext.name)) AS properties2				
				FROM extensions ext where ext.name = :extension");
		$stmnt->execute(['extension' => $ext]);
		$res = $stmnt->fetch(PDO::FETCH_ASSOC);
		if ($res) {
			if ($res['features2'] > 0 || $res['properties2'] > 0) {
				$links = [];
				if ($res['features2'] > 0) {
					$links[] = "<a href='listfeaturesextensions.php?extension=$ext&platform=$platform'>features</a>";
				}
				if ($res['properties2'] > 0) {
					$links[] = "<a href='listpropertiesextensions.php?extension=$ext&platform=$platform'>properties</a>";
				}
				$linkInfo = implode(' and ', $links);
				$subcaption = "<div style='margin-top: 10px;' class='subcaption-level-1'>This extension has additional $linkInfo</div>";
			}
		}
		// Display the date at which this extension has bee first submitted to the database
		$dateColumn = 'date';
		if ($platform !== 'all') {
			$dateColumn = 'date'.strtolower($platform);
		}		
		$stmnt = DB::$connection->prepare("SELECT date(min($dateColumn)) as date FROM extensions ext where ext.name = :extension");
		$stmnt->execute(['extension' => $ext]);
		$res = $stmnt->fetch(PDO::FETCH_ASSOC);
		if ($res['date']) {
			$subcaption .= "<div style='margin-top: 10px;' class='subcaption-level-2'>Extension was first submitted at ".$res['date']."</div>";
		}
	} catch (Throwable $e) {
		echo $e->getMessage();
	}
	DB::disconnect();
}
PageGenerator::header($pageTitle);
?>

<div class="centered">
	<div class='header'>
		<h4>
			<?php
			echo $caption ? $caption : "Listing available devices";
			echo $subcaption ? "<br>$subcaption" : "";
			?>
		</h4>
	</div>
<?php PageGenerator::globalFilterText(); ?>	

	<!-- Compare block (only visible when at least one report is selected) -->
	<div id="compare-div" class="well well-sm" role="alert" style="text-align: center; display: none;">
		<div class="compare-header">Selected devices for compare:</div>
		<span id="compare-info"></span>
		<div class="compare-footer">
			<Button onClick="clearCompare()"><span class='glyphicon glyphicon-button glyphicon-erase'></span> Clear</Button>
			<Button onClick="compare()"><span class='glyphicon glyphicon-button glyphicon-duplicate'></span> Compare</Button>
		</div>
	</div>

	<?php PageGenerator::platformNavigation('listdevicescoverage.php', $platform, true, $filter_list->filters); ?>

	<div class='tablediv tab-content' style='display: inline-flex;'>

		<div id='devices_div' class='tab-pane fade in active'>
			<table id='devices' class='table table-striped table-bordered table-hover responsive' style='width:auto'>
				<thead>
					<tr>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
					</tr>
					<tr>
						<th>Device</th>
						<th>Vendor</th>
						<th>Driver <span title="First known driver version supporting this extension/feature" class="hint">[?]</span></th>
						<th>Date</th>
						<th>Compare</th>
					</tr>
				</thead>
			</table>
			<div id="errordiv" style="color:#D8000C;"></div>
		</div>
</div>

<script src="js/devicecompare.js"></script>

<script>
	$(document).on("keypress", "form", function(event) {
		return event.keyCode != 13;
	});

	$(document).ready(function() {

		$.get(comparerUrl, null, function (response) {
			displayCompare(response);
		});		

		var table = $('#devices').DataTable({
			"processing": true,
			"serverSide": true,
			"paging": true,
			"searching": true,
			"lengthChange": true,
			"lengthMenu": [
				[10, 25, 50, 100, -1],
				[10, 25, 50, 100, "All"]
			],
			"dom": 'lrtip',
			"pageLength": 50,
			"order": [
				[0, 'asc']
			],
			"columnDefs": [{
				"orderable": false,
				"targets": [4]
			}],
			"ajax": {
				url: "api/internal/devices.php?platform=<?php echo $platform ?>&minversion=true",
				data: {
					"filter": {
						'extension': 					'<?= $filter_list->getFilter('extension') ?>',
						'feature': 						'<?= $filter_list->getFilter('feature') ?>',
						'submitter': 					'<?= $filter_list->getFilter('submitter') ?>',
						'linearformat': 				'<?= $filter_list->getFilter('linearformat') ?>',
						'optimalformat': 				'<?= $filter_list->getFilter('optimalformat') ?>',
						'bufferformat': 				'<?= $filter_list->getFilter('bufferformat') ?>',
						'devicelimit': 					'<?= $filter_list->getFilter('limit') ?>',
						'memorytype': 					'<?= $filter_list->getFilter('memorytype') ?>',
						'option': 						'<?= $filter_list->getFilter('option') ?>',
						'surfaceformat': 				'<?= $filter_list->getFilter('surfaceformat') ?>',
						'surfaceformatcolorspace':		'<?= $filter_list->getFilter('surfaceformatcolorspace') ?>',
						'surfacepresentmode': 			'<?= $filter_list->getFilter('surfacepresentmode') ?>',
						'surfacetransformmode': 		'<?= $filter_list->getFilter('surfacetransformmode') ?>',
						'surfacecompositealphamode':    '<?= $filter_list->getFilter('surfacecompositealphamode') ?>',
						'devicename': 					'<?= $filter_list->getFilter('devicename') ?>',
						'displayname': 					'<?= $filter_list->getFilter('displayname') ?>',
						'extensionfeature_name': 		'<?= $filter_list->getFilter('extensionname') ?>',
						'extensionfeature_feature': 	'<?= $filter_list->getFilter('extensionfeature') ?>',
						'extensionproperty_name': 		'<?= $filter_list->getFilter('extensionname') ?>',
						'extensionproperty_property': 	'<?= $filter_list->getFilter('extensionproperty') ?>',
						'extensionproperty_value': 		'<?= $filter_list->getFilter('extensionpropertyvalue') ?>',
						'coreproperty': 				'<?= $filter_list->getFilter('coreproperty') ?>',
						'core': 						'<?= $filter_list->getFilter('core') ?>',
						'lineartilingformat':			'<?= $filter_list->getFilter('lineartilingformat') ?>',
						'optimaltilingformat':			'<?= $filter_list->getFilter('optimaltilingformat') ?>',
						'bufferformat':					'<?= $filter_list->getFilter('bufferformat') ?>',
						'featureflagbit':				'<?= $filter_list->getFilter('featureflagbit') ?>',
						'surfaceusageflag':				'<?= $filter_list->getFilter('surfaceusageflag') ?>',
						'profile':						'<?= $filter_list->getFilter('profile') ?>',
						'queuefamilyflags':				'<?= $filter_list->getFilter('queuefamilyflags') ?>',
					}
				},
				error: function(xhr, error, thrown) {
					$('#errordiv').html('Could not fetch data (' + error + ')');
					$('#devices_processing').hide();
				}
			},
			"columns": [{
					data: 'device'
				},
				{
					data: 'vendor'
				},
				{
					data: 'driver'
				},
				{
					data: 'submissiondate'
				},
				{
					data: 'compare'
				}
			],
			// Pass order by column information to server side script
			fnServerParams: function(data) {
				data['order'].forEach(function(items, index) {
					data['order'][index]['column'] = data['columns'][items.column]['data'];
				});
			},
		});

		yadcf.init(table, [{
				column_number: 0,
				filter_type: "text",
				filter_delay: 500,
				style_class: "filter-240"
			},
			{
				column_number: 1,
				filter_type: "text",
				filter_delay: 500
			},
			{
				column_number: 2,
				filter_type: "text",
				filter_delay: 500
			},
		], {
			filters_tr_index: 0
		});

	});
</script>

<?php PageGenerator::footer(); ?>

</body>

</html>