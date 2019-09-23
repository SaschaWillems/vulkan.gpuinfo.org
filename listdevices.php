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

	include './header.inc';	
	include './functions.php';	
	include './dbconfig.php';	

	$platform = "all";
	if (isset($_GET['platform'])) {
		$platform = $_GET['platform'];
		// TODO: Check valid platforms
	}

	$caption = 'Listing all devices';
	$showTabs = true;

	if (isset($_GET['platform'])) {
		$caption = "Listing all <img src='images/".$platform."logo.png' height='14px' style='padding-right:5px'/>".ucfirst($platform)." devices";
	}
	if (isset($_GET["extension"])) {
		$caption .= " supporting ".$_GET["extension"];
		$showTabs = false;
	}
	if (isset($_GET["submitter"])) {
		$caption .= "Devices submitted by ".$_GET["submitter"];
		$showTabs = false;
	}
?>

<center>

	<div class='header'>	
		<h4>
		<?php		
			echo $caption;
		?>
		</h4>
	</div>

<?php	
	if ($showTabs) {
?>		
	<div>
		<ul class='nav nav-tabs'>
			<li <?php if ($platform == "all") 	  { echo "class='active'"; } ?>> <a href='listdevices.php'>All platforms</a> </li>
			<li <?php if ($platform == "windows") { echo "class='active'"; } ?>> <a href='listdevices.php?platform=windows'><img src="images/windowslogo.png" height="14px" style="padding-right:5px">Windows</a> </li>
			<li <?php if ($platform == "linux")   { echo "class='active'"; } ?>> <a href='listdevices.php?platform=linux'><img src="images/linuxlogo.png" height="16px" style="padding-right:4px">Linux</a> </li>
			<li <?php if ($platform == "android") { echo "class='active'"; } ?>> <a href='listdevices.php?platform=android'><img src="images/androidlogo.png" height="16px" style="padding-right:4px">Android</a> </li>
		</ul>
	</div>
<?php	
	}	
?>

	<div class='tablediv tab-content' style='display: inline-flex;'>

	<div id='devices_div' class='tab-pane <?php if ($i == 0) { echo "fade in active"; } ?>'>
		<form method="get" action="compare.php">
		<table id='devices' class='table table-striped table-bordered table-hover responsive' style='width:auto'>
			<thead>
				<tr>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
				</tr>			
				<tr>
					<th>Device</th>
					<th>Max. API version</th>
					<th>Latest Driver version</th>
					<th>Last submission</th>
					<th>Count</th>
					<th><input type='submit' class='button' value='compare'></th>
				</tr>
			</thead>		
		</table>
		<div id="errordiv" style="color:#D8000C;"></div>		
		</form>

	</div>
</center>

<script>
	$(document).on("keypress", "form", function(event) { 
    	return event.keyCode != 13;
	});	
		
	$( document ).ready(function() {
		var table = $('#devices').DataTable({
			"processing": true,
			"serverSide": true,
			"paging" : true,		
			"searching": true,	
			"lengthChange": false,
			"dom": 'lrtip',	
			"pageLength" : 25,		
			"order": [[ 3, 'desc' ]],
			"columnDefs": [
				{ 
					"searchable": false, "targets": [ 3, 4, 5 ] ,
					"orderable": false, "targets": [ 5 ]			
			    }
			],			
			"ajax": {
				url :"responses/devices.php?platform=<?php echo $platform ?>",
				data: {
					"filter": {
						'extension' : '<?php echo $_GET["extension"] ?>' ,
						'feature' : '<?php echo $_GET["feature"] ?>' ,
						'submitter' : '<?php echo $_GET["submitter"] ?>',
						'linearformat' : '<?php echo $_GET["linearformat"] ?>',
						'optimalformat' : '<?php echo $_GET["optimalformat"] ?>',
						'bufferformat' : '<?php echo $_GET["bufferformat"] ?>',
						'devicelimit' : '<?php echo $_GET["limit"] ?>',
						'option' : '<?php echo $_GET["option"] ?>',
						'surfaceformat' : '<?php echo $_GET["surfaceformat"] ?>',
						'surfacepresentmode' : '<?php echo $_GET["surfacepresentmode"] ?>',
						'devicename' : '<?php echo $_GET["devicename"] ?>',
						'displayname' : '<?php echo $_GET["displayname"] ?>',												
					}
				},
				error: function (xhr, error, thrown) {
					$('#errordiv').html('Could not fetch data (' + error + ')');
					$('#devices_processing').hide();
				}				
			},
			"columns": [
				{ data: 'device' },
				{ data: 'api' },
				{ data: 'driver' },
				// { data: 'reportversion' },
				{ data: 'submissiondate' },
				{ data: 'reportcount' },
				{ data: 'compare' }
			],
			// Pass order by column information to server side script
			fnServerParams: function(data) {
				data['order'].forEach(function(items, index) {
					data['order'][index]['column'] = data['columns'][items.column]['data'];
				});
			},
		});

        yadcf.init(table, [
            {
                column_number: 0,
				filter_type: "text",
                filter_delay: 500
            },
            {
                 column_number: 1,
                 filter_type: "text",
                 filter_delay: 500
            },
            {
                column_number: 2,
				filter_type: "text",
                filter_delay: 500
            },
        ], { filters_tr_index: 0});
	});
</script>

<?php include './footer.inc'; ?>

</body>
</html>
