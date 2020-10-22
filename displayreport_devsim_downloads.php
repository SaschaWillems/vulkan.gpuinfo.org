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
		
	if ($report->info->version >= '1.4') {
		echo "<tr><td class='subkey'>";
		echo "Device simulation layer JSON <span title=\"JSON files that can be used with LunarG's Vulkan device simulation layer\" class=\"hint\">[?]</span>";
		echo "</td><td>";
		echo "<a href=\"api/v2/devsim/getreport.php?id=".$report->id."\"><span class=\"glyphicon glyphicon-floppy-save\"></span> Full JSON file</a>";
		if ($report->flags->has_portability_extension) {
			echo "<br/><a href=\"api/v2/devsim/extension_json.php?id=".$report->id."&extension=VK_KHR_portability_subset\"><span class=\"glyphicon glyphicon-floppy-save\"></span> Portability extension JSON file (VK_KHR_portability_subset)</a>";
		}
		echo "</td><td>".$group."</td></tr>\n";		
	}
?>