<?php
    error_reporting(E_ALL);
    define("_SECURE",1);    // define secure constant

    include "admin_header.php";
    include "$settings_dir/database.php";

    $f  = '0';
    $db_num = '0';
    if (isset($_GET['f']))
    $f = $_GET['f'];
    if (isset($_GET['db_num']))
    $db_num = $_GET['db_num'];

    if ($db_num == '1') {
        $mysql_table_prefix = $mysql_table_prefix1;
        $db_con = idb_connect($mysql_host1, $mysql_user1, $mysql_password1, $database1);
    }
    if ($db_num == '2') {
        $mysql_table_prefix = $mysql_table_prefix2;
        $db_con = idb_connect($mysql_host2, $mysql_user2, $mysql_password2, $database2);
    }
    if ($db_num == '3') {
        $mysql_table_prefix = $mysql_table_prefix3;
        $db_con = idb_connect($mysql_host3, $mysql_user3, $mysql_password3, $database3);
    }
    if ($db_num == '4') {
        $mysql_table_prefix = $mysql_table_prefix4;
        $db_con = idb_connect($mysql_host4, $mysql_user4, $mysql_password4, $database4);
    }
    if ($db_num == '5') {
        $mysql_table_prefix = $mysql_table_prefix5;
        $db_con = idb_connect($mysql_host5, $mysql_user5, $mysql_password5, $database5);
    }

    echo "    <h1>Sphider-plus installation script to create all tables for database ".$db_num."</h1>
            <p>&nbsp;</p>
            ";
    if ($f == '0') {
        echo "<div id='settings'>
                <form class='cntr' name='form10' method='post' action='db_config.php'>
                    <fieldset>
                        <label for='submit'class='sml'>
                            Up to now, nothing worth happened.<br />
                            If you want to return without installing the tables:
                        </label>
                        <br />
                        <input class='sbmt' type='submit' value='Cancel' id='submit' title='Click once to return to database settings'/>
                    </fieldset>
                </form>
                <br />
                <form class='cntr' name='form11' method='post' action='install_tables.php?f=1&db_num=".$db_num."'>
                    <fieldset>
                        <label for='submit'class='sml'>
                            If you really want to create all tables for database ".$db_num."<br /><br />
                            <span class='red'>Attention:</span> Already existing tables with the prefix <strong>'".$mysql_table_prefix."'</strong> <br />
                            will be destroyed and the content of all tables will be lost !<br />
                            Also the default configuration will be placed into<br />
                            the 'Settings' menue for this set of tables!
                        </label>
                        <br /><br /><br /><input class='sbmt' type='submit' value='Install now' id='submit' title='Click once to install all tables' />
                    </fieldset>
                </form>
                <br />
            </div>
        </body>
    </html>
            ";
    } else {
/*
$db_con->close();

        $db_con = new mysqli($mysql_host1, $mysql_user1, $mysql_password1, $database1);

        if ($db_con->connect_errno) {
            printf("<p><span class='red'>&nbsp;MySQL connect failed: %s\n&nbsp;<br /></span></p>", $db_con->connect_error);

        }
*/


        $sql_query = "DROP TABLE IF EXISTS `".$mysql_table_prefix."addurl`";
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

        $sql_query = "CREATE TABLE IF NOT EXISTS `".$mysql_table_prefix."addurl`(
                          url varchar(1024) not null,
                          title varchar(255),
                          description varchar(255),
                          category_id int(11),
                          account varchar(255),
                          authent varchar(255),
                          created timestamp NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP)
                          ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin
                        ";
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

        $sql_query = "DROP TABLE IF EXISTS `".$mysql_table_prefix."banned`";
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

        $sql_query = "CREATE TABLE IF NOT EXISTS `".$mysql_table_prefix."banned` (
                          domain varchar(1024) not null,
                          created timestamp NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP)
                          ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin
                        ";
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

        $sql_query = "DROP TABLE IF EXISTS `".$mysql_table_prefix."real_log`";
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

        $sql_query = "CREATE TABLE IF NOT EXISTS `".$mysql_table_prefix."real_log`(
                          url varchar(1024) not null,
                          real_log mediumtext,
                          refresh integer not null primary key,
                          created timestamp NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP)
                          ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin
                        ";
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

        $sql_query = "DROP TABLE IF EXISTS `".$mysql_table_prefix."sites`";
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

        $sql_query = "CREATE TABLE IF NOT EXISTS `".$mysql_table_prefix."sites`(
                          site_id int auto_increment not null primary key,
                          url varchar(1024),
                          title varchar(255),
                          short_desc text,
                          indexdate date,
                          spider_depth int default -1,
                          required text not null,
                          disallowed text not null,
                          can_leave_domain bool,
                          db bool,
                          smap_url varchar(1024),
                          authent varchar(255),
                          use_prefcharset int default 0,
                          prior_level int default 1)
                          ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin
                        ";
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

        $sql_query = "DROP TABLE IF EXISTS `".$mysql_table_prefix."links`";
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

        $sql_query = "CREATE TABLE IF NOT EXISTS `".$mysql_table_prefix."links` (
                          link_id int auto_increment primary key not null,
                          site_id int,
                          url varchar(1024) not null,
                          title varchar(255),
                          description varchar(255),
                          fulltxt mediumtext,
                          indexdate date,
                          size float(2),
                          md5sum varchar(32),
                          key url (url(128)),
                          key md5key (md5sum),
                          visible int default 0,
                          level int,
                          click_counter INT NULL DEFAULT 0,
                          last_click INT NULL DEFAULT 0,
                          last_query VARCHAR(255),
                          ip varchar(255),
                          relo_count integer,
                          webshot MEDIUMBLOB)
                          ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin
                        ";
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

        $sql_query = "DROP TABLE IF EXISTS `".$mysql_table_prefix."keywords`";
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

        $sql_query = "CREATE TABLE IF NOT EXISTS `".$mysql_table_prefix."keywords`	(
                          keyword_id int primary key not null auto_increment,
                          keyword varchar(255) not null,
                          unique kw (keyword),
                          key keyword (keyword(10)))
                          ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin
                        ";
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

        $sql_query = "DROP TABLE IF EXISTS `".$mysql_table_prefix."link_keyword`";
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

        $sql_query = "CREATE TABLE IF NOT EXISTS `".$mysql_table_prefix."link_keyword` (
                          link_id int not null,
                          keyword_id int not null,
                          weight int(3),
                          domain int(4),
                          hits int(3),
                          indexdate datetime,
                          key linkid(link_id),
                          key keyid(keyword_id))
                          ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin
                        ";
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

        $sql_query = "DROP TABLE IF EXISTS `".$mysql_table_prefix."link_details`";
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

        $sql_query = "CREATE TABLE IF NOT EXISTS `".$mysql_table_prefix."link_details` (
                          link_id int not null,
                          link_cont varchar(1024),
                          url varchar(1024),
                          title varchar(255),
                          indexdate datetime,
                          domain varchar(1024))
                          ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin
                        ";
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

        $sql_query = "DROP TABLE IF EXISTS `".$mysql_table_prefix."categories`";
        if ($db_con->errno) {
            printf("MySQL failure: %s\n", $db_con->error);
            echo "<br />Script aborted.";
            exit;
        }

        $sql_query = "CREATE TABLE IF NOT EXISTS `".$mysql_table_prefix."categories` (
                          category_id integer not null auto_increment primary key,
                          category text,
                          parent_num integer,
                          group_sel0 text,
                          group_sel1 text,
                          group_sel2 text,
                          group_sel3 text,
                          group_sel4 text)
                          ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin
                        ";
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

        $sql_query = "DROP TABLE IF EXISTS `".$mysql_table_prefix."site_category`";
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

        $sql_query = "CREATE TABLE IF NOT EXISTS `".$mysql_table_prefix."site_category` (
                          site_id integer,
                          category_id integer)
                          ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin
                        ";
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

        $sql_query = "DROP TABLE IF EXISTS `".$mysql_table_prefix."temp`";
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

        $sql_query = "CREATE TABLE IF NOT EXISTS `".$mysql_table_prefix."temp` (
                          link varchar(1024),
                          level integer,
                          id varchar (32),
                          relo_link varchar(1024),
                          relo_count integer)
                          ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin
                        ";
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

        $sql_query = "DROP TABLE IF EXISTS `".$mysql_table_prefix."pending`";
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

        $sql_query = "CREATE TABLE IF NOT EXISTS `".$mysql_table_prefix."pending` (
                          site_id integer,
                          temp_id varchar(32),
                          level integer,
                          count integer,
                          num integer)
                          ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin
                        ";
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

        $sql_query = "DROP TABLE IF EXISTS `".$mysql_table_prefix."query_log`";
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

        $sql_query = "CREATE TABLE IF NOT EXISTS `".$mysql_table_prefix."query_log` (
                          query varchar(255),
                          time timestamp default 0,
                          elapsed float(2),
                          results int,
                          ip varchar(255),
                          media int,
                          key query_key(query))
                          ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin
                        ";
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

        $sql_query = "DROP TABLE IF EXISTS `".$mysql_table_prefix."domains`";
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

        $sql_query = "CREATE TABLE IF NOT EXISTS `".$mysql_table_prefix."domains` (
                          domain_id int auto_increment primary key not null,
                          domain varchar(1024))
                          ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin
                        ";
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

        $sql_query = "DROP TABLE IF EXISTS `".$mysql_table_prefix."media`";
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

        $sql_query = "CREATE TABLE IF NOT EXISTS `".$mysql_table_prefix."media` (
                          media_id int auto_increment not null primary key,
                          link_id int(11) NOT NULL,
                          link_addr varchar(1024) COLLATE utf8_bin DEFAULT NULL,
                          media_link varchar(1024) COLLATE utf8_bin DEFAULT NULL,
                          thumbnail MEDIUMBLOB,
                          title varchar(255) COLLATE utf8_bin NOT NULL,
                          type varchar(255) COLLATE utf8_bin NOT NULL,
                          size_x int(11) NOT NULL,
                          size_y int(11) NOT NULL,
                          click_counter int(11) DEFAULT '0',
                          last_click int(11) DEFAULT '0',
                          last_query varchar(255) COLLATE utf8_bin DEFAULT NULL,
                          id3 mediumtext COLLATE utf8_bin NOT NULL,
                          md5sum varchar(32),
                          name varchar(1024),
                          suffix varchar(32),
                          ip varchar(255))
                          ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin
                        ";
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


        //  now copy the default configuration to the according folder
        $source = "./settings/backup/Sphider-plus_default-configuration.php";
        $desti  = "".$settings_dir."/db".$db_num."/conf_".$mysql_table_prefix.".php";
        if (!copy($source, $desti)) {   //  in case that cpying of configuration failed
            echo "<p class='warnok em cntr'>
                Creating of tables successfully completed.</p>
                <br /><br />
                <p class='red em cntr'><br />Copying of default configuration failed.</p>
                <p class='red em cntr'><br />Unable to proceed with this database.<br /><br /></p>
                <br />
        <br /><br /><br />
    </body>
</html>
            ";
            die (); //  unable to proceed
        }
        //  successfully created the tables and the configuration file
        echo "<p class='warnok em cntr'>Creating of database$db_num table set <span class='red'>$mysql_table_prefix</span> and default configuration successfully completed.</p>
        <br /><br /><br />";

        echo "
            <div id='settings'>
                <form class='cntr txt' name='form10' method='post' action='db_config.php'>
                    <fieldset>
                        <label for='submit'class='sml'>
                            Return to Database configuration:
                        </label>
                        <input class='sbmt' type='submit' value='Config' id='submit' title='Click once to return to Database configuration'/>
                    </fieldset>
                </form>
            </div>

            <br />
        </body>
    </html>
                    ";
    }

    // Database1-5 connection
    function idb_connect($mysql_host, $mysql_user, $mysql_password, $database) {

        $db_con = @new mysqli($mysql_host, $mysql_user, $mysql_password, $database);
        /* check connection */
        if ($db_con->connect_errno) {
            echo "<p>&nbsp;</p>
            <p><p class='warnadmin cntr'><br />&nbsp;No valid datbase found to start up.<br />&nbsp;Configure at least one database.<br /><br />
            <p>&nbsp;</p>
            ";

        }

        /* define character set to utf8 */
        if (!$db_con->set_charset("utf8")) {
            printf("Error loading character set utf8: %s\n", $db_con->error);

            /* Print current character set */
            $charset = $db_con->character_set_name();
            printf ("<br />Current character set is: %s\n", $charset);

            $db_con->close();
            exit;
        }

        return ($db_con);
    }

?>
