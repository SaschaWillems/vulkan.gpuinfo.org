<?php

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

class ReportCompareFlags
{
    public $has_surface_caps = false;
    public $has_platform_details = false;
    public $has_extended_features = false;
    public $has_extended_properties = false;
    public $has_vulkan_1_1_features = false;
    public $has_vulkan_1_1_properties = false;
    public $has_vulkan_1_2_features = false;
    public $has_vulkan_1_2_properties = false;
    public $has_vulkan_1_3_features = false;
    public $has_vulkan_1_3_properties = false;
    public $has_profiles = false;
}

class ReportCompareDeviceInfo
{
    public $name;
    public $displayname;
    public $driver_version;
    public $api_version;
    public $platform;
    public $ostype;
    public $reportid;
}

class ReportCompareData
{
    public $captions;
    public $data;
    public $count;
}

class ReportCompareFormatData
{
    public $linear;
    public $optimal;
    public $buffer;
}

class ReportCompare
{

    private $header_column_names = ['device', 'driverversion', 'apiversion', 'os'];
    private $report_column_names = ['id', 'submissiondate', 'submitter', 'devicename', 'driverversion', 'apiversion', 'counter', 'osarchitecture', 'osname', 'osversion', 'description', 'version', 'headerversion', 'displayname', 'ostype', 'internalid', 'reportid'];    

    public $report_ids = [];
    public $report_count = 0;
    public ReportCompareFlags $flags;
    public $device_infos = [];

