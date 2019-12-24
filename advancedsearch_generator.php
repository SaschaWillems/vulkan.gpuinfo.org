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
    
    class AvancedSearchGenerator {
        public $active = false;

        // @todo: Add mapping functions for captions
        // @todo: put into separate file
        private $availablefilters = [
            "queuefamilyflags" => [
                "whereclause" => "r.id in (select reportid from devicequeues where flags & :queuefamilyflags)", 
                "parameter" => "queuefamilyflags", 
                "caption" => "queue family flags"],
            "memorytypeflags" => [
                "whereclause" => "r.id in (select reportid from devicememorytypes where %where_arguments%)", 
                "column" => "propertyflags",
                "comparer" => "&",
                // "whereclause" => "r.id in (select reportid from devicememorytypes where propertyflags & :propertyflags)", 
                //"parameter" => "propertyflags", 
                "caption" => "memory type flags"],
            "subgroup_supportedstages" => [
                "whereclause" => "r.id in (select reportid from deviceproperties where `subgroupProperties.supportedStages` & :supportedStages)", 
                "parameter" => "supportedStages", 
                "caption" => "supported subgroup stages"],
            "subgroup_supportedoperations" => [
                "whereclause" => "r.id in (select reportid from deviceproperties where `subgroupProperties.supportedOperations` & :supportedOperations)", 
                "parameter" => "supportedOperations", 
                "caption" => "supported subgroup operations"],
            "subgroup_size" => [
                "whereclause" => "r.id in (select reportid from deviceproperties where `subgroupProperties.subgroupSize` = :subgroupSize)", 
                "parameter" => "subgroupSize", 
                "caption" => "subgroup size"],
            "subgroup_quadOperationsInAllStages" => [
                "whereclause" => "r.id in (select reportid from deviceproperties where `subgroupProperties.quadOperationsInAllStages` = :quadOperationsInAllStages)", 
                "parameter" => "quadOperationsInAllStages", 
                "caption" => "quad operations in all stages"]                
        ];
       
        /**
         * Generate AJAX-Request JSON-Node from advanced filter request to be passed to server-side processing
         */
        public function getAjaxFilter($request) {
            $search = null;
            $search_values = [];

            foreach ($request as $key => $value) {
                if (key_exists($key, $this->availablefilters) && $value != '') {
                    $search = $key;
                    $search_values = $value;
                }
            }

            if (($search == null) || (count($search_values) == 0)) {
                return null;
            }

            $filter = [
                'enabled' => $this->active,
                'search' => $search,
                'values' => $search_values
            ];
            return json_encode($filter);
        }

        /**
         * Get where clause for the search subject
         */
        public function getWhereClause($search_subject) {
            if (key_exists($search_subject, $this->availablefilters)) {
                return $this->availablefilters[$search_subject]['whereclause'];
            }
        }

        public function setupFilter($search, &$whereClause, &$parameters) {
            $filter = $this->availablefilters[$search['search']];
            assert($filter);
            $where_arguments = [];
            $parameters = [];
            foreach($search['values'] as $index => $value) {
                $where_arguments[] = $filter['column'].' '.$filter['comparer'].' :p'.$index;
                $parameters['p'.$index] = (float)$value;
            }
            $whereClause .= str_replace('%where_arguments%', implode(' and ', $where_arguments), $filter['whereclause']);
        }

        /**
         * Get parameter name for the search subject
         */
        public function getParameterName($search_subject) {
            if (key_exists($search_subject, $this->availablefilters)) {
                return $this->availablefilters[$search_subject]['parameter'];
            }
        }

        /**
         * Get the display caption for the search subject
         */
        public function getCaption($request) {
            foreach ($request as $key => $value) {
                if (key_exists($key, $this->availablefilters) && $value != '') {
                    return $this->availablefilters[$key]['caption']." = ".$value;
                }
            }
        }

        function __construct($request) {
            $this->active = isset($request['advancedsearch']) && ($request['advancedsearch'] == 1);
        }

    }

?>