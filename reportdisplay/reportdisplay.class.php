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

class ReportFlags
{
    public $has_instance_data = false;
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
    public $has_portability_extension = false;
    public $has_update_history = false;
    public $has_profiles = false;
}

class ReportApiVersion
{
    public $major = null;
    public $minor = null;
    public $patch = null;
}

class ReportInfo
{
    public $version = null;
    public $device_description = null;
    public $platform = null;
}

class Report
{
    public $id = null;
    public ReportApiVersion $apiversion;
    public ReportInfo $info;
    public ReportFlags $flags;

    function __construct($reportid)
    {
        $this->id = $reportid;
        $this->apiversion = new ReportApiVersion;
        $this->flags = new ReportFlags;
        $this->info = new ReportInfo;
    }

    public function exists()
    {
        DB::connect();
        $stmnt = DB::$connection->prepare("SELECT 1 from reports where id = :reportid LIMIT 1");
        $stmnt->execute([':reportid' => $this->id]);
        $result = $stmnt->fetchColumn();
        DB::disconnect();
        return $result;
    }

    public function fetchData()
    {
        DB::connect();
        // Basic report information
        $sql = "SELECT
                p.devicename,
                r.displayname,
                VendorId(p.vendorid) as 'vendor',
                r.version as reportversion,
                r.ostype,
                p.apiversionraw
                from reports r
                left join
                deviceproperties p on (p.reportid = r.id)
                where r.id = :reportid";
        $stmnt = DB::$connection->prepare($sql);
        $stmnt->execute([':reportid' => $this->id]);
        $row = $stmnt->fetch(PDO::FETCH_ASSOC);
        $this->info->version = $row['reportversion'];
        if ($row['ostype'] == 2) {
            // Display device name from platform data instead of GPU vendor and name on Android
            $this->info->device_description = $row['displayname'];
        } else {
            if (($row['vendor']) && (stripos($row['devicename'], $row['vendor']) === 0)) {
                // Don't include vendor name if it's already part of the device name
                $this->info->device_description = $row['devicename'];
            } else {
                $this->info->device_description = $row['vendor'] . " " . $row['devicename'];
            }
        }
        $this->apiversion->major = $row['apiversionraw'] >> 22;
        $this->apiversion->minor = ($row['apiversionraw'] >> 12) & 0x3ff;
        $this->apiversion->patch = ($row['apiversionraw'] & 0xfff);
        $this->info->platform = platformname($row['ostype']);
        // Flags for optional data
        $this->flags->has_instance_data =  DB::getCount("SELECT (select count(*) from deviceinstanceextensions where reportid = :reportid) + (select count(*) from deviceinstancelayers where reportid = :reportid)", [':reportid' => $this->id]) > 0;
        $this->flags->has_surface_caps = DB::getCount("SELECT count(*) from devicesurfacecapabilities where reportid = :reportid", [':reportid' => $this->id]) > 0;
        $this->flags->has_platform_details = DB::getCount("SELECT count(*) from deviceplatformdetails where reportid = :reportid", [':reportid' => $this->id]) > 0;
        $this->flags->has_extended_features = DB::getCount("SELECT count(*) from devicefeatures2 where reportid = :reportid", [':reportid' => $this->id]) > 0;
        $this->flags->has_extended_properties = DB::getCount("SELECT count(*) from deviceproperties2 where reportid = :reportid", [':reportid' => $this->id]) > 0;
        $this->flags->has_vulkan_1_1_features = DB::getCount("SELECT count(*) from devicefeatures11 where reportid = :reportid", [':reportid' => $this->id]) > 0;
        $this->flags->has_vulkan_1_1_properties = DB::getCount("SELECT count(*) from deviceproperties11 where reportid = :reportid", [':reportid' => $this->id]) > 0;
        if ($this->flags->has_vulkan_1_1_properties === false) {
            $this->flags->has_vulkan_1_1_properties = (($this->apiversion->major >= 1) && ($this->apiversion->minor >= 1));
        }
        $this->flags->has_vulkan_1_2_features = DB::getCount("SELECT count(*) from devicefeatures12 where reportid = :reportid", [':reportid' => $this->id]) > 0;
        $this->flags->has_vulkan_1_2_properties = DB::getCount("SELECT count(*) from deviceproperties12 where reportid = :reportid", [':reportid' => $this->id]) > 0;
        $this->flags->has_vulkan_1_3_features = DB::getCount("SELECT count(*) from devicefeatures13 where reportid = :reportid", [':reportid' => $this->id]) > 0;
        $this->flags->has_vulkan_1_3_properties = DB::getCount("SELECT count(*) from deviceproperties13 where reportid = :reportid", [':reportid' => $this->id]) > 0;
        $this->flags->has_portability_extension = DB::getCount("SELECT count(*) from deviceextensions de right join extensions e on de.extensionid = e.id where reportid = :reportid and name = :extension", [':reportid' => $this->id, ':extension' => 'VK_KHR_portability_subset']) > 0;
        $this->flags->has_update_history = DB::getCount("SELECT count(*) from reportupdatehistory where reportid = :reportid", [':reportid' => $this->id]) > 0;
        $this->flags->has_profiles =  DB::getCount("SELECT count(*) from deviceprofiles where reportid = :reportid", [':reportid' => $this->id]) > 0;
        DB::disconnect();
    }

