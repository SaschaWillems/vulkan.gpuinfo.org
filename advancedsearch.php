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

	include 'page_generator.php';
	include './functions.php';
  include './dbconfig.php';
    
  PageGenerator::header("Advanced Search");
 
?>

<center>
<div style="max-width:720px; margin: 0;">
<h2>Advanced search</h2>

<h3>Queue families</h3>
<form class="form-horizontal" style="margin-bottom: 100px; padding-top: 25px;" action="./listdevices.php">
<!-- <form class="form-horizontal" style="margin-bottom: 100px; padding-top: 25px;" action="./advancedsearch_result.php"> -->
  <div class="form-group">
    <div class="col-sm-3">
      <label for="queueflags" class="control-label">Supported flags</label>
    </div>
    <div class="col-sm-6">
      <!-- <input type="text" class="form-control" id="queueflags" placeholder="Author" name="queueflags"> -->
        <select class="form-control" id="queuefamilyflags" name="queuefamilyflags" placeholder="select flag">
            <option></option>
            <option value="1">GRAPHICS_BIT</option>
            <option value="2">COMPUTE_BIT</option>
            <option value="4">TRANSFER_BIT</option>
            <option value="8">SPARSE_BINDING_BIT</option>
            <option value="16">PROTECTED_BIT</option>
        </select>      
    </div>
    <div class="col-sm-3">
    <button type="submit" name="advancedsearch" value="1" class="btn btn-block btn-primary">Search</button>
    </div>
  </div>
</form>

<h3>Memory</h3>
<form class="form-horizontal" style="margin-bottom: 100px; padding-top: 25px;" action="./listdevices.php">
  <div class="form-group">
    <div class="col-sm-3">
      <label for="memorytypeflags" class="control-label">Memory type flags</label>
    </div>
    <div class="col-sm-6">
      <select class="form-control" id="memorytypeflags" name="memorytypeflags" placeholder="select flag">
          <option></option>
          <option value="1">DEVICE_LOCAL_BIT</option>
          <option value="2">HOST_VISIBLE_BIT</option>
          <option value="4">HOST_COHERENT_BIT</option>
          <option value="8">HOST_CACHED_BIT</option>
          <option value="16">LAZILY_ALLOCATED_BIT</option>     
      </select>      
    </div>
    <div class="col-sm-3">
    <button type="submit" name="advancedsearch" value="1" class="btn btn-block btn-primary">Search</button>
    </div>
  </div>
</form>

<?php
    // PageGenerator::footer();
?>
</div>
</center>

</body>
</html>