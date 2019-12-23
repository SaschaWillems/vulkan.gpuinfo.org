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

<?php

$search_groups = [];

$search_groups["memory"] = [
  "caption" => "Queue families",
  "subjects" => [
    [
      'subject' => 'Memory type flags', 
      'id' => 'memorytypeflags', 
      'type' => 'select', 
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
        0x001F => "ALL GRAPHICS"]],
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
        0x0080 => "QUAD"]]      
  ]
];


foreach ($search_groups as $group) {
  echo '<h3>'.$group['caption'].'</h3>';
  foreach ($group['subjects'] as $search) {
?>    
    <form class="form-horizontal" style="margin-bottom: 25px; padding-top: 25px;" action="./listdevices.php">
      <div class="form-group">
      <div class="col-sm-4" style="text-align:left;">
        <label for="<?= $search['id'] ?>" class="control-label"><?= $search['subject'] ?>:</label>
      </div>
      <div class="col-sm-6">
<?php
      switch ($search['type']) {
        case 'select':
          echo '<select class="form-control" id="'.$search['id'].'" name="'.$search['id'].'">';
          echo '<option></option>';
          foreach ($search['options'] as $value => $text) {
            echo '<option value="'.$value.'">'.$text.'</option>';
          }
          echo '</select>';
        break;
      }
?>
      </div>
      <div class="col-sm-2">
      <button type="submit" name="advancedsearch" value="1" class="btn btn-block btn-primary">Search</button>
      </div>
    </div>
   </form>
<?php
  }  
}
   // PageGenerator::footer();
?>
</div>
</center>

</body>
</html>