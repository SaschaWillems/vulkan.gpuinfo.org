<?php
	/*
		*
		* Vulkan hardware capability database server implementation
		*
		* Copyright (C) 2016-2018 by Sascha Willems (www.saschawillems.de)
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
?>

	<div>
	<ul class='nav nav-tabs'>
		<li class='active'><a data-toggle='tab' href='#surface-tabs-1'>Surface properties</a></li>
		<li><a data-toggle='tab' href='#surface-tabs-2'>Surface formats</a></li>
	<!-- <li><a data-toggle='tab' href='#surface-tabs-3'>Present modes</a></li> -->
	</ul>
	</div>

	<div class='tab-content'>

<?php	
	/* 
		Surface properties
	*/
	$surfaceProperties = array();

	echo "<div id='surface-tabs-1' class='tab-pane fade in active reportdiv'>";

	$rowCount = 0;
	try {
		$stmnt = DB::$connection->prepare("SELECT count(*) from devicesurfacecapabilities WHERE reportid in (".implode(',', $reportids).")");
		$stmnt->execute();
		$rowCount = $stmnt->rowCount();
	} catch (PDOException $e) {
		die("Could not fetch device surface!");
	}		

	if ($rowCount > 0) {
		$reportIndex = 0;

		echo "<table id='surface-caps' width='100%' class='table table-striped table-bordered'>";
		ReportCompare::insertTableHeader("Surface property", $deviceinfo_data, count($reportids));
		ReportCompare::insertDeviceColumns($deviceinfo_captions, $deviceinfo_data, count($reportids));		

		$props = null;

		try {
			$stmnt = DB::$connection->prepare("SELECT *from devicesurfacecapabilities WHERE reportid in (".implode(',', $reportids).")");
			$stmnt->execute();
			$rowCount = $stmnt->rowCount();
		} catch (PDOException $e) {
			die("Could not fetch device surface!");
		}		
	
		$idx = 0;
		while ($row = $stmnt->fetch(PDO::FETCH_ASSOC)) {
			foreach($row as $key => $value) {
				if ($key == "reportid") {
					continue;
				}
				$surfaceProperties[$row["reportid"]][$key] = $value;
				if ($idx == 0) {
					$props[] = $key;
				}
			}
			$idx++;
		}

		foreach ($props as $prop) {
			echo "<tr><td>".$prop."</td>";
			foreach ($reportids as $repid) {
				echo "<td>";
				if ($surfaceProperties[$repid] == null) {
					echo "<span class='inactive'>n/a</span>";
				} else {
					$value = $surfaceProperties[$repid][$prop];
					if ($prop == "supportedUsageFlags") {
						listFlags(getImageUsageFlags($value));
						continue;
					}
					if ($prop == "supportedTransforms") {
						listFlags(getSurfaceTransformFlags($value));
						continue;
					}
					if ($prop == "supportedCompositeAlpha") {
						listFlags(getCompositeAlphaFlags($value));
						continue;
					}	
					echo $value;
				}
				echo "</td>";
			}
			echo "</tr>";
		}	

		echo "</tbody></table>";
	} else {
		echo "<i>No data</i>";
	}

	echo "</div>";


	/* 
		Surface formats
	*/
	$surfaceFormats = array(); 

	echo "<div id='surface-tabs-2' class='tab-pane fade in reportdiv'>";

	$rowCount = 0;
	try {
		$stmnt = DB::$connection->prepare("SELECT *from devicesurfaceformats WHERE reportid in (".implode(',', $reportids).")");
		$stmnt->execute();
		$rowCount = $stmnt->rowCount();
	} catch (PDOException $e) {
		die("Could not fetch device surface formats!");
	}		
	if ($rowCount > 0 ) {
		$reportIndex = 0;

		echo "<table id='surface-formats' width='100%' class='table table-striped table-bordered'>";
		ReportCompare::insertTableHeader("Surface format", $deviceinfo_data, count($reportids));
		ReportCompare::insertDeviceColumns($deviceinfo_captions, $deviceinfo_data, count($reportids));		
	
		try {
			$stmnt = DB::$connection->prepare("SELECT dsf.reportid AS reportid, vf.name as name FROM devicesurfaceformats dsf JOIN VkFormat vf ON dsf.format = vf.value WHERE reportid IN (".implode(',', $reportids).")");
			$stmnt->execute();
			$rowCount = $stmnt->rowCount();
		} catch (PDOException $e) {
			die("Could not fetch device surface formats!");
		}
		$idx = 0;
		while ($row = $stmnt->fetch(PDO::FETCH_ASSOC)) {
			foreach($row as $key => $value) {
				if ($key == "reportid") {
					continue;
				}
				$formats[$row["name"]][$row["reportid"]] = true;
			}
			$idx++;
		}

		foreach ($formats as $key => $format) {
			echo "<tr><td>".$key."</td>";
			foreach ($reportids as $repid) {
				if (isset($format[$repid])) {
					echo "<td class='supported'>true</td>";
				} else {
					echo "<td class='unsupported'>false</td>";
				}
			}
			echo "</tr>";
		}	

		echo "</tbody></table>";
	} else {
		echo "<i>No data</i>";
	}

	echo "</div>";
	
	echo "</div>";
?>