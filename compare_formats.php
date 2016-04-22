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

	// Navigation
	echo "<div>";
	echo "<ul class='nav nav-tabs'>";
	echo "	<li class='active'><a data-toggle='tab' href='#format-tabs-1'>Linear tiling</a></li>";
	echo "	<li><a data-toggle='tab' href='#format-tabs-2'>Optimal tiling</a></li>";
	echo "	<li><a data-toggle='tab' href='#format-tabs-3'>Buffer</a></li>";
	echo "</ul>";
	echo "</div>";
	
	echo "<div class='tab-content'>";		
	
	// Get format names		
	$sqlresult = mysql_query("select VkFormat(formatid) as format from deviceformats where reportid = " . $repids[0]); 
	$formatnames = array(); 
	while($row = mysql_fetch_row($sqlresult))
	{	
		$formatnames[] = $row[0];	  
	}
		
	// Get format feature flags
	$linearfeatures = array(); 
	$optimalfeatures = array();
	$bufferfeatures = array();
	
	foreach ($reportids as $repid) 
	{
		$str = "select lineartilingfeatures, optimaltilingfeatures, bufferfeatures from deviceformats where reportid = $repid";			
		$sqlresult = mysql_query($str); 
		$linear = array();
		$optimal = array();
		$buffer = array();
		while($row = mysql_fetch_row($sqlresult)) 
		{	
			$linear[] = $row[0];	  
			$optimal[] = $row[1];	  
			$buffer[] = $row[2];	  
		}
		$linearfeatures[] = $linear; 
		$optimalfeatures[] = $optimal; 
		$bufferfeatures[] = $buffer; 
	}
	
	// Generate tables
	$colspan = count($reportids) + 1;
	
	$featurearrays = array($linearfeatures, $optimalfeatures, $bufferfeatures);
	for ($i = 0; $i < sizeof($featurearrays); $i++)
	{	
		$featurearray = $featurearrays[$i];
		if ($i == 0)
		{
			echo "<div id='format-tabs-".($i+1)."' class='tab-pane fade in active reportdiv'>";	
		}
		else
		{
			echo "<div id='format-tabs-".($i+1)."' class='tab-pane fade reportdiv'>";	
		}
		echo "<table id='formats-".($i)."' width='100%' class='table table-striped table-bordered table-hover'>";	
		
		// Table header
		echo "<thead><tr><td class='caption'>Format</td>";
		foreach ($reportids as $reportId) 
		{
			echo "<td class='caption'>Report $reportId</td>";
		}
		echo "</tr></thead><tbody>";	

		reportCompareDeviceColumns($deviceinfo_captions, $deviceinfo_data, sizeof($reportids));
		
		$rowindex = 0;
		foreach ($formatnames as $extension)
		{
			// Check format diffs
			$diff = false;
			$reportindex = 0;
			$lastval = ($featurearray[0][$rowindex] > 0);
			foreach ($reportids as $repid) 
			{
				if (($featurearray[$reportindex][$rowindex] > 0) != $lastval)
				{
					$diff = true;
					break;
				}
				else
				{
					$lastval = ($featurearray[$reportindex][$rowindex] > 0);		 	
				}
				$reportindex++;
			}  			
			
			$add = ($diff) ? 'color:#FF0000;' : "";
			$className = ($diff) ? "diff" : "same";

			// Linear tiling features
		
			echo "<tr style='$add' class='$className'><td class='firstrow'>$extension</td>\n";		 
			$reportindex = 0;
			foreach ($reportids as $repid) 
			{
				if ($featurearray[$reportindex][$rowindex] > 0) 
				{ 
					echo "<td class='valuezeroleftdark'><img src='icon_check.png' width=16px</td>";
				} 
				else 
				{
					echo "<td class='valuezeroleftdark'><img src='icon_missing.png' width=16px></td>";
				}	
				// todo : flags as (".$featurearray[$reportindex][$rowindex].") as hidden column
				$reportindex++;
			}  
			$rowindex++;
			echo "</tr>"; 		
		}	  
		echo "</tbody></table></div>";				
	}
	
	
	echo "</div>";	
?>