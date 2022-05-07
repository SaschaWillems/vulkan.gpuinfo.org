<?php

/** 		
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright 2016-2021 (C) by Sascha Willems (www.saschawillems.de)
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

//@todo: Show hint that this view has been deprecated and link to the new explicit views

include 'pagegenerator.php';
include './database/database.class.php';
include './includes/functions.php';

$platform = 'all';
if (isset($_GET['platform'])) {
	$platform = GET_sanitized('platform');
}

PageGenerator::header("Formats");
?>

<div class='header'>
	<?php echo "<h4>Image and buffer format support on ".PageGenerator::filterInfo($platform); ?>
</div>

<center>
	<div class="div-h-center">
		<div class="div-alert alert alert-danger error">
			This listing has been deprecated in favour of dedicated listings for different format types:
			<ul style="list-style-type:none;">
				<li><a href="listlineartilingformats.php">Linear image tiling format support</a></li>
				<li><a href="listoptimaltilingformats.php">Optimal image tiling format support</a></li>
				<li><a href="listbufferformats.php">Buffer format support</a></li>
			</ul>
		</div>
	</div>	
	<?php PageGenerator::footer(); ?>
</center>
</body>

</html>