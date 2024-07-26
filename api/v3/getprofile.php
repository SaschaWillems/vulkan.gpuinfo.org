<?php
/* 		
*
* Vulkan hardware capability database back-end
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

/** Generate a Vulkan Profile schema compliant JSON for device simulation */

require './../../database/database.class.php';
require './../../includes/functions.php';
require './../../includes/vktypes.php';

header("Content-type: application/json");

if (!isset($_GET['id'])) {
    header('HTTP/ 400 missing_or');
    echo "No report id specified!";
    die();
}

DB::connect();
$reportid = $_GET['id'];	
$stmnt = DB::$connection->prepare("SELECT * from reports where id = :reportid");
$stmnt->execute([":reportid" => $reportid]);
if ($stmnt->rowCount() == 0) {
    DB::disconnect();
    echo json_encode(['error' => "Could not find report with id $reportid"]);
    exit();
}
$row = $stmnt->fetch(PDO::FETCH_ASSOC);

class VulkanProfile {
    private $reportid = null;
    private $queue_families = [];
    private $formats = [];
    private $extensions = [];
    private $features = [];
    private $extension_features = [];
    private $properties = [];
    private $extension_properties = [];
    public $warnings = [];

    private $profile_version = 1;
    private $device_name = null;
    private $report_label = null;
    private $api_version = null;
    
    public $profile_name;
    public $json = null;
    private $json_schema_name = null;
    private $json_schema = null;
    private $extension_mapping = null;

    function __construct($reportid) {
        $this->reportid = $reportid;
        $src = file_get_contents('./../../includes/mappings.json');
        $this->extension_mapping = json_decode($src, true);
    }

    /** Loads the JSON schema matching the report's api header version */
    private function loadSchema($apiversion) {
        // Get profiles schema based on patch level (=header revision)
        $header_version = explode('.', $apiversion)[2];
        $report_profile_name = "../../profiles/schema/profiles-0.8.2-$header_version.json";
        // Use the latest profile if no matching file could be found
        if (!file_exists($report_profile_name)) {
            $report_profile_name = "../../profiles/schema/profiles-0.8-latest.json";
        }
        $file = file_get_contents($report_profile_name);
        $this->json_schema_name = 'https://schema.khronos.org/vulkan/'.str_replace("../../profiles/schema/", "", $report_profile_name).'#';
        $this->json_schema = json_decode($file, true);
    }

    /** Some fields in the database tables are not part of the spec and need to be skipped completely or skipped to be remapped later*/
    private function skipField($name, $version) {
        $skip_fields = [
            'reportid',
            'headerversion',
            'productModel',
            'productManufacturer',
            'deviceid',
            'devicename',
            'devicetype',
            'driverversion',
            'driverversionraw',
        ];
        if ($version == '1.0') {
            $skip_fields = array_merge($skip_fields, [
                'residencyAlignedMipSize',
                'residencyNonResidentStrict',
                'residencyStandard2DBlockShape',
                'residencyStandard2DMultisampleBlockShape',
                'residencyStandard3DBlockShape',
                'subgroupProperties.subgroupSize',
                'subgroupProperties.supportedStages',
                'subgroupProperties.supportedOperations',
                'subgroupProperties.quadOperationsInAllStages',
                'maxComputeWorkGroupCount[0]',
                'maxComputeWorkGroupCount[1]',
                'maxComputeWorkGroupCount[2]',
                'maxComputeWorkGroupSize[0]',
                'maxComputeWorkGroupSize[1]',
                'maxComputeWorkGroupSize[2]',
                'maxViewportDimensions[0]',
                'maxViewportDimensions[1]',
                'pointSizeRange[0]',
                'pointSizeRange[1]',
                'viewportBoundsRange[0]',
                'viewportBoundsRange[1]',
                'lineWidthRange[0]',
                'lineWidthRange[1]',
            ]);
        }
        return in_array($name, $skip_fields);
    }

    /** Checks if a (type) definition is available in the currently loaded schema and returns it (or null if not) */
    private function getSchemaDefintion($name) {
        if (array_key_exists($name, $this->json_schema['definitions'])) {
            return $this->json_schema['definitions'][$name];
        }
        return null;
    }

