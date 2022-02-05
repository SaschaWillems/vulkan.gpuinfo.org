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

/** Generate mappings used by the database from the Vulkan XML registry */

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

    /**
     * Checks if the given type is aliased, and returns the type name to look for
     */
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

    public static function getsType($node)
    {
        foreach ($node->member as $member) {
            if ($member['values'] && (stripos((string)$member['values'], 'VK_STRUCTURE_TYPE_') !== false)) {
                return $node->member['values'];
            }
        }
    }
}

class Extension
{
    public $name;
    public $stype;
    public $group;
    public $features2 = null;
    public $properties2 = null;
    public $promotedto = null;
}

class ExtensionContainer
{
    public $extensions = [];

    function __construct($xml, $typecontainer)
    {
        foreach ($xml->extensions->extension as $ext_node) {
            $features2_node = null;
            $properties2_node = null;
            // We're interested in extensions with property or feature types                
            foreach ($ext_node->require as $require) {
                foreach ($require as $requirement) {
                    if (strcasecmp($requirement->getName, 'type')) {
                        $ft2 = $typecontainer->getFeatures2Type((string)$requirement['name']);
                        if (!$features2_node && $ft2) {
                            $features2_node = $ft2;
                        }
                        $prop2 = $typecontainer->getProperties2Type((string)$requirement['name']);
                        if (!$properties2_node && $prop2) {
                            $properties2_node = $prop2;
                        }
                    }
                }
            }
            $ext = new Extension();
            $ext->name = (string)$ext_node['name'];
            $ext->group = substr($ext->name, 3, strpos($ext->name, '_', 3) - 3);
            $ext->features2 = $features2_node;
            $ext->properties2 = $properties2_node;
            // Has the extension been promoted into core?
            if ($ext_node['promotedto']) {
                $ext->promotedto = (string)$ext_node['promotedto'];
            }
            $this->extensions[] = $ext;
        }
    }
}

$xml = simplexml_load_file("./vk.xml") or exit("Could not read vk.xml");
$header_version_node = $xml->xpath("./types/type/name[.='VK_HEADER_VERSION']/..");
$vk_header_version = filter_var($header_version_node[0], FILTER_SANITIZE_NUMBER_INT);

$template = 
'<?php
    /** This file is auto generated */
    class Mappings {
        public static $extensions = [
#place_holder#
        ];
    }
';

$type_container = new TypeContainer($xml);
$extension_container = new ExtensionContainer($xml, $type_container);
ob_start();
foreach ($extension_container->extensions as $extension) {
    $struct_type_pyhsical_device_features = $extension->features2['name'] ? ("'".$extension->features2['name']."'") : 'null';
    $struct_type_physical_device_properties = $extension->properties2['name'] ? ("'".$extension->properties2['name']."'") : 'null';
    $promoted_to = $extension->promotedto ? ("'".$extension->promotedto."'") : 'null';
    echo "  '".$extension->name."' => [".PHP_EOL;
    echo "      'struct_type_physical_device_features' => ".$struct_type_pyhsical_device_features.",".PHP_EOL;
    echo "      'struct_type_physical_device_properties' => ".$struct_type_physical_device_properties.",".PHP_EOL;
    echo "      'promoted_to' => ".$promoted_to.",".PHP_EOL;
    echo "  ],".PHP_EOL;
}
$output = ob_get_contents();
ob_end_clean();
$template = str_replace('#place_holder#', $output, $template);
file_put_contents("./mappings.php", $template);
