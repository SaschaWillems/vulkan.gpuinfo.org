<?php

/**	
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2022 by Sascha Willems (www.saschawillems.de)
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
?>

<div class='header'>
	<?php echo "<h4>Listing available instance extensions ".PageGenerator::filterInfo() ?>
</div>			

<center>	
	<div class='tablediv' style='width:auto; display: inline-block;'>
		<table id="extensions" class="table table-striped table-bordered table-hover responsive" style='width:auto;'>
			<thead>
				<tr>
					<th></th>
					<th colspan=2 style="text-align: center;">Device coverage</th>
				</tr>			
				<tr>			
					<th>Extensions</th>
					<th style="text-align: center;"><img src='images/icons/check.png' width=16px></th>
					<th style="text-align: center;"><img src='images/icons/missing.png' width=16px></th>				
				</tr>
			</thead>
			<tbody>		
				<?php
					DB::connect();
					try {
						$instanceextensions = SqlRepository::listInstanceExtensions();
						foreach($instanceextensions as $instanceextension) {
							$name = $instanceextension['name'];
							$coverageLink = null;
							$coverage = $instanceextension['coverage'];
							echo "<tr>";						
							echo "<td class='value'><a href='listreports.php?instanceextension=".$name."'>".$name."</a> (<a href='listreports.php?instanceextension=".$name."&option=not'>not</a>)</td>";
							echo "<td class='text-center'><a class='supported' href='$coverageLink'>" . round($coverage, 1) . "<span style='font-size:10px;'>%</span></a></td>";
							echo "<td class='text-center'><a class='na' href='$coverageLink&option=not'>" . round(100 - $coverage, 1) . "<span style='font-size:10px;'>%</span></a></td>";
							echo "</tr>";
						}

					} catch (PDOException $e) {
						echo "<b>Error while fetching data!</b><br>";
					}
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