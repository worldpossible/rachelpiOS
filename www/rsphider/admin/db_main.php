<?php
    //error_reporting (E_ALL);    //  use this for script debugging
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE & ~E_STRICT);
    define("_SECURE",1);    // define secure constant

    extract($_POST);
    extract($_REQUEST);

    $backup_path    = "./backup";  //  subfolder of .../admin/ where all the backups will be stored
    $delimiter      = ";#%%";       //  delimiter for sql insert
    $settings_dir   = "../settings";

    include "db_common.php";
    include "$settings_dir/database.php";

    //      Select default database for backup / restore
    if (!$db) {
        $db = $dba_act;
    }

    //      Headline for BACKUP MANAGEMENT
    echo "    <form name='dbform1123' id='dbform1123' method='post' action='admin.php'>
                <div class='submenu y2'>
                    <ul>
            ";
    if ($db1_set == "0") {
        echo "            <li class = \"warnadmin\"><a href='db_config.php' title='Create/configure'>&nbsp;&nbsp;Configure&nbsp;&nbsp;</a></li>";
    } else {
        echo "            <li><a href='db_config.php' title='Create/configure'>&nbsp;&nbsp;Configure&nbsp;&nbsp;</a></li>";
    }

    //  tables in db are up to date?
    if ($latest || $send2) {
    echo "
                        <li><a href='db_activate.php' title='Activate/disable'>&nbsp;&nbsp;Activate / disable&nbsp;&nbsp;</a></li>
                        <li><a href='admin.php?f=database&amp;del=99' title='Backup &amp; Restore'>&nbsp;&nbsp;Backup &amp; Restore&nbsp;&nbsp;</a></li>
                        <li><a href='db_copy.php' title='Copy databases'>&nbsp;&nbsp;Copy / Move&nbsp;&nbsp;</a></li>
                    </ul>
                </div>
            </form>
            ";
    } else {
        echo "
                        <li><a href='url_backup.php?latest=$latest&dba_act=$dba_act&mysql_table_prefix=$mysql_table_prefix'&send2=$send2' title='Copy current URLs'>&nbsp;&nbsp;Export current URL list&nbsp;&nbsp;</a></li>
                        <input class='hide' type='hidden' name='latest' id='latest' value='$latest'
                        <input class='hide' type='hidden' name='dba_act' id='dba_act' value='$dba_act'
                        <input class='hide' type='hidden' name='mysql_table_prefix' id='mysql_table_prefix' value='$mysql_table_prefix'
                    </ul>
                </div>
            </form>
            ";
        exit;
    }

    //      Check if any database is available
    if (!$db1_set || !$db_active) {
        echo "<center><br />
                <span class='red'>No database available</p>
                <br /><br />
                <span class='red'>Please configure at least one database</p>
                <br /><br /><br /></center>
            </body>
        </html>
            ";
        exit;
    }

    //      Check if Admin and User databases is activated
    if (!$dba_act) {
        echo "<center><br />
                <span class='red'>No database is activated for Admin</p>
                <br /><br />
                <span class='red'>Please activate a database</p>
                <br /><br /><br /></center>
            </body>
        </html>
            ";
        exit;
    }

    if (!$dbu_act) {
        echo "<center><br />
                <span class='red'>No database is activated for User</p>
                <br /><br />
                <span class='red'>Please activate a database</p>
                <br /><br /><br /></center>
            </body>
        </html>
            ";
        exit;
    }


    //      Submenu only  ?
    if ($sel == '1') {
        exit;
    }

    //      ZLIB installed on this server  ?
    if (!get_extension_funcs('zlib')) {
        echo "<br />
                <p class='warnadmin cntr'>Compression module status notice:</p>
                <p class='warnadmin cntr'>Zlib is NOT installed on the server! Backup disabled!</p>
                <br /><br />
            </html>
        </body>
                ";
        exit();
    }

    //      If backup folders do not exist, create them
    if (!is_dir($backup_path)) {
        mkdir($backup_path, 0777);
        if (!is_dir($backup_path)) {
            die('Unable to create folder for backup files.');
        }
    }

    $i = '1';
    while ($i < 6) {
        if (!is_dir("".$backup_path."/db".$i."")) {
            mkdir("".$backup_path."/db".$i."", 0777);
            if (!is_dir("".$backup_path."/db".$i."")) {
                die("Unable to create folder for db".$i." backup files.");
            }
        }
        $i++  ;
    }

    //      Select default database for backup / restore
    if (!$db) {
        $db = $dba_act;
    }

    $bgcolor='odrow';
    echo "<br />
            <div>
            <form name='dbselect121' id='dbselect121' action=\"admin.php\" method=\"get\">
                <input type=\"hidden\" name=\"f\" value=\"database\" />
                <table width='60%'>
                    <tr>
                        <td class='headline' colspan='6'>
                        <div class='headline cntr'>Select database for backup / restore </div>
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
                        <td>"; if ($db1_set) {echo "<input type=\"radio\" name=\"db\" value=\"1\""; if ($db == 1) print " checked=\"checked\""; echo" />";} echo "</td>
                        <td>"; if ($db2_set) {echo "<input type=\"radio\" name=\"db\" value=\"2\""; if ($db == 2) print " checked=\"checked\""; echo" />";} echo "</td>
                        <td>"; if ($db3_set) {echo "<input type=\"radio\" name=\"db\" value=\"3\""; if ($db == 3) print " checked=\"checked\""; echo" />";} echo "</td>
                        <td>"; if ($db4_set) {echo "<input type=\"radio\" name=\"db\" value=\"4\""; if ($db == 4) print " checked=\"checked\""; echo" />";} echo "</td>
                        <td>"; if ($db5_set) {echo "<input type=\"radio\" name=\"db\" value=\"5\""; if ($db == 5) print " checked=\"checked\""; echo" />";} echo "</td>
                        <td><input type=\"submit\" value=\"Select\" /></td>
                    </tr>
                </table>
            </form>
            </div>
            <br /><br />
            ";

    //      Prepare database and its backup folder
    $sql_query  ="SELECT DATABASE()";

    if ($db == '1') {
        $db_con = db_connect($mysql_host1, $mysql_user1, $mysql_password1, $database1);
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
        $database           = $database1;
        $mysql_table_prefix = $mysql_table_prefix1;
        $backup_path        = "".$backup_path."/db1";
    }

    if ($db == '2') {
        $db_con = db_connect($mysql_host2, $mysql_user2, $mysql_password2, $database2);
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
        $database           = $database2;
        $mysql_table_prefix = $mysql_table_prefix2;
        $backup_path        = "".$backup_path."/db2";
    }

    if ($db == '3') {
        $db_con =db_connect($mysql_host3, $mysql_user3, $mysql_password3, $database3);
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
        $database           = $database3;
        $mysql_table_prefix = $mysql_table_prefix3;
        $backup_path        = "".$backup_path."/db3";
    }

    if ($db == '4') {
        $db_con = db_connect($mysql_host4, $mysql_user4, $mysql_password4, $database4);
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
        $database           = $database4;
        $mysql_table_prefix = $mysql_table_prefix4;
        $backup_path        = "".$backup_path."/db4";
    }

    if ($db == '5') {
        $db_con = db_connect($mysql_host5, $mysql_user5, $mysql_password5, $database5);
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
        $database           = $database5;
        $mysql_table_prefix = $mysql_table_prefix5;
        $backup_path        = "".$backup_path."/db5";
    }

    //      Here we start to list available backup files
    $bgcolor='odrow';
    $is_first=1;
    $folder = scandir($backup_path);
    echo "<div class='submenu cntr'>Backup &amp; Restore for database ".$db.":&nbsp;'".$database."'</div>
            ";
    if($is_first==1){
        echo "<form name='make_backup' id='make_backup' action=\"admin.php\" method=\"get\">
                <table width='90%'>
                    <tr>
                        <td class='headline cntr sml' width='65%'>Backup Files</td>
                        <td class='headline cntr sml'>Manage</td>
                    </tr>
                ";
    }

    $is_first=0;
    $count_backup = 0;
    foreach ($folder as $backname) {
        if (preg_match("/__/i",$backname)) {    //show only folder with two _ in its name

            $han = opendir("$backup_path/$backname");
            $fcount = '0';
            while (false !== ($backfiles = readdir($han))) {
                $fcount++;
            }
            closedir($han);

            if ($fcount > '2') {        //show all folder that are not empty
                $count_backup++ ;
                echo "    <tr>
                        <td class='$bgcolor sml cntr' >".$backname."</td>
                        <td class='$bgcolor cntr'>
                        <input class='sbmt' type='button' name='lrestore'
                        onclick=\"confirm_rest_prompt('./admin.php?f=database&amp;file=$backname&amp;del=0&amp;db=".$db."');\" value='Restore'
                        title='Beware! Once started, the database restore could take some while to complete!'/>&nbsp;&nbsp;&nbsp;&nbsp;
                        <input class='sbmt' type='button' name='ldelete'
                        onclick=\"confirm_del_prompt('./admin.php?f=database&amp;file=$backname&amp;del=1&amp;db=".$db."');\" value='Delete'
                        title='Click to Permanently Delete database backup'/>
                        </td>
                    </tr>
                        ";

                if ($bgcolor=='odrow') {
                    $bgcolor='evrow';
                } else {
                    $bgcolor='odrow';
                }
            }


        }
    }

    if($count_backup == 0){
        echo "<table width='90%'>
                    <tr>
                        <td class='odrow cntr sml' width='65%'><span class='warnadmin'>No backup file exists!</span></td>
                        <td class='odrow cntr sml'>Create them soon!</td>
                    </tr>
                ";
    }
    echo "
                </table>
                <br />
            ";

    echo "
                <div class='tblhead cntr'>
                    <div class='panel x2 cntr'>
                        <p class='evrow cntr sml'>Create a new Backup file from selected database:
                        <input type='hidden' name='f' value='database' />
                        <input type='hidden' name='db' value='$db' />
                        <input class='sbmt' type='submit' name='send2' value='Backup'
                        title='Beware! Once started, the database backup could take some while to complete!' /></p>
                    </div>
                </div>
            </form>

                ";

    //      Enter here to backup current db and thumbnails
    if($send2 == "Backup") {
        $starttime  = time();

        $folder_name = "db".$db."_".date("Y-m-d__H-i")."_content";

        if (!is_dir($backup_path)) mkdir($backup_path, 0777); //if not exist, create folder for backup

        $path = "".$backup_path."/".$folder_name."";
        if (!is_dir("$path")) mkdir("$path", 0777); //create individual sub-folder for backup-files
        optimize($clear);//      before backup, preventively repair and optimize current database

        $header = "-- ------------------------------------------------------------ \n".
            "-- \n".
            "-- MySQL Server version: ".$db_con->get_server_info()."\n".
            "-- Backup from Sphider-plus database ".$dba_act."\n".
            "-- Creation date: ".date("d-M-Y H:i",time())."\n".
            "-- Name of database: ".$database."\n".
            "-- Table prefix: ".$mysql_table_prefix."\n".
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

            $back   = ("$path/$tab.sql.gz"); //create path, filename and suffix for this backup-file
            $fp     = gzopen ("$back","w");

            gzwrite ($fp,$header);           //write header into backup-file
            get_def($database,$tab,$fp);     //get structure of this table
            get_content($database,$tab,$fp); // get content of this table

            gzwrite ($fp,"--  Valid end of table: '$tab'\n");
            gzclose ($fp);
        }
        $result->free_result();

        $endtime = time();
        $consum = ($endtime-$starttime);
        echo "<body onload='JumpBottom()'>
                <p class='warnok bd cntr'>Backup of current database '".$database."' done in ".$consum."
                ";
        if ($consum == 1) {
            echo " second.</p>";
        } else {
            echo " seconds.</p>";
        }
