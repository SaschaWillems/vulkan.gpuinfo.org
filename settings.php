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

require 'pagegenerator.php';
PageGenerator::header('Global settings');

$version_options = [
    'all' => 'All Vulkan versions',
    '1.1' => 'Vulkan 1.1 and up',
    '1.2' => 'Vulkan 1.2 and up',
    '1.3' => 'Vulkan 1.3 and up'
];

$date_options = [
    'all' => 'All reports',
    '2' => 'Newer than 2 years',
    '1' => 'Newer than 1 year',
];

$platform_options = [
    'all' => 'All platforms',
    'windows' => 'Windows',
    'linux' => 'Linux',
    'android' => 'Android',
    'macos' => 'macOS',
    'ios' => 'iOS'
];

$device_type_options = [
    'all' => 'All device types',
    'no_cpu' => 'Exclude CPU implementations',
    'no_virtual' => 'Exclude virtual implementations',
    'no_cpu_no_virtual' => 'Exclude CPU and virtual implementations'
]

?>

<div class="container">
    <div class="center panel panel-default" style="margin: auto; max-width: 70%">
        <div class="panel-body">

            <div class="page-header">
                <h2>Global settings</h2>
            </div>

            <div>Changes made on this page are globally applied to all views and can be used to prefilter data</div>

            <form class="form-horizontal" style="max-width: 640px; margin-top: 50px;" action="database/updatesettings.php" method="POST">

                <div class="form-group">
                    <label for="vulkan_version" class="control-label col-sm-4">Min. Vulkan version: </label>
                    <div class="col-sm-6">
                        <select name="vulkan_version" id="vulkan_version" class="form-control">
                            <?php
                            foreach ($version_options as $value => $text) {
                                $select = ($_SESSION['minversion'] == $value) ? 'selected' : '';
                                echo "<option value=\"$value\" $select>$text</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="date_range" class="control-label col-sm-4">Min. report age: </label>
                    <div class="col-sm-6">
                        <select name="date_range" id="date_range" class="form-control">
                            <?php
                            foreach ($date_options as $value => $text) {
                                $select = ($_SESSION['date_range'] == $value) ? 'selected' : '';
                                echo "<option value=\"$value\" $select>$text</option>";
                            }
                            ?>                        
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="device_types" class="control-label col-sm-4">Device types: </label>
                    <div class="col-sm-6">
                        <select name="device_types" id="device_types" class="form-control">
                        <?php
                            foreach ($device_type_options as $value => $text) {
                                $select = ($_SESSION['device_types'] == $value) ? 'selected' : '';
                                echo "<option value=\"$value\" $select>$text</option>";
                            }
                            ?>   
                        </select>
                    </div>
                </div>                

                <div class="form-group">
                    <label for="default_os_selection" class="control-label col-sm-4">Default coverage view: </label>
                    <div class="col-sm-6">
                        <select name="default_os_selection" id="default_os_selection" class="form-control">
                        <?php
                            foreach ($platform_options as $value => $text) {
                                $select = ($_SESSION['default_os_selection'] == $value) ? 'selected' : '';
                                echo "<option value=\"$value\" $select>$text</option>";
                            }
                            ?>   
                        </select>
                    </div>
                </div>

                <div class="form-group" style="padding-top: 25px;">
                    <div class="col-sm-offset-4 col-sm-10">
                        <button type="submit" name="save" class="btn btn-success">Save settings</button>
                        <button type="submit" name="reset" class="btn btn-danger">Reset to default</button>
                    </div>
                </div>

                <?php
                    if (isset($_SESSION['message'])) {
                        echo "<div class=\"col-sm-4\"></div><div class=\"col-sm-6\"><div style=\"margin: 5px 0px 15px 0px;\">".$_SESSION['message']."</div></div>";
                        unset($_SESSION['message']);
                    }
                ?>

            </form>

        </div>
    </div>
</div>