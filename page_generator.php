<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2020-2021 by Sascha Willems (www.saschawillems.de)
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

class PageGenerator
{

	public static function header($title = null)
	{
		$page_title = $title;
		include 'header.php';
	}

	public static function footer()
	{
		include 'footer.php';
	}

	public static function errorMessage($message, $header = true, $footer = true, $end_script = true)
	{
		if ($header) {
			self::header();
?>
			<div class="div-h-center">
				<div class="div-alert alert alert-danger error">
					<?= $message ?>
				</div>
			</div>
<?php
			if ($footer) {
				self::footer();
			}
			if ($end_script) {
				die();
			}
		}
	}
}
