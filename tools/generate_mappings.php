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

/** Generates a JSON mapping file matching extensions and their structs from the Vulkan XML registry */

error_reporting(E_ERROR | E_PARSE);

class TypeContainer
{

    public $property_types;
    public $feature_types;
    public $aliases;

    private function getActualType($type, $xml)
    {
        if ($type['alias']) {
            foreach ($xml->types->type as $alias_type) {
                if ($alias_type['name'] && strcasecmp($alias_type['name'], $type['alias']) === 0) {
                    $this->aliases[(string)$type['name']] = $alias_type;
                    return $alias_type;
                }
            }
        }
        return $type;
    }

    function __construct($xml)
    {
        // We're only interested in types extending VkPhysicalDeviceProperties2 or VkPhysicalDeviceFeatures2
        foreach ($xml->types->type as $type) {
            $actual_type = $this->getActualType($type, $xml);
            if (($actual_type['structextends']) && (strcasecmp($actual_type['structextends'], 'VkPhysicalDeviceProperties2') === 0)) {
                $this->property_types[] = $actual_type;
            }
            if (($actual_type['structextends']) && (stripos($actual_type['structextends'], 'VkPhysicalDeviceFeatures2') !== false)) {
                $this->feature_types[] = $actual_type;
            }
        }
    }

    public function featureTypeExists($type_name) 
    {
        foreach ($this->feature_types as $type) {
            if (strcasecmp((string)$type['name'], $type_name) == 0) {
                return true;
            }
        }
        return false;
    }

    public function propertyTypeExists($type_name) 
    {
        foreach ($this->property_types as $type) {
            if (strcasecmp((string)$type['name'], $type_name) == 0) {
                return true;
            }
        }
        return false;
    }

    /** Checks if the given type is aliased, and returns the type name to look for */
    private function getActualTypeName($type_name)
    {
        $actual_name = $type_name;
        if ($this->aliases[(string)$type_name]) {
            $actual_name = $this->aliases[(string)$type_name]['name'];
        }
        return $actual_name;
    }

    public function getProperties2Type($name)
    {
        $actual_name = $this->getActualTypeName($name);
        foreach ($this->property_types as $property_type) {
            if (strcasecmp($property_type['name'], $actual_name) === 0) {
                return $property_type;
            }
        }
        return null;
    }

    public function getFeatures2Type($name)
    {
        $actual_name = $this->getActualTypeName($name);
        foreach ($this->feature_types as $feature_type) {
            if (strcasecmp($feature_type['name'], $actual_name) === 0) {
                return $feature_type;
            }
        }
        return null;
    }
}

$xml = simplexml_load_file('https://raw.githubusercontent.com/KhronosGroup/Vulkan-Docs/main/xml/vk.xml') or exit("Could not load vk.xml from the Khronos repository");

$mappings = [];

$header_version_node = $xml->xpath("./types/type/name[.='VK_HEADER_VERSION']/..");
$vk_header_version = filter_var($header_version_node[0], FILTER_SANITIZE_NUMBER_INT);
$mappings['headerversion'] = $vk_header_version;

// Extensions
$type_container = new TypeContainer($xml);
foreach ($xml->extensions->extension as $ext_node) {
    $features2_node = null;
    $properties2_node = null;
    $extension = [];
    $extension['name'] = (string)$ext_node['name'];
    $extension['promotedTo'] = null;  
    $extension['structs']['ext']['physicalDeviceFeatures'] = [];
    $extension['structs']['ext']['physicalDeviceProperties'] = [];
    $extension['structs']['core']['physicalDeviceFeatures'] = [];
    $extension['structs']['core']['physicalDeviceProperties'] = [];
    $extension['types'] = [];  
    // echo (string)$ext_node['name'].PHP_EOL;
    foreach ($ext_node->require as $require) {
        foreach ($require as $requirement) {
            if (preg_match('/^VkPhysicalDevice.*Features.*/m', (string)$requirement['name']) > 0) {                
                $extension['structs']['ext']['physicalDeviceFeatures'][] = (string)$requirement['name'];
            }
            if (preg_match('/^VkPhysicalDevice.*Properties.*/m', (string)$requirement['name']) > 0) {                
                $extension['structs']['ext']['physicalDeviceProperties'][] = (string)$requirement['name'];
            }
        }
    }
    if ($ext_node['promotedto']) {
        // $ext->promotedto = (string)$ext_node['promotedto'];
        $extension['promotedTo'] = (string)$ext_node['promotedto'];
        // Promoted feature/property names
        foreach($extension['structs']['ext']['physicalDeviceFeatures'] as $property_feature) {
            $match = null;
            preg_match('/^VkPhysicalDevice.*Features/m', $property_feature, $match);
            $name = $match[0];
            if ($type_container->featureTypeExists($name)) {
                $extension['structs']['core']['physicalDeviceFeatures'][] = $name;
            }
        }
        foreach($extension['structs']['ext']['physicalDeviceProperties'] as $property_prop) {
            $match = null;
            preg_match('/^VkPhysicalDevice.*Properties/m', $property_prop, $match);
            $name = $match[0];
            if ($type_container->propertyTypeExists($name)) {
                $extension['structs']['core']['physicalDeviceProperties'][] = $name;
            }
        }
    }
    // To convert values at profile generation we store a list of property structure members and their types
    // This can be used at runtime to convert from the database string representation to proper basic or Vk types
    $property_types = [];
    foreach($extension['structs']['ext']['physicalDeviceProperties'] as $property_struct_name) {
        $property_type = $type_container->getProperties2Type($property_struct_name);
        if ($property_type) {
            foreach ($property_type as $member) {
                if ($member->type) {
                    $property_types[(string)$member->name] = (string)$member->type;
                }
            }
        } else {
            echo "[WARN] Could not get types for $property_struct_name".PHP_EOL;
        }
    }
    $extension['types'] = $property_types;

    $mappings[$extension['name']] = $extension;
}

// Gather enums for translation at runtime
$enums = [];
foreach ($xml->enums as $enum_node) {
    $enum = null;
    if (strcmp($enum_node['name'], 'API Constants') == 0) {
        continue;
    }   
    echo($enum_node['name']).PHP_EOL;
    $enum['name'] = (string)$enum_node['name'];
    $enum['type'] = (string)$enum_node['type'];    
    foreach ($enum_node->enum as $enum_childnode) {
        if (isset($enum_childnode['value'])) {
            $enum['values'][] = (int)$enum_childnode['value'];
        }
        if (isset($enum_childnode['bitpos'])) {
            $enum['bitpos'][] = (int)$enum_childnode['bitpos'];
        }
        $enum['names'][] = (string)$enum_childnode['name'];
    }
    if ($enum) {
        $enums[$enum['name']] = $enum;
    }
}
$mappings['enums'] = $enums;

file_put_contents('./../includes/mappings.json', json_encode($mappings, JSON_PRETTY_PRINT));