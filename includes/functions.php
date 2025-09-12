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
 */

function versionToString($version)
{
	$versionStr = ($version >> 22) . "." . (($version >> 12) & 0x3ff) . "." . ($version & 0xfff);
	return $versionStr;
}

function getFlags($flagList, $flag)
{
	$flags = array();

	$arrVals = array_values($flagList);

	$index = 0;
	foreach ($flagList as $i => $value) {
		if ($flag & $i) {
			$flags[] = $arrVals[$index];
		}
		$index++;
	}
	return $flags;
}


function getImageUsageFlags($flag)
{
	$flags = array(
		0x0001 => "TRANSFER_SRC_BIT",
		0x0002 => "TRANSFER_DST_BIT",
		0x0004 => "SAMPLED_BIT",
		0x0008 => "STORAGE_BIT",
		0x0010 => "COLOR_ATTACHMENT_BIT",
		0x0020 => "DEPTH_STENCIL_ATTACHMENT_BIT",
		0x0040 => "TRANSIENT_ATTACHMENT_BIT",
		0x0080 => "INPUT_ATTACHMENT_BIT",
	);
	return getFlags($flags, $flag);
}

function getSurfaceTransformFlags($flag)
{
	$flags = array(
		0x0001 => "IDENTITY_BIT_KHR",
		0x0002 => "ROTATE_90_BIT_KHR",
		0x0004 => "ROTATE_180_BIT_KHR",
		0x0008 => "ROTATE_270_BIT_KHR",
		0x0010 => "HORIZONTAL_MIRROR_BIT_KHR",
		0x0020 => "HORIZONTAL_MIRROR_ROTATE_90_BIT_KHR",
		0x0040 => "HORIZONTAL_MIRROR_ROTATE_180_BIT_KHR",
		0x0080 => "HORIZONTAL_MIRROR_ROTATE_270_BIT_KHR",
		0x0100 => "INHERIT_BIT_KHR",
	);
	return getFlags($flags, $flag);
}

function getCompositeAlphaFlags($flag)
{
	$flags = array(
		0x0001 => "OPAQUE_BIT_KHR",
		0x0002 => "PRE_MULTIPLIED_BIT_KHR",
		0x0004 => "POST_MULTIPLIED_BIT_KHR",
		0x0008 => "INHERIT_BIT_KHR",
	);
	return getFlags($flags, $flag);
}

function getMemoryTypeFlags($flag)
{
	$flags = array(
		0x0001 => "DEVICE_LOCAL_BIT",
		0x0002 => "HOST_VISIBLE_BIT",
		0x0004 => "HOST_COHERENT_BIT",
		0x0008 => "HOST_CACHED_BIT",
		0x0010 => "LAZILY_ALLOCATED_BIT",
		0x0020 => "PROTECTED_BIT",
		0x0040 => "DEVICE_COHERENT_BIT_AMD",
		0x0080 => "DEVICE_UNCACHED_BIT_AMD",
		0x0100 => "RDMA_CAPABLE_BIT_NV"
	);
	return getFlags($flags, $flag);
}

function getMemoryHeapFlags($flag)
{
	$flags = array(
		0x0001 => "DEVICE_LOCAL_BIT",
	);
	return getFlags($flags, $flag);
}

function getQueueFlags($flag)
{
	$flags = array(
		0x0001 => "GRAPHICS_BIT",
		0x0002 => "COMPUTE_BIT",
		0x0004 => "TRANSFER_BIT",
		0x0008 => "SPARSE_BINDING_BIT",
		0x0010 => "PROTECTED_BIT",
		0x0020 => "VIDEO_DECODE_BIT_KHR",
		0x0040 => "VIDEO_ENCODE_BIT_KHR",
	);
	return getFlags($flags, $flag);
}


function listSubgroupFeatureFlags($flag)
{
	$flags = array(
		0x0001 => "BASIC",
		0x0002 => "VOTE",
		0x0004 => "ARITHMETIC",
		0x0008 => "BALLOT",
		0x0010 => "SHUFFLE",
		0x0020 => "SHUFFLE (RELATIVE)",
		0x0040 => "CLUSTERED",
		0x0080 => "QUAD",
		0x0100 => "PARTITIONED_BIT_NV"
	);

	if ($flag === null) {
		return "<span class='na'>n/a</span>";
	}
	
	$res = null;
	$arr_values = array_values($flags);
	$index = 0;
	foreach ($flags as $i => $value) {
		$class = ($flag & $i) ? "supported" : "na";
		$res .= "<span class='" . $class . "'>" . strtolower($arr_values[$index]) . "</span><br>";
		$index++;
	}
	return $res;
}

