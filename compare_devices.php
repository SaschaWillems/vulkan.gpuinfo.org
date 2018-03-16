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
	

	$repids = implode(",", $reportids);   
	
	$sqlresult = mysql_query("select 
			p.devicename,
			r.displayname,
			p.driverversion,
			p.devicetype,
			p.apiversion,
			VendorId(p.vendorid) as 'vendor',
			concat('0x', hex(cast(p.deviceid as UNSIGNED))) as 'deviceid',
			r.submitter,
			r.submissiondate,
			r.osname,
			r.osarchitecture,
			r.osversion,
			r.description,
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
		where r.id in (" . $repids . ")") or die(mysql_error());				
	
	$reportindex = 0;
	
	// Gather data into arrays
	$column = array();
	$captions = array();
	$groups = array();
	
	while($row = mysql_fetch_row($sqlresult)) 
	{
		$colindex = 0;
		$reportdata = array();		
		
		foreach ($row as $data)
		{
			$caption = mysql_field_name($sqlresult, $colindex);		  
			$group = "Device";
			
			if (($caption == 'pipelineCacheUUID') && (!is_null($data))) {
				$arr = unserialize($data);
				foreach ($arr as &$val) 
					$val = strtoupper(str_pad(dechex($val), 2, "0", STR_PAD_LEFT));
				$reportdata[] = implode($arr);
				$captions[] = $caption;
				$groups[] = $group;
				$colindex++;
				continue;
			}

			if (strpos($caption, 'residency') !== false) {
				$group = "Sparse residency";
			}			

			if (strpos($caption, 'subgroupProperties') !== false) {
				$group = "Subgroup operations";	
				$caption = str_replace('subgroupProperties.', '', $caption);				
			}			

			if ($caption != "reportid") {
				$reportdata[] = $data;
				$captions[] = $caption;
				$groups[] = $group;
			}									
			
			$colindex++;
		} 
		
		$column[] = $reportdata; 
		
		$reportindex++;
	}   

	// Platform details (when available)
	//todo:
	/*
select distinct name from deviceplatformdetails 
left join platformdetails on platformdetails.id = deviceplatformdetails.platformdetailid 
where deviceplatformdetails.ReportID in (".$repids.");"	
	*/
	
	// Generate table from selected reports

	echo "<thead><tr><td class='caption'>Key</td><td>Group</td>";
	foreach ($reportids as $reportId) {
		echo "<td class='caption'>Report $reportId</td>";
	}
	echo "</tr></thead><tbody>";

	$index = 1;  
	for ($i = 0, $arrsize = sizeof($column[0]); $i < $arrsize; ++$i) 
	{ 	  	
		if (strcasecmp($captions[$i], "displayname") == 0) {
			$empty = true;
			for ($j = 0, $subarrsize = sizeof($column); $j < $subarrsize; ++$j) {
				if ($column[$j][$i] !== null) {
					$empty = false;
					break;
				}
			}
			if ($empty) {
				continue;
			}
		}

		// Get min and max for this capability
		if (is_numeric($column[0][$i])) {
			
			$minval = $column[0][$i];
			$maxval = $column[0][$i];
			
			for ($j = 0, $subarrsize = sizeof($column); $j < $subarrsize; ++$j) 
			{	 			
				if ($column[$j][$i] < $minval) 
				{
					$minval = $column[$j][$i];
				}
				if ($column[$j][$i] > $maxval) 
				{
					$maxval = $column[$j][$i];
				}
			}
		}								
		
		// Report header
		$className = "";
		$fontStyle = "";
		if (in_array($groups[$i], ["Sparse residency", "Subgroup operations"])) 
		{
			$className = ($minval < $maxval) ? "" : "class='sameCaps'";
			$fontStyle = ($minval < $maxval) ? "style='color:red;'" : "";					
		} 

		echo "<tr ".$className.">\n";
		echo "<td class='subkey' $fontStyle>". $captions[$i] ."</td>\n";									
		echo "<td>".$groups[$i]."</td>";
		
		// Values
		for ($j = 0, $subarrsize = sizeof($column); $j < $subarrsize; ++$j) 
		{	 
			if (strcasecmp($groups[$i], 'Subgroup operations') == 0) {
				if ($column[$j][$i] == null) {
					echo "<td class='na' title='Only available with Vulkan 1.1 and up and report version 1.5 and up'>n/a</td>";
					continue;
				}
				if (strcasecmp($captions[$i], 'quadOperationsInAllStages') == 0) {
					$class = ($column[$j][$i] == 1) ? "supported" : "unsupported";
					$support = ($column[$j][$i] == 1) ? "true" : "false";
					$column[$j][$i] = "<span class='".$class."'>".$support."</span>";						
				}
				if (strcasecmp($captions[$i], 'supportedStages') == 0) {
					echo "<td>".listSubgroupStageFlags($column[$j][$i])."</td>";					
					continue;
				}
				if (strcasecmp($captions[$i], 'supportedOperations') == 0) {
					echo "<td>".listSubgroupFeatureFlags($column[$j][$i])."</td>";					
					continue;
				}
			}

			echo "<td>";
			if (strpos($captions[$i], 'residency') === false) 
			{
				echo $column[$j][$i];				
			}
			else
			{
				// Features are bool only
				echo ($column[$j][$i] == 1) ? "<span class='supported'>true</font>" : "<span class='unsupported'>false</font>";
			}
			echo "</td>";			
		} 
		echo "</tr>\n";
		$index++;
	}   


?>