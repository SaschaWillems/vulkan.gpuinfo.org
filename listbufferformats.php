<?php

/** 		
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright 2016-2022 (C) by Sascha Willems (www.saschawillems.de)
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

$platform = 'all';
if (isset($_GET['platform'])) {
	$platform = GET_sanitized('platform');
}

$minapiversion = null;
if (SqlRepository::getMinApiVersion() !== null) {
    $minapiversion = "_".str_replace(".", "_", SqlRepository::getMinApiVersion());
}

PageGenerator::header("Formats");
?>

<div class='header'>
	<?php echo "<h4>Buffer format support on ".PageGenerator::filterInfo($platform); ?>
</div>

<center>
	<?php 
		PageGenerator::platformNavigation('listbufferformats.php', $platform, true);
		include "./static/bufferformat_".$platform.$minapiversion.".html";
		PageGenerator::footer();
	?>
</center>

<script>
    $(document).ready(function() {
        var table = $('#formats').DataTable({
            pageLength: -1,
            paging: false,
            stateSave: false,
            searchHighlight: true,
            dom: 'f',
            bInfo: false,
            order: [
                [0, "asc"]
            ],
            columnDefs: [{
                    orderable: true,
                    targets: 0
                }
            ]
        });

        $("#searchbox").on("keyup search input paste cut", function() {
            table.search(this.value).draw();
        });

    });
</script>

</body>

</html>