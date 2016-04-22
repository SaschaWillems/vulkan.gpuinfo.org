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
	
	$memheapcount = mysql_result(mysql_query("select count(*) from devicememoryheaps where reportid = $reportID"), 0);			
	
	// Navigation
	echo "<div>";
	echo "<ul class='nav nav-tabs'>";
	echo "	<li class='active'><a data-toggle='tab' href='#memorytypes'>Memory types <span class='badge'>$memtypecount</span></a></li>";
	echo "	<li><a data-toggle='tab' href='#memoryheaps'>Memory heaps <span class='badge'>$memheapcount</span></a></li>";
	echo "</ul>";
	echo "</div>";
	
	echo "<div class='tab-content'>";	
	
	// Memory types
	echo "<div id='memorytypes' class='tab-pane fade in active reportdiv'>";
	
	$sqlresult = mysql_query("select * from devicememorytypes where reportid = $reportID") or die(mysql_error());
	$index = 0;
	while($row = mysql_fetch_assoc($sqlresult))
	{
		echo "<table class='table table-striped table-bordered table-hover responsive' style='width:100%;'>";
		echo "<thead><tr>";
		echo "<tr><td colspan=2 class=tablehead>Memory type $index</td></tr>";		
		echo "</tr></thead><tbody>";			

		echo "<tr>";
		echo "<td class='key'>Heapindex</td>";
		echo "<td>".$row["heapindex"]."</td>";
		echo "</tr>";

		// Flags
		echo "<tr>";
		echo "<td class='key'>Flags</td>";
		echo "<td>";
		$memoryFlags = getMemoryTypeFlags($row["propertyflags"]);
		if (sizeof($memoryFlags) > 0)
		{
			foreach ($memoryFlags as $flag)
			{
				echo $flag."<br>";
			}
		}
		else
		{
			echo "none";
		}
		echo "<tr>";	
		
		echo "</tbody></table>";					
		
		$index++;
	}			

	echo "</div>";	
	
	// Memory heaps
	echo "<div id='memoryheaps' class='tab-pane fade reportdiv'>";
	
?>	
	<div class="alert alert-warning" role="alert">
		<b>Note:</b> Listing may contain memory heaps with host sizes!
	</div>	
<?		
	
	$sqlresult = mysql_query("select * from devicememoryheaps where reportid = $reportID") or die(mysql_error());
	$index = 0;
	while($row = mysql_fetch_assoc($sqlresult))
	{
		echo "<table class='table table-striped table-bordered table-hover responsive' style='width:100%;'>";
		echo "<thead><tr>";
		echo "<tr><td colspan=2 class=tablehead>Memory heap $index</td></tr>";		
		echo "</tr></thead><tbody>";			

		echo "<tr>";
		echo "<td class='key'>Size</td>";
		echo "<td>".number_format($row["size"])." bytes</td>";
		echo "</tr>";

		// Flags
		echo "<tr>";
		echo "<td class='key'>Flags</td>";
		echo "<td>";
		$flags = getMemoryHeapFlags($row["flags"]);
		if (sizeof($flags) > 0)
		{
			foreach ($flags as $flag)
			{
				echo $flag."<br>";
			}
		}
		else
		{
			echo "none";
		}
		echo "<tr>";	
		
		echo "</tbody></table>";							
		
		$index++;
	}			

	echo "</div>";	
		
	echo "</div>";		
?>