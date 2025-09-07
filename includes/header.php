<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *
 * Copyright (C) 2016-2025 by Sascha Willems (www.saschawillems.de)
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

session_set_cookie_params(0, '/', $_SERVER['SERVER_NAME']);
session_name('vulkan');
session_start();
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html" charset="UTF-8">
	<meta name="robots" content="noindex">
	<?php echo "<title>" . (isset($page_title) ? ($page_title . " - Vulkan Hardware Database by Sascha Willems") : "Vulkan Hardware Database by Sascha Willems") . "</title>"; ?>
	<link rel="icon" type="image/png" href="/images/Vulkan_LogoBug_32px_Nov17.png" sizes="32x32">
	<link rel="stylesheet" type="text/css" href="external/css/bootstrap.min.css" />
	<link rel="stylesheet" type="text/css" href="external/css/dataTables.bootstrap.min.css" />
	<link rel="stylesheet" type="text/css" href="external/css/responsive.bootstrap.min.css" />
	<link rel="stylesheet" type="text/css" href="external/bootstrap-toggle.min.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="external/css/fixedHeader.bootstrap.min.css" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" href="external/fontawesome/css/fontawesome.min.css" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" href="external/fontawesome/css/solid.min.css" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" href="style.css">
	<script type="text/javascript" src="external/jquery-2.2.0.min.js"></script>
	<script type="text/javascript" src="external/bootstrap.min.js"></script>
	<script type="text/javascript" src="external/jquery.dataTables.min.js"></script>
	<script type="text/javascript" src="external/jquery.dataTables.yadcf.js"></script>
	<script type="text/javascript" src="external/dataTables.bootstrap.min.js"></script>
	<script type="text/javascript" src="external/bootstrap-toggle.min.js"></script>
	<script type="text/javascript" src="external/dataTables.fixedHeader.min.js"></script>
	<script src="external/apexcharts/apexcharts.js"></script>
	<script type="text/javascript" src="external/responsive.bootstrap.min.js"></script>
	<script>
		$(document).ready(function() {
			$.each($('#navbar').find('li'), function() {
				$(this).toggleClass('active',
					'/' + $(this).find('a').attr('href') == window.location.pathname);
			});
		});
		$(window).resize(function() {
			$('body').css('padding-top', parseInt($('#main-navbar').css("height")));
		});
		$(window).load(function() {
			$('body').css('padding-top', parseInt($('#main-navbar').css("height")));
		});
	</script>
</head>
<body>
	<nav class="navbar navbar-default navbar-fixed-top" id="main-navbar">
		<div class="container-fluid">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a href="./listdevices.php">
					<img src="./images/vulkan48.png" class="vulkanlogo">
				</a>
			</div>
			<div class="collapse navbar-collapse" id="myNavbar">
				<ul class="nav navbar-nav">
					<li><a href="listdevices.php">Devices</a></li>
					<li><a href="listreports.php">Reports</a></li>
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">Properties<span class="caret"></span></a>
						<ul class="dropdown-menu">
							<li><a href="listpropertiescore10.php">Core 1.0</a></li>
							<li><a href="listpropertiescore11.php">Core 1.1</a></li>
							<li><a href="listpropertiescore12.php">Core 1.2</a></li>
							<li><a href="listpropertiescore13.php">Core 1.3</a></li>
							<li><a href="listpropertiescore14.php">Core 1.4</a></li>
							<li role="separator" class="divider"></li>
							<li><a href="listpropertiesextensions.php">Extensions</a></li>
						</ul>
					</li>
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">Features<span class="caret"></span></a>
						<ul class="dropdown-menu">
							<li><a href="listfeaturescore10.php">Core 1.0</a></li>
							<li><a href="listfeaturescore11.php">Core 1.1</a></li>
							<li><a href="listfeaturescore12.php">Core 1.2</a></li>
							<li><a href="listfeaturescore13.php">Core 1.3</a></li>
							<li><a href="listfeaturescore14.php">Core 1.4</a></li>
							<li role="separator" class="divider"></li>
							<li><a href="listfeaturesextensions.php">Extensions</a></li>
						</ul>
					</li>
					<li><a href="listextensions.php">Extensions</a></li>
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">Formats
							<span class="caret"></span></a>
						<ul class="dropdown-menu">
							<li><a href="listlineartilingformats.php">Linear tiling</a></li>
							<li><a href="listoptimaltilingformats.php">Optimal tiling</a></li>
							<li><a href="listbufferformats.php">Buffer</a></li>
						</ul>
					</li>
					<li><a href="listmemory.php">Memory</a></li>
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">Surface
							<span class="caret"></span></a>
						<ul class="dropdown-menu">
							<li><a href="listsurfaceformats.php">Formats</a></li>
							<li><a href="listsurfacepresentmodes.php">Present modes</a></li>
							<li><a href="listsurfaceusageflags.php">Usage flags</a></li>
							<li><a href="listsurfacetransformmodes.php">Transform modes</a></li>
							<li><a href="listsurfacecompositealphamodes.php">Composite alpha modes</a></li>
						</ul>
					</li>
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">Instance
							<span class="caret"></span></a>
						<ul class="dropdown-menu">
							<li><a href="listinstanceextensions.php">Extensions</a></li>
							<li><a href="listinstancelayers.php">Layers</a></li>
						</ul>
					</li>
					<li><a href="listprofiles.php">Profiles</a></li>
					<li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
					<li><a href="download.php"><i class="fas fa-download"> </i> Download</a></li>
					<li><a href="about.php"><i class="fas fa-question-circle"></i> About</a></li>
				</ul>
				<ul class="nav navbar-nav navbar-right">
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">gpuinfo.org
							<span class="caret"></span></a>
						<ul class="dropdown-menu">
							<li><a href="https://opengl.gpuinfo.org">OpenGL</a></li>
							<li><a href="https://opengles.gpuinfo.org">OpenGL ES</a></li>
							<li><a href="https://opencl.gpuinfo.org">OpenCL</a></li>
							<li><a href="https://vulkan.gpuinfo.org">Vulkan</a></li>
							<li role="separator" class="divider"></li>
							<li><a href="https://android.gpuinfo.org">Android</a></li>
							<li role="separator" class="divider"></li>
							<li><a href="https://www.gpuinfo.org">Launchpad</a></li>
						</ul>
					</li>
				</ul>
			</div>
		</div>
	</nav>
