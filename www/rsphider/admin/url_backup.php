<?php
/*******************************************************
 This script handles the import / export and delete functions for the URL list
 Called by 'admin.php' via f=40, the backup files are processed.
 *******************************************************/

    define("_SECURE",1);            // define secure constant
    $send2 = '';
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE & ~E_STRICT);
    extract($_POST);
    extract($_GET);
    extract($_REQUEST);

    $now            = date("Y.m.d-H.i.s");
    $filename       = "db".$dba_act."_".$mysql_table_prefix."_urls_$now.txt";
    $files          = array();
    $default        = '';
    $settings_dir   = "../settings";

    @include "".$settings_dir."/db".$dba_act."/conf_".$mysql_table_prefix.".php";
    if (!$plus_nr) {
        include "./settings/backup/Sphider-plus_default-configuration.php";
        $default = '1';
    }

    $template_dir   = "../".$templ_dir."";
    $template_path  = "$template_dir/$template";

    if(!$latest) {  //we do need the HTML header, because it is a fresh output
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
            <div id='main'>
        ";
    }

    //      Headline for URL import/export
    echo "<div class='submenu cntr'>| URL Import/Export Management |</div>
        <div class='tblhead'>
            <form name='urlimport' id='urlimport' method='post' action='admin.php'>
            <dl class='tblhead'>
                ";

    //      List available URL files
    if (!is_dir($url_path)) {
        mkdir($url_path, 0766);
    }

    $bgcolor    = 'odrow';
    $is_first   = 1;
    $files      = scandir($url_path);

    if($is_first==1){
        echo "<dt class='headline x2 cntr'>URL Files</dt>
                <dd class='headline cntr'>Manage</dd>
                ";
    }

    $is_first=0;
    $count_urls = 0;
    foreach ($files as $this_file) {
        if (preg_match("/_/i",$this_file)) {    //show only files with a  _ in its name
            $count_urls++ ;
            echo "<dt class='$bgcolor x2' style='padding:9px;'>$this_file</dt>
                    <dd class='$bgcolor cntr'>
                    <input class='sbmt' type='button' name='lrestore'
                    onclick=\"confirm_rest_url('./admin.php?f=40&amp;file=$this_file&amp;del=0');\" value='Import'
                    title='Beware! Once started, the current database will be modified!'
                    />
                    <input class='sbmt' type='button' name='ldelete'
                    onclick=\"confirm_del_url('./admin.php?f=40&amp;file=$this_file&amp;del=1');\" value='Delete'
                    title='Click to delete this URL file'
                    />
                    </dd>
                ";

            if ($bgcolor=='odrow') {
                $bgcolor='evrow';
            } else {
                $bgcolor='odrow';
            }
        }
    }

    if($count_urls == 0){
        echo "<dt class='odrow x2 cntr'><span class='warnadmin'>No URL file exists!</span></dt>
                <dd class='odrow cntr'>You should create a file</dd>
            ";
    }
    echo "</dl>
            <br />
            <div class='panel cntr'>
                <input type='hidden' name='f' value='40' />
                <p class='evrow cntr sml'>Create a new URL backup file from database <span class='red'>$dba_act</span> and table set <span class='red'>$mysql_table_prefix</span><input class='sbmt' type='submit' name='send2' value='Create'
                title='Create a new URL file from current sites table' /></p>
            </div>
            </form>
        </div>
        ";

