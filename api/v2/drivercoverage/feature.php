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

	include './../../../dbconfig.php';
	include './../../../functions.php';
	include './apiendpoint.php';

	header('Content-Type: application/json');

	$feature = $_GET["feature"];
	if(!$feature) {
		exit(json_encode(["error" => "No feature specified"]));
	}
	
	DB::connect();	

	$count = DB::getCount("SELECT count(*) from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME = 'devicefeatures' and COLUMN_NAME = :feature", ["feature" => $feature]);
	if ($count == 0) {
		die(json_encode(["error" => "Unknown feature"]));
	}
	$whereClause = "WHERE r.id in (select distinct(reportid) from devicefeatures df where df.$feature)";
		
	try {
		$endpoint = new apiendpoint();
		$endpoint->setWhereClause($whereClause);
		$endpoint->execute($params, ["feature" => $feature]);
	} catch (Throwable $e) {
		echo json_encode(["error" => "Server error while fetching report list"]);
	}

	DB::disconnect();
?>