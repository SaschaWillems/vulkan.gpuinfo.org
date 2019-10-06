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
	
	ReportCompare::insertTableHeader("Ext. Property", $deviceinfo_data, count($reportids));
	ReportCompare::insertDeviceColumns($deviceinfo_captions, $deviceinfo_data, count($reportids));

	// Gather all extended properties for reports to compare
	$extended_properties = null;
	try {
		$stmnt = DB::$connection->prepare("SELECT distinct extension, name from deviceproperties2 where reportid in ($repids)");
		$stmnt->execute();
		$extended_properties = $stmnt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
	} catch (Exception $e) {
		die('Could not fetch extended properties!');
		DB::disconnect();
	}

	// Get extended properties for each selected report into an array 
	$extended_properties_reports = null;	
	foreach ($reportids as $reportid) {
		try {
			$stmnt = DB::$connection->prepare("SELECT extension, name, value from deviceproperties2 where reportid = :reportid");
			$stmnt->execute(['reportid' => $reportid]);
			$extended_properties_reports[] = $stmnt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			die("Could not fetch device extended properties for single report!");
		}	
	}
	
	// Generate table
	foreach ($extended_properties as $extension => $properties) {
		echo "<tr><td class='group' style='border-right:0px'>$extension</td>\n";
		foreach ($reportids as $repid) {
			echo "<td class='group' style='border-right:0px'></td>";
		}  
		echo "</tr>"; 
		// Feature support
		foreach ($properties as $feature) {
			$html = '';
			$diff = false;
			$last_val = null;			
			foreach ($extended_properties_reports as $index => $extended_properties_report) {
				$ext_present = array_key_exists($extension, $extended_properties_report);
				if ($ext_present) {
					$ext = $extended_properties_report[$extension];
					$value = null;
					foreach ($ext as $ext_f) {
						if ($ext_f['name'] == $feature['name']) {
							$value = $ext_f['value'];
							if (is_string($value) && substr($value, 0, 2) == "a:") {
								$arr = unserialize($value);
								$value = "[".implode(',', $arr)."]";
							}							
						}
					}
					if ($index > 0 && $value != $last_val) {
						$diff = true;
					}
					$last_val = $value;
					if (in_array($value, ["true", "false"])) {
						$html .= "<td><span class=".($value == "true" ? "supported" : "unsupported").">$value</span></td>";
					} else {
						$html .= "<td>$value</td>";
					}
				} else {
					$html .= "<td class='na'>n.a.</td>";
					$diff = true;
				}
			}
			$html = "<tr class='".($diff ? "diff" : "same")."'><td class='firstrow' style='padding-left:25px'>".$feature['name']."</td>".$html."</tr>";
			echo $html;
		}
	}	  
?>