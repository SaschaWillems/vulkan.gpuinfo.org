<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2022 by Sascha Willems (www.saschawillems.de)
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

// Writes and reads reports to compare to the server session

session_start();
header("HTTP/1.1 200 OK");

$action = $_POST['action'];
$reportid = intval($_POST['reportid']);
$reportname = $_POST['reportname'];

switch($action) {
    case 'add':
        if ((!is_array($_SESSION['compare_reports'])) || (!array_key_exists($reportid, $_SESSION['compare_reports']))) {
            $_SESSION['compare_reports'][$reportid] = $reportname;
        }
        break;
    case 'remove':
        if ((!is_array($_SESSION['compare_reports'])) || (array_key_exists($reportid, $_SESSION['compare_reports']))) {
            unset($_SESSION['compare_reports'][$reportid]);
        }
        break;
    case 'clear':      
        $_SESSION['compare_reports'] = [];
        break;
}

$response = [];
if (is_array($_SESSION['compare_reports'])) {
    foreach ($_SESSION['compare_reports'] as $key => $value) {
        $response[] = [
            "id" => $key,
            "name" => $value
        ];
    }
}        
$json = json_encode($response);
echo $json;