/*
        if ($structonly == "Yes") {
            echo "<p class='odrow cntr'>
                    Keep in mind that only database structure has been stored in this backup file.</p>
                    ";
        }
*/
        echo "<div>
                <p class='evrow cntr'>
                <a class='bkbtn' href='admin.php?db=".$db."&amp;f=database' title='Go back to Database'>Complete this process</a></p></div>
            </div>
        </body>
    </html>
                ";
        die ('');
    }

    //      Enter here to restore backup files into database and also restore thumbnails
    if (isset($file) && $del==0) {      //first check for too large backup-files
        $starttime = time();

        if (preg_match("/__/i",$file)) {
            $dir = ("$backup_path/$file"); //folder with backup-files to be restored in database

            if ($dh = opendir($dir)) {
                while (($dbfile = readdir($dh)) !== false) {

                    if (preg_match("/\.gz$/i", $dbfile)) {
                        $zp = @gzopen("$dir/$dbfile", "rb"); //open backup-file of one table
                        if(!$zp) {
                            die("Cannot read backup-file: ".$dbfile."");
                        }
                        flush();                //clear buffer
                        set_time_limit(1800);   //the rest of this backup-file should be done in 30 minutes (increase timeout)
                        $temp= '';

                        while(!gzeof($zp)){
                            $temp = $temp.gzgets($zp, '8192');                //  get  one row from current backup-file
                            if ($endoff = strpos($temp, $delimiter)) {      //  find end of sql insert
                                $temp = substr($temp, 0, $endoff);          //delete delimiter
                                $temp = str_replace("\n\n","\n", $temp);    // delete blank rows
                                $sql_query = substr($temp, 0, $endoff);         // this part of tempfile is the current query
                                $db_con->query($sql_query);                     //insert into table
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
                                $temp = '';
                            }
                        }
                        gzclose($zp);
                    }
                }

                $endtime = time();
                $consum = ($endtime-$starttime);
                echo " <p class='odrow cntr'>
                            <body onload='JumpBottom()'>
                            Your restore request was processed in ".$consum." seconds.<br />
                            If you did not receive any errors on the screen,<br />
                            then you should find that your database tables been restored.<br />
                            <p class='evrow cntr'>
                            <a class='bkbtn' href='admin.php?db=".$db."&amp;f=database' title='Go back'>Complete this process</a></p>
                        ";

            } else {
                echo " <p class='odrow cntr'>
                        <body onload='JumpBottom()'>
                        <span class='warn bd'>Invalid folder for Backup-files selected ! <br />
                        '$dir' does not exist.</span></p>
                        ";
            }
        } else {
            echo "<p class='odrow cntr'>
                     <body onload='JumpBottom()'>
                     <span class='warn bd'>Invalid Backup '".$file."' selected.</span></p>
                    ";
        }
        echo"</div>
            </div>
        </body>
    </html>
                ";
        die ('');
    }

    //      Enter here to delete backup files
    if (isset($file) && $del==1) {

        $db_dir = ("$backup_path/$file");                        //db-backup folder to be deleted
        if (is_dir($db_dir)) {
            if ($dh = opendir($db_dir)) {
                while (($dbfile = readdir($dh)) !== false) {
                    @unlink("$db_dir/$dbfile");             //now delete all files in db folder
                    //echo "Deleted file: $dbfile <br />";
                }
                closedir($dh);
            }
        }
        rmdir($db_dir);     //  now delete empty backup folder

        echo "<div class='cntr'>
                 <body onload='JumpBottom()'>
                 <p class='odrow bd cntr sml'><br />Backup File '".$file."' deleted.<br /><br /></p>
                 <p class='evrow cntr'>
                 <a class='bkbtn' href='admin.php?db=".$db."&amp;f=database' title='Go back to Database'>Complete this process</a></p></div>
            </div>
        </body>
    </html>
                ";
        die ('');
    }

    //      List current database tables
    $sql_query ="SHOW TABLE STATUS FROM $database LIKE '$mysql_table_prefix%'";
    $stats  = $db_con->query ($sql_query);
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

    $tables = $stats->field_count;
    if ($tables) {
        echo "
            <br /><br /><br />
            <table width='98%'>
                <tr>
                    <td class='headline' colspan='6'>
                    <div class='headline cntr'>Table Overview of database ".$db.": <span class='odrow'>'".$database."'</span> </div>
                    </td>
                </tr>
            </table>
            <table width='98%'>
                <tr>
                    <td width='20%' class='tblhead'>Table</td>
                    <td width='20%' class='tblhead'>Rows</td>
                    <td width='25%' class='tblhead'>Created</td>
                    <td width='15%' class='tblhead'>Data Size kB</td>
                    <td width='19%' class='tblhead'>Index Size kB</td>
                </tr>
                ";

        $bgcolor='odrow';
        $i = -1;
        while ($rows = $stats->fetch_array(MYSQLI_ASSOC) ) {
            echo "     <tr class='$bgcolor cntr'>
                    <td>".$rows['Name']."</td>
                    <td>".$rows['Rows']."</td>
                    <td>".$rows['Create_time']."</td>
                    <td>".number_format($rows['Data_length']/1024,1)."</td>
                    <td>".number_format($rows['Index_length']/1024,1)."</td>
                </tr>
           ";
            $i++;
            if ($bgcolor=='odrow') {
                $bgcolor='evrow';
            } else {
                $bgcolor='odrow';
            }
        }
        echo " </table>
            <form name='optimize' id='optimize' method='post' action='admin.php'>
                <p class='tblhead'>
                <input type='hidden' name='f' value='database' />
                <input type='hidden' name='db' value='".$db."' />
                <span class='bd'>Repair and optimize current database</span>
                <input class='sbmt' type='submit' name='send2' value='Optimize' title='Attempt to minimize database' />
                </p>
            </form>
           ";

    } else {
        echo "<p class='odrow cntr'>
                <span class='warn bd'>Warning: Database contains no tables</p>
                ";
    }

    //Enter here to repair and optimize database
    if($send2=="Optimize"){
        optimize($clear);
        echo "<p class='odrow cntr'>
                <span class='bd'>Completed!</span> ".$i." tables processed.<br />
                Current database '".$database."' repaired and optimized.</p>
                ";
    }

    echo " <div class='clear'></div>
                <a class='navup' href='admin.php?db=".$db."&amp;f=database&amp;del=99' title='Jump to Page Top'>Top</a>
                <br /><br />
            </div>
        </body>
    </html>
       ";
    die ('');
?>