function listSubgroupStageFlags($flag)
{
	$flags = array(
		0x0001 => "VERTEX",
		0x0002 => "TESSELLATION CONTROL",
		0x0004 => "TESSELLATION EVALUATION",
		0x0008 => "GEOMETRY",
		0x0010 => "FRAGMENT",
		0x0020 => "COMPUTE",
		0x0040 => "TASK",
		0x0080 => "MESH",		
		0x001F => "ALL GRAPHICS",
	);

	if ($flag === null) {
		return "<span class='na'>n/a</span>";
	}

	$res = null;
	$arr_values = array_values($flags);
	$index = 0;
	foreach ($flags as $i => $value) {
		if ($i == 0x001F) {
			$class = (($flag & $i) == $i) ? "supported" : "na";
		} else {
			$class = ($flag & $i) ? "supported" : "na";
		}
		$res .= "<span class='" . $class . "'>" . strtolower($arr_values[$index]) . "</span><br>";
		$index++;
	}
	return $res;
}

function listSampleCountFlags($value)
{
	$flags = [
		0x0001 => '1',
		0x0002 => '2',
		0x0004 => '4',
		0x0008 => '8',
		0x0010 => '16',
		0x0020 => '32',
		0x0040 => '64',
	];
	$res = [];
	foreach ($flags as $flag => $text) {
		$class = (($value & $flag) == $flag) ? "supported" : "unsupported-grey";
		$res[] = "<span class='" . $class . "'>$text</span>";
	}
	return implode(', ', $res);
}

function listResolveModeFlags($value)
{
	$flags = [
		0x0001 => 'Zero',
		0x0002 => 'Average',
		0x0004 => 'Minimum',
		0x0008 => 'Maximum',
	];
	$res = [];
	foreach ($flags as $flag => $text) {
		$class = (($value & $flag) == $flag) ? "supported" : "unsupported-grey";
		$res[] = "<span class='" . $class . "'>$text</span>";
	}
	return implode(', ', $res);
}

// Generate a simple ul/li list for the flags
function listFlags($flags)
{
	if (sizeof($flags) > 0) {
		foreach ($flags as $flag) {
			echo $flag . "<br>";
		}
	} else {
		echo "none";
	}
}
function listImageLayouts($value) {
	$formats = [
		0 => 'UNDEFINED',
		1 => 'GENERAL',
		2 => 'COLOR_ATTACHMENT_OPTIMAL',
		3 => 'DEPTH_STENCIL_ATTACHMENT_OPTIMAL',
		4 => 'DEPTH_STENCIL_READ_ONLY_OPTIMAL',
		5 => 'SHADER_READ_ONLY_OPTIMAL',
		6 => 'TRANSFER_SRC_OPTIMAL',
		7 => 'TRANSFER_DST_OPTIMAL',
		8 => 'PREINITIALIZED',
		1000117000 => 'DEPTH_READ_ONLY_STENCIL_ATTACHMENT_OPTIMAL',
		1000117001 => 'DEPTH_ATTACHMENT_STENCIL_READ_ONLY_OPTIMAL',
		1000241000 => 'DEPTH_ATTACHMENT_OPTIMAL',
		1000241001 => 'DEPTH_READ_ONLY_OPTIMAL',
		1000241002 => 'STENCIL_ATTACHMENT_OPTIMAL',
		1000241003 => 'STENCIL_READ_ONLY_OPTIMAL',
		1000314000 => 'READ_ONLY_OPTIMAL',
		1000314001 => 'ATTACHMENT_OPTIMAL',
		1000001002 => 'PRESENT_SRC_KHR',
		1000024000 => 'VIDEO_DECODE_DST_KHR',
		1000024001 => 'VIDEO_DECODE_SRC_KHR',
		1000024002 => 'VIDEO_DECODE_DPB_KHR',
		1000111000 => 'SHARED_PRESENT_KHR',
		1000218000 => 'FRAGMENT_DENSITY_MAP_OPTIMAL_EXT',
		1000164003 => 'FRAGMENT_SHADING_RATE_ATTACHMENT_OPTIMAL_KHR',
		1000299000 => 'VIDEO_ENCODE_DST_KHR',
		1000299001 => 'VIDEO_ENCODE_SRC_KHR',
		1000299002 => 'VIDEO_ENCODE_DPB_KHR',
		1000339000 => 'ATTACHMENT_FEEDBACK_LOOP_OPTIMAL_EXT',  
	];     

	try {
		$arr = unserialize($value);
		foreach ($arr as &$val) {
			if (array_key_exists($val, $formats)) {
				$val = $formats[$val];
			} else {
				$val = 'UNKNOWN LAYOUT';
			}
		}
		return implode('<br>', $arr);
	} catch (Throwable $e) {
		return null;
	}
}

