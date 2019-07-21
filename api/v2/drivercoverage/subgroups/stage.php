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

	include './../../../../dbconfig.php';
	include './../../../../functions.php';
	include './../apiendpoint.php';

	header('Content-Type: application/json');

	$enum = $_GET["stage"];
	if(!$enum) {
		exit(json_encode(["error" => "No subgroup stage specified"]));
	}

	function enumToValue(string $enum)
	{
		$mapping = array(

			0x00000001 => 'VK_SHADER_STAGE_VERTEX_BIT',
			0x00000002 => 'VK_SHADER_STAGE_TESSELLATION_CONTROL_BIT',
			0x00000004 => 'VK_SHADER_STAGE_TESSELLATION_EVALUATION_BIT',
			0x00000008 => 'VK_SHADER_STAGE_GEOMETRY_BIT',
			0x00000010 => 'VK_SHADER_STAGE_FRAGMENT_BIT',
			0x00000020 => 'VK_SHADER_STAGE_COMPUTE_BIT',
			0x0000001F => 'VK_SHADER_STAGE_ALL_GRAPHICS',
			// 0x7FFFFFFF => 'VK_SHADER_STAGE_ALL',
			// 0x00000100 => 'VK_SHADER_STAGE_RAYGEN_BIT_NV',
			// 0x00000200 => 'VK_SHADER_STAGE_ANY_HIT_BIT_NV',
			// 0x00000400 => 'VK_SHADER_STAGE_CLOSEST_HIT_BIT_NV',
			// 0x00000800 => 'VK_SHADER_STAGE_MISS_BIT_NV',
			// 0x00001000 => 'VK_SHADER_STAGE_INTERSECTION_BIT_NV',
			// 0x00002000 => 'VK_SHADER_STAGE_CALLABLE_BIT_NV',
			// 0x00000040 => 'VK_SHADER_STAGE_TASK_BIT_NV',
			// 0x00000080 => 'VK_SHADER_STAGE_MESH_BIT_NV',
		);
		if (in_array($enum, $mapping)) {
			$key = array_search($enum, $mapping);
			return $key;
		};
		return null;
	}	

	DB::connect();	

	$enumValue = enumToValue($enum);
	if ($enumValue === null) {
		exit(json_encode(["error" => "Unknown subgroup stage"]));
	}
	$params["stage"] = $enumValue;

	$whereClause = "WHERE (`subgroupProperties.supportedstages` & :stage) > 0";

	try {
		$endpoint = new apiendpoint();
		$endpoint->setWhereClause($whereClause);
		$endpoint->execute($params, ["stage" => $enumValue]);
	} catch (Throwable $e) {
		echo json_encode(["error" => "Server error while fetching report list"]);
	}

	DB::disconnect();
?>