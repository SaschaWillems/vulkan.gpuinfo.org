<?php
	/* 		
		*
		* Vulkan hardware capability database server implementation
		*	
		* Copyright (C) 2016 by Sascha Willems (www.saschawillems.de)
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
	<div class="alert alert-warning" role="alert">
		<b>Note:</b> Layers are only available if the Vulkan SDK is installed and may contain custom layers installed by the user!
	</div>	
<?		
	echo "<table id='devicelayers' class='table table-striped table-bordered table-hover reporttable'>";
	echo "<thead><tr>";
	echo "<td width='15px'></td>";	
	echo "<td class='caption'>Layername</td>";
	echo "<td class='caption'>Spec</td>";
	echo "<td class='caption'>Implementation</td>";
	echo "<td class='caption'>Extensions</td></tr>";
	echo "</thead><tbody>";
	// Available layers
	$sqlresult = mysql_query("
		select 
			l.name as name, dl.implversion as implversion, dl.specversion as specversion, 
			(select GROUP_CONCAT(name) from devicelayerextensions dlext where dlext.reportid = dl.reportid and dlext.devicelayerid = dl.layerid) as layerexts		
		from devicelayers dl join layers l on dl.layerid = l.id 
		where dl.reportid = $reportID") or die(mysql_error());
	while($row = mysql_fetch_assoc($sqlresult))
	{
		echo "<tr>";
		if ($row["layerexts"] != "")
		{
			echo "<td class='details-control'></td>";	
		}
		else
		{
			echo "<td></td>";	
		}
		echo "<td class='key'>".$row["name"]."</td>";
		echo "<td>".versionToString($row["specversion"])."</td>";
		echo "<td>".versionToString($row["implversion"])."</td>";
		echo "<td>".$row["layerexts"]."</td>";
		echo "</td></tr>\n";
	}
	echo "</tbody></table>";					
?>