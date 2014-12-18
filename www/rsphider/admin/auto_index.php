<?php
    /*************************************************************
     *
     This script handles the periodical re-indexing proccedure.
     Called by 'admin.php' via f=59, the cyclical re-indexing is started.
     This script is aborted, if the log file is deleted by 'admin.php' via f=60
     *
     *************************************************************/

    //error_reporting (E_ALL);    //  use this for script debugging
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE & ~E_STRICT);

    $host   = $_SERVER['HTTP_HOST'];
    $uri    = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    $home   = 'admin.php';
    header("Location: http://$host$uri/$home");    //   if all is done, jump back to Sites menu

    set_time_limit (0);
    define("_SECURE",1);    // define secure constant

    $plus_nr        = '';
    $include_dir    = "../include";
    $settings_dir   = "../settings";
    $converter_dir  = "../converter";
    $dict_dir       = "$converter_dir/dictionaries";
    $stem_dir       = "$include_dir/stemming";
    $textcache_dir  = "$include_dir/textcache";
    $mediacache_dir = "$include_dir/mediacache";

    include "$settings_dir/database.php";
    include "$include_dir/commonfuncs.php";

    require_once "messages.php";
    require_once "spiderfuncs.php";
    require_once ("$converter_dir/ConvertCharset.class.php");

    if ($mb == 1) {
        mb_internal_encoding("UTF-8");      //  define standard charset for mb functions
    }

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

    //  now fetch the according configuration file
    @include "".$settings_dir."/db".$dba_act."/conf_".$mysql_table_prefix.".php";

    $template_dir   = "../".$templ_dir."";
    $template_path  = "$template_dir/$template";
    $id3_dir        = "./getid3";
    $local_redir    = '';
    $site_url       = '';
    $multi          = '';
    $com_in         = array();

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
            $audiolist = array();
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

    $start  = '';
    extract (getHttpVars());
    $id = $multi;   //  update $id

    if ($all == '1' && $multi_indexer > '1' && !$site_url){    //  'index/re-index all' was initialized by Admin interface, but not by command line operation
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
        $result = $db_con->query($sql_query);
    }

    $delay_time     = 0;
    $command_line   = 0;
    $copy           = '1';
    $omit           = '';
    $tmp_urls       = Array();

    if (isset($soption) && $soption == 'full') {
        $maxlevel = '-1';
    }

    if (!isset($domaincb)) {
        $domaincb = '0';
    }

    if (!isset($use_prefcharset)) {
        $use_prefcharset = '0';
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

    if ($keep_log && $multi != '1' && ($all < '4' || $all >= '20') || $site_url) {
        $log_handle = create_logFile($id);
    }

    if (!$started){
        $started = '0'; //  initialize this variable(will become timestamp when first indexer was started)
    }
    if ($multi == '2'){ //  Admin started the first indexer
        $started = time();
    }

    if ($multi == '1' && !$site_url) {    //  Wait for admin's first thread activation
        die();
    }

    if ($multi_indexer > '1') { // superess output for multithreaded indexing
        $cl = '1';
        $command_line = '1';
    }

    //********************************************************


    $no_log = '1';  //  surpress  any output for the Auto Re-indexer
    $cl     = '1';  //  surpress  any output for the Auto Re-indexer
    $back   = "admin.php";  //  back to main menu in Admin backend

    $interval = '20';    // for (short running [20 seconds] interval) tests only

    printHTMLHeader($omit, $site_url, $cl, $multi, $all, $started) ;

    //  for new start of Auto-Re-indexer, reset the log file
    if ($start == "1") {
        $fp = fopen($logfile,"w");    //  reset the auto-indexer log file
        if (!is_writable($logfile)) {
            print "Auto indexer not started, because the log-file file is not writable.";
            die();
        } else {
            fclose($fp);
        }
    }

    //  loop through the re-indexing procedures
    while ($i <= $intv_count) {
        $started    = time();
        $fp         = '';
        $update     = "".$i."count".$started."\r\n";

        $fp = @fopen($logfile,"r");      //  add new start date and time to the auto-indexer log file
        if (!is_readable($logfile)) {
            @fclose($fp);
            print "Auto indexer aborted, because the log-file file is not readable.";
            die();
        } else {
            @fclose($fp);
            $fp = @fopen($logfile,"a");      //  add new start date and time to the auto-indexer log file
            fwrite($fp, $update);
            fclose($fp);
        }

        if (!$site_url) {    //  enter here to re-index all sites

            index_all();

        } else {        //  enter here foe site specific re-index

            $reindex    = '1';
            $cl         = '0';
            $all        = '0';

            if ($maxlevel == -1) {
                $soption = 'full';
            } else {
                $soption = 'level';
            }

            index_site($site_url, $reindex, $maxlevel, $soption, $include, $not_include, $can_leave_domain, $use_robot,  $use_nofollow, $cl, $all, $use_prefcharset);
        }

        $auto_ended     = time();
        $auto_consumed  = $auto_ended - $started;       //  calculated time for last re-index procedure
        $wait           = $interval - $auto_consumed;   //  calculate rest of index interval

        if ($wait <=0) {    //  if interval is too short
            $update = "aborted".time()."";
            $fp = fopen($logfile,"a");      //  add 'aborted' to the end of the auto-indexer log file
            if (is_readable($logfile)) {
                fwrite($fp, $update);
                fclose($fp);
            }

            print "<br />Auto indexer aborted, because the last index procedure took too long.<br />
                Index procedure consumed $consumed seconds, while the re-index interval was defined only to $interval seconds.";
            die();
        } else {
            sleep($wait);   //wait for the rest of the index interval
            $i++;
        }
    }       //  end of periodical Re-indexing loop

    $update = "finished".time()."";
    $fp = fopen($logfile,"a");      //  add 'finished' to the end of the auto-indexer log file
    if (is_readable($logfile)) {
        fwrite($fp, $update);
        fclose($fp);
    }

    exit;

?>