function listCooperativeMatrixProperties($value)
{
	$displayValues = [];
	try {
		// Value stores an array of VkCooperativeMatrixPropertiesKHR, which have been turned into an array themselves
		// aaa
		$arr = unserialize($value);
		foreach ($arr as $index => &$propArray) {
			$tableTemplate =
			"<table class='ext-prop-table'>
			<tr> <td>MSize</td> <td>$propArray[0]</td> </tr>
			<tr> <td>NSize</td> <td>$propArray[1]</td> </tr>
			<tr> <td>KSize</td> <td>$propArray[2]</td> </tr>
			<tr> <td>AType</td> <td>".getComponentTypeString($propArray[3])."</td> </tr>
			<tr> <td>BType</td> <td>".getComponentTypeString($propArray[4])."</td> </tr>
			<tr> <td>CType</td> <td>".getComponentTypeString($propArray[5])."</td> </tr>
			<tr> <td>ResultType</td> <td>".getComponentTypeString($propArray[6])."</td> </tr>
			<tr> <td>sat.Accum.</td> <td>".displayBool($propArray[7])."</td> </tr>
			<tr> <td>scope</td> <td>".getScopString($propArray[8])."</td> </tr>
			</table>";
			$displayValues[] = "[$index]<br>$tableTemplate";
		}
		return implode('<br>', $displayValues);
	} catch (Throwable $e) {
		return null;
	}	
}

function getShaderFloatControlsIndependence($value)
{
	$values = [
		'32-bit only' => 0,
		'All bit widths' => 1,
		'None' => 2,
	];
	return (in_array($value, $values) ? array_search($value, $values) : null);
}

function getPointClippingBehavior($value)
{
	$values = [
		'All clip planes' => 0,
		'User clip planes only' => 1
	];
	return (in_array($value, $values) ? array_search($value, $values) : null);
}

function getDriverId($value)
{
	$values = [
		'AMD (Proprietary)' => 1,
		'AMD (Open Source)' => 2,
		'Mesa RADV' => 3,
		'NVIDIA (Proprietary)' => 4,
		'Intel Windows (Proprietary)' => 5,
		'Intel Mesa (Open Source)' => 6,
		'Imagination (Proprietary)' => 7,
		'Qualcomm (Proprietary)' => 8,
		'ARM (Proprietary)' => 9,
		'Google Swiftshader' => 10,
		'GGP (Proprietary)' => 11,
		'Broadcom (Proprietary)' => 12,
		'Mesa LLVMPIPE' => 13,
		'MoltenVK' => 14,
		'Mesa Turnip' => 18,
		'Mesa V3DV' => 19,
		'Mesa panvk' => 20,
		'Mesa venus' => 22,
		'Mesa Dozen' => 23,
		'Mesa NVK' => 24,
		'Imagination Mesa (Open Source)' => 25,
		'Mesa Honeykrisp' => 26
	];
	return (in_array($value, $values) ? array_search($value, $values) : null);
}


function getPresentMode($value)
{
	$modes = array(
		"IMMEDIATE_KHR" => 0,
		"MAILBOX_KHR" => 1,
		"FIFO_KHR" => 2,
		"FIFO_RELAXED_KHR" => 3,
		"SHARED_DEMAND_REFRESH_KHR" => 1000111000,
		"SHARED_CONTINUOUS_REFRESH_KHR" => 1000111001,
	);
	if (in_array($value, $modes)) {
		$key = array_search($value, $modes);
		return $key;
	} else {
		return "unknown";
	}
}

function getColorSpace($value)
{
	$modes = array(
		"SRGB_NONLINEAR_KHR" => 0,
		"DISPLAY_P3_NONLINEAR_EXT" => 1000104001,
		"EXTENDED_SRGB_LINEAR_EXT" => 1000104002,
		"DISPLAY_P3_LINEAR_EXT" => 1000104003,
		"DCI_P3_NONLINEAR_EXT" => 1000104004,
		"BT709_LINEAR_EXT" => 1000104005,
		"BT709_NONLINEAR_EXT" => 1000104006,
		"BT2020_LINEAR_EXT" => 1000104007,
		"HDR10_ST2084_EXT" => 1000104008,
		"DOLBYVISION_EXT" => 1000104009,
		"HDR10_HLG_EXT" => 1000104010,
		"ADOBERGB_LINEAR_EXT" => 1000104011,
		"ADOBERGB_NONLINEAR_EXT" => 1000104012,
		"PASS_THROUGH_EXT" => 1000104013,
		"EXTENDED_SRGB_NONLINEAR_EXT" => 1000104014,
		"DISPLAY_NATIVE_AMD" => 1000213000
	);
	if (in_array($value, $modes)) {
		$key = array_search($value, $modes);
		return $key;
	} else {
		return "unknown";
	}
}

function getPipelineRobustnessBufferBehavior($value) {
	return match($value) {
		0 => 'DEVICE_DEFAULT',
		1 => 'DISABLED',
		2 => 'ROBUST_BUFFER_ACCESS',
		3 => 'ROBUST_BUFFER_ACCESS_2',
		default => 'unknown'
	};
}

function getPipelineRobustnessImageBehavior($value) {
	return match($value) {
		0 => 'DEVICE_DEFAULT',
		1 => 'DISABLED',
		2 => 'ROBUST_IMAGE_ACCESS',
		3 => 'ROBUST_IMAGE_ACCESS_2',
		default => 'unknown'
	};
}

function getLayeredDriverUnderlyingApi($value) {
	return match($value) {
		0 => 'NONE',
		1 => 'D3D12',
		default => 'unknown'
	};
}