    /** Checks if a feature type is defined in the currently loaded schema */
    private function getSchemaFeatureTypeDefinition($name) {
        if (array_key_exists($name, $this->json_schema['properties']['capabilities']['additionalProperties']['properties']['features']['properties'])) {
            return $this->json_schema['properties']['capabilities']['additionalProperties']['properties']['features']['properties'][$name];
        }        
        return null;
    }

    /** Checks if a property type is defined in the currently loaded schema */
    private function getSchemaPropertyTypeDefinition($name) {
        if (array_key_exists($name, $this->json_schema['properties']['capabilities']['additionalProperties']['properties']['properties']['properties'])) {
            return $this->json_schema['properties']['capabilities']['additionalProperties']['properties']['properties']['properties'][$name];
        }
        return null;
    }

    /** Checks if an extension definition is available in the mapping and returns it (or null if not) */
    private function getExtensionMapping($name) {
        if (array_key_exists($name, $this->extension_mapping)) {
            return $this->extension_mapping[$name];
        }
        return null;
    }

    /** @todo */
    private function getEnumMapping($name) {
        if (array_key_exists($name, $this->extension_mapping['enums'])) {
            return $this->extension_mapping['enums'][$name];
        }
        return null;
    }

    /** Checks if the extension has been promoted to the core version of the report */
    private function getExtensionPromoted($extension) {
        // Build list of core api versions to skip based on device's api level
        $api_version_skip_list = [];
        $api_major = explode('.', $this->api_version)[0];
        $api_minor = explode('.', $this->api_version)[1];
        if ($api_minor >= 1) {
            $api_version_skip_list[] = 'VK_VERSION_1_1';
        }
        if ($api_minor >= 2) {
            $api_version_skip_list[] = 'VK_VERSION_1_2';
        }
        if ($api_minor >= 3) {
            $api_version_skip_list[] = 'VK_VERSION_1_3';
        }        
        if ($extension['promoted_to'] !== '') {
            if (stripos($extension['promoted_to'], 'VK_VERSION') !== false) {
                if (in_array($extension['promoted_to'], $api_version_skip_list)) {
                    return true;
                }
            }
        }
        return false;
    }    

    /** Applies conversion rules based on value types */
    private function convertValue($value, $type, $name = null, $extension = null) {
        $convert = function($value, $type, $extension) {
            // Try to automatically convert from mapping based on spec
            // This should work for most types, esp. for extensions
            $enum_mapping = self::getEnumMapping($type);
            if ($enum_mapping) {
                if ($enum_mapping['type'] == 'enum') {
                    $idx = array_search($value, $enum_mapping['values']);
                    if ($idx !== false) {
                        return $enum_mapping['names'][$idx];
                    }
                }
            }
            // For flags this is not as easy, since the type does not directly tell us the flag set
            // e.g. VkOpticalFlowGridSizeFlagsNV -> VkOpticalFlowGridSizeFlagBitsNV
            // @todo: Check if we can get this from vk.xml somehow
            $flag_type_mappings = [
                'VkOpticalFlowGridSizeFlagsNV' => 'VkOpticalFlowGridSizeFlagBitsNV',
                'VkMemoryDecompressionMethodFlagsNV' => 'VkMemoryDecompressionMethodFlagBitsNV',
                'VkQueueFlags' => 'VkQueueFlagBits'
            ];
            if (array_key_exists($type, $flag_type_mappings) != false) {
                $enum_mapping = self::getEnumMapping($flag_type_mappings[$type]);
                if ($enum_mapping) {
                    $bit_positions = $enum_mapping['bitpos'];
                    $supported_flags = [];
                    $index = 0;
                    foreach ($bit_positions as $bit_pos) {
                        if ((int)$value & pow(2, $bit_pos)) {
                            $supported_flags[] = $enum_mapping['bitnames'][$index];
                        }
                        $index++;
                    }
                    return $supported_flags;                    
                }
            }
            // If we can't convert from the mapping, try to manually convert
            switch($type) {
                case 'uint8_t':
                case 'uint16_t':
                case 'uint32_t':
                case 'int8_t':
                case 'int16_t':
                case 'int32_t':
                case 'size_t':
                    return intval($value);
                case 'VkDeviceSize':
                case 'int64_t':
                case 'uint64_t':
                    // @todo: JS/JSON has limited support for 64 bit values, but probably okay to export them as int here
                    return intval($value);
                case 'float':
                    return floatval($value);
                case 'VkBool32':
                    return boolval($value);
                case 'VkSampleCountFlags':
                    return VkTypes::VkSampleCountFlags($value);
                case 'VkShaderStageFlags':
                    return VkTypes::VkShaderStageFlags($value);
                case 'VkSampleCountFlagBits':
                    return VkTypes::VkSampleCountFlagBits($value);
                case 'VkSubgroupFeatureFlags':
                    return VkTypes::VkSubgroupFeatureFlags($value);
                case 'VkDriverId':
                    return VkTypes::VkDriverId($value, $extension);
                case 'VkConformanceVersion':
                    return VkTypes::VkConformanceVersion($value);
                case 'VkResolveModeFlags':
                    return VkTypes::VkResolveModeFlags($value);
                case 'VkShaderCorePropertiesFlagsAMD':
                    // No flags defined in spec
                    return [];
                case 'VkExtent2D':
                    $arr = unserialize($value);                
                    return ['width' => $arr[0], 'height' => $arr[1]];
            }
            return $value;
        };
        if (!in_array($type, ['VkExtent2D', 'VkExtent3D']) && substr($value, 0, 2) == 'a:') {
            $arr = unserialize($value);
            $values = [];
            foreach($arr as $value) {
                $values[] = $convert($value, $type, $extension);
            }
            if ($name == "deviceLUID") {
                $values = array_slice($values, 0, 8);
            }
            return $values;
        } else {
            $val = $convert($value, $type, $extension);
            if (($val == null) && (in_array($name, ['sparseAddressSpaceSize']))) {
                return 0;
            }
            return $val;
        }
    }  

