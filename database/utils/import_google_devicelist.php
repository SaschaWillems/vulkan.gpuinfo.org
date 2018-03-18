<?php

    // Import script for reading the supported device list from google into a database mapping table used to read retail names for mobile devices
    // Device list: https://support.google.com/googleplay/android-developer/answer/6154891?hl=en
    // Note: List needs to be converted to UTF-8 first

	include './dbconfig.php';
    DB::connect();

    echo "Starting import...<br>";

    $count = 0;

    $conn = DB::$connection_vk;

    $q = $conn->prepare("truncate table googledevicelist");
    $q->execute();

    $row = 1;
    if (($handle = fopen("supported_devices.csv", "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if ($row == 1) {
                $row++;
                continue;
            }
            $num = count($data);
            $row++;
            if (substr($data[1], 0, 2) == '\\x') {
                $data[1] = null;
            }
            for ($c=0; $c < $num; $c++) {
                echo $data[$c] . " ";
            }
            $params = [
                "rb" => $data[0], 
                "mn" => $data[1], 
                "d" => $data[2], 
                "m" => $data[3]
            ];
           
            $q = $conn->prepare("insert ignore into googledevicelist values (:rb, :mn, :d, :m)");
            $q->execute($params);
            echo "<br>";
            $count++;
        }
        fclose($handle);
    }

    echo "Imported ".$count." devices.";

    DB::disconnect();

    // Update reports that have been uploaded with devices more recent than the last table update:
    /*
    update reports set displayname = (select trim(concat(retailbranding, ' ', marketingname)) as ds from googledevicelist gdl where model = ((select value from deviceplatformdetails dpf where dpf.platformdetailid = 4 and dpf.reportid = id)) limit 1)
    where displayname is null and id in (select reportid from deviceplatformdetails where platformdetailid = 4)    
    */
?>