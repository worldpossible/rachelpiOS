<?php
/***********************************************************
 If 'Real-time Logging' is enabled, this script takes over to display latest logging data.
 Requesting fresh data from the JavaScript file 'real_ping.js' ,
 all new logging data will always been placed into <div id='realLogContainer'  />
 ***********************************************************/
    define("_SECURE",1);    // define secure constant

    $include_dir  = "../include";
    $settings_dir = "../settings";

    include "$settings_dir/database.php";
    include "$include_dir/commonfuncs.php";

    if ($dba_act == '1') {
        $db_con = db_connect($mysql_host1, $mysql_user1, $mysql_password1, $database1);
        $mysql_table_prefix = $mysql_table_prefix1;
    }

    if ($dba_act == '2') {
        $db_con = db_connect($mysql_host2, $mysql_user2, $mysql_password2, $database2);
        $mysql_table_prefix = $mysql_table_prefix2;
    }

    if ($dba_act == '3') {
        $db_con = db_connect($mysql_host3, $mysql_user3, $mysql_password3, $database3);
        $mysql_table_prefix = $mysql_table_prefix3;
    }

    if ($dba_act == '4') {
        $db_con = db_connect($mysql_host4, $mysql_user4, $mysql_password4, $database4);
        $mysql_table_prefix = $mysql_table_prefix4;
    }

    if ($dba_act == '5') {
        $db_con = db_connect($mysql_host5, $mysql_user5, $mysql_password5, $database5);
        $mysql_table_prefix = $mysql_table_prefix5;
    }

    $plus_nr = '';
    @include "".$settings_dir."/db".$dba_act."/conf_".$mysql_table_prefix.".php";
    if (!$plus_nr) {
        include "/settings/backup/Sphider-plus_default-configuration.php";
    }

    if ($debug == '0') {
        error_reporting(0);  //     suppress  PHP messages
    }

    set_time_limit (0);
    $template_dir = "../".$templ_dir."";
    $template_path = "$template_dir/$template";

    echo "
    <!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
        <html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
        <head>
         <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
         <title>Log File real-time output</title>
         <link rel='stylesheet' href='$template_path/adminstyle.css' media='screen' type='text/css' />
         <link rel='stylesheet' href='../$template_path/adminstyle.css' media='all' type='text/css' />
         <meta http-equiv='cache-control' content='no-cache'>
         <meta http-equiv='pragma' content='no-cache'>
         <script type='text/javascript' src='real_ping.js'></script>
        </head>
        <body onload='process()'>
         <div class='submenu cntr y3'>Sphider-plus v.$plus_nr - Real-time Logging.
         <br /><br />
         Update every $refresh seconds.</div>
         <div id='realLogContainer'  />
      </body>
    </html>
        ";

?>

