<?php

    function swap_max(&$arr, $start, $domain) {
        $pos  = $start;
        $maxweight = $arr[$pos]['weight'];
        for  ($i = $start; $i< count($arr); $i++) {
            if ($arr[$i]['domain'] == $domain) {
                $pos = $i;
                $maxweight = $arr[$i]['weight'];
                break;
            }
            if ($arr[$i]['weight'] > $maxweight) {
                $pos = $i;
                $maxweight = $arr[$i]['weight'];
            }
        }
        $temp = $arr[$start];
        $arr[$start] = $arr[$pos];
        $arr[$pos] = $temp;
    }

    function sort_with_domains(&$arr) {
        $domain = -1;
        for  ($i = 0; $i< count($arr)-1; $i++) {
            swap_max($arr, $i, $domain);
            $domain = $arr[$i]['domain'];
        }
    }

    function sort_by_bestclick(&$arr) {
        $click_counter = -1;
        for  ($i = 0; $i< count($arr)-1; $i++) {
            swap_click($arr, $i, $click_counter);
            $click_counter = $arr[$i]['click_counter'];
        }
    }

    function swap_click(&$arr, $start, $click_counter) {
        $pos  = $start;
        $maxclick = $arr[$pos]['click_counter'];
        for  ($i = $start; $i< count($arr); $i++) {
            if ($arr[$i]['click_counter'] == $domain) {
                $pos = $i;
                $maxclick = $arr[$i]['click_counter'];
                break;
            }
            if ($arr[$i]['click_counter'] > $maxclick) {
                $pos = $i;
                $maxclick = $arr[$i]['click_counter'];
            }
        }
        $temp = $arr[$start];
        $arr[$start] = $arr[$pos];
        $arr[$pos] = $temp;
    }

    function cmp_val($a, $b) {
        if ($a == $b)
        return 0;
        return ($a < $b) ? -1 : 1;
    }

    function cmp_weight($a, $b) {
        if ($a['weight'] == $b['weight'])
        return 0;
        return ($a['weight'] > $b['weight']) ? -1 : 1;
    }

    function cmp_dom_dot($a, $b) {
        $dots_a = substr_count($a['domain'], ".");
        $dots_b = substr_count($b['domain'], ".");

        if ($dots_a == $dots_b)
        return 0;

        return ($dots_a < $dots_b) ? -1 : 1;
    }

    function cmp_path_dot($a, $b) {
        $path_a = preg_replace('/([^/]+)$/i', "", $a['path']);    //      get path without filename
        $path_b = preg_replace('/([^/]+)$/i', "", $b['path']);    //      get path without filename

        $dots_a = substr_count($path_a, ".");
        $dots_b = substr_count($path_b, ".");

        if ($dots_a == $dots_b)
        return 0;

        return ($dots_a < $dots_b) ? -1 : 1;
    }

    function cmp_path_slash($a, $b) {
        $path_a = preg_replace('/([^/]+)$/i', "", $a['path']);    //      get path without filename
        $path_b = preg_replace('/([^/]+)$/i', "", $b['path']);    //      get path without filename

        $slash_a = substr_count($a['path'], "/");
        $slash_b = substr_count($b['path'], "/");

        if ($slash_a == $slash_b)
        return 0;

        return ($slash_a < $slash_b) ? -1 : 1;
    }

    function addmarks($a) {
        $a = preg_replace("/[ ]+/i", " ", $a);
        $a = str_replace(" +", "+", $a);
        $a = str_replace(" ", "+", $a);
        return $a;
    }

    function makeboollist($a, $type) {
        global $entities, $stem_words, $case_sensitive, $del_secchars, $cn_seg;

        while ($char = each($entities)) {
            $a = preg_replace("/$char[0]/i", $char[1], $a);
        }
        $strictpos = strpos($a, '!'); //   if  ! is in position 0, we do have to search strict
        if ($strictpos === 0) {
        } else {
            if ($type != "phrase") {    //  delete secondary characters from query, but not for STRICT search

                $search = "1";
                $a      = del_secchars($a, $search);
            }
        }

        $a = trim($a);
        $a = preg_replace("/&quot;/i", "\"", $a);
        $returnWords = array();

        //get all phrases
        $regs = Array();
        while (preg_match("/([-]?)\"([^\"]+)\"/i", $a, $regs)) {
            if ($regs[1] == '') {
                $returnWords['+s'][] = $regs[2];
                $returnWords['hilight'][] = $regs[2];
            } else {
                $returnWords['-s'][] = $regs[2];
            }
            $a = str_replace($regs[0], "", $a);
        }

        if ($case_sensitive == 1) {
            $a = preg_replace("/[ ]+/i", " ", $a);
        } else {
            $a = preg_replace("/[ ]+/", " ", $a);
        }

        //  $a = remove_accents($a);
        $a = trim($a);
        $words = explode(' ', $a);
        if ($a=="") {
            $limit = 0;
        } else {
            $limit = count($words);
        }

        $k = 0;
        //get all words (both include and exlude)
        $includeWords = array();
        while ($k < $limit) {
            if (substr($words[$k], 0, 1) == '+') {
                $includeWords[] = substr($words[$k], 1);
                if (!ignoreWord(substr($words[$k], 1))) {
                    $returnWords['hilight'][] = substr($words[$k], 1);
                    if ($stem_words != 'none') {
                        $returnWords['hilight'][] = stem_word(substr($words[$k], 1), $type);
                    }
                }
            } else if (substr($words[$k], 0, 1) == '-') {
                $returnWords['-'][] = substr($words[$k], 1);
            } else {
                $includeWords[] = $words[$k];
                if (!ignoreWord($words[$k])) {
                    $returnWords['hilight'][] = $words[$k];
                    if ($stem_words != 'none') {
                        $returnWords['hilight'][] = stem_word($words[$k], $type);
                    }
                }
            }
            $k++;
        }

        //add words from phrases to includes
        if (isset($returnWords['+s'])) {
            foreach ($returnWords['+s'] as $phrase) {
                if ($case_sensitive == '0') {
                    $phrase = lower_ent($phrase);
                    $phrase = lower_case(preg_replace("/[ ]+/i", " ", $phrase));
                } else {
                    $phrase = preg_replace("/[ ]+/i", " ", $phrase);
                }

                $phrase = trim($phrase);
                $temparr = explode(' ', $phrase);
                foreach ($temparr as $w)
                $includeWords[] = $w;
            }
        }

        foreach ($includeWords as $word) {
            if (!($word =='')) {
                if (ignoreWord($word)) {

                    $returnWords['ignore'][] = $word;
                } else {
                    $returnWords['+'][] = $word;
                }
            }

        }
        return $returnWords;
    }

    function ignoreword($word) {
        global $common;
        global $min_word_length;
        global $index_numbers;

        if ($index_numbers == 1) {
            $pattern = "[a-z0-9]+";
        } else {
            $pattern = "[a-z]+";
        }
        if (strlen($word) < $min_word_length || ($common[$word] == 1)) {
            return 1;
        } else {
            return 0;
        }
    }

    function links_only($searchstr, $type, $possible_to_find, $db_slv) {
        global $db_con, $mysql_table_prefix, $sph_messages, $type, $mark, $case_sensitive;
        global $stem_words, $did_you_mean_enabled, $max_results, $include_dir, $vowels, $noacc_el;

        $url        = '';
        $fulltxt    = '';
        $res        = array();

        $wildcount = substr_count($searchstr['+']['0'], '*');
        if ($wildcount) {       //  ****        for * wildcard , enter here
            $searchstr['+']['0'] = str_replace('*','%', $searchstr['+']['0']);
        }

        if ($type == "tol" || $vowels == "1") {
            $searchstr['+']['0'] = remove_acc($searchstr['+']['0'], '0');
        }

        if ($type == "tol" || $noacc_el == "1") {
            $searchstr['+']['0'] = remove_acc_el($searchstr['+']['0'], '0');
        }

        $i = 1;

        if ($type == "or") {
            foreach ($searchstr['+'] as $query) {
                if ($stem_words != 'none') {
                    $query = stem_word($query, $type);
                }
                $query1 = $db_con->real_escape_string($query);

                //  build up the MySQL query for OR search
                if ($i != '1' ) {
                    if ($case_sensitive == '1') {
                        $or_query .= " or title like '%$query1%' ";
                    }else {
                        $or_query .= " or CONVERT((title)USING utf8) like '%$query1%' ";
                    }
                } else {
                    if ($case_sensitive == '1') {
                        $or_query .= " title like '%$query1%' ";
                    } else {
                        $or_query .= " CONVERT((title)USING utf8) like '%$query1%' ";
                    }
                }
                $i++;
            }

            $sql_query = "SELECT link_id, url, title from ".$mysql_table_prefix."link_details where ".$or_query."";
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
            //echo "\r\n\r\n<br /> OR num_rows: $num_rows<br />\r\n";
            if ($num_rows == 0) {
                $possible_to_find = '0';
            }
        }

        if ($type == 'and' ) {
            foreach ($searchstr['+'] as $query) {
                $query1 = $db_con->real_escape_string($query);
                //  build up the MySQL query for AND search
                if ($i != '1' ) {
                    if ($case_sensitive == '1') {
                        $and_query .= " and title like '%$query1%' ";
                    } else {
                        $and_query .= " and CONVERT((title)USING utf8) like '%$query1%' ";
                    }
                } else {
                    if ($case_sensitive == '1') {
                        $and_query .= " title like '%$query1%' ";
                    } else {
                        $and_query .= " CONVERT((title)USING utf8) like '%$query1%' ";
                    }
                }
                $i++;
            }

            $sql_query = "SELECT link_id, url, title, domain from ".$mysql_table_prefix."link_details where ".$and_query."";
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
            //echo "\r\n\r\n<br /> AND num_rows: $num_rows<br />\r\n";
            if ($num_rows == 0) {
                $possible_to_find = '0';
            }
        }

        if ($type == "phrase" || $type == "tol" || $vowels == "1" || $noacc_el == "1"  ) {
            foreach ($searchstr['+'] as $query) {
                $phrase .= $query." ";
                if ($type == "tol" || $vowels == "1" ) {
                    $phrase = remove_acc($phrase, '0');
                }
                if ($type == "tol" || $noacc_el == "1" ) {
                    $phrase = remove_acc_el($phrase, '0');
                }
            }

            $phrase1 = trim($db_con->real_escape_string($phrase));
            $sql_query = "SELECT link_id, url, title from ".$mysql_table_prefix."link_details where CONVERT((title)USING utf8) like '%$phrase1%'";
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
            //echo "\r\n\r\n<br /> Phrase-Tol num_rows: $num_rows<br />\r\n";
            if ($num_rows == 0) {
                $possible_to_find = '0';
            }
        }

        if ($possible_to_find == '1') {

            if ($mark == 'markbold') {
                $highlight = "span class=\"mak_1\"";
            }
            if ($mark == 'markblue') {
                $highlight = "span class=\"mak_2\"";
            }
            if ($mark == 'markyellow') {
                $highlight = "span class=\"mak_3\"";
            }
            if ($mark == 'markgreen') {
                $highlight = "span class=\"mak_4\"";
            }
            if ($mark == 'markred') {
                $highlight = "span class=\"mak_5\"";
            }
            $i = 0;
            for ($i = 0; $i < $max_results && $row = $result->fetch_array(MYSQLI_NUM); $i++) {

                $sql_query = "SELECT * from ".$mysql_table_prefix."links where link_id like '$row[0]'";
                $page_res = $db_con->query($sql_query);
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
                $page_row = $page_res->fetch_array(MYSQLI_NUM);
                $page_title = $page_row[3];   //    get title of page that contains this new result (link)

                $title = " ".$row[2]." ";       //  free the title of this new result (link)

                foreach($searchstr['hilight'] as $change) {
                    $title  = highlight($title, $change, $highlight);   //  mark all searchwords found in this link text
                }

                //      prepare this link-url for our click counter
                $url_crypt  = str_replace("&", "-_-", $row[1]);    //  crypt the & character
                $url_click  = "$include_dir/click_counter.php?url=$url_crypt&amp;query=$query&amp;db=$db_slv&amp;prefix=$mysql_table_prefix";   //  redirect users click in order to update Most Popular Links

                $fulltxt  = "<br />Link: <a href=\"".$url_click."\" target =top>".$title."</a><br /><br />";

                //  now build up the result array
                $res[$i]['title']           = stripslashes($page_row[3]);
                $res[$i]['url']             = stripslashes($page_row[2]);
                $res[$i]['fulltxt']         = $fulltxt;
                $res[$i]['size']            = $page_row[7];
                $res[$i]['click_counter']   = $page_row[11];
                $res[$i]['weight']          = "100";
                $res[$i]['domain']          = $row[3];
                $urlparts = parse_url($res[$i]['url']);
                //$res[$i]['path'] = $urlparts['path'];    //      get full path
                $res[$i]['path']            = preg_replace('/([^\/]+)$/i', "", $urlparts['path']);    //      get path without filename
                $res[$i]['maxweight']       = "100";
                $res[$i]['results']         = $num_rows;
                $res[$i]['db']              = $db_slv;      //  all these results are from db (the currently active db)


            }

            if ($clear == 1) {
                unset ($fulltxt, $title);
            }
            //echo "\r\n\r\n<br>res Array:<br><pre>";print_r($res);echo "</pre>";
            return $res;

        } else {    //  if nothing found, try 'Did you mean'
            if ($possible_to_find == 0 && $did_you_mean_enabled == 1) {
                reset ($searchstr['+']);
                foreach ($searchstr['+'] as $word) {
                    $word2 = str_ireplace("Ã", "à", addslashes("$word"));
                    $sql_query = "SELECT keyword from ".$mysql_table_prefix."keywords where soundex(keyword) = soundex('$word2%')";
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
                    $max_distance = 100;
                    $near_word ="";
                    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                        $distance = levenshtein($row["keyword"], $word);
                        if ($distance < $max_distance && $distance <10) {
                            $max_distance = $distance;
                            $near_word = ($row[0]);
                        }
                    }

                    if ($near_word != "" && $word != $near_word) {
                        $near_words[$word] = $near_word;
                    }
                }

                if ($wildcount == '0' && $near_words != "") {   //   No 'Did you mean' for wildcount search
                    $res['did_you_mean'] = $near_words;
                    return $res;
                }
            }
        }
    }

    function search($searchstr, $category, $start, $per_page, $type, $domain, $prefix) {
        global $db_con, $mysql_table_prefix, $show_meta_description, $sort_results, $all_wild;
        global $stem_words, $did_you_mean_enabled, $relevance, $query, $clear, $greek, $translit_el, $noacc_el;
        global $wildcount, $case_sensitive, $debug, $use_cache, $max_ctresults, $dom_count, $out;
        global $dbu_act, $db1_slv, $db2_slv, $db3_slv, $db4_slv, $db5_slv;
        global $database1, $database2, $database3, $database4, $database5;
        global $mysql_table_prefix1, $mysql_table_prefix2, $mysql_table_prefix3, $mysql_table_prefix4, $mysql_table_prefix5;

        //  collect results from all involved databases
        $res = array();

        //  search for query input
        $res = search_dbs($searchstr, $category, $start, $per_page, $type, $domain, $prefix, $res);
        //echo "\r\n\r\n<br>res Array:<br><pre>";print_r($res);echo "</pre>\r\n";

        if ($res['did_you_mean']){
            return $res;
        }

        if (count($res) == 0) {
            return null;
        }

        $all = count($res);
        if ($domain) {
            $sort_results = '1';            // overwriite Admin settings, as for search in one domain, we need all results in relevance order
        }

        if ($sort_results != '3') {
            usort($res, "cmp_weight");      //  standard output sorted by relevance (weight)
        }

        //if (($sort_results == '4'  && $domain_qry == "" ) || $sort_results == '3') {    //  output alla Google  OR  by domain name
        if ($sort_results == '4'  && $domain_qry == "" ) {    //  output alla Google
            sort_with_domains($res);
        } else {
            if ($sort_results == '2') {             //      enter here if 'Main URLs' on top of listing
                usort($res, "cmp_dom_dot");         //      sort domains without dots on top
                usort($res, "cmp_path_slash");      //      sort minimal slashes on top
            }

            if ($sort_results == '5') {             //      enter here if 'Most Popular Click' on top of listing
                sort_by_bestclick($res);
            }
        }

        //  limit number of results per domain if Admin defined
        if ($dom_count && $sort_results == '3'){
            $i = '0';
            $known_domain = $res[0]['domain'];      //  first known domain
            foreach($res as &$v) {
                $domain = $v['domain'];             //  fetch actual domain from result array
                if ($known_domain == $domain && $i < $dom_count) {
                    $dom_res[] = $v;               //  build new result array
                    $i++;
                } else {    //  no more results from known domain or counter maximum reached
                    if ($known_domain != $domain) { // fetched another domain in result array
                        $known_domain = $domain;
                        $dom_res[] = $v;            //  add first result of new domain
                        $i = '1';
                    }
                }
            }
            $res = $dom_res;
        }

        $results = count ($res);  //  total amount of results
        //  limit result count to limit of text-cache
        if ($use_cache == '1') {
            if($results > $max_ctresults) {
                $results = $max_ctresults;
                $res = array_slice($res, 0, $max_ctresults);
            }
        }

        /*
         *   in case that full (all) text results should be stored in XML output file,
         *   uncomment next 3 rows and comment the row
         *   convert_xml($xml_result, 'text');
         *   in function 'get_text_results'

         if ($out == 'xml') {
         text_xml($res, count($res), $searchstr);
         }
         */
        //  reduce results for one page in result listing
        $offset = ($start-1)*$per_page;
        $res = array_slice($res, $offset, $per_page);

        $res['maxweight'] = $res[0]['maxweight'];
        $res['results'] = $results;
        $res['hilight'] = $searchstr['hilight'];
//echo "<br>res Array complete:<br><pre>";print_r($res);echo "</pre>";
        return $res;
    }

    function search_dbs($searchstr, $category, $start, $per_page, $type, $domain, $prefix, $res) {
        global $db_con, $mysql_table_prefix, $show_meta_description, $sort_results;
        global $stem_words, $did_you_mean_enabled, $relevance, $query, $clear, $max_results;
        global $wildcount, $type, $case_sensitive, $debug, $debug_user, $use_cache, $max_ctresults;
        global $dbu_act, $user_db, $db1_slv, $db2_slv, $db3_slv, $db4_slv, $db5_slv;
        global $database1, $database2, $database3, $database4, $database5;
        global $mysql_table_prefix1, $mysql_table_prefix2, $mysql_table_prefix3, $mysql_table_prefix4, $mysql_table_prefix5;
        global $mysql_host1, $mysql_host2, $mysql_host3, $mysql_host4, $mysql_host5;
        global $mysql_user1, $mysql_user2, $mysql_user3, $mysql_user4, $mysql_user5;
        global $mysql_password1, $mysql_password2, $mysql_password3, $mysql_password4, $mysql_password5;

        $yet_results = "";  //  predefined: up to now no results were found

        if ($db1_slv == 1 && !$user_db || $user_db == 1) {    //  as defined in Admin's Database Management settings or by user overwritten
            $db_con = db_connect($mysql_host1, $mysql_user1, $mysql_password1, $database1);
            $valid = '1';

            if ($prefix > '0' ) {       //      if requested by the Search Form, we need to use the shifted table-suffix
                $valid = '';
                $result = $db_con->query("SHOW TABLES");
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
                $mysql_table_prefix = $mysql_table_prefix1; //  use default suffix for this db
            }

            if ($valid) { //   for standard table-suffix, or if shifted suffix is valid for this db
                $db_slv = '1';   // get results from this db
                $res = slave_search ($searchstr, $category, $domain, $mysql_table_prefix, $start, $per_page, $db_slv, $type, $yet_results);
            }
        }

        if ($db2_slv == 1 && !$user_db || $user_db == 2) {
            $db_con = db_connect($mysql_host2, $mysql_user2, $mysql_password2, $database2);
            $valid = '1';

            if ($prefix > '0' ) {
                $valid = '';
                $result = $db_con->query("SHOW TABLES");
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
                        echo "Table prefix '$prefix' does not exist in database 2 ";
                        die();
                    }
                }
            } else {
                $mysql_table_prefix = $mysql_table_prefix2; //  use default suffix for this db
            }

            if ($valid) {

                $db_slv = '2';   // active db
                if ($res) {
                    $yet_results = "1"; //  if result (or 'did you mean') was aready found
                }

                $res2 = slave_search ($searchstr, $category, $domain, $mysql_table_prefix, $start, $per_page, $db_slv, $type, $yet_results);
                $res = array_merge($res, $res2);
            }
        }

        if ($db3_slv == 1 && !$user_db || $user_db == 3) {
            $db_con = db_connect($mysql_host3, $mysql_user3, $mysql_password3, $database3);
            $valid = '1';

            if ($prefix > '0' ) {
                $valid = '';
                $result = $db_con->query("SHOW TABLES");
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
                        echo "Table prefix '$prefix' does not exist in database 3 ";
                        die();
                    }
                }
            } else {
                $mysql_table_prefix = $mysql_table_prefix3; //  use default suffix for this db
            }
            if ($valid) {
                $db_slv = '3';   // active db
                if ($res) {
                    $yet_results = "1"; //  if result (or 'did you mean') was aready found
                }
                $res3 = slave_search ($searchstr, $category, $domain, $mysql_table_prefix, $start, $per_page, $db_slv, $type, $yet_results);
                $res = array_merge($res, $res3);
            }
        }

        if ($db4_slv == 1 && !$user_db || $user_db == 4) {
            $db_con =db_connect($mysql_host4, $mysql_user4, $mysql_password4, $database4);
            $valid = '1';

            if ($prefix > '0' ) {
                $valid = '';
                $result = $db_con->query("SHOW TABLES");
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
                        echo "Table prefix '$prefix' does not exist in database 4 ";
                        die();
                    }
                }
            } else {
                $mysql_table_prefix = $mysql_table_prefix4; //  use default suffix for this db
            }

            if ($valid) {
                $db_slv = '4';   // active db
                if ($res) {
                    $yet_results = "1"; //  if result (or 'did you mean') was aready found
                }
                $res4 = slave_search ($searchstr, $category, $domain, $mysql_table_prefix, $start, $per_page, $db_slv, $type, $yet_results);
                $res = array_merge($res, $res4);
            }
        }

        if ($db5_slv == 1 && !$user_db || $user_db == 5) {
            $db_con = db_connect($mysql_host5, $mysql_user5, $mysql_password5, $database5);
            $valid = '1';

            if ($prefix > '0' ) {
                $valid = '';
                $result = $db_con->query("SHOW TABLES");
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
                        echo "Table prefix '$prefix' does not exist in database 5 ";
                        die();
                    }
                }
            } else {
                $mysql_table_prefix = $mysql_table_prefix5; //  use default suffix for this db
            }
            if ($valid) {
                $db_slv = '5';  // active db
                if ($res) {
                    $yet_results = "1"; //  if result (or 'did you mean') was aready found
                }
                $res5 = slave_search ($searchstr, $category, $domain, $mysql_table_prefix, $start, $per_page, $db_slv, $type, $yet_results);
                $res = array_merge($res, $res5);
            }
        }

        //      re-activate database of actual 'Search User'
        if ($dbu_act == '1') {
            $db_con = db_connect($mysql_host1, $mysql_user1, $mysql_password1, $database1);
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
        $res = array_slice($res, 0, $max_results, TRUE);
//echo "\r\n\r\n<br>res Array1:<br><pre>";print_r($res);echo "</pre>\r\n";
        return $res;
    }

    function slave_search($searchstr, $category, $domain, $mysql_table_prefix, $start, $per_page, $db_slv, $type, $yet_results) {
        global $db_con, $show_meta_description, $sort_results, $stem_words, $did_you_mean_enabled, $relevance, $index_meta_description;
        global $wildcount, $case_sensitive, $debug, $max_results, $clear, $only_links, $vowels, $noacc_el, $translit_el, $mb ;
        global $cat_sel, $cat_sel0, $cat_sel0a, $cat_sel1, $cat_sel2, $cat_sel3, $cat_sel4, $cat_sel_all ;

        $domain_qry         = "";
        $possible_to_find   = 1;
        $notlist            = array();
        $domains            = array();

        //  if domain is a numeric
        if ($domain > 0 && is_numeric($domain)) {
            $domain_qry = "and domain = ".$domain;  //  limit the query on one domain
        }

        //  if domain is already decoded as a 'name'
        if(!is_numeric($domain) && !$domain_qry) {
            $sql_query = "SELECT * from ".$mysql_table_prefix."domains where domain = '$domain'";
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
                $thisrow = $result->fetch_array(MYSQLI_NUM);
                $domain_qry = "and domain = ".$thisrow[0];
            }
        }

        //find all sites that should not be included in the result
        if (count($searchstr['+']) == 0) {
            return $notlist;
        }
        $wordarray = $searchstr['-'];
        $not_words = 0;

        while ($not_words < count($wordarray)) {
            if ($stem_words != 'none') {
                $searchword = addslashes(stem_word($wordarray[$not_words], $type));
            } else {
                $searchword = addslashes($wordarray[$not_words]);
            }

            $sql_query1 = "SELECT link_id from ".$mysql_table_prefix."link_keyword, ".$mysql_table_prefix."keywords where ".$mysql_table_prefix."link_keyword.keyword_id= ".$mysql_table_prefix."keywords.keyword_id and keyword='$searchword'";
            $result =$db_con->query($sql_query1);

            if ($result->num_rows) {
	            while ($row = $result->fetch_array(MYSQLI_NUM)) {
	                $notlist[$not_words]['id'][$row[0]] = 1;
	            }
        	}
            $not_words++;
        }
