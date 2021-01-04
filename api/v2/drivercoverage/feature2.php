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

	include './../../../database/database.class.php';
	include './../../../functions.php';
	include './apiendpoint.php';

	header('Content-Type: application/json');

	$extension = $_GET["extension"];
	if(!$extension) {
		exit(json_encode(["error" => "No extension specified"]));
	}
	$feature = $_GET["feature"];
	if(!$feature) {
		exit(json_encode(["error" => "No feature specified"]));
	}

	DB::connect();	

	$params = ["extension" => $extension, "feature" => $feature];
	$count = DB::getCount("SELECT count(*) FROM devicefeatures2 where extension = :extension and name = :feature", $params);
	if ($count == 0) {
		die(json_encode(["error" => "Unknown extension and feature combination"]));
	}

	$whereClause = "WHERE r.id in (select reportid from devicefeatures2 where extension = :extension and name = :feature and supported = 1)";
		
	try {
		$endpoint = new apiendpoint();
		$endpoint->setWhereClause($whereClause);
		$endpoint->execute($params, ["extension" => $extension, "feature" => $feature]);
	} catch (Throwable $e) {
		echo json_encode(["error" => "Server error while fetching report list"]);
	}

	DB::disconnect();
?>