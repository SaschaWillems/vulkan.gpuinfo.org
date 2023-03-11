<?php

/** 		
 *
 * Vulkan hardware capability database back-end
 *	
 * Copyright (C) 2016-2023 by Sascha Willems (www.saschawillems.de)
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

/** Manual report submission page for platforms without GUI or internet connection */

include 'pagegenerator.php';

PageGenerator::header("Manual report upload");

?>

<script>
    async function uploadFile() {
        let progressArea = document.getElementById("progress-area");
        progressArea.style.display = 'block';
        let formData = new FormData();
        formData.append("data", fileupload.files[0]);
        const response = await fetch('/api/v3/uploadreport.php', {
            method: "POST",
            body: formData
        });
        progressArea.style.display = 'none';
        let resultArea = document.getElementById("result-area");
        resultArea.style.display = 'block';
        const status = await response.status;
        const text = await response.text();
        resultArea.className = 'alert alert-info'
        resultArea.innerText = text;
        if (text.trim() == 'res_uploaded') {
            resultArea.innerText = 'Report uploaded successfully';
            resultArea.className = 'alert alert-success'
        }
        if (text.indexOf('already present') > 0) {
            resultArea.className = 'alert alert-warning'
        }
    }

    $(document).ready(function() {
        document.getElementById("fileupload").onchange = function() {
            document.getElementById("upload-file-info").innerHTML = this.files[0].name;
            document.getElementById("upload-button").style.display = 'block';
        };
    });
</script>

<center>
    <div class='header'>
        <h4>Manual report upload</h4>
    </div>

    <div>
        <div class="tablediv" style="max-width: 512px; margin-top: 15px;">
            <div>
                Select a report for manual submission<br />
                The file must be a valid JSON file exported from the UI or command line version of the Vulkan Hardware Capability Viewer
            </div>
            <div id="upload-area" style="margin-top: 20px;">
                <div class="file-input">
                    <input type="file" id="fileupload" class="fileupload" name="fileupload" accept="application/JSON">
                    <label for="fileupload">
                        Select file
                        <p class="file-name"></p>
                    </label>
                    <p id="upload-file-info"></p>
                </div>
                <div class="file-input">
                    <button id="upload-button" class="file-input" onclick="uploadFile()" style="margin-top: 10px; display:none; ">Upload </button>
                </div>
            </div>
            <div id="progress-area" style="display:none; margin-top: 20px; margin-bottom: 0px">Uploading...</div>
            <div id="result-area" class="alert alert-info" style="display:none; margin-top: 20px; margin-bottom: 0px"></div>
        </div>
    </div>
</center>

<?php PageGenerator::footer(); ?>
</body>

</html>