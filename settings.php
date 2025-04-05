<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *
 * Copyright (C) 2016-2025 by Sascha Willems (www.saschawillems.de)
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
    '1.3' => 'Vulkan 1.3 and up',
    '1.4' => 'Vulkan 1.4 and up'
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
];

$layered_implementation_options= [
    0 => 'No',
    1 => 'Yes'
];

function addOption($caption, $label, $options) {
    $session_id = $label;
    // Backwards compat. with existing settings
    if ($label == 'vulkan_version') {
        $session_id = 'minversion';
    }
    echo "<div class=\"form-group\"> <label for=\"$label\" class=\"control-label col-sm-4\">$caption: </label> <div class=\"col-sm-6\"> <select name=\"$label\" id=\"$label\" class=\"form-control\">";               
    foreach ($options as $value => $text) {
        $selected = ($_SESSION[$session_id] == $value) ? 'selected' : '';
        echo "<option value=\"$value\" $selected>$text</option>";
    };
    echo "</select></div></div>".PHP_EOL;
}

?>

<div class="container">
    <div class="center panel panel-default" style="margin: auto; max-width: 70%">
        <div class="panel-body">

            <div class="page-header">
                <h2>Global settings</h2>
            </div>

            <div>
                Changes made on this page are globally applied to all views and can be used to prefilter data
            </div>

            <div style="padding-top: 25px;">
        	    <b>Note:</b> Settings use server side sessions. In order to identify your session, a session cookie is stored in your browser.
                This cookie is <b>only</b> used for settings on this page and is in no way used for user-tracking.
                As such it is considered a <b>necessary cookie</b>.
            </div>

            <form class="form-horizontal" style="max-width: 640px; margin-top: 50px;" action="database/updatesettings.php" method="POST">

                <?php
                    addOption('Min. Vulkan version', 'vulkan_version', $version_options);
                    addOption('Min. report age', 'date_range', $date_options);
                    addOption('Device types', 'device_types', $device_type_options);
                    addOption('Include layered impl.', 'layered_implementations', $layered_implementation_options);
                    addOption('Default platform', 'default_os_selection', $platform_options);
                ?>
         
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