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

if (isset($_GET['version'])) {
    $available_versions = ['1.1', '1.2', '1.4', 'all'];
    if (in_array($_GET['version'], $available_versions)) {
        $_SESSION['minversion'] = $_GET['version'];
        if ($_GET['version'] == 'all') {
            unset($_SESSION['minversion']);
        }
    }
    header('Location: ' . $_SERVER['HTTP_REFERER']);
}
