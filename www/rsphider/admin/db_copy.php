<?php

    define("_SECURE",1);    // define secure constant

    include "admin_header.php";
    //include "auth_db.php";
    include "db_common.php" ;
    include "$settings_dir/database.php";

    $path = "./tmp";        //      folder for temporary sql files
    $delimiter  = ";#%%";   //      delimiter for sql insert

    //      Header like in global Admin
    $site_funcs     = Array (22=> "default",21=> "default",4=> "default", 19=> "default", 1=> "default", 2 => "default", "add_site" => "default", 20=> "default", 28=> "default", 30=> "default", 40=> "default", 45=> "default", 50=> "default", 51=> "default", "edit_site" => "default", 5=>"default");
    $stat_funcs     = Array ("statistics" => "default",  "delete_log"=> "default");
    $settings_funcs = Array ("settings" => "default", 41=> "default");
    $index_funcs    = Array ("index" => "default");
    $clean_funcs    = Array ("clean" => "default", 15=>"default", 16=>"default", 17=>"default", 23=>"default");
    $cat_funcs      = Array (11=> "default", 10=> "default", "categories" => "default", "edit_cat"=>"default", "delete_cat"=>"default", "add_cat" => "default", 7=> "default");
    $database_funcs = Array ("database" => "default");

    echo "    <div id='admin'>
                <div id='tabs'>
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

    echo "                 <li><a title='Manage Sites' href='admin.php?f=2' class='$site_funcs[$f]'>Sites</a></li>
                        <li><a title='Manage Categories' href='admin.php?f=categories' class='$cat_funcs[$f]'>Categories</a></li>
                        <li><a title='Indexing Options' href='admin.php?f=index' class='$index_funcs[$f]'>Index</a></li>
                        <li><a title='Main Settings' href='admin.php?f=settings' class='$settings_funcs[$f]'>Settings</a></li>
                        <li><a  name='head' title='Indexing Statistics' href='admin.php?f=statistics' class='$stat_funcs[$f]'>Statistics</a> </li>
                        <li><a title='Database Cleaning Options' href='admin.php?f=clean' class='$clean_funcs[$f]'>Clean</a> </li>
                        <li><a title='Display Database Contents' href='admin.php?f=database&amp;sel=1' class='selected'>Database</a></li>
                        <li><a title='Close Sphider' href='admin.php?f=24' class='default'>Log out</a></li>
                    </ul>
                </div>
                <div id='main'>
        ";

    extract (getHttpVars());

    //      Headline and submenu
    echo "        <form name='dbform1123' id='dbform1123' method='post' action='admin.php'>
                    <div class='submenu y2'>
                        <ul>
            ";
    if ($db1_set == "0") {
        echo "              <li class = \"warnadmin\"><a href='db_config.php' title='Create/configure'>&nbsp;&nbsp;Configure&nbsp;&nbsp;</a></li>";
    } else {
        echo "              <li><a href='db_config.php' title='Create/configure'>&nbsp;&nbsp;Configure&nbsp;&nbsp;</a></li>";
    }
    echo "
                            <li><a href='db_activate.php' title='Activate/disable'>&nbsp;&nbsp;Activate / disable&nbsp;&nbsp;</a></li>
                            <li><a href='admin.php?f=database&amp;del=99' title='Backup &amp; Restore'>&nbsp;&nbsp;Backup &amp; Restore&nbsp;&nbsp;</a></li>
                            <li><a href='db_copy.php' title='Copy databases'>&nbsp;&nbsp;Copy / Move&nbsp;&nbsp;</a></li>
                        </ul>
                    </div>
                </form>
            ";

    //      Check if any database is available
    if (!$db1_set) {
        echo "      <center><br />
                    <span class='red'>No database available</p>
                    <br /><br />
                    <span class='red'>Please configure at least one database</p>
                    <br /><br /><br /></center>
                </form>
            </div>
        </body>
    </html>
            ";
        exit;
    }

    echo "<div class='submenu cntr'>| Database Copy and Move utility |</div>
        ";

    $bgcolor='odrow';

    //      define default source database
    if ($db_source != "2" && $db_source != "3" && $db_source != "4" && $db_source != "5") {
        $db_source = "1";
    }

    if ($db_source == $db_dest) {
        echo "  <center><br />
                <span class='red'>&nbsp;Invalid selection!&nbsp;<br /><br />&nbsp;Source (db".$db_source.") = Destination (db".$db_dest.")&nbsp;</p>
                <br /><br /><br />
                </center>
                <p class='evrow cntr'>
                <a class='bkbtn' href='db_copy.php'Go back'>Complete this process</a></p>
            </div>
        </body>
    </html>
                ";
        die ('');
    }

    //      enter here to copy or move db
    if (isset($copy)) {
        $starttime  = time();

        $count = clear_TCache();     //      we will get different results from the new database
        echo "
                <p class='cntr em sml'>Text cache cleared [<span class='warnok'> ".$count." </span>] files deleted.</p>
            ";
        $count = clear_MCache();     //      so we need to delete the old results from the caches
        echo "
                <p class='cntr em sml'>Media cache cleared [<span class='warnok'> ".$count." </span>] files deleted.</p>
                ";

        //      Prepare source database
        if ($db_source == '1') {
            $db_con             = db_connect($mysql_host1, $mysql_user1, $mysql_password1, $database1);
            $database           = $database1;
            $mysql_table_prefix = $mysql_table_prefix1;
        }

        if ($db_source == '2') {
            $db_con             = db_connect($mysql_host2, $mysql_user2, $mysql_password2, $database2);
            $database           = $database2;
            $mysql_table_prefix = $mysql_table_prefix2;
        }

        if ($db_source == '3') {
            $db_con             = db_connect($mysql_host3, $mysql_user3, $mysql_password3, $database3);
            $database           = $database3;
            $mysql_table_prefix = $mysql_table_prefix3;
        }

        if ($db_source == '4') {
            $db_con             = db_connect($mysql_host4, $mysql_user4, $mysql_password4, $database4);
            $database           = $database4;
            $mysql_table_prefix = $mysql_table_prefix4;
        }

        if ($db_source == '5') {
            $db_con             = db_connect($mysql_host5, $mysql_user5, $mysql_password5, $database5);
            $database           = $database5;
            $mysql_table_prefix = $mysql_table_prefix5;
        }

        $prefix_source = $mysql_table_prefix;

        //      delete eventually old intermediate sql files
        if ($dh = @opendir($path)) {
            while (($interm_file = readdir($dh)) !== false) {
                if (preg_match("/.gz/", $interm_file)) {       //  delete only those with valid file-suffix
                    @unlink("$path/$interm_file");
                }
            }
            closedir($dh);
        }

        //      create folder for intermediate sql-files
        if (!is_dir("$path")) mkdir("$path", 0777);
        optimize($clear);//      before copy, preventively repair and optimize current database
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
        
        $header = "-- ------------------------------------------------------------ \n".
            "-- \n".
            "-- Backup from Sphider-plus database ".$db_source."\n".
            "-- Creation date: ".date("d-M-Y H:i",time())."\n".
            "-- MySQL Server version: ".$db_con->get_server_info()."\n".
            "-- Source database: ".$database."\n".
            "-- Source table prefix: ".$mysql_table_prefix."\n".
            "-- \n".
            "-- ------------------------------------------------------------ \n\n" ;
        $sql_query ="SHOW TABLES FROM $database LIKE '$mysql_table_prefix%'";
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

        while($row = $result->fetch_array(MYSQLI_NUM)) {
            $tab    = "$row[0]";             //name of actual table
            $back   = ("$path/$tab.sql.gz"); //create path, filename and suffix for this sql-file
            $fp     = gzopen ("$back","w");

            gzwrite ($fp, $header);           //write header into sql-file
            get_def($database, $tab, $fp);    //get structure of this table
            get_content($database,$tab,$fp);  //get content of this table

            gzwrite ($fp,"--  Valid end of table: '$tab'\n");
            gzclose ($fp);

            if ($copy == '2') {     //      if Move utility, clear source database
                $sql_query ="TRUNCATE ".$tab."";
                $trunc = $db_con->query($sql_query);
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

        //          Prepare destination database
        if ($db_dest == '1') {
            $db_con             = db_connect($mysql_host1, $mysql_user1, $mysql_password1, $database1);
            $database           = $database1;
            $mysql_table_prefix = $mysql_table_prefix1;
        }

        if ($db_dest == '2') {
            $db_con             = db_connect($mysql_host2, $mysql_user2, $mysql_password2, $database2);
            $database           = $database2;
            $mysql_table_prefix = $mysql_table_prefix2;
        }

        if ($db_dest == '3') {
            $db_con             = db_connect($mysql_host3, $mysql_user3, $mysql_password3, $database3);
            $database           = $database3;
            $mysql_table_prefix = $mysql_table_prefix3;
        }

        if ($db_dest == '4') {
            $db_con             = db_connect($mysql_host4, $mysql_user4, $mysql_password4, $database4);
            $database           = $database4;
            $mysql_table_prefix = $mysql_table_prefix4;
        }

        if ($db_dest == '5') {
            $db_con             = db_connect($mysql_host5, $mysql_user5, $mysql_password5, $database5);
            $database           = $database5;
            $mysql_table_prefix = $mysql_table_prefix5;
        }

        if ($dh = opendir($path)) {
            while (($dbfile = readdir($dh)) !== false) {
                if (preg_match("/\.gz$/i", $dbfile)) {
                    $zp = @gzopen("$path/$dbfile", "rb"); //open intermediate-file of one table
                    if(!$zp) {
                        die("Cannot read intermediatefile: ".$dbfile."");
                    }
                    flush();                //clear buffer
                    set_time_limit(1800);   //the rest  should be done in 30 minutes (increase timeout)
                    $temp= '';

                    while(!gzeof($zp)){
                        $temp=$temp.gzgets($zp, '8192');                //  get  one row from current file
                        if ($endoff = strpos($temp, $delimiter)) {      //  find end of sql insert
                            $temp = substr($temp, 0, $endoff);          //delete delimiter
                            $temp = str_replace("\n\n","\n", $temp);    // delete blank rows
                            $sql_query =substr($temp, 0, $endoff);         // this part of tempfile is the current query
                            $sql_query =str_replace($prefix_source, $mysql_table_prefix, $sql_query);
                            $db_con->query($sql_query);                        //insert into table and allow duplicate entries
                            if ($debug && $db_con->errno) {
                                $err_row = $sql_query;
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
                            //mysql_query($sql_query) or die(mysql_error()); //insert into table and protect duplicate entries
                            $temp = '';
                        }
                    }
                    gzclose($zp);
                }
            }

            //      delete all intermediate sql files
            if ($dh = @opendir($path)) {
                while (($interm_file = readdir($dh)) !== false) {
                    if (preg_match("/.gz/", $interm_file)) {       //  delete only those with valid file-suffix
                        @unlink("$path/$interm_file");
                    }
                }
                closedir($dh);
            }

            echo "  <center>
                    ";
            if ($copy == 1) {
                echo "<p class='cntr em sml'>&nbsp;Source (<span class='warnok'>db".$db_source."</span>) copied to Destination (<span class='warnok'>db".$db_dest."</span>)</p>";
            } else {
                echo "<p class='cntr em sml'>&nbsp;Source (<span class='warnok'>db".$db_source."</span>) moved to Destination (<span class='warnok'>db".$db_dest."</span>)</p>";
            }
            echo "<hr></center></hr><br />
                    ";

            $endtime = time();
            $consum = ($endtime-$starttime);
            echo " <p class='odrow cntr sml'>
                        Your<strong>"; if ($copy ==1) {echo" Copy"; } else {echo " Move";} echo "</strong> request was processed in ".$consum." seconds.<br />
                        If you did not receive any errors on the screen,<br />
                        then you should find that your database tables<br />
                    ";
            if ($index_media == '1') {
                echo "and thumbnails been"; if ($copy ==1) {echo" copied"; } else {echo " moved";} echo ".<br /><br /></p>
                        ";
            } else {
                echo "been"; if ($copy ==1) {echo" copied"; } else {echo " moved";} echo ".<br /><br /></p>
                        ";
            }
            echo "
                     <p class='evrow cntr'><a class='bkbtn' href='db_copy.php?db_source=$db_source&amp;db_dest=$db_dest'>Complete this process</a></p>
                </div>
            </div>
        </body>
    </html>";
            die ('');
        } else {
            echo " <p class='odrow cntr'>
                    <span class='warn bd'>Invalid folder for temporary files selected ! <br />
                    '$dir' does not exist.</span></p>
                    ";
        }
    }   //      end of copy and move procedure

    //      show selection table for source and destination db
    if ($copy == '') {
        $copy = '1';
    }

    echo "    <br />

            <form name='db_copy' id='db_copy' action=\"db_copy.php\" method=\"get\">
                <input type='hidden' name='copy' value='1' />
                <table width='60%'>
                    <tr>
                        <td class='headline' colspan='6'>
                        <div class='headline cntr'>Select source database </div>
                        </td>
                    </tr>
                </table>
                <table width='60%'>
                    <tr class='sml cntr'>
                        <td width='8%' class='tblhead'>db 1</td>
                        <td width='8%' class='tblhead'>db 2</td>
                        <td width='8%' class='tblhead'>db 3</td>
                        <td width='8%' class='tblhead'>db 4</td>
                        <td width='8%' class='tblhead'>db 5</td>
                    </tr>
                    <tr class='$bgcolor cntr'>
                        <td>"; if ($db1_set) {echo "<input type=\"radio\" name=\"db_source\" value=\"1\" "; if ($db_source == 1) print "checked='checked'"; echo" />";} echo "</td>
                        <td>"; if ($db2_set) {echo "<input type=\"radio\" name=\"db_source\" value=\"2\" "; if ($db_source == 2) print "checked='checked'"; echo" />";} echo "</td>
                        <td>"; if ($db3_set) {echo "<input type=\"radio\" name=\"db_source\" value=\"3\" "; if ($db_source == 3) print "checked='checked'"; echo" />";} echo "</td>
                        <td>"; if ($db4_set) {echo "<input type=\"radio\" name=\"db_source\" value=\"4\" "; if ($db_source == 4) print "checked='checked'"; echo" />";} echo "</td>
                        <td>"; if ($db5_set) {echo "<input type=\"radio\" name=\"db_source\" value=\"5\" "; if ($db_source == 5) print "checked='checked'"; echo" />";} echo "</td>
                    </tr>
                </table>
                <br />
            ";


    //      define default destination database
    if ($db_dest != "1" && $db_dest != "3" && $db_dest != "4" && $db_dest != "5") {
        $db_dest = "2";
    }

    echo "    <br />
                <table width='60%'>
                    <tr>
                        <td class='headline' colspan='6'>
                        <div class='headline cntr'>Select destination database</div>
                        </td>
                    </tr>
                </table>
                <table width='60%'>
                    <tr class='sml cntr'>
                        <td width='10%' class='tblhead'>db 1</td>
                        <td width='10%' class='tblhead'>db 2</td>
                        <td width='10%' class='tblhead'>db 3</td>
                        <td width='10%' class='tblhead'>db 4</td>
                        <td width='10%' class='tblhead'>db 5</td>
                    </tr>
                    <tr class='$bgcolor cntr'>
                        <td>"; if ($db1_set) {echo "<input type=\"radio\" name=\"db_dest\" value=\"1\" "; if ($db_dest == 1) print "checked='checked'"; echo" />";} echo "</td>
                        <td>"; if ($db2_set) {echo "<input type=\"radio\" name=\"db_dest\" value=\"2\" "; if ($db_dest == 2) print "checked='checked'"; echo" />";} echo "</td>
                        <td>"; if ($db3_set) {echo "<input type=\"radio\" name=\"db_dest\" value=\"3\" "; if ($db_dest == 3) print "checked='checked'"; echo" />";} echo "</td>
                        <td>"; if ($db4_set) {echo "<input type=\"radio\" name=\"db_dest\" value=\"4\" "; if ($db_dest == 4) print "checked='checked'"; echo" />";} echo "</td>
                        <td>"; if ($db5_set) {echo "<input type=\"radio\" name=\"db_dest\" value=\"5\" "; if ($db_dest == 5) print "checked='checked'"; echo" />";} echo "</td>
                    </tr>
                </table>
                <br /><br />

                <table width='60%'>
                    <tr>
                        <td class='headline' colspan='6'>
                        <div class='headline cntr'>Define Copy or Move utility </div>
                        </td>
                    </tr>
                </table>
                <table width='60%'>
                    <tr class='sml cntr'>
                        <td width='8%' class='tblhead'>Copy</td>
                        <td width='8%' class='tblhead'>Move</td>

                    </tr>
                    <tr class='$bgcolor cntr'>
                        <td><input type=\"radio\" name=\"copy\" value=\"1\" "; if ($copy == 1) print "checked='checked'"; echo" /></td>
                        <td><input type=\"radio\" name=\"copy\" value=\"2\" "; if ($copy == 2) print "checked='checked'"; echo" /></td>
                    </tr>
                </table>
                <br /><br /><center>
                <input type=\"submit\" id=\"submit1\" value=\"Start now\"
                    title='Beware! Once started, the destination database will be lost and overwritten !'/>
                </center>
                <br />
            </form>
            </div>
            <br />
            <div class='clear'></div>
            <br />
            </div>
        </body>
    </html>
            ";
    exit();