//      Enter here to create a new URL file
    if($send2 == "Create") {
        echo "<p class='headline x1 cntr'><span class='bd'><br />Export Url list</span></p>
                ";

        $file   = "$url_path$filename";

        if (!is_dir($url_path)) {
            mkdir($url_path, 0766);
        }

        if (!$handle = fopen($file, "w")) {
            print "Unable to open $file (destination file)";
            exit;
        }
        echo "<br /><p class='alert'><span class='em'>
                Starting to export to file: $file</p>
            ";

        //      Get url  from database
        $sql_query = "SELECT * from ".$mysql_table_prefix."sites order by url";
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
        while ($this_array = $result->fetch_array(MYSQLI_ASSOC)) {
            $all[] = $this_array;
        }

        for ($i=0; $i<$rows; $i++) {
            $site_id            = $all[$i]["site_id"];
            $url                = $all[$i]["url"];
            $title              = $all[$i]["title"];
            $short_desc         = $all[$i]["short_desc"];
            $index_date         = $all[$i]["indexdate"];
            $spider_depth       = $all[$i]["spider_depth"];
            $required           = $all[$i]["required"];
            $disallowed         = $all[$i]["disallowed"];
            $can_leave_domain   = $all[$i]["can_leave_domain"];
            $db                 = $all[$i]["db"];
            $smap_url           = $all[$i]["smap_url"];
            $authent            = $all[$i]["authent"];
            $use_prefcharset    = $all[$i]["use_prefcharset"];
            $prior_level        = $all[$i]["prior_level"];

            //  decode the line feed
            $required           = str_replace("\r\n", "_-_", $required);
            $disallowed         = str_replace("\r\n", "_-_", $disallowed);

            $num = $i+1;

            if ($show_url == 1) {
                if ($num & 1) {
                    echo "	<p class='odrow'>\n";
                } else {
                    echo "	<p class='evrow'>\n";
                }
                echo "
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$num. $url</p>
                        ";
                echo ("");
            }

            //      Search for possible category_id
            $category = '';
            $sql_query ="SELECT * from ".$mysql_table_prefix."site_category where site_id ='$site_id'";
            $reso = $db_con->query($sql_query);
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

            $cat_rows =  $reso->num_rows;
           
            if ($cat_rows) {
                //  get all categories
                while ($this_array = $reso->fetch_array(MYSQLI_ASSOC)) {
                    $cats[] = $this_array;
                }

                for ($j=0; $j<$cat_rows; $j++) {
                    $cat_id = $cats[$j]["category_id"];
                    //      If exist, get name of category
                    if ($cat_id) {
                        $sql_qry = "SELECT * from ".$mysql_table_prefix."categories where category_id ='$cat_id'";
                        $res_cat = $db_con->query($sql_qry);
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
                            printf("<p>$sql_qry</p>");
                            exit;
                        }
                        $cat        = $res_cat->fetch_array(MYSQLI_ASSOC);                         
                        $this_cat   = $cat['category'];
                        $category .= "$this_cat,";

                    }
                }
           
                $group_sel0 = $cat['group_sel0'];
                $group_sel1 = $cat['group_sel1'];
                $group_sel2 = $cat['group_sel2'];
                $group_sel3 = $cat['group_sel3'];
                $group_sel4 = substr($cat['group_sel4'], 0, -1);   //  kill the last delimiter
                $category   = substr($category, 0, -1);
            }

            //      Now write all data to file
            if (!fwrite($handle, "$url$arg_delim$title$arg_delim$short_desc$arg_delim$index_date$arg_delim$spider_depth$arg_delim$required$arg_delim$disallowed$arg_delim$can_leave_domain$arg_delim$db$arg_delim$smap$arg_delim$authent$arg_delim$use_prefcharset$arg_delim$category$arg_delim$group_sel0$arg_delim$group_sel1$arg_delim$group_sel2$arg_delim$group_sel3$arg_delim$group_sel4$arg_delim$prior_level\n")) {
                print "Unable to write to $file";
                exit;
            }
        }

        //      Close file
        fclose($handle);

        echo "<br />
                <p class='headline x1 cntr'>Ready</p>
                <div>
                <p class='evrow cntr'>
                <a class='bkbtn' href='admin.php?f=40' title='Go back to URL Management'>Complete this process</a></p></div>
                </div>
                </div>
                </body>
                </html>
            ";
        die ('');
    }

