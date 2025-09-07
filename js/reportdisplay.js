/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2025 by Sascha Willems (www.saschawillems.de)
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

$(document).ready(
    function() {
        var tableNames = [
            'deviceextensions',
            'devicelayerextensions',
            'devicememoryheaps',
            'devicememorytypes',
            'devicesurfaceproperties',
            'deviceinstanceextensions',
            'deviceinstancelayers',
            'table_features_core_10',
            'table_features_core_11',
            'table_features_core_12',
            'table_features_core_13',
            'table_features_core_14',
            'table_properties_core_11',
            'table_properties_core_12',
            'table_properties_core_13',
            'table_properties_core_14',
            'table_deviceprofiles',
            'table_surfaceformats',
            'table_presentmodes'
        ];
        for (var i = 0, arrlen = tableNames.length; i < arrlen; i++) {
            if (typeof $('#' + tableNames[i]) != undefined) {
                $('#' + tableNames[i]).dataTable({
                    "pageLength": -1,
                    "paging": false,
                    "order": [],
                    "searchHighlight": true,
                    "bAutoWidth": false,
                    "sDom": 'flpt',
                    "deferRender": true,
                    "processing": true
                });
            }
        }

        // Grouped tables
        tableNames = [
            'table_device',
            'table_properties_core_10',
            'table_features_extensions',
            'table_properties_extensions',
            'deviceproperties',
            'deviceproperties_extensions',
            'devicememory'
        ];

        // Device properties table with grouping
        for (var i = 0, arrlen = tableNames.length; i < arrlen; i++) {
            if (typeof $('#' + tableNames[i]) != undefined) {
                $('#' + tableNames[i]).dataTable({
                    "pageLength": -1,
                    "paging": false,
                    "order": [],
                    "columnDefs": [{
                        "visible": false,
                        "targets": 2
                    }],
                    "searchHighlight": true,
                    "bAutoWidth": false,
                    "sDom": 'flpt',
                    "deferRender": true,
                    "processing": true,
                    "drawCallback": function(settings) {
                        var api = this.api();
                        var rows = api.rows({
                            page: 'current'
                        }).nodes();
                        var last = null;
                        api.column(2, {
                            page: 'current'
                        }).data().each(function(group, i) {
                            if (last !== group) {
                                $(rows).eq(i).before(
                                    '<tr><td colspan="2" class="group">' + group + '</td></tr>'
                                );
                                last = group;
                            }
                        });
                    }
                });
            }
        }

        // Feature tables
        tableNames = [
            'deviceformats_linear',
            'deviceformats_optimal',
            'deviceformats_buffer',
            'devicequeues'
        ];
        for (var i = 0, arrlen = tableNames.length; i < arrlen; i++) {
            $('#' + tableNames[i]).dataTable({
                "pageLength": -1,
                "paging": false,
                "order": [],
                "searchHighlight": true,
                "bAutoWidth": false,
                "sDom": 'flpt',
                "deferRender": true,
                "processing": true,
                "ordering": true,
                "fixedHeader": {
                    "header": true,
                    "headerOffset": 50
                },
            });
        }

        // Extended features table with grouping
        $('#extended_features').dataTable({
            "pageLength": -1,
            "paging": false,
            "order": [],
            "columnDefs": [{
                "visible": false,
                "targets": 2
            }],
            "searchHighlight": true,
            "bAutoWidth": false,
            "sDom": 'flpt',
            "deferRender": true,
            "processing": true,
            "drawCallback": function(settings) {
                var api = this.api();
                var rows = api.rows({
                    page: 'current'
                }).nodes();
                var last = null;
                api.column(2, {
                    page: 'current'
                }).data().each(function(group, i) {
                    if (last !== group) {
                        $(rows).eq(i).before(
                            '<tr><td colspan="2" class="group">' + group + '</td></tr>'
                        );
                        last = group;
                    }
                });
            }
        });

        // Extended properties table with grouping
        $('#extended_properties').dataTable({
            "pageLength": -1,
            "paging": false,
            "order": [],
            "columnDefs": [{
                "visible": false,
                "targets": 2
            }],
            "searchHighlight": true,
            "bAutoWidth": false,
            "sDom": 'flpt',
            "deferRender": true,
            "processing": true,
            "drawCallback": function(settings) {
                var api = this.api();
                var rows = api.rows({
                    page: 'current'
                }).nodes();
                var last = null;
                api.column(2, {
                    page: 'current'
                }).data().each(function(group, i) {
                    if (last !== group) {
                        $(rows).eq(i).before(
                            '<tr><td class="group" colspan="2">' + group + '</td></tr>'
                        );
                        last = group;
                    }
                });
            }
        });

    });

$(function() {
    var a = document.location.hash;
    if (a) {
        // Nested tabs, need to show parent tab too
        if ((a === '#features_core') || (a === '#features_extensions')) {
            $('.nav a[href=\\#features]').tab('show');
        }
        if ((a === '#properties_core') || (a === '#properties_extensions')) {
            $('.nav a[href=\\#properties]').tab('show');
        }
        if ((a === '#formats_linear') || (a === '#formats_optimal') || (a === '#formats_buffer')) {
            $('.nav a[href=\\#formats]').tab('show');
        }
        if ((a === '#instanceextensions') || (a === '#instancelayers')) {
            $('.nav a[href=\\#instance]').tab('show');
        }
        if ((a === '#surfaceproperties') || (a === '#surfaceformats') || (a === '#presentmodes')) {
            $('.nav a[href=\\#surface]').tab('show');
        }
        // @todo: jump to feature/props table (e.g. 1.3)
        $('.nav a[href=\\' + a + ']').tab('show');
    }

    $('a[data-toggle="tab"]').on('show.bs.tab', function(e) {
        window.location.hash = e.target.hash;
    });
});