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

    $data = array();

    DB::connect();
             
    $orderByColumn = $_REQUEST['order'][0]['column'];
    $orderByDir = $_REQUEST['order'][0]['dir'];

    $searchColumns = array('id', 'p.devicename', 'p.driverversion', 'p.apiversion', 'vendor', 'p.devicetype', 'r.osname', 'r.osversion', 'r.osarchitecture');
    $search = $_REQUEST['columns'][1]['search']['value'];

    $filters = array();
    for ($i = 0; $i < sizeof($_REQUEST['columns']); $i++) {
        $column = $_REQUEST['columns'][$i];
        if ($column['search']['value'] != '') {
            $filters[] = $searchColumns[$i]." like '%".$column['search']['value']."%'";
        }
    }
    if (sizeof($filters) > 0) {
        $searchClause = 'having '.implode(' and ', $filters);
    }

    $whereClause = '';
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
            $whereClause = "where r.id ".($negate ? "not" : "")."in (select distinct(reportid) from deviceextensions de join extensions ext on de.extensionid = ext.id where ext.name = '".$extension."')";
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
            $whereClause = "where r.submitter = '".$submitter."'";
        }
	}
	// Format support
	$linearformatfeature = $_REQUEST['filter']['linearformat'];
	$optimalformatfeature =$_REQUEST['filter']['optimalformat'];
	$bufferformatfeature = $_REQUEST['filter']['bufferformat'];	
	if ($linearformatfeature != '') {
		$whereClause = "where id in (select reportid from deviceformats df join VkFormat vf on vf.value = df.formatid where vf.name = '".$linearformatfeature."' and df.lineartilingfeatures > 0)";
	}	
	if ($optimalformatfeature != '') {
		$whereClause = "where id in (select reportid from deviceformats df join VkFormat vf on vf.value = df.formatid where vf.name = '".$optimalformatfeature."' and df.optimaltilingfeatures > 0)";
	}	
	if ($bufferformatfeature != '') {
		$whereClause = "where id in (select reportid from deviceformats df join VkFormat vf on vf.value = df.formatid where vf.name = '".$bufferformatfeature."' and df.bufferfeatures > 0)";
	}    

    $sql = "select 
        r.id,
        p.devicename as device,
        ifnull(p.driverversionraw, p.driverversion) as driver,
        p.driverversion,
        p.vendorid,
        p.apiversion as api,
        VendorId(p.vendorid) as vendor,
        p.devicetype,
        r.osname,
        r.osversion,
        r.osarchitecture
        from reports r
        left join
        deviceproperties p on (p.reportid = r.id)
        ".$whereClause."
        ".$searchClause."                
        order by ".$orderByColumn." ".$orderByDir;

    $paging = "LIMIT ".$_REQUEST["length"]. " OFFSET ".$_REQUEST["start"];

    $devices = DB::$connection->prepare($sql." ".$paging);
    $devices->execute();
    if ($devices->rowCount() > 0) { 
        foreach ($devices as $device) {
            $driver = getDriverVerson($device["driver"], "", $device["vendorid"]);
            $data[] = array(
                'id' => $device["id"], 
                'device' => '<a href="displayreport.php?id='.$device["id"].'">'.$device["device"].'</a>', 
                'driver' => $device["driverversion"], 
                'api' => $device["api"], 
                'vendor' => $device["vendor"],
				'devicetype' => strtolower(str_replace('_GPU', '', $device["devicetype"])),
				'osname' => $device["osname"],
				'osversion' => $device["osversion"],
				'osarchitecture' => $device["osarchitecture"],
                'compare' => '<center><input type="checkbox" name="id['.$device["id"].']"></center>'
            );
        }        
    }

    $stmnt = DB::$connection->prepare("select count(*) from reports");
    $stmnt->execute();
    $totalCount = $stmnt->fetchColumn(); 

    $filteredCount = $totalCount;
    if (($searchClause != '') or ($whereClause != ''))  {
        $stmnt = DB::$connection->prepare($sql);
        $stmnt->execute();
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