    function __construct($reportids)
    {
        foreach ($reportids as $id) {
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
        $this->flags->has_vulkan_1_3_features = DB::getCount("SELECT count(*) from devicefeatures13 where reportid in (" . $this->reportIdsParam() . ")", []) > 0;
        $this->flags->has_vulkan_1_3_properties = DB::getCount("SELECT count(*) from deviceproperties13 where reportid in (" . $this->reportIdsParam() . ")", []) > 0;
        // DB::disconnect();
        // Fetch descriptions for devices to be compared
        try {
            $stmnt = DB::$connection->prepare(
                "SELECT 
                    concat(VendorId(p.vendorid), ' ', p.devicename),
                    r.displayname,
                    p.driverversion,
                    p.apiversion,
                    concat(r.osname, ' ', r.osversion, ' (',  r.osarchitecture, ')'),
                    r.ostype,
                    r.id
                from reports r left join deviceproperties p on (p.reportid = r.id) where r.id in (" . $this->reportIdsParam() . ")"
            );
            $stmnt->execute();
        } catch (PDOException $e) {
            die("Could not fetch report data!");
        }
        foreach ($stmnt->fetchAll(PDO::FETCH_NUM) as $device) {
            $device_info = new ReportCompareDeviceInfo;
            $device_info->name = $device[0];
            $device_info->displayname = $device[1];
            $device_info->driver_version = $device[2];
            $device_info->api_version = $device[3];
            $device_info->platform = $device[4];
            $device_info->ostype = $device[5];
            $device_info->reportid = $device[6];
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
    public function insertTableHeader($caption, $grouping_column = false, $device_info = true)
    {
        echo "<thead><tr><th>$caption</th>";
        if ($grouping_column) {
            echo "<th></th>";
        }
        if ($device_info) {
            foreach ($this->device_infos as $device_info) {
                echo "<th>";
                echo $device_info->name;
                echo "<br>";
                if (stripos($device_info->platform, 'android') !== false) {
                    echo $device_info->displayname;
                    echo "<br>";   
                }
                echo "Driver $device_info->driver_version";
                echo "<br>";
                echo ucfirst($device_info->platform);
                echo "<br>";
                echo "Vulkan $device_info->api_version";
                echo "</th>";
            }
        } else {
            foreach ($this->device_infos as $device_info) {
                echo "<th>&nbsp;</th>";
            }
        }
        echo "</thead><tbody>";
    }

    /**
     * Insert device identification rows and columns into the current table
     */
    public function insertDeviceInformation($grouping_column = false)
    {
        echo "<tr><td class='subkey'>Driver version</td>";
        if ($grouping_column) {
            echo "<td>$grouping_column</td>";
        }
        foreach ($this->device_infos as $device_info) {
            echo "<td>$device_info->driver_version</td>";
        }
        echo "</tr>";
        echo "<tr><td class='subkey'>API version</td>";
        if ($grouping_column) {
            echo "<td>$grouping_column</td>";
        }
        foreach ($this->device_infos as $device_info) {
            echo "<td>$device_info->api_version</td>";
        }
        echo "</tr>";
        echo "<tr><td class='subkey'>Platform</td>";
        if ($grouping_column) {
            echo "<td>$grouping_column</td>";
        }
        foreach ($this->device_infos as $device_info) {
            echo "<td>" . ucfirst($device_info->platform) . "</td>";
        }
        echo "</tr>";
    }

    public function fetchDeviceInfo()
    {
        try {
            $stmnt = DB::$connection->prepare(
                "SELECT 
                        r.displayname,
                        p.driverversion,
                        p.devicetype,
                        p.apiversion,
                        p.vendorid,
                        VendorId(p.vendorid) as 'vendor',
                        concat('0x', hex(cast(p.deviceid as UNSIGNED))) as 'deviceid',
                        r.osname,
                        r.osarchitecture,
                        r.osversion,
                        r.submitter,
                        r.submissiondate,
                        (SELECT max(date) from reportupdatehistory where reportid = r.id) as lastupdate,
                        r.version as reportversion,                        
                        r.description		
                    from reports r
                    left join
                    deviceproperties p on (p.reportid = r.id)				
                    where r.id in (" . $this->reportIdsParam() . ")" );
            $stmnt->execute();
            $result = $stmnt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Throwable $e) {
            return null;
        }
    }    

    public function fetchFeatures($version)
    {
        $table = null;
        switch ($version) {
            case '1.0':
                $table = 'devicefeatures';
                break;
            case '1.1':
                $table = 'devicefeatures11';
                break;
            case '1.2':
                $table = 'devicefeatures12';
                break;
            case '1.3':
                $table = 'devicefeatures13';
                break;
        }
        if (!$table) {
            return null;
        }
        try {
            // Need to join with reports to get rows for reports with no row in the properties tabel
            $sql = "SELECT * from reports r left join $table dp on r.id = dp.reportid where r.id in (" . $this->reportIdsParam() . ") order by r.id asc";
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute();
            $rows = $stmnt->fetchAll(PDO::FETCH_ASSOC);
            $result = new ReportCompareData;
            foreach ($rows as $index => $row) {
                $reportdata = [];
                foreach ($row as $key => $values) {
                    if (in_array($key, $this->report_column_names)) { continue; }                    
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

    public function fetchCoreProperties($version)
    {
        $table = null;
        $columns = "*";
        switch ($version) {
            case '1.0':
                $table = 'deviceproperties';
                $columns = "r.apiVersion,
                        r.driverVersion,
                        vendorID,
                        deviceID,
                        deviceType,
                        r.deviceName,
                        pipelineCacheUUID,
                        residencyAlignedMipSize,
                        residencyNonResidentStrict, 
                        residencyStandard2DBlockShape, 
                        residencyStandard2DMultisampleBlockShape, 
                        residencyStandard3DBlockShape,
                        `subgroupProperties.subgroupSize`,
                        `subgroupProperties.supportedStages`,
                        `subgroupProperties.supportedOperations`,
                        `subgroupProperties.quadOperationsInAllStages`";
                break;
            case '1.1':
                $table = 'deviceproperties11';
                break;
            case '1.2':
                $table = 'deviceproperties12';
                break;
            case '1.3':
                $table = 'deviceproperties13';
                break;
        }
        if (!$table) {
            return null;
        }
        try {
            // Need to join with reports to get rows for reports with no row in the properties tabel
            $sql = "SELECT $columns from reports r left join $table dp on r.id = dp.reportid where r.id in (" . $this->reportIdsParam() . ") order by r.id asc";
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute();
            $rows = $stmnt->fetchAll(PDO::FETCH_ASSOC);
            $result = new ReportCompareData;
            foreach ($rows as $index => $row) {
                $reportdata = [];
                foreach ($row as $key => $values) {
                    if (in_array($key, $this->report_column_names)) { continue; }                    
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
            return null;
        }
    }

    public function fetchExtensionFeatures(&$features, &$reports)
    {
        try {
            // Gather all extended features for reports to compare
            try {
                $stmnt = DB::$connection->prepare("SELECT distinct extension, name from devicefeatures2 where reportid in (" . $this->reportIdsParam() . ")");
                $stmnt->execute();
                $features = $stmnt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                die('Could not fetch extended features for compare!');
                DB::disconnect();
            }

            // Get extended features for each selected report into an array 
            foreach ($this->report_ids as $reportid) {
                try {
                    $stmnt = DB::$connection->prepare("SELECT extension, name, supported from devicefeatures2 where reportid = :reportid");
                    $stmnt->execute(['reportid' => $reportid]);
                    $reports[] = $stmnt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    die("Could not fetch device extended features for compare!");
                }
            }
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }    

    public function fetchExtensionProperties(&$properties, &$reports)
    {
        try {
            // Get all extended features for all reports
            try {
                $stmnt = DB::$connection->prepare("SELECT distinct extension, name from deviceproperties2 where reportid in (" . $this->reportIdsParam() . ")");
                $stmnt->execute();
                $properties = $stmnt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                die('Could not fetch extended properties for compare!');
            }

            // Get extended features for each selected report into an array
            foreach ($this->report_ids as $reportid) {
                try {
                    $stmnt = DB::$connection->prepare("SELECT extension, name, value from deviceproperties2 where reportid = :reportid");
                    $stmnt->execute(['reportid' => $reportid]);
                    $reports[] = $stmnt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    die("Could not fetch device extended properties for compare!");
                }
            }

            return true;
        } catch (Throwable $e) {
            return false;
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
            foreach ($rows as $index => $row) {
                $reportdata = [];
                foreach ($row as $key => $values) {
                    if ($key == "reportid") {
                        continue;
                    }
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
                    where deviceextensions.ReportID in (" . $this->reportIdsParam() . ")");
            $stmnt->execute();
            $rows = $stmnt->fetchAll(PDO::FETCH_NUM);
            foreach ($rows as $row) {
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
                while ($row = $stmnt->fetch(PDO::FETCH_NUM)) {
                    foreach ($row as $extension) {
                        $report_extensions[] = $extension;
                    }
                }
                $result->data[] = $report_extensions;
            }
            foreach ($rows as $index => $row) {
                $reportdata = [];
                foreach ($row as $key => $values) {
                    if ($key == "reportid") {
                        continue;
                    }
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
                foreach ($stmnt->fetchAll(PDO::FETCH_ASSOC) as $row) {
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

    public function fetchAvailableFormats()
    {
        try {
            $stmnt = DB::$connection->prepare("SELECT name from VkFormat where value > 0");
            $stmnt->execute();
            return $stmnt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }        
    }

    public function fetchProfiles()
    {       
        try {
            $result = new ReportCompareData;
            $result->captions = [];
            // Gather profiles extensions supported by at least one of the reports
            $stmnt = DB::$connection->prepare("SELECT distinct Name from deviceprofiles 
                    left join profiles on profiles.id = deviceprofiles.profileid 
                    where deviceprofiles.ReportID in (" . $this->reportIdsParam() . ")");
            $stmnt->execute();
            $rows = $stmnt->fetchAll(PDO::FETCH_NUM);
            foreach ($rows as $row) {
                $result->captions[] = $row[0];
            }

            // Get profiles for each selected report              
            foreach ($this->report_ids as $report_id) {
                try {
                    $stmnt = DB::$connection->prepare("SELECT name from profiles left join deviceprofiles on profiles.id = deviceprofiles.profileid where deviceprofiles.reportId = :reportid and supported = 1");
                    $stmnt->execute(["reportid" => $report_id]);
                } catch (PDOException $e) {
                    die("Could not fetch device profiles for single report!");
                }
                $report_profiles = [];
                while ($row = $stmnt->fetch(PDO::FETCH_NUM)) {
                    foreach ($row as $profile) {
                        $report_profiles[] = $profile;
                    }
                }
                $result->data[] = $report_profiles;
            }
            foreach ($rows as $index => $row) {
                $reportdata = [];
                foreach ($row as $key => $values) {
                    if ($key == "reportid") {
                        continue;
                    }
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

    public function fetchSurfaceProperties()
    {
        try {
            $sql = "SELECT * from devicesurfacecapabilities where reportid in (" . $this->reportIdsParam() . ") order by reportid asc";
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute();
            $rows = $stmnt->fetchAll(PDO::FETCH_ASSOC);
            $result = new ReportCompareData;
            $result->data = [];
            $result->captions = [];
            foreach ($rows as $row) {
                foreach ($row as $caption => $value) {
                    // Min and max extents depend on surface size and may be misleading, so remove them
                    if (in_array($caption, ['reportid', 'minImageExtent.width', 'minImageExtent.height', 'maxImageExtent.width', 'maxImageExtent.height'])) {
                        continue;
                    }
                    // Captions from column names
                    if (!in_array($caption, $result->captions)) {
                        $result->captions[] = $caption;
                    }
                    $result->data[$caption][$row['reportid']] = $value;
                }
            }
            // Insert empty data for reports with no or partial surface property data
            foreach ($result->captions as $caption) {
                foreach ($this->report_ids as $reportid) {
                    if (!array_key_exists($reportid, $result->data[$caption])) {
                        $result->data[$caption][$reportid] = null;
                    }
                }
            }
            return $result;
        } catch (Throwable $e) {
            return [];
        }    
    }

    public function fetchSurfaceFormats()
    {
        try {            
            $sql = "SELECT dsf.reportid AS reportid, vf.name as format FROM devicesurfaceformats dsf JOIN VkFormat vf ON dsf.format = vf.value WHERE reportid IN (" . $this->reportIdsParam() . ")";          
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute();
            $rows = $stmnt->fetchAll(PDO::FETCH_ASSOC);
            $result = new ReportCompareData;
            // Get format list
            $result->data = [];
            $result->captions = [];           
            foreach ($rows as $row) {
                if (!in_array($row['format'], $result->captions)) {
                    $result->captions[] = $row['format'];
                    $result->data[$row['format']] = [];
                }
            }
            // Get format support per report
            foreach ($rows as $row) {
                $result->data[$row['format']][$row['reportid']] = true;
            }
            // Insert empty data for reports with no or partial surface property data
            foreach ($result->captions as $caption) {
                foreach ($this->report_ids as $reportid) {
                    if (!array_key_exists($reportid, $result->data[$caption])) {
                        $result->data[$caption][$reportid] = null;
                    }
                }
            }                        
            return $result;
        } catch (Throwable $e) {
            return [];
        }    
    }

    public function fetchSurfacePresentModes()
    {
        try {            
            $sql = "SELECT presentmode, reportid from devicesurfacemodes WHERE reportid IN (" . $this->reportIdsParam() . ")";          
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute();
            $rows = $stmnt->fetchAll(PDO::FETCH_ASSOC);
            $result = new ReportCompareData;
            // Get mode list
            $result->data = [];
            $result->captions = [];
            foreach ($rows as $row) {
                $mode = getPresentMode($row['presentmode']);
                if (!in_array($mode, $result->captions)) {
                    $result->captions[] = $mode;
                    $result->data[$mode] = [];
                }
            }
            // Get format support per report
            $reports_with_data = [];
            foreach ($rows as $row) {
                $mode = getPresentMode($row['presentmode']);
                $result->data[$mode][$row['reportid']] = true;
                if (!in_array($row['reportid'], $reports_with_data)) {
                    $reports_with_data[] = $row['reportid'];
                }
            }
            // Fill data structure with missing information
            foreach ($result->captions as $caption) {
                foreach ($this->report_ids as $reportid) {
                    if (!array_key_exists($reportid, $result->data[$caption])) {
                        $result->data[$caption][$reportid] = in_array($reportid, $reports_with_data) ? false : null;
                    }
                }
            }                        
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
        echo "<div id='$id' class='tab-pane fade reportdiv " . ($active ? "in active" : "") . "'>";
    }

    public function endTab()
    {
        echo "</div>";
    }
}
