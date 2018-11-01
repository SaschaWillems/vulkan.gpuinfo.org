<?php
	/* 		
		*
		* Vulkan hardware capability database server implementation
		*	
		* Copyright (C) 2016-2018 by Sascha Willems (www.saschawillems.de)
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
		<li class='active'><a data-toggle='tab' href='#surfaceproperties'>Surface properties</a></li>
		<li><a data-toggle='tab' href='#surfaceformats'>Surface formats <span class='badge'><?php echo $surfaceformatscount ?></span></a></li>
		<li><a data-toggle='tab' href='#presentmodes'>Present modes <span class='badge'><?php echo $surfacepresentmodescount ?></span></a></li>
	</ul>
</div>
<div class='tab-content'>
	<!-- Surface properties -->
	<div id='surfaceproperties' class='tab-pane fade in active reportdiv'>
		<table id='devicesurfaceproperties' class='table table-striped table-bordered table-hover responsive' style='width:auto;'>
			<thead>
				<tr>
					<td class='caption'>Property</td>
					<td class='caption'>Value</td>
				</tr>
			</thead>
			<tbody>
<?php	
                try {
                    $stmnt = DB::$connection->prepare("SELECT * from devicesurfacecapabilities where reportid = :reportid");
                    $stmnt->execute(array(":reportid" => $reportID));
                    while($row = $stmnt->fetch(PDO::FETCH_NUM)) {
						for($i = 0; $i < count($row); $i++)
						{			
							$meta = $stmnt->getColumnMeta($i);
							$fname = $meta["name"];  			
							$value = $row[$i];
							if ($fname == "reportid")
								continue;
							echo "<tr><td class='key'>".$fname."</td><td>";
							if ($fname == "supportedUsageFlags") {
								listFlags(getImageUsageFlags($value));
								continue;
							}
							if ($fname == "supportedTransforms") {
								listFlags(getSurfaceTransformFlags($value));
								continue;
							}
							if ($fname == "supportedCompositeAlpha") {
								listFlags(getCompositeAlphaFlags($value));
								continue;
							}		
							//
							echo $value;			
							echo "</td></tr>\n";
						}
                    }
                } catch (Exception $e) {
                    die('Error while fetching report surface capabilities');
                    DB::disconnect();
				}				
?>	
			</tbody>
		</table>
	</div>

	<!-- Surface formats	 -->
	<div id='surfaceformats' class='tab-pane fade reportdiv'>
		<table id='devicesurfaceformats' class='table table-striped table-bordered table-hover responsive' style='width:auto;'>
			<thead>
				<tr>
					<td class='caption'>Index</td>
					<td class='caption'>Format</td>
					<td class='caption'>Colorspace</td>
				</tr>
			</thead>
			<tbody>
<?php	
                try {
                    $stmnt = DB::$connection->prepare("SELECT VkFormat(format), colorspace from devicesurfaceformats where reportid = :reportid");
					$stmnt->execute(array(":reportid" => $reportID));
					$n = 0;					
                    while($row = $stmnt->fetch(PDO::FETCH_NUM)) {
						echo "<tr>";
						echo "<td class='key'>".$n."</td>";
						$n++;
						for($i = 0; $i < count($row); $i++) {			
							$meta = $stmnt->getColumnMeta($i);
							$fname = $meta["name"];  		  			
							$value = $row[$i];
							if ($fname == "colorspace") {
								echo "<td class='key'>".getColorSpace($value)."</td>\n";
								continue;
							}
							echo "<td class='key'>".$value."</td>\n";
						}				
						echo "</tr>";
                    }
                } catch (Exception $e) {
                    die('Error while fetching report surface formats');
                    DB::disconnect();
				}		
?>
			</tbody>
		</table>
	</div>
	
	<!-- Present modes	 -->
	<div id='presentmodes' class='tab-pane fade reportdiv'>
		<table id='devicepresentmodes' class='table table-striped table-bordered table-hover responsive' style='width:auto;'>
			<thead>
				<tr>
					<td class='caption'>Present mode</td>
				</tr>
			</thead>
			<tbody>
<?php	
                try {
                    $stmnt = DB::$connection->prepare("SELECT presentmode from devicesurfacemodes where reportid = :reportid");
					$stmnt->execute(array(":reportid" => $reportID));			
                    while($row = $stmnt->fetch(PDO::FETCH_NUM)) {
						echo "<tr>";
						echo "<td class='key'>".getPresentMode($row[0])."</td>";
						echo "</tr>";
                    }
                } catch (Exception $e) {
                    die('Error while fetching report surface present modes');
					DB::disconnect();
				}
?>
			</tbody>
		</table>
	</div>

</div>