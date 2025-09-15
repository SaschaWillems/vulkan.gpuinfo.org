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
	
	include './../includes/functions.php';
	
	$reportid = (int)($_GET['id']);	
	$json = null;
	$filename = getReportJsonFolderName($reportid).$reportid.'.json';

	if (file_exists($filename)) {
		$json = file_get_contents($filename);
		logToFile("Json for report $reportid served from file");
	} else {
		logToFile("Json for report $reportid not found");
	}

	if ($json) {
		header('Content-Type: application/json');
		echo _format_json($json);
	} else {
		header('HTTP/ 404 report_not_present');
		echo "report not present";
	}
?>