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
		
	if ($reportversion >= '1.4') {
		echo "<tr><td class='subkey'>Device simulation layer JSON</td><td>";
		echo "<a href=\"api/v2/devsim/getreport.php?id=".$reportID."\"><span class=\"glyphicon glyphicon-floppy-save\"></span> Full JSON file</a>";
		$portability_ext_present = DB::getCount("SELECT count(*) from deviceextensions de right join extensions e on de.extensionid = e.id where reportid = :reportid and name = :extension", [':reportid' => $reportID, ':extension' => 'VK_KHR_portability_subset']);
		if ($portability_ext_present > 0) {			
			echo "<br/><a href=\"api/v2/devsim/extension_json.php?id=".$reportID."&extension=VK_KHR_portability_subset\"><span class=\"glyphicon glyphicon-floppy-save\"></span> Portability extension JSON file (VK_KHR_portability_subset)</a>";
		}
		echo "</td><td>".$group."</td></tr>\n";		
	}
?>