//      Enter here to restore URL files into database
    if (isset($file) && $del==0) {

        $file               = "$url_path$file";
        $short_desc 		= '';
        $title 				= '';
        $required			= '';
        $disallowed			= '';
        $can_leave_domain	= '';
        $parent_num         = "0";
        $lines              = array();

        //  read .xls files into an array
        if (stristr($file, ".xls" )) {
            $error = '';
            require_once "".$converter_dir."/xls_reader.php";
            $data = new Spreadsheet_Excel_Reader();

            if ($mb == '1') {
                //  if extention exists, change 'iconv' to mb_convert_encoding:
                $data->setUTFEncoder('mb');
            }

            // set output encoding.
            $data->setOutputEncoding('UTF-8');

            //  read this document
            $data->read($file);
            $error = $data->_ole->error;
            if ($error == '1'){
                printStandardReport('xlsError',$command_line, $no_log);
                $result = 'ERROR';
            } else {
                $result = ' ';
                $boundsheets    = array();
                $sheets         = array();
                $boundsheets    = $data->boundsheets;   // get all tables in this file
                $sheets         = $data->sheets;        // get content of all sheets in all tables

                if($boundsheets) {
                    foreach ($boundsheets as &$bs) {
                        //$result .= "".$bs['name'].", "; //  collect all table names in this file
                    }

                    if ($sheets) {
                        foreach ($sheets as &$sheet) {
                            $cells = $sheet['cells'];
                            if ($cells) {    //  ignore empty cells
                                foreach ($cells as &$cell) {    //  fetch all individual data for each cell
                                    $lines[] .= "$cell[1]$arg_delim$cell[2]$arg_delim$cell[3]$arg_delim$cell[4]$arg_delim$cell[5]$arg_delim$cell[6]$arg_delim$cell[7]$arg_delim$cell[8]$arg_delim$cell[9]$arg_delim$cell[10]$arg_delim$cell[11]$arg_delim$cell[12]$arg_delim$cell[13]$arg_delim$cell[14]$arg_delim$cell[15]$arg_delim$cell[16]$arg_delim$cell[17]$arg_delim$cell[18]$arg_delim";
                                }
                            }
                        }
                    }
                }
            }
        }

        //  read .txt files into an array
        if (stristr($file, ".txt" )) {
            $lines    = file($file);
        }

        echo "<br /><p class='alert'><span class='em'>Starting to import</p>
            ";

        $num = '1';
        foreach ($lines as $new) {

            $new = trim(substr ($new, 0,4096));

            //echo "<br>NEW:<br><pre>";print_r($new);echo "</pre>";
            if (strlen($new) > 10) {
                $new = explode($arg_delim,$new);

                $url                = $new[0];
                $title              = $db_con->real_escape_string($new[1]);
                $short_desc         = $db_con->real_escape_string($new[2]);
                $index_date         = '0000-00-00';     //  if URL is imported, set index date always to 0000-00-00
                //$index_date         = $new[3];
                $spider_depth       = $new[4];
                $required           = $db_con->real_escape_string($new[5]);
                $disallowed         = $db_con->real_escape_string($new[6]);
                $can_leave_domain   = $new[7];
                //$db                 = $new[8];                                                        //  remains free in order to import into all databases
                $smap_url           = $new[9];
                $authent            = $new[10];
                $use_prefcharset    = $new[11];
                $category           = $db_con->real_escape_string($new[12]);
                $group_sel0         = $db_con->real_escape_string($new[13]);
                $group_sel1         = $db_con->real_escape_string($new[14]);
                $group_sel2         = $db_con->real_escape_string($new[15]);
                $group_sel3         = $db_con->real_escape_string($new[16]);
                $group_sel4         = $db_con->real_escape_string($new[17]);
                $prior_level        = $new[18];

                if (!$prior_level) {
                    $prior_level = 1;
                }

                //  encode the line feed
                $required           = str_replace("_-_", "\r\n", $required);
                $disallowed         = str_replace("_-_", "\r\n", $disallowed);

                if ($spider_depth == ('')) $spider_depth = '-1';

                if ($show_url == 1) {
                    if ($num & 1) {
                        echo "	<p class='odrow'>\n";
                    } else {
                        echo "	<p class='evrow'>\n";
                    }
                    echo "
                    $num. $url<br />
                        ";
                }

                //  clean url
                if ($idna && strstr($url, "xn--")) {
                    require_once "$include_dir/idna_converter.php";
                    // Initialize the converter class
                    $IDN = new idna_convert(array('idn_version' => 2008));
                    // The input string, if input is not UTF-8 or UCS-4, it must be converted before
                    //$input = utf8_encode($url);
                    // Decode it to its readable presentation
                    $url = $IDN->decode($url);
                }

                $url        = urldecode($db_con->real_escape_string($url));
                $compurl    = parse_url("".$url);
                if ($compurl['path']=='')
                $url=$url."/";
                $sql_query = "SELECT site_ID from ".$mysql_table_prefix."sites where url='$url'";
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
                if ($rows===0 ) {
                    //  save new url, spider-depth and number of currently activated Admin database
                    $sql_query = "INSERT INTO ".$mysql_table_prefix."sites (url, title, short_desc, indexdate, spider_depth, required, disallowed, can_leave_domain, db, smap_url, authent, use_prefcharset, prior_level )
                                                                    VALUES ('$url','$title','$short_desc',$index_date,'$spider_depth','$required','$disallowed','$can_leave_domain','$dba_act','$smap_url','$authent','$use_prefcharset','$prior_level')";
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
                    //  handle the category if we do have one
                    if ($category) {
                        $all_cats = explode(",", $category);    //  in case that the URL is part of different categories, we need to add all categories

                        foreach($all_cats as $category) {
/*
                            $sql_qry = "SELECT category from ".$mysql_table_prefix."categories where category='$category'";
                            $result = $db_con->query($sql_qry);
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
                                printf("<p>$sql_qry</p>");
                                exit;
                            }

                            $rows = $result->num_rows;
*/
                            // add new categories
                            $sql_query1 = "INSERT INTO ".$mysql_table_prefix."categories (category, parent_num, group_sel0, group_sel1, group_sel2, group_sel3, group_sel4) VALUE ('$category', '$parent_num', '$group_sel0', '$group_sel1', '$group_sel2', '$group_sel3', '$group_sel4')";
                            $db_con->query($sql_query1);
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
                                printf("<p>$sql_query1</p>");
                                exit;
                            }

                            //  get the new category_id
                            $sql_query2 = "SELECT category_id from ".$mysql_table_prefix."categories where category='$category' and group_sel0='$group_sel0' and group_sel1='$group_sel1' and group_sel2='$group_sel2' and group_sel3='$group_sel3' and group_sel4='$group_sel4'";
                            $result = $db_con->query($sql_query2);
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
                                printf("<p>$sql_query2</p>");
                                exit;
                            }
                            $cat = $result->fetch_array(MYSQLI_ASSOC);
                            $cat_id = $cat['category_id'];

                            //  get site_id
                            $sql_query3 = "SELECT * from ".$mysql_table_prefix."sites where url='$url'";
                            $result = $db_con->query($sql_query3);
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
                                printf("<p>$sql_query3</p>");
                                exit;
                            }
                            $sit = $result->fetch_array(MYSQLI_ASSOC);
                            $site_id = $sit['site_id'];

                            //  save new site_id and cat_id
                            $sql_query4 = "INSERT INTO ".$mysql_table_prefix."site_category (site_id, category_id) VALUES ('$site_id', '$cat_id')";
                            $db_con->query($sql_query4);
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
                                printf("<p>$sql_query4</p>");
                                exit;
                            }
                            //echo "<br>cat id:<br><pre>";print_r($cat);echo "</pre>";
                        }
                    }

                } else 	{
                    if ($show_url == 1)  {
                        echo "
                                <span class='warnadmin'>
                                Attention: Site is already in database. Currently not imported a second time.</span>
                            ";
                    }
                }
                echo "</p>
                    ";
            }
            $num++ ;
        }

        echo "<br />
                <p class='headline x1'>Ready</p>
                <div>
                <p class='evrow cntr'>
                <a class='bkbtn' href='admin.php?f=40' title='Go back to URL Management'>Complete this process</a></p></div>
                </div>
                </div>
                </body>
                </html>
            ";
        die ('');

    }

    //      Enter here to delete URL files
    if (isset($file) && $del==1) {

        if (is_dir($url_path)) {
            if ($dh = opendir($url_path)) {
                while (($this_file = readdir($dh)) !== false) {
                    if ($this_file == $file) {
                        @unlink("$url_path/$this_file");    //    delete this file
                    }
                }
                closedir($dh);
            }
        }

        echo "<div class='cntr'>
             <body onload='JumpBottom()'>
             <p class='odrow bd cntr'>URL File '$file' deleted.</p>
             <p class='evrow cntr'>
             <a class='bkbtn' href='admin.php?f=40' title='Go back to URL Management'>Complete this process</a></p></div>
            </div>
            </div>
            </body>
            </html>
            ";
        die ('');
    }

    echo "
            </div>
            </div>
            </body>
            </html>
            ";

    die ('');
?>