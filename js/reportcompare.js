/** 		
 *
 * OpenCL hardware capability database server implementation
 *	
 * Copyright (C) 2021-2022 by Sascha Willems (www.saschawillems.de)
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

var comparerUrl = 'api/internal/reportcomparer.php',
compareIds = [];

function clearCompare() {
    data =  {'action': 'clear' };
    $.post(comparerUrl, data, function (response) {
        displayCompare(null);
    });
};

function removeFromCompare(reportid) {
    data =  {'action': 'remove', 'reportid': reportid };
    $.post(comparerUrl, data, function (response) {
        displayCompare(response);
    });		
}

function addToCompare(reportid, reportname) {
    data = {'action': 'add', 'reportid': reportid, 'reportname': reportname};
    $.post(comparerUrl, data, function (response) {
        displayCompare(response);
    });
}

function displayCompare(data) {
    elem = $('#compare-info');
    div = $('#compare-div'); 
    html = '';
    arr = JSON.parse(data);
    compareIds = [];
    if (Array.isArray(arr)) {
        html = '';
        for (var i = 0; i < arr.length; i++) {
            var element = arr[i];
            var last = (i == arr.length - 1);
            html += element.name + ' <span onClick="removeFromCompare(' + element.id + ');" class="glyphicon glyphicon-button glyphicon-trash report-remove-icon"></span> ' + (last ? '' : '- ');
            compareIds.push(element.id);
        }
    }
    elem.html(html);
    compareIds.length > 0 ? div.show() : div.hide();			
}

function compare() {
    location.href = 'compare.php?reports=' + compareIds.join();
}