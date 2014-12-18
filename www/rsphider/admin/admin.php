<?php
/********************************************
 * Sphider-plus version 3.2014a created 2014-03-11
 *
 * Based on original Sphider version 1.3.5
 * released: 2009-12-13
 * by Ando Saabas     http://www.sphider.eu
 *
 * This program is licensed under the GNU GPL by:
 * Rolf Kellner  [Tec]   tec@sphider-plus.eu
 * Original Sphider GNU GPL licence by:
 * Ando Saabas   ando(a t)cs.ioc.ee
 ********************************************/

    //error_reporting (E_ALL);    //  use this for script debugging  (see also .../admin/admin_header.php)

    define("_SECURE",1);            // define secure constant

    include "admin_header.php";     //  display header
    include "messages.php";         //  contains all messages for Admin backend
    include "$language_dir/en-language.php";

    $testfile       = "dummy.txt";                  //  dummy file for folder write tests
    $pdf_dummy      = "$converter_dir/dummy.pdf";   //  dummy file for PDF converter test
    $pdf_config     = "$converter_dir/xpdfrc";      //  configuration file for the PDF converter
    $cat_sel_all    = $sph_messages['all'];

    if ($mb == 1) {
        @mb_internal_encoding("UTF-8");     //  define standard charset for mb functions
    }

    $row        = '';
    $repeat     = '';
    $url        = '';
    $db_active  = '1';
    $cancel      = '';
    extract (getHttpVars());    //  get all the passed input variables

    //      create and test temporary folder
    if (!is_dir($tmp_dir)) {
        @mkdir("".$tmp_dir."", 0777);         //if not exist, try to create tmp folder
        if (!is_dir("./".$tmp_dir."")) {
            echo "  <br />
                        <p class='warnadmin cntr'><br />
                        Unable to create folder<span class='blue'> .../admin/".$tmp_dir."</span>.<br />
                        Sphider-plus will not be able to store any temporary file required during index procedure.
                        <br /><br /></p>
                    </div>
                  </body>
                </html>
                    ";
            die();
        }
    }

    $fp = @fopen("".$tmp_dir."/".$testfile."","w");    //  try to write into a testfile
    if(!is_writeable("./".$tmp_dir."/".$testfile."")) {
        echo "  <br />
                    <p class='warnadmin cntr'><br />
                    Temporary folder is not writable.<br />
                    Sphider-plus will not be able to store any temporary file required during index procedure.<br /><br />
                    chmod 777 the folder<span class='blue'> .../admin/".$tmp_dir."</span> under *nix operating systems..<br />
                    <br /><br /></p>
                </div>
              </body>
            </html>
                ";
        die();

    }
    fclose($fp);
    unlink("".$tmp_dir."/".$testfile."");   // remove testfile

    //  if not exists, create the file, which will contain all thread-id
    $fp = @fopen("".$tmp_dir."/thread_ids.txt","a+");
    @fclose($fp);

    //  if index procedure manually was aborted
    if ($cancel) {
        // remove the temp file, we don't need it any longer
        //  also the missing file will immediately abort all threads for multithreaded indexing
        unlink("".$tmp_dir."/thread_ids.txt");

        if ($multi_indexer > "1") {
            sleep(30);     //  wait until all multithreded indexer will be aborted
        }

        echo "<br />
        <div class='submenu'>&nbsp;</div>
        <p class='odrow cntr'><span class='bd'>Indaxation aborted!</span>
        <br />
            Database '".$database."' repaired and optimized.
        <br />
            Now containing all data until indexing was cancelled.
        </p>
        <br />
        ";

        $back = '';
        cleanKeywords($back);    //  delete all keywords not associated with any link
        cleanLinks($back);       // delete all links not associated with any site
        echo "<div class='submenu cntr'>&nbsp;</div>";
        // preventive repair the active database, as the index procedure was aborted.
        $flush  = 1;     //  in any case repair, optimize & flush the db after index was aborted
        $i      = (optimize($flush)-1);
    }

    if (!isset($f)) {
        $f=2;
    } else {
        $url    = str_replace("-_-", "&", $url);   //      decrypt the & character
        $url    = str_replace("_-_", "+", $url);   //      decrypt the + character
    }


    $site_funcs     = Array (22=> "default",21=> "default",4=> "default", 19=> "default", 1=> "default", 2 => "default", "add_site" => "default", 20=> "default", 28=> "default", 30=> "default", 40=> "default", 45=> "default", 50=> "default", 51=> "default", "edit_site" => "default", 5=>"default");
    $stat_funcs     = Array ("statistics" => "default",  "delete_log"=> "default");
    $settings_funcs = Array ("settings" => "default", 41=> "default");
    $index_funcs    = Array ("index" => "default");
    $clean_funcs    = Array ("clean" => "default", 15=>"default", 16=>"default", 17=>"default", 23=>"default");
    $cat_funcs      = Array (11=> "default", 10=> "default", "categories" => "default", "edit_cat"=>"default", "delete_cat"=>"default", "add_cat" => "default", 7=> "default");
    $database_funcs = Array ("database" => "default");

    echo "    <div id='tabs'>
              <ul>
        ";

    if ($stat_funcs[$f] ) {
        $stat_funcs[$f] = "selected";
    } else {
        $stat_funcs[$f] = "default";
    }

    if ($site_funcs[$f] ) {
        $site_funcs[$f] = "selected";
    }else {
        $site_funcs[$f] = "default";
    }

    if ($settings_funcs[$f] ) {
        $settings_funcs[$f] = "selected";
    } else {
        $settings_funcs[$f] = "default";
    }

    if ($index_funcs[$f] ) {
        $index_funcs[$f]  = "selected";
    } else {
        $index_funcs[$f] = "default";
    }

    if ($cat_funcs[$f] ) {
        $cat_funcs[$f]  = "selected";
    } else {
        $cat_funcs[$f] = "default";
    }

    if ($clean_funcs[$f] ) {
        $clean_funcs[$f]  = "selected";
    } else {
        $clean_funcs[$f] = "default";
    }

    if ($database_funcs[$f] ) {
        $database_funcs[$f]  = "selected";
    } else {
        $database_funcs[$f] = "default";
    }

    echo "        <li><a title='Manage Sites' href='admin.php?f=2' class='$site_funcs[$f]'>Sites</a></li>
                <li><a title='Manage Categories' href='admin.php?f=categories' class='$cat_funcs[$f]'>Categories</a></li>
                <li><a title='Indexing Options' href='admin.php?f=index' class='$index_funcs[$f]'>Index</a></li>
                <li><a title='Main Settings' href='admin.php?f=settings' class='$settings_funcs[$f]'>Settings</a></li>
                <li><a  name='head' title='Indexing Statistics' href='admin.php?f=statistics' class='$stat_funcs[$f]'>Statistics</a> </li>
                <li><a title='Memory and Database Cleaning Options' href='admin.php?f=clean' class='$clean_funcs[$f]'>Clean</a> </li>
                <li><a title='Display Database Contents' href='admin.php?f=database&amp;sel=1' class='$database_funcs[$f]'>Database</a></li>
                <li><a title='Close Admin backend' href='admin.php?f=24' class='default'>Log out</a></li>
              </ul>
            </div>
            <div id='main'>
        ";

    // Check for 'safe_mode' off
    if (function_exists('ini_get')) {
        if ((bool)ini_get('safe_mode')) {
            echo "<br />
                        <p class='warnadmin cntr'><br />
                        Attention: Sphider-plus does not work with your current PHP installation.
                        <br /><br /></p>
                        <p class='warnadmin cntr sml'>
                        Please switch off the <strong>'Safe Mode'</strong> in your php.ini file and restart your server.
                        <br /><br />
                        If the server is managed with Plesk control panel, then the PHP safe mode
                        <br /><br />
                        has to be turned off in Plesk, not in php.ini file.
                        <br /><br />
                        (CP -> Domains -> domain.tld -> Setup page. Look for safe_mode checkbox right from 'PHP support')
                        <br /><br /></p>
                        <br />
                ";
            die ;
        }
    }

    //  check for existing function iconv()
    if (!function_exists('iconv')) {
        echo "<br />
                    <p class='warnadmin cntr'><br />
                    Attention: Sphider-plus does not work with your current PHP installation.
                    <br /><br /></p>
                    <p class='warnadmin cntr sml'>
                    In order to support UTF-8, the module <strong>libiconv</strong> as part of the PHP libraries is mandatory.
                    <br /><br /></p>
                    <br />
            ";
        die ;
    }

    //  check whether ZIP function is available
    if (!function_exists('gzopen')) {
        echo "<br />
                <div class='cntr warnadmin sml'>
                    <br />
                    <strong>Attention:</strong>  Sphider-plus does not work with your current PHP installation.
                    <br />
                    <br />
                    An installed ZIP library (zlib) as part of the PHP environment is obligatory required.
                    <br /><br />
                    Currently gzopen(\$file) does not work.<br />
                    <br /><br />
                </div>
            ";
        die ;
    }

    //      get active database for Suggest URL User and look for new suggested URLs
    $add_url = '';
    $suggest = '';

    if ($db_count >= 1) {    
        $db_con = db_connect($mysql_host1, $mysql_user1, $mysql_password1, $database1);
        $mysql_table_prefix = $mysql_table_prefix1;

        $sql_query ="SELECT url FROM ".$mysql_table_prefix."addurl LIMIT 10";
        if ($result = $db_con->query($sql_query)) {
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

            if($result->num_rows){
                $add_url = '1';
            }
            /* free result set */

        }
    }

    if ($db_count >= 2) {
        $db_con = db_connect($mysql_host2, $mysql_user2, $mysql_password2, $database2);

        $mysql_table_prefix = $mysql_table_prefix2;
        $sql_query ="SELECT url FROM ".$mysql_table_prefix."addurl LIMIT 10";
        if ($result = $db_con->query($sql_query)) {
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

            if($result->num_rows){
                $add_url = '2';
            }
            /* free result set */

        }
    }

    if ($db_count >= 3) {
        $db_con = db_connect($mysql_host3, $mysql_user3, $mysql_password3, $database3);
        $mysql_table_prefix = $mysql_table_prefix3;

        $sql_query ="SELECT url FROM ".$mysql_table_prefix."addurl LIMIT 10";
        if ($result = $db_con->query($sql_query)) {
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

            if($result->num_rows){
                $add_url = '3';
            }
            /* free result set */

        }
    }

    if ($db_count >= 4) {
        $db_con = db_connect($mysql_host4, $mysql_user4, $mysql_password4, $database4);
        $mysql_table_prefix = $mysql_table_prefix4;

        $sql_query ="SELECT url FROM ".$mysql_table_prefix."addurl LIMIT 10";
        if ($result = $db_con->query($sql_query)) {
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

            if($result->num_rows){
                $add_url = '4';
            }
            /* free result set */

        }
    }

    if ($db_count == 5) {
        $db_con = db_connect($mysql_host5, $mysql_user5, $mysql_password5, $database5);
        $mysql_table_prefix = $mysql_table_prefix5;

        $sql_query ="SELECT url FROM ".$mysql_table_prefix."addurl LIMIT 10";
        if ($result = $db_con->query($sql_query)) {
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

            if($result->num_rows){
                $add_url = '5';
            }
            /* free result set */

        }
    }

    if ($add_url) {
        $suggest = 1;
        echo "    <br />
                <p class='warnadmin cntr' ><br />
                <strong>Attention:</strong> New suggested sites are waiting for approval in database ".$add_url."
                <br /><br />
                </p>
                <br />
                ";
    }

    //      rebuild the active database for Admin scripts
    $success = '';
    if ($dba_act == '1') {
        $db_con = db_connect($mysql_host1, $mysql_user1, $mysql_password1, $database1);        
        $sql_query ="SELECT DATABASE()";
        if ($result = $db_con->query($sql_query)) {
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
            $success = $result->fetch_array(MYSQLI_ASSOC);

        }
        $mysql_table_prefix = $mysql_table_prefix1;

        $sql_query ="SELECT url FROM ".$mysql_table_prefix."addurl LIMIT 10";
        if ($result = $db_con->query($sql_query)) {
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
            $tables = $result->field_count;
        }
    }

    if ($dba_act == '2') {
        $db_con = db_connect($mysql_host2, $mysql_user2, $mysql_password2, $database2);
        $sql_query ="SELECT DATABASE()";
        if ($result = $db_con->query($sql_query)) {
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
            $success = $result->fetch_array(MYSQLI_ASSOC);
        }

        $mysql_table_prefix = $mysql_table_prefix2;

        $sql_query ="SELECT url FROM ".$mysql_table_prefix."addurl LIMIT 10";
        if ($result = $db_con->query($sql_query)) {
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
            $tables = $result->field_count;
        }

    }

    if ($dba_act == '3') {
        $db_con = db_connect($mysql_host3, $mysql_user3, $mysql_password3, $database3);

        $sql_query ="SELECT DATABASE()";
        if ($result = $db_con->query($sql_query)) {
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
            $success = $result->fetch_array(MYSQLI_ASSOC);
        }

        $mysql_table_prefix = $mysql_table_prefix3;

        $sql_query ="SELECT url FROM ".$mysql_table_prefix."addurl LIMIT 10";
        if ($result = $db_con->query($sql_query)) {
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
            $tables = $result->field_count;
        }
    }

    if ($dba_act == '4') {
        $db_con = db_connect($mysql_host4, $mysql_user4, $mysql_password4, $database4);
        $sql_query ="SELECT DATABASE()";
        if ($result = $db_con->query($sql_query)) {
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
            $success = $result->fetch_array(MYSQLI_ASSOC);
        }

        $mysql_table_prefix = $mysql_table_prefix4;
        $sql_query ="SELECT url FROM ".$mysql_table_prefix."addurl LIMIT 10";
        if ($result = $db_con->query($sql_query)) {
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
            $tables = $result->field_count;
        }


    }

    if ($dba_act == '5') {
        $db_con = db_connect($mysql_host5, $mysql_user5, $mysql_password5, $database5);
        $sql_query ="SELECT DATABASE()";
        if ($result = $db_con->query($sql_query)) {
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
            $success = $result->fetch_array(MYSQLI_ASSOC);
        }


        $mysql_table_prefix = $mysql_table_prefix5;
        $sql_query ="SELECT url FROM ".$mysql_table_prefix."addurl LIMIT 10";
        if ($result = $db_con->query($sql_query)) {
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

            $tables = $result->field_count;
        }

    }

    //      if there was not found any active db,  jump to database configuration menu
    if (!$success || !$tables) {
        $f = 'database';
        $db_active = '';
    }

    if($tables && !$send2) {
        //  check whether table structure of currently active database is up to date
        //  $send2 is part of URL Backup utility, thus we need to bypass this test
        $sql_query ="SELECT ".$latest_field." FROM ".$mysql_table_prefix."".$latest_table." LIMIT 10";
        if ($result = $db_con->query($sql_query)) {
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
            $latest = $result->field_count;
        }

        if (!$latest) {
            echo "  <br />
                        <p class='warnadmin cntr'><br />
                        <strong>Attention:</strong> The table structure of database ".$dba_act." with table prefix: ".$mysql_table_prefix."<br />
                        is not up to date for this version of Sphider-plus (v.".$plus_nr.").<br /><br />
                        If available, please export your current URL list.<br />
                        Afterwards select 'Configure' in 'Database' menu and run:<br />
                        'Install all tables in database ".$dba_act."'.<br />
                        Finally import the URL list again and re-index all.
                        <br /><br />
                        </p>
                        <br />
                    ";

            $f  = 'database';
            $send2 = '';
        }
    }
    //      create and test the folder for sitemaps
    if (!is_dir($smap_dir)) {
        @mkdir("".$smap_dir."", 0777);                        //if not exist, try to create folder for sitemaps
        if (!is_dir("".$smap_dir."")) {
            echo "  <br />
                        <p class='warnadmin cntr'><br />
                        Unable to create folder<span class='blue'> .../admin/".$smap_dir."</span>.<br />
                        Sphider-plus will not be able to store any sitemap.
                        <br /><br /></p>
                    ";
        }
    }

    $fp = @fopen("".$smap_dir."/".$testfile."","w");    //  try to write into a testfile
    if(!is_writeable("".$smap_dir."/".$testfile."")) {
        echo "  <br />
                    <p class='warnadmin cntr'><br />
                    Folder for sitemaps is not writeable.<br />
                    Sphider-plus will not be able to store any sitemap.<br /><br />
                    chmod 777 the folder<span class='blue'> .../admin/".$smap_dir."</span> under *nix operating systems.<br />
                    <br /><br /></p>
                ";
    }
    fclose($fp);
    unlink("".$smap_dir."/".$testfile."");       // remove testfile

    //      create and test folder for text cache
    if (!is_dir("./".$textcache_dir."")) {
        @mkdir("./".$textcache_dir."", 0777);                        //if not exist, try to create folder for sitemaps
        if (!is_dir("./".$textcache_dir."")) {
            echo "  <br />
                        <p class='warnadmin cntr'><br />
                        Unable to create folder<span class='blue'> ".$textcache_dir."</span>.<br />
                        Sphider-plus will not be able to cache any text results.
                        <br /><br /></p>
                    ";
        }
    }

    $fp = @fopen("./".$textcache_dir."/".$testfile."","w");    //  try to write into a testfile
    if(!is_writeable("./".$textcache_dir."/".$testfile."")) {
        echo "  <br />
                    <p class='warnadmin cntr'><br />
                    Folder for text cache is not writeable.<br />
                    Sphider-plus will not be able to cache any text results.<br /><br />
                    chmod 777 the folder<span class='blue'> ".$textcache_dir."</span> under *nix operating systems.<br />
                    <br /><br /></p>
                ";
    }
    fclose($fp);
    unlink("".$textcache_dir."/".$testfile."");     // remove testfile

    //      create and test folder for media cache
    if (!is_dir("./".$mediacache_dir."")) {
        @mkdir("./".$mediacache_dir."", 0777);      //if not exist, try to create folder for sitemaps
        if (!is_dir("./".$mediacache_dir."")) {
            echo "  <br />
                        <p class='warnadmin cntr'><br />
                        Unable to create folder<span class='blue'> ".$mediacache_dir."</span>.<br />
                        Sphider-plus will not be able to cache any media results.
                        <br /><br /></p>
                    ";
        }
    }

    $fp = @fopen("./".$mediacache_dir."/".$testfile."","w");    //  try to write into a testfile
    if(!is_writeable("./".$mediacache_dir."/".$testfile."")) {
        echo "  <br />
                    <p class='warnadmin cntr'><br />
                    Folder for media cache is not writeable.<br />
                    Sphider-plus will not be able to cache any media results.<br /><br />
                    chmod 777 the folder<span class='blue'> ".$mediacache_dir."</span> under *nix operating systems.<br />
                    <br /><br /></p>
                ";
    }
    fclose($fp);
    unlink("".$mediacache_dir."/".$testfile."");    // remove testfile

    //      create and test log folder
    if (!is_dir($log_dir)) {
        @mkdir("$log_dir", 0777);             //if not exist, try to create tmp folder
        if (!is_dir("./".$log_dir."")) {
            echo "  <br />
                        <p class='warnadmin cntr'><br />
                        Unable to create folder<span class='blue'> .../admin/".$log_dir."</span>.<br />
                        Sphider-plus will not be able to store any log file during index procedure.
                        <br /><br /></p>
                    ";
        }
    }

    $fp = @fopen("".$log_dir."/".$testfile."","w");    //  try to write into a testfile
    if(!is_writeable("./".$log_dir."/".$testfile."")) {
        echo "  <br />
                    <p class='warnadmin cntr'><br />
                    Log folder is not writeable.<br />
                    Sphider-plus will not be able to store any log file during index procedure.<br /><br />
                    chmod 777 the folder<span class='blue'> .../admin/".$log_dir."</span> under *nix operating systems..<br />
                    <br /><br /></p>
                ";
    }
    fclose($fp);
    unlink("".$log_dir."/".$testfile."");       // remove testfile

    //      create and test folder for URL import/export
    $url_dir = "urls";
    if (!is_dir($url_dir)) {
        @mkdir("".$url_dir."", 0777);                        //if not exist, try to create folder for url import/export
        if (!is_dir("./".$url_dir."")) {
            echo "  <br />
                        <p class='warnadmin cntr'><br />
                        Unable to create folder<span class='blue'> .../admin/".$url_dir."</span>.<br />
                        Sphider-plus will not be able to import or export any URL list.
                        <br /><br /></p>
                    ";
        }
    }

    $fp = @fopen("".$url_dir."/".$testfile."","w");    //  try to write into a testfile
    if(!is_writeable("".$url_dir."/".$testfile."")) {
        echo "  <br />
                    <p class='warnadmin cntr'><br />
                    Folder for URL import/export is not writeable.<br />
                    Sphider-plus will not be able to import or export any URL list.<br /><br />
                    chmod 777 the folder<span class='blue'> .../admin/".$url_dir."</span> under *nix operating systems.<br />
                    <br /><br /></p>
                ";

    }
    fclose($fp);
    unlink("".$url_dir."/".$testfile."");       // remove testfile

    //  try to write into settings folder
    $fp = @fopen("".$settings_dir."/".$testfile."","w");
    if(!is_writeable("".$settings_dir."/".$testfile."")) {
        echo "  <br />
                    <p class='warnadmin cntr'><br />
                    The configuration file and the database settings are not writeable.<br />
                    Sphider-plus will not be able to store any Admin settings.<br /><br />
                    Under *nix operating systems chmod 777 the folder<span class='blue'> ".$settings_dir."/</span>,<br />
                    all sub-folder and all files in those folders.<br />
                    <br /><br /></p>
                ";
    }
    fclose($fp);
    unlink("".$settings_dir."/".$testfile."");       // remove testfile


    //  try to write into db1 settings folder (hopefully db2 - db5 subfolders will become writeable after warning for db1 folder . . .)
    $fp = @fopen("".$settings_dir."/db1/".$testfile."","w");
    if(!is_writeable("".$settings_dir."/db1/".$testfile."")) {
        echo "  <br />
                    <p class='warnadmin cntr'><br />
                    The configuration file and the database settings are not writable.<br />
                    Sphider-plus will not be able to store any Admin settings.<br /><br />
                    Under *nix operating systems chmod 777 the folder<span class='blue'> ".$settings_dir."/</span>,<br />
                    all sub-folder and all files in those folders.<br />
                    <br /><br /></p>
                ";
    }
    fclose($fp);
    unlink("".$settings_dir."/db1/".$testfile."");       // remove testfile

    //  try to write into folder of IDS logging
    $fp = @fopen("".$include_dir."/IDS/tmp/".$testfile."","w");
    if(!is_writeable("./".$include_dir."/IDS/tmp/".$testfile."")) {
        echo "  <br />
                    <p class='warnadmin cntr'><br />
                    The folder for logging the intrusion attempts is not writeable.<br />
                    Sphider-plus will not be able to log any attempts and the according IP's .<br /><br />
                    Under *nix operating systems chmod 777 the folder<span class='blue'> ".$include_dir."/IDS/tmp/</span><br />
                    and all files in that folder.<br />
                    <br /><br /></p>
                ";
    }
    fclose($fp);
    unlink("".$include_dir."/IDS/tmp/".$testfile."");       // remove testfile

    //      create and test XML folder for search procedure
    if (!is_dir(".".$xml_dir)) {
        @mkdir(".".$xml_dir, 0777);       //if not exist, try to create global folder for thumbnails
        if (!is_dir(".".$xml_dir)) {
            echo "  <br />
                        <p class='warnadmin cntr'><br />
                        Unable to create folder<span class='blue'> ".$xml_dir."</span>.<br />
                        Sphider-plus will not be able to store XML files during search procedure.
                        <br /><br /></p>
                    ";
        }
    }

    $fp = @fopen(".".$xml_dir."/".$testfile."","w");    //  try to write into a testfile
    if(!is_writeable(".".$xml_dir."/".$testfile."")) {
        echo "  <br />
                    <p class='warnadmin cntr'><br />
                    Folder for XML is not writeable.<br />
                    Sphider-plus will not be able to store XML files during search procedure.<br /><br />
                    chmod 777 the folder<span class='blue'> ".$xml_dir."</span> under *nix operating systems.<br />
                    <br /><br /></p>
                ";

    } else {
        fclose($fp);
        unlink(".".$xml_dir."/".$testfile."");       // remove testfile
    }

    //      create and test XML store folder for search procedure
    if (!is_dir(".".$xml_dir."/stored")) {
        @mkdir(".".$xml_dir."/stored", 0777);       //if not exist, try to create global folder for thumbnails
        if (!is_dir(".".$xml_dir."/stored")) {
            echo "  <br />
                        <p class='warnadmin cntr'><br />
                        Unable to create folder<span class='blue'> ".$xml_dir."/stored/</span>.<br />
                        Sphider-plus will not be able to save XML results during search procedure.
                        <br /><br /></p>
                    ";
        }
    }

    $fp = @fopen(".".$xml_dir."/stored/".$testfile."","w");    //  try to write into a testfile
    if(!is_writeable(".".$xml_dir."/stored/".$testfile."")) {
        echo "  <br />
                    <p class='warnadmin cntr'><br />
                    Folder for XML stored files is not writeable.<br />
                    Sphider-plus will not be able to store XML files during search procedure.<br /><br />
                    chmod 777 the folder<span class='blue'> ".$xml_dir."/stored/</span> under *nix operating systems.<br />
                    <br /><br /></p>
                ";

    } else {
        fclose($fp);
        unlink(".".$xml_dir."/stored/".$testfile."");       // remove testfile from XML stored folder
    }



    if ($thumb_folder) {
        //  delete all former thumbnails in temporary folder
        clear_folder(".".$thumb_folder);
    }