//echo "\r\n\r\n<br>searchstr Array:<br><pre>";print_r($searchstr);echo "</pre>\r\n";

        //find all sites containing the search PHRASE
        $wordarray      = $searchstr['+s'];;
        $phrase_words   = 0;
        $phraselist     = '';
        if ($type == "phrase") {
            while ($phrase_words < count($wordarray)) {

                $searchword = addslashes($wordarray[$phrase_words]);
                $phrase_query = $searchword;    //  remember this for highlighting
                //echo "\r\n\r\n<br /> PHRASE searchword: $searchword<br />\r\n";
                if ($vowels) {      //	replace Latin vowels with wildcard letters
                    $searchword = rep_latvowels(remove_acc($searchword, '0'));
                }
/*
                 if ($translit_el) {  //  translit to Greek letters
                 $searchword = translit_el($searchword);
                 }
 */
                if ($noacc_el) {   //	replace Greek vowels with wildcard letters
                    $searchword = rep_elvowels(remove_acc_el($searchword, '0'));
                }

                //  search for phrase in fulltext
                if ($case_sensitive =='1') {
                    $sql_query1 = "SELECT link_id from ".$mysql_table_prefix."links where fulltxt like '%$searchword%'";
                } else {
                    $sql_query1 = "SELECT link_id from ".$mysql_table_prefix."links where CONVERT(LOWER(fulltxt)USING utf8) like '%$searchword%'";
                }

                $result = $db_con->query($sql_query1);
                $num_rows = $result->num_rows;
                //echo "\r\n\r\n<br /> num_rows: $num_rows<br />\r\n";
                if ($num_rows == 0 && !$only_links) {
                    //      phrase not found in fulltext. Now try to find in title tag. But not for 'only link search'
                    if ($case_sensitive =='1') {
                        $sql_query1 = "SELECT link_id from ".$mysql_table_prefix."links where title like '%$searchword%'";
                    }

                    if ($case_sensitive =='0') {
                        $searchword = lower_case($searchword);
                        $sql_query1 = "SELECT link_id from ".$mysql_table_prefix."links where CONVERT(LOWER(title)USING utf8) like '%$searchword%'";
                    }

                    $result     = $db_con->query($sql_query1);
                    $num_rows   = $result->num_rows;

                    if ($num_rows == 0 && $index_meta_description == 1) {
                        //      phrase not found in title tag . Now try to find in dexcription tag.
                        if ($case_sensitive =='1') {
                            $query1 = "SELECT link_id from ".$mysql_table_prefix."links where description like '%$searchword%'";
                        }

                        if ($case_sensitive =='0') {
                            $searchword = lower_case($searchword);
                            $sql_query1 = "SELECT link_id from ".$mysql_table_prefix."links where CONVERT(LOWER(description)USING utf8) like '%$searchword%'";
                        }
                        $result = $db_con->query($sql_query1);
                        $num_rows = $result->num_rows;

                    }

                    if ($num_rows == 0) {
                        $possible_to_find = 0;
                        break;
                    }
                }
                if ($result->num_rows) {
	                while ($row = $result->fetch_array(MYSQLI_NUM)) {
	                    $value =$row[0];
	                    $phraselist[$phrase_words]['id'][$row[0]] = 1;
	                    $phraselist[$phrase_words]['val'][$row[0]] = $value;
	                }
                }
                $phrase_words++;
            }
        }
