<?php
	/*
		*
		* Vulkan hardware capability database server implementation
		*
		* Copyright (C) by Sascha Willems (www.saschawillems.de)
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

    class VulkanEnums {

        private static function flagName($flags, $flag) {
            return array_key_exists($flag, $flags) ? $flags[$flag] : $flag;
        }

        public static $imageUsageFlags = [
            0x0001 => "TRANSFER_SRC_BIT",
            0x0002 => "TRANSFER_DST_BIT",
            0x0004 => "SAMPLED_BIT",
            0x0008 => "STORAGE_BIT",
            0x0010 => "COLOR_ATTACHMENT_BIT",
            0x0020 => "DEPTH_STENCIL_ATTACHMENT_BIT",
            0x0040 => "TRANSIENT_ATTACHMENT_BIT",
            0x0080 => "INPUT_ATTACHMENT_BIT",
        ];

        public static $formatFeatureFlags = [
            0x0001 => "SAMPLED_IMAGE_BIT",
            0x0002 => "STORAGE_IMAGE_BIT",
            0x0004 => "STORAGE_IMAGE_ATOMIC_BIT",
            0x0008 => "UNIFORM_TEXEL_BUFFER_BIT",
            0x0010 => "STORAGE_TEXEL_BUFFER_BIT",
            0x0020 => "STORAGE_TEXEL_BUFFER_ATOMIC_BIT",
            0x0040 => "VERTEX_BUFFER_BIT",
            0x0080 => "COLOR_ATTACHMENT_BIT",
            0x0100 => "COLOR_ATTACHMENT_BLEND_BIT",
            0x0200 => "DEPTH_STENCIL_ATTACHMENT_BIT",
            0x0400 => "BLIT_SRC_BIT",
            0x0800 => "BLIT_DST_BIT",
            0x1000 => "SAMPLED_IMAGE_FILTER_LINEAR_BIT",
            0x4000 => "TRANSFER_SRC_BIT",
            0x8000 => "TRANSFER_DST_BIT",
        ];

        public static $surfaceTransformFlags = [
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

        public static $surfaceCompositeAlphaFlags = [
            0x0001 => "OPAQUE_BIT_KHR",
            0x0002 => "PRE_MULTIPLIED_BIT_KHR",
            0x0004 => "POST_MULTIPLIED_BIT_KHR",
            0x0008 => "INHERIT_BIT_KHR",
        ];

        public static $queueFamilyFlags = [
            0x0001 => "GRAPHICS_BIT",
            0x0002 => "COMPUTE_BIT",
            0x0004 => "TRANSFER_BIT",
            0x0008 => "SPARSE_BINDING_BIT",
            0x0010 => "PROTECTED_BIT",
        ];

        public static $memoryTypeFlags = [
            0x0001 => "DEVICE_LOCAL_BIT",
            0x0002 => "HOST_VISIBLE_BIT",
            0x0004 => "HOST_COHERENT_BIT",
            0x0008 => "HOST_CACHED_BIT",
            0x0010 => "LAZILY_ALLOCATED_BIT",
        ];

        public static $subgroupSupportedStageFlags = [
            0x0001 => "VERTEX",
            0x0002 => "TESSELLATION CONTROL",
            0x0004 => "TESSELLATION EVALUATION",
            0x0008 => "GEOMETRY",
            0x0010 => "FRAGMENT",
            0x0020 => "COMPUTE",
            0x001F => "ALL GRAPHICS"
        ];

        public static $subgroupSupportedOperationFlags = [
            0x0001 => "BASIC",
            0x0002 => "VOTE",
            0x0004 => "ARITHMETIC",
            0x0008 => "BALLOT",
            0x0010 => "SHUFFLE",
            0x0020 => "SHUFFLE (RELATIVE)",
            0x0040 => "CLUSTERED",
            0x0080 => "QUAD"
        ];

        public static function formatFlagName($flag)
        {
            $flags = [
                0x0001 => "SAMPLED_IMAGE_BIT",
                0x0002 => "STORAGE_IMAGE_BIT",
                0x0004 => "STORAGE_IMAGE_ATOMIC_BIT",
                0x0008 => "UNIFORM_TEXEL_BUFFER_BIT",
                0x0010 => "STORAGE_TEXEL_BUFFER_BIT",
                0x0020 => "STORAGE_TEXEL_BUFFER_ATOMIC_BIT",
                0x0040 => "VERTEX_BUFFER_BIT",
                0x0080 => "COLOR_ATTACHMENT_BIT",
                0x0100 => "COLOR_ATTACHMENT_BLEND_BIT",
                0x0200 => "DEPTH_STENCIL_ATTACHMENT_BIT",
                0x0400 => "BLIT_SRC_BIT",
                0x0800 => "BLIT_DST_BIT",
                0x1000 => "SAMPLED_IMAGE_FILTER_LINEAR_BIT",
                0x4000 => "TRANSFER_SRC_BIT",
                0x8000 => "TRANSFER_DST_BIT",
            ];
            return VulkanEnums::flagName($flags, $flag);
        }

        public static function imageUsageFlagName($flag)
        {
            return VulkanEnums::flagName(VulkanEnums::$imageUsageFlags, $flag);
        }

        public static function memoryTypeFlagName($flag)
        {
            $flags = [
                0x0001 => "DEVICE_LOCAL_BIT",
                0x0002 => "HOST_VISIBLE_BIT",
                0x0004 => "HOST_COHERENT_BIT",
                0x0008 => "HOST_CACHED_BIT",
                0x0010 => "LAZILY_ALLOCATED_BIT",
            ];
            return VulkanEnums::flagName($flags, $flag);
        }

        public static function queueFamilyFlagName($flag)
        {
            $flags = [
                0x0001 => "GRAPHICS_BIT",
                0x0002 => "COMPUTE_BIT",
                0x0004 => "TRANSFER_BIT",
                0x0008 => "SPARSE_BINDING_BIT",
                0x0010 => "PROTECTED_BIT",
            ];
            return VulkanEnums::flagName($flags, $flag);
        }

        public static function surfaceTransformFlagName($flag)
        {
            $flags = [
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
            return VulkanEnums::flagName($flags, $flag);
        }

        public static function surfaceCompositeAlphaFlagName($flag)
        {
            $flags = [
                0x0001 => "OPAQUE_BIT_KHR",
                0x0002 => "PRE_MULTIPLIED_BIT_KHR",
                0x0004 => "POST_MULTIPLIED_BIT_KHR",
                0x0008 => "INHERIT_BIT_KHR",
            ];
            return VulkanEnums::flagName($flags, $flag);
        }

    }

?>