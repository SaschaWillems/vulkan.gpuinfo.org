<?php
	/** 		
	 *
	 * Vulkan hardware capability database back-end
	 *	
	 * Copyright (C) 2020 by Sascha Willems (www.saschawillems.de)
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
    
    /**
     * Implements report update logic for:
     *  - Core 1.1 features and properties
     *  - Core 1.2 features and properties
     *  - Extension features and properties
     */

    // @todo: log last update info in report
    // @todo: update report version

    include './../../dbconfig.php';

    function update_extended_data($json, $reportid, &$update_log) {
        // Extended feature set
        if (array_key_exists('extended', $json)) {
            $extended = $json['extended'];
            // Device features
            if (array_key_exists('devicefeatures2', $extended)) {
                foreach ($extended['devicefeatures2'] as $feature) {
                    $params = [
                        'reportid' => $reportid, 
                        'name' => $feature['name'], 
                        'extension' => $feature['extension']
                    ];                    
                    $stmnt_present = DB::$connection->prepare("SELECT * from devicefeatures2 where reportid = :reportid and name = :name and extension = :extension");
                    $stmnt_present->execute($params);
                    if ($stmnt_present->rowCount() == 0) {
                        // @todo: log only for testing
                        $update_log[] = sprintf('Inserted missing extension feature %s for %s', $feature['name'], $feature['extension']); 
                        $params['supported'] = $feature['supported'];
                        $stmnt_insert = DB::$connection->prepare("INSERT INTO devicefeatures2 (reportid, name, extension, supported) VALUES (:reportid, :name, :extension, :supported)");
                        $stmnt_insert->execute($params);
                    }
                }
            }
            // Device properties
            if (array_key_exists('deviceproperties2', $extended)) {
                foreach ($extended['deviceproperties2'] as $property) {
                    $params = [
                        'reportid' => $reportid, 
                        'name' => $property['name'], 
                        'extension' => $property['extension']
                    ];                    
                    $stmnt_present = DB::$connection->prepare("SELECT * from deviceproperties2 where reportid = :reportid and name = :name and extension = :extension");
                    $stmnt_present->execute($params);
                    if ($stmnt_present->rowCount() == 0) {
                        // @todo: log only for testing
                        $update_log[] = sprintf('Inserted missing extension property %s for %s', $property['name'], $property['extension']); 
                        if (is_array($property['value'])) {
                            $value = serialize($property['value']);
                        } else {
                            $value = $property['value'];
                        }                        
                        $params['value'] = $value;
                        $stmnt_insert = DB::$connection->prepare("INSERT INTO deviceproperties2 (reportid, name, extension, value) VALUES (:reportid, :name, :extension, :value)");
                        $stmnt_insert->execute($params);
                    }
                }
            }		
        }        
    }

    if (!isset($_GET['reportid'])) {
		header('HTTP/1.1 400 No report id set');
		exit();
	}

	$reportid = (int)($_GET['reportid']);

	// Check if report is present
	DB::connect();
	$stmnt = DB::$connection->prepare("SELECT * from reports where id = :reportid");
	$stmnt->execute(['reportid' => $reportid]);
	$report_present = $stmnt->rowCount() > 0;
	DB::disconnect();
	if (!$report_present) {
		header('HTTP/1.1 404 No report with that id found');
		exit();
	}

	$MAX_FILESIZE = 512 * 1024;
	$upload_file_name = $_FILES['data']['name'];
	if ($_FILES['data']['size'] > $MAX_FILESIZE)  {
		echo "File exceeds size limitation of 512 KByte!";    
		exit();  
	}	
	$ext = pathinfo($upload_file_name, PATHINFO_EXTENSION);
	if ($ext != 'json') {
		echo "Report '$file' is not of file type json!";
		exit();  
    }
    $path = './';
	$file_name = uniqid('update_report').'json';
	move_uploaded_file($_FILES['data']['tmp_name'], $path.$file_name) or die('');
	$json_file_contents = file_get_contents($file_name);
    $report = json_decode($json_file_contents, true);
    unlink($file_name);
    
	// Check if reports match
	DB::connect();
	$stmnt = DB::$connection->prepare("SELECT * from reports where
		devicename = :devicename and 
		driverversion = :driverversion and
		osname = :osname and
		osversion = :osversion and
		osarchitecture = :osarchitecture and
		apiversion = :apiversion and
		id = :reportid");
	$params = [
		'devicename' => $report['properties']['deviceName'],
		'driverversion' => $report['properties']['driverVersionText'],
		'osname' => $report['environment']['name'],
		'osversion' => $report['environment']['version'],
		'osarchitecture' => $report['environment']['architecture'],
		'apiversion' => $report['properties']['apiVersionText'],
		'reportid' => $reportid
	];
	$stmnt->execute($params);
	$report_match = $stmnt->rowCount() > 0;
	DB::disconnect();
	if (!$report_match) {
		header('HTTP/1.1 400 Devices do not match');
		exit();
    }
    
    $update_log = [];

    DB::connect();
    try {
        update_extended_data($report, $reportid, $update_log);
    } finally {
        DB::disconnect();
    }

    // Log update
    if (count($update_log) > 0) {
        DB::connect();
        $params = [
            'submitter' => $report['environment']['submitter'],
            'log' => implode('<br/>', $update_log),
            'reportid' => $reportid,
            'json' => $json_file_contents,
            'reportversion' => $report['environment']['reportversion']
        ];
        $stmnt_update = DB::$connection->prepare("INSERT into reportupdatehistory (submitter, log, reportid, json, reportversion) VALUES (:submitter, :log, :reportid, :json, :reportversion)");
        $stmnt_update->execute($params);
        echo implode('<br/>', $update_log);
        DB::disconnect();
    } else {
        echo "Nothing was updated!";
    }