//echo "\r\n\r\n<br>phraselist:<br><pre>";print_r($phraselist);echo "</pre>\r\n";
        if (($category> 0) && $possible_to_find==1) {
            $allcats = get_cats($category);
            $catlist = implode(",", $allcats);

            $sql_query1 = "SELECT link_id from ".$mysql_table_prefix."links, ".$mysql_table_prefix."sites, ".$mysql_table_prefix."categories, ".$mysql_table_prefix."site_category
                                                WHERE ".$mysql_table_prefix."links.site_id = ".$mysql_table_prefix."sites.site_id and ".$mysql_table_prefix."sites.site_id = ".$mysql_table_prefix."site_category.site_id and ".$mysql_table_prefix."site_category.category_id in ($catlist)";
            $result     = $db_con->query($sql_query1);
            $num_rows   = $result->num_rows;

            if (!$num_rows) {
                $possible_to_find = 0;
            } else {
	            while ($row = $result->fetch_array(MYSQLI_NUM)) {
	                $category_list[$row[0]] = 1;
	            }
            }
        }

        //  if selected, search only links as full text and present them
        if ($only_links) {
            $res = links_only($searchstr, $type, $possible_to_find, $db_slv);
            return $res;

        }

        $wordarray  = $searchstr['+'];
        $words      = 0;
        $searchword = addslashes($wordarray[$words]);   //  get only first word of search query
        $strictpos  = strpos($searchword, '!');         //   if  ! is in position 0, we have to search strict

//    ****        for 'STRICT search' enter here
        if ($strictpos === 0) {
            $searchword = str_replace('!', '', $searchword);    //  remove the strict directive from query

            $sql_query  = "SELECT keyword_id, keyword from ".$mysql_table_prefix."keywords
                                   WHERE keyword = '$searchword'";
            $result     = $db_con->query($sql_query);
            $num_rows   = $result->num_rows;

            if (!$num_rows) {   // if there was no searchword in table keywords
                $possible_to_find = 0;
                $break = 1;
            } else {
                // get all searchwords as keywords from table keywords
                $reso = $result->fetch_array(MYSQLI_ASSOC);

                $keyword_id = $reso["keyword_id"];
                $keyword    = $reso["keyword"];
                $keyword    = str_replace("'", "\\'", $keyword);       //  replace backslash as during index created for MySQL database required

                if ($sort_results == '7') {   //      get query hit results
                    $sql_query1 = "SELECT distinct * from ".$mysql_table_prefix."link_keyword, ".$mysql_table_prefix."keywords where ".$mysql_table_prefix."link_keyword.keyword_id= ".$mysql_table_prefix."keywords.keyword_id and keyword='$searchword' $domain_qry order by hits desc";
                } else {                    // get weight results
                    $sql_query1 = "SELECT * from ".$mysql_table_prefix."link_keyword  where keyword_id = '$keyword_id' order by weight desc";
                }
                $reso   = $db_con->query($sql_query1);
                $lines  = $reso->num_rows;

                $indx = $words;
                if ($lines != 0) {
                    while ($row = $reso->fetch_array(MYSQLI_ASSOC)) {
//echo "\r\n\r\n<br>row Array:<br><pre>";print_r($row);echo "</pre>\r\n";
                        $linklist[$indx]['id'][] = $row["link_id"];
                        $domains[$row["link_id"]] = $row["domain"];

                        if ($sort_results == '6') {
                            $linklist[$indx]['weight'][] = $row["indexdate"];   //  use indexdate
                        } else {
                            $linklist[$indx]['weight'][] = $row["weight"];      //  use weight
                        }

                        if ($sort_results == '7') {   //      ensure that result is also available in full text
                            $sql_query = "SELECT * FROM ".$mysql_table_prefix."links where link_id = '".$row["link_id"]."'";
                            $result = $db_con->query($sql_query);
                            $txt_res = $result->fetch_array(MYSQLI_ASSOC);
                            $full_txt = $txt_res["fulltxt"];          //       get fulltxt  of this link ID

                            if ($case_sensitive == '0') {
                                $full_txt= lower_ent($full_txt);
                                $full_txt = lower_case($full_txt);
                            }

                            $foundit = strpos($full_txt, $searchword);  //      get first hit
                            if ($foundit) {
                                $page_hits = $linklist[$indx]['weight'][$row["link_id"]] ;
                                $i = '0';

                                while ($i < $page_hits) {       //      find out if all results in full text are really strict
                                    $found_in = strpos($full_txt, $searchword);
                                    $tmp_front = substr($full_txt, $found_in-1, 20); //  one character before found match position
                                    $pos = $found_in+strlen($searchword);
                                    $tmp_behind = substr($full_txt, $pos, 20); //  one character behind found match position
                                    $full_txt = substr($full_txt, $pos);  //  get rest of fulltxt
                                    //  check whether found match is realy strict
                                    $found_before = preg_match("/[(a-z)-_*.\/\:&@\w]/", substr($tmp_front, 0, 1));
                                    $found_behind = preg_match("/[(a-z)-_*.,\/\:&@\w]/", substr($tmp_behind, 0, 1));

                                    if ($found_before == 1 || $found_behind == 1) {          //      correct count of hits
                                        $linklist[$indx]['weight'] = $linklist[$indx]['weight'] - 1;
                                    }
                                    $i++;
                                }
                            } else {
                                $linklist[$indx]['weight']  = '0';  //      nothing found in full text. Hits = 0
                            }
                        }
                    }
                }
                $indx++;
            }
        } else {    //****       if not strict-search try here
            $wild_correct = 0;
            $wildcount = substr_count($searchword, '*');
//****       for ' * WILDCARD search' enter  here
            if ($wildcount) {
                $searchword = str_replace('*','%', $searchword);
                $words = '0';

                $sql_query  = "SELECT * from ".$mysql_table_prefix."keywords
                                       WHERE keyword like '$searchword'";
                $result     = $db_con->query($sql_query);
                $num_rows   = $result->num_rows;

                if (!$num_rows) {   // if there was no searchword in table keywords
                    $possible_to_find = 0;
                    $break = 1;
                } else {
                     while ($this_array = $result->fetch_array(MYSQLI_ASSOC)) {

                        $keyword_id = $this_array["keyword_id"];
                        $keyword    = $this_array["keyword"];

                        if ($sort_results == '7') {   //      get query hit results
                            $sql_query1 = "SELECT * from ".$mysql_table_prefix."link_keyword  where keyword_id = '$keyword_id' order by hits desc";
                        } else {                    // get weight results
                            $sql_query1 = "SELECT * from ".$mysql_table_prefix."link_keyword  where keyword_id = '$keyword_id' order by weight desc";
                        }
                        $result     = $db_con->query($sql_query);
                        $num_rows   = $result->num_rows;


                        if (!$num_rows) {   // if there was no searchword in table keywords
                            $possible_to_find = 0;
                            $break = 1;
                        } else {
                            global $all_wild;
                            $all_wild = '';
                            while ($this_array = $result->fetch_array(MYSQLI_ASSOC)) {
                                $all[] = $this_array;
                            }
                            for ($i=0; $i<$num_rows; $i++) {        // get all searchwords as keywords from table keywords
                                $keyword_id = $all[$i]["keyword_id"];
                                $keyword    = $all[$i]["keyword"];

                                $all_wild   = "$all_wild $keyword";

                                $sql_query1 = "SELECT * from ".$mysql_table_prefix."link_keyword  where keyword_id = '$keyword_id' order by hits desc";
                                $reso = $db_con->query($sql_query1);
                                $lines = $reso->num_rows;

                                if ($lines == 0) {
                                    if ($type != "or") {
                                        $possible_to_find = 0;
                                        break;
                                    }
                                }
                                if ($type == "or" && $sort_results == '7') {
                                    $indx = 0;
                                } else {
                                    $indx = $words;
                                }

                                while ($row = $reso->fetch_array(MYSQLI_ASSOC)) {
                                    $linklist[$indx]['id'][] = $row["link_id"];
                                    $domains[$row["link_id"]] = $row["domain"];

                                    if ($sort_results == '6') {
                                        $linklist[$indx]['weight'][$row["link_id"]] = $row["indexdate"];      //  use indexdate
                                    } else {
                                        $linklist[$indx]['weight'][$row["link_id"]] = $row["weight"];      //  use weight
                                    }


                                    if ($sort_results == '7') {   //      ensure that result is also available in fulltxt

                                        $searchword = str_replace("%", '', $searchword);
                                        $sql_query  = "SELECT * FROM ".$mysql_table_prefix."links where link_id = '".$row["link_id"]."'";
                                        $txt_res    = $db_con->query($sql_query);

                                        $row = $txt_res->fetch_array(MYSQLI_ASSOC);

                                        $full_txt = $row["fulltxt"];          //       get fulltxt  of this link ID
                                        if ($case_sensitive == '0') {
                                            $full_txt= lower_ent($full_txt);
                                            $full_txt = lower_case($full_txt);
                                        }

                                        $pureword = str_replace('%','', $searchword);
                                        $foundit = substr_count($full_txt, $pureword);
                                        $linklist[$indx]['weight'][$row["link_id"]] = $foundit;     //  count of hits

                                        if (!$foundit) {
                                            $linklist[$indx]['weight'][$row["link_id"]] = '0';  //      nothing found in full text. Hits = 0
                                        }
                                    }
                                }
                            }
                            $words++;
                        }
                    }
                }
            } else {
//****       for 'TOLERANT search' enter here
                if ($type == 'tol') {
                    $searchword = remove_acc($searchword, '0');
                    if ($noacc_el) {
                        $searchword = remove_acc_el($searchword, '0');
                    }

                    //echo "\r\n\r\n<br /> TOLERANT searchword: $searchword<br />\r\n";
                    $sql_query  = "SELECT * from ".$mysql_table_prefix."keywords where keyword like '$searchword'";
                    $result     = $db_con->query($sql_query);
                    $num_rows   = $result->num_rows;

                    if (!$num_rows) {   // if there was no searchword in table keywords
                        $possible_to_find = 0;
                        $break = 1;
                    } else {
                        global $all_wild;
                        $all_wild = '';

                        while ($all = $result->fetch_array(MYSQLI_ASSOC)) {

                                $keyword_id = $all["keyword_id"];
                                $keyword    = $all["keyword"];

                                $accept = '1';
                                //      hopefully the PHP multibyte extention is available; otherwise use all results
                                if (function_exists(mb_strlen)) {
                                    if (mb_strlen($keyword) != mb_strlen($searchword)){     //  use only those results with same length as searchword
                                        $accept = '0';
                                    }
                                }

                                if ($accept == '1') {
                                    $all_wild .= $keyword;

                                    if ($sort_results == '7') {   //      get query hit results
                                        $sql_query1 = "SELECT * from ".$mysql_table_prefix."link_keyword where keyword_id = '$keyword_id' order by hits desc";
                                    } else {                    // get weight results
                                        $sql_query1 = "SELECT * from ".$mysql_table_prefix."link_keyword where keyword_id = '$keyword_id' order by weight desc";
                                    }
                                    $reso   = $db_con->query($sql_query1);
                                    $lines  = $reso->num_rows;

                                    if ($lines != 0) {
                                        $indx =$words;
                                    }

                                    while ($row = $reso->fetch_array(MYSQLI_ASSOC)) {
                                        $linklist[$indx]['id'][] = $row["link_id"];
                                        $domains[$row["link_id"]] = $row["domain"];

                                        if ($sort_results == '6') {
                                            $linklist[$indx]['weight'][$row["link_id"]] = $row["indexdate"];    //  use indexdate
                                        } else {
                                            $linklist[$indx]['weight'][$row["link_id"]] = $row["weight"];      //  use weight
                                        }

                                    }
                                    //$words++;
                                }

                            $words++;
                        }
                    }

                } else {
//      *******  finally 'STANDARD search'
                    $words = 0;
                    while (($words < count($wordarray)) && $possible_to_find == 1) {
                        if ($stem_words != 'none') {
                            $searchword = addslashes(stem_word($wordarray[$words], $type));
                        } else {
                            $searchword = addslashes($wordarray[$words]);
                        }

                        $sql_query1 = "SELECT distinct * from ".$mysql_table_prefix."link_keyword, ".$mysql_table_prefix."keywords where ".$mysql_table_prefix."link_keyword.keyword_id= ".$mysql_table_prefix."keywords.keyword_id and keyword='$searchword' $domain_qry order by hits desc";
                        $result     = $db_con->query($sql_query1);
                        $num_rows   = $result->num_rows;

                        if ($type == "or" && $sort_results == '7') {
                            $indx = 0;
                        } else {
                            $indx = $words;
                        }

                        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                            $linklist[$indx]['id'][] = $row["link_id"];
                            $domains[$row["link_id"]] = $row["domain"];

                            if ($sort_results == '6') {     //  6 = sort results 'By index date'
                                $linklist[$indx]['weight'][$row["link_id"]] = $row["indexdate"];      //  use indexdate
                            } else {
                                $linklist[$indx]['weight'][$row["link_id"]] = $row["weight"];      //  use weight
                            }

                            if ($sort_results == '7') {   //     7= sort results 'By hit counts in full text'  => ensure that result is also available in fulltxt
                                if ($type == 'phrase') {
                                    $searchword = $phrase_query;
                                }
                                $linklist[$indx]['weight'][$row["link_id"]] = '0';
                                $sql_query1 = "SELECT * FROM ".$mysql_table_prefix."links where link_id = '$row[0]'";
                                $reso = $db_con->query($sql_query1);
                                $txt_res = $reso->fetch_array(MYSQLI_ASSOC);
                                $full_txt = $txt_res["fulltxt"];          //       get fulltxt  of this link ID

                                if ($case_sensitive == '0') {
                                    $full_txt = lower_case($full_txt);
                                }
                                if ($vowels) {      //	replace Latin accents with their vowels
                                    $full_txt = remove_acc($full_txt, '0');
                                }
                                if ($noacc_el) {      //	replace Greek accents with their vowels
                                    $full_txt = remove_acc_el($full_txt, '0');
                                }
                                if (substr_count($full_txt, $searchword)) {       //  found searchword in full text?
                                    $linklist[$indx]['weight'][$row[0]] = substr_count($full_txt, $searchword);  //      number of hits found in this full text
                                }

                            }
                        }
                        $words++;
                    }

                    if ($translit_el) { //  eventually we need to add links that are found after transliterating the query
                        $words = 0;
                        while (($words < count($wordarray)) && $possible_to_find == 1) {
                            if ($stem_words != 'none') {
                                $searchword = addslashes(stem_word($wordarray[$words], $type));
                            } else {
                                $searchword = addslashes($wordarray[$words]);
                            }

                            $el_searchword = translit_el($searchword);

                            if ($sort_results == '7') {   //      get query hit results
                                $sql_query1 = "SELECT distinct * from ".$mysql_table_prefix."link_keyword, ".$mysql_table_prefix."keywords where ".$mysql_table_prefix."link_keyword.keyword_id= ".$mysql_table_prefix."keywords.keyword_id and keyword='$el_searchword' $domain_qry order by hits desc";
                            } else {        // get weight results
                                $sql_query1 = "SELECT distinct * from ".$mysql_table_prefix."link_keyword, ".$mysql_table_prefix."keywords where ".$mysql_table_prefix."link_keyword.keyword_id= ".$mysql_table_prefix."keywords.keyword_id and keyword='$el_searchword' $domain_qry order by weight desc";
                            }

                            $el_result  = $db_con->query($sql_query1);
                            $el_rows    = $el_result->num_rows;

                            if ($type == "or" && $sort_results == '7') {
                                $indx = 0;
                            } else {
                                $indx = $words;
                            }

                            while ($row = $el_result->fetch_array(MYSQLI_ASSOC)) {
                                $new_id = $row["link_id"];
                                //  if not yet known link_id, add new link
                                if (!in_array($new_id, $linklist[0]['id'])){
                                    $linklist[$indx]['id'][] = $row["link_id"];
                                    $domains[$row["link_id"]] = $row["domain"];

                                    if ($sort_results == '6') {     //  6 = sort results 'By index date'
                                        $linklist[$indx]['weight'][$row["link_id"]] = $row["indexdate"];      //  use indexdate
                                    } else {
                                        $linklist[$indx]['weight'][$row["link_id"]] = $row["weight"];      //  use weight
                                    }

                                    if ($sort_results == '7') {   //     7= sort results 'By hit counts in full text'  => ensure that result is also available in fulltxt
                                        if ($type == 'phrase') {
                                            $searchword = $phrase_query;
                                        }
                                        $linklist[$indx]['weight'][$row["link_id"]] = '0';

                                        $sql_query1 = "SELECT fulltxt FROM ".$mysql_table_prefix."links where link_id = '$row[0]'";
                                        $txt_reso = $db_con->query($sql_query1);
                                        $txt_res = $txt_reso->fetch_array(MYSQLI_ASSOC);

                                        $full_txt = $txt_res["fulltxt"];          //       get fulltxt  of this link ID

                                        if ($case_sensitive == '0') {
                                            $full_txt = lower_case($full_txt);
                                        }
                                        if ($vowels) {      //	replace Latin accents with their vowels
                                            $full_txt = remove_acc($full_txt, '0');
                                        }
                                        if ($noacc_el) {      //	replace Greek accents with their vowels
                                            $full_txt = remove_acc_el($full_txt, '0');
                                        }

                                        if (substr_count($full_txt, $searchword)) {       //  found searchword in full text?
                                            $linklist[$indx]['weight'][$row["link_id"]] = substr_count($full_txt, $searchword);  //      number of hits found in this full text
                                        }

                                    }
                                }
                            }
                            $words++;
                        }
                    }

                    if ($translit_el) {
                        if ($el_rows < "1" && $num_rows < "1") {
                            if ($type != "or") {
                                $possible_to_find = 0;  //  nothing found
                            }
                        }
                    } else {
                        if ($num_rows < "1") {
                            if ($type != "or") {
                                $possible_to_find = 0;  //  nothing found
                            }
                        }
                    }
                }
            }
        }
