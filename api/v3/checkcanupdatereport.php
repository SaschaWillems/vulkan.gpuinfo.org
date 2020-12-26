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
     * Implements report update check logic
     * A report can be updated if:
     *  - If the newer report has Core 1.1 features and/or properties that the old report is lacking
     *  - If the newer report has Core 1.2 features and/or properties that the old report is lacking
     *  - If the newer report has extension features and/or properties that the old report is lacking
     */

	include './../../dbconfig.php';

	function checkCore11Update($report, $compare_id) {
		if (array_key_exists('core11', $report)) {
			if (array_key_exists('features', $report['core11'])) {
				// Update allowed if features are present in new report, but no in old report
				if ((is_array($report['core11']['features'])) && (count($report['core11']['features']) > 0)) {
					$stmnt = DB::$connection->prepare("SELECT * from devicefeatures11 where reportid = :reportid");
					$stmnt->execute(['reportid' => $compare_id]);
					if ($stmnt->rowCount() == 0) {
						return true;
					}
				}
			}
			if (array_key_exists('properties', $report['core11'])) {
				// Update allowed if properties are present in new report, but no in old report
				if ((is_array($report['core11']['properties'])) && (count($report['core11']['properties']) > 0)) {
					$stmnt = DB::$connection->prepare("SELECT * from deviceproperties11 where reportid = :reportid");
					$stmnt->execute(['reportid' => $compare_id]);
					if ($stmnt->rowCount() == 0) {
						return true;
					}
				}
			}
		}
		return false;
	}

	function checkCore12Update($report, $compare_id) {
		if (array_key_exists('core12', $report)) {
			if (array_key_exists('features', $report['core12'])) {
				// Update allowed if features are present in new report, but no in old report
				if ((is_array($report['core12']['features'])) && (count($report['core12']['features']) > 0)) {
					// Update allowed if no features present in old report 
					$stmnt = DB::$connection->prepare("SELECT * from devicefeatures12 where reportid = :reportid");
					$stmnt->execute(['reportid' => $compare_id]);
					if ($stmnt->rowCount() == 0) {
						return true;
					}
				}
			}
			if (array_key_exists('properties', $report['core12'])) {
				// Update allowed if properties are present in new report, but no in old report
				if ((is_array($report['core12']['properties'])) && (count($report['core12']['properties']) > 0)) {
					$stmnt = DB::$connection->prepare("SELECT * from deviceproperties12 where reportid = :reportid");
					$stmnt->execute(['reportid' => $compare_id]);
					if ($stmnt->rowCount() == 0) {
						return true;
					}
				}
			}
		}
		return false;	
	}

	function checkExtensionFeaturesUpdate($report, $compare_id) {
		if (array_key_exists('extended', $report)) {
			if (array_key_exists('devicefeatures2', $report['extended'])) {
				if ((is_array($report['extended']['devicefeatures2'])) && (count($report['extended']['devicefeatures2']) > 0)) {
					// Update allowed if number of extended features in the new report is higher than what's stored on the databae
					$stmnt = DB::$connection->prepare("SELECT count(*) from devicefeatures2 where reportid = :reportid");
					$stmnt->execute(['reportid' => $compare_id]);
					$count_report = count($report['extended']['devicefeatures2']);
					$count_database = intval($stmnt->fetchColumn());
					if ($count_report > $count_database) {
						return true;
					}
				}
			}
		}
		return false;
	}

	function checkExtensionPropertiesUpdate($report, $compare_id) {
		if (array_key_exists('extended', $report)) {
			if (array_key_exists('deviceproperties2', $report['extended'])) {
				if ((is_array($report['extended']['deviceproperties2'])) && (count($report['extended']['deviceproperties2']) > 0)) {
					// Update allowed if number of extended properties in the new report is higher than what's stored on the databae
					$stmnt = DB::$connection->prepare("SELECT count(*) from deviceproperties2 where reportid = :reportid");
					$stmnt->execute(['reportid' => $compare_id]);
					$count_report = count($report['extended']['deviceproperties2']);
					$count_database = intval($stmnt->fetchColumn());
					if ($count_report > $count_database) {
						return true;
					}
				}
			}
		}
		return false;
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
	$file_name = uniqid('report_update_check').'json';
	move_uploaded_file($_FILES['data']['tmp_name'], $path.$file_name) or die('');

	$jsonFile = file_get_contents($file_name);	
	$report = json_decode($jsonFile, true);

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


	$can_update = false;

	try {
		DB::connect();
		$can_update = checkCore11Update($report, $reportid);
		if (!$can_update) {
			$can_update = checkCore12Update($report, $reportid);
		}
		if (!$can_update) {
			$can_update = checkExtensionFeaturesUpdate($report, $reportid);
		}
		if (!$can_update) {
			$can_update = checkExtensionPropertiesUpdate($report, $reportid);
		}
		DB::disconnect();
		if ($can_update) {
			header('HTTP/1.1 200 update_allowed');
			echo 1;
		} else {
			header('HTTP/1.1 200 update_not_allowed');
			echo 0;
		}
	} catch (Exception $e) {
		header('HTTP/1.1 500 error');
		echo "Server error";
	} finally {
		unlink($path.$file_name);
	}