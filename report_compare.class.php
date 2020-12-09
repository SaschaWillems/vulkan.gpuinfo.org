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

    class ReportCompareFlags {
        public $has_surface_caps = false;
        public $has_platform_details = false;
        public $has_extended_features = false;
        public $has_extended_properties = false;
        public $has_vulkan_1_1_features = false;
        public $has_vulkan_1_1_properties = false;
        public $has_vulkan_1_2_features = false;
        public $has_vulkan_1_2_properties = false;
    }

    class ReportCompareDeviceInfo {
        public $name;
        public $driver_version;
        public $api_version;
        public $platform;
        public $ostype;
        public $reportid;
    }

    class ReportCompareData {
        public $captions;
        public $data;
        public $count;
    }

    class ReportCompare {

        private $header_column_names = ['device', 'driverversion', 'apiversion', 'os'];
        
        public $report_ids = [];
        public $report_count = 0;
        public ReportCompareFlags $flags;        
        public $device_infos = [];

        function __construct($reportids)
        {
            foreach($reportids as $id) {
                $this->report_ids[] = intval($id);
            }
            sort($this->report_ids);
            $this->report_count = count($reportids);
            $this->flags = new ReportCompareFlags;
        }

        private function reportIdsParam()
        {
            assert(count($this->report_ids) > 0);
            return implode(",", $this->report_ids);
        }

        public function fetchData() 
        {
            // DB::connect();
            // Flags for optional data
            $this->flags->has_surface_caps = DB::getCount("SELECT count(*) from devicesurfacecapabilities where reportid in (" . $this->reportIdsParam() . ")") > 0;
            $this->flags->has_platform_details = DB::getCount("SELECT count(*) from deviceplatformdetails where reportid in (" . $this->reportIdsParam() . ")", []) > 0;
            $this->flags->has_extended_features = DB::getCount("SELECT count(*) from devicefeatures2 where reportid in (" . $this->reportIdsParam() . ")", []) > 0;
            $this->flags->has_extended_properties = DB::getCount("SELECT count(*) from deviceproperties2 where reportid in (" . $this->reportIdsParam() . ")", []) > 0;
            $this->flags->has_vulkan_1_1_features = DB::getCount("SELECT count(*) from devicefeatures11 where reportid in (" . $this->reportIdsParam() . ")", []) > 0;
            $this->flags->has_vulkan_1_1_properties = DB::getCount("SELECT count(*) from deviceproperties11 where reportid in (" . $this->reportIdsParam() . ")", []) > 0;
            $this->flags->has_vulkan_1_2_features = DB::getCount("SELECT count(*) from devicefeatures12 where reportid in (" . $this->reportIdsParam() . ")", []) > 0;
            $this->flags->has_vulkan_1_2_properties = DB::getCount("SELECT count(*) from deviceproperties12 where reportid in (" . $this->reportIdsParam() . ")", []) > 0;
            // DB::disconnect();
            // Fetch descriptions for devices to be compared
            try {
                $stmnt = DB::$connection->prepare(
                "SELECT 
                    concat(VendorId(p.vendorid), ' ', p.devicename),
                    p.driverversion,
                    p.apiversion,
                    concat(r.osname, ' ', r.osversion, ' (',  r.osarchitecture, ')'),
                    r.ostype,
                    r.id
                from reports r left join deviceproperties p on (p.reportid = r.id) where r.id in (" . $this->reportIdsParam() . ")");
                $stmnt->execute();
            } catch (PDOException $e) {
                die("Could not fetch report data!");
            }
            foreach($stmnt->fetchAll(PDO::FETCH_NUM) as $device) {
                $device_info = new ReportCompareDeviceInfo;
                $device_info->name = $device[0];
                $device_info->driver_version = $device[1];
                $device_info->api_version = $device[2];
                $device_info->platform = $device[3];                
                $device_info->ostype = $device[4];
                $device_info->reportid = $device[5];
                $this->device_infos[] = $device_info;
            }
        }        

        public function isHeaderColumn($column_name)
        {
            return in_array($column_name, $this->header_column_names);
        }

        public function getDiffIcon()
        {
            return "<span class='glyphicon glyphicon-transfer' title='This value differs across reports' style='padding-right: 5px;'></span>";
        }

        /**
         * Insert table header with device names into the current table
         */
        public function insertTableHeader($caption, $grouping_column = false) {
            echo "<thead><tr><th>$caption</th>";
            if ($grouping_column) {
                echo "<th></th>";
            }
            foreach($this->device_infos as $device_info) {
                echo "<th>$device_info->name</th>";
            }            
            echo "</th></thead><tbody>";
        }
    
        /**
         * Insert device identification rows and columns into the current table
         */
        public function insertDeviceInformation($grouping_column = false)
        {            
            echo "<tr><td class='subkey'>Driver version</td>";
            if ($grouping_column) { echo "<td>$grouping_column</td>"; }
            foreach($this->device_infos as $device_info) {
                echo "<td>$device_info->driver_version</td>";
            }
            echo "</tr>";
            echo "<tr><td class='subkey'>API version</td>";
            if ($grouping_column) { echo "<td>$grouping_column</td>"; }
            foreach($this->device_infos as $device_info) {
                echo "<td>$device_info->api_version</td>";
            }
            echo "</tr>";
            echo "<tr><td class='subkey'>Platform</td>";
            if ($grouping_column) { echo "<td>$grouping_column</td>"; }
            foreach($this->device_infos as $device_info) {
                echo "<td>$device_info->platform</td>";
            }
            echo "</tr>";
        }

        public function fetchFeatures()
        {
            try {
                $sql = "SELECT features.* from reports r left join deviceproperties p on (p.reportid = r.id) left join devicefeatures features on (features.reportid = r.id) where r.id in (" . $this->reportIdsParam() . ") order by r.id asc";
                $stmnt = DB::$connection->prepare($sql);
                $stmnt->execute();
                $rows = $stmnt->fetchAll(PDO::FETCH_ASSOC);
                $result = new ReportCompareData;
                foreach($rows as $index => $row) {
                    $reportdata = [];				
                    foreach ($row as $key => $values) {
                        if ($key == "reportid") { continue; }
                        $reportdata[] = $values;	  
                        if ($index == 0) {
                            $result->captions[] = $key;
                        }
                    } 	
                    $result->data[] = $reportdata;
                }                
                $result->count = count($result->captions);
                return $result;
            } catch (Throwable $e) {
                return [];
            }
        }

        public function fetchLimits()
        {
            try {          
                $sql = "SELECT limits.* from reports r left join deviceproperties p on (p.reportid = r.id) left join devicelimits limits on (limits.reportid = r.id) where r.id in (" . $this->reportIdsParam() . ") order by r.id asc";
                $stmnt = DB::$connection->prepare($sql);
                $stmnt->execute();
                $rows = $stmnt->fetchAll(PDO::FETCH_ASSOC);
                $result = new ReportCompareData;
                foreach($rows as $index => $row) {
                    $reportdata = [];				
                    foreach ($row as $key => $values) {
                        if ($key == "reportid") { continue; }
                        $reportdata[] = $values;	  
                        if ($index == 0) {
                            $result->captions[] = $key;
                        }
                    } 	
                    $result->data[] = $reportdata;
                }
                $result->count = count($result->captions);
                return $result;
            } catch (Throwable $e) {
                return [];
            }
        }

        public function fetchExtensions()
        {
            try {       
                $result = new ReportCompareData;
	            // Gather all extensions supported by at least one of the reports
                $stmnt = DB::$connection->prepare("SELECT distinct Name from deviceextensions 
                    left join extensions on extensions.ID = deviceextensions.extensionid 
                    where deviceextensions.ReportID in (". $this->reportIdsParam().")");
                $stmnt->execute();
                $rows = $stmnt->fetchAll(PDO::FETCH_NUM);
                foreach($rows as $row) {
                    $result->captions[] = $row[0];
                }

                // Get extensions for each selected report              
                foreach ($this->report_ids as $report_id) {
                    try {
                        $stmnt = DB::$connection->prepare("SELECT name from extensions left join deviceextensions on extensions.id = deviceextensions.extensionid where deviceextensions.reportId = :reportid");
                        $stmnt->execute(["reportid" => $report_id]);
                    } catch (PDOException $e) {
                        die("Could not fetch device extension for single report!");
                    }	
                    $report_extensions = [];
                    while($row = $stmnt->fetch(PDO::FETCH_NUM)) {	
                        foreach ($row as $extension) {
                            $report_extensions[] = $extension;	  
                        }
                    }
                    $result->data[] = $report_extensions; 
                }
                foreach($rows as $index => $row) {
                    $reportdata = [];				
                    foreach ($row as $key => $values) {
                        if ($key == "reportid") { continue; }
                        $reportdata[] = $values;	  
                        if ($index == 0) {
                            $result->captions[] = $key;
                        }
                    } 	
                    $result->data[] = $reportdata;
                }
                $result->count = count($result->captions);
                return $result;
            } catch (Throwable $e) {
                return [];
            }
        }

        public function fetchQueueFamilies()
        {
            try {       
                $result = new ReportCompareData;
                foreach ($this->report_ids as $reportid) {
                    $stmnt = DB::$connection->prepare("SELECT count, flags, timestampValidBits, `minImageTransferGranularity.width`, `minImageTransferGranularity.height`, `minImageTransferGranularity.depth` from devicequeues where reportid = :reportid");
                    $stmnt->execute(["reportid" => $reportid]);
                    $queue_families = [];
                    foreach($stmnt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                        $queue_family = new stdClass;                        
                        $queue_family->count = $row['count'];
                        $queue_family->flags = $row['flags'];
                        $queue_families[] = $queue_family;
                    }                    
                    $result->data[] = $queue_families;

                }            
                $result->count = count($result->data);
                return $result;
            } catch (Throwable $e) {
                return [];
            }            
        }

        public function beginTable($id)
        {
            echo "<table id='$id' width='100%' class='table table-striped table-bordered table-hover'>";
        }

        public function endTable()
        {
            echo "</tbody></table>";
        }

        public function beginTab($id, $active = false)
        {
            echo "<div id='$id' class='tab-pane fade reportdiv ".($active ? "in active" : "")."'>";
        }

        public function endTab()
        {
            echo "</div>";
        }
        
    }
?>