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
switch($action) {
    case 'add':
        if (in_array(intval($_POST['reportid']), $_SESSION['compare_ids']) === false) {
            $_SESSION['compare_ids'][] = intval($_POST['reportid']);
            $_SESSION['compare_names'][] = $_POST['reportname'];
        }
        break;
    case 'remove':
        $deleteIdx = null;
        for ($i = 0; $i < count($_SESSION['compare_ids']); $i++) {
            if ($_SESSION['compare_ids'][$i] == intval($_POST['reportid'])) {
                $deleteIdx = $i;
                break;
            }
        }
        if ($deleteIdx !== null) {
            array_splice($_SESSION['compare_ids'], $deleteIdx, 1);
            array_splice($_SESSION['compare_names'], $deleteIdx, 1);
        }
        break;
    case 'clear':
        $_SESSION['compare_ids'] = [];
        $_SESSION['compare_names'] = [];
        break;
}

$response = [];
if (is_array($_SESSION['compare_ids'])) {
    for ($i = 0; $i < count($_SESSION['compare_ids']); $i++) {
        $response[] = [
            "id" => $_SESSION['compare_ids'][$i],
            "name" => $_SESSION['compare_names'][$i]
        ];
    }
}

$json = json_encode($response);
echo $json;