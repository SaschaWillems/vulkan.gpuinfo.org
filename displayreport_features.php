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
	<table id='devicefeatures' class='table table-striped table-bordered table-hover responsive' style='width:100%;'>
		<thead>
			<tr>
				<td class='caption'>Feature</td>
				<td class='caption'>Value</td>
			</tr>
		</thead>
	<tbody>
<?php	
	try {
		$stmnt = DB::$connection->prepare("SELECT * from devicefeatures where reportid = :reportid");
		$stmnt->execute(array(":reportid" => $reportID));
		while($row = $stmnt->fetch(PDO::FETCH_NUM)) {
			for($i = 0; $i < count($row); $i++) {
				if ($row[$i] == "") { continue; }
				$meta = $stmnt->getColumnMeta($i);
				$fname = $meta["name"];
				if ($fname == 'reportid') {
					continue;
				}
				$value = $row[$i];
				echo "<tr><td class='key'>$fname</td><td>";					
				echo ($value == 1) ? "<font color='green'>true</font>" : "<font color='red'>false</font>";
				echo "</td></tr>\n";
			}				
		}
	} catch (Exception $e) {
		die('Error while fetching report features');
		DB::disconnect();
	}
?>
	</tbody>
</table>
