<?php

    error_reporting (E_ALL ^ E_WARNING ^ E_NOTICE ^ E_STRICT);

    define("_SECURE",1);            // define secure constant

    include "admin_header.php";     //  display header

    $query  = '';
    $submit = '';
    $start  = '';

    extract (getHttpVars());        //  get all the passed input variables

    if (isset($query)){             //  contains the query string
        $query = trim(substr($query,0,50));
    }

    if (isset($submit)){            //  contains search mode (sites, links, keywords)
        $submit = substr(trim($submit),0,50);
    }

    if (isset($start)){             //  number of result page to be displayed
        $start = substr(trim($start), 0, 6);
        if (!preg_match("/^[0-9]+$/", $start)) {
            $start = '1';
        }
    }

    //      get active database for Admin
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

    //  for any 'submit' AND 'query' AND 'start' input we will produce some useful output
    if ($query && $submit && $start) {

        //  first 'site' search
        if ($query && strstr($submit, "sites")) {

            //  display headline
            echo "    <h1>Results for 'site' search and query: <span class='red'> $query </span></h1>";

            $sql_query ="SELECT * from ".$mysql_table_prefix."sites where url like '%$query%'";
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
                printf("<p>$sql_query</p>");
                exit;
            }

            $rows       = array();
            $sites      = array ();
            $num_rows   = $result->num_rows;

            if ($num_rows) {
                while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    $sites[] = $row;
                }
            }

            if ($sites) {
                $pages = ceil($num_rows / $results_per_page);   // Calculate count of required pages

                if($start > $pages) $start = $pages;

                if ($start < 1) $start = '1';                   // As $start is not yet well defined, this is required for the first result page
                if ($start == '1') {
                    $from = '0';                                // Also for first page in order not to multipy with 0
                }else{
                    $from = ($start-1) * $results_per_page;         // First $num_row of actual page
                }

                $fromm      = $from+1;
                $to = $num_rows;                                // Last $num_row of actual page
                $rest = $num_rows - $start;
                if ($num_rows > $results_per_page) {            // Display more then one page?
                    $rest = $num_rows - $from;
                    $to = $from + $rest;                        // $to for last page
                    if ($rest > $results_per_page) $to = $from + ($results_per_page); // Calculate $num_row of actual page
                }

                //  display result header
                echo "
            <br /><br />
            <table width='97%'>
                <tr>
                    <td class='tblhead sml'>Displaying result ".$from." - ".$to." from ".$num_rows." results</td>
                </tr>
            </table>
            <table width='97%'>
                <tr>
                    <td class='tblhead sml'>No.</td>
                    <td class='tblhead sml'>URL</td>
                    <td class='tblhead sml'>Indexed</td>
                    <td class='tblhead sml'>Link count</td>
                    <td class='tblhead sml'>Site</td>
                </tr>";

                //  prepare all site results
                for ($i=$from; $i<$to; $i++) {

                    $n          = $i+1;
                    $site_id    = $sites[$i]["site_id"];
                    $url        = $sites[$i]["url"];
                    $indexdate  = $sites[$i]["indexdate"];
                    if(!$indexdate) {
                        $indexdate = "---";
                    }

                    //  get count of links for this site
                    $sql_qry = "SELECT * from ".$mysql_table_prefix."links where site_id like '$site_id'";
                    $res = $db_con->query($sql_qry);
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
                        printf("<p>$sql_qry</p>");
                        exit;
                    }

                    $links = $result->num_rows;

                    if ($class =="evrow") {
                        $class = "odrow";
                    } else {
                        $class = "evrow";
                    }

                    //  dispaly results for this site
                    echo "
                <tr class='$class'>
                    <td class='sml cntr'>$n</td>
                    <td class='sml'>&nbsp;&nbsp;<a href='".stripslashes($url)."' target='_blank' title='Visit site in new window'>".stripslashes($url)."</a></td>
                    <td class='sml cntr'>".stripslashes($indexdate)."</td>
                    <td class='sml cntr'>".stripslashes($links)."</td>
                    <td class='sml cntr'><a href='admin.php?f=20&amp;site_id=$site_id' class='options' title='Click to browse site options'>Options</a></td>
                </tr>";
                }

                echo "
            </table>";

            } else {
                echo "  <br /><br />
                        <p class='cntr'>Nothing found for site query: '$query'<p>
                        ";
            }
        }

        //  now 'link' search
        if($query && strstr($submit, "links")) {

            //  display headline
            echo "      <h1>Results for 'link' search and query: <span class='red'> $query </span></h1>";

            $sql_query ="SELECT * from ".$mysql_table_prefix."links where url like '%$query%'";
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
            $rows       = array();
            $links      = array();
            $num_rows   = $result->num_rows;

            if ($num_rows) {
                while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    $links[] = $row;
                }
            }

            if ($links) {

                $pages = ceil($num_rows / $results_per_page);   // Calculate count of required pages

                if($start > $pages) $start = $pages;

                if ($start < 1) $start = '1';                   // As $start is not yet well defined, this is required for the first result page
                if ($start == '1') {
                    $from = '0';                                // Also for first page in order not to multipy with 0
                }else{
                    $from = ($start-1) * $results_per_page;         // First $num_row of actual page
                }

                $fromm      = $from+1;
                $to = $num_rows;                                // Last $num_row of actual page
                $rest = $num_rows - $start;
                if ($num_rows > $results_per_page) {            // Display more then one page?
                    $rest = $num_rows - $from;
                    $to = $from + $rest;                        // $to for last page
                    if ($rest > $results_per_page) $to = $from + ($results_per_page); // Calculate $num_row of actual page
                }

                //  display result header
                echo "
            <br /><br />
            <table width='97%'>
                <tr>
                   <td class='tblhead sml'>Displaying result ".$from." - ".$to." from ".$num_rows." total results</td>
                </tr>
            </table>
            <table width='97%'>
                <tr>
                    <td class='tblhead sml'>No.</td>
                    <td class='tblhead sml'>URL</td>
                    <td class='tblhead sml'>Indexed</td>
                </tr>";

                //  prepare all link results
                for ($i=$from; $i<$to; $i++) {

                    $n          = $i+1;
                    $url        = $links[$i]["url"];
                    $indexdate  = $links[$i]["indexdate"];

                    if(!$indexdate) {
                        $indexdate = "---";
                    }

                    if ($class =="evrow") {
                        $class = "odrow";
                    } else {
                        $class = "evrow";
                    }

                    //  dispaly results for this link
                    echo "
                <tr class='$class'>
                    <td class='sml cntr'>$n</td>
                    <td class='sml'>&nbsp;&nbsp;<a href='".stripslashes($url)."' target='_blank' title='Visit site in new window'>".stripslashes($url)."</a></td>
                    <td class='sml cntr'>".stripslashes($indexdate)."</td>
                </tr>";
                }

                echo "
            </table>";

            } else {
                echo "  <br /><br />
                        <p class='cntr'>Nothing found for link query: '$query'<p>
                        ";
            }

        }

        //  now the 'keyword' search
        if($query && strstr($submit, "keywords")) {

            //  display headline
            echo "    <h1>Results for 'keyword' search and query: <span class='red'> $query </span></h1>";

            $sql_query ="SELECT * from ".$mysql_table_prefix."keywords where keyword like '%$query%'";
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
            $rows       = array();
            $keywords   = array();
            $num_rows   = $result->num_rows;

            if ($num_rows) {
                while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    $keywords[] = $row;
                }
            }

            if ($keywords) {
                $num_rows = $result->num_rows;

                $pages = ceil($num_rows / $results_per_page);   // Calculate count of required pages

                if($start > $pages) $start = $pages;

                if ($start < 1) $start = '1';                   // As $start is not yet well defined, this is required for the first result page
                if ($start == '1') {
                    $from = '0';                                // Also for first page in order not to multipy with 0
                }else{
                    $from = ($start-1) * $results_per_page;         // First $num_row of actual page
                }

                $fromm      = $from+1;
                $to = $num_rows;                                // Last $num_row of actual page
                $rest = $num_rows - $start;
                if ($num_rows > $results_per_page) {            // Display more then one page?
                    $rest = $num_rows - $from;
                    $to = $from + $rest;                        // $to for last page
                    if ($rest > $results_per_page) $to = $from + ($results_per_page); // Calculate $num_row of actual page
                }

                //  display result header
                echo "
            <br /><br />
            <table width='97%'>
                <tr>
                    <td class='tblhead sml'>Displaying result ".$from." - ".$to." from ".$num_rows." results</td>
                </tr>
            </table>";

                //  prepare all keywors results
                for ($i=$from; $i<$to; $i++) {
                    $found_keywords   .= "&nbsp;&nbsp;&nbsp;&nbsp;".($keywords[$i]["keyword"])."<br />";
                }

                echo "<table width='97%'>
                <tr>
                    <td class='sml'><br />$found_keywords<br /><br /></td>
                </tr>";
                echo "
            </table>";
            } else {
                echo "  <br /><br />
                        <p class='cntr'>No keyword found for query: '$query'<p>
                        ";
            }

        }

        //  finally the 'category' search
        if($query && strstr($submit, "categories")) {

            //  display headline
            echo "    <h1>Results for 'category' search and query: <span class='red'> $query </span></h1>";

            $sql_query ="SELECT * from ".$mysql_table_prefix."categories where category like '%$query%'";
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
            $rows = array();

            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $categors[] = $row;
            }

            if ($categors) {
                $num_rows = $result->num_rows;

                $pages = ceil($num_rows / $results_per_page);   // Calculate count of required pages

                if($start > $pages) $start = $pages;

                if ($start < 1) $start = '1';                   // As $start is not yet well defined, this is required for the first result page
                if ($start == '1') {
                    $from = '0';                                // Also for first page in order not to multipy with 0
                }else{
                    $from = ($start-1) * $results_per_page;         // First $num_row of actual page
                }

                $fromm      = $from+1;
                $to = $num_rows;                                // Last $num_row of actual page
                $rest = $num_rows - $start;
                if ($num_rows > $results_per_page) {            // Display more then one page?
                    $rest = $num_rows - $from;
                    $to = $from + $rest;                        // $to for last page
                    if ($rest > $results_per_page) $to = $from + ($results_per_page); // Calculate $num_row of actual page
                }

                //  display result header
                echo "
            <br /><br />
            <table width='97%'>
                <tr>
                    <td class='tblhead sml'>Displaying result ".$from." - ".$to." from ".$num_rows." results</td>
                </tr>
            </table>";

                //  prepare all keywors results
                for ($i=$from; $i<$to; $i++) {
                    $categories   .= "&nbsp;&nbsp;&nbsp;&nbsp;".($categors[$i]["category"])."<br />";
                }

                //$keywords = preg_replace("/[^a-zAZ0-9,. -\/]+/si","", $keywords);
                //$keywords = str_replace("Â“", "", $keywords);

                echo "<table width='97%'>
                <tr>
                    <td class='sml'><br />$categories<br /><br /></td>
                </tr>";
                echo "
            </table>";
            } else {
                echo "  <br /><br />
                        <p class='cntr'>No category found for query: '$query'<p>
                        ";
            }

        }

        if ($pages > 1) { // If we have more than 1 result-page
            echo "<br ><br />
            <div class='submenu cntr y5'>
                Result page: $start from $pages
                <br /><br />
                <form class='cntr' name='form_page' method='post' action='admin_search.php'>
                    Page selection:&nbsp;&nbsp;&nbsp;
                    ";

            if($start > 1) { // Display 'First'
                echo "  <a href='admin_search.php?start=1&amp;query=$query&amp;submit=$submit'>First</a>&nbsp;&nbsp;
                    ";

                if ($start > 5 ) { // Display '-5'
                    $minus = $start-5;
                    echo "  <a href='admin_search.php?start=$minus&amp;query=$query&amp;submit=$submit'>- 5</a>&nbsp;&nbsp;
                    ";
                }
            }
            if($start > 1) { // Display 'Previous'
                $prev = $start-1;
                echo "  <a href='admin_search.php?start=$prev&amp;query=$query&amp;submit=$submit'>Previous</a>&nbsp;&nbsp;
                    ";
            }
            if($rest >= $results_per_page) { // Display 'Next'
                $next = $start+1;
                echo "  <a href='admin_search.php?start=$next&amp;query=$query&amp;submit=$submit' >Next</a>&nbsp;&nbsp;
                    ";

                if ($pages-$start > 5 ) { // Display '+5'
                    $plus = $start+5;
                    echo "  <a href='admin_search.php?start=$plus&amp;query=$query&amp;submit=$submit'>+ 5</a>&nbsp;&nbsp;
                        ";
                }
            }
            if($start < $pages) { // Display 'Last'
                echo "  <a href='admin_search.php?start=$pages&amp;query=$query&amp;submit=$submit'>Last</a>
                    ";
            }

            echo "&nbsp;&nbsp;&nbsp;&nbsp;Page no.&nbsp;&nbsp;
                    <input name='start' id='start' value='$start' type='text' size='4' maxlength='6' title='Enter page number to be displayed.'/>
                    &nbsp;&nbsp;
                    <input class='sbmt' type='submit' value='Jump' id='submit' title='Click once to jump to that page.' />
                    <input class='hide' type='hidden' name='query' value='$query' />
                    <input class='hide' type='hidden' name='submit' value='$submit' />
                </form>
            </div>";
        }

    } else {
        //  display query error warning
        echo "     <br /><br />
             <h1><span class='red'>&nbsp;Invalid query for Admin search !&nbsp;</span></h1>";
    }

    echo "
            <br /><br />
            <a class='bkbtn' href='admin.php?repeat=1' title='Go back'>Back to Admin's Sites view</a>
            <br /><br />
        </body>
    </html>
                ";

    exit();
?>