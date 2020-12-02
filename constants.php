<?php
define('VULKAN_CORE_1_0_TEXT', 'Vulkan Core 1.0');
define('VULKAN_CORE_1_1_TEXT', 'Vulkan Core 1.1');
define('VULKAN_CORE_1_2_TEXT', 'Vulkan Core 1.2');

$queue_flag_bits = [
    0x0001 => "GRAPHICS_BIT",
    0x0002 => "COMPUTE_BIT",
    0x0004 => "TRANSFER_BIT",
    0x0008 => "SPARSE_BINDING_BIT",
    0x0010 => "PROTECTED_BIT",
];