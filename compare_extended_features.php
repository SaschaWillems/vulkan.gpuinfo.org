
<?php
	/* 		
		*
		* Vulkan hardware capability database server implementation
		*	
		* Copyright (C) by Sascha Willems (www.saschawillems.de)
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
	
	// Table header
	echo "<thead><tr><td class='caption'></td>";
	foreach ($reportids as $reportid) {
		echo "<td class='caption'>Report $reportid</td>";
	}
	echo "</tr></thead><tbody>";
	reportCompareDeviceColumns($deviceinfo_captions, $deviceinfo_data, sizeof($reportids));

	// Gather all extended features for reports to compare
	$extended_features = null;
	try {
		$stmnt = DB::$connection->prepare("SELECT distinct extension, name from devicefeatures2 where reportid in ($repids)");
		$stmnt->execute();
		$extended_features = $stmnt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
	} catch (Exception $e) {
		die('Could not fetch extended features!');
		DB::disconnect();
	}

	// Get extended features for each selected report into an array 
	$extended_features_reports = null;	
	foreach ($reportids as $reportid) {
		try {
			$stmnt = DB::$connection->prepare("SELECT extension, name, supported from devicefeatures2 where reportid = :reportid");
			$stmnt->execute(['reportid' => $reportid]);
			$extended_features_reports[] = $stmnt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			die("Could not fetch device extended features for single report!");
		}	
	}
	
	// Generate table
	foreach ($extended_features as $extension => $features) {
		echo "<tr class='same'><td class='group' style='border-right:0px'>$extension</td>\n";
		foreach ($reportids as $repid) {
			echo "<td class='group' style='border-right:0px'></td>";
		}  
		echo "</tr>"; 
		// Feature support
		foreach ($features as $feature) {
			echo "<tr class='$className'><td class='firstrow' style='padding-left:25px'>".$feature['name']."</td>\n";
			$index = 0;			
			foreach ($extended_features_reports as $extended_features_report) {
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
					echo "<td><span class=".($supported ? "supported" : "unsupported").">".($supported ? "true" : "false")."</span></td>";
				} else {
					echo "<td class='na'>n.a.</td>";
				}
				$index++;
			}
			echo "</tr>"; 
		}
	}	  
?>