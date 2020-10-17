<?php
	/* 		
		*
		* Vulkan hardware capability database server implementation
		*	
		* Copyright (C) 2016~2018 by Sascha Willems (www.saschawillems.de)
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
	
	// @todo: Split into tabs for core and extensions

?>
	<table id='deviceproperties' class='table table-striped table-bordered table-hover responsive' style='width:100%;'>
		<thead>
			<tr>
				<td class='caption'>Property</td>
				<td class='caption'>Value</td>
				<td class='caption'></td>
			</tr>
		</thead>
	<tbody>
<?php
	
	// Device
	$sql = "SELECT 
		p.residencyAlignedMipSize,
		p.residencyNonResidentStrict, 
		p.residencyStandard2DBlockShape, 
		p.residencyStandard2DMultisampleBlockShape, 
		p.residencyStandard3DBlockShape,
		p.`subgroupProperties.subgroupSize`,
		p.`subgroupProperties.supportedStages`,
		p.`subgroupProperties.supportedOperations`,
		p.`subgroupProperties.quadOperationsInAllStages`
	from reports r
	left join
	deviceproperties p on (p.reportid = r.id)
	where r.id = :reportid";

	try {
		$stmnt = DB::$connection->prepare($sql);
		$stmnt->execute(array(":reportid" => $reportID));

		while($row = $stmnt->fetch(PDO::FETCH_NUM)) {
			for($i = 0; $i < count($row); $i++) {
				if ($row[$i] == "") { continue; }
				$meta = $stmnt->getColumnMeta($i);
				$fname = $meta["name"];
				$value = $row[$i];
				if (strpos($fname, 'residency') !== false) {
					$class = ($value == 1) ? "supported" : "unsupported";
					$support = ($value == 1) ? "true" : "false";
					$value = "<span class='".$class."'>".$support."</span>";
				}				
				if (strpos($fname, 'subgroupProperties') !== false) {
					// $fname = str_replace('subgroupProperties.', '', $fname);
					if (strcasecmp($fname, 'subgroupProperties.quadOperationsInAllStages') == 0) {
						$class = ($value == 1) ? "supported" : "unsupported";
						$support = ($value == 1) ? "true" : "false";
						$value = "<span class='".$class."'>".$support."</span>";						
					}
					if (strcasecmp($fname, 'subgroupProperties.supportedStages') == 0) {
						echo "<tr><td class='subkey'>".$fname."</td>";
						echo "<td>".listSubgroupStageFlags($value)."</td>";					
						echo "<td>".VULKAN_CORE_1_0_TEXT."</td></tr>\n";
						continue;
					}
					if (strcasecmp($fname, 'subgroupProperties.supportedOperations') == 0) {
						echo "<tr><td class='subkey'>".$fname."</td>";
						echo "<td>".listSubgroupFeatureFlags($value)."</td>";					
						echo "<td>".VULKAN_CORE_1_0_TEXT."</td></tr>\n";
						continue;
					}				
				}
				echo "<tr><td class='subkey'>".$fname."</td><td>".$value."</td><td>".VULKAN_CORE_1_0_TEXT."</td></tr>\n";
			}				
		}
	} catch (Exception $e) {
		die('Error while fetching report properties');
		DB::disconnect();
	}

	function insertPropertyRow($property, $value, $grouping) {
		if ($property == 'reportid') {
			return;
		}
		$displayvalue = $value;
		if (in_array($property, ['deviceUUID', 'driverUUID', 'deviceLUID'])) {
			$arr = unserialize($value);
			foreach ($arr as &$val) {
				$val = strtoupper(str_pad(dechex($val), 2, "0", STR_PAD_LEFT));
			}
			$displayvalue = implode($arr);
		}		
		echo "<tr><td class='subkey'>$property</td>";
		echo "<td>$displayvalue</td>";
		echo "<td>$grouping</td>";
		echo "</tr>";
	}	

	// Vulkan Core 1.1
	try {
		$stmnt = DB::$connection->prepare("SELECT * from deviceproperties11 where reportid = :reportid");
		$stmnt->execute(array(":reportid" => $reportID));
		while ($row = $stmnt->fetch(PDO::FETCH_NUM)) {
			for ($i = 0; $i < count($row); $i++) {
				if ($row[$i] == "") { continue; }
				$meta = $stmnt->getColumnMeta($i);
				$fname = $meta["name"];
				insertPropertyRow($fname, $row[$i], VULKAN_CORE_1_1_TEXT);
			}				
		}
	} catch (Exception $e) {
		die('Error while fetching report features');
		DB::disconnect();
	}

	// Vulkan Core 1.2
	try {
		$stmnt = DB::$connection->prepare("SELECT * from deviceproperties12 where reportid = :reportid");
		$stmnt->execute(array(":reportid" => $reportID));
		while ($row = $stmnt->fetch(PDO::FETCH_NUM)) {
			for ($i = 0; $i < count($row); $i++) {
				if ($row[$i] == "") { continue; }
				$meta = $stmnt->getColumnMeta($i);
				$fname = $meta["name"];
				insertPropertyRow($fname, $row[$i], VULKAN_CORE_1_2_TEXT);
			}				
		}
	} catch (Exception $e) {
		die('Error while fetching report features');
		DB::disconnect();
	}

	/*
	// Extensions
	try {
		$stmnt = DB::$connection->prepare("SELECT name, value, extension from deviceproperties2 where reportid = :reportid");
		$stmnt->execute(array(":reportid" => $reportID));
		while($row = $stmnt->fetch(PDO::FETCH_NUM)) {
			$value = $row[1];
			if (is_string($value) && substr($value, 0, 2) == "a:") {
				$arr = unserialize($value);
				$value = "[".implode(',', $arr)."]";
			}		
			echo "<tr><td class='subkey'>".$row[0]."</td>";					
			echo "<td>";
			switch($value) {
				case 'true':
					echo "<span class='supported'>true</span>";
				break;
				case 'false':
					echo "<span class='unsupported'>false</span>";
				break;
				default:
					echo $row[1];
			}
			echo "</td>";
			echo "<td>".$row[2]."</td>";
			echo "</tr>\n";
			}
	} catch (Exception $e) {
		die('Error while fetching report extended features');
		DB::disconnect();
	}
	*/
	
	echo "</tbody></table>";	
?>