    /** Core features */
    private function readFeatures($version) {
        $table_names = [
            '1.0' => 'devicefeatures',
            '1.1' => 'devicefeatures11',
            '1.2' => 'devicefeatures12',
            '1.3' => 'devicefeatures13',
        ];
        $table = $table_names[$version];
        $stmnt = DB::$connection->prepare("SELECT * from $table where reportid = :reportid");
        $stmnt->execute([":reportid" => $this->reportid]);
        $result = $stmnt->fetch(PDO::FETCH_ASSOC);
        if ($stmnt->rowCount() == 0) {
            return null;
        }
        $features = [];
        foreach ($result as $key => $value) {
            if ($this->skipField($key, $version)) {
                continue;
            }
            $features[$key] = boolval($value);
        }
        return count($features) > 0 ? $features : null;
    }

    /** Vulkan 1.0 device limits */
    private function readDeviceLimits() {
        $limit_stmnt = DB::$connection->prepare('SELECT * from devicelimits where reportid = :reportid');
        $limit_stmnt->execute([":reportid" => $this->reportid]);
        $limit_result = $limit_stmnt->fetch(PDO::FETCH_ASSOC);
        foreach ($limit_result as $limit_key => $limit_value) {
            if ($this->skipField($limit_key, '1.0')) {
                continue;
            }
            $type = VkTypes::$VkPhysicalDeviceLimits[$limit_key];
            $limits[$limit_key] = $this->convertValue($limit_value, $type, $limit_key);
        }    
        
        $limitToArray = function($name, $dim, $type) use ($limit_result) {
            $values = [];
            for ($i = 0; $i < $dim; $i++) {
                if ($type == 'int') {
                    $values[] = intval($limit_result[$name.'['.$i.']']);
                };
                if ($type == 'float') {
                    $values[] = floatval($limit_result[$name.'['.$i.']']);
                };
            }
            return $values;
        };
    
        // Multi-dimensional arrays are stored as single columns in the database and need to be remapped        
        $limits['maxComputeWorkGroupCount'] = $limitToArray('maxComputeWorkGroupCount', 3, 'int');
        $limits['maxComputeWorkGroupSize'] = $limitToArray('maxComputeWorkGroupSize', 3, 'int');
        $limits['maxViewportDimensions'] = $limitToArray('maxViewportDimensions', 2, 'int');
        $limits['pointSizeRange'] = $limitToArray('pointSizeRange', 2, 'float');
        $limits['viewportBoundsRange'] = $limitToArray('viewportBoundsRange', 2, 'float');
        $limits['lineWidthRange'] = $limitToArray('lineWidthRange', 2, 'float');
    
        return $limits;
    }