/*
 //      multi database activation
 echo "<br />
 <div>
 <form name='dbactivate' id='dbactivate' action=\"db_activate.php\" method=\"get\">
 <input class='hide' type='hidden' name='Submit' value='1'>
 <table width='60%'>
 <tr>
 <td class='headline' colspan='6'>
 <div class='headline cntr'>Select all databases to be activated</span> </div>
 </td>
 </tr>
 <tr class='sml cntr'>
 <td width='10%' class='tblhead'>db 1</td>
 <td width='10%' class='tblhead'>db 2</td>
 <td width='10%' class='tblhead'>db 3</td>
 <td width='10%' class='tblhead'>db 4</td>
 <td width='10%' class='tblhead'>db 5</td>
 <td width='20%' class='tblhead'></td>
 </tr>
 <tr class='$bgcolor cntr'>
 <td>"; if ($db1_set != '0') {echo "<input name='_db1_act' type='checkbox' value='1' id='_db1_act'"; if ($db1_act==1) {echo " checked='checked'";}echo ">";} echo "</td>
 <td>"; if ($db2_set != '0') {echo "<input name='_db2_act' type='checkbox' value='1' id='_db2_act'"; if ($db2_act==1) {echo " checked='checked'";}echo ">";} echo "</td>
 <td>"; if ($db3_set != '0') {echo "<input name='_db3_act' type='checkbox' value='1' id='_db3_act'"; if ($db3_act==1) {echo " checked='checked'";}echo ">";} echo "</td>
 <td>"; if ($db4_set != '0') {echo "<input name='_db4_act' type='checkbox' value='1' id='_db4_act'"; if ($db4_act==1) {echo " checked='checked'";}echo ">";} echo "</td>
 <td>"; if ($db5_set != '0') {echo "<input name='_db5_act' type='checkbox' value='1' id='_db5_act'"; if ($db5_act==1) {echo " checked='checked'";}echo ">";} echo "</td>
 <td><input type=\"submit\" value=\"Store selection\"></td>
 </tr>
 </table>
 </form>
 </div>
 <br />
 <div class='clear'></div>
 <br />
 ";
 */

?>