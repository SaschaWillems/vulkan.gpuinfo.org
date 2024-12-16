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

if (isset($_POST['reset'])) {
    unset($_SESSION['minversion']);
    unset($_SESSION['date_range']);
    unset($_SESSION['default_os_selection']);
    unset($_SESSION['device_types']);
    $_SESSION['message'] = 'Settings reset to default';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

if (isset($_POST['vulkan_version'])) {
    $available_versions = ['1.1', '1.2', '1.3', '1.4', 'all'];
    if (in_array($_POST['vulkan_version'], $available_versions)) {
        if ($_POST['vulkan_version'] == 'all') {
            unset($_SESSION['minversion']);
        } else {
            $_SESSION['minversion'] = $_POST['vulkan_version'];
        }
    }
}

if (isset($_POST['date_range'])) {
    $available_date_ranges = ['1', '2', 'all'];
    if (in_array($_POST['date_range'], $available_date_ranges)) {
        if ($_POST['date_range'] == 'all') {
            unset($_SESSION['date_range']);
        } else {
            $_SESSION['date_range'] = $_POST['date_range'];
        }
    }
}

if (isset($_POST['default_os_selection'])) {
    $available_os = ['windows', 'linux', 'android', 'macos', 'ios', 'all'];
    if (in_array($_POST['default_os_selection'], $available_os)) {
        if ($_POST['default_os_selection'] == 'all') {
            unset($_SESSION['default_os_selection']);
        } else {
            $_SESSION['default_os_selection'] = $_POST['default_os_selection'];
        }
    }
}

if (isset($_POST['device_types'])) {
    $device_types = ['all', 'no_cpu', 'no_virtual', 'no_cpu_no_virtual'];
    if (in_array($_POST['device_types'], $device_types)) {
        if ($_POST['device_types'] == 'all') {
            unset($_SESSION['device_types']);
        } else {
            $_SESSION['device_types'] = $_POST['device_types'];
        }
    }
}

// Redirect to invoking page
$_SESSION['message'] = 'Settings saved';
header('Location: ' . $_SERVER['HTTP_REFERER']);