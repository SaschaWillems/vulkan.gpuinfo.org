<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2024 by Sascha Willems (www.saschawillems.de)
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
<table id='deviceextensions' class='table table-striped table-bordered table-hover reporttable'>
	<thead>
		<tr>
			<td class='caption'>Extension</td>
			<td class='caption'>Version</td>
		</tr>
	</thead>
	<tbody>
		<?php
		$extension_list = $report->fetchExtensions();
		$extension_blacklist = $report->fetchDeviceExtensionsBlacklist();
		foreach ($extension_list as $extension) {
			$extension_name = $extension['name'];
			echo "<tr>";
			// Some drivers wrongly report some instance extensions as device extensions
			// To avoid confusion, those entries are marked accordingly
			if (in_array($extension_name, $extension_blacklist)) {
				echo "<td class='na'>⚠️ $extension_name (<i>Wrongly reported as device instead of instance extension</i>)</td>";
				echo "<td class='na'>r.".$extension['specversion']."</td>";
			} else {
				$link = "displayextensiondetail.php?extension=$extension_name";
				echo "<td><a href='$link'>$extension_name</a></td>";
				echo "<td>r.".$extension['specversion']."</td>";
			}
			echo "</tr>";
		}
		?>
	</tbody>
</table>