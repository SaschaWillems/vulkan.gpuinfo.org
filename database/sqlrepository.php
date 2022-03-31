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


/** Builder class for the SQL statements used on different pages */
class SqlRepository {

    const VK_API_VERSION_1_0 = '1.0';
    const VK_API_VERSION_1_1 = '1.1';
    const VK_API_VERSION_1_2 = '1.2';
    const VK_API_VERSION_1_3 = '1.3';

    public static function getMinApiVersion() {
        if (isset($_SESSION['minversion'])) {
            return $_SESSION['minversion'];
        }
        return null;
    }

    private static function getOSType() {
        if (isset($_GET['platform'])) {
            return ostype(GET_sanitized('platform'));
        }
        return null;
    }

    public static function getGetValue($name) {
        if (isset($_GET[$name])) {
            return GET_sanitized($_GET[$name]);
        }
        return null;
    }

    public static function appendCondition(&$sql, $condition) {
        if (strpos(strtolower($sql), 'where') !== false) {
            $sql .= " and $condition";
        } else {
            $sql .= " where $condition";
        }
    }

    public static function appendFilters(&$sql, &$params) {
        $ostype = self::getOSType();
        if ($ostype !== null) {            
            self::appendCondition($sql, "ostype = :ostype");
            $params['ostype'] = $ostype;
        }
        $apiversion = self::getMinApiVersion();
        if ($apiversion) {
            self::appendCondition($sql, "r.apiversion >= :apiversion");
            $params['apiversion'] = $apiversion;
        }      
    }

    public static function deviceCount($sqlAppend = null) {
        // @todo: count(distinct displayname) ? (slightly different numbers)
        $sql = "SELECT count(distinct(ifnull(r.displayname, dp.devicename))) from reports r join deviceproperties dp on dp.reportid = r.id $sqlAppend";
        $ostype = self::getOSType();
        if ($ostype !== null) {
            self::appendCondition($sql, "r.ostype = :ostype");
            $params['ostype'] = $ostype;
        }
        $apiversion = self::getMinApiVersion();
        if ($apiversion) {
            self::appendCondition($sql, "r.apiversion >= :apiversion");
            $params['apiversion'] = $apiversion;
        }
        $stmnt= DB::$connection->prepare($sql);
        $stmnt->execute($params);
        $count = $stmnt->fetch(PDO::FETCH_COLUMN);        
        return $count;
    }

    /** Global extension listing */
    public static function listExtensions() {        
        $deviceCount = self::deviceCount();
        // Fetch extension features and properties to highlight extensions with a detail page
        $params = [];
        $sql = "SELECT distinct(extension) FROM devicefeatures2 d join reports r on r.id = d.reportid";
        self::appendFilters($sql, $params);
        $stmnt = DB::$connection->prepare($sql);
        $stmnt->execute($params);
        $extensionFeatures = $stmnt->fetchAll(PDO::FETCH_COLUMN, 0);
        //
        $params = [];
        $sql = "SELECT distinct(extension) FROM deviceproperties2 d join reports r on r.id = d.reportid";
        self::appendFilters($sql, $params);
        $stmnt = DB::$connection->prepare($sql);
        $stmnt->execute($params);
        $extensionProperties = $stmnt->fetchAll(PDO::FETCH_COLUMN, 0);        
        //
        $params = [];
        $sql ="SELECT e.name, count(distinct displayname) as coverage from extensions e 
            join deviceextensions de on de.extensionid = e.id 
            join reports r on r.id = de.reportid";
        self::appendFilters($sql, $params);
        $sql .= " group by name";
        $stmnt = DB::$connection->prepare($sql);
        $stmnt->execute($params);
        $extensions = [];
        while ($row = $stmnt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
            $extensions[] = [
                'name' => $row['name'],
                'coverage' => round($row['coverage'] / $deviceCount * 100, 1),
                'hasfeatures' => in_array($row['name'], $extensionFeatures) != false, 
                'hasproperties' => in_array($row['name'], $extensionProperties) != false
            ];
        }        
        return $extensions;
    }

