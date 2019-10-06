<?php
	/* 		
		*
		* Vulkan hardware capability database server implementation
		*	
		* Copyright (C) by Sascha Willems (www.saschawillems.de)
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
	<div>
	<ul class='nav nav-tabs'>
		<li class='active'><a data-toggle='tab' href='#format-tabs-1'>Linear tiling</a></li>
		<li><a data-toggle='tab' href='#format-tabs-2'>Optimal tiling</a></li>
		<li><a data-toggle='tab' href='#format-tabs-3'>Buffer</a></li>
	</ul>
	</div>
	<div class='tab-content'>
<?php
	
	// Get format names
	try {
		$stmnt = DB::$connection->prepare("SELECT distinct VkFormat(formatid) as format from deviceformats where reportid in (" . $repids . ")");
		$stmnt->execute();
		while($row = $stmnt->fetch(PDO::FETCH_ASSOC)) {	
			$formatnames[] = $row["format"];
		}
	} catch (PDOException $e) {
		die("Could not fetch device formats!");
	}		
		
	// Get format feature flags
	$linearfeatures = array(); 
	$optimalfeatures = array();
	$bufferfeatures = array();
	
	foreach ($reportids as $repid) {
		try {
			$stmnt = DB::$connection->prepare("SELECT lineartilingfeatures, optimaltilingfeatures, bufferfeatures from deviceformats where reportid = :reportid");
			$stmnt->execute(["reportid" => $repid]);
		} catch (PDOException $e) {
			die("Could not fetch device formats!");
		}			
		$linear = array();
		$optimal = array();
		$buffer = array();
		while($row = $stmnt->fetch(PDO::FETCH_ASSOC)) {	
			$linear[] = $row["lineartilingfeatures"];	  
			$optimal[] = $row["optimaltilingfeatures"];	  
			$buffer[] = $row["bufferfeatures"];	  
		}
		$linearfeatures[] = $linear; 
		$optimalfeatures[] = $optimal; 
		$bufferfeatures[] = $buffer; 
	}
	
	// Generate tables
	$colspan = count($reportids) + 1;
	
	$featurearrays = array($linearfeatures, $optimalfeatures, $bufferfeatures);
	for ($i = 0; $i < sizeof($featurearrays); $i++) {	
		$featurearray = $featurearrays[$i];
		if ($i == 0) {
			echo "<div id='format-tabs-".($i+1)."' class='tab-pane fade in active reportdiv'>";	
		} else {
			echo "<div id='format-tabs-".($i+1)."' class='tab-pane fade reportdiv'>";	
		}
		echo "<table id='formats-".($i)."' width='100%' class='table table-striped table-bordered table-hover'>";	
		
		$captions = ['Linear image', 'Optimal image', 'Buffer'];
		ReportCompare::insertTableHeader($captions[$i]." format", $deviceinfo_data, count($reportids));
		ReportCompare::insertDeviceColumns($deviceinfo_captions, $deviceinfo_data, count($reportids));
	
		$rowindex = 0;
		foreach ($formatnames as $extension) {
			// Check format diffs
			$diff = false;
			$reportindex = 0;
			$lastval = ($featurearray[0][$rowindex] > 0);
			foreach ($reportids as $repid) {
				if (($featurearray[$reportindex][$rowindex] > 0) != $lastval) {
					$diff = true;
					break;
				} else {
					$lastval = ($featurearray[$reportindex][$rowindex] > 0);		 	
				}
				$reportindex++;
			}  			
			
			$add = ($diff) ? 'color:#FF0000;' : "";
			$className = ($diff) ? "diff" : "same";

			// Linear tiling features
		
			echo "<tr style='$add' class='$className'><td class='firstrow'>$extension</td>\n";		 
			$reportindex = 0;
			foreach ($reportids as $repid) {
				if ($featurearray[$reportindex][$rowindex] > 0) { 
					echo "<td class='valuezeroleftdark'><img src='icon_check.png' width=16px</td>";
				} else {
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