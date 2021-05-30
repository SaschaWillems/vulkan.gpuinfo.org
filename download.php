<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2021 by Sascha Willems (www.saschawillems.de)
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
PageGenerator::header('Download');
?>

<div class="panel panel-default">
	<div class="panel-body" style="margin-left:50px; width:65%px;">
		<div class="page-header">
			<h2>Downloads</h2>
		</div>
		<div>
			The database is populated using the Vulkan Hardware Capability Viewer application, available for multiple platforms. It reads and displays Vulkan related information for a selected implementation, and that data can then be uploaded to the database.
			<!-- <div>
				<a href="images/hardware_capability_viewer_3.0.png"><img src="images/hardware_capability_viewer_3.0.png" width="512px"></a>
			</div> -->
			The Vulkan Hardware Capability Viewer is open source, you can always build the most recent version yourself using the sources from <a href="https://github.com/SaschaWillems/VulkanCapsViewer">the repository</a>.<br>
		</div>
		<div class="page-header">
			<h3>Current stable release 3.01</h3>
			<ul>		
				<li>Windows
					<ul>
						<li><a href="downloads/vulkancapsviewer_3.01_win64.zip">Windows 64-bit (zip)</a></li>
						<li><a href="downloads/vulkancapsviewer_3.01_x86.zip">Windows 32-bit (zip)</a><br/><b>Please note:</b> The 32-bit windows release should only be run on platforms that don't support 64-bit!<br/>Some Vulkan implementations may not expose all hardware capabilities when run under 32 bits.</li>
					</ul>
				</li>
				<li>Linux
					<ul>
						<li><a href="downloads/vulkancapsviewer_3.01_linux64.AppImage">X11 x86-64</a> (AppImage)</li>
						<li><a href="downloads/vulkancapsviewer_3.01_linux64_wayland.AppImage">Wayland x86-64</a> (AppImage)</li>
					</ul>
				</li>
				<li>Android
					<ul>
						<li><a href="downloads/vulkancapsviewer_3.01_arm.apk">Android arm-v8 (apk)</a></li>
					</ul>
				<li><a href="downloads/vulkancapsviewer_3.01_osx.dmg">Mac OSX (dmg)</a></li>
				<li><a href="https://apps.apple.com/us/app/vulkan-capabilities-viewer/id1552796816">iOS (App Store, provided by <a href="https://www.lunarg.com/">LunarG</a>)</a></li>
			</ul>
		</div>
		<div class="page-header">
			<h3>Release notes</h3>
			<h4>3.01 - 2021-04-24</h4>
			<ul>
				<li>Added VK_KHR_video_queue encode and decode flags to queue family display</li>
				<li>Restructured memory heaps and types display</li>
				<li>Added support for new features and properties provided via&nbsp;VK_KHR_GET_PHYSICAL_DEVICE_PROPERTIES_2:
					<ul>
						<li>VK_EXT_ycbcr_2plane_444_formats</li>
						<li>VK_EXT_vertex_input_dynamic_state</li>
						<li>VK_EXT_color_write_enable</li>
						<li>VK_KHR_synchronization2</li>
						<li>VK_KHR_zero_initialize_workgroup_memory</li>
						<li>VK_KHR_workgroup_memory_explicit_layout</li>
						<li>VK_NV_inherited_viewport_scissor</li>
					</ul>					
				</li>
			</ul>
			<h4>3.0 - 2021-01-10</h4>
			<ul>
				<li>Added update mechanism for updating reports present in the database with data from newer application versions</li>
				<li>Added dedicated Vulkan Core 1.1 and Core 1.2 features and properties (requires Vulkan 1.2+ implementation)</li>
				<li>Restructured layout, moved Core 1.0 limits into properties page</li>
				<li>Added support for new features and properties provided via&nbsp;VK_KHR_GET_PHYSICAL_DEVICE_PROPERTIES_2:
					<ul>
						<li>VK_NV_fragment_shading_rate_enums</li>
						<li>VK_VALVE_mutable_descriptor_type</li>
					</ul>
				</li>
			</ul>
			<h4>2.23 - 2020-11-23</h4>
			<ul>
				<li>Added support for new features and properties provided via&nbsp;VK_KHR_GET_PHYSICAL_DEVICE_PROPERTIES_2:
					<ul>
						<li>VK_KHR_acceleration_structure</li>
						<li>VK_KHR_ray_tracing_pipeline</li>
						<li>VK_KHR_ray_tracing</li>
						<li>VK_KHR_ray_query</li>
					</ul>
				</li>
			</ul>
			<h4>2.21 - 2020-09-26</h4>
			<ul>
				<li>Added support for new features and properties provided via&nbsp;VK_KHR_GET_PHYSICAL_DEVICE_PROPERTIES_2:
					<ul>
						<li>VK_KHR_portability_subset</li>
						<li>VK_EXT_4444_formats</li>
					</ul>
				</li>
			</ul>
			<h4>2.2 - 2020-08-02</h4>
			<ul>
				<li>Added support for new features and properties provided via&nbsp;VK_KHR_GET_PHYSICAL_DEVICE_PROPERTIES_2:
					<ul>
						<li>VK_EXT_fragment_density_map2</li>
						<li>VK_EXT_shader_atomic_float</li>
						<li>VK_EXT_extended_dynamic_state</li>
						<li>VK_EXT_private_data</li>
						<li>VK_EXT_image_robustness</li>
					</ul>
				</li>
			</ul>
			<h4>2.1 - 2020-03-17</h4>
			<ul>
				<li>Added support for new features and properties provided via&nbsp;VK_KHR_GET_PHYSICAL_DEVICE_PROPERTIES_2:
					<ul>
						<li>VK_KHR_ray_tracing</li>
						<li>VK_EXT_pipeline_creation_cache_control</li>
						<li>VK_NV_device_generated_commands</li>
						<li>VK_NV_device_diagnostics_config</li>
					</ul>
				<li>Several tweaks for formatting display values</li>
				</li>
			</ul>
			<h4>2.03 - 2019-12-02</h4>
			<ul>
				<li>Added support for new features and properties provided via&nbsp;VK_KHR_GET_PHYSICAL_DEVICE_PROPERTIES_2:
					<ul>
						<li>VK_KHR_performance_query</li>
						<li>VK_KHR_separate_depth_stencil_layouts</li>
						<li>VK_KHR_buffer_device_address</li>
					</ul>
				</li>
			</ul>
		</div>
	</div>
</div>

<?php
PageGenerator::footer();
?>

</body>

</html>