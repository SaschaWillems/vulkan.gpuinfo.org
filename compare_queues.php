<?php
	/*
		*
		* Vulkan hardware capability database server implementation
		*
		* Copyright (C) 2016-2020 by Sascha Willems (www.saschawillems.de)
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

	$report_compare->beginTable("comparequeuefamilies");
	$report_compare->insertTableHeader("Queue family", true);
	$report_compare->insertDeviceInformation("Device");

	$compare_queue_families = $report_compare->fetchQueueFamilies();

	$max_queue_family_count = 0;
	foreach ($compare_queue_families->data as $report_queue_families) {
		if (count($report_queue_families) > $max_queue_family_count) {
			$max_queue_family_count = count($report_queue_families);
		}
	}

	// Per report queue counts
	echo "<tr><td class='subkey'>Queue count</td><td>Device</td>";
	for ($i = 0; $i < $report_compare->report_count; $i++) {
		echo "<td>".count($compare_queue_families->data[$i])."</td>";
	}
	echo "</tr>";

	for ($i = 0; $i < $max_queue_family_count; $i++) {
		$queue_family = "Queue family $i";		
		// Queue counts
		echo "<tr><td class='subkey'>Count</td><td>$queue_family</td>";
		foreach ($compare_queue_families->data as $report_queue_families) {
			if (count($report_queue_families) > $i) {
				echo "<td>".$report_queue_families[$i]->count."</td>";
			} else {
				echo "<td><font color=#BABABA>n/a</font></td>";
			}
		}
		echo "</tr>";
		// Flags
		echo "<tr><td class='subkey'>Flags</td><td>$queue_family</td>";
		foreach ($compare_queue_families->data as $report_queue_families) {
			if (count($report_queue_families) > $i) {
				echo "<td>";
				$flags = getQueueFlags($report_queue_families[$i]->flags);
				if (sizeof($flags) > 0) {
					foreach ($flags as $flag) {
						echo $flag."<br>";
					}
				} else {
					echo "none";
				}
				echo "</td>";
			} else {
				echo "<td><font color=#BABABA>n/a</font></td>";
			}
		}
		echo "</tr>";


		/*
		// Counts
		echo "<tr><td class='subkey'>queueCount</td><td>$queue_family</td>";
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
		echo "<tr><td class='subkey'>Flags</td><td>$queue_family</td>";
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
			echo "<td>$queue_family</td>";

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
		*/
	}

	/*

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

	// Queue counts
	echo "<tr><td class='subkey'>Queue count</td><td>Device</td>";
	for ($i = 0; $i < $report_compare->report_count; $i++) {
		echo "<td>".$queueCounts[$i]."</td>";
	}
	echo "</tr>";

	for ($i = 0; $i < $maxQueueCount; ++$i) {
		$queue_family = "Queue family $i";
		// echo "<tr><td class='caption' colspan=".$colspan.">Queue ".$i."</td></tr>";

		// Counts
		echo "<tr><td class='subkey'>queueCount</td><td>$queue_family</td>";
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
		echo "<tr><td class='subkey'>Flags</td><td>$queue_family</td>";
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
			echo "<td>$queue_family</td>";

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

	*/

	$report_compare->endTable();
?>