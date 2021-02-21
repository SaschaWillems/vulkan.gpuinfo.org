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

define('VULKAN_CORE_1_0_TEXT', 'Vulkan Core 1.0');
define('VULKAN_CORE_1_1_TEXT', 'Vulkan Core 1.1');
define('VULKAN_CORE_1_2_TEXT', 'Vulkan Core 1.2');

define('VK_API_VERSION_1_0', '1.0');
define('VK_API_VERSION_1_1', '1.1');
define('VK_API_VERSION_1_2', '1.2');

$queue_flag_bits = [
    0x0001 => "GRAPHICS_BIT",
    0x0002 => "COMPUTE_BIT",
    0x0004 => "TRANSFER_BIT",
    0x0008 => "SPARSE_BINDING_BIT",
    0x0010 => "PROTECTED_BIT",
];

$platforms = ['windows', 'linux', 'android'];

// @todo: move to constants
$device_format_flags_tiling = [
    0x0001 => "SAMPLED_IMAGE",
    0x0002 => "STORAGE_IMAGE",
    0x0004 => "STORAGE_IMAGE_ATOMIC",
    0x0080 => "COLOR_ATTACHMENT",
    0x0100 => "COLOR_ATTACHMENT_BLEND",
    0x0200 => "DEPTH_STENCIL_ATTACHMENT",
    0x0400 => "BLIT_SRC",
    0x0800 => "BLIT_DST",
    0x1000 => "SAMPLED_IMAGE_FILTER_LINEAR",
    0x4000 => "TRANSFER_SRC",
    0x8000 => "TRANSFER_DST",
];

$device_format_flags_buffer = [
    0x0008 => "UNIFORM_TEXEL_BUFFER",
    0x0010 => "STORAGE_TEXEL_BUFFER",
    0x0020 => "STORAGE_TEXEL_BUFFER_ATOMIC",
    0x0040 => "VERTEX_BUFFER",
];
