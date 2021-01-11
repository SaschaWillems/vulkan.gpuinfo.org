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

    include './../../database/database.class.php';

    function update_core_features($version, $json, $reportid, &$update_log) {
        $version_short = str_replace('.', '', $version);
        $node_name = 'core'.$version_short;
        if (!array_key_exists($node_name, $json)) {
            return;
        }
        $table_name = 'devicefeatures'.$version_short;
        $stmnt = DB::$connection->prepare("SELECT * from $table_name where reportid = :reportid");
        $stmnt->execute(['reportid' => $reportid]);
        if ($stmnt->rowCount() == 0) {
            // Update if target report has no core 1.1 features
            if (array_key_exists('features', $json[$node_name])) {
                $jsonnode = $json[$node_name ]['features'];
                $columns = ['reportid'];
                $params = [':reportid'];
                $values = [':reportid' => $reportid];
                foreach($jsonnode as $key => $value) {
                    $columns[] = $key;
                    $params[] = ":$key";
                    $values[":$key"] = $value;
                }
                $sql = sprintf("INSERT INTO $table_name (%s) VALUES (%s)", implode(",", $columns), implode(",", $params));
                $stmnt = DB::$connection->prepare($sql);
                $stmnt->execute($values);
                $update_log[] = "Core features for version $version";
            }            
        }
    }

    function update_core_properties($version, $json, $reportid, &$update_log) {
        $version_short = str_replace('.', '', $version);
        $node_name = 'core'.$version_short;
        if (!array_key_exists($node_name, $json)) {
            return;
        }
        $table_name = 'deviceproperties'.$version_short;
        $stmnt = DB::$connection->prepare("SELECT * from $table_name where reportid = :reportid");
        $stmnt->execute(['reportid' => $reportid]);
        if ($stmnt->rowCount() == 0) {
            // Update if target report has no core 1.1 features
            if (array_key_exists('features', $json[$node_name])) {
                $jsonnode = $json[$node_name ]['properties'];
                $columns = ['reportid'];
                $params = [':reportid'];
                $values = [':reportid' => $reportid];
                foreach($jsonnode as $key => $value) {
                    $columns[] = $key;
                    $params[] = ":$key";
                    if (is_array($value)) {
                        // UUIDs etc. need to be serialized
                        $values[":$key"] = serialize($value);
                    } else {
                        $values[":$key"] = $value;
                    }
                }
                $sql = sprintf("INSERT INTO $table_name (%s) VALUES (%s)", implode(",", $columns), implode(",", $params));
                $stmnt = DB::$connection->prepare($sql);
                $stmnt->execute($values);	
                $update_log[] = "Core properties for version $version";
            }            
        }
    }    

    function update_extended_data($json, $reportid, &$update_log) {
        // Extended feature set
        if (array_key_exists('extended', $json)) {
            $extended = $json['extended'];
            // Device features
            if (array_key_exists('devicefeatures2', $extended)) {
                $updated_extenstion_list = [];
                foreach ($extended['devicefeatures2'] as $feature) {
                    $params = [
                        'reportid' => $reportid, 
                        'name' => $feature['name'], 
                        'extension' => $feature['extension']
                    ];        
                    $stmnt_present = DB::$connection->prepare("SELECT * from devicefeatures2 where reportid = :reportid and name = :name and extension = :extension");
                    $stmnt_present->execute($params);
                    if ($stmnt_present->rowCount() == 0) {
                        if (!in_array($feature['extension'], $updated_extenstion_list)) {
                            $updated_extenstion_list[] = $feature['extension'];
                        }
                        $params['supported'] = $feature['supported'];
                        $stmnt_insert = DB::$connection->prepare("INSERT INTO devicefeatures2 (reportid, name, extension, supported) VALUES (:reportid, :name, :extension, :supported)");
                        $stmnt_insert->execute($params);
                    }
                }
                if (count($updated_extenstion_list) > 0) {
                    foreach ($updated_extenstion_list as $updated_extension) {
                        $update_log[] = sprintf('Extension features for %s', $updated_extension);
                    }
                }
            }
            // Device properties
            if (array_key_exists('deviceproperties2', $extended)) {
                $updated_extenstion_list = [];
                foreach ($extended['deviceproperties2'] as $property) {
                    $params = [
                        'reportid' => $reportid, 
                        'name' => $property['name'], 
                        'extension' => $property['extension']
                    ];
                    $stmnt_present = DB::$connection->prepare("SELECT * from deviceproperties2 where reportid = :reportid and name = :name and extension = :extension");
                    $stmnt_present->execute($params);
                    if ($stmnt_present->rowCount() == 0) {
                        if (!in_array($property['extension'], $updated_extenstion_list)) {
                            $updated_extenstion_list[] = $property['extension'];
                        }
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
                if (count($updated_extenstion_list) > 0) {
                    foreach ($updated_extenstion_list as $updated_extension) {
                        $update_log[] = sprintf('Extension properties for %s', $updated_extension);
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
        update_core_features("1.1", $report, $reportid, $update_log);
        update_core_features("1.2", $report, $reportid, $update_log);
        update_core_properties("1.1", $report, $reportid, $update_log);
        update_core_properties("1.2", $report, $reportid, $update_log);
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
        echo json_encode(['updated' => true, 'log' => $update_log]);
        DB::disconnect();

        try {
            $msgtitle = "Vulkan report updated for ".$report['properties']['deviceName']." (".$report['properties']['driverVersionText'].")";
            if ($development_db) {
                $msgtitle = "[DEVELOPMENT] ".$msgtitle;
                $msg = "New Vulkan hardware report uploaded to the development database\n\n";
                $msg .= "Link : https://vulkan.gpuinfo.org/dev/displayreport.php?id=$reportid\n\n";
            } else {
                $msg = "New Vulkan hardware report uploaded to the database\n\n";
                $msg .= "Link : https://vulkan.gpuinfo.org/displayreport.php?id=$reportid\n\n";
            }
            
            $msg .= "Devicename = ".$report['properties']['deviceName']."\n";
            $msg .= "Driver version = ".$report['properties']['driverVersionText']."\n";
            $msg .= "API version = ".$report['properties']['apiVersionText']."\n";
            $msg .= "OS = ".$report['environment']['name']."\n";
            $msg .= "OS version = ".$report['environment']['version']."\n";
            $msg .= "OS arch = ".$report['environment']['architecture']."\n";
            $msg .= "Submitter = ".$report['environment']['submitter']."\n";
            $msg .= "Comment = ".$report['environment']['comment']."\n";
            $msg .= "Report version = ".$report['environment']['reportversion']."\n";

            $msg .= "Updatelog:\n";
            $msg .= implode('\n', $update_log);
            
            mail($mailto, $msgtitle, $msg);
        } catch (Exception $e) {
            // Failure to mail is not critical
        }	

    } else {
        echo json_encode(['updated' => false]);
    }

	try {
		if (array_key_exists('displayName', $report['properties'])) {
            $display_name = $report['properties']['displayName'];
        } else {
            $display_name = $report['properties']['deviceName'];
        }
        $msgtitle = "Vulkan report updated for ".$display_name." (".$report['properties']['driverVersionText'].")";
        $msg = "Vulkan hardware report has been updated\n\n";
        $msg .= "Link : https://vulkan.gpuinfo.org/displayreport.php?id=$reportid\n\n";
        $msg .= "Devicename = ".$report['properties']['deviceName']."\n";
        $msg .= "Updated data:\n";
        foreach ($update_log as $log) {
            $msg .= $log."\n";
        }
		mail($mailto, $msgtitle, $msg);
	} catch (Exception $e) {
		// Failure to mail is not critical
	}	    