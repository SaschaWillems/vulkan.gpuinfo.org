<?php
function versionToString($version)
{
	$versionStr = ($version >> 22).".".(($version >> 12) & 0x3ff).".".($version & 0xfff);
	return $versionStr;
}

function getFlags($flagList, $flag)
{
	$flags = array();

	$arrVals = array_values($flagList);

	$index = 0;
	foreach ($flagList as $i => $value)
	{
		if ($flag & $i)
		{
			$flags[] = $arrVals[$index];
		}
		$index++;
	}
	return $flags;
}

function getFormatFlags($flag)
{
	$flags = array(
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
	);
	return getFlags($flags, $flag);	
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
	);
	return getFlags($flags, $flag);
}

function getSampleCountFlags($flag)
{
	$flags = array();
	for ($i = 0; $i < 7; ++$i)
	{
		$flags[pow(2, $i)] = pow(2, $i);
	}
	return getFlags($flags, $flag);
}

function getStageFlags($flag)
{
	$flags = array(
		0x0001 => "VERTEX",
		0x0002 => "TESSELLATION_CONTROL",
		0x0004 => "TESSELLATION_EVALUATION",
		0x0008 => "GEOMETRY",
		0x0010 => "FRAGMENT",
		0x0020 => "COMPUTE",
		0x001F => "ALL_GRAPHICS",
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
		0x0080 => "QUAD"
		//0x0100 => "PARTITIONED_BIT_NV"
	);

	$res = null;
	$arr_values = array_values($flags);
	$index = 0;
	foreach ($flags as $i => $value) {
		$class = ($flag & $i) ? "supported" : "unsupported";
		$res .= "<span class='".$class."'>".strtolower($arr_values[$index])."</span><br>";
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
		0x001F => "ALL GRAPHICS",
	);

	$res = null;
	$arr_values = array_values($flags);
	$index = 0;
	foreach ($flags as $i => $value) {
		if ($i == 0x001F) {
			$class = (($flag & $i) == $i) ? "supported" : "unsupported";
		} else {
			$class = ($flag & $i) ? "supported" : "unsupported";
		}
		$res .= "<span class='".$class."'>".strtolower($arr_values[$index])."</span><br>";
		$index++;
	}
	return $res;
}

// Generate a simple ul/li list for the flags
function listFlags($flags)
{
	if (sizeof($flags) > 0)
	{
		foreach ($flags as $flag)
		{
			echo $flag."<br>";
		}
	}
	else
	{
		echo "none";
	}
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
	if (in_array($value, $modes))
	{
		$key = array_search($value, $modes);
		return $key;
	}
	else
	{
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
	if (in_array($value, $modes))
	{
		$key = array_search($value, $modes);
		return $key;
	}
	else
	{
		return "unknown"; 
	}
}

// Generate device info table part for report compare pages
function reportCompareDeviceColumns($deviceinfo_captions, $deviceinfo_data, $count)
{
	for ($i = 0; $i < sizeof($deviceinfo_data[0]); ++$i) 
	{
		echo "<tr>";
		echo "<td>".$deviceinfo_captions[$i]."</td>";
		for ($j = 0, $arrsize = $count; $j < $arrsize; ++$j) 				
		{
			echo "<td class='deviceinfo'>".$deviceinfo_data[$j][$i]."</td>";
		}
		echo "</tr>";
	}
}

class ReportCompare {

	public static function insertTableHeader($caption, $deviceinfo_data, $count) {
		echo "<thead><tr><th>$caption</th>";
		for ($i = 0; $i < $count; $i++) {
			echo "<td class='caption'>".$deviceinfo_data[$i][0]."</td>";
		}
		echo "</th></thead><tbody>";
	}

	public static function insertDeviceColumns($deviceinfo_captions, $deviceinfo_data, $count)
	{
		for ($i = 1; $i < sizeof($deviceinfo_data[0]); ++$i) 
		{
			echo "<tr>";
			echo "<td>".$deviceinfo_captions[$i]."</td>";
			for ($j = 0, $arrsize = $count; $j < $arrsize; ++$j) 				
			{
				echo "<td class='deviceinfo'>".$deviceinfo_data[$j][$i]."</td>";
			}
			echo "</tr>";
		}
	}
	
}


// Convert vendor specific driver version string
function getDriverVerson($versionraw, $versiontext, $vendorid, $osname)
{
	if ($versionraw != '')
	{
		// NVIDIA
		if ($vendorid == 4318)
		{
			return sprintf("%d.%d.%d.%d",
				($versionraw >> 22) & 0x3ff,
				($versionraw >> 14) & 0x0ff,
				($versionraw >> 6) & 0x0ff,
				($versionraw) & 0x003f
				);
		}
		if ($vendorid == 0x8086 && $osname == 'windows')
		{
			return sprintf("%d.%d",
				($versionraw >> 14),
				($versionraw) & 0x3fff
				);
		}
		// Use Vulkan version conventions if vendor mapping is not available
		return sprintf("%d.%d.%d",
			($versionraw >> 22),
			($versionraw >> 12) & 0x3ff,
			$versionraw & 0xfff,
			"<span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true' title='The version number conversion scheme for this vendor is not yet available'></span>"
			);
	}
	
	return $versiontext;
}

function mailError($error, $content) {
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
function ostype($platform) {
	switch($platform) {
		case 'windows':
			return 0;
		case 'linux':
			return 1;
		case 'android':
			return 2;
	}
	return null;
}

/**
 * Return platform name from database os type
 * 
 * @param integer $ostype Database os type
 * @return int|null Numan readable platform name or null if unknown
 */
function platformname($ostype) {
	switch($ostype) {
		case 0:
			return 'windows';
		case 1:
			return 'linux';
		case 2:
			return 'android';
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
function _format_json($json, $html = false) {
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
	for($i = 0; $i < strlen($json); $i++) { 
		$char = $json[$i]; 
		if ($ignorenext) { 
			$result .= $char; 
			$ignorenext = false; 
		} else { 
			switch($char) { 
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

function getDeviceCount($platform, $andWhere = null) {
	return DB::getCount("SELECT count(distinct(ifnull(r.displayname, dp.devicename))) from reports r join deviceproperties dp on dp.reportid = r.id where r.ostype = :ostype $andWhere", ['ostype' => ostype($platform)]);
}

function setPageTitle(string $title) {
	echo '<script language="javascript">document.title = "'.$title.' - Vulkan Hardware Database by Sascha Willems";</script>';
}

?>