<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2023 Sascha Willems (www.saschawillems.de)
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
require './includes/constants.php';
include './includes/filterlist.class.php';

$filters = ['platform'];
$filter_list = new FilterList($filters);

$platform = 'all';
if (isset($_GET['platform'])) {
	$platform = GET_sanitized('platform');
}

$vulkan_html_registry = "https://registry.khronos.org/vulkan/specs/1.3-extensions/man/html/";

PageGenerator::header("Extensions");
?>

<div class='header'>
	<?php echo "<h4>Extension coverage for ".PageGenerator::filterInfo() ?>
</div>

<center>
	<?php PageGenerator::platformNavigation('listextensions.php', $platform, true); ?>

	<div class='tablediv' style='width:auto; display: inline-block;'>
		<table id="extensions" class="table table-striped table-bordered table-hover responsive" style='width:auto;'>
			<thead>
				<tr>
					<th></th>
					<th class="centered" colspan=2>Device coverage</th>
					<th class="centered">First seen</th>
					<th class="centered" colspan=2>Additional</th>
				</tr>
				<tr>
					<th>Extension</th>
					<th class="centered"><img src='images/icons/check.png' width=16px></th>
					<th class="centered"><img src='images/icons/missing.png' width=16px></th>
					<th class="centered"><abbr title="Date at when the extension was first submitted to the database for the current platform selection">Date</abbr></th>
					<th><abbr title="Extension-related features">F.</abbr></th>
					<th><abbr title="Extension-related properties">P.</abbr></th>
				</tr>
			</thead>
		</table>
	</div>

	<script>
		$(document).ready(function() {
			var table = $('#extensions').DataTable({
				pageLength: -1,
				paging: false,
				stateSave: false,
				searchHighlight: true,
				processing: true,
				dom: 'f',
				bInfo: false,
				fixedHeader: {
					header: true,
					headerOffset: 50
				},
				order: [
					[0, "asc"]
				],
				columnDefs: [{
					targets: [1, 2],
				}],
				ajax: {
					url: "api/internal/extensions.php",
					data: {
						"filter": {
							'platform': '<?= $filter_list->getFilter('platform') ?>',
						}
					},
					error: function(xhr, error, thrown) {
						$('#errordiv').html('Could not fetch data (' + error + ')');
						$('#extensions_processing').hide();
					}
				},
				columns: [
					{
						data: 'name'
					},
					{
						data: 'coverage',
						className: 'centered',
					},
					{
						data: 'coverageunsupported',
						className: 'centered',
					},
					{
						data: 'date',
						className: 'centered',
					},
					{
						data: 'features',
						className: 'centered',
					},
					{
						data: 'properties',
						className: 'centered',
					},
				],				
			});

		});
	</script>

	<?php PageGenerator::footer(); ?>

</center>
</body>

</html>