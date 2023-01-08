<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2020-2022 by Sascha Willems (www.saschawillems.de)
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

session_set_cookie_params(0, '/', '.gpuinfo.org');
session_name('gpuinfo');
session_start();

if ($_SESSION['theme']) {
    if ($_SESSION['theme'] == 'dark') {
        $_SESSION['theme'] = 'light';
    } else {
        $_SESSION['theme'] = 'dark';
    }
} else {
    $_SESSION['theme'] = 'dark';
}

header('Location: ' . $_SERVER['HTTP_REFERER']);