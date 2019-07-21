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

	$enum = $_GET["operation"];
	if(!$enum) {
		exit(json_encode(["error" => "No subgroup operation specified"]));
	}

	function enumToValue(string $enum)
	{
		$mapping = array(
			0x00000001 => 'VK_SUBGROUP_FEATURE_BASIC_BIT',
			0x00000002 => 'VK_SUBGROUP_FEATURE_VOTE_BIT',
			0x00000004 => 'VK_SUBGROUP_FEATURE_ARITHMETIC_BIT',
			0x00000008 => 'VK_SUBGROUP_FEATURE_BALLOT_BIT',
			0x00000010 => 'VK_SUBGROUP_FEATURE_SHUFFLE_BIT',
			0x00000020 => 'VK_SUBGROUP_FEATURE_SHUFFLE_RELATIVE_BIT',
			0x00000040 => 'VK_SUBGROUP_FEATURE_CLUSTERED_BIT',
			0x00000080 => 'VK_SUBGROUP_FEATURE_QUAD_BIT',
			0x00000100 => 'VK_SUBGROUP_FEATURE_PARTITIONED_BIT_NV',
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
		die(json_encode(["error" => "Unknown subgroup operation"]));
	}
	$params["operation"] = $enumValue;

	$whereClause = "WHERE (`subgroupProperties.supportedOperations` & :operation) > 0";

	try {
		$endpoint = new apiendpoint();
		$endpoint->setWhereClause($whereClause);
		$endpoint->execute($params, ["operation" => $enumValue]);
	} catch (Throwable $e) {
		echo json_encode(["error" => "Server error while fetching report list"]);
	}

	DB::disconnect();
?>