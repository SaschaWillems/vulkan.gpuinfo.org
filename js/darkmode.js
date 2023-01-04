/** 		
 *
 * Vulkan hardware capability database server implementation
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

function toggleDarkMode() {
  if (localStorage.mode != 'dark') {
    document.documentElement.setAttribute('data-theme', 'dark');
    localStorage.mode = 'dark';    
  } else {
    document.documentElement.removeAttribute('data-theme', 'light')
    localStorage.mode = 'light';
  }
}

function loadDarkMode() {
  const mode = localStorage.getItem('mode') ? localStorage.getItem('mode') : null;
  if (mode) {
    document.documentElement.setAttribute('data-theme', mode);
  }
}