// Convert vendor specific driver version string
function getDriverVersion($versionraw, $versiontext, $vendorid, $osname)
{
	if ($versionraw != '') {
		// NVIDIA
		if ($vendorid == 4318) {
			return sprintf(
				"%d.%d.%d.%d",
				($versionraw >> 22) & 0x3ff,
				($versionraw >> 14) & 0x0ff,
				($versionraw >> 6) & 0x0ff,
				($versionraw) & 0x003f
			);
		}
		// Intel (Windows)
		if ($vendorid == 0x8086 && $osname == 'windows') {
			return sprintf(
				"%d.%d",
				($versionraw >> 14),
				($versionraw) & 0x3fff
			);
		}
		// Broadcom
		if ($vendorid == 5348) {
			// Version encoded as human-readable (10000 * major + 100 * minor)
			$major = $versionraw / 10000;
			$minor = ($versionraw % 10000) / 100;
			return sprintf('%d.%d', $major, $minor);
		}
		// Imagination Technologies
		if ($vendorid == 0x1010) {
			// For production drivers driverVersion is simply a monotonic integer
			// changeset number that a driver release was built from, with each
			// subsequent driver release comparing > than the previous.
			//
			// The VK_KHR_driver_properties driverInfo field provides more information
			// such as the major/minor release branch, 1.10, 1.11. etc.
			//
			// Non-production builds are automatically given a made up version starting
			// from 500,000,000 and can be ignored/formatted separately to not clash.
			if ($versionraw > 500000000) {
				return sprintf("0.0.%d", $versionraw);
			}
			else {
				return sprintf("%d", $versionraw);
			}
		}		
		// Use Vulkan version conventions if vendor mapping is not available
		return sprintf(
			"%d.%d.%d",
			($versionraw >> 22),
			($versionraw >> 12) & 0x3ff,
			$versionraw & 0xfff,
			"<span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true' title='The version number conversion scheme for this vendor is not yet available'></span>"
		);
	}

	return $versiontext;
}

function getComponentTypeString($value)
{
	return match($value) {
		0 => 'FLOAT16_KHR',
		1 => 'FLOAT32_KHR',
		2 => 'FLOAT64_KHR',
		3 => 'SINT8_KHR',
		4 => 'SINT16_KHR',
		5 => 'SINT32_KHR',
		6 => 'SINT64_KHR',
		7 => 'UINT8_KHR',
		8 => 'UINT16_KHR',
		9 => 'UINT32_KHR',
		10 => 'UINT64_KHR',
		1000491000 => 'SINT8_PACKED_NV',
		1000491001 => 'UINT8_PACKED_NV',
		1000491002 => 'FLOAT_E4M3_NV',
		1000491003 => 'FLOAT_E5M2_NV',
		default => 'unknown'
	};
}

function getScopString($value)
{
	return match($value) {
		1 => 'DEVICE_KHR',
		2 => 'WORKGROUP_KHR',
		3 => 'SUBGROUP_KHR',
		5 => 'QUEUE_FAMILY_KHR',
   		default => 'unknown'
	};
}

function mailError($error, $content)
{
	$msgtitle = "Vulkan database upload error";
	$msg = "Error:\n";
	$msg .= $error;
	$msg .= "\n\nContent:\n";
	$msg .= $content;
	mail('webmaster@saschawillems.de', $msgtitle, $msg);
}

/**
 * Return database os type from platform name
 * 
 * @param string $platform Human readable platform name (Windows, Linux, Android)
 * @return int|null Database mapped os type or null if unknown
 */
function ostype($platform)
{
	switch (strtolower($platform)) {
		case 'windows':
			return 0;
		case 'linux':
			return 1;
		case 'android':
			return 2;
		case 'macos':
			return 3;
		case 'ios':
			return 4;
	}
	return null;
}

/**
 * Return platform name from database os type
 * 
 * @param integer $ostype Database os type
 * @return int|null Numan readable platform name or null if unknown
 */
function platformname($ostype)
{
	switch ($ostype) {
		case 0:
			return 'windows';
		case 1:
			return 'linux';
		case 2:
			return 'android';
		case 3:
			return 'macOS';
		case 4:
			return 'ios';
	}
	return null;
}

/**
 * Formats a JSON string for pretty printing
 *
 * @param string $json The JSON to make pretty
 * @param bool $html Insert nonbreaking spaces and <br />s for tabs and linebreaks
 * @return string The prettified output
 * @author Jay Roberts (https://github.com/GloryFish)
 */
function _format_json($json, $html = false)
{
	$tabcount = 0;
	$result = '';
	$inquote = false;
	$ignorenext = false;
	if ($html) {
		$tab = "&nbsp;&nbsp;&nbsp;";
		$newline = "<br/>";
	} else {
		$tab = "\t";
		$newline = "\n";
	}
	for ($i = 0; $i < strlen($json); $i++) {
		$char = $json[$i];
		if ($ignorenext) {
			$result .= $char;
			$ignorenext = false;
		} else {
			switch ($char) {
				case '{':
					$tabcount++;
					$result .= $char . $newline . str_repeat($tab, $tabcount);
					break;
				case '}':
					$tabcount--;
					$result = trim($result) . $newline . str_repeat($tab, $tabcount) . $char;
					break;
				case ',':
					$result .= $char . $newline . str_repeat($tab, $tabcount);
					break;
				case '"':
					$inquote = !$inquote;
					$result .= $char;
					break;
				case '\\':
					if ($inquote) $ignorenext = true;
					$result .= $char;
					break;
				default:
					$result .= $char;
			}
		}
	}
	return $result;
}

