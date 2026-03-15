<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *
 * Copyright (C) 2016-2026 Sascha Willems (www.saschawillems.de)
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

    public function __construct($filters)
    {
        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }
    }

    public function addFilter($name)
    {
        $value = GET_sanitized($name);
        if (($value !== null) && (trim($value) != '')) {
            $this->filters[$name] = $value;
        }
    }

    public function getFilter($name)
    {
        if (key_exists($name, $this->filters)) {
            $value = $this->filters[$name];
            if (trim($value) != '') {
                return $value;
            }
        }
        return null;
    }

    public function hasFilter($name)
    {
        return (key_exists($name, $this->filters));
    }

    public function hasFilters()
    {
        return (count($this->filters) > 0);
    }

    private function addOption($caption, $label, $options) {
        echo "<div>$caption: <select name='$label' id='$label' class='form-control' onchange='this.form.submit()'></div>";
        foreach ($options as $value => $text) {
            $selected = ($this->hasFilter($label) && $this->getFilter($label) == $value) ? 'selected' : '';
            echo "<option value=\"$value\" $selected>$text</option>";
        };
        echo "</select>";
    }

    /** Adds HTML select elements for age and api version */
    public function addDefaultFilterOptions()
    {
        echo "<form method='get'>";
        $this->addOption('Age', 'age', [
            'recent' => 'Recent (1y)',
            'historic' => 'Historic (All)'
        ]);
        $this->addOption('Versions', 'apiversion', [
            'all' => 'All Vulkan versions',
            '1.1' => 'Vulkan 1.1 and up',
            '1.2' => 'Vulkan 1.2 and up',
            '1.3' => 'Vulkan 1.3 and up',
            '1.4' => 'Vulkan 1.4 and up'
        ]);			        
        // Some filters can't be explictly set by the user, but need to be persisted, so we pass them as hidden inputs
        if ($this->hasFilter('platform')) {
            echo "<input type='hidden' name='platform' value='".$this->getFilter('platform')."' />";
        }
        if ($this->hasFilter('namefilter')) {
            echo "<input type='hidden' name='namefilter' value='".$this->getFilter('namefilter')."' />";
        }
        echo "</form>";
    }

    /** Applies the currently set default filter options (age, api, platform) to the given url */
    public function applyDefaultUrlFilter($url) {
        $filters = [];
        if ($this->hasFilter('age')) {
            $filters[] = "age=".$this->getFilter('age');
        }        
        if ($this->hasFilter('platform')) {
            $filters[] = "platform=".$this->getFilter('platform');
        }
        // @todo: rename?
        if ($this->hasFilter('apiversion')) {
            $filters[] = "minapiversion=".$this->getFilter('apiversion');
        }
        if (sizeof($filters) > 0) {
            $filter_string = implode('&', $filters);
            if (strpos($url, '?') === false) {
                return $url.'?'.$filter_string;
            } else {
                return $url.'&'.$filter_string;
            }
        } else {
            return $url;
        }
    }

}
