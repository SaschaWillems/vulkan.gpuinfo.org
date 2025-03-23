<?php

/**	
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2024 by Sascha Willems (www.saschawillems.de)
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

require 'pagegenerator.php';
require './database/database.class.php';
require './database/sqlrepository.php';
require './includes/functions.php';

PageGenerator::header("Instance extensions");
$platform = PageGenerator::getDefaultOSSelection();
PageGenerator::pageCaption("Listing available instance extensions");
PageGenerator::globalFilterText();
?>	

<center>
	<?php PageGenerator::platformNavigation('listinstanceextensions.php', $platform, true); ?>

	<div class='tablediv' style='width:auto; display: inline-block;'>
		<table id="extensions" class="table table-striped table-bordered table-hover responsive" style='width:auto;'>
			<thead>
				<tr>
					<th></th>
					<th colspan=2 class="centered">Device coverage</th>
				</tr>			
				<tr>			
					<th>Extensions</th>
					<th class="centered"><img src='images/icons/check.png' width=16px></th>
					<th class="centered"><img src='images/icons/missing.png' width=16px></th>
					<th class="centered"><abbr title="Date at when the extension was first submitted to the database for the current platform selection">First seen</abbr></th>
				</tr>
			</thead>
			<tbody>		
				<?php
					DB::connect();
					$start = microtime(true);
					try {
						$instanceextensions = SqlRepository::listInstanceExtensions();
						foreach($instanceextensions as $instanceextension) {
							$extensionname = $instanceextension['name'];
							$coverageLink = "listreports.php?instanceextension=$extensionname&platform=$platform";
							$coverage = $instanceextension['coverage'];
							echo "<tr>";						
							echo "<td class='value'>$extensionname</td>";
							echo "<td class='text-center'><a class='supported' href='$coverageLink'>" . round($coverage, 2) . "<span style='font-size:10px;'>%</span></a></td>";
							echo "<td class='text-center'><a class='na' href='$coverageLink&option=not'>" . round(100 - $coverage, 2) . "<span style='font-size:10px;'>%</span></a></td>";
							echo "<td class='value'>".$instanceextension['date']."</td>";
							echo "</tr>";
						}

					} catch (PDOException $e) {
						echo "<b>Error while fetching data!</b><br>";
					}
					DB::log('listinstanceextensions.php', null, (microtime(true) - $start) * 1000);
					DB::disconnect();
				?>   
			</tbody>
		</table>  
</div>

<script>
	$(document).ready(function() {
		var table = $('#extensions').DataTable({
			"pageLength" : -1,
			"paging" : false,
			"stateSave": false, 
			"searchHighlight" : true,	
			"dom": 'f',			
			"bInfo": false,	
			"order": [[ 0, "asc" ]]	
		});

		$("#searchbox").on("keyup search input paste cut", function() {
			table.search(this.value).draw();
		});  		

	} );	
</script>

<?php PageGenerator::footer(); ?>

</center>
</body>
</html>