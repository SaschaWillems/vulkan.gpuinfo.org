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
?>

<?php
	function insertCoreProperties($report, $version) {
		$features = $report->fetchCoreProperties($version);
		if ($features) {
			foreach($features as $key => $value) {
				if ($version == '1.0') {
					if (strpos($key, 'residency') !== false) {
						$class = ($value == 1) ? "supported" : "unsupported";
						$support = ($value == 1) ? "true" : "false";
						$value = "<span class='".$class."'>".$support."</span>";
					}				
					if (strpos($key, 'subgroupProperties') !== false) {
						if (strcasecmp($key, 'subgroupProperties.quadOperationsInAllStages') == 0) {
							$class = ($value == 1) ? "supported" : "unsupported";
							$support = ($value == 1) ? "true" : "false";
							$value = "<span class='".$class."'>".$support."</span>";						
						}
						if (strcasecmp($key, 'subgroupProperties.supportedStages') == 0) {
							echo "<tr><td class='subkey'>".$key."</td>";
							echo "<td>".listSubgroupStageFlags($value)."</td>";					
							echo "<td>Vulkan Core $version</td></tr>\n";
							continue;
						}
						if (strcasecmp($key, 'subgroupProperties.supportedOperations') == 0) {
							echo "<tr><td class='subkey'>".$key."</td>";
							echo "<td>".listSubgroupFeatureFlags($value)."</td>";					
							echo "<td>Vulkan Core $version</td></tr>\n";
							continue;
						}				
					}
					echo "<tr><td class='subkey'>".$key."</td><td>".$value."</td><td>Vulkan Core $version</td></tr>";
					continue;					
				}
				$displayvalue = $value;
				if (in_array($key, ['deviceUUID', 'driverUUID', 'deviceLUID'])) {
					$arr = unserialize($value);
					foreach ($arr as &$val) {
						$val = strtoupper(str_pad(dechex($val), 2, "0", STR_PAD_LEFT));
					}
					$displayvalue = implode($arr);
				}		
				echo "<tr><td class='subkey'>$key</td>";
				echo "<td>$displayvalue</td>";
				echo "<td>Vulkan Core $version</td>";
				echo "</tr>";
			}
		}
	}

	function insertExtensionProperties($reportid) {
		try {
			$stmnt = DB::$connection->prepare("SELECT name, value, extension from deviceproperties2 where reportid = :reportid");
			$stmnt->execute(array(":reportid" => $reportid));
			while($row = $stmnt->fetch(PDO::FETCH_NUM)) {
				$value = $row[1];
				if (is_string($value) && substr($value, 0, 2) == "a:") {
					$arr = unserialize($value);
					$value = "[".implode(',', $arr)."]";
				}
				echo "<tr><td class='subkey'>".$row[0]."</td><td>";					
				echo $value;
				echo "<td>".$row[2]."</td>";
				echo "</td></tr>\n";
				}
		} catch (Exception $e) {
			die('Error while fetching report extended features');
			DB::disconnect();
		}
	}

	if ($report->flags->has_extended_properties) {
	?>		
			<div>
				<ul class='nav nav-tabs nav-level1'>
					<li class='active'><a data-toggle='tab' href='#properties_core'>Core</a></li>
					<li><a data-toggle='tab' href='#properties_extensions'>Extensions</a></li>
				</ul>
			</div>
	<?php		
	}
	?>
	<div class='tab-content'>
        <!-- Core -->
        <div id='properties_core' class='tab-pane fade in active reportdiv'>
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
			insertCoreProperties($report, '1.0');
			if ($report->flags->has_vulkan_1_1_properties) {
				insertCoreProperties($report, '1.1');
			}
			if ($report->flags->has_vulkan_1_2_properties) {
				insertCoreProperties($report, '1.2');
			}
			?>
			</tbody>
		</table>
	</div>

	<!-- Extensions -->
<?php
	if ($report->flags->has_extended_properties) {
?>
	<div id='properties_extensions' class='tab-pane fade reportdiv'>
		<table id='deviceproperties_extensions' class='table table-striped table-bordered table-hover responsive' style='width:100%;'>
			<thead>
				<tr>
					<td class='caption'>Feature</td>
					<td class='caption'>Supported</td>
					<td>Extension</td>
				</tr>
			</thead>
			<tbody>
			<?php
			if ($report->flags->has_extended_properties) {
				insertExtensionProperties($reportID);
			}
			?>
			</tbody>
		</table>
	</div>
<?php
	}
?>
</div>