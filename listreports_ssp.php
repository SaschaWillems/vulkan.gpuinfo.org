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

	include './dbconfig.php';
	include './header.inc';	
	include './functions.php';	

	dbConnect();

?>

<center>
	<div class="tablediv">	
		<form method="get" action="compare.php?compare">	
		<table id='reports' class='table table-striped table-bordered table-hover responsive' style='width:auto;'>
			<thead>
				<tr>
					<th></th>
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
					<td>id</td>
					<td>Device</td>
					<td>Driver</td>
					<td>Api</td>
					<td>Vendor</td>
					<td>Type</td>
					<td>OS</tdth>
					<td>Version</td>
					<td>Platform</td>
					<td>Compare</td>
				</tr>
			</thead>		
		</table>
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
			"dom": 'lrtip',	
			"pageLength" : 25,		
			"order": [[ 0, 'desc' ]],
			"columnDefs": [
				{ "orderable": false, "targets": 9 }
			],
			"ajax": {
				url :"responses/listreports.php",
				type: "post",
			},
			"columns": [
				{ data: 'id' },
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
			if ((i != 0) && (i != 9)) {
				var title = $('#reports thead th').eq( $(this).index() ).text();
				var w = (i > 1) ? 120 : 240;
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
