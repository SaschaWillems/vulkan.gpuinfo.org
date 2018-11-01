<?php
	/* 		
		*
		* Vulkan hardware capability database server implementation
		*	
		* Copyright (C) 2016-2018 by Sascha Willems (www.saschawillems.de)
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
?>	
	<div>
		<ul class='nav nav-tabs'>
			<li class='active'><a data-toggle='tab' href='#memorytypes'>Memory types <span class='badge'><?php echo $memtypecount ?></span></a></li>
			<li><a data-toggle='tab' href='#memoryheaps'>Memory heaps <span class='badge'><?php echo $memheapcount ?></span></a></li>
		</ul>
	</div>
	
	<div class='tab-content'>
		<div id='memorytypes' class='tab-pane fade in active reportdiv'>
<?php	
	try {
		$stmnt = DB::$connection->prepare("SELECT * from devicememorytypes where reportid = :reportid");
		$stmnt->execute(array(":reportid" => $reportID));
		$index = 0;
		while($row = $stmnt->fetch(PDO::FETCH_ASSOC)) {
			echo "<table class='table table-striped table-bordered table-hover responsive' style='width:100%;'>";
			echo "<thead><tr>";
			echo "<tr><td colspan=2 class=tablehead>Memory type $index</td></tr>";		
			echo "</tr></thead><tbody>";				
			echo "<tr>";
			echo "<td class='key'>Heapindex</td>";
			echo "<td>".$row["heapindex"]."</td>";
			echo "</tr>";	
			// Flags
			echo "<tr>";
			echo "<td class='key'>Flags</td>";
			echo "<td>";
			$memoryFlags = getMemoryTypeFlags($row["propertyflags"]);
			if (sizeof($memoryFlags) > 0) {
				foreach ($memoryFlags as $flag) {
					echo $flag."<br>";
				}
			} else {
				echo "none";
			}
			echo "<tr>";				
			echo "</tbody></table>";								
			$index++;
		}
	} catch (Exception $e) {
		die('Error while fetching report features');
		DB::disconnect();
	}

	echo "</div>";	
	
?>	
	<div id='memoryheaps' class='tab-pane fade reportdiv'>
		<div class="alert alert-warning" role="alert">
			<b>Note:</b> Listing may contain memory heaps with host sizes!
		</div>	
<?php			
	try {
		$stmnt = DB::$connection->prepare("SELECT * from devicememoryheaps where reportid = :reportid");
		$stmnt->execute(array(":reportid" => $reportID));
		$index = 0;
		while($row = $stmnt->fetch(PDO::FETCH_ASSOC)) {
			echo "<table class='table table-striped table-bordered table-hover responsive' style='width:100%;'>";
			echo "<thead><tr>";
			echo "<tr><td colspan=2 class=tablehead>Memory heap $index</td></tr>";		
			echo "</tr></thead><tbody>";				
			echo "<tr>";
			echo "<td class='key'>Size</td>";
			echo "<td>".number_format($row["size"])." bytes</td>";
			echo "</tr>";	
			// Flags
			echo "<tr>";
			echo "<td class='key'>Flags</td>";
			echo "<td>";
			$flags = getMemoryHeapFlags($row["flags"]);
			if (sizeof($flags) > 0) {
				foreach ($flags as $flag) {
					echo $flag."<br>";
				}
			} else {
				echo "none";
			}
			echo "<tr>";				
			echo "</tbody></table>";										
			$index++;
		}
	} catch (Exception $e) {
		die('Error while fetching report features');
		DB::disconnect();
	}
?>
</div>
</div>