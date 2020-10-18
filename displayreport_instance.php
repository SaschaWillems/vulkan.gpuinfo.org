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

	$extensions = DB::$connection->prepare("SELECT e.name as name, ie.specversion as specversion from deviceinstanceextensions ie join instanceextensions e on ie.extensionid = e.id where reportid = :reportid");
	$extensions->execute([":reportid" => $reportID]);	
	$extCount = $extensions->rowCount();

	$layers = DB::$connection->prepare("SELECT il.name as name, dil.specversion as specversion, dil.implversion as implversion from deviceinstancelayers dil join instancelayers il on il.id = dil.layerid where reportid = :reportid");
	$layers->execute([":reportid" => $reportID]);	
	$layerCount = $layers->rowCount();

?>	
	<div>
		<ul class='nav nav-tabs nav-level1'>
			<li class='active'><a data-toggle='tab' href='#tabinstanceextensions'>Extensions <span class='badge'><?php echo $extCount ?></span></a></li>
			<li><a data-toggle='tab' href='#tabinstancelayers'>Layers <span class='badge'><?php echo $layerCount ?></span></a></li>
		</ul>
	</div>

	<div class = "tab-content">

		<div id='tabinstanceextensions' class='tab-pane fade in active reportdiv'>
			<table id='instanceextensions' class='table table-striped table-bordered table-hover reporttable'>
				<thead>
					<tr>
						<td class='caption'>Extension</td>
						<td class='caption'>Version</td>
					</tr>
				</thead>
				<tbody>
					<?php	
						if ($extCount > 0) { 
							foreach ($extensions as $ext) {
								echo "<tr><td><a href='listreports.php?instanceextension=".$ext["name"]."'>".$ext["name"]."</a></td>";
								echo "<td>".versionToString($ext["specversion"])."</td>";
								echo "</tr>\n";
							}        
						}
					?>
				</tbody>
			</table>
		</div>

		<div id='tabinstancelayers' class='tab-pane reportdiv'>
			<table id='instancelayers' class='table table-striped table-bordered table-hover reporttable'>
				<thead>
					<tr>
						<td class='caption'>Layername</td>
						<td class='caption'>Spec</td>
						<td class='caption'>Implementation</td>
					</tr>
				</thead>
				<tbody>
					<?php	
						if ($layerCount > 0) { 
							foreach ($layers as $layer) {
								echo "<tr><td><a href='listreports.php?instancelayer=".$layer["name"]."'>".$layer["name"]."</a></td>";
								echo "<td>".versionToString($layer["specversion"])."</td>";
								echo "<td>".versionToString($layer["implversion"])."</td>";
								echo "</tr>\n";
							}        
						}
					?>
				</tbody>
			</table>
		</div>

	</diV>	