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

  include 'advancedsearch_data.php';
	include 'page_generator.php';
	include './functions.php';
  include './dbconfig.php';
    
  PageGenerator::header("Advanced Search");

  function generateFormatSelection() {
    DB::connect();
    $stmnt = DB::$connection->prepare("SELECT value as id, name from VkFormat where value != 0");
    $stmnt->execute();
    echo '<select class="form-control" id="format" name="format">';
    echo '<option></option>';
    foreach ($stmnt as $row) {
      echo '<option value="'.$row[0].'">'.$row[1].'</option>';
    }
    echo '</select>';
    DB::disconnect();    
  }
 
  function generateSearchGroup(string $name) {
    global $search_groups;
    $group = $search_groups[$name];
    assert($group);
    echo '<h3>'.$group['caption'].'</h3>';
    foreach ($group['subjects'] as $search) {
  ?>    
      <form class="form-horizontal" style="margin-bottom: 25px; padding-top: 25px;" method="get" action="./listdevices.php">
        <div class="form-group">

        <?php
          if (strpos($search['id'], 'format_') === 0) {
            echo '<div class="col-sm-4" style="text-align:left;">';
            echo '<label style="text-align:left;" for="'.$search['id'].'" class="control-label">'.$search['subject'].':</label>';
            echo '</div>';
            echo '<div class="col-sm-6">';
            generateFormatSelection();
            echo '</div>';
          }
        ?>

        <div class="col-sm-4" style="text-align:left;">
          <label style="text-align:left;" for="<?= $search['id'] ?>" class="control-label"><?= $search['subject'] ?>:</label>
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
          case 'select_list':
            echo '<select multiple class="form-control" id="'.$search['id'].'" name="'.$search['id'].'[]" size="'.count($search["options"]).'">';
            foreach ($search['options'] as $value => $text) {
              echo '<option value="'.$value.'">'.$text.'</option>';
            }
            echo '</select>';
            break;
          case 'number':
            echo '<input class="form-control" id="'.$search['id'].'" name="'.$search['id'].'" type="number">';
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

?>

<div class='header'>
	<h4>Advanced search</h4>
</div>

<div>
  <ul class='nav nav-tabs'>
    <li class='active'><a data-toggle='tab' href='#device'>Device</a></li>
    <li><a data-toggle='tab' href='#formats'>Formats</a></a></li>
    <li><a data-toggle='tab' href='#queuefamilies'>Queue families</a></li>
    <li><a data-toggle='tab' href='#memory'>Memory</a></a></li>
    <li><a data-toggle='tab' href='#surface'>Surface</a></a></li>
  </ul>
</div>

<div class='tablediv tab-content' style="max-width:960px; margin: auto;">

<div id='device' class='tab-pane fade in active'>
  <?php 
    generateSearchGroup("subgroup_operations"); 
  ?>
</div>

<div id='formats' class='tab-pane fade'>
  <?php 
    generateSearchGroup("formats");
  ?>
</div>

<div id='queuefamilies' class='tab-pane fade'>
  <?php 
    generateSearchGroup("queue_families"); 
  ?>
</div>

<div id='memory' class='tab-pane fade'>
  <?php 
    generateSearchGroup("memory_types"); 
  ?>
</div>

<div id='surface' class='tab-pane fade'>
  <?php 
  ?>
</div>

</div>
<?php PageGenerator::footer(); ?>

</body>
</html>