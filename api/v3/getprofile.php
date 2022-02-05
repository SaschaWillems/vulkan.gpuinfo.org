<?php
/* 		
*
* Vulkan hardware capability database back-end
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

include './../../database/database.class.php';
include './../../includes/functions.php';
	
if (!isset($_GET['id'])) {
    header('HTTP/ 400 missing_or');
    echo "No report id specified!";
    die();
}

DB::connect();

$reportid = $_GET['id'];	
$json_data = null;

$stmnt = DB::$connection->prepare("SELECT * from reports where id = :reportid");
$stmnt->execute([":reportid" => $reportid]);
if ($stmnt->rowCount() == 0) {
    DB::disconnect();
    header('HTTP/ 400 missing_or');
    echo "No report id specified!";
    die();
}
$row = $stmnt->fetch(PDO::FETCH_ASSOC);
$device_name = $row['devicename'];
$api_version = $row['apiversion'];

$capabilities = [];
$profile_info = [];

$profile_info['GPUINFO_Exported_Profile'] = [
    "version" => 1,
    "api-version" => $api_version,
    "label" => "$device_name",
    "description" => "Exported from https://vulkan.gpuinfo.org",
    "contributors" => [],
    "history" => [],
    "capabilities" => ["baseline"]
];

$profile_caps['baseline'] = [
    'extensions' => [],
    'features' => (object)null,
    'properties' => (object)null,
    'formats' => (object)null,
    'queueFamiliesProperties' => []
];

function skipField($name) {
    $skip_fields = [
        'reportid',
        'headerversion',
        'productModel',
        'productManufacturer',
        'apiversionraw',
        'deviceid',
        'devicename',
        'devicetype',
        'driverversion',
        'driverversionraw',
    ];
    return in_array($name, $skip_fields);
}

function capitalizeFieldName($name) {
    $spelling = [
        'vendorid' => 'vendorID',
        'apiversion' => 'apiVersion'
    ];
    if (array_key_exists(strtolower($name), $spelling)) {
        return $spelling[$name];
    }
    return $name;
}

function convertFieldValue($name, $value) {
    switch (strtolower($name)) {
        case 'vendorid':
        case 'maxmultiviewviewcount':
        case 'maxmultiviewinstanceindex':
            return intval($value);
        case 'pipelinecacheuuid':
        case 'deviceuuid':
        case 'driveruuid':
        case 'deviceluid':
            return unserialize($value);
        case 'deviceluidvalid':
        case 'protectednofault':
        case 'subgroupquadoperationsinallstages':
            return boolval($value);
    }
    return $value;
}

function insertDeviceFeatures($version, $reportid, &$cap_node) {
    $table = 'devicefeatures';
    $req_name = 'vulkan10requirements';
    $struct_name = 'VkPhysicalDeviceFeatures';
    switch ($version) {
        case '1.1':
            $table = 'devicefeatures11';
            $req_name = 'vulkan11requirements';
            $struct_name = 'VkPhysicalDeviceVulkan11Features';
            break;
        case '1.2':
            $table = 'devicefeatures12';
            $req_name = 'vulkan12requirements';
            $struct_name = 'VkPhysicalDeviceVulkan12Features';
            break;
        case '1.3':
            $table = 'devicefeatures13';
            $req_name = 'vulkan13requirements';
            $struct_name = 'VkPhysicalDeviceVulkan13Features';
            break;
    }
    $stmnt = DB::$connection->prepare("SELECT * from $table where reportid = :reportid");
    $stmnt->execute([":reportid" => $reportid]);
    $result = $stmnt->fetch(PDO::FETCH_ASSOC);
    if ($stmnt->rowCount() == 0) {
        return;
    }
    $features_node = [];
    foreach ($result as $key => $value) {
        if (skipField($key)) {
            continue;
        }
        $features_node[$key] = boolval($value);
    }
    $cap_node[$req_name]['features'][$struct_name] = $features_node;
}

function insertDeviceProperties($version, $reportid, &$cap_node) {
    // @todo: limits for vk1.0
    // @todo: sparse properties for vk1.0
    $table = 'deviceproperties';
    $req_name = 'vulkan10requirements';
    $struct_name = 'VkPhysicalDeviceProperties';
    switch ($version) {
        case '1.1':
            $table = 'deviceproperties11';
            $req_name = 'vulkan11requirements';
            $struct_name = 'VkPhysicalDeviceVulkan11Properties';
            break;
        case '1.2':
            $table = 'deviceproperties12';
            $req_name = 'vulkan12requirements';
            $struct_name = 'VkPhysicalDeviceVulkan12Properties';
            break;
        case '1.3':
            $table = 'deviceproperties13';
            $req_name = 'vulkan13requirements';
            $struct_name = 'VkPhysicalDeviceVulkan13Properties';
            break;
    }
    $stmnt = DB::$connection->prepare("SELECT * from $table where reportid = :reportid");
    $stmnt->execute([":reportid" => $reportid]);
    $result = $stmnt->fetch(PDO::FETCH_ASSOC);
    if ($stmnt->rowCount() == 0) {
        return;
    }
    $features_node = [];
    foreach ($result as $key => $value) {
        if (skipField($key)) {
            continue;
        }
        $converted_value = convertFieldValue($key, $value);
        $features_node[capitalizeFieldName($key)] = $converted_value;
    }
    $cap_node[$req_name]['properties'][$struct_name] = $features_node;
}

$versions = ['1.0', '1.1', '1.2', '1.3'];

foreach ($versions as $version) {
    insertDeviceFeatures($version, $reportid, $profile_caps);
    insertDeviceProperties($version, $reportid, $profile_caps);
}

// Extensions
$stmnt = DB::$connection->prepare("SELECT name, specversion from deviceextensions de join extensions e on de.extensionid = e.id where reportid = :reportid");
$stmnt->execute([":reportid" => $reportid]);
while ($row = $stmnt->fetch(PDO::FETCH_ASSOC)) {
    $profile_caps['baseline']['extensions'][$row['name']] = intval($row['specversion']);
}

$profile['$schema'] = 'https://schema.khronos.org/vulkan/profiles-1.3.204.json#';
$profile['profiles'] = $profile_info;
$profile['capabilities'] = $profile_caps;

$filename = $device_name;
$filename = preg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $filename);
$filename = preg_replace("([\.]{2,})", '', $filename);	
$filename .= ".json";

header("Content-type: application/json");
// header("Content-Disposition: attachment; filename=".strtolower($filename));

echo json_encode($profile, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

DB::disconnect();