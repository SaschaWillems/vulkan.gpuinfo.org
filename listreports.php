<?php
	/* 		
		*
		* Vulkan hardware capability database server implementation
		*	
		* Copyright (C) 2016-2017 by Sascha Willems (www.saschawillems.de)
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

?>

<center>

<?php
	// Header
	$defaultHeader = true;
	$alertText = null;	
    $negate = false;
	if (isset($_GET['option'])) {
		if ($_GET['option'] == 'not') {
			$negate = true;
		}
    }	
	// Extension
	$extension = $_GET['extension'];
	if ($extension != '') {
		$defaultHeader = false;
		$headerClass = $negate ? "header-red" : "header-green";			
		$caption = "Reports ".($negate ? "<b>not</b>" : "")." supporting <b>".$extension."</b>";	
		$caption .= " (<a href='listreports.php?extension=".$extension.($negate ? "" : "&option=not")."'>toggle</a>)";
	}
	// Feature
	$feature = $_GET['feature'];	
	if ($feature != '') {
		$defaultHeader = false;
		$headerClass = $negate ? "header-red" : "header-green";			
		$caption = "Reports ".($negate ? "<b>not</b>" : "")." supporting <b>".$feature."</b>";	
		$caption .= " (<a href='listreports.php?feature=".$feature.($negate ? "" : "&option=not")."'>toggle</a>)";
	}	
	// Submitter
	$submitter = $_GET['submitter'];	
	if ($submitter != '') {
		$defaultHeader = false;
		$headerClass = "header-blue";
		$caption = "Reports submitted by <b>".$submitter."</b>";		
	}		
	// Format support
	$linearformatfeature = $_GET['linearformat'];
	if ($linearformatfeature != '') {
		$defaultHeader = false;
		$headerClass = $negate ? "header-red" : "header-green";				
		$caption = "Reports ".($negate ? "<b>not</b>" : "")." supporting <b>".$linearformatfeature."</b> for <b>linear tiling</b>";		
		$caption .= " (<a href='listreports.php?linearformat=".$linearformatfeature.($negate ? "" : "&option=not")."'>toggle</a>)";				
	}	
	$optimalformatfeature = $_GET['optimalformat'];
	if ($optimalformatfeature != '') {
		$defaultHeader = false;
		$headerClass = $negate ? "header-red" : "header-green";				
		$caption = "Reports ".($negate ? "<b>not</b>" : "")." supporting <b>".$optimalformatfeature."</b> for <b>optimal tiling</b>";		
		$caption .= " (<a href='listreports.php?optimalformat=".$optimalformatfeature.($negate ? "" : "&option=not")."'>toggle</a>)";				
	}
	$bufferformatfeature = $_GET['bufferformat'];	
	if ($bufferformatfeature != '') {
		$defaultHeader = false;
		$headerClass = $negate ? "header-red" : "header-green";				
		$caption = "Reports ".($negate ? "<b>not</b>" : "")." supporting <b>".$bufferformatfeature."</b> for <b>buffer usage</b>";		
		$caption .= " (<a href='listreports.php?bufferformat=".$bufferformatfeature.($negate ? "" : "&option=not")."'>toggle</a>)";				
	}	
	// List (and order) by limit
	$limit = $_GET['limit'];
	if ($limit != '') {
		$defaultHeader = false;
		$headerClass = "header-green";
		$caption = "Listing limits for <b>".$limit."</b>";		
		// Check if a limit requirement rule has to be applied (see Table 36. of the specs)
		DB::connect();	
		$sql = "select feature from limitrequirements where limitname = :limit";  
		$reqs = DB::$connection->prepare($sql);
		$reqs->execute(array(":limit" => $limit));
		if ($reqs->rowCount() > 0) {
			$req = $reqs->fetch();
			$caption .= "<br>(Feature requirement ".$req["feature"]." is applied)";
		}
		DB::disconnect();
	}	
	// Surface format	
	$surfaceformat = $_GET['surfaceformat'];
	if ($surfaceformat != '') {
		$defaultHeader = false;
		$headerClass = $negate ? "header-red" : "header-green";	
		$caption = "Reports ".($negate ? "<b>not</b>" : "")." supporting surface format <b>".$surfaceformat."</b>";		
		$alertText = "<b>Note:</b> Surface format data only available for reports with version 1.2 (or higher)";
		$caption .= " (<a href='listreports.php?surfaceformat=".$surfaceformat.($negate ? "" : "&option=not")."'>toggle</a>)";		
	}
	// Surface present mode	
	$surfacepresentmode = $_GET['surfacepresentmode'];
	if ($surfacepresentmode != '') {
		$defaultHeader = false;
		$headerClass = $negate ? "header-red" : "header-green";	
		$caption = "Reports ".($negate ? "<b>not</b>" : "")." supporting surface present mode <b>".getPresentMode($surfacepresentmode)."</b>";		
		$alertText = "<b>Note:</b> Surface present mode data only available for reports with version 1.2 (or higher)";
		$caption .= " (<a href='listreports.php?surfacepresentmode=".$surfacepresentmode.($negate ? "" : "&option=not")."'>toggle</a>)";		
	}		
	// Device name
	$devicename = $_GET['devicename'];
	if ($devicename != '') {
		$defaultHeader = false;
		$headerClass = "header-blue";
		$caption = "Reports for <b>".$devicename."</b>";		
	}		
	// Display name (Android devices)
	$displayname = $_GET['displayname'];
	if ($displayname != '') {
		$defaultHeader = false;
		$headerClass = "header-blue";
		$caption = "Reports for <b>".$displayname."</b>";		
	}		
	// Instance extension
	$instanceextension = $_GET['instanceextension'];
	if ($instanceextension != '') {
		$defaultHeader = false;
		$headerClass = $negate ? "header-red" : "header-green";			
		$caption = "Reports ".($negate ? "<b>not</b>" : "")." supporting instance extension <b>".$instanceextension."</b>";	
		$caption .= " (<a href='listreports.php?instanceextension=".$instanceextension.($negate ? "" : "&option=not")."'>toggle</a>)";
	}
	// Instance layer
	$instancelayer = $_GET['instancelayer'];
	if ($instancelayer != '') {
		$defaultHeader = false;
		$headerClass = $negate ? "header-red" : "header-green";			
		$caption = "Reports ".($negate ? "<b>not</b>" : "")." supporting instance layer <b>".$instancelayer."</b>";	
		$caption .= " (<a href='listreports.php?instancelayer=".$instancelayer.($negate ? "" : "&option=not")."'>toggle</a>)";
	}	

	if ($defaultHeader) {
		echo "<div class='header'>";	
		echo "	<h4>Listing reports</h4>";
		echo "</div>";		
	}	
?>

	<div class="tablediv">	

		<form method="get" action="compare.php?compare">	
		<table id='reports' class='table table-striped table-bordered table-hover responsive' style='width:auto'>
			<?php
				if (!$defaultHeader) {
					echo "<caption class='".$headerClass." header-span'>".$caption."</caption>";
				}
			?>
			<thead>
				<tr>
					<th></th>
					<?php if (isset($_GET["limit"])) echo "<th>limit</th>" ?>
					<th>device</th>
					<th>driver</th>
					<th>api</th>
					<th>vendor</th>
					<th>type</th>
					<th>os</thth>
					<th>version</th>
					<th>platform</th>
					<th><input type='submit' name='compare' value='compare' class='button'></th>
				</tr>
				<tr>
					<th>id</th>
					<?php if (isset($_GET["limit"])) echo "<th>Limit</th>" ?>
					<th>Device</th>
					<th>Driver</th>
					<th>Api</th>
					<th>Vendor</th>
					<th>Type</th>
					<th>OS</th>
					<th>Version</th>
					<th>Platform</th>
					<th></th>
				</tr>
			</thead>		
		</table>
		<div id="errordiv" style="color:#D8000C;"></div>		
		</form>
	</div>
</center>

<script>
	$( document ).ready(function() {

		var table = $('#reports').DataTable({
			"processing": true,
			"serverSide": true,
			"paging" : true,		
			"searching": true,	
			"lengthChange": false,
			"dom": 'lrtip',	
			"pageLength" : 25,		
			"order": [[ 0, 'desc' ]],
			"columnDefs": [
				{ 
					"searchable": false, "targets": [ 0, <?php echo (isset($_GET["limit"])) ? "10" : "9" ?>] ,
					"orderable": false, "targets": <?php echo (isset($_GET["limit"])) ? "10" : "9" ?>,					
			    }
			],
			"ajax": {
				url :"responses/listreports.php",
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
						'instanceextension': '<?php echo $_GET["instanceextension"] ?>',
						'instancelayer': '<?php echo $_GET["instancelayer"] ?>'
					}
				},
				error: function (xhr, error, thrown) {
					$('#errordiv').html('Could not fetch data (' + error + ')');
					$('#reports_processing').hide();
				}				
			},
			"columns": [
				{ data: 'id' },
				<?php if (isset($_GET["limit"])) echo "{ data: 'devicelimit'},\n" ?>
				{ data: 'device' },
				{ data: 'driver' },
				{ data: 'api' },
				{ data: 'vendor' },
				{ data: 'devicetype' },
				{ data: 'osname' },
				{ data: 'osversion' },
				{ data: 'osarchitecture' },
				{ data: 'compare' },
			],
			// Pass order by column information to server side script
			fnServerParams: function(data) {
				data['order'].forEach(function(items, index) {
					data['order'][index]['column'] = data['columns'][items.column]['data'];
				});
			},
		});   

		// Per-Column filter boxes
		$('#reports thead th').each( function (i) {
			var title = $('#reports thead th').eq( $(this).index() ).text();
			if ((title !== 'id') && (title !== '')) {
				var w = (title != 'device') ? 120 : 240;
				$(this).html( '<input type="text" placeholder="'+title+'" data-index="'+i+'" style="width: '+w+'px;" class="filterinput" />' );
			}
		}); 
		$(table.table().container() ).on('keyup', 'thead input', function () {
			table
				.column($(this).data('index'))
				.search(this.value)
				.draw();
		});		

	});
</script>

<?php include './footer.inc'; ?>

</body>
</html>
