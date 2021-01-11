<?php
	/* 		
	*
	* Vulkan hardware capability database server implementation
	*	
	* Copyright (C) 2011-2018 by Sascha Willems (www.saschawillems.de)
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

	// Return report as json (uploaded from client application)
	
	include './../database/database.class.php';
	
	DB::connect();				
	try {	
		$stmnt = DB::$connection->prepare("SELECT count(*) from reports");
		$stmnt->execute([]);
		$arr = array('count' => $stmnt->fetchColumn());	
		echo "jsoncallback(".json_encode($arr).")";			
	} catch (PDOException $e) {
		header('HTTP/ 500 server error');
		echo 'Server error: Could not check report!';
	}
	DB::disconnect();	
?>