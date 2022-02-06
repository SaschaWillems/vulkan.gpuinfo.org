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

require './../../database/database.class.php';
require './../../includes/functions.php';
require './../../includes/mappings.php';
	
if (!isset($_GET['id'])) {
    header('HTTP/ 400 missing_or');
    echo "No report id specified!";
    die();
}

DB::connect();
$reportid = $_GET['id'];	
$stmnt = DB::$connection->prepare("SELECT * from reports where id = :reportid");
$stmnt->execute([":reportid" => $reportid]);
if ($stmnt->rowCount() == 0) {
    DB::disconnect();
    header('HTTP/ 400 missing_or');
    echo "No report id specified!";
    die();
}
$row = $stmnt->fetch(PDO::FETCH_ASSOC);

function skipField($name, $version) {
    $skip_fields = [
        'reportid',
        'headerversion',
        'productModel',
        'productManufacturer',
        'deviceid',
        'devicename',
        'devicetype',
        'driverversion',
        'driverversionraw',
    ];
    if ($version == '1.0') {
        $skip_fields = array_merge($skip_fields, [
            'residencyAlignedMipSize',
            'residencyNonResidentStrict',
            'residencyStandard2DBlockShape',
            'residencyStandard2DMultisampleBlockShape',
            'residencyStandard3DBlockShape',
            'subgroupProperties.subgroupSize',
            'subgroupProperties.supportedStages',
            'subgroupProperties.supportedOperations',
            'subgroupProperties.quadOperationsInAllStages',
            'maxComputeWorkGroupCount[0]',
            'maxComputeWorkGroupCount[1]',
            'maxComputeWorkGroupCount[2]',
            'maxComputeWorkGroupSize[0]',
            'maxComputeWorkGroupSize[1]',
            'maxComputeWorkGroupSize[2]',
            'maxViewportDimensions[0]',
            'maxViewportDimensions[1]',
            'pointSizeRange[0]',
            'pointSizeRange[1]',
            'viewportBoundsRange[0]',
            'viewportBoundsRange[1]',
            'lineWidthRange[0]',
            'lineWidthRange[1]',
        ]);
    }
    return in_array($name, $skip_fields);
}

function capitalizeFieldName($name) {
    $spelling = [
        'vendorid' => 'vendorID',
        'apiversion' => 'apiVersion',
		'idp8bitunsignedaccelerated' => 'integerDotProduct8BitUnsignedAccelerated',
		'idp8bitsignedaccelerated' => 'integerDotProduct8BitSignedAccelerated',
		'idp8bitmixedsignednessaccelerated' => 'integerDotProduct8BitMixedSignednessAccelerated',
		'idp4x8bitpackedunsignedaccelerated' => 'integerDotProduct4x8BitPackedUnsignedAccelerated',
		'idp4x8bitpackedsignedaccelerated' => 'integerDotProduct4x8BitPackedSignedAccelerated',
		'idp4x8bitpackedmixedsignednessaccelerated' => 'integerDotProduct4x8BitPackedMixedSignednessAccelerated',
		'idp16bitunsignedaccelerated' => 'integerDotProduct16BitUnsignedAccelerated',
		'idp16bitsignedaccelerated' => 'integerDotProduct16BitSignedAccelerated',
		'idp16bitmixedsignednessaccelerated' => 'integerDotProduct16BitMixedSignednessAccelerated',
		'idp32bitunsignedaccelerated' => 'integerDotProduct32BitUnsignedAccelerated',
		'idp32bitsignedaccelerated' => 'integerDotProduct32BitSignedAccelerated',
		'idp32bitmixedsignednessaccelerated' => 'integerDotProduct32BitMixedSignednessAccelerated',
		'idp64bitunsignedaccelerated' => 'integerDotProduct64BitUnsignedAccelerated',
		'idp64bitsignedaccelerated' => 'integerDotProduct64BitSignedAccelerated',
		'idp64bitmixedsignednessaccelerated' => 'integerDotProduct64BitMixedSignednessAccelerated',
		'idpaccumulatingsaturating8bitunsignedaccelerated' => 'integerDotProductAccumulatingSaturating8BitUnsignedAccelerated',
		'idpaccumulatingsaturating8bitsignedaccelerated' => 'integerDotProductAccumulatingSaturating8BitSignedAccelerated',
		'idpaccumulatingsaturating8bitmixedsignednessaccelerated' => 'integerDotProductAccumulatingSaturating8BitMixedSignednessAccelerated',
		'idpaccumulatingsaturating4x8bitpackedunsignedaccelerated' => 'integerDotProductAccumulatingSaturating4x8BitPackedUnsignedAccelerated',
		'idpaccumulatingsaturating4x8bitpackedsignedaccelerated' => 'integerDotProductAccumulatingSaturating4x8BitPackedSignedAccelerated',
		'idpaccumulatingsaturating4x8bitpackedmixedsignednessaccelerated' => 'integerDotProductAccumulatingSaturating4x8BitPackedMixedSignednessAccelerated',
		'idpaccumulatingsaturating16bitunsignedaccelerated' => 'integerDotProductAccumulatingSaturating16BitUnsignedAccelerated',
		'idpaccumulatingsaturating16bitsignedaccelerated' => 'integerDotProductAccumulatingSaturating16BitSignedAccelerated',
		'idpaccumulatingsaturating16bitmixedsignednessaccelerated' => 'integerDotProductAccumulatingSaturating16BitMixedSignednessAccelerated',
		'idpaccumulatingsaturating32bitunsignedaccelerated' => 'integerDotProductAccumulatingSaturating32BitUnsignedAccelerated',
		'idpaccumulatingsaturating32bitsignedaccelerated' => 'integerDotProductAccumulatingSaturating32BitSignedAccelerated',
		'idpaccumulatingsaturating32bitmixedsignednessaccelerated' => 'integerDotProductAccumulatingSaturating32BitMixedSignednessAccelerated',
		'idpaccumulatingsaturating64bitunsignedaccelerated' => 'integerDotProductAccumulatingSaturating64BitUnsignedAccelerated',
		'idpaccumulatingsaturating64bitsignedaccelerated' => 'integerDotProductAccumulatingSaturating64BitSignedAccelerated',
		'idpaccumulatingsaturating64bitmixedsignednessaccelerated' => 'integerDotProductAccumulatingSaturating64BitMixedSignednessAccelerated'
    ];
    if (array_key_exists(strtolower($name), $spelling)) {
        return $spelling[strtolower($name)];
    }
    return $name;
}

