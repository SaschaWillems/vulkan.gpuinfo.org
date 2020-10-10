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
  
  require_once('vulkanenums.php');

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
        'type' => 'select_list', 
        'options' => [
          0x0001 => "GRAPHICS_BIT",
          0x0002 => "COMPUTE_BIT",
          0x0004 => "TRANSFER_BIT",
          0x0008 => "SPARSE_BINDING_BIT",
          0x0010 => "PROTECTED_BIT"]]
    ]
  ];

  $search_groups["formats"] = [
    "caption" => "Format feature flags ",
    "subjects" => [
      [
        'subject' => ' Linear tiling', 
        'id' => 'format_features_linear', 
        'type' => 'select_list', 
        'options' => [
          0x0001 => "SAMPLED_IMAGE_BIT",
          0x0002 => "STORAGE_IMAGE_BIT",
          0x0004 => "STORAGE_IMAGE_ATOMIC_BIT",
          0x0008 => "UNIFORM_TEXEL_BUFFER_BIT",
          0x0010 => "STORAGE_TEXEL_BUFFER_BIT",
          0x0020 => "STORAGE_TEXEL_BUFFER_ATOMIC_BIT",
          0x0040 => "VERTEX_BUFFER_BIT",
          0x0080 => "COLOR_ATTACHMENT_BIT",
          0x0100 => "COLOR_ATTACHMENT_BLEND_BIT",
          0x0200 => "DEPTH_STENCIL_ATTACHMENT_BIT",
          0x0400 => "BLIT_SRC_BIT",
          0x0800 => "BLIT_DST_BIT",
          0x1000 => "SAMPLED_IMAGE_FILTER_LINEAR_BIT",
          0x4000 => "TRANSFER_SRC_BIT",
          0x8000 => "TRANSFER_DST_BIT"       
        ]
      ],
      [
        'subject' => ' Optimal tiling', 
        'id' => 'format_features_optimal', 
        'type' => 'select_list', 
        'options' => [
          0x0001 => "SAMPLED_IMAGE_BIT",
          0x0002 => "STORAGE_IMAGE_BIT",
          0x0004 => "STORAGE_IMAGE_ATOMIC_BIT",
          0x0008 => "UNIFORM_TEXEL_BUFFER_BIT",
          0x0010 => "STORAGE_TEXEL_BUFFER_BIT",
          0x0020 => "STORAGE_TEXEL_BUFFER_ATOMIC_BIT",
          0x0040 => "VERTEX_BUFFER_BIT",
          0x0080 => "COLOR_ATTACHMENT_BIT",
          0x0100 => "COLOR_ATTACHMENT_BLEND_BIT",
          0x0200 => "DEPTH_STENCIL_ATTACHMENT_BIT",
          0x0400 => "BLIT_SRC_BIT",
          0x0800 => "BLIT_DST_BIT",
          0x1000 => "SAMPLED_IMAGE_FILTER_LINEAR_BIT",
          0x4000 => "TRANSFER_SRC_BIT",
          0x8000 => "TRANSFER_DST_BIT"
        ]
      ],
      [
        'subject' => ' Buffer', 
        'id' => 'format_features_buffer', 
        'type' => 'select_list', 
        'options' => [
          0x0001 => "SAMPLED_IMAGE_BIT",
          0x0002 => "STORAGE_IMAGE_BIT",
          0x0004 => "STORAGE_IMAGE_ATOMIC_BIT",
          0x0008 => "UNIFORM_TEXEL_BUFFER_BIT",
          0x0010 => "STORAGE_TEXEL_BUFFER_BIT",
          0x0020 => "STORAGE_TEXEL_BUFFER_ATOMIC_BIT",
          0x0040 => "VERTEX_BUFFER_BIT",
          0x0080 => "COLOR_ATTACHMENT_BIT",
          0x0100 => "COLOR_ATTACHMENT_BLEND_BIT",
          0x0200 => "DEPTH_STENCIL_ATTACHMENT_BIT",
          0x0400 => "BLIT_SRC_BIT",
          0x0800 => "BLIT_DST_BIT",
          0x1000 => "SAMPLED_IMAGE_FILTER_LINEAR_BIT",
          0x4000 => "TRANSFER_SRC_BIT",
          0x8000 => "TRANSFER_DST_BIT"
        ]
      ]      
    ]
  ];  

  $search_groups["subgroup_operations"] = [
    "caption" => "Subgroup operations",
    "subjects" => [
      [
        'subject' => 'Subgroup size', 
        'id' => 'subgroup_size', 
        'type' => 'select_database',
        'options_statement' => 'select `subgroupProperties.subgroupSize` as `values` from deviceproperties where `subgroupProperties.subgroupSize` > 0 group by `values` order by `values` asc'
      ],
      [
        'subject' => 'Supported stages', 
        'id' => 'subgroup_supportedstages', 
        'type' => 'select_list', 
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
        'type' => 'select_list', 
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

  $search_groups["surface"] = [
    "caption" => "Surface",
    "subjects" => [
      [
        'subject' => 'Supported usage flags', 
        'id' => 'surface_usage_flags', 
        'type' => 'select_list', 
        'options' => VulkanEnums::$imageUsageFlags
      ],      
      [
        'subject' => 'Supported transforms', 
        'id' => 'surface_transforms', 
        'type' => 'select_list', 
        'options' => [
          0x0001 => "IDENTITY_BIT_KHR",
          0x0002 => "ROTATE_90_BIT_KHR",
          0x0004 => "ROTATE_180_BIT_KHR",
          0x0008 => "ROTATE_270_BIT_KHR",
          0x0010 => "HORIZONTAL_MIRROR_BIT_KHR",
          0x0020 => "HORIZONTAL_MIRROR_ROTATE_90_BIT_KHR",
          0x0040 => "HORIZONTAL_MIRROR_ROTATE_180_BIT_KHR",
          0x0080 => "HORIZONTAL_MIRROR_ROTATE_270_BIT_KHR",
          0x0100 => "INHERIT_BIT_KHR"      
        ]
      ],   
      [
        'subject' => 'Supported composite alpha', 
        'id' => 'surface_composite_alpha', 
        'type' => 'select_list', 
        'options' => [
          0x0001 => "OPAQUE_BIT_KHR",
          0x0002 => "PRE_MULTIPLIED_BIT_KHR",
          0x0004 => "POST_MULTIPLIED_BIT_KHR",
          0x0008 => "INHERIT_BIT_KHR"   
        ]
      ], 
    ]
  ];  
 
?>