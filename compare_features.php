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

	function insertCoreFeatures($report_compare, $version) {
		// @todo: Move data fetching to report compare class
		$compare_features = $report_compare->fetchFeatures($version);
	
		$report_compare->beginTab('features_core_'.str_replace('.', '',$version), $version == '1.0');
		$report_compare->beginTable("compare_core_features");
		$report_compare->insertTableHeader("Feature", true);
		$report_compare->insertDeviceInformation("Device");

		for ($i = 0; $i < $compare_features->count; $i++) { 	  
			// Check if row contains differing values
			$differing_values = false;
			for ($j = 1; $j < count($compare_features->data); $j++) {
				if ($compare_features->data[$j][$i] !== $compare_features->data[0][$i]) {
					$differing_values = true;
					break;
				}
			}
			
			$row_class = "";
			if (!$report_compare->isHeaderColumn($compare_features->captions[$i])) {
				$row_class = $differing_values ? "" : "class='sameCaps'";
			};
			echo "<tr $row_class>";
			echo "<td class='subkey'>".($differing_values ? $report_compare->getDiffIcon() : "").$compare_features->captions[$i] ."</td>";
			echo "<td>Feature</td>";		
			for ($j = 0; $j < count($compare_features->data); $j++) {	 
				echo "<td>".displayBool($compare_features->data[$j][$i])."</td>";
			}
			echo "</tr>";
		}		

		$report_compare->endTable();
		$report_compare->endTab();
	}

	function insertExtensionFeatures($report_compare) {
		// @todo: Move data fetching to report compare classe

		// Gather all extended features for reports to compare
		$extended_features = null;
		try {
			$stmnt = DB::$connection->prepare("SELECT distinct extension, name from devicefeatures2 where reportid in (".implode(",", $report_compare->report_ids).")");
			$stmnt->execute();
			$extended_features = $stmnt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
		} catch (Exception $e) {
			die('Could not fetch extended features!');
			DB::disconnect();
		}

		// Get extended features for each selected report into an array 
		$extended_features_reports = null;	
		foreach ($report_compare->report_ids as $reportid) {
			try {
				$stmnt = DB::$connection->prepare("SELECT extension, name, supported from devicefeatures2 where reportid = :reportid");
				$stmnt->execute(['reportid' => $reportid]);
				$extended_features_reports[] = $stmnt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
			} catch (PDOException $e) {
				die("Could not fetch device extended features for single report!");
			}	
		}
		
		$report_compare->beginTab('features_extensions');
		$report_compare->beginTable("compare_extended_features");
		$report_compare->insertTableHeader("Feature", true);
		$report_compare->insertDeviceInformation("Device");

		foreach ($extended_features as $extension => $features) {
			foreach ($features as $feature) {
				$html = '';
				$diff = false;
				$last_val = null;
				foreach ($extended_features_reports as $index => $extended_features_report) {
					$ext_present = array_key_exists($extension, $extended_features_report);
					if ($ext_present) {
						$supported = false;
						if (array_key_exists($extension, $extended_features_report)) {
							foreach ($extended_features_report[$extension]as $ext_report_f) {
								if ($ext_report_f['name'] == $feature['name']) {
									$supported = $ext_report_f['supported'] == 1;
								}
							}
						}
						if ($index > 0 && $supported != $last_val) {
							$diff = true;
						}
						$last_val = $supported;
						$html .= "<td><span class=".($supported ? "supported" : "unsupported").">".($supported ? "true" : "false")."</span></td>";
					} else {
						$html .= "<td class='na'>n.a.</td>";
						$diff = true;
					}
				}
				$html = "<tr class='".($diff ? "diff" : "same")."'><td class='subkey'>".($diff ? $report_compare->getDiffIcon() : "").$feature['name']."</td><td>".$extension."</td>".$html."</tr>";
				echo $html;
			}
		}		

		$report_compare->endTable();
		$report_compare->endTab();
	}	

	$display_tabs = ($report_compare->flags->has_vulkan_1_1_features || $report_compare->has_vulkan_1_2_features || $report_compare->flags->has_extended_features);
	if ($display_tabs) {
		echo "<div>";
		echo "	<ul class='nav nav-tabs nav-level1'>";		
		echo "		<li class='active'><a data-toggle='tab' href='#features_core_10'>Core 1.0</a></li>";
		if ($report_compare->flags->has_vulkan_1_1_features) {
			echo "<li><a data-toggle='tab' href='#features_core_11'>Core 1.1</a></li>";
		}
		if ($report_compare->flags->has_vulkan_1_2_features) {
			echo "<li><a data-toggle='tab' href='#features_core_12'>Core 1.2</a></li>";
		}
		if ($report_compare->flags->has_extended_features) {
			echo "<li><a data-toggle='tab' href='#features_extensions'>Extension features</a></li>";
		}
		echo "	</ul>";
		echo "</div>";
		echo "<div class='tab-content'>";
	}
		
	insertCoreFeatures($report_compare, '1.0');
	if ($report_compare->flags->has_vulkan_1_1_features) {
		insertCoreFeatures($report_compare, '1.1');
	}
	if ($report_compare->flags->has_vulkan_1_2_features) {
		insertCoreFeatures($report_compare, '1.2');
	}

	if ($report_compare->flags->has_extended_features) {
		insertExtensionFeatures($report_compare);
	}
?>
	</div>