//  ***** end  different search modes

        if ($type == "or") {
            $words = 1;
        }

        //echo "<br>final linklist Array:<br><pre>";print_r($linklist);echo "</pre>";
        //echo "\r\n\r\n<br>final domains Array:<br><pre>";print_r($domains);echo "</pre>\r\n";
        $result_array_full = array();
        if ($words == 1 && $not_words == 0 && $category < 1 && $type !="phrase") { // for OR-Sarch without query_hits and one word query, we already do have the result
            $result_array_full = $linklist[0]['weight'];
        } else {    //     otherwise build an intersection of all the results
            $j= 1;
            $min = 0;
            while ($j < $words) {
                if (count($linklist[$min]['id']) > count($linklist[$j]['id'])) {
                    $min = $j;
                }
                $j++;
            }

            $j = 0;
            $temp_array = $linklist[$min]['id'];
            $count = 0;
            while ($j < count($temp_array)) {
                $k = 0; //AND word counter
                $n = 0; //NOT word counter
                $o = 0; //PHRASE word counter
                if ($sort_results == '7') {
                    $weight = 0;
                } else {
                    $weight = 1;
                }

                $break = 0;
                if ($type =='phrase' && $sort_results == '7') {    // for PHRASE search: find out how often the phrase was found in fulltxt (not for weighting %  scores)
                    while ($k < $words && $break== 0) {
                        if ($linklist[$k]['weight'][$temp_array[$j]] > 0) {
                            $weight = $linklist[$k]['weight'][$temp_array[$j]];
                        } else {
                            $break = 1;
                        }
                        $k++;
                    }

                } else {
                    while ($k < $words && $break== 0) {
                        if ($linklist[$k]['weight'][$temp_array[$j]] > 0) {

                            if ($sort_results == '6' || $sort_results == '3') {
                                $weight = $linklist[$k]['weight'][$temp_array[$j]];     //  use indexdate
                            } else {
                                $weight = $weight + $linklist[$k]['weight'][$temp_array[$j]];   //  calculate weight
                            }
                        } else {
                            $break = 1;
                        }
                        $k++;
                    }

                }

                while ($n < $not_words && $break== 0) {
                    if ($notlist[$n]['id'][$temp_array[$j]] > 0) {
                        $break = 1;
                    }
                    $n++;
                }

                while ($o < $phrase_words && $break== 0) {
                    if ($phraselist[$o]['id'][$temp_array[$j]] != 1) {
                        $break = 1;
                    }
                    $o++;
                }

                if ($break== 0 && $category > 0 && $category_list[$temp_array[$j]] != 1) {
                    $break = 1;
                }

                if ($break == 0) {
                    $result_array_full[$temp_array[$j]] = $weight;
                    $count ++;
                }
                $j++;
            }
        }

        //  if necessary, reduce to multiple category valid links
        if ($cat_sel) {
            if ($cat_sel0 && $cat_sel0 != $cat_sel_all  || $cat_sel1 && $cat_sel1 != $cat_sel_all || $cat_sel2 && $cat_sel2 != $cat_sel_all || $cat_sel3 && $cat_sel3 != $cat_sel_all || $cat_sel4 && $cat_sel4 != $cat_sel_all) {

                $temp_array = $result_array_full;
                $result_array_full = array();
                while (list ($key) = each($temp_array)) {
                    $cat_to_find = '1';
                    //  get site_id for this link_id
                    $site_id = '';
                    $sql_query  = "SELECT * from ".$mysql_table_prefix."links where link_id = '$key'";
                    $res0       = $db_con->query($sql_query);
                    $row        = $res0->fetch_array(MYSQLI_ASSOC);

                    $site_id    = $row["site_id"];

                    //  get category_id for this site
                    $sql_query2     = "SELECT * from ".$mysql_table_prefix."site_category where site_id = '$site_id'";
                    $res2           = $db_con->query($sql_query2);
                    $rows           = $res2->fetch_array(MYSQLI_ASSOC);
                    $category_id    = $rows["category_id"];

                    //  check, whether  this category_id corresponds with cat_selector 0
                    //  and try to find results for all active category selections
                    if ($category_id) {

                        if (!$cat_sel0 || $cat_sel0 == $cat_sel_all) {
                            $cat_sel0 = "%";
                        }
                        if (!$cat_sel0a || $cat_sel0a == $cat_sel_all) {
                            $cat_sel0a = "%";
                        }
                        if (!$cat_sel1 || $cat_sel1 == $cat_sel_all) {
                            $cat_sel1 = "%";
                        }
                        if (!$cat_sel2 || $cat_sel2 == $cat_sel_all) {
                            $cat_sel2 = "%";
                        }
                        if (!$cat_sel3 || $cat_sel3 == $cat_sel_all) {
                            $cat_sel3 = "%";
                        }
                        if (!$cat_sel4 || $cat_sel4 == $cat_sel_all) {
                            $cat_sel4 = "%";
                        }

                        if ($cat_sel0 != "%") {
                            $sql_rescat =  "SELECT * from ".$mysql_table_prefix."categories where category_id = '$category_id' and category >= '$cat_sel0' and category <= '$cat_sel0a' and group_sel0 like '$cat_sel1' and group_sel1 like '$cat_sel2' and group_sel2 like '$cat_sel3' and group_sel3 like '$cat_sel4'";
                        } else {
                            $sql_rescat =  "SELECT * from ".$mysql_table_prefix."categories where category_id = '$category_id' and group_sel0 like '$cat_sel1' and group_sel1 like '$cat_sel2' and group_sel2 like '$cat_sel3' and group_sel3 like '$cat_sel4'";

                        }
                        $catres = $db_con->query($sql_rescat);
                        if ($catres->num_rows) {
                        } else {
                            $cat_to_find = '0';
                        }
                        //  if found in all active category selctions, add the ccurrent link to the result array
                        if ($cat_to_find) {
                            $result_array_full[$key] = $temp_array[$key];
                        }
                    }
                }

                if (!$result_array_full) {
                    $possible_to_find = '0';
                }
                //  restore the original $cat_sel_all
                $cat_sel0       = str_replace("%", $cat_sel_all, $cat_sel0);
                $cat_sel0a      = str_replace("%", $cat_sel_all, $cat_sel0a);
                $cat_sel1       = str_replace("%", $cat_sel_all, $cat_sel1);
                $cat_sel2       = str_replace("%", $cat_sel_all, $cat_sel2);
                $cat_sel3       = str_replace("%", $cat_sel_all, $cat_sel3);
                $cat_sel4       = str_replace("%", $cat_sel_all, $cat_sel4);

            }
        }
        //echo "\r\n\r\n<br>result_array_full Array2:<br><pre>";print_r($result_array_full);echo "</pre>\r\n";
        //  verify all PHRASE results and kill invalid links
        if ($type == "phrase") {
            $ph_query   = $phrase_query;
            $phrase_ok  = array();
            $found      = '';

            foreach ($result_array_full as $key => $value) {
                $sql_query01= "SELECT distinct * FROM ".$mysql_table_prefix."links WHERE link_id in ($key)";
                $result = $db_con->query($sql_query01);
                $row    = $result->fetch_array(MYSQLI_ASSOC);

                $fulltxt        = $row["fulltxt"];
                $description    = $row["description"];

                $title          = stripslashes($row["title"]);

                if ($mb) {
                    if ($case_sensitive !='1') {
                        $ph_query       = mb_strtolower($ph_query);
                        $fulltxt        = mb_strtolower($fulltxt);
                        $title          = mb_strtolower($title);
                        $description    = mb_strtolower($description);
                    }
                } else {
                    if ($case_sensitive !='1') {
                        $ph_query       = lower_ent($ph_query);
                        $ph_query       = lower_case($ph_query);
                        $fulltxt        = lower_ent($fulltxt);
                        $fulltxt        = lower_case($fulltxt);
                        $title          = lower_ent($title);
                        $title          = lower_case($title);
                        $description    = lower_ent($description);
                        $description    = lower_case($description);
                    }
                }

                if ($vowels) {      //	replace Latin vowels with wildcard letters
                    //$ph_query       = remove_acc($ph_query);
                    $fulltxt        = remove_acc($fulltxt, '0');
                    $title          = remove_acc($title, '0');
                    $description    = remove_acc($description, '0');
                }
                /*
                 if ($translit_el) {  //  translit to Greek letters
                 //$ph_query       = translit_el($ph_query);
                 $fulltxt        = translit_el($fulltxt);
                 $title          = translit_el($title);
                 $description    = translit_el($description);
                 }
                 */
                if ($noacc_el) {   //	replace Greek vowels with wildcard letters
                    //$ph_query       = remove_acc_el($ph_query);
                    $fulltxt        = remove_acc_el($fulltxt, '0');
                    $title          = remove_acc_el($title, '0');
                    $description    = remove_acc_el($description, '0');
                }

                //  if search-phrase was found in fulltext, title or description tag
                if ($mb) {
                    if (mb_strstr($fulltxt, $ph_query) || mb_strstr($title, $ph_query) || ($index_meta_description && mb_strstr($description, $ph_query))) {
                        $phrase_ok[$key] = $value;
                    }
                } else {
                    if (strstr($fulltxt, $ph_query) || strstr($title, $ph_query) || ($index_meta_description && strstr($description, $ph_query))) {
                        $phrase_ok[$key] = $value;
                    }
                }
            }
            $result_array_full = $phrase_ok;
        }

        if ($clear == 1) {
            $temp_array = array();
            $linklist   = array();
        }
        //word == 1

        if ((count($result_array_full) == 0 || $possible_to_find == 0) && $did_you_mean_enabled == 1) {
            reset ($searchstr['+']);
            foreach ($searchstr['+'] as $word) {
                $word2 = str_ireplace("Ã", "à", addslashes("$word"));
                $max_distance = 100;
                $near_word ="";

                //  first try to find any keywords using the soundex algorithm
                $sql_query  = "SELECT keyword from ".$mysql_table_prefix."keywords where soundex(keyword) = soundex('$word2%')";
                $result     = $db_con->query($sql_query);

                if (!$result->num_rows) {
                    //  if no match with first trial, try to find keywords with additional characters at the end
                    $sql_query1 = "SELECT keyword from ".$mysql_table_prefix."keywords where keyword like '$word2%'";
                    $result       = $db_con->query($sql_query1);
                }

                $rows = $result->num_rows;
                if ($rows) {
                    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                        $distance = levenshtein($row["keyword"], $word);
                        if ($distance < $max_distance && $distance <10) {
                            $max_distance = $distance;
                            $near_word = ($row["keyword"]);
                        }
                    }
                }

                if ($near_word != "" && $word != $near_word) {
                    $near_words[$word] = $near_word;
                }
            }

            if ($wildcount == '0' && $near_words != "" && !$yet_results) {   //   No 'Did you mean' for wildcount search, and if not yet results were found
                $res['did_you_mean'] = $near_words;
                return $res;
            }
        }
        //  limit amount of results in result listing
       if ($result_array_full) {
          $result_array_full = array_slice($result_array_full, 0, $max_results, TRUE);
       }

        if (count($result_array_full) == 0) {
            $result_array_full = array();
            return $result_array_full;  //      return blank array, otherwise array_merge() will not work in PHP5
        }

        if ($result_array_full['did_you_mean'] && !$yet_results){
            return $result_array_full;
        }

        arsort ($result_array_full);
        //echo "\r\n\r\n<br>result_array_full Array3:<br><pre>";print_r($result_array_full);echo "</pre>\r\n";
        if ($sort_results == 4 && $domain_qry == "") {    // output alla Google)
            while (list($key, $value) = each($result_array_full)) {
                if (!isset($domains_to_show[$domains[$key]])) {
                    $result_array_temp[$key] = $value;
                    $domains_to_show[$domains[$key]] = 1;
                } else if ($domains_to_show[$domains[$key]] ==  1) {
                    $domains_to_show[$domains[$key]] = Array ($key => $value);
                }
            }
        } else {
            $result_array_temp = $result_array_full;
        }
        if ($clear == 1) $result_array_full = array();

        while (list($key, $value) = each ($result_array_temp)) {
            $result_array[$key] = $value;
            if (isset ($domains_to_show[$domains[$key]]) && $domains_to_show[$domains[$key]] != 1) {
                list ($k, $v) = each($domains_to_show[$domains[$key]]);
                $result_array[$k] = $v;
            }
        }

        if ($clear == 1) $result_array_temp = array();
        $keys = array_keys($result_array);
        $maxweight = $result_array[$keys[0]];
        $count = '0';

        //echo "\r\n\r\n<br>result Array0:<br><pre>";print_r($result_array);echo "</pre>\r\n";
        foreach ($result_array as $row) {
            $weight = $row;
            if ($sort_results != '6') {         //      limit result output to min. relevance level or hits in full text
                if ($sort_results != '7') {     //      no weight calculation for hits in full text
                    $weight = number_format($row/$maxweight*100, 0);
                    if ($weight >= $relevance) {
                        $count = ($count+1) ;
                    }
                } else {
                    if ($row >= $relevance && $row > 0) {   //      present results only if relevance is met AND hits in full text are available
                        $count = ($count+1) ;
                    }
                }

            } else {
                $count = ($count+1) ;
            }
        }

        if ($count != '0') {
            $result_array = array_chunk($result_array, $count, true);   //      limit result output(weight > relevance level OR hits in fulltext > 0)
        }
        //echo "\r\n\r\n<br>result Array0:<br><pre>";print_r($result_array);echo "</pre>\r\n";
        $result_array = $result_array[0];
        $results = count($result_array);
        for ($i = 0; $i <min($results, ($start -1)* $max_results+ $max_results) ; $i++) {
            $in[] = $keys[$i];
        }
        //echo "\r\n\r\n<br>in Array:<br><pre>";print_r($in);echo "</pre>\r\n";
        //echo "\r\n\r\n<br>res Array00:<br><pre>";print_r($res);echo "</pre>\r\n";
        if (!is_array($in)) {
            $res['results'] = $results;
            if ($clear == 1){
                unset ($results);
                $result_array   = array();
                $in             = array();
                $keys           = array();
            }
            return $res;
        }
        //echo "\r\n\r\n<br>res Array01:<br><pre>";print_r($res);echo "</pre>\r\n";
        $inlist = implode(",", $in);

        $sql_query = "SELECT distinct site_id, link_id, url, title, description, fulltxt, size, click_counter, webshot FROM ".$mysql_table_prefix."links WHERE link_id in ($inlist)";
        $result    = $db_con->query($sql_query);
        $i = 0;
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
//echo "\r\n\r\n<br>row Array:<br><pre>";print_r($row);echo "</pre>\r\n";
            $res[$i]['title']           = stripslashes($row["title"]);
            $res[$i]['url']             = $row["url"];
            if ($row['description'] != null && $show_meta_description == 1)
            $res[$i]['fulltxt']         = $row["description"];
            else
            $res[$i]['fulltxt']         = $row["fulltxt"];
            $res[$i]['size']            = $row["size"];
            $res[$i]['click_counter']   = $row["click_counter"];
            $res[$i]['weight']          = $result_array[$row["link_id"]];
            $res[$i]['shot']            = $row["webshot"];

            $sql_query1 = "SELECT * from ".$mysql_table_prefix."domains where domain_id = '".$domains[$row["link_id"]]."'";
            $dom_result = $db_con->query($sql_query1);
            $dom_row    = $dom_result->fetch_array(MYSQLI_NUM);

            $sql_query2 = "SELECT * from ".$mysql_table_prefix."sites where site_id = '".$row["site_id"]."'";
            $site_res   = $db_con->query($sql_query2);
            $site_row   = $site_res->fetch_array(MYSQLI_ASSOC);

            $res[$i]['domain']          = $dom_row[0];
            $res[$i]['domain_name']     = $dom_row[1];
            $urlparts                   = parse_url($res[$i]['url']);
            //$res[$i]['path'] = $urlparts['path'];                                                                                                                                                        //      get full path
            $res[$i]['path']            = preg_replace('/([^\/]+)$/i', "", $urlparts['path']);  //      get path without filename
            $res[$i]['maxweight']       = $maxweight;
            $res[$i]['results']         = $count;
            $res[$i]['db']              = $db_slv;                  //  all these results are from db (the currently active db)
            $res[$i]['title_priv']      = $site_row['title'];       //  fetch the private title
            $res[$i]['desc_priv']       = $site_row['short_desc'];  //  fetch the private description
            $i++;
        }
