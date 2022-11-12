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

var comparerUrl = 'api/internal/devicecomparer.php',
compareDevices = [];

function clearCompare() {
    data =  {'action': 'clear' };
    $.post(comparerUrl, data, function (response) {
        displayCompare(null);
    });
};

function removeFromCompare(name) {
    data =  {'action': 'remove', 'devicename': name };
    $.post(comparerUrl, data, function (response) {
        displayCompare(response);
    });	
}

function addToCompare(devicename, ostype) {
    data = {'action': 'add', 'devicename': devicename, 'ostype': ostype};
    $.post(comparerUrl, data, function (response) {
        displayCompare(response);
    });
}	

function displayCompare(data) {
    elem = $('#compare-info');
    div = $('#compare-div'); 
    html = '';
    arr = JSON.parse(data);
    compareDevices = [];
    if (Array.isArray(arr)) {
        html = '';
        for (var i = 0; i < arr.length; i++) {
            var element = arr[i];
            var last = (i == arr.length - 1);
            var ostypes = ['Windows', 'Linux', 'Android', 'macOS', 'iOS'];
            var osname = (element.ostype !== null) ? ostypes[element.ostype] : 'All';
            html += element.name + ' (' + osname + ') <span onClick="removeFromCompare(\'' + element.name + '\');" class="glyphicon glyphicon-button glyphicon-trash report-remove-icon"></span> ' + (last ? '' : '- ');
            compareDevices.push(element);
        }
    }
    elem.html(html);
    compareDevices.length > 0 ? div.show() : div.hide();			
}

function compare() {
    var url = 'compare.php?devices[]=';
    for (var i = 0; i < compareDevices.length; i++) {
        if (i > 0) {
            url += '&devices[]=';
        }
        var ostypes = ['windows', 'linux', 'android', 'macos', 'ios'];
        let ostype = compareDevices[i].ostype !== null ? ostypes[compareDevices[i].ostype] : 'all';
        url += compareDevices[i].name;
        url += '&os=' + ostype;
    }
    location.href = url;
}