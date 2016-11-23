<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset="ISO-8859-1">
	<title>Vulkan Hardware Database - � 2016 by Sascha Willems</title>	
	
	<link rel="stylesheet" type="text/css" href="external/css/bootstrap.min.css"/>
	<link rel="stylesheet" type="text/css" href="external/css/dataTables.bootstrap.min.css"/>
	<link rel="stylesheet" type="text/css" href="external/css/fixedHeader.bootstrap.min.css"/>
	<link rel="stylesheet" type="text/css" href="external/css/responsive.bootstrap.min.css"/>

	<link rel="stylesheet" type="text/css" href="style.css">

	<script type="text/javascript" src="external/jquery-2.2.0.min.js"></script>
	<script type="text/javascript" src="external/bootstrap.min.js"></script>
	<script type="text/javascript" src="external/jquery.dataTables.min.js"></script>
	<script type="text/javascript" src="external/dataTables.bootstrap.min.js"></script>
	<script type="text/javascript" src="external/dataTables.fixedHeader.min.js"></script>
<!--	<script type="text/javascript" src="external/dataTables.responsive.min.js"></script> -->
	<script type="text/javascript" src="external/responsive.bootstrap.min.js"></script>

</head>

<body>
<!-- Bootstrap nav bar -->
	<nav class="navbar navbar-default navbar-fixed-top">
	  <div class="container-fluid">
		<div class="navbar-header">
		  <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span> 
		  </button>
		  <a href="./listreports.php">
			<img src="./images/vulkan48.png" class="vulkanlogo">
		  </a>
		</div>
		<div class="collapse navbar-collapse" id="myNavbar">
		  <ul class="nav navbar-nav">
			<li><a href="listreports.php">Reports</a></li>
			<li><a href="listfeatures.php">Features</a></li> 
			<li><a href="listlimits.php">Limits</a></li> 
			<li><a href="listextensions.php">Extensions</a></li> 
			<li><a href="listformats.php">Formats</a></li>

		  <li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#">Surface
				<span class="caret"></span></a>
				<ul class="dropdown-menu">
				<li><a href="listsurfaceformats.php">Formats</a></li>
				<li><a href="listsurfacepresentmodes.php">Present modes</a></li>
				</ul>
			</li>

			<li><a href="download.php">Download</a></li> 
			<li><a href="about.php">About</a></li> 
		  </ul>
		  <ul class="nav navbar-nav navbar-right">
			  <li class="dropdown">
				  <a class="dropdown-toggle" data-toggle="dropdown" href="#">gpuinfo.org
				  <span class="caret"></span></a>
				  <ul class="dropdown-menu">
					<li><a href="http://opengl.gpuinfo.org">OpenGL</a></li>
					<li><a href="http://opengles.gpuinfo.org">OpenGL ES</a></li>
					<li><a href="http://vulkan.gpuinfo.org">Vulkan</a></li> 
                    <li role="separator" class="divider"></li>
					<li><a href="http://www.gpuinfo.org">Launchpad</a></li> 
				  </ul>
			  </li>
		  </ul>			  
		</div>
	  </div>
	</nav>