    /** Core properties */
    private function readProperties($version) {
        switch($version) {
            case '1.0':
                $table = 'deviceproperties';
                $type_mappings = VkTypes::$VkPhysicalDeviceProperties;
                break;
            case '1.1':
                $table = 'deviceproperties11';
                $type_mappings = VkTypes::$VkPhysicalDeviceVulkan11Properties;
                break;
            case '1.2':
                $table = 'deviceproperties12';
                $type_mappings = VkTypes::$VkPhysicalDeviceVulkan12Properties;
                break;
            case '1.3':
                $table = 'deviceproperties13';
                $type_mappings = VkTypes::$VkPhysicalDeviceVulkan13Properties;
                break;
        }
        $stmnt = DB::$connection->prepare("SELECT * from $table where reportid = :reportid");
        $stmnt->execute([":reportid" => $this->reportid]);
        $result = $stmnt->fetch(PDO::FETCH_ASSOC);
        if ($stmnt->rowCount() == 0) {
            return null;
        }
        $properties = [];
        foreach ($result as $key => $value) {
            if ($this->skipField($key, $version)) {
                continue;
            }
            $key_name = $key;
            if ($version == '1.0') {
                // Use non-human readable api version
                if ($key == 'apiversion') {
                    continue;
                }
                if ($key == 'apiversionraw') {
                    $key_name = 'apiVersion';
                }
                // Fix some spelling differences between spec and database
                if ($key == 'vendorid') {
                    $key_name = 'vendorID';
                }
            }
            if ($version == '1.3') {
                // Some member names are so long that they had to be truncated in the database, so we need to get the long names
                if (stripos($key_name, 'idp') == 0) {
                    $key_name = str_replace('idp', 'integerDotProduct', $key_name);
                }
            }
            $type = $type_mappings[$key_name];
            $properties[$key_name] = $this->convertValue($value, $type, $key_name);
        }
        if ($version == '1.0') {
            // Remap sparse properties into struct
            $sparse_stmnt = DB::$connection->prepare('SELECT residencyAlignedMipSize, residencyNonResidentStrict, residencyStandard2DBlockShape, residencyStandard2DMultisampleBlockShape, residencyStandard3DBlockShape from deviceproperties where reportid = :reportid');
            $sparse_stmnt->execute([":reportid" => $this->reportid]);
            $sparse_result = $sparse_stmnt->fetch(PDO::FETCH_ASSOC);
            foreach ($sparse_result as $sparse_key => $sparse_value) {
                $sparse_properties[$sparse_key] = boolval($sparse_value);
            }
            $properties['sparseProperties'] = $sparse_properties;
            // Append VK1.0 limits
            $properties['limits'] = $this->readDeviceLimits();
        }
        return $properties;        
    }

    /** Extension features */
    function readExtensionFeatures() {
        $stmnt = DB::$connection->prepare("SELECT extension, name, supported from devicefeatures2 where reportid = :reportid");
        $stmnt->execute([":reportid" => $this->reportid]);
        $result = $stmnt->fetchAll(PDO::FETCH_GROUP  | PDO::FETCH_ASSOC);
        foreach ($result as $key => $values) {
            $ext = $this->getExtensionMapping($key);
            if (!$ext) {
                $this->warnings[] = "Could not find a mapping for extension $ext";
                continue;
            }
            // Skip if there is no feature struct defined for this extension
            foreach ($ext['structs']['ext']['physicalDeviceFeatures'] as $struct_name) {
                if (((!$struct_name) || ($struct_name == ''))) {
                    continue;
                }            
                // Skip feature structs that have been promoted to a core version supported by the device
                if ($this->getExtensionPromoted($ext)) {
                    continue;
                }
                // Skip feature structs not defined in the selected schema
                if ($this->getSchemaFeatureTypeDefinition($struct_name) == null) {
                    $this->warnings[] = "$struct_name not found in selected schema";
                    continue;
                }

                $feature = null;
                foreach ($values as $value) {
                    $feature[$value['name']] = boolval($value['supported']);
                }
                $this->extension_features[$struct_name] = $feature;
            }
        }
    }

