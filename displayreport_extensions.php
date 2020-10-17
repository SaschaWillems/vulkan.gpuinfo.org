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
<table id='deviceextensions' class='table table-striped table-bordered table-hover reporttable'>
	<thead>
		<tr>
			<td class='caption'>Extension</td>
			<td class='caption'>Version</td>
		</tr>
	</thead>
	<tbody>
	<?php	
		$data = $report->fetchExtensions();
		if ($data) {
			foreach($data as $extension) {
				$link = "listdevicescoverage.php?extension=".$extension['name'].$linkplatform;
				echo "<tr><td class='subkey'><a href='$link'>".$extension['name']."</a></td>";
				echo "<td>".versionToString($extension['specversion'])."</td>";
				echo "</tr>";
			}
		}
	?>
	</tbody>
</table>