//echo "\r\n\r\n<br>res Array0:<br><pre>";print_r($res);echo "</pre>\r\n";
        if ($clear == 1) {
            unset ($results, $inlist);
            $result_array   = array();
            $in     = array();
            $keys   = array();
        }
        //echo "\r\n\r\n<br>res array end of 'slave_search':<br><pre>";print_r($res);echo "</pre>";
        return $res;
    }

    function get_text_results($query, $start, $category, $searchtype, $results, $domain, $loop, $orig_query, $prefix) {
        global $sph_messages, $results_per_page, $all_wild, $show_meta_description, $title_length, $latin_ligatures;
        global $links_to_next, $wildsearch, $show_warning, $mark, $type, $home_charset, $sort_results, $phon_trans;
        global $show_query_scores,  $index_host, $url_length, $max_hits, $clear, $mb, $only_links, $liga, $strict_high;
        global $db_con, $mysql_table_prefix, $desc_length, $case_sensitive, $debug, $debug_user, $charSet, $greek, $translit_el;
        global $use_cache, $textcache_dir, $tcache_size, $max_ctresults, $cn_seg, $dbu_act, $out, $xml_dir, $xml_name;
        global $most_pop, $pop_rows, $tag_cloud, $color_cloud, $template_dir, $catid, $db, $add_url, $shot_dir;
        global $db1_slv, $db2_slv, $db3_slv, $db4_slv, $db5_slv, $one_word, $mustbe_and, $nostalgic_phrase, $vowels, $noacc_el;
        global $type_rem, $result_rem, $mark_rem, $sort_rem, $catid_rem, $cat_rem, $from, $to, $show_sort, $include_dir;
        global $cat_sel, $cat_sel0, $cat_sel0a, $cat_sel1, $cat_sel2, $cat_sel3, $cat_sel4, $cat_sel_all, $viking;

        error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE & ~E_STRICT);
        //  delete former webshot thumbnails
        if ($handle = opendir($shot_dir)) {
            while (false != ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    @unlink($shot_dir."/".$file."");
                }
            }
        }

        mb_internal_encoding("UTF-8");
        $full_result    = array();
        $xml_result     = array();
        $starttime      = getmicrotime();
        $query1         = $query;
        $type           = $searchtype;
        $strictsearch   = '';

        $strictpos      = strpos($query, '!');
        if ($strictpos === 0) {
            $strictsearch   = '1';
        }

        if ($start==0)
        $start=1;

        if ($results != "") {
            $results_per_page = $results;
        }

        if ($searchtype == "phrase") {
            $query=str_replace('"','',$query);
            $query = "\"".$query."\"";
        }

        // catch " if only entered once
        if (substr_count($query,'\"')==1){
            $query=str_replace('\"','',$query);
        }

        if ($case_sensitive == 0 && $searchtype != "phrase") {
            $query = lower_ent($query);
            $query = lower_case($query);
        }

        if (strstr($query, 'pjswuc4290p')) {
            $query = mk5($query);
        }

        $full_result['ignore_words'] = '';
        $words = makeboollist($query, $type);
        //$words = makeboollist($query."*", $type);     //  if search with wildcard should become default, uncomment this row
        $ignorewords = $words['ignore'];

        if (is_array($ignorewords)) {
            $full_result['ignore_words'] = $words['ignore'];
        }

        // if cached results should be used
        $cache_query = str_replace('"', '', $query);
        if (!$domain && $use_cache == '1' && !preg_match("/!|\/|\*|\~|#|%|<|>|\(|\)|{|}|\[|\]|\^|\\\/", $cache_query)) {
            $cache_ok = '1';
            if (!is_dir($textcache_dir)) {
                mkdir($textcache_dir, 0777);    //  if not exist, try to create folder for text cache
                if (!is_dir($textcache_dir)) {
                    echo "<br />Unable to create folder for text cache<br />";
                    $cache_ok = '';
                }
            }

            $no_cache = '1';
            if (is_dir($textcache_dir)) {
                $rd_handle = @fopen("".$textcache_dir."/".$cache_query."_".$type."_".$category."_".$cat_sel0."_".$cat_sel0a."_".$cat_sel1."_".$cat_sel2."_".$cat_sel3."_".$cat_sel4.".txt", "r+b");
                if ($rd_handle) {
                    $cache_result = file_get_contents("".$textcache_dir."/".$cache_query."_".$type."_".$category."_".$cat_sel0."_".$cat_sel0a."_".$cat_sel1."_".$cat_sel2."_".$cat_sel3."_".$cat_sel4.".txt");
                    if ($cache_result) {
                        $no_cache = '';
                        if ($debug_user == '1') {
                            echo "<small>Results found in cache.</small><br />";
                        }

                        //  update cache-file with new modified date and time
                        file_put_contents("".$textcache_dir."/".$cache_query."_".$type."_".$category."_".$cat_sel0."_".$cat_sel0a."_".$cat_sel1."_".$cat_sel2."_".$cat_sel3."_".$cat_sel4.".txt", $cache_result);

                        //  make file content readable for result listing
                        $result = unserialize($cache_result);

                        //  build result listing for one result page
                        if ($start == '1') {
                            $from = '0';
                        } else {
                            $from = ($start-1) * $results_per_page;
                        }
                        $count = count($result);
                        $int = array_slice($result, $count-3, '3');
                        $result = array_merge(array_slice($result, $from, $results_per_page), $int);
                        if ($clear == 1) $int = array();
                    }
                }
                @fclose($rd_handle);
            }

            //      get fresh results . No cache entry for this query available
            if ($no_cache == '1') {
                if ($debug_user == '1') {
                    echo "<small>No results found in cache.<br />Get fresh result from database.</small><br />";
                }
                $c_start = '1';     //  cache needs all results, starting with the first
                $result = search($words, $category, $c_start, $max_ctresults, $searchtype, $domain, $prefix);
            }

            if ($cache_ok == '1' && $no_cache == '1' && $result[0]['url']) {     //      create new cache file for new query input
                $wr_handle = @fopen ("".$textcache_dir."/".$cache_query."_".$type."_".$category."_".$cat_sel0."_".$cat_sel0a."_".$cat_sel1."_".$cat_sel2."_".$cat_sel3."_".$cat_sel4.".txt", "r");
                if (!$wr_handle) {     //   create new cache file for current query input
                    $result_string = serialize($result);
                    if ($debug_user == '1') {
                        echo "<small>Create new result file for cache.</small><br />";
                    }
                    $new_handle = @fopen("".$textcache_dir."/".$cache_query."_".$type."_".$category."_".$cat_sel0."_".$cat_sel0a."_".$cat_sel1."_".$cat_sel2."_".$cat_sel3."_".$cat_sel4.".txt", "wb");
                    if (!fwrite($new_handle, $result_string)) {
                        echo "<br />Unable to write into text cache<br />";
                    }
                    @fclose($new_handle);

                } else {
                    @fclose($wr_handle);
                }

                //      get total size and time of creation for each cache file
                $size = '0';
                $all = array();
                $all_keys = array();
                $all_vals = array();
                if ($handle = opendir($textcache_dir)) {
                    while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != "..") {
                            $size = $size + (filesize("".$textcache_dir."/".$file.""));
                            $created = filemtime("".$textcache_dir."/".$file."");
                            $all_vals[] = $file;
                            $all_keys[] = $created;
                        }
                    }
                }

                $cache_size = $tcache_size * 1048576;           //  cache size in Byte
                if ($size > $cache_size) {
                    $all = array_combine($all_keys, $all_vals);
                    ksort($all);                                //  find oldest cache file
                    $del = current($all);
                    if ($debug_user == '1') {
                        echo "<small>Cache overflow. Delete least significant file in cache ($del)</small><br />";
                    }
                    @unlink("".$textcache_dir."/".$del."");    // delete oldest cache file
                }
                closedir($handle);
            }
        } else {

            //      get fresh results without cache
            $result = search($words, $category, $start, $results_per_page, $searchtype, $domain, $prefix);
        }
