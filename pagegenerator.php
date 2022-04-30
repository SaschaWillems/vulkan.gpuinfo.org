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
	private static $platform_list = ['windows', 'linux', 'android', 'macos', 'ios'];

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

	public static function databaseErrorMessage()
	{
		self::header();
?>
		<div class="div-h-center">
			<div class="div-alert alert alert-danger error">
				<strong>Error while fetching data</strong><br/>
				A database error occured. Please try again, and if the error persists please submit the issue <a href="https://github.com/SaschaWillems/vulkan.gpuinfo.org">here</a>.
			</div>
		</div>
<?php
		self::footer();
		die();
	}	

	public static function platformInfo($platform)
	{
		if ($platform == 'all') {
			return " all platforms";
		}
		return "<img src='images/" . $platform . "logo.png' height='14px' style='padding-right:5px'/>" . ucfirst($platform);
	}

	public static function filterInfo()
	{
		$platform = null;
		if (isset($_GET['platform'])) {
			$platform = GET_sanitized('platform');
		}
		// @todo: also take from $_GET
		$apiversion = null;
		if (isset($_SESSION['minversion'])) {
			$apiversion = sanitize($_SESSION['minversion']);
		}
		$info = '';
		if ($platform && ($platform !== 'all')) {
			$info = "<img src='images/" . $platform . "logo.png' height='14px' style='padding-right:5px'/>" . ucfirst($platform);
		} else {
			$info = " all platforms";
		}
		if ($apiversion) {
			$info .= " Vulkan $apiversion (and up)";
		}
		return $info;
	}

	/**
	 * Inserts a set of platform navigation tabs
	 * 
	 * @param string $base_url Base url without parameters of the page where the navigation is inserted to
	 * @param string $active_platform Name of the platform whose tab will be activated
	 * @param bool $combined_tab If true, a combined tab with no platform filtering
	 * @param array[] $url_parameters Key/Value array (name => value) of url parameters to append to the navigation links
	 */	
	public static function platformNavigation($base_url, $active_platform, $combined_tab = false, $url_parameters = [])
	{
		// Construct url parameter string from additional url parameters (e.g. for filtered views)
		$url_parameter_string = null;
		if (count($url_parameters) > 0) {
			$idx = 0;
			foreach ($url_parameters as $key => $value) {
				// Ignore platform url parameter
				if (strcasecmp($key, 'platform') == 0) {
					continue;
				}
				if ($idx > 0) {
					$url_parameter_string .= '&';
				}
				$url_parameter_string .= "$key=$value";
				$idx++;
			}
		}

		echo "<div>";
		echo "	<ul class='nav nav-tabs'>";
		if ($combined_tab) {
			// Combined tab for all supported platforms
			$active = ($active_platform == 'all');
			$target_url = $base_url;
			// Append url parameters
			if ($url_parameter_string) {
				$target_url .= '?'.$url_parameter_string;
			}			
			echo "<li" . ($active ? ' class="active"' : null) . "><a href='$target_url'>All platforms</a> </li>";
		}
		foreach (self::$platform_list as $navplatform) {
			$active = ($active_platform == $navplatform);
			$icon_size = ($navplatform == 'windows') ? 14 : 16;
			$target_url = $base_url."?platform=".$navplatform;
			// Append url parameters
			if ($url_parameter_string) {
				$target_url .= '&'.$url_parameter_string;
			}			
			echo "<li" . ($active ? ' class="active"' : null) . "><a href='$target_url'><img src='images/" . $navplatform . "logo.png' height='".$icon_size."px' style='padding-right:5px'/>" . ucfirst($navplatform) . "</a> </li>";
		};
		echo "	</ul>";
		echo "</div>";
	}
}
