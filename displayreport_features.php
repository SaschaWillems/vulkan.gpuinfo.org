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
		$report->beginTab('features_core_'.str_replace('.', '',$version), $version == '1.0');
		$report->beginTable('table_features_core_'.str_replace('.', '',$version), ['Feature', 'Supported']);
		$features = $report->fetchCoreFeatures($version);
		if ($features) {
			foreach($features as $key => $value) {
				if ($key == 'reportid') { continue; }
				echo "<tr><td class='subkey'>$key</td>";
				echo "<td class='".($value ? 'supported' : 'unsupported')."'>".($value ? 'true' : 'false')."</td>";
				echo "</tr>";
			}
		}
		$report->endTable();
		$report->endTab();		
	}

	function insertExtensionFeatures($report) {
		$report->beginTab('features_extensions', false);
		$report->beginTable('table_features_extensions', ['Feature', 'Supported', 'Extension']);
		$extension_features = $report->fetchExtensionFeatures();
		if ($extension_features) {
			foreach($extension_features as $extension_feature) {
				echo "<tr><td class='subkey'>".$extension_feature['name']."</td><td>";
				echo ($extension_feature['supported'] == 1) ? "<font color='green'>true</font>" : "<font color='red'>false</font>";
				echo "<td>".$extension_feature['extension']."</td>";
				echo "</td></tr>";
			}
		}
		$report->endTable();
		$report->endTab();
	}
	
	$display_tabs = ($report->flags->has_vulkan_1_1_features || $report->has_vulkan_1_2_features || $report->flags->has_extended_features);
	if ($display_tabs) {
		echo "<div>";
		echo "	<ul class='nav nav-tabs nav-level1'>";		
		echo "		<li class='active'><a data-toggle='tab' href='#features_core_10'>Core 1.0</a></li>";
		if ($report->flags->has_vulkan_1_1_features) {
			echo "<li><a data-toggle='tab' href='#features_core_11'>Core 1.1</a></li>";
		}
		if ($report->flags->has_vulkan_1_2_features) {
			echo "<li><a data-toggle='tab' href='#features_core_12'>Core 1.2</a></li>";
		}
		if ($report->flags->has_extended_features) {
			echo "<li><a data-toggle='tab' href='#features_extensions'>Extensions</a></li>";
		}
		echo "	</ul>";
		echo "</div>";
		echo "<div class='tab-content'>";
	}

	insertCoreFeatures($report, '1.0');
	if ($report->flags->has_vulkan_1_1_features) {
		insertCoreFeatures($report, '1.1');
	}
	if ($report->flags->has_vulkan_1_2_features) {
		insertCoreFeatures($report, '1.2');
	}
	if ($report->flags->has_extended_features) {
		insertExtensionFeatures($report);
	}

	if ($display_tabs) {
		echo "</div>";
	}

?>