function UUIDtoString($uuid)
{
	try {
		$arr = unserialize($uuid);
		foreach ($arr as &$val) {
			$val = strtoupper(str_pad(dechex($val), 2, "0", STR_PAD_LEFT));
		}
		return implode($arr);
	} catch (Throwable $e) {
		return null;
	}
}

function displayBool($value)
{
	if (($value == 'true') || ($value == 'false')) {
		$class = (strtolower($value) == 'true') ? 'supported' : 'unsupported';
		return "<span class='$class'>$value</span>";
	}
	return ($value == 1) ? "<span class='supported'>true</span>" : "<span class='unsupported'>false</span>";
}

function displayHex($value)
{
	return '0x' . strtoupper(dechex($value));
}

/**
 * Visualize certain properties (e.g. flags) in a more readable way
 * If $shorten is set to true, values larger than 20 chars are shortened (use for e.g. report compare to not break the layout)
 */
function getPropertyDisplayValue($key, $value, $shorten = false)
{
	$displayvalue = $value;
	switch ($key) {
			// Core 1.0
		case 'vendorID':
		case 'deviceID':
			$displayvalue = displayHex($value);
			break;
		case 'residencyAlignedMipSize':
		case 'residencyNonResidentStrict':
		case 'residencyStandard2DBlockShape':
		case 'residencyStandard2DMultisampleBlockShape':
		case 'residencyStandard3DBlockShape':
			$displayvalue = displayBool($value);
			break;
		case 'pipelineCacheUUID':
			$displayvalue = UUIDtoString($value);
			break;
			// Core 1.1
		case 'deviceUUID':
		case 'driverUUID':
		case 'deviceLUID':
			$displayvalue = UUIDtoString($value);
			break;
		case 'deviceLUIDValid':
		case 'subgroupQuadOperationsInAllStages':
		case 'subgroupProperties.subgroupQuadOperationsInAllStages':
		case 'protectedNoFault':
			$displayvalue = displayBool($value);
			break;
		case 'pointClippingBehavior':
			$displayvalue = getPointClippingBehavior($value);
			break;
		case 'subgroupSupportedStages':
		case 'subgroupProperties.supportedStages':
			$displayvalue = listSubgroupStageFlags($value);
			break;
		case 'subgroupSupportedOperations':
		case 'subgroupProperties.supportedOperations':
			$displayvalue = listSubgroupFeatureFlags($value);
			break;
		case 'framebufferColorSampleCounts':
		case 'framebufferDepthSampleCounts':
		case 'framebufferNoAttachmentsSampleCounts':
		case 'framebufferStencilSampleCounts':
		case 'sampledImageColorSampleCounts':
		case 'sampledImageDepthSampleCounts':
		case 'sampledImageIntegerSampleCounts':
		case 'sampledImageStencilSampleCounts':
		case 'storageImageSampleCounts':
			$displayvalue = listSampleCountFlags($value);
			break;
			// Core 1.2
		case 'driverID':
			$displayvalue = getDriverId($value);
			break;
		case 'shaderSignedZeroInfNanPreserveFloat16':
		case 'shaderSignedZeroInfNanPreserveFloat32':
		case 'shaderSignedZeroInfNanPreserveFloat64':
		case 'shaderDenormPreserveFloat16':
		case 'shaderDenormPreserveFloat32':
		case 'shaderDenormPreserveFloat64':
		case 'shaderDenormFlushToZeroFloat16':
		case 'shaderDenormFlushToZeroFloat32':
		case 'shaderDenormFlushToZeroFloat64':
		case 'shaderRoundingModeRTEFloat16':
		case 'shaderRoundingModeRTEFloat32':
		case 'shaderRoundingModeRTEFloat64':
		case 'shaderRoundingModeRTZFloat16':
		case 'shaderRoundingModeRTZFloat32':
		case 'shaderRoundingModeRTZFloat64':
		case 'shaderUniformBufferArrayNonUniformIndexingNative':
		case 'shaderSampledImageArrayNonUniformIndexingNative':
		case 'shaderStorageBufferArrayNonUniformIndexingNative':
		case 'shaderStorageImageArrayNonUniformIndexingNative':
		case 'shaderInputAttachmentArrayNonUniformIndexingNative':
		case 'robustBufferAccessUpdateAfterBind':
		case 'quadDivergentImplicitLod':
		case 'independentResolveNone':
		case 'independentResolve':
		case 'filterMinmaxSingleComponentFormats':
		case 'filterMinmaxImageComponentMapping':
			$displayvalue = displayBool($value);
			break;
		case 'framebufferIntegerColorSampleCounts':
			$displayvalue = listSampleCountFlags($value);
			break;
		case 'supportedDepthResolveModes':
		case 'supportedStencilResolveModes':
			$displayvalue = listResolveModeFlags($value);
			break;
		case 'denormBehaviorIndependence':
		case 'roundingModeIndependence':
			$displayvalue = getShaderFloatControlsIndependence($value);
			break;
			// Extensions
		case 'sampleLocationSampleCounts':
			$displayvalue = listSampleCountFlags($value);
			break;
		// Core 1.3
		case 'integerDotProduct8BitUnsignedAccelerated':
		case 'integerDotProduct8BitSignedAccelerated':
		case 'integerDotProduct8BitMixedSignednessAccelerated':
		case 'integerDotProduct4x8BitPackedUnsignedAccelerated':
		case 'integerDotProduct4x8BitPackedSignedAccelerated':
		case 'integerDotProduct4x8BitPackedMixedSignednessAccelerated':
		case 'integerDotProduct16BitUnsignedAccelerated':
		case 'integerDotProduct16BitSignedAccelerated':
		case 'integerDotProduct16BitMixedSignednessAccelerated':
		case 'integerDotProduct32BitUnsignedAccelerated':
		case 'integerDotProduct32BitSignedAccelerated':
		case 'integerDotProduct32BitMixedSignednessAccelerated':
		case 'integerDotProduct64BitUnsignedAccelerated':
		case 'integerDotProduct64BitSignedAccelerated':
		case 'integerDotProduct64BitMixedSignednessAccelerated':
		case 'integerDotProductAccumulatingSaturating8BitUnsignedAccelerated':
		case 'integerDotProductAccumulatingSaturating8BitSignedAccelerated':
		case 'integerDotProductAccumulatingSaturating8BitMixedSignednessAccelerated':
		case 'integerDotProductAccumulatingSaturating4x8BitPackedUnsignedAccelerated':
		case 'integerDotProductAccumulatingSaturating4x8BitPackedSignedAccelerated':
		case 'integerDotProductAccumulatingSaturating4x8BitPackedMixedSignednessAccelerated':
		case 'integerDotProductAccumulatingSaturating16BitUnsignedAccelerated':
		case 'integerDotProductAccumulatingSaturating16BitSignedAccelerated':
		case 'integerDotProductAccumulatingSaturating16BitMixedSignednessAccelerated':
		case 'integerDotProductAccumulatingSaturating32BitUnsignedAccelerated':
		case 'integerDotProductAccumulatingSaturating32BitSignedAccelerated':
		case 'integerDotProductAccumulatingSaturating32BitMixedSignednessAccelerated':
		case 'integerDotProductAccumulatingSaturating64BitUnsignedAccelerated':
		case 'integerDotProductAccumulatingSaturating64BitSignedAccelerated':
		case 'integerDotProductAccumulatingSaturating64BitMixedSignednessAccelerated':
		case 'storageTexelBufferOffsetSingleTexelAlignment':
		case 'uniformTexelBufferOffsetSingleTexelAlignment':
			$displayvalue = displayBool($value);
			break;
		// Core 1.4
		case 'supportsNonZeroFirstInstance':
		case 'dynamicRenderingLocalReadDepthStencilAttachments':
		case 'dynamicRenderingLocalReadMultisampledAttachments':
		case 'earlyFragmentMultisampleCoverageAfterSampleCounting':
		case 'earlyFragmentSampleMaskTestBeforeSampleCounting':
		case 'depthStencilSwizzleOneSupport':
		case 'polygonModePointSize':
		case 'nonStrictSinglePixelWideLinesUseParallelogram':
		case 'nonStrictWideLinesUseParallelogram':
		case 'blockTexelViewCompatibleMultipleLayers':
		case 'fragmentShadingRateClampCombinerInputs':
		case 'identicalMemoryTypeRequirements':
			$displayvalue = displayBool($value);
			break;
		case 'defaultRobustnessStorageBuffers':
		case 'defaultRobustnessUniformBuffers':
		case 'defaultRobustnessVertexInputs':
			$displayvalue = getPipelineRobustnessBufferBehavior($value);
			break;
		case 'defaultRobustnessImages':
			$displayvalue = getPipelineRobustnessImageBehavior($value);
			break;
		// Extensions (partially promoted to core, but same name = same formatting rule)
		case 'shaderModuleIdentifierAlgorithmUUID':
		case 'shaderBinaryUUID':
		case 'optimalTilingLayoutUUID':
			$displayvalue = UUIDtoString($value);			
			break;
		case 'pCopyDstLayouts':
		case 'pCopySrcLayouts':									
			$displayvalue = listImageLayouts($value);
			break;
		case 'cooperativeMatrixProperties':
			$displayvalue = listCooperativeMatrixProperties($value);
			break;
		case 'underlyingAPI':
			$displayvalue = getLayeredDriverUnderlyingApi((int)$value);
			break;			
		default:
			// Serialized arrays
			if (is_string($value) && (substr($value, 0, 2) == "a:") && (strpos($value, '{') !== false)) {
				$arr = unserialize($value);
				$displayvalue = "[" . implode(',', $arr) . "]";
			}
			// Boolean string
			if (($value == 'true') || ($value == 'false')) {
				$displayvalue = displayBool($value);
			};
	}
	// Some value may contain very long values that break the layout, so we shorten theme for the compare view
	if ($shorten && (in_array(strtolower($key), ['driverinfo']))) {
		$displayvalue = shorten($displayvalue);
	}
	return $displayvalue;
}