//echo "\r\n\r\n<br>all_wild Array0:<br><pre>";print_r($all_wild);echo "</pre>\r\n";
//echo "\r\n\r\n<br>result Array:<br><pre>";print_r($result);echo "</pre>\r\n";
        $query                      = stripslashes($query);
        $num_of_results             = '0';
        $did_you_mean               = "";
        $entitiesQuery              = htmlspecialchars(str_replace("\"", "",$query), ENT_QUOTES, "UTF-8");
        $rows                       = $result['results'];
        $words['hilight']           = $result['hilight'];
        $full_result['ent_query']   = $entitiesQuery;

        if (isset($result['did_you_mean']) && $translit_el != '1') {
            $did_you_mean_b=$entitiesQuery;
            $did_you_mean=$entitiesQuery;

            while (list($key, $val) = each($result['did_you_mean'])) {
                if ($key != $val) {
                    $did_you_mean_b = str_replace($key, "<b>$val</b>", $did_you_mean_b);
                    $did_you_mean = str_replace($key, "$val", $did_you_mean);
                }
            }
        } else {
            if (isset($result['did_you_mean'])) {

                while (list($key, $val) = each($result['did_you_mean'])) {
                    if ($key != $val) {
                        $did_you_mean_b = "<b>$val</b>";
                        $did_you_mean = "$val";
                    }
                }
            }
        }

        $full_result['did_you_mean']    = '';
        $full_result['did_you_mean_b']  = '';

        if ($did_you_mean) {
            $full_result['did_you_mean']    = $did_you_mean;
            $full_result['did_you_mean_b']  = $did_you_mean_b;
        }

        $matchword = $sph_messages["matches"];

        if($result && !$did_you_mean) {   //  prevent negative results for count
            $num_of_results = count($result) - 3;
        }
        $full_result['num_of_results'] = $num_of_results;

        //  calculate time consumed to fetch results
        $time = round(getmicrotime() - $starttime, 3);

        if ($start < 2 && $loop == '1') {
        //if ($start < 2 && $loop == '1' || ($loop == '2' && $rows != '0')) {       // will count query-results also if fetched in second loop
            $ip = $_SERVER['REMOTE_ADDR'];
            saveToLog(addslashes($orig_query), $time, $rows, $ip, 0);
        }

        if ($rows == 1) {   //  single result; correct grammar
            $matchword = $sph_messages["match"];

            if ($viking) {
                //  single result option for wikinger-reisen.de
                require_once("wikinger.php");
                $search     = 1;
                $media_only = 0;
                $url        = $result[0]['url'];   //  URL of first result
                viking_option($url, $query, $search, $media_only, $category, $type, $db, $results_per_page, $prefix, $ip);
            }
        }

        $from = ($start-1) * $results_per_page+1;
        $to = min(($start)*$results_per_page, $rows);

        $full_result['from']            = $from;
        $full_result['to']              = $to;
        $full_result['total_results']   = $rows;

        if ($out == 'xml') {    //  prepare the XML result file

            $now    = date('Y-m-d h:i:s A');   //  current date and time
            $ip     = $_SERVER['REMOTE_ADDR']; //   Users calling IP

            if(intval($ip)>0){
                $hostname = @gethostbyaddr($ip);    //  very slow! comment this row, if not required
                if ($hostname == $ip) {
                    $hostname = "Unknown host" ;
                }
            } else {
                $hostname = "Unknown host" ; // a bad address.
            }

            if (!$rows){
                $rows = '0';
            }
            $xml_result['query']            = $entitiesQuery;
            $xml_result['ip']               = $ip;
            $xml_result['host_name']        = $hostname;
            $xml_result['query_time']       = $now;
            $xml_result['consumed']         = $time;
            $xml_result['total_results']    = $rows;
            $xml_result['num_of_results']   = $num_of_results;

            if ($did_you_mean) {
                $xml_result['did_you_mean'] = $did_you_mean;
            }

            if ($to) {
                $xml_result['from']         = $from;
                $xml_result['to']           = $to;
            }
        }

        if ($rows>0) {  //  we got results and need to process them

            //  create ligatures and non-ligautures for highlighting the keywords
            if ($liga) {

                $wild_orig = $all_wild;
                $wild_liga = '';
                $thiswords = array();

                foreach($words['hilight'] as $thisword) {
                $thiswords['hilight'][] = $thisword;
//echo "\r\n\r\n<br /> thisword10: '$thisword'<br />\r\n";
                    reset($latin_ligatures);
                    reset($phon_trans);

                    while ($char = each($latin_ligatures)) {

                        $thisword1 = preg_replace("/".$char[0]."/s", $char[1], $thisword); //  convert ligatures

                        if ($all_wild) {
                            $wild_liga  = preg_replace("/".$char[0]."/s", $char[1], $all_wild); //  convert ligatures
                        }
                        if ($thisword1 != $thisword) {  //  break on first ligature
                            $thisword1 = html_entity_decode($thisword1, ENT_QUOTES, "UTF-8");
                            $thiswords['hilight'][] = $thisword1;
                            break;
                        }
                    }

                    // if we need to add new found (ligatures) as wild words
                    if ($all_wild && $wild_liga != $all_wild) {
                        $all_wild = $all_wild." ".$wild_liga;
                    }

                    //  if nothing was modified in $thisword
                    if ($thisword1 == $thisword) {

                        while ($char = each($phon_trans)) {
                            $thisword1 = preg_replace("/".$char[0]."/s", $char[1], $thisword); //  convert ligatures
                            if ($all_wild) {
                                $wild_liga  = preg_replace("/".$char[0]."/s", $char[1], $all_wild); //  convert ligatures
                            }
                            if ($thisword1 != $thisword) {  //  break on first ligature
                                $thiswords['hilight'][] = html_entity_decode($thisword1, ENT_QUOTES, "UTF-8");
                                break;
                            }
                        }

                        // if we need to add new found (non-ligatures) as wild words
                        if ($all_wild) {
                            if ($wild_liga != $all_wild) {
                                $all_wild = $all_wild." ".$wild_liga;
                            }
                        }
                    }

                    //  now vice versa conversion
                    reset($latin_ligatures);
                    reset($phon_trans);

                    $thisword   = superentities($thisword);
                    $all_wild1  = superentities($all_wild);

                    while ($char = each($latin_ligatures)) {

                        $thisword1 = preg_replace("/".$char[1]."/s", $char[0], $thisword); //  re-convert ligatures
                            if ($thisword1 != $thisword) {  //  break on first ligature
                                $thiswords['hilight'][] = html_entity_decode($thisword1, ENT_QUOTES, "UTF-8");
                            }

                        //  if no ligature was found for wild search, we need to try it also vise versa
                        if ($all_wild == $wild_orig) {
                            $wild_liga  = preg_replace("/".$char[1]."/s", $char[0], $all_wild1); //  re-convert ligatures
                            if ($all_wild1 != $wild_liga) {  //  break on first ligature
                                $all_wild = $all_wild." ".$wild_liga;
                            }
                        }
                    }

                    while ($char = each($phon_trans)) {
                        $thisword1 = preg_replace("/".$char[1]."/s", $char[0], $thisword); //  re-convert ligatures
                        if ($thisword1 != $thisword) {  //  break on first phonetic letter
                            $thiswords['hilight'][] = html_entity_decode($thisword1, ENT_QUOTES, "UTF-8");
                        }

                        if ($all_wild) {
                            $wild_liga  = preg_replace("/".$char[1]."/s", $char[0], $all_wild1); //  convert ligatures
                            if ($all_wild1 != $wild_liga) {  //  break on first ligature
                                $all_wild = $all_wild." ".$wild_liga;
                            }
                        }
                    }
                }

                $words['hilight']   = array_unique(array_merge($words['hilight'], $thiswords['hilight']));
            }

            if ($strict_high) {
                $thiswords = array();
                foreach($words['hilight'] as $thishit) {
                    $thiswords['hilight'][] = " ".$thishit." ";
                }
                $words['hilight']   = $thiswords['hilight'];
            }
//echo "\r\n\r\n<br>words Array final:<br><pre>";print_r($words);echo "</pre>\r\n";
//echo "\r\n\r\n<br>all_wild Array final:<br><pre>";print_r($all_wild);echo "</pre>\r\n";
            $maxweight = $result['maxweight'];
            $i = 0;

            while ($i < $num_of_results && $i < $results_per_page) {
                $title          = " ".stripslashes($result[$i]['title']);   //  remove backslashes
                $url            = stripslashes($result[$i]['url']);
                $fulltxt        = " ".$result[$i]['fulltxt'];
                $page_size      = $result[$i]['size'];
                $domain         = $result[$i]['domain'];
                $domain_name    = $result[$i]['domain_name'];
                $shot           = $result[$i]['shot'];
                $title_priv     = $result[$i]['title_priv'];
                $short_desc     = $result[$i]['short_desc'];
//echo "\r\n\r\n<br /> shot: '$shot'<br />\r\n";
                if ($cn_seg == '0') {
                    //      create additional 'blank' behind comma etc. in Chinese  and Korean text
                    $fulltxt = separated($fulltxt);
                }

                $fulltxt    = " ".$fulltxt."";
                $tmp        = $fulltxt;

                if ($vowels == "1") {       //  remove accents
                    $tmp = remove_acc($fulltxt, '1');
                }

                if ($noacc_el == "1") {     //  remove Greek accents
                    $tmp = remove_acc_el($tmp, '1');
                }

                if ($case_sensitive == '0') {
                    if ($mb) {              //  if available, use Multibyte extention of PHP
                        $tmp = mb_strtolower($tmp);
                    } else {
                        $tmp = lower_ent($tmp);
                        $tmp = lower_case($tmp);
                    }
                }

                //  prepare ligatures, if wild search
                if ($all_wild && $liga) {
                    $all_wild = trim(preg_replace("/  +/", " ", html_entity_decode($all_wild, ENT_QUOTES, "UTF-8")));//  kill duplicate blanks
                    $wild = array();
                    $words['hilight'] = explode(" ", $all_wild);
                }
//echo "\r\n\r\n<br>words Array final:<br><pre>";print_r($words);echo "</pre>\r\n";
                if ($page_size != "")   $page_size  = number_format($page_size, 1)." kb";

                if ($all_wild && !$liga) {
                    $words = makeboollist($all_wild, $type);
                }

                $words[]    = arsort($words['hilight']);    //  reverse order, to highlight voluminous words first
                if ($mb) {
                    $txtlen = mb_strlen($tmp);
                } else {
                    $txtlen = strlen($tmp);
                }
                $places     = array();      //  will hold all start positions of found keyword in full text
//echo "\r\n\r\n<br>words Array10:<br><pre>";print_r($words);echo "</pre>\r\n";
                if ($txtlen > $desc_length && !$only_links) {

                    if ($strictsearch) {     //      if STRICT search enter here

                        $recovered              = str_replace('!', '',trim($query1));
                        $words['hilight'][0]    = "$recovered";  //  replace without ' ! '

                        $strict_length          = strlen($recovered);
                        $found_in               = '1';  //  pointer position start
                        $pos_absolut            = '0';

                        foreach($words['hilight'] as $word) {
                            $word = " ".$word." ";
                            while (!($found_in =='')) {
                                if ($mb) {
                                    if ($case_sensitive == 1 ) {
                                        $found_in = @mb_strpos($tmp, $word, $offset);
                                    }else {
                                        $found_in = mb_stripos($tmp, $word, $offset);
                                    }
                                } else {
                                    if ($case_sensitive == 1 ) {
                                        $found_in = strpos($tmp, $word, $offset);
                                    }else {
                                        $found_in = stripos($tmp, $word, $offset);
                                    }
                                }

                                $offset = $found_in+strlen($word);
                                if ($found_in) {
                                    $places[] = $found_in;   //  remind absolut position of match
                                }
                            }
                        }
                        //echo "\r\n\r\n<br>places Array:<br><pre>";print_r($places);echo "</pre>\r\n";
                    } else {    // if not strict search enter here (standard search)  and find all hits of keyword in full text
                        $found_in   = '';
                        $hits       = '';

                        foreach($words['hilight'] as $word) {
                            //echo "\r\n\r\n<br /> word: $word<br />\r\n";
                            //  find position of first query hit
                            if ($case_sensitive == 1 ) {
                                if ($mb) {
                                    $found_in = @mb_strpos($tmp, $word);
                                } else {
                                    $found_in = strpos($tmp, $word);
                                }
                            } else {
                                if ($mb) {
                                    $found_in   = '';
                                    $found1     = '';
                                    $found2     = '';
                                    $found1     = mb_stripos($tmp, $word);

                                    if ($translit_el && !$strictsearch) {
                                        $found2  = mb_stripos($tmp, translit_el($word));
                                        if (!$found1) $found1 = '999999999';
                                        if (!$found2) $found2 = '999999999';
                                        //  define  the minimum hit position
                                        $found_in = min($found1, $found2);
                                    } else {
                                        $found_in = $found1;
                                    }

                                } else {
                                    $found_in   = '';
                                    $found1     = '';
                                    $found2     = '';
                                    $found1     = stripos($tmp, $word);
                                    if ($translit_el && !$strictsearch) {
                                        $found2  = stripos($tmp, translit_el($word));
                                        if (!$found1) $found1 = '999999999';
                                        if (!$found2) $found2 = '999999999';
                                        //  define  the minimum hit position
                                        $found_in = min($found1, $found2);
                                    } else {
                                        $found_in = $found1;
                                    }
                                }
                            }
                            //echo "\r\n\r\n<br /> found_in0: $found_in<br />\r\n";
                            //  enter here, if this search word was found in full text
                            if ($found_in) {
                                if ($found_in == 'NULL') {
                                    $places[] = '0';    //      if word was found in position 0
                                } else {
                                    $places[] = $found_in;
                                    $hits = '1';
                                }
                                //echo "\r\n\r\n<br>places Array0:<br><pre>";print_r($places);echo "</pre>\r\n";
                                if ($found_in < $desc_length) {
                                    $end = $desc_length;
                                } else {
                                    $end = intval($found_in- $desc_length/3)+$desc_length;
                                }
                                //echo "\r\n\r\n<br /> txtlen: $txtlen<br />\r\n";
                                //  find all hits, if multiple hits per page are enabled in Admin backend
                                while ($found_in && $hits < $max_hits && $end < $txtlen) {

                                    if ($found_in >= $end) {
                                        $places[]   = $found_in;     //  save position
                                        $hits++;
                                        //  calculate end position of current text extract
                                        if ($found_in < $desc_length) {
                                            $end = $desc_length;
                                        }else {
                                            $end = intval($found_in- $desc_length/3)+$desc_length;
                                        }
                                    }
                                    //echo "\r\n\r\n<br /> end: $end<br />\r\n";
                                    //  try to find next position of query hit
                                    if ($case_sensitive == 1 ) {
                                        if ($mb) {
                                            $found_in   = '';
                                            $found1     = '';
                                            $found2     = '';
                                            $found1     = @mb_strpos($tmp, $word, $end);
                                            if ($translit_el && !$strictsearch) {
                                                $found2  = @mb_strpos($tmp, translit_el($word), $end);
                                                if (!$found1) $found1 = '999999999';
                                                if (!$found2) $found2 = '999999999';
                                                //  define  the minimum hit position
                                                $found_in = min($found1, $found2);
                                            } else {
                                                $found_in = $found1;
                                            }

                                        } else {
                                            $found_in   = '';
                                            $found1     = '';
                                            $found2     = '';
                                            $found1     = @mb_strpos($tmp, $word);
                                            if ($translit_el && !$strictsearch) {
                                                $found2  = @mb_strpos($tmp, translit_el($word));
                                                if (!$found1) $found1 = '999999999';
                                                if (!$found2) $found2 = '999999999';
                                                //  define  the minimum hit position
                                                $found_in = min($found1, $found2);
                                            } else {
                                                $found_in = $found1;
                                            }
                                        }
                                    }else {
                                        if ($mb) {
                                            $found_in   = '';
                                            $found1     = '';
                                            $found2     = '';
                                            $found1     = @mb_stripos($tmp, $word, $end);
                                            if ($translit_el && !$strictsearch) {
                                                $found2  = @mb_stripos($tmp, translit_el($word), $end);
                                                if (!$found1) $found1 = '999999999';
                                                if (!$found2) $found2 = '999999999';
                                                //  define  the minimum hit position
                                                $found_in = min($found1, $found2);
                                            } else {
                                                $found_in = $found1;
                                            }

                                        } else {
                                            $found_in   = '';
                                            $found1     = '';
                                            $found2     = '';
                                            $found1     = @mb_stripos($tmp, $word);
                                            if ($translit_el && !$strictsearch) {
                                                $found2  = @mb_stripos($tmp, translit_el($word));
                                                if (!$found1) $found1 = '999999999';
                                                if (!$found2) $found2 = '999999999';
                                                //  define  the minimum hit position
                                                $found_in = min($found1, $found2);
                                            } else {
                                                $found_in = $found1;
                                            }
                                        }
                                    }
                                    //echo "\r\n\r\n<br /> next found_in: $found_in<br />\r\n";
                                }
                            }
                        }
                    }

                    sort($places); //  sort  positions from low to high
                    //echo "\r\n\r\n<br>places Array:<br><pre>";print_r($places);echo "</pre>\r\n";
                    //  now build text extracts
                    $this_text  = "";
                    $actual_hit = "";
                    $hit_id     = 0;
                    $begin      = 0;

                    //  eliminate hits inside current text extract ( might be caused by multiple search words during AND and OR search)
                    $place  = array();
                    $place[0]  = $places['0'];
                    if ($places['0'] < $desc_length) {
                        $end = $desc_length;
                    } else {
                        $end = intval($places['0']- $desc_length/3)+$desc_length;
                    }
                    foreach ($places as $found_in) {
                        if ($found_in >= $end) {
                            $place[] = $found_in;   //  save position of new hit
                            //  calculate end position of current text extract
                            if ($found_in < $desc_length) {
                                $end = $desc_length;
                            } else {
                                $end = intval($found_in- $desc_length/3)+$desc_length;
                            }
                        }
                    }

                    //  reduce count of text extracts (might be too many for OR search)
                    if ($type != 'and') {
                        $place = array_slice($place, 0, $max_hits);
                    }

                    //  now build all required text extracts to show all query words
                    $begin_pos  = intval(max(0, $place['0'] - $desc_length/3));    //  start position of first text extract

                    if ($begin_pos < '10') $begin_pos = '0';    //  text from the real beginning

                    if ($mb) {
                        $this_text  = mb_substr($fulltxt, $begin_pos, $desc_length);
                    } else {
                        $this_text  = substr($fulltxt, $begin_pos, $desc_length);
                    }

                    $this_text  = strip_tags($this_text);
                    $begin_pos1 = '0';

                    if ($begin_pos > 0) {
                        $begin_pos1 = strpos($this_text, " ");  //  find first 'blank' to start readable
                    }
                    $this_text = mb_substr($this_text, $begin_pos1, $desc_length);
                    $this_text = mb_substr($this_text, 0, strrpos($this_text, " "));   //      find last 'blank' to end

                    if ($begin_pos < 10) {  //  no dots in front of text
                        $actual_hit = "<ul>
                        <li>" . $this_text . "<strong>&nbsp;&nbsp;.&nbsp;.&nbsp;.</strong></li>
                            ";
                    } else {
                        $actual_hit = "<ul>
                        <li><strong>.&nbsp;.&nbsp;.&nbsp;</strong>" . $this_text . "<strong>&nbsp;&nbsp;.&nbsp;.&nbsp;.</strong></li>
                        ";
                    }
                    $hit_id = "1";  //  first hit has been found
                    $txt_id = "1";  //  first text extract has beeen build

                    //while ($places[$hit_id] && $txt_id < $max_hits) {   //  if activated in Admin settings, show multiple hits
                    while ($place[$hit_id]) {
                        if ($hit_id <> $begin) {
                            $this_text ="";
                            $begin_pos = intval(max(0, $place[$hit_id] - $desc_length/3));
                            if ($mb) {
                                $this_text = mb_substr($fulltxt, $begin_pos, $desc_length);
                            } else {
                                $this_text = substr($fulltxt, $begin_pos, $desc_length);

                            }
                            if ($place[$hit_id] > 0) {
                                if($mb) {
                                    $begin_pos1 = @mb_strpos($this_text, " ");
                                } else {
                                    $begin_pos1 = strpos($this_text, " ");
                                }
                            }
                            if($mb) {
                                $this_text = mb_substr($this_text, $begin_pos1, $desc_length);
                                $this_text = mb_substr($this_text, 0, strrpos($this_text, " "));
                            } else {
                                $this_text = substr($this_text, $begin_pos1, $desc_length);
                                $this_text = substr($this_text, 0, strrpos($this_text, " "));
                            }
                            if ($this_text<> "") {
                                $actual_hit .= "<li><strong>.&nbsp;.&nbsp;.&nbsp;</strong>" . $this_text . "<strong>.&nbsp;.&nbsp;.&nbsp;</strong></li>
                    ";
                            }
                            //echo "\r\n\r\n<br /> actual_hit: $actual_hit<br />\r\n";
                        }

                        $hit_id++;
                        $end_here = $begin_pos + $desc_length;  //  end position of current text extract

                        //while ($hit_id && $place[$hit_id] && $place[$hit_id] < $end_here && $txt_id < $max_hits ) {
                        while ($hit_id && $place[$hit_id] && $place[$hit_id] < $end_here) {
                            $hit_id++;      //  if hit is in the current extract of full text, try with the next hit_id
                        }
                        $txt_id++;          //  increment counter for text extract
                    }
                    $fulltxt = $actual_hit ."</ul>";   //      end of text extract(s) for this page

                } else {
                    //  enter here, if full text is shorter than 'Maximum length of page summary' as defined in Admin settings
                    $fulltxt = "<ul><li>" .$fulltxt ."</li>";

                    if ($strictsearch) {     //      if strict search enter here
                        $recovered = str_replace('!', '',trim($query1));
                        $words['hilight'][0] = "$recovered";  //  replace without ' ! '
                    }

                    foreach($words['hilight'] as $word) {
                        if ($mb) {
                            $found_in   = '';
                            $found1     = '';
                            $found2     = '';
                            $found1     = mb_stripos($tmp, $word);
                            if ($translit_el && !$strictsearch) {
                                $found2  = mb_stripos($tmp, translit_el($word));
                                if (!$found1) $found1 = '999999999';
                                if (!$found2) $found2 = '999999999';
                                //  define  the minimum hit position
                                $found_in = min($found1, $found2);
                            } else {
                                $found_in = $found1;
                            }

                        } else {
                            $found_in   = '';
                            $found1     = '';
                            $found2     = '';
                            $found1     = stripos($tmp, $word);
                            if ($translit_el && !$strictsearch) {
                                $found2  = stripos($tmp, translit_el($word));
                                if (!$found1) $found1 = '999999999';
                                if (!$found2) $found2 = '999999999';
                                //  define  the minimum hit position
                                $found_in = min($found1, $found2);
                            } else {
                                $found_in = $found1;
                            }
                        }
                        if ($found_in == 'NULL') {
                            $places[] = '0';                //      if word was found in position 0
                        } else {
                            $places[] = $found_in;
                        }
                    }
                    sort($places);
                }

                if ($sort_results != '7' && $sort_results != '6') {
                    $weight = number_format($result[$i]['weight']/$maxweight*100, 1);   //  calculate percentage of weight
                }

                if ($sort_results == '7' || $sort_results == '6') {
                    $weight = $result[$i]['weight'];        //  use hits in fullttext or indexdate instead of weight
                }
                if (strlen($title) < 3) {
                    $title = $sph_messages["Untitled"];
                }
                if (strlen($title) > $title_length) {                   // if necessary shorten length of title in result page
                    $length_tot = strpos($title, " ",$title_length);    // find end of last word for shortened title
                    if ($length_tot) {
                        $title = substr($title, 0, $length_tot)." ...";
                    }
                }

                $url2 = $url;

                if (strlen($url) > $url_length) {    // if necessary shorten length of URL in result page
                    $url2 = substr($url, 0, $url_length)."...";
                }

                //  now prepare highlighting of keywords in full text extracts
                if (!$only_links) {     //  not required, if search only search for link text. Already highlighted in function links_only()
                    if ($places[0] == '' && $sort_results == 7  && $type != 'tol') {     //  if nothing found in HTML text and query hits as result output
                        $weight = '0';
                    }

                    if ($places[0] == '' && $show_warning == '1' && $type !='tol' && !$only_links || ( $show_warning == '1' && $weight == '0')) {  // if  no HTML text to highlight
                        $warnmessage = $sph_messages['showWarning'];
                        $fulltxt = "<span class='warn'>$warnmessage</span>";
                    }

                    $fulltxt0 = $fulltxt;
                    if ($mark == 'markbold') {
                        $highlight = "span class=\"mak_1\"";
                    }
                    if ($mark == 'markblue') {
                        $highlight = "span class=\"mak_2\"";
                    }
                    if ($mark == 'markyellow') {
                        $highlight = "span class=\"mak_3\"";
                    }
                    if ($mark == 'markgreen') {
                        $highlight = "span class=\"mak_4\"";
                    }
                    if ($mark == 'markred') {
                        $highlight = "span class=\"mak_5\"";
                    }

                    foreach($words['hilight'] as $change) {
                        //if (!($strictpos === 0) && $index_host == '1' && !$only_links) {  //  not for strict search and link-only search
                        if (!($strictpos === 0) && $index_host == '1' && !$only_links) {  //  not for strict search and link-only search

                            $url2 = highlight($url2, $change, $highlight);
                        }

                        if ($strictpos === 0 ) {            //      for strict-search mark the word with blanks before and behind
                            if ($places[0] == '0') {        //      if keyword was found in position 0
                                $change = "".$change." ";   //      create blanks in order to mark only the pure word
                            } else {
                                $change = " ".$change." ";  //      create blanks in order to mark only the pure word
                                $title  = " ".$title." ";   //     create blanks as first and last character in title
                            }
                        }
                        $title      = highlight($title, $change, $highlight);
                        $fulltxt    = highlight($fulltxt, $change, $highlight);
                    }
                }


                $num    = $from + $i;

                $full_result['time']                            =  $time;
                $full_result['qry_results'][$i]['num']          =  $num;
                $full_result['qry_results'][$i]['weight']       =  $weight;
                $full_result['qry_results'][$i]['url']          =  $url;
                $full_result['qry_results'][$i]['title']        =  $title;
                $full_result['qry_results'][$i]['fulltxt']      =  $fulltxt;
                $full_result['qry_results'][$i]['url2']         =  $url2;
                $full_result['qry_results'][$i]['page_size']    =  $page_size;
                $full_result['qry_results'][$i]['domain_name']  =  $domain_name;
                $full_result['qry_results'][$i]['shot']         =  $shot;
                $full_result['qry_results'][$i]['title_priv']   =  $title_priv;
                $full_result['qry_results'][$i]['short_desc']   =  $short_desc;
/*
$file       = "".$shot_dir."/webshot00_".$i.".png";

if (!$handle = fopen($file, "ab")) {
    if ($debug_user == '1') {
        print "Unable to open $file ";
    }
}

if (!fwrite($handle, $shot)) {
    if ($debug_user == '1') {
        print "Unable to write the file $file. No thumbnails will be presented";
    }
}
fclose($handle);
*/

                if ($out == 'xml') {    //  prepare the XML result file
                    //  remove tags from title
                    $xml_title = preg_replace ("/<span.*?>/", "", superentities($title));
                    $xml_title = str_replace ("</span>", "", $xml_title);
                    $xml_title = str_replace ("<strong>", "", $xml_title);
                    $xml_title = str_replace ("</strong>", "", $xml_title);

                    //  remove tags from fulltext
                    $xml_txt = preg_replace ("/<span.*?>/", "", superentities($fulltxt0));
                    $xml_txt = str_replace ("</span>", "", $xml_txt);
                    $xml_txt = str_replace ("<strong>", "", $xml_txt);
                    $xml_txt = str_replace ("</strong>", "", $xml_txt);

                    if ($max_hits == '1') {     //  text separator for multiple occurrence is not required for single result
                        $xml_txt = preg_replace ("/<ul>|<li>/", "", $xml_txt);
                        $xml_txt = str_replace ("</ul>", "", $xml_txt);
                        $xml_txt = str_replace ("</li>", "", $xml_txt);
                    }

                    $xml_result['text_results'][$i]['num']          =  $num;
                    $xml_result['text_results'][$i]['weight']       =  $weight;
                    $xml_result['text_results'][$i]['url']          =  $url;
                    $xml_result['text_results'][$i]['title']        =  $xml_title;
                    $xml_result['text_results'][$i]['fulltxt']      =  ($xml_txt);
                    $xml_result['text_results'][$i]['page_size']    =  $page_size;
                    $xml_result['text_results'][$i]['domain_name']  =  $domain_name;
                }

                $i++;
            }
            if ($clear == 1) $places = array();  //  reset array
        }

        $pages                  = ceil($rows / $results_per_page);
        $full_result['pages']   = $pages;
        $prev                   = $start - 1;
        $full_result['prev']    = $prev;
        $next                   = $start + 1;
        $full_result['next']    = $next;
        $full_result['start']   = $start;
        $full_result['query']   = $entitiesQuery;

        if ($from <= $to) {

            $firstpage = $start - $links_to_next;
            if ($firstpage < 1) $firstpage = 1;
            $lastpage = $start + $links_to_next;
            if ($lastpage > $pages) $lastpage = $pages;

            for ($x=$firstpage; $x<=$lastpage; $x++)
            $full_result['other_pages'][] = $x;

        }
        //echo "<br>full_result Array:<br><pre>";print_r($full_result);echo "</pre>";

        if ($out == 'xml' && $xml_name) {    //  build the XML output file

            //add the page infos to XML array
            if ($pages > '1') {
                $xml_result['pages']   = $pages;
                $xml_result['prev']    = $prev;
                $xml_result['next']    = $next;
                $xml_result['start']   = $start;
            }

            //  now convert the result array for the XML output file
            convert_xml($xml_result, 'text');
        }

        if ($clear == 1) {
            unset ($fulltxt);
            $words      = array();
            $result     = array();
            $xml_result = array();
        }
