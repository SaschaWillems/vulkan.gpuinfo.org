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

    private static function addExtensionSuffix(&$value, $extension) {
        if (strpos($extension, '_KHR_') !== false) {
            $value .= '_KHR';
            return;
        };        
        if (strpos($extension, '_EXT_') !== false) {
            $value .= '_EXT';
            return;
        };
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

    public static function VkQueueFlags($value) {
        $flags = [
            0x00000001 => 'VK_QUEUE_GRAPHICS_BIT',
            0x00000002 => 'VK_QUEUE_COMPUTE_BIT',
            0x00000004 => 'VK_QUEUE_TRANSFER_BIT',
            0x00000008 => 'VK_QUEUE_SPARSE_BINDING_BIT',
            0x00000010 => 'VK_QUEUE_PROTECTED_BIT',
            0x00000020 => 'VK_QUEUE_VIDEO_DECODE_BIT_KHR',
            0x00000040 => 'VK_QUEUE_VIDEO_ENCODE_BIT_KHR',
        ];
        return self::getFlags($flags, $value);
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

    public static function VkDriverId($value, $extension = false) {
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
        $value = self::getValue($flags, $value);   
        self::addExtensionSuffix($value, $extension);
        return $value;
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

    public static function VkShaderFloatControlsIndependence($value, $extension) {
        $flags = [
            0 => 'VK_SHADER_FLOAT_CONTROLS_INDEPENDENCE_32_BIT_ONLY',
            1 => 'VK_SHADER_FLOAT_CONTROLS_INDEPENDENCE_ALL',
            2 => 'VK_SHADER_FLOAT_CONTROLS_INDEPENDENCE_NONE',
        ];        
        $value = self::getValue($flags, $value);
        self::addExtensionSuffix($value, $extension);
        return $value;     
    }    

    /** Type mappings for Vulkan structures */
    public static $VkPhysicalDeviceProperties = [
        'apiVersion' => 'uint32_t',
        'driverVersion' => 'uint32_t',
        'vendorID' => 'uint32_t',
        'deviceID' => 'uint32_t',
        'deviceType' => 'VkPhysicalDeviceType',
        'deviceName' => 'char',
        'pipelineCacheUUID' => 'uint8_t',
        'limits' => 'VkPhysicalDeviceLimits',
        'sparseProperties' => 'VkPhysicalDeviceSparseProperties',
    ];

    public static $VkPhysicalDeviceVulkan11Properties = [
        'deviceUUID' => 'uint8_t',
        'driverUUID' => 'uint8_t',
        'deviceLUID' => 'uint8_t',
        'deviceNodeMask' => 'uint32_t',
        'deviceLUIDValid' => 'VkBool32',
        'subgroupSize' => 'uint32_t',
        'subgroupSupportedStages' => 'VkShaderStageFlags',
        'subgroupSupportedOperations' => 'VkSubgroupFeatureFlags',
        'subgroupQuadOperationsInAllStages' => 'VkBool32',
        'pointClippingBehavior' => 'VkPointClippingBehavior',
        'maxMultiviewViewCount' => 'uint32_t',
        'maxMultiviewInstanceIndex' => 'uint32_t',
        'protectedNoFault' => 'VkBool32',
        'maxPerSetDescriptors' => 'uint32_t',
        'maxMemoryAllocationSize' => 'VkDeviceSize',
    ];

    public static $VkPhysicalDeviceVulkan12Properties = [
        'driverID' => 'VkDriverId',
        'driverName' => 'char',
        'driverInfo' => 'char',
        'conformanceVersion' => 'VkConformanceVersion',
        'denormBehaviorIndependence' => 'VkShaderFloatControlsIndependence',
        'roundingModeIndependence' => 'VkShaderFloatControlsIndependence',
        'shaderSignedZeroInfNanPreserveFloat16' => 'VkBool32',
        'shaderSignedZeroInfNanPreserveFloat32' => 'VkBool32',
        'shaderSignedZeroInfNanPreserveFloat64' => 'VkBool32',
        'shaderDenormPreserveFloat16' => 'VkBool32',
        'shaderDenormPreserveFloat32' => 'VkBool32',
        'shaderDenormPreserveFloat64' => 'VkBool32',
        'shaderDenormFlushToZeroFloat16' => 'VkBool32',
        'shaderDenormFlushToZeroFloat32' => 'VkBool32',
        'shaderDenormFlushToZeroFloat64' => 'VkBool32',
        'shaderRoundingModeRTEFloat16' => 'VkBool32',
        'shaderRoundingModeRTEFloat32' => 'VkBool32',
        'shaderRoundingModeRTEFloat64' => 'VkBool32',
        'shaderRoundingModeRTZFloat16' => 'VkBool32',
        'shaderRoundingModeRTZFloat32' => 'VkBool32',
        'shaderRoundingModeRTZFloat64' => 'VkBool32',
        'maxUpdateAfterBindDescriptorsInAllPools' => 'uint32_t',
        'shaderUniformBufferArrayNonUniformIndexingNative' => 'VkBool32',
        'shaderSampledImageArrayNonUniformIndexingNative' => 'VkBool32',
        'shaderStorageBufferArrayNonUniformIndexingNative' => 'VkBool32',
        'shaderStorageImageArrayNonUniformIndexingNative' => 'VkBool32',
        'shaderInputAttachmentArrayNonUniformIndexingNative' => 'VkBool32',
        'robustBufferAccessUpdateAfterBind' => 'VkBool32',
        'quadDivergentImplicitLod' => 'VkBool32',
        'maxPerStageDescriptorUpdateAfterBindSamplers' => 'uint32_t',
        'maxPerStageDescriptorUpdateAfterBindUniformBuffers' => 'uint32_t',
        'maxPerStageDescriptorUpdateAfterBindStorageBuffers' => 'uint32_t',
        'maxPerStageDescriptorUpdateAfterBindSampledImages' => 'uint32_t',
        'maxPerStageDescriptorUpdateAfterBindStorageImages' => 'uint32_t',
        'maxPerStageDescriptorUpdateAfterBindInputAttachments' => 'uint32_t',
        'maxPerStageUpdateAfterBindResources' => 'uint32_t',
        'maxDescriptorSetUpdateAfterBindSamplers' => 'uint32_t',
        'maxDescriptorSetUpdateAfterBindUniformBuffers' => 'uint32_t',
        'maxDescriptorSetUpdateAfterBindUniformBuffersDynamic' => 'uint32_t',
        'maxDescriptorSetUpdateAfterBindStorageBuffers' => 'uint32_t',
        'maxDescriptorSetUpdateAfterBindStorageBuffersDynamic' => 'uint32_t',
        'maxDescriptorSetUpdateAfterBindSampledImages' => 'uint32_t',
        'maxDescriptorSetUpdateAfterBindStorageImages' => 'uint32_t',
        'maxDescriptorSetUpdateAfterBindInputAttachments' => 'uint32_t',
        'supportedDepthResolveModes' => 'VkResolveModeFlags',
        'supportedStencilResolveModes' => 'VkResolveModeFlags',
        'independentResolveNone' => 'VkBool32',
        'independentResolve' => 'VkBool32',
        'filterMinmaxSingleComponentFormats' => 'VkBool32',
        'filterMinmaxImageComponentMapping' => 'VkBool32',
        'maxTimelineSemaphoreValueDifference' => 'uint64_t',
        'framebufferIntegerColorSampleCounts' => 'VkSampleCountFlags',
    ];

    public static $VkPhysicalDeviceVulkan13Properties = [
        'minSubgroupSize' => 'uint32_t',
        'maxSubgroupSize' => 'uint32_t',
        'maxComputeWorkgroupSubgroups' => 'uint32_t',
        'requiredSubgroupSizeStages' => 'VkShaderStageFlags',
        'maxInlineUniformBlockSize' => 'uint32_t',
        'maxPerStageDescriptorInlineUniformBlocks' => 'uint32_t',
        'maxPerStageDescriptorUpdateAfterBindInlineUniformBlocks' => 'uint32_t',
        'maxDescriptorSetInlineUniformBlocks' => 'uint32_t',
        'maxDescriptorSetUpdateAfterBindInlineUniformBlocks' => 'uint32_t',
        'maxInlineUniformTotalSize' => 'uint32_t',
        'integerDotProduct8BitUnsignedAccelerated' => 'VkBool32',
        'integerDotProduct8BitSignedAccelerated' => 'VkBool32',
        'integerDotProduct8BitMixedSignednessAccelerated' => 'VkBool32',
        'integerDotProduct4x8BitPackedUnsignedAccelerated' => 'VkBool32',
        'integerDotProduct4x8BitPackedSignedAccelerated' => 'VkBool32',
        'integerDotProduct4x8BitPackedMixedSignednessAccelerated' => 'VkBool32',
        'integerDotProduct16BitUnsignedAccelerated' => 'VkBool32',
        'integerDotProduct16BitSignedAccelerated' => 'VkBool32',
        'integerDotProduct16BitMixedSignednessAccelerated' => 'VkBool32',
        'integerDotProduct32BitUnsignedAccelerated' => 'VkBool32',
        'integerDotProduct32BitSignedAccelerated' => 'VkBool32',
        'integerDotProduct32BitMixedSignednessAccelerated' => 'VkBool32',
        'integerDotProduct64BitUnsignedAccelerated' => 'VkBool32',
        'integerDotProduct64BitSignedAccelerated' => 'VkBool32',
        'integerDotProduct64BitMixedSignednessAccelerated' => 'VkBool32',
        'integerDotProductAccumulatingSaturating8BitUnsignedAccelerated' => 'VkBool32',
        'integerDotProductAccumulatingSaturating8BitSignedAccelerated' => 'VkBool32',
        'integerDotProductAccumulatingSaturating8BitMixedSignednessAccelerated' => 'VkBool32',
        'integerDotProductAccumulatingSaturating4x8BitPackedUnsignedAccelerated' => 'VkBool32',
        'integerDotProductAccumulatingSaturating4x8BitPackedSignedAccelerated' => 'VkBool32',
        'integerDotProductAccumulatingSaturating4x8BitPackedMixedSignednessAccelerated' => 'VkBool32',
        'integerDotProductAccumulatingSaturating16BitUnsignedAccelerated' => 'VkBool32',
        'integerDotProductAccumulatingSaturating16BitSignedAccelerated' => 'VkBool32',
        'integerDotProductAccumulatingSaturating16BitMixedSignednessAccelerated' => 'VkBool32',
        'integerDotProductAccumulatingSaturating32BitUnsignedAccelerated' => 'VkBool32',
        'integerDotProductAccumulatingSaturating32BitSignedAccelerated' => 'VkBool32',
        'integerDotProductAccumulatingSaturating32BitMixedSignednessAccelerated' => 'VkBool32',
        'integerDotProductAccumulatingSaturating64BitUnsignedAccelerated' => 'VkBool32',
        'integerDotProductAccumulatingSaturating64BitSignedAccelerated' => 'VkBool32',
        'integerDotProductAccumulatingSaturating64BitMixedSignednessAccelerated' => 'VkBool32',
        'storageTexelBufferOffsetAlignmentBytes' => 'VkDeviceSize',
        'storageTexelBufferOffsetSingleTexelAlignment' => 'VkBool32',
        'uniformTexelBufferOffsetAlignmentBytes' => 'VkDeviceSize',
        'uniformTexelBufferOffsetSingleTexelAlignment' => 'VkBool32',
        'maxBufferSize' => 'VkDeviceSize',
    ];

    public static $VkPhysicalDeviceLimits = [
        'maxImageDimension1D' => 'uint32_t',
        'maxImageDimension2D' => 'uint32_t',
        'maxImageDimension3D' => 'uint32_t',
        'maxImageDimensionCube' => 'uint32_t',
        'maxImageArrayLayers' => 'uint32_t',
        'maxTexelBufferElements' => 'uint32_t',
        'maxUniformBufferRange' => 'uint32_t',
        'maxStorageBufferRange' => 'uint32_t',
        'maxPushConstantsSize' => 'uint32_t',
        'maxMemoryAllocationCount' => 'uint32_t',
        'maxSamplerAllocationCount' => 'uint32_t',
        'bufferImageGranularity' => 'VkDeviceSize',
        'sparseAddressSpaceSize' => 'VkDeviceSize',
        'maxBoundDescriptorSets' => 'uint32_t',
        'maxPerStageDescriptorSamplers' => 'uint32_t',
        'maxPerStageDescriptorUniformBuffers' => 'uint32_t',
        'maxPerStageDescriptorStorageBuffers' => 'uint32_t',
        'maxPerStageDescriptorSampledImages' => 'uint32_t',
        'maxPerStageDescriptorStorageImages' => 'uint32_t',
        'maxPerStageDescriptorInputAttachments' => 'uint32_t',
        'maxPerStageResources' => 'uint32_t',
        'maxDescriptorSetSamplers' => 'uint32_t',
        'maxDescriptorSetUniformBuffers' => 'uint32_t',
        'maxDescriptorSetUniformBuffersDynamic' => 'uint32_t',
        'maxDescriptorSetStorageBuffers' => 'uint32_t',
        'maxDescriptorSetStorageBuffersDynamic' => 'uint32_t',
        'maxDescriptorSetSampledImages' => 'uint32_t',
        'maxDescriptorSetStorageImages' => 'uint32_t',
        'maxDescriptorSetInputAttachments' => 'uint32_t',
        'maxVertexInputAttributes' => 'uint32_t',
        'maxVertexInputBindings' => 'uint32_t',
        'maxVertexInputAttributeOffset' => 'uint32_t',
        'maxVertexInputBindingStride' => 'uint32_t',
        'maxVertexOutputComponents' => 'uint32_t',
        'maxTessellationGenerationLevel' => 'uint32_t',
        'maxTessellationPatchSize' => 'uint32_t',
        'maxTessellationControlPerVertexInputComponents' => 'uint32_t',
        'maxTessellationControlPerVertexOutputComponents' => 'uint32_t',
        'maxTessellationControlPerPatchOutputComponents' => 'uint32_t',
        'maxTessellationControlTotalOutputComponents' => 'uint32_t',
        'maxTessellationEvaluationInputComponents' => 'uint32_t',
        'maxTessellationEvaluationOutputComponents' => 'uint32_t',
        'maxGeometryShaderInvocations' => 'uint32_t',
        'maxGeometryInputComponents' => 'uint32_t',
        'maxGeometryOutputComponents' => 'uint32_t',
        'maxGeometryOutputVertices' => 'uint32_t',
        'maxGeometryTotalOutputComponents' => 'uint32_t',
        'maxFragmentInputComponents' => 'uint32_t',
        'maxFragmentOutputAttachments' => 'uint32_t',
        'maxFragmentDualSrcAttachments' => 'uint32_t',
        'maxFragmentCombinedOutputResources' => 'uint32_t',
        'maxComputeSharedMemorySize' => 'uint32_t',
        'maxComputeWorkGroupCount' => 'uint32_t',
        'maxComputeWorkGroupInvocations' => 'uint32_t',
        'maxComputeWorkGroupSize' => 'uint32_t',
        'subPixelPrecisionBits' => 'uint32_t',
        'subTexelPrecisionBits' => 'uint32_t',
        'mipmapPrecisionBits' => 'uint32_t',
        'maxDrawIndexedIndexValue' => 'uint32_t',
        'maxDrawIndirectCount' => 'uint32_t',
        'maxSamplerLodBias' => 'float',
        'maxSamplerAnisotropy' => 'float',
        'maxViewports' => 'uint32_t',
        'maxViewportDimensions' => 'uint32_t',
        'viewportBoundsRange' => 'float',
        'viewportSubPixelBits' => 'uint32_t',
        'minMemoryMapAlignment' => 'size_t',
        'minTexelBufferOffsetAlignment' => 'VkDeviceSize',
        'minUniformBufferOffsetAlignment' => 'VkDeviceSize',
        'minStorageBufferOffsetAlignment' => 'VkDeviceSize',
        'minTexelOffset' => 'int32_t',
        'maxTexelOffset' => 'uint32_t',
        'minTexelGatherOffset' => 'int32_t',
        'maxTexelGatherOffset' => 'uint32_t',
        'minInterpolationOffset' => 'float',
        'maxInterpolationOffset' => 'float',
        'subPixelInterpolationOffsetBits' => 'uint32_t',
        'maxFramebufferWidth' => 'uint32_t',
        'maxFramebufferHeight' => 'uint32_t',
        'maxFramebufferLayers' => 'uint32_t',
        'framebufferColorSampleCounts' => 'VkSampleCountFlags',
        'framebufferDepthSampleCounts' => 'VkSampleCountFlags',
        'framebufferStencilSampleCounts' => 'VkSampleCountFlags',
        'framebufferNoAttachmentsSampleCounts' => 'VkSampleCountFlags',
        'maxColorAttachments' => 'uint32_t',
        'sampledImageColorSampleCounts' => 'VkSampleCountFlags',
        'sampledImageIntegerSampleCounts' => 'VkSampleCountFlags',
        'sampledImageDepthSampleCounts' => 'VkSampleCountFlags',
        'sampledImageStencilSampleCounts' => 'VkSampleCountFlags',
        'storageImageSampleCounts' => 'VkSampleCountFlags',
        'maxSampleMaskWords' => 'uint32_t',
        'timestampComputeAndGraphics' => 'VkBool32',
        'timestampPeriod' => 'float',
        'maxClipDistances' => 'uint32_t',
        'maxCullDistances' => 'uint32_t',
        'maxCombinedClipAndCullDistances' => 'uint32_t',
        'discreteQueuePriorities' => 'uint32_t',
        'pointSizeRange' => 'float',
        'lineWidthRange' => 'float',
        'pointSizeGranularity' => 'float',
        'lineWidthGranularity' => 'float',
        'strictLines' => 'VkBool32',
        'standardSampleLocations' => 'VkBool32',
        'optimalBufferCopyOffsetAlignment' => 'VkDeviceSize',
        'optimalBufferCopyRowPitchAlignment' => 'VkDeviceSize',
        'nonCoherentAtomSize' => 'VkDeviceSize',
    ];
}