    /** Global core feature listings */
    public static function listCoreFeatures($version) { 
        $table = 'devicefeatures';
        switch ($version) {
            case self::VK_API_VERSION_1_1:
                $table = 'devicefeatures11';
                break;
            case self::VK_API_VERSION_1_2:
                $table = 'devicefeatures12';
                break;
            case self::VK_API_VERSION_1_3:
                $table = 'devicefeatures13';
                break;
        }

        // Collect feature column names
        $sql = "SELECT COLUMN_NAME from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME = '$table' and COLUMN_NAME not in ('reportid')";
        $stmnt = DB::$connection->prepare($sql);
        $stmnt->execute();
        $features = [];
        $sqlColumns = "";
        while ($row = $stmnt->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
            $sqlColumns .= "max(" . $row[0] . ") as $row[0],";
        }
        $sqlColumnList = substr($sqlColumns, 0, -1);

        // Get total count for coverage calculation
        $join = null;
        if ($version !== self::VK_API_VERSION_1_0) {
            $join = "join $table df on df.reportid = id";
        }
        $deviceCount = SqlRepository::deviceCount($join);

        // Get device support coverage
        $params = [];
        $sql ="SELECT ifnull(r.displayname, dp.devicename) as device, $sqlColumnList FROM $table df join deviceproperties dp on dp.reportid = df.reportid join reports r on r.id = df.reportid";
        $ostype = self::getOSType();
        if ($ostype !== null) {            
            self::appendCondition($sql, "ostype = :ostype");
            $params['ostype'] = $ostype;
        }
        $apiversion = self::getMinApiVersion();
        if ($apiversion) {
            self::appendCondition($sql, "r.apiversion >= :apiversion");
            $params['apiversion'] = $apiversion;
        }
        $sql .= " group by device";

        // $supportedCounts = [];
        $stmnt = DB::$connection->prepare($sql);
        $stmnt->execute($params);
        while ($row = $stmnt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
            foreach ($row as $key => $value) {
                if (strcasecmp($key, 'device') != 0) {
                    $features[$key] += $value;
                }
            }
        }

        foreach($features as $feature => &$coverage) {
            if ($deviceCount > 0) {
                $coverage = round($coverage / $deviceCount * 100, 1);
            } else {
                $coverage = 0;
            }
        }
        return $features;
    }

    /** Global extension feature listing */
    public static function listExtensionFeatures($extension) {
        // Get the total count of devices that have been submitted with a report version that has support for extension features (introduced with 1.4)
        $deviceCount = SqlRepository::deviceCount("WHERE r.version >= '1.4'");      
        // Limit to features for a given extension
        $ext_filter = null;
        if ($extension) {
            $params['extension'] = $extension;
            $ext_filter = 'AND df2.extension = :extension';
        }
        $sql = 
            "SELECT 
                extension,
                name,
                COUNT(DISTINCT IFNULL(r.displayname, dp.devicename)) AS supporteddevices
            FROM
                devicefeatures2 df2
                    JOIN
                reports r ON df2.reportid = r.id
                    JOIN
                deviceproperties dp ON dp.reportid = r.id
            WHERE
                supported = 1
                $ext_filter";
        self::appendFilters($sql, $params);
        $sql .= " GROUP BY extension , name ORDER BY extension ASC , name ASC";
        $stmnt = DB::$connection->prepare($sql);
        $stmnt->execute($params);
        $features = [];
        while ($row = $stmnt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
            $features[] = [
                'extension' => $row['extension'],
                'name' => $row['name'], 
                'coverage' => round($row['supporteddevices'] / $deviceCount * 100, 1),
            ];
        }
        return $features;
    }

