<?php

/******************************************************
 This script updates the columns click_counter and last_click in table 'media'
 after a user clicked  a media link in the result listing.
 *******************************************************/


    //error_reporting (E_ALL);    //  use this for script debugging
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE & ~E_STRICT);
    $url        = '';
    $query      = '';
    $db         = '0';
    $prefix     = '';
    $client_ip  = '';

    $url        = trim(substr($_GET['url'], 0, 1024));
    $query      = trim(substr($_GET['query'], 0, 100));
    $db         = trim(substr($_GET['db'], 0, 1));
    $prefix     = trim(substr($_GET['prefix'], 0, 20));
    $client_ip  = trim(substr($_GET['client_ip'], 0, 255));

    $url    = str_replace("-_-", "&", $url);   //      decrypt the & character
    $url    = str_replace("_-_", "+", $url);   //      decrypt the + character
    $time   = time();

    header("Location: $url");       //  this is what the user really wants to get when clicking the object
    //  Okay, we will let him go. But also we will store the destination.

    define("_SECURE",1);            // define secure constant

    $include_dir  = "../include";
    $settings_dir = "../settings";
    include "$include_dir/commonfuncs.php";
    include "$settings_dir/database.php";

    $prefix = addslashes($prefix);

    //      if requested by Search-form, overwrite default db number
    if ($db > 0 && $db <= 5) {
        $dbu_act = $db;
    }

    //      if requested by Search-form, overwrite default table prefix
    if ($prefix != 0 ) {
        $mysql_table_prefix = $prefix;

    }

    //      get active database
    if ($dbu_act == '1') {
        $db_con = db_connect($mysql_host1, $mysql_user1, $mysql_password1, $database1);
        if ($prefix != 0 ) {    //      if requested by Search-form, overwrite default table prefix
            $mysql_table_prefix = $prefix;
        } else {
            $mysql_table_prefix = $mysql_table_prefix1;
        }
    }

    if ($dbu_act == '2') {
        $db_con = db_connect($mysql_host2, $mysql_user2, $mysql_password2, $database2);
        if ($prefix != 0 ) {    //      if requested by Search-form, overwrite default table prefix
            $mysql_table_prefix = $prefix;
        } else {
            $mysql_table_prefix = $mysql_table_prefix2;
        }
    }

    if ($dbu_act == '3') {
        $db_con = db_connect($mysql_host3, $mysql_user3, $mysql_password3, $database3);
        if ($prefix != 0 ) {    //      if requested by Search-form, overwrite default table prefix
            $mysql_table_prefix = $prefix;
        } else {
            $mysql_table_prefix = $mysql_table_prefix3;
        }
    }

    if ($dbu_act == '4') {
        $db_con = db_connect($mysql_host4, $mysql_user4, $mysql_password4, $database4);
        if ($prefix != 0 ) {    //      if requested by Search-form, overwrite default table prefix
            $mysql_table_prefix = $prefix;
        } else {
            $mysql_table_prefix = $mysql_table_prefix4;
        }
    }

    if ($dbu_act == '5') {
        $db_con = db_connect($mysql_host5, $mysql_user5, $mysql_password5, $database5);
        if ($prefix != 0 ) {    //      if requested by Search-form, overwrite default table prefix
            $mysql_table_prefix = $prefix;
        } else {
            $mysql_table_prefix = $mysql_table_prefix5;
        }
    }

    $plus_nr = '';
    @include "".$settings_dir."/db".$dbu_act."/conf_".$mysql_table_prefix.".php";
    if (!$plus_nr) {
        include "/admin/settings/backup/Sphider-plus_default-configuration.php";
    }

    $url                = convert_url($db_con->real_escape_string($url));
    $query              = $db_con->real_escape_string($query);
    $client_ip          = $db_con->real_escape_string($client_ip);
    $mysql_table_prefix = $db_con->real_escape_string($mysql_table_prefix);

    //  try to update the link in actual database
    $sql_query = "SELECT * from ".$mysql_table_prefix."media  where media_link = '$url' LIMIT 1";
    $result = $db_con->query($sql_query);
    if ($debug && $db_con->errno) {
        $err_row = __LINE__-2;
        printf("<p><span class='red'>&nbsp;MySQL failure: %s&nbsp;\n<br /></span></p>", $db_con->error);
        if (__FUNCTION__) {
            printf("<p><span class='red'>&nbsp;Found in script: ".__FILE__."&nbsp;&nbsp;row: $err_row&nbsp;&nbsp;in function():&nbsp;".__FUNCTION__."&nbsp;<br /></span></p>");
        } else {
            printf("<p><span class='red'>&nbsp;Found in script: ".__FILE__."&nbsp;&nbsp;row: $err_row&nbsp;<br /></span></p>");
        }
        printf("<p><span class='red'>&nbsp;Script execution aborted.&nbsp;<br /></span>");
        printf("<p><strong>Invalid query string, which caused the SQL error:</strong></p>");
        echo   "<p> $sql_query </p>";
        exit;
    }

    if ($result->num_rows) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $last_click = $row['last_click'];           //  get time of last click
        if ($last_click+$click_wait < $time) {      //  prevent  promoted clicks, else remember this click
            $sql_query = "UPDATE ".$mysql_table_prefix."media set click_counter=click_counter+1, last_click='$time', last_query='$query', ip='$client_ip' where media_link = '$url' LIMIT 1";
            $db_con->query($sql_query);
            if ($debug && $db_con->errno) {
                $err_row = __LINE__-2;
                printf("<p><span class='red'>&nbsp;MySQL failure: %s&nbsp;\n<br /></span></p>", $db_con->error);
                if (__FUNCTION__) {
                    printf("<p><span class='red'>&nbsp;Found in script: ".__FILE__."&nbsp;&nbsp;row: $err_row&nbsp;&nbsp;in function():&nbsp;".__FUNCTION__."&nbsp;<br /></span></p>");
                } else {
                    printf("<p><span class='red'>&nbsp;Found in script: ".__FILE__."&nbsp;&nbsp;row: $err_row&nbsp;<br /></span></p>");
                }
                printf("<p><span class='red'>&nbsp;Script execution aborted.&nbsp;<br /></span>");
                printf("<p><strong>Invalid query string, which caused the SQL error:</strong></p>");
                echo   "<p> $sql_query </p>";
                exit;
            }
        }
    }

    exit ('');      //  Good-bye, we've got your media click.

?>