    /** Extension properties */
    function readExtensionProperties() {
        $stmnt = DB::$connection->prepare("SELECT extension, name, value from deviceproperties2 where reportid = :reportid order by name asc");
        $stmnt->execute([":reportid" => $this->reportid]);
        $result = $stmnt->fetchAll(PDO::FETCH_GROUP  | PDO::FETCH_ASSOC);
        foreach ($result as $key => $values) {
            $ext = $this->getExtensionMapping($key);
            if (!$ext) {
                $this->warnings[] = "Could not find a mapping for extension $ext";
                continue;
            }
            foreach($ext['structs']['ext']['physicalDeviceProperties'] as $struct_name) {
                // Skip if there is no property struct defined for this extension
                if (((!$struct_name) || ($struct_name == ''))) {
                    continue;
                }
                // Skip property structs that have been promoted to a core version supported by the device
                if ($this->getExtensionPromoted($ext)) {
                    continue;
                }
                // Skip property structs not defined in the selected schema
                $property_type_definition = $this->getSchemaPropertyTypeDefinition($struct_name);
                if ($property_type_definition == null) {
                    $this->warnings[] = "$struct_name not found in selected schema";
                }

                // Convert property values based on their types
                $property = null;
                foreach ($values as $value) {
                    $type = null;
                    $value_name = $value['name'];
                    // Some properties are stored in a different format in the database (compared to the struct layouts) and require some transformation
                    if (stripos($struct_name, 'VkPhysicalDeviceSampleLocationsPropertiesEXT') === 0) {
                        if (in_array($value_name, ['maxSampleLocationGridSize.width', 'maxSampleLocationGridSize.height'])) {
                            $property['maxSampleLocationGridSize'][str_replace('maxSampleLocationGridSize.', '', $value_name)] = $this->convertValue($value['value'], 'int32_t', null, $key);
                            continue;
                        }
                        if (in_array($value_name, ['sampleLocationCoordinateRange[0]', 'sampleLocationCoordinateRange[1]'])) {
                            $property['sampleLocationCoordinateRange'][] = $this->convertValue($value['value'], 'float', null, $key);
                            continue;
                        }                    
                    }
                    if (array_key_exists($value_name, $ext['types'][$struct_name])) {
                        $type = $ext['types'][$struct_name][$value_name];
                        $property[$value_name] = $this->convertValue($value['value'], $type, null, $key);
                    }
                }
                if ($property) {
                    $this->extension_properties[$struct_name] = $property;
                }
            }
        }
    }        

    /** Extension list */
    private function readExtensions() {
        $this->extensions = [];
        $stmnt = DB::$connection->prepare("SELECT name, specversion from deviceextensions de join extensions e on de.extensionid = e.id where reportid = :reportid");
        $stmnt->execute([":reportid" => $this->reportid]);
        $schema_extension_list = $this->json_schema["properties"]["capabilities"]["additionalProperties"]["properties"]["extensions"]["properties"];
        while ($row = $stmnt->fetch(PDO::FETCH_ASSOC)) {
            // Skip extensions that are not defined in the current schema
            if (!key_exists($row['name'], $schema_extension_list)) {
                continue;
            }
            $this->extensions[$row['name']] = intval($row['specversion']);
        }
    }

    /** Image and buffer formats */
    private function readFormats() {
        $this->formats = [];
        $stmnt = DB::$connection->prepare("SELECT name, lineartilingfeatures, optimaltilingfeatures, bufferfeatures from deviceformats df join VkFormat vf on df.formatid = vf.value where reportid = :reportid and supported = 1 order by name asc");    
        $stmnt->execute([":reportid" => $this->reportid]);
        while ($row = $stmnt->fetch(PDO::FETCH_ASSOC)) {
            $format = [
                'VkFormatProperties' => [
                    'linearTilingFeatures' => VkTypes::VkFormatFeatureFlags($row['lineartilingfeatures']),
                    'optimalTilingFeatures' => VkTypes::VkFormatFeatureFlags($row['optimaltilingfeatures']),
                    'bufferFeatures' => VkTypes::VkFormatFeatureFlags($row['bufferfeatures'])
                ]
            ];
            $this->formats["VK_FORMAT_".$row['name']] = $format;
        }    
    }

