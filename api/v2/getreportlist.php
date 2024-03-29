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

	// Return list of all available reports as json
	
	include './../../database/database.class.php';
	
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

	DB::connect();	
			
	try {
		$sql ="select 
			concat('0x', hex(cast(dp.vendorid as unsigned))) as vendorid,
			concat('0x', hex(cast(dp.deviceid as unsigned))) as deviceid,
			VendorID(dp.vendorid) as vendorname,
			dp.devicename,
			dp.devicetype,
			dp.apiversion,
			dp.driverversionraw,
			dp.driverversion,
			r.osname,
			r.osversion,
			r.osarchitecture,
			r.headerversion,
			r.version as reportversion,
			concat('https://vulkan.gpuinfo.org/api/v2/getreport.php?id=', dp.reportid) as url
			from deviceproperties dp
			join reports r on r.id = dp.reportid
			where r.version >= '1.4'
			order by vendorid, devicename, apiversion desc, driverversionraw";

		$stmnt = DB::$connection->prepare($sql);
		$stmnt->execute();
	
		if ($stmnt->rowCount() > 0) {		
			$rows = array();

			while ($row = $stmnt->fetch(PDO::FETCH_ASSOC)) {
				$rows[] = $row;
			}

			header('Content-type:application/json;charset=utf-8');
			//echo _format_json(json_encode($rows), false);			
			echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		} 
		else {
			header('HTTP/ 404 empty_response');
			echo json_encode(array("info", "No reports with version > 1.4"));
		}
	} catch (Exception $e) {
		header('HTTP/ 500 server_error');
		echo "Server error while fetching report list";	
	}

	DB::disconnect();
?>