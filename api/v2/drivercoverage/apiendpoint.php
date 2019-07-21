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

    /**
     * Base class for device coverage api endpoints
     */
    class apiendpoint {
        private $whereClause;
        private $osType;
        private $platform;
       
        private function getOSType() {
            $this->ostype = null;
            $this->platform = null;
            if (isset($_GET["platform"])) {
                $this->platform = $_GET["platform"];
                switch($this->platform) {
                    case 'windows':
                        $this->osType = 0;
                        break;
                    case 'linux':
                        $this->osType = 1;
                        break;
                    case 'android':
                        $this->osType = 2;
                        break;
                }
                if ($this->osType === null) {
                    exit(json_encode(["error" => "Unknown platform type"]));
                }
            }            
        }

        public function setWhereClause($whereClause) {
            $this->whereClause = $whereClause;
            $this->getOSType();
        }

        public function getStatement() {
            $whereClause = $this->whereClause;

            if ($this->osType !== null) {
                $whereClause .= " AND r.ostype = :ostype";
            }

			return "SELECT 
				ifnull(r.displayname, dp.devicename) as device, 
				min(dp.apiversionraw) as api,
				min(dp.driverversion) as driverversion,
				min(dp.driverversionraw) as driverversionraw, 
				concat('0x', hex(cast(dp.deviceid as unsigned))) as deviceid,			
				concat('0x', hex(cast(dp.vendorid as unsigned))) as vendorid,
				VendorId(dp.vendorid) as vendor,
				date(min(submissiondate)) as submissiondate,
				r.osname as platform
				FROM reports r
				JOIN deviceproperties dp on r.id = dp.reportid
				$whereClause
				GROUP BY device
				ORDER by platform, device";
        }

        public function execute($params, $queryinfo) {
            if ($this->osType !== null) {
                $params["ostype"] = $this->osType;
            }
            $stmnt = DB::$connection->prepare($this->getStatement());
            $stmnt->execute($params);       
            $results = array();
            if ($stmnt->rowCount() > 0) {		
                while ($row = $stmnt->fetch(PDO::FETCH_ASSOC)) {
                    $results[] = $row;
                }
            } 
            $response["query"] = $queryinfo;
            if ($this->osType !== null) {
                $response["query"]["platform"] = $this->platform;
            }
            $response["query"]["resultcount"] = $stmnt->rowCount();
            $response["devices"] = $results;
            echo _format_json(json_encode($response), false);			
        }
    } 
?>