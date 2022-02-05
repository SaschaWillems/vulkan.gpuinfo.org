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

include './../../database/database.class.php';
include './../../includes/functions.php';
	
if (!isset($_GET['id'])) {
    header('HTTP/ 400 missing_or');
    echo "No report id specified!";
    die();
}

DB::connect();

$reportid = $_GET['id'];	
$json_data = null;

$stmnt = DB::$connection->prepare("SELECT * from reports where id = :reportid");
$stmnt->execute([":reportid" => $reportid]);
if ($stmnt->rowCount() == 0) {
    DB::disconnect();
    header('HTTP/ 400 missing_or');
    echo "No report id specified!";
    die();
}
$row = $stmnt->fetch(PDO::FETCH_ASSOC);
$device_name = $row['devicename'];
$api_version = $row['apiversion'];

$capabilities = [];
$profile_info = [];

$profile_info['GPUINFO_Exported_Profile'] = [
    "version" => 1,
    "api-version" => $api_version,
    "label" => "$device_name",
    "description" => "Exported from https://vulkan.gpuinfo.org",
    "contributors" => [],
    "history" => [],
    "capabilities" => ["baseline"]
];

$profile_caps['baseline'] = [
    'extensions' => [],
    'features' => (object)null,
    'properties' => (object)null,
    'formats' => (object)null,
    'queueFamiliesProperties' => []
];

function skipField($name) {
    $skip_fields = [
        'reportid',
        'headerversion',
        'productModel',
        'productManufacturer',
        'apiversionraw',
        'deviceid',
        'devicename',
        'devicetype',
        'driverversion',
        'driverversionraw',
    ];
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

function convertFieldValue($name, $value) {
    switch (strtolower($name)) {
        case 'vendorid':
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
            return intval($value);
        case 'pipelinecacheuuid':
        case 'deviceuuid':
        case 'driveruuid':
        case 'deviceluid':
            return unserialize($value);
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
    }
    return $value;
}

function insertDeviceFeatures($version, $reportid, &$cap_node) {
    $table = 'devicefeatures';
    $req_name = 'vulkan10requirements';
    $struct_name = 'VkPhysicalDeviceFeatures';
    switch ($version) {
        case '1.1':
            $table = 'devicefeatures11';
            $req_name = 'vulkan11requirements';
            $struct_name = 'VkPhysicalDeviceVulkan11Features';
            break;
        case '1.2':
            $table = 'devicefeatures12';
            $req_name = 'vulkan12requirements';
            $struct_name = 'VkPhysicalDeviceVulkan12Features';
            break;
        case '1.3':
            $table = 'devicefeatures13';
            $req_name = 'vulkan13requirements';
            $struct_name = 'VkPhysicalDeviceVulkan13Features';
            break;
    }
    $stmnt = DB::$connection->prepare("SELECT * from $table where reportid = :reportid");
    $stmnt->execute([":reportid" => $reportid]);
    $result = $stmnt->fetch(PDO::FETCH_ASSOC);
    if ($stmnt->rowCount() == 0) {
        return;
    }
    $features_node = [];
    foreach ($result as $key => $value) {
        if (skipField($key)) {
            continue;
        }
        $features_node[$key] = boolval($value);
    }
    $cap_node[$req_name]['features'][$struct_name] = $features_node;
}

function insertDeviceProperties($version, $reportid, &$cap_node) {
    // @todo: limits for vk1.0
    // @todo: sparse properties for vk1.0
    $table = 'deviceproperties';
    $req_name = 'vulkan10requirements';
    $struct_name = 'VkPhysicalDeviceProperties';
    switch ($version) {
        case '1.1':
            $table = 'deviceproperties11';
            $req_name = 'vulkan11requirements';
            $struct_name = 'VkPhysicalDeviceVulkan11Properties';
            break;
        case '1.2':
            $table = 'deviceproperties12';
            $req_name = 'vulkan12requirements';
            $struct_name = 'VkPhysicalDeviceVulkan12Properties';
            break;
        case '1.3':
            $table = 'deviceproperties13';
            $req_name = 'vulkan13requirements';
            $struct_name = 'VkPhysicalDeviceVulkan13Properties';
            break;
    }
    $stmnt = DB::$connection->prepare("SELECT * from $table where reportid = :reportid");
    $stmnt->execute([":reportid" => $reportid]);
    $result = $stmnt->fetch(PDO::FETCH_ASSOC);
    if ($stmnt->rowCount() == 0) {
        return;
    }
    $features_node = [];
    foreach ($result as $key => $value) {
        if (skipField($key)) {
            continue;
        }
        $converted_value = convertFieldValue($key, $value);
        $features_node[capitalizeFieldName($key)] = $converted_value;
    }
    $cap_node[$req_name]['properties'][$struct_name] = $features_node;
}

$versions = ['1.0', '1.1', '1.2', '1.3'];

foreach ($versions as $version) {
    insertDeviceFeatures($version, $reportid, $profile_caps);
    insertDeviceProperties($version, $reportid, $profile_caps);
}

// Extensions
$stmnt = DB::$connection->prepare("SELECT name, specversion from deviceextensions de join extensions e on de.extensionid = e.id where reportid = :reportid");
$stmnt->execute([":reportid" => $reportid]);
while ($row = $stmnt->fetch(PDO::FETCH_ASSOC)) {
    $profile_caps['baseline']['extensions'][$row['name']] = intval($row['specversion']);
}

$profile['$schema'] = 'https://schema.khronos.org/vulkan/profiles-1.3.204.json#';
$profile['profiles'] = $profile_info;
$profile['capabilities'] = $profile_caps;

$filename = $device_name;
$filename = preg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $filename);
$filename = preg_replace("([\.]{2,})", '', $filename);	
$filename .= ".json";

header("Content-type: application/json");
// header("Content-Disposition: attachment; filename=".strtolower($filename));

echo json_encode($profile, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

DB::disconnect();