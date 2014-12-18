<?php

    //  search for images and present them as part of the result listing (text + media)
    function image($query, $url, $media_type, $all, $urlx, $title1, $image_dir, $info, $info2, $thumb, $mode, $media_only, $type, $category, $catid, $mark, $db, $prefix, $domain) {
        global $include_dir, $admin_dir, $sph_messages, $mysql_table_prefix, $template, $template_dir, $index_id3, $limit_media, $delim;

        //error_reporting (E_ALL);      //  for debug only
        $query          = str_replace('*', '', $query);      //      kill wildcards, as media search already includes it
        $media_results  = get_media_results($query, $url, $media_type, $all, $domain, $prefix);
        reactivate_dbuact($prefix);

        if ($media_results) {
            //  limit amount of images presented in result listing (text + media)
            if ($all !=1) {
                $media_results = array_slice($media_results, 0, $limit_media);
            }
            //   display header for image results
            include "".$template_dir."/html/130_image-results header.html";
            $client_ip = @$_SERVER['REMOTE_ADDR'];
            $i = '0';
            while (list($key, $value) = each($media_results)) {
                //      prepare current object-link for click counter
                $link_crypt = str_replace("&", "-_-", $value[3]);    //  crypt the & character
                $link_click = "$include_dir/media_counter.php?url=$link_crypt&amp;query=$query&amp;db=$db&amp;prefix=$prefix&amp;client_ip=$client_ip";   //  redirect users click in order to update Most Media Popular Links
                $thumb_link = utf8_encode($value[4]);
                $title      = substr($value[5], 0, strpos($value[5], $delim));
                //   display  image results
                include "".$template_dir."/html/140_image-results.html";
            }
            //   display  end image results table
            include "".$template_dir."/html/150_end image-results.html";
        }
        return ($media_results);
    }

    //  search for audio and video streams and present them as part of the result listing (text + media)
    function media($query, $url, $media_type, $all, $urlx, $title1, $image_dir, $info, $info2, $thumb, $mode, $media_only, $type, $category, $catid, $mark, $db, $prefix, $domain) {
        global $include_dir, $admin_dir, $sph_messages, $template, $template_dir, $index_id3, $limit_media;

        $orig_query     = $query;
        $starttime      = getmicrotime();
        $query          = str_replace('*', '', $query);      //      kill wildcards, as media search already includes it
        $media_results  = get_media_results($query, $url, $media_type, $all, $domain, $prefix);

        reactivate_dbuact($prefix);
        //  save info to query_log
        $endtime = getmicrotime() - $starttime;
        $rows = count($media_results);
        $time = round($endtime*100)/100;
        $ip = $_SERVER['REMOTE_ADDR'];
        //saveToLog(addslashes($orig_query), $time, $rows, $ip, 1);

        if ($media_results) {
            //  limit amount of audio+video streams presented in result listing (text + media)
            $media_results = array_slice($media_results, 0, $limit_media);
            $client_ip = @$_SERVER['REMOTE_ADDR'];
            //   display header for stream results
            include "".$template_dir."/html/160_stream-results header.html";

            while (list($key, $value) = each($media_results)) {
                $id3_array = explode("<br />",$value[12]);   //  separate ID3 and EXIF data
                $time = $id3_array[7];
                $playtime = substr($time, strrpos($time, ';;')+3);  // get play time
                if ($playtime) {
                    $minutes = $sph_messages['minutes'];
                    $playtime = "".$playtime."&nbsp;&nbsp;".$minutes."";
                }
                $title      = substr($value[5], 0, strpos($value[5], $delim));
                //      prepare current object-link for click counter
                $link_crypt  = str_replace("&", "-_-", $value[3]);    //  crypt the & character
                $link_click  = "$include_dir/media_counter.php?url=$link_crypt&amp;query=$query&amp;db=$db&amp;prefix=$prefix&amp;client_ip=$client_ip";   //  redirect users click in order to update Most Media Popular Links

                //   display  stream results
                include "".$template_dir."/html/170_stream-results.html";
            }
            //   display end of stream result table
            include "".$template_dir."/html/180_end stream-results.html";

        }
        return ($media_results);
    }

    //  if 'query' meets only media results or 'Search Media only' is selected in 'search field', enter here
    function media_only($query, $start, $media_only, $type, $category, $catid, $mark, $db, $prefix, $domain) {
        global $db_con, $mysql_table_prefix, $debug, $debug_user, $admin_dir, $include_dir, $case_sensitive;
        global $results_per_page, $image_dir, $sph_messages, $dbu_act, $template, $template_dir, $index_id3;
        global $use_cache, $mediacache_dir, $mcache_size, $max_cmresults, $max_results;
        global $dbu_act, $db1_slv, $db2_slv, $db3_slv, $db4_slv, $db5_slv, $elapsed;
        global $mytitle, $show_categories, $has_categories, $checked_cat, $tpl, $checked_all;
        global $adv, $advanced_search, $show_media, $description, $embedded;
        global $out, $xml_dir, $xml_name, $vowels, $noacc_el, $translit_el, $delim, $viking;
        global $cat_sel, $cat_sel0, $cat_sel0a, $cat_sel1, $cat_sel2, $cat_sel3, $cat_sel4, $cat_sel_all;

        //error_reporting (E_ALL);      //  for debug only
        $orig_query = $query;
        $starttime  = getmicrotime();
        $query = str_replace('*', '', $query);      //      kill wildcards, as media search already includes it

        if ($domain) {  //  prepare the mysql query for domain search
            $domain_qry = "AND link_addr like '%".$domain."%'";
        } else {
            $domain_qry = "";
        }

        if (!$category) {
            $category = '0';
        }

        if ($debug_user == '1') {
            $slv1 = '';
            $slv2 = '';
            $slv3 = '';
            $slv4 = '';
            $slv5 = '';
            if ($db1_slv == 1)  $slv1 = '1,';
            if ($db2_slv == 1)  $slv2 = '2,';
            if ($db3_slv == 1)  $slv3 = '3,';
            if ($db4_slv == 1)  $slv4 = '4,';
            if ($db5_slv == 1)  $slv5 = '5';

            echo "      <small>Results from database ".$slv1." ".$slv2." ".$slv3." ".$slv4." ".$slv5."</small>
          <br />
    ";
        }

        // if cached results should be used
        $cache_query = str_replace('"', '', $query);
        if (!$domain && $use_cache == '1' && !preg_match("/!|\/|\*|\~|#|%|<|>|\(|\)|{|}|\[|\]|\^|\\\/", $cache_query)) {
            $cache_ok = '1';
            if (!is_dir($mediacache_dir)) {
                mkdir($mediacache_dir, 0777);    //if not exist, try to create folder for media cache
                if (!is_dir($mediacache_dir)) {
                    echo "<br />Unable to create folder for media cache<br />";
                    $cache_ok = '';
                }
            }

            $no_cache = '1';
            if (is_dir($mediacache_dir)) {
                $rd_handle = fopen("".$mediacache_dir."/".$cache_query."_".$type."_".$category."_".$cat_sel0."_".$cat_sel0a."_".$cat_sel1."_".$cat_sel2."_".$cat_sel3."_".$cat_sel4.".txt", "r+b");
                if ($rd_handle) {
                    $cache_result = file_get_contents("".$mediacache_dir."/".$cache_query."_".$type."_".$category."_".$cat_sel0."_".$cat_sel0a."_".$cat_sel1."_".$cat_sel2."_".$cat_sel3."_".$cat_sel4.".txt");
                    if ($cache_result) {
                        $no_cache = '';
                        if ($debug_user == '1') {
                            echo "<small>Results found in cache</small><br />";
                        }
                        //  update cache-file with new modified date and time
                        file_put_contents("".$mediacache_dir."/".$cache_query."_".$type."_".$category."_".$cat_sel0."_".$cat_sel0a."_".$cat_sel1."_".$cat_sel2."_".$cat_sel3."_".$cat_sel4.".txt", $cache_result);
                        //  make file content readable for result listing
                        $media_results = unserialize($cache_result);
                    }
                }
                fclose($rd_handle);
            }

            //      get fresh results, because no cached result for this query available
            if ($no_cache == '1') {
                if ($debug_user == '1') {
                    echo "<small>No results found in cache.<br />Get fresh result from database.</small><br />";
                }

                $media_results = all_fresh($query, $domain_qry, $mysql_table_prefix, $catid, $prefix);
                $media_count = count($media_results);
                //      if query did not match any media object
                if ($media_count < '1'){

                    $msg = str_replace ('%query', htmlentities(utf8_decode($query)), $sph_messages["noMediaMatch"]);
                    //   display no media results found
                    include "".$template_dir."/html/200_no media found.html";
                    return('');
                }

            }
            $media_results = array_slice($media_results, 0, $max_cmresults);    //  reduce to max allowed results per query
            if ($cache_ok == '1' && $no_cache == '1' && $media_results[0][2]) {     //      create new cache file for new query input
                $wr_handle = fopen ("".$mediacache_dir."/".$cache_query."_".$type."_".$category."_".$cat_sel0."_".$cat_sel0a."_".$cat_sel1."_".$cat_sel2."_".$cat_sel3."_".$cat_sel4.".txt", "r");
                if (!$wr_handle) {     //   create new cache file for current query input
                    $result_string = serialize($media_results);
                    if ($debug_user == '1') {
                        echo "<small>Create new result files and thumbnails for media cache.</small><br />";
                    }
                    $new_handle = fopen("".$mediacache_dir."/".$cache_query."_".$type."_".$category."_".$cat_sel0."_".$cat_sel0a."_".$cat_sel1."_".$cat_sel2."_".$cat_sel3."_".$cat_sel4.".txt", "wb");
                    if (!fwrite($new_handle, $result_string)) {
                        echo "<br />Unable to write into media cache<br />";
                    }
                    fclose($new_handle);

                } else {
                    fclose($wr_handle);
                }

                //      get total size and time of creation for each cache file
                $size = '0';
                $all = array();
                $all_keys = array();
                $all_vals = array();
                if ($handle = opendir($mediacache_dir)) {
                    while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != "..") {
                            $size = $size + (filesize("".$mediacache_dir."/".$file.""));
                            $created = filemtime("".$mediacache_dir."/".$file."");
                            $all_vals[] = $file;
                            $all_keys[] = $created;
                        }
                    }
                }

                $cache_size = $mcache_size * 1048576;           //  cache size in Byte
                if ($size > $cache_size) {
                    $all = array_combine($all_keys, $all_vals);
                    ksort($all);                                //  find oldest cache file
                    $del = current($all);
                    @unlink("".$mediacache_dir."/".$del."");    // delete oldest cache file
                    if ($debug_user == '1') {
                        echo "<small>Cache overflow. Delete least significant file in cache ($del)</small><br />";
                    }

                }
                closedir($handle);
            }
        } else {    //      get fresh results without cache
            $media_results = all_fresh($query, $domain_qry, $mysql_table_prefix, $catid, $prefix);
        }

        //  limit amount of results in result listing shown for pure media search
        $media_results = array_slice($media_results, 0, $max_results, TRUE);
        //  save info to query_log
        $endtime        = getmicrotime() - $starttime;
        $media_count    = count($media_results);
        $time           = round($endtime, 3);
        $client_ip      = $_SERVER['REMOTE_ADDR'];
        $orig_query     = str_replace ("*", "", $orig_query);   //  remove wildcard character

        saveToLog(addslashes($orig_query), $time, $media_count, $client_ip, 1);

        //  if activated, prepare the XML result file
        if ($out == 'xml' && $xml_name) {
            media_xml($media_results, $media_count, $orig_query, $time);
        }

        //  single result option for wikinger-reisen.de
        if ($media_count == 1 && $viking) {
                require_once("wikinger.php");
                $search     = 1;
                $media_only = 1;
                $client_ip  = $_SERVER['REMOTE_ADDR'];
                $url        = $media_results[0][2];   //  URL of first result
                viking_option($url, $query, $search, $media_only, $category, $type, $db, $results_per_page, $prefix, $client_ip);
        }

        //      if query did not match any media object
        if ($media_count < '1'){
            //$msg = str_replace ('%query', htmlentities(utf8_decode($query)), $sph_messages["noMediaMatch"]);
            $msg = str_replace ('%query', $orig_query, $sph_messages["noMediaMatch"]);
            //   display no media results found
            include "".$template_dir."/html/200_no media found.html";
            return('');
        }

        //Prepare results for listing
        $pages  = ceil($media_count / $results_per_page);   // Calculate count of required pages
        $class  = "odrow";

        if (empty($start)) $start = '1';                // As $start is not yet defined this is required for the first result page
        if ($start == '1') {
            $from = '0';                                // Also for first page in order not to multipy with 0
        }else{
            $from = ($start-1) * $results_per_page;         // First $num_row of actual page
        }

        $to = $media_count;                             // Last $num_row of actual page
        $rest = $media_count - $start;
        if ($media_count > $results_per_page) {         // Display more then one page?
            $rest = $media_count - $from;
            $to = $from + $rest;                        // $to for last page
            if ($rest > $results_per_page) $to = $from + ($results_per_page); // Calculate $num_row of actual page
        }

        //  result listing starts here
        if ($media_count > '0') {

            $fromm = $from+1;
            $result = $sph_messages['Results'];
            $result = str_replace ('%from', $from, $result);
            $result = str_replace ('%to', $to, $result);
            $result = str_replace ('%all', $media_count, $result);
            $matchword = $sph_messages["matches"];

            if ($media_count== 1) {
                $matchword= $sph_messages["match"];
            } else {
                $matchword= $sph_messages["matches"];
            }

            //  should we show the elapsed time in header?
            if ($elapsed) {
                $result = str_replace ('%matchword', $matchword, $result);
                $result = str_replace ('%secs', $time, $result);
            } else {
                $result = '';
                if ($media_count > 1) {
                    $result = "".$sph_messages['matches']." ".$from." - ".$to." ".$sph_messages['from']." ".$media_count."" ;
                }
            }

            //  get name for valid catid
            $row = array();
            $row['category'] = '';
            if ($category != '-1') {
                $sql_query = "SELECT * from ".$mysql_table_prefix."categories
                                                where category_id = '$catid'";
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

                if ($reso->num_rows) {
                    $row = $reso->fetch_array(MYSQL_ASSOC);
                }
            }

            //   display header for media-only results
            include "".$template_dir."/html/110_media-only header.html";
            //  loop through all results
            for ($i=$from; $i<$to; $i++) {
                $this_media=$media_results[$i];
                //      prepare current object-link for media counter
                $media_crypt  = str_replace("&", "-_-", $this_media[3]);    //  crypt the & character
                $media_click  = "$include_dir/media_counter.php?url=$media_crypt&amp;query=$query&amp;db=$db&amp;prefix=$prefix&amp;client_ip=$client_ip";     //  redirect users click in order to update Most Popular Media
                //      prepare current page-link for click counter
                $link_crypt  = str_replace("&", "-_-", $this_media[2]);
                $link_click  = "$include_dir/click_counter.php?url=$link_crypt&amp;query=$query&amp;db=$db&amp;prefix=$prefix&amp;client_ip=$client_ip";       //  redirect users click in order to update Most Popular Links

                $media_title    = $this_media[5];   //  media title
                $thumb_link     = utf8_encode($this_media[4]);   //  link to thumbnail

                $i_1 = $i+1;                    //  so table output does not start with zero

                $title = array();
                $sql_query = "SELECT title from ".$mysql_table_prefix."links where link_id = ".$this_media[1]."";  //   if available get title of current page
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

                if ($result->num_rows > '0') {
                    $row        = $result->fetch_array(MYSQLI_ASSOC);
                    $page_title = $row["title"];  // presents the page title
                    $img_name   = substr($this_media[3], strrpos($this_media[3], "/")+1);
                }

                if ($class =="odrow")
                $class = "evrow";
                else
                $class = "odrow";
                //   display  media-only result listing
                include "".$template_dir."/html/120_media-only results.html";
            }
            //   display  end of result listing and links to other result pages
            include "".$template_dir."/html/190_more media-results.html";
        }
        return;
    }

    function get_media_results($query, $link, $media_type, $all, $domain, $prefix) {
        global $db_con, $dbu_act, $user_db, $db1_slv, $db2_slv, $db3_slv, $db4_slv, $db5_slv;
        global $database1, $database2, $database3, $database4, $database5;
        global $mysql_table_prefix1, $mysql_table_prefix2, $mysql_table_prefix3, $mysql_table_prefix4, $mysql_table_prefix5;
        global $mysql_host1, $mysql_host2, $mysql_host3, $mysql_host4, $mysql_host5;
        global $mysql_user1, $mysql_user2, $mysql_user3, $mysql_user4, $mysql_user5;
        global $mysql_password1, $mysql_password2, $mysql_password3, $mysql_password4, $mysql_password5;        global $db_con, $debug;

        $media_results = array();
        $valid = "1";

        if ($db1_slv == 1 && !$user_db || $user_db == 1) {
            $db_con     = db_connect($mysql_host1, $mysql_user1, $mysql_password1, $database1);
            $valid = "1";
            $found = "0";
            if ($prefix > '0' ) {       //      if requested by the Search Form, we need to use the shifted table-suffix
                $valid = "0";
                $result = $result = $db_con->query("SHOW TABLES");
                $num_rows = $result->num_rows;
                $tables = $result->fetch_array(MYSQLI_NUM);

                for ($i = 0; $i < $num_rows; $i++) {        //  the shifted table-suffix is part of this database?
                    $tables = $result->fetch_array(MYSQLI_NUM);
                    $found  = strstr($tables[0], $prefix);    //  will create a non-zero value if tablename found

                    if ($found) {
                        $valid = "1";
                    }
                }

                if ($valid) {
                    $mysql_table_prefix = $prefix;      //  replace the table suffix
                } else {
                    if ($debug_user == '1') {
                        echo "Table prefix '$prefix' does not exist in database 1 ";
                        die();
                    }
                }
            } else {
                $mysql_table_prefix = $mysql_table_prefix1;
            }

            if ($valid) { //   for standard table-suffix, or if shifted suffix is valid for this db
                $db_slv = '1';
                $media_results = thislink_media($query, $link, $media_type, $all, $domain, $db_slv, $mysql_table_prefix);
            }
        }

        if ($db2_slv == 1 && !$user_db || $user_db == 2) {
            $db_con = db_connect($mysql_host2, $mysql_user2, $mysql_password2, $database2);
            $valid = "1";
            $found = "0";
            $media_resultx = array();
            if ($prefix > '0' ) {
                $valid = "0";
                $result = $result = $db_con->query("SHOW TABLES");
                $num_rows = $result->num_rows;
                $tables = $result->fetch_array(MYSQLI_NUM);

                for ($i = 0; $i < $num_rows; $i++) {        //  the shifted table-suffix is part of this database?
                    $tables = $result->fetch_array(MYSQLI_NUM);
                    $found  = strstr($tables[0], $prefix);    //  will create a non-zero value if tablename found

                    if ($found) {
                        $valid = "1";
                    }
                }

                if ($valid) {
                    $mysql_table_prefix = $prefix;      //  replace the table suffix
                } else {
                    if ($debug_user == '1') {
                        echo "Table prefix '$prefix' does not exist in database 1 ";
                        die();
                    }
                }
            } else {
                $mysql_table_prefix = $mysql_table_prefix2;
            }
            if ($valid) {
                $db_slv = '2';
                $media_resultx = thislink_media($query, $link, $media_type, $all, $domain, $db_slv, $mysql_table_prefix);
                if ($media_results && is_array($media_resultx)) {
                    $media_results = array_merge($media_results, $media_resultx);
                } else{
                    if (is_array($media_resultx)) {
                        $media_results = $media_resultx;
                    }
                }
            }
        }

        if ($db3_slv == 1 && !$user_db || $user_db == 3) {
            $db_con = db_connect($mysql_host3, $mysql_user3, $mysql_password3, $database3);
            $valid = "1";
            $found = "0";
            $media_resultx = array();
            if ($prefix > '0' ) {
                $valid = "0";
                $result = $result = $db_con->query("SHOW TABLES");
                $num_rows = $result->num_rows;
                $tables = $result->fetch_array(MYSQLI_NUM);

                for ($i = 0; $i < $num_rows; $i++) {        //  the shifted table-suffix is part of this database?
                    $tables = $result->fetch_array(MYSQLI_NUM);
                    $found  = strstr($tables[0], $prefix);    //  will create a non-zero value if tablename found

                    if ($found) {
                        $valid = "1";
                    }
                }

                if ($valid) {
                    $mysql_table_prefix = $prefix;      //  replace the table suffix
                } else {
                    if ($debug_user == '1') {
                        echo "Table prefix '$prefix' does not exist in database 1 ";
                        die();
                    }
                }
            } else {
                $mysql_table_prefix = $mysql_table_prefix3;
            }
            if ($valid) {
                $db_slv = '3';
                $media_resultx = thislink_media($query, $link, $media_type, $all, $domain, $db_slv, $mysql_table_prefix);
                if ($media_results && is_array($media_resultx)) {
                    $media_results = array_merge($media_results, $media_resultx);
                } else{
                    if (is_array($media_resultx)) {
                        $media_results = $media_resultx;
                    }
                }
            }
        }

        if ($db4_slv == 1 && !$user_db || $user_db == 4) {
            $db_con = db_connect($mysql_host4, $mysql_user4, $mysql_password4, $database4);
            $valid = "1";
            $found = "0";
            $media_resultx = array();
            if ($prefix > '0' ) {
                $valid = "0";
                $result = $result = $db_con->query("SHOW TABLES");
                $num_rows = $result->num_rows;
                $tables = $result->fetch_array(MYSQLI_NUM);

                for ($i = 0; $i < $num_rows; $i++) {        //  the shifted table-suffix is part of this database?
                    $tables = $result->fetch_array(MYSQLI_NUM);
                    $found  = strstr($tables[0], $prefix);    //  will create a non-zero value if tablename found

                    if ($found) {
                        $valid = "1";
                    }
                }

                if ($valid) {
                    $mysql_table_prefix = $prefix;      //  replace the table suffix
                } else {
                    if ($debug_user == '1') {
                        echo "Table prefix '$prefix' does not exist in database 1 ";
                        die();
                    }
                }
            } else {
                $mysql_table_prefix = $mysql_table_prefix4;
            }
            if ($valid) {
                $db_slv = '4';
                $media_resultx = thislink_media($query, $link, $media_type, $all, $domain, $db_slv, $mysql_table_prefix);
                if ($media_results && is_array($media_resultx)) {
                    $media_results = array_merge($media_results, $media_resultx);
                } else{
                    if (is_array($media_resultx)) {
                        $media_results = $media_resultx;
                    }
                }
            }
        }

        if ($db5_slv == 1 && !$user_db || $user_db == 5) {
            $db_con = db_connect($mysql_host5, $mysql_user5, $mysql_password5, $database5);
            $valid = "1";
            $found = "0";
            $media_resultx = array();
            if ($prefix > '0' ) {
                $valid = "0";
                $result = $result = $db_con->query("SHOW TABLES");
                $num_rows = $result->num_rows;
                $tables = $result->fetch_array(MYSQLI_NUM);

                for ($i = 0; $i < $num_rows; $i++) {        //  the shifted table-suffix is part of this database?
                    $tables = $result->fetch_array(MYSQLI_NUM);
                    $found  = strstr($tables[0], $prefix);    //  will create a non-zero value if tablename found

                    if ($found) {
                        $valid = "1";
                    }
                }

                if ($valid) {
                    $mysql_table_prefix = $prefix;      //  replace the table suffix
                } else {
                    if ($debug_user == '1') {
                        echo "Table prefix '$prefix' does not exist in database 1 ";
                        die();
                    }
                }
            } else {
                $mysql_table_prefix = $mysql_table_prefix5;
            }
            if ($valid) {
                $db_slv = '5';
                $media_resultx = thislink_media($query, $link, $media_type, $all, $domain, $db_slv, $mysql_table_prefix);
                if ($media_results && is_array($media_resultx)) {
                    $media_results = array_merge($media_results, $media_resultx);
                } else{
                    if (is_array($media_resultx)) {
                        $media_results = $media_resultx;
                    }
                }
            }
        }

        return $media_results;
    }

    //  search for media files in one link
    function thislink_media($query, $link, $media_type, $all, $domain, $db_slv, $mysql_table_prefix) {
        global $db_con, $debug, $case_sensitive, $max_results, $sort_media, $translit_el;
        global $thumb_dir, $delim, $debug_user, $domain_qry;

        $media_results  = array();
        $sort           = "title";

        //  define order of result listing
        if ($sort_media == "1") {
            $sort = "title";
        }
        if ($sort_media == "2") {
            $sort = "size_x DESC, size_y DESC, title";
        }
        if ($sort_media == "3") {
            $sort = "last_query DESC, title";
        }
        if ($sort_media == "4") {
            $sort = "click_counter DESC, title";
        }
        if ($sort_media == "5") {
            $sort = "suffix DESC, title";
        }

        if ($translit_el) {
            $query = translit_el($query);
        }

        $query = $db_con-> real_escape_string($query);

        //  find all media files of this page
        if ($all =='1') {
            $sql_query = "SELECT * from ".$mysql_table_prefix."media
                                        where link_addr = '$link' AND type = '$media_type'
                                        ORDER BY $sort ";
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
        } else {
            //  search for results in title of media
            if ($case_sensitive =='0') {
                $sql_query = "SELECT * from ".$mysql_table_prefix."media
                                                where link_addr = '$link' AND type = '$media_type' AND (CONVERT(LOWER(title)USING UTF8) like '%".$query."%') $domain_qry
                                                ORDER BY $sort ";
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
            } else {
                $sql_query = "SELECT * from ".$mysql_table_prefix."media
                                            where link_addr = '$link' AND type = '$media_type' AND title like ('%".($db_con->real_escape_string($query))."%')
                                            order by $sort ";
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
            }
        }


        if ($result->num_rows == '0') {
            $media_results = '';
            return $media_results;
        }

       //  collect all results into one array
        while ($this_array = $result->fetch_array(MYSQLI_NUM)) {
            $media_results[] = $this_array;
        }

        //  limit amount of results in result listing shown per page/link for combined text and media search
        $media_results = array_slice($media_results, 0, $max_results, TRUE);

        $i = 0 ;
        $title = '';

        foreach ($media_results as $this_media[$i]) {
            if ($this_media[$i][6] == 'image') {
                $name = basename($this_media[$i][3]);   //  extract file name
                $title  = substr($this_media[$i][5], 0, strpos($this_media[$i][5], $delim));         //  get basic part of the title
                //  add folder path, db, table-prefix, remove original suffix and add own suffix
                $file = utf8_decode("".$thumb_dir."/db".$db_slv."_".$mysql_table_prefix."_".substr($name, 0, strrpos($name, ".")).".gif");

                if (!$handle = fopen($file, "ab")) {
                    if ($debug_user == '1') {
                        print "Unable to open $filename ";
                    }
                }

                if (!fwrite($handle, $this_media[$i][4])) {
                    if ($debug_user == '1') {
                        print "Unable to write the file $filename. No thumbnails will be presented";
                    }
                }
                fclose($handle);
                $this_media[$i][4] = $file; //  replace content of thumbnail  with path to thumbnail
                $this_media[$i][5] = $title;
                $i++;
            }
        }
        return $this_media;
    }

    function all_fresh($query, $domain_qry, $mysql_table_prefix, $catid, $prefix){
        global $db_con, $dbu_act, $db1_slv, $db2_slv, $db3_slv, $db4_slv, $db5_slv, $debug_user;
        global $database1, $database2, $database3, $database4, $database5;
        global $mysql_table_prefix1, $mysql_table_prefix2, $mysql_table_prefix3, $mysql_table_prefix4, $mysql_table_prefix5;
        global $mysql_host1, $mysql_host2, $mysql_host3, $mysql_host4, $mysql_host5;
        global $mysql_user1, $mysql_user2, $mysql_user3, $mysql_user4, $mysql_user5;
        global $mysql_password1, $mysql_password2, $mysql_password3, $mysql_password4, $mysql_password5;
        $res = array();
        //  get results from all involved databases
        if ($db1_slv == 1) {
            $db_con     = db_connect($mysql_host1, $mysql_user1, $mysql_password1, $database1);
            $valid = "1";
            $found = "0";
            $media_results = array();
            if ($prefix > '0' ) {       //      if requested by the Search Form, we need to use the shifted table-suffix
                $valid = "0";
                $result = $result = $db_con->query("SHOW TABLES");
                $num_rows = $result->num_rows;
                $tables = $result->fetch_array(MYSQLI_NUM);

                for ($i = 0; $i < $num_rows; $i++) {        //  the shifted table-suffix is part of this database?
                    $tables = $result->fetch_array(MYSQLI_NUM);
                    $found  = strstr($tables[0], $prefix);    //  will create a non-zero value if tablename found

                    if ($found) {
                        $valid = "1";
                    }
                }

                if ($valid) {
                    $mysql_table_prefix = $prefix;      //  replace the table suffix
                } else {
                    if ($debug_user == '1') {
                        echo "Table prefix '$prefix' does not exist in database 1 ";
                        die();
                    }
                }
            } else {
                $mysql_table_prefix = $mysql_table_prefix1;
            }

            if ($valid) {
                $db_slv = '1';   // active db
                $res = fresh_media($query, $domain_qry, $mysql_table_prefix, $catid, $db_slv);
            }

        }

        if ($db2_slv == 1) {
            $db_con = db_connect($mysql_host2, $mysql_user2, $mysql_password2, $database2);
            $valid = "1";
            $found = "0";
            if ($prefix > '0' ) {
                $valid = "0";
                $result = $result = $db_con->query("SHOW TABLES");
                $num_rows = $result->num_rows;
                $tables = $result->fetch_array(MYSQLI_NUM);

                for ($i = 0; $i < $num_rows; $i++) {    //  the shifted table-suffix is part of this database?
                    $tables = $result->fetch_array(MYSQLI_NUM);
                    $found  = strstr($tables[0], $prefix);    //  will create a non-zero value if tablename found

                    if ($found) {
                        $valid = "1";
                    }
                }

                if ($valid) {
                    $mysql_table_prefix = $prefix;      //  replace the table suffix
                } else {
                    if ($debug_user == '1') {
                        echo "Table prefix '$prefix' does not exist in database 1 ";
                        die();
                    }
                }
            } else {
                $mysql_table_prefix = $mysql_table_prefix2;
            }
            if ($valid) {
                $db_slv = '2';   // active db
                $res2 = fresh_media($query, $domain_qry, $mysql_table_prefix, $catid, $db_slv);
                $res = array_merge($res, $res2);
            }
        }

        if ($db3_slv == 1) {
            $db_con = db_connect($mysql_host3, $mysql_user3, $mysql_password3, $database3);
            $valid = "1";
            $found = "0";
            if ($prefix > '0' ) {
                $valid = "0";
                $result = $result = $db_con->query("SHOW TABLES");
                $num_rows = $result->num_rows;
                $tables = $result->fetch_array(MYSQLI_NUM);

                for ($i = 0; $i < $num_rows; $i++) {    //  the shifted table-suffix is part of this database?
                    $tables = $result->fetch_array(MYSQLI_NUM);
                    $found  = strstr($tables[0], $prefix);    //  will create a non-zero value if tablename found

                    if ($found) {
                        $valid = "1";
                    }
                }

                if ($valid) {
                    $mysql_table_prefix = $prefix;      //  replace the table suffix
                } else {
                    if ($debug_user == '1') {
                        echo "Table prefix '$prefix' does not exist in database 1 ";
                        die();
                    }
                }
            } else {
                $mysql_table_prefix = $mysql_table_prefix3;
            }
            if ($valid) {
                $db_slv = '3';   // active db
                $res3 = fresh_media($query, $domain_qry, $mysql_table_prefix, $catid, $db_slv);
                $res = array_merge($res, $res3);
            }
        }

        if ($db4_slv == 1) {
            $db_con = db_connect($mysql_host4, $mysql_user4, $mysql_password4, $database4);
            $valid = "1";
            $found = "0";
            if ($prefix > '0' ) {
                $valid = "0";
                $result = $result = $db_con->query("SHOW TABLES");
                $num_rows = $result->num_rows;
                $tables = $result->fetch_array(MYSQLI_NUM);

                for ($i = 0; $i < $num_rows; $i++) {    //  the shifted table-suffix is part of this database?
                    $tables = $result->fetch_array(MYSQLI_NUM);
                    $found  = strstr($tables[0], $prefix);    //  will create a non-zero value if tablename found

                    if ($found) {
                        $valid = "1";
                    }
                }

                if ($valid) {
                    $mysql_table_prefix = $prefix;      //  replace the table suffix
                } else {
                    if ($debug_user == '1') {
                        echo "Table prefix '$prefix' does not exist in database 1 ";
                        die();
                    }
                }
            } else {
                $mysql_table_prefix = $mysql_table_prefix4;
            }
            if ($valid) {
                $db_slv = '4';   // active db
                $res4 = fresh_media($query, $domain_qry, $mysql_table_prefix, $catid, $db_slv);
                $res = array_merge($res, $res4);
            }
        }

        if ($db5_slv == 1) {
            $db_con = db_connect($mysql_host5, $mysql_user5, $mysql_password5, $database5);
            $valid = "1";
            $found = "0";
            if ($prefix > '0' ) {
                $valid = "0";
                $result = $result = $db_con->query("SHOW TABLES");
                $num_rows = $result->num_rows;
                $tables = $result->fetch_array(MYSQLI_NUM);

                for ($i = 0; $i < $num_rows; $i++) {    //  the shifted table-suffix is part of this database?
                    $tables = $result->fetch_array(MYSQLI_NUM);
                    $found  = strstr($tables[0], $prefix);    //  will create a non-zero value if tablename found

                    if ($found) {
                        $valid = "1";
                    }
                }

                if ($valid) {
                    $mysql_table_prefix = $prefix;      //  replace the table suffix
                } else {
                    if ($debug_user == '1') {
                        echo "Table prefix '$prefix' does not exist in database 1 ";
                        die();
                    }
                }
            } else {
                $mysql_table_prefix = $mysql_table_prefix5;
            }
            if ($valid) {
                $db_slv = '5';   // active db
                $res5 = fresh_media($query, $domain_qry, $mysql_table_prefix, $catid, $db_slv);
                $res = array_merge($res, $res5);
            }
        }
        reactivate_dbuact($prefix);

        if (!is_array($res)) {
            $msg = str_replace ('%query', htmlentities(utf8_decode($query)), $sph_messages["noMediaMatch"]);

            echo "<div class='mainlist'>
                        <div class='warnadmin cntr'>$msg</div>
                    </div>
                ";
        }
        return $res;
    }

    function fresh_media($query, $domain_qry, $mysql_table_prefix, $catid, $db_slv){
        global $db_con, $case_sensitive, $debug, $category, $search_id3, $mysql_charset;
        global $sort_media, $thumb_dir, $delim, $debug_user, $use_cache, $mediacache_dir;
        global $case_sensitive, $vowels, $noacc_el, $translit_el, $greek, $type;
        global $cat_sel, $cat_sel0, $cat_sel0a, $cat_sel1, $cat_sel2, $cat_sel3, $cat_sel4, $cat_sel_all;

        $all_media  = array();
        $this_media = array();
        $sort       = "title, id3";

        if (!$category) {
            $category = '-1';
        }

        //  define order of result listing
        if ($sort_media == "1") {
            $sort = "title, id3";
        }
        if ($sort_media == "2") {
            $sort = "size_x DESC, size_y DESC, title, id3";
        }
        if ($sort_media == "3") {
            $sort = "last_query DESC, title, id3";
        }
        if ($sort_media == "4") {
            $sort = "click_counter DESC, title, id3";
        }
        if ($sort_media == "5") {
            $sort = "suffix, title";
        }

        if ($query == '')       $query = '&nbsp;';  //    prevent blank results for media search

        if ($query == 'media:') $query   = '%';      //    search for all media files in database /category

        if ($case_sensitive =='0') {
            $query = lower_case(lower_ent($query));
        }
        if ($vowels || $greek) {
            $query = remove_acc($query, '0');     //  remove Latin accents
        }
        if ($noacc_el) {
            $query = remove_acc_el($query, '0');  //  remove Greek accents
        }
        if ($translit_el) {
            $query = translit_el($query);
        }

        //  OR search, but only for multiple query words
        if ($type == "or" && strpos($query, " ")) {

            $known_id   = array();
            $all        = explode(" ", $query);     //  build an array from all query words

            foreach ($all as $query) {      //  try to get results for any query word
                if ($search_id3 == '1') {    // search in name, title, EXIF and ID3 info
                    $sql_query ="SELECT * from ".$mysql_table_prefix."media
                                                    where title like LOWER('%".($db_con->real_escape_string($query))."%') $domain_qry
                                                    OR name like LOWER('%".($db_con->real_escape_string($query))."%') $domain_qry
                                                    OR (id3 like '%".($db_con->real_escape_string($query))."%') $domain_qry
                                                    ORDER BY $sort ";
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

                } else {    //  search only in media name and title
                    $sql_query = "SELECT * from ".$mysql_table_prefix."media
                                                   where (CONVERT(LOWER(title)USING UTF8) like '%".($db_con->real_escape_string($query))."%') $domain_qry
                                                   OR (CONVERT(LOWER(name)USING UTF8) like '%".($db_con->real_escape_string($query))."%') $domain_qry
                                                   ORDER BY $sort ";
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
                }
                //  collect all results, but only for different links
                while ($row = $result->fetch_array(MYSQLI_NUM)) {
                    $new_id = $row[0];

                    if (count($all_media) > 0 ) {
                        foreach ($all_media as $this_media) {
                            if (!in_array($row[0], $known_id)) {
                                $all_media[]    = $row;    //  add the unknown media result
                                $known_id[]     = $row[0];
                            }

                        }
                    } else {
                        $all_media[]    = $row;     //  get the first media result
                        $known_id[]     = $row[0];  //  remember this media_id
                    }
                }
            }

        } else {    //  AND, PHRASE and TOL search
            $query = str_replace(" ", "%", $query);

            if ($search_id3 == '1') {    // search in name, title, EXIF and ID3 info
                $sql_query = "SELECT * from ".$mysql_table_prefix."media
                                                where title like LOWER('%".($db_con->real_escape_string($query))."%') $domain_qry
                                                OR media_link like LOWER('%".($db_con->real_escape_string($query))."%') $domain_qry
                                                OR (id3 like '%".($db_con->real_escape_string($query))."%') $domain_qry
                                                ORDER BY $sort ";
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

            } else {    //  search only in media name and title

                $sql_query = "SELECT * from ".$mysql_table_prefix."media
                                               where (CONVERT(LOWER(title)USING UTF8) like '%".($db_con->real_escape_string($query))."%') $domain_qry
                                               OR (CONVERT(LOWER(media_link)USING UTF8) like '%".($db_con->real_escape_string($query))."%') $domain_qry
                                               ORDER BY $sort ";
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
            }

            //      if query did not match any media object
            if ($result->num_rows == 0){
                return $all_media;  //      return blank array, otherwise array_merge() will not work in PHP5
            }

            //  collect all results
            while ($this_array = $result->fetch_array(MYSQLI_NUM)) {
                $all_media[] = $this_array;
            }
        }

        $fresh_media = array();
        //  if necessary, reduce to single category valid links
        if ($category != '-1') {

            while (list($key, $value) = each($all_media)) {

                $sql_query = "SELECT site_id from ".$mysql_table_prefix."links
                                                where url = '$value[2]'";
                //  get site_id corresponding to this page
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


                $site_id = $result->fetch_array(MYSQLI_NUM);

                //  check for valid catid
                $sql_query = "SELECT * from ".$mysql_table_prefix."site_category
                                                where site_id = '$site_id[0]' AND category_id ='$catid'";
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
                //  add valid link to result array
                if ($result->num_rows) {
                    $fresh_media[] = $value;
                }
            }

        } else {
            $fresh_media = $all_media;    //  no category search
        }

        if (!$cat_sel0) $cat_sel0 = $cat_sel_all;
        if (!$cat_sel1) $cat_sel1 = $cat_sel_all;
        if (!$cat_sel2) $cat_sel2 = $cat_sel_all;
        if (!$cat_sel3) $cat_sel3 = $cat_sel_all;
        if (!$cat_sel4) $cat_sel4 = $cat_sel_all;

        //  enter here for multiple category search and, if necessary, reduce results
        if ($cat_sel0 != $cat_sel_all  || $cat_sel1 != $cat_sel_all || $cat_sel2 != $cat_sel_all  || $cat_sel3 != $cat_sel_all  || $cat_sel4 != $cat_sel_all) {

            $temp_array = $fresh_media;
            $fresh_media = array();
            while (list($key, $value) = each($all_media)) {

                $cat_to_find = '1';
                //  get site_id for this link_id
                $sql_query = "SELECT site_id from ".$mysql_table_prefix."links
                                                where url = '$value[2]'";
                $res0 = $db_con->query($sql_query);
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

                $site_id = $res0->fetch_array(MYSQLI_NUM);

                //  get category_id for this site
                $sql_query = "SELECT category_id from ".$mysql_table_prefix."site_category where site_id = '$site_id[0]'";
                $res1 = $db_con->query($sql_query);
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

                $category_id = $res1->fetch_array(MYSQLI_NUM);

                //  check, whether  this category_id corresponds with cat_selector 0
                //  and try to find results for all active category selections
                if ($category_id) {

                    if ($cat_sel0 == $cat_sel_all) {
                        $cat_sel0 = "%";
                    }
                    if ($cat_sel0a == $cat_sel_all) {
                        $cat_sel0a = "%";
                    }
                    if ($cat_sel1 == $cat_sel_all) {
                        $cat_sel1 = "%";
                    }
                    if ($cat_sel2 == $cat_sel_all) {
                        $cat_sel2 = "%";
                    }
                    if ($cat_sel3 == $cat_sel_all) {
                        $cat_sel3 = "%";
                    }
                    if ($cat_sel4 == $cat_sel_all) {
                        $cat_sel4 = "%";
                    }

                    if ($cat_sel0 != "%") {
                        $sql_query = "SELECT * from ".$mysql_table_prefix."categories where category_id = '$category_id[0]' and category >= '$cat_sel0' and category <= '$cat_sel0a' and group_sel0 like '$cat_sel1' and group_sel1 like '$cat_sel2' and group_sel2 like '$cat_sel3' and group_sel3 like '$cat_sel4'";
                        $res_cat = $db_con->query($sql_query);
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
                    } else {
                        $sql_query = "SELECT * from ".$mysql_table_prefix."categories where category_id = '$category_id[0]' and group_sel0 like '$cat_sel1' and group_sel1 like '$cat_sel2' and group_sel2 like '$cat_sel3' and group_sel3 like '$cat_sel4'";
                        $res_cat =$db_con->query($sql_query);
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


                    if ($res_cat->num_rows) {
                    } else {
                        $cat_to_find = '0';
                    }
                    //  if found in all active category selctions, add the ccurrent link to the result array
                    if ($cat_to_find) {
                        $fresh_media[] = $value;
                    }
                }
            }
            //  restore the original $cat_sel_all
            $cat_sel0       = str_replace("%", $cat_sel_all, $cat_sel0);
            $cat_sel0a      = str_replace("%", $cat_sel_all, $cat_sel0a);
            $cat_sel1       = str_replace("%", $cat_sel_all, $cat_sel1);
            $cat_sel2       = str_replace("%", $cat_sel_all, $cat_sel2);
            $cat_sel3       = str_replace("%", $cat_sel_all, $cat_sel3);
            $cat_sel4       = str_replace("%", $cat_sel_all, $cat_sel4);
        }

        $i = 0 ;
        $title = '';
        $this_media = array();  //  reset, because used a second time
        if ($use_cache) {
            $thumb_dir = $mediacache_dir;       //  store new thumbnail in folder for media cache
        }

        foreach ($fresh_media as $this_media[$i]) {
            if ($this_media[$i][6] == 'image') {        //  build thumbnail to be displayed in result listing
                $name = basename($this_media[$i][3]);   //  extract file name

                $title = substr($this_media[$i][5], 0, strpos($this_media[$i][5], $delim));         //  get basic part of the title
                //  add folder path, db, table-prefix, remove original suffix and add own suffix
                $file = utf8_decode("".$thumb_dir."/db".$db_slv."_".$mysql_table_prefix."_".substr($name, 0, strrpos($name, ".")).".gif");

                if (!$handle = fopen($file, "ab")) {
                    if ($debug_user == '1') {
                        print "Unable to open $file ";
                    }
                }

                if (!fwrite($handle, $this_media[$i][4])) {
                    if ($debug_user == '1') {
                        print "Unable to write the file $file. No thumbnails will be presented";
                    }
                }
                fclose($handle);

                $this_media[$i][4] = $file;     //  replace content of thumbnail  with path to thumbnail
                $this_media[$i][5] = $title;    //  the title up to delimeter
                $i++;
            }
        }
//echo "\r\n\r\n<br>this_media Array0:<br><pre>";print_r($this_media);echo "</pre>\r\n";
        return $this_media;
    }

    function reactivate_dbuact($prefix) {
        global $dbu_act, $db1_slv, $db2_slv, $db3_slv, $db4_slv, $db5_slv;
        global $database1, $database2, $database3, $database4, $database5;
        global $mysql_table_prefix1, $mysql_table_prefix2, $mysql_table_prefix3, $mysql_table_prefix4, $mysql_table_prefix5;
        global $mysql_host1, $mysql_host2, $mysql_host3, $mysql_host4, $mysql_host5;
        global $mysql_user1, $mysql_user2, $mysql_user3, $mysql_user4, $mysql_user5;
        global $mysql_password1, $mysql_password2, $mysql_password3, $mysql_password4, $mysql_password5;
        //      re-active default db for 'Search User'
        if ($dbu_act == '1') {
            $db_con     = db_connect($mysql_host1, $mysql_user1, $mysql_password1, $database1);
            if ($prefix > '0' ) {
                $mysql_table_prefix = $prefix;
            } else {
                $mysql_table_prefix = $mysql_table_prefix1;
            }
        }

        if ($dbu_act == '2') {
            $db_con = db_connect($mysql_host2, $mysql_user2, $mysql_password2, $database2);
            if ($prefix > '0' ) {
                $mysql_table_prefix = $prefix;
            } else {
                $mysql_table_prefix = $mysql_table_prefix2;
            }
        }

        if ($dbu_act == '3') {
            $db_con = db_connect($mysql_host3, $mysql_user3, $mysql_password3, $database3);
            if ($prefix > '0' ) {
                $mysql_table_prefix = $prefix;
            } else {
                $mysql_table_prefix = $mysql_table_prefix3;
            }
        }

        if ($dbu_act == '4') {
            $db_con = db_connect($mysql_host4, $mysql_user4, $mysql_password4, $database4);
            if ($prefix > '0' ) {
                $mysql_table_prefix = $prefix;
            } else {
                $mysql_table_prefix = $mysql_table_prefix4;
            }
        }

        if ($dbu_act == '5') {
            $db_con = db_connect($mysql_host5, $mysql_user5, $mysql_password5, $database5);
            if ($prefix > '0' ) {
                $mysql_table_prefix = $prefix;
            } else {
                $mysql_table_prefix = $mysql_table_prefix5;
            }
        }
        return ($db_con);
    }
?>