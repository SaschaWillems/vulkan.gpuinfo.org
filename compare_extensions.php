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
	echo "<thead><tr><td class='caption'>Extension</td>";
	foreach ($reportids as $reportId) 
	{
		echo "<td class='caption'>Report $reportId</td>";
	}
	echo "</tr></thead><tbody>";
	// Gather all extensions supported by at least one of the reports
	$str = "select distinct Name from deviceextensions 
			left join extensions on extensions.ID = deviceextensions.extensionid 
			where deviceextensions.ReportID in (" . $repids . ")";
	$sqlresult = mysql_query($str); 
	$extcaption = array(); // Names (for all gathered extensions)   
	
	while($row = mysql_fetch_row($sqlresult))
	{	
		foreach ($row as $data) 
		{
			$extcaption[] = $data;	  
		}
	}
	
	// Get extensions for each selected report into an array 
	$extarray = array(); 
	
	foreach ($reportids as $repid) 
	{
		$str = "select name from extensions 
			left join deviceextensions 
			on extensions.id = deviceextensions.extensionid where deviceextensions.reportId = $repid";			
		$sqlresult = mysql_query($str); 
		$subarray = array();
		while($row = mysql_fetch_row($sqlresult)) 
		{	
			foreach ($row as $data) 
			{
				$subarray[] = $data;	  
			}
		}
		$extarray[] = $subarray; 
	}
	
	// Generate table
	$colspan = count($reportids) + 1;	
	
	reportCompareDeviceColumns($deviceinfo_captions, $deviceinfo_data, sizeof($reportids));

	// Extension count 	
	echo "<tr class='firstrow'><td class='firstrow'>Extension count</td>"; 
	for ($i = 0, $arrsize = sizeof($extarray); $i < $arrsize; ++$i) 
	{ 	  
		echo "<td class='valuezeroleftdark'>".count($extarray[$i])."</td>";
	}
	echo "</tr>"; 		
	$rowindex++;
	
	foreach ($extcaption as $extension)
	{
		
		// Check if missing it at least one report
		$missing = false;
		$index = 0;
		foreach ($reportids as $repid) 
		{
			if (!in_array($extension, $extarray[$index])) 
			{
				$missing = true;
			}
			$index++;
		}  			
		
		$add = '';
		if ($missing) 
		{
			$add = 'color:#FF0000;';
		}
		$className = "same";
		$index = 0;
		foreach ($reportids as $repid) 
		{
			if (!in_array($extension, $extarray[$index])) 
			{ 
				$className = "diff";
			}
			$index++;
		}
		echo "<tr style='$add' class='$className'><td class='firstrow'>$extension</td>\n";		 
		$index = 0;
		foreach ($reportids as $repid) 
		{
			if (in_array($extension, $extarray[$index])) 
			{ 
				echo "<td class='valuezeroleftdark'><img src='icon_check.png' width=16px></td>";
				} else {
				echo "<td class='valuezeroleftdark'><img src='icon_missing.png' width=16px></td>";
			}	
			$index++;
		}  
		$rowindex++;
		echo "</tr>"; 
	}	  
?>