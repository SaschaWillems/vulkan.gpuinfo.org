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
	
	$surfaceformatscount = mysql_result(mysql_query("select count(*) from devicesurfaceformats where reportid = $reportID"), 0);
	$surfacepresentmodescount = mysql_result(mysql_query("select count(*) from devicesurfacemodes where reportid = $reportID"), 0);			
	
	// Navigation
	echo "<div>";
	echo "<ul class='nav nav-tabs'>";
	echo "	<li class='active'><a data-toggle='tab' href='#surfaceproperties'>Surface properties</a></li>";
	echo "	<li><a data-toggle='tab' href='#surfaceformats'>Surface formats <span class='badge'>$surfaceformatscount</span></a></li>";
	echo "	<li><a data-toggle='tab' href='#presentmodes'>Present modes <span class='badge'>$surfacepresentmodescount</span></a></li>";
	echo "</ul>";
	echo "</div>";
	
	echo "<div class='tab-content'>";	

	// Surface properties
	echo "<div id='surfaceproperties' class='tab-pane fade in active reportdiv'>";
	echo "<table id='devicesurfaceproperties' class='table table-striped table-bordered table-hover responsive' style='width:auto;'>";
	echo "<thead><tr><td class='caption'>Property</td><td class='caption'>Value</td></tr></thead><tbody>";		
	$sqlresult = mysql_query("select * from devicesurfacecapabilities where reportid = $reportID") or die(mysql_error());	
	while($row = mysql_fetch_row($sqlresult))
	{
		for($i = 0; $i < count($row); $i++)
		{			
			$fname = mysql_field_name($sqlresult, $i);		  			
			$value = $row[$i];
			if ($fname == "reportid")
				continue;
			echo "<tr><td class='key'>".$fname."</td><td>";
			// Usage flags			
			if ($fname == "supportedUsageFlags")
			{
				listFlags(getImageUsageFlags($value));
				continue;
			}
			// Transforms
			if ($fname == "supportedTransforms")
			{
				listFlags(getSurfaceTransformFlags($value));
				continue;
			}
			// Composite alpha
			if ($fname == "supportedCompositeAlpha")
			{
				listFlags(getCompositeAlphaFlags($value));
				continue;
			}		
			//
			echo $value;			
			echo "</td></tr>\n";
		}				
	}		
	echo "</tbody></table>";	
	echo "</div>";

	// Surface formats	
	echo "<div id='surfaceformats' class='tab-pane fade in active reportdiv'>";
	echo "<table id='devicesurfaceformats' class='table table-striped table-bordered table-hover responsive' style='width:auto;'>";
	echo "<thead><tr><td class='caption'>Index</td><td class='caption'>Format</td><td class='caption'>Colorspace</td></tr></thead><tbody>";	
	
	$sqlresult = mysql_query("select VkFormat(format), colorspace from devicesurfaceformats where reportid = $reportID") or die(mysql_error());	
	$n = 0;
	while($row = mysql_fetch_row($sqlresult))
	{
		echo "<tr>";
		echo "<td class='key'>".$n."</td>";
		$n++;
		for($i = 0; $i < count($row); $i++)
		{			
			$fname = mysql_field_name($sqlresult, $i);		  			
			$value = $row[$i];
			if ($fname == "colorspace")
			{
				echo "<td class='key'>".getColorSpace($value)."</td>\n";
				continue;
			}
			echo "<td class='key'>".$value."</td>\n";
		}				
		echo "</tr>";
	}		
	echo "</tbody></table>";	
	echo "</div>";	
	
	// Surface formats	
	echo "<div id='presentmodes' class='tab-pane fade in active reportdiv'>";
	echo "<table id='devicepresentmodes' class='table table-striped table-bordered table-hover responsive' style='width:auto;'>";
	echo "<thead><tr><td class='caption'>Present mode</td></tr></thead><tbody>";	
	
	$sqlresult = mysql_query("select presentmode from devicesurfacemodes where reportid = $reportID") or die(mysql_error());	
	$n = 0;
	while($row = mysql_fetch_row($sqlresult))
	{
		echo "<tr>";
		echo "<td class='key'>".getPresentMode($row[0])."</td>";
		echo "</tr>";
	}		
	echo "</tbody></table>";	
	echo "</div>";	

	echo "</div>";		
?>