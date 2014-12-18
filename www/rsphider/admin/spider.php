<?php

    set_time_limit (0);
    define("_SECURE",1);    // define secure constant
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE & ~E_STRICT);

    $plus_nr        = '';

    $include_dir    = "../include";
    $settings_dir   = "../settings";
    $converter_dir  = "../converter";
    $language_dir   = "../languages";
    $dict_dir       = "$converter_dir/dictionaries";
    $stem_dir       = "$include_dir/stemming";
    $textcache_dir  = "$include_dir/textcache";
    $mediacache_dir = "$include_dir/mediacache";

    include "$settings_dir/database.php";
    include "$include_dir/commonfuncs.php";
    include "$language_dir/en-language.php";

    error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE & ~E_STRICT);

    $cat_sel_all    = $sph_messages['all'];

    //      get active database for this task
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

    @include "".$settings_dir."/db".$dba_act."/conf_".$mysql_table_prefix.".php";
    if (!$plus_nr) {
        include "/settings/backup/Sphider-plus_default-configuration.php";
    }

    if ($default_agent == 1) {
        $user_agent = 'Mozilla/5.0 (Windows NT 6.1; rv:12.0) Gecko/20100101 Firefox/12.0';
    }

    include "messages.php";
    include "spiderfuncs.php";

    $com_in = array();
    $local_redir    = '';
    $url            = '';
    $multi          = '';

    //  now replace some variables with actual Admin settings
    if (is_dir($common_dir)) {
        $handle = opendir($common_dir);
        if ($use_common == 'all') {
            while (false !== ($common_file = readdir($handle))) {   //  get all common files
                if (strpos($common_file, "ommon_")) {
                    $act = @file($common_dir.$common_file);         //  get content of actual common file
                    $com_in = array_merge($com_in, $act);                 //  build a complete array of common words
                }
            }
        }

        if ($use_common != 'all' && $use_common != 'none') {
            $com_in = @file("".$common_dir."common_".$use_common.".txt");         //  get content of language specific common file
        }

        $suffix = @file($common_dir.'suffix.txt');      //  get all file suffixes to be ignored during index procedure
        if ($use_white1 == '1' || $use_white2 == '1') $white_in = @file($common_dir.'whitelist.txt');    //  get all words to enable page indexing
        if ($use_black == '1') $black_in = @file($common_dir.'blacklist.txt');     //  get all words to prevent indexing of page

        if ($index_image) $image = @file($common_dir.'image.txt');       //  get all image suffixes to be indexed
        if ($index_audio) $audio = @file($common_dir.'audio.txt');       //  get all audio suffixes to be indexed
        if ($index_video) $video = @file($common_dir.'video.txt');       //  get all audio suffixes to be indexed

        $divs_not   = @file($common_dir.'divs_not.txt');    //  get all div's to not to be indexed (Admin selected)
        $divs_use   = @file($common_dir.'divs_use.txt');    //  get all div's to be indexed (Admin selected)
        $sld        = @file($common_dir.'sld.txt');         //  get all SLDs

        closedir($handle);

        if (is_array($com_in)) {
            while (list($id, $word) = each($com_in))
            $common[trim($word)] = 1;
        }

        if (is_array($suffix)) {
            $ext = array();
            while (list($id, $word) = each($suffix))
            $ext[] = trim($word);
            $ext = array_unique($ext);
            sort($ext);
        }

        if (is_array($white_in)) {
            $white      = array();
            $whitelist  = array();

            foreach ($white_in as $val) {
                if ($case_sensitive == '0') {
                    $val = lower_case($val);
                }
                $val = @iconv($home_charset,"UTF-8",$val);

                $white[] = addslashes($val);
            }

            foreach ($white as $val) {
                if ($trim_words) {
                    $whitelist[] = trim($val);
                } else {
                    $whitelist[] = $val;
                }
            }

            $whitelist = array_unique($whitelist);
            sort($whitelist);
        }

        if (is_array($black_in)) {
            $black      = array();
            $blacklist  = array();

            foreach ($black_in as $val) {
                if ($case_sensitive == '0') {
                    $val = lower_case($val);
                }

                $val = @iconv($home_charset,"UTF-8",$val);
                $black[] = addslashes($val);
            }

            foreach ($black as $val) {
                if ($trim_words) {
                    $blacklist[] = trim($val);
                } else {
                    $blacklist[] = $val;
                }
            }
            $blacklist = array_unique($blacklist);
            sort($blacklist);
        }

        if (is_array($image)) {
            $imagelist = array();
            while (list($id, $word) = each($image))
            $imagelist[] = trim(strtolower($word));
            $imagelist = array_unique($imagelist);
            sort($imagelist);
        }

        if (is_array($audio)) {
            $audiolist = array();
            while (list($id, $word) = each($audio))
            $audiolist[] = trim(strtolower($word));
            $audiolist = array_unique($audiolist);
            sort($audiolist);
        }

        if (is_array($video)) {
            $videolist = array();
            while (list($id, $word) = each($video))
            $videolist[] = trim(strtolower($word));
            $videolist = array_unique($videolist);
            sort($videolist);
        }

        if (is_array($divs_not)) {
            $not_divlist = array();
            while (list($id, $word) = each($divs_not))
            $not_divlist[] = trim($word);
            $not_divlist = array_unique($not_divlist);
            sort($not_divlist);
        }

        if (is_array($divs_use)) {
            $use_divlist = array();
            while (list($id, $word) = each($divs_use))
            $use_divlist[] = trim($word);
            $use_divlist = array_unique($use_divlist);
            sort($use_divlist);
        }

        if (is_array($sld)) {
            $sldlist = array();
            while (list($id, $word) = each($sld))
            $sldlist[] = trim($word);
            $sldlist = array_unique($sldlist);
            sort($sldlist);
        }
    }

    if ($mb == 1) {
        mb_internal_encoding("UTF-8");      //  define standard charset for mb functions
    }

    if ($dba_act == '1') {
        $db_con = db_connect($mysql_host1, $mysql_user1, $mysql_password1, $database1);
        $mysql_table_prefix = $mysql_table_prefix1;
        //  try to initialize a 10 MByte MySQL cache (might not work on shared hosting systems) for database  1
        $mysql_csize    = $db_con->query("SET GLOBAL query_cache_size = 10485760");
        $mysql_cacheon  = $db_con->query("SET GLOBAL query_cache_type = ON") ;
    }

    if ($dba_act == '2') {
        $db_con = db_connect($mysql_host2, $mysql_user2, $mysql_password2, $database2);
        $mysql_table_prefix = $mysql_table_prefix2;
        //  try to initialize a 10 MByte MySQL cache (might not work on shared hosting systems) for database  2
        $mysql_csize    = $db_con->query("SET GLOBAL query_cache_size = 10485760");
        $mysql_cacheon  = $db_con->query("SET GLOBAL query_cache_type = ON") ;
    }

    if ($dba_act == '3') {
        $db_con = db_connect($mysql_host3, $mysql_user3, $mysql_password3, $database3);
        $mysql_table_prefix = $mysql_table_prefix3;
        //  try to initialize a 10 MByte MySQL cache (might not work on shared hosting systems) for database  3
        $mysql_csize    = $db_con->query("SET GLOBAL query_cache_size = 10485760");
        $mysql_cacheon  = $db_con->query("SET GLOBAL query_cache_type = ON") ;
    }

    if ($dba_act == '4') {
        $db_con = db_connect($mysql_host4, $mysql_user4, $mysql_password4, $database4);
        $mysql_table_prefix = $mysql_table_prefix4;
        //  try to initialize a 10 MByte MySQL cache (might not work on shared hosting systems) for database  4
        $mysql_csize    = $db_con->query("SET GLOBAL query_cache_size = 10485760");
        $mysql_cacheon  = $db_con->query("SET GLOBAL query_cache_type = ON") ;
    }

    if ($dba_act == '5') {
        $db_con = db_connect($mysql_host5, $mysql_user5, $mysql_password5, $database5);
        $mysql_table_prefix = $mysql_table_prefix5;
        //  try to initialize a 10 MByte MySQL cache (might not work on shared hosting systems) for database  5
        $mysql_csize    = $db_con->query("SET GLOBAL query_cache_size = 10485760");
        $mysql_cacheon  = $db_con->query("SET GLOBAL query_cache_type = ON") ;
    }

    extract (getHttpVars());

    $id     = $multi;   //  update $id
    $url    = str_replace("-_-", "&", $url);   //      decrypt the & character
    $url    = str_replace("_-_", "+", $url);   //      decrypt the + character

    require_once ("$converter_dir/ConvertCharset.class.php");

    $template_dir   = "../".$templ_dir."";
    $template_path  = "$template_dir/$template";
    $id3_dir        = "./getid3";

    if ($all == '1' && $multi_indexer > '1'){    //  'index/re-index all' was initialized by Admin interface, but not by command line operation
        pre_all();      //  define all sites as erased, but don't erase the content
        $all = '3';     //  now re-index all with support of multithreaded indexer
    }

    if ($index_rss == '1') {
        include "$converter_dir/feed_parser.php";
    }

    if ($index_id3 == '1') {
        include "$id3_dir/getid3.php";
    }

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

    $delay_time     = 0;
    $command_line   = 0;
    $copy           = '1';
    $omit           = '';
    $cl             = '0';
    $tmp_urls       = Array();

    if (isset($_SERVER['argv']) && $_SERVER['argc'] >= 2) {
        $multi_indexer = 1;     //  command line operation does not require interactive start of indexer
        $id = '0';
        $started = time();
        $command_line = 1;
        $cl = 1;
        $ac = 1; 	//argument counter
        while ($ac < (count($_SERVER['argv']))) {
            $arg = $_SERVER['argv'][$ac];

            if ($arg  == '-all') {
                $all = 1;
                if ($clear_cache == '1') {
                    clear_TextCache();
                    clear_MediaCache();
                }
                //pre_all();      //  define all sites as erased, but don't erase the content
                $log_handle = create_logFile($id);
                break;

            } else if (strpos($arg, "new")) {
                $all= 2;
                $id  = substr($arg, strpos($arg, "new")+3);     //  extract ID from command line
                break;

            } else if (strpos($arg, "erased")) {
                $all = 3;
                $id  = substr($arg, strpos($arg, "erased")+6);  //  extract ID from command line
                break;

            } else if ($arg  == '-eall') {
                $all= 4;
                break;

            } else if ($arg  == '-erase') {
                $all= 5;
                break;

            } else if ($arg  == '-preall') {
                $all= 6;
                break;

            } else if ($arg  == '-u') {
                $url = $_SERVER['argv'][$ac+1];
                $ac= $ac+2;
            } else if ($arg  == '-f') {
                $soption = 'full';
                $ac++;
            } else if ($arg == '-d') {
                $soption = 'level';
                $maxlevel =  $_SERVER['argv'][$ac+1];;
                $ac= $ac+2;
            } else if ($arg == '-l') {
                $can_leave = 1;
                $ac++;
            } else if ($arg == '-r') {
                $reindex = 1;
                $ac++;
            } else if ($arg  == '-m') {
                $in =  str_replace("\\n", chr(10), $_SERVER['argv'][$ac+1]);
                $ac= $ac+2;
            } else if ($arg  == '-n') {
                $out =  str_replace("\\n", chr(10), $_SERVER['argv'][$ac+1]);
                $ac= $ac+2;
            } else {
                commandline_help();
                die();
            }
        }
    }

    /*
     // simulate command line  operation
     $started = time();
     $multi_indexer = 1;
     $command_line = 1;
     $cl = 1;
     */

    if (isset($soption) && $soption == 'full') {
        $maxlevel = '-1';
    }

    if (!isset($can_leave)) {
        $can_leave = '0';
    }

    if (!isset($use_pref)) {
        $use_pref = '0';
    }

    if(!isset($reindex)) {
        $reindex = '0';
    }

    if(!isset($not_use_robot) || $not_use_robot == '0') {
        $use_robot = '1';
    }

    if ($not_use_robot == '1') {
        $use_robot = '0';
    }

    if(!isset($not_use_nofollow) || $not_use_nofollow == '0') {
        $use_nofollow = '1';
    }
    if ($not_use_nofollow == '1') {
        $use_nofollow = '0';
    }

    if(!isset($maxlevel)) {
        $maxlevel = '0';
    }

    if ($multi_indexer > '1') { //  multithreaded indexing?
        if (!$multi) {          //  first loop in multi-indexer
            $multi = '0';
        }
        $multi++;
    }

    if ($keep_log && $multi != '1' && ($all < '4' || $all >= '20') || $url) {

        $log_handle = create_logFile($id);
    }

    //  get our current MySQL thread-id and save it
    $thread_id = "".$db_con->thread_id."\r\n";
    //  ensure that indexation meanwhile wasn't manually aborted (for multithreaded indexing)
    if(!is_file("".$tmp_dir."/thread_ids.txt")) {
        $db_con->kill($thread_id); //close last MySQL connection
        $report = "<br />Indexation manually aborted.<br />";
        printWarning($report, $cl);
        if ($log_format == "html") {
            echo "  </body>
                </html>
            ";
        }
        exit;
    }

    $fp = @fopen("".$tmp_dir."/thread_ids.txt","a+");    //  try to write at the end of file

    if(!is_writeable("".$tmp_dir."/thread_ids.txt")) {
        echo "  <!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
    <html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
        <head>
            <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
            <title>Sphider-plus v.$plus_nr Error output</title>
            <link rel='stylesheet' href='$template_path/adminstyle.css' media='screen' type='text/css' />
            <meta http-equiv='X-UA-Compatible' content='IE=9' />
            <link href='../templates/html/sphider-plus.ico' rel='shortcut icon' type='image/x-icon' />
        </head>
        <body>
            <p class='warnadmin cntr'>
            <br /><br />
            Unable to open the file .../admin/".$tmp_dir."/thread_ids.txt
            <br /><br />
            Index procedure aborted.
            <br /><br /></p>
            <br /><br />
            <p class='evrow'><a class='bkbtn' href='admin.php' title='Go back to Admin'>Back to admin</a></p>
            <br /><br />
        </body>
    </html>
                ";
        exit;
    }
    if (!fwrite($fp, $thread_id)) {
        echo "  <!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
     <html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
        <head>
            <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
            <title>Sphider-plus v.$plus_nr Error output</title>
            <link rel='stylesheet' href='$template_path/adminstyle.css' media='screen' type='text/css' />
            <meta http-equiv='X-UA-Compatible' content='IE=9' />
            <link href='../templates/html/sphider-plus.ico' rel='shortcut icon' type='image/x-icon' />
        </head>
        <body>
            <p class='warnadmin cntr'>
            <br /><br />
            Unable to write the actual MySQL thread-id into file ".$tmp_dir."/thread_ids.txt
            <br /><br />
            Index procedure aborted.
            <br /><br /></p>
            <br /><br />
            <p class='evrow'><a class='bkbtn' href='admin.php' title='Go back to Admin'>Back to admin</a></p>
            <br /><br />
        </body>
    </html>
        ";
        exit;
    }
    fclose($fp);

    if (!$started){
        $started = '0'; //  initialize this variable(will become timestamp when first indexer was started)
    }

    if ($multi == '2'){ //  Admin started the first indexer
        $started = time();
    }

    if ($all != '6') {
        printHTMLHeader($omit, $url, $cl, $multi, $all, $started) ;
    }

    if ($multi == '1' && !$url) {    //  Wait for admin's first thread activation
        die();
    }

    if ($multi_indexer > '1') { // superess output for multithreaded indexing
        $cl = '1';
        $command_line = '1';
    }

    if ($all == '1') {      //  for command line operation: index all sites in database
        index_all();
    }

    if ($all == '2') {      //  index all new sites, never indexed before
        index_new();
    }

    if ($all == '3') {      //  index all erased sites
        index_erased();
    }

    if ($all == '4') {      //  'Erase & Re-index all' for command line operation
        erase();
        $log_handle = create_logFile($id);
        index();
    }

    if ($all == '5') {
        erase();            //  erase for command line operation
    }

    if ($all == '6') {
        pre_all();          //  clear 'last indexed' for command line operation
        die();
    }

    if ($all == '20') {     //  index all suspended sites
        index_suspended();
    }

    if ($all == '21') {     //  index all sites shown on one page
        index_these();
    }

    if ($all == '22') {     //  index only the prioritized sites
        index_prior($pref_level);
    }

    if ($all != '1' && $all != '2' && $all != '3' && $all != '4' && $all != '5' && $all != '20' && $all != '21' && $all != '22') {
        if ($reindex == 1 && $command_line == 1) {
            mysqltest();
            $sql_query = "SELECT url, spider_depth, required, disallowed, can_leave_domain, use_prefcharset from ".$mysql_table_prefix."sites where url='$url'";
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

            if ($this_url = $result->fetch_array(MYSQLI_NUM) ) {
                $url = $this_url[0];
                $maxlevel = $this_url[1];
                $in= $this_url[2];
                $out = $this_url[3];
                $can_leave = $this_url[4];
                $use_pref = $this_url[5];

                if ($can_leave=='') {
                    $can_leave=0;
                }
                if ($maxlevel == -1) {
                    $soption = 'full';
                } else {
                    $soption = 'level';
                }
            }
            if ($clear == 1) clean_resource($result, '01') ;
        }

        if (!isset($in)) {
            $in = "";
        }
        if (!isset($out)) {
            $out = "";
        }

        $started = time();
        index_site($url, $reindex, $maxlevel, $soption, $in, $out, $can_leave, $use_robot, $use_nofollow, $cl, $all, $use_pref);
        $ended = time();

        $consumed = $ended - $started;
        printConsumedReport('consumed', $cl, '0', $consumed);
        printStandardReport('ReindexFinish',$command_line, '0');
    }

    printStandardReport('quit',$command_line, '0');

    if ($email_log) {
        $indexed = ($all==1) ? 'ALL' : $url;
        $log_report = "";

        if ($log_handle) {
            $log_report = "Log saved into $log_file";
        }
        mail($admin_email, "Sphider indexing report", "Sphider has finished indexing $indexed at ".date("y-m-d H:i:s").". ".$log_report);
    }

    if ( $log_handle) {
        fclose($log_handle);
    }

?>