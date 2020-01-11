<?php 	
	/* 		
		*
		* Vulkan hardware capability database server implementation
		*	
		* Copyright (C) 2016-2017 by Sascha Willems (www.saschawillems.de)
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
	include 'page_generator.php';
	PageGenerator::header('Download');
?>

<div id='reportdiv'>	   
	<div class="panel panel-default">
		<div class="panel-body" style="margin-left:50px; width:65%px;">    
			<div class="page-header">
				<h2>Downloads</h2>
			</div>
			<div>
				The Vulkan Hardware Capability Viewer is open source, you can always build the most recent version yourself using the sources from <a href="https://github.com/SaschaWillems/VulkanCapsViewer">https://github.com/SaschaWillems/VulkanCapsViewer</a>.<br>
			</div>
			<div class="page-header">
				<h3>Current Version (1.4)</h3>
				<ul>
					<li><a href="downloads/vulkancapsviewer_1_4_win64.zip">Windows x86-64</a></li>
					<li><a href="downloads/vulkancapsviewer_1_4_linux64.tar.gz">Linux x86-64</a></li>
					<li>Android (<a href="https://play.google.com/store/apps/details?id=de.saschawillems.vulkancapsviewer">Google Playstore</a>)
					<ul>
						<li><a href="downloads/vulkancapsviewer_1_4_arm.apk">Android arm-v7 Beta</a></li>
						<li><a href="downloads/vulkancapsviewer_1_4_x86.apk">Android x86</a></li>
					</ul>
				</ul>
			</div>			
			<div class="page-header">
				<h3>Changelog</h3>
				<h4>1.4 - 2017-05-28</h4>
					<ul>
						<li>Added support for new features and properties provided via&nbsp;VK_KHR_GET_PHYSICAL_DEVICE_PROPERTIES_2
					<ul>
						<li>Currently supported:
					<ul>
						<li>VK_KHR_push_descriptor</li>
						<li>VK_EXT_discard_rectangles</li>
						<li>VK_KHX_multiview</li>
						<li>VK_NVX_multiview_per_view_attributes</li>
					</ul>
					</li>
					</ul>
					</li>
						<li>Reports can now be saved to disk via command line using the -s argument (without invoking the UI)
					<ul>
						<li>Example : vulkanCapsViewer -s my_device.json</li>
					</ul>
					</li>
						<li>Added pipeline cache UUID</li>
						<li>Exported JSON is now compatible with vkjson_info from LunarG SDK</li>
						<li>Added Android platform info
					<ul>
						<li>Device model and manufacturer</li>
						<li>Build information</li>
					</ul>
					</li>
						<li>UI improvements&nbsp;
					<ul>
						<li>Updated layout and UI scaling&nbsp;</li>
						<li>Support for high DPI scaling</li>
						<li>Better touch support for mobile devices</li>
					</ul>
					</li>
					</ul>
				<hr>
				<h4>Beta 1.2 - 2016-11-19</h4>
					<ul>
						<li><b>New feature: </b> Added support for os-specific surface capabilities</li>
					</ul>
				<hr>
				<h4>Beta 1.1 - 2016-04-03</h4>
					<ul>
						<li><b>Fixed : </b>Memory type flags now displayed correct</li>
						<li><b>Fixed : </b>Assign queue priorities (fixes potential crashes)</li>
						<li>Display and upload API driver version (integer)</li>
					</ul>
			</div>
		</div>    
	</div>
</div>

<?php
	PageGenerator::footer();
?>

</body>
</html>

