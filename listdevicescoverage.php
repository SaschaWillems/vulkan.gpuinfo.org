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
	}
	$negate = false;
	if (isset($_GET['option'])) {
		$negate = $_GET['option'] == 'not';
	}

	$caption = null;
	$subcaption = null;

	if (isset($_GET["extension"])) {
		$caption = $negate ? 
			"Listing devices <span style='color:red;'>not</span> supporting <b>".$_GET["extension"]."</b>" 
			: 
			"Listing first known driver version support for <b>".$_GET["extension"]."</b>";
		// Check if extension has features2 or properties2
		$ext = $_GET["extension"];
		DB::connect();
		try {
			$stmnt = DB::$connection->prepare("SELECT
				(SELECT COUNT(DISTINCT df2.name) FROM devicefeatures2 df2 WHERE (df2.extension = ext.name)) AS features2,
				(SELECT COUNT(DISTINCT dp2.name) FROM deviceproperties2 dp2 WHERE (dp2.extension = ext.name)) AS properties2
				FROM extensions ext where ext.name = :extension");
			$stmnt->execute(['extension' => $ext]);
			$res = $stmnt->fetch(PDO::FETCH_ASSOC);
			if ($res) {
				if ($res['features2'] > 0 || $res['properties2'] > 0) {
					$arr = [];
					if ($res['features2'] > 0) { $arr[] = 'Features'; }
					if ($res['properties2'] > 0) { $arr[] = 'properties'; }
					$linkTitle = implode(' and ', $arr);
					$subcaption = "<div style='margin-top: 10px;'>This extension has additional <a href='displayextension.php?name=$ext'>$linkTitle</a></div>";
				}
			}	
		} catch(Throwable $e) {
		}
		DB::disconnect();
	}

	if (isset($_GET["feature"])) {
		$caption = $negate ? 
			"Listing devices <span style='color:red;'>not</span> supporting for <b>".$_GET["feature"]."</b>" 
			: 
			"Listing first known driver version support for <b>".$_GET["feature"]."</b>";
	}

	if (isset($_GET['linearformat'])) {
		$caption = $negate ?
			"Listing devices <span style='color:red;'>not</span> supporting <b>".$_GET['linearformat']."</b> for <b>linear tiling</b>"
			:
			"Listing first known driver version support for <b>".$_GET['linearformat']."</b> for <b>linear tiling</b>";
	}	
	if (isset($_GET['optimalformat'])) {
		$caption = $negate ?
			"Listing devices <span style='color:red;'>not</span> supporting <b>".$_GET['optimalformat']."</b> for <b>optimal tiling</b>"
			:
			"Listing first known driver version support for <b>".$_GET['optimalformat']."</b> for <b>optimal tiling</b>";
	}
	if (isset($_GET['bufferformat'])) {
		$caption = $negate ?
			"Listing devices <span style='color:red;'>not</span> supporting <b>".$_GET['bufferformat']."</b> for <b>buffer usage</b>"
			:
			"Listing first known driver version support for <b>".$_GET['bufferformat']."</b> for <b>buffer usage</b>";
	}	

	if (isset($_GET['surfaceformat'])) {
		$caption = $negate ?
			"Listing devices <span style='color:red;'>not</span> supporting surface format <b>".$_GET['surfaceformat']."</b>"
			:
			"Listing first known driver version support for surface format <b>".$_GET['surfaceformat']."</b>";
	}

	if (isset($_GET['surfacepresentmode'])) {
		$caption = $negate ?
			"Listing devices <span style='color:red;'>not</span> supporting surface present mode <b>".$_GET['surfacepresentmode']."</b>"
			:
			"Listing first known driver version support for surface present mode <b>".$_GET['surfacepresentmode']."</b>";
	}

	if (isset($_GET['platform'])) {
		$caption .= " on <img src='images/".$platform."logo.png' height='14px' style='padding-right:5px'/>".ucfirst($platform);
	}

	if (isset($_GET["submitter"])) {
		$caption .= "<br/>Devices submitted by ".$_GET["submitter"];
	}
?>

<center>

	<div class='header'>	
		<h4>
		<?php		
			echo $caption ? $caption : "Listing available devices";
			echo $subcaption ? "<br>$subcaption" : "";
		?>
		</h4>
	</div>

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
				</tr>
				<tr>
					<th>Device</th>
					<th>Vendor</th>
					<th>Driver <span title="First known driver version supporting this extension/feature" class="hint">[?]</span></th>
					<th>Date</th>
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
			"lengthChange": true,
			"lengthMenu": [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "All"] ],
			"dom": 'lrtip',	
			"pageLength" : 50,		
			"order": [[ 0, 'asc' ]],
			"columnDefs": [{ "orderable": false, "targets": [ 4 ] }],
			"ajax": {
				url :"responses/devices.php?platform=<?php echo $platform ?>&minversion=true",
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
				{ data: 'vendor' },
				{ data: 'driver' },
				{ data: 'submissiondate' },
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
				filter_delay: 500,
				style_class: "filter-240"
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