    public function fetchDeviceInfo()
    {
        try {
            $sql = "SELECT 
                p.devicename,
                r.displayname,
                p.driverversionraw,
                p.driverversion,
                p.devicetype,
                p.apiversion,
                p.vendorid,
                VendorId(p.vendorid) as 'vendor',
                concat('0x', hex(cast(p.deviceid as UNSIGNED))) as 'deviceid',
                p.pipelineCacheUUID,
                r.osname,
                r.osarchitecture,
                r.osversion,
                r.submitter,
                r.submissiondate,
                (SELECT max(date) from reportupdatehistory where reportid = :reportid) as lastupdate,
                r.version as reportversion,
                r.description,
                'profile' as `profile`
                from reports r
                left join
                deviceproperties p on (p.reportid = r.id)
                where r.id = :reportid";
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute([":reportid" => $this->id]);
            $result = $stmnt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function fetchPlatformDetails()
    {
        try {
            $sql = "SELECT name, value from deviceplatformdetails dpfd join platformdetails pfd on dpfd.platformdetailid = pfd.id where dpfd.reportid = :reportid order by name asc";
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute([":reportid" => $this->id]);
            $result = $stmnt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function fetchLimits()
    {
        try {
            $sql = "SELECT * from devicelimits where reportid = :reportid";
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute([":reportid" => $this->id]);
            $result = $stmnt->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function fetchExtensions()
    {
        try {
            $sql = "SELECT e.name as name, de.specversion as specversion from deviceextensions de join extensions e on de.extensionid = e.id where reportid = :reportid order by name asc";
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute([":reportid" => $this->id]);
            $result = $stmnt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function fetchFormats()
    {
        try {
            $sql = "SELECT VkFormat(formatid) as format, deviceformats.* from deviceformats where reportid = :reportid order by format asc";
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute([":reportid" => $this->id]);
            $result = $stmnt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function fetchQueueFamilies()
    {
        try {
            $sql = "SELECT * from devicequeues where reportid = :reportid";
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute([":reportid" => $this->id]);
            $result = $stmnt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function fetchMemoryHeaps()
    {
        // Complex statement, as database design is missing a proper foreign key
        try {
            $sql = "SELECT id, flags, size from devicememoryheaps where reportid = :reportid order by id asc";
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute([":reportid" => $this->id]);
            $result = $stmnt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function fetchMemoryTypes($heap_index)
    {
        try {
            $sql = "SELECT * from devicememorytypes where heapindex = :heapindex and reportid = :reportid order by id asc";
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute([":reportid" => $this->id, ":heapindex" => $heap_index]);
            $result = $stmnt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function fetchInstanceExtensions()
    {
        try {
            $sql = "SELECT e.name as name, ie.specversion as specversion from deviceinstanceextensions ie join instanceextensions e on ie.extensionid = e.id where reportid = :reportid";
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute([":reportid" => $this->id]);
            $result = $stmnt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function fetchInstanceLayers()
    {
        try {
            $sql = "SELECT il.name as name, dil.specversion as specversion, dil.implversion as implversion from deviceinstancelayers dil join instancelayers il on il.id = dil.layerid where reportid = :reportid";
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute([":reportid" => $this->id]);
            $result = $stmnt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function fetchSurfaceProperties()
    {
        try {
            $sql = "SELECT * from devicesurfacecapabilities where reportid = :reportid";
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute([":reportid" => $this->id]);
            $result = $stmnt->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function fetchSurfaceFormats()
    {
        try {
            $sql = "SELECT VkFormat(format) as format, colorspace from devicesurfaceformats where reportid = :reportid";
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute([":reportid" => $this->id]);
            $result = $stmnt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function fetchSurfacePresentModes()
    {
        try {
            $sql = "SELECT presentmode from devicesurfacemodes where reportid = :reportid";
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute([":reportid" => $this->id]);
            $result = $stmnt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function fetchCoreFeatures($version)
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
            $sql = "SELECT * from $table where reportid = :reportid";
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute([":reportid" => $this->id]);
            $result = $stmnt->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function fetchExtensionFeatures()
    {
        try {
            $sql = "SELECT name, supported, extension from devicefeatures2 where reportid = :reportid order by extension, name asc";
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute([":reportid" => $this->id]);
            $result = $stmnt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function fetchCoreProperties($version)
    {
        $table = null;
        $columns = "*";
        switch ($version) {
            case '1.0':
                $table = 'deviceproperties';
                $columns = "apiVersion,
                    driverVersion,
                    vendorID,
                    deviceID,
                    deviceType,
                    deviceName,
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
            $sql = "SELECT $columns from $table where reportid = :reportid";
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute([":reportid" => $this->id]);
            $result = $stmnt->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function fetchSubgroupProperties()
    {
        $table = null;
        $columns = "`subgroupProperties.subgroupSize` as subgroupSize,
                `subgroupProperties.supportedStages` as subgroupSupportedStages,
                `subgroupProperties.supportedOperations` as subgroupSupportedOperations,
                `subgroupProperties.quadOperationsInAllStages` as subgroupQuadOperationsInAllStages";
        try {
            $sql = "SELECT $columns from deviceproperties where reportid = :reportid";
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute([":reportid" => $this->id]);
            $result = $stmnt->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function fetchExtensionProperties()
    {
        try {
            $sql = "SELECT name, value, extension from deviceproperties2 where reportid = :reportid order by extension, name asc";
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute([":reportid" => $this->id]);
            $result = $stmnt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function fetchUpdateHistory()
    {
        try {
            $sql = "SELECT date, submitter, log, reportversion from reportupdatehistory where reportid = :reportid order by id desc";
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute([":reportid" => $this->id]);
            $result = $stmnt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function fetchProfiles()
    {
        try {
            $sql = "SELECT name, supported from deviceprofiles dp join profiles p on dp.profileid = p.id where reportid = :reportid order by name asc";
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute([":reportid" => $this->id]);
            $result = $stmnt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Throwable $e) {
            return null;
        }     
    }

    /**
     * Helper functions for setting up tabs and tables
     */

    public function beginTable($id, $header_columns)
    {
        echo "<table id='$id' width='100%' class='table table-striped table-bordered table-hover'>";
        if (count($header_columns) > 0) {
            echo "<thead><tr>";
            foreach ($header_columns as $header_column) {
                echo "<th>$header_column</th>";
            }
            echo "</tr></thead>";
        }
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
