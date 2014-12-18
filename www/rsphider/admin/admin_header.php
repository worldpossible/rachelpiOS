<?php

    set_time_limit (0);

    $database1  = '';
    $plus_nr    = '';

    $tmp_folder     = "/tmp";
    $include_dir    = "../include";
    $settings_dir   = "../settings";
    $converter_dir  = "../converter";
    $language_dir   = "../languages";
    $image_dir      = "$include_dir/images";
    $textcache_dir  = "$include_dir/textcache";
    $mediacache_dir = "$include_dir/mediacache";

    include "$settings_dir/database.php";
    if (!$database1) {
        echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
    <html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
    <html>
        <header>
            <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
            <title>Sphider-plus administrator warning</title>
            <meta http-equiv='X-UA-Compatible' content='IE=9' />
            <link href='../templates/html/sphider-plus.ico' rel='shortcut icon' type='image/x-icon' />
        </header>
        <body>
            <br /><br />
            <div style=\"text-align:center;\">
                <strong>Attention:</strong> Unable to load the database connfiguration file.<br />
                Pleaase reinstall Sphider-plus by using the original scripts as per download.<br />
                <br /><br />
            </div>
        </body>
    </html>
                ";
        die ();
    }

    //      get active database for Admin
    if ($dba_act == '1') {
        $db_con             = adb_connect($mysql_host1, $mysql_user1, $mysql_password1, $database1) ;
        $database           = $database1;
        $mysql_table_prefix = $mysql_table_prefix1;
    }

    if ($dba_act == '2') {
        $db_con             = adb_connect($mysql_host2, $mysql_user2, $mysql_password2, $database2) ;
        $database           = $database2;
        $mysql_table_prefix = $mysql_table_prefix2;
    }

    if ($dba_act == '3') {
        $db_con             = adb_connect($mysql_host3, $mysql_user3, $mysql_password3, $database3) ;
        $database           = $database3;
        $mysql_table_prefix = $mysql_table_prefix3;
    }

    if ($dba_act == '4') {
        $db_con             = adb_connect($mysql_host4, $mysql_user4, $mysql_password4, $database4) ;
        $database           = $database4;
        $mysql_table_prefix = $mysql_table_prefix4;
    }

    if ($dba_act == '5') {
        $db_con             = adb_connect($mysql_host5, $mysql_user5, $mysql_password5, $database5) ;
        $database           = $database5;
        $mysql_table_prefix = $mysql_table_prefix5;
    }

    $default = '';
    @include "".$settings_dir."/db".$dba_act."/conf_".$mysql_table_prefix.".php";
    if (!$plus_nr) {
        include "./settings/backup/Sphider-plus_default-configuration.php";
        $default = '1';
    }

    include "$include_dir/commonfuncs.php";

    if ($debug == '0') {
        if (function_exists("ini_set")) {
            ini_set("display_errors", "0");
        }
        error_reporting(0);  //     suppress  PHP messages
    } else {
        //error_reporting (E_ALL);    //  use this for script debugging
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE & ~E_STRICT);

    }

    //  check if multibyte functions are available
    //  this check is required only for first call of admin.php
    //  later on this check is also performed by configset.php together with a warning message
    $mb = '';
    if (function_exists('mb_internal_encoding')) {
        if(function_exists('mb_stripos')) {
            $mb = '1';
        }
    }
    if ($mb != 1) {
        $mb = '0';
    }

    $template_dir   = "../".$templ_dir."";
    $template_path  = "$template_dir/$template";

    //require_once('phpSecInfo/PhpSecInfo.php');        //   (might not work on shared hosting systems)
    include("geoip.php");

    echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
    <html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
        <head>
            <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
            <meta http-equiv='expires' content='0'>
            <meta http-equiv='pragma' content='no-cache'>
            <title>Sphider-plus administrator tools</title>
            <meta http-equiv='X-UA-Compatible' content='IE=9' />
            <link href='../templates/html/sphider-plus.ico' rel='shortcut icon' type='image/x-icon' />
            <link rel='stylesheet' type='text/css' href='$template_path/adminstyle.css' />
            <script type='text/javascript' src='confirm.js'></script>
            <script type='text/javascript'>
                function JumpBottom() {
                    window.scrollTo(0,1000);
                }
            </script>
        </head>
        <body>
        ";

    include "auth.php";

    $php_vers = phpversion();
    if (preg_match('/^4\./', trim($php_vers)) == '1') {
        echo "<br />
                <div id='main'>
                <h1 class='cntr'>
                Sphider-plus. The PHP Search Engine
                </h1>
                    <div class='cntr warnadmin'>
                        <br />
                        <p>Your current PHP version is $php_vers</p>
                        <p>Sorry, but Sphider-plus v. $plus_nr requires PHP 5.x</p>
                        <br /><br />
                    </div>
                </div>
                </body>
                </html>
            ";
        die ('');
    }

    if ($default == '1') {
        echo "  <br />
                    <p class='warnadmin cntr'><br />
                    <strong>Attention:</strong> The configuration file for database <strong>$dba_act</strong> and the table set <strong>$mysql_table_prefix</strong> does not yet exist.<br /><br />
                    Alternatively using the Sphider-plus default configuration.<br />
                    <br /><br />
                    </p>
                    <br />
                ";
    }

    // Database 1-5 connection
    function adb_connect($mysql_host, $mysql_user, $mysql_password, $database) {

        //error_reporting (E_ALL);    //  use this for script debugging
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE & ~E_STRICT);
        $db_con = '';
        $db_con = @new mysqli($mysql_host, $mysql_user, $mysql_password, $database);
        /* check connection */
        if ($db_con->connect_errno) {
            echo "<p>&nbsp;</p>
            <p><p class='warnadmin cntr'><br />&nbsp;No valid datbase found to start up.<br />&nbsp;Configure at least one database.<br /><br />
            <p>&nbsp;</p>
            ";

        }
       
        if (!$db_con->connect_errno) {
            /* define character set to utf8 */
            if (!$db_con->set_charset("utf8")) {
                printf("Error loading character set utf8: %s\n", $db_con->error);

                /* Print current character set */
                $charset = $db_con->character_set_name();
                printf ("<br />Current character set is: %s\n", $charset);

                $db_con->close();
                exit;
            }
        }      
        return ($db_con);
        
    }

?>
