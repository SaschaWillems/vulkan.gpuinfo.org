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
	
	function insertCoreFeatures($report, $version) {
		$features = $report->fetchCoreFeatures($version);
		if ($features) {
			foreach($features as $key => $value) {
				echo "<tr><td class='subkey'>$key</td>";
				echo "<td class='".($value ? 'supported' : 'unsupported')."'>".($value ? 'true' : 'false')."</td>";
				echo "<td>Vulkan Core $version</td>";
				echo "</tr>";				
			}
		}
	}

	function insertExtensionFeatures($report) {
?>
        <div id='features_extensions' class='tab-pane fade reportdiv'>
			<table id='devicefeatures_extensions' class='table table-striped table-bordered table-hover responsive' style='width:100%;'>
				<thead>
					<tr>
						<td class='caption'>Feature</td>
						<td class='caption'>Supported</td>
						<td>Extension</td>
					</tr>
				</thead>
				<tbody>
				<?php
					$extension_features = $report->fetchExtensionFeatures();
					if ($extension_features) {
						foreach($extension_features as $extension_feature) {
							echo "<tr><td class='subkey'>".$extension_feature['name']."</td><td>";
							echo ($extension_feature['supported'] == 1) ? "<font color='green'>true</font>" : "<font color='red'>false</font>";
							echo "<td>".$extension_feature['extension']."</td>";
							echo "</td></tr>";
						}
					}
				?>
				</tbody>
			</table>
		</div>

<?php
	}
	
	if ($report->flags->has_extended_features) {
?>		
		<div>
			<ul class='nav nav-tabs nav-level1'>
				<li class='active'><a data-toggle='tab' href='#features_core'><span class='glyphicon glyphicon-ok icon-pad-right'></span>Core</a></li>
				<li><a data-toggle='tab' href='#features_extensions'><span class='glyphicon glyphicon-cog icon-pad-right'></span>Extensions</a></li>
			</ul>
		</div>
<?php		
	}

?>
	<div class='tab-content'>
        <!-- Core -->
        <div id='features_core' class='tab-pane fade in active reportdiv'>
			<table id='devicefeatures' class='table table-striped table-bordered table-hover responsive' style='width:100%;'>
				<thead>
					<tr>
						<td class='caption'>Feature</td>
						<td class='caption'>Supported</td>
						<td></td>
					</tr>
				</thead>
				<tbody>				
				<?php
					insertCoreFeatures($report, '1.0');
					if ($report->flags->has_vulkan_1_1_features) {
						insertCoreFeatures($report, '1.1');
					}
					if ($report->flags->has_vulkan_1_2_features) {
						insertCoreFeatures($report, '1.2');
					}
				?>
				</tbody>
			</table>
		</div>
<?php
	if ($report->flags->has_extended_features) {
		insertExtensionFeatures($report);
	}
?>
	</div>