function getVkFlags($flags, $flag) {
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

function getVkValue($lookup, $value) {
    return (in_array($value, $lookup) ? array_search($value, $lookup) : null);    
}

function convertFieldValue($name, $value) {
    switch (strtolower($name)) {
        case 'vendorid':
        case 'apiversion':
        case 'maxmultiviewviewcount':
        case 'maxmultiviewinstanceindex':
        case 'devicenodemask':
        case 'subgroupsize':
        case 'maxpersetdescriptors':
        case 'maxperstagedescriptorupdateafterbindsamplers':
        case 'maxperstagedescriptorupdateafterbinduniformbuffers':
        case 'maxperstagedescriptorupdateafterbindstoragebuffers':
        case 'maxperstagedescriptorupdateafterbindsampledimages':
        case 'maxperstagedescriptorupdateafterbindstorageimages':
        case 'maxperstagedescriptorupdateafterbindinputattachments':
        case 'maxperstageupdateafterbindresources':
        case 'maxdescriptorsetupdateafterbindsamplers':
        case 'maxdescriptorsetupdateafterbinduniformbuffers':
        case 'maxdescriptorsetupdateafterbinduniformbuffersdynamic':
        case 'maxdescriptorsetupdateafterbindstoragebuffers':
        case 'maxdescriptorsetupdateafterbindstoragebuffersdynamic':
        case 'maxdescriptorsetupdateafterbindsampledimages':
        case 'maxdescriptorsetupdateafterbindstorageimages':
        case 'maxdescriptorsetupdateafterbindinputattachments':
        case 'maxupdateafterbinddescriptorsinallpools':
        case 'minsubgroupsize':
        case 'maxsubgroupsize':
        case 'maxcomputeworkgroupsubgroups':
        case 'maxinlineuniformblocksize':
        case 'maxperstagedescriptorinlineuniformblocks':
        case 'maxperstagedescriptorupdateafterbindinlineuniformblocks':
        case 'maxdescriptorsetinlineuniformblocks':
        case 'maxdescriptorsetupdateafterbindinlineuniformblocks':
        case 'maxinlineuniformtotalsize':
        case 'mintexelgatheroffset':
        case 'mintexeloffset':
        case 'minmemorymapalignment':
        case 'discretequeuepriorities':
        case 'maxbounddescriptorsets':
        case 'maxclipdistances':
        case 'maxcolorattachments':
        case 'maxcombinedclipandculldistances':
        case 'maxcomputesharedmemorysize':
        case 'maxcomputeworkgroupcount[3]':
        case 'maxcomputeworkgroupinvocations':
        case 'maxcomputeworkgroupsize[3]':
        case 'maxculldistances':
        case 'maxdescriptorsetinputattachments':
        case 'maxdescriptorsetsampledimages':
        case 'maxdescriptorsetsamplers':
        case 'maxdescriptorsetstoragebuffers':
        case 'maxdescriptorsetstoragebuffersdynamic':
        case 'maxdescriptorsetstorageimages':
        case 'maxdescriptorsetuniformbuffers':
        case 'maxdescriptorsetuniformbuffersdynamic':
        case 'maxdrawindexedindexvalue':
        case 'maxdrawindirectcount':
        case 'maxfragmentcombinedoutputresources':
        case 'maxfragmentdualsrcattachments':
        case 'maxfragmentinputcomponents':
        case 'maxfragmentoutputattachments':
        case 'maxframebufferheight':
        case 'maxframebufferlayers':
        case 'maxframebufferwidth':
        case 'maxgeometryinputcomponents':
        case 'maxgeometryoutputcomponents':
        case 'maxgeometryoutputvertices':
        case 'maxgeometryshaderinvocations':
        case 'maxgeometrytotaloutputcomponents':
        case 'maximagearraylayers':
        case 'maximagedimension1d':
        case 'maximagedimension2d':
        case 'maximagedimension3d':
        case 'maximagedimensioncube':
        case 'maxmemoryallocationcount':
        case 'maxperstagedescriptorinputattachments':
        case 'maxperstagedescriptorsampledimages':
        case 'maxperstagedescriptorsamplers':
        case 'maxperstagedescriptorstoragebuffers':
        case 'maxperstagedescriptorstorageimages':
        case 'maxperstagedescriptoruniformbuffers':
        case 'maxperstageresources':
        case 'maxpushconstantssize':
        case 'maxsamplemaskwords':
        case 'maxsamplerallocationcount':
        case 'maxstoragebufferrange':
        case 'maxtessellationcontrolperpatchoutputcomponents':
        case 'maxtessellationcontrolpervertexinputcomponents':
        case 'maxtessellationcontrolpervertexoutputcomponents':
        case 'maxtessellationcontroltotaloutputcomponents':
        case 'maxtessellationevaluationinputcomponents':
        case 'maxtessellationevaluationoutputcomponents':
        case 'maxtessellationgenerationlevel':
        case 'maxtessellationpatchsize':
        case 'maxtexelbufferelements':
        case 'maxtexelgatheroffset':
        case 'maxtexeloffset':
        case 'maxuniformbufferrange':
        case 'maxvertexinputattributeoffset':
        case 'maxvertexinputattributes':
        case 'maxvertexinputbindings':
        case 'maxvertexinputbindingstride':
        case 'maxvertexoutputcomponents':
        case 'maxviewportdimensions[2]':
        case 'maxviewports':
        case 'mipmapprecisionbits':
        case 'subpixelinterpolationoffsetbits':
        case 'subpixelprecisionbits':
        case 'subtexelprecisionbits':
        case 'viewportsubpixelbits':
            return intval($value);
        // Float
        case 'maxsamplerlodbias':
        case 'maxsampleranisotropy':
        case 'mininterpolationoffset':
        case 'maxinterpolationoffset':
        case 'timestampperiod':
        case 'pointsizegranularity':
        case 'linewidthgranularity':
            return floatval($value);
        case 'pipelinecacheuuid':
        case 'deviceuuid':
        case 'driveruuid':
            return unserialize($value);
        case 'deviceluid':
            return array_slice(unserialize($value), 0, 8);
        // Boolean
        case 'deviceluidvalid':
        case 'protectednofault':
        case 'subgroupquadoperationsinallstages':
        case 'shadersignedzeroinfnanpreservefloat16':
        case 'shadersignedzeroinfnanpreservefloat32':
        case 'shadersignedzeroinfnanpreservefloat64':
        case 'shaderdenormpreservefloat16':
        case 'shaderdenormpreservefloat32':
        case 'shaderdenormpreservefloat64':
        case 'shaderdenormflushtozerofloat16':
        case 'shaderdenormflushtozerofloat32':
        case 'shaderdenormflushtozerofloat64':
        case 'shaderroundingmodertefloat16':
        case 'shaderroundingmodertefloat32':
        case 'shaderroundingmodertefloat64':
        case 'shaderroundingmodertzfloat16':
        case 'shaderroundingmodertzfloat32':
        case 'shaderroundingmodertzfloat64':
        case 'shaderuniformbufferarraynonuniformindexingnative':
        case 'shadersampledimagearraynonuniformindexingnative':
        case 'shaderstoragebufferarraynonuniformindexingnative':
        case 'shaderstorageimagearraynonuniformindexingnative':
        case 'shaderinputattachmentarraynonuniformindexingnative':
        case 'robustbufferaccessupdateafterbind':
        case 'quaddivergentimplicitlod':
        case 'independentresolvenone':
        case 'independentresolve':
        case 'filterminmaxsinglecomponentformats':
        case 'filterminmaximagecomponentmapping':
        case 'idp8bitunsignedaccelerated':
        case 'idp8bitsignedaccelerated':
        case 'idp8bitmixedsignednessaccelerated':
        case 'idp4x8bitpackedunsignedaccelerated':
        case 'idp4x8bitpackedsignedaccelerated':
        case 'idp4x8bitpackedmixedsignednessaccelerated':
        case 'idp16bitunsignedaccelerated':
        case 'idp16bitsignedaccelerated':
        case 'idp16bitmixedsignednessaccelerated':
        case 'idp32bitunsignedaccelerated':
        case 'idp32bitsignedaccelerated':
        case 'idp32bitmixedsignednessaccelerated':
        case 'idp64bitunsignedaccelerated':
        case 'idp64bitsignedaccelerated':
        case 'idp64bitmixedsignednessaccelerated':
        case 'idpaccumulatingsaturating8bitunsignedaccelerated':
        case 'idpaccumulatingsaturating8bitsignedaccelerated':
        case 'idpaccumulatingsaturating8bitmixedsignednessaccelerated':
        case 'idpaccumulatingsaturating4x8bitpackedunsignedaccelerated':
        case 'idpaccumulatingsaturating4x8bitpackedsignedaccelerated':
        case 'idpaccumulatingsaturating4x8bitpackedmixedsignednessaccelerated':
        case 'idpaccumulatingsaturating16bitunsignedaccelerated':
        case 'idpaccumulatingsaturating16bitsignedaccelerated':
        case 'idpaccumulatingsaturating16bitmixedsignednessaccelerated':
        case 'idpaccumulatingsaturating32bitunsignedaccelerated':
        case 'idpaccumulatingsaturating32bitsignedaccelerated':
        case 'idpaccumulatingsaturating32bitmixedsignednessaccelerated':
        case 'idpaccumulatingsaturating64bitunsignedaccelerated':
        case 'idpaccumulatingsaturating64bitsignedaccelerated':
        case 'idpaccumulatingsaturating64bitmixedsignednessaccelerated':
        case 'storagetexelbufferoffsetsingletexelalignment':
        case 'uniformtexelbufferoffsetsingletexelalignment':            
        case 'timestampcomputeandgraphics':
        case 'strictlines':
        case 'standardsamplelocations':
            return boolval($value);
        case 'conformanceversion':
            $parts = explode('.', $value);
            $ret_val = [
                'major' => intval($parts[0]),
                'minor' => intval($parts[1]),
                'patch' => intval($parts[2]),
                'subminor' => intval($parts[3]),
            ];
            return $ret_val;
        case 'requiredsubgroupsizestages':
        case 'subgroupsupportedstages':
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
            $ret_val = getVkFlags($flags, $value);
            return $ret_val;
        case 'supporteddepthresolvemodes':
        case 'supportedstencilresolvemodes':
            $flags = [
                0 => 'VK_RESOLVE_MODE_NONE',
                0x00000001 => 'VK_RESOLVE_MODE_SAMPLE_ZERO_BIT',
                0x00000002 => 'VK_RESOLVE_MODE_AVERAGE_BIT',
                0x00000004 => 'VK_RESOLVE_MODE_MIN_BIT',
                0x00000008 => 'VK_RESOLVE_MODE_MAX_BIT',
            ];
            $ret_val = getVkFlags($flags, $value);
            return $ret_val;
        case 'denormbehaviorindependence':
        case 'roundingmodeindependence':
            $lookup = [
                'VK_SHADER_FLOAT_CONTROLS_INDEPENDENCE_32_BIT_ONLY' => 0,
                'VK_SHADER_FLOAT_CONTROLS_INDEPENDENCE_ALL' => 1,
                'VK_SHADER_FLOAT_CONTROLS_INDEPENDENCE_NONE' => 2,
            ];          
            $ret_val = getVkValue($lookup, $value);
            return $ret_val;
        case 'pointclippingbehavior':
            $lookup = [
                'VK_POINT_CLIPPING_BEHAVIOR_ALL_CLIP_PLANES' => 0,
                'VK_POINT_CLIPPING_BEHAVIOR_USER_CLIP_PLANES_ONLY' => 1,
            ];          
            $ret_val = getVkValue($lookup, $value);
            return $ret_val;
        case 'subgroupsupportedoperations':
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
            $ret_val = getVkFlags($flags, $value);
            return $ret_val;
        case 'driverid':
            $lookup = [
                'VK_DRIVER_ID_AMD_PROPRIETARY' => 1,
                'VK_DRIVER_ID_AMD_OPEN_SOURCE' => 2,
                'VK_DRIVER_ID_MESA_RADV' => 3,
                'VK_DRIVER_ID_NVIDIA_PROPRIETARY' => 4,
                'VK_DRIVER_ID_INTEL_PROPRIETARY_WINDOWS' => 5,
                'VK_DRIVER_ID_INTEL_OPEN_SOURCE_MESA' => 6,
                'VK_DRIVER_ID_IMAGINATION_PROPRIETARY' => 7,
                'VK_DRIVER_ID_QUALCOMM_PROPRIETARY' => 8,
                'VK_DRIVER_ID_ARM_PROPRIETARY' => 9,
                'VK_DRIVER_ID_GOOGLE_SWIFTSHADER' => 10,
                'VK_DRIVER_ID_GGP_PROPRIETARY' => 11,
                'VK_DRIVER_ID_BROADCOM_PROPRIETARY' => 12,
                'VK_DRIVER_ID_MESA_LLVMPIPE' => 13,
                'VK_DRIVER_ID_MOLTENVK' => 14,
                'VK_DRIVER_ID_COREAVI_PROPRIETARY' => 15,
                'VK_DRIVER_ID_JUICE_PROPRIETARY' => 16,
                'VK_DRIVER_ID_VERISILICON_PROPRIETARY' => 17,
                'VK_DRIVER_ID_MESA_TURNIP' => 18,
                'VK_DRIVER_ID_MESA_V3DV' => 19,
                'VK_DRIVER_ID_MESA_PANVK' => 20,
            ];
            $ret_val = getVkValue($lookup, $value);
            return $ret_val;
        case 'framebufferintegercolorsamplecounts':
        case 'framebuffercolorsamplecounts':
        case 'framebufferdepthsamplecounts':
        case 'framebufferstencilsamplecounts':
        case 'framebuffernoattachmentssamplecounts':
        case 'sampledimagecolorsamplecounts':
        case 'sampledimageintegersamplecounts':
        case 'sampledimagedepthsamplecounts':
        case 'sampledimagestencilsamplecounts':
        case 'storageimagesamplecounts':
            $flags = [
                0x00000001 => 'VK_SAMPLE_COUNT_1_BIT',
                0x00000002 => 'VK_SAMPLE_COUNT_2_BIT',
                0x00000004 => 'VK_SAMPLE_COUNT_4_BIT',
                0x00000008 => 'VK_SAMPLE_COUNT_8_BIT',
                0x00000010 => 'VK_SAMPLE_COUNT_16_BIT',
                0x00000020 => 'VK_SAMPLE_COUNT_32_BIT',
                0x00000040 => 'VK_SAMPLE_COUNT_64_BIT',
            ];
            $ret_val = getVkFlags($flags, $value);
            return $ret_val;            
        case 'queueflags':
            $flags = [
                0x00000001 => 'VK_QUEUE_GRAPHICS_BIT',
                0x00000002 => 'VK_QUEUE_COMPUTE_BIT',
                0x00000004 => 'VK_QUEUE_TRANSFER_BIT',
                0x00000008 => 'VK_QUEUE_SPARSE_BINDING_BIT',
                0x00000010 => 'VK_QUEUE_PROTECTED_BIT',
                0x00000020 => 'VK_QUEUE_VIDEO_DECODE_BIT_KHR',
                0x00000040 => 'VK_QUEUE_VIDEO_ENCODE_BIT_KHR',
            ];
            $ret_val = getVkFlags($flags, $value);
            return $ret_val;            
    }
    return $value;
}

function getVkFormatFlags($flag) {
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

class VulkanProfile {
    private $reportid = null;
    private $queue_families = [];
    private $formats = [];
    private $extensions = [];
    private $features = [];
    private $extension_features = [];
    private $properties = [];

    private $profile_name = 'device';
    private $profile_version = 1;
    private $device_name = null;
    private $api_version = null;
    
    public $json = null;

    function __construct($reportid) {
        $this->reportid = $reportid;
    }

    private function readFeatures($version) {
        $table_names = [
            '1.0' => 'devicefeatures',
            '1.1' => 'devicefeatures11',
            '1.2' => 'devicefeatures12',
            '1.3' => 'devicefeatures13',
        ];
        $table = $table_names[$version];
        $stmnt = DB::$connection->prepare("SELECT * from $table where reportid = :reportid");
        $stmnt->execute([":reportid" => $this->reportid]);
        $result = $stmnt->fetch(PDO::FETCH_ASSOC);
        if ($stmnt->rowCount() == 0) {
            return null;
        }
        $features = [];
        foreach ($result as $key => $value) {
            if (skipField($key, $version)) {
                continue;
            }
            $features[$key] = boolval($value);
        }
        return count($features) > 0 ? $features : null;
    }

    /** Vulkan 1.0 device limits */
    private function readDeviceLimits() {
        $limit_stmnt = DB::$connection->prepare('SELECT * from devicelimits where reportid = :reportid');
        $limit_stmnt->execute([":reportid" => $this->reportid]);
        $limit_result = $limit_stmnt->fetch(PDO::FETCH_ASSOC);
        foreach ($limit_result as $limit_key => $limit_value) {
            if (skipField($limit_key, '1.0')) {
                continue;
            }
            $limits[$limit_key] = convertFieldValue($limit_key, $limit_value);
        }    
        
        $limitToArray = function($name, $dim, $type) use ($limit_result) {
            $values = [];
            for ($i = 0; $i < $dim; $i++) {
                if ($type == 'int') {
                    $values[] = intval($limit_result[$name.'['.$i.']']);
                };
                if ($type == 'float') {
                    $values[] = floatval($limit_result[$name.'['.$i.']']);
                };
            }
            return $values;
        };
    
        // Multi-dimensional arrays are stored as single columns in the database and need to be remapped        
        $limit_properties['maxComputeWorkGroupCount'] = $limitToArray('maxComputeWorkGroupCount', 3, 'int');
        $limit_properties['maxViewportDimensions'] = $limitToArray('maxViewportDimensions', 2, 'int');
        $limit_properties['pointSizeRange'] = $limitToArray('pointSizeRange', 2, 'float');
        $limit_properties['viewportBoundsRange'] = $limitToArray('viewportBoundsRange', 2, 'float');
        $limit_properties['lineWidthRange'] = $limitToArray('lineWidthRange', 2, 'float');
    
        return $limits;
    }

    private function readProperties($version) {
        $table_names = [
            '1.0' => 'deviceproperties',
            '1.1' => 'deviceproperties11',
            '1.2' => 'deviceproperties12',
            '1.3' => 'deviceproperties13',
        ];
        $table = $table_names[$version];
        $stmnt = DB::$connection->prepare("SELECT * from $table where reportid = :reportid");
        $stmnt->execute([":reportid" => $this->reportid]);
        $result = $stmnt->fetch(PDO::FETCH_ASSOC);
        if ($stmnt->rowCount() == 0) {
            return null;
        }
        $properties = [];
        foreach ($result as $key => $value) {
            if (skipField($key, $version)) {
                continue;
            }
            $key_name = $key;
            if ($version == '1.0') {
                // Use non-human readable api version
                if ($key == 'apiversion') {
                    continue;
                }
                if ($key == 'apiversionraw') {
                    $key_name = 'apiversion';
                }
            }
            $converted_value = convertFieldValue($key_name, $value);
            $properties[capitalizeFieldName($key_name)] = $converted_value;
        }
        if ($version == '1.0') {
            // Remap sparse properties into struct
            $sparse_stmnt = DB::$connection->prepare('SELECT residencyAlignedMipSize, residencyNonResidentStrict, residencyStandard2DBlockShape, residencyStandard2DMultisampleBlockShape, residencyStandard3DBlockShape from deviceproperties where reportid = :reportid');
            $sparse_stmnt->execute([":reportid" => $this->reportid]);
            $sparse_result = $sparse_stmnt->fetch(PDO::FETCH_ASSOC);
            foreach ($sparse_result as $sparse_key => $sparse_value) {
                $sparse_properties[$sparse_key] = boolval($sparse_value);
            }
            $properties['sparseProperties'] = $sparse_properties;
            // Append VK1.0 limits
            $properties['limits'] = $this->readDeviceLimits();
        }
        return $properties;        
    }

    function readExtensionFeatures() {
        // Build list of core api versions to skip based on device's api level
        $api_version_skip_list = [];
        $api_major = explode('.', $this->api_version)[0];
        $api_minor = explode('.', $this->api_version)[1];
        if ($api_minor >= 1) {
            $api_version_skip_list[] = 'VK_VERSION_1_1';
        }
        if ($api_minor >= 2) {
            $api_version_skip_list[] = 'VK_VERSION_1_2';
        }
        if ($api_minor >= 3) {
            $api_version_skip_list[] = 'VK_VERSION_1_3';
        }
        $stmnt = DB::$connection->prepare("SELECT extension, name, supported from devicefeatures2 where reportid = :reportid");
        $stmnt->execute([":reportid" => $this->reportid]);
        $result = $stmnt->fetchAll(PDO::FETCH_GROUP  | PDO::FETCH_ASSOC);
        foreach ($result as $key => $values) {
            if (!array_key_exists($key, Mappings::$extensions)) {
                continue;
            }
            $ext = Mappings::$extensions[$key];
            if ($ext['struct_type_physical_device_features'] == '') {
                continue;
            }            
            // Skip feature structs that have been promoted to a core version supported by the device
            if ($ext['promoted_to'] !== '') {
                if (stripos($ext['promoted_to'], 'VK_VERSION') !== false) {
                    if (in_array($ext['promoted_to'], $api_version_skip_list)) {
                        continue;
                    }
                }
            }
            // @todo: only include those not part of the reports api version (promotedto)
            $feature = null;
            foreach ($values as $value) {
                $feature[$value['name']] = boolval($value['supported']);
            }
            $this->extension_features[$ext['struct_type_physical_device_features']] = $feature;
        }
    }    

    private function readExtensions() {
        $this->extensions = [];
        $stmnt = DB::$connection->prepare("SELECT name, specversion from deviceextensions de join extensions e on de.extensionid = e.id where reportid = :reportid");
        $stmnt->execute([":reportid" => $this->reportid]);
        while ($row = $stmnt->fetch(PDO::FETCH_ASSOC)) {
           $this->extensions[$row['name']] = intval($row['specversion']);
        }
    }

    private function readFormats() {
        $this->formats = [];
        $stmnt = DB::$connection->prepare("SELECT name, lineartilingfeatures, optimaltilingfeatures, bufferfeatures from deviceformats df join VkFormat vf on df.formatid = vf.value where reportid = :reportid and supported = 1 order by name asc");    
        $stmnt->execute([":reportid" => $this->reportid]);
        while ($row = $stmnt->fetch(PDO::FETCH_ASSOC)) {
            $format = [
                'VkFormatProperties' => [
                    'linearTilingFeatures' => getVkFormatFlags($row['lineartilingfeatures']),
                    'optimalTilingFeatures' => getVkFormatFlags($row['optimaltilingfeatures']),
                    'bufferFeatures' => getVkFormatFlags($row['bufferfeatures'])
                ]
            ];
            $this->formats["VK_FORMAT_".$row['name']] = $format;
        }    
    }

    private function readQueueFamilies() {
        $this->queue_families = [];
        $stmnt = DB::$connection->prepare("SELECT * from devicequeues where reportid = :reportid");
        $stmnt->execute([":reportid" => $this->reportid]);
        while ($row = $stmnt->fetch(PDO::FETCH_ASSOC)) {
            $profile_queue_family = [
                'VkQueueFamilyProperties' => [
                    'queueFlags' => convertFieldValue('queueFlags', $row['flags']),
                    'queueCount' => intval($row['count']),
                    'timestampValidBits' => intval($row['timestampValidBits']),
                    'minImageTransferGranularity' => [
                        'width' => intval($row['minImageTransferGranularity.width']),
                        'height' => intval($row['minImageTransferGranularity.height']),
                        'depth' => intval($row['minImageTransferGranularity.depth']),
                    ]
                ]
            ];
            $this->queue_families[] = $profile_queue_family;
        }   
    }

    private function readDeviceInfo() {
        $stmnt = DB::$connection->prepare("SELECT * from reports where id = :reportid");
        $stmnt->execute([":reportid" => $this->reportid]);
        if ($stmnt->rowCount() == 0) {
            header('HTTP/ 400 missing_or');
            exit("Could not find report");
        }
        $row = $stmnt->fetch(PDO::FETCH_ASSOC);
        $this->device_name = $row['devicename'];
        $this->api_version = $row['apiversion'];
    }

    function generateJSON() {
        $api_versions =  ['1.0', '1.1', '1.2', '1.3'];

        DB::connect();
        $this->readDeviceInfo();
        $this->readExtensions();
        foreach ($api_versions as $version) {
            $this->features[$version] = $this->readFeatures($version);
            $this->properties[$version] = $this->readProperties($version);
        }
        $this->readExtensionFeatures();
        $this->readFormats();
        $this->readQueueFamilies();
        DB::disconnect();

        $this->json['$schema'] = 'https://schema.khronos.org/vulkan/profiles-1.3.204.json#';        
        $this->json['profiles'] = [
            'GPUINFO_Exported_Profile' => [
                "version" => $this->profile_version,
                "api-version" => $this->api_version,
                "label" => "$this->device_name",
                "description" => "Exported from https://vulkan.gpuinfo.org",
                "contributors" => [],
                "history" => [],
                "capabilities" => [$this->profile_name]
            ]
        ];

        // Features
        foreach ($api_versions as $version) {
            $node_names = [
                '1.0' => ['requirement' => 'vulkan10requirements', 'struct' => 'VkPhysicalDeviceFeatures'],
                '1.1' => ['requirement' => 'vulkan11requirements', 'struct' => 'VkPhysicalDeviceVulkan11Features'],
                '1.2' => ['requirement' => 'vulkan12requirements', 'struct' => 'VkPhysicalDeviceVulkan12Features'],
                '1.3' => ['requirement' => 'vulkan13requirements', 'struct' => 'VkPhysicalDeviceVulkan13Features'],
            ];
            if (array_key_exists($version, $this->features) && count($this->features[$version]) > 0) {
                $this->json['capabilities'][$this->profile_name]['features'][$node_names[$version]['struct']] = $this->features[$version];
            }
        }
        if (count($this->extension_features) > 0) {
            foreach ($this->extension_features as $ext => $features) {
                $this->json['capabilities'][$this->profile_name]['features'][$ext] = $features;
            }
        }

        // Properties   
        foreach ($api_versions as $version) {
            $node_names = [
                '1.0' => ['requirement' => 'vulkan10requirements', 'struct' => 'VkPhysicalDeviceProperties'],
                '1.1' => ['requirement' => 'vulkan11requirements', 'struct' => 'VkPhysicalDeviceVulkan11Properties'],
                '1.2' => ['requirement' => 'vulkan12requirements', 'struct' => 'VkPhysicalDeviceVulkan12Properties'],
                '1.3' => ['requirement' => 'vulkan13requirements', 'struct' => 'VkPhysicalDeviceVulkan13Properties'],
            ];
            if (array_key_exists($version, $this->properties) && count($this->properties[$version]) > 0) {
                $this->json['capabilities'][$this->profile_name]['properties'][$node_names[$version]['struct']] = $this->properties[$version];
            }
        }

        if ($this->extensions && (count($this->extensions) > 0)) {
            $this->json['capabilities'][$this->profile_name]['extensions'] = $this->extensions;
        } else {
            $this->json['capabilities'][$this->profile_name]['extensions'] = (object)null;
        }

        if ($this->formats && (count($this->formats) > 0)) {
            $this->json['capabilities'][$this->profile_name]['formats'] = $this->formats;
        } else {
            $this->json['capabilities'][$this->profile_name]['formats'] = (object)null;
        }

        if ($this->queue_families && (count($this->queue_families) > 0)) {
            $this->json['capabilities'][$this->profile_name]['queueFamiliesProperties'] = $this->queue_families;
        } else {
            $this->json['capabilities'][$this->profile_name]['queueFamiliesProperties'] = (object)null;
        }
    }
}
// Profile generation

$profile = new VulkanProfile($reportid);
$profile->generateJSON();

$filename = $device_name;
$filename = preg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $filename);
$filename = preg_replace("([\.]{2,})", '', $filename);	
$filename .= ".json";

header("Content-type: application/json");
// header("Content-Disposition: attachment; filename=".strtolower($filename));
echo json_encode($profile->json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

// @todo
//  insertDeviceExtensionFeatures($reportid, $profile_caps);

DB::disconnect();