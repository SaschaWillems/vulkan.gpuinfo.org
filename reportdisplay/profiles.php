<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2022 by Sascha Willems (www.saschawillems.de)
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
<table id='table_deviceprofiles' class='table table-striped table-bordered table-hover reporttable'>
	<thead>
		<tr>
			<td class='caption'>Profile</td>
			<td class='caption'>Supported</td>
		</tr>
	</thead>
	<tbody>
		<?php
		$data = $report->fetchProfiles();
		if ($data) {
			foreach ($data as $profile) {
				$link = "listdevicescoverage.php?profile=".$profile['name'].$linkplatform;
				echo "<tr><td><a href='$link'>" . $profile['name'] . "</a></td>";
				echo "<td class='".($profile['supported'] ? 'supported' : 'unsupported')."'>" . ($profile['supported'] ? 'true' : 'false')."</td>";
				echo "</tr>";
			}
		}
		?>
	</tbody>
</table>