<?php 
	/* 		
		*
		* Vulkan hardware capability database server implementation
		*	
		* Copyright (C) Sascha Willems (www.saschawillems.de)
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
	
	include './dbconfig.php';
	include './header.inc';	
?>

<script>
	$(document).ready(function() {
		var table = $('#extensions').DataTable({
			"pageLength" : -1,
			"paging" : false,
			"stateSave": false, 
			"searchHighlight" : true,	
			"dom": 'f',			
			"bInfo": false,	
			"order": [[ 0, "asc" ]],
			"columnDefs": [{
      			"targets": [ 2, 3, 4 ],
      			"render": $.fn.dataTable.render.percentBar('round', '#000', '#eaeaea', '#14963c', '#fff', 2, 'solid')
			}]
		});

		$("#searchbox").on("keyup search input paste cut", function() {
			table.search(this.value).draw();
		});

		$('#extensions tbody td').click( function () {
			var data = table.row( $(this).parents('tr') ).data();
			var index = table.cell( this ).index().columnVisible;
			var platform = null;
			switch(index) {
				case 2:
					platform = 'windows';
					break;
				case 3:
					platform = 'linux';
					break;
				case 4:
					platform = 'android';
					break;
			}
			if (index > 1) {
				window.open('listdevices.php?platform='+platform+'&extension='+data[0]);
			}
		} );

	} );	
</script>

<!-- #dc3c14 -->

<div class='header'>
	<h4>Listing all available extensions</h4>
	<?php echo count($devicesWindows) . ' / ' . count($devicesLinux) . ' / ' . count($devicesAndroid); ?>
</div>			

<center>
	<div class='tablediv' style='width:auto; display: inline-block;'>

	<table id="extensions" class="table table-striped table-bordered table-hover responsive" style='width:auto;'>
		<thead>
			<tr>			
				<th></th>
				<th colspan=3 style="text-align: center;">Device coverage</th>
			</tr>
			<tr>			
				<th style="display:none;">Extensions</th>
				<th>Extensions</th>
				<th style="text-align: center; width:90px;">Windows</th>
				<th style="text-align: center; width:90px;">Linux</th>
				<th style="text-align: center; width:90px;">Android</th>
			</tr>
		</thead>
		<tbody>		
			<?php		
				DB::connect();
				try {
					$viewDeviceCount =DB::$connection->prepare("SELECT * from viewDeviceCount");
					$viewDeviceCount->execute(); 
					$deviceCounts = $viewDeviceCount->fetch(PDO::FETCH_ASSOC);					

					$extensions = DB::$connection->prepare("SELECT name, windows, linux, android, features2, properties2 from viewExtensionsPlatforms");
					$extensions->execute();

					if ($extensions->rowCount() > 0) { 
						while ($extension = $extensions->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {								
							$link = ($extension[4] > 0 || $extension[5] > 0);
							echo "<tr>";						
							echo "<td style=\"display:none;\">".$extension[0]."</td>";
							echo $link ? "<td><a href=\"displayextension.php?name=".$extension[0]."\">".$extension[0]."</a></td>" : "<td>".$extension[0]."</td>";
							echo "<td>".round($extension[1]/$deviceCounts["windows"]*100,2)."</td>";
							echo "<td>".round($extension[2]/$deviceCounts["linux"]*100,2)."</td>";
							echo "<td>".round($extension[3]/$deviceCounts["android"]*100,2)."</td>";
							echo "</tr>";	       
						}
					}

				} catch (PDOException $e) {
					echo "<b>Error while fetcthing data!</b><br>";
				}
				DB::disconnect();
			?>   
		</tbody>
	</table>  

</div>

<?php include './footer.inc'; ?>

</center>
</body>
</html>