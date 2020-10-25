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
<table id='devicelimits' class='table table-striped table-bordered table-hover responsive' style='width:100%;'>
	<thead>
		<tr>
			<td class='caption'>Limit</td>
			<td class='caption'>Value</td>
		</tr>
	</thead>
	<tbody>
	<?php	
		$data = $report->fetchLimits();
		if ($data) {
			foreach($data as $key => $value) {
				if ($key == 'reportid') {
					continue;
				}
				echo "<tr><td class='subkey'>$key</td>";
				if (strpos($key, 'SampleCounts')) {				
					$sampleCountflags = getSampleCountFlags($value);
					if (count($sampleCountflags) > 0) {
						echo "<td>".listSampleCountFlags($value)."</td>";
					} else {
						echo "<td><font color='red'>none</font></td>";
					}
				} else {
					echo "<td>".$value."</td>";
				}			
				echo "</tr>";
			}
		}
	?>
	</tbody>
</table>