    /** Global core property listings */
    public static function listCoreProperties($version) { 
        $table = 'deviceproperties';
        switch ($version) {
            case self::VK_API_VERSION_1_1:
                $table = 'deviceproperties11';
                break;
            case self::VK_API_VERSION_1_2:
                $table = 'deviceproperties12';
                break;
            case self::VK_API_VERSION_1_3:
                $table = 'deviceproperties13';
                break;
        }

        // Columns with coverage numbers
        $coverage_columns = [
            // VK 1.0
            'residencyAlignedMipSize',
            'residencyNonResidentStrict',
            'residencyStandard2DBlockShape',
            'residencyStandard2DMultisampleBlockShape',
            'residencyStandard3DBlockShape',
            'subgroupProperties.quadOperationsInAllStages',
            // VK 1.1
            'deviceLUIDValid',
            'subgroupQuadOperationsInAllStages',
            'protectedNoFault',            
            // VK 1.2
            'shaderSignedZeroInfNanPreserveFloat16',
            'shaderSignedZeroInfNanPreserveFloat32',
            'shaderSignedZeroInfNanPreserveFloat64',
            'shaderDenormPreserveFloat16',
            'shaderDenormPreserveFloat32',
            'shaderDenormPreserveFloat64',
            'shaderDenormFlushToZeroFloat16',
            'shaderDenormFlushToZeroFloat32',
            'shaderDenormFlushToZeroFloat64',
            'shaderRoundingModeRTEFloat16',
            'shaderRoundingModeRTEFloat32',
            'shaderRoundingModeRTEFloat64',
            'shaderRoundingModeRTZFloat16',
            'shaderRoundingModeRTZFloat32',
            'shaderRoundingModeRTZFloat64',
            'shaderUniformBufferArrayNonUniformIndexingNative',
            'shaderSampledImageArrayNonUniformIndexingNative',
            'shaderStorageBufferArrayNonUniformIndexingNative',
            'shaderStorageImageArrayNonUniformIndexingNative',
            'shaderInputAttachmentArrayNonUniformIndexingNative',
            'robustBufferAccessUpdateAfterBind',
            'quadDivergentImplicitLod',
            'independentResolveNone',
            'independentResolve',
            'filterMinmaxSingleComponentFormats',
            'filterMinmaxImageComponentMapping',            
            // VK 1.3
            'idp8BitUnsignedAccelerated',
            'idp8BitSignedAccelerated',
            'idp8BitMixedSignednessAccelerated',
            'idp4x8BitPackedUnsignedAccelerated',
            'idp4x8BitPackedSignedAccelerated',
            'idp4x8BitPackedMixedSignednessAccelerated',
            'idp16BitUnsignedAccelerated',
            'idp16BitSignedAccelerated',
            'idp16BitMixedSignednessAccelerated',
            'idp32BitUnsignedAccelerated',
            'idp32BitSignedAccelerated',
            'idp32BitMixedSignednessAccelerated',
            'idp64BitUnsignedAccelerated',
            'idp64BitSignedAccelerated',
            'idp64BitMixedSignednessAccelerated',
            'idpAccumulatingSaturating8BitUnsignedAccelerated',
            'idpAccumulatingSaturating8BitSignedAccelerated',
            'idpAccumulatingSaturating8BitMixedSignednessAccelerated',
            'idpAccumulatingSaturating4x8BitPackedUnsignedAccelerated',
            'idpAccumulatingSaturating4x8BitPackedSignedAccelerated',
            'idpAccumulatingSaturating4x8BitPackedMixedSignednessAccelerated',
            'idpAccumulatingSaturating16BitUnsignedAccelerated',
            'idpAccumulatingSaturating16BitSignedAccelerated',
            'idpAccumulatingSaturating16BitMixedSignednessAccelerated',
            'idpAccumulatingSaturating32BitUnsignedAccelerated',
            'idpAccumulatingSaturating32BitSignedAccelerated',
            'idpAccumulatingSaturating32BitMixedSignednessAccelerated',
            'idpAccumulatingSaturating64BitUnsignedAccelerated',
            'idpAccumulatingSaturating64BitSignedAccelerated',
            'idpAccumulatingSaturating64BitMixedSignednessAccelerated',
            'storageTexelBufferOffsetSingleTexelAlignment',
            'uniformTexelBufferOffsetSingleTexelAlignment',            
        ];
       
        // Columns to ignore (not part of the api structure)
        $ignore_columns = [
            'reportid',
            'headerversion',
            'driverversionraw',
            'pipelineCacheUUID',
            'apiversionraw',
            'productManufacturer',
            'productModel'
        ];

        // Get total count for coverage calculation
        $join = null;
        if ($version !== self::VK_API_VERSION_1_0) {
            $join = "join $table df on df.reportid = id";
        }
        $deviceCount = SqlRepository::deviceCount($join);        

        // Collect property column names
        $sql = "SELECT COLUMN_NAME from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME = '$table' and COLUMN_NAME not in ("."'" . implode("','", $ignore_columns) . "') order by COLUMN_NAME";
        $stmnt = DB::$connection->prepare($sql);
        $stmnt->execute();
        $properties = [];
        $sqlColumns = "";
        while ($row = $stmnt->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
            if (in_array($row[0], $coverage_columns)) {
                // Device coverage with numbers
                $sqlColumns .= "max(`$row[0]`) as `$row[0]`,";
            } else {
                // Value listing (no numbers)
                $sqlColumns .= "'novalue' as `$row[0]`,";
            }
        }
        $sqlColumnList = rtrim($sqlColumns, ',');
        $sql = "SELECT r.displayname as device, $sqlColumnList FROM $table dp join reports r on r.id = dp.reportid";
        self::appendFilters($sql, $params);
        $sql .= " GROUP BY device";
        $stmnt = DB::$connection->prepare($sql);
        $stmnt->execute($params);

        $properties = [];
        while ($row = $stmnt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
            foreach ($row as $key => $value) {
                if (strcasecmp($key, 'device') == 0) {
                    continue;
                }
                if ($value == 'novalue') {
                    $properties[$key] = 'valuelisting';
                } else {
                    $properties[$key] += $value;
                }
            }
        }

        // For Vulkan 1.0 we also report limits as properties
        if ($version == self::VK_API_VERSION_1_0) {
            $sql = "SELECT COLUMN_NAME as name from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME = 'devicelimits' and COLUMN_NAME not in ('reportid') order by COLUMN_NAME";
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute();
            while ($row = $stmnt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
                $properties[$row['name']] = 'limit';
            }            
        }

        foreach ($properties as $property => &$coverage) {
            if (is_numeric($coverage)) {
                $coverage = round($coverage / $deviceCount * 100, 1);
            }
        }

        return $properties;
    }

