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
  
    class ReportFlags {
        public $has_instance_data = false;
        public $has_surface_caps = false;
        public $has_platform_details = false;
        public $has_extended_features = false;
        public $has_extended_properties = false;
        public $has_vulkan_1_1_features = false;
        public $has_vulkan_1_1_properties = false;
        public $has_vulkan_1_2_features = false;
        public $has_vulkan_1_2_properties = false;
        public $has_portability_extension = false;
    }

    class ReportInfo {
        public $version = null;
        public $device_description = null;
        public $platform = null;
    }

    class Report {        
        public $id = null;
        public ReportInfo $info;
        public ReportFlags $flags;

        function __construct($reportid)
        {
            $this->id = $reportid;
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
                VendorId(p.vendorid) as 'vendor',
                r.version as reportversion,
                r.ostype
                from reports r
                left join
                deviceproperties p on (p.reportid = r.id)
                where r.id = :reportid";
            $stmnt = DB::$connection->prepare($sql); 
            $stmnt->execute([':reportid' => $this->id]);
            $row = $stmnt->fetch(PDO::FETCH_ASSOC);
            $this->info->version = $row['reportversion'];
            if (strpos($row['devicename'], $row['vendor']) === 0) {
                // Don't include vendor name if it's already part of the device name
                $this->info->device_description = $row['devicename'];
            } else {
                $this->info->device_description = $row['vendor']." ".$row['devicename'];
            }
            $this->info->platform = platformname($row['ostype']);
            // Flags for optional data
            $this->flags->has_instance_data =  DB::getCount("SELECT (select count(*) from deviceinstanceextensions where reportid = :reportid) + (select count(*) from deviceinstancelayers where reportid = :reportid)", [':reportid' => $this->id]) > 0;
            $this->flags->has_surface_caps = DB::getCount("SELECT count(*) from devicesurfacecapabilities where reportid = :reportid", [':reportid' => $this->id]) > 0;
            $this->flags->has_platform_details = DB::getCount("SELECT count(*) from deviceplatformdetails where reportid = :reportid", [':reportid' => $this->id]) > 0;
            $this->flags->has_extended_features = DB::getCount("SELECT count(*) from devicefeatures2 where reportid = :reportid", [':reportid' => $this->id]) > 0;
            $this->flags->has_extended_properties = DB::getCount("SELECT count(*) from deviceproperties2 where reportid = :reportid", [':reportid' => $this->id]) > 0;
            $this->flags->has_vulkan_1_1_features = DB::getCount("SELECT count(*) from devicefeatures11 where reportid = :reportid", [':reportid' => $this->id]) > 0;
            $this->flags->has_vulkan_1_1_properties = DB::getCount("SELECT count(*) from deviceproperties11 where reportid = :reportid", [':reportid' => $this->id]) > 0;
            $this->flags->has_vulkan_1_2_features = DB::getCount("SELECT count(*) from devicefeatures12 where reportid = :reportid", [':reportid' => $this->id]) > 0;
            $this->flags->has_vulkan_1_2_properties = DB::getCount("SELECT count(*) from deviceproperties12 where reportid = :reportid", [':reportid' => $this->id]) > 0;
            $this->flags->has_portability_extension = DB::getCount("SELECT count(*) from deviceextensions de right join extensions e on de.extensionid = e.id where reportid = :reportid and name = :extension", [':reportid' => $this->id, ':extension' => 'VK_KHR_portability_subset']) > 0;
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
                r.version as reportversion,
                r.description,
                'devsim' as `devsim`
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

    }