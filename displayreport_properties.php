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
				if ($key == 'reportid') { continue; }
				$displayvalue = getPropertyDisplayValue($key, $value);
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
				$key = $row[0];
				$value = $row[1];
				$displayvalue = $value;
				if (is_string($value) && substr($value, 0, 2) == "a:") {
					$arr = unserialize($value);
					$displayvalue = "[".implode(',', $arr)."]";
				} else {
					$displayvalue = getPropertyDisplayValue($key, $value);
				}
				echo "<tr><td class='subkey'>$key</td><td>";					
				echo $displayvalue;
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