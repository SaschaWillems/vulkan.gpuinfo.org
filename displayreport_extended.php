<?php
	/* 		
		*
		* Vulkan hardware capability database server implementation
		*	
		* Copyright (C) 2016~2017 by Sascha Willems (www.saschawillems.de)
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

	<!-- Navigation -->
	<div>
	<ul class='nav nav-tabs'>
        <?php 
        	$featurecount = mysql_result(mysql_query("select count(*) from devicefeatures2 where reportid = $reportID"), 0);
	        $propertycount = mysql_result(mysql_query("select count(*) from deviceproperties2 where reportid = $reportID"), 0);			

            echo "<li class='active'><a data-toggle='tab' href='#features2'>Features <span class='badge'>$featurecount</span></a></li>";
            echo "<li><a data-toggle='tab' href='#properties2'>Properties <span class='badge'>$propertycount</span></a></li>";
        ?>
	</ul>
	</div>
	
	<div class='tab-content'>
	
        <!-- Features -->
        <div id='features2' class='tab-pane fade in active reportdiv'>
            <table id='extended_features' class="table table-striped table-bordered table-hover reporttable responsive autowidthtable">
            <thead><tr><td class='caption'>Feature</td><td class='caption'>Value</td><td class='caption'>Extension</td></tr></thead><tbody>
            <?php
                $sqlresult = mysql_query("select name, supported, extension from devicefeatures2 where reportid = $reportID") or die(mysql_error());
                while($row = mysql_fetch_row($sqlresult)) {
                    $value = $row[$i];
                    echo "<tr><td class='subkey'>".$row[0]."</td><td>";					
                    echo ($row[1] == 1) ? "<font color='green'>true</font>" : "<font color='red'>false</font>";
                    echo "<td>".$row[2]."</td>";
                    echo "</td></tr>";
                }    
            ?>
            </tbody></table>
        </div>

        <!-- Properties -->
        <div id='properties2' class='tab-pane fade reportdiv'>
            <table id='extended_properties' class='table table-striped table-bordered table-hover responsive autowidthtable'>
            <thead><tr><td class='caption'>Property</td><td class='caption'>Value</td><td class='caption'>Extension</td></tr></thead><tbody>
            <?php
                $sqlresult = mysql_query("select name, value, extension from deviceproperties2 where reportid = $reportID") or die(mysql_error());
                while($row = mysql_fetch_row($sqlresult)) {
                    $value = $row[$i];
                    echo "<tr><td class='subkey'>".$row[0]."</td><td>";					
                    echo $row[1];
                    echo "<td>".$row[2]."</td>";
                    echo "</td></tr>\n";
                }    
            ?>
            </tbody></table>
        </div>

	</div>