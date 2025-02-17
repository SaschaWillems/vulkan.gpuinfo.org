<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2024 by Sascha Willems (www.saschawillems.de)
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

define('SESSION_NAME', 'vulkan');

define('VULKAN_REGISTRY_URL', "https://registry.khronos.org/vulkan/specs/1.3-extensions/man/html/");

$queue_flag_bits = [
    0x0001 => "GRAPHICS_BIT",
    0x0002 => "COMPUTE_BIT",
    0x0004 => "TRANSFER_BIT",
    0x0008 => "SPARSE_BINDING_BIT",
    0x0010 => "PROTECTED_BIT",
    0x0020 => "VIDEO_DECODE_BIT_KHR",
    0x0040 => "VIDEO_ENCODE_BIT_KHR",
];

$platforms = ['windows', 'linux', 'android'];

class FormatFeatureFlags {
    const TilingFlags = [
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

    const BufferFlags = [
        0x0008 => "UNIFORM_TEXEL_BUFFER",
        0x0010 => "STORAGE_TEXEL_BUFFER",
        0x0020 => "STORAGE_TEXEL_BUFFER_ATOMIC",
        0x0040 => "VERTEX_BUFFER",
        0x20000000 => "ACCELERATION_STRUCTURE_VERTEX_BUFFER",
    ];
}

class FormatFeatureFlags2 {
    const TilingFlags = [
        0x00000001     => "Sampled image",
        0x00000002     => "Storage image",
        0x00000004     => "Storage image atomic",
        0x00000080     => "Color attachment",
        0x00000100     => "Color attachment blend",
        0x00000200     => "Depth stencil attachment",
        0x00000400     => "Blit src",
        0x00000800     => "Blit dst",
        0x00001000     => "Sampled image filter linear",
        0x00002000     => "Sampled image filter cubic",
        0x00004000     => "Transfer src",
        0x00008000     => "Transfer dst",
        0x00010000     => "Sampled image filter minmax",
        0x00020000     => "Midpoint chroma samples",
        0x00040000     => "Sampled image ycbcr conversion<br> linear filter",
        0x00080000     => "Sampled image ycbcr conversion<br> separate reconstruction filter",
        0x00100000     => "Sampled image ycbcr conversion<br> chroma reconstruction explicit",
        0x00200000     => "Sampled image ycbcr conversion<br> chroma reconstruction explicit forceable",
        0x00400000     => "Disjoint",
        0x00800000     => "Cosited chroma samples",
        0x80000000     => "Storage read without format",
        0x100000000    => "Storage write without format",
        0x200000000    => "Sampled image depth comparison",
        0x02000000     => "Video decode output KHR",
        0x04000000     => "Video decode dpb KHR",
        0x01000000     => "Fragment density map EXT",
        0x40000000     => "Fragment shading rate attachment kHR",
        0x400000000000 => "Host image transfer EXT",
        0x08000000     => "Video encode input KHR",
        0x10000000     => "Video encode dpb KHR",
        // @todo: disable vendor specific extensions for now
        /*
        0x4000000000   => "Linear color attachment NV",
        0x400000000    => "Weight image QCOM",
        0x800000000    => "Weight sampled image QCOM",
        0x1000000000   => "Block matching QCOM",
        0x2000000000   => "Box filter sampled QCOM",
        0x10000000000  => "Optical flow image NV",
        0x20000000000  => "Optical flow vector NV",
        0x40000000000  => "Optical flow cost NV",
        */
    ];

    const BufferFlags = [
        0x00000008  => "Uniform texel buffer",
        0x00000010  => "Storage texel buffer",
        0x00000020  => "Storage texel buffer atomic",
        0x00000040  => "Vertex buffer",
        0x00004000  => "Transfer src",
        0x00008000  => "Transfer dst",
        0x20000000  => "Acceleration structure vertex buffer",
        0x80000000  => "Storage read without format",
        0x100000000 => "Storage write without format",
    ];    
}

class SurfaceConstants {
    const UsageFlags = [
        0x0001 => "TRANSFER_SRC_BIT",
        0x0002 => "TRANSFER_DST_BIT",
        0x0004 => "SAMPLED_BIT",
        0x0008 => "STORAGE_BIT",
        0x0010 => "COLOR_ATTACHMENT_BIT",
        0x0020 => "DEPTH_STENCIL_ATTACHMENT_BIT",
        0x0040 => "TRANSIENT_ATTACHMENT_BIT",
        0x0080 => "INPUT_ATTACHMENT_BIT",
    ];    
    const TransformFlags = [
        0x0001 => "IDENTITY_BIT_KHR",
        0x0002 => "ROTATE_90_BIT_KHR",
        0x0004 => "ROTATE_180_BIT_KHR",
        0x0008 => "ROTATE_270_BIT_KHR",
        0x0010 => "HORIZONTAL_MIRROR_BIT_KHR",
        0x0020 => "HORIZONTAL_MIRROR_ROTATE_90_BIT_KHR",
        0x0040 => "HORIZONTAL_MIRROR_ROTATE_180_BIT_KHR",
        0x0080 => "HORIZONTAL_MIRROR_ROTATE_270_BIT_KHR",
        0x0100 => "INHERIT_BIT_KHR",
    ];
    const CompositeAlphaFlags = [
        0x0001 => "OPAQUE_BIT_KHR",
        0x0002 => "PRE_MULTIPLIED_BIT_KHR",
        0x0004 => "POST_MULTIPLIED_BIT_KHR",
        0x0008 => "INHERIT_BIT_KHR",        
    ];
}