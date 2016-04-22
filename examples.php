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
	include './header.inc';	
?>

<div id='reportdiv'>	   
	<div class="panel panel-default">
		<div class="panel-body" style="margin-left:50px; width:65%px;">    
			<div class="page-header">
				<h2>Vulkan Examples binaries</h2>
			</div>
			<div>
				These are the binaries for the Vulkan examples at <a href="https://github.com/SaschaWillems/Vulkan">https://github.com/SaschaWillems/Vulkan</a>.<br>
				They're not always up-to-date with the repository, so if you want the latests changes, you still need to build them from the repository.<br>
			</div>
			<div class="page-header">
				<h3><img src="images/windowslogo.png" height=32px style="margin-right:10px;">Windows</h3>							
			</div>
			<p>
				<b>Note :</b> The windows binaries require the media pack (see below) to be present for loading shaders, meshes and textures.<br><br>
				<a href="downloads/examples/vulkan_examples_windows_x64.7z">64-Bit Binaries (2016-03-28)</a> ~1.5 MBytes			
			</p>
			<div class="page-header">
				<h3><img src="images/linuxlogo.png" height=32px style="margin-right:10px;">Linux</h3>				
			</div>
			<p>
				<b>Note :</b> The linux binaries require the media pack (see below) to be present for loading shaders, meshes and textures.<br><br>
				<a href="downloads/examples/vulkan_examples_linux_x64.tar.gz">64-Bit Binaries (2016-03-28)</a> ~1.6 MBytes			
			</p>
			<div class="page-header">
				<h3><img src="images/androidlogo.png" height=32px style="margin-right:10px;">Android</h3>				
			</div>
			<p>
				<b>Note :</b>Requires a ARM device with Vulkan support. Gamepads are supported.<br>
				Assets required (shaders, textures, models) are part of each apk, the media pack is not requried.<br>
				The archive contains two batch files for easy installation and removal of all examples at once.<br><br>
				<a href="downloads/examples/vulkan_examples_android.7z">ARM Binaries (2016-03-28)</a> ~51 MBytes			
			</p>
			<div class="page-header">
				<h3>Media Pack</h3>				
			</div>			
			<p>
				<b>Note :</b> This archive contains all shaders, models and textures required to run the windows and linux examples. Put the data folder to where the bin folder for windows or linux has been extracted so the examples can find the required assets.<br><br>
				<a href="downloads/examples/vulkan_examples_mediapack.7z">64-Bit Binaries (2016-03-28)</a> ~20 MBytes			
			</p>
			<p>
			</p>
		</div>    
	</div>
</div>

<?php
	include './footer.inc';
?>

</body>
</html>

