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

    require_once('vulkanenums.php');

    class AvancedSearchGenerator {
        public $active = false;

        private $availablefilters = [];
       
        /**
         * Generate AJAX-Request JSON-Node from advanced filter request to be passed to server-side processing
         */
        public function getAjaxFilter($request) {
            $search = null;
            $search_values = [];

            foreach ($request as $key => $value) {
                if (key_exists($key, $this->availablefilters) && $value != '') {
                    $search = $key;
                    $search_values[] = $value;
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
         * Returns the query string for the current advanced search filter
         */
        public function getQueryString($request) {
            $active_queries = [];
            $queries = [];
            parse_str($_SERVER['QUERY_STRING'], $queries);
            // Only select query parameters that belong to an advanced search filter
            foreach($queries as $query => $query_value) {
                if (key_exists($query, $this->availablefilters)) {
                    if (is_array($query_value)) {
                        foreach($query_value as $index => $value) {
                            $active_queries[] = $query."=".$value;
                        }
                    } else {
                        $active_queries[] = $query."=".$query_value;
                    }
                }
            }
            $active_queries[] = "advancedsearch=" .($this->active ? "1" : "0");
            return implode('&', $active_queries);
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
                    $where_arguments[] = $filter['column'].' '.$filter['comparator'].' :p'.$index;
                    $parameters['p'.$index] = (float)$value;
                }
            } else {
                $where_arguments[] = $filter['column'].' '.$filter['comparator'].' :param';
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
            // Load available filters
            $json_data = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/advancedsearch.json');
            $json = json_decode($json_data, true);
            foreach ($json['groups'] as $group) {
                foreach ($group['subjects'] as $subject) {
                    if (array_key_exists('filter', $subject)) {
                        $this->availablefilters[$subject['id']] = $subject['filter'];
                        $this->availablefilters[$subject['id']]['caption'] = $subject['caption'];
                    }
                }
            }
        }

    }

?>