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

  include 'vulkanenums.php';
	include 'page_generator.php';
	include './functions.php';
  include './dbconfig.php';

  PageGenerator::header("Advanced Search");

  function getEnumList($name) {
    switch ($name) {
      case 'formatFeatureFlags':
        return VulkanEnums::$formatFeatureFlags;
      case 'imageUsageFlags':
        return VulkanEnums::$imageUsageFlags;
      case 'memoryTypeFlags':
        return VulkanEnums::$memoryTypeFlags;
      case 'queueFamilyFlags':
        return VulkanEnums::$queueFamilyFlags;
      case 'subgroupSupportedOperationFlags':
        return VulkanEnums::$subgroupSupportedOperationFlags;
      case 'subgroupSupportedStageFlags':
        return VulkanEnums::$subgroupSupportedStageFlags;
      case 'surfaceTransformFlags':
        return VulkanEnums::$surfaceTransformFlags;
      case 'surfaceCompositeAlphaFlags':
        return VulkanEnums::$surfaceCompositeAlphaFlags;
      default:
        return [];
    }
  }

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

  // Create the visual inputs for the given search group and it's search subjects
  function generateGroup($group, $active) {
    $class = $active ? "in active" : "";
    echo "<div id='".$group['id']."' class='tab-pane fade $class'>";
    echo "  <div class='panel-group'>";

    // One form per search subject
    foreach ($group['subjects'] as $subject) {
      ?>
      <form class="form-horizontal" style="padding-top: 25px;" method="get" action="./listdevices.php">
        <div class="form-group" style="margin: 0;">

        <div class="panel panel-default">
          <div class="panel-heading"><b><?= $subject['caption'] ?></b></div>
          <div class="panel-body">
      <?php
          // Insert input type and options based on selection properties
          $selection = $subject['selection'];
          switch ($selection['type']) {
            // Dropdown with fixed option list from selection properties
            case 'select':
              echo '<select class="form-control" id="'.$subject['id'].'" name="'.$subject['id'].'">';
              echo '<option></option>';
              foreach ($selection['options'] as $option) {
                echo '<option value="'.$option["value"].'">'.$option["caption"].'</option>';
              }
              echo '</select>';
              break;
            // Dropdown with option list from database query
            case 'select_database':
              DB::connect();
              try {
                $stmnt = DB::$connection->prepare($selection['options']);
                $stmnt->execute();
                echo '<select class="form-control" id="'.$subject['id'].'" name="'.$subject['id'].'">';
                echo '<option></option>';
                foreach ($stmnt as $row) {
                  echo '<option value="'.$row[0].'">'.$row[0].'</option>';
                }
                echo '</select>';
              } catch (Throwable $e) {
                echo "<span style='color:red;'>Error: Could not retrieve values from database!</span>";
              }
              DB::disconnect();
              break;
            // Multi-select list with fixed option list from selection properties
            case 'select_list':
              echo '<select multiple class="form-control" id="'.$subject['id'].'" name="'.$subject['id'].'[]" size="'.count($selection['options']).'">';
              foreach ($selection['options'] as $option) {
                echo '<option value="'.$option["value"].'">'.$option["caption"].'</option>';
              }
              echo '</select>';
              break;
            // Multi-select list with option list from Vulkan Enums
            case 'select_list_enum':
              $options = getEnumList($selection['options']);
              echo '<select multiple class="form-control" id="'.$subject['id'].'" name="'.$subject['id'].'[]" size="'.count($options).'">';
              foreach ($options as $value => $caption) {
                echo '<option value="'.$value.'">'.$caption.'</option>';
              }
              echo '</select>';
              break;
            // Manual number input
            case 'number':
              echo '<input class="form-control" id="'.$subject['id'].'" name="'.$subject['id'].'" type="number">';
              break;
          }
      ?>
          </div>
          <div class="panel-footer text-right">
            <button type="submit" name="advancedsearch" value="1" class="btn btn-primary">Search</button>
          </div>
        </div>

        </div>
      </form>
      <?php
    }

    echo "  </div>";
    echo "</div>";
  }

  function generateSearchGroup(string $name) {
    global $search_groups;
    $group = $search_groups[$name];
    assert($group);
    echo '<h3>'.$group['caption'].'</h3>';
    foreach ($group['subjects'] as $search) {
      $format_selection = strpos($search['id'], 'format_') === 0;
  ?>
      <form class="form-horizontal" style="padding-top: 25px;" method="get" action="./listdevices.php">
        <div class="form-group" style="margin: 0;">

        <div class="panel panel-default">
          <div class="panel-heading"><b><?= $search['subject'] ?></b></div>
          <div class="panel-body">
            <?php
              if ($format_selection) {
                echo "<div style='margin-bottom: 5px;'>Select format:</div>";
                generateFormatSelection();
                echo "<div style='margin-top:15px; margin-bottom: 5px;'>Select feature flags:</div>";
              }
              switch ($search['type']) {
                case 'select':
                  echo '<select class="form-control" id="'.$search['id'].'" name="'.$search['id'].'">';
                  echo '<option></option>';
                  foreach ($search['options'] as $value => $text) {
                    echo '<option value="'.$value.'">'.$text.'</option>';
                  }
                  echo '</select>';
                  break;
                case 'select_database':
                  DB::connect();
                  try {
                    $stmnt = DB::$connection->prepare($search['options_statement']);
                    $stmnt->execute();
                    echo '<select class="form-control" id="'.$search['id'].'" name="'.$search['id'].'">';
                    echo '<option></option>';
                    foreach ($stmnt as $row) {
                      echo '<option value="'.$row[0].'">'.$row[0].'</option>';
                    }
                    echo '</select>';
                  } catch (Throwable $e) {
                    echo "<span style='color:red;'>Error: Could not retrieve values from database!</span>";
                  }
                  DB::disconnect();
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
          <div class="panel-footer text-right">
            <button type="submit" name="advancedsearch" value="1" class="btn btn-primary">Search</button>
          </div>
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

<?php
  $json_data = file_get_contents('./advancedsearch.json');
  $json = json_decode($json_data, true);
  // var_dump($json);
  // @todo: From Request parameter (optional)
  $active_tab = 'Queue Families';
?>

<!-- Navigation -->
<div>
  <ul class='nav nav-tabs'>
  <?php
    foreach($json['groups'] as $group) {
      $class = ($group['Node'] == $active_tab) ? "class='active'" : "";
      echo "<li ".$class."><a data-toggle='tab' href='#".$group['id']."'>".$group['Node']."</a></li>";
    }
  ?>
  </ul>
</div>

<!-- Tabs and filters -->
<div class='tablediv tab-content' style="max-width:960px; margin: auto;">
<?php
    foreach($json['groups'] as $group) {
      generateGroup($group, $group['Node'] == $active_tab);
    }
  ?>
</div>

<?php PageGenerator::footer(); ?>

</body>
</html>