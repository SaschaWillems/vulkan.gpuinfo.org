<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2021 by Sascha Willems (www.saschawillems.de)
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

/**
 * Builds and returns SQL statements used by the database taking user settings into account
 */
class SqlRepository
{
    public $vulkan_api_version = null;
    public $ostype = 0;

    function __construct($platform)
    {
        $this->ostype = ostype($platform);
        if ((isset($_COOKIE["vulkan_api_version"])) && ($_COOKIE["vulkan_api_version"] !== '')) {
            // @todo: sanity check
            $this->vulkan_api_version = $_COOKIE["vulkan_api_version"];
        }
    }

    // General

    public function deviceCount()
    {
        $sql = "SELECT count(DISTINCT displayname) from reports where ostype = :ostype";
        $params['ostype'] = $this->ostype;
        if ($this->vulkan_api_version != null) {
            $sql .= " AND left(apiversion, 3) >= :settings_vulkan_api_version";
            $params['settings_vulkan_api_version'] = $this->vulkan_api_version;
        }
        $query = DB::$connection->prepare($sql);
        try {
            $query->execute($params);
            $count = $query->fetch(PDO::FETCH_COLUMN);
            return $count;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getTableColumnNames($table)
    {
        $columns = [];
        $sql = "SELECT COLUMN_NAME from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME = '$table' and COLUMN_NAME not in ('reportid')";
        $query = DB::$connection->prepare($sql);
        try {
            $query->execute();
            while ($row = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                $columns[] = $row[0];
            }            
            return $columns;
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Extensions

    public function extensionDeviceFeatures2List()
    {
        $sql = "SELECT distinct(extension) FROM devicefeatures2";
        $query = DB::$connection->prepare($sql);
        try {
            $query->execute();
            $result = $query->fetchAll(PDO::FETCH_COLUMN, 0);
            return $result;
        } catch (Exception $e) {
            throw $e;
        }        
    }

    public function extensionDeviceProperties2List()
    {
        $sql = "SELECT distinct(extension) FROM deviceproperties2";
        $query = DB::$connection->prepare($sql);
        try {
            $query->execute();
            $result = $query->fetchAll(PDO::FETCH_COLUMN, 0);
            return $result;
        } catch (Exception $e) {
            throw $e;
        }        
    }    

    public function extensionList()
    {
        $sql = "SELECT e.name, count(distinct displayname) as coverage from extensions e 
            join deviceextensions de on de.extensionid = e.id 
            join reports r on r.id = de.reportid
            where ostype = :ostype";
        $params['ostype'] = $this->ostype;
        if ($this->vulkan_api_version != null) {
            $sql .= " AND left(apiversion, 3) >= :settings_vulkan_api_version";
            $params['settings_vulkan_api_version'] = $this->vulkan_api_version;
        }
        $sql .= " GROUP BY Name";
        $query = DB::$connection->prepare($sql);
        try {
            $query->execute($params);
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Formats

    public function formatList($target)
    {
        $sql = "SELECT 
            vkf.name as name, 
            count(distinct(ifnull(r.displayname, dp.devicename))) as coverage
            from reports r
            join deviceformats df on df.reportid = r.id and df.$target > 0
            join VkFormat vkf on vkf.value = df.formatid
            join deviceproperties dp on dp.reportid = r.id
            where r.ostype = :ostype";
        $params['ostype'] = $this->ostype;
        if ($this->vulkan_api_version != null) {
            $sql .= " AND left(r.apiversion, 3) >= :settings_vulkan_api_version";
            $params['settings_vulkan_api_version'] = $this->vulkan_api_version;
        }
        $sql .= " GROUP BY Name";
        $query = DB::$connection->prepare($sql);
        try {
            $query->execute($params);
            return $query;
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Features

    public function getFeatureCoverageDeviceCount($core_version)
    {
        $table = 'devicefeatures';
        switch ($core_version) {
            case VK_API_VERSION_1_1:
                $table = 'devicefeatures11';
                break;
            case VK_API_VERSION_1_2:
                $table = 'devicefeatures12';
                break;
        }              
        $sql = "SELECT count(distinct(displayname)) from reports join $table dp on dp.reportid = id where ostype = :ostype";
        $params['ostype'] = $this->ostype;
        if ($this->vulkan_api_version != null) {
            $sql .= " AND left(apiversion, 3) >= :settings_vulkan_api_version";
            $params['settings_vulkan_api_version'] = $this->vulkan_api_version;
        }
        $query = DB::$connection->prepare($sql);
        try {
            $query->execute($params);
            $count = $query->fetch(PDO::FETCH_COLUMN);
            return $count;
        } catch (Exception $e) {
            throw $e;
        }        
    }

    public function getFeatureCoverageCore($core_version)
    {
        $table = 'devicefeatures';
        switch ($core_version) {
            case VK_API_VERSION_1_1:
                $table = 'devicefeatures11';
                break;
            case VK_API_VERSION_1_2:
                $table = 'devicefeatures12';
                break;
        }        
        $features = $this->getTableColumnNames($table);
        $params['ostype'] = $this->ostype;
        $sql = "SELECT ifnull(r.displayname, dp.devicename) as device ";     
        // Get max. values for features (max = 1 = supported)
        foreach ($features as $feature) {
            $sql .= ", max($feature) as $feature";
        }
        $sql .= " FROM $table df join deviceproperties dp on dp.reportid = df.reportid join reports r on r.id = df.reportid where r.ostype = :ostype";
        if ($this->vulkan_api_version != null) {
            $sql .= " AND left(r.apiversion, 3) >= :settings_vulkan_api_version";
            $params['settings_vulkan_api_version'] = $this->vulkan_api_version;
        }
        $sql .= " group by device";
        $query = DB::$connection->prepare($sql);
        $coverages = [];
        try {
            $query->execute($params);
            while ($row = $query->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
                foreach ($row as $key => $value) {
                    if (strcasecmp($key, 'device') != 0) {
                        $coverages[$key] += $value;
                    }
                }
            }
            return $coverages;
        } catch (Exception $e) {
            throw $e;
        }        
    }

    // Misc. function

    /** Insert a note for currently applied global filters */
    public function filterHeader()
    {
        if ($this->vulkan_api_version !== null) {
            echo "<div class=\"alert alert-warning\" style=\"margin-bottom: 0px;\">Only displaying data for devices with Vulkan version $this->vulkan_api_version and up (<a href='settings.php'>change settings</a>)</div>";
        }
    }

}
