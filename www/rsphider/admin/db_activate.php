<?php

    define("_SECURE",1);    // define secure constant

    include "admin_header.php";
    include "$settings_dir/database.php";
    include "db_common.php";

    $db_old = $dbu_act;

    //      Header like in global Admin
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

    echo "            <li><a title='Manage Sites' href='admin.php?f=2' class='$site_funcs[$f]'>Sites</a></li>
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

    //      deselect not yet activated databases
    if ($_db1_slv == "") {
        $_db1_slv = 0;
    }
    if ($_db2_slv == "") {
        $_db2_slv = 0;
    }
    if ($_db3_slv == "") {
        $_db3_slv = 0;
    }
    if ($_db4_slv == "") {
        $_db4_slv = 0;
    }
    if ($_db5_slv == "") {
        $_db5_slv = 0;
    }

    //      overwrite the default prefix by Admin selection
    if ($prefix1) {
        $mysql_table_prefix1 = $prefix1;
    }
    if ($prefix2) {
        $mysql_table_prefix2 = $prefix2;
    }
    if ($prefix3) {
        $mysql_table_prefix3 = $prefix3;
    }
    if ($prefix4) {
        $mysql_table_prefix4 = $prefix4;
    }
    if ($prefix5) {
        $mysql_table_prefix5 = $prefix5;
    }

    //       activate the corresponding database slave
    if ($dbu_act == 1) $_db1_slv = 1 ;      //  activate the corresponding database slave
    if ($dbu_act == 2) $_db2_slv = 1 ;
    if ($dbu_act == 3) $_db3_slv = 1 ;
    if ($dbu_act == 4) $_db4_slv = 1 ;
    if ($dbu_act == 5) $_db5_slv = 1 ;

    //      Headline and submenu
    echo "        <form name='dbform1123' id='dbform1123' method='post' action='admin.php'>
                    <div class='submenu y2'>
                        <ul>
            ";
    if ($db1_set == "0") {
        echo "                <li class = \"warnadmin\"><a href='db_config.php' title='Create/configure'>&nbsp;&nbsp;Configure&nbsp;&nbsp;</a></li>";
    } else {
        echo "                <li><a href='db_config.php' title='Create/configure'>&nbsp;&nbsp;Configure&nbsp;&nbsp;</a></li>";
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
        echo "
                <center><br />
                <span class='red'>No database available</p>
                <br /><br />
                <span class='red'>Please configure at least one database</p>
                <br /><br /><br /></center>
                </form>
            ";
        exit;
    }

    echo "    <div class='submenu cntr'>| Database Activation |</div>
        ";


    if($Submit == '1') {
        //      clean text and media cache
        clear_TCache('0');
        clear_MCache('0');
        //      write all to database.php
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

        fwrite($fhandle,"\n\n?>");
        fclose($fhandle);

        echo "
                <br />
                <p class='cntr em sml'>Selection has been saved.</p>
                <p class='cntr em sml'>Text cache and media cache cleared.</p>
                <br />
                <a class='bkbtn' href='db_activate.php' title='Activation saved'>Complete this process</a>
                <br /><br />
            </div>
        </body>
    </html>
            ";
        die ('');

    }

    include "$settings_dir/database.php";

    //      just to be sure that everything is well configured
    $db1_set    = '';
    $db2_set    = '';
    $db3_set    = '';
    $db4_set    = '';
    $db5_set    = '';
    $tables     = '';
    $sql_query0 = "SELECT DATABASE()";

    $db_con = db_connect($mysql_host1, $mysql_user1, $mysql_password1, $database1);
    if ($result = $db_con->query($sql_query0)) {
        $sql_query = "SELECT * from ".$mysql_table_prefix1."addurl";
        $result    = $db_con->query($sql_query);
        $tables = $result->field_count;
    }
    if ($tables) $db1_set = '1';
    $tables  = '';

    if (strlen($database2) > 2) {   
        $db_con = db_connect($mysql_host2, $mysql_user2, $mysql_password2, $database2);
        if ($result = $db_con->query($sql_query0)) {
            $sql_query = "SELECT * from ".$mysql_table_prefix2."addurl";
            $result    = $db_con->query($sql_query);
            $tables = $result->field_count;
        }
        if ($tables) $db2_set = '1';
        $tables  = '';
    }

    if (strlen($database3) > 2) {
        $db_con = db_connect($mysql_host3, $mysql_user3, $mysql_password3, $database3);
        if ($result = $db_con->query($sql_query0)) {
            $sql_query = "SELECT * from ".$mysql_table_prefix3."addurl";
            $result    = $db_con->query($sql_query);
            $tables = $result->field_count;
        }
        if ($tables) $db3_set = '1';
        $tables  = '';
    }

    if (strlen($database4) > 2) {
        $db_con = db_connect($mysql_host4, $mysql_user4, $mysql_password4, $database4);
        if ($result = $db_con->query($sql_query0)) {
            $sql_query = "SELECT * from ".$mysql_table_prefix4."addurl";
            $result    = $db_con->query($sql_query);
            $tables = $result->field_count;
        }
        if ($tables) $db4_set = '1';
        $tables  = '';
    }

    if (strlen($database5) > 2) {
        $db_con = db_connect($mysql_host5, $mysql_user5, $mysql_password5, $database5);
        if ($result = $db_con->query($sql_query0)) {
            $sql_query = "SELECT * from ".$mysql_table_prefix5."addurl";
            $result    = $db_con->query($sql_query);
            $tables = $result->field_count;
        }
        if ($tables) $db5_set = '1';
        $tables  = '';
    }

    //      Database activation for Admin, define default database
    if ($dba_act != "2" && $dba_act != "3" && $dba_act != "4" && $dba_act != "5") {
        $dba_act = "1";
    }

    $bgcolor='odrow';
    echo "        <br />
                <div>
                    <form name='db_activate' id='db_activate' action=\"db_activate.php\" method=\"get\">
                        <input class='hide' type='hidden' name='Submit' value='1' />
                        <table width='60%'>
                            <tr>
                                <td class='headline' colspan='6'>
                                <div class='headline cntr'>Select the database used for 'Admin'</div>
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
                                <td>"; if ($db1_set) {echo "<input type=\"radio\" name=\"dba_act\" value=\"1\" "; if ($dba_act == 1) print "checked=\"checked\""; echo" />";} echo "</td>
                                <td>"; if ($db2_set) {echo "<input type=\"radio\" name=\"dba_act\" value=\"2\" "; if ($dba_act == 2) print "checked=\"checked\""; echo" />";} echo "</td>
                                <td>"; if ($db3_set) {echo "<input type=\"radio\" name=\"dba_act\" value=\"3\" "; if ($dba_act == 3) print "checked=\"checked\""; echo" />";} echo "</td>
                                <td>"; if ($db4_set) {echo "<input type=\"radio\" name=\"dba_act\" value=\"4\" "; if ($dba_act == 4) print "checked=\"checked\""; echo" />";} echo "</td>
                                <td>"; if ($db5_set) {echo "<input type=\"radio\" name=\"dba_act\" value=\"5\" "; if ($dba_act == 5) print "checked=\"checked\""; echo" />";} echo "</td>
                            </tr>
                        </table>
                        <br />
            ";

    //      Database activation for Search User, define default database
    if ($dbu_act != "2" && $dbu_act != "3" && $dbu_act != "4" && $dbu_act != "5") {
        $dbu_act = "1";
    }

    echo "            <br />
                        <table width='60%'>
                            <tr>
                                <td class='headline' colspan='6'>
                                <div class='headline cntr'>Select the database used for 'Search' User</div>
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
                                <td>"; if ($db1_set) {echo "<input type=\"radio\" name=\"dbu_act\" value=\"1\" "; if ($dbu_act == 1) print "checked=\"checked\""; echo" />";} echo "</td>
                                <td>"; if ($db2_set) {echo "<input type=\"radio\" name=\"dbu_act\" value=\"2\" "; if ($dbu_act == 2) print "checked=\"checked\""; echo" />";} echo "</td>
                                <td>"; if ($db3_set) {echo "<input type=\"radio\" name=\"dbu_act\" value=\"3\" "; if ($dbu_act == 3) print "checked=\"checked\""; echo" />";} echo "</td>
                                <td>"; if ($db4_set) {echo "<input type=\"radio\" name=\"dbu_act\" value=\"4\" "; if ($dbu_act == 4) print "checked=\"checked\""; echo" />";} echo "</td>
                                <td>"; if ($db5_set) {echo "<input type=\"radio\" name=\"dbu_act\" value=\"5\" "; if ($dbu_act == 5) print "checked=\"checked\""; echo" />";} echo "</td>
                            </tr>
                        </table>
                        <br />
            ";

    echo "            <br />
                        <table width='60%'>
                            <tr>
                                <td class='headline' colspan='6'>
                                <div class='headline cntr'>Select all databases that should deliver search results.<br />Database $dbu_act is the currently activated default db.<br /> </span> </div>
                                </td>
                            </tr>
                            <tr class='sml cntr'>
                                <td width='20%' class='tblhead'>db 1</td>
                                <td width='20%' class='tblhead'>db 2</td>
                                <td width='20%' class='tblhead'>db 3</td>
                                <td width='20%' class='tblhead'>db 4</td>
                                <td width='20%' class='tblhead'>db 5</td>
                            </tr>
                            <tr class='$bgcolor cntr'>
                                <td>"; if ($db1_set) {echo "<input type='checkbox' name='_db1_slv' value='1' "; if ($db1_slv==1) echo "checked='checked'"; echo" />";} echo "</td>
                                <td>"; if ($db2_set) {echo "<input type='checkbox' name='_db2_slv' value='1' "; if ($db2_slv==1) echo "checked='checked'"; echo" />";} echo "</td>
                                <td>"; if ($db3_set) {echo "<input type='checkbox' name='_db3_slv' value='1' "; if ($db3_slv==1) echo "checked='checked'"; echo" />";} echo "</td>
                                <td>"; if ($db4_set) {echo "<input type='checkbox' name='_db4_slv' value='1' "; if ($db4_slv==1) echo "checked='checked'"; echo" />";} echo "</td>
                                <td>"; if ($db5_set) {echo "<input type='checkbox' name='_db5_slv' value='1' "; if ($db5_slv==1) echo "checked='checked'"; echo" />";} echo "</td>
                            </tr>
                        </table>
                        <br />
            ";

    //      Database activation for URL suggest user, define default database
    if ($dbs_act != "2" && $dbs_act != "3" && $dbs_act != "4" && $dbs_act != "5") {
        $dbs_act = "1";
    }

    echo "            <br />
                        <table width='60%'>
                            <tr>
                                <td class='headline' colspan='6'>
                                <div class='headline cntr'>Select the database used for 'Suggest URL' User</div>
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
                                <td>"; if ($db1_set) {echo "<input type=\"radio\" name=\"dbs_act\" value=\"1\" "; if ($dbs_act == 1) print "checked=\"checked\""; echo" />";} echo "</td>
                                <td>"; if ($db2_set) {echo "<input type=\"radio\" name=\"dbs_act\" value=\"2\" "; if ($dbs_act == 2) print "checked=\"checked\""; echo" />";} echo "</td>
                                <td>"; if ($db3_set) {echo "<input type=\"radio\" name=\"dbs_act\" value=\"3\" "; if ($dbs_act == 3) print "checked=\"checked\""; echo" />";} echo "</td>
                                <td>"; if ($db4_set) {echo "<input type=\"radio\" name=\"dbs_act\" value=\"4\" "; if ($dbs_act == 4) print "checked=\"checked\""; echo" />";} echo "</td>
                                <td>"; if ($db5_set) {echo "<input type=\"radio\" name=\"dbs_act\" value=\"5\" "; if ($dbs_act == 5) print "checked=\"checked\""; echo" />";} echo "</td>
                            </tr>
                        </table>
                        <br >
                        <br /><br /><center>
                        <input type=\"submit\" value=\"Store all selections\" />
                        </center>
                        <br /><br />
            ";

    //      Select prefix for databases, if more than one set of tables is available
    $table_sets = array();
    $db_con = db_connect($mysql_host1, $mysql_user1, $mysql_password1, $database1);
    $sql_query = "SHOW TABLES like '%addurl'";
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

    $tables 	= array();
    $num_rows   = $result->num_rows;

    if ($num_rows) {
	    while ($this_tableset = $result->fetch_array(MYSQL_ASSOC)) {
	        $tables1[] = $this_tableset;
	    }

	    foreach($tables1 as $this_set) {
	        $my_set = array_flip($this_set);
	        $table_sets[] = array_keys($my_set);
	    }
    }

    if (count($table_sets) > 1) {
        echo "            <table width='40%'>
                            <tr>
                                <td class='headline' colspan='6'>
                                <div class='headline cntr'>Select table set (by prefix) for database 1</div>
                                </td>
                            </tr>
                        </table>
                        <table width='40%'>
            ";
        $i = 0;
        foreach ($table_sets as $prefix) {
                $prefix1 = str_replace("addurl", "", $prefix[0]);  //  extract the prefix for all table sets

            //mark the prefix for GRHS entire content
            if ($prefix1 == "search1_") {
                $grhs_1 = "<strong>search1_</strong>";
            } else {
                $grhs_1 = $prefix1;
            }
            if ($i & 1) {
                echo "      <tr class='evrow cntr'>
                              <td>"; echo "$grhs_1</td>
                              <td> <input type=\"radio\" name=\"prefix1\" value=\"$prefix1\" "; if ($prefix1 == $mysql_table_prefix1) print "checked=\"checked\""; echo" /></td>
                          </tr>
                    ";
            } else {
                echo "              <tr class='odrow cntr'>
                              <td>"; echo "$grhs_1</td><td> <input type=\"radio\" name=\"prefix1\" value=\"$prefix1\""; if ($prefix1 == $mysql_table_prefix1) print "checked=\"checked\""; echo" /></td>
                          </tr>
                    ";
            }
            $i++;
        }
        echo "    </table>
                        <br /><br />";
    }

    //      Select prefix for db2, if more than one set of tables is available
    if ($db2_set) {
        $table_sets = array();
        $db_con = db_connect($mysql_host2, $mysql_user2, $mysql_password2, $database2);
        $sql_query = "SHOW TABLES like '%addurl'";
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
        $tables2 	= array();
	    $num_rows   = $result->num_rows;

	    if ($num_rows) {
	        while ($this_tableset = $result->fetch_array(MYSQL_ASSOC)) {
	            $tables2[] = $this_tableset;
	        }
	        foreach($tables2 as $this_set) {
	            $my_set = array_flip($this_set);
	            $table_sets[] = array_keys($my_set);
	        }
    	}
        if (count($table_sets) > 1) {
            echo "
                        <table width='40%'>
                            <tr>
                                <td class='headline' colspan='6'>
                                <div class='headline cntr'>Select table set (by prefix) for database 2</div>
                                </td>
                            </tr>
                        </table>
                        <table width='40%'>
                ";
            $i = 0;
            foreach ($table_sets as $prefix) {
                $prefix2 = str_replace("addurl", "", $prefix[0]);  //  extract the prefix for all table sets

                if ($i & 1) {
                    echo "
                            <tr class=\"odrow cntr\">
                                <td>"; echo "$prefix2</td><td> <input type=\"radio\" name=\"prefix2\" value=\"$prefix2\" "; if ($prefix2 == $mysql_table_prefix2) print "checked=\"checked\""; echo" /></td>

                            </tr>
                            ";
                } else {
                    echo "
                            <tr class=\"evrow cntr\">
                                <td>"; echo "$prefix2</td><td> <input type=\"radio\" name=\"prefix2\" value=\"$prefix2\""; if ($prefix2 == $mysql_table_prefix2) print "checked=\"checked\""; echo" /></td>
                            </tr>
                            ";
                }
                $i++;
            }
            echo "
                        </table>
                        <br /><br />";
        }
    }

    //      Select prefix for db3, if more than one set of tables is available
    if ($db3_set) {
        $table_sets = array();
        $db_con = db_connect($mysql_host3, $mysql_user3, $mysql_password3, $database3);
        $sql_query = "SHOW TABLES like '%addurl'";
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
        $tables3 	= array();
	    $num_rows   = $result->num_rows;

	    if ($num_rows) {
	        while ($this_tableset = $result->fetch_array(MYSQL_ASSOC)) {
	            $tables3[] = $this_tableset;
	        }
	        foreach($tables3 as $this_set) {
	            $my_set = array_flip($this_set);
	            $table_sets[] = array_keys($my_set);
	        }
	    }
        if (count($table_sets) > 1) {
            echo "
                        <table width='40%'>
                            <tr>
                                <td class='headline' colspan='6'>
                                <div class='headline cntr'>Select table set (by prefix) for database 3</div>
                                </td>
                            </tr>
                        </table>
                        <table width='40%'>
                ";
            $i = 0;
            foreach ($table_sets as $prefix) {
                $prefix3 = str_replace("addurl", "", $prefix[0]);  //  extract the prefix for all table sets

                if ($i & 1) {
                    echo "
                            <tr class=\"odrow cntr\">
                                <td>"; echo "$prefix3</td><td> <input type=\"radio\" name=\"prefix3\" value=\"$prefix3\" "; if ($prefix3 == $mysql_table_prefix3) print "checked=\"checked\""; echo" /></td>
                            </tr>
                            ";
                } else {
                    echo "
                            <tr class=\"evrow cntr\">
                                <td>"; echo "$prefix3</td><td> <input type=\"radio\" name=\"prefix3\" value=\"$prefix3\""; if ($prefix3 == $mysql_table_prefix3) print "checked=\"checked\""; echo" /></td>
                            </tr>
                            ";
                }
                $i++;
            }
            echo "
                        </table>
                        <br /><br />";
        }
    }

    //      Select prefix for db4, if more than one set of tables is available
    if ($db4_set) {
        $table_sets = array();
        $db_con = db_connect($mysql_host4, $mysql_user4, $mysql_password4, $database4);
        $sql_query = "SHOW TABLES like '%addurl'";
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
        $tables4 	= array();
	    $num_rows   = $result->num_rows;

	    if ($num_rows) {
	        while ($this_tableset = $result->fetch_array(MYSQL_ASSOC)) {
	            $tables4[] = $this_tableset;
	        }
	        foreach($tables4 as $this_set) {
	            $my_set = array_flip($this_set);
	            $table_sets[] = array_keys($my_set);
	        }
	    }
        if (count($table_sets) > 1) {
            echo "
                        <table width='40%'>
                            <tr>
                                <td class='headline' colspan='6'>
                                <div class='headline cntr'>Select table set (by prefix) for database 4</div>
                                </td>
                            </tr>
                        </table>
                        <table width='40%'>
                ";
            $i = 0;
            foreach ($table_sets as $prefix) {
                $prefix4 = str_replace("addurl", "", $prefix[0]);  //  extract the prefix for all table sets

                if ($i & 1) {
                    echo "
                            <tr class=\"odrow cntr\">
                                <td>"; echo "$prefix4</td><td> <input type=\"radio\" name=\"prefix4\" value=\"$prefix4\" "; if ($prefix4 == $mysql_table_prefix4) print "checked=\"checked\""; echo" /></td>

                            </tr>
                            ";
                } else {
                    echo "
                            <tr class=\"evrow cntr\">
                                <td>"; echo "$prefix4</td><td> <input type=\"radio\" name=\"prefix4\" value=\"$prefix4\""; if ($prefix4 == $mysql_table_prefix4) print "checked=\"checked\""; echo" /></td>
                            </tr>
                            ";
                }
                $i++;
            }
            echo "
                        </table>
                        <br /><br />";
        }
    }

    //      Select prefix for db5, if more than one set of tables is available
    if ($db5_set) {
        $table_sets = array();
        $db_con = db_connect($mysql_host5, $mysql_user5, $mysql_password5, $database5);
        $sql_query = "SHOW TABLES like '%addurl'";
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
        $tables5 	= array();
	    $num_rows   = $result->num_rows;

	    if ($num_rows) {
	        while ($this_tableset = $result->fetch_array(MYSQL_ASSOC)) {
	            $tables5[] = $this_tableset;
	        }
	        foreach($tables5 as $this_set) {
	            $my_set = array_flip($this_set);
	            $table_sets[] = array_keys($my_set);
	        }
	    }
        if (count($table_sets) > 1) {
            echo "
                        <table width='40%'>
                            <tr>
                                <td class='headline' colspan='6'>
                                <div class='headline cntr'>Select table set (by prefix) for database 5</div>
                                </td>
                            </tr>
                        </table>
                        <table width='40%'>
                ";
            $i = 0;
            foreach ($table_sets as $prefix) {
                $prefix5 = str_replace("addurl", "", $prefix[0]);  //  extract the prefix for all table sets

                if ($i & 1) {
                    echo "
                            <tr class=\"odrow cntr\">
                                <td>"; echo "$prefix5</td><td> <input type=\"radio\" name=\"prefix5\" value=\"$prefix5\" "; if ($prefix5 == $mysql_table_prefix5) print "checked=\"checked\""; echo" /></td>

                            </tr>
                            ";
                } else {
                    echo "
                            <tr class=\"odrow cntr\">
                                <td>"; echo "$prefix5</td><td> <input type=\"radio\" name=\"prefix5\" value=\"$prefix1\""; if ($prefix5 == $mysql_table_prefix5) print "checked=\"checked\""; echo" /></td>
                            </tr>
                            ";
                }
                $i++;
            }
            echo "
                        </table>
                        <br /><br />";
        }
    }

    echo "
                    </form>
                </div>
                <br />
                <div class='clear'></div>
                <br />
            </div>
        </body>
    </html>
            ";

?>