    /** Queue family types */
    private function readQueueFamilies() {
        $this->queue_families = [];
        $stmnt = DB::$connection->prepare("SELECT * from devicequeues where reportid = :reportid");
        $stmnt->execute([":reportid" => $this->reportid]);
        while ($row = $stmnt->fetch(PDO::FETCH_ASSOC)) {
            $profile_queue_family = [
                'VkQueueFamilyProperties' => [
                    'queueFlags' => VkTypes::VkQueueFlags($row['flags']),
                    'queueCount' => intval($row['count']),
                    'timestampValidBits' => intval($row['timestampValidBits']),
                    'minImageTransferGranularity' => [
                        'width' => intval($row['minImageTransferGranularity.width']),
                        'height' => intval($row['minImageTransferGranularity.height']),
                        'depth' => intval($row['minImageTransferGranularity.depth']),
                    ]
                ]
            ];
            $this->queue_families[] = $profile_queue_family;
        }   
    }

    /** Some mappings require manual conversion */
    private function addManualMappings() {
        if ($this->api_version >= '1.0') {
            // Subgroup operations have no explicit struct, so we need to manually create and append that
            $stmnt = DB::$connection->prepare('SELECT 
                `subgroupProperties.subgroupSize` as subgroupSize,
                `subgroupProperties.supportedStages` as supportedStages,
                `subgroupProperties.supportedOperations` as supportedOperations,
                `subgroupProperties.quadOperationsInAllStages` as quadOperationsInAllStages
                from deviceproperties where reportid = :reportid');
            $stmnt->execute([':reportid' => $this->reportid]);
            $row = $stmnt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $subgroup_properties = [
                    'subgroupSize' => intval($row['subgroupSize']),
                    'supportedStages' => VkTypes::VkShaderStageFlags($row['supportedStages']),
                    'supportedOperations' => VkTypes::VkSubgroupFeatureFlags($row['supportedOperations']),
                    'quadOperationsInAllStages' => $row['quadOperationsInAllStages'] ? true : false
                ];
                $this->json['capabilities']['device']['properties']['VkPhysicalDeviceSubgroupProperties'] = $subgroup_properties;
            }
        }        
    }

    /** Device info (including identifiers) */
    private function readDeviceInfo() {
        $stmnt = DB::$connection->prepare("SELECT ifnull(displayname, devicename) as device, reports.* from reports where id = :reportid");
        $stmnt->execute([":reportid" => $this->reportid]);
        if ($stmnt->rowCount() == 0) {
            header('HTTP/ 400 missing_or');
            exit("Could not find report");
        }
        $row = $stmnt->fetch(PDO::FETCH_ASSOC);
        $this->device_name = $row['device'];
        $this->api_version = $row['apiversion'];
        $this->report_label = $row['device']." driver ".$row['driverversion']." on ".ucfirst($row['osname']). " ".$row['osversion'];
        $this->profile_name = "VP_GPUINFO_".$row['device']."_".$row['driverversion']."_".$row['osname']."_".$row['osversion'];
        $this->profile_name = preg_replace("/[^A-Za-z0-9]/", '_', $this->profile_name);
    }

