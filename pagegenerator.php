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
	private static $platform_list = ['windows', 'linux', 'android'];

	public static function header($title = null)
	{
		$page_title = $title;
		include './includes/header.php';
	}

	public static function footer()
	{
		include './includes/footer.php';
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

	public static function platformInfo($platform)
	{
		return "<img src='images/" . $platform . "logo.png' height='14px' style='padding-right:5px'/>" . ucfirst($platform);
	}

	public static function platformNavigation($base_url, $active_platform, $combined_tab = false)
	{
		echo "<div>";
		echo "	<ul class='nav nav-tabs'>";
		if ($combined_tab) {
			$active = ($active_platform == 'all');
			echo "<li" . ($active ? ' class="active"' : null) . "><a href='$base_url'>All platforms</a> </li>";
		}
		foreach (self::$platform_list as $navplatform) {
			$active = ($active_platform == $navplatform);
			$icon_size = ($navplatform == 'windows') ? 14 : 16;
			echo "<li" . ($active ? ' class="active"' : null) . "><a href='$base_url?platform=$navplatform'><img src='images/" . $navplatform . "logo.png' height='".$icon_size."px' style='padding-right:5px'/>" . ucfirst($navplatform) . "</a> </li>";
		};
		echo "	</ul>";
		echo "</div>";
	}
}
