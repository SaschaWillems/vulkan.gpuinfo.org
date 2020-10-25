<?php
	/* 		
		*
		* Vulkan hardware capability database server implementation
		*	
		* Copyright (C) 2016-2020 by Sascha Willems (www.saschawillems.de)
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

    class ReportCompare {

        private $header_column_names = ['device', 'driverversion', 'apiversion', 'os'];
        
        public $reports_ids = [];

        function __construct($reportids)
        {
            foreach($reportids as $id) {
                if (is_int($id)) {
                    $this->reports_ids[] = $id;
                }
            }
        }

        private function reportIdsParam()
        {
            return implode(",", $this->reports_ids);
        }

        public function isHeaderColumn($column_name)
        {
            return in_array($column_name, $this->header_column_names);
        }

        public function getDiffIcon()
        {
            return "<span class='glyphicon glyphicon-transfer' title='This value differs across reports' style='padding-right: 5px;'></span>";
        }

        public static function insertTableHeader($caption, $deviceinfo_data, $count, $grouping_column = false) {
            echo "<thead><tr><th>$caption</th>";
            if ($grouping_column) {
                echo "<td class='caption'></td>";
            }
            for ($i = 0; $i < $count; $i++) {
                echo "<td class='caption'>".$deviceinfo_data[$i][0]."</td>";
            }
            echo "</th></thead><tbody>";
        }
    
        public static function insertDeviceColumns($deviceinfo_captions, $deviceinfo_data, $count, $grouping_column = null)
        {
            for ($i = 1; $i < sizeof($deviceinfo_data[0]); ++$i) 
            {
                echo "<tr>";
                echo "<td class='subkey'>".$deviceinfo_captions[$i]."</td>";
                if ($grouping_column) {
                    echo "<td>$grouping_column</td>";
                }
                for ($j = 0, $arrsize = $count; $j < $arrsize; ++$j) 				
                {
                    echo "<td class='deviceinfo'>".$deviceinfo_data[$j][$i]."</td>";
                }
                echo "</tr>";
            }
        }

        public function fetchFeatures() 
        {
            try {                
                $sql = "SELECT features.* from reports r left join deviceproperties p on (p.reportid = r.id) left join devicefeatures features on (features.reportid = r.id) where r.id in (" . $this->reportIdsParam() . ")";
                $stmnt = DB::$connection->prepare($sql);
                $stmnt->execute();
                $result = $stmnt->fetchAll(PDO::FETCH_ASSOC);
                return $result;
            } catch (Throwable $e) {
                return null;
            }            
        }
        
    }
?>