    /** Generate the profile JSON file */
    function generateJSON() {
        $api_versions =  ['1.0', '1.1', '1.2', '1.3'];

        DB::connect();
        $this->readDeviceInfo();
        $this->loadSchema($this->api_version);
        $this->readExtensions();
        foreach ($api_versions as $version) {
            $this->features[$version] = $this->readFeatures($version);
            $this->properties[$version] = $this->readProperties($version);
        }
        $this->readExtensionFeatures();
        $this->readExtensionProperties();
        $this->readFormats();
        $this->readQueueFamilies();

        $this->json['$schema'] = $this->json_schema_name;
        $this->json['profiles'] = [
            $this->profile_name => [
                "version" => $this->profile_version,
                "api-version" => $this->api_version,
                "label" => "$this->report_label",
                "description" => "Exported from https://vulkan.gpuinfo.org",
                "contributors" => [],
                "history" => [],
                "capabilities" => ["device"]
            ]
        ];

        // Required fixed profile details
        $this->json['profiles'][$this->profile_name]['history'][] = [
            "revision" => 1,
            "date" => date('Y-m-d'),
            "author" => "Sascha Willems",
            "comment" => "Automated export from https://vulkan.gpuinfo.org"
        ];
        $this->json['profiles'][$this->profile_name]['contributors']['Sascha Willems'] = [
            "company" => "Independent",
            "contact" => true
        ];

        // Features
        foreach ($api_versions as $version) {
            $node_names = [
                '1.0' => ['requirement' => 'vulkan10requirements', 'struct' => 'VkPhysicalDeviceFeatures'],
                '1.1' => ['requirement' => 'vulkan11requirements', 'struct' => 'VkPhysicalDeviceVulkan11Features'],
                '1.2' => ['requirement' => 'vulkan12requirements', 'struct' => 'VkPhysicalDeviceVulkan12Features'],
                '1.3' => ['requirement' => 'vulkan13requirements', 'struct' => 'VkPhysicalDeviceVulkan13Features'],
            ];
            if (array_key_exists($version, $this->features) && ($this->features[$version] !== null) && count($this->features[$version]) > 0) {
                // Skip if not part of the schema (for reports with invalid api versions)
                $struct_name = $node_names[$version]['struct'];
                if ($this->getSchemaFeatureTypeDefinition($struct_name) == null) {
                    $this->warnings[] = "$struct_name not found in selected schema";
                    continue;
                }   
                $this->json['capabilities']['device']['features'][$struct_name] = $this->features[$version];
            }
        }
        if (count($this->extension_features) > 0) {
            foreach ($this->extension_features as $ext => $features) {
                $this->json['capabilities']['device']['features'][$ext] = $features;
            }
        }

        // Properties   
        foreach ($api_versions as $version) {
            $node_names = [
                '1.0' => ['requirement' => 'vulkan10requirements', 'struct' => 'VkPhysicalDeviceProperties'],
                '1.1' => ['requirement' => 'vulkan11requirements', 'struct' => 'VkPhysicalDeviceVulkan11Properties'],
                '1.2' => ['requirement' => 'vulkan12requirements', 'struct' => 'VkPhysicalDeviceVulkan12Properties'],
                '1.3' => ['requirement' => 'vulkan13requirements', 'struct' => 'VkPhysicalDeviceVulkan13Properties'],
            ];
            if (array_key_exists($version, $this->properties) && ($this->properties[$version] !== null) && count($this->properties[$version]) > 0) {
                // Skip if not part of the schema (for reports with invalid api versions)
                $struct_name = $node_names[$version]['struct'];
                if ($this->getSchemaPropertyTypeDefinition($struct_name) == null) {
                    $this->warnings[] = "$struct_name not found in selected schema";
                    continue;
                }   
                $this->json['capabilities']['device']['properties'][$struct_name] = $this->properties[$version];
            }
        }
        if (count($this->extension_properties) > 0) {
            foreach ($this->extension_properties as $ext => $features) {
                $this->json['capabilities']['device']['properties'][$ext] = $features;
            }
        }

        if ($this->extensions && (count($this->extensions) > 0)) {
            $this->json['capabilities']['device']['extensions'] = $this->extensions;
        } else {
            $this->json['capabilities']['device']['extensions'] = (object)null;
        }

        if ($this->formats && (count($this->formats) > 0)) {
            $this->json['capabilities']['device']['formats'] = $this->formats;
        } else {
            $this->json['capabilities']['device']['formats'] = (object)null;
        }

        if ($this->queue_families && (count($this->queue_families) > 0)) {
            $this->json['capabilities']['device']['queueFamiliesProperties'] = $this->queue_families;
        } else {
            $this->json['capabilities']['device']['queueFamiliesProperties'] = [];
        }

        $this->addManualMappings();
        DB::disconnect();
    }
}

// Profile generation
try {
    $profile = new VulkanProfile($reportid);
    $profile->generateJSON();

    $filename = $profile->profile_name;
    $filename = preg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $filename);
    $filename = preg_replace("([\.]{2,})", '', $filename);	
    $filename .= ".json";

    header("Content-Disposition: attachment; filename=".strtolower($filename));
    echo json_encode($profile->json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} catch (Exception $e) {
    echo json_encode(['error' => "Could not generate profile"]);
} finally {
    DB::disconnect();
}