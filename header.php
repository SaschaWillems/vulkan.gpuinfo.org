<html>
<head>
	<meta http-equiv="Content-Type" content="text/html" charset="ISO-8859-1">
	<?php echo "<title>".(isset($page_title) ? ($page_title." - Vulkan Hardware Database by Sascha Willems") : "Vulkan Hardware Database by Sascha Willems")."</title>"; ?>

	<link rel="stylesheet" type="text/css" href="external/css/bootstrap.min.css"/>
	<link rel="stylesheet" type="text/css" href="external/css/dataTables.bootstrap.min.css"/>
	<link rel="stylesheet" type="text/css" href="external/css/responsive.bootstrap.min.css"/>
	<link rel="stylesheet" type="text/css" href="external/bootstrap-toggle.min.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="external/css/fixedHeader.bootstrap.min.css" rel="stylesheet"/>

	<link rel="stylesheet" type="text/css" href="style.css">

	<script type="text/javascript" src="external/jquery-2.2.0.min.js"></script>
	<script type="text/javascript" src="external/bootstrap.min.js"></script>
	<script type="text/javascript" src="external/jquery.dataTables.min.js"></script>
	<script type="text/javascript" src="external/jquery.dataTables.yadcf.js"></script>
	<script type="text/javascript" src="external/dataTables.bootstrap.min.js"></script>
	<script type="text/javascript" src="external/bootstrap-toggle.min.js"></script>	
	<script type="text/javascript" src="external/dataTables.fixedHeader.min.js"></script>

<!--	<script type="text/javascript" src="external/dataTables.responsive.min.js"></script> -->
	<script type="text/javascript" src="external/responsive.bootstrap.min.js"></script>

	<script>
		$(document).ready(function () {
				$.each($('#navbar').find('li'), function() {
						$(this).toggleClass('active',
								'/' + $(this).find('a').attr('href') == window.location.pathname);
				});
		});
	</script>

	<meta name="twitter:card" content="summary" />
	<meta name="twitter:site" content="gpuinfo.org" />
	<meta name="twitter:creator" content="Sascha Willems" />

	<meta name="twitter:card" content="summary" />
	<meta name="twitter:site" content="@SaschaWillems2" />
	<meta name="twitter:title" content="Vulkan on gpuinfo.org" />
	<meta name="twitter:description" content="Vulkan hardware capability database." />
	<meta name="twitter:image" content="https://vulkan.gpuinfo.org/images/vulkanlogoscene.png" />
</head>

<body>
<!-- Bootstrap nav bar -->
	<nav class="navbar navbar-default navbar-fixed-top" id="navbar">
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
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">Core
							<span class="caret"></span></a>
							<ul class="dropdown-menu">
								<li><a href="listfeatures.php">Features</a></li>
								<li><a href="listlimits.php">Limits</a></li>
								<li><a href="list_properties_core.php">Properties</a></li>
							</ul>
					</li>
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">Extensions
							<span class="caret"></span></a>
							<ul class="dropdown-menu">
								<li><a href="listextensions.php">List</a></li>
								<li><a href="list_features_extensions.php">Features</a></li>
								<li><a href="list_properties_extensions.php">Properties</a></li>
							</ul>
					</li>
					<li><a href="listformats.php">Formats</a></li>
					<li><a href="listmemory.php">Memory</a></li>
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">Surface
							<span class="caret"></span></a>
						<ul class="dropdown-menu">
							<li><a href="listsurfaceformats.php">Formats</a></li>
							<li><a href="listsurfacepresentmodes.php">Present modes</a></li>
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
					<!-- <li><a href="vulkansupport.php">Devices</a></li> -->
					<li><a href="download.php">Download</a></li>
					<li><a href="about.php">About</a></li>
				</ul>
				<ul class="nav navbar-nav navbar-right">
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">gpuinfo.org
							<span class="caret"></span></a>
						<ul class="dropdown-menu">
							<li><a href="https://opengl.gpuinfo.org">OpenGL</a></li>
							<li><a href="https://opengles.gpuinfo.org">OpenGL ES</a></li>
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