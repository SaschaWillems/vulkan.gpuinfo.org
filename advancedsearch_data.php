<?php
	/* 		
		*
		* Vulkan hardware capability database server implementation
		*	
		* Copyright (C) by Sascha Willems (www.saschawillems.de)
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

  $search_groups = [];

  $search_groups["memory_types"] = [
    "caption" => "Memory types",
    "subjects" => [
      [
        'subject' => 'Memory type flags', 
        'id' => 'memorytypeflags', 
        'type' => 'select_list',
        'options' => [
          0x0001 => "DEVICE_LOCAL_BIT",
          0x0002 => "HOST_VISIBLE_BIT",
          0x0004 => "HOST_COHERENT_BIT",
          0x0008 => "HOST_CACHED_BIT",
          0x0010 => "LAZILY_ALLOCATED_BIT"]]
    ]
  ];

  $search_groups["queue_families"] = [
    "caption" => "Queue families",
    "subjects" => [
      [
        'subject' => 'Supported flags', 
        'id' => 'queuefamilyflags', 
        'type' => 'select', 
        'options' => [
          0x0001 => "GRAPHICS_BIT",
          0x0002 => "COMPUTE_BIT",
          0x0004 => "TRANSFER_BIT",
          0x0008 => "SPARSE_BINDING_BIT",
          0x0010 => "PROTECTED_BIT"]]
    ]
  ];

  $search_groups["subgroup_operations"] = [
    "caption" => "Subgroup operations",
    "subjects" => [
      [
        'subject' => 'Subgroup size', 
        'id' => 'subgroup_size', 
        'type' => 'number'
      ],
      [
        'subject' => 'Supported stages', 
        'id' => 'subgroup_supportedstages', 
        'type' => 'select', 
        'options' => [
          0x0001 => "VERTEX",
          0x0002 => "TESSELLATION CONTROL",
          0x0004 => "TESSELLATION EVALUATION",
          0x0008 => "GEOMETRY",
          0x0010 => "FRAGMENT",
          0x0020 => "COMPUTE",
          0x001F => "ALL GRAPHICS"]
      ],
      [
        'subject' => 'Supported operations', 
        'id' => 'subgroup_supportedoperations', 
        'type' => 'select', 
        'options' => [
          0x0001 => "BASIC",
          0x0002 => "VOTE",
          0x0004 => "ARITHMETIC",
          0x0008 => "BALLOT",
          0x0010 => "SHUFFLE",
          0x0020 => "SHUFFLE (RELATIVE)",
          0x0040 => "CLUSTERED",
          0x0080 => "QUAD"]
      ],
      [
        'subject' => 'Quad operations in all stages', 
        'id' => 'subgroup_quadOperationsInAllStages', 
        'type' => 'select', 
        'options' => [
          1 => "true",
          0 => "false"]
      ]       
    ]
  ];
 
?>