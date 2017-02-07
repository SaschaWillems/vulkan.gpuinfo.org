<?php
	/* 		
	*
	* Vulkan hardware capability database server implementation
	*	
	* Copyright (C) 2016-2017 by Sascha Willems (www.saschawillems.de)
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

	include './../dbconfig.php';
	
	dbConnect();	
			
	$devicename = mysql_real_escape_string($_GET['devicename']);	
	$driverversion = mysql_real_escape_string($_GET['driverversion']);	
	$osname = mysql_real_escape_string($_GET['osname']);	
	$osversion = mysql_real_escape_string($_GET['osversion']);	
	$osarchitecture = mysql_real_escape_string($_GET['osarchitecture']);
	
	$deviceselector = "
		devicename = '$devicename' and 
		driverversion = '$driverversion' and
		osname = '$osname' and
		osversion = '$osversion' and
		osarchitecture = '$osarchitecture'";	
	if (isset($_GET['apiversion'])) {
		$deviceselector .= " and apiversion = '".mysql_real_escape_string($_GET['apiversion'])."'"; 
	}
	$sqlstr = "select id from reports where $deviceselector";
	$sqlresult = mysql_query($sqlstr) or die(mysql_error());
	$sqlcount = mysql_num_rows($sqlresult);   
	$sqlrow = mysql_fetch_row($sqlresult);
	
	if ($sqlcount > 0) {
		header('HTTP/ 200 report_present '.$sqlrow[0].'');
		echo "$sqlrow[0]";
	} else {
		header('HTTP/ 200 report_new');
		echo "-1";
	}

	dbDisconnect();	
?>