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

    include '../dbconfig.php';
    include '../functions.php';

    DB::connect();

    $data = array();
    $params = array();    
             
    // Ordering
    $orderByColumn = '';
    $orderByDir = '';
    if (isset($_REQUEST['order']) && count($_REQUEST['order'] > 0)) {
        $orderByColumn = $_REQUEST['order'][0]['column'];
        $orderByDir = $_REQUEST['order'][0]['dir'];
    }

    // Paging
    $paging = '';
    if (isset($_REQUEST['start'] ) && $_REQUEST['length'] != '-1') {
        $paging = "LIMIT ".$_REQUEST["length"]. " OFFSET ".$_REQUEST["start"];
    }  

    // Filtering
    $searchColumns = ['device', 'api', 'driverversion', 'reportversion', 'reportcount'];

    // Per-column, filtering
    $filters = array();
    for ($i = 0; $i < count($_REQUEST['columns']); $i++) {
        $column = $_REQUEST['columns'][$i];
        if (($column['searchable'] == 'true') && ($column['search']['value'] != '')) {
            $filters[] = $searchColumns[$i].' like :filter_'.$i;
            $params['filter_'.$i] = '%'.$column['search']['value'].'%';
        }
    }
    if (sizeof($filters) > 0) {
        $searchClause = 'having '.implode(' and ', $filters);
    }        

    $whereClause = '';
    $selectAddColumns = '';
    $negate = false;
	if (isset($_REQUEST['filter']['option'])) {
		if ($_REQUEST['filter']['option'] == 'not') {
			$negate = true;
		}
    }        
	// Filters
    // Extension
	if (isset($_REQUEST['filter']['extension'])) {
	    $extension = $_REQUEST['filter']['extension'];
        if ($extension != '') {
            $whereClause = "where r.id ".($negate ? "not" : "")." in (select distinct(reportid) from deviceextensions de join extensions ext on de.extensionid = ext.id where ext.name = :filter_extension)";
            $params['filter_extension'] = $extension;
        }
	}
    // Feature
	if (isset($_REQUEST['filter']['feature'])) {
	    $feature = $_REQUEST['filter']['feature'];
        if ($feature != '') {
            $whereClause = "where r.id in (select distinct(reportid) from devicefeatures df where df.".$feature." = ".($negate ? "0" : "1").")";
        }    
    }
    // Submitter
    if (isset($_REQUEST['filter']['submitter'])) {
	    $submitter = $_REQUEST['filter']['submitter'];
        if ($submitter != '') {
            $whereClause = "where r.submitter = :filter_submitter";
            $params['filter_submitter'] = $submitter;            
        }
	}
	// Format support
	$linearformatfeature = $_REQUEST['filter']['linearformat'];
	$optimalformatfeature = $_REQUEST['filter']['optimalformat'];
	$bufferformatfeature = $_REQUEST['filter']['bufferformat'];	
	if ($linearformatfeature != '') {
		$whereClause = "where id ".($negate ? "not" : "")." in (select reportid from deviceformats df join VkFormat vf on vf.value = df.formatid where vf.name = :filter_linearformatfeature and df.lineartilingfeatures > 0)";
        $params['filter_linearformatfeature'] = $linearformatfeature;
	}	
	if ($optimalformatfeature != '') {
		$whereClause = "where id ".($negate ? "not" : "")." in (select reportid from deviceformats df join VkFormat vf on vf.value = df.formatid where vf.name = :filter_optimalformatfeature and df.optimaltilingfeatures > 0)";
        $params['filter_optimalformatfeature'] = $optimalformatfeature;
	}	
	if ($bufferformatfeature != '') {
		$whereClause = "where id ".($negate ? "not" : "")." in (select reportid from deviceformats df join VkFormat vf on vf.value = df.formatid where vf.name = :filter_bufferformatfeature and df.bufferfeatures > 0)";
        $params['filter_bufferformatfeature'] = $bufferformatfeature;
	}    
	// Surface format	
	$surfaceformat = $_REQUEST['filter']['surfaceformat'];
	if ($surfaceformat != '') {
		$whereClause = "where r.version >= '1.2' and id ".($negate ? "not" : "")." in (select reportid from devicesurfaceformats dsf join VkFormat f on dsf.format = f.value where f.name = :filter_surfaceformat)";
        $params['filter_surfaceformat'] = $surfaceformat;        
	}
	// Surface present mode	
	$surfacepresentmode = $_REQUEST['filter']['surfacepresentmode'];
	if ($surfacepresentmode != '') {
		$whereClause = "where r.version >= '1.2' and id ".($negate ? "not" : "")." in (select reportid from devicesurfacemodes dsp where dsp.presentmode = :filter_surfacepresentmode)";
        $params['filter_surfacepresentmode'] = $surfacepresentmode;        
	}	    
	// Limit
	$limit = $_REQUEST['filter']['devicelimit'];
	if ($limit != '') {
		$selectAddColumns = ",(select dl.`".$limit."` from devicelimits dl where dl.reportid = r.id) as devicelimit";
		// Check if a limit requirement rule has to be applied (see Table 36. of the specs)
		$sql = "select feature from limitrequirements where limitname = :limit";  
		$reqs = DB::$connection->prepare($sql);
		$reqs->execute(array(":limit" => $limit));
		if ($reqs->rowCount() > 0) {
			$req = $reqs->fetch();
		    $whereClause = "where r.id in (select distinct(reportid) from devicefeatures df where df.".$req["feature"]." = 1)";
		}
	}    
    // Devicename
    if (isset($_REQUEST['filter']['devicename'])) {
	    $devicename = $_REQUEST['filter']['devicename'];
        if ($devicename != '') {
            $whereClause = "where r.devicename = :filter_devicename";
            $params['filter_devicename'] = $devicename;            
        }
	}    
    // Displayname (Android devices)
    if (isset($_REQUEST['filter']['displayname'])) {
	    $displayname = $_REQUEST['filter']['displayname'];
        if ($displayname != '') {
            $whereClause = "where r.displayname = :filter_displayname";
            $params['filter_displayname'] = $displayname;            
        }
	}    

    $orderBy = "order by ".$orderByColumn." ".$orderByDir;

    // if ($orderByColumn == "api") {
    //     $orderBy = "order by length(".$orderByColumn.") ".$orderByDir.", ".$orderByColumn." ".$orderByDir;
    // }

    if (isset($_REQUEST["platform"])) {
        $platform = $_REQUEST["platform"];
        if ($platform !== "all") {
            if ($whereClause != '') {
                $whereClause .= ' and ';
            } else {
                $whereClause = ' where ';
            }
            if ($platform == 'linux') {
                $whereClause .= "r.osname not in ('windows', 'android', 'ios', 'osx')";
            } else {
                $whereClause .= "r.osname = '".$platform."'";
            }
        }
    }

    $sql = "SELECT 
        r.id,
        ifnull(r.displayname, dp.devicename) as device, 
        max(dp.apiversionraw) as api,
        max(dp.driverversion) as driverversion,
        count(distinct r.id) as reportcount,
        max(r.version) as reportversion,
        max(r.submissiondate) as submissiondate
        from deviceproperties dp
        join reports r on r.id = dp.reportid
        $whereClause
        group by device
        ".$searchClause."
        ".$orderBy;

    $devices = DB::$connection->prepare($sql." ".$paging);
    $devices->execute($params);
    if ($devices->rowCount() > 0) { 
        foreach ($devices as $device) {
            $data[] = array(
                'device' => '<a href="listreports.php?devicename='.$device["device"].'">'.$device["device"].'</a>', 
                'api' => versionToString($device["api"]), 
                'driver' => $device["driverversion"], 
				'reportcount' => $device["reportcount"],
                'reportversion' => $device["reportversion"],
                'submissiondate' => $device["submissiondate"]
            );
        }        
    }

    $filteredCount = 0;
    $stmnt = DB::$connection->prepare($sql);
    $stmnt->execute($params);
    $totalCount = $stmnt->fetchColumn(); 

    $filteredCount = $totalCount;
    if (($searchClause != '') or ($whereClause != ''))  {
        $stmnt = DB::$connection->prepare($sql);
        $stmnt->execute($params);
        $filteredCount = $stmnt->rowCount();     
    }

    $results = array(
        "draw" => isset($_REQUEST['draw']) ? intval( $_REQUEST['draw'] ) : 0,        
        "recordsTotal" => intval($totalCount),
        "recordsFiltered" => intval($filteredCount),
        "data" => $data);

    DB::disconnect();     

    echo json_encode($results);
?>