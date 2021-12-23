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

/** Small wrapper for drawing a chart uusing ApexCharts.js */
class Chart {
    const colors = [ '#3366CC', '#DC3912', '#FF9900', '#109618', '#990099', '#0099C6', '#DD4477', '#66AA00', '#B82E2E', '#316395', '#994499', '#22AA99', '#AAAA11', '#6633CC', '#E67300', '#8B0707', '#651067', '#329262', '#5574A6', '#3B3EAC', '#B77322', '#16D620', '#B91383', '#F4359E', '#9C5935', '#A9C413', '#2A778D', '#668D1C', '#BEA413', '#0C5922', '#743411'];
    
    public static function getColor($index) {
        return self::colors[$index % count(self::colors)];
    }

    public static function draw($labels, $series) {
        // Counts below a certain threshold will be grouped into a single "others" slice
        $total_count = array_sum($series);
        $lower_limit = $total_count * 0.0015;
        $others_count = 0;
        $chart_labels = [];
        $chart_series = [];
        $chart_colors = [];
        for ($i = 0; $i < count($labels); $i++) {        
            if ($series[$i] <= $lower_limit) {
                $others_count += $series[$i];
                continue;
            }
            $chart_labels[] = $labels[$i];
            $chart_series[] = $series[$i];
            $chart_colors[] = self::colors[$i % count(self::colors)];
        }
        if ($others_count > 0)  {
            $chart_labels[] = 'Others';
            $chart_series[] = $others_count;
            $chart_colors[] = '#CCC';
        }
        echo "
            var options = {
                chart: {
                    type: 'pie',
                    expandOnClick: false,
                    height: '400px',
                    width: '400px',
                    animations: {
                        enabled: false,
                    },
                },
                legend: {
                    show: false,
                },
                stroke: {
                    width: 1,
                },
                colors: ['".implode("','", $chart_colors)."'],
                labels: ['".str_replace('\\n', '<br/>', implode("','", $chart_labels))."'],
                series: [".implode(',', $chart_series)."]
            }
            var chart = new ApexCharts(document.getElementById('chart'), options)
            chart.render()
        ";
    }    
}
