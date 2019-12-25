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
                "caption" => "queue family flags"
            ],
            "memorytypeflags" => [
                "whereclause" => "r.id in (select reportid from devicememorytypes where %where_arguments%)", 
                "column" => "propertyflags",
                "comparer" => "&",
                // "whereclause" => "r.id in (select reportid from devicememorytypes where propertyflags & :propertyflags)", 
                //"parameter" => "propertyflags", 
                "caption" => "memory type flags"
            ],
            "subgroup_supportedstages" => [
                "whereclause" => "r.id in (select reportid from deviceproperties where `subgroupProperties.supportedStages` & :supportedStages)", 
                "parameter" => "supportedStages", 
                "caption" => "supported subgroup stages"
            ],
            "subgroup_supportedoperations" => [
                "whereclause" => "r.id in (select reportid from deviceproperties where %where_arguments%)", 
                "column" => "`subgroupProperties.supportedOperations`",
                "comparer" => "&", 
                "caption" => "supported subgroup operations"
            ],
            "subgroup_size" => [
                "whereclause" => "r.id in (select reportid from deviceproperties where %where_arguments%)",
                "column" => "`subgroupProperties.subgroupSize`",
                "comparer" => "=",
                "caption" => "subgroup size"
            ],
            "subgroup_quadOperationsInAllStages" => [
                "whereclause" => "r.id in (select reportid from deviceproperties where `subgroupProperties.quadOperationsInAllStages` = :quadOperationsInAllStages)", 
                "parameter" => "quadOperationsInAllStages", 
                "caption" => "quad operations in all stages"
            ],
            "format_features_linear" => [
                "whereclause" => "r.id in (select reportid from deviceformats where %where_arguments% and formatid = :format )", 
                "column" => "lineartilingfeatures",
                "comparer" => "&", 
                "translator" => "getFormatFlagName",
                "caption" => "linear image format feature flags"
            ],    
            "format_features_optimal" => [
                "whereclause" => "r.id in (select reportid from deviceformats where %where_arguments% and formatid = :format )", 
                "column" => "optimaltilingfeatures",
                "comparer" => "&", 
                "translator" => "getFormatFlagName",
                "caption" => "optimal image format feature flags"
            ],             
            "format_features_buffer" => [
                "whereclause" => "r.id in (select reportid from deviceformats where %where_arguments% and formatid = :format )", 
                "column" => "bufferfeatures",
                "comparer" => "&", 
                "translator" => "getFormatFlagName",
                "caption" => "buffer format feature flags"
            ],             
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

            if (isset($request['format'])) {
                $filter['format'] = $request['format'];
            }

            return json_encode($filter);
        }

        /** 
         * Setup where clause and parameters for filtering for a given search subject
         */
        public function setupFilter($search, &$whereClause, &$parameters) {
            $filter = $this->availablefilters[$search['search']];
            assert($filter);
            $where_arguments = [];
            $parameters = [];
            if (is_array($search['values'])) {
                foreach($search['values'] as $index => $value) {
                    $where_arguments[] = $filter['column'].' '.$filter['comparer'].' :p'.$index;
                    $parameters['p'.$index] = (float)$value;
                }
            } else {
                $where_arguments[] = $filter['column'].' '.$filter['comparer'].' :param';
                $parameters['param'] = (float)$search['values'];
            }
            if (strpos($search['search'], 'format_') === 0) {
                $parameters['format'] = $search['format'];
            }
            $whereClause .= str_replace('%where_arguments%', implode(' and ', $where_arguments), $filter['whereclause']);
        }

        /**
         * Get the display caption for the search subject
         */
        public function getCaption($request) {
            foreach ($request as $key => $value) {
                if (key_exists($key, $this->availablefilters) && $value != '') {
                    $filter = $this->availablefilters[$key];
                    $translator = $filter['translator'];
                    $display_values = null;
                    if (is_array($value)) {
                        foreach ($value as $val) {
                            $display_values[] = $translator ? $translator($val) : $val;
                        }
                    } else {
                        $display_values[] = $translator ? $translator($value) : $value;
                    }
                    $caption = $filter['caption']." = ".implode(' & ', $display_values);
                    if (isset($request['format'])) {
                        DB::connect();
                        $stmnt = DB::$connection->prepare('SELECT name from VkFormat where value = :id');
                        $stmnt->execute(['id' => $request['format']]);
                        $res = $stmnt->fetch();
                        if ($res) {
                            $caption .= " for format ".$res[0];
                        }
                        DB::disconnect();
                    }
                    return $caption;
                }
            }
        }

        function __construct($request) {
            $this->active = isset($request['advancedsearch']) && ($request['advancedsearch'] == 1);
        }

    }

?>