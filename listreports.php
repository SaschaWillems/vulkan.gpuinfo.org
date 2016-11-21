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
	
<?php
	$defaultHeader = true;
	$negate = false;
	$headerText = '';
	$headerClass = "header";
	$sqlWhere = '';
	$sqlSelectPre = '';
	$sqlOrderBy = 'order by id desc';
	$alertText = '';

	if ($_GET['option'] != '')
	{
		if ($_GET['option'] == 'not') 
		{
			$negate = true;
		}
	}
	
	// List by supported feature
	$feature = mysql_real_escape_string($_GET['feature']);	
	if ($feature != '')
	{
		$defaultHeader = false;
		if (!$negate)
		{
			$headerClass = "header-green";
			$headerText = "Listing all reports supporting <b>".$feature."</b>";		
			$sqlWhere = "where r.id in (select distinct(reportid) from devicefeatures df where df.".$feature." = 1)";
		}
		else
		{
			$headerClass = "header-red";
			$headerText = "Listing all reports not supporting <b>".$feature."</b>";		
			$sqlWhere = "where r.id in (select distinct(reportid) from devicefeatures df where df.".$feature." = 0)";
		}
	}
	
	// List by supported extensions
	$extension = mysql_real_escape_string($_GET['extension']);
	if ($extension != '') 
	{
		$defaultHeader = false;
		if (!$negate)
		{
			$headerClass = "header-green";		
			$headerText = "Listing all reports supporting <b>".$extension."</b>";		
			$sqlWhere = "where r.id in (select distinct(reportid) from deviceextensions de join extensions ext on de.extensionid = ext.id where ext.name = '".$extension."')";
		}
		else
		{
			$headerClass = "header-red";
			$headerText = "Listing all reports not supporting <b>".$extension."</b>";		
			$sqlWhere = "where r.id not in (select distinct(reportid) from deviceextensions de join extensions ext on de.extensionid = ext.id where ext.name = '".$extension."')";
		}
	}
	
	// List (and order) by limit
	$limit = mysql_real_escape_string($_GET['limit']);
	if ($limit != '')
	{
		$defaultHeader = false;
		$headerText = "Listing limits for <b>".$limit."</b>";		
		$sqlSelectPre = "(select `".$limit."` from devicelimits dl where dl.reportid = r.id) as value,";
		$sqlOrderBy = 'order by value desc';
	}
		
	// List by format support
	$linearformatfeature = mysql_real_escape_string($_GET['linearformat']);
	$optimalformatfeature = mysql_real_escape_string($_GET['optimalformat']);
	$bufferformatfeature = mysql_real_escape_string($_GET['bufferformat']);
	
	if ($linearformatfeature != '')
	{
		$defaultHeader = false;
		$headerClass = "header-green";				
		$headerText = "Listing all reports supporting <b>".$linearformatfeature."</b> for <b>linear tiling</b>";		
		$sqlWhere = "
			where id in (select reportid from deviceformats df join VkFormat vf on vf.value = df.formatid where vf.name = '".$linearformatfeature."' and df.lineartilingfeatures > 0)";
	}
	
	if ($optimalformatfeature != '')
	{
		$defaultHeader = false;
		$headerClass = "header-green";				
		$headerText = "Listing all reports supporting <b>".$optimalformatfeature."</b> for <b>optimal tiling</b>";		
		$sqlWhere = "
			where id in (select reportid from deviceformats df join VkFormat vf on vf.value = df.formatid where vf.name = '".$optimalformatfeature."' and df.optimaltilingfeatures > 0)";
	}
	
	if ($bufferformatfeature != '')
	{
		$defaultHeader = false;
		$headerClass = "header-green";				
		$headerText = "Listing all reports supporting <b>".$bufferformatfeature."</b> for <b>buffer usage</b>";		
		$sqlWhere = "
			where id in (select reportid from deviceformats df join VkFormat vf on vf.value = df.formatid where vf.name = '".$bufferformatfeature."' and df.bufferfeatures > 0)";
	}

	// List by surface format	
	$surfaceformat = mysql_real_escape_string($_GET['surfaceformat']);

	if ($surfaceformat != '')
	{
		$defaultHeader = false;
		$headerClass = "header-green";
		$sqlResult = mysql_query("select VkFormat(format) from devicesurfaceformats where format = ".$surfaceformat);
		$surfaceformatname = mysql_result($sqlResult, 0);
		$headerText = "Listing all reports supporting surface format <b>".$surfaceformatname."</b>";		
		$sqlWhere = "where id in (select reportid from devicesurfaceformats df where df.format = ".$surfaceformat.")";
		$alertText = "<b>Note:</b> Surface format data only available for reports with version 1.2 (or higher)";
	}

	// List by submitter
	$submitter = mysql_real_escape_string($_GET['submitter']);	
	if ($submitter != '')
	{
		$defaultHeader = false;
		$headerClass = "header-blue";
		$headerText = "Listing all reports submitted by <b>".$submitter."</b>";		
		$sqlWhere = "where r.submitter = '".$submitter."'";
	}
	
	if ($defaultHeader == true)
	{
		$sqlResult = mysql_query("SELECT count(*) FROM reports");
		$sqlCount = mysql_result($sqlResult, 0);
		$headerText = "Listing all reports (".$sqlCount.")";		
	}
	
	echo "<div class='".$headerClass."'>";	
	echo "	<h4>".$headerText."</h4>";
	echo "</div>";				
