<?php
	/* 		
		*
		* Vulkan hardware capability database server implementation
		*	
		* Copyright (C) 2016-2018 by Sascha Willems (www.saschawillems.de)
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
	try {
		$stmnt = DB::$connection->prepare("SELECT e.name as name, de.specversion as specversion from deviceextensions de join extensions e on de.extensionid = e.id where reportid = :reportid");
		$stmnt->execute(array(":reportid" => $reportID));
		while($row = $stmnt->fetch(PDO::FETCH_NUM)) {
			echo "<tr><td class='key'><a href='listreports.php?extension=".$row[0]."'>".$row[0]."</a></td>";
			echo "<td>".versionToString($row[1])."</td>";
			echo "</tr>\n";		
			echo "</td></tr>\n";
		}
	} catch (Exception $e) {
		die('Error while fetching report features');
		DB::disconnect();
	}
?>
	</tbody>
</table>