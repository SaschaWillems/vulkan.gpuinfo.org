<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2025 by Sascha Willems (www.saschawillems.de)
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
require './includes/functions.php';
require './includes/constants.php';
require './reportdisplay/reportdisplay.class.php';

$reportID = $_GET['id'];
if (!$reportID) {
	PageGenerator::errorMessage("<strong>Warning!</strong><br> No report ID set to display!");
}

$report = new Report($reportID);
if (!$report->exists()) {
	PageGenerator::errorMessage("
		<strong>This is not the <strike>droid</strike> report you are looking for!</strong><br><br>
		Could not find report with ID <?php echo $reportID; ?> in database.<br>
		It may have been removed due to faulty data."
	);
}

// Try to load report from cache first
$cachedFileName = "reportcache/report_$reportID.inc";
if (file_exists($cachedFileName)) {
	$loadFromCache = true;
	// Check if report has been updated since it was cached
	$reportDate = null;
	try {
		DB::connect();
		$reportDate = DB::getReportDate($reportID);
	} finally {
		DB::disconnect();
	}
	if ($reportDate) {
		$cachedFileDate = filemtime($cachedFileName);
		$dbDate = new DateTime($reportDate);
		$fileDate = new DateTime('@'.$cachedFileDate);
		if ($fileDate < $dbDate) {
			logToFile("Cached report $reportID is outdated");
			$loadFromCache = false;
		}
	}
	if ($loadFromCache) {
		$start = microtime(true);
		$report->fetchDescription();
		$cachedPage = file_get_contents($cachedFileName);
		PageGenerator::header($report->info->device_description);
		echo $cachedPage;
		PageGenerator::footer();
		$delta = (microtime(true) - $start) * 1000;
		logToFile("Report $reportID loaded from cache in $delta ms");
		exit;
	}
}

$start = microtime(true);

$report->fetchData();
PageGenerator::header($report->info->device_description);

ob_start();

// Counters
try {
	DB::connect();
	$extcount = DB::getCount("SELECT count(*) from deviceextensions where reportid = :reportid", [':reportid' => $reportID]);
	$formatcount = DB::getCount("SELECT count(*) from deviceformats where reportid = :reportid and (lineartilingfeatures > 0 or optimaltilingfeatures > 0 or bufferfeatures > 0)", [':reportid' => $reportID]);
	$queuecount = DB::getCount("SELECT count(*) from devicequeues where reportid = :reportid", [':reportid' => $reportID]);
	$memtypecount = DB::getCount("SELECT count(*) from devicememorytypes where reportid = :reportid", [':reportid' => $reportID]);
	$memheapcount = DB::getCount("SELECT count(*) from devicememoryheaps where reportid = :reportid", ["reportid" => $reportID]);
	$surfaceformatscount =  DB::getCount("SELECT count(*) from devicesurfaceformats where reportid = :reportid", [':reportid' => $reportID]);
	$surfacepresentmodescount =  DB::getCount("SELECT count(*) from devicesurfacemodes where reportid = :reportid", [':reportid' => $reportID]);
	$profilecount =  DB::getCount("SELECT count(*) from deviceprofiles where reportid = :reportid", [':reportid' => $reportID]);
} catch (PDOException $e) {
	DB::disconnect();
	die("<b>Error while fetching report data!</b><br>");
}
echo "<center>";

// Header
$header = "Device report for " . $report->info->device_description;
if ($report->info->platform !== null) {
	$header .= " on <img src='images/" . strtolower($report->info->platform) . "logo.png' height='14px' style='padding-right:5px'/>" . PageGenerator::platformDisplayName($report->info->platform);
}
echo "<div class='header'>";
echo "<h4>$header</h4>";
echo "</div>";

?>
<div>
	<ul class='nav nav-tabs nav-report'>
		<li class='active'><a data-toggle='tab' href='#device'>Device</a></li>
		<li><a data-toggle='tab' href='#properties'>Properties</a></li>
		<li><a data-toggle='tab' href='#features'>Features</a></li>
		<li><a data-toggle='tab' href='#extensions'>Extensions <span class='badge'><?php echo $extcount ?></span></a></li>
		<li><a data-toggle='tab' href='#formats'>Formats <span class='badge'><?php echo $formatcount ?></span></a></a></li>
		<li><a data-toggle='tab' href='#queuefamilies'>Queue families <span class='badge'><?php echo $queuecount ?></span></a></li>
		<li><a data-toggle='tab' href='#memory'>Memory <span class='badge'><?php echo $memtypecount ?></span></a></a></li>
		<?php if ($report->flags->has_surface_caps) {
			echo "<li><a data-toggle='tab' href='#surface'>Surface</a></a></li>";
		} ?>
		<?php if ($report->flags->has_instance_data) {
			echo "<li><a data-toggle='tab' href='#instance'>Instance</a></li>";
		} ?>
		<?php if ($report->flags->has_profiles) {
			echo "<li><a data-toggle='tab' href='#profiles'>Profiles</a></li>";
		} ?>
	</ul>
</div>

<div class='tablediv tab-content' style='width:75%;'>

	<?php
	$views = [
		'device',
		'properties',
		'features',
		'extensions',
		'formats',
		'queuefamilies',
		'memory',
	];
	if ($report->flags->has_surface_caps) {
		$views[] = 'surface';
	}
	if ($report->flags->has_instance_data) {
		$views[] = 'instance';
	}
	if ($report->flags->has_profiles) {
		$views[] = 'profiles';
	}
	foreach ($views as $index => $view) {
		echo "<div id='$view' class='tab-pane fade ".($index == 0 ? "in active" : null)." reportdiv'>";
			include "reportdisplay/$view.php";
		echo "</div>";
	}	

	if ($report->flags->has_update_history) {
		include 'reportdisplay/history.php';
	}
	?>

	<script type="text/javascript" src="js/reportdisplay.js"></script>
</div>

<?php
logToFile("No cache found for $reportID, cached report will be generated");
$pageContent = ob_get_contents();
// Store in cache
file_put_contents($cachedFileName, $pageContent);
ob_end_clean();
// Display
echo $pageContent;
?>

<?php PageGenerator::footer(); ?>

</center>
</body>
</html>

<?php
$delta = (microtime(true) - $start) * 1000;
logToFile("Report $reportID not present in cache, cache generated and displayed in $delta ms");
?>

