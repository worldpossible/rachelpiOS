<?php
    //error_reporting (E_ALL);      //  for debug only
    $query      = strtolower($query);
    $pos        = strpos($query,":");
    $urlquery   = strip_tags(trim(substr($query,$pos+1)));
    $all_links  = array();
    $link_found = '';
    $urlparts   = parse_url($urlquery);

    if (!array_key_exists('host', $urlparts)) {
        $urlparts['host'] = '';
    }

    $urlquery   = $urlparts['host']."".$urlparts['path'];

    function multiple_choice($num_rows, $res_array, $db_slv, $urlquery) {
        global $db_con, $debug, $sph_messages, $search_script, $out, $xml_name, $start_all;

        if ($out == 'xml' && $xml_name) {    //prepare XML output file
            multiple_link_xml($num_rows, $res_array, $urlquery, $start_all);
        }

        echo "
                <div class='panel'>
                <table width='100%'>
                <div class='tblhead red'>
                ".$sph_messages['mulChoice']."
                </div>
            ";

        $class = "evrow";
        for ($i=0; $i<$num_rows; $i++) {
            $url2           = $res_array[$i]["url"];
            $indexdate      = $res_array[$i]["indexdate"];
            $num = $i+1;

            echo "
                    <tr class='$class'>
                    <td>
                    $num.
                    </td>
                    <td>
                    <a href='./$search_script?query_t=site:$url2&search=1'> $url2 </a>
                    </td>
                    <td>
                    indexed: $indexdate
                ";

                    if(ceil($num/10) == $num/10) {      // This routine places a "to page top" link on every 10th record
                        echo "<a class='navup' href='#top' title='Jump to Page Top'>Top</a>
                    ";
                    }

                    echo "
                    </td>
                    </tr>
                ";

                    if ($class =="evrow") {
                        $class = "odrow";
                    }else{
                        $class = "evrow";
                    }
        }

        echo "
                </table>
                <br />
                </div>
            ";
        return('');
    }

    //      print header for link search
    echo "<br />
            <center>
            <div class=\"mainlist\">
            <font color=\"red\">".$sph_messages['LinkSearch']."</font>
            <br />
            ".$sph_messages['Resfor']." \"$urlquery\"
            <br />
            </div>
            <br />
            <div id=\"results\">
            ";

    //      Search for URLs that were already indexed.
    if ($db1_slv == 1 && !$user_db || $user_db == 1) {  //  as defined in Admin's Database Management settings
        if ($debug_user == '1') {                            //  inform about  databases, which delivers results
            echo "<br />
                    <div id=\"results\">
                    Results from database 1:
                    <br />
                    </div>
                    ";
        }
        $db_con = db_connect($mysql_host1, $mysql_user1, $mysql_password1, $database1);
        if ($prefix > '0' ) {               //      if requested by the Search Form, we need to use the shifted table-suffix
            $mysql_table_prefix = $prefix;  //  replace the tablesuffix
        } else {
            $mysql_table_prefix = $mysql_table_prefix1; //  use default suffix for this db
        }

        //  search for sites in this database
        $sql_query  =  "SELECT * from ".$mysql_table_prefix."sites where url like '%$urlquery%' AND indexdate != '0000-00-00'";
        $result     = $db_con->query($sql_query);
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

        if ($num_rows) {
            $link_found = '1';
            $res_array  = array();
            while ($this_array = $result->fetch_array(MYSQLI_ASSOC)) {
                $res_array[] = $this_array;
            }
        }

        if ($num_rows > '1') {  //      multiple choice, select one URL
            multiple_choice($num_rows, $res_array, 1, $urlquery);
        }
        if ($num_rows == '1') {
            $links = show_links($query, $urlquery, $res_array, $mysql_table_prefix, $start_all, 1, $results_per_page, $db, $prefix, $start_links);
        }
    }

    if ($db2_slv == 1 && !$user_db || $user_db == 2) {  //  as defined in Admin's Database Management settings
        if ($debug_user == '1') {                            //  inform about  databases, which delivers results
            echo "<br />
                    <div id=\"results\">
                    Results from database 2:
                    <br />
                    </div>
                    ";
        }
        $db_con = db_connect($mysql_host2, $mysql_user2, $mysql_password2, $database2);
        if ($prefix > '0' ) {               //      if requested by the Search Form, we need to use the shifted table-suffix
            $mysql_table_prefix = $prefix;  //  replace the tablesuffix
        } else {
            $mysql_table_prefix = $mysql_table_prefix2; //  use default suffix for this db
        }

        //  search for sites in this database
        $sql_query  =  "SELECT * from ".$mysql_table_prefix."sites where url like '%$urlquery%' AND indexdate != ''";
        $result     = $db_con->query($sql_query);
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

        if ($num_rows) {
            $link_found = '1';
            $res_array  = array();
            while ($this_array = $result->fetch_array(MYSQLI_ASSOC)) {
                $res_array[] = $this_array;
            }
        }

        if ($num_rows > '1') {  //      multiple choice, select one URL
            multiple_choice($num_rows, $res_array, 2, $urlquery);
        }
        if ($num_rows == '1') {
            $links = show_links($query, $urlquery, $res_array, $mysql_table_prefix, $start_all, 2, $results_per_page, $db, $prefix, $start_links);
        }
    }

    if ($db3_slv == 1 && !$user_db || $user_db == 3) {  //  as defined in Admin's Database Management settings
        if ($debug_user == '1') {                            //  inform about  databases, which delivers results
            echo "<br />
                    <div id=\"results\">
                    Results from database 3:
                    <br />
                    </div>
                    ";
        }
        $db_con = db_connect($mysql_host3, $mysql_user3, $mysql_password3, $database3);
        if ($prefix > '0' ) {               //      if requested by the Search Form, we need to use the shifted table-suffix
            $mysql_table_prefix = $prefix;  //  replace the tablesuffix
        } else {
            $mysql_table_prefix = $mysql_table_prefix3; //  use default suffix for this db
        }

        //  search for sites in this database
        $sql_query  =  "SELECT * from ".$mysql_table_prefix."sites where url like '%$urlquery%' AND indexdate != ''";
        $result     = $db_con->query($sql_query);
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

        if ($num_rows) {
            $link_found = '1';
            $res_array  = array();
            while ($this_array = $result->fetch_array(MYSQLI_ASSOC)) {
                $res_array[] = $this_array;
            }
        }

        if ($num_rows > '1') {  //      multiple choice, select one URL
            multiple_choice($num_rows, $res_array, 3, $urlquery);
        }
        if ($num_rows == '1') {
            $links = show_links($query, $urlquery, $res_array, $mysql_table_prefix, $start_all, 3, $results_per_page, $db, $prefix, $start_links);
        }
    }

    if ($db4_slv == 1 && !$user_db || $user_db == 4) {  //  as defined in Admin's Database Management settings
        if ($debug_user == '1') {                            //  inform about  databases, which delivers results
            echo "<br />
                    <div id=\"results\">
                    Results from database 4:
                    <br />
                    </div>
                    ";
        }
        $db_con = db_connect($mysql_host4, $mysql_user4, $mysql_password4, $database4);
        if ($prefix > '0' ) {               //      if requested by the Search Form, we need to use the shifted table-suffix
            $mysql_table_prefix = $prefix;  //  replace the tablesuffix
        } else {
            $mysql_table_prefix = $mysql_table_prefix4; //  use default suffix for this db
        }

        //  search for sites in this database
        $sql_query  =  "SELECT * from ".$mysql_table_prefix."sites where url like '%$urlquery%' AND indexdate != ''";
        $result     = $db_con->query($sql_query);
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

        if ($num_rows) {
            $link_found = '1';
            $res_array  = array();
            while ($this_array = $result->fetch_array(MYSQLI_ASSOC)) {
                $res_array[] = $this_array;
            }
        }

        if ($num_rows > '1') {  //      multiple choice, select one URL
            multiple_choice($num_rows, $res_array, 4, $urlquery);
        }
        if ($num_rows == '1') {
            $links = show_links($query, $urlquery, $res_array, $mysql_table_prefix, $start_all, 4, $results_per_page, $db, $prefix, $start_links);
        }
    }

    if ($db5_slv == 1 && !$user_db || $user_db == 1) {  //  as defined in Admin's Database Management settings
        if ($debug_user == '1') {                            //  inform about  databases, which delivers results
            echo "<br />
                    <div id=\"results\">
                    Results from database 5:
                    <br />
                    </div>
                    ";
        }
        $db_con = db_connect($mysql_host5, $mysql_user5, $mysql_password5, $database5);
        if ($prefix > '0' ) {               //      if requested by the Search Form, we need to use the shifted table-suffix
            $mysql_table_prefix = $prefix;  //  replace the tablesuffix
        } else {
            $mysql_table_prefix = $mysql_table_prefix5; //  use default suffix for this db
        }

        //  search for sites in this database
        $sql_query  =  "SELECT * from ".$mysql_table_prefix."sites where url like '%$urlquery%' AND indexdate != ''";
        $result     = $db_con->query($sql_query);
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

        if ($num_rows) {
            $link_found = '1';
            $res_array  = array();
            while ($this_array = $result->fetch_array(MYSQLI_ASSOC)) {
                $res_array[] = $this_array;
            }
        }

        if ($num_rows > '1') {  //      multiple choice, select one URL
            multiple_choice($num_rows, $res_array, 5, $urlquery);
        }
        if ($num_rows == '1') {
            $links = show_links($query, $urlquery, $res_array, $mysql_table_prefix, $start_all, 5, $results_per_page, $db, $prefix, $start_links);
        }
    }

    if ($link_found != 1) {   //      Nothing found in any database
        $noMatch = str_replace ('%query', $urlquery, $sph_messages["noSiteMatch"]);
        echo "
                <br>
                <div class='tblhead red'>
                $noMatch
                </div>
            ";
                if (!$embedded) {
                    echo "
                    </body>
                    </html>
                ";
                }
    }

    //      display ''Suggest a new URL' and 'footer'
    if (!$embedded) {
        include "".$template_dir."/html/090_footer.html" ;
    } else {
        include "".$template_dir."/html/091_footer.html" ;    //    no </body> and >/html>
    }

    //  wait for next query
    die ('');

    //      show all links of this URL.
    function show_links($query_t, $urlquery, $res_array, $mysql_table_prefix, $start_all, $db_slv, $results_per_page, $db, $prefix, $start_links) {
        global $db_con, $debug, $sph_messages, $minus, $prev, $inc, $plus, $pages, $search_script, $out;

        $id         = $res_array[0]["site_id"];
        $sql_query  = "SELECT * from ".$mysql_table_prefix."links where site_id = '$id'";
        $result     = $db_con->query($sql_query);
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

        $links = array();

        if ($num_rows == 0) {   //      No links found
            echo "
                <br />
                    <div class='warn cntr'>
                    ".$sph_messages['noLinks']."
                    </div>
                ";
        } else {
            while ($this_array = $result->fetch_array(MYSQLI_ASSOC)) {
                $links[] = $this_array;
            }
        }

        if ($out == 'xml') {
            link_xml($links, $query_t, $urlquery, $start_all);
        }

        //Prepare header and all results for listing
        $pages = ceil($num_rows / $results_per_page);   // Calculate count of required pages

        if (empty($start_links)) $start_links = '1';    // As $start_links is not yet defined this is required for the first result page
        if ($start_links == '1') {
            $from = '0';                                // Also for first page in order not to multipy with 0
        }else{
            $from = ($start_links-1) * $results_per_page;   // First $num_row of actual page
        }

        $to = $num_rows;                                // Last $num_row of actual page
        $rest = $num_rows - $start_links;
        if ($num_rows > $results_per_page) {            // Display more then one page?
            $rest = $num_rows - $from;
            $to = $from + $rest;                        // $to for last page
            if ($rest > $results_per_page) {
                $to = $from + ($results_per_page);      // Calculate $num_row of actual page
            }
        }

        if ($num_rows > 0) {    //      Display header and results
            $fromm = $from+1;

            echo "
                    </div>
                    <br />
                    <div class='results'>
                    <table width='100%'>
                ";
            if ($pages > 1) {

                echo "<div class='tblhead'>
                    ".$sph_messages['matches']." $fromm - $to&nbsp;&nbsp;".$sph_messages['from']." $num_rows
                    </div>
                ";
            }

            $class = "evrow";
            for ($i=$from; $i<$to; $i++) {     //      get all results and show them
                $url2           = stripslashes($links[$i]["url"]);  //  remove backslashes
                $title          = stripslashes($links[$i]["title"]);
                $description    = $links[$i]["description"];
                $page_size      = $links[$i]["size"];
                $num = $i+1;

                if ($num == 1){
                    echo "
                        <tr class='$class bd'>
                    ";
                } else {
                    echo "
                            <tr class='$class '>
                        ";
                }
                echo "
                        <td>
                        $num. <a href='$url2' target='_blank' title='Open Link in a new window'>$title
                    ";
                        if (!$title) {
                            echo "
                            ".$sph_messages['notitle']."
                        ";
                        }
                        echo "
                        </a>
                        <br />
                        $description
                    ";
                        if (!$description) {
                            echo "
                            ".$sph_messages['nodes']."
                        ";
                        }
                        echo "
                        <br />
                        $url2 &nbsp;&nbsp;($page_size kB)
                    ";

                        if ($num == 1) {
                            echo "&nbsp;&nbsp;&nbsp;&nbsp;
                            ".$sph_messages['MainURL']."
                        ";
                        }

                        if(ceil($num/10) == $num/10) {      // This routine places a "to page top" link on every 10th record
                            echo "<a class='navup' href='#top' title='Jump to Page Top'>Top</a>
                        ";
                        }
                        if ($num_rows == 1) {   //      No links found
                            echo "<br />
                            <div class='warn cntr'>
                            ".$sph_messages['noLinks']."
                            </div>
                        ";
                        }
                        echo "
                        </tr>
                    ";

                        if ($class =="evrow") {
                            $class = "odrow";
                        }else{
                            $class = "evrow";
                        }
            }
        }

        // Display end of table
        if ($num_rows > 0) {
            echo "</table>
                    </div>
                ";

            if ($pages > 1) { // If we have more than 1 result-page
                echo "
                        <div class='submenu cntr'>
                        ".$sph_messages['Result page'].": $start_links ".$sph_messages['from']." $pages
                        &nbsp;&nbsp;&nbsp;
                    ";

                if($start_links > 1) { // Display 'First'
                    echo "
                            <a href='$search_script?query_t=$query_t&amp;start_links=1&amp;results=$results_per_page&amp;search=1&amp;db=$db&amp;prefix=$prefix'>".$sph_messages['First']."</a>&nbsp;&nbsp;
                        ";

                    if ($start_links > 5 ) { // Display '-5'
                        $minus = $start_links-5;
                        echo "
                            <a href='$search_script?query_t=$query_t&amp;start_links=$minus&amp;results=$results_per_page&amp;search=1&amp;db=$db&amp;prefix=$prefix'>- 5</a>&nbsp;&nbsp;
                        ";
                    }
                }
                if($start_links > 1) { // Display 'Previous'
                    $prev = $start_links-1;
                    echo "
                            <a href='$search_script?query_t=$query_t&amp;start_links=$prev&amp;results=$results_per_page&amp;search=1&amp;db=$db&amp;prefix=$prefix'>".$sph_messages['Previous']."</a>&nbsp;&nbsp;
                        ";
                }
                if($rest >= $results_per_page) { // Display 'Next'
                    $inc = $start_links+1;
                    echo "
                            <a href='$search_script?query_t=$query_t&amp;start_links=$inc&amp;results=$results_per_page&search=1&amp;db=$db&amp;prefix=$prefix' >".$sph_messages['Next']."</a>&nbsp;&nbsp;
                        ";

                    if ($pages-$start_links > 5 ) { // Display '+5'
                        $plus = $start_links+5;
                        echo "
                             <a href='$search_script?query_t=$query_t&amp;start_links=$plus&amp;results=$results_per_page&amp;search=1&amp;db=$db&amp;prefix=$prefix'>+ 5</a>&nbsp;&nbsp;
                            ";
                    }
                }
                if($start_links < $pages) { // Display 'Last'
                    echo "
                         <a href='$search_script?query_t=$query_t&amp;start_links=$pages&amp;results=$results_per_page&amp;search=1&amp;db=$db&amp;prefix=$prefix'>".$sph_messages['Last']."</a>
                        ";
                }
                echo "</div>
                    ";
            }
        }
        return ('');
    }

?>