function sanitize($value)
{
	return htmlentities($value, ENT_QUOTES);
}

/**
 * Return a sanitized $_GET value to avoid XSS
 */
function GET_sanitized($name)
{
	if (isset($_GET[$name]) ) {
		return sanitize($_GET[$name]);
	};
	return null;
}

/** Some Vulkan names are so long they'd break MySQL's 64 chracter limit for column names, so we need to alias */
function getFullFieldName($short_name) 
{
	$aliases = [
		'idp8BitUnsignedAccelerated' => 'integerDotProduct8BitUnsignedAccelerated',
		'idp8BitSignedAccelerated' => 'integerDotProduct8BitSignedAccelerated',
		'idp8BitMixedSignednessAccelerated' => 'integerDotProduct8BitMixedSignednessAccelerated',
		'idp4x8BitPackedUnsignedAccelerated' => 'integerDotProduct4x8BitPackedUnsignedAccelerated',
		'idp4x8BitPackedSignedAccelerated' => 'integerDotProduct4x8BitPackedSignedAccelerated',
		'idp4x8BitPackedMixedSignednessAccelerated' => 'integerDotProduct4x8BitPackedMixedSignednessAccelerated',
		'idp16BitUnsignedAccelerated' => 'integerDotProduct16BitUnsignedAccelerated',
		'idp16BitSignedAccelerated' => 'integerDotProduct16BitSignedAccelerated',
		'idp16BitMixedSignednessAccelerated' => 'integerDotProduct16BitMixedSignednessAccelerated',
		'idp32BitUnsignedAccelerated' => 'integerDotProduct32BitUnsignedAccelerated',
		'idp32BitSignedAccelerated' => 'integerDotProduct32BitSignedAccelerated',
		'idp32BitMixedSignednessAccelerated' => 'integerDotProduct32BitMixedSignednessAccelerated',
		'idp64BitUnsignedAccelerated' => 'integerDotProduct64BitUnsignedAccelerated',
		'idp64BitSignedAccelerated' => 'integerDotProduct64BitSignedAccelerated',
		'idp64BitMixedSignednessAccelerated' => 'integerDotProduct64BitMixedSignednessAccelerated',
		'idpAccumulatingSaturating8BitUnsignedAccelerated' => 'integerDotProductAccumulatingSaturating8BitUnsignedAccelerated',
		'idpAccumulatingSaturating8BitSignedAccelerated' => 'integerDotProductAccumulatingSaturating8BitSignedAccelerated',
		'idpAccumulatingSaturating8BitMixedSignednessAccelerated' => 'integerDotProductAccumulatingSaturating8BitMixedSignednessAccelerated',
		'idpAccumulatingSaturating4x8BitPackedUnsignedAccelerated' => 'integerDotProductAccumulatingSaturating4x8BitPackedUnsignedAccelerated',
		'idpAccumulatingSaturating4x8BitPackedSignedAccelerated' => 'integerDotProductAccumulatingSaturating4x8BitPackedSignedAccelerated',
		'idpAccumulatingSaturating4x8BitPackedMixedSignednessAccelerated' => 'integerDotProductAccumulatingSaturating4x8BitPackedMixedSignednessAccelerated',
		'idpAccumulatingSaturating16BitUnsignedAccelerated' => 'integerDotProductAccumulatingSaturating16BitUnsignedAccelerated',
		'idpAccumulatingSaturating16BitSignedAccelerated' => 'integerDotProductAccumulatingSaturating16BitSignedAccelerated',
		'idpAccumulatingSaturating16BitMixedSignednessAccelerated' => 'integerDotProductAccumulatingSaturating16BitMixedSignednessAccelerated',
		'idpAccumulatingSaturating32BitUnsignedAccelerated' => 'integerDotProductAccumulatingSaturating32BitUnsignedAccelerated',
		'idpAccumulatingSaturating32BitSignedAccelerated' => 'integerDotProductAccumulatingSaturating32BitSignedAccelerated',
		'idpAccumulatingSaturating32BitMixedSignednessAccelerated' => 'integerDotProductAccumulatingSaturating32BitMixedSignednessAccelerated',
		'idpAccumulatingSaturating64BitUnsignedAccelerated' => 'integerDotProductAccumulatingSaturating64BitUnsignedAccelerated',
		'idpAccumulatingSaturating64BitSignedAccelerated' => 'integerDotProductAccumulatingSaturating64BitSignedAccelerated',
		'idpAccumulatingSaturating64BitMixedSignednessAccelerated' => 'integerDotProductAccumulatingSaturating64BitMixedSignednessAccelerated',
	];
	if (key_exists($short_name, $aliases)) {
		return $aliases[$short_name];
	}
	return $short_name;
}