    /** Value listing for given core property */
    public static function listCorePropertyValues($name, $table) {
        $params = [];
        switch ($name) {
            case 'vendorid':
                $sql = "SELECT dp.`$name`as value, VendorId(vendorid) as displayvalue, count(0) as count from $table dp join reports r on r.id = dp.reportid";
                break;
            default:
                $sql = "SELECT dp.`$name` as value, null as displayvalue, count(0) as count from $table dp join reports r on r.id = dp.reportid";
        }                
        self::appendFilters($sql, $params);
        $sql .= " group by 1 order by 3 desc";
        $stmnt = DB::$connection->prepare($sql);
        $stmnt->execute($params);
        $values = [];
        while ($row = $stmnt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
            $values[] = [
                'value' => $row['value'],
                'displayvalue' => ($row['displayvalue'] !== null) ? $row['displayvalue'] : getPropertyDisplayValue($name, $row['value']),
                'count' => $row['count']
            ];
        }
        return $values;
    }

    /** Value listing for given extension property */
    public static function listExtensionPropertyValues($name, $extension) {
        $params = [":name" => $name, ":extension" => $extension];
        $sql = 'SELECT value, count(distinct(r.displayname)) as `count` from deviceproperties2 dp2 join reports r on dp2.reportid = r.id where name = :name and extension = :extension';
        self::appendFilters($sql, $params);
        $sql .= " group by value";
        $stmnt = DB::$connection->prepare($sql);
        $stmnt->execute($params);
        $values = [];
        while ($row = $stmnt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
            $values[] = [
                'value' => $row['value'],
                'count' => $row['count']
            ];
        }
        return $values;
    }

    /** Check if extension property exists */
    public static function extensionPropertyExists($name, $extension) {
        $result = DB::$connection->prepare("SELECT * from deviceproperties2 where name = :name and extension = :extension");
        $result->execute([":name" => $name, ":extension" => $extension]);
        $result->fetch(PDO::FETCH_ASSOC);
        return ($result->rowCount() > 0);
    }

