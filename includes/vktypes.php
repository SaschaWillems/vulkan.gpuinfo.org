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
}