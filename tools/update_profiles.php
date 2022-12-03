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

/** Download current Vulkan profiles from github and put them into the appropriate folder */

if (!file_exists('Khronos-Schemas')) {
    system('git clone https://github.com/KhronosGroup/Khronos-Schemas');
} else {
    system('cd Khronos-Schemas');
    system('git pull');
    system('cd ..');
}
$files = glob('Khronos-Schemas/vulkan/profiles*.json');
foreach ($files as $file) {
    $dst = str_replace('Khronos-Schemas/vulkan', '../profiles/schema', $file);
    copy($file, $dst);
    echo $dst.PHP_EOL;
}

