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

        private $availablefilters = [
            "queuefamilyflags" => ["whereclause" => "r.id in (select reportid from devicequeues where flags & :queuefamilyflags)", "parameter" => "queuefamilyflags", "caption" => "queue family flags"],
            "memorytypeflags" => ["whereclause" => "r.id in (select reportid from devicememorytypes where propertyflags & 16)", "parameter" => "propertyflags", "caption" => "memory type flags"]
        ];
       
        /**
         * Generate AJAX-Request JSON-Node from advanced filter request to be passed to server-side processing
         */
        public function getAjaxFilter($request) {
            $search = null;
            $search_value = null;

            foreach ($request as $key => $value) {
            if (key_exists($key, $this->availablefilters) && $value != '') {
                    $search = $key;
                    $search_value = $value;
                    break;
                }
            }

            if (($search == null) || ($search_value == null)) {
                return null;
            }

            $ajax_filter = "'advanced': { 'enabled': '$this->active', 'search': '$search', 'value' : $value}";
            return $ajax_filter;
        }

        /**
         * Get where clause for the search subject
         */
        public function getWhereClause($search_subject) {
            if (key_exists($search_subject, $this->availablefilters)) {
                return $this->availablefilters[$search_subject]['whereclause'];
            }
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