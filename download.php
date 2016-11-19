<?php 	
	/* 		
		*
		* Vulkan hardware capability database server implementation
		*	
		* Copyright (C) 2016 by Sascha Willems (www.saschawillems.de)
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
	include './header.inc';	
?>

<div id='reportdiv'>	   
	<div class="panel panel-default">
		<div class="panel-body" style="margin-left:50px; width:65%px;">    
			<div class="page-header">
				<h2>Downloads</h2>
			</div>
			<div>
				The Vulkan Hardware Capability Viewer is open sourced, you can always build the most recent version yourself using the sources from <a href="https://github.com/SaschaWillems/VulkanCapsViewer">https://github.com/SaschaWillems/VulkanCapsViewer</a>.<br>
			</div>
			<div class="page-header">
				<h3>Changelog</h3>
				<h4>Beta 1.2 - 2016-11-19</h4>
				<ul>
					<li><b>New feature: </b> Added support for os-specific surface capabilities</li>
				</ul>
				<h4>Beta 1.1 - 2016-04-03</h4>
				<ul>
					<li><b>Fixed : </b>Memory type flags now displayed correct</li>
					<li><b>Fixed : </b>Assign queue priorities (fixes potential crashes)</li>
					<li>Display and upload API driver version (integer)</li>
				</ul>
			</div>
			<div class="page-header">
				<h3>Windows</h3>
				<ul>
					<li><a href="downloads/vulkancapsviewer_1_2_win64.zip">Beta 1.2 (64-Bit)</a></li>
				</ul>
			</div>
			<div>
			</div>
			<div class="page-header">
				<h3>Linux</h3>
				<ul>
					<li><a href="downloads/vulkancapsviewer_1_2_linux64.tar.gz">Beta 1.2 (64-Bit)</a></li>
				</ul>
			</div>
			<div>
			</div>
			<div class="page-header">
				<h3>Android</h3>								
				<ul>
					<li><a href="https://play.google.com/store/apps/details?id=de.saschawillems.vulkancapsviewer">Google Playstore entry</a></li>
					<li>APKs for manual installation
					<ul>
						<li><a href="downloads/vulkancapsviewer_1_2_arm.apk">ARM(v7) Beta 1.2</a></li>
						<li><a href="downloads/vulkancapsviewer_1_2_x86.apk">x86 Beta 1.2</a></li>
					</ul>
					</li>
				</ul>				
			</div>
		</div>    
	</div>
</div>

<?php
	include './footer.inc';
?>

</body>
</html>