    /** Global extension properties listing */
    public static function listExtensionProperties($extension) {
        // Get the total count of devices that have been submitted with a report version that has support for extension features (introduced with 1.4)
        $deviceCount = SqlRepository::deviceCount("WHERE r.version >= '1.4'");      
        // Limit to features for a given extension
        $ext_filter = null;
        if ($extension) {
            $params['extension'] = $extension;
            $ext_filter = 'AND df2.extension = :extension';
        }
        // We use three unions to get the whole picture (coverage numbers, value listings)
        $sql_union_a = "SELECT 
                    extension,
                    name,
                    'coverage' as type,
                    COUNT(DISTINCT IFNULL(r.displayname, dp.devicename)) AS supporteddevices
                FROM
                    deviceproperties2 d2
                        JOIN
                    reports r ON d2.reportid = r.id
                        JOIN
                    deviceproperties dp ON dp.reportid = r.id
                WHERE
                    value = 'true'";
                                    
        $sql_union_b = "SELECT 
                    extension,
                    name,
                    'coverage' as type,
                    0 as supporteddevices
                FROM
                    deviceproperties2 d2
                        JOIN
                    reports r ON d2.reportid = r.id
                        JOIN
                    deviceproperties dp ON dp.reportid = r.id
                WHERE
                    value ='false'";
                                            
        $sql_union_c = "SELECT 
                    extension,
                    name,
                    'values' as type,
                    0 as supporteddevices
                FROM
                    deviceproperties2 d2
                        JOIN
                    reports r ON d2.reportid = r.id
                        JOIN
                    deviceproperties dp ON dp.reportid = r.id
                WHERE
                    value not in ('true', 'false')";

        if ($ext_filter) {
            $sql_union_a .= " AND $ext_filter";
            $sql_union_b .= " AND $ext_filter";
            $sql_union_c .= " AND $ext_filter";
        }
                
        self::appendFilters($sql_union_a, $params);
        self::appendFilters($sql_union_b, $params);
        self::appendFilters($sql_union_c, $params);

        $sql = "SELECT extension, name, type, sum(supporteddevices) as supporteddevices FROM
            (
                $sql_union_a
                GROUP BY extension, name
            UNION
                $sql_union_b
                GROUP BY extension, name
            UNION
                $sql_union_c
                GROUP BY extension, name
            ) tbl
            GROUP BY extension, name, type
            ORDER BY extension ASC , name ASC";
        $stmnt = DB::$connection->prepare($sql);
        $stmnt->execute($params);        
        $properties = [];
        while ($row = $stmnt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
            $properties[] = [
                'extension' => $row['extension'],
                'name' => $row['name'], 
                'type' => $row['type'],
                'coverage' => round($row['supporteddevices'] / $deviceCount * 100, 1),
            ];
        }
        return $properties;        
    }

    /** Global memory type listings */
    public static function listMemoryTypes() {
        $deviceCount = SqlRepository::deviceCount();
        $sql = "SELECT
            propertyflags as memtype, count(distinct(ifnull(r.displayname, dp.devicename))) as coverage
            from devicememorytypes dmt
            join reports r on r.id = dmt.reportid
            join deviceproperties dp on dp.reportid = r.id";
        self::appendFilters($sql, $params);
        $sql .= " group by memtype desc";
        $stmnt = DB::$connection->prepare($sql);
        $stmnt->execute($params);        
        $memorytypes = [];
        while ($row = $stmnt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
            $memorytypes[] = [
                'memtype' => $row['memtype'],
                'coverage' => round($row['coverage'] / $deviceCount * 100, 1)
            ];
        }
        return $memorytypes;
    }

    /** Global surface format listing */
    public static function listSurfaceFormats() {
        $deviceCount = SqlRepository::deviceCount("WHERE r.version >= '1.2'");
        $sql = "SELECT
            VkFormat(dsf.format) as format,
            dsf.colorspace,
            count(distinct(ifnull(r.displayname, dp.devicename))) as coverage
            from reports r
            join devicesurfaceformats dsf on dsf.reportid = r.id
            join deviceproperties dp on dp.reportid = r.id";
        self::appendFilters($sql, $params);
        $sql .= " group by format, colorspace";
        $stmnt = DB::$connection->prepare($sql);
        $stmnt->execute($params);        
        $surfaceformats = [];
        while ($row = $stmnt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
            $surfaceformats[] = [
                'format' => $row['format'],
                'colorspace' => $row['colorspace'],
                'coverage' => round($row['coverage'] / $deviceCount * 100, 1)
            ];
        }
        return $surfaceformats;
    }

