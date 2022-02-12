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

?>
<table id='table_device' class='table table-striped table-bordered table-hover responsive' style='width:100%;'>
	<thead>
		<tr>
			<td class='caption'>Property</td>
			<td class='caption'>Value</td>
			<td class='caption'>Group</td>
		</tr>
	</thead>
	<tbody>
		<?php

		$device_info_field_aliases = [
			'devicename' => 'Name',
			'displayname' => 'Display Name',
			'devicetype' => 'Type',
			'apiversion' => 'API Version',
			'osname' => 'Name',
			'osarchitecture' => 'Architecture',
			'osversion' => 'Version'
		];

		try {
			$data = $report->fetchDeviceInfo();
			$group = 'Device';
			foreach ($data[0] as $key => $value) {
				if ($value == "") {
					continue;
				}
				$display_key = $key;
				$display_value = $value;
				switch ($key) {
					case 'driverversion':
					case 'vendorid':
					case 'deviceid':
					case 'pipelineCacheUUID':
						continue 2;
						break;
					case 'displayname':
						if ($value == $data[0]['devicename']) {
							continue 2;
						}
						break;
					case 'osname':
						$group = 'Platform';
						$value = ucfirst($value);
						break;
					case 'submitter':
						$key = 'Submitted by';
						$value = '<a href="listreports.php?submitter=' . $value . '">' . $value . '</a>';
						$group = 'Report';
						break;
					case 'submissiondate':
						$key = 'Submitted at';
						break;
					case 'devicename':
						$value = '<a href="listreports.php?devicename=' . $value . '">' . $value . '</a>';
						break;
					case 'displayname':
						$value = '<a href="listreports.php?displayname=' . $value . '">' . $value . '</a>';
						break;
					case 'driverversionraw':
						$key = 'Driver version';
						$value = getDriverVerson($value, $data[0]['driverversion'], $data[0]['vendorid'], $data[0]['osname']);
						break;
					case 'lastupdate':
						$key = 'Last update at';
						$value = '<a href=# data-toggle="modal" data-target="#modal_report_history">' . $value . '</a>';
						break;
					case 'profile':
						include 'profile_download.php';
						continue 2;
						break;
				}
				if (array_key_exists($key, $device_info_field_aliases)) {
					$key = $device_info_field_aliases[$key];
				}
				echo "<tr><td class='subkey'>" . ucfirst($key) . "</td><td>$value</td><td>$group</td></tr>";
			}

			// Platform details (if available)
			if ($report->flags->has_platform_details) {
				$data = $report->fetchPlatformDetails();
				foreach ($data as $row) {
					echo "<tr><td class='subkey'>" . $row['name'] . "</td><td>" . $row['value'] . "</td><td>Platform details</td></tr>";
				}
			}
		} catch (Exception $e) {
			die('Error while fetching report properties');
			DB::disconnect();
		}
		?>
	</tbody>
</table>