?>	
	

	
<div class="tablediv">

	<form method="get" action="compare.php?compare" style="margin-bottom:0px;">

			<?php
			
				$sqlresult = mysql_query("select 
					".$sqlSelectPre."
					r.id,
					p.devicename as Device,
					ifnull(p.driverversionraw, p.driverversion) as Driver,
					p.vendorid,
					p.apiversion as api,
					VendorId(p.vendorid) as 'Vendor',
					p.devicetype as 'Type',
					r.osname as 'OS',
					r.osversion as 'version',
					r.osarchitecture as platform
				from reports r
				left join
				deviceproperties p on (p.reportid = r.id) ".$sqlWhere." ".$sqlOrderBy) or die(mysql_error());							
				
				if ($alertText != "")
				{
					echo "<div class='alert alert-warning' role='alert'>".$alertText."</div>";
				}

				echo "<table id='reports' class='table table-striped table-bordered table-hover responsive' style='width:100%;'>";
				echo "<thead><tr>";
				
				for($i = 0; $i < mysql_num_fields($sqlresult); $i++)
				{
					$fname = mysql_field_name($sqlresult, $i);		  			
					if (($fname == 'id') | ($fname == 'vendorid'))
					{
						continue;
					}
					echo "<td class='caption'>$fname</td>\n";
				}				

				echo "<td class='caption' width='50px' align=center><input type='submit' name='compare' value='compare'></td>";			
				
				echo "</tr></thead><tbody>";					
				
				while($row = mysql_fetch_row($sqlresult))
				{
					echo "<tr>";
					$reportid = $row[0];
					for($i = 0; $i < count($row); $i++)
					{
						$fname = mysql_field_name($sqlresult, $i);		  			
						if (($fname == 'id') | ($fname == 'vendorid'))
							continue;
						$value = $row[$i];

						if ($fname == 'Device') 
						{
							echo "<td><a href='displayreport.php?id=$reportid'>$value</a></td>\n";
							continue;
						}
						
						if ($fname == 'Type') 
						{
							$value = strtolower(str_replace('_GPU', '', $value));
						}
						
						if ($fname == 'Driver')
						{
							if (ctype_digit($value))
							{
								$value = getDriverVerson($value, "", $row[$i+1]);
							}
							else
							{
								$value = "<font color='#ABABAB'>".$value."</span>";
							}
						}
												
						echo "<td>".$value."</td>\n";
					}			
					echo "	<td align='center'><input type='checkbox' name='id[$reportid]'></td>";					
					echo "</tr>";
				}
				
				echo "</tbody></table>";					
			?>

	</form>

	<script>
		$(document).ready(function() {
			var table = $('#reports').DataTable({
				"deferRender": true,
				"pageLength" : -1,
				"lengthChange" : false,
				"paging" : true,				
				"pageLength" : 25,
				"bInfo": false,
				"stateSave": false,
				"searchHighlight": true,
				"order": [],
				//"lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
				//"orderCellsTop": true,
				"sDom": 'flipt',
				columnDefs: [  { orderable: false, targets: [7] }, {type: 'natural-nohtml', targets: 2} ],						
				
				initComplete: function () {
					var api = this.api();
								
					api.columns().indexes().flatten().each( function ( i ) 
					{
						if (i < 8) 
						{						
							var column = api.column( i );
							var title = column.header().innerText;
							var select = $('<br><select onclick="stopPropagation(event);" style="padding-right:10px;"><option value=""></option></select>')
							.appendTo( $(column.header()) )
							.on( 'change', function () 
							{
								var val = $.fn.dataTable.util.escapeRegex($(this).val());

								column
								.search( val ? '^'+val+'$' : '', true, false )
								.draw();
							} );	

							column.data().unique().sort().each( function ( d, j ) 
							{
								var regex = /(<([^>]+)>)/ig
								var text = d.replace(regex, "");
								select.append( '<option value="'+text+'">'+text+'</option>' )
							} );
						};
					} );
				}

			});
		
		
		} );
		
	  function stopPropagation(evt) {
			if (evt.stopPropagation !== undefined) {
				evt.stopPropagation();
			} else {
				evt.cancelBubble = true;
			}
		}		
	</script>

	</center>
</div>

<?php include './footer.inc'; ?>

</body>
</html>