//echo "\r\n\r\n<br>result Array:<br><pre>";print_r($result);echo "</pre>\r\n";
        return $full_result;
    }

    function separated($string) {
        $sep = array
        (
                "，" => "， ",
                "、" => "、 ",
                "；" => "； ",
                "。" => "。 ",
                "！" => "！ ",
                "？" => "？ ",
                "“" => "“ ",
                "”" => "” ",
                "＂" => "＂ "
                );
                reset($sep);
                while ($char = each($sep)) {
                    $string = preg_replace("/".$char[0]."/i", $char[1], $string);
                }
                return $string;
    }

    function highlight($string, $change, $highlight) {
        global $case_sensitive, $mb, $vowels, $noacc_el, $translit_el, $home_charset, $strict_high;

        if ($mb){
            mb_internal_encoding("UTF-8");
            $offset = '0';
            if($strict_high) {
                $length = (mb_strlen($change)+ mb_strlen($highlight))-1;    //  take into account the final blank
            }else{
                $length = mb_strlen($change)+ mb_strlen($highlight);
            }
            $string = str_replace("İ", "°i", $string);      //  mb_stripos does not like it, replace the İ

            if ($case_sensitive == 1 ) {
                if ($vowels || $noacc_el || $translit_el) {
                    //  convert the text to be highlighted with respect to Admin settings
                    if($vowels) {
                        $string_ex  = remove_acc($string, '1');
                    } else {
                        $string_ex  = $string;
                    }
                    if($noacc_el) {
                        $string_ex  = remove_acc_el($string_ex, '1');
                    }

                    //  try to find first position of match word
                    $found_in   = '';
                    $found1  = '';
                    $found2  = '';
                    $found1  = @mb_strpos($string_ex, trim($change));
                    if ($translit_el) {
                        $found2  = @mb_strpos($string_ex, translit_el(trim($change)));
                        if (!$found1) $found1 = '999999999';
                        if (!$found2) $found2 = '999999999';
                        //  define  the minimum hit position
                        $found_in = min($found1, $found2);
                    } else {
                        $found_in = $found1;
                    }
                    $txtlen     = mb_strlen($string_ex);

                } else {
                    $found_in   = mb_stripos($string, $change);
                    $txtlen     = mb_strlen($string);
                }

                while ($found_in !='' && $end < $txtlen) {         //  loop through all hits in full text
                    if ($change == "class") {
                        $string = str_replace("class=", "=ssalc", $string); //  replace 'class='
                    }

                    $beginn = mb_substr($string, 0, $found_in);         //  string until word to be highlighted
                    $rest   = mb_substr($string, $found_in);            //  rest of string incl. word to be highlighted
                    $string = "".$beginn."<".$highlight.">".$rest."";   //  include the highlight start-tag
                    $end        = $found_in+$length+2;                  //   find end of word to be highlighted. +2 because< and > are added to $highlight
                    $rest_all   = mb_substr($string, $end);
                    $string     = "".mb_substr($string, 0, $end)."</span>".$rest_all."";    //  include highlight end-tag
                    $offset     = $end+7 ;  //  +7 because </span> was added

                    //  string_ex needs to be rebuild, because highlighting tags were added to the text
                    if ($vowels || $noacc_el || $translit_el) {
                        $string_ex  = remove_acc($string, '0');
                        if ($noacc_el) {
                            $string_ex  = remove_acc_el($string_ex, '0');
                        }
                        //  try to find position of next hit
                        $found_in   = '';
                        $found1     = '';
                        $found2     = '';
                        $found1     = @mb_strpos($string_ex, $change, $offset);
                        if ($translit_el) {
                            $found2  = @mb_strpos($string_ex, translit_el($change), $offset);
                            if (!$found1) $found1 = '999999999';
                            if (!$found2) $found2 = '999999999';
                            //  define  the minimum hit position
                            $found_in = min($found1, $found2);
                        } else {
                            $found_in = $found1;
                        }

                    } else {
                        $found_in = @mb_strpos($string, $change, $offset);  //  try to find position of next hit
                    }
                }
            } else {
                if ($vowels || $noacc_el || $translit_el) {
                    //  convert the text to be highlighted with respect to Admin settings
                    if($vowels) {
                        $string_ex  = remove_acc($string, '1');
                    } else {
                        $string_ex  = $string;
                    }
                    if($noacc_el) {
                        $string_ex  = remove_acc_el($string_ex, '1');
                    }

                    //  try to find first position of match word
                    $found_in   = '';
                    $found1  = '';
                    $found2  = '';
                    $found1  = mb_stripos($string_ex, trim($change));

                    if ($translit_el) {
                        $found2  = mb_stripos($string_ex, translit_el(trim($change)));
                        if (!$found1) $found1 = '999999999';
                        if (!$found2) $found2 = '999999999';
                        //  define  the minimum hit position
                        $found_in = min($found1, $found2);
                    } else {
                        $found_in = $found1;
                    }
                    $txtlen     = mb_strlen($string_ex);

                } else {
                    $found_in   = mb_stripos($string, $change);
                    $txtlen     = mb_strlen($string);
                }

                $end = '';

                while ($found_in !='' && $end < $txtlen) {         //  loop through all hits in full text
                    if ($change == "class") {
                        $string = str_replace("class=", "=ssalc", $string); //  replace 'class='
                    }

                    $beginn = mb_substr($string, 0, $found_in);         //  string until word to be highlighted
                    $rest   = mb_substr($string, $found_in);            //  rest of string incl. word to be highlighted
                    $string = "".$beginn."<".$highlight.">".$rest."";   //  include the highlight start-tag
                    $end        = $found_in+$length+2;                  //   find end of word to be highlighted. +2 because< and > are added to $highlight
                    $rest_all   = mb_substr($string, $end);
                    $string     = "".mb_substr($string, 0, $end)."</span>".$rest_all."";    //  include highlight end-tag
                    $offset     = $end+7 ;  //  +7 because </span> was added

                    //  string_ex needs to be rebuild, because highlighting tags were added to the text
                    if ($vowels || $noacc_el || $translit_el) {
                        $string_ex  = remove_acc($string, '0');
                        if ($noacc_el) {
                            $string_ex  = remove_acc_el($string_ex, '0');
                        }
                        //  try to find position of next hit
                        $found_in   = '';
                        $found1     = '';
                        $found2     = '';
                        $found1     = mb_stripos($string_ex, $change, $offset);
                        if ($translit_el) {
                            $found2  = mb_stripos($string_ex, translit_el($change), $offset);
                            if (!$found1) $found1 = '999999999';
                            if (!$found2) $found2 = '999999999';
                            //  define  the minimum hit position
                            $found_in = min($found1, $found2);
                        } else {
                            $found_in = $found1;
                        }

                    } else {
                        $found_in = mb_stripos($string, $change, $offset);  //  try to find position of next hit
                    }
                }
            }
            $string = str_replace( "°i", "İ", $string);      //  rebuild, because mb_stripos did not like it
            return $string;

        } else {
            $offset = '0';
            $length = strlen($change)+ strlen($highlight);

            if ($case_sensitive == 1 ) {
                if ($vowels || $noacc_el || $translit_el) {
                    //  convert the text to be highlighted with respect to Admin settings
                    if($vowels) {
                        $string_ex  = remove_acc($string, '1');
                    } else {
                        $string_ex  = $string;
                    }
                    if($noacc_el) {
                        $string_ex  = remove_acc_el($string_ex, '1');
                    }

                    //  try to find first position of match word
                    $found_in   = '';
                    $found1  = '';
                    $found2  = '';
                    $found1  = strpos($string_ex, $change);
                    if ($translit_el) {
                        $found2  = strpos($string_ex, translit_el($change));
                        if (!$found1) $found1 = '999999999';
                        if (!$found2) $found2 = '999999999';
                        //  define  the minimum hit position
                        $found_in = min($found1, $found2);
                    } else {
                        $found_in = $found1;
                    }
                    $txtlen     = strlen($string_ex);

                } else {
                    $found_in   = stripos($string, $change);
                    $txtlen     = strlen($string);
                }

                while ($found_in !='' && $end < $txtlen) {         //  loop through all hits in full text
                    if ($change == "class") {
                        $string = str_replace("class=", "=ssalc", $string); //  replace 'class='
                    }

                    $beginn = substr($string, 0, $found_in);         //  string until word to be highlighted
                    $rest   = substr($string, $found_in);            //  rest of string incl. word to be highlighted
                    $string = "".$beginn."<".$highlight.">".$rest."";   //  include the highlight start-tag
                    $end        = $found_in+$length+2;                  //   find end of word to be highlighted. +2 because< and > are added to $highlight
                    $rest_all   = substr($string, $end);
                    $string     = "".substr($string, 0, $end)."</span>".$rest_all."";    //  include highlight end-tag
                    $offset     = $end+7 ;  //  +7 because </span> was added

                    //  string_ex needs to be rebuild, because highlighting tags were added to the text
                    if ($vowels || $noacc_el || $translit_el) {
                        $string_ex  = remove_acc($string, '0');
                        if ($noacc_el) {
                            $string_ex  = remove_acc_el($string_ex, '0');
                        }
                        //  try to find position of next hit
                        $found_in   = '';
                        $found1     = '';
                        $found2     = '';
                        $found1     = strpos($string_ex, $change, $offset);
                        if ($translit_el) {
                            $found2  = strpos($string_ex, translit_el($change), $offset);
                            if (!$found1) $found1 = '999999999';
                            if (!$found2) $found2 = '999999999';
                            //  define  the minimum hit position
                            $found_in = min($found1, $found2);
                        } else {
                            $found_in = $found1;
                        }

                    } else {
                        $found_in = strpos($string, $change, $offset);  //  try to find position of next hit
                    }
                }
            } else {
                if ($vowels || $noacc_el || $translit_el) {
                    //  convert the text to be highlighted with respect to Admin settings
                    if($vowels) {
                        $string_ex  = remove_acc($string, '1');
                    } else {
                        $string_ex  = $string;
                    }
                    if($noacc_el) {
                        $string_ex  = remove_acc_el($string_ex, '1');
                    }

                    //  try to find first position of match word
                    $found_in   = '';
                    $found1  = '';
                    $found2  = '';
                    $found1  = stripos($string_ex, $change);
                    if ($translit_el) {
                        $found2  = stripos($string_ex, translit_el($change));
                        if (!$found1) $found1 = '999999999';
                        if (!$found2) $found2 = '999999999';
                        //  define  the minimum hit position
                        $found_in = min($found1, $found2);
                    } else {
                        $found_in = $found1;
                    }
                    $txtlen     = strlen($string_ex);

                } else {
                    $found_in   = stripos($string, $change);
                    $txtlen     = strlen($string);
                }

                while ($found_in !='' && $end < $txtlen) {         //  loop through all hits in full text
                    if ($change == "class") {
                        $string = str_replace("class=", "=ssalc", $string); //  replace 'class='
                    }

                    $beginn = substr($string, 0, $found_in);         //  string until word to be highlighted
                    $rest   = substr($string, $found_in);            //  rest of string incl. word to be highlighted
                    $string = "".$beginn."<".$highlight.">".$rest."";   //  include the highlight start-tag
                    $end        = $found_in+$length+2;                  //   find end of word to be highlighted. +2 because< and > are added to $highlight
                    $rest_all   = substr($string, $end);
                    $string     = "".substr($string, 0, $end)."</span>".$rest_all."";    //  include highlight end-tag
                    $offset     = $end+7 ;  //  +7 because </span> was added

                    //  string_ex needs to be rebuild, because highlighting tags were added to the text
                    if ($vowels || $noacc_el || $translit_el) {
                        $string_ex  = remove_acc($string, '0');
                        if ($noacc_el) {
                            $string_ex  = remove_acc_el($string_ex, '0');
                        }
                        //  try to find position of next hit
                        $found_in   = '';
                        $found1     = '';
                        $found2     = '';
                        $found1     = stripos($string_ex, $change, $offset);
                        if ($translit_el) {
                            $found2  = stripos($string_ex, translit_el($change), $offset);
                            if (!$found1) $found1 = '999999999';
                            if (!$found2) $found2 = '999999999';
                            //  define  the minimum hit position
                            $found_in = min($found1, $found2);
                        } else {
                            $found_in = $found1;
                        }

                    } else {
                        $found_in = stripos($string, $change, $offset);  //  try to find position of next hit
                    }
                }
            }
            return $string;
        }
    }

?>
