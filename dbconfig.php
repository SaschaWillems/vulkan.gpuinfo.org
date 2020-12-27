<?php
    include "dbsettings.php";

	$bgcolordef = '#FFFFFF';	    
	$bgcolorodd = '#EFEFEF';	 	
	$bgcolorhead = '#CFCFCF';	 		
	
    $db_link = false;
    
    function dbConnect()
    {
        global $db_link;
        if ($db_link) {
			return $db_link;
		}
        $db_link = mysql_connect(DBHOST, DBUSER, DBPASSWORD) or die('Could not connect to database');
        mysql_select_db(DBNAME, $db_link) or die('Database selection failed');
        return $db_link;
    }
    
    function dbDisconnect()
    {
        global $db_link;
        if(!$db_link) {
            mysql_close($db_link);
		}
        $db_link = false;
    }	

    // PDO
    class DB {
        public static $connection = null;

        public static function connect() {
            $DBNAME = DBNAME;
            $DBHOST = DBHOST;
            try {
                DB::$connection = new PDO("mysql:dbname=$DBNAME;host=$DBHOST", DBUSER, DBPASSWORD);
                DB::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                DB::$connection = null;
                die($e->getMessage());
            }                
        }
        public static function disconnect() {
            DB::$connection = null;
        }
        public static function getCount($statement, $params = null) {
            $stmnt = DB::$connection->prepare($statement);
            $stmnt->execute($params);				
            return $stmnt->fetchColumn();
        }        
    }        
?>