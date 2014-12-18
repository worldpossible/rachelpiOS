<?php

/*
 Feed parser based on lastRSS 0.9.1 feed reader
 Original by Vojtech Semecky, webmaster @ webdot.cz

 In order to meet the requirements of Sphider-plus
 the script was modified by [Tec] 2012-01-01
 */

    class feedParser {

        //  Standard tags processed for RDF and RSS feeds
        var $channeltags    = array('title', 'link', 'description', 'language', 'copyright', 'managingEditor', 'webMaster', 'pubDate', 'lastBuildDate', 'category', 'generator', 'rating', 'docs');
        var $itemtags       = array('title', 'link', 'description', 'author', 'category', 'comments', 'enclosure', 'guid', 'pubDate', 'source');
        var $textinputtags  = array('title', 'description', 'name', 'link');
        var $imagetags      = array('title', 'url', 'link', 'width', 'height');
        //  Additional remark for RSS feeds:        optional sub-elements of the CATEGORY element (that identifies a categorization taxonomy) are currently not supported.

        //  For RDF feeds, the following individual tags are additionally processed
        var $dcs            = array('dc:', 'sy:', 'prn:');
        var $pers_chan_tags = array('publisher', 'rights', 'date');     //      valid for channel only
        var $pers_item_tags = array('country', 'coverage', 'contributor', 'date', 'industry', 'language', 'publisher', 'state', 'subject');     //  valid for items only

        //  Tags processed for RSD feeds
        var $servicetags    = array('engineName', 'engineLink', 'homePageLink');
        var $apitags        = array('name', 'apiLink', 'blogID');
        var $settings       = array('docs', 'notes');

        //  Tags processed for Atom feeds
        var $metatags           = array('author', 'category', 'contributor', 'title', 'subtitle', 'link', 'id', 'published', 'updated', 'summary', 'rights', 'generator', 'icon','logo');
        var $entrytags          = array('author', 'category', 'contributor', 'title', 'link', 'id', 'published', 'updated', 'summary', 'rights');
        var $authortags         = array('name', 'uri', 'email');
        var $contributortags    = array('name', 'uri', 'email');
        var $categorytags       = array('term', 'scheme', 'label');
        var $generatortags      = array('uri', 'version');
        //  Additional remark for Atom feeds:     SOURCE  elements are currently not supported

        // Parse feed and returns associative array.
        function Get ($rss_url, $feed_content) {
            // If CACHE ENABLED
            if ($this->cache_dir != '') {
                $cache_file = $this->cache_dir . '/rsscache_' . md5($rss_url);
                $timedif = @(time() - filemtime($cache_file));
                if ($timedif < $this->cache_time) {
                    // cached file is fresh enough, return cached array
                    $result = unserialize(join('', file($cache_file)));
                    // set 'cached' to 1 only if cached file is correct
                    if ($result) $result['cached'] = 1;
                } else {
                    // cached file is too old, create new
                    $result = $this->Parse($rss_url, $feed_content);
                    $serialized = serialize($result);
                    if ($f = @fopen($cache_file, 'w')) {
                        fwrite ($f, $serialized, strlen($serialized));
                        fclose($f);
                    }
                    if ($result) $result['cached'] = 0;
                }
            }
            // If CACHE DISABLED >> load and parse the file directly
            else {
                $result = $this->Parse($rss_url, $feed_content);
                if ($result) $result['cached'] = 0;
            }
            // return result
            return $result;
        }

        // for single match of pattern
        function subject_match ($pattern, $subject) {
            preg_match($pattern, $subject, $out);
            if(isset($out[1])) {
                //      kill useless blanks and line feeds
                $out[1] = preg_replace("/[  |\r\n]+/i", " ", $out[1]);
                // Process CDATA (if present)
                if ($this->CDATA == 'content') { // Get CDATA content (without CDATA tag)
                    $out[1] = preg_replace("/<!\[CDATA\[|\]\]>/i","",$out[1] );                     //  get it all  (naughty)
                } else {
                    $out[1] = preg_replace("/<!\[CDATA\[(.*?)\]\]>/i","",$out[1] );                 //  well educated crawler
                }
                return trim($out[1]);
            } else {
                return '';
            }
        }

        // for eventually multiple match of pattern
        function subject_match_all ($pattern, $subject) {
            preg_match_all($pattern, $subject, $out );
            $all_out = implode(" , ", $out[1]);

            if(isset($out[1])) {
                //      kill useless blanks and line feeds
                $out[1] = preg_replace("/[  |\r\n]+/i", " ", $out[1]);
                // Process CDATA (if present)
                if ($this->CDATA == 'content') {                                                    // Get CDATA content (without CDATA tag)
                    $all_out = preg_replace("/<!\[CDATA\[|\]\]>/i","", $all_out);                   //  get it all  (naughty)
                } else {
                    $all_out = preg_replace("/<!\[CDATA\[(.*?)\]\]>/i","",$all_out );               //  well educated crawler
                }
                return trim($all_out);
            } else {
                return '';
            }
        }

        // Parse() is private method used by Get() to load and parse feeds.
        function Parse ($rss_url, $feed_content) {
            //  if feed is not yet available as string, load file
            if ($this->file != '1') {
                // Open and load file
                if ($f = @fopen($rss_url, 'r')) {
                    $feed_content = '';
                    while (!feof($f)) {
                        $feed_content .= fgets($f, 4096);
                    }
                    fclose($f);
                }
            }

            if ($feed_content) {
                //echo "\r\n\r\n<br>feed_content Array0:<br><pre>";print_r($feed_content);echo "</pre>\r\n";
                $result = array();
                $result['type'] = 'unknown';
                // Parse feed type
                if (preg_match("/<rdf:/si", substr($feed_content,0,400))) {                         //  RDF feed ?
                    $result['type'] = 'RDF';
                }
                if (preg_match("/atom|<feed/si", substr($feed_content,0,400))) {                    //  Atom feed ?
                    $result['type'] = 'Atom';
                }
                if (preg_match("/rsd/si", substr($feed_content,0,400))) {                           //  RSD feed ?
                    $result['type'] = 'RSD';
                }
                if (preg_match("/<rss(.*?)version(.*?)=(.*?)[\'|\"](.*?)[\'|\"]/si", substr($feed_content,0,400), $regs)){      //  RSS version 2.0  ?
                    if (trim($regs[4]) == '2.0') {
                        $result['type'] = 'RSS v.2.0';
                    } else {
                        if (!$result['type']) {                                                     //  should be RSS 0.91 or 0.92 feed
                            $result['type'] = 'RSS v.0.91/0.92';
                        }
                    }
                }

                // Parse document encoding
                $result['encoding_in'] = strtoupper($this->subject_match("'encoding=[\'\"](.*?)[\'\"]'si", $feed_content));
                $result['encoding_out'] = $this->out_cp;
                // if encoding is specified, use it
                if ($result['encoding_in'] != '') {
                    $this->feed_cp = $result['encoding_in'];  // this is used in my_preg_match()
                    // otherwise use the input charset
                } else {
                    $this->feed_cp = $this->in_cp;
                }
                //  convert feed content into utf-8
                if ($this->out_cp != $this->in_cp) {
                    $feed_content = iconv($this->feed_cp, $this->out_cp.'//IGNORE', $feed_content);
                }
                // **********  For RDF and RSS feeds enter here **********
                if ($result['type'] == 'RSS v.2.0' | $result['type'] == 'RSS v.0.91/0.92' | $result['type'] == 'RDF') {
                    $dcc =$this->dc;
                    if ($this->dc == '1') {
                        foreach($this->dcs as $dc) {
                            //$feed_content = preg_replace("/<$dc|<\/$dc/si", "<", $feed_content);
                            $feed_content = str_replace($dc, "", $feed_content);    //  remove personal tag parts like dc:
                        }
                    }

                    // Parse ITEMS
                    preg_match_all("'<item(| .*?)>(.*?)</item>'si", $feed_content, $items);
                    $i = 0;
                    $result['items'] = array(); // create array even if there are no items
                    foreach($items[2] as $rss_item) {
                        // If number of items is lower then limit: Parse one item
                        if ($i < $this->limit) {
                            foreach($this->itemtags as $itemtag) {
                                $temp = $this->subject_match_all("'<$itemtag.*?>(.*?)</$itemtag>'si", $rss_item);
                                if ($temp != '') {
                                    if (strstr($temp, "://")) {
                                        $temp = "<a href=\"".$temp."\">".$temp."</a>";              //  create URI
                                    }
                                    $result['items'][$i][$itemtag] = $temp;                         // set only if not empty
                                }
                            }

                            //      if defined, parse also the Dublin Core item tags
                            if ($this->dc == '1') {
                                foreach($this->pers_item_tags as $pers_item_tag) {
                                    $temp = $this->subject_match_all("'<$pers_item_tag.*?>(.*?)</$pers_item_tag>'si", $rss_item);
                                    if ($temp != '') {
                                        if (strstr($temp, "://")) {
                                            $temp = "<a href=\"".$temp."\">".$temp."</a>";          //  create URI
                                        }
                                        $result['items'][$i][$pers_item_tag] = $temp;               // set only if not empty
                                    }
                                }
                            }

                            // If date_format is specified and pubDate is valid
                            if ($this->date_format != '' && ($timestamp = strtotime($result['items'][$i]['pubDate'])) !==-1) {
                                // convert pubDate to specified date format
                                $result['items'][$i]['pubDate'] = date($this->date_format, $timestamp);
                            }
                            // If date_format is specified and date is valid (for Dublin Core tags only)
                            if ($this->date_format != '' && ($timestamp = strtotime($result['items'][$i]['date'])) !==-1) {
                                // convert date to specified date format
                                $result['items'][$i]['date'] = date($this->date_format, $timestamp);
                            }

                            $i++;    // Item counter
                        }
                    }
                    $feed_content = str_replace($items[2], "", $feed_content);

                    // Parse CHANNEL info
                    preg_match("'<channel.*?>(.*?)</channel>'si", $feed_content, $out_channel);
                    foreach($this->channeltags as $channeltag) {
                        $temp = $this->subject_match_all("'<$channeltag.*?>(.*?)</$channeltag>'si", $out_channel[1]);
                        if ($temp != '') {
                            if (strstr($temp, "://")) {
                                $temp = "<a href=\"".$temp."\">".$temp."</a>";                      //  create URI
                            }
                            $result[$channeltag] = $temp;                                           // set only if not empty
                        }
                    }

                    //      if defined, parse also the Dublin Core channel tags
                    if ($this->dc == '1') {
                        foreach($this->pers_chan_tags as $pers_chan_tag) {
                            $temp = $this->subject_match_all("'<$pers_chan_tag.*?>(.*?)</$pers_chan_tag>'si", $out_channel[1]);
                            if ($temp != '') {
                                if (strstr($temp, "://")) {
                                    $temp = "<a href=\"".$temp."\">".$temp."</a>";                  //  create URI
                                }
                                $result[$pers_chan_tag] = $temp;                                    // set only if not empty
                            }
                        }
                    }

                    // If date_format is specified and lastBuildDate is valid
                    if ($this->date_format != '' && ($timestamp = strtotime($result['lastBuildDate'])) !==-1) {
                        // convert lastBuildDate to specified date format
                        $result['lastBuildDate'] = date($this->date_format, $timestamp);
                    }
                    // If date_format is specified and date is valid (for Dublin Core tags only)
                    if ($this->date_format != '' && ($timestamp = strtotime($result['date'])) !==-1) {
                        // convert lastBuildDate to specified date format
                        $result['date'] = date($this->date_format, $timestamp);
                    }

                    // Parse TEXTINPUT info
                    preg_match("'<textinput(|[^>]*[^/])>(.*?)</textinput>'si", $feed_content, $out_textinfo);
                    // This a little strange regexp means:
                    // Look for tag <textinput> with or without any attributes, but skip truncated version <textinput /> (it's not beggining tag)
                    if (isset($out_textinfo[2])) {
                        foreach($this->textinputtags as $textinputtag) {
                            $temp = $this->subject_match("'<$textinputtag.*?>(.*?)</$textinputtag>'si", $out_textinfo[2]);
                            if ($temp != '') {
                                if (strstr($temp, "://")) {
                                    $temp = "<a href=\"".$temp."\">".$temp."</a>";                  //  create URI
                                }
                                $result['textinput_'.$textinputtag] = $temp;                        // set only if not empty
                            }
                        }
                    }
                    // Parse IMAGE info
                    preg_match("'<image.*?>(.*?)</image>'si", $feed_content, $out_imageinfo);
                    if (isset($out_imageinfo[1])) {
                        foreach($this->imagetags as $imagetag) {
                            $temp = $this->subject_match("'<$imagetag.*?>(.*?)</$imagetag>'si", $out_imageinfo[1]);
                            if ($temp != '') $result['image_'.$imagetag] = $temp;                   // set only if not empty
                        }
                    }

                    $result['sub_count'] = $i;
                    return $result;
                }
                // **********  For Atom feeds enter here **********
                if ($result['type'] == 'Atom') {
                    // First parse all ENTRIES
                    $feed_entries = array();
                    preg_match_all("'<entry(| .*?)>(.*?)</entry>'si", $feed_content, $entries);
                    $feed_entries = $entries[2];
                    $i = 0;
                    $result['entries'] = array(); // create array even if there are no entries
                    foreach($feed_entries as $feed_entry) {
                        // If number of entries is lower then limit: Parse one entry
                        if ($i < $this->limit) {
                            // if available in ENTRY-tag,, parse LINK tag
                            if (in_array("link", $this->entrytags)) {
                                preg_match("'<link.*?=[\'\"](.*?)[\'\"]'si", $feed_entry, $out_link);
                                if (isset($out_link[1])) {
                                    $result['entries'][$i]['link'] = "<a href=\"".$out_link[1]."\">".$out_link[1]."</a>" ; // set only if not empty
                                }
                            }

                            // if available in ENTRY-tag, parse also all AUTHOR tags
                            if (in_array("author", $this->entrytags)) {
                                preg_match_all("'<author.*?>(.*?)</author?>'si", $feed_entry, $out_author);
                                if (isset($out_author[1])) {
                                    $all_auth = implode(" , ", $out_author[1]);
                                    foreach($this->authortags as $authortag) {
                                        $author_temp = $this->subject_match_all("'<$authortag.*?>(.*?)</$authortag>'si", $all_auth);
                                        if ($author_temp != '') {
                                            if (strstr($author_temp, "://")) {
                                                $author_temp = "<a href=\"".$author_temp."\">".$author_temp."</a>";     //  create URI
                                            }
                                            $result['entries'][$i]['author_'.$authortag] = $author_temp;                // set only if not empty
                                        }
                                    }
                                    $feed_entry = preg_replace("'<author.*?>(.*?)</author?>'si", "", $feed_entry);      //  in order not to interference with the CONTRIBUTOR tag, kill this part
                                }
                            }

                            // if available in ENTRY-tag, parse also all  CONTRIBUTOR tags
                            if (in_array("contributor", $this->entrytags)) {
                                preg_match_all("'<contributor.*?>(.*?)</contributor?>'si", $feed_entry, $out_contri);
                                if (isset($out_contri[1])) {
                                    $all_contri = implode(" , ", $out_contri[1]);
                                    foreach($this->contributortags as $contritag) {
                                        $contri_temp = $this->subject_match_all("'<$contritag.*?>(.*?)</$contritag>'si", $all_contri);
                                        if ($contri_temp != '') {
                                            if (strstr($contri_temp, "://")) {
                                                $contri_temp = "<a href=\"".$contri_temp."\">".$contri_temp."</a>";     //  create URI
                                            }
                                            $result['entries'][$i]['contributor_'.$contritag] = $contri_temp;           // set only if not empty
                                        }
                                    }
                                    $feed_entry = preg_replace("'<contributor.*?>(.*?)</contributor?>'si", "", $feed_entry);    //  in order not to interference with the CATEGORY tag, kill this part
                                }
                            }

                            // if available in ENTRY-tag, parse also all CATEGORY tags
                            if (in_array("category", $this->entrytags)) {
                                preg_match_all("'<category.*?>(.*?)</category?>'si", $feed_entry, $out_cat);
                                if (isset($out_cat[1])) {
                                    $all_cats = implode(" , ", $out_coat[1]);
                                    foreach($this->categorytags as $cattag) {
                                        $cat_temp = $this->subject_match_all("'<$cattag.*?>(.*?)</$cattag>'si", $all_cats);
                                        if ($cat_temp != '') {
                                            if (strstr($cat_temp, "://")) {
                                                $cat_temp = "<a href=\"".$cat_temp."\">".$cat_temp."</a>";
                                            }
                                            $result['entries'][$i]['category_'.$cattag] = $cat_temp; // Set only if not empty
                                        }
                                    }
                                    $feed_entry = preg_replace("'<category.*?>(.*?)</category?>'si", "", $feed_entry);      //  kill all CATEGORY tags in this ENTRY tag
                                }
                            }

                            //     Walk through all other ENTRY tags
                            foreach($this->entrytags as $entrytag) {
                                $temp = $this->subject_match("'<$entrytag.*?>(.*?)</$entrytag>'si", $feed_entry);
                                if ($temp != '' && ($entrytag != 'author' || $entrytag != 'catergory' || $entrytag != 'contributor')) {
                                    if (strstr($temp, "://")) {
                                        $temp = "<a href=\"".$temp."\">".$temp."</a>";
                                    }
                                    $result['entries'][$i][$entrytag] = $temp;                      // set only if not empty
                                }
                            }

                            // If date_format is specified and UPDATED is valid
                            if ($this->date_format != '' && ($timestamp = strtotime($result['entries'][$i]['updated'])) !==-1) {
                                // Convert UPDATED to specified date format
                                $result['entries'][$i]['updated'] = date($this->date_format, $timestamp);
                            }
                            // If date_format is specified and PUBLISHED is valid
                            if ($this->date_format != '' && ($timestamp = strtotime($result['entries'][$i]['published'])) !==-1) {
                                // Convert PUBLISHED to specified date format
                                $result['entries'][$i]['published'] = date($this->date_format, $timestamp);
                            }

                            $i++;    // ENTRY counter
                        }
                    }
                    $result['sub_count'] = $i;
                    $feed_content =  preg_replace("'<entry(| .*?)>(.*?)</entry>'si", " ", $feed_content);   //      Kill all ENTRY tags

                    // Now parse outside of ENTRIES
                    //  If available process AUTHOR
                    if (in_array("author", $this->metatags)) {
                        preg_match_all("'<author.*?>(.*?)</author?>'si", $feed_content, $out_author);
                        if (isset($out_author[1])) {
                            $all_auth = implode(" , ", $out_author[1]);
                            foreach($this->authortags as $authortag) {
                                $temp = $this->subject_match_all("'<$authortag.*?>(.*?)</$authortag>'si", $all_auth);
                                if ($temp != '') {
                                    if (strstr($temp, "://")) {
                                        $temp = "<a href=\"".$temp."\">".$temp."</a>";
                                    }
                                    $result['author_'.$authortag] = $temp;                          // set only if not empty
                                }
                            }
                            $feed_content =  preg_replace("'<author(.*?)</author>'si", " ", $feed_content);   //      Kill all AUTHOR tag
                        }
                    }

                    // If available, parse LINK tag
                    if (in_array("link", $this->metatags)) {
                        preg_match("'<link.*?=[\'\"](.*?)[\'\"]'si", $feed_content, $out_link);
                        if (isset($out_link[1])) {
                            $result['link'] = "<a href=\"".$out_link[1]."\">".$out_link[1]."</a>" ; // set only if not empty
                        }
                    }

                    // Parse CATEGORY tags
                    if (in_array("category", $this->metatags)) {
                        preg_match_all("'<category.*?>(.*?)</category?>'si", $feed_content, $out_category);
                        if (isset($out_category[1])) {
                            $all_cats = implode(" , ", $out_author[1]);
                            foreach($this->categorytags as $categorytag) {
                                $temp = $this->subject_match_all("'<$categorytag.*?>(.*?)</$categorytag>'si", $out_cats);
                                if ($temp != ''){
                                    if (strstr($temp, "://")) {
                                        $temp = "<a href=\"".$temp."\">".$temp."</a>";
                                    }
                                    $result['category_'.$categorytag] = $temp;                      // set only if not empty
                                }
                            }
                            $feed_content =  preg_replace("'<category(.*?)</category>'si", " ", $feed_content);   //      Kill all AUTHOR tag
                        }
                    }

                    // Parse CONTRIBUTOR tags
                    if (in_array("contributor", $this->metatags)) {
                        preg_match_all("'<contributor.*?>(.*?)</contributor?>'si", $feed_content, $out_contri);
                        if (isset($out_contri[1])) {
                            $all_contri = implode(" , ", $out_author[1]);
                            foreach($this->contributortags as $contritag) {
                                $temp = $this->subject_match_all("'<$contritag.*?>(.*?)</$contritag>'si", $all_contri);
                                if ($temp != ''){
                                    if (strstr($temp, "://")) {
                                        $temp = "<a href=\"".$temp."\">".$temp."</a>";
                                    }
                                    $result['contributor_'.$contritag] = $temp;                     // set only if not empty
                                }
                            }
                            $feed_content =  preg_replace("'<contributor(.*?)</contributor>'si", " ", $feed_content);   //      Kill all AUTHOR tag
                        }
                    }

                    // Parse GENERATOR tags
                    if (in_array("generator", $this->metatags)) {
                        preg_match("'<generator.*?>(.*?)</generator?>'si", $feed_content, $out_generator);
                        if (isset($out_generator[1])) {
                            foreach($this->generatortags as $generatortag) {
                                $temp = $this->subject_match("'<$generatortag.*?>(.*?)</$generatortag>'si", $out_generator[1]);
                                if ($temp != ''){
                                    if (strstr($temp, "://")) {
                                        $temp = "<a href=\"".$temp."\">".$temp."</a>";
                                    }
                                    $result['generator_'.$generatortag] = $temp;                    // set only if not empty
                                }
                            }
                            $feed_content =  preg_replace("'<generator(.*?)</generator>'si", " ", $feed_content);   //      Kill all AUTHOR tag
                        }
                    }

                    //  Parse all other META tags
                    foreach($this->metatags as $metatag){
                        $temp = $this->subject_match("'<$metatag.*?>(.*?)</$metatag>'si", $feed_content);
                        if ($temp != ''){
                            if (strstr($temp, "://")) {
                                $temp = "<a href=\"".$temp."\">".$temp."</a>";
                            }
                            $result[$metatag] = $temp;                                              // set only if not empty
                        }
                    }

                    return $result;
                }

                // **********  For RSD feeds enter here **********
                if ($result['type'] == 'RSD') {
                    //$result['title'] = "RSD feed";                                                 //  virtual title, as RSD feeds do not contain a real title
                    preg_match("'<apis>(.*?)</apis>'si",  $feed_content, $service);                 //  get only the APIS tag of the RSD feed
                    $content =  $service[1];
                    $content = preg_replace("/[  |\r\n]+/i", " ", $service[1]);
                    $feed_apis = array();
                    $result['api'] = array();                                                       // create array even if there are no API tags
                    $i = 0;

                    //  Parse all 1.order API tags
                    preg_match_all("'<api(.*?)\/>'si", $content, $apis);                            //  get all api tags
                    $feed_apis = $apis[1];

                    foreach($feed_apis as $api) {
                        // If number of API is lower then limit: Parse one API tag
                        if ($i < $this->limit) {
                            //      preferred  is TRUE?
                            preg_match("'preferred(.*?)=\"(.*?)\"'si", $api, $reg);
                            $preferred = $reg[2];

                            //  follow Admin setting how to proceed
                            if ($this->pro == '0' | (preg_match("/TRUE/i", $preferred) && $this->pro == '1')) {
                                //     Walk through all API tags
                                foreach($this->apitags as $apitag) {
                                    $temp = $this->subject_match("'$apitag=\"(.*?)\"'si", $api);

                                    if (strstr($temp, "://")) {
                                        $temp = "<a href=\"".$temp."\">".$temp."</a>";
                                    }
                                    $result['api'][$i][$apitag] = $temp;                            // set only if not empty
                                }
                                $i++;
                            }
                        }
                        $content = str_replace($api, " ", $content);
                    }

                    //  Parse all 2.order API tags
                    preg_match_all("'<api(.*?)<\/api>'si", $content, $apis);                        //      get all API tags
                    $feed_apis = $apis[1];

                    foreach($feed_apis as $api) {
                        // If number of API is stil lower then limit: Parse one API tag
                        if ($i < $this->limit) {
                            //      preferred  is TRUE?
                            preg_match("'preferred(.*?)=\"(.*?)\"'si", $api, $reg);
                            $preferred = $reg[2];

                            //  follow Admin directive how to proceed
                            if ($this->pro == '0' | (preg_match("/TRUE/i", $preferred) && $this->pro == '1')) {
                                //     Walk through all SETTINGS tags
                                foreach($this->settings as $setting) {
                                    $stemp = $this->subject_match("'<$setting.*?>(.*?)</$setting>'si", $api);
                                    if ($stemp != ''){
                                        if (strstr($stemp, "://")) {
                                            $stemp = "<a href=\"".$stemp."\">".$stemp."</a>";
                                        }
                                        $result['api'][$i]['settings_'.$setting] = $stemp;           // set only if not empty
                                        $api = str_replace($stemp, "", $api);                       //  kill this tag, as no longer required
                                    }
                                }

                                //     Walk through all API tags
                                foreach($this->apitags as $apitag) {
                                    $temp = $this->subject_match("'$apitag=\"(.*?)\"'si", $api);

                                    if (strstr($temp, "://")) {
                                        $temp = "<a href=\"".$temp."\">".$temp."</a>";
                                    }
                                    $result['api'][$i][$apitag] = $temp;                            // set only if not empty
                                }
                                $i++;
                            }

                        }
                        $content = str_replace($api, " ", $content);
                    }

                    $result['sub_count'] = $i;

                    //  Parse all SERVICE tags
                    preg_match("'<service>(.*?)</service>'si",  $feed_content, $service);           //   get only the active part of the RSD feed
                    $feed_content =  $service[1];
                    $feed_content =  preg_replace("'<apis(| .*?)>(.*?)</apis>'si", " ", $feed_content);   //      Kill APIS tag

                    foreach($this->servicetags as $servicetag){
                        $temp = $this->subject_match("'<$servicetag.*?>(.*?)</$servicetag>'si", $feed_content);
                        if ($temp != ''){
                            if (strstr($temp, "://")) {
                                $temp = "<a href=\"".$temp."\">".$temp."</a>";
                            }
                            $result[$servicetag] = $temp;                                           // set only if not empty
                        }
                    }

                    return $result;
                }
                return $result; //  error output
            } else {
                return FALSE;   //  error in file opening or no feed string
            }
        }
    }

?>
