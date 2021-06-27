<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *
 * Copyright (C) 2016-2021 Sascha Willems (www.saschawillems.de)
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

/** Manages filters from url parameters */
class FilterList
{

    public $filters = [];

    function __construct($filters)
    {
        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }
    }

    function addFilter($name)
    {
        $value = GET_sanitized($name);
        if (($value !== null) && (trim($value) != '')) {
            $this->filters[$name] = $value;
        }
    }

    function getFilter($name)
    {
        if (key_exists($name, $this->filters)) {
            $value = $this->filters[$name];
            if (trim($value) != '') {
                return $value;
            }
        }
        return null;
    }

    function hasFilter($name)
    {
        return (key_exists($name, $this->filters));
    }

    function hasFilters()
    {
        return (count($this->filters) > 0);
    }
}
