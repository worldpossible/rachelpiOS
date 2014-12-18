<?php

    // we want this to work for any value
    if (!empty($search)) {
        $search = 1;
    }

    switch ($search){

        case 1:
            //  get and present search results
            if (!isset($results)) {
                $results = "";
            }

            $orig_query = $query;

            //  if requested by query, kill AND characters
            if (strpos($query, " && ")){
                $query  = str_replace(" && ", " ",$query);
            }

            //  if requested by query, kill OR characters
            if (strpos($query, " || ")){
                $query  = str_replace(" || ", " ",$query);
            }

            //  if requested in Admin settings, remove Latin accents from their vowels
            if ($vowels) {
                $query = remove_acc($query, '0');
            }

            //  if requested in Admin settings, remove Greek accents from their vowels
            if ($noacc_el) {
                $query = remove_acc_el($query, '0');
            }

            //  clear former thumbnails from the temporary folder
            clear_folder($thumb_dir);

            //      perhaps we want to search for all pages of a site or perform a domain restricted search
            $pos = strstr(strtolower($query),"site:");
            if ($pos) {

                $domain_search = substr($query, '5');

                //      If you want to search for all pages of a site (site:abc.de) enter here
                if (!strrpos($domain_search, ' ')){
                    include ("$include_dir/search_links.php");
                }

                //      must be search only in domain (site:abc.de query)
                $query      = substr($domain_search, strpos($domain_search, ' ')+1);  //  extract query
                $dom_url    = substr($domain_search, 0, strpos($domain_search, ' '));  //  extract domain

                //  buid domain URL
                $url        = parse_url($dom_url);
                $hostname   = $url[host];

                //  rebuild domain for localhost applications
                if ($hostname == 'localhost') {
                    $host1 = str_replace($local,'',$dom_url);
                }

                $pos = strpos($host1, "/");         //      on local server delete all behind the /
                if ($pos) {
                    $host1 = substr($host1,0,$pos); //      build full adress again, now only local domain
                }

                if ($hostname == 'localhost') {
                    $domain = ("".$local."".$host1."/");
                    $domain = str_replace("http://",'',$domain);
                }else {
                    $domain = $hostname;
                }
            }

            $strictpos = strpos($query, '!');

            //  if search with wildcards is activated in Admin settings for queries containing numbers
            if ($wild_num == '1' && preg_match("/[0-9]/i", $query ) && !strstr($query, " ") && !strstr($query, "*")) {
                $query = "*".$query."*";
            }

            $strictpos = strpos($query, '!');
            $wildcount = substr_count($query, '*');
            $strict_search = '';
            if ($wildcount || $strictpos === 0) {
                $type           = 'and';        //  if wildcard, or strict search mode, switch always to AND search
                $strict_search  = '1';          //  prevent wildcard for quotes search
            }

            if ($wildcount || $strictpos === 0 || $type =='tol') {  //  if wildcard, strict or tolerant search mode, we have to search a lot but only for the first word
                $first = strpos($query, ' ');
                if ($first) {
                    $query = substr($query, '0', $first);
                }
            }

            $query      = str_replace('http://', '', $query);    //      URL's are stored without this in database
            $query      = preg_replace("/&nbsp;/", " ", $query); //      replace '&nbsp;' with " "
            $query      = preg_replace("/&apos;/", "'", $query); //      replace '&nbsp;' with " ' "
            $multi_word = strpos($query, " ");      //  check, whether the query contains a 'blank' character?

            //  if search without quotes is activated in Admin settings
            if ($no_quotes == '1' && !$multi_word) {
                $query = preg_replace("/&#8216;|&lsquo;|&#8217;|&rsquo;|&#8242;|&prime;|‘|‘|´|`/", "'", $query);
                $quote = strstr($query, "'");
                if ($quote && !$strict_search) {
                    $q_pos = strpos($query, "'");
                    $word1 =  substr($query, 0, $q_pos);
                    $word2 =  substr($query, $q_pos+1);
                    $query = '';
                    if (strlen($word1) >= $min_word_length) {
                        $query = "$word1*";
                    }

                    if (strlen($word2) >= $min_word_length) {
                        //$query .= " ".$word2."";      //  depending on some Admin 'spider' settings, this does not always deliver results
                        $query .= "*".$word2."";
                    }
                }
            }

            //$query = trim($query);
            if ($query == ''){    //  don't care about 'blank' queries
                break;
            }

            if (!$media_only) {
                $loop = '1';

                //  search for text results
                $text_results = get_text_results($query, $start, $category, $type, $results, $domain, $loop, $orig_query, $prefix);
                extract($text_results);   // get the text results
//echo "\r\n\r\n<br>text_results Array1:<br><pre>";print_r($text_results);echo "</pre>\r\n";
                if ($text_results['total_results'] == '') {         //  if nothing found, try to find something different
                    $query = strtr($query, "-,.?ČĬİI", "    ĬČIİ"); //   query written without hyphen,comma, dot etc.
                    $loop = '2';
                    $text_results = get_text_results($query, $start, $category, $type, $results, $domain, $loop, $orig_query, $prefix);
                    extract($text_results);   // get the text results
                }
            }

            //  search only for media results
            if ($show_media == '1' && $media_only == '1') {
                media_only($orig_query, $start, $media_only, $type, $category, $catid, $mark, $db, $prefix, $domain);
                break;
            }

            if ($text_results['ignore_words'] && $type !='phrase'){
                while ($thisword=each($ignore_words)) {
                    $ignored .= ", ".$thisword[1];
                }
                $ignored = substr($ignored, 1);
            }

            if ($debug == '2') {
                $slv1 = '';
                $slv2 = '';
                $slv3 = '';
                $slv4 = '';
                $slv5 = '';
                if ($db1_slv == 1 && !$user_db || $user_db == 1)  $slv1 = '1,';
                if ($db2_slv == 1 && !$user_db || $user_db == 2)  $slv2 = '2,';
                if ($db3_slv == 1 && !$user_db || $user_db == 3)  $slv3 = '3,';
                if ($db4_slv == 1 && !$user_db || $user_db == 4)  $slv4 = '4,';
                if ($db5_slv == 1 && !$user_db || $user_db == 5)  $slv5 = '5';
            }

            if ($text_results['total_results'] == 0){   //      if query did not match any keyword or any media
                $catname    = '';
                $catsearch  = '';
                if ($category != '-1') {    // if active search in categories, enter here
                    // fetch again the up to date catname (just to be sure)
                    $tpl_['category']   = sqli_fetch_all('SELECT category FROM '.$mysql_table_prefix.'categories WHERE category_id='.$category);
                    $catname            = $tpl_['category'][0]['category'];
                    $catsearch          = $sph_messages['catsearch'];
                }
                $no_res = str_replace ('%query', $orig_query, $sph_messages["noMatch"]);
            }

            //      Now prepare the text results  and eventually also media results
            if ($total_results != 0 && $from <= $to){   // this is the standard results header
                $result = $sph_messages['Results'];
                $result = str_replace ('%from', $from, $result);
                $result = str_replace ('%to', $to, $result);
                $result = str_replace ('%all', $total_results, $result);

                if ($elapsed) {
                    $result = str_replace ('%secs', $time, $result);
                } else {
                    $result = preg_replace("/\(.*?\)/", "", $result);   //  kill elapsed time info in result header
                }

                //  prepare result header, showing the cateory
                if ($advanced_search == 1 && $show_categories == 1 && $category) {    // additional headline for category search results
                    // fetch again the up to date catname (just to be sure)
                    $tpl_['category']   = sqli_fetch_all('SELECT category FROM '.$mysql_table_prefix.'categories WHERE category_id='.$category);
                    $catname            = $tpl_['category'][0]['category'];

                    if ($catname) {
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

                        $highlight  = "span class=\"red\"";      // comment this row for standard highlighting
                        $catname    = "<".$highlight.">".$catname."</span>";
                        $result     = "$result<br />";
                        $catsearch  = $sph_messages['catsearch'];
                        $result     = "".$result." ".$catsearch." ".$catname."";
                    }
                }

                //  prepare result header, showing all active cateory selections for parallel ctegory search
                if ($advanced_search == 1 && $show_categories == 1 && $cat_sel ) {

                    $catname = '';
                    if ($cat_sel0 && $cat_sel0 != "%" && $cat_sel0 != $cat_sel_all) {
                        $catname            = "&nbsp;".$cat_sel0;
                        if ($cat_sel0 != $cat_sel0a) {
                            $catname        .= "-".$cat_sel0a."&nbsp;";
                        }
                    }

                    if ($cat_sel1 && $cat_sel1 != "%" && $cat_sel1 != $cat_sel_all) {
                        if ($catname) {
                            $catname .= "+&nbsp;".$cat_sel1."&nbsp;";
                        } else {
                            $catname .= "&nbsp;".$cat_sel1."&nbsp;";
                        }
                    }
                    if ($cat_sel2 && $cat_sel2 != "%" && $cat_sel2 != $cat_sel_all) {
                        if ($catname) {
                            $catname .= "+&nbsp;".$cat_sel2."&nbsp;";
                        } else {
                            $catname .= "&nbsp;".$cat_sel2."&nbsp;";
                        }
                    }
                    if ($cat_sel3 && $cat_sel3 != "%" && $cat_sel3 != $cat_sel_all) {
                        if ($catname) {
                            $catname .= "+&nbsp;".$cat_sel3."&nbsp;";
                        } else {
                            $catname .= "&nbsp;".$cat_sel3."&nbsp;";
                        }
                    }
                    if ($cat_sel4 && $cat_sel4 != "%" && $cat_sel4 != $cat_sel_all) {
                        if ($catname) {
                            $catname .= "+&nbsp;".$cat_sel4."&nbsp;";
                        } else {
                            $catname .= "&nbsp;".$cat_sel4."&nbsp;";
                        }
                    }

                    if ($catname != '') {
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

                        $highlight  = "span class=\"red\"";      // comment this row for standard highlighting
                        $catname    = "<".$highlight.">".$catname."</span>";
                        $result     = "$result<br />";
                        $catsearch  = $sph_messages['catsearch'];
                        $result     = "".$result." ".$catsearch." ".$catname."";
                    }
                }

                $matchword = $sph_messages["matches"];
                if ($total_results== 1) {
                    $matchword= $sph_messages["match"];
                } else {
                    $matchword= $sph_messages["matches"];
                }

                $result = str_replace ('%matchword', $matchword, $result);

                if ($show_sort == '1' && $wildcount != '1') {
                    $res_order = $sph_messages['ResultOrder'];    // show order of result listing
                    if ($sort_results == '1') {
                        $this_list = $sph_messages['order1'];
                    }
                    if ($sort_results == '2') {
                        $this_list = $sph_messages['order2'];
                    }
                    if ($sort_results == '3') {
                        $this_list = $sph_messages['order3'];
                    }
                    if ($sort_results == '4') {
                        $this_list = $sph_messages['order4'];
                    }
                    if ($sort_results == '5') {
                        $this_list = $sph_messages['order5'];
                    }
                    if ($sort_results == '6') {
                        $this_list = $sph_messages['order6'];
                    }
                    if ($sort_results == '7') {
                        $this_list = $sph_messages['order7'];
                    }
                }
            }

//      display result header
            include "".$template_dir."/html/050_result-header.html";

            //  if no text results, alternatively search for media results
            //  but only if media results should be shown
            //  and 'text only' is not selected in advanced search form
            if ($text_results['total_results'] == '' && $show_media == '1' && $submit != $sph_messages['t_search']) {
                media_only($orig_query, $start, $media_only, $type, $category, $catid, $mark, $db, $prefix, $domain);
                break;
            }

            if (isset($qry_results)) {  //  start of result listing
                $known_host = '1';
                $class = "evrow";       //  in order to start with something
                $media_results = array();
                $n = 0;

                foreach ($qry_results as $_key => $_row){
                    //$last_domain = $domain_name;
                    extract($_row, EXTR_OVERWRITE);
                    $hits = $weight;
                    $change = '1';

                    if ($show_query_scores == 0 || $sort_results > '2' || $wildcount == '1') {
                        $indexdate  = $weight;  //  remember the indexdate
                        //$weight     = '';
                    }
                    if ($sort_results == '7') {
                        $high_hits = "span class=\"mak_1 blue\"";
                        $weight = "<".$high_hits.">[ ".$sph_messages['queryhits']." ".$hits." ]</".$high_hits.">";
                    } else {
                        $weight = "<strong>[ ".$weight." % ]</strong>";
                    }

                    if ($show_query_scores == 1 && $sort_results == '6') {      //  show indexdate instead of weighting
                        $weight = "<strong>[ ".$sph_messages['LastIndexed']." ".$indexdate." ]</strong>";
                    }

                    if ($show_query_scores == 0 || $sort_results == 5) {
                        $weight     = '';
                    }

                    $title1     = strip_tags($title);
                    $urlx       = $url2;

                    if ($sort_results > 1 || $sort_results < 5) {               //  only for domain sorting
                        $n_h        = parse_url($url);
                        $new_host   = $n_h["host"];

                        if ($new_host == 'localhost') {
                            $host1 = str_replace($local,'',$url2);
                            $pos = strpos($host1, "/");         //      on local server delete all behind the /
                            if ($pos) {
                                $host1 = substr($host1,0,$pos); //      build full adress again, now only local domain
                            }
                            $this_host = ("".$local."".$host1."/");
                            $new_host = str_replace("http://",'',$this_host);

                        }

                        if ($new_host == $known_host || $known_host == '') {    //  display another host?
                            $change = '0';
                            $i++;
                        } else {
                            $change = '1';
                            $i = '1';
                        }
                    }

                    //      prepare current page-url for click counter
                    //$url_crypt  = str_replace("&", "-_-", $url);        //  crypt the & character
                    //$url_crypt  = str_replace("+", "_-_", $url_crypt);  //  crypt the + character
                    $url_crypt  = urlencode($url); // jfield - yeah, no, you have to do more than the & and +
                                                   // (some wikipedia urls with special chars weren't working
                                                   // after going through the click_counter)
                    $url_click  = "$include_dir/click_counter.php?url=$url_crypt&amp;query=$query&amp;search=$search&amp;&media_only=$media_only&amp;category=$category&amp;catid=$catid&cat_sel0=$cat_sel0&cat_sel0a=$cat_sel0a&amp;cat_sel1=$cat_sel1&amp;cat_sel2=$cat_sel2&amp;cat_sel3=$cat_sel3&amp;cat_sel4=$cat_sel4&amp;type=$type&amp;mark=$mark&amp;results=$results_per_page&amp;db=$db&amp;prefix=$prefix&amp;client_ip=$client_ip";   //  redirect users click in order to update Most Popular Links
                    //  prepare the category selection for each result
                    if ($more_catres == '1'){
                        $catidx = $catid;   //  separate catid for cat selection
                        if (!$catid) $catidx = $category;    //  rebuild default cat_id as input
                        $catlist    = findcats($urlx, $category, $catidx, $mysql_table_prefix);
                        $catlinks   = array ();
                        $catlink    = '';

                        foreach ($catlist as $value) {
                            $sql_query = "SELECT category_id from ".$mysql_table_prefix."categories where category like '$value'";   //  get cat_id for this category
                            $res = $db_con->query($sql_query);
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
                            $row = $result->fetch_array(MYSQLI_NUM);
                            $catidx = $res[0];

                            if ($catidx) {
                                //  build complete query for cat search
                                $catlink = "<a href=\"$search_script?query=$query&amp;start=$prev&amp;search=1&amp;category=$catidx&amp;catid=$catidx&amp;mark=$mark&amp;results=$results_per_page&amp;db=$db&amp;prefix=$prefix&amp;client_ip=$client_ip&amp;domain=$domain\">$value</a>";
                                $catlinks[] = $catlink;
                            }
                        }
                        $cat_links = implode(", ", $catlinks);
                    }

                    $urlx       = reconvert_url($urlx);     //  recover blank characters
                    $file = '';

                    //  prepare the webshot thumbnails for result listing
                    if($shot) {
                        $file = "".$shot_dir."/webshot".$n.".png";
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
                    }

                    //  make the domain URL readable for result listing
                    if ($sort_results == 3 || $sort_results == 4) {
                        $sql_query = "SELECT * from ".$mysql_table_prefix."domains where domain like '$new_host'";
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

                        if ($res1->num_rows) {
                            $thisrow = $res1->fetch_array(MYSQLI_NUM);
                            $domain_name = $thisrow[1];
                        }
                    }

//       display result-listing
                    include "".$template_dir."/html/060_text-results.html";
                    $known_host = $new_host;    //  remember host of this link (for domain sorting)
                    $n++;
                }   //  end of result listing

                ?>
    </div>
                <?php
                if (isset($other_pages)) {
                    if ($adv==1) {
                        $adv_qry = "&amp;adv=1";
                    }
                    if ($type != "") {
                        $type_qry = "&amp;type=$type";
                    }
//      display links to more result pages
                    include "".$template_dir."/html/070_more-results.html";
                }

            }
            break;

    default:

        if (!$cat_sel) {    //  if not multiple category selection
            if ($show_categories) {
                if ($_REQUEST['catid']  && is_numeric($catid)) {
                    $cat_info = get_category_info($catid);
                } else {
                    $cat_info = get_categories_view();
                }
            }

            //  get name of current category
            $sql_query = "SELECT category from ".$mysql_table_prefix."categories where category_id = '$catid'";
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

            $row        = $result->fetch_array(MYSQLI_NUM);
            $catname    = $row[0];

            if ($catid && is_numeric($catid)){
                //      display category tree (for sub-cats)
                include "".$template_dir."/html/040_category-tree.html";
                die();
            } else {
                if ($cat_info['main_list']){    // main category selection
                    //      display main category selection
                    include "".$template_dir."/html/030_category-selection.html";
                    die();
                }
            }
        }


        break;

    case 2:
        //  present all media results of a selected page

        //  first clear former thumbnails from the temporary folder
        $thumb_folder = str_replace("../", "", $thumb_folder);
        clear_folder($thumb_folder);

        if ($debug == '2') {
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
        }

        //  prepare the URL for click_counter
        $link_crypt  = str_replace("&", "-_-", $link);    //  crypt the & character
        $link_crypt  = str_replace("+", "_-_", $link);    //  crypt the + character
        $url_click  = "$include_dir/click_counter.php?url=$link_crypt&amp;query=$query&amp;db=$db&amp;prefix=$prefix&amp;client_ip=$client_ip";   //  redirect users click in order to update Most Media Popular Links

        //      display header for all media results of a selected page
        include "".$template_dir."/html/100_all-media result-header.html";

        //  get all media results of this page and present them as result listing
        $all            = '1';
        $mode           = '1';
        $media_results  = array();

        $image_results = image($query, $link, 'image', $all, $urlx, $title, $image_dir, 'allImages', 'normSearch', 'camera60.gif', $mode, $media_only, $type, $category, $catid, $mark, $db, $prefix, $domain);
        //  prepare media results for XML output file
        if ($image_results && $out == 'xml'){
            $media_results = $image_results;
        }

        $audio_results = media($query, $link, 'audio', $all, $urlx, $title, $image_dir, 'foundAudio', 'normSearch', 'notes60.gif', $mode, $media_only, $type, $category, $catid, $mark, $db, $prefix, $domain);
        //  prepare media results for XML output file
        if ($audio_results && $out == 'xml'){
            $media_results = array_merge($media_results, $audio_results);
        }

        $video_results = media($query, $link, 'video', $all, $urlx, $title, $image_dir, 'foundVideo', 'normSearch', 'film60.gif', $mode, $media_only, $type, $category, $catid, $mark, $db, $prefix, $domain);
        //  prepare media results for XML output file
        if ($video_results && $out == 'xml'){
            $media_results = array_merge($media_results, $video_results);
        }

        //  if activated, prepare the XML result file
        if ($out == 'xml' && $xml_name) {
            media_xml($media_results, count($media_results), $query, round(getmicrotime() - $start_all, 3));
        }

        echo "
                <br /><br />
                <a class=\"navup\" href=\"#top\" title=\"Jump to Page Top\">".$sph_messages['top']."</a>
                ";
        break;
    }

    // if selected in Admin settings, show 'Most Popular Searches'
    if ($most_pop == 1 ) {
        $bgcolor='odrow';
        $count = 0;

        //  get most popular queries sorted by count of searches
        $sql_query = "SELECT query, count(*) as c,
                                    date_format(max(time),
                                    '%Y-%m-%d %H:%i:%s'),
                                    avg(results), media
                                    from ".$mysql_table_prefix."query_log
                                    group by query order by c desc";
/*
         //  use the following for sorting by 'Last queried'
         $sql_query = "SELECT query, count(*) as c,
         date_format(max(time),
         '%Y-%m-%d %H:%i:%s'),
         avg(results), media
         from ".$mysql_table_prefix."query_log
         group by query order by time desc";
*/
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

        //      display 'Most Popular Search' as detailed table
        include "".$template_dir."/html/080_most_pop.html" ;
    }
?>
