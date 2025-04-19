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
require 'pagegenerator.php';
PageGenerator::header('Download');
$release = '4.01';
$filename_prefix = "downloads/vulkancapsviewer_".$release;
?>

<div class="panel panel-default">
	<div class="panel-body" style="margin-left:50px; width:65%px;">
		<div class="page-header">
			<h2>Downloads</h2>
		</div>
		<div>
			The database is populated using the Vulkan Hardware Capability Viewer application, available for multiple platforms. It reads and displays Vulkan related information for a selected implementation, and that data can then be uploaded to the database.<br/>			
			The Vulkan Hardware Capability Viewer is open source and can be found in this public <a href="https://github.com/SaschaWillems/VulkanCapsViewer">repository</a>.<br>
		</div>
		<div class="page-header">
			<h3>Current release <?=$release?></h3>
			<ul>
				<li>Windows
					<ul>
						<li><a href="<?=$filename_prefix?>_win64.zip">Windows 64-bit (zip)</a></li>
						<li><a href="<?=$filename_prefix?>_winx86.zip">Windows 32-bit (zip)</a><br/><b>Please note:</b> The 32-bit windows release should only be run on platforms that don't support 64-bit!<br/>Some Vulkan implementations may not expose all hardware capabilities when run under 32 bits.</li>
					</ul>
				</li>
				<li>Linux
					<ul>
						<li><a href="<?=$filename_prefix?>_linux64.AppImage">X11 x86-64</a> (AppImage)</li>
						<li><a href="<?=$filename_prefix?>_linux64_wayland.AppImage">Wayland x86-64</a> (AppImage)</li>
					</ul>
				</li>
				<li>Android
					<ul>
						<li><a href="https://play.google.com/store/apps/details?id=de.saschawillems.vulkancapsviewer&hl=en_US">Install from GooglePlay</a> (Releases may take some time to get updated)</li>
						<li><a href="<?=$filename_prefix?>_arm.apk">Android arm-v8 (apk)</a></li>
					</ul>
				<!-- <li><a href="<?=$filename_prefix?>_osx.dmg">Mac OSX (dmg)</a></li> -->
				<li><a href="https://apps.apple.com/us/app/vulkan-capabilities-viewer/id1552796816">iOS (App Store)</a> (provided by <a href="https://www.lunarg.com/">LunarG</a>)</li>
			</ul>
		</div>
		<div class="page-header">
			<h3>Release notes</h3>
			<h4>4.01 - 2025-04-19</h4>
			<ul>
				<li>Vulkan header 1.4.313</li>
				<li>Added support for new features and properties provided via&nbsp;VK_KHR_GET_PHYSICAL_DEVICE_PROPERTIES_2:
					<ul>
						<li>VK_ARM_pipeline_opacity_micromap</li>
						<li>VK_EXT_fragment_density_map_offset</li>
						<li>VK_KHR_depth_clamp_zero_one</li>
						<li>VK_KHR_maintenance8</li>
						<li>VK_KHR_shader_bfloat16</li>
						<li>VK_KHR_video_maintenance2</li>
						<li>VK_NV_cluster_acceleration_structure</li>
						<li>VK_NV_cooperative_vector</li>
						<li>VK_NV_partitioned_acceleration_structure</li>
						<li>VK_NV_present_metering</li>
						<li>VK_NV_ray_tracing_linear_swept_spheres</li>
					</ul>
				</li>
				<li>Note for Linux users: Builds were created with Ubuntu 22.04 (instead of 20.04) and might need fuse to properly run.</li>
			</ul>				
			<h4>4.00 - 2024-12-06</h4>
			<ul>
				<li>Added support for Vulkan 1.4. (incl. update mechanism for updating reports)</li>
				<li>Vulkan header 1.4.303</li>
				<li>Added support for new features and properties provided via&nbsp;VK_KHR_GET_PHYSICAL_DEVICE_PROPERTIES_2:
					<ul>
						<li>VK_EXT_vertex_attribute_robustness</li>
						<li>VK_KHR_video_encode_av1</li>
						<li>VK_KHR_video_encode_quantization_map</li>
					</ul>
				</li>
				<li>Better handling of surface queries on Wayland</li>
			</ul>				
			<h4>3.43 - 2024-10-02</h4>
			<ul>
				<li>Vulkan header 1.3.296</li>
				<li>Added support for new features and properties provided via&nbsp;VK_KHR_GET_PHYSICAL_DEVICE_PROPERTIES_2:
					<ul>
						<li>VK_KHR_pipeline_binary</li>
						<li>VK_KHR_compute_shader_derivatives</li>
						<li>VK_EXT_device_generated_commands</li>
						<li>VK_EXT_depth_clamp_control</li>
						<li>VK_AMD_anti_lag</li>
						<li>VK_NV_command_buffer_inheritance</li>
					</ul>
				</li>
				<li>Fix for missing surface info on macOS and iOS (thanks to LunarG)</li>
			</ul>				
			<h4>3.41 - 2024-06-28</h4>
			<ul>
				<li>Vulkan header 1.3.289</li>
				<li>Added support for new features and properties provided via&nbsp;VK_KHR_GET_PHYSICAL_DEVICE_PROPERTIES_2:
					<ul>
						<li>VK_EXT_legacy_vertex_attributes</li>
						<li>VK_EXT_shader_replicated_composites</li>
						<li>VK_KHR_maintenance7</li>
						<li>VK_KHR_shader_relaxed_extended_instruction</li>
						<li>VK_MESA_image_alignment_control</li>
					</ul>
				</li>	
			</ul>			
			<h4>3.40 - 2024-03-31</h4>
			<ul>
				<li>Vulkan header 1.3.280</li>
				<li>Added support for new features and properties provided via&nbsp;VK_KHR_GET_PHYSICAL_DEVICE_PROPERTIES_2:
					<ul>
						<li>VK_EXT_map_memory_placed</li>
						<li>VK_NV_shader_atomic_float16_vector</li>
						<li>VK_NV_raw_access_chains</li>
						<li>VK_NV_ray_tracing_validation</li>
					</ul>
					<li>Updated Vulkan profiles library to SDK 1.3.280</li>
					<li>Minor changes to report JSON to avoid issues with e.g. large VkFormat feature values</li>
				</li>	
			</ul>
			<h4>3.33 - 2024-01-28</h4>
			<ul>
				<li>Vulkan header 1.3.276</li>
				<li>Added support for new features and properties provided via&nbsp;VK_KHR_GET_PHYSICAL_DEVICE_PROPERTIES_2:
					<ul>
						<li>VK_ANDROID_external_format_resolve</li>
						<li>VK_ARM_render_pass_striped</li>
						<li>VK_ARM_scheduling_controls</li>
						<li>VK_EXT_nested_command_buffer</li>
						<li>VK_IMG_relaxed_line_rasterization</li>
						<li>VK_KHR_dynamic_rendering_local_read</li>
						<li>VK_KHR_index_type_uint8</li>
						<li>VK_KHR_line_rasterization</li>
						<li>VK_KHR_maintenance6</li>
						<li>VK_KHR_shader_expect_assume</li>
						<li>VK_KHR_shader_float_controls2</li>
						<li>VK_KHR_shader_maximal_reconvergence</li>
						<li>VK_KHR_shader_quad_control</li>
						<li>VK_KHR_shader_subgroup_rotate</li>
						<li>VK_KHR_vertex_attribute_divisor</li>
						<li>VK_KHR_video_maintenance1</li>
						<li>VK_NV_cuda_kernel_launch</li>
						<li>VK_NV_extended_sparse_address_space</li>
						<li>VK_NV_per_stage_descriptor_set</li>
					</ul>
				</li>	
			</ul>
			<h4>3.32 - 2023-09-08</h4>
			<ul>
				<li>Vulkan header 1.3.264</li>
				<li>Added support for new features and properties provided via&nbsp;VK_KHR_GET_PHYSICAL_DEVICE_PROPERTIES_2:
					<ul>
						<li>VK_KHR_cooperative_matrix</li>
						<li>VK_EXT_frame_boundary</li>
						<li>VK_EXT_depth_bias_control</li>
						<li>VK_EXT_dynamic_rendering_unused_attachments</li>
						<li>VK_NV_descriptor_pool_overallocation</li>
						<li>VK_MSFT_layered_driver</li>
						<li>VK_QCOM_image_processing2</li>
						<li>VK_QCOM_filter_cubic_weights</li>
						<li>VK_QCOM_ycbcr_degamma</li>
						<li>VK_QCOM_filter_cubic_clamp</li>
						<li>VK_QNX_external_memory_screen_buffer</li>
					</ul>
				</li>	
				<li>Properly display image layouts for VK_EXT_host_image_copy</li>
				<li>Updated Vulkan profiles library</li>
			</ul>				
			<h4>3.31 - 2023-05-27</h4>
			<ul>
				<li>Enable VK_EXT_swapchain_colorspace if supported by the implementation
					<ul>
						<li>This will report additional surface formats (e.g. for HDR color spaces), esp. on Android</li>
						<li>Existing reports can be updated if new surface formats are reported with this version</li>
					</ul>
				</li>
				<li>Fixes a bug that stopped the application from working on certain Linux platforms using wayland</li>
			</ul>			
			<h4>3.30 - 2023-05-13</h4>
			<ul>
				<li><b>Important note for Linux users:</b> Due to changes with automated builds, this version may no longer work on older eol Linux versions (e.g. Ubuntu < 20)</li>
				<li>Vulkan header 1.3.250</li>
				<li>Added support for new features and properties provided via&nbsp;VK_KHR_GET_PHYSICAL_DEVICE_PROPERTIES_2:
					<ul>
						<li>VK_KHR_ray_tracing_position_fetch</li>
						<li>VK_EXT_attachment_feedback_loop_dynamic_state</li>
						<li>VK_EXT_shader_tile_image</li>
						<li>VK_EXT_shader_object</li>
						<li>VK_NV_displacement_micromap</li>						
					</ul>
				</li>	
			</ul>	

			<!-- Old version -->

			<details>
				<summary style="cursor: pointer;">Click to see release notes for old versions...</summary>
				<h4>3.29 - 2023-03-17</h4>
				<ul>
					<li>Added support for new features and properties provided via&nbsp;VK_KHR_GET_PHYSICAL_DEVICE_PROPERTIES_2:
						<ul>
							<li>VK_ARM_shader_core_properties</li>
							<li>VK_EXT_image_sliced_view_of_3d</li>
							<li>VK_EXT_pipeline_library_group_handles</li>
							<li>VK_QCOM_multiview_per_view_render_areas</li>
						</ul>
					</li>
					<li>Filtering will keep child values displayed</li>
					<li>Add new queue family flag bits</li>				
				</ul>
				<h4>3.28 - 2023-01-25</h4>
				<ul>
					<li>Added support for new features and properties provided via&nbsp;VK_KHR_GET_PHYSICAL_DEVICE_PROPERTIES_2:
						<ul>
							<li>VK_HUAWEI_cluster_culling_shader</li>
						</ul>
					</li>
					<li>Use Vulkan profiles library from LunarG SDK</li>
				</ul>
				<h4>3.27 - 2022-11-19</h4>
				<ul>
					<li>Added support for new features and properties provided via&nbsp;VK_KHR_GET_PHYSICAL_DEVICE_PROPERTIES_2:
						<ul>
							<li>VK_EXT_descriptor_buffer</li>
							<li>VK_ARM_shader_core_builtins</li>
							<li>VK_NV_copy_memory_indirect</li>
							<li>VK_NV_memory_decompression</li>
							<li>VK_NV_ray_tracing_invocation_reorder</li>
						</ul>
					</li>
				</ul>
				<h4>3.26 - 2022-09-30</h4>
				<ul>
					<li>Added support for new features and properties provided via&nbsp;VK_KHR_GET_PHYSICAL_DEVICE_PROPERTIES_2:
						<ul>
							<li>VK_EXT_opacity_micromap</li>
							<li>VK_EXT_extended_dynamic_state3</li>
							<li>VK_EXT_device_fault</li>
							<li>VK_EXT_device_address_binding_report</li>
							<li>VK_EXT_pipeline_protected_access</li>
							<li>VK_EXT_legacy_dithering</li>
							<li>VK_EXT_mutable_descriptor_type</li>
							<li>VK_EXT_rasterization_order_attachment_access</li>
							<li>VK_NV_present_barrier</li>
							<li>VK_NV_optical_flow</li>
						</ul>
					</li>
					<li>Updated Vulkan profiles library to the latest version</li>
					<li>Added detection for Windows 11</li>
					<li>Fixed a bug with large values for maxTimelineSemaphoreValueDifference</li>
					</li>
				</ul>
				<h4>3.25 - 2022-09-01</h4>
				<ul>
					<li>Added support for new features and properties provided via&nbsp;VK_KHR_GET_PHYSICAL_DEVICE_PROPERTIES_2:
						<ul>
							<li>VK_EXT_mesh_shader</li>
							<li>VK_EXT_depth_clamp_zero_one</li>
							<li>VK_EXT_rasterization_order_attachment_access</li>
						</ul>
					</li>
					<li>Updated Vulkan profiles library to the latest version</li>
				</ul>
				<h4>3.24 - 2022-08-06</h4>
				<ul>
					<li>Added support for new features and properties provided via&nbsp;VK_KHR_GET_PHYSICAL_DEVICE_PROPERTIES_2:
						<ul>
							<li>VK_EXT_pipeline_robustness</li>
							<li>VK_EXT_shader_module_identifier</li>
							<li>VK_EXT_attachment_feedback_loop_layout</li>
							<li>VK_EXT_multisampled_render_to_single_sampled</li>
							<li>VK_QCOM_image_processing</li>
							<li>VK_QCOM_tile_properties</li>
							<li>VK_SEC_amigo_profiling</li>
						</ul>
					</li>
					<li>Updated Vulkan profiles library to the latest version</li>
				</ul>
				<h4>3.23 - 2022-06-27</h4>
				<ul>
					<li>Added support for the VK_KHR_portability_enumeration extension on MacOS</li>
					<li>Added support for new features and properties provided via&nbsp;VK_KHR_GET_PHYSICAL_DEVICE_PROPERTIES_2:
						<ul>
							<li>VK_EXT_non_seamless_cube_map</li>
						</ul>
					</li>
				</ul>
				<h4>3.22 - 2022-05-26</h4>
				<ul>
					<li>Added support for new features and properties provided via&nbsp;VK_KHR_GET_PHYSICAL_DEVICE_PROPERTIES_2:
						<ul>
							<li>VK_EXT_image_2d_view_of_3d</li>
							<li>VK_EXT_image_compression_control</li>
							<li>VK_EXT_image_compression_control_swapchain</li>
							<li>VK_EXT_pipeline_properties</li>
							<li>VK_EXT_subpass_merge_feedback</li>
							<li>VK_KHR_ray_tracing_maintenance1</li>
							<li>VK_KHR_fragment_shader_barycentric</li>
							<li>VK_AMD_shader_early_and_late_fragment_tests</li>
						</ul>
					</li>
					<li>Disabled uploads when feature modifying tools are detected (e.g. an active profiles layer)</li>
				</ul>
				<h4>3.21 - 2022-04-03</h4>
				<ul>
					<li>Added support for new features and properties provided via&nbsp;VK_KHR_GET_PHYSICAL_DEVICE_PROPERTIES_2:
						<ul>
							<li>VK_EXT_graphics_pipeline_library</li>
							<li>VK_EXT_primitives_generated_query</li>
							<li>VK_VALVE_descriptor_set_host_mapping</li>
						</ul>
					</li>
				</ul>
				<h4>3.2 - 2022-02-19</h4>
				<ul>
					<li>Added support for Vulkan profiles</li>
				</ul>
				<h4>3.11 - 2022-01-28</h4>
				<ul>
					<li>Bugfix for large maxBufferSize values in Vulkan 1.3 core properties</li>
				</ul>
				<h4>3.1 - 2022-01-25</h4>
				<ul>
					<li>Added support for Vulkan 1.3 core features and properties</li>
					<li>Added support for new features and properties provided via&nbsp;VK_KHR_GET_PHYSICAL_DEVICE_PROPERTIES_2:
						<ul>
							<li>VK_QCOM_fragment_density_map_offset</li>
							<li>VK_KHR_global_priority</li>
							<li>VK_NV_linear_color_attachment</li>
						</ul>
					</li>
				</ul>
				<h4>3.05 - 2021-11-27</h4>
				<ul>
					<li>Added support for new features and properties provided via&nbsp;VK_KHR_GET_PHYSICAL_DEVICE_PROPERTIES_2:
						<ul>
							<li>VK_ARM_rasterization_order_attachment_access</li>
							<li>VK_KHR_dynamic_rendering</li>
							<li>VK_EXT_border_color_swizzle</li>
							<li>VK_EXT_image_view_min_lod</li>
							<li>VK_EXT_depth_clip_control</li>
						</ul>
					</li>
					<li>Fixed proxy settings not being properly applied and changed proxy type to http.	</li>
				</ul>
				<h4>3.04 - 2021-10-12</h4>
				<ul>
					<li>Added support for new features and properties provided via&nbsp;VK_KHR_GET_PHYSICAL_DEVICE_PROPERTIES_2:
						<ul>
							<li>VK_KHR_maintenance4</li>
							<li>VK_EXT_rgba10x6_formats</li>
							<li>VK_KHR_shader_integer_dot_product</li>
							<li>VK_EXT_primitive_topology_list_restart</li>
							<li>VK_EXT_pageable_device_local_memory</li>
							<li>VK_KHR_shader_integer_dot_product</li>
						</ul>
					</li>
					<li>Added support for PowerVR image formats</li>
				</ul>
				<h4>3.03 - 2021-08-22</h4>
				<ul>
					<li>Added support for new features and properties provided via&nbsp;VK_KHR_GET_PHYSICAL_DEVICE_PROPERTIES_2:
						<ul>
							<li>VK_EXT_shader_atomic_float2</li>
							<li>VK_HUAWEI_invocation_mask</li>
							<li>VK_KHR_present_wait</li>
							<li>VK_KHR_present_id</li>
						</ul>
					</li>
				</ul>
				<h4>3.02 - 2021-08-07</h4>
				<ul>
					<li>Fixed queue family present support info on Linux</li>
					<li>Added filtering on nested values</li>
					<li>Report upload no longer requires file-access</li>
					<li>Added 32 bit Windows build</li>
					<li>Added support for new features and properties provided via&nbsp;VK_KHR_GET_PHYSICAL_DEVICE_PROPERTIES_2:
						<ul>
							<li>VK_EXT_physical_device_drm</li>
							<li>VK_EXT_multi_draw</li>
							<li>VK_EXT_global_priority_query</li>
							<li>VK_KHR_shader_subgroup_uniform_control_flow</li>
							<li>VK_HUAWEI_subpass_shading</li>
							<li>VK_NV_ray_tracing_motion_blur</li>
							<li>VK_NV_external_memory_rdma</li>
							<li>VK_EXT_provoking_vertex</li>
							<li>VK_EXT_extended_dynamic_state2</li>
						</ul>
					</li>
				</ul>
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
			</details>
		</div>
	</div>
</div>

<?php
PageGenerator::footer();
?>

</body>

</html>