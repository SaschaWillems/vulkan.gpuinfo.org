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
<table id='deviceformats' class='table table-striped table-bordered table-hover reporttable'>
	<thead>
		<tr>
			<td></td>
			<td class='caption'>Format</td>
			<td class='caption'>Linear</td>
			<td class='caption'>Optimal</td>
			<td class='caption'>Buffer</td>
			<td class='caption'>flags</td>
			<td class='caption'>flags</td>
			<td class='caption'>flags</td>
		</tr>
	</thead>
	<tbody>
	<?php
		$data = $report->fetchFormats();
		if ($data) {
			foreach($data as $format) {
				$supported = ($format["supported"] == 1);
				$class = $supported ? 'default' : 'unsupported';
				echo "<tr class='$class'>";			
				echo "<td class='details-control'></td>";			
				echo "<td>".$format["format"]."</td>";
				
				// Linear tiling
				$class = ($format["lineartilingfeatures"] != 0) ? 'supported' : 'unsupported';
				$supported = ($format["lineartilingfeatures"] != 0) ? 'true' : 'false';
				echo "<td class='$class'>".$supported."</td>";
				
				// Optimal tiling
				$class = ($format["optimaltilingfeatures"] != 0) ? 'supported' : 'unsupported';
				$supported = ($format["optimaltilingfeatures"] != 0) ? 'true' : 'false';
				echo "<td class='$class'>".$supported."</td>";
				
				// Buffer features
				$class = ($format["bufferfeatures"] != 0) ? 'supported' : 'unsupported';
				$supported = ($format["bufferfeatures"] != 0) ? 'true' : 'false';
				echo "<td class='$class'>".$supported."</td>";
				
				// Invisible columns containing flags
				echo "<td>".$format["lineartilingfeatures"]."</td>";
				echo "<td>".$format["optimaltilingfeatures"]."</td>";
				echo "<td>".$format["bufferfeatures"]."</td>";
				
				echo "</tr>";
			}
		}
		/*
		try {
			$stmnt = DB::$connection->prepare("SELECT VkFormat(formatid) as format, deviceformats.* from deviceformats where reportid = :reportid");
			$stmnt->execute(array(":reportid" => $reportID));
			while($row = $stmnt->fetch(PDO::FETCH_ASSOC)) {
				$supported = ($row["supported"] == 1);
				$class = $supported ? 'default' : 'unsupported';
				echo "<tr class='$class'>";			
				echo "<td class='details-control'></td>";			
				echo "<td>".$row["format"]."</td>";
				
				// Linear tiling
				$class = ($row["lineartilingfeatures"] != 0) ? 'supported' : 'unsupported';
				$supported = ($row["lineartilingfeatures"] != 0) ? 'true' : 'false';
				echo "<td class='$class'>".$supported."</td>";
				
				// Optimal tiling
				$class = ($row["optimaltilingfeatures"] != 0) ? 'supported' : 'unsupported';
				$supported = ($row["optimaltilingfeatures"] != 0) ? 'true' : 'false';
				echo "<td class='$class'>".$supported."</td>";
				
				// Buffer features
				$class = ($row["bufferfeatures"] != 0) ? 'supported' : 'unsupported';
				$supported = ($row["bufferfeatures"] != 0) ? 'true' : 'false';
				echo "<td class='$class'>".$supported."</td>";
				
				// Invisible columns containing flags
				echo "<td>".$row["lineartilingfeatures"]."</td>";
				echo "<td>".$row["optimaltilingfeatures"]."</td>";
				echo "<td>".$row["bufferfeatures"]."</td>";
				
				echo "</td></tr>\n";
			}
		} catch (Exception $e) {
			die('Error while fetching report features');
			DB::disconnect();
		}
		*/
	?>
	</tbody>
</table>
