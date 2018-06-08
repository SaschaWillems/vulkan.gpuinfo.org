<?php
	/* 		
		*
		* Vulkan hardware capability database server implementation
		*	
		* Copyright (C) 2016~2018 by Sascha Willems (www.saschawillems.de)
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
	<table id='deviceproperties' class='table table-striped table-bordered table-hover responsive' style='width:100%;'>
		<thead>
			<tr>
				<td class='caption'>Property</td>
				<td class='caption'>Value</td>
				<td class='caption'>Group</td>
			</tr>
		</thead>
	<tbody>
<?php
	
	$sqlresult = mysql_query("select 
		p.devicename,
		r.displayname,
		p.driverversionraw,
		p.driverversion,
		p.devicetype,
		p.apiversion,
		p.vendorid,
		VendorId(p.vendorid) as 'vendor',
		concat('0x', hex(cast(p.deviceid as UNSIGNED))) as 'deviceid',
		r.submitter,
		r.submissiondate,
		r.osname,
		r.osarchitecture,
		r.osversion,
		r.description,
		r.version as reportversion,
		p.pipelineCacheUUID,
		p.residencyAlignedMipSize,
		p.residencyNonResidentStrict, 
		p.residencyStandard2DBlockShape, 
		p.residencyStandard2DMultisampleBlockShape, 
		p.residencyStandard3DBlockShape,
		p.`subgroupProperties.subgroupSize`,
		p.`subgroupProperties.supportedStages`,
		p.`subgroupProperties.supportedOperations`,
		p.`subgroupProperties.quadOperationsInAllStages`
	from reports r
	left join
	deviceproperties p on (p.reportid = r.id)
	where r.id = $reportID") or die(mysql_error());
	
	while($row = mysql_fetch_row($sqlresult))
	{
		for($i = 0; $i < count($row); $i++)
		{
			if ($row[$i] == "") { continue; }
			$fname = mysql_field_name($sqlresult, $i);		  			
			$value = $row[$i];
			$group = 'Device';
			if ($fname == 'submitter') {
				$value = '<a href="listreports.php?submitter='.$value.'">'.$value.'</a>';
			}
			if ($fname == 'devicename') {
				$value = '<a href="listreports.php?devicename='.$value.'">'.$value.'</a>';			
			}
			if ($fname == 'displayname') {
				$value = '<a href="listreports.php?displayname='.$value.'">'.$value.'</a>';			
			}
			if (strpos($fname, 'residency') !== false) {
				$class = ($value == 1) ? "supported" : "unsupported";
				$support = ($value == 1) ? "true" : "false";
				$value = "<span class='".$class."'>".$support."</span>";
				$group = "Sparse residency";
			}
			if (($fname == 'driverversion') | ($fname == 'vendorid')) {
				continue;
			}
			if ($fname == 'driverversionraw') {
				$fname = 'driverversion';
				$value = getDriverVerson($value, $row[2], $row[6], $row[11])
			}
			if (($fname == 'pipelineCacheUUID') && (!is_null($value))) {
				$arr = unserialize($value);
				foreach ($arr as &$val) 
					$val = strtoupper(str_pad(dechex($val), 2, "0", STR_PAD_LEFT));
				$value = implode($arr);
			}
			if (strpos($fname, 'subgroupProperties') !== false) {
				$group = "Subgroup operations";					
				$fname = str_replace('subgroupProperties.', '', $fname);
				if (strcasecmp($fname, 'quadOperationsInAllStages') == 0) {
					$class = ($value == 1) ? "supported" : "unsupported";
					$support = ($value == 1) ? "true" : "false";
					$value = "<span class='".$class."'>".$support."</span>";						
				}
				if (strcasecmp($fname, 'supportedStages') == 0) {
					echo "<tr><td class='subkey'>".$fname."</td>";
					echo "<td>".listSubgroupStageFlags($value)."</td>";					
					echo "<td>".$group."</td></tr>\n";
					continue;
				}
				if (strcasecmp($fname, 'supportedOperations') == 0) {
					echo "<tr><td class='subkey'>".$fname."</td>";
					echo "<td>".listSubgroupFeatureFlags($value)."</td>";					
					echo "<td>".$group."</td></tr>\n";
					continue;
				}				
			}
			echo "<tr><td class='subkey'>".$fname."</td><td>".$value."</td><td>".$group."</td></tr>\n";
		}				
	}

	// Platform details (when available)
	$sqlresult = mysql_query("select name, value from deviceplatformdetails dpfd join platformdetails pfd on dpfd.platformdetailid = pfd.id where dpfd.reportid = ".$reportID." order by name asc");
	while($row = mysql_fetch_row($sqlresult)) {
		echo "<tr><td class='key'>".$row[0]."</td><td>".$row[1]."</td></tr>\n";
	}
	
	echo "</tbody></table>";	
?>