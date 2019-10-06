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

	echo "<table id='queue-families' width='100%' class='table table-striped table-bordered'>";
	ReportCompare::insertTableHeader("Queue family", $deviceinfo_data, count($reportids));
	ReportCompare::insertDeviceColumns($deviceinfo_captions, $deviceinfo_data, count($reportids));

	// Get queues for each selected report into an array
	$qCount = array();
	$qFlags = array();
	$qTimestampBits = array();
	$qTransferW = array();
	$qTransferH = array();
	$qTransferD = array();
	$queueCounts = array();
	$maxQueueCount = 0;

	$reportIndex = 0;
	foreach ($reportids as $repid) {
		$str = "$repid";
		try {
			$stmnt = DB::$connection->prepare("SELECT count, flags, timestampValidBits, `minImageTransferGranularity.width`, `minImageTransferGranularity.height`, `minImageTransferGranularity.depth` from devicequeues where reportid = :reportid");
			$stmnt->execute(["reportid" => $repid]);
		} catch (PDOException $e) {
			die("Could not fetch device queue!");
		}			
		$subarray = array();
		$queueCounts[$reportIndex] = 0;
		while ($row =  $stmnt->fetch(PDO::FETCH_NUM)) {
			$qCount[$reportIndex][] = $row[0];
			$qFlags[$reportIndex][] = $row[1];
			$qTimestampBits[$reportIndex][] = $row[2];
			$qTransferW[$reportIndex][] = $row[3];
			$qTransferH[$reportIndex][] = $row[4];
			$qTransferD[$reportIndex][] = $row[5];
			$queueCounts[$reportIndex]++;
		}
		$reportIndex++;
	}

	// Get max number
	$reportIndex = 0;
	foreach ($reportids as $repid){
		if ($queueCounts[$reportIndex] > $maxQueueCount) {
			$maxQueueCount = $queueCounts[$reportIndex];
		}
		$reportIndex++;
	}

	// Generate table
	$colspan = count($reportids) + 1;

	reportCompareDeviceColumns($deviceinfo_captions, $deviceinfo_data, sizeof($reportids));

	// Memory type counts
	echo "<tr class='firstrow'><td class='firstrow'>Queue count</td>";
	for ($i = 0, $arrsize = sizeof($extarray); $i < $arrsize; ++$i) {
		echo "<td>".$queueCounts[$i]."</td>";
	}
	echo "</tr>";

	for ($i = 0; $i < $maxQueueCount; ++$i) {
		echo "<tr><td class='caption' colspan=".$colspan.">Queue ".$i."</td></tr>";
		// Count
		echo "<tr><td class='subkey'>queueCount</td>";
		$index = 0;
		foreach ($reportids as $repid) {
			if ($i < $queueCounts[$index]) {
				echo "<td>".$qCount[$index][$i]."</td>";
			} else {
				echo "<td><font color=#BABABA>n/a</font></td>";
			}
			$index++;
		}
		echo "</tr>";
 		// Flags
		echo "<tr><td class='subkey'>Flags</td>";
		$index = 0;
		foreach ($reportids as $repid) {
			echo "<td>";
			if ($i < $queueCounts[$index]) {
				$flags = getQueueFlags($qFlags[$index][$i]);
				if (sizeof($flags) > 0) {
					foreach ($flags as $flag) {
						echo $flag."<br>";
					}
				} else {
					echo "none";
				}
			} else {
				echo "<font color=#BABABA>n/a</font>";
			}

			echo "</td>";
			$index++;
		}
		echo "</tr>";

		for ($j = 0; $j < 4; ++$j) {
			$arr = array();
			switch ($j)
			{
				case 0:
					echo "<tr><td class='subkey'>timestampValidBits</td>";
					$arr = $qTimestampBits;
					break;
				case 1:
					echo "<tr><td class='subkey'>minImageTransferGranularity.width</td>";
					$arr = $qTransferW;
					break;
				case 2:
					echo "<tr><td class='subkey'>minImageTransferGranularity.height</td>";
					$arr = $qTransferH;
					break;
				case 3:
					echo "<tr><td class='subkey'>minImageTransferGranularity.depth</td>";
					$arr = $qTransferD;
					break;
			}

			$index = 0;
			foreach ($reportids as $repid) {
				if ($i < $queueCounts[$index]) {
					echo "<td>".$arr[$index][$i]."</td>";
				} else {
					echo "<td><font color=#BABABA>n/a</font></td>";
				}
				$index++;
			}
			echo "</tr>";
		}

		}
	echo "</tbody></table>";
?>