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

	include './../../../../database/database.class.php';
	include './../../../../functions.php';
	include './../apiendpoint.php';

	header('Content-Type: application/json');

	$enum = $_GET["presentmode"];
	if(!$enum) {
		exit(json_encode(["error" => "No presentmode specified"]));
	}

	function enumToValue(string $enum)
	{
		$modes = array(
			0 => "VK_PRESENT_MODE_IMMEDIATE_KHR",
			1 => "VK_PRESENT_MODE_MAILBOX_KHR",
			2 => "VK_PRESENT_MODE_FIFO_KHR",
			3 => "VK_PRESENT_MODE_FIFO_RELAXED_KHR",
			1000111000 => "VK_PRESENT_MODE_SHARED_DEMAND_REFRESH_KHR",
			1000111001 => "VK_PRESENT_MODE_SHARED_CONTINUOUS_REFRESH_KHR",
		);		
		if (in_array($enum, $modes)) {
			$key = array_search($enum, $modes);
			return $key;
		};
		return null;
	}	

	DB::connect();	

	$enumValue = enumToValue($enum);
	if ($enumValue === null) {
		die(json_encode(["error" => "Unknown surface present mode"]));
	}
	$params["presentmode"] = $enumValue;

	// Surface info is stored starting with report version 1.2, so ignore older reports
	$whereClause = "WHERE r.version >= '1.2' and r.id in (select reportid from devicesurfacemodes dsp where dsp.presentmode = :presentmode)";

	try {
		$endpoint = new apiendpoint();
		$endpoint->setWhereClause($whereClause);
		$endpoint->execute($params, ["presentmode" => $enum]);
	} catch (Throwable $e) {
		echo json_encode(["error" => "Server error while fetching report list"]);
	}

	DB::disconnect();
?>