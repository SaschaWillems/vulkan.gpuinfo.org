<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2024 by Sascha Willems (www.saschawillems.de)
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

session_set_cookie_params(31536000, '/', $_SERVER['SERVER_NAME']);
session_name('vulkan');
session_start(); 

if (isset($_GET['vulkan_version'])) {
    $available_versions = ['1.1', '1.2', '1.3', 'all'];
    if (in_array($_GET['vulkan_version'], $available_versions)) {
        if ($_GET['vulkan_version'] == 'all') {
            unset($_SESSION['minversion']);
        } else {
            $_SESSION['minversion'] = $_GET['vulkan_version'];
        }
    }
}

if (isset($_GET['date_range'])) {
    $available_date_ranges = ['1', '2', 'all'];
    if (in_array($_GET['date_range'], $available_date_ranges)) {
        if ($_GET['date_range'] == 'all') {
            unset($_SESSION['date_range']);
        } else {
            $_SESSION['date_range'] = $_GET['date_range'];
        }
    }
}

if (isset($_GET['default_os_selection'])) {
    $available_os = ['windows', 'linux', 'android', 'macos', 'ios', 'all'];
    if (in_array($_GET['default_os_selection'], $available_os)) {
        if ($_GET['default_os_selection'] == 'all') {
            unset($_SESSION['default_os_selection']);
        } else {
            $_SESSION['default_os_selection'] = $_GET['default_os_selection'];
        }
    }
}

if (isset($_GET['device_types'])) {
    $device_types = ['all', 'no_cpu'];
    if (in_array($_GET['device_types'], $device_types)) {
        if ($_GET['device_types'] == 'all') {
            unset($_SESSION['device_types']);
        } else {
            $_SESSION['device_types'] = $_GET['device_types'];
        }
    }
}

// Redirect to invoking page
header('Location: ' . $_SERVER['HTTP_REFERER']);