/** Some Vulkan names are so long they'd break MySQL's 64 chracter limit for column names, so we need to alias */
function getShortFieldName($full_name) 
{
	$aliases = [
		'integerDotProduct8BitUnsignedAccelerated' => 'idp8BitUnsignedAccelerated',
		'integerDotProduct8BitSignedAccelerated' => 'idp8BitSignedAccelerated',
		'integerDotProduct8BitMixedSignednessAccelerated' => 'idp8BitMixedSignednessAccelerated',
		'integerDotProduct4x8BitPackedUnsignedAccelerated' => 'idp4x8BitPackedUnsignedAccelerated',
		'integerDotProduct4x8BitPackedSignedAccelerated' => 'idp4x8BitPackedSignedAccelerated',
		'integerDotProduct4x8BitPackedMixedSignednessAccelerated' => 'idp4x8BitPackedMixedSignednessAccelerated',
		'integerDotProduct16BitUnsignedAccelerated' => 'idp16BitUnsignedAccelerated',
		'integerDotProduct16BitSignedAccelerated' => 'idp16BitSignedAccelerated',
		'integerDotProduct16BitMixedSignednessAccelerated' => 'idp16BitMixedSignednessAccelerated',
		'integerDotProduct32BitUnsignedAccelerated' => 'idp32BitUnsignedAccelerated',
		'integerDotProduct32BitSignedAccelerated' => 'idp32BitSignedAccelerated',
		'integerDotProduct32BitMixedSignednessAccelerated' => 'idp32BitMixedSignednessAccelerated',
		'integerDotProduct64BitUnsignedAccelerated' => 'idp64BitUnsignedAccelerated',
		'integerDotProduct64BitSignedAccelerated' => 'idp64BitSignedAccelerated',
		'integerDotProduct64BitMixedSignednessAccelerated' => 'idp64BitMixedSignednessAccelerated',
		'integerDotProductAccumulatingSaturating8BitUnsignedAccelerated' => 'idpAccumulatingSaturating8BitUnsignedAccelerated',
		'integerDotProductAccumulatingSaturating8BitSignedAccelerated' => 'idpAccumulatingSaturating8BitSignedAccelerated',
		'integerDotProductAccumulatingSaturating8BitMixedSignednessAccelerated' => 'idpAccumulatingSaturating8BitMixedSignednessAccelerated',
		'integerDotProductAccumulatingSaturating4x8BitPackedUnsignedAccelerated' => 'idpAccumulatingSaturating4x8BitPackedUnsignedAccelerated',
		'integerDotProductAccumulatingSaturating4x8BitPackedSignedAccelerated' => 'idpAccumulatingSaturating4x8BitPackedSignedAccelerated',
		'integerDotProductAccumulatingSaturating4x8BitPackedMixedSignednessAccelerated' => 'idpAccumulatingSaturating4x8BitPackedMixedSignednessAccelerated',
		'integerDotProductAccumulatingSaturating16BitUnsignedAccelerated' => 'idpAccumulatingSaturating16BitUnsignedAccelerated',
		'integerDotProductAccumulatingSaturating16BitSignedAccelerated' => 'idpAccumulatingSaturating16BitSignedAccelerated',
		'integerDotProductAccumulatingSaturating16BitMixedSignednessAccelerated' => 'idpAccumulatingSaturating16BitMixedSignednessAccelerated',
		'integerDotProductAccumulatingSaturating32BitUnsignedAccelerated' => 'idpAccumulatingSaturating32BitUnsignedAccelerated',
		'integerDotProductAccumulatingSaturating32BitSignedAccelerated' => 'idpAccumulatingSaturating32BitSignedAccelerated',
		'integerDotProductAccumulatingSaturating32BitMixedSignednessAccelerated' => 'idpAccumulatingSaturating32BitMixedSignednessAccelerated',
		'integerDotProductAccumulatingSaturating64BitUnsignedAccelerated' => 'idpAccumulatingSaturating64BitUnsignedAccelerated',
		'integerDotProductAccumulatingSaturating64BitSignedAccelerated' => 'idpAccumulatingSaturating64BitSignedAccelerated',
		'integerDotProductAccumulatingSaturating64BitMixedSignednessAccelerated' => 'idpAccumulatingSaturating64BitMixedSignednessAccelerated',
	];
	if (key_exists($full_name, $aliases)) {
		return $aliases[$full_name];
	}
	return $full_name;
}

function shorten($value, $length = 20, $add = '...')
{
	if (strlen($value) >= $length) {
		return substr($value, 0, $length - strlen($add)) . $add;
	}
	return $value;
}

function logToFile($msg)
{
	$log_msg = "[".date('d-M-Y H:m:s e')."] ".$msg.PHP_EOL;
	file_put_contents($_SERVER['DOCUMENT_ROOT'].'/logs/log'.date('Y-m-d').'.txt', $log_msg, FILE_APPEND); 	
}