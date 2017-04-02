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
	
	// Table header
	echo "<thead><tr><td class='caption'>Key</td>";
	foreach ($reportids as $reportId) {
		echo "<td class='caption'>Report $reportId</td>";
	}
	echo "</tr></thead><tbody>";
	
	$repids = implode(",", $reportids);   
	
	$sqlresult = mysql_query("select 
			p.devicename,
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
			p.residencyAlignedMipSize, 
			p.residencyNonResidentStrict, 
			p.residencyStandard2DBlockShape, 
			p.residencyStandard2DMultisampleBlockShape, 
			p.residencyStandard3DBlockShape,
			p.pipelineCacheUUID
		from reports r
		left join
		deviceproperties p on (p.reportid = r.id)				
		where r.id in (" . $repids . ")") or die(mysql_error());				
	
	$reportindex = 0;
	
	// Gather data into array
	$column    = array();
	$captions  = array();
	
	while($row = mysql_fetch_row($sqlresult)) 
	{
		$colindex = 0;
		$reportdata = array();		
		
		foreach ($row as $data) 
		{
			$caption = mysql_field_name($sqlresult, $colindex);		  
			
			if ($caption == 'pipelineCacheUUID') {
				$arr = unserialize($data);
				foreach ($arr as &$val) 
					$val = strtoupper(str_pad(dechex($val), 2, "0", STR_PAD_LEFT));
				$reportdata[] = implode($arr);
				$colindex++;
				continue;
			}

			if ($caption != "reportid") {
				$reportdata[] = $data;	  
				$captions[]   = $caption;
			}									
			
			$colindex++;
		} 
		
		$column[] = $reportdata; 
		
		$reportindex++;
	}   
	
	// Generate table from selected reports
	$index = 1;  
	for ($i = 0, $arrsize = sizeof($column[0]); $i < $arrsize; ++$i) 
	{ 	  	
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
		if (strpos($captions[$i], 'residency') !== false) 
		{
			$className = ($minval < $maxval) ? "" : "class='sameCaps'";
			$fontStyle = ($minval < $maxval) ? "style='color:red;'" : "";					
		} 

		echo "<tr style='$className'>\n";
		echo "<td class='firstrow' $fontStyle>". $captions[$i] ."</td>\n";									
		
		// Values
		for ($j = 0, $subarrsize = sizeof($column); $j < $subarrsize; ++$j) 
		{	 
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