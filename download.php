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
			The Vulkan Hardware Capability Viewer is open source, you can always build the most recent version yourself using the sources from <a href="https://github.com/SaschaWillems/VulkanCapsViewer">the repository</a>.<br>
		</div>
		<div class="page-header">
			<h3>Current stable release 3.0</h3>
			<ul>
				<li><a href="downloads/vulkancapsviewer_3_0_win64.zip">Windows x86-64 (zip)</a></li>
				<li>Linux
					<ul>
						<li><a href="downloads/vulkancapsviewer_3_0_linux64.AppImage">X11 x86-64</a> (AppImage)</li>
						<li><a href="downloads/vulkancapsviewer_3_0_linux64_wayland.AppImage">Wayland x86-64</a> (AppImage)</li>
					</ul>
				</li>
				<li>Android
					<ul>
						<li><a href="downloads/vulkancapsviewer_3_0_arm.apk">Android arm-v8 (apk)</a></li>
					</ul>
				<li><a href="downloads/vulkancapsviewer_3_0_osx.dmg">Mac OSX (dmg)</a></li>
			</ul>
		</div>
		<div class="page-header">
			<h3>Release notes</h3>
			<h4>3.0 - 2021-01-xx</h4>
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