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

	// Return list of all available reports as json
	
	include './../dbconfig.php';
	
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
		$stmnt = DB::$connection->prepare(
			"SELECT 
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
				concat('http://vulkan.gpuinfo.org/services/getreportjson.php?id=', dp.reportid) as url
			from deviceproperties dp
			join reports r on r.id = dp.reportid
			order by vendorid");			
		$stmnt->execute();
		if ($stmnt->rowCount() > 0) {
			header('Content-Type: application/json');		
			$rows = array();
			while($r = $stmnt->fetch(PDO::FETCH_ASSOC)) {
				$rows[] = $r;
			}
			echo _format_json(json_encode($rows), false);			
		} 
		else {
			header('HTTP/ 404 empty_response');
			echo "no reports on list";
		}	
	} catch (PDOException $e) {
		header('HTTP/ 500 error');
		die("Could not get report listing!");
	}	
	
	DB::disconnect();	
?>