/*
    //  try to initialize a 32 MByte MySQL cache  (might not work on shared hosting systems)  33554432
    if ($qcache == 1) {
        $sql_query ="SET global query_cache_size = 33554432";
        $mysql_csize    = $db_con->query($sql_query);
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
/*
        $sql_query ="SET global query_cache_type = ON";
        $mysql_cacheon  = $db_con->query($sql_query) ;
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

        if(!$mysql_csize || !$mysql_cacheon) {
            echo "  <br />
                        <p class='warnadmin cntr'><br />
                        Warning: Unable to initialize a 32 MByte cache on the MySQL server.<br />
                        <br /><br /></p>
                    ";
        }

    }
*/
    if ($real_log == '1') {
        //  Delete old log information and define refresh rate
        $sql_query ="TRUNCATE ".$mysql_table_prefix."real_log";
        $db_con->query ($sql_query);
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

        $sql_query ="INSERT ".$mysql_table_prefix."real_log set `url`='' , `real_log`='' , `refresh` =$refresh ";
        $db_con->query ($sql_query);
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

    if ($latest) {  //  if tables are up to date
        //  delete all invalid URLs from table 'sites'
        $sql_query ="DELETE from ".$mysql_table_prefix."sites where site_id='0' OR site_id='' OR url='' OR url='http:/' OR url='http:///'";
        $db_con->query ($sql_query);
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

    function walk_through_cats($parent, $lev, $site_id) {
        global $db_con, $mysql_table_prefix, $debug;

        $cattype = "Category";
        $inputclass = "";
        for ($x = 0; $x < $lev; $x++) {
            $cattype ="Sub-Category";
            $inputclass = " ";
        }
        $sql_query ="SELECT * FROM ".$mysql_table_prefix."categories WHERE parent_num=$parent ORDER BY category";
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

        $num_rows = $result->num_rows;

        if ($num_rows) {
            $n = 1;
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $id = $row['category_id'];
                $cat = $row['category'];
                $state = '';
                if ($site_id) {
                    $sql_query ="SELECT * from ".$mysql_table_prefix."site_category where site_id=$site_id and category_id=$id";
                    $result2 = $db_con->query($sql_query);
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

                    if ($result2->num_rows) {
                        $state = " checked='checked'";
                    }

                }
                if (!$inputclass =="") {
                    while ($n < $lev) {
                        $inputclass .= "<span class='tree'>&raquo;</span>";
                        $n++;
                    }
                    echo "
                    $inputclass&nbsp;<span title='Sub-category'>
                            <input class='catlist' title='Click to select/deselect this sub-category' type='checkbox' name='cat[$id]' id='cat$id' ".$state."
                            />&nbsp;".$cat."</span><br />
                        ";
                } else {
                    echo "<label class='em' for='cat$id'>$cattype</label>
                            <input type='checkbox' title='Click to select/deselect this Category' name='cat[$id]' id='cat$id' ".$state."
                            /><span class='em warnok' title='Category Root'>".$cat."</span><br />
                        ";
                }
                walk_through_cats($id, $lev + 1, $site_id);
            }
        }

    }

    function addcatform($parent) {
        global $db_con, $mysql_table_prefix, $debug, $dba_act;

        $par2 = "";
        $par2num = "";
        echo "<div class='submenu cntr'>| Add New Category Form |</div>
            ";

        if ($parent=='') {
            $par='(Top level)';
        } else {
            $sql_query ="SELECT category, parent_num FROM ".$mysql_table_prefix."categories WHERE category_id='$parent'";
            $result = $db_con->query($sql_query);
            if (!$db_con->errno) {
                if ($row = $result->fetch_array(MYSQLI_NUM)) {
                    $par=$row[0];

                    $sql_query ="SELECT Category_ID, Category FROM ".$mysql_table_prefix."categories WHERE Category_ID='$row[1]'";
                    $result = $db_con->query($sql_query);
                    if ($db_con->errno) {
                        $err_row = __LINE__-2;
                        printf("<p><span class='red'>&nbsp;MySQL failure: %s&nbsp;\n<br /></span></p>", $db_con->error);
                        if (__FUNCTION__) {
                            printf("<p><span class='red'>&nbsp;Found in script: ".__FILE__."&nbsp;&nbsp;row: $err_row&nbsp;&nbsp;in function():&nbsp;".__FUNCTION__."&nbsp;<br /></span></p>");
                        } else {
                            printf("<p><span class='red'>&nbsp;Found in script: ".__FILE__."&nbsp;&nbsp;row: $err_row&nbsp;<br /></span></p>");
                        }
                        printf("<p><span class='red'>&nbsp;Script execution aborted.&nbsp;<br /></span>");
                        exit;
                    }

                    if ($result->num_rows) {
                        $row = $result->fetch_array(MYSQLI_NUM);
                        $par2num = $row[0];
                        $par2 = $row[1];
                    } else {
                        $par2 = "Top level";
                    }
                }
            } else {
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
            echo "</td></tr></table>
                ";
        }

        echo "<div class='panel x1'>
                <form class='txt' action='admin.php' method='post'>
                <input type='hidden' name='f' value='7'
                />
                <input type='hidden' name='parent' value='".$parent."'
                />
                <div class='cntr tblhead'>Parent: <a href='admin.php?f=add_cat&amp;parent=$par2num'>$par2</a> &raquo; ".stripslashes($par)."</div>
                <br />
            ";

        $sql_query ="SELECT category_ID, Category FROM ".$mysql_table_prefix."categories WHERE parent_num='$parent'";
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
            $subcats ="y";
            echo "<fieldset><legend>[ Create new subcategory under ]</legend>
                    <div class='odrow'>&bull;
                ";

	        $acats = "";
	        while ($row = $result->fetch_array(MYSQLI_NUM)) {
	            $acats .="<a title='Select as Main Category for new Sub-Category'
	                href='admin.php?f=add_cat&amp;parent=".$row[0]."'>".stripslashes($row[1])."</a> &bull;&nbsp;";
	        }
        }

        $acats = substr($acats,0,strlen($acats)-13);
        echo $acats;
        if ($subcats=="y") {
            echo "</div>
                    </fieldset>
                ";
        }
        echo "<div class='w75'>
                <fieldset><legend>[ New category ]</legend>
                <label for='category'>Enter Category Name</label>
                <input type='text' name='category' id='category' size='40' title='Click and type in category name'
                />
                </fieldset>
                <fieldset><legend>[ Save ]</legend>
                <input type='submit' id='submit' value='Add New Category' title='Click to add New Category'
                />
                </fieldset></div></form>
                </div>
            ";
    }


    function addcat($category, $parent) {
        global $db_con, $mysql_table_prefix, $debug;

        if ($category == "") return;
        $category = addslashes($category);
        if ($parent == "") {
            $parent = 0;
        }
        $sql_query ="INSERT INTO ".$mysql_table_prefix."categories (category, parent_num) VALUES ('$category', ".$parent.")";
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
        } else {
            return "<p class='cntr'>Category <span class='em'>$category</span> now added...</p><br />";
        }
    }

    function approve_newsites() {
        global $db_con, $mysql_table_prefix, $show_categories, $debug, $dba_act, $add_auth;
        global $sites_per_page, $sug_start, $whois_admin, $whois_ext, $include_dir;

        //  do we have categories defined by our Admin?
        $category   = '';
        $sql_query  = "SELECT * from ".$mysql_table_prefix."categories";
        $result     = $db_con->query($sql_query);

        if (!$rows = $result->num_rows){
            $category = -1;
        }

        $sql_query ="SELECT * FROM `".$mysql_table_prefix."addurl` LIMIT 0 , 30";
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

        $count = 1;

        if ($result->num_rows) {

            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $all[] = $row;
            }

            $links = '0';
            $start = $sug_start;
            $num_rows = $result->num_rows;
            $pages = ceil($num_rows / $sites_per_page);   // Calculate count of required pages

            if($start > $pages) $start = $pages;

            if ($start < 1) $start = '1';                   // As $start is not yet well defined, this is required for the first result page
            if ($start == '1') {
                $from = '0';                                // Also for first page in order not to multipy with 0
            }else{
                $from = ($start-1) * $sites_per_page;       // First $num_row of actual page
            }

            $fromm      = $from+1;
            $to = $num_rows;                                // Last $num_row of actual page
            $rest = $num_rows - $start;
            if ($num_rows > $sites_per_page) {              // Display more then one page?
                $rest = $num_rows - $from;
                $to = $from + $rest;                        // $to for last page
                if ($rest > $sites_per_page) $to = $from + ($sites_per_page); // Calculate $num_row of actual page
            }

            //  display result header
        echo "
            <div class='submenu cntr'>| Sites for Approval |</div>
            <div class='tblhead'>
                <br /><br />
                <table width='97%'>
                    <tr>
                        <td class='tblhead sml'>Displaying suggested URLs ".$fromm." - ".$to."&nbsp;&nbsp;from ".$num_rows."</td>
                    </tr>
                </table>";

            for ($i=$from; $i<$to; $i++) {

                //  prepare result for this new URL
                $n              = $i+1;
                $url            = $all[$i]['url'];
                $title          = $all[$i]['title'];
                $description    = $all[$i]['description'];
                $created        = $all[$i]['created'];
                $account        = $all[$i]['account'];
                $authent        = $all[$i]['authent'];

                if ($whois_admin) {
                    require_once "$include_dir/domain_whois.php";   //  load the WHOIS class
                    $list       = "";
                    $whois      = new whois();  //new class
                    $whois_res  = $whois->lookup($url, $whois_ext, $list);
                    unset($whois);

                    $whois_server   = $whois_res['whoisserver'];
                    $whois_result   = $whois_res['result'];
                    $whois_answer   =  $whois_res['answer'];
                }

                echo "
                <br />
                Site $n awaiting approval:<br /><br />
                <form action='admin.php' method='post'><input type='hidden' name='f' value='29' />
                    <table width='80%'>
                        <tr class='y3 odrow'>
                            <td class='cntr' ></br>User suggestion:</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr class='y3 odrow'>
                            <td>
                            URL:
                            </td>
                            <td class='left' ><input size='50' type='text' name=\"url\" value=\"$url\" />
                            &nbsp;&nbsp;
                            <a target=\"_blank\" href=\"$url\">visit</a>
                            </td>
                        </tr>
                        <tr class='y3 odrow'>
                            <td>Title:
                            </td>
                            <td class='left' ><textarea rows='1' name='title' cols='38'>$title</textarea>
                            </td>
                        </tr>
                        <tr class='odrow'>
                            <td>Description:
                            </td>
                            <td class='left' ><textarea rows='5' name='short_desc' cols='38'>$description</textarea>
                            </td>
                        </tr>";

                if($show_categories =='1' && $category != -1) {
                    echo "
                            <tr class='y3 odrow'>
                                <td>
                                Category:
                                </td>
                                <td class='left' ><select name=\"cat\">
                        ";
                    $category_id = $all[$i]['category_id'];
                    list_catsform (0, 0, "white", "", $category_id);
                    echo "
                                </select>
                                </td>
                            </tr>
                        ";
                }
                echo "      <tr class='y3 odrow'>
                            <td>suggested:
                            </td>
                            <td class='left' ><input size='50' type='text' name=\"created\" value=\"$created\" />
                            </td>
                        </tr>
                        <tr class='odrow'>
                            <td>by:
                            </td>
                            <td class='left' ><input size='50' type='text' name=\"dispatcher\" value=\"$account\" />
                            </td>
                        </tr>";
                if ($add_auth == '1') {

                    echo "<tr class='y3 warn'>
                                <td>Authentication code:
                                </td>
                                <td class='left' ><input size='50' type='text' name=\"authent\" value=\"$authent\" />
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Admin input required</td>
                        </tr>";
                }

                if ($whois_admin) {
                    echo "<tr class='y3 odrow'>
                                <td class='cntr' ></br>WHOIS report:</td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr class='y3 odrow'>
                                <td>Result:
                                </td>
                                <td class='left' ><input size='50' type='text' name=\"whois_result\" value=\"$whois_result\" />
                                </td>
                            </tr>
                            <tr class='y3 odrow'>
                                <td>Server:
                                </td>
                                <td class='left' ><input size='50' type='text' name=\"whois_server\" value=\"$whois_server\" />
                                </td>
                            </tr>
                            <tr class='odrow'>
                                <td>Server answer:
                                </td>
                                <td class='left' ><textarea rows='5' name='whois_answer' cols='38'>$whois_answer</textarea>
                                </td>
                            </tr>";
                }

                echo "
                    </table>
                    <table width='80%'>
                        <tr class='y3 cntr odrow sml'>
                            <td>
                            <input type='submit' name='approve' value='Approve' />&nbsp;&nbsp;&nbsp;&nbsp;
                            <input type='submit' name='delete' value='Reject' />&nbsp;&nbsp;&nbsp;&nbsp;
                            <input type='submit' name=\"bann\" value=\"Ban !\" />
                            <input type='hidden' name=\"domain\" value=\"$url\" />
                            </td>
                        </tr>
                    </table>
                </form>
                <br />";
            }
        }


        echo "
            </div>";

        if ($pages > 1) { // If we have more than 1 result-page
            echo "
            <div class='submenu cntr y5'>
                    Result page: $start from $pages
                <br /><br />
                Page selection:&nbsp;&nbsp;&nbsp;
                ";

            if($start > 1) { // Display 'First'
                echo "<a href='admin.php?f=28&amp;sug_start=1'>First</a>&nbsp;&nbsp;
                ";

                if ($start > 5 ) { // Display '-5'
                    $minus = $start-5;
                    echo "<a href='admin.php?f=28&amp;sug_start=$minus'>- 5</a>&nbsp;&nbsp;
                ";
                }
            }
            if($start > 1) { // Display 'Previous'
                $prev = $start-1;
                echo "<a href='admin.php?f=28&amp;sug_start=$prev'>Previous</a>&nbsp;&nbsp;
                ";
            }
            if($rest >= $sites_per_page) { // Display 'Next'
                $next = $start+1;
                echo "<a href='admin.php?f=28&amp;sug_start=$next'>Next</a>&nbsp;&nbsp;
                ";

                if ($pages-$start > 5 ) { // Display '+5'
                    $plus = $start+5;
                    echo "<a href='admin.php?f=28&amp;sug_start=$plus'>+ 5</a>&nbsp;&nbsp;
                    ";
                }
            }
            if($start < $pages) { // Display 'Last'
                echo "<a href='admin.php?f=28&amp;sug_start=$pages'>Last</a>
                ";
            }

            echo "
            </div>
            ";
        }

    }

    function banned_domains($valid) {
        global $db_con, $mysql_table_prefix, $debug;

        //      Headline for Banned Domain Manager
        echo "<div class='submenu cntr'>| Banned domain Manager |</div>
                <div class='tblhead'>
                <br />
            ";

        if ($valid != '1') {
            echo "<div class='warnadmin cntr'>Invalid input for Banned domain name.</div>
                    <p>\n</p>
                ";
        } else {
            echo "<table width='80%'>
                        <tr class='headline x3 cntr'>
                            <td>Banned domain</td>
                            <td>Banned since</td>
                            <td>Delete</td>
                        </tr>
                ";
            $bgcolor='odrow';
            $count_backup = 0;

            $Bquery = "SELECT * FROM `".$mysql_table_prefix."banned`ORDER By domain LIMIT 0 , 3000";
            $Bresult = $db_con->query($Bquery);
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

            if ($Bresult->num_rows) {
                while ($Brow = $Bresult->fetch_array(MYSQLI_ASSOC)) {
                    echo "<tr class='$bgcolor cntr'>
                                <td>".urldecode($Brow['domain'])."</td>
                                <td>".$Brow['created']."</td>
                                <form action='admin.php' method='post'>
                                <input type='hidden' name='f' value='31' />
                                <td><input type='submit' title='Click to permanently delete from database' value=\"Remove\" /></td>
                                <input type='hidden' name='domain' value=\"".$Brow['domain']."\" />
                                </form>
                            </tr>
                        ";

                    if ($bgcolor=='odrow') {
                        $bgcolor='evrow';
                    } else {
                        $bgcolor='odrow';
                    }
                }

            } else{
                echo "<tr><td class='warnadmin red cntr'>No domains banned</td>
                        <td class='odrow cntr'>&nbsp;</td>
                        <td class='odrow cntr'>&nbsp;</td>
                        </tr>
                    ";
            }
            echo "
                    </table>
                    <br />
                    </div>
                ";

        }
        echo "<p>\n</p>
                    <div class='tblhead'>
                    <br />
                    <form action='admin.php' method='post'><input type='hidden' name='f' value='32' />
                    <div class='panel x2 cntr'>
                    <p class='evrow cntr'>Add a new domain to be banned\n\n
                        <input type='text' name=\"new_banned\" size='40' maxlength='1024' />
                        <input type='submit' value=\"Add\" />
                    </p>
                    </div>
                    </form>
                    <br />
                    </div>
                    <br />
                ";
    }

    function addsiteform() {
        global $db_con, $mysql_table_prefix, $debug, $dba_act, $add_auth;

        $def_include = '1';

        echo "    <div class='submenu cntr'>| Add New site |</div>
                <div class='panel'>
                <form class='txt' action='admin.php' method='post'>
                <input type='hidden' name='f' value='1' />
                <fieldset><legend>[ Add New Site Details ]</legend>
                    <label class='em' for='url'>URL:</label>
                    <input placeholder=\"Enter the new URL starting with: http:// or https://\" type='text' name='url' id='url' title='Enter the new URL starting with: http://' size='60' value ='' />
                    <label class='em' for='title'>Site Title:</label>
                    <input placeholder=\"Enter a title for the new site\" type='text' name='title' id='title' title='Enter Web Site title' size='60' maxlength='60'/>
                    <label class='em' for='short_desc'>Short description:</label>
                    <input placeholder=\"Enter a short description here\" type='text' name='short_desc' id='short_desc' title='Enter short site description' size='90' maxlength='90'/>
                    <br /><br />
                    <label class='em' for='smap_url'>URL of sitemap&nbsp;&nbsp;&nbsp;&nbsp;(leave without URL for standard sitemap)</label>
                    <input placeholder=\"Enter URL, if sitemap is not in root folder\" type='text' name='smap_url' id='smap_url' title='Enter URL, if sitemap is not in root folder' size='60' value ='' />
                    <br /><br />
                    <label class='em' for='def_include'>Use default values as defined in common files for the<br />'URL Must include' and 'URL must Not include' definitions :</label>
                    <input name='def_include' type='checkbox' value='1' id='def_include' title='Leave blank for no input'
            ";
        if ($def_include==1) {
            echo "        checked='checked'";
        }

        echo" />
                    <label class='em' for='prior_level'>Define preference level for 'Re-index only priorizited sites':</label>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1 = highest preference, 5 = lowest preference:&nbsp;&nbsp;<select name='prior_level' id='prior_level' title='Select level for Re-index priorizited sites'>
                    <option value='1'";
            if ($prior_level == '1'){
                echo "checked='checked'";
            }
            echo ">1</option>
                    <option value='2'";
            if ($prior_level == '2') {
                echo " selected='selected'";
            }
            echo ">2</option>
                    <option value='3'";
            if ($prior_level == '3') {
                echo " selected='selected'";
            }
            echo ">3</option>
                    <option value='4'";
            if ($prior_level == '4') {
                echo " selected='selected'";
            }
            echo ">4</option>
                    <option value='5'";
            if ($prior_level == '5') {
                echo " selected='selected'";
            }
            echo ">5</option></select>
                    <p>&nbsp;</p>
                    <br /><br />
                </fieldset>
            ";
        $sql_query ="SELECT count(site_id) from ".$mysql_table_prefix."sites";
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


        $rows = $result->num_rows;


        if (!$rows = 0) {
            echo "    <br />
                <fieldset>
                <legend>Category Selection</legend>
                ";
            walk_through_cats(0, 0, '');
            echo "</fieldset>
                ";
        }

        echo "<br />
                <fieldset><legend>[ Confirm Addition ]</legend>
                <input type='submit' id='submit' value='Add New Site' title='Click to Add New Site for indexing'
                />
                </fieldset>
                </form>
                <a class='bkbtn' href='javascript:history.go(-1)' title='Go back a Page'>Back</a>
                </div>
            ";
    }

    //  will present the edit form as part of the site options
    function editsiteform($site_id) {
        global $db_con, $mysql_table_prefix, $debug, $add_auth;

        $sql_query ="SELECT * from ".$mysql_table_prefix."sites where site_id=$site_id";
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

        $row = $result->fetch_array(MYSQLI_ASSOC);

        $url            = $db_con->real_escape_string($row['url']);
        $depth          = $row['spider_depth'];
        $fullchecked    = "";
        $depthchecked   = "";

        if ($depth == -1 ) {
            $fullchecked = 'checked="checked"';
            $depth ="";
        } else {
            $depthchecked = 'checked="checked"';
        }
        $domainlv = $row['can_leave_domain'];
        if ($domainlv == 1 ) {
            $domainlv = 'checked="checked"';
        } else {
            $domainlv = "";
        }

        $use_pref = $row['use_prefcharset'];
        if ($use_pref == 1 ) {
            $use_pref = 'checked="checked"';
        } else {
            $use_pref = "";
        }

        $prior_level = $row['prior_level'];

        //  already sitemap url in database?
        if (!preg_match("/http:\/\//", $row['smap_url'])) {
            $row['smap_url'] = 'If sitemap is not in root folder, enter URL here';
        }

?>              <div class='submenu em cntr'>| Edit site |</div>
                <div class='panel w75'>
                <form class='txt' action='admin.php' method='post'>
                <input type='hidden' name='f' value='4'
                />
                <input type='hidden' name='site_id' value="<?php echo $site_id?>"
                />
                <fieldset>
                <legend>[ Edit Site Details ]</legend>
                <label class='em' for='url'>URL:</label>
                <input type='text' name='url' id='url' title='Enter URL' value="<?php echo $url?>" size='68' maxlength='1024'
                />
                <label class='em' for='title'>Title:</label>
                <input type='text' name='title' id='title' title='Enter Web Site title' value="<?php echo $row['title'] ?>" size='60' maxlength='255'
                />
                <label class='em' for='short_desc'>Short description:</label>
                <input type='text' name='short_desc' id='short_desc' title='Enter short site description' size='68' maxlength='255' value="<?php  echo $row['short_desc'] ?>"
                />

                <label class='em' for='smap_url'>URL of sitemap:</label>
                <input type='text' name='smap_url' id='smap_url' title='Enter URL of sitemap if not in root folder' value="<?php echo $row['smap_url']?>" size='68' maxlength='1024'/>
                <br /><br />
<?php
        if ($add_auth == '1') {
            echo "
                    <label class='em' for='authent'>Enter tag value for authentification &nbsp;&nbsp;&nbsp;&nbsp;(leave blank for authentification free sites)</label>
                    <input type='text' name='authent' id='authent' title='Enter the header tag value here' size='68' maxlength='255' value='".stripslashes($row['authent'])."'
                    />
                    <br /><br />
                    ";
        }

        echo"
                </fieldset>
                <fieldset><legend>[ Basic Indexing Options ]</legend>
                <label class='em' for='soption'>Spidering options:</label>
                <input type='radio' name='soption' id='soption' value='full' $fullchecked
                /> Full<br />
                <input type='radio' name='soption' value='level' $depthchecked
                /> Index Depth:
                <input type='text' name='depth' size='2' value='$depth'
                />
                <label class='em' for='domainlv'>Spider can leave domain?</label>
                <input type='checkbox' name='domainlv' id='domainlv' value='1' title='Check box if Sphider can leave above domain' $domainlv
                /> Check for Yes
                <label class='em' for='use_pref'>Use preferred charset for indexing?</label>
                <input type='checkbox' name='use_pref' id='use_pref' value='1' title='Check box if Sphider should use the preferred charset as defined in \"Settings\"' $use_pref
                /> Check for Yes
                <label class='em' for='prior_level'>Define preference level for 'Re-index only priorizited sites':</label>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1 = highest preference, 5 = lowest preference:&nbsp;&nbsp;<select name='prior_level' id='prior_level' title='Select level for Re-index priorizited sites'>

                <option value='1'";
            if ($prior_level == '1'){
                echo "checked='checked'";
            }
            echo ">1</option>
                    <option value='2'";
            if ($prior_level == '2') {
                echo " selected='selected'";
            }
            echo ">2</option>
                    <option value='3'";
            if ($prior_level == '3') {
                echo " selected='selected'";
            }
            echo ">3</option>
                <option value='4'";
            if ($prior_level == '4') {
                echo " selected='selected'";
            }
            echo ">4</option>
                <option value='5'";
            if ($prior_level == '5') {
                echo " selected='selected'";
            }
            echo ">5</option></select>
            <p>&nbsp;</p>


                </fieldset>
                <fieldset><legend>[ Include/Exclude Options ]</legend>
                    <label class='em' for='in'>URL Must include:</label>
                    <textarea name='in' id='in' cols='45' rows='5' title='Enter URLs that Must be included, one per line'>".$row['required']."</textarea>
                    <p>&nbsp;</p>
                    <label class='em' for='out'>URL must Not include:</label>
                    <textarea name='out' cols='45' rows='5' title='Enter URLs that Must Not be included, one per line'>".$row['disallowed']."</textarea>
                    <p>&nbsp;</p>
            ";

        walk_through_cats(0, 0, $site_id);

        echo "</fieldset>
                <fieldset><legend>[ Confirm Changes ]</legend>
                    <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit'  id='submit'  value='Update'  title='Click to confirm Site Edit update'/></p>

                </fieldset>
                </form>
                <a class='bkbtn' href='admin.php?f=20&amp;site_id=".$row['site_id']."' title='Go back a Page'>Back</a>
                </div>
            ";
    }

    //  will store the edited options of a site
    function editsite($site_id, $url, $title, $short_desc, $depth, $required, $disallowed, $domainlv, $cat, $smap_url, $authent, $use_pref, $prior_level) {
        global $db_con, $mysql_table_prefix, $debug, $add_auth;

        $short_desc = addslashes($short_desc);
        $title = addslashes($title);
        $sql_query ="DELETE from ".$mysql_table_prefix."site_category where site_id = $site_id";
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

        $compurl = parse_url($url);
        if ($compurl['path']=='') {
            $url=$url."/";
        }

        if(!stristr($url, "ttp")){
            $url = "http://".$url;          //  lazy admins need some assistance
        }

        //  valid sitemap url?
        if (!preg_match("/http:\/\//", $smap_url)) {
            $smap_url = 'NULL';
        }

        $url = $db_con->real_escape_string($url);
        $sql_query ="UPDATE ".$mysql_table_prefix."sites SET url='$url', title='$title', short_desc='$short_desc', spider_depth =$depth, required='$required', disallowed='$disallowed', can_leave_domain='$domainlv', smap_url='$smap_url', authent='$authent', use_prefcharset='$use_pref', prior_level='$prior_level' WHERE site_id=$site_id";
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

        $sql_query ="SELECT category_id from ".$mysql_table_prefix."categories";
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

        while ($row = $result->fetch_array(MYSQLI_NUM)) {
            $cat_id=$row[0];
            if ($cat[$cat_id]=='on') {
                $sql_query ="INSERT INTO ".$mysql_table_prefix."site_category (site_id, category_id) values ('$site_id', '$cat_id')";
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

        if (!$db_con->errno) {
            return "<p class='msg'>Site Indexing Options updated...</p>" ;
        }
    }

    function editcatform($cat_id) {
        global $db_con, $mysql_table_prefix, $debug;

        $sql_query ="SELECT category FROM ".$mysql_table_prefix."categories where category_id='$cat_id'";
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

        $row = $result->fetch_array(MYSQLI_NUM);
        $category=$row[0];
        echo "<div class='submenu cntr'>| Edit category |</div>
                <div class='panel x2'>
                <form class='txt' action='admin.php' method='post'>
                    <input type='hidden' name='f' value='10'
                    />
                    <input type='hidden' name='cat_id' value='".$cat_id."'
                    />
                    <fieldset><legend>[ Edit Index Category ]</legend>
                    <label class='em' for='category'>Category:</label>
                    <input type='text' name='category' id='category' value='$category' size='40'
                    /></fieldset>
                    <fieldset><legend>[ Save Category Edit ]</legend>
                    <input class='sbmt' type='submit'  id='submit'  value='Update'
                    /></fieldset>
                </form>
                </div>
            ";
    }

    function editcat($cat_id, $category) {
        global $db_con, $mysql_table_prefix, $debug;

        $qry = "UPDATE ".$mysql_table_prefix."categories SET category='".addslashes($category)."' WHERE category_id='$cat_id'";
        $db_con->query($qry);
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
            printf("<p>$qry</p>");
            exit;
        }
    }

    function showsites($f) {
        global $db_con, $mysql_table_prefix, $start, $sites_per_page, $site_funcs, $db_con;
        global $debug, $dba_act, $add_auth, $suggest, $tmp_dir, $interval;
        global $sites_alpha, $sites_prior, $sites_latest, $sites_oldest, $sites_title;
        global $cat_title, $cat_sel, $cat_sel0, $cat_sel0a, $cat_sel1, $cat_sel2, $cat_sel3, $cat_sel4, $cat_sel_all;
        global $group_name_0, $group_name_1, $group_name_2, $group_name_3, $group_name_4;

        //  clean input
        $start = substr(trim($start), 0, 6);
        if (!preg_match("/^[0-9]+$/", $start)) {
            $start = '1';
        }

        if ($sites_alpha == '1' ) {   // sort Admin Sites table in alphabetic order
            $sql_query ="SELECT site_id, url, title, indexdate, authent, prior_level from ".$mysql_table_prefix."sites ORDER by url, indexdate";
            $result = $db_con->query($sql_query);
        }

        if ($sites_prior == '1' ) {   // sort Admin Sites table in alphabetic order
            $sql_query ="SELECT site_id, url, title, indexdate, authent, prior_level from ".$mysql_table_prefix."sites ORDER by prior_level, url";
            $result = $db_con->query($sql_query);
        }


        if ($sites_latest == '1' ) {  //    sort  Admin Sites table by indexdate, latest on top
            $sql_query ="SELECT site_id, url, title, indexdate, prior_level from ".$mysql_table_prefix."sites ORDER by indexdate DESC, title";
            $result = $db_con->query($sql_query);
        }

        if ($sites_oldest == '1' ) {  //    sort  Admin Sites table by indexdate, oldest on top
            $sql_query ="SELECT site_id, url, title, indexdate, prior_level from ".$mysql_table_prefix."sites ORDER by indexdate, title";
            $result = $db_con->query($sql_query);
        }

        if ($sites_title == '1' ) {  //    sort  Admin Sites table by internal title
            $sql_query ="SELECT site_id, url, title, indexdate, prior_level from ".$mysql_table_prefix."sites ORDER by title, indexdate";
            $result = $db_con->query($sql_query);
        }
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

        $num_rows = $result->num_rows;

        $all = array();
        $c = '0';
        while ($c < $num_rows) {
            $all[$c] = $result->fetch_array(MYSQLI_ASSOC);
            $c++;
        }

        echo "    <div class='submenu cntr y5'>
                <br />
                <ul>
                  <li><a href='admin.php?f=add_site' title='Add new site for indexing'>&nbsp;&nbsp;Add site&nbsp;&nbsp;</a></li>
                  <li><a href='admin.php?f=40' title='Import URL list from folder: urls'>&nbsp;&nbsp;Import / Export URL list&nbsp;&nbsp;</a></li>
            ";

        if ($result->num_rows > 0) {

            $site_id = '';  //  initialize dummy variable
            //   open and flush the temp file that will hold the actual site ids
            $filename = "$tmp_dir/act_sites.txt";
            $handle = @fopen($filename, "w+");

            if ($debug == '0') {
                if (function_exists("ini_set")) {
                    ini_set("display_errors", "0");
                }
                error_reporting(0);  //     suppress  PHP messages
            } else {
                error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE ^ E_STRICT);
            }

            echo "<li><a href='admin.php?f=51' title='Index all the sites not yet indexed'>&nbsp;&nbsp;Index only the new&nbsp;&nbsp;</a></li>
                    <li><a href='admin.php?f=50' title='Re-index all sites'>&nbsp;&nbsp;Re-index all&nbsp;&nbsp;</a></li>
                    <li><a href='admin.php?f=45' title='Erase entire existing index then Re-index' onclick=\"return confirm('Are you sure you want to Erase? Site details will be kept but all Indexing information will be lost!')\">&nbsp;&nbsp;Erase &amp; Re-index all&nbsp;&nbsp;</a></li>
                    <li><a href='admin.php?f=54' title='Re-index all meanwhile erased sites'>&nbsp;&nbsp;Re-index all the erased&nbsp;&nbsp;</a></li>
                    <li><a href='admin.php?f=56' title='Continue indexing all interrupted'>&nbsp;&nbsp;Continue indexing all the suspended&nbsp;&nbsp;</a></li>
                    <li><a href='admin.php?f=58' title='Re-index all the sites shown below'>&nbsp;&nbsp;Re-index the below&nbsp;&nbsp;</a></li>
                    <li><a href='admin.php?f=61' title='Re-index preferred sites'>&nbsp;&nbsp;Re-index only preferred sites&nbsp;&nbsp;</a></li>

                    ";
            if ($interval != 'never') {
                echo "    <li><a href='admin.php?f=59&amp;site_id=".$site_id."' title='Start and Stop the time driven auto indexer'>&nbsp;&nbsp;Periodical Re-index&nbsp;&nbsp;</a></li>
                ";
            }
            if ($suggest) {
                echo "    <li class='warnadmin'><a href='admin.php?f=28' title='Approve sites suggested by user'>&nbsp;&nbsp;Approve sites&nbsp;&nbsp;&nbsp;</a></li>";
            } else {
                echo "    <li><a href='admin.php?f=28' title='Approve sites suggested by user'>&nbsp;&nbsp;Approve sites&nbsp;&nbsp;&nbsp;</a></li>";
            }
            echo "
                    <li><a href='admin.php?f=30' title='Banned domains Manager'>&nbsp;&nbsp;Banned domains&nbsp;&nbsp;&nbsp;</a></li>";

        }

        echo "    </ul>
            </div>
            ";

        //Prepare header and all results for listing
        //$sites_per_page = '100'; // if you prefer another count than used for Sphiders result pages, uncomment this row and place your count of URLs per page here.
        $pages = ceil($num_rows / $sites_per_page);     // Calculate count of required pages

        if($start > $pages) $start = $pages;

        if ($start < 1) $start = '1';                   // As $start is not yet well defined, this is required for the first result page
        if ($start == '1') {
            $from = '0';                                // Also for first page in order not to multipy with 0
        }else{
            $from = ($start-1) * $sites_per_page;           // First $num_row of actual page
        }

        $to = $num_rows;                                // Last $num_row of actual page
        $rest = $num_rows - $start;

        if ($num_rows > $sites_per_page) {              // Display more then one page?
            $rest = $num_rows - $from;
            $to = $from + $rest;                        // $to for last page
            if ($rest > $sites_per_page) $to = $from + ($sites_per_page); // Calculate $num_row of actual page
        }

        if ($num_rows > 0) {

            if ($pages > 1) { // If we have more than 1 result-page
                echo "<div class='submenu cntr y5'>
                Page: $start from $pages
                <br /><br />
                <form class='cntr' name='form_page' method='post' action='admin.php?f=2'>
                    Page selection:&nbsp;&nbsp;&nbsp;
                    ";

                if($start > 1) { // Display 'First'
                    echo "<a href='admin.php?f=2&amp;start=1&id=' print $site_funcs[$f]'>First</a>&nbsp;&nbsp;
                        ";

                    if ($start > 5 ) { // Display '-5'
                        $minus = $start-5;
                        echo "<a href='admin.php?f=2&amp;start=$minus&id=$site_funcs[$f]'>- 5</a>&nbsp;&nbsp;
                        ";
                    }
                }
                if($start > 1) { // Display 'Previous'
                    $prev = $start-1;
                    echo "<a href='admin.php?f=2&amp;start=$prev&id=$site_funcs[$f]'>Previous</a>&nbsp;&nbsp;
                        ";
                }
                if($rest >= $sites_per_page) { // Display 'Next'
                    $next = $start+1;
                    echo "<a href='admin.php?f=2&amp;start=$next&id=$site_funcs[$f]' >Next</a>&nbsp;&nbsp;
                        ";

                    if ($pages-$start > 5 ) { // Display '+5'
                        $plus = $start+5;
                        echo "<a href='admin.php?f=2&amp;start=$plus&id=$site_funcs[$f]'>+ 5</a>&nbsp;&nbsp;
                            ";
                    }
                }
                if($start < $pages) { // Display 'Last'
                    echo "<a href='admin.php?f=2&amp;start=$pages&id=$site_funcs[$f]'>Last</a>
                        ";
                }

                echo "
                    &nbsp;&nbsp;&nbsp;&nbsp;Page no.&nbsp;&nbsp;<input name='start' id='start' value='$start' type='text' size='4' maxlength='6' title='Enter page number to be displayed.'/>
                    &nbsp;&nbsp;<input class='sbmt' type='submit' value='Jump' id='submit' title='Click once to jump to that page.' />
                    <input class='hide' type='hidden' name='id' value='$site_funcs[$f]' />
                </form>
            </div>
            ";
            }

            $fromm = $from+1;
            echo "  <div>
                <p class='tblhead sml'>Displaying URLs ".$fromm." - ".$to."&nbsp;&nbsp;from ".$num_rows."</p>
                <table width='97%'>
                    <tr>
                        <td class='tblhead sml'>Site title</td>";

            if ($add_auth){
                echo "<td class='tblhead sml'>Authent value</td>";
            }

            echo "  <td class='tblhead sml'>Site URL</td>
                        <td class='tblhead sml'>Prio. level</td>
                        <td class='tblhead sml'>Last indexed</td>
                        <td class='tblhead sml'>Index</td>
                        <td class='tblhead sml'>Site</td>
                    </tr>
                ";

        } else {
            echo "<div class='cntr sml'>
                <p class='em cntr'>Welcome to the Sphider-plus Admin section.</p>
                <p class='em cntr'>At present there are no sites available in database ".$dba_act."</p>
                <p>&nbsp;</p>
                <p class='em'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Choose <a href='admin.php?f=add_site' title='Add new site for indexing'>'Add site'</a> from the submenu to add a new site, or...</p>
                <p class='em'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Choose <a href='admin.php?f=40' title='Import Url list from folder: urls'
                onclick=\"return confirm('Are you sure you want to import? Current Url table will be lost and overwritten!')\">'Import Url list'</a> if currently available, or...</p>
                <p class='em'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Choose <a href='admin.php?f=index' title='Index directly a site'>'Index'</a> to directly go to the indexing section.</p>
                <p>&nbsp;</p>
                <p>&nbsp;</p>
                <span class='txt cntr warnadmin'>In either case you may like to review the <a href='admin.php?f=settings' title='Define all settings'>'Settings'</a> page first!</span>
                <p>&nbsp;</p>
                <p>&nbsp;</p>
            </div>
                ";
        }
        $class = "evrow";
        for ($i=$from; $i<$to; $i++) {

            $this_site  = $all[$i];
            $site_id    = $this_site["site_id"];
            $site_url   = reconvert_url($this_site["url"]);
            $title      = $this_site["title"];
            $prior_level= $this_site["prior_level"];
            $indexdate  = $this_site["indexdate"];
            $authent    = $this_site["authent"];
            //      prepare the URL for multiple & and + as part of the URL
            $url_crypt  = str_replace("&", "-_-", $site_url);   //  crypt the & character
            $url_crypt  = str_replace("+", "_-_", $url_crypt);  //  crypt the + character
            $url_crypt  = htmlentities($url_crypt, ENT_QUOTES);
            $url_ent    = htmlentities($site_url, ENT_QUOTES);  //  required for direct visit in new window

            $my_cats    = array();
            if (!$group_name_0) {
                $group_name_0 = ' ';
            }

            if ($cat_title || $cat_sel) {
                //  get category_id for this site
                $sql_query ="SELECT category_id from ".$mysql_table_prefix."site_category where site_id = '$site_id'";
                $cat_res =  $db_con->query($sql_query);
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

                $cat_id = $cat_res->fetch_row();

                //  get category names involved for this cat_id
                $sql_query ="SELECT * from ".$mysql_table_prefix."categories where category_id = '$cat_id[0]'";
                $res_cat =  $db_con->query($sql_query);
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

                $my_cats = $res_cat->fetch_array(MYSQLI_ASSOC);
                if ($my_cats) {
                    $cat_row = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Involved cats&nbsp;";
                    if ($group_name_0 && $my_cats['category']) {
                        $cat_row .= "".trim($group_name_0).": ".$my_cats['category']."&nbsp;&nbsp;";
                    }
                    if ($group_name_1 && $my_cats['group_sel0']) {
                        $cat_row .= "".$group_name_1.": ".$my_cats['group_sel0']."&nbsp;&nbsp;";
                    }
                    if ($group_name_2 && $my_cats['group_sel1']) {
                        $cat_row .= "".$group_name_2.": ".$my_cats['group_sel1']."&nbsp;&nbsp;";
                    }
                    if ($group_name_3 && $my_cats['group_sel2']) {
                        $cat_row .= "".$group_name_3.": ".$my_cats['group_sel2']."&nbsp;&nbsp;";
                    }
                    if ($group_name_4 && $my_cats['group_sel3']) {
                        $cat_row .= "".$group_name_4.": ".$my_cats['group_sel3']."";
                    }
                }
            }


            if ($indexdate=='') {
                $indexstatus="<span class='warnadmin cntr'>Not indexed</span>";
                $indexoption="<form  class='cntr08' action='admin.php' method='post'>
                                    <input type='hidden' name='f' value='index' />
                                    <input type='hidden' name='url' value='$url_crypt' />
                                    <input class='cntr' type='submit' value='Index now'>
                                    </form>
                                ";
            } else {
                $sql_query ="SELECT site_id from ".$mysql_table_prefix."pending where site_id =$site_id";
                $result2 = $db_con->query($sql_query);
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

                $row2 =$result2->fetch_array(MYSQLI_ASSOC);;
                $result2->free_result();

                if ($row2['site_id'] == $site_id) {
                    $indexstatus = "<span class='warn cntr'>Unfinished</span>";
                    $indexoption="<form  class='cntr08' action='admin.php' method='post'>
                                        <input type='hidden' name='f' value='index' />
                                        <input type='hidden' name='url' value='$url_crypt' />
                                        <input class='cntr' type='submit' value='Continue'>
                                        </form>
                                    ";
                } else {
                    $indexstatus = $indexdate;
                    $indexoption="
                            <form  class='cntr08' action='admin.php' method='post'>
                                <input type='hidden' name='f' value='index' />
                                <input type='hidden' name='reindex' value='1' />
                                <input type='hidden' name='url' value='$url_crypt' />
                                <input class='cntr' type='submit' value='Re-index'>
                            </form>
                ";
                }
            }
            if ($class =="evrow") {
                $class = "odrow";
            }else{
                $class = "evrow";
            }

            echo "    <tr class='$class'>
                        <td class='sml'>".stripslashes($title)."</td>";

            if ($add_auth){
                echo "<td class='sml'>".stripslashes($authent)."</td>";
            }

            if ($my_cats) {
                echo "
                        <td class='sml'><a href='".$site_url."' target='_blank' title='Visit site in new window'>".$url_ent."</a><br />".$cat_row."</td>
                ";
            } else {
                echo "
                        <td class='sml'><a href='".$site_url."' target='_blank' title='Visit site in new window'>".$url_ent."</a></td>
                ";
            }
            echo "
                        <td class='cntr08'>$prior_level</td>
                        <td class='cntr08'>$indexstatus</td>
                        <td class='cntr08'>$indexoption        </td>
                        <td class='cntr08'><a href='admin.php?f=20&amp;site_id=$site_id' class='options' title='Click to browse site options'>Options</a></td>
                    </tr>
                ";

            //  write  actual $site_id into the temp file
            @fwrite($handle, "$site_id\r\n");
        }
        //  close temp file
        @fclose($handle);

        // Display end of table
        if ($num_rows > 0) {
            echo "</table>
            </div>
            ";

            if ($pages > 1) { // If we have more than 1 result-page
                echo "<div class='submenu cntr y5'>
                Page: $start from $pages
                <br /><br />
                <form class='cntr' name='form_page' method='post' action='admin.php?f=2'>
                    Page selection:&nbsp;&nbsp;&nbsp;
                    ";

                if($start > 1) { // Display 'First'
                    echo "<a href='admin.php?f=2&amp;start=1&id=' print $site_funcs[$f]'>First</a>&nbsp;&nbsp;
                        ";

                    if ($start > 5 ) { // Display '-5'
                        $minus = $start-5;
                        echo "<a href='admin.php?f=2&amp;start=$minus&id=$site_funcs[$f]'>- 5</a>&nbsp;&nbsp;
                        ";
                    }
                }
                if($start > 1) { // Display 'Previous'
                    $prev = $start-1;
                    echo "<a href='admin.php?f=2&amp;start=$prev&id=$site_funcs[$f]'>Previous</a>&nbsp;&nbsp;
                        ";
                }
                if($rest >= $sites_per_page) { // Display 'Next'
                    $next = $start+1;
                    echo "<a href='admin.php?f=2&amp;start=$next&id=$site_funcs[$f]' >Next</a>&nbsp;&nbsp;
                        ";

                    if ($pages-$start > 5 ) { // Display '+5'
                        $plus = $start+5;
                        echo "<a href='admin.php?f=2&amp;start=$plus&id=$site_funcs[$f]'>+ 5</a>&nbsp;&nbsp;
                            ";
                    }
                }
                if($start < $pages) { // Display 'Last'
                    echo "<a href='admin.php?f=2&amp;start=$pages&id=$site_funcs[$f]'>Last</a>
                        ";
                }

                echo "
                    &nbsp;&nbsp;&nbsp;&nbsp;Page no.&nbsp;&nbsp;<input name='start' id='start' value='$start' type='text' size='4' maxlength='6' title='Enter page number to be displayed.'/>
                    &nbsp;&nbsp;<input class='sbmt' type='submit' value='Jump' id='submit' title='Click once to jump to that page.' />
                    <input class='hide' type='hidden' name='id' value='$site_funcs[$f]' />
                </form>
            </div>
            ";
            }
        }
    }

    function deletecat($cat_id) {
        global $db_con, $mysql_table_prefix, $debug;

        $list = implode(",", get_cats($cat_id));
        $sql_query ="DELETE from ".$mysql_table_prefix."categories where category_id in ($list)";
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

        $sql_query ="DELETE from ".$mysql_table_prefix."site_category where category_id=$cat_id";
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

        return "<p class='msg'>Category deleted.</p>";
    }

    function deletesite($site_id) {
        global $db_con, $mysql_table_prefix, $debug;

        $sql_query ="DELETE from ".$mysql_table_prefix."sites where site_id=$site_id";
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

        $sql_query ="DELETE from ".$mysql_table_prefix."site_category where site_id=$site_id";
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

        $sql_query ="DELETE from ".$mysql_table_prefix."pending where site_id=$site_id";
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

        $sql_query ="SELECT link_id from ".$mysql_table_prefix."links where site_id=$site_id";
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

        $todelete = array();
        $num_rows   = $result->num_rows;

        if ($num_rows) {
	        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
	            $todelete[]=$row['link_id'];
	        }
        }


        if (count($todelete)>0) {
            $todelete = implode(",", $todelete);
            $sql_query ="DELETE from ".$mysql_table_prefix."link_keyword where link_id in($todelete)";
            $db_con->query($sql_query);
            if ($debug > 0 && $db_con->errno) {
                printf("MySQL failure: %s\n", $db_con->error);
                echo "<br />Script aborted.";
                exit;
            }
        }

        $sql_query ="DELETE from ".$mysql_table_prefix."links where site_id=$site_id";
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

        return "<br /><p class='msg'>Site deleted...</p>";
    }

    function deletePage($link_id) {
        global $db_con, $mysql_table_prefix, $debug;

        $sql_query ="DELETE from ".$mysql_table_prefix."links where link_id=$link_id";
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

        $sql_query ="DELETE from ".$mysql_table_prefix."link_keyword where link_id=$link_id";
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

        return "<br /><p class='msg'>Page deleted...</p>";
    }


    function cleanTemp($back) {
        global $db_con, $mysql_table_prefix, $debug;

        $sql_query ="DELETE from ".$mysql_table_prefix."temp where level >= 0 or relo_count > 0";
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

        $del = $db_con->affected_rows;

        if ($back) {
            echo "<div class='submenu'>&nbsp;</div>
                    <p class='msg cnt'>Temp table cleared [<span class='warnok'> ".$del." </span>] items deleted.</p>
                    <br />
                    <a class='bkbtn' href='admin.php?f=clean' title='Go back to Clean'>Back</a>
                ";
        } else {
            echo "
                    <p class='cntr em sml'>Temp table cleared [<span class='warnok'> ".$del." </span>] items deleted.</p>
                    <br />
                ";
        }
    }

    function cleanPending($back) {
        global $db_con, $mysql_table_prefix, $debug;

        $sql_query ="DELETE from ".$mysql_table_prefix."pending";
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

        $del = $db_con->affected_rows;

        if ($back) {
            echo "<div class='submenu'>&nbsp;</div>
                    <p class='msg cnt'>Pending table cleared [<span class='warnok'> ".$del." </span>] items deleted.</p>
                    <br />
                    <a class='bkbtn' href='admin.php?f=clean' title='Go back to Clean'>Back</a>
                ";
        } else {
            echo "
                    <p class='cntr em sml'>Pending table cleared [<span class='warnok'> ".$del." </span>] items deleted.</p>
                ";
        }
    }

    function clearLog() {
        global $db_con, $mysql_table_prefix, $debug;

        $sql_query ="DELETE from ".$mysql_table_prefix."query_log where time >= 0";
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

        $del = $db_con->affected_rows;

        echo "<div class='submenu'>&nbsp;</div>
                <p class='msg cntr'>Search log cleared [<span class='warnok'> ".$del." </span>] items deleted.</p>
                <br />
                <a class='bkbtn' href='admin.php?f=clean' title='Go back to Clean'>Back</a>
            ";
    }

    function clearAddurl() {
        global $db_con, $mysql_table_prefix, $debug;

        $sql_query ="DELETE from ".$mysql_table_prefix."addurl";
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

        $del = $db_con->affected_rows;

        echo "<div class='submenu'>&nbsp;</div>
                <p class='msg cntr'>Addurl table flushed. [<span class='warnok'> ".$del." </span>] URLs deleted.</p>
                <br />
                <a class='bkbtn' href='admin.php?f=clean' title='Go back to Clean'>Back</a>
            ";
    }

    function clearBanned() {
        global $db_con, $mysql_table_prefix, $debug;

        $sql_query ="DELETE from ".$mysql_table_prefix."banned";
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

        $del = $db_con->affected_rows;

        echo "<div class='submenu'>&nbsp;</div>
                <p class='msg cntr'>Banned table flushed. [<span class='warnok'> ".$del." </span>] domains deleted.</p>
                <br />
                <a class='bkbtn' href='admin.php?f=clean' title='Go back to Clean'>Back</a>
            ";
    }

    function clearXML() {
        global $xml_dir;

        $dir = ".$xml_dir/stored/";
        if($dh = opendir($dir)){
            while(($file = readdir($dh))!== false){
                if(file_exists($dir.$file)) @unlink($dir.$file);
            }
            closedir($dh);
        }

        echo "<div class='submenu'>&nbsp;</div>
                <p class='msg cntr'>All files in XML subfolder deleted.</p>
                <br />
                <a class='bkbtn' href='admin.php?f=clean' title='Go back to Clean'>Back</a>
            ";
    }

    function truncateAll() {
        global $db_con, $mysql_table_prefix, $debug;

        //  clear all tables in db
        $erase =array ("addurl","banned","categories","domains","keywords","links","link_details","link_keyword","media","pending","query_log","real_log","sites","site_category","temp");
        foreach ($erase as $allthis){
            $db_con->query ("TRUNCATE `".$mysql_table_prefix."$allthis`");
        }
        echo "<div class='submenu'>&nbsp;</div>
                <br />
                <p class='msg cntr'>Okay, all tables flushed.</p>
                <br />
                <a class='bkbtn' href='admin.php?f=clean' title='Go back to Clean'>Back</a>
            ";
    }


    function clearBestPage() {
        global $db_con, $mysql_table_prefix, $debug;

        $sql_query = "UPDATE ".$mysql_table_prefix."links set click_counter= 0, last_click= 0, last_query= '' where click_counter OR last_click > 0";
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

        $del = $db_con->affected_rows;
        echo "<div class='submenu'>&nbsp;</div>
                <p class='msg cntr'>Most Popular Links cleared [<span class='warnok'> ".$del." </span>] items deleted.</p>
                <br />
                <a class='bkbtn' href='admin.php?f=clean' title='Go back to Clean'>Back</a>
            ";
    }

    function clearBestMedia() {
        global $db_con, $mysql_table_prefix, $debug;

        $sql_query ="UPDATE ".$mysql_table_prefix."media set click_counter= 0, last_click= 0, last_query= '' where click_counter OR last_click > 0";
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

        $del = $db_con->affected_rows;
        echo "<div class='submenu'>&nbsp;</div>
                <p class='msg cntr'>Most Popular Media cleared [<span class='warnok'> ".$del." </span>] items deleted.</p>
                <br />
                <a class='bkbtn' href='admin.php?f=clean' title='Go back to Clean'>Back</a>
            ";
    }

    function clearTempFolder($back) {
        global $db_con, $tmp_folder;

        $count = '0';
        if ($handle = opendir(".".$tmp_folder)) {
            while (false != ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    @unlink(".".$tmp_folder."/".$file."");
                    $count++;
                }
            }
        }
        echo "<div>&nbsp;</div>
                <p class='cntr em sml'>Temporary folder cleared [<span class='warnok'> ".$count." </span>] files deleted.</p>
            ";
        if ($back == '1') {
            echo "<br />
                <a class='bkbtn' href='admin.php?f=clean' title='Go back to Clean'>Back</a>
                ";
        }



    }

    function clearTextCache($back) {
        global $db_con, $textcache_dir;

        $count = '0';
        if ($handle = opendir($textcache_dir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    @unlink("".$textcache_dir."/".$file."");
                    $count++;
                }
            }
        }
        echo "<div>&nbsp;</div>
                <p class='cntr em sml'>Text cache cleared [<span class='warnok'> ".$count." </span>] files deleted.</p>
            ";
        if ($back == '1') {
            echo "<br />
                <a class='bkbtn' href='admin.php?f=clean' title='Go back to Clean'>Back</a>
                ";
        }
    }

    function clearMediaCache($back) {
        global $db_con, $mediacache_dir;

        $count = '0';
        if ($handle = opendir($mediacache_dir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    @unlink("".$mediacache_dir."/".$file."");
                    $count++;
                }
            }
        }
        echo "<div>&nbsp;</div>
                <p class='cntr em sml'>Media cache cleared [<span class='warnok'> ".$count." </span>] files deleted.</p>
                ";
        if ($back == '1') {
            echo "<br />
                <a class='bkbtn' href='admin.php?f=clean' title='Go back to Clean'>Back</a>
                ";
        }
    }

    function cleanMediaLink() {
        global $db_con, $mysql_table_prefix, $debug;

        $del = '0';
        $allmedia = array();

        $sql_query ="SELECT link_id from ".$mysql_table_prefix."media";       // get all media link_id's
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

        $allmedia = array();
        $num_rows   = $result->num_rows;

        if ($num_rows) {
	        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
	            $allmedia[] = $row['link_id'];
	        }
        }

        $allmedia = array_unique($allmedia);

        foreach ($allmedia as $thismedia) {
            $sql_query ="SELECT link_id from ".$mysql_table_prefix."links where link_id=$thismedia";       //  get all currrently valid link_id's
            $res = $db_con->query($sql_query);
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

            if ($res->num_rows == '0') {
                $sql_query ="DELETE from ".$mysql_table_prefix."media where link_id=$thismedia";
                $result = $db_con->query($sql_query); //  delete all media with unknown link_id
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

                $deleted = $db_con->affected_rows;
                $del = $del+$deleted;
            }

        }

        echo "<div class='submenu'>&nbsp;</div>
                <p class='msg cntr'>Media table cleaned [<span class='warnok'> ".$del." </span>] items deleted.</p>
                <br />
                <a class='bkbtn' href='admin.php?f=clean' title='Go back to Clean'>Back</a>
            ";
    }

    function clearSpLog() {     //      Bulk delete for all spider log files
        global $db_con, $mysql_table_prefix, $log_dir, $debug;
        $i = '0';

        if (is_dir("./$log_dir/")) {
            if ($dh = opendir("./$log_dir/")) {
                while (($logfile = readdir($dh)) !== false) {
                    if (preg_match("/\.log/i$", $logfile) || preg_match("/\.html$/i", $logfile)) {  //	  only *.html and *.log are valid log-files
                        @unlink("./$log_dir/$logfile");	//	  delete this log file
                        $i++ ;	  //	  count all log files
                    }
                }
                closedir($dh);
            }
            echo "<div class='submenu'>&nbsp;</div>
                    <p class='warnok'>Spider log cleared. [<span class='warnok'> $i </span>] files deleted.</p>
                    <a class='bkbtn' href='admin.php?f=clean' title='Go back to Clean'>Back</a>
                ";
        } else {
            echo "<p class='warnadmin'><br />
                    Folder '$log_dir' does not exist.<br />
                    No files deleted.</p>
                ";
        }
    }

    function showIDS($back) {
        global $db_con, $include_dir, $row;

        $lines = array();
        $ids_count = '';
        $last = '';
        $when = '';
        $i = '0';
        $file = "ids_log.txt";

        $handle = @fopen ("$include_dir/IDS/tmp/phpids_log.txt","r");
        if ($handle) {      //      read complete IDS log file
            $ids_log = @file("$include_dir/IDS/tmp/phpids_log.txt");
            @fclose($handle);

            $handle = @fopen ("$include_dir/IDS/tmp/phpids_log.txt","w");
            foreach ($ids_log as $this_log) {
                if ($i != $row) {
                    $lines[] = $this_log;   //  take this row, if it should not been deleted
                    @fputs($handle, $this_log); //store this row in new log file
                }
                $i++;
            }
            @fclose($handle);

            $ids_count = count($lines);
            $all_lines = array_reverse($lines); //  newest intrusion on top of array
            preg_match("@\",(.*?),(.*?),@",$all_lines[0], $regs);
            $when = $regs[1];   //  extract date and time of latest intrusion
        }

        if ($ids_count) {
            $class = "odrow";
            echo "<br /><br />
                        <p class='headline cntr'>Intrusion Detection System</p>
                        <br /><br /><br />
                        <form action='' id='clearids'>
                        <table width='70%'>
                            <tr>
                                <td class='tblhead'>Log file</td>
                                <td class='tblhead'>Last intrusion</td>
                                <td class='tblhead' width='30%'>Option</td>
                            </tr>
                    ";
            echo "
                        <tr class='$class'>
                            <td class='cntr sml'>
                            <a href='$include_dir/IDS/tmp/phpids_log.txt' target='_blank' title='Open this Log File in new window'>$file</a></td>
                            <td class='cntr sml'>$when</td>
                            <td class='cntr sml options'><a href='?f=clear_ids' class='options' title='Click to clear this log file'
                                    onclick=\"return confirm('Are you sure you want to clear $file? Complete content will be lost.')\">Clear log file completely</a></td>
                        </tr>
                    </table>
                    </form>
                    <br /><br />
                    <p class=' cntr'>Delete individual entries from log file</p>
                    <br />
                    <form action='' id='del_idsrow'>
                    <table width='95%'>
                    <tr>
                        <td class='tblhead'>Created</td>
                        <td class='tblhead'>IP</td>
                        <td class='tblhead'>Impact</td>
                        <td class='tblhead'>Involved tags</td>
                        <td class='tblhead'>Option</td>
                    </tr>
                    ";

            $i = '0';
            foreach ($all_lines as $row) {

                preg_match("@\"(.*?)\",(.*?),(.*?),\"(.*?)\"@",$row, $regs);

                if ($class =="evrow")
                $class = "odrow";
                else
                $class = "evrow";

                echo "<tr class='$class'>
                            <td class='cntr sml'>$regs[2]</td>
                            <td class='cntr sml'>$regs[1]</td>
                            <td class='cntr sml'>$regs[3]</td>
                            <td class='cntr sml'>$regs[4]</td>
                            <td class='cntr sml options'><a href='?f=57&amp;row=$i' class='options' title='Click to delete this intrusion'
                                        onclick=\"return confirm('Are you sure you want to delete this row?')\">Delete</a></td>
                        ";
                $i++;
            }
            echo "
                            </tr>
                        </table>
                        </form>
                        <br /><br />
                        ";

        } else {
            echo "<br /><br />
                    <p class='cntr msg'>Note: <span class='warnadmin'>No events stored in IDS log file</span></p>
                    <br /> <br />
                ";
        }
        if ($back == '1') {
            echo "
                <a class='bkbtn' href='admin.php?f=clean' title='Jump back to Clean'>Back to Clean menu</a>
                ";
        } else {
            echo "
                <a class='bkbtn' href='admin.php?f=statistics' title='Jump back to Statistics'>Back to Statistics menu</a>
                ";

        }
        echo "
                </div>
                <br />
                </div>
            </body>
        </html>
            ";

        exit;
    }

    function cleanCats() {
        global $db_con, $mysql_table_prefix, $debug;
        $del ='0';
        $sql_query ="SELECT * from ".$mysql_table_prefix."site_category";
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

        if ($result->num_rows ) {
            while ($rows= $result->fetch_array(MYSQLI_ASSOC)) {
                $category_id = $rows['category_id'];
                $site_id = $rows['site_id'];
                if (!$site_id) {    //  delete all cats without any association
                    $sql_query ="DELETE from ".$mysql_table_prefix."site_category where category_id=$category_id";
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

                    $del++;
                } else {
                    $sql_query ="SELECT * from ".$mysql_table_prefix."sites where site_id=$site_id";
                    $res = $db_con->query($sql_query);
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

                    $site = $res->num_rows;
                    if (!$site) {    //  delete all cats without association to a valid site
                        $sql_query ="DELETE from ".$mysql_table_prefix."site_category where category_id=$category_id";
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
                        $del++;
                    }
                }
            }
        }

        echo "<div class='submenu'>&nbsp;</div>
                <p class='msg'>Category table cleared [<span class='warnok'> $del </span>] entries deleted.</p>
                <br />
                <a class='bkbtn' href='javascript:history.go(-1)' title='Go back a Page'>Back</a>
            ";
    }

    function cleanKeywords($back) {
        global $db_con, $mysql_table_prefix, $debug;

        $sql_query ="SELECT keyword_id, keyword from ".$mysql_table_prefix."keywords";
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

        $del 		= 0;
        $num_rows	= $result->num_rows;

        if ($num_rows) {
	        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
	            $keyId=$row['keyword_id'];
	            $keyword=$row['keyword'];
	            $sql_query ="SELECT keyword_id from ".$mysql_table_prefix."link_keyword where keyword_id = $keyId";
	            $result2 = $db_con->query($sql_query);
	            if ($debug > 0 && $db_con->errno) {
	                printf("MySQL failure: %s\n", $db_con->error);
	                echo "<br />Script aborted.";
	                exit;
	            }
	            if ($result2->num_rows < 1 && !strpos($keyword,"'")) {
	                $sql_query ="DELETE from ".$mysql_table_prefix."keywords where keyword_id=$keyId";
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

	                $del++;
	            }
	            $result2->free_result();
	        }
    	}
        if ($back) {
            echo "<div class='submenu cntr'>&nbsp;</div>
        <p class='msg cntr'>All keyword tables cleared.<br />Because of missing keyword/link relationship, $del words were deleted.</p>
        <a class='bkbtn' href='javascript:history.go(-1)' title='Go back a Page'>Back</a>
            ";
        }
    }

    function cleanLinks($back) {
        global $db_con, $mysql_table_prefix, $debug;

        $sql_query ="SELECT site_id from ".$mysql_table_prefix."sites";
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

        $todelete = array();
        if ($result->num_rows) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $todelete[]=$row['site_id'];
            }
            $todelete = implode(",", $todelete);
            $sql_end = " not in ($todelete)";
        }

        $sql_query ="SELECT link_id from ".$mysql_table_prefix."links where site_id".$sql_end ;
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

        $del = $result->num_rows;
        if ($del) {
	        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
	            $link_id=$row['link_id'];
                    $db_con->query("DELETE from ".$mysql_table_prefix."link_keyword where link_id=$link_id");
	            $db_con->query("DELETE from ".$mysql_table_prefix."links where link_id=$link_id");
	        }
    	}


        $sql_query = "SELECT link_id from ".$mysql_table_prefix."links where site_id is NULL";
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

        $del += $result->num_rows;
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $link_id=$row['link_id'];
            $db_con->query("DELETE from ".$mysql_table_prefix."link_keyword where link_id=$link_id");
            $db_con->query("DELETE from ".$mysql_table_prefix."links where link_id=$link_id");
        }
        if ($back) {
            echo "<div class='submenu cntr'>&nbsp;</div>
        <p class='msg cntr'>Link table cleared. $del links deleted.<br /></p>
        <a class='bkbtn' href='javascript:history.go(-1)' title='Go back a Page'>Back</a>
            ";
        }
    }

    function delRelated($site_id) {
        global $db_con, $mysql_table_prefix, $debug;

        $sql_query ="SELECT keyword_id, keyword from ".$mysql_table_prefix."keywords";
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

        $del = 0;
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $keyId=$row['keyword_id'];
            $keyword=$row['keyword'];
            $sql_query ="SELECT keyword_id from ".$mysql_table_prefix."link_keyword where keyword_id = $keyId";
            $result2 = $db_con->query($sql_query);

            if ($result2->num_rows < 1 && !strpos($keyword,"'")) {
                $db_con->query("DELETE from ".$mysql_table_prefix."keywords where keyword_id=$keyId");
                $del++;
            }
            $result2->free_result();
        }


        echo "<br /><p class='msg'>Site deleted...</p>
                <p class='msg'>Keyword tables cleared.&nbsp;<span class='warnadmin'>&nbsp;$del&nbsp;</span>&nbsp;words deleted.</p>
                <br />
                <a class='bkbtn' href='admin.php' title='Go back to Sites view'>Back</a>
            ";
    }

    function bound_db() {
        global $db_con, $mysql_table_prefix, $debug, $max_results;

        $count = '0';
        $sql_query ="SELECT * from ".$mysql_table_prefix."keywords";   //  get all keyword id's from db
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

        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $keyId = $row['keyword_id'];
            $keyword = $row['keyword'];

            $sql_query ="SELECT link_id, keyword_id, weight from ".$mysql_table_prefix."link_keyword where keyword_id = $keyId order by weight desc";
            $result2 = $db_con->query($sql_query);

            $all_results = array();
            while ($this_result = $result2->fetch_array(MYSQLI_ASSOC)){
                $all_results[] = $this_result;          // build an array containing all results for this keyword
            }
            $result2->free_result();
            if (count($all_results) > $max_results) {   // if too many results in db
                $rest_array = array_slice($all_results,$max_results);   // that array contains only result  > $max_results
                foreach ($rest_array as $kill_this) {
                    $this_link = $kill_this['link_id'];
                    $this_key = $kill_this['keyword_id'];

                    $db_con->query("DELETE from ".$mysql_table_prefix."link_keyword where link_id = $this_link and keyword_id = $this_key");
                    $count++;
                }
            }
        }


        echo "<br /><br />
                <p class='msg cntr'>Done.</p>
                <br />
                <p class='msg cntr'>All keyword tables cleared.<br /><br /><span class='warnadmin'>&nbsp;$count&nbsp;</span>&nbsp;overhanging results deleted.</p>
                <br />
                    <a class='bkbtn' href='admin.php?f=clean' title='Jump back to Clean'>Back to Clean menu</a>
                </div>
                <br />
                </div>
            </body>
        </html>
            ";
        exit;
    }

    function refreshSite() {
        global $db_con, $mysql_table_prefix, $site_id, $debug, $clear_cache, $clear_query, $not_erase;

        //  get current site-name and other attributes
        $sql_query ="SELECT url, required, disallowed from ".$mysql_table_prefix."sites where site_id =$site_id";
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

        $row    = $result->fetch_array(MYSQLI_ASSOC) ;


        $url        = $row['url'];
        $required   = explode("\r\n", $row['required']);
        $disallowed = explode("\r\n", $row['disallowed']);

        //      prepare the URL for multiple & and + as part of the URL
        $url_crypt  = str_replace("&", "-_-", $row['url']); //  crypt the & character
        $url_crypt  = str_replace("+", "_-_", $url_crypt);  //  crypt the + character
        $url_crypt  = htmlentities($url_crypt, ENT_QUOTES);

        //  delete complete query log in database
        if ($clear_query == '1') {
            $sql_query = "truncate ".$mysql_table_prefix."query_log";
            $db_con->query ($sql_query);
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

        //      get all links related to this site
        $sql_query ="SELECT link_id, url from ".$mysql_table_prefix."links where site_id=$site_id ";
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

        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $link_id=$row['link_id'];
            $url=$row['url'];

            //  try to find all "Must Not include"  words in URL in order to exclude from erasing
            foreach ($disallowed as $not_in_url) {

                if (!strpos($url, $not_in_url) || !$not_erase) {   //  erase all links not meeting the "Must Not include" string list

                    //  delete all keyword_id with their weights associated to this link
                    $db_con->query ("DELETE from ".$mysql_table_prefix."link_keyword where link_id=$link_id");
                    //  delete all link details
                    $db_con->query ("DELETE from ".$mysql_table_prefix."link_details where link_id=$link_id");
                    //  delete this link
                    $db_con->query ("DELETE from ".$mysql_table_prefix."links where  link_id=$link_id");
                    //  delete all media  data in database related to this link
                    $db_con->query ("DELETE from ".$mysql_table_prefix."media where link_id=$link_id");
                }
            }
        }


        //      delete those keywords that are no longer required in table 'keywords'
        $sql_query ="SELECT keyword_id, keyword from ".$mysql_table_prefix."keywords";
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

        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $keyId=$row['keyword_id'];
            $keyword=$row['keyword'];
            $sql_query ="SELECT keyword_id from ".$mysql_table_prefix."link_keyword where keyword_id = $keyId";
            $result2 = $db_con->query($sql_query);
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

            if ($result2->num_rows < 1) {
                $sql_query ="DELETE from ".$mysql_table_prefix."keywords where keyword_id=$keyId";
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
            $result2->free_result();
        }


        //  mark this site as not yet indexed
        $sql_query = "UPDATE ".$mysql_table_prefix."sites set indexdate='' where site_id =$site_id";
        $db_con->query ($sql_query);
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
        echo "<div class='submenu cntr'>Erase &amp; Re-index for site: $url</div>
                ";
        if ($clear_cache == '1') {
            $back = '';
            clearTextCache($back);
            clearMediaCache($back);
        }
        echo "
                <p class='cntr em sml'>
                <br />
                Site specific data cleaned from
                <br />
                MySQL database and thumbnails folder.
                <br /><br /><br />
                <form  class='cntr' action='admin.php' method='post'>
                    <input type='hidden' name='f' value='index' />
                    <input type='hidden' name='adv' value='0' />
                    <input type='hidden' name='reindex' value='1' />
                    <input type='hidden' name='url' value='$url_crypt' />
                    <input type='submit' value='Start now to re-index this site'>
                </form>
                <br /><br />
                <p class='cntr em'><a class='bkbtn' href='admin.php' title='Back to admin'>Return to admin without re-index</a></p>
                <br /><br />
                </p>
            ";
    }

    function eraseSite() {
        global $db_con, $mysql_table_prefix, $site_id, $debug, $thumb_dir, $clear_cache, $clear_query, $not_erase;

        //  get current site-name
        $sql_query ="SELECT url, required, disallowed from ".$mysql_table_prefix."sites where site_id =$site_id";
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

        $row = $result->fetch_array(MYSQLI_ASSOC) ;

        $site=$row['url'];
        $required   = explode("\r\n", $row['required']);
        $disallowed = explode("\r\n", $row['disallowed']);

        //  delete complete query log in database
        if ($clear_query == '1') {
            $sql_query = "truncate ".$mysql_table_prefix."query_log";
            $db_con->query ($sql_query);
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

        //      get all links related to this site
        $sql_query ="SELECT link_id, url from ".$mysql_table_prefix."links where site_id=$site_id ";
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

        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $link_id=$row['link_id'];
            $url=$row['url'];

            //  try to find all "Must Not include"  words in URL in order to exclude from erasing
            foreach ($disallowed as $not_in_url) {

                if (!strpos($url, $not_in_url) || !$not_erase) {   //  erase all links not meeting the "Must Not include" string list

                    //  delete all keyword_id with their weights associated to this site
                    $db_con->query ("DELETE from ".$mysql_table_prefix."link_keyword where link_id=$link_id");
                    //  delete all links related to this site
                    $db_con->query ("DELETE from ".$mysql_table_prefix."links where link_id=$link_id");
                    //  delete all media  data in database related to this link
                    $db_con->query ("DELETE from ".$mysql_table_prefix."media where link_id=$link_id");

                    //  delete all thumbnails related to this link
                    if (is_dir("./$thumb_dir/")) {
                        if ($dh = opendir("./$thumb_dir/")) {
                            while (($thumbfile = readdir($dh)) !== false) {
                                //	  delete only thumb-files with this link_id  and the currently defined table prefix
                                if (preg_match("/".$link_id."_-_/i", $thumbfile) && preg_match("/...".$mysql_table_prefix.".../i", $thumbfile)) {
                                    @unlink("./$thumb_dir/$thumbfile");	//	  delete this file
                                    $i++ ;	  //	  count all files
                                }
                            }
                            closedir($dh);
                        }
                    }
                }
            }
        }


        //      delete those keywords that are no longer required in table 'keywords'
        $sql_query ="SELECT keyword_id, keyword from ".$mysql_table_prefix."keywords";
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

        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $keyId=$row['keyword_id'];
            $keyword=$row['keyword'];
            $sql_query ="SELECT keyword_id from ".$mysql_table_prefix."link_keyword where keyword_id = $keyId";
            $result2 = $db_con->query($sql_query);

            if ($result2->num_rows < 1) {
                $db_con->query("DELETE from ".$mysql_table_prefix."keywords where keyword_id=$keyId");
            }
            $result2->free_result();
        }


        //  mark this site as not yet indexed
        $sql_query = "UPDATE ".$mysql_table_prefix."sites set indexdate='' where site_id =$site_id";
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

        echo "<div class='submenu cntr'>Erase site: $site</div>
                <p class='cntr em sml'>
                <br />
                Site specific data cleaned from
                <br />
                MySQL database and thumbnails folder.
                <br /><br />
                <p class='cntr em'><a class='bkbtn' href='admin.php' title='Back to admin'>Return to admin</a></p>
                <br /><br />
                </p>
            ";
    }


    function addsite($url, $title, $short_desc, $cat, $def_include, $smap_url, $authent, $prior_level) {
        global $db_con,  $mysql_table_prefix, $debug,  $dba_act, $common_dir, $add_auth, $home_charset, $curl;
        global $depth, $domaincb, $use_prefcharset, $include_dir, $idna, $conv_puny, $cyrillic;

        if ($conv_puny && strstr($url, "xn--")) {
            require_once "$include_dir/idna_converter.php";
            // Initialize the converter class
            $IDN = new idna_convert(array('idn_version' => 2008));
            // Decode it to its readable presentation
            $url = $IDN->decode($url);
        }

        $url = urldecode($url);      //  get it readable
        if ($cyrillic) {
            $url        = to_utf8($url);    //  because of the bug in PHP function urldecode() we need special processing for CP1252 charset
        }

        $compurl    = parse_url("".$url);   //  we will need all details of the URL

        //  https scheme requires cURL extension
        if(!$curl && $compurl['scheme'] == "https") {
            $message = "<p class='msg cntr'><br /><br /><span class='warnadmin'>Sorry, but in order to index URLs containing the https scheme,<br />you need to install the cURL extension on your server.</span><br /><br /><br /></p>";
            echo "$message";
            addsiteform();
            exit;

        }

        //  find out whether the URL contains www. or only basic domain
        //  also remove scheme (http <-> https
        //  only one will be accepted as new URL to be added to the database
        $url1 = $compurl['host']."".$compurl['path'];
        $url1 = str_replace("www.", "", $url1);
        if ($compurl['path']=='') {
            $url1   = $url1."/";
        }

        if (strpos($url1, "?")) {
            $url1 = substr($url1, 0, strpos($url1, "?"));   //  remove arguments
        }
        $url1 = $db_con->real_escape_string($url1);
        //  now check against already existing site URLs
        $sql_query = "SELECT site_ID from ".$mysql_table_prefix."sites where url like'%$url1%'";
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

        $rows = $result->num_rows;


        if ($rows==0) {
            $must_include       = '';
            $must_not_include   = '';
            if ($def_include == '1') {
                //  get default values for URL 'must_include' and 'must_not_include'
                $must_include       = addslashes(@file_get_contents("$common_dir/must_include.txt"));
                $must_not_include   = addslashes(@file_get_contents("$common_dir/must_not_include.txt"));
            }

            //  valid sitemap url?
            if (!preg_match("/http:\/\//", $smap_url)) {
                $smap_url = 'NULL';
            }

            $sql_query = "SELECT * from ".$mysql_table_prefix."sites";
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

            $count = $result->num_rows;   //  current count of URLs in table 'sites'

            // clean admin's input
            $url = $db_con->real_escape_string(substr(trim($url),0,1024));

            if ($title) {
                $title = $db_con->real_escape_string(cleaninput(substr(trim($title),0,255)));
            }

            if ($short_desc) {
                $short_desc = $db_con->real_escape_string(cleaninput(trim($short_desc)));
            }

            if ($disallowed) {
                $disallowed = $db_con->real_escape_string(cleaninput(trim($disallowed)));
            }

            if ($smap_url) {
                $smap_url = $db_con->real_escape_string(substr(trim($smap_url),0,1024));
            }

            if ($authent) {
                $authent = $db_con->real_escape_string(cleaninput(substr(trim($authent),0,255)));
            }

            //  insert new URL into sites table
            $sql_query = "INSERT INTO ".$mysql_table_prefix."sites (url, title, short_desc, spider_depth, required, disallowed, can_leave_domain, db, smap_url, authent, use_prefcharset, prior_level)
                                                            VALUES ('$url', '$title', '$short_desc', '$depth', '$must_include', '$must_not_include', '$domaincb', '$dba_act', '$smap_url', '$authent', '$use_prefcharset', '$prior_level')";
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

            $sql_query = "SELECT site_ID from ".$mysql_table_prefix."sites where url='$url'";
            $result = $db_con->query($sql_query);
            if ($db_con->errno) {
                $err_row = __LINE__-2;
                printf("<p><span class='red'>&nbsp;MySQL failure: %s&nbsp;\n<br /></span></p>", $db_con->error);
                if (__FUNCTION__) {
                    printf("<p><span class='red'>&nbsp;Found in script: ".__FILE__."&nbsp;&nbsp;row: $err_row&nbsp;&nbsp;in function():&nbsp;".__FUNCTION__."&nbsp;<br /></span></p>");
                } else {
                    printf("<p><span class='red'>&nbsp;Found in script: ".__FILE__."&nbsp;&nbsp;row: $err_row&nbsp;<br /></span></p>");
                }
                printf("<p><span class='red'>&nbsp;Script execution aborted.&nbsp;<br /></span>");
                echo   "<p> $sql_query </p>";
                exit;
            }

            $row = $result->fetch_array(MYSQLI_NUM);

            $site_id = $row[0];

            $sql_query = "SELECT category_id from ".$mysql_table_prefix."categories";
            $result=$db_con->query($sql_query);
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

            while ($row = $result->fetch_array(MYSQLI_NUM)) {
                $cat_id=$row[0];
                if ($cat[$cat_id]=='on') {
                    $db_con->query("INSERT INTO ".$mysql_table_prefix."site_category (site_id, category_id) values ('$site_id', '$cat_id')");
                }
            }


            if (!$db_con->errno) {
                $message =  "<p class='msg'>&nbsp;&nbsp;&nbsp;&nbsp;New Site added to database $dba_act ...</p>" ;
            }
        } else {
            echo "<br />
                    <p class='msg cntr'><span class='warnadmin'>&nbsp;$url&nbsp;</span></p>
                    <br />
                    <p class='msg cntr'><span class='warnadmin'>&nbsp;Site already in database&nbsp;</span></p>
                    <br />
                ";
            addsiteform();
            exit;
        }

        //  delete all invalid URLs from table 'sites'
        $sql_query ="DELETE from ".$mysql_table_prefix."sites where site_id='0' OR site_id=''";
        $db_con->query ($sql_query);
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

        $sql_query = "SELECT* from ".$mysql_table_prefix."sites";
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

        $new_count = $result->num_rows; //    count of URLs after adding new site

        if ($count == $new_count) {
            $message =  "<p class='msg'>&nbsp;&nbsp;&nbsp;&nbsp;New Site not added to database $dba_act, because invlid</p>" ;
        }
        return $message;
    }

    function indexscreen($url, $reindex) {
        global $db_con, $mysql_table_prefix, $real_log, $debug, $conv_puny, $cyrillic, $curl, $include_dir;
/*
        if ($conv_puny && strstr($url, "xn--")) {
            require_once "$include_dir/idna_converter.php";
            // Initialize the converter class
            $IDN = new idna_convert(array('idn_version' => 2008));
            // Decode it to its readable presentation
            $url = $IDN->decode($url);
        }
*/
        $url        = urldecode($url);      //  get it readable
        if ($cyrillic) {
            $url        = to_utf8($url);    //  because of the bug in PHP function urldecode() we need special processing for CP1252 charset
        }

        $compurl    = parse_url("".$url);   //  we will need all details of the URL

        //  https scheme requires cURL extension
        if(!$curl && $compurl['scheme'] == "https") {
            $message = "<p class='msg cntr'><br /><br /><span class='warnadmin'>Sorry, but in order to index URLs containing the https scheme,<br />you need to install the cURL extension on your server.</span><br /><br /><br /></p>";
            echo "$message";
            addsiteform();
            exit;

        }

        $check = "";
        $levelchecked = 'checked="checked"';
        $spider_depth = 2;

        if ($url=="") {
            $url        = "http://";
            $url_crypt  = "";
        } else {

            //      prepare the URL for multiple & and + as part of the URL
            $url_crypt  = str_replace("&", "-_-", $url);   //  crypt the & character
            $url_crypt  = str_replace("+", "_-_", $url_crypt);  //  crypt the + character
            $url_crypt  = htmlentities($url_crypt, ENT_QUOTES);
            $url_ent  = htmlentities($url, ENT_QUOTES);

            $advurl = $url;
            $sql_query = "SELECT spider_depth, required, disallowed, can_leave_domain, use_prefcharset from ".$mysql_table_prefix."sites " .
                        "where url='$url_ent'";
            $result = $db_con->query($sql_query);

            if ($result->num_rows) {
                $row = $result->fetch_array(MYSQLI_NUM);
                $spider_depth = $row[0];
                if ($spider_depth == -1 ) {
                    $fullchecked = 'checked="checked"';
                    $spider_depth ="";
                    $levelchecked = "";
                }
                $must               = $row[1];
                $mustnot            = $row[2];
                $canleave           = $row[3];
                $use_pref           = $row[4];
            }
        }

        echo "<br />
            ";
        if ($must !="" || $mustnot !="" || $canleave == 1 || $use_pref) {
            $_SESSION['index_advanced']=1;
        }
        if ($_SESSION['index_advanced']==1){
            echo "<form class='cntr sml' action='admin.php' method='post'>
                    <input type='hidden' name='f' value='index' />
                    <input type='hidden' name='adv' value='0' />
                    <input type='hidden' name='url' value='$url_crypt' />
                    <input class='cntr sbmt' type='submit' id='submit' value='&nbsp;Hide advanced options&nbsp;' title='Click to hide the advanced options in this menue' />
                    </form>
                ";
        } else {
            echo "<form class='cntr sml' action='admin.php' method='post'>
                    <input type='hidden' name='f' value='index' />
                    <input type='hidden' name='adv' value='1' />
                    <input type='hidden' name='url' value='$url_crypt' />
                    <input class='cntr sbmt' type='submit' id='submit' value='&nbsp;Show advanced options&nbsp;' title='Click to show all the advanced options in this menue' />
                    </form>
                ";
        }

        echo "<br />
                <div class='panel w75'>
                <form class='txt' action='spider.php' method='post'>
                <fieldset><legend>[ Basic Indexing Options ]</legend>
                <label class='em' for='url'>Address:</label>
                <input type='text' name='url' id='url' size='68' maxlength='1024' title='Enter new URL' value='$url_crypt' />
                <label class='em' for='soption'>Spidering options:</label>
                <input type='radio' name='soption' id='soption' title='Check box for Full indexing' value='full' $fullchecked /> Full<br />
                <input type='radio' name='soption' value='level' title='Check box to limit indexing depth' $levelchecked />
                Index depth:
                <input type='text' name='maxlevel' size='2' title='Enter indexing depth level' value='$spider_depth' />
            ";

        if ($reindex==1) {$check='checked="checked"';}
        echo "<label class='em' for='reindex'>Re-index</label>
                <input type='checkbox' name='reindex' id='reindex' title='Check box to Re-index' value='1' $check /> Check to Re-index
                </fieldset>
            ";

        if ($_SESSION['index_advanced']==1){
            if ($canleave==1) {$checkcan='checked="checked"' ;}
            if ($use_pref==1) {$use_pref='checked="checked"' ;}
            echo "<fieldset><legend>[ Advanced Indexing Options ]</legend>
                    <label class='em' for='can_leave'>Spider can leave domain?</label>
                    <input type='checkbox' name='can_leave' id='can_leave' value='1' title='Check box if Sphider can leave above domain' $checkcan /> Check for Yes
                    <label class='em' for='use_pref'>Use preferred charset for indexing?</label>
                    <input type='checkbox' name='use_pref' id=use_pref'' value='1' title='Check box if Sphider should use the preferred charset as defined in \"Settings\"' $use_pref /> Check for Yes
                    <label class='em' for='reindex'>robots.txt</label>
                    <input type='hidden' name='not_use_robot' value='0' />
                    <input type='checkbox' name='not_use_robot' value='1' $not_use_robot /> Temporary ignore 'robots.txt'
                    <label class='em' for='nofollow'>'nofollow' tags</label>
                    <input type='hidden' name='not_use_nofollow' value='0' />
                    <input type='checkbox' name='not_use_nofollow' value='1' $not_use_nofollow /> Temporary ignore 'nofollow' directive
                    </fieldset>
                    <fieldset><legend>[ Include/Exclude Options ]</legend>
                    <label class='em' for='in'>URL Must include:</label>
                    <textarea name='in' id='in' cols='35' rows='5' title='Enter URLs that Must be included, one per line'>$must</textarea>
                    <label class='em' for='out'>URL must Not include:</label>
                    <textarea name='out' id='out' cols='35' rows='5' title='Enter URLs that must Not be included, one per line'>$mustnot</textarea></fieldset>
                ";
        }
        echo "<fieldset><legend>[ Start Indexing ]</legend>
            ";
        if ($real_log == '1') {
            echo "
                    <input class='cntr sbmt' type='submit' id='submit' value='&nbsp;Start&nbsp;' title='Click to start indexing process' onclick=\"window.open('real_log.php')\" />
                ";
        }else{
            echo "
                    <input class='cntr sbmt' type='submit' id='submit' value='&nbsp;Start&nbsp;' title='Click to start indexing process' />
                ";
        }
        echo "
                </fieldset>
                </form>
                </div>
            ";
    }

    function siteScreen($site_id, $message)  {
        global $db_con, $mysql_table_prefix, $indexoption, $debug, $interval;

        $sql_query = "SELECT site_id, url, title, short_desc, indexdate from ".$mysql_table_prefix."sites where site_id=$site_id";
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

        $row=$result->fetch_array(MYSQLI_ASSOC);


        //      prepare the URL for multiple & and + as part of the URL
        $url_crypt  = str_replace("&", "-_-", $row[url]);   //  crypt the & character
        $url_crypt  = str_replace("+", "_-_", $url_crypt);  //  crypt the + character
        $url_crypt  = htmlentities($url_crypt, ENT_QUOTES);

        if ($row['indexdate']=='') {
            $indexstatus="<span class='warnadmin cntr'>Not indexed</span>";
            $indexoption="<a href='admin.php?f=index&amp;url=$url_crypt' title='Click to start indexing this site'>Index<br />&nbsp;</a>";
        } else {
            $site_id = $row['site_id'];
            $sql_query ="SELECT site_id from ".$mysql_table_prefix."pending where site_id =$site_id";
            $result2 = $db_con->query($sql_query);
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

            $row2 = $result2->fetch_array(MYSQLI_ASSOC);
            $result2->free_result();

            if ($row2['site_id'] == $row['site_id']) {
                $indexstatus = "Unfinished";
                $indexoption="<a href='admin.php?f=index&amp;url=$url_crypt' title='Continue paused or incomplete indexing'><span class=\"warnadmin\">&nbsp;&nbsp;Continue&nbsp;&nbsp;<br />&nbsp;&nbsp;indexing&nbsp;&nbsp;</span></a>";
            } else {
                $indexstatus = $row['indexdate'];
                $indexoption="<a href='admin.php?f=index&amp;url=$url_crypt&amp;reindex=1' title='Re-index this site'>Re-index<br />&nbsp;</a>";
            }
        }
        echo "<div class='submenu cntr'>| Manage Site Options |</div>
        ";
        echo $message;
        $sitename = $row['title'];
        echo "<div class='panel w85'>
                <dl class='cntr'>
                    <dt class='headline bd x6'>Title:</dt><dd class='headline em'>&nbsp;".stripslashes($row['title'])."</dd>
                    <dt class='odrow bd x6'>URL:</dt>
                    <dd class='odrow'><a href='".$row['url']."' target='_blank' title='Visit site in new window'>".$row['url']."</a></dd>
                    <dt class='evrow bd x6'>Description:</dt><dd class='evrow'>&nbsp;".stripslashes($row['short_desc'])."</dd>
                    <dt class='odrow bd x6'>&nbsp;Last indexed:</dt><dd class='odrow'>&nbsp;$indexstatus</dd>
                </dl>
                <div class='evrow'>
                <div id='vertmenu'>
                <p class='bd'>Management Options:</p>
                <ul>
                    <li><a href='admin.php?f=edit_site&amp;site_id=".$row['site_id']."' title='Edit indexing parameters'>Edit<br />&nbsp;</a></li>
                    <li title='Start indexing this site'>$indexoption</li>
                    <li><a href='admin.php?f=48&amp;site_id=".$row['site_id']."' title='Erase all stored data of this site and afterwards perform a re-index'>Erase&nbsp;&nbsp;&amp;<br />Re-index</a></li>
                    <li><a href='admin.php?f=55&amp;site_id=".$row['site_id']."' title='Erase all stored data of this site'>Erase<br />&nbsp;</a></li>
                    <li><a href='admin.php?f=5&amp;site_id=".$row['site_id']."' title='Delete Entire Site and Indexing'
                    onclick=\"return confirm('Are you sure you want to Delete the $sitename site? All Site Details and Indexing will be lost!')\">Delete<br />&nbsp;</a></li>
                    <li><a href='admin.php?f=46&amp;site_id=".$row['site_id']."' title='Show all pages belonging to this site'>Pages<br />&nbsp;</a></li>
                    <li><a href='admin.php?f=21&amp;site_id=".$row['site_id']."' title='Browse indexed pages'>Browse<br />&nbsp;</a></li>
                    <li><a href='admin.php?f=19&amp;site_id=".$row['site_id']."' title='Generate site statistics'>Statistics<br />&nbsp;</a></li>
                ";
        if ($interval != 'never') {
            echo "
                    <li><a href='admin.php?f=59&amp;site_id=".$row['site_id']."' title='Start the periodical Re-indexer for this site'>Periodical<br />Re-index</a></li>
                ";
        }
        echo "
                    </ul></div>
                    <div class='clear'></div><br />
                    <a class='bkbtn' href='admin.php?f=2' title='Go back to Site Menu'>Back</a></div>
                </div>
            ";
    }

    function show_links($site_id) {
        global $db_con, $mysql_table_prefix, $debug;

        $sql_query = "SELECT site_id, url, title, short_desc, indexdate from ".$mysql_table_prefix."sites where site_id=$site_id";
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

        $row = $result->fetch_array(MYSQLI_ASSOC);
        $result->free_result;
        $url = replace_ampersand($row['url']);

        //      Headline for Show links
        echo "<div class='submenu cntr'>| Show all Pages of Url ' $url ' |</div>
            ";

        //      Get all links of this Url.
        $sql_query = "SELECT * from ".$mysql_table_prefix."links where site_id = '$site_id'";
        $res = $db_con->query($sql_query);
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

        $num_rows   = $res->num_rows;

		if ($num_rows) {
	        while($this_link = $res->fetch_array(MYSQLI_ASSOC)){
	            $links[] = $this_link;
	        }
    	}

        $class = "evrow";
        if (!$num_rows)	{
            print "<br /><div id =\"result_report\" class='cntr'>The search didn't match any indexed links</div>";
        } else {    //      Display header row and all results
            echo "<div class='panel'>
                    <table width='90%' class='cntr'>
                    <tr>
                        <td class='headline'>Count</td>
                        <td class='headline'>Page Url</td>
                        <td class='headline'>Last indexed</td>
                        <td class='headline'>Page size</td>
                    </tr>
                ";

            for ($i=0; $i<$num_rows; $i++) {
                $url2       = $links[$i]['url'];
                $indexed    = $links[$i]['indexdate'];
                $page_size  = $links[$i]['size'];
                $count =$i+1;
                if ($class =="evrow") {
                    $class = "odrow";
                }else{
                    $class = "evrow";
                }

                echo "<tr class='$class'>
                        <td>$count</td>
                        <td><a href='$url2' target='_blank' title='Visit in new window'>$url2</a></td>
                        <td>$indexed</td>
                        <td>$page_size kB</td>
                        </tr>
                    ";
            }
            echo "</table>
                    <a class='bkbtn' href='admin.php?f=20&amp;site_id=$site_id' title='Go back to Site Options'>Back</a>
                ";
        }
    }

    function siteStats($site_id) {
        global $db_con, $mysql_table_prefix, $debug;

        $sql_query = "SELECT url from ".$mysql_table_prefix."sites where site_id=$site_id";
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

        if ($row = $result->fetch_array(MYSQLI_NUM)) {
            $url=$row[0];

            $lastIndexQuery = "SELECT indexdate from ".$mysql_table_prefix."sites where site_id = $site_id";
            $sumSizeQuery = "SELECT sum(length(fulltxt)) from ".$mysql_table_prefix."links where site_id = $site_id";
            $siteSizeQuery = "SELECT sum(size) from ".$mysql_table_prefix."links where site_id = $site_id";
            $linksQuery = "SELECT count(*) from ".$mysql_table_prefix."links where site_id = $site_id";

            $result = $db_con->query($lastIndexQuery);
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
                printf("<p>$lastIndexQuery</p>");
                exit;
            }

            if ($row = $result->fetch_array(MYSQLI_NUM)) {
                $stats['lastIndex']=$row[0];
            }

            $result = $db_con->query($sumSizeQuery);
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
                printf("<p>$sumSizeQuery</p>");
                exit;
            }

            if ($row = $result->fetch_array(MYSQLI_NUM)) {
                $stats['sumSize']=$row[0];
            }
            $result = $db_con->query($linksQuery);
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
                printf("<p>$linksQuery</p>");
                exit;
            }

            if ($row = $result->fetch_array(MYSQLI_NUM)) {
                $stats['links']=$row[0];
            }

            $result = $db_con->query("SELECT count(*) from ".$mysql_table_prefix."links, ".$mysql_table_prefix."link_keyword
                                    where ".$mysql_table_prefix."links.link_id=".$mysql_table_prefix."link_keyword$char.link_id and ".$mysql_table_prefix."links.site_id = $site_id");

            if ($row = $result->fetch_array(MYSQLI_NUM)) {
                $stats['index']+=$row[0];
            }
            $wordQuery = "SELECT count(distinct keyword) from ".$mysql_table_prefix."keywords, ".$mysql_table_prefix."links, ".$mysql_table_prefix."link_keyword
                    where ".$mysql_table_prefix."links.link_id=".$mysql_table_prefix."link_keyword.link_id and ".$mysql_table_prefix."links.site_id = $site_id
                    and ".$mysql_table_prefix."keywords.keyword_id = ".$mysql_table_prefix."link_keyword.keyword_id";
            $result = $db_con->query($wordQuery);

            if ($row = $result->fetch_array(MYSQLI_NUM)) {
                $stats['words']+=$row[0];
            }

            $result = $db_con->query($siteSizeQuery);
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
                printf("<p>$siteSizeQuery</p>");
                exit;
            }

            if ($row = $result->fetch_array(MYSQLI_NUM)) {
                $stats['siteSize']=$row[0];
            }
            if ($stats['siteSize']=="")
            $stats['siteSize'] = 0;
            $stats['siteSize'] = number_format($stats['siteSize'], 2);
            echo "<div class='submenu'></div>
                    <div class='panel'>
                    <dl class='tblhead'>
                    <dt class='headline x5'>Statistics for site:</dt>
                    <dd class='odrow'><a class='options' href='admin.php?f=20&amp;site_id=$site_id' title='Return to site options screen'>".rtrim(substr($url,0,65))."</a></dd>
                    <dt class='evrow bd x5'>Last Indexed:</dt><dd class='evrow'>&nbsp;".$stats['lastIndex']."</dd>
                    <dt class='odrow bd x5'>Pages indexed:</dt><dd class='odrow'>&nbsp;".$stats['links']."</dd>
                    <dt class='evrow bd x5'>Total index size:</dt><dd class='evrow'>&nbsp;".$stats['index']."</dd>
                ";
            $sum = number_format($stats['sumSize']/1024, 2);
            echo "<dt class='odrow bd x5'>Cached texts:</dt><dd class='odrow'>&nbsp;$sum Kb</dd>
                    <dt class='evrow bd x5'>Keywords Total:</dt><dd class='evrow'>&nbsp;".$stats['words']."</dd>
                    <dt class='odrow bd x5'>Site size:</dt><dd class='odrow'>&nbsp;".$stats['siteSize']."kb</dd>
                    </dl>
                    <a class='bkbtn' href='admin.php?f=20&amp;site_id=$site_id' title='Go back to Site Options'>Back</a>
                    </div>
                ";
        }

    }

    function browsePages($site_id, $start, $filter, $per_page) {
        global $db_con, $mysql_table_prefix, $debug;

        $sql_query = "SELECT url from ".$mysql_table_prefix."sites where site_id=$site_id";
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

        $row = $result->fetch_array(MYSQLI_NUM);
        $url = $row[0];

        $query_add = "";
        if ($filter != "") {
            $query_add = "and url like '%$filter%'";
        }
        $linksQuery = "SELECT count(*) from ".$mysql_table_prefix."links where site_id = $site_id $query_add";
        $result = $db_con->query($linksQuery);
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
            printf("<p>$linksQuery</p>");
            exit;
        }


        $row = $result->fetch_array(MYSQLI_NUM);
        $numOfPages = $row[0];

        $result = $db_con->query($linksQuery);
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
            printf("<p>$linksQuery</p>");
            exit;
        }

        $from = ($start-1) * 10;
        $to = min(($start)*10, $numOfPages);


        $linksQuery = "SELECT link_id, url from ".$mysql_table_prefix."links where site_id = $site_id and url like '%$filter%' order by url limit $from, $per_page";
        $result = $db_con->query($linksQuery);
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
            printf("<p>$linksQuery</p>");
            exit;
        }

        echo "<div class='submenu cntr'>|Browse Pages |</div>
                <div class='panel'>
                <p class='headline'>| Indexed Pages List |</p>
                <p class='headline'>
                Pages of site: <a href='admin.php?f=20&amp;site_id=$site_id' target='_blank' title='Open site in new window'>$url</a></p>
                <div id='settings'>
                <table width='100%'>
            ";
        $class = "evrow";
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            if ($class =="evrow"){
                $class = "odrow";
            }else{
                $class = "evrow";
            }
            echo "<tr class='$class'>
                    <td><a title='Open page in new window' target='rel' href='".$row['url']."'>".$row['url']."</a></td>
                        <td width='8%'><a class='options' title='Click to delete!'
                        href='admin.php?link_id=".$row['link_id']."&amp;f=22&amp;site_id=$site_id&amp;start=1&amp;filter=$filter&amp;per_page=$per_page'
                        onclick=\"return confirm('Are you sure you want to delete? Page will be dropped.')\">Delete</a>
                    </td></tr>
                ";
        }
        echo "<tr><td colspan='2'></td></tr>
                <tr><td class='evrow' colspan='2'>
            ";
        $pages = ceil($numOfPages / $per_page);
        $prev = $start - 1;
        $next = $start + 1;

        if ($pages > 0) {
            echo "Pages:
                ";
        }
        $links_to_next =10;
        $firstpage = $start - $links_to_next;
        if ($firstpage < 1) {
            $firstpage = 1;
        }
        $lastpage = $start + $links_to_next;
        if ($lastpage > $pages) {
            $lastpage = $pages;
        }
        for ($x=$firstpage; $x<=$lastpage; $x++) {
            if ($x<>$start) {
                echo "<a href='admin.php?f=21&amp;site_id=$site_id&amp;start=$x&amp;filter=$filter&amp;per_page=$per_page'
                        title='Go to Next Page'>$x</a>
                    ";
            } else {
                echo "<span class='em'>$x </span>
                    ";
            }
        }
        echo "</td></tr>
                </table>
                </div>
                <form class='txt' action='admin.php' method='post'>
                    <input type='hidden' name='start' value='1'
                    />
                    <input type='hidden' name='site_id' value='$site_id'
                    />
                    <input type='hidden' name='f' value='21'
                    />
                    <fieldset><legend>Page Filtering</legend>
                    <label class='em' for='per_page'>URLs per page</label>
                    <input type='text' name='per_page' id='per_page' size='3' value='$per_page'
                    /> URLs
                    <label class='em' for='filter'>URL contains:</label>
                    <input type='text' name='filter' id='filter' size='15' value='$filter'
                    /></fieldset>
                    <fieldset><legend>Apply File Filter</legend>
                    <input class='sbmt' type='submit' id='submit' value='Filter'
                    /></fieldset></form>
                    <a class='bkbtn'href='admin.php?f=20&amp;site_id=$site_id' title='Go back to Site Options'>Back</a>
                </div>
            ";
    }


    function cleanForm() {
        global $db_con, $mysql_table_prefix, $log_dir, $debug, $thumb_dir, $include_dir, $max_results;
        global $textcache_dir, $mediacache_dir, $tcache_size, $mcache_size, $dba_act, $tmp_folder, $xml_dir;

        $link_clicks = '0';
        $sql_query = "SELECT * from ".$mysql_table_prefix."links where click_counter > 0";
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

        if ($row = $result->num_rows) {
            $link_clicks=$row;
        }

        $media_clicks = '0';
        $sql_query = "SELECT * from ".$mysql_table_prefix."media where click_counter > 0";
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

        if ($row = $result->num_rows) {
            $media_clicks=$row;
        }

        $sql_query ="SELECT count(*) from ".$mysql_table_prefix."query_log";
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

        if ($row = $result->fetch_array(MYSQLI_NUM)) {
            $log=$row[0];
        }

        $sql_query = "SELECT count(*) from ".$mysql_table_prefix."temp";
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

        if ($row = $result->fetch_array(MYSQLI_NUM)) {
            $temp=$row[0];
        }

        $sql_query = "SELECT count(*) from ".$mysql_table_prefix."pending";
        $result = $db_con->query($sql_query);
        if ($debug && $db_con->errno) {
            $err_row = __LINE__-2;
            printf("<p><span class='red'>&nbsp;MySQL failure: %s&nbsp;\n<br /></span></p>", $db_con->error);
            if (__FUNCTION__) {
                printf("<p><span class='red'>&nbsp;Found in script: ".__FILE__."&nbsp;&nbsp;row: $err_row&nbsp;&nbsp;in function():&nbsp;".__FUNCTION__."&nbsp;<br /></span></p>");
            } else {
                printf("<p><span class='red'>&nbsp;Found in script: ".__FILE__."&nbsp;&nbsp;row: $err_row&nbsp;<br /></span></p>");
            }
            printf("<p><span class='red'>&nbsp;Script execution aborted.&nbsp;<br /></span>");;
            printf("<p><strong>Invalid query string, which caused the SQL error:</strong></p>");
            echo   "<p> $sql_query </p>";
            exit;
        }

        if ($row = $result->fetch_array(MYSQLI_NUM)) {
            $pending=$row[0];
        }
        $sql_query = "SELECT count(*) from ".$mysql_table_prefix."media";
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

        if ($row = $result->fetch_array(MYSQLI_NUM)) {
            $med_links=$row[0];
        }
        $sql_query = "SELECT count(*) from ".$mysql_table_prefix."addurl";
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

        if ($row = $result->fetch_array(MYSQLI_NUM)) {
            $addurl_count=$row[0];
        }
        $sql_query = "SELECT count(*) from ".$mysql_table_prefix."banned";
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

        if ($row = $result->fetch_array(MYSQLI_NUM)) {
            $banned_count=$row[0];
        }

        if (is_dir("./$log_dir/")) {
            if ($dh = opendir("./$log_dir/")) {
                $i = '0';
                while (($logfile = readdir($dh)) !== false) {
                    if (preg_match("/\.log$/i", $logfile) || preg_match("/\.html$/i", $logfile)) {  //	  only *.html and *.log are valid log files
                        $i++ ;	  //	  count all log files
                    }
                }
                closedir($dh);
            }
        }

        $t_size  = '0';
        $t_count = '0';
        if ($handle = opendir($textcache_dir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    $t_size = $t_size + (filesize("".$textcache_dir."/".$file.""));
                    $t_count++;
                }
            }
        }

        if ($t_size > '0') {
            $len = strlen($t_size);
            if ($len > '3') {
                $kb = $len-3;
                $t_size = substr($t_size, 0, $kb);
            }
        }

        $m_size  = '0';
        $m_count = '0';
        if ($handle = opendir($mediacache_dir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    $m_size = $m_size + (filesize("".$mediacache_dir."/".$file.""));
                    $m_count++;
                }
            }
            @fclose($handle);
        }
        if ($m_size > '0') {
            $len = strlen($m_size);
            if ($len > '3') {
                $kb = $len-3;
                $m_size = substr($m_size, 0, $kb);
            }
        }

        $tmp_count = "0";
        if ($handle = opendir(".".$tmp_folder)) {

            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    $tmp_count++;
                }
            }
            @fclose($handle);
        }

        $handle = @fopen ("$include_dir/IDS/tmp/phpids_log.txt","r");
        if ($handle) {      //      read IDS log file
            $lines = @file("$include_dir/IDS/tmp/phpids_log.txt");
            @fclose($handle);
            $ids_count = count($lines);
        }

        $xml_count = "0";
        if ($handle = opendir(".".$xml_dir."/stored")) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    $xml_count++;
                }
            }
            @fclose($handle);
        }

        echo "<div class='submenu cntr'>|&nbsp;&nbsp;&nbsp;Database $dba_act&nbsp;&nbsp;with table prefix '$mysql_table_prefix'&nbsp;&nbsp;&nbsp;|</div>
                <div class='panel'>
                <dl>
                    <dd class='cntr headline sml'>Database, Memory &amp; Log Cleaning Options</dd>
                    <dt class='bd x3'>
                    <a href='admin.php?f=bound_db' class='options' title='Click to delete needless keyword relationships from daatabase'>Bound database</a></dt>
                    <dd class='odrow'>Delete all keyword relationships that exceed a count of $max_results results</dd>
                    <dt class='bd x3'>
                    <a href='admin.php?f=15' class='options' title='Click to remove redundant keywords'>Clean keywords</a></dt>
                    <dd class='evrow'>Delete all keywords not associated with any link</dd>
                    <dt class='bd x3'>
                    <a href='admin.php?f=16' class='options' title='Click to remove redundant links'>Clean links</a></dt>
                    <dd class='odrow'>Delete all links not associated with any site</dd>
                    <dt class='bd x3'>
                    <a href='admin.php?f=47' class='options' title='Click to delete all non used categories'>Clean Cat table</a></dt>
                    <dd class='evrow'>Delete all categories not associated with any site</dd>
                    <dt class='bd x3'>
                    <a href='admin.php?f=26' class='options' title='Click to delete unused media links'>Clean media links</a></dt>
                    <dd class='odrow'>Delete all media links not associated with any link</dd>
                    <dt class='bd x3'>
                    <a href='admin.php?f=17' class='options' title='Click to erase the Temporary Link References'>Clear Temp table</a></dt>
                    <dd class='evrow'>".$temp." Items in Temporary table</dd>
                    <dt class='bd x3'>
                    <a href='admin.php?f=18' class='options' title='Click to erase the Pending table'>Clear Pending table</a></dt>
                    <dd class='odrow'>".$pending." Items in Pending table</dd>
                    <dt class='bd x3'>
                    <a href='admin.php?f=23' class='options' title='Click to erase the Search Log entries'>Clear Search log</a></dt>
                    <dd class='evrow'>".$log." Items in Query log</dd>
                    <dt class='bd x3'>
                    <a href='admin.php?f=25' class='options' title='Click to erase the Most Popular Page Link entries'>Clear 'Most Popular Pages' log</a></dt>
                    <dd class='odrow'>".$link_clicks." Items in 'Links Best Click' log</dd>
                    <dt class='bd x3'>
                    <a href='admin.php?f=14' class='options' title='Click to erase the Most Popular Media Link entries'>Clear 'Most Popular Media' log</a></dt>
                    <dd class='evrow'>".$media_clicks." Items in 'Media Best Click' log</dd>
                    <dt class='bd x3'>
                    <a href='admin.php?f=statistics&amp;type=spidering_log' class='options' title='Click to delete all Spidering Logs'>Clear Spider log</a></dt>
                    <dd class='odrow'>".$i." Files in Spidering log folder</dd>
                    <dt class='bd x3'>
                    <a href='admin.php?f=52' class='options' title='Click to erase all Text cache files'>Clear Text cache</a></dt>
                    <dd class='evrow'>".$t_count." files in cache&nbsp;&nbsp;&nbsp;( ".$t_size." kB used from ".$tcache_size." MByte )</dd>
                    <dt class='bd x3'>
                    <a href='admin.php?f=53' class='options' title='Click to erase all Media cache files'>Clear Media cache</a></dt>
                    <dd class='odrow'>".$m_count." files in cache&nbsp;&nbsp;&nbsp;( ".$m_size." kB used from ".$mcache_size." MByte )</dd>
                    <dt class='bd x3'>
                    <a href='admin.php?f=57' class='options' title='Click to erase the Intrusion Detection System log file'>Clear IDS log file</a></dt>
                    <dd class='evrow'>".$ids_count." events in log file</dd>
                    <dt class='bd x3'>
                    <a href='admin.php?f=clear_tmp' class='options' title='Click to erase the temporary folder'>Delete all files in temp folder</a></dt>
                    <dd class='odrow'>".$tmp_count." files in temporary folder</dd>
                    <dt class='bd x3'>
                    <a href='admin.php?f=clear_addurl' class='options' title='Click to erase the addurl table'>Clear all entries in 'addurl' table</a></dt>
                    <dd class='evrow'>".$addurl_count." URLs in table</dd>
                    <dt class='bd x3'>
                    <a href='admin.php?f=clear_banned' class='options' title='Click to erase the Banned table'>Clear all entries in 'banned' table</a></dt>
                    <dd class='odrow'>".$banned_count." domains in table</dd>
                    <dt class='bd x3'>
                    <a href='admin.php?f=clear_xml' class='options' title='Click to erase the folder'>Delete all files in 'XML' subfolder</a></dt>
                    <dd class='evrow'>".$xml_count." result files in folder</dd>
                    <dt class='bd x3'>
                    <a href='admin.php?f=truncate_all' class='options' title='Click to erase all tables in currently active database'>Truncate all tables in database</a></dt>
                    <dd class='odrow'><strong>Attention >>></strong> Content of all '$mysql_table_prefix' tables will be lost in database $dba_act</dd>
                </dl>
                </div>
            ";
    }

    function statisticsForm($type) {
        global $db_con, $mysql_table_prefix, $log_dir, $include_dir, $sites_per_page, $search_script;
        global $debug, $thumb_dir, $start, $image_dir, $mysql_csize, $mysql_cacheon, $dba_act;
        global $index_id3, $delim, $thumb_folder;

        error_reporting(0);

        echo "<div class='submenu cntr'>|&nbsp;&nbsp;&nbsp;Database $dba_act&nbsp;&nbsp;with table prefix '$mysql_table_prefix'&nbsp;&nbsp;&nbsp;|</div>
            <div class='submenu y4'>
                <ul>
                    <li><a href='admin.php?f=statistics&amp;type=keywords' title='Show list of Top 100 Keywords'>Top keywords</a></li>
                    <li><a href='admin.php?f=statistics&amp;type=thumb_files' title='Show list of all indexed thumbnails.'>Indexed Thumbnails</a></li>
                    <li><a href='admin.php?f=statistics&amp;type=pages' title='Show list of Largest Pages and their indexed file size'>Largest pages</a></li>
                    <li><a href='admin.php?f=statistics&amp;type=spidering_log' title='Show list of Spidering Logs'>Spidering logs</a></li>
                    <li><a href='admin.php?f=statistics&amp;type=autoindex' title='Show start time and index counter'>Auto Re-index log file</a></li>
                    <li><a href='admin.php?f=statistics&amp;type=ids' title='Show list of intrusion attempts'>IDS log file</a></li>
                    <li><a href='admin.php?f=statistics&amp;type=server_info' title='Show all available server info'>Server Info</a></li>
                </ul>
                <br /><br />
                <ul>
                    <li><a href='admin.php?f=statistics&amp;type=log' title='Show Search Log activity'>Search log</a></li>
                    <li><a href='admin.php?f=statistics&amp;type=top_searches' title='Show list of the most popular on-line Searches'>Most popular searches</a></li>
                    <li><a href='admin.php?f=statistics&amp;type=top_links' title='Show list of the most popular page links, clicked by the user'>Most popular page links</a></li>
                    <li><a href='admin.php?f=statistics&amp;type=top_media' title='Show list of the most popular media flinks, clicked by the user'>Most popular media links</a></li>
                </ul>
            </div>
            ";

        if ($type == "") {
            $cachedSumQuery = "SELECT sum(length(fulltxt)) from ".$mysql_table_prefix."links";
            $result=$db_con->query($cachedSumQuery);
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
                printf("<p>$cachedSumQuery</p>");
                exit;
            }

            if ($row = $result->fetch_array(MYSQLI_NUM)) {
                $cachedSumSize = $row[0];
            }
            $cachedSumSize = number_format($cachedSumSize / 1024, 2);

            $sitesSizeQuery = "SELECT sum(size) from ".$mysql_table_prefix."links";
            $result=$db_con->query("$sitesSizeQuery");
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
                printf("<p>$sitesSizeQuery</p>");
                exit;
            }

            if ($row = $result->fetch_array(MYSQLI_NUM)) {
                $sitesSize = $row[0];
            }
            $sitesSize = number_format($sitesSize, 2);

            $sql_query = "SELECT ip from ".$mysql_table_prefix."query_log";
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

            $query_tot = '0';
            $sql_query = "SELECT count(*) from ".$mysql_table_prefix."query_log";
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

            if ($row = $result->fetch_array(MYSQLI_NUM)) {
                $query_tot=$row[0];
            }

            $page_clicks = '0';
            $sql_query = "SELECT sum(click_counter) from ".$mysql_table_prefix."links";
            $result = $db_con->query($sql_query);
            if(!$db_con->errno){
                if ($row = $result->fetch_array(MYSQLI_NUM)) {
                    $page_clicks=$row[0];
                }

                $result = $db_con->query("SELECT count(*) from ".$mysql_table_prefix."media");
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

                if ($row = $result->fetch_array(MYSQLI_NUM)) {
                    $media_tot=$row[0];
                }

                $media_clicks = '0';
                $sql_query = "SELECT * from ".$mysql_table_prefix."media where click_counter > 0";
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

                if ($row=$result->num_rows) {
                    $media_clicks=$row;
                }

                $stats = getStatistics();
                echo "<div class='panel w60'>
                        <dl class='tblhead'>
                        <dt class='headline x2'>Overall Statistics :</dt><dd class='headline'>&nbsp;Counts</dd>
                        <dt class='odrow bd x2'>Sites :</dt><dd class='odrow'>&nbsp;".$stats['sites']."</dd>
                        <dt class='evrow bd x2'>Links :</dt><dd class='evrow'>&nbsp;".$stats['links']."</dd>
                        <dt class='odrow bd x2'>Categories :</dt><dd class='odrow'>&nbsp;".$stats['categories']."</dd>
                        <dt class='evrow bd x2'>Keywords :</dt><dd class='evrow'>&nbsp;".$stats['keywords']."</dd>
                        <dt class='odrow bd x2'>Media files :</dt><dd class='odrow'>&nbsp;$media_tot</dd>
                        <dt class='odrow bd x2'>Keyword link-relations :</dt><dd class='odrow'>&nbsp;".$stats['index']."</dd>
                        <dt class='evrow bd x2'>Cached texts total :</dt><dd class='evrow'>&nbsp;$cachedSumSize kb</dd>
                        <dt class='odrow bd x2'>Sites size total :</dt><dd class='odrow'>&nbsp;$sitesSize kb</dd>
                        <dt class='evrow bd x2'>Queries total :</dt><dd class='evrow'>&nbsp;$query_tot</dd>
                        <dt class='odrow bd x2'>Page Link clicks total :</dt><dd class='odrow'>&nbsp;$page_clicks</dd>
                        <dt class='odrow bd x2'>Media Link clicks total :</dt><dd class='odrow'>&nbsp;$media_clicks</dd>
                     </dl>
                    </div>
                    ";
            } else {
                echo "
                        <div class='submenu cntr'>
                        <span class='warnadmin'>
                    ";

                echo "<br /><br /><br />
                        Invalid database table installation.
                        <br />
                        </span>
                        </div>
                        <br /><br />
                    ";
                die ;
            }
            echo "</div>
            </div>
        </body>
    </html>
                    ";
            exit;
        }

        if ($type=='keywords') {
            $class = "evrow";
            $sql_query = "SELECT keyword, count(".$mysql_table_prefix."link_keyword.keyword_id) as x from ".$mysql_table_prefix."keywords, ".$mysql_table_prefix."link_keyword
                    where ".$mysql_table_prefix."keywords.keyword_id = ".$mysql_table_prefix."link_keyword.keyword_id group by keyword order by x desc limit 30";
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

                            $num_rows = $result->num_rows;
            if ($num_rows) {
                    while ($row = $result->fetch_array(MYSQLI_NUM)) {
                        $topwords[$row[0]] = $row[1];
                    }
            }
            arsort($topwords);
            echo "<div class='panel'>
                    <p class='headline cntr'>Top 100 Keywords</p>
                ";
            $nloops = 1;
            do {
                $nloops++;
                $count = 1;
                echo "<div class='ltfloat x3'>
                            <dl>
                        <dt class='headline x2'>Keyword</dt><dd class='headline'>:&nbsp;Instances</dd>
                    ";
                while ((list($word, $weight) = each($topwords)) && $count < 34) {
                    $count++;
                    if ($class =="evrow") {
                        $class = "odrow";
                    } else {
                        $class = "evrow";
                    }
                    echo "<dt class='$class'><a href='../$search_script?query=$word&amp;search=1' target='rel' title='View search results in new window'>".trim(substr($word,0,35))."</a></dt>
                        <dd class='$class'>:&nbsp;".$weight."</dd>
                    ";
                }
                echo "</dl>
                        </div>
                    ";
            } while ($nloops <=3);
            echo "<div class='clear'></div>
                        <br />
                        <a class='navup' href='#head' title='Jump to Page Top'>Top</a>
                        <br />
                    </div>
                </div>
            </div>
        </body>
    </html>
                    ";
            exit;
        }

        if ($type=='pages') {
            $class = "evrow";
            echo "<div class='panel'>
                    <dl class='tblhead'>
                    <dt class='headline x8'>File Size</dt><dd class='headline cntr'>Links to Largest Pages</dd>
                ";
            $sql_query = "SELECT ".$mysql_table_prefix."links.link_id, url, length(fulltxt)  as x from ".$mysql_table_prefix."links order by x desc LIMIT 20";
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


            while ($row = $result->fetch_array(MYSQLI_NUM)) {
                if ($class =="evrow")
                $class = "odrow";
                else
                $class  = "evrow";
                $url    = reconvert_url($row[1]);
                $sum    = number_format($row[2]/1024, 2);

                echo "<dt class='$class x8'>".$sum."kb&nbsp;&nbsp;&nbsp;</dt>
                        <dd class='$class'><a href='$url' title='Open this page in new window' target='_blank'>".$url."</a></dd>
                    ";
            }
            echo "</dl>
                        <br />
                        <a class='navup' href='#head' title='Jump to Page Top'>Top</a>
                        <br />
                    </div>
                </div>
            </div>
        </body>
    </html>
                    ";
            exit;
        }

        if ($type=='top_searches') {
            $class = "evrow";
            echo "<div class='panel'>
                    <p class='headline cntr sml'>Most Popular Searches (Top 50)</p>
                    <table width='100%'>
                        <tr>
                            <td class='tblhead'>Query</td>
                            <td class='tblhead'>Count</td>
                            <td class='tblhead'>Media query</td>
                            <td class='tblhead'>Average results</td>
                            <td class='tblhead'>Last queried</td>
                            <td class='tblhead'>Queried by IP</td>
                            <td class='tblhead'>Country</td>
                            <td class='tblhead'>Host name</td>
                    </tr>
                ";
            $sql_query = "SELECT query, count(*) as c, date_format(max(time), '%Y-%m-%d %H:%i:%s'), avg(results), media from ".$mysql_table_prefix."query_log group by query order by c desc LIMIT 50";
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

            while ($row = $result->fetch_array(MYSQLI_NUM)) {
                if ($class =="evrow")
                $class = "odrow";
                else
                $class  = "evrow";

                $word   = $row[0];
                $times  = $row[1];
                $date   = $row[2];
                $media  = $row[4];

                $avg    = number_format($row[3], 0);
                $word   = str_replace("\"", "", $word);

                $sql_query = "SELECT ip from ".$mysql_table_prefix."query_log where query='$word' order by time desc ";
                $result1 = $db_con->query($sql_query);
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

                $ips    = $result1->fetch_array(MYSQLI_NUM);
                $ip     =  $ips[0];
                //$ip = "222.46.18.34";  // just for tests

                if(intval($ip)>0){
                    $hostname = @gethostbyaddr($ip);    //  very slow! comment this row, if not required
                    if ($hostname == $ip) {
                        $hostname = "Unknown host" ;
                    }
                } else {
                    $hostname = "Unknown host" ; // a bad address.
                }

                //  convert any IP into IPv6
                $ip6 = IPv4To6($ip);

                //  find Country Code using the IPv6 database and IPV6 IPs
                $gi         = geoip_open("GeoIPv6.dat",GEOIP_STANDARD);
                $cc         = geoip_country_code_by_addr_v6($gi, $ip6);
                $country    = geoip_country_name_by_addr_v6($gi, $ip6);
                geoip_close($gi);

                if ($media) {   //  prepare a media search
                    $sql_query ="query_m=".$word."&submit=Media&search=1&media_only=0&cat_sel0=&cat_sel1=&cat_sel2=&cat_sel3=&cat_sel4=&type=and&results=10&db=0&prefix=0";
                } else {        //  prepare a text search
                    $sql_query ="query_t=".$word."&submit=Text&search=1&media_only=0&cat_sel0=&cat_sel1=&cat_sel2=&cat_sel3=&cat_sel4=&type=and&mark=blau+markiert&results=10&db=0&prefix=0";
                }

                echo "<tr class='$class sml'>
                        <td><a href='../$search_script?$query' target='_blank' title='Open this Log File in new window'>".($word)."</a></td>
                        <td class='cntr'> ".$times."</td>
                        <td class='cntr sml'> ".$media."</td>
                        <td class='cntr'> ".$avg."</td>
                        <td class='cntr'> ".$date."</td>
                        <td class='cntr'> ".$ip."</td>
                        <td class='cntr sml'> ".$cc." - ".$country."</td>
                        <td class='cntr sml'> ".$hostname."</td>
                        </tr>
                    ";
            }
            echo "
                    </table>
                    <br />
                    <a class='navup' href='#head' title='Jump to Page Top'>Top</a>
                    <br />
                </div>
              </div>
            </div>
        </body>
    </html>
                    ";
            exit;
        }

        if ($type=='top_links') {
            $class = "evrow";
            echo "<div class='panel'>
                    <p class='headline cntr sml'>Most Popular Links (Top 50)</p>
                    <table width='100%'>
                        <tr>
                            <td class='tblhead'>Link</td>
                            <td class='tblhead'>Total clicks</td>
                            <td class='tblhead'>Last clicked</td>
                            <td class='tblhead'>Last query</td>
                            <td class='tblhead'>Queried<br />by IP</td>
                            <td class='tblhead'>Country</td>
                            <td class='tblhead'>Host name</td>
                        </tr>
                ";

            $sql_query ="SELECT url, click_counter, last_click, last_query  from ".$mysql_table_prefix."links order by click_counter DESC, url LIMIT 50";
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

            while ($row = $result->fetch_array(MYSQLI_NUM)) {
                if ($class =="evrow")
                $class = "odrow";
                else
                $class = "evrow";

                $url            = reconvert_url($row[0]);
                $click_counter  = $row[1];
                $Timestamp      = $row[2];
                $last_query     = $row[3];
                if ($Timestamp != '0') {
                    $last_click = date("Y-m-d H:i:s", $Timestamp);

                    $sql_query = "SELECT ip from ".$mysql_table_prefix."links where last_query='$last_query' order by last_click desc ";
                    $result1 = $db_con->query($sql_query);
                    $ips    = $result1->fetch_array(MYSQLI_NUM);
                    $ip     =  $ips[0];
                    //$ip = "222.46.18.34";  // just for tests

                    if(intval($ip)>0){
                        $hostname = @gethostbyaddr($ip);    //  very slow! comment this row, if not required
                        if ($hostname == $ip) {
                            $hostname = "Unknown host" ;
                        }
                    } else {
                        $hostname = "Unknown host" ; // a bad address.
                    }

                    //  convert any IP into IPv6
                    $ip6 = IPv4To6($ip);

                    //  find Country Code using the IPv6 database and IPV6 IPs
                    $gi         = geoip_open("GeoIPv6.dat",GEOIP_STANDARD);
                    $cc         = geoip_country_code_by_addr_v6($gi, $ip6);
                    $country    = geoip_country_name_by_addr_v6($gi, $ip6);
                    geoip_close($gi);

                    echo "<tr class='$class sml'>
                                <td><a href='$url' target='rel' title='View link in new window'>".htmlentities($url)."</a></td>
                                <td class='cntr sml'> ".$click_counter."</td>
                                <td class='cntr sml'> ".$last_click."</td>
                                <td class='cntr sml'> ".$last_query."</td>
                                <td class='cntr'> ".$ip."</td>
                                <td class='cntr sml'> ".$cc." - ".$country."</td>
                                <td class='cntr sml'> ".$hostname."</td>
                            </tr>
                        ";

                }
            }
            echo "
                    </table>
                    <br />
                    <a class='navup' href='#head' title='Jump to Page Top'>Top</a>
                    <br />
                </div>
              </div>
            </div>
        </body>
    </html>
                    ";
            exit;
        }

        if ($type=='top_media') {
            $class = "evrow";
            echo "<div class='panel'>
                    <p class='headline cntr sml'>Most Popular Media (Top 50)</p>
                    <form action='' id='fdeltm'>
                    <table width='100%'>
                    <tr>
                        <td colspan='5' class='odrow bd'>
                            <input type='hidden' name='f' value='14' />
                            <input class='sbmt' id='submit11' type='submit' value='Clear this statistics' title='Start statistics deletion'
                                onclick=\"return confirm('Are you sure you want to delete the 'Most popular media' statistics?')\" />
                        </td>
                    </tr>
                    <tr>
                        <td class='tblhead'>Thumbnail</td>
                        <td class='tblhead'>Details</td>
                        <td class='tblhead'>Total clicks</td>
                        <td class='tblhead'>Last clicked</td>
                        <td class='tblhead'>Last query</td>
                        <td class='tblhead'>Queried<br />by IP</td>
                        <td class='tblhead'>Country</td>
                        <td class='tblhead'>Host name</td>
                    </tr>
                ";

            $sql_query = "SELECT link_addr, media_link, thumbnail, title, type, click_counter, last_click, last_query  from ".$mysql_table_prefix."media order by click_counter DESC, media_link LIMIT 50";
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

            while ($row = $result->fetch_array(MYSQLI_NUM)) {
                if ($class =="evrow")
                $class = "odrow";
                else
                $class = "evrow";

                $title      = substr($row[3], 0, strpos($row[3], $delim));  //  extract original title without transliterated words
                $name0      = basename($var[1]);                            //  extract file name
                $name       = substr($name0, 0, strrpos($name0, "."));      //  remove original suffix
                $last_query = $row[7];

                //  add folder path, db, table-prefix and add own suffix
                $file = utf8_decode(".".$thumb_folder."/db".$dba_act."_".$mysql_table_prefix."_".$name.".gif");

                //  temporary save thumbnail in folder
                if (!$handle = fopen($file, "ab")) {
                    if ($debug > 0) {
                        print "Unable to open $file ";
                    }
                }
                if (!fwrite($handle, $row[2])) {
                    if ($debug > 0) {
                        print "Unable to write the file $file. No thumbnails will be presented";
                    }
                }
                fclose($handle);

                if ($row[4] == 'audio') {   // use dummy thumbnail
                    $thumb_link = "$image_dir/notes60.gif" ;
                }
                if ($row[4] == 'video') {   // use dummy thumbnail
                    $thumb_link = "$image_dir/film60.gif" ;
                }

                if ($row[6] != '0') {
                    $last_click = date("Y-m-d H:i:s", $row[6]);

                    $sql_query = "SELECT ip from ".$mysql_table_prefix."media where last_query='$last_query' order by last_click desc ";
                    $result1 = $db_con->query($sql_query);
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
                    $ips    = $result1->fetch_array(MYSQLI_NUM);
                    $ip     =  $ips[0];
                    //$ip = "222.46.18.34";  // just for tests

                    if(intval($ip)>0){
                        $hostname = @gethostbyaddr($ip);    //  very slow! comment this row, if not required
                        if ($hostname == $ip) {
                            $hostname = "Unknown host" ;
                        }
                    } else {
                        $hostname = "Unknown host" ; // a bad address.
                    }

                    //  convert any IP into IPv6
                    $ip6 = IPv4To6($ip);

                    //  find Country Code using the IPv6 database and IPV6 IPs
                    $gi         = geoip_open("GeoIPv6.dat",GEOIP_STANDARD);
                    $cc         = geoip_country_code_by_addr_v6($gi, $ip6);
                    $country    = geoip_country_name_by_addr_v6($gi, $ip6);
                    geoip_close($gi);

                    echo "<tr class='$class sml'>
                                <td class='cntr'><a href='$row[1]' target='rel' title='Open this object'><img src='$file' border='1' alt='Open this object' /></a></td>
                                <td><strong>Title:</strong>&nbsp;&nbsp;$title
                                <br /><br />
                                <strong>Found at:</strong>&nbsp;<a href='$row[0]' target='rel' title='Open this page in a new window'>$row[0]</a></td>
                                <td class='cntr sml'> ".$row[5]."</td>
                                <td class='cntr sml'> ".$last_click."</td>
                                <td class='cntr sml'> ".$row[7]."</td>
                                <td class='cntr'> ".$ip."</td>
                                <td class='cntr sml'> ".$cc." - ".$country."</td>
                                <td class='cntr sml'> ".$hostname."</td>
                            </tr>
                        ";
                }
            }
            echo "
                    </table>
                    </form>
                    <br />
                    <a class='navup' href='#head' title='Jump to Page Top'>Top</a>
                    <br />
                </div>
              </div>
            </div>
        </body>
    </html>
                    ";
            exit;
        }

        if ($type=='autoindex') {

            $i = "0";
            $logfolder    = "./log/";
            if ($handle = opendir($logfolder)) {    //  open log-folder
                echo "<div class='panel'>
                ";

                while (false !== ($logfile = readdir($handle))) {   //get all log files

                    if (strstr($logfile, "db".$dba_act."_".$mysql_table_prefix."")) {    //separate only log files of current active db and table prefix

                        $att = '';
                        if (strstr($logfile, "._all")) {    //  this log file was created during 'Re-index all sites'
                            $att = "all";
                        } else {                            //  this log file was created during individual site indexing
                            //  extract site_id from file name
                            $end        = strrpos($logfile, "_");
                            $start      = strpos ($logfile, "._")+2 ;
                            $site_id    = substr($logfile, $start , $end-$start);

                            //  get URL according to actual site_id
                            $sql_query = "SELECT * from ".$mysql_table_prefix."sites where site_id=$site_id";
                            $result = $db_con->query($sql_query);
                            $row = $result->fetch_array(MYSQLI_ASSOC);
                            $site_url   = $row['url'];
                        }
                        //  build some nice headlines for monitor output
                        if ($att) {
                            $headline = "Periodical Re-indexer<br /><br />Log file created during indexing all sites";
                        } else {
                            $headline = "<br >Periodical Re-indexer<br /><br />Log file created during indexing<br />$site_url<br />Site id: $site_id";
                        }

                        $content    = @file("log/".$logfile."");   //  get content of the current logfile

                        //  output the periodical Re-indexer for each log file
                        $class      = "evrow";
                        echo "<p class='headline cntr sml'>$headline</p>
                <br />
                <table width='50%'>
                    <tr>
                        <td class='tblhead'>Index counter</td>
                        <td class='tblhead'>Started</td>
                    </tr>
                ";
                        foreach($content as $thisrow) {

                            if ($class =="evrow")
                            $class = "odrow";
                            else
                            $class = "evrow";

                            $counter = substr($thisrow, 0, strpos($thisrow, "count"));
                            $started = date("Y-m-d H:i:s", substr($thisrow, strpos($thisrow, "count")+5));

                            //  if Auto-indexer already finished by count limit
                            if (strpos($thisrow, "inish")) {
                                $counter = "finished:";
                                $started = date("Y-m-d H:i:s", substr($thisrow, strpos($thisrow, "finished")+8));
                            }

                            //  if Auto-indexer already finished because interval was too short
                            if (strpos($thisrow, "borted")) {
                                $counter = "Aborted because<br />interval too short";
                                $started = date("Y-m-d H:i:s", substr($thisrow, strpos($thisrow, "finished")+7));
                            }

                            echo "    <tr class='$class sml'>
                        <td class='cntr'>$counter</td>
                        <td class='cntr'>$started</td>
                    </tr>
                ";
                        }
                        echo "</table>
                <br /><br />
                <a class='navup' href='#head' title='Jump to Page Top'>Top</a>
                <br /><br />
                ";
                        $i++;
                    }
                }

                if ($i < "1") {
                    echo "<br />
                <p class='cntr msg'>Note: <span class='warnadmin'>Currently no log file available</span></p>
                ";

                }
            }

            echo "<br />
            </div>
            <br />
        </body>
    </html>
                    ";
            exit;
        }

        if ($type=='log') {
            $class = "evrow";
            echo "<div class='panel w100'>
                    <p class='headline cntr sml'>Search Log (Latest 100)</p>
                    <table width='100%'>
                    <tr>
                        <td class='tblhead'>Query</td>
                        <td class='tblhead'>Media query</td>
                        <td class='tblhead'>Results</td>
                        <td class='tblhead'>Queried at:</td>
                        <td class='tblhead'>Time taken</td>
                        <td class='tblhead'>User IP</td>
                        <td class='tblhead'>Country</td>
                        <td class='tblhead'>Host name</td>
                    </tr>
                ";
            $sql_query = "SELECT query,  date_format(time, '%Y-%m-%d %H:%i:%s'), elapsed, results, ip, media from ".$mysql_table_prefix."query_log order by time desc LIMIT 100";
            $result = $db_con->query($sql_query);

            while ($row = $result->fetch_array(MYSQLI_NUM)) {

                if ($class =="evrow")
                $class = "odrow";
                else
                $class      = "evrow";

                $query      = '';
                $word       = $row[0];
                $time       = $row[1];
                $elapsed    = $row[2];
                $results    = $row[3];
                $ip         = $row[4];
                $media      = $row[5];
                //$ip = "222.46.18.34";  // just for tests

                if(intval($ip)>0){
                    $hostname = @gethostbyaddr($ip);    //  very slow! comment this row, if not required
                    if ($hostname == $ip) {
                        $hostname = "Unknown host" ;
                    }
                } else {
                    $hostname = "Unknown host" ; // a bad address.
                }

                //  convert any IP into IPv6
                $ip6 = IPv4To6($ip);

                //  find Country Code using the IPv6 database and IPV6 IPs
                $gi         = geoip_open("GeoIPv6.dat",GEOIP_STANDARD);
                $cc         = geoip_country_code_by_addr_v6($gi, $ip6);
                $country    = geoip_country_name_by_addr_v6($gi, $ip6);
                geoip_close($gi);

                if ($media) {   //  prepare a media search
                    $query = "query_m=".$word."&submit=Media&search=1&media_only=0&cat_sel0=&cat_sel1=&cat_sel2=&cat_sel3=&cat_sel4=&type=and&results=10&db=0&prefix=0";
                } else {        //  prepare a text search
                    $query = "query_t=".$word."&submit=Text&search=1&media_only=0&cat_sel0=&cat_sel1=&cat_sel2=&cat_sel3=&cat_sel4=&type=and&mark=blau+markiert&results=10&db=0&prefix=0";
                }

                echo "<tr class='$class'>
                            <td><a href='../$search_script?$query' target='_blank' title='Open this Log File in new window'>".($word)."</a></td>
                            <td class='cntr sml'> ".$media."</td>
                            <td class='cntr sml'> ".$results."</td>
                            <td class='cntr sml'> ".$time."</td>
                            <td class='cntr sml'> ".$elapsed."</td>
                            <td class='cntr sml'> ".$ip."</td>
                            <td class='cntr sml'> ".$cc." - ".$country."</td>
                            <td class='cntr sml'> ".$hostname."</td>
                        </tr>
                    ";
            }
            echo "
                    </table>
                    <br />
                    <a class='navup' href='#head' title='Jump to Page Top'>Top</a>
                    <br />
                </div>
              </div>
            </div>
        </body>
    </html>
                    ";
            exit;
        }

        if ($type=='spidering_log') {
            $class = "evrow";
            $files = get_dir_contents($log_dir);
            if (count($files)>0) {
                echo "<div class='panel w75'>
                        <p class='headline cntr'>Spidering Logs</p>
                            <form action='' id='fdelfm'>
                            <table width='100%'>
                            <tr>
                                <td class='tblhead'>File</td>
                                <td class='tblhead'>Created</td>
                                <td class='tblhead' width='25%'>Option</td>
                            </tr>
                            <tr>
                                <td colspan='3' class='odrow cntr bd'>
                                <input type='hidden' name='f' value='44'
                                />
                                <input class='sbmt' id='submit1' type='submit' value='Delete ALL log files' title='Start Log File deletion'
                                onclick=\"return confirm('Are you sure you want to delete ALL log files? &nbsp;&nbsp;&nbsp;&nbsp;ATTENTION: A still running periodical indexing will be aborted.')\" />
                                </td>
                            </tr>
                        ";
                for ($i=0; $i<count($files); $i++) {
                    $file=$files[$i];
                    $year = substr($file, 4,2);
                    $month = substr($file, 6,2);
                    $day = substr($file, 8,2);
                    $hour = substr($file, 11,2);
                    $minute = substr($file, 14,2);
                    $second = substr($file, 17,2);

                    if ($class =="evrow")
                    $class = "odrow";
                    else
                    $class = "evrow";
                    echo "<tr class='$class'>
                            <td class='cntr sml'>
                            <a href='$log_dir/$file' target='_blank' title='Open this Log File in new window'>$file</a></td>
                            ";
                    if (strlen ($file) > '13') {

                        if (strstr($file, "auto-indexer")) {
                            //  the log-file for the Auto Re-indexer
                            $logfile    = "log/db".$dba_act."_".$mysql_table_prefix."._all_auto-indexer.log";
                            $content    = @file($logfile);   //  content of the logfile
                            $started    = date("Y-m-d H:i:s", substr($content[0], strpos($content[0], "count")+5));
                            echo "  <td class='cntr sml'>$started</td>
                                            <td class='cntr sml options'><a href='?f=delete_log&amp;file=$file' class='options' title='Click to Delete this Log File'
                                            onclick=\"return confirm('Are you sure you want to delete? $file &nbsp;&nbsp;&nbsp;&nbsp;ATTENTION: Deleting this Log File may abort periodical Re-indexing.')\">Delete</a></td>
                                    ";

                        } else {
                            echo "  <td class='cntr sml'>20$year-$month-$day $hour:$minute.$second</td>
                                            <td class='cntr sml options'><a href='?f=delete_log&amp;file=$file' class='options' title='Click to Delete this Log File'
                                            onclick=\"return confirm('Are you sure you want to delete '$file' Indexing Log File will be lost.')\">Delete</a></td>
                                    ";
                        }
                    } else {
                        echo "  <td></td><td></td>
                                ";
                    }
                    echo "
                        </tr>
                        ";
                }
                echo "
                        </table></form>
                        <br />
                        <a class='navup' href='#head' title='Jump to Page Top'>Top</a>
                        <br />
                        </div>
                    ";
            } else {
                echo "<br />
                        <p class='cntr msg'>Note: <span class='warnadmin'>No saved spidering logs exist!</span></p>
                        <br /> <br />
                    ";
            }
            echo "</div>
                    </div>
                    </body>
                    </html>
                    ";
            exit;
        }

        if ($type=='thumb_files') {
            if ($thumb_folder) {
                //  delete all former thumbnails in temporary folder
                clear_folder(".".$thumb_folder);
            }
            $class  = "odrow";
            $files  = array();
            $sql_query = "SELECT media_id from ".$mysql_table_prefix."media";
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

            $thumb_count = $result->num_rows;
			if ($thumb_count) {
	            while($row = $result->fetch_array(MYSQLI_ASSOC)) {
	                $files[] = $row['media_id'];
	            }
			}

            //Prepare thumbnails and their info for listing
            //$sites_per_page = '100'; // if you prefer another count than used for Sphiders result pages, uncomment this row and place your count of thumbnails per page here.
            $pages = ceil($thumb_count / $sites_per_page);   // Calculate count of required pages

            if (empty($start)) $start = '1';                // As $start is not yet defined this is required for the first result page
            if ($start == '1') {
                $from = '0';                                // Also for first page in order not to multipy with 0
            }else{
                $from = ($start-1) * $sites_per_page;           // First $num_row of actual page
            }

            $to = $thumb_count;                             // Last $num_row of actual page
            $rest = $thumb_count - $start;
            if ($thumb_count > $sites_per_page) {           // Display more then one page?
                $rest = $thumb_count - $from;
                $to = $from + $rest;                        // $to for last page
                if ($rest > $sites_per_page) $to = $from + ($sites_per_page); // Calculate $num_row of actual page
            }

            if ($thumb_count > '0') {
                $fromm = $from+1;
                echo "<div class='panel'>
            <p class='headline cntr sml'>Displaying thumbnails $fromm - $to&nbsp;&nbsp;from $thumb_count</p>
            <form action='' id='fdelfm'>
                <table width='100%'>
                    <tr>
                        <td class='tblhead' width='30%'>Thumbnail</td>
                        <td class='tblhead'>Details</td>
                        <td class='tblhead' width='20%'>Option</td>
                    </tr>
                    <tr>
                        <td colspan='3' class='odrow bd'>
                            <input type='hidden' name='f' value='49' />
                            <input class='sbmt' id='submit1' type='submit' value='Delete ALL images' title='Start File deletion'
                                onclick=\"return confirm('Are you sure you want to delete ALL indexed images \\n and ALL thumbnail files?')\" />
                        </td>
                    </tr>
                ";

                for ($i=$from; $i<$to; $i++) {

                    $this_thumb=$files[$i];
                    $i_1 = $i+1;                //  so table output does not start with zero

                    $result = $db_con->query("SELECT media_id, media_link, thumbnail, title from ".$mysql_table_prefix."media where media_id like '$this_thumb'");
                    if ($result->num_rows > '0') {
                        $var = $result->fetch_array(MYSQLI_NUM);

                        $media_id   = $var[0];
                        $this_img   = $var[1];
                        $title      = substr($var[3], 0, strpos($var[3], $delim));  //  extract original title without transliterated words
                        $name0      = basename($var[1]);                            //  extract file name
                        $name       = substr($name0, 0, strrpos($name0, "."));        //  remove original suffix

                        //  add folder path, db, table-prefix and add own suffix
                        $file = utf8_decode(".".$thumb_folder."/db".$dba_act."_".$mysql_table_prefix."_".$name.".gif");

                        //  temporary save thumbnail in folder
                        if (!$handle = fopen($file, "ab")) {
                            if ($debug > 0) {
                                print "Unable to open $file ";
                            }
                        }
                        if (!fwrite($handle, $var[2])) {
                            if ($debug > 0) {
                                print "Unable to write the file $file. No thumbnails will be presented";
                            }
                        }
                        fclose($handle);
                    }

                    if ($class =="odrow")
                    $class = "evrow";
                    else
                    $class = "odrow";
                    echo "    <tr class='$class'>
                        <td class='cntr x4'>
                            <a href='$this_img' target='rel' title='View the real image (not only the thumbnail) in new window'><img src=\"$file\" border='1' alt=\"Thumbnail\" /></a>
                        </td>
                        <td class='lft sml'>
                            <p> Thumb no.:&nbsp;&nbsp;&nbsp;$i_1</p>
                            <p> File name:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$name0</p>
                            <p> Title:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$title</p>
                            <p> Thumb_ID : &nbsp;&nbsp;$media_id</p>
                            ";
                    if ($index_id3 == '1') {
                        echo "<p><a href='".$include_dir."/show_id3.php?media_id=$media_id' target='rel' title='Click here to see EXIF and ID3 Infos in a new window'>Show EXIF Info</a></p>
                        ";
                    }
                    echo "</td>
                        <td class='cntr sml options'>
                            <a href='?f=delete_thumb&amp;file=$media_id' class='options' title='Click to delete this thumbnail'
                            onclick=\"return confirm('Are you sure you want to delete \'$name\'. \\nThumbnail file will be lost.')\">Delete</a>
                        </td>
                    </tr>
                ";

                }
                echo "</table>
            </form>
            <br />
            <a class='navup' href='#head' title='Jump to Page Top'>Top</a>
            <br />
            </div>
            ";

                // Display end of table
                if ($thumb_count > 0) {
                    echo "
            <br />";

                    if ($pages > 1) { // If we have more than 1 result-page
                        echo "
            <div class='submenu cntr'>
                Result page: $start from $pages&nbsp;&nbsp;&nbsp;
                ";

                        if($start > 1) { // Display 'First'
                            echo "
                                    <a href='admin.php?f=statistics&amp;type=thumb_files&amp;start=1'>First</a>&nbsp;&nbsp;
                                ";

                            if ($start > 5 ) { // Display '-5'
                                $minus = $start-5;
                                echo "
                                    <a href='admin.php?f=statistics&amp;type=thumb_files&amp;start=$minus'>- 5</a>&nbsp;&nbsp;
                                ";
                            }
                        }
                        if($start > 1) { // Display 'Previous'
                            $prev = $start-1;
                            echo "
                                    <a href='admin.php?f=statistics&amp;type=thumb_files&amp;start=$prev'>Previous</a>&nbsp;&nbsp;
                                ";
                        }
                        if($rest >= $sites_per_page) { // Display 'Next'
                            $next = $start+1;
                            echo "
                                    <a href='admin.php?f=statistics&amp;type=thumb_files&amp;start=$next' >Next</a>&nbsp;&nbsp;
                                ";

                            if ($pages-$start > 5 ) { // Display '+5'
                                $plus = $start+5;
                                echo "
                                     <a href='admin.php?f=statistics&amp;type=thumb_files&amp;start=$plus'>+ 5</a>&nbsp;&nbsp;
                                ";
                            }
                        }
                        if($start < $pages) { // Display 'Last'
                            echo "
                                    <a href='admin.php?f=statistics&amp;type=thumb_files&amp;start=$pages'>Last</a>
                                ";
                        }
                        echo "</div>
                        ";
                    }
                }
            } else {
                echo "<br />
                        <p class='cntr msg'>Note: <span class='warnadmin'>No saved thumbnails exist!</span></p>
                        <br /> <br />
                    ";
            }
            echo "
            </div>
        </body>
    </html>
                    ";
            exit;
        }

        if ($type == "ids") {
            $back = '';
            showIDS($back);
            exit;
        }

        if ($type = 'server_info') {
            $s_infos = str_replace ("&", "&amp;",$_SERVER);
            $e_infos = str_replace ("&", "&amp;",$_ENV);
            echo "<div class='submenu'>
                    <ul>
                    <li><a href='#serv_info'>Server</a></li>
                    <li><a href='#en_info'>Environment</a></li>
                    <li><a href='#mysql_info'>MySQL</a></li>
                    <li><a href='#pdf_con'>PDF-converter</a></li>
                    <li><a href='#gd_info'>Image func.</a></li>
                    <li><a href='#php_ini'>php.ini file</a></li>
                    <li><a href='admin.php?f=35'>PHP integration</a></li>
                    <li><a href='#php_sec'>PHP security info</a></li>
                    </ul>
                    </div>
                    <div class='tblhead sml'><a name='serv_info'>Server</a> </div>
                    <table width='98%'>
                    <tr>
                        <td width='20%' class='tblhead'>Key</td>
                        <td class='tblhead'>Value</td>

                    </tr>
                ";

            $bgcolor='odrow';
            $i=0;

            reset ($s_infos);
            while (list($key, $value) = each ($s_infos)) {
                echo "<tr class='$bgcolor cntr'>
                            <td>$key</td>
                            <td class='bordl'>$value</td>
                        </tr>
                    ";
                $i++;
                if ($bgcolor=='odrow') {
                    $bgcolor='evrow';
                } else {
                    $bgcolor='odrow';
                }
            }
            echo "
                    </table><br />
                    <a class='navup' href='#head' title='Jump to Page Top'>Top</a>
                    <br /><br />
                    <div class='headline cntr sml'><a name='en_info'>Environment</a></div>
                    <table width='98%'>
                    <tr>
                        <td width='20%' class='tblhead'>Key</td>
                        <td class='tblhead'>Value</td>

                    </tr>
                ";

            $bgcolor='odrow';
            $i=0;

            reset ($e_infos);
            while (list($key, $value) = each ($e_infos)) {
                echo "<tr class='$bgcolor cntr'>
                            <td>$key</td>
                            <td  class='bordl'>$value</td>
                        </tr>
                    ";
                $i++;
                if ($bgcolor=='odrow') {
                    $bgcolor='evrow';
                } else {
                    $bgcolor='odrow';
                }
            }
            echo "
                    </table><br />
                    <a class='navup' href='#head' title='Jump to Page Top'>Top</a>
                ";

            $server_version     = $db_con->server_info;
            $host_info          = $db_con->host_info;
            $client_info        = $db_con->client_info;
            $protocol_version   = $db_con->protocol_version;

            $sql_query = "SHOW STATUS LIKE 'Qcache_free_memory'";
            $status     = $db_con->query($sql_query);
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

            $row        = $status->fetch_array(MYSQLI_ASSOC);
            $cmem_size  = $row['Value'];

            echo "<br /><br />
                    <div class='headline cntr sml'><a name='mysql_info'>MySQL Info</a></div>
                    <table width='98%'>
                    <tr>
                        <td width='35%' class='tblhead'>Key</td>
                        <td  class='tblhead'>Value</td>
                    </tr>
                ";

            $bgcolor='odrow';
            echo "
                    <tr class='$bgcolor cntr'>
                        <td>MySQL Server version</td>
                        <td  class='bordl'>$server_version</td>
                    </tr>
                ";

            $bgcolor='evrow';
            echo "
                    <tr class='$bgcolor cntr'>
                        <td>Connection info</td>
                        <td  class='bordl'>$host_info</td>
                    </tr>
                ";

            $bgcolor='odrow';
            echo "
                    <tr class='$bgcolor cntr'>
                        <td>Client library info</td>
                        <td  class='bordl'>$client_info</td>
                    </tr>
                ";
            $bgcolor='evrow';
            echo "
                    <tr class='$bgcolor cntr'>
                        <td>MySQL protocol version</td>
                        <td  class='bordl'>$protocol_version</td>
                    </tr>
                ";
            $bgcolor='odrow';
            echo "
                    <tr class='$bgcolor cntr'>
                        <td>Support for mysqli</td>
                        <td  class='bordl'>See below as part of your PHP installation</td>
                    </tr>
                ";
            $bgcolor='evrow';
            echo "<tr><td></td><td></td></tr>
                    <tr class='$bgcolor cntr'>
                        <td>MySQL cache</td>
                ";
            if ($cmem_size == '0') {
                echo "<td  class='warnadmin'>Cache is not initialized</td>";
            } else {
                echo "<td  class='bordl'>32 MByte initialized</td>
                        </tr>
                    ";
                $bgcolor='odrow';
                echo "
                        <tr class='$bgcolor cntr'>
                            <td>Currently free cache size</td>
                            <td  class='bordl'>$cmem_size Bytes</td>
                        </tr>
                    ";

                $sql_query = "SHOW STATUS LIKE 'Qcache_total_blocks'";
                $status     = $db_con->query($sql_query);
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

                $row        = $status->fetch_array(MYSQLI_ASSOC);
                $ctot_blocks  = $row['Value'];
                $bgcolor='evrow';
                echo "
                        <tr class='$bgcolor cntr'>
                            <td>Cache total blocks</td>
                            <td  class='bordl'>$ctot_blocks</td>
                        </tr>
                    ";

                $sql_query = "SHOW STATUS LIKE 'Qcache_free_blocks'";
                $status     = $db_con->query($sql_query);
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

                $row        = $status->fetch_array(MYSQLI_ASSOC);
                $cfree_blocks  = $row['Value'];
                $bgcolor='odrow';
                echo "
                        <tr class='$bgcolor cntr'>
                            <td>Cache free blocks</td>
                            <td  class='bordl'>$cfree_blocks</td>
                        </tr>
                    ";


                $sql_query = "SHOW STATUS LIKE 'Qcache_hits'";
                $status     = $db_con->query($sql_query);
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

                $row        = $status->fetch_array(MYSQLI_ASSOC);
                $cache_hits = $row['Value'];
                $bgcolor='evrow';
                echo "
                        <tr class='$bgcolor cntr'>
                            <td>Cache hits</td>
                            <td  class='bordl'>$cache_hits</td>
                        </tr>
                    ";

                $sql_query = "SHOW STATUS LIKE 'Qcache_inserts'";
                $status     = $db_con->query($sql_query);
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

                $row        = $status->fetch_array(MYSQLI_ASSOC);
                $cache_inserts = $row['Value'];
                $bgcolor='odrow';
                echo "
                        <tr class='$bgcolor cntr'>
                            <td>Cache inserts</td>
                            <td  class='bordl'>$cache_inserts</td>
                        </tr>
                    ";

                $sql_query = "SHOW STATUS LIKE 'Qcache_queries_in_cache'";
                $status     = $db_con->query($sql_query);
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

                $row        = $status->fetch_array(MYSQLI_ASSOC);
                $queries_in_cache = $row['Value'];
                $bgcolor='evrow';
                echo "
                        <tr class='$bgcolor cntr'>
                            <td>Queries in cache</td>
                            <td  class='bordl'>$queries_in_cache</td>
                        </tr>
                    ";

                $sql_query = "SHOW STATUS LIKE 'Qcache_not_cached'";
                $status     = $db_con->query($sql_query);
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

                $row        = $status->fetch_array(MYSQLI_ASSOC);
                $q_not_cached = $row['Value'];
                $bgcolor='odrow';
                echo "
                        <tr class='$bgcolor cntr'>
                            <td>Queries not cached</td>
                            <td  class='bordl'>$q_not_cached</td>
                        </tr>
                    ";

                $sql_query = "SHOW STATUS LIKE 'Qcache_lowmem_prunes'";
                $status     = $db_con->query($sql_query);
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

                $row        = $status->fetch_array(MYSQLI_ASSOC);
                $q_low_prun = $row['Value'];
                $bgcolor='evrow';
                echo "
                        <tr class='$bgcolor cntr'>
                            <td>Not cached because low memory</td>
                            <td  class='bordl'>$q_low_prun</td>
                        </tr>
                    ";
            }
            echo "
                    </table><br />
                    <a class='navup' href='#head' title='Jump to Page Top'>Top</a>
                ";


            $os = '';
            $os = $_ENV['OS'];                              // not all shared hosting server will supply this info
            $admin_path = $_ENV['ORIG_PATH_TRANSLATED'];    // that might work for shared hosting server
            $admin_file = $_SERVER['SCRIPT_FILENAME'];      // should present the physical path
            $sdoc_root = $_SERVER['DOCUMENT_ROOT'];         // this should provide every hoster (???)
            $edoc_root = $_ENV['DOCUMENT_ROOT'];            // this should provide every hoster (???)

            echo "<br /><br />
                    <div class='headline cntr sml'><a name='pdf_con'>PDF-converter relevant Info</a></div>
                    <table width='98%'>
                    <tr>
                        <td width='35%' class='tblhead'>Key</td>
                        <td  class='tblhead'>Value</td>
                    </tr>
                ";

            $bgcolor='odrow';
            if ($os) {
                echo "
                        <tr class='$bgcolor cntr'>
                            <td>Operating System</td>
                            <td  class='bordl'>$os</td>
                        </tr>
                    ";
                $bgcolor='evrow';
            }
            if (!$os) {
                $s_soft = $_SERVER['SERVER_SOFTWARE'];
                $sys_os = stripos($s_soft, "lin");
                if (!$sys_os) {
                    $sys_os = stripos($s_soft, "uni");
                    if (!$sys_os) {
                        $sys_os = stripos($s_soft, "win");
                    }
                }
                if ($sys_os) {
                    $os = substr($s_soft, $sys_os, '5');
                    echo "
                            <tr class='$bgcolor cntr'>
                                <td>Operating System</td>
                                <td  class='bordl'>$os</td>
                            </tr>
                        ";
                    $bgcolor='evrow';
                } else {
                    $s_sig = $_SERVER['SERVER_SIGNATURE'];
                    $sys_os = stripos($s_sig, "lin");
                    if (!$sys_os) {
                        $sys_os = stripos($s_sig, "uni");
                        if (!$sys_os) {
                            $sys_os = stripos($s_sig, "win");
                        }
                    }
                }
                if ($sys_os) {
                    $os = substr($s_sig, $sys_os, '5');
                    echo "
                            <tr class='$bgcolor cntr'>
                                <td>Operating System</td>
                                <td  class='bordl'>$os</td>
                            </tr>
                        ";
                    $bgcolor='evrow';
                }
            }
            //  if ENV or SERVER_SIGNATURE or SERVER_SOFTWARE do not deliver OperatingSystem info, we will use the PHPinfo to extract it
            if (!$os) {
                $phpinfo ='';
                ob_start();                     // redirect output into buffer
                phpinfo();
                $phpinfo = ob_get_contents();   // get all from phpinfo
                ob_end_clean();                 // clean buffer and close it

                //  extract OS information
                $start  = stripos($phpinfo, "\"v\"")+4;
                $end    = stripos($phpinfo, "</td>", $start);
                $length = $end - $start;
                $os = substr($phpinfo, $start, $length);

                echo "
                        <tr class='$bgcolor cntr'>
                            <td>Operating System</td>
                            <td  class='bordl'>$os</td>
                        </tr>
                    ";
                $bgcolor='evrow';
            }

            if ($admin_path) {
                $admin_path = str_replace("\\\\", "/", $admin_path);
                $admin_path = str_replace("\\", "/", $admin_path);

                $pdf_path = substr($admin_path, 0, strrpos($admin_path, "/"));
                $pdf_path = substr($pdf_path, 0, strrpos($pdf_path, "/"));
                $pdf_path = "".$pdf_path."/converter/pdftotext";


                echo "
                        <tr class='$bgcolor cntr'>
                            <td>Physical path to Sphider-plus Admin</td>
                            <td  class='bordl'>$admin_path</td>
                        </tr>
                    ";
                $bgcolor='odrow';
                echo "
                        <tr class='$bgcolor cntr'>
                            <td>Physical path to the Linux / UNIX PDF-converter</td>
                            <td  class='bordl'>$pdf_path</td>
                        </tr>
                    ";
                $bgcolor='evrow';
            } else {
                if ($admin_file) {
                    $admin_file = str_replace("\\\\", "/", $admin_file);
                    $admin_file = str_replace("\\", "/", $admin_file);

                    $pdf_path = substr($admin_file, 0, strrpos($admin_file, "/"));
                    $pdf_path = substr($pdf_path, 0, strrpos($pdf_path, "/"));
                    $pdf_path = "".$pdf_path."/converter/pdftotext";


                    echo "
                            <tr class='$bgcolor cntr'>
                                <td>Physical path to Sphider-plus Admin</td>
                                <td  class='bordl'>$admin_file</td>
                            </tr>
                        ";
                    $bgcolor='odrow';
                    echo "
                            <tr class='$bgcolor cntr'>
                                <td>Physical path to the Linux / UNIX PDF-converter</td>
                                <td  class='bordl'>$pdf_path</td>
                            </tr>
                        ";

                    $bgcolor='evrow';
                }
            }

            if ($sdoc_root){
                echo "
                        <tr class='$bgcolor cntr'>
                            <td>Physical path to document root</td>
                            <td  class='bordl'>$sdoc_root</td>
                        </tr>
                    ";
            } else {
                if ($edoc_root){
                    echo "
                            <tr class='$bgcolor cntr'>
                                <td>Physical path to document root</td>
                                <td  class='bordl'>$edoc_root</td>
                            </tr>
                        ";
                }
            }

            if (!$admin_path && !$admin_file) {
                if ($sdoc_root){
                    echo "
                            <tr class='$bgcolor cntr'>
                                <td>Physical path to document root</td>
                                <td  class='bordl'>$sdoc_root</td>
                            </tr>
                        ";
                }

                if ($edoc_root){
                    echo "
                            <tr class='$bgcolor cntr'>
                                <td>Physical path to document root</td>
                                <td  class='bordl'>$edoc_root</td>
                            </tr>
                        ";
                } else {
                    echo "
                            </table>
                            <table width='98%'>
                            <tr>
                                <td>
                                <span class='cntr warnadmin'><br />
                                Attention: Your server does not deliver information about the physical path to Sphider-plus.<br />
                                For LINUX and UNIX systems you will have to initialize the PDF converter manually.<br />
                                For details see the file readme.pdf, chapter: PDF converter for Linux/UNIX systems.<br />
                                <br /></span>
                                </td>
                            </tr>
                        ";
                }
            }

            echo "
                    </table><br />
                    <a class='navup' href='#head' title='Jump to Page Top'>Top</a><br /><br />
                ";

            if (function_exists(gd_info)) {
                $gd =gd_info();     //  get details about image functions
            }
            echo "<br /><br />
                    <div class='headline cntr sml'><a name='gd_info'>Info about Image Functions (GD-library)</a></div>
                    <table width='98%'>
                    <tr>
                        <td width='50%' class='tblhead'>Key</td>
                        <td  class='tblhead'>Value</td>
                    </tr>
                ";

            if ($gd) {
                $bgcolor = "evrow";
                foreach($gd as $key => $val) {
                    if ($bgcolor =="evrow") {
                        $bgcolor = "odrow";
                    } else {
                        $bgcolor = "evrow";
                    }
                    echo "
                            <tr class='$bgcolor cntr'>
                                <td>$key</td>
                                <td class='bordl'>$val</td>
                            </tr>
                        ";
                }
            } else {
                echo "
                        <tr class='warnadmin cntr'>
                            <td>Image functions are not installed.</td>
                            <td>You will need to compile PHP with the GD library.</td>
                        </tr>
                    ";
            }

            echo "
                    </table><br />
                    <a class='navup' href='#head' title='Jump to Page Top'>Top</a><br /><br />
                ";

            echo "
                    <div class='headline cntr sml'>PHP Info</div>
                    <br />
                    <div class='headline cntr sml'><a name='php_ini'>php.ini file</a></div>
                    <table width='98%'>
                    <tr>
                        <td width='20%' class='tblhead'>Key</td>
                        <td class='tblhead'>Value</td>

                    </tr>
                ";
            $php_ini = str_replace("&", "&amp;", ini_get_all());

            $bgcolor='odrow';
            $i=0;

            reset ($php_ini);
            while (list($key, $value) = each ($php_ini)) {
                echo "
                        <tr class='$bgcolor cntr'>
                            <td>$key</td>
                            <td class='bordl'>
                    ";
                print_r($value);
                echo "
                            </td>
                        </tr>
                    ";
                $i++;
                if ($bgcolor=='odrow') {
                    $bgcolor='evrow';
                } else {
                    $bgcolor='odrow';
                }
            }
            echo "
                    </table>
                    <br /><br />
                    <a class='navup' href='#head' title='Jump to Page Top'>Top</a>
                    <br /><br />
                    <div class='headline cntr sml'><a name='php_sec'>PHP security info</a></div>
                    <table width='98%'>
                    <tr>
                        <td width='20%' class='tblhead'></td>
                        <td class='tblhead'></td>

                    </tr>
                ";

            //phpsecinfo();   //  get PHP security information

            echo "
                    </table>
                    <br /><br />
                    <a class='navup' href='#head' title='Jump to Page Top'>Top</a>
                    <br /><br />
                    </div>
                ";

            exit;
        }
    }

    switch ($f)	{

        case 1:
            if ($url) {     //  add new site to db

                if (!strpos(substr($url, 0, 5), "ttp")) {
                    $url = "http://".$url;
                }

                $compurl=parse_url($url);
                if ($compurl['path']=='') {
                    $url=$url."/";
                }

                $message = addsite($url, $title, $short_desc, $cat, $def_include, $smap_url, $authent, $prior_level);
                echo $message;

                $url_enc = urlencode($url);
                $sql_query = "SELECT site_id from ".$mysql_table_prefix."sites where url='urlencode($url_enc)'";
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

                $this_row = $result->fetch_array(MYSQLI_ASSOC);
                if ($this_row['side_id'] != ""){
                    siteScreen($site_id, $message);
                }
            }
            showsites($message);
            break;

        case 2:
            showsites($f);      //  present "Sites" view
            break;

        case edit_site:
            editsiteform($site_id);
            break;

        case 4:

            if (!isset($domainlv)) {
                $domainlv = 0;
            }
            if (!isset($cat)) {
                $cat = "";
            }
            if ($soption =='full') {
                $depth = '-1' ;
            }
            if ($soption =='level' && $depth =='') {
                $depth = '2' ;
            }
            if (!isset($in)) {
                $in = '';
            }
            if (!isset($out)) {
                $out = '';
            }

            if (!isset($use_pref)) {
                $use_pref = 0;
            }

            $message = editsite ($site_id, $url, $title, $short_desc, $depth, $in, $out, $domainlv, $cat, $smap_url, $authent, $use_pref, $prior_level);
            showsites($message);
            break;

        case 5:
            deletesite ($site_id);
            if ($del_related == '1') {
                delRelated($site_id);
                break;
            }
            showsites($message);
            break;

        case add_cat:
            if (!isset($parent))
            $parent = "";
            addcatform ($parent);
            break;

        case 7:
            if (!isset($parent)) {
                $parent = "";
            }
            $message = addcat ($category, $parent);
            list_cats (0, 0, "evrow", $message);
            break;

        case categories:
            list_cats (0, 0, "evrow", "");
            break;

        case edit_cat;
        editcatform($cat_id);
        break;

        case 10;
        $message = editcat ($cat_id, $category);
        list_cats (0, 0, "evrow", $message);
        break;

        case 11;
        deletecat($cat_id);
        list_cats (0, 0, "evrow");
        break;

        case 14;
        clearBestMedia();
        break;

        case 15;
        $back = "1";
        cleanKeywords($back);
        break;

        case 16;
        $back = "1";
        cleanLinks($back);
        break;

        case 17;
        $back = '1';
        cleanTemp($back);
        break;

        case 18;
        $back = '1';
        cleanPending($back);
        break;

        case 19;
        siteStats($site_id);
        break;

        case 20;
        siteScreen($site_id, $message);
        break;

        case 21;
        if (!isset($start))
        $start = 1;
        if (!isset($filter))
        $filter = "";
        if (!isset($per_page))
        $per_page = 10;

        browsePages($site_id, $start, $filter, $per_page);
        break;

        case 22;
        deletePage($link_id);
        if (!isset($start))
        $start = 1;
        if (!isset($filter))
        $filter = "";
        if (!isset($per_page))
        $per_page = 10;
        browsePages($site_id, $start, $filter, $per_page);
        break;

        case 23;
        clearLog();
        break;

        case 24;    //  log-out the current Admin
        session_destroy();
        exit('<meta http-equiv="refresh" content="0; url=admin.php"/>');
        break;

        case 25;
        clearBestPage();
        break;

        case 26;
        cleanMediaLink();
        break;

        case 28;    //  show menu 'Sites awaiting approval'
        approve_newsites();
        break;

        case 29:    //  show menus 'Approved', 'Rejected' or 'Banned'

            if($add_auth == '1' && $approve == "Approve" && preg_match("/not yet defined/", $authent)) {
                echo "<br />
                            <p class='warnadmin cntr'><br />
                            <strong>Attention:</strong>
                            <br /><br >
                            According to the Admin settings, suggested URLs require authentification tags.
                            <br /><br />
                            But no authentification value was entered in 'Approve sites' form.
                            <br /><br /></p>
                            <br />
                            <a class='bkbtn' href='admin.php?f=28' title='Reload Approve sites'>Back to 'Approve sites'</a>
                            <p>\n\n</p>
                            </div>
                            </body>
                        </html>
                    ";
                die ;  //   because of missing authentication value
            }

            //  in order to speed up, forget the arguments
            if (strstr($url, "?")) {
                $url_del = substr($url, 0, strpos($url, "?"));
                $url_del = urldecode($url_del."%");
            } else {
                $url_del = urldecode($url);
            }

            $sql_query = "SELECT * FROM ".$mysql_table_prefix."addurl where url ='$url'";
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

            $row = $result->fetch_array(MYSQLI_ASSOC);
            $account = $row['account'];
            $created = $row['created'];
            $mailer = "Sphider-plus the AddURL-mailer";
            $header = "from: Sphider-plus administrator<".$dispatch_email.">\r\n";
            $header .= "Reply-To: ".$dispatch_email."\r\n";

            $subject2    = "URL Submitted: $url";

            if ($add_auth == '1') {

                //      Text for e-mail to dispatcher when suggestion was approved with authentification
                $text2 = "On $created you suggested the site $url to be indexed by our search engine.\n
    Your suggestion was accepted by the system administrator and will be indexed shortly.\n
    Please add the following tag into the header of the suggested site:\n
    <meta name='Sphider-plus' content='$authent'>\n
    In order to enable indexing of your site, this tag is mandatory\n
    and is tested periodically by the indexer of Sphider-plus.\n
    We appreciate your help and effort in building this search engine.\n\n
    This mail was automatically generated by $mailer.\n";

            } else {

                //      Text for e-mail to dispatcher when suggestion was approved without authentification
                $text2 = "On $created you suggested the site $url to be indexed by our search engine.\n
    Your suggestion was accepted by the system administrator and will be indexed shortly.\n
    We appreciate your help and effort in building this search engine.\n\n
    This mail was automatically generated by $mailer.\n";

            }

            //      Text for e-mail to dispatcher when suggestion was rejected
            $text3 = "On $created you suggested the site $url to be indexed by our search engine.\n
    Your suggestion was rejected by the system administrator and will not be indexed.\n
    We appreciate your help and effort in building this search engine.\n\n
    This mail was automatically generated by $mailer.\n";

            //      Text for e-mail to dispatcher when suggestion was rejected and banned
            $text4 = "On $created you suggested the site $url to be indexed by our search engine.\n
    Your suggestion was rejected and banned by the system administrator and will never be indexed.\n\n
    This mail was automatically generated by $mailer.\n";

            if ($approve == "Approve") {

                $compurl=parse_url($url);
                if ($compurl['path']==''){
                    $url=$url."/";
                }
                $prior_level = 1;   //  define another prior level in 'Edit' option for this URL

                addsite($url, $title, $short_desc, $cat, '', '', $authent, $prior_level);

                $sql_query  ="SELECT site_ID from ".$mysql_table_prefix."sites where url='$url'";
                $result     = $db_con->query($sql_query);
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

                $row        = $result->fetch_array(MYSQLI_NUM);
                $site_id    = $row[0];
                $sql_query  = "INSERT INTO ".$mysql_table_prefix."site_category (site_id, category_id) values ('$site_id', '$cat')";
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

                $sql_query = "DELETE FROM ".$mysql_table_prefix."addurl WHERE url like '$url_del'";
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

                echo "<div class='submenu cntr'>| Sites for Approval |</div>
                        <div class='cntr'>
                        <p>\n\n</p>
                        Site approved.
                        <p>\n\n</p>
                    ";

                if ($addurl_info == 1 && $account != $admin_email) {
                    // e-mail to dispatcher "approved"
                    if (mail($account,$subject2,$text2,$header)) {
                        echo "
                                Dispatcher was informed by e-mail.
                                <p>\n\n</p>
                            ";
                    } else {
                        echo "
                                Error ! Could not inform the dispatcher ( $account )<br />Unable to send the e-mail!
                                <p>\n\n</p>
                            ";
                    }
                }

                //  more URLs  available to be approved, rejected, or banned?
                $sql_query = "SELECT * FROM ".$mysql_table_prefix."addurl";
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

                $rows = $result->num_rows;

                if ($rows) {
                    $url1 = urldecode($url);
                    echo "(Don't forget to index the site $url1)
                            <p>\n\n\n</p>
                            </div>
                            <div class='odrow cntr'>
                            <p>\n\n</p>
                            <a class='bkbtn' href='admin.php?f=28' title='Reload Approve sites'>Back to 'Approve sites'</a>
                            <p>\n\n</p>
                            </div>
                            </body>
                            </html>
                        ";
                } else {
                    echo "(Don't forget to index the site $url1)
                        <p>\n\n\n</p>
                        </div>
                        <div class='odrow cntr'>
                        <p>\n\n</p>
                        <a class='bkbtn' href='admin.php' title='Reload Admin'>Back to 'Admin sites'</a>
                        <p>\n\n</p>
                        </div>
                        </body>
                        </html>
                    ";
                }
            }
            elseif ($delete == "Reject") {
                $sql_query = "DELETE FROM ".$mysql_table_prefix."addurl WHERE url like '$url_del'";
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

                echo "<div class='submenu cntr'>| Sites for Approval |</div>
                        <div class='cntr'>
                        <p>\n\n</p>
                        URL $url_del rejected and deleted.
                        <p>\n\n</p>
                    ";

                if ($addurl_info == 1 && $account != $admin_email) {
                    // e-mail to dispatcher "rejected"
                    if (mail($account,$subject2,$text3,$header)) {
                        echo "
                                Dispatcher was informed by e-mail.
                                <p>\n\n</p>
                            ";
                    } else {
                        echo "
                                Error ! Could not inform the dispatcher ( $account )<br />Unable to send the e-mail!
                                <p>\n\n</p>
                            ";
                    }
                }

                //  more URLs  available to be approved, rejected, or banned?
                $sql_query = "SELECT * FROM ".$mysql_table_prefix."addurl";
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

                $rows = $result->num_rows;

                if ($rows) {
                    echo "
                            </div>
                            <div class='odrow cntr'>
                            <p>\n\n</p>
                            <a class='bkbtn' href='admin.php?f=28' title='Reload Approve sites'>Back to 'Approve sites'</a>
                            <p>\n\n</p>
                            </div>
                            </body>
                            </html>
                        ";
                } else {
                    echo "
                        </div>
                        <div class='odrow cntr'>
                        <p>\n\n</p>
                        <a class='bkbtn' href='admin.php' title='Reload Admin'>Back to 'Admin sites'</a>
                        <p>\n\n</p>
                        </div>
                        </body>
                        </html>
                        ";
                }
            }
            elseif ($bann == "Ban !") {
                $sql_query = "DELETE FROM ".$mysql_table_prefix."addurl WHERE url like '$url_del'";
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

                $url_bann = strtolower(trim($url));

                if (!strpos($url, "ttp://")) {
                    $url_bann = "http://".$url_bann;      //  if missing, add the scheme
                }

                $urlparts  = parse_url($url_bann);
                $new_domain = @str_replace('www.', '', $urlparts['host']) ;

                $sql_query = "INSERT INTO `".$mysql_table_prefix."banned` (`domain`) VALUES ('".$new_domain."');";
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

                echo "<div class='submenu cntr'>| Sites for Approval |</div>
                        <div class='cntr'>
                        <p>\n\n\n</p>
                        The URL $url_del is banned now!
                        <p>\n\n</p>
                        ";

                if ($addurl_info == 1 && $account != $admin_email) {
                    // e-mail to dispatcher "rejected and banned"
                    if (mail($account,$subject2,$text4,$header)) {
                        echo "
                                Dispatcher was informed by e-mail.
                                <p>\n\n</p>
                            ";
                    } else {
                        echo "
                                Error ! Could not inform the dispatcher ( $account )<br />Unable to send the e-mail!
                                <p>\n\n</p>
                            ";
                    }
                }

                //  more URLs  available to be approved, rejected, or banned?
                $sql_query = "SELECT * FROM ".$mysql_table_prefix."addurl";
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

                $rows = $result->num_rows;

                if ($rows) {
                    echo "
                            </div>
                            <div class='odrow cntr'>
                            <p>\n\n</p>
                            <a class='bkbtn' href='admin.php?f=28' title='Reload Approve sites'>Back to 'Approve sites'</a>
                            <p>\n\n</p>
                            </div>
                            </body>
                            </html>
                        ";
                } else {
                    echo "
                        </div>
                        <div class='odrow cntr'>
                        <p>\n\n</p>
                        <a class='bkbtn' href='admin.php' title='Reload Admin'>Back to 'Admin sites'</a>
                        <p>\n\n</p>
                        </div>
                        </body>
                        </html>
                    ";
                }
            }

            break;

        case 30;
        $valid = '1';
        banned_domains($valid);   //	show menu 'Banned Domains Manager'  and get new 'Banned domains'
        break;

        case 31;    // remove from 'banned domains'
        $sql_query = "DELETE FROM `".$mysql_table_prefix."banned` WHERE domain like ('".$domain."') LIMIT 1";
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

        $valid = '1';
        banned_domains($valid);
        break;

        // add to 'banned domains'
        case 32;
        $www = '';
        $dot = '';
        $valid = '';

        $url = strtolower(trim($new_banned));
        if (!strpos($url, "ttp://")) {
            $url = "http://".$url;      //  if missing, add the scheme
        }

        $urlparts  = parse_url($url);
        $new_domain = @str_replace('www.', '', $urlparts['host']) ;
        $sql_query = "INSERT INTO `".$mysql_table_prefix."banned` (`domain`) VALUES ('".$new_domain."');";
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

        $valid = '1';

        banned_domains($valid);
        break;

        case '':
            approve_newsites();
            break;

        case 35:
            $phpinfo ='';
            ob_start();                     // redirect output into buffer
            phpinfo();
            $phpinfo = ob_get_contents();   // get all from phpinfo
            ob_end_clean();                 // clean buffer and close it

            //  extract the table content
            $start  = stripos($phpinfo, "<table ");
            $end    = strripos($phpinfo, "</table>")+8;
            $length = $end - $start;
            $phpinfo = substr($phpinfo, $start, $length);

            //  replace phpinfo() style with valid Sphider-plus design
            $phpinfo = str_replace("width=\"600\"", "width=\"98%\"", $phpinfo);
            $phpinfo = str_replace("class=\"h\"", "class=\"stats\"", $phpinfo);
            $phpinfo = str_replace("class=\"e\"", "class=\"odrow\"", $phpinfo);
            $phpinfo = str_replace("class=\"v\"", "class=\"evrow\"", $phpinfo);
            $phpinfo = str_replace("border=\"0\"", "border=\"1\"", $phpinfo);
            $phpinfo = str_replace("<h2>", "<h1>", $phpinfo);
            $phpinfo = str_replace("</h2>", "</h1>", $phpinfo);


            echo "
                    <table width='98%'>
                        <tr>
                            <td class='headline' colspan='6'>
                            <div class='headline cntr'>PHP Integration</div>
                            </td>
                        </tr>
                    </table>
                    <br />
                    <a class='bkbtn' href='admin.php?f=statistics&amp;type=server_info' title='Jump back to Server infos'>Back to Server Info</a>
                    <br /><br />
                    <center>
                    $phpinfo
                    <br />
                    <a class='navup' href='#head' title='Jump to Page Top'>Top</a>
                    <br /><br />
                ";
                    break;

                    //  Import / Export URL list
        case 40:
            include "url_backup.php";
            break;

            //  Import / Export settings (conf.php)
        case 41:
            include "setting_backup.php";
            break;

            //      Used for bulk delete of spider log files
        case 44;
        clearSpLog();
        break;

        //    Used for re-index with erase all sites
        case 45;
        $info = '0';
        $back = '';

        echo "<div class='submenu cntr'>Erase &amp; Re-index all</div>
                    ";

        //  delete temp table
        cleanTemp($back);
        //  delete pending table
        cleanPending($back);

        //  clear tables in db
        $erase =array ("domains","keywords","links","link_details","link_keyword","media");
        foreach ($erase as $allthis){
            $db_con->query ("TRUNCATE `".$mysql_table_prefix."$allthis`");
        }

        //  define all sites as erased
        $sql_query = "UPDATE ".$mysql_table_prefix."sites set indexdate='NULL'";
        $db_con->query ($sql_query);
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

        if ($clear_cache == '1') {
            $back = '';
            clearTextCache($back);
            clearMediaCache($back);
            if ($clear == "1" && $qcache == "1") {
                $mysql_cachereset  = $db_con->query("RESET QUERY CACHE");
                echo "<div>&nbsp;</div>
                            <p class='cntr em sml'>MySQL query cache cleared.</p>
                        ";
            }
        }

        echo "<br />
                    <p class='cntr em'>MySQL database and thumbnails folder cleared.</p>
                    ";

        if ($real_log == '0'){
            echo"
                        <br /><br />
                        <p class='cntr em'><a href='spider.php?all=3' title='Reindex now'>Okay, now re-index all</a></p>
                        <br />
                        <p class='cntr em'><a class='bkbtn' href='admin.php' title='Back to admin'>Return to admin without reindex</a>
                        <br /><br />
                        </p>
                    ";
        } else {
            echo "
                        <br /><br /><br />
                        <form action='spider.php' method='get'>
                            <table class='searchBox'>
                                <tr>
                                    <td>
                                    <input type='hidden' name='all' id='all' value='3' />
                                    <input type='submit' value='Start now to re-index all' onclick=\"window.open('real_log.php')\" />
                                    </td>
                                </tr>
                            </table>
                        </form>
                        <br /><br />
                        <p class='cntr em'>
                        <a class='bkbtn' href='admin.php' title='Back to admin'>Return to admin without re-index</a>
                        <br /><br />
                        </p>
                        ";
        }
        break;

        case 46;    // show all links of current site
        show_links($site_id);
        break;

        case 47;    // clean categories
        cleanCats();
        break;

        case 48;    // Erase & Re-index a single site
        refreshSite();
        break;

        case 49;    // Delete all indexed images and their thumbnails
        $sql_query = "DELETE from ".$mysql_table_prefix."media where type='image'";
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

        statisticsForm('thumb_files');

        break;

        case 50;    // Re-index all
        if ($real_log == '0'){
            echo "<div class='submenu cntr'>Re-index all</div>
                        ";
            if ($clear_cache == '1') {
                $back = '';
                clearTextCache($back);
                clearMediaCache($back);
                if ($clear == "1" && $qcache == "1") {
                    $mysql_cachereset  = $db_con->query("RESET QUERY CACHE");
                    echo "<div>&nbsp;</div>
                                <p class='cntr em sml'>MySQL query cache cleared.</p>
                            ";
                }
            }
            echo "
                        <p class='cntr em'>
                        <br /><br /><br />
                        <a href='spider.php?all=1' title='Reindex now'>Okay, now re-index all</a>
                        <br /><br /><br />
                        <a class='bkbtn' href='admin.php' title='Back to admin'>Return to admin without reindex</a>
                        <br /><br />
                        </p>
                    ";
        } else {
            echo "
                        <div class='submenu cntr'>Re-index all</div>
                        ";
            if ($clear_cache == '1') {
                clearTextCache();
                clearMediaCache();
                if ($clear == "1" && $qcache == "1") {
                    $mysql_cachereset  = $db_con->query("RESET QUERY CACHE");
                    echo "<div>&nbsp;</div>
                                <p class='cntr em sml'>MySQL query cache cleared.</p>
                            ";
                }
            }
            echo "
                        <br /><br /><br />
                        <form action='spider.php' method='get'>
                            <table class='searchBox'>
                                <tr>
                                    <td>
                                    <input type='hidden' name='all' id='all' value='1' />
                                    <input type='submit' value='Start now to re-index all' onclick=\"window.open('real_log.php')\" />
                                    </td>
                                </tr>
                            </table>
                        </form>
                        <br /><br />
                        <p class='cntr em'>
                        <a class='bkbtn' href='admin.php' title='Back to admin'>Return to admin without re-index</a>
                        <br /><br />
                        </p>
                    ";

            //createEndBody($lreal_handle);
            fclose ($real_handle);
        }

        break;

        case 51;    // Index only the new
        if ($real_log == '0'){
            echo "<div class='submenu cntr'>Index only the new sites</div>
                        <p class='cntr em'>
                        <br /><br /><br />
                        <a href='spider.php?all=2' title='Index now'>Okay, now index all new sites</a>
                        <br /><br /><br />
                        <a class='bkbtn' href='admin.php' title='Back to admin'>Return to admin without reindex</a>
                        <br /><br />
                        </p>
                    ";
        } else {
            echo "
        <div class='submenu cntr'>Index only new sites</div>
        <p class='cntr em'>
        <br /><br /><br />
        <form action='spider.php' method='get'>
            <table class='searchBox'>
                <tr>
                    <td>
                    <input type='hidden' name='all' id='all' value='2' />
                    <input type='submit' value='Start now to index all new sites' onclick=\"window.open('real_log.php')\" />
                    </td>
                </tr>
            </table>
        </form></p>
        <br /><br />
        <p class='cntr em'>
        <a class='bkbtn' href='admin.php' title='Back to admin'>Return to admin without re-index</a>
        <br /><br />
        </p>
                    ";

            //createEndBody($lreal_handle);
            fclose ($real_handle);
        }

        break;

        case 52;    // Used to delete text cache
        $back = '1';
        clearTextCache($back);
        break;

        case 53;    // Used to delete media cache
        $back = '1';
        clearMediaCache($back);
        break;

        case 54;    // Index all erased sites
        if ($real_log == '0'){
            echo "<div class='submenu cntr'>Index all erased sites</div>
                        <p class='cntr em'>
                        <br /><br /><br />
                        <a href='spider.php?all=3' title='Index now'>Okay, now index all erased sites</a>
                        <br /><br /><br />
                        <a class='bkbtn' href='admin.php' title='Back to admin'>Return to admin without reindex</a>
                        <br /><br />
                        </p>
                    ";
        } else {
            echo "
        <div class='submenu cntr'>Index all erased sites</div>
        <p class='cntr em'>
        <br /><br /><br />
        <form action='spider.php' method='get'>
            <table class='searchBox'>
                <tr>
                    <td>
                    <input type='hidden' name='all' id='all' value='3' />
                    <input type='submit' value='Start now to index all erased sites' onclick=\"window.open('real_log.php')\" />
                    </td>
                </tr>
            </table>
        </form></p>
        <br /><br />
        <p class='cntr em'>
        <a class='bkbtn' href='admin.php' title='Back to admin'>Return to admin without re-index</a>
        <br /><br />
        </p>
                    ";

            //  createEndBody($lreal_handle);
            fclose ($real_handle);
        }

        break;

        case 55;    // erase a single site
        eraseSite();
        break;

        case 56;    // Index all suspended sites
        if ($real_log == '0'){
            echo "<div class='submenu cntr'>Re-index all suspended sites</div>
                        <p class='cntr em'>
                        <br /><br /><br />
                        <a href='spider.php?all=20' title='Index now'>Okay, now continue re-indexing all suspended sites</a>
                        <br /><br /><br />
                        <a class='bkbtn' href='admin.php' title='Back to admin'>Return to admin without re-index</a>
                        <br /><br />
                        </p>
                    ";
        } else {
            echo "
        <div class='submenu cntr'>Index all suspended sites</div>
        <p class='cntr em'>
        <br /><br /><br />
        <form action='spider.php' method='get'>
            <table class='searchBox'>
                <tr>
                    <td>
                    <input type='hidden' name='all' id='all' value='20' />
                    <input type='submit' value='Start now to index all suspended sites' onclick=\"window.open('real_log.php')\" />
                    </td>
                </tr>
            </table>
        </form></p>
        <br /><br />
        <p class='cntr em'>
        <a class='bkbtn' href='admin.php' title='Back to admin'>Return to admin without re-index</a>
        <br /><br />
        </p>
                    ";

            //  createEndBody($lreal_handle);
            fclose ($real_handle);
        }

        break;

        case 57;    // Used to show IDS log file menu for clearance
        $back = '1';
        showIDS($back);
        break;

        case clear_ids;    // Used to clear IDS log file completely
        $handle = @fopen ("$include_dir/IDS/tmp/phpids_log.txt","r");
        if ($handle) {      //      read IDS log file
            $lines = @file("$include_dir/IDS/tmp/phpids_log.txt");
            @fclose($handle);
            $ids_count = count($lines);
        }
        $handle = @fopen ("$include_dir/IDS/tmp/phpids_log.txt","w");   //  clear IDS log file
        @fclose($handle);
        echo "<div class='submenu'>&nbsp;</div>
                    <p class='warnok'>IDS log cleared. [<span class='warnok'> $ids_count </span>] items deleted.</p>
                    <a class='bkbtn' href='admin.php?f=clean' title='Go back to Clean'>Back</a>
                ";
        break;

        case clear_addurl;
        $back = '1';
        clearAddurl($back);
        break;

        case clear_banned;
        $back = '1';
        clearBanned($back);
        break;


        case clear_xml;
        $back = '1';
        clearXML($back);
        break;

        case truncate_all;

        if (!$do_it) {
            echo "
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p class='cntr warnadmin em'>
            <br /><br />
            Are you sure to truncate all tables in database $dba_act&nbsp;&nbsp;with table prefix '$mysql_table_prefix'&nbsp;&nbsp;?<br /><br />
            <br/>
            All table content will be lost!
            <br />
            <br />
        </p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <table class='cntr' width='30%'>
            <tr>
                <form action='admin.php' method='get'>
                    <td>
                        <p>&nbsp;</p>
                        <input type='hidden' name='f' id='truncate_all' value='truncate_all' />
                        <input type='hidden' name='do_it' id='do_it' value='do_it' />
                        <input type='submit' value='&nbsp;&nbsp;Yes&nbsp;&nbsp;' />
                        <p>&nbsp;</p>
                    </td>
                </form>
                <form action='admin.php' method='get'>
                    <td>
                        <p>&nbsp;</p>
                        <input type='submit' value='&nbsp;&nbsp;NO&nbsp;&nbsp;' />
                        <input type='hidden' name='f' id='clean'  value='clean'/>
                        <p>&nbsp;</p>
                    </td>
                </form>
            </tr>
        </table>
        <br /><br />
         <a class='bkbtn' href='admin.php' title='Back to admin without truncating all content'>Back to admin</a>
        <br /><br />
    </body>
</html>";
            exit();
        }
        $back = '1';
        truncateAll($back);
        break;

        case 58;    // Re-index the sites actually shown on 'Sites' view
        if ($real_log == '0'){
            echo "<div class='submenu cntr'>Re-index all pre-defined sites</div>
                        <p class='cntr em'>
                        <br /><br /><br />
                        <a href='spider.php?all=21' title='Re-index now'>Okay, now Re-index these sites</a>
                        <br /><br /><br />
                        <a class='bkbtn' href='admin.php' title='Back to admin'>Return to admin without reindex</a>
                        <br /><br />
                        </p>
                    ";
        } else {
            echo "
        <div class='submenu cntr'>Re-index all pre-defined sites</div>
        <p class='cntr em'>
        <br /><br /><br />
        <form action='spider.php' method='get'>
            <table class='searchBox'>
                <tr>
                    <td>
                    <input type='hidden' name='all' id='all' value='21' />
                    <input type='submit' value='Start now to Re-index all pre-defined sites' onclick=\"window.open('real_log.php')\" />
                    </td>
                </tr>
            </table>
        </form></p>
        <br /><br />
        <p class='cntr em'>
        <a class='bkbtn' href='admin.php' title='Back to admin'>Return to admin without re-index</a>
        <br /><br />
        </p>
                    ";

            //  createEndBody($lreal_handle);
            fclose ($real_handle);
        }

        break;

        case 59;    //  activate the periodical  Re-index procedure in script 'auto_index.php'
        $content    = '';
        $when       = '';
        $finished   = '';
        $aborted    = '';
        $final      = '';
        $delay      = '';
        $fp         = '';
        $content    = '';
        $first_start= '';
        $when       = '';
        $site_url   = '';

        //  define log file for periodical Re-indexer
        if (!$site_id) {
            $logfile    = "log/db".$dba_act."_".$mysql_table_prefix."._all_auto-indexer.log";
        } else {
            $logfile    = "log/db".$dba_act."_".$mysql_table_prefix."._".$site_id."_auto-indexer.log";
            //  get URL according to actual site_id (for site specific periodical Re-indexing only)
            $sql_query = "SELECT * from ".$mysql_table_prefix."sites where site_id=$site_id";
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

            $row = $result->fetch_array(MYSQLI_ASSOC);

            $site_url           = $row['url'];
            $maxlevel           = $row['spider_depth'];
            $include            = $row['required'];
            $not_include        = $row['disallowed'];
            $can_leave_domain   = $row['can_leave_domain'];
            $use_prefcharset    = $row['use_prefcharset'];
        }

        //  make interval (seconds) readable for monitor output
        if ($interval == "10800")   $delay = "3 hours";
        if ($interval == "43200")   $delay = "12 hours";
        if ($interval == "86400")   $delay = "1 day";
        if ($interval == "604800")  $delay = "1 week";
        if ($interval == "2419200") $delay = "1 month";

        echo "    <div class='submenu cntr'>
                    Auto Re-indexer
                </div>
                <p class='cntr em'>
                  <br /><br />
                  The time interval for periodical Re-indexing is set to $delay
                  <br />
                </p>
                ";
        if ($site_url) {
            echo "
                <p class='cntr'>
                  <br />
                  URL to be re-indexed periodically:
                </p>
                <p class='cntr em'>
                $site_url
                  <br />
                </p>
                    ";
        }

        $final= array_pop(@file($logfile));     // get last entry of log-file
        if (strstr($final,"finished" )) {       //  and convert to date+time
            $finished = date("Y-m-d H:i:s",substr($final, strpos($final, "count")+8,10));
        }

        if (strstr($final,"aborted" )) {       //  and convert to date+time
            $aborted = date("Y-m-d H:i:s",substr($final, strpos($final, "count")+7,10));
        }

        $fp = @fopen($logfile,"r");
        if (!$fp) {   //  if the log file does not yet exist
            $fp = @fopen($logfile,"w");
            if (!is_writable($logfile)) {   //  unwritable, error message
                print "Auto indexer not available, because the log-file is not writable.";
                @fclose($fp);
                die();
            }
            @fclose($fp);
            @unlink($logfile);  //  was used only for 'write' test. Auto indexer will start its log-file

        } else {    //  if Auto-indexer was already started before
            $content = fread($fp, 8192);
            if (strpos($content, "ount")) {
                $first_start = substr($content, strpos($content, "count")+5,10);
                $when = date("Y-m-d H:i:s",$first_start);
                echo"<p class='cntr em'>
                    <br /><br />
                    Auto Re-indexer has already been started at $when
                    <br /><br />
                </p>
                        ";
                if ($finished) {
                    echo"<p class='cntr em'>
                        and finished at $finished
                        <br /><br />
                    </p>
                        ";
                }

            } else {
                @fclose($fp);
                @unlink($logfile);
            }
        }

        if ($when && !$finished) { //  enable aborting of running Auto Re-index procedure
            if (!$aborted) {
                echo"<p class='cntr em'>
                            <br />
                            <a href='admin.php?f=60&amp;logfile=$logfile' title='Abort auto indexer'>Abort the periodical Re-indexer.</a>
                            <br /><br /><br />
                        </p>
                        ";
            } else {
                echo "<p class='cntr em warnadmin'>
                            Last Re-index took too long. Process was aborted by too short time interval.
                        </p>
                        ";
            }
        }

        if ($when  && $finished || !$when || $when && $aborted) {    //  allow start and restart of Auto-indexer
            echo" <form action='auto_index.php' method='get'>
                    <p class='cntr em'>
                    <br /><br />
                    Auto indexing could be started by pressing the button below.
                    <br /><br />
                    </p>
                    <p class='cntr sml'>(This Re-indexing will silently work in the background)</p>
                    <p class='cntr em'>
                    <br /><br />
                    Afterwards you may return to the Admin menu.
                    <br /><br /><br /><br />
                    <text-align 'center'>
                    ";
            if ($finished) {    //  restart after finished by interval counter
                echo "
                    <input type='submit' name='submit' class='sbmt' title='Just press it once. Further clicking will be ignored.' value='Restart the Auto Re-indexer now' />
                        ";
            } else {            //  fresh start for the Auto Re-indexer
                echo "
                    <input type='submit' name='submit' class='sbmt' title='Just press it once. Further clicking will be ignored.' value='Start the Auto Re-indexer now' />
                        ";
            }
            echo "
                    <input type='hidden' name='start' value='1' />
                    <input type='hidden' name='i' value='1' />
                    <input type='hidden' name='site_url' value='".$site_url."' />
                    <input type='hidden' name='logfile' value='".$logfile."' />
                    <input type='hidden' name='maxlevel' value='".$maxlevel."' />
                    <input type='hidden' name='include' value='".$include."' />
                    <input type='hidden' name='not_include' value='".$not_include."' />
                    <input type='hidden' name='can_leave_domain' value='".$can_leave_domain."' />
                    <input type='hidden' name='use_prefcharset' value='".$use_prefcharset."' />
                </form>
                <br /><br /><br /><br />
                    ";
        }

        echo "
                  <p class='cntr em'>
                    <a class='bkbtn' href='admin.php' title='Back to admin'>Return to Admin menu</a>
                    <br /><br />
                  </p>
                ";
        break;

        case 60;    //  Used to abort the periodical Re-indexer inderectly by deleting its log file
        //echo "\r\n\r\n<br /> logfile: $logfile<br />\r\n";
        @unlink($logfile);
        echo "    <p class='cntr em'>
                    <br /><br />
                    Okay, periodical Re-indexing will be aborted at next call of index interval.
                    <br /><br /><br />
                    <a class='bkbtn' href='admin.php' title='Back to admin'>Return to admin </a>
                    <br /><br />
                </p>
                ";
        break;

        case 61;    // index only the preferred sites
        if ($real_log == '0'){
            echo "    <div class='submenu cntr'>Index only the preferred sites</div>
                <br /><br /><br />
                <form class='txt' action='spider.php' method='post' >
                    <input type='hidden' name='all' value='22' />
                    <p class='cntr em'>Define preference level, which should be used for this procedure:
                    <select name='pref_level' id='pref_level' title='Select level for Re-index preferred sites'>
                        <option value='1'";
                    if ($pref_level == '1'){
                        echo "checked='checked'";
                    }
                    echo ">1</option>
                        <option value='2'";
                    if ($pref_level == '2') {
                        echo " selected='selected'";
                    }
                    echo ">2</option>
                        <option value='3'";
                    if ($pref_level == '3') {
                        echo " selected='selected'";
                    }
                    echo ">3</option>
                        <option value='4'";
                    if ($pref_level == '4') {
                        echo " selected='selected'";
                    }
                    echo ">4</option>
                        <option value='5'";
                    if ($pref_level == '5') {
                        echo " selected='selected'";
                    }
                    echo ">5</option></select></p>
                    <p>&nbsp;</p>
                    <p>&nbsp;</p>
                    <p class='cntr em'><input type='submit' id='submit' value='Start Re-index procedure' title='Click to proceed' /></p>
                </form>
                <br /><br /><br />
                <p class='cntr em'><a class='bkbtn' href='admin.php' title='Back to admin'>Return to admin without reindex</a></p>
                <br /><br />

            ";
        } else {
            echo "
        <div class='submenu cntr'>Index only preferred sites</div>
        <p class='cntr em'>
        <br /><br /><br />
        <form action='spider.php' method='get'>
            <table class='searchBox'>
                <tr>
                    <td>
                    <input type='hidden' name='all' id='all' value='22' />
                    <input type='submit' value='Start now to index all preferred sites' onclick=\"window.open('real_log.php')\" />
                    </td>
                </tr>
            </table>
        </form></p>
        <br /><br />
        <p class='cntr em'>
        <a class='bkbtn' href='admin.php' title='Back to admin'>Return to admin without re-index</a>
        <br /><br />
        </p>
                    ";

            //createEndBody($lreal_handle);
            fclose ($real_handle);
        }
        break;

        case bound_db;    // Used to delete all needless keywords in database
        $back = '';
        clearTextCache($back);
        clearMediaCache($back);
        bound_db($back);
        break;

        case clear_tmp;     //  used to delete all files in temp folder (as per default ../admin/tmp/
        $back = '1';
        clearTempFolder($back);
        break;

        case database;
        include "db_main.php";
        break;

        case settings;
        include('configset.php');
        break;

        case delete_log;
        unlink($log_dir."/".$file);
        statisticsForm('spidering_log');
        break;

        case delete_thumb;
        $sql_query = "DELETE from ".$mysql_table_prefix."media where media_id=$file";
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

        statisticsForm('thumb_files');
        break;

        case '':
            showsites();
            break;

        case statistics;
        if (!isset($type))
        $type = "";
        statisticsForm($type);
        break;

        case index;
        if (!isset($url))
        $url = "";
        if (!isset($reindex))
        $reindex = "";
        if (isset($adv)) {
            $_SESSION['index_advanced']=$adv;
        }
        indexscreen($url, $reindex);
        break;

        case add_site;
        addsiteform();
        break;

        case clean;
        cleanForm();
        break;

    }

    if ($db_con && $success && $tables) {
        $stats = getStatistics();

        echo "<div >
                <p class='stats'>
                    <br />
                    <span class='em'>Database ".$dba_act."&nbsp;&nbsp;with table prefix '$mysql_table_prefix'&nbsp;&nbsp;&nbsp;contains: </span>
                    <br /><br />
                    ".$stats['sites']." sites <b>+</b> ".$stats['links']." page links <b>+</b> ".$stats['categories']." categories <b>+</b> ".$stats['keywords']." keywords
                ";

        if ($index_media == '1') {
            echo "    <strong>+</strong> ".$stats['media']." media links.
                ";
            if ($debug == "2") {    //  show free disk space
                $bytes = '';
                $bytes = @disk_free_space(".");
                if ($bytes) {
                    $si_prefix = array( 'B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB' );
                    $base = 1024;
                    $class = min((int)log($bytes , $base) , count($si_prefix) - 1);
                    echo "&nbsp;&nbsp;";
                    echo sprintf('(%1.1f' , $bytes / pow($base,$class)) . ' ' . $si_prefix[$class] . ' disk space free)';
                }
            }
        }

        echo "  <br /><br />
                </p>
                <br />
                <form action=\"admin_search.php\" method=\"get\">
                    <p style=\"text-align:center;\">";

        //  show Admin search form for "Sites" and "Categories" menue
        if ($f == '2' || $f =="categories" ) {
            if ($repeat != '1') {
                echo "
                        <input placeholder=\"Admin query input here\" type=\"text\" name=\"query\" size=\"50\" value=\"\" />&nbsp;&nbsp;&nbsp;Search for:&nbsp;&nbsp;&nbsp;&nbsp;";
            } else {
                echo "
                        <input type=\"text\" name=\"query\" size=\"50\" value=\"\" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Search for:";
            }

            echo "
                        <input type=\"submit\" name=\"submit\" value=\"&nbsp;&nbsp;sites&nbsp;&nbsp;\" title=\"Will always search with wildcards for domains and sites already included here\" />&nbsp;&nbsp;&nbsp;
                        <input type=\"submit\"  name=\"submit\" value=\"&nbsp;&nbsp;&nbsp;&nbsp;links&nbsp;&nbsp;&nbsp;&nbsp;\"  title=\"Will always search with wildcards for links already indexed\"/>&nbsp;&nbsp;&nbsp;
                        <input type=\"submit\" name=\"submit\" value=\"keywords\"  title=\"Will always search with wildcards for keywords already indexed\"/>&nbsp;&nbsp;&nbsp;
                        <input type=\"submit\" name=\"submit\" value=\"categories\"  title=\"Will always search with wildcards for categories already defined\"/>
                        <input type=\"hidden\" name=\"start\" value=\"1\" />
                    </p>
                </form>
                <br />
                ";

        }
    }

    include "admin_footer.php" ;
?>