    /** Global surface present mode listing */
    public static function listSurfacePresentModes() {
        $deviceCount = SqlRepository::deviceCount("WHERE r.version >= '1.2'");
        $sql = "SELECT
            vkpm.name as mode,
            count(distinct(ifnull(r.displayname, dp.devicename))) as coverage
            from devicesurfacemodes dsm
            join reports r on r.id = dsm.reportid
            join VkPresentMode vkpm on vkpm.value = dsm.presentmode
            join deviceproperties dp on dp.reportid = r.id";
        self::appendFilters($sql, $params);
        $sql .= " group by mode";
        $stmnt = DB::$connection->prepare($sql);
        $stmnt->execute($params);        
        $surfaceformats = [];
        while ($row = $stmnt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
            $surfaceformats[] = [
                'mode' => $row['mode'],
                'coverage' => round($row['coverage'] / $deviceCount * 100, 1)
            ];
        }
        return $surfaceformats;
    }

    /** Global surface usage flags listing */
    public static function listSurfaceUsageFlags($surface_usage_flags ) {
        $deviceCount = SqlRepository::deviceCount("join devicesurfacecapabilities d on d.reportid = r.id where r.version >= '1.2'");
        $surfaceusageflags = [];
        foreach ($surface_usage_flags as $enum => $flag_name) {
            $sql = "SELECT
                count(distinct(r.displayname)) as coverage
                from devicesurfacecapabilities dsf
                join reports r on r.id = dsf.reportid
                where supportedUsageFlags & $enum = $enum";
            self::appendFilters($sql, $params);
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute($params);
            $row = $stmnt->fetch(PDO::FETCH_ASSOC);
            $surfaceusageflags[] = [
                'name' => $flag_name,
                'coverage' => round($row['coverage'] / $deviceCount * 100, 1)
            ];
        };
        return $surfaceusageflags;
    }

    /** Global instance extension listing */
    public static function listInstanceExtensions() {
        $deviceCount = SqlRepository::deviceCount();
        $sql = "SELECT 
            distinct(name),
            count(distinct(r.displayname)) as coverage
            from deviceinstanceextensions di            
            join instanceextensions ie on di.extensionid = ie.id
            right join reports r on r.id = di.reportid";
        self::appendFilters($sql, $params);
        $sql .= " GROUP by name";
        $stmnt = DB::$connection->prepare($sql);
        $stmnt->execute($params);        
        $instanceextensions = [];
        while ($row = $stmnt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
            if (trim($row['name']) == '') {
                continue;
            }
            $instanceextensions[] = [
                'name' => $row['name'],
                'coverage' => round($row['coverage'] / $deviceCount * 100, 1)
            ];
        }
        return $instanceextensions;
    }

    /** Global instance layer listing */
    public static function listInstanceLayers() {
        $deviceCount = SqlRepository::deviceCount();
        $sql = "SELECT 
            distinct(name),
            count(distinct(r.displayname)) as coverage
            from deviceinstancelayers dl
            join instancelayers il on dl.layerid = il.id
            right join reports r on r.id = dl.reportid";
        self::appendFilters($sql, $params);
        $sql .= " GROUP by name";
        $stmnt = DB::$connection->prepare($sql);
        $stmnt->execute($params);        
        $instancelayers = [];
        while ($row = $stmnt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
            if (trim($row['name']) == '') {
                continue;
            }
            $instancelayers[] = [
                'name' => $row['name'],
                'coverage' => round($row['coverage'] / $deviceCount * 100, 1)
            ];
        }
        return $instancelayers;
    }

    /** Global profiles listing */
    public static function listProfiles() {
        $deviceCount = SqlRepository::deviceCount("join deviceprofiles d on d.reportid = r.id");
        $sql = "SELECT
                name,
                count(distinct (case when supported = 1 then displayname end)) as coverage
            from
                deviceprofiles dp
                join profiles p on p.id = dp.profileid
                join reports r on r.id = dp.reportid";
        self::appendFilters($sql, $params);
        $sql .= " GROUP by name";
        $stmnt = DB::$connection->prepare($sql);
        $stmnt->execute($params);        
        $instancelayers = [];
        while ($row = $stmnt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
            if (trim($row['name']) == '') {
                continue;
            }
            $instancelayers[] = [
                'name' => $row['name'],
                'coverage' => round($row['coverage'] / $deviceCount * 100, 1)
            ];
        }
        return $instancelayers;        
    }

}