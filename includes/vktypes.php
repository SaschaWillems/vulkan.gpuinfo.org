<?php
/* 		
*
* Vulkan hardware capability database back-end
*	
* Copyright (C) 2016-2022 by Sascha Willems (www.saschawillems.de)
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

class VkTypes {
    private static function getFlags($flags, $flag) {
        $flag_values = array_values($flags);
        $supported_flags = [];
        $index = 0;
        foreach ($flags as $i => $value) {
            if ($flag & $i) {
                $supported_flags[] = $flag_values[$index];
            }
            $index++;
        }
        return $supported_flags;
    }
    
    private static function getValue($lookup, $value) {
        return $lookup[$value];
    }

    public static function VkFormatFeatureFlags($flag) {
        $flag_values = [
            0x0001 => "VK_FORMAT_FEATURE_SAMPLED_IMAGE_BIT",
            0x0002 => "VK_FORMAT_FEATURE_STORAGE_IMAGE_BIT",
            0x0004 => "VK_FORMAT_FEATURE_STORAGE_IMAGE_ATOMIC_BIT",
            0x0008 => "VK_FORMAT_FEATURE_UNIFORM_TEXEL_BUFFER_BIT",
            0x0010 => "VK_FORMAT_FEATURE_STORAGE_TEXEL_BUFFER_BIT",
            0x0020 => "VK_FORMAT_FEATURE_STORAGE_TEXEL_BUFFER_ATOMIC_BIT",
            0x0040 => "VK_FORMAT_FEATURE_VERTEX_BUFFER_BIT",
            0x0080 => "VK_FORMAT_FEATURE_COLOR_ATTACHMENT_BIT",
            0x0100 => "VK_FORMAT_FEATURE_COLOR_ATTACHMENT_BLEND_BIT",
            0x0200 => "VK_FORMAT_FEATURE_DEPTH_STENCIL_ATTACHMENT_BIT",
            0x0400 => "VK_FORMAT_FEATURE_BLIT_SRC_BIT",
            0x0800 => "VK_FORMAT_FEATURE_BLIT_DST_BIT",
            0x1000 => "VK_FORMAT_FEATURE_SAMPLED_IMAGE_FILTER_LINEAR_BIT",
            0x4000 => "VK_FORMAT_FEATURE_TRANSFER_SRC_BIT",
            0x8000 => "VK_FORMAT_FEATURE_TRANSFER_DST_BIT",
        ];
        $array_values = array_values($flag_values);
        $supported_flags = [];
        $index = 0;
        foreach ($flag_values as $i => $value) {
            if ($flag & $i) {
                $supported_flags[] = $array_values[$index];
            }
            $index++;
        }
        return $supported_flags;
    }    
    
    public static function VkSampleCountFlags($value) {
        $flags = [
            0x00000001 => 'VK_SAMPLE_COUNT_1_BIT',
            0x00000002 => 'VK_SAMPLE_COUNT_2_BIT',
            0x00000004 => 'VK_SAMPLE_COUNT_4_BIT',
            0x00000008 => 'VK_SAMPLE_COUNT_8_BIT',
            0x00000010 => 'VK_SAMPLE_COUNT_16_BIT',
            0x00000020 => 'VK_SAMPLE_COUNT_32_BIT',
            0x00000040 => 'VK_SAMPLE_COUNT_64_BIT',
        ];
        return self::getFlags($flags, $value);
    }

    public static function VkSampleCountFlagBits($value) {
        $flags = [
            0x00000001 => 'VK_SAMPLE_COUNT_1_BIT',
            0x00000002 => 'VK_SAMPLE_COUNT_2_BIT',
            0x00000004 => 'VK_SAMPLE_COUNT_4_BIT',
            0x00000008 => 'VK_SAMPLE_COUNT_8_BIT',
            0x00000010 => 'VK_SAMPLE_COUNT_16_BIT',
            0x00000020 => 'VK_SAMPLE_COUNT_32_BIT',
            0x00000040 => 'VK_SAMPLE_COUNT_64_BIT',
        ];
        return self::getValue($flags, $value);
    }
    
    public static function VkShaderStageFlags($value) {
        $flags = [
            0x00000001 => 'VK_SHADER_STAGE_VERTEX_BIT',
            0x00000002 => 'VK_SHADER_STAGE_TESSELLATION_CONTROL_BIT',
            0x00000004 => 'VK_SHADER_STAGE_TESSELLATION_EVALUATION_BIT',
            0x00000008 => 'VK_SHADER_STAGE_GEOMETRY_BIT',
            0x00000010 => 'VK_SHADER_STAGE_FRAGMENT_BIT',
            0x00000020 => 'VK_SHADER_STAGE_COMPUTE_BIT',
            0x0000001F => 'VK_SHADER_STAGE_ALL_GRAPHICS',
            0x7FFFFFFF => 'VK_SHADER_STAGE_ALL',
            0x00000100 => 'VK_SHADER_STAGE_RAYGEN_BIT_KHR',
            0x00000200 => 'VK_SHADER_STAGE_ANY_HIT_BIT_KHR',
            0x00000400 => 'VK_SHADER_STAGE_CLOSEST_HIT_BIT_KHR',
            0x00000800 => 'VK_SHADER_STAGE_MISS_BIT_KHR',
            0x00001000 => 'VK_SHADER_STAGE_INTERSECTION_BIT_KHR',
            0x00002000 => 'VK_SHADER_STAGE_CALLABLE_BIT_KHR',
            0x00000040 => 'VK_SHADER_STAGE_TASK_BIT_NV',
            0x00000080 => 'VK_SHADER_STAGE_MESH_BIT_NV',
            0x00004000 => 'VK_SHADER_STAGE_SUBPASS_SHADING_BIT_HUAWEI'
        ];
        return self::getFlags($flags, $value);
    }

    public static function VkPointClippingBehavior($value) {
        $flags = [
            0 => 'VK_POINT_CLIPPING_BEHAVIOR_ALL_CLIP_PLANES',
            1 => 'VK_POINT_CLIPPING_BEHAVIOR_USER_CLIP_PLANES_ONLY',
        ];          
        return self::getValue($flags, $value);
    }

    public static function VkSubgroupFeatureFlags($value) {
        $flags = [
            0x00000001 => 'VK_SUBGROUP_FEATURE_BASIC_BIT',
            0x00000002 => 'VK_SUBGROUP_FEATURE_VOTE_BIT',
            0x00000004 => 'VK_SUBGROUP_FEATURE_ARITHMETIC_BIT',
            0x00000008 => 'VK_SUBGROUP_FEATURE_BALLOT_BIT',
            0x00000010 => 'VK_SUBGROUP_FEATURE_SHUFFLE_BIT',
            0x00000020 => 'VK_SUBGROUP_FEATURE_SHUFFLE_RELATIVE_BIT',
            0x00000040 => 'VK_SUBGROUP_FEATURE_CLUSTERED_BIT',
            0x00000080 => 'VK_SUBGROUP_FEATURE_QUAD_BIT',
            0x00000100 => 'VK_SUBGROUP_FEATURE_PARTITIONED_BIT_NV',
        ];          
        return self::getFlags($flags, $value);
    }

    public static function VkDriverId($value) {
        $flags = [
            1 => 'VK_DRIVER_ID_AMD_PROPRIETARY',
            2 => 'VK_DRIVER_ID_AMD_OPEN_SOURCE',
            3 => 'VK_DRIVER_ID_MESA_RADV',
            4 => 'VK_DRIVER_ID_NVIDIA_PROPRIETARY',
            5 => 'VK_DRIVER_ID_INTEL_PROPRIETARY_WINDOWS',
            6 => 'VK_DRIVER_ID_INTEL_OPEN_SOURCE_MESA',
            7 => 'VK_DRIVER_ID_IMAGINATION_PROPRIETARY',
            8 => 'VK_DRIVER_ID_QUALCOMM_PROPRIETARY',
            9 => 'VK_DRIVER_ID_ARM_PROPRIETARY',
            10 => 'VK_DRIVER_ID_GOOGLE_SWIFTSHADER',
            11 => 'VK_DRIVER_ID_GGP_PROPRIETARY',
            12 => 'VK_DRIVER_ID_BROADCOM_PROPRIETARY',
            13 => 'VK_DRIVER_ID_MESA_LLVMPIPE',
            14 => 'VK_DRIVER_ID_MOLTENVK',
            15 => 'VK_DRIVER_ID_COREAVI_PROPRIETARY',
            16 => 'VK_DRIVER_ID_JUICE_PROPRIETARY',
            17 => 'VK_DRIVER_ID_VERISILICON_PROPRIETARY',
            18 => 'VK_DRIVER_ID_MESA_TURNIP',
            19 => 'VK_DRIVER_ID_MESA_V3DV',
            20 => 'VK_DRIVER_ID_MESA_PANVK',
        ];
        return self::getValue($flags, $value);   
    }

    public static function VkConformanceVersion($value) {
        $parts = explode('.', $value);
        return [
            'major' => intval($parts[0]),
            'minor' => intval($parts[1]),
            'patch' => intval($parts[2]),
            'subminor' => intval($parts[3]),
        ];
    }

    public static function VkResolveModeFlags($value) {
        $flags = [
            0 => 'VK_RESOLVE_MODE_NONE',
            0x00000001 => 'VK_RESOLVE_MODE_SAMPLE_ZERO_BIT',
            0x00000002 => 'VK_RESOLVE_MODE_AVERAGE_BIT',
            0x00000004 => 'VK_RESOLVE_MODE_MIN_BIT',
            0x00000008 => 'VK_RESOLVE_MODE_MAX_BIT',
        ];
        return self::getFlags($flags, $value);     
    }

    public static function VkShaderFloatControlsIndependence($value) {
        $flags = [
            0 => 'VK_SHADER_FLOAT_CONTROLS_INDEPENDENCE_32_BIT_ONLY',
            1 => 'VK_SHADER_FLOAT_CONTROLS_INDEPENDENCE_ALL',
            2 => 'VK_SHADER_FLOAT_CONTROLS_INDEPENDENCE_NONE',
        ];          
        return self::getValue($flags, $value);      
    }
}