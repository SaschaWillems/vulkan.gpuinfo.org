<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2021 Sascha Willems (www.saschawillems.de)
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

$fname =  $_POST['fname'];
$lname = $_POST['lname'];

if (isset($_POST['vulkan_api_version']))
{
    setcookie('vulkan_api_version', $_POST['vulkan_api_version'], time() + 36000);
    $_COOKIE['vulkan_api_version'] = $_POST['vulkan_api_version'];
}

require 'pagegenerator.php';
require 'database/database.settings.php';

$database_settings = new DatabaseSettings();

PageGenerator::header("Settings");
?>

<div class='header'>
    <h4>
        Settings
    </h4>
</div>

<form class="form-horizontal" style="padding-top: 25px; max-width: 512px; margin: auto" method="post" action="settings.php">

    <div class="alert alert-info">
        Settings are stored in a cookie
    </div>

    <div class="form-group" style="margin-left: 0px; margin-right: 0px;">
        <label for="vulkan_api_version">Vulkan API version to display on the database</label>
        <p>Selecting a value other than "all" will limit all database queries to device reports with at least the selected version</p>
        <select class="form-control" id="vulkan_api_version" name="vulkan_api_version">
            <option value="1.2" <?= ($database_settings->vulkan_api_version == "1.2" ? "selected" : null) ?>>Vulkan 1.2 and up</option>
            <option value="1.1" <?= ($database_settings->vulkan_api_version == "1.1" ? "selected" : null) ?>>Vulkan 1.1 and up</option>
            <option value="" <?= ($database_settings->vulkan_api_version == null ? "selected" : null) ?>>All Vulkan versions</option>
        </select>
    </div>

    <button type="submit" name="submit" value="settings" class="btn btn-primary">Save</button>

</form>