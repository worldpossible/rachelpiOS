<?php

        define("_SECURE",1);    // define secure constant

        include "admin_header.php";
        include "auth_db.php";
        include "$settings_dir/database.php";

        $password_hd = "********";

        $output = '1';

        //      Header like in global Admin
        $site_funcs     = Array (22=> "default",21=> "default",4=> "default", 19=> "default", 1=> "default", 2 => "default", "add_site" => "default", 20=> "default", 28=> "default", 30=> "default", 40=> "default", 45=> "default", 50=> "default", 51=> "default", "edit_site" => "default", 5=>"default");
        $stat_funcs     = Array ("statistics" => "default",  "delete_log"=> "default");
        $settings_funcs = Array ("settings" => "default", 41=> "default");
        $index_funcs    = Array ("index" => "default");
        $clean_funcs    = Array ("clean" => "default", 15=>"default", 16=>"default", 17=>"default", 23=>"default");
        $cat_funcs      = Array (11=> "default", 10=> "default", "categories" => "default", "edit_cat"=>"default", "delete_cat"=>"default", "add_cat" => "default", 7=> "default");
        $database_funcs = Array ("database" => "default");

        echo "  <div id='tabs'>
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

        echo "    <li><a title='Manage Sites' href='admin.php?f=2' class='$site_funcs[$f]'>Sites</a></li>
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

        if ($_password1_hd != $password_hd) {
            $_mysql_password1 = $_password1_hd;
        }

        if ($_password2_hd != $password_hd) {
            $_mysql_password2 = $_password2_hd;
        }

        if ($_password3_hd != $password_hd) {
            $_mysql_password3 = $_password3_hd;
        }

        if ($_password4_hd != $password_hd) {
            $_mysql_password4 = $_password4_hd;
        }

        if ($_password5_hd != $password_hd) {
            $_mysql_password5 = $_password5_hd;
        }

        //        Enter here for database configuration
        if (!isset($f)) {
            //      Headline and submenu
            echo "<form name='dobackup' id='dbform1' method='post' action='admin.php'>
                <div class='submenu y2'>
                    <ul>
                        <li><a href='db_config.php' title='Create/configure'>&nbsp;&nbsp;Configure&nbsp;&nbsp;</a></li>
                        <li><a href='db_activate.php' title='Activate/disable'>&nbsp;&nbsp;Activate / disable&nbsp;&nbsp;</a></li>
                        <li><a href='admin.php?f=database&amp;del=99' title='Backup &amp; Restore'>&nbsp;&nbsp;Backup &amp; Restore&nbsp;&nbsp;</a></li>
                        <li><a href='db_copy.php' title='Copy databases'>&nbsp;&nbsp;Copy / Move&nbsp;&nbsp;</a></li>
                    </ul>
                </div>
            </form>
        </div>
        <div class='submenu cntr'>| Database Configuration |
        </div>
        ";

            //      db1  transfer
            if ($_database1 == "") {
                $_database1 = $database1;
            }

            if ($_mysql_user1 == "") {
                $_mysql_user1 = $mysql_user1;
            }

            if ($_mysql_password1 == "") {
                $_mysql_password1 = $mysql_password1;
            }

            if ($_mysql_host1  == "") {
                $_mysql_host1 = $mysql_host1;
            }

            if ($_mysql_table_prefix1 == "") {
                $_mysql_table_prefix1 = $mysql_table_prefix1;
            }

            //      db2  transfer
            if ($_database2 == "") {
                $_database2 = $database2;
            }

            if ($_mysql_user2 == "") {
                $_mysql_user2 = $mysql_user2;
            }

            if ($_mysql_password2 == "") {
                $_mysql_password2 = $mysql_password2;
            }

            if ($_mysql_host2  == "") {
                $_mysql_host2 = $mysql_host2;
            }

            if ($_mysql_table_prefix2 == "") {
                $_mysql_table_prefix2 = $mysql_table_prefix2;
            }

            //      db3  transfer
            if ($_database3 == "") {
                $_database3 = $database3;
            }

            if ($_mysql_user3 == "") {
                $_mysql_user3 = $mysql_user3;
            }

            if ($_mysql_password3 == "") {
                $_mysql_password3 = $mysql_password3;
            }

            if ($_mysql_host3  == "") {
                $_mysql_host3 = $mysql_host3;
            }

            if ($_mysql_table_prefix3 == "") {
                $_mysql_table_prefix3 = $mysql_table_prefix3;
            }

            //      db4  transfer
            if ($_database4 == "") {
                $_database4 = $database4;
            }

            if ($_mysql_user4 == "") {
                $_mysql_user4 = $mysql_user4;
            }

            if ($_mysql_password4 == "") {
                $_mysql_password4 = $mysql_password4;
            }

            if ($_mysql_host4  == "") {
                $_mysql_host4 = $mysql_host4;
            }

            if ($_mysql_table_prefix4 == "") {
                $_mysql_table_prefix4 = $mysql_table_prefix4;
            }

            //      db5  transfer
            if ($_database5 == "") {
                $_database5 = $database5;
            }

            if ($_mysql_user5 == "") {
                $_mysql_user5 = $mysql_user5;
            }

            if ($_mysql_password5 == "") {
                $_mysql_password5 = $mysql_password5;
            }

            if ($_mysql_host5  == "") {
                $_mysql_host5 = $mysql_host5;
            }

            if ($_mysql_table_prefix5 == "") {
                $_mysql_table_prefix5 = $mysql_table_prefix5;
            }

            //      check database settings
            if (!isset($Submit)) {
                echo "<fieldset><legend>[ Database Settings Overview ]</legend>
            <br />
            <table width=\"90%\"  border=\"1\">
                <tr>
                    <td width=\"50%\">
                      <blockquote>
                        <p>The following settings have been tested:</p>
                        <hr />
            ";

                $db_count   = '0';
                if (is_object($db_con)) {
                    $db_con->close();
                }
                if ($output == '1') {
                    echo "            <p><span class='cntr'>Database1:</p>
                    ";
                }

                //      check for correct database1 settings
                $db_con1    = new mysqli($mysql_host1, $mysql_user1, $mysql_password1, $database1);
                /* check connection */
                if ($db_con1->connect_errno) {
                    printf("<p><span class='red'>&nbsp;MySQLi Connect failed: %s\n&nbsp;<br /></span></p>", $db_con1->connect_error);
                } else {
                    if ($output == '1' && $database1 && $mysql_user1 && $mysql_password1 && $mysql_host1) {
                        echo "            <p><span class='green cntr'>-&nbsp;&nbsp;Database 1 settings are okay.</p>
                        ";
                    }
                    if ($database1 && $mysql_user1 && $mysql_password1 && $mysql_host1) {
                        //  check for installed tables in db1
                        $sql_query = "SELECT ".$latest_field." FROM ".$mysql_table_prefix1."".$latest_table." LIMIT 10";
                        $result     = $db_con1->query($sql_query);
                        if ($debug == 2 && $db_con->errno) {
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

                        $tables1    = $result->field_count;

                        if (!$tables1) {
                            if($output == '1') {
                                echo "<p><span class='red cntr'>&nbsp;&nbsp;Tables are not installed for database 1<br /></p>
                                <br />
                                ";
                            }
                        } else {
                            $db_count ++;
                            if ($output == '1') {
                                echo "<p><span class='green cntr'>&nbsp;&nbsp;&nbsp;&nbsp;All tables are installed for database 1</p>
                        <br />
                        ";
                            }
                        }
                    }
                }

                if ($db_count == 1) {
                    if ($output == '1') {
                        echo "            <p><span class='cntr'>Database2:</p>
                        ";
                    }
                    //      check for enabled MySQL cache
                    $sql_query      = "SHOW STATUS LIKE 'Qcache_free_memory'";
                    $result     = $db_con1->query($sql_query);
                    if ($debug == 2 && $db_con->errno) {
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

                    $rows       = $result->fetch_array();
                    $cmem_size  = $rows['Value'];

                    /* free all */
                    $result->free();
                    $db_con1->close();
                  
                    //  check db2 settings
                    $db_con2    = new mysqli($mysql_host2, $mysql_user2, $mysql_password2, $database2);                   
                    /* check connection */
                    if ($db_con2->connect_errno) {
                        printf("<p><span class='red'>&nbsp;MySQLi Connect failed: %s\n&nbsp;<br /></span></p>", $db_con2->connect_error);
                        echo "      <p><span class='cntr'>&nbsp;&nbsp;Never mind if you don't need database2.<br /></p>";
                    } else {
                        if($output == '1' && $database2 && $mysql_user2 && $mysql_password2 && $mysql_host2) {
                            echo "<p><span class='green cntr'>-&nbsp;&nbsp;Database 2 settings are okay.</p>
                        ";
                        }
                        if ($database2 && $mysql_user2 && $mysql_password2 && $mysql_host2) {
                            //  check for installed tables
                            $sql_query = "SELECT ".$latest_field." FROM ".$mysql_table_prefix2."".$latest_table." LIMIT 10";
                            $result = $db_con2->query($sql_query);
                            if ($debug == 2 && $db_con->errno) {
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

                            $tables2 = $result->field_count;

                            if (!$tables2) {
                                if($output == '1') {
                                    echo "<p><span class='red cntr'>&nbsp;&nbsp;Tables are not installed for database 2<br /></p>
                                    <br />
                                    ";
                                }
                            } else {
                                $db_count ++;
                                if ($output == '1') {
                                    echo "<p><span class='green cntr'>&nbsp;&nbsp;&nbsp;&nbsp;All tables are installed for database 2</p>
                        <br />
                        ";
                                }
                            }
                            /* free rall*/
                            $db_con2->close();
                        }
                    }
                }

                if ($db_count == 2) {
                    if ($output == '1') {
                        echo "            <p><span class='cntr'>Database3:</p>
                        ";
                    }
                    //check db3 settings
                    $db_con3    = new mysqli($mysql_host3, $mysql_user3, $mysql_password3, $database3);
                    /* check connection */
                    if ($db_con3->connect_errno) {
                        printf("<p><span class='red'>&nbsp;MySQLi Connect failed: %s\n&nbsp;<br /></span></p>", $db_con3->connect_error);
                        echo "<p><span class='cntr'>&nbsp;&nbsp;Never mind if you don't need database3.<br /></p>";
                    } else {
                        if($output == '1' && $database3 && $mysql_user3 && $mysql_password3 && $mysql_host3) {
                            echo "<p><span class='green cntr'>-&nbsp;&nbsp;Database 3 settings are okay.</p>
                        ";
                        }
                        if ($database3 && $mysql_user3 && $mysql_password3 && $mysql_host3) {
                            //  check for installed tables
                            $sql_query = "SELECT ".$latest_field." FROM ".$mysql_table_prefix3."".$latest_table." LIMIT 10";
                            $result = $db_con3->query($sql_query);
                            if ($debug == 2 && $db_con->errno) {
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

                            $tables3 = $result->field_count;

                            if (!$tables3) {
                                if($output == '1') {
                                    echo "  <p><span class='red cntr'>&nbsp;&nbsp;Tables are not installed for database 3<br /></p>
                                    <br />
                                ";
                                }
                            } else {
                                $db_count ++;
                                if ($output == '1') {
                                    echo "<p><span class='green cntr'>&nbsp;&nbsp;&nbsp;&nbsp;All tables are installed for database 3</p>
                        <br />
                        ";
                                }
                            }
                            /* free all*/
                            $db_con3->close();
                        }
                    }
                }

                if ($db_count == 3) {
                    if ($output == '1') {
                        echo "            <p><span class='cntr'>Database4:</p>
                        ";
                    }
                    //check db4 settings
                    $db_con4    = new mysqli($mysql_host4, $mysql_user4, $mysql_password4, $database4);
                    /* check connection */
                    if ($db_con4->connect_errno) {
                        printf("<p><span class='red'>&nbsp;MySQLi Connect failed: %s\n&nbsp;<br /></span></p>", $db_con4->connect_error);
                        echo "<p><span class='cntr'>&nbsp;&nbsp;Never mind if you don't need database4.<br /></p>";
                    } else {
                        if($output == '1' && $database4 && $mysql_user4 && $mysql_password4 && $mysql_host4) {
                            echo "<p><span class='green cntr'>-&nbsp;&nbsp;Database 4 settings are okay.</p>
                        ";
                        }
                        if ($database4 && $mysql_user4 && $mysql_password4 && $mysql_host4) {
                            //  check for installed tables
                            $sql_query = "SELECT ".$latest_field." FROM ".$mysql_table_prefix4."".$latest_table." LIMIT 10";
                            $result = $db_con4->query($sql_query);
                            if ($debug == 2 && $db_con->errno) {
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

                            $tables4 = $result->field_count;

                            if (!$tables4) {
                                if($output == '1') {
                                    echo "<p><span class='red cntr'>&nbsp;&nbsp;Tables are not installed for database 4<br /></p>
                                    <br />
                                    ";
                                }
                            } else {
                                $db_count ++;
                                if ($output == '1') {
                                    echo "<p><span class='green cntr'>&nbsp;&nbsp;&nbsp;&nbsp;All tables are installed for database 4</p>
                        <br />
                        ";
                                }
                            }
                            /* free all*/
                            $db_con4->close();
                        }
                    }
                }

                if ($db_count == 4) {
                    if ($output == '1') {
                        echo "            <p><span class='cntr'>Database5:</p>
                        ";
                    }
                    //check db5 settings
                    $db_con5    = new mysqli($mysql_host5, $mysql_user5, $mysql_password5, $database5);
                    /* check connection */
                    if ($db_con5->connect_errno) {
                        printf("<p><span class='red'>&nbsp;MySQLi Connect failed: %s\n&nbsp;<br /></span></p>", $db_con5->connect_error);
                        echo "<p><span class='cntr'>&nbsp;&nbsp;Never mind if you don't need database5.<br /></p>";
                    } else {
                        if($output == '1' && $database5 && $mysql_user5 && $mysql_password5 && $mysql_host5) {
                            echo "<p><span class='green cntr'>-&nbsp;&nbsp;Database 5 settings are okay.</p>
                        ";
                        }
                        if ($database5 && $mysql_user5 && $mysql_password5 && $mysql_host5) {
                            //  check for installed tables
                            $sql_query = "SELECT ".$latest_field." FROM ".$mysql_table_prefix5."".$latest_table." LIMIT 10";
                            $result = $db_con5->query($sql_query);
                            if ($debug == 2 && $db_con->errno) {
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

                            $tables5 = $result->field_count;

                            if (!$tables5) {
                                if($output == '1') {
                                    echo "<p><span class='red cntr'>&nbsp;&nbsp;Tables are not installed for database 5<br /></p>
                                <br />
                                ";
                                }
                            } else {
                                $db_count ++;
                                if ($output == '1') {
                                    echo "<p><span class='green cntr'>&nbsp;&nbsp;&nbsp;&nbsp;All tables are installed for database 5</p>
                      ";
                                }
                            }
                            /* free rall*/
                            $db_con5->close();
                        }
                    }
                }

                echo "</blockquote>
                    </td>
                    <td><span class='green cntr sml'>&nbsp;&nbsp;MySQL cache:
        ";

                if ($cmem_size == 0) {
                    echo "<span  class='warnadmin'>&nbsp;&nbsp;Cache is not initialized</span></td>";
                } else {
                    echo "              &nbsp;&nbsp;32 MByte initialized</span></td>
                ";
                }
                echo "</tr>
                </table>
                ";
            }

            //          Write as much as possible to database.php file
            if (isset($Submit)) {

                if ($dba_act =="") {
                    $dba_act= '1';
                }

                if ($dbu_act =="") {
                    $dbu_act= '1';
                }
                if ($dbs_act =="") {
                    $dbs_act= '1';
                }

                //      prepare for multi db result fetching
                if ($dbu_act == 1) $_db1_slv = 1 ;
                if ($dbu_act == 2) $_db2_slv = 1 ;
                if ($dbu_act == 3) $_db3_slv = 1 ;
                if ($dbu_act == 4) $_db4_slv = 1 ;
                if ($dbu_act == 5) $_db5_slv = 1 ;

                if ($db1_slv == 1) $_db1_slv = 1 ;
                if ($db2_slv == 1) $_db2_slv = 1 ;
                if ($db3_slv == 1) $_db3_slv = 1 ;
                if ($db4_slv == 1) $_db4_slv = 1 ;
                if ($db5_slv == 1) $_db5_slv = 1 ;

                if ($_db1_slv == "") $_db1_slv = 0;
                if ($_db2_slv == "") $_db2_slv = 0;
                if ($_db3_slv == "") $_db3_slv = 0;
                if ($_db4_slv == "") $_db4_slv = 0;
                if ($_db5_slv == "") $_db5_slv = 0;



                if (!is_writable("../settings/database.php")) {
                    print "Database file is not writable, chmod 777 .../settings/database.php under *nix systems";
                } else {
                    $fhandle=fopen("../settings/database.php","wb");
                    fwrite($fhandle,"<?php \n");

                    fwrite($fhandle,"/************************************************\n ");
                    fwrite($fhandle,"Sphider-plus version $plus_nr Database configuration file.\n");
                    fwrite($fhandle,"\n > > >  DO NOT EDIT THIS FILE. < < < \n\n");
                    fwrite($fhandle,"Any changes must be done by Admin's database settings. \n");
                    fwrite($fhandle,"*************************************************/");

                    fwrite($fhandle,"\n\n\n\n/******************************* Check for forbidden direct access ************************************/\n\n");
                    fwrite($fhandle,"    if (!defined('_SECURE')) die (\"No direct access to database file\");");

                    fwrite($fhandle,"\n\n\n\n/*********************** \nGlobal database settings\n***********************/");
                    fwrite($fhandle, "\n\n// Count of successfully created databases\n");
                    fwrite($fhandle,"$"."db_count = \"".$_db_count."\";");
                    fwrite($fhandle, "\n\n// Currently activated Admin database\n");
                    fwrite($fhandle,"$"."dba_act = \"".$dba_act."\";");
                    fwrite($fhandle, "\n\n// Currently activated Search User database\n");
                    fwrite($fhandle,"$"."dbu_act = \"".$dbu_act."\";");
                    fwrite($fhandle, "\n\n// Currently activated Suggest URL User database\n");
                    fwrite($fhandle,"$"."dbs_act = \"".$dbs_act."\";");
                    fwrite($fhandle, "\n\n// Activated databases that should deliver search results\n");
                    fwrite($fhandle,"$"."db1_slv = \"".$_db1_slv."\";\n");
                    fwrite($fhandle,"$"."db2_slv = \"".$_db2_slv."\";\n");
                    fwrite($fhandle,"$"."db3_slv = \"".$_db3_slv."\";\n");
                    fwrite($fhandle,"$"."db4_slv = \"".$_db4_slv."\";\n");
                    fwrite($fhandle,"$"."db5_slv = \"".$_db5_slv."\";\n");

                    fwrite($fhandle,"\n\n\n/*********************** \nDatabase 1 settings\n***********************/");
                    fwrite($fhandle, "\n\n// Name of database\n");
                    fwrite($fhandle,"$"."database1 = '$_database1';");
                    fwrite($fhandle, "\n\n// MySQL User\n");
                    fwrite($fhandle,"$"."mysql_user1 = '$_mysql_user1';");
                    fwrite($fhandle, "\n\n// MySQL Password\n");
                    fwrite($fhandle,"$"."mysql_password1 = '$_mysql_password1';");
                    fwrite($fhandle, "\n\n// MySQL Host\n");
                    fwrite($fhandle,"$"."mysql_host1 = '$_mysql_host1';");
                    fwrite($fhandle, "\n\n// Prefix for tables\n");
                    fwrite($fhandle,"$"."mysql_table_prefix1 = '$_mysql_table_prefix1';\n\n");

                    fwrite($fhandle,"\n\n\n/*********************** \nDatabase 2 settings\n***********************/");
                    fwrite($fhandle, "\n\n// Name of database\n");
                    fwrite($fhandle,"$"."database2 = '$_database2';");
                    fwrite($fhandle, "\n\n// MySQL User\n");
                    fwrite($fhandle,"$"."mysql_user2 = '$_mysql_user2';");
                    fwrite($fhandle, "\n\n// MySQL Password\n");
                    fwrite($fhandle,"$"."mysql_password2 = '$_mysql_password2';");
                    fwrite($fhandle, "\n\n// MySQL Host\n");
                    fwrite($fhandle,"$"."mysql_host2 = '$_mysql_host2';");
                    fwrite($fhandle, "\n\n// Prefix for tables\n");
                    fwrite($fhandle,"$"."mysql_table_prefix2 = '$_mysql_table_prefix2';");

                    fwrite($fhandle,"\n\n\n/*********************** \nDatabase 3 settings\n***********************/");
                    fwrite($fhandle, "\n\n// Name of database\n");
                    fwrite($fhandle,"$"."database3 = '$_database3';");
                    fwrite($fhandle, "\n\n// MySQL User\n");
                    fwrite($fhandle,"$"."mysql_user3 = '$_mysql_user3';");
                    fwrite($fhandle, "\n\n// MySQL Password\n");
                    fwrite($fhandle,"$"."mysql_password3 = '$_mysql_password3';");
                    fwrite($fhandle, "\n\n// MySQL Host\n");
                    fwrite($fhandle,"$"."mysql_host3 = '$_mysql_host3';");
                    fwrite($fhandle, "\n\n// Prefix for tables\n");
                    fwrite($fhandle,"$"."mysql_table_prefix3 = '$_mysql_table_prefix3';");

                    fwrite($fhandle,"\n\n\n/*********************** \nDatabase 4 settings\n***********************/");
                    fwrite($fhandle, "\n\n// Name of database\n");
                    fwrite($fhandle,"$"."database4 = '$_database4';");
                    fwrite($fhandle, "\n\n// MySQL User\n");
                    fwrite($fhandle,"$"."mysql_user4 = '$_mysql_user4';");
                    fwrite($fhandle, "\n\n// MySQL Password\n");
                    fwrite($fhandle,"$"."mysql_password4 = '$_mysql_password4';");
                    fwrite($fhandle, "\n\n// MySQL Host\n");
                    fwrite($fhandle,"$"."mysql_host4 = '$_mysql_host4';");
                    fwrite($fhandle, "\n\n// Prefix for tables\n");
                    fwrite($fhandle,"$"."mysql_table_prefix4 = '$_mysql_table_prefix4';");

                    fwrite($fhandle,"\n\n\n/*********************** \nDatabase 5 settings\n***********************/");
                    fwrite($fhandle, "\n\n// Name of database\n");
                    fwrite($fhandle,"$"."database5 = '$_database5';");
                    fwrite($fhandle, "\n\n// MySQL User\n");
                    fwrite($fhandle,"$"."mysql_user5 = '$_mysql_user5';");
                    fwrite($fhandle, "\n\n// MySQL Password\n");
                    fwrite($fhandle,"$"."mysql_password5 = '$_mysql_password5';");
                    fwrite($fhandle, "\n\n// MySQL Host\n");
                    fwrite($fhandle,"$"."mysql_host5 = '$_mysql_host5';");
                    fwrite($fhandle, "\n\n// Prefix for tables\n");
                    fwrite($fhandle,"$"."mysql_table_prefix5 = '$_mysql_table_prefix5';");

                    fwrite($fhandle,"\n\n?>");
                    fclose($fhandle);

                    //      check db settings after storing the new db settings
                    $output = '1';
                    check_dbs($output, $mysql_table_prefix1, $mysql_table_prefix2, $mysql_table_prefix3, $mysql_table_prefix4,  $mysql_table_prefix5);

                    //      return to db-settings dialog
                    echo "<br />
                            <a class='bkbtn' href='db_config.php' title='Reload Settings'>Complete this process</a>
                            <p>\n\n</p>
                            </div></div></div>
                            </body>
                            </html>
                        ";
                    die ('');
                }
            }

            //      check db settings
            $output = '0';
            check_dbs($output, $mysql_table_prefix1, $mysql_table_prefix2, $mysql_table_prefix3,$mysql_table_prefix4,  $mysql_table_prefix5);

            echo "<br />
            </fieldset>
            <br />
            <div id='settings'>
                <form class='txt' name='form1' method='post' action='db_config.php'>
                    <input class='hide' type='hidden' name='Submit' value='1'/>
                    <br />
                    <fieldset><legend><a name=\"set_1\">[ Database 1 settings ]</a></legend><br />
                        <br />
                        <label for='database'>Name of database 1:</label>
                        <input name='_database1' type='text' id='database1' size='20' maxlength='255' value='$database1' title='Enter Database name'/>
                        <label for='mysql_user'>Username:</label>
                        <input name='_mysql_user1' type='text' id='mysql_user1' size='20' maxlength='255' value='$mysql_user1' title='Enter User name'/>
                        <label for='mysql_password'>Password:</label>
                        <input name='_password1_hd' type='text' id='password1_hd' size='20' maxlength='255' value='$password_hd' title='Enter MySQL password'/>
                        <label for='mysql_host'>Database host :</label>
                        <input name='_mysql_host1' type='text' id='mysql_host1' size='20' maxlength='255' value='$mysql_host1' title='Enter Host name'/>
                        <label for='mysql_table_prefix'>Prefix for Tables:</label>
                        <input name='_mysql_table_prefix1' type='text' id='mysql_table_prefix1' size='20' maxlength='255' value='$mysql_table_prefix1' title='Enter prefix'/>
                        <p>&nbsp;</p>
                        <label for='submit1'>Save settings for database 1:</label>
                        <input class='hide' type='hidden' name='_db_count' id='_db_count' value='$db_count'/>
                        <input class='sbmt' type='submit' value='Save' id='submit1' title='Click once to save these settings'/>
                        <p>&nbsp;</p>
                    </fieldset>
                </form>
                ";

            if (!$tables1) {
                echo "
            <form class='cntr' name='form1a' method='post' action='install_tables.php?f=0&amp;db_num=1'>
                <fieldset>
                    <label for='submit1a' class='sml'>Install all tables for database 1:</label>
                    <input class='sbmt' type='submit' value='Install' id='submit1a' title='Click once to install all tables'/>
                </fieldset>
            </form>
        ";
            }

            if ($tables1) {
                //      try to allocate db2
                echo "
                <form class='txt' name='form2' method='post' action='db_config.php'>
                    <input class='hide' type='hidden' name='Submit' value='1'/>
                    <br />
                    <fieldset><legend><a name=\"set_2\">[ Database 2 settings ]</a></legend><br />
                        <br />
                        <label for='database'>Name of database 2:</label> <input name='_database2' type='text' id='database2' size='20' maxlength='255' value='$database2' title='Enter Database name'/>
                        <label for='mysql_user'>Username:</label>
                        <input name='_mysql_user2' type='text' id='mysql_user2' size='20' maxlength='255' value='$mysql_user2' title='Enter User name'/>
                        <label for='mysql_password'>Password:</label>
                        <input name='_password2_hd' type='text' id='password2_hd' size='20' maxlength='255' value='$password_hd' title='Enter MySQL password'/>
                        <label for='mysql_host'>Database host :</label>
                        <input name='_mysql_host2' type='text' id='mysql_host2' size='20' maxlength='255' value='$mysql_host2' title='Enter Host name'/>
                        <label for='mysql_table_prefix'>Prefix for Tables:</label>
                        <input name='_mysql_table_prefix2' type='text' id='mysql_table_prefix2' size='20' maxlength='255' value='$mysql_table_prefix2' title='Enter prefix'/>
                        <p>&nbsp;</p>
                        <input class='hide' type='hidden' name='_db_count' id='_db_count' value='$db_count'/>
                        <label for='submit2'>Save settings for database2:</label>
                        <input class='sbmt' type='submit' value='Save' id='submit2' title='Click once to save these settings'/>
                        <p>&nbsp;</p>
                    </fieldset>
                </form>
                ";
                if (!$tables2) {
                    echo "
                <form class='cntr' name='form2a' method='post' action='install_tables.php?f=0&amp;db_num=2'>
                    <fieldset>
                        <label for='submit2a' class='sml'>Install all tables for database 2:</label>
                        <input class='sbmt' type='submit' value='Install' id='submit2a' title='Click once to install all tables'/>
                    </fieldset>
                </form>
            ";
                }
            }

            if ($tables2) {
                //      try to allocate db3
                echo "
                <form class='txt' name='form3' method='post' action='db_config.php'>
                    <input class='hide' type='hidden' name='Submit' value='1' />
                    <br />
                    <fieldset><legend><a name=\"set_3\">[ Database 3 settings ]</a></legend><br />
                        <br />
                        <label for='database'>Name of database 3:</label>
                        <input name='_database3' type='text' id='database3' size='20' maxlength='255' value='$database3' title='Enter Database name'/>
                        <label for='mysql_user'>Username:</label>
                        <input name='_mysql_user3' type='text' id='mysql_user3' size='20' maxlength='255' value='$mysql_user3' title='Enter User name'/>
                        <label for='mysql_password'>Password:</label>
                        <input name='_password3_hd' type='text' id='password3_hd' size='20' maxlength='255' value='$password_hd' title='Enter MySQL password'/>
                        <label for='mysql_host'>Database host :</label>
                        <input name='_mysql_host3' type='text' id='mysql_host3' size='20' maxlength='255' value='$mysql_host3' title='Enter Host name' />
                        <label for='mysql_table_prefix'>Prefix for Tables:</label>
                        <input name='_mysql_table_prefix3' type='text' id='mysql_table_prefix3' size='20' maxlength='255' value='$mysql_table_prefix3'title='Enter prefix'/>
                        <p>&nbsp;</p>
                        <label for='submit3'>Save settings for database3:</label>
                        <input class='hide' type='hidden' name='_db_count' id='_db_count' value='$db_count' />
                        <input class='sbmt' type='submit' value='Save' id='submit3' title='Click once to save these settings' />
                        <p>&nbsp;</p>
                    </fieldset>
                </form>
                ";
                if (!$tables3) {
                    echo "
                <form class='cntr' name='form3a' method='post' action='install_tables.php?f=0&amp;db_num=3'>
                    <fieldset>
                        <label for='submit' class='sml'>Install all tables for database 3:</label>
                        <input class='sbmt' type='submit' value='Install' id='submit3a' title='Click once to install all tables' />
                    </fieldset>
                </form>
            ";
                }
            }

            if ($tables3) {
                //      try to allocate db4
                echo "
                <form class='txt' name='form4' method='post' action='db_config.php'>
                    <input class='hide' type='hidden' name='Submit' value='1'/>
                    <br />
                    <fieldset><legend><a name=\"set_4\">[ Database 4 settings ]</a></legend><br />
                        <br />
                        <label for='database'>Name of database 4:</label>
                        <input name='_database4' type='text' id='database4' size='20' maxlength='255' value='$database4' title='Enter Database name' />
                        <label for='mysql_user'>Username:</label>
                        <input name='_mysql_user4' type='text' id='mysql_user4' size='20' maxlength='255' value='$mysql_user4'title='Enter User name' />
                        <label for='mysql_password'>Password:</label>
                        <input name='_password4_hd' type='text' id='password4_hd' size='20' maxlength='255' value='$password_hd'title='Enter MySQL password' />
                        <label for='mysql_host'>Database host :</label>
                        <input name='_mysql_host4' type='text' id='mysql_host4' size='20' maxlength='255' value='$mysql_host4' title='Enter Host name' />
                        <label for='mysql_table_prefix'>Prefix for Tables:</label>
                        <input name='_mysql_table_prefix4' type='text' id='mysql_table_prefix4' size='20' maxlength='255' value='$mysql_table_prefix4' title='Enter prefix'/>
                        <p>&nbsp;</p>
                        <label for='submit4'>Save settings for database4:</label>
                        <input class='hide' type='hidden' name='_db_count' id='_db_count' value='$db_count' />
                        <input class='sbmt' type='submit' value='Save' id='submit4' title='Click once to save these settings'/>
                        <p>&nbsp;</p>
                    </fieldset>
                </form>
                ";
                if (!$tables4) {
                    echo "
                <form class='cntr' name='form4a' method='post' action='install_tables.php?f=0&amp;db_num=4'>
                    <fieldset>
                        <label for='submit4a' class='sml'>Install all tables for database 4:</label>
                        <input class='sbmt' type='submit' value='Install' id='submit4a' title='Click once to install all tables'/>
                    </fieldset>
                </form>
            ";
                }
            }


            if ($tables4) {
                //      try to allocate db5
                echo "
                <form class='txt' name='form5' method='post' action='db_config.php'>
                    <input class='hide' type='hidden' name='Submit' value='1'/>
                    <br />
                    <fieldset><legend><a name=\"set_5\">[ Database 5 settings ]</a></legend><br />
                        <br />
                        <label for='database'>Name of database 5:</label>
                        <input name='_database5' type='text' id='database5' size='20' maxlength='255' value='$database5' title='Enter Database name' />
                        <label for='mysql_user'>Username:</label>
                        <input name='_mysql_user5' type='text' id='mysql_user5' size='20' maxlength='255' value='$mysql_user5' title='Enter User name' />
                        <label for='mysql_password'>Password:</label>
                        <input name='_password5_hd' type='text' id='password5_hd' size='20' maxlength='255' value='$password_hd' title='Enter MySQL password' />
                        <label for='mysql_host'>Database host :</label>
                        <input name='_mysql_host5' type='text' id='mysql_host5' size='20' maxlength='255' value='$mysql_host5' title='Enter Host name' />
                        <label for='mysql_table_prefix'>Prefix for Tables:</label>
                        <input name='_mysql_table_prefix5' type='text' id='mysql_table_prefix5' size='20' maxlength='255' value='$mysql_table_prefix5' title='Enter prefix' />
                        <p>&nbsp;</p>
                        <label for='submit5'>Save settings for database5:</label>
                        <input class='hide' type='hidden' name='_db_count' id='_db_count' value='$db_count'/>
                        <input class='sbmt' type='submit' value='Save' id='submit5' title='Click once to save these settings' />
                        <p>&nbsp;</p>
                    </fieldset>
                </form>
                ";
                if (!$tables5) {
                    echo "
                <form class='cntr' name='form5a' method='post' action='install_tables.php?f=0&amp;db_num=5'>
                    <fieldset>
                        <label for='submit5a' class='sml'>Install all tables for database 5:</label>
                        <input class='sbmt' type='submit' value='Install' id='submit5a' title='Click once to install all tables'/>
                    </fieldset>
                </form>
            ";
                }
            }

        }

        echo "
            </div>
            <div class='clear'>
                <a class='navup' href='db_config.php' title='Jump to Page Top'>Top</a>
            </div>
            <br />
            <br />
        </body>
    </html>
                ";


    function check_dbs($output, $mysql_table_prefix1, $mysql_table_prefix2, $mysql_table_prefix3, $mysql_table_prefix4, $mysql_table_prefix5) {
        global $debug, $db_con, $plus_nr, $db1_slv, $db2_slv, $db3_slv, $db4_slv, $db5_slv, $latest_field, $latest_table ;

        include "../settings/database.php";
        if($output == '1') {
            echo "
            <fieldset><legend>[ Database Settings Overview ]</legend>
                <br />
                <p class='txt blue cntr'>New database settings have been saved!</p>
                <p>\n\n</p>
                <table width=\"90%\"  border=\"1\">
                    <tr>
                        <td width=\"50%\">
                            <blockquote>
                            <p>The following settings have been tested:</p>
            ";
        }

        //      prepare for multi db result fetching
        if ($dbu_act == 1) $_db1_slv = 1 ;
        if ($dbu_act == 2) $_db2_slv = 1 ;
        if ($dbu_act == 3) $_db3_slv = 1 ;
        if ($dbu_act == 4) $_db4_slv = 1 ;
        if ($dbu_act == 5) $_db5_slv = 1 ;

        if ($db1_slv == 1) $_db1_slv = 1 ;
        if ($db2_slv == 1) $_db2_slv = 1 ;
        if ($db3_slv == 1) $_db3_slv = 1 ;
        if ($db4_slv == 1) $_db4_slv = 1 ;
        if ($db5_slv == 1) $_db5_slv = 1 ;

        if ($_db1_slv == "") $_db1_slv = 0;
        if ($_db2_slv == "") $_db2_slv = 0;
        if ($_db3_slv == "") $_db3_slv = 0;
        if ($_db4_slv == "") $_db4_slv = 0;
        if ($_db5_slv == "") $_db5_slv = 0;

        //      in order to get fresh status values, reset all db's
        $db1_set    = '0';
        $db2_set    = '0';
        $db3_set    = '0';
        $db4_set    = '0';
        $db5_set    = '0';
        $db_count   = '0';

        //      check for correct database1 settings
        $tables     = '';
        $db_con1    = @new mysqli($mysql_host1, $mysql_user1, $mysql_password1, $database1);
        /* check connection */
        if ($db_con1->connect_errno) {
            if ($output) {
                printf("<p><span class='red'>&nbsp;MySQLi Connect failed: %s\n&nbsp;<br /></span></p>", $db_con1->connect_error);
            }
        } else {
            if($output == '1') {
                echo "<p><span class='green cntr'>-&nbsp;&nbsp;Database 1 settings are okay.</p>
                ";
            }
            //  check for installed tables
            $sql_query = "SELECT ".$latest_field." FROM ".$mysql_table_prefix1."".$latest_table." LIMIT 10";
            $result = $db_con1->query($sql_query);
            if ($debug == 2 && $db_con->errno) {
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

            if (!$tables) {
                if($output == '1') {
                    echo "<p><span class='red cntr'><br />&nbsp;&nbsp;Tables are not installed for database 1<br /></p>
                    <br />
                    ";
                }
            } else {
                $db1_set = 1;
                $db_count ++;

                if ($output == '1') {
                    echo "<p><span class='green cntr'>&nbsp;&nbsp;&nbsp;&nbsp;All tables are installed for database 1</p>
                    <br />";
                }
            }
        }
        $db_con1->close();

        //      check for correct database2 settings
        if ($database2 && $mysql_user2 && $mysql_password2 && $mysql_host2) {
            $tables     = '';
            $db_con2    = @new mysqli($mysql_host2, $mysql_user2, $mysql_password2, $database2);
            /* check connection */
            if ($db_con2->connect_errno) {
                if ($output) {
                    printf("<p><span class='red'>&nbsp;MySQLi Connect failed: %s\n&nbsp;<br /></span></p>", $db_con2->connect_error);
                }
                //return;
            } else {
                if($output == '1') {
                    echo "<p><span class='green cntr'>-&nbsp;&nbsp;Database 2 settings are okay.</p>
                    ";
                }
                //  check for installed tables
                $sql_query = "SELECT ".$latest_field." FROM ".$mysql_table_prefix2."".$latest_table." LIMIT 10";
                $result = $db_con2->query($sql_query);
                if ($debug == 2 && $db_con->errno) {
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
                if (!$tables) {
                    if($output == '1') {
                        echo "<p><span class='red cntr'><br />&nbsp;&nbsp;Tables are not installed for database 2<br /></p>
                        <br />
                        ";
                    }
                } else {
                    $db2_set = 1;
                    $db_count ++;

                    if ($output == '1') {
                        echo "<p><span class='green cntr'>&nbsp;&nbsp;&nbsp;&nbsp;All tables are installed for database 2</p>
                        <br />";
                    }
                }

            }
            $db_con2->close();
        }

        //      check for correct database3 settings
        if ($database3 && $mysql_user3 && $mysql_password3 && $mysql_host3) {
            $tables     = '';
            $db_con3    = @new mysqli($mysql_host3, $mysql_user3, $mysql_password3, $database3);
            /* check connection */
            if ($db_con3->connect_errno) {
                if ($output) {
                    printf("<p><span class='red'>&nbsp;MySQLi Connect failed: %s\n&nbsp;<br /></span></p>", $db_con3->connect_error);
                }
            } else {
                if($output == '1') {
                    echo "<p><span class='green cntr'>-&nbsp;&nbsp;Database 3 settings are okay.</p>
                    ";
                }
                //  check for installed tables
                $sql_query = "SELECT ".$latest_field." FROM ".$mysql_table_prefix3."".$latest_table." LIMIT 10";
                $result = $db_con3->query($sql_query);
                if ($debug == 2 && $db_con->errno) {
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

                if (!$tables) {
                    if($output == '1') {
                        echo "<p><span class='red cntr'><br />&nbsp;&nbsp;Tables are not installed for database 3<br /></p>
                        <br />
                        ";
                    }
                } else {
                    $db3_set = 1;
                    $db_count ++;

                    if ($output == '1') {
                        echo "<p><span class='green cntr'>&nbsp;&nbsp;&nbsp;&nbsp;All tables are installed for database 3</p>
                        <br />";
                    }
                }

            }
            $db_con3->close();
        }

        //      check for correct database4 settings
        if ($database4 && $mysql_user4 && $mysql_password4 && $mysql_host4) {
            $tables     = '';
            $db_con4    = @new mysqli($mysql_host4, $mysql_user4, $mysql_password4, $database4);
            /* check connection */
            if ($db_con4->connect_errno) {
                if ($output) {
                    printf("<p><span class='red'>&nbsp;MySQLi Connect failed: %s\n&nbsp;<br /></span></p>", $db_con4->connect_error);
                }
            } else {
                if($output == '1') {
                    echo "<p><span class='green cntr'>-&nbsp;&nbsp;Database 4 settings are okay.</p>
                    ";
                }
                //  check for installed tables
                $sql_query = "SELECT ".$latest_field." FROM ".$mysql_table_prefix4."".$latest_table." LIMIT 10";
                $result = $db_con4->query($sql_query);
                if ($debug == 2 && $db_con->errno) {
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

                if (!$tables) {
                    if($output == '1') {
                        echo "<p><span class='red cntr'><br />&nbsp;&nbsp;Tables are not installed for database 4<br /></p>
                        <br />
                        ";
                    }
                } else {
                    $db4_set = 1;
                    $db_count ++;

                    if ($output == '1') {
                        echo "<p><span class='green cntr'>&nbsp;&nbsp;&nbsp;&nbsp;All tables are installed for database 4</p>
                        <br />";
                    }
                }
            }
            $db_con4->close();
        }

        //      check for correct database5 settings
        if ($database5 && $mysql_user5 && $mysql_password5 && $mysql_host5) {
            $tables     = '';
            $db_con5    = @new mysqli($mysql_host5, $mysql_user5, $mysql_password5, $database5);
            /* check connection */
            if ($db_con5->connect_errno) {
                if ($output) {
                    printf("<p><span class='red'>&nbsp;MySQLi Connect failed: %s\n&nbsp;<br /></span></p>", $db_con5->connect_error);
                }
            } else {
                if($output == '1') {
                    echo "<p><span class='green cntr'>-&nbsp;&nbsp;Database 5 settings are okay.</p>
                    ";
                }
                //  check for installed tables
                $sql_query = "SELECT ".$latest_field." FROM ".$mysql_table_prefix5."".$latest_table." LIMIT 10";
                $result = $db_con5->query($sql_query);
                if ($debug == 2 && $db_con->errno) {
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

                if (!$tables) {
                    if($output == '1') {
                        echo "<p><span class='red cntr'><br />&nbsp;&nbsp;Tables are not installed for database 5<br /></p>
                        <br />
                        ";
                    }
                } else {
                    $db5_set = 1;
                    $db_count ++;

                    if ($output == '1') {
                        echo "<p><span class='green cntr'>&nbsp;&nbsp;&nbsp;&nbsp;All tables are installed for database 5</p>
                        ";
                    }
                }
            }
            $db_con5->close();
        }

        //  define the defaults
        if ($dba_act =="") {
            $dba_act= '1';
        }

        if ($dbu_act =="") {
            $dbu_act= '1';
        }
        if ($dbs_act =="") {
            $dbs_act= '1';
        }

        //      write all to database.php
        if (!is_writable("../settings/database.php")) {
            print "Database file is not writable, chmod 666 .../admin/db_config.php under *nix systems";
        } else {
            $fhandle=fopen("../settings/database.php","wb");
            fwrite($fhandle,"<?php \n");

            fwrite($fhandle,"/************************************************\n ");
            fwrite($fhandle,"Sphider-plus version $plus_nr Database configuration file.\n");
            fwrite($fhandle,"\n > > >  DO NOT EDIT THIS FILE. < < < \n\n");
            fwrite($fhandle,"Any changes must be done by Admin's database settings. \n");
            fwrite($fhandle,"*************************************************/");

            fwrite($fhandle,"\n\n\n\n/******************************* Check for forbidden direct access ************************************/\n\n");
            fwrite($fhandle,"    if (!defined('_SECURE')) die (\"No direct access to database file\");");

            fwrite($fhandle, "\n\n\n\n// Count of successfully created databases\n");
            fwrite($fhandle,"$"."db_count = \"".$db_count."\";");
            fwrite($fhandle, "\n\n// Currently activated Admin database\n");
            fwrite($fhandle,"$"."dba_act = \"".$dba_act."\";");
            fwrite($fhandle, "\n\n// Currently activated Search User database\n");
            fwrite($fhandle,"$"."dbu_act = \"".$dbu_act."\";");
            fwrite($fhandle, "\n\n// Currently activated Suggest URL User database\n");
            fwrite($fhandle,"$"."dbs_act = \"".$dbs_act."\";");
            fwrite($fhandle, "\n\n// Activated databases that should deliver search results\n");
            fwrite($fhandle,"$"."db1_slv = \"".$_db1_slv."\";\n");
            fwrite($fhandle,"$"."db2_slv = \"".$_db2_slv."\";\n");
            fwrite($fhandle,"$"."db3_slv = \"".$_db3_slv."\";\n");
            fwrite($fhandle,"$"."db4_slv = \"".$_db4_slv."\";\n");
            fwrite($fhandle,"$"."db5_slv = \"".$_db5_slv."\";\n");

            fwrite($fhandle,"\n\n\n/*********************** \nDatabase 1 settings\n***********************/");
            fwrite($fhandle, "\n\n// Name of database\n");
            fwrite($fhandle,"$"."database1 = '$database1';");
            fwrite($fhandle, "\n\n// MySQL User\n");
            fwrite($fhandle,"$"."mysql_user1 = '$mysql_user1';");
            fwrite($fhandle, "\n\n// MySQL Password\n");
            fwrite($fhandle,"$"."mysql_password1 = '$mysql_password1';");
            fwrite($fhandle, "\n\n// MySQL Host\n");
            fwrite($fhandle,"$"."mysql_host1 = '$mysql_host1';");
            fwrite($fhandle, "\n\n// Prefix for tables\n");
            fwrite($fhandle,"$"."mysql_table_prefix1 = '$mysql_table_prefix1';");
            fwrite($fhandle, "\n\n// Status of database\n");
            fwrite($fhandle,"$"."db1_set = '$db1_set';");
            fwrite($fhandle, "\n\n// Activation status\n");
            fwrite($fhandle,"$"."db1_act = '$db1_act';");

            fwrite($fhandle,"\n\n\n/*********************** \nDatabase 2 settings\n***********************/");
            fwrite($fhandle, "\n\n// Name of database\n");
            fwrite($fhandle,"$"."database2 = '$database2';");
            fwrite($fhandle, "\n\n// MySQL User\n");
            fwrite($fhandle,"$"."mysql_user2 = '$mysql_user2';");
            fwrite($fhandle, "\n\n// MySQL Password\n");
            fwrite($fhandle,"$"."mysql_password2 = '$mysql_password2';");
            fwrite($fhandle, "\n\n// MySQL Host\n");
            fwrite($fhandle,"$"."mysql_host2 = '$mysql_host2';");
            fwrite($fhandle, "\n\n// Prefix for tables\n");
            fwrite($fhandle,"$"."mysql_table_prefix2 = '$mysql_table_prefix2';");
            fwrite($fhandle, "\n\n// Status of database\n");
            fwrite($fhandle,"$"."db2_set = '$db2_set';");
            fwrite($fhandle, "\n\n// Activation status\n");
            fwrite($fhandle,"$"."db2_act = '$db2_act';");

            fwrite($fhandle,"\n\n\n/*********************** \nDatabase 3 settings\n***********************/");
            fwrite($fhandle, "\n\n// Name of database\n");
            fwrite($fhandle,"$"."database3 = '$database3';");
            fwrite($fhandle, "\n\n// MySQL User\n");
            fwrite($fhandle,"$"."mysql_user3 = '$mysql_user3';");
            fwrite($fhandle, "\n\n// MySQL Password\n");
            fwrite($fhandle,"$"."mysql_password3 = '$mysql_password3';");
            fwrite($fhandle, "\n\n// MySQL Host\n");
            fwrite($fhandle,"$"."mysql_host3 = '$mysql_host3';");
            fwrite($fhandle, "\n\n// Prefix for tables\n");
            fwrite($fhandle,"$"."mysql_table_prefix3 = '$mysql_table_prefix3';");
            fwrite($fhandle, "\n\n// Status of database\n");
            fwrite($fhandle,"$"."db3_set = '$db3_set';");
            fwrite($fhandle, "\n\n// Activation status\n");
            fwrite($fhandle,"$"."db3_act = '$db3_act';");

            fwrite($fhandle,"\n\n\n/*********************** \nDatabase 4 settings\n***********************/");
            fwrite($fhandle, "\n\n// Name of database\n");
            fwrite($fhandle,"$"."database4 = '$database4';");
            fwrite($fhandle, "\n\n// MySQL User\n");
            fwrite($fhandle,"$"."mysql_user4 = '$mysql_user4';");
            fwrite($fhandle, "\n\n// MySQL Password\n");
            fwrite($fhandle,"$"."mysql_password4 = '$mysql_password4';");
            fwrite($fhandle, "\n\n// MySQL Host\n");
            fwrite($fhandle,"$"."mysql_host4 = '$mysql_host4';");
            fwrite($fhandle, "\n\n// Prefix for tables\n");
            fwrite($fhandle,"$"."mysql_table_prefix4 = '$mysql_table_prefix4';");
            fwrite($fhandle, "\n\n// Status of database\n");
            fwrite($fhandle,"$"."db4_set = '$db4_set';");
            fwrite($fhandle, "\n\n// Activation status\n");
            fwrite($fhandle,"$"."db4_act = '$db4_act';");

            fwrite($fhandle,"\n\n\n/*********************** \nDatabase 5 settings\n***********************/");
            fwrite($fhandle, "\n\n// Name of database\n");
            fwrite($fhandle,"$"."database5 = '$database5';");
            fwrite($fhandle, "\n\n// MySQL User\n");
            fwrite($fhandle,"$"."mysql_user5 = '$mysql_user5';");
            fwrite($fhandle, "\n\n// MySQL Password\n");
            fwrite($fhandle,"$"."mysql_password5 = '$mysql_password5';");
            fwrite($fhandle, "\n\n// MySQL Host\n");
            fwrite($fhandle,"$"."mysql_host5 = '$mysql_host5';");
            fwrite($fhandle, "\n\n// Prefix for tables\n");
            fwrite($fhandle,"$"."mysql_table_prefix5 = '$mysql_table_prefix5';");
            fwrite($fhandle, "\n\n// Status of database\n");
            fwrite($fhandle,"$"."db5_set = '$db5_set';");
            fwrite($fhandle, "\n\n// Activation status\n");
            fwrite($fhandle,"$"."db5_act = '$db5_act';");

            fwrite($fhandle,"\n\n?>");
            fclose($fhandle);
        }

        if($output == '1') {
            echo "
                    </blockquote>
                    </td>
                </tr>
                </table>
            ";
        }
    }

?>
