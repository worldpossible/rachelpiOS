<?php
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE & ~E_STRICT);
    $messages = Array (
          "Skipped_02" => Array (
    0 => "  <br /><span class='warn'>
                    >>> Skipped, because 'Authentication tag' not found in header of this site. <<<
                    </span><br /><br />  ",
    1 => "Skipped, because 'Authentication tag' not found in header of this site.\n"
    ),
          "Skipped_03" => Array (
    0 => "  <br /><span class='warn'>
                    >>> Skipped, because the value of 'Authentication tag' is invalid in header of this site. <<<
                    </span><br /><br />  ",
    1 => "Skipped, because the value of 'Authentication tag' is invalid in header of this site.\n"
    ),
         "statError" => Array (
    0 => " <br />\n<span class='warnadmin'> URL status not okay, or content is not text.</span>\n",
    1 => "  URL status not okay, or content is not text.\n"
    ),
         "noProduct" => Array (
    0 => "  <br /><br /><span class='warnadmin'>
                    No product found on this page.
                    </span>",
    1 => "No product found on this page.\n"
    ),
         "unreachable1" => Array (
    0 => "  <br /><br /><span class='warnadmin'>
                    Unable to write to local file.
                    </span>",
    1 => "Unable to write to local file.\n"
    ),
         "unreachable2" => Array (
    0 => "  <br /><br /><span class='warnadmin'>
                    Unable to open the remote file by cURL library.
                    </span>",
    1 => "Unable to open the remote file by cURL library.\n"
    ),
         "unreachable3" => Array (
    0 => "  <br /><br /><span class='warnadmin'>
                    PHP function fopen() failed to open the remote file. Also cURL library is unavailable.
                    </span>",
    1 => "PHP function fopen() failed to open the remote file. Also cURL library is unavailable.\n"
    ),
         "validSitemapInd" => Array (
    0 => "  <br /><span class='warnok'>
                    >>> Valid Sitemap index file found. <<<
                    </span><br /><br />  ",
    1 => "Valid Sitemap index file found.\n"
    ),
         "invalidSitemapInd" => Array (
    0 => "  <br /><span class='warn'>
                    >>> Unable to extract any links from that file. <<<<br /><br />
                    >>> Continue index/re-index with links extracted by Sphider-plus for this site. <<<
                    </span><br /><br />  ",
    1 => "Unable to extract any links from that file.\nContinue index/re-index with links extracted by Sphider-plus for this site.\n"
    ),
         "invalidSecSitemap" => Array (
    0 => "  <br /><span class='warn'>
                    >>> Secondary Sitemap file found for this site, but unable to extract any links from that file. <<<
                    </span><br />",
    1 => "Invalid secondary Sitemap found.\nContinue index/re-index with links extracted by Sphider-plus for this site\n"
    ),
         "validSitemap" => Array (
    0 => "  <br /><span class='warnok'>
                    >>> Valid Sitemap file found for this site <<<
                    </span><br /><br />  ",
    1 => "Valid Sitemap file found for this site.\n"
    ),
         "invalidSitemap" => Array (
    0 => "  <br /><span class='warn'>
                    >>> Sitemap file found for this site, but unable to extract any links from that file. <<< <br /><br />
                    >>> Continue index/re-index with links extracted by Sphider-plus for this site. <<<
                    </span><br /><br />",
    1 => "Continue index/re-index with links extracted by Sphider-plus for this site.\n"
    ),
         "ErasedFinished" => Array (
    0 => "  <p class='bd'><span class='em'><br /><br />Erase finished.<br /><br /></span></p>
                    <p class='evrow'><a class='bkbtn' href='admin.php' title='Go back to Admin'>Back to admin</a></p>",
    1 => "\r\nErase finished.\n"
    ),
         "ReindexFinish" => Array (
    0 => "  <p class='bd'><span class='em'><br /><br />Re-Indexing finished.<br /><br /></span></p>
                    <p class='evrow'><a class='bkbtn' href='admin.php' title='Go back to Admin'>Back to admin</a></p>",
    1 => "\r\nRe-Indexing finished.\n"
    ),
         "NewStart" => Array (
    0 => "  <div class='submenu cntr'>
                    <span class='em'>
                    Now indexing all new sites.<br /><br /><br />
                    </span></div>",
    1 => "Now indexing all new sites.\n"
    ),
         "NewFinish" => Array (
    0 => "  <p class='bd'><span class='em'><br /><br />Indexing of new sites finished.<br /><br /></span></p>
                    <p class='evrow'><a class='bkbtn' href='admin.php' title='Go back to Admin'>Back to admin</a></p>",
    1 => "Indexing of the new sites finished.\n"
    ),
         "ErasedStart" => Array (
    0 => "  <div class='submenu cntr'>
                    <span class='em'>
                    Now indexing all erased sites.<br /><br /><br />
                    </span></div>",
    1 => "Now indexing all erased sites.\n"
    ),
         "ErasedFinish" => Array (
    0 => "  <p class='bd'><span class='em'><br /><br />Indexing of erased sites finished.<br /><br /></span></p>
                    <p class='evrow'><a class='bkbtn' href='admin.php' title='Go back to Admin'>Back to admin</a></p>",
    1 => "Indexing of erased sites finished.\n"
    ),
         "SuspendedStart" => Array (
    0 => "  <div class='submenu cntr'>
                    <span class='em'>
                    Now indexing all suspended sites.<br /><br /><br />
                    </span></div>",
    1 => "Now indexing all suspended sites.\n"
    ),
         "SuspendedFinish" => Array (
    0 => "  <p class='bd'><span class='em'><br /><br />Indexing of suspended sites finished.<br /><br /></span></p>
                    <p class='evrow'><a class='bkbtn' href='admin.php' title='Go back to Admin'>Back to admin</a></p>",
    1 => "Indexing of suspended sites finished.\n"
    ),
         "errorIndexRAR" => Array (
    0 => " <br /><span class='warnadmin'>Indexing of RAR archives is not supported by this version of Sphider-plus.<br /></span>",
    1 => " Indexing of RAR archives is not supported by this version of Sphider-plus.\n"
    ),
         "errorIndexZIP" => Array (
    0 => " <br />\n<span class='warnadmin'>Indexing of ZIP archives is not activated in Admin Settings.<br /></span>\n",
    1 => " Indexing of ZIP archives is not activated in Admin Settings.\n"
    ),
         "errorOpenPDF" => Array (
    0 => " <br />\n<span class='warnadmin'>Error opening this PDF file, perhaps because it is corrupted.<br />Unable to process this PDF document.<br /></span>\n",
    1 => " Error opening this PDF file, perhaps because it is corrupted.\n"
    ),
         "errorNoPDFConv" => Array (
    0 => " <br /><br />\n\n<span class='warnadmin'>Cannot access the PDF converter. Invalid path defined.<br />Please correct the path in Admin settings 'General Settings'<br /></span>\n",
    1 => " Cannot access PDF converter. Invalid path defined to PDF converter.\nPlease close the Admin window and correct the path in .../settings/config.php.\n"
    ),
         "permissionError" => Array (
    0 => " <br />\n<span class='warnadmin'>Error related to PDF permissions.<br /><br />Unable to process this PDF document.<br /></span>\n",
    1 => " Error related to PDF permissions.\n"
    ),
         "noConverter" => Array (
    0 => " <br />\n<span class='warnadmin'>Could not find PDF converter.<br /><br />Unable to process this PDF document.<br /></span>\n",
    1 => " Could not find PDF converter.\n"
    ),
         "ufoError" => Array (
    0 => " <br />\n<span class='warnadmin'> Unable to process this PDF document.<br /><br />Converter didn't pass back the ready status or any known error message.</span>\n",
    1 => " Unable to process this PDF document.\nConverter didn't pass back the ready status or any known error message. n"
    ),
         "nothingFound" => Array (
    0 => " <br />\n<span class='warnadmin'> Converter did not send any error message, but was unable to extract any word from this file.</span>\n",
    1 => " Converter did not send any error message, but was unable to extract any word from that file.\n"
    ),
         "jsEmpty" => Array (
    0 => " <br />\n<span class='warnadmin'> Nothing found to be indexed in this JavaScript.</span>\n",
    1 => " Nothing found to be indexed in this JavaScript.\n"
    ),
         "xlsError" => Array (
    0 => " <br />\n<span class='warnadmin'> EXEL reader was unable to extract any word from this file.</span>\n",
    1 => " EXELm reader was unable to extract any word from that file.\n"
    ),
          "noFollow" => Array (
    0 => " <span class='warnadmin'>No-follow flag set</span><br />\n",
    1 => " No-follow flag set."
    ),
         "inDatabase" => Array (
    0 => " <br />\n<span class='warnadmin'>already in database</span></p>\n",
    1 => " already in database\n"
    ),
         "consumed" => Array (
    0 => "	<br /><br />\r\n    <p class='alert'><span class='em'>Indexing completed in: </span> %spent <span>seconds.</span></p>\n",
    1 => " %spent seconds consumed.\n"
    ),
         "completed" => Array (
    0 => "	<p class='alert'><span class='em'>Indexing completed at: </span> %cur_time.</p>\n
                    <p class='evrow'><a class='bkbtn' href='admin.php' title='Go back to Admin'>Back to admin</a></p>\n",
    1 => "Completed at %cur_time.\n"
    ),
         "aborted" => Array (
    0 => "	<p><span class='red'>&nbsp;Mysql Server not avaliable! Tried 5 times to reconnect without success. Process aborted&nbsp;</font><br /><br /></p>\n
                    <p class='alert'><span class='warnadmin'>&nbsp;&nbsp;Indexing aborted at: </span> &nbsp;&nbsp;%cur_time.</p>\n
                    <p class='evrow'><a class='bkbtn' href='admin.php' title='Go back to Admin'>Back to admin</a></p>\n",
    1 => "Tried 5 times without success. Process aborted at %cur_time.\n"
    ),

         "noSQL" => Array (
    0 => "<span class='red'><br />&nbsp;Mysql Server not available!&nbsp;<br /></span>\n
                  <span class='blue sml'>&nbsp;&nbsp;Trying to reconnect to database . . .<br /></span>\n",
    1 => "No Sql Server available. trying to reconnect\n"
    ),
         "noDBconn" => Array (
    0 => "<span class='red'>&nbsp;&nbsp;Cannot connect to database.<br />\n",
    1 => "Cannot connect to database\n"
    ),
         "noDB" => Array (
    0 => "<span class='red'>&nbsp;&nbsp;Cannot choose database.<br />\n",
    1 => "Cannot choose database\n"
    ),
         "end" => Array (
    0 => "<p>&nbsp;</p>
                  </body></html>",
    1 => "End index\n"
    ),
         "newSQL" => Array (
    0 => "<span class='warnok'><br />&nbsp;Mysql Server successfully reconnected !&nbsp;<br /></span>\n",
    1 => "No Sql Server reconnected\n"
    ),
          "starting" => Array (
    0 => " <p class='alert'><span class='em'>Start indexing at %cur_time.</p>\n",
    1 => " \r\n\r\nStarting indexing at %cur_time.\r\n"
    ),
         "quit" => Array (
    0 => "\r\n    </div>\r\n   </body>\r\n  </html>",
    1 => ""
    ),
         "pageRemoved" => Array (
    0 => "<br />\n	<span class='warnadmin'>Page removed from index</span><br />\n",
    1 => " Page removed from index.\n"
    ),
          "continueSuspended" => Array (
    0 => "	<span class='bd'>Continuing suspended indexing.</span>\n",
    1 => " Continuing suspended indexing.\r\n"
    ),
          "newProducts" => Array (
    0 => "	\n<span class='bd'>\n<br /><br /><br />Products found here:<br /><br />\n</span>\n",
    1 => " \nProducts found here:\n"
    ),
          "newKeywords" => Array (
    0 => "	\n<br /><br /><span class='bd'>New keywords found here:</span><br /><br />\n\n",
    1 => " \r\n\r\nNew keywords found here:\n"
    ),
          "newLinks" => Array (
    0 => "	\n<span class='bd'>\n<br /><br /><br />New links found here:<br /><br />\n</span>\n",
    1 => " \nNew links found here:\n"
    ),
          "newImage" => Array (
    0 => "	\n<span class='bd'>\n<br /><br />New images found here:<br /><br />\n</span>\n",
    1 => " \r\n\r\nNew images files found here:\r\n"
    ),
          "newAudio" => Array (
    0 => "	\n<span class='bd'>\n<br />New audio streams found here:<br /><br />\n</span>\n",
    1 => " \nNew audio files found here:\n"
    ),
          "newVideo" => Array (
    0 => "	\n<span class='bd'>\n<br />New videos found here:<br /><br />\n</span>\n",
    1 => " \nNew videos files found here:\n"
    ),
          "newFrameset" => Array (
    0 => "	\n<span class='bd'>\n<br />New framesets found here:<br /><br />\n</span>\n",
    1 => " \nNew framesets found here:\n"
    ),
          "newIframe" => Array (
    0 => "	\n<span class='bd'>\n<br /><br />New iframes found here:<br /><br />\n</span>\n",
    1 => " \nNew iframes found here:\n"
    ),
          "archivFiles" => Array (
    0 => "	\n<span class='bd'>\n<br />Files found in archive:<br /><br />\n</span>\n",
    1 => " \n Files found in archive: \n"
    ),
          "indexed1" => Array (
    0 => "<span class='bd warnok'>\n<br /><br />Indexed</span>\n",
    1 => "Indexed\r\n"
    ),
          "indexed" => Array (
    0 => "<span class='bd warnok'><br />Indexed</span>\n",
    1 => "Indexed\r\n"
    ),
        "duplicate" => Array (
    0 => "<br /><span class='warnadmin'><br />Content of page is duplicate with:</span><br />\n",
    1 => " \nContent of page is duplicate with:\n"
    ),
        "md5notChanged" => Array (
    0 => "<br /><span class='warnadmin'>MD5 sum checked. Page content not changed.</span>\n",
    1 => " MD5 sum checked. Page content not changed.\n"
    ),
        "unreachable" => Array (
    0 => "<br /><span class='warnadmin'>Link is unreachable.</span>\n",
    1 => " Link is unreachable.\n"
    ),
        "noCharset" => Array (
    0 => "<br /><span class=\"warnadmin\">Unable to detect the charset of this page or charset not available for code-converter.<br />Using home charset ($home_charset) as defined by Admin.</span>\n",
    1 => " Unable to detect the charset of this page. Using home charset as defined by Admin.\n"
    ),
        "noHomeChar" => Array (
    0 => "<br /><span class=\"warnadmin\">Also unable to use the charset ($home_charset) as defined in Admin settings.<br />Content of this page is not converted to UTF-8</span>\n",
    1 => " Also unable to detect the charset as defined in Admin settings. Content not converted to UTF-8.\n"
    ),
         "abortedIndx" => Array (
    0 => "<br />\n	<span class='warnadmin'><strong><center>Unable to proceed with indexing.&nbsp;&nbsp;Process aborted for this link.</center></stong></span><br />\n",
    1 => " Unable to proceed with indexing. Process aborted for this link.\n"
    ),

        "notRSS" => Array (
    0 => "<br /><span class='warnadmin'>Currently unsupported XML file. No RDF, RSS or Atom tag detected here.</span>\n",
    1 => " Currently unsupported XML file.  No RDF, RSD, RSS or Atom tag detected here.\n"
    ),
        "invalidRSS" => Array (
    0 => "<br /><span class='warnadmin'>Not a valid RDF, RSD, RSS or Atom feed for Sphider-plus.</span>\n",
    1 => " Not a valid RDF, RSS or Atom feed for Sphider-plus. \n"
    ),
        "noWhitelist" => Array (
    0 => "<br /><span class='warnadmin'>Content ignored, as it did not match the whitelist.</span>\n",
    1 => " Content ignored, as it did not match the whitelist.\n"
    ),
        "noDoclist" => Array (
    0 => "<br /><span class='warnadmin'>Content ignored, as it did not match the docs list.</span>\n",
    1 => " Content ignored, as it did not match the docs list.\n"
    ),
        "matchBlacklist" => Array (
    0 => "<br /><span class='warnadmin'>Content ignored, as it met the blacklist.</span>\n",
    1 => " Content ignored, as it met the blacklist.\n"
    ),
        "metaNoindex" => Array (
    0 => "<br /><span class='warnadmin'>No-Index flag set in meta tags.</span>\n",
    1 => " No-Index flag set in meta tags.\n"
    ),
        "NoSitesFound" => Array (
    0 => "<br /><span class='warnadmin'>No sites stored in temporary file for this page.</span>\n",
    1 => " No sites stored in temporary file for this page.\n"
    ),

          "re-indexed1" => Array (
    0 => "<br /><span class='warnok em'><br />Re-indexed</span>\n",
    1 => " Re-indexed\n"
    ),

          "re-indexed" => Array (
    0 => "<br /><span class='warnok em'>Re-indexed</span>\n",
    1 => " Re-indexed\n"
    ),
        "start_reindex" => Array (
    0 => "	<p class='bd'>Starting Re-index.</p>\n",
    1 => "\r\nStarting re-index.\r\n"
    ),
        "start_link_check" => Array (
    0 => "	<p class='bd'>Starting Link-check.</p>\n",
    1 => " Starting Link-check.\n"
    ),
        "link_okay" => Array (
    0 => "<br /><span class='warnok em'>Okay, page is available.</span><br />\n",
    1 => " Okay, page is available.\n"
    ),
          "link_local" => Array (
    0 => "<br /><span class='warnok em'>Not checked, local link.</span><br />\n",
    1 => " Not checked, local link.\n"
    ),
        "minWords" => Array (
    0 => " <br /><span class='warnadmin'>Page contains less than $min_words_per_page words</span><br />\n",
    1 => "\nPage contains less than $min_words_per_page words.\n"
    ),
        "js_content" => Array (
    0 => " <br /><span class='warnadmin'>Page contains only JavaScript, which is only indexed in order to find links.</span><br />\n",
    1 => "\nPage contains only JavaScript, which is only indexed in order to find links.\n"
    )
    );

    function printValidSecSmap($i, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        $log_msg_txt = "$i valid secondary Sitemap files found.\n";
        $log_msg_html = "<br /><span class='warnok'> >>> $i valid secondary Sitemap files found. <<< </span><br /><br />\n";
        if ($no_log == '0') {
            if ($cl != 1) {
                print $log_msg_html;
            } else {
                print $log_msg_txt;
            }
            @ob_flush();
            @flush();
        }
        if ($log_format=="html") {
            writeToLog($log_msg_html, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }
    }

    function printDupReport($dup_url, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        $log_msg_txt = "$dup_url\n";
        $log_msg_html = "<span><a href='$dup_url' target='_blank'\n	title='Open this link in new window'>$dup_url</a></span><br /><br />\n";
        if ($no_log == '0') {
            if ($cl != 1) {
                print $log_msg_html;
            } else {
                print $log_msg_txt;
            }
            @ob_flush();
            @flush();
        }
        if ($log_format=="html") {
            writeToLog($log_msg_html, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }
    }

    function printRobotsReport($num, $thislink, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        $num = rtrim(trim($num), '.');
        $log_msg_txt = "$num. Link $thislink: File checking forbidden in robots.txt file.\n";
        $log_msg_html = "<p class='alert'>\n<span class='em'>$num</span>. Link <span class='em'>$thislink</span><br /><span class='warnadmin'>File checking forbidden in robots.txt file</span></p>\n";
        if ($no_log == '0') {
            if ($cl != 1) {
                print $log_msg_html;
            } else {
                print $log_msg_txt;
            }
            @ob_flush();
            @flush();
        }
        if ($log_format=="html") {
            writeToLog($log_msg_html, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }
    }

    function printUrlStringReport($num, $thislink, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        $num = rtrim(trim($num), '.');
        $log_msg_txt = "$num. Link $thislink: \n File checking forbidden  by required/disallowed string rule.\n";
        $log_msg_html = "<p class='alert'>\n<span class='em'>$num</span>. Link <span class='em'>$thislink</span><br /> <span class='warnadmin'>\n File checking forbidden by required/disallowed string rule</span></p>";
        if ($no_log == '0') {
            if ($cl != 1) {
                print $log_msg_html;
            } else {
                print $log_msg_txt;
            }
            @ob_flush();
            @flush();
        }

        if ($log_format=="html") {
            writeToLog($log_msg_html, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }
    }

    function printRetrieving($num, $thislink, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE & ~E_STRICT);
        $num = rtrim(trim($num), '.');
        $log_msg_txt = "\n$num. Retrieving: $thislink at " . date("H:i:s").".\n";
        if ($num & 1) {
            $log_msg_html = "	<p class='evrow'>\n";
        } else {
            $log_msg_html = "	<p class='odrow'>\n";
        }
        $log_msg_html .="	<span class='em'>$num</span>. Retrieving: <span class='em'>";
        $log_msg_html .="<a href='$thislink' target='_blank'\n	title='Open link in new window'>$thislink</a></span><br />\n";
        if ($no_log == '0') {
            if ($cl != 1) {
                print $log_msg_html;
            } else {
                print $log_msg_txt;
            }
            @ob_flush();
            @flush();
        }

        if ($log_format=="html") {
            writeToLog($log_msg_html, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }
    }

    function printLinksReport($numoflinks, $all_links, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        $log_msg_txt = "Valid links found: $all_links - New links found: $numoflinks\n";
        $log_msg_html = "<span class='bd'><br /> Links found: $all_links - New links: $numoflinks</span><br /><br />\n";
        if ($no_log == '0') {
            if ($cl != 1) {
                print $log_msg_html;
            } else {
                print $log_msg_txt;
            }
            @ob_flush();
            @flush();
        }

        if ($log_format=="html") {
            writeToLog($log_msg_html, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }
    }

    function printXLSXreport($count, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        $log_msg_txt = "Found $count XLSX spreadsheets here. Named: ";
        $log_msg_html = "<br />Found $count XLSX spreadsheets here. Named: ";
        if ($no_log == '0') {
            if ($cl != 1) {
                print $log_msg_html;
            } else {
                print $log_msg_txt;
            }
            @ob_flush();
            @flush();

            if ($log_format=="html") {
                writeToLog($log_msg_html, $copy);
            } else {
                writeToLog($log_msg_txt, $copy);
            }
        }
    }

    function printSegCN($seg_add, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        $log_msg_txt = " Chinese word segmentation added $seg_add basic words.\n";
        $log_msg_html = "<br /><br />Chinese word segmentation extracted $seg_add additional basic words in full text.\n";
        if ($no_log == '0') {
            if ($cl != 1) {
                print $log_msg_html;
            } else {
                print $log_msg_txt;
            }
            @ob_flush();
            @flush();

            if ($log_format=="html") {
                writeToLog($log_msg_html, $copy);
            } else {
                writeToLog($log_msg_txt, $copy);
            }
        }
    }

    function printSegKR($seg_add, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        $log_msg_txt = " Korean word segmentation added $seg_add basic words.\n";
        $log_msg_html = "<br /><br />Korean word segmentation extracted $seg_add additional basic words.\n";
        if ($no_log == '0') {
            if ($cl != 1) {
                print $log_msg_html;
            } else {
                print $log_msg_txt;
            }
            @ob_flush();
            @flush();
        }

        if ($log_format=="html") {
            writeToLog($log_msg_html, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }
    }

    function printSegJA($seg_add, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        $log_msg_txt = " Japanese word segmentation added $seg_add basic words.\n";
        $log_msg_html = "<br /><br />Japanese word segmentation extracted $seg_add additional basic words.\n";
        if ($no_log == '0') {
            if ($cl != 1) {
                print $log_msg_html;
            } else {
                print $log_msg_txt;
            }
            @ob_flush();
            @flush();
        }

        if ($log_format=="html") {
            writeToLog($log_msg_html, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }
    }

    function printValidFeed($type, $entries, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        if ($type == 'Atom') {
            $log_msg_txt = "Valid feed found here. Type: $type, containing $entries entries.\n";
            $log_msg_html = "<br />Valid feed found here. Type: $type, containing $entries entries.<br />\n";
        }
        if ($type == 'RSD') {
            $log_msg_txt = "Valid feed found here. Type: $type, containing $entries api tags.\n";
            $log_msg_html = "<br />Valid feed found here. Type: $type, containing $entries api tags.<br />\n";
        }else {
            $log_msg_txt = "Valid feed found here. Type: $type, containing $entries items.\n";
            $log_msg_html = "<br />Valid feed found here. Type: $type, containing $entries items.<br />\n";
        }
        if ($no_log == '0') {
            if ($cl != 1) {
                print $log_msg_html;
            } else {
                print $log_msg_txt;
            }
            @ob_flush();
            @flush();
        }

        if ($log_format=="html") {
            writeToLog($log_msg_html, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }

    }

    function printInvalidFeedType($type, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        $log_msg_txt = "Unable to extract the feed type found here. \n";
        $log_msg_html = "<br /><span class='warnadmin'>Feed found here, but unable to extract the type of the feed.<br />No item extracted.</span><br />\n";

        if ($no_log == '0') {
            if ($cl != 1) {
                print $log_msg_html;
            } else {
                print $log_msg_txt;
            }
            @ob_flush();
            @flush();
        }

        if ($log_format=="html") {
            writeToLog($log_msg_html, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }

    }

    function printNotWellFormedXML($errorMessage, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        $log_msg_txt = "XML feed is not valid, reason:\r\n$errorMessage \n";
        $log_msg_html = "<br /><br /><span class='warnadmin'><strong>XML feed is not valid, reason:</strong>&nbsp;<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$errorMessage</span><br />\n";
        if ($no_log == '0') {
            if ($cl != 1) {
                print $log_msg_html;
            } else {
                print $log_msg_txt;
            }
            @ob_flush();
            @flush();
        }

        if ($log_format=="html") {
            writeToLog($log_msg_html, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }
    }

    function printNoDictionary($charSet, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        $log_msg_txt = " No dictionary found for segmentation of text with charset: $charSet \n";
        $log_msg_html = "<br /><br /><span class='warnadmin'>No dictionary found for segmentation of text with charset: <strong>$charSet</strong></span><br />\n";
        if ($no_log == '0') {
            if ($cl != 1) {
                print $log_msg_html;
            } else {
                print $log_msg_txt;
            }
            @ob_flush();
            @flush();
        }

        if ($log_format=="html") {
            writeToLog($log_msg_html, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }
    }

    function printConverterError($FileName, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        $log_msg_txt = " Converter Error: Unable to read converter table for '$FileName' \n";
        $log_msg_html = "<br /><span class='warnadmin'>Charset Converter Error. Can NOT read converter table for <strong>$FileName</strong></span><br />\n";
        if ($no_log == '0') {
            if ($cl != 1) {
                print $log_msg_html;
            } else {
                print $log_msg_txt;
            }
            @ob_flush();
            @flush();
        }

        if ($log_format=="html") {
            writeToLog($log_msg_html, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }
    }

    function printTryHome($home_charset, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        $log_msg_txt = " Trying to use home charset $home_charset as defined in Admin setting.\n";
        $log_msg_html = "<br />Trying to use home charset $home_charset as defined in Admin setting.<br />\n";
        if ($no_log == '0') {
            if ($cl != 1) {
                print $log_msg_html;
            } else {
                print $log_msg_txt;
            }
            @ob_flush();
            @flush();
        }

        if ($log_format=="html") {
            writeToLog($log_msg_html, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }
    }

    function printRefreshed($link, $wait, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        $log_msg_txt = " Refresh tag found in page header. Redirected to:\n$link \n after $wait seconds \n";
        $log_msg_html = "<br /><br /><span class='warnadmin'>Found a <strong>\"refresh\"</strong> tag in HTML head. Redirected after $wait seconds to:<br /><br />$link</span><br /><br />\n";
        if ($no_log == '0') {
            if ($cl != 1) {
                print $log_msg_html;
            } else {
                print $log_msg_txt;
            }
            @ob_flush();
            @flush();
        }

        if ($log_format=="html") {
            writeToLog($log_msg_html, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }
    }

    function printCanonical($cano_link, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        $log_msg_txt = " Canonical link tag found in page header. Redirected to:\n$cano_link \n";
        $log_msg_html = "<br /><br /><span class='warnadmin'>Found a <strong>rel=\"canonical\"</strong> link tag in HTML head. Redirected to:<br /><br />$cano_link</span><br /><br />\n";
        if ($no_log == '0') {
            if ($cl != 1) {
                print $log_msg_html;
            } else {
                print $log_msg_txt;
            }
            @ob_flush();
            @flush();
        }

        if ($log_format=="html") {
            writeToLog($log_msg_html, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }
    }

    function printNoCanonical($cano_link, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        $log_msg_txt = " Canonical link found in page header.\n . Unable to extract Redirection \n";
        $log_msg_html = "<br /><br /><span class='warnadmin'>Canonical link found in page header.<br /><strong>Unable to extract Redirection</strong></span><br /><br />\n";
        if ($no_log == '0') {
            if ($cl != 1) {
                print $log_msg_html;
            } else {
                print $log_msg_txt;
            }
            @ob_flush();
            @flush();
        }

        if ($log_format=="html") {
            writeToLog($log_msg_html, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }
    }

    function printHTMLHeader($omit, $url, $cl, $multi, $all, $started) {
        global $db_con, $log_format, $template_path, $copy, $plus_nr, $home_charset, $cn_seg, $no_log, $multi_indexer, $no_log;

        error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE & ~E_STRICT);
        if ($multi_indexer == 1 || $url) {   //  enter here if multi indexing is not activated or for single URL indexing
            $log_msg_html_0 = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
        <html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
        <head>
         <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
         <title>Sphider-plus v.$plus_nr Log File output</title>
         <link rel='stylesheet' href='$template_path/adminstyle.css' media='screen' type='text/css' />
         <meta http-equiv='expires' content='0'>
         <meta http-equiv='pragma' content='no-cache'>
         <meta http-equiv='X-UA-Compatible' content='IE=9' />
         <link href='../templates/html/sphider-plus.ico' rel='shortcut icon' type='image/x-icon' />
        </head>
        <body>
         <div class='submenu cntr'>Sphider-plus v.$plus_nr -  Log File output</div> <br />
         <div id='report'>
         <br />
         <p class='evrow'><br /><a class='bkbtn' href='admin.php' title='jump back to Sites view'><strong>Back to admin</strong></a>&nbsp;&nbsp;&nbsp;(Aborting straightly, and without caring about the consequences)</p>
         <br /><br />
         <p class='evrow'><br /><a class='bkbtn' href='admin.php?cancel=1' title='Abort and jump back to Admin'><strong>Cancel this index procedure</strong></a><br /><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Database will be updated, repaired, optimized and flushed. Please remain patient, as it may take some time to work out all.</p>
         <br /><br />
         <p class='bd'><span class='em'>Start indexing at " . date("H:i:s")."</span></p>
         <br /><br />
         <div class='submenu cntr'>
             <span class='em'>Indexing sites . . .<br /><br /><br /></span>
         </div>
                ";
        } else {  //    enter here for multi indexing
            if ($no_log == '0' || ($multi && $multi <= $multi_indexer) ) {
                $indexer = $multi-1;

                if ($multi && $indexer == '0') {    //  for first loop, not yet started any indexer
                    $log_msg_html_0 = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
        <html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
        <head>
         <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
         <title>Sphider-plus v.$plus_nr Log File output</title>
         <link rel='stylesheet' href='$template_path/adminstyle.css' media='screen' type='text/css' />
         <meta http-equiv='expires' content='0'>
         <meta http-equiv='pragma' content='no-cache'>
         <meta http-equiv='X-UA-Compatible' content='IE=9' />
         <link href='../templates/html/sphider-plus.ico' rel='shortcut icon' type='image/x-icon' />
        </head>
        <body>
         <div class='submenu cntr'>Sphider-plus v.$plus_nr -  Log File output</div>
         <br />
         <br />
         <p class='evrow'><br /><a class='bkbtn' href='admin.php?cancel=1' title='Abort and jump back to Admin'><strong>Cancel all index procedures</strong></a><br /><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Database will be updated, repaired, optimized and flushed. Please remain patient, as it may take some time to work out all.</p>
         <br /><br />
         <p class='bd'><span class='em'>Start indexing at:&nbsp;&nbsp;&nbsp;not yet started</span></p>
         <br /><br />
         <p class='evrow, cntr'>Ready to start the $multi_indexer indexer now.</p>
         <br /><br />
         <div class='no_write'>
         <form action='spider.php' method='get'>
            <table class='searchBox'>
                <tr>
                    <td>
                    <input type='hidden' name='all' id='all' value='".$all."' />
                    <input type='hidden' name='multi' id='multi' value='".$multi."' />
                    <input type='hidden' name='started' id='started' value='".$started."' />
                    <input type='submit' value='Start indexer ".$multi."' />
                    </td>
                </tr>
            </table>
         </form>
         <br /><br />
         <p class='evrow, cntr'>Do not close this window until the according button is presented below.</p>
         </div>
         <br /><br />
                    ";

                } else {    //  here to start more indexer
                    $log_msg_html_0 = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
        <html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
        <head>
         <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
         <title>Sphider-plus v.$plus_nr Log File output</title>
         <link rel='stylesheet' href='$template_path/adminstyle.css' media='screen' type='text/css' />
         <meta http-equiv='expires' content='0'>
         <meta http-equiv='pragma' content='no-cache'>
         <meta http-equiv='X-UA-Compatible' content='IE=9' />
         <link href='../templates/html/sphider-plus.ico' rel='shortcut icon' type='image/x-icon' />
        </head>
        <body>
         <div class='submenu cntr'>Sphider-plus v.$plus_nr -  Log File output</div>
         <br />
         <br />
         <p class='evrow'><br /><a class='bkbtn' href='admin.php?cancel=1' title='Abort and jump back to Admin'><strong>Cancel all index procedures</strong></a><br /><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Database will be updated, repaired, optimized and flushed. Please remain patient, as it may take some time to work out all.</p>
         <br /><br />
         <p class='bd'><span class='em'>Start indexing at " . date("H:i:s")."</span></p>
         <br /><br />
         <p class='evrow, cntr'>Okay, indexer ".$indexer." initialized.</p>
         <br /><br />
         <div class='no_write'>
         <form action='spider.php' method='get'>
            <table class='searchBox'>
                <tr>
                    <td>
                    <input type='hidden' name='all' id='all' value='".$all."' />
                    <input type='hidden' name='multi' id='multi' value='".$multi."' />
                    <input type='hidden' name='started' id='started' value='".$started."' />
                    <input type='submit' value='Start indexer ".$multi."' />
                    </td>
                </tr>
            </table>
         </form>
         <br /><br />
         <p class='evrow, cntr'>Do not close this window until the according button is presented below.</p>
         </div>
         <br /><br />
         <div class='submenu cntr'>
            <span class='em'>Now indexing sites . . .<br /><br /><br /></span>
         </div>
         <br /><br />
                    ";
                }
            } else {    //  enter here, if all indexer had been started
                $log_msg_html_0 = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
        <html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
        <head>
         <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
         <title>Sphider-plus v.$plus_nr Log File output</title>
         <link rel='stylesheet' href='$template_path/adminstyle.css' media='screen' type='text/css' />
         <meta http-equiv='X-UA-Compatible' content='IE=9' />
         <link href='../templates/html/sphider-plus.ico' rel='shortcut icon' type='image/x-icon' />
        </head>
        <body>
         <div class='submenu cntr'>Sphider-plus v.$plus_nr -  Log File output</div>
         <br />
         <br />
         <p class='evrow'><br /><a class='bkbtn' href='admin.php?cancel=1' title='Abort and jump back to Admin'><strong>Cancel all index procedures</strong></a><br /><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Database will be updated, repaired, optimized and flushed. Please remain patient, as it may take some time to work out all.</p>
         <br /><br />
         <p class='bd'><span class='em'>Start indexing at " . date("H:i:s")."</span></p>
         <div class='no_write'>
         <br /><br />
         <p class='evrow, cntr'>Okay, all available indexer are initialized.</p>
         <br /><br />
         <p class='evrow, cntr'>Do not close this window until the according button is presented below.</p>
         <br /><br />
         <div class='submenu cntr'>
            <span class='em'>Now indexing sites . . .<br /><br /><br /></span>
         </div>
         <br /><br />
                ";
            }

        }

        if ($cl != 1) {
            echo $log_msg_html_0;   //  now output the header
        }

        @ob_flush();
        @flush();

        if ($log_format=="html" && $multi != '1' || $url) {
            $copy = '0';
            writeToLog($log_msg_html_0, $copy);
            $copy = '1';
        }
    }

    function printHeader($omit, $url, $cl) {
        global $db_con, $log_format, $template_path, $copy, $no_log;

        ob_start();
        if ($omit) {
            $urlparts = parse_addr($url);
            $omits = array();

            foreach ($omit as $dir) {
                if ($cl != 1) {
                    $omits[] = "<li>".$urlparts['scheme']."://".$urlparts['host'].$dir."</li>";
                }else{
                    $omits[] = $urlparts['scheme']."://".$urlparts['host'].$dir;
                }
            }
        }

        $log_msg_txt = "\r\nSpidering $url\r\n\r\n";
        if ($omits) {
            $log_msg_txt .= "Disallowed files and directories in robots.txt:\r\n";
            $log_msg_txt .= implode("\n", $omit);
            $log_msg_txt .= "\r\n";
        }

        $log_msg_html_1 = "     <h1>Spidering: <span class='warnok'>$url</span></h1>\n";

        $log_msg_html_link = "	<p>[Go back to <span  class='em'><a href='admin.php'>admin</a></span>]</p>";

        if ($omits) {
            $log_msg_html_2 =  "     <div class='alert'>\n		<p class='em'>Disallowed files and directories in robots.txt:</p>\n		<ul class='txt'>\n";
            $log_msg_html_2 .=  implode("\n", $omits);
            $log_msg_html_2 .=  "\n</ul>\n</div>\n";
        }

        if ($no_log == '0') {
            if ($cl != 1) {
                echo $log_msg_html_1.$log_msg_html_2;
            } else {
                print $log_msg_txt;
            }

            @ob_flush();
            @flush();
        }

        if ($log_format=="html") {
            writeToLog($log_msg_html_1.$log_msg_html_2, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }
    }


    function printBadRobots($url, $cl) {
        global $db_con, $log_format, $template_path, $copy, $no_log;

        ob_start();

        $log_msg_html_1 = "     <h1>Spidering: <span class='warnok'>$url</span></h1>\n";
        $log_msg_html_2 = "     <br /><span class='warnadmin'>robots.txt found here. But its content does not meet the requirements, or is corrupted.<br />Indexing $url by ignoring the robots.txt file.</span><br /><br />";

        $log_msg_txt = "\r\nSpidering $url\r\n\r\n";
        $log_msg_txt .= "Disallowed files and directories in robots.txt:\r\n";
        $log_msg_txt .= "robots.txt found here. But its content does not meet the requirements, or is corrupted.\nIndexing $url by ignoring robots.txt.";
        $log_msg_txt .= "\r\n";

        if ($no_log == '0') {
            if ($cl != 1) {
                echo $log_msg_html_1.$log_msg_html_2;
            } else {
                print $log_msg_txt;
            }

            @ob_flush();
            @flush();
        }

        if ($log_format=="html") {
            writeToLog($log_msg_html_1.$log_msg_html_2, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }
    }




    function printSitemapCreated($filename, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        $log_msg_html = "<p class='bd'>Sitemap created: $filename</p>";
        $log_msg_txt = "Sitemap created: $filename\n\n";

        if ($no_log == '0') {
            if ($cl  != 1) {
                echo $log_msg_html;
            } else {
                print $log_msg_txt;
            }

            @ob_flush();
            @flush();
        }

        if ($log_format=="html") {
            writeToLog($log_msg_html, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }
    }

    function printInvalidFile($filename, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        $log_msg_html = "<p class='warn'>Unable to open $filename</p>";
        $log_msg_txt = "Unable to open $filename\n";

        //if ($print_results) {
        if ($cl != 1) {
            echo $log_msg_html;
        } else {
            print $log_msg_txt;
        }

        @ob_flush();
        @flush();
        //}

        if ($log_format=="html") {
            writeToLog($log_msg_html, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }
    }


    function printDocReport($message, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        $log_msg_html = "
                                <p class='em'><span class='warnadmin'>
                                Error message sent by the .doc converter:<br /><br />$message</span>
                                <br />
                                </p>
                            ";
        $log_msg_txt = "Error meessage sent by the .doc converter:\n $message\n";

        if ($no_log == '0') {
            if ($cl != 1) {
                echo $log_msg_html;
            } else {
                print $log_msg_txt;
            }

            @ob_flush();
            @flush();
        }
    }

    function printMaxLinks($max_links, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        $log_msg_html = "
                                <p class='em'><span class='warnadmin'>
                                Reached the limit of max. links = $max_links for this site.</span>
                                <br />
                                </p>
                            ";
        $log_msg_txt = "Reached the limit of max. links = $max_links for this site.\n";

        if ($no_log == '0') {
            if ($cl != 1) {
                echo $log_msg_html;
            } else {
                print $log_msg_txt;
            }

            @ob_flush();
            @flush();
        }

        if ($log_format=="html") {
            writeToLog($log_msg_html, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }
    }

    function printInterrupt($interrupt, $url, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        $indexoption="<form  class='cntr bd' action='admin.php' method='post'>
                            <input type='hidden' name='f' value='index' />
                            <input type='hidden' name='url' value='$url' />
                            <input class='cntr' type='submit' value='Continue the suspended index procedure'>
                            </form>
                        ";

        $log_msg_html = "   <p>&nbsp;</p>
                                <p class='bd cntr08'>Indexer interrupted, because reached the limit of $interrupt links.</p>
                                <p>&nbsp;</p>
                                <p class='cntr08'>$indexoption        </p>
                                <p>&nbsp;</p>
                                <p class='evrow'><a class='bkbtn' href='admin.php' title='Go back to Admin'>Back to admin</a></p>
                            ";
        $log_msg_txt = "Indexer interrupted, because reached the limit of $interrupt links.\n
        $indexoption\n
                            <a href='admin.php' title='Go back to Admin'>Back to admin</a>\n
                            ";

        if ($no_log == '0') {
            if ($cl != 1) {
                echo $log_msg_html;
            } else {
                print $log_msg_txt;
            }

            @ob_flush();
            @flush();
        }

        if ($log_format=="html") {
            writeToLog($log_msg_html, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }
    }



    function printDatabase($stats, $cl) {
        global $db_con, $log_format, $copy, $no_log, $dba_act, $index_media;

        $log_msg_html = "	<p class='stats'>
                <span class='em'>Database ".$dba_act." contains: </span>".$stats['sites']." sites <b>+</b> ".$stats['links']." page links <b>+</b> ".$stats['categories']." categories <b>+</b> ".$stats['keywords']." keywords
                ";
        if ($index_media == '1') {
            $log_msg_html .= "<b>+</b> ".$stats['media']." media links.</span></p>";
        } else {
            $log_msg_html .= "</p>";
        }

        $log_msg_txt = "\r\nDatabase ".$dba_act." contains: ".$stats['sites']." sites + ".$stats['links']." page links + ".$stats['categories']." categories + ".$stats['keywords']." keywords";
        if ($index_media == '1') {
            $log_msg_txt .= " + ".$stats['media']." media links.\r\n";
        } else {
            $log_msg_txt .= "\r\n";
        }
        $log_msg_txt .= "\r\n--------------------------------------------------------------------";

        if ($no_log == '0') {
            if ($cl != 1) {
                echo $log_msg_html;
            } else {
                print $log_msg_txt;
            }

            @ob_flush();
            @flush();
        }

        if ($log_format=="html") {
            writeToLog($log_msg_html, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }
    }

    function printActKeyword($word) {
        global $db_con, $log_format, $copy, $no_log;

        $log_msg_txt = "$word,  ";
        if ($no_log == '0') {
            print $log_msg_txt;
            @ob_flush();
            @flush();
        }
        writeToLog($log_msg_txt, $copy);
    }

    function printActMedia($word) {
        global $db_con, $log_format, $copy, $no_log, $cl;

        if ($cl == 1) {
            $log_msg_txt = "$word\r\n<br />";
        } else {
            $log_msg_txt = "$word<br />";
        }

        if ($no_log == '0') {
            print $log_msg_txt;
            @ob_flush();
            @flush();
        }
        writeToLog($log_msg_txt, $copy);
    }

    function printNewLinks($act_link, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        if ($cl == 1) {
            $log_msg_txt = "$act_link\r\n";
        } else {
            $log_msg_txt = "$act_link<br />";
        }

        if ($no_log == '0') {
            print $log_msg_txt;
            @ob_flush();
            @flush();
        }
        writeToLog($log_msg_txt, $copy);
    }

    function printPageSizeReport($pageSize, $content) {
        global $db_con, $log_format, $copy, $no_log, $cl;

        if ($cl == 1) {
            if ($content == 'rar' || $content == 'zip') {
                $log_msg_txt = "Size of extracted text from archive: $pageSize"." kByte. ";
            } else {
                $log_msg_txt = "Size of page: $pageSize"." kByte. ";
            }
        } else {
            if ($content == 'rar' || $content == 'zip') {
                $log_msg_txt = "<br />Size of extracted text from archive: $pageSize"." kByte. ";
            } else {
                $log_msg_txt = "<br />Size of page: $pageSize"." kByte. ";
            }
        }

        if ($no_log == '0') {
            print $log_msg_txt;
            @ob_flush();
            @flush();
        }
        writeToLog($log_msg_txt, $copy);
    }

    function printWarning($report, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        $log_msg_txt = "$report\n";
        $log_msg_html = " <span class='warnadmin'>$report</span><br />\n";
        if ($no_log == '0') {
            if ($cl != 1) {
                print $log_msg_html;
            } else {
                print $log_msg_txt;
            }
            @ob_flush();
            @flush();
        }
        if ($log_format=="html") {
            writeToLog($log_msg_html, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }
    }

    function printCancel($report, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        $log_msg_txt = "$report\n";
        $log_msg_html = "   <br /><br /><span class='warnadmin'>&nbsp;&nbsp;$report&nbsp;&nbsp;</span><br />\n";
        if ($no_log == '0') {
            if ($cl != 1) {
                print $log_msg_html;
            } else {
                print $log_msg_txt;
            }
            @ob_flush();
            @flush();
        }
        if ($log_format=="html") {
            writeToLog($log_msg_html, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }
    }

    function printEndHTMLBody($cl) {
        global $db_con, $log_format, $copy, $no_log;

        $log_msg_txt = "\n";
        $log_msg_html = "<br />
        <form  class='cntr'>
            <table class='closeWindow'>
                <tr>
                    <td>
                    <input type='submit' value='Close this window' 'title='Return to Log File output' onclick='window.close()'>
                    </td>
                </tr>
            </table>
        </form>
      </body>
    </html>\n";
        if ($no_log == '0') {
            if ($cl != 1) {
                print $log_msg_html;
            } else {
                print $log_msg_txt;
            }
            @ob_flush();
            @flush();
        }
        if ($log_format=="html") {
            writeToLog($log_msg_html, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }
    }

    function printThis($report, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        $log_msg_txt = "$report\n";
        $log_msg_html = " $report<br />\n";
        if ($no_log == '0') {
            if ($cl != 1) {
                print $log_msg_html;
            } else {
                print $log_msg_txt;
            }
            @ob_flush();
            @flush();
        }
        if ($log_format=="html") {
            writeToLog($log_msg_html, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }
    }



    function printNofollowLink($report, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        $log_msg_txt = "$report\n";
        $log_msg_html = " <span class='warnadmin'>$report</span><br />\n";
        if ($no_log == '0') {
            if ($cl != 1) {
                print $log_msg_html;
            } else {
                print $log_msg_txt;
            }
            @ob_flush();
            @flush();
        }
        if ($log_format=="html") {
            writeToLog($log_msg_html, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }
    }

    function printFreeRes($event, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        $log_msg_html = "<br /><br />
                            <span class='warn'>Warning:&nbsp;&nbsp;&nbsp;&nbsp;Unable to free resources. Invalid or no resource available (Event: $event)</span>
                            <br />
                            ";
        $log_msg_txt = "Warning: Unable to free resources. Invalid or no resource available (Event: $event).\n";

        if ($cl != 1) {
            echo $log_msg_html;
        } else {
            print $log_msg_txt;
        }

        @ob_flush();
        @flush();

        if ($log_format=="html") {
            writeToLog($log_msg_html, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }
    }

    function printFreeMySQL($result, $event, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        $log_msg_html = "<br /><br />
                             <span class='warn'>Warning:&nbsp;&nbsp;&nbsp;&nbsp;Unable to free MySQL resource. Connection is still in use.&nbsp;&nbsp;&nbsp;&nbsp;Event: $event&nbsp;&nbsp;&nbsp;&nbsp;Resource: $result</span>
                             <br />
                            ";
        $log_msg_txt = "Warning: Unable to free MySQL resource. Connection is still in use. Event: $event , Resource: $result .\n";

        if ($cl != 1) {
            echo $log_msg_html;
        } else {
            print $log_msg_txt;
        }

        @ob_flush();
        @flush();

        if ($log_format=="html") {
            writeToLog($log_msg_html, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }
    }

    function printUrlStatus($report, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        $log_msg_txt = "$report\n";
        $log_msg_html = " <span class='warnadmin'>$report</span><br />\n";
        if ($no_log == '0') {
            if ($cl != 1) {
                print $log_msg_html;
            } else {
                print $log_msg_txt;
            }
            @ob_flush();
            @flush();
        }
        if ($log_format=="html") {
            writeToLog($log_msg_html, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }
    }

    function printRedirected($redirect, $re_url, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        $log_msg_txt = "\n$redirect $re_url\n";
        $log_msg_html = "<br /><span class='warnadmin'>$redirect&nbsp;&nbsp;$re_url</span><br />\n";
        if ($no_log == '0') {
            if ($cl != 1) {
                print $log_msg_html;
            } else {
                print $log_msg_txt;
            }
            @ob_flush();
            @flush();
        }
        if ($log_format=="html") {
            writeToLog($log_msg_html, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }

    }

    function printConnectErrorReport($errmsg) {
        global $db_con, $log_format, $copy, $no_log;

        $log_msg_txt = "Establishing connection with socket failed. ";
        $log_msg_txt .= $errmsg;

        if ($no_log == '0') {
            print $log_msg_txt;
            @ob_flush();
            @flush();
        }
        writeToLog($log_msg_txt, $copy);
    }

    function writeToLog($msg, $copy) {
        global $db_con, $keep_log, $log_handle, $real_handle, $real_log, $copy,  $mysql_table_prefix, $refresh, $debug, $db_con;

        //  rebuild stylesheet of external css file, which is not available here
        $msg = preg_replace("/<link rel='stylesheet'(.*?)type='text\/css' \/>/i", "<style>
                a { padding: 6px; text-decoration: underline; }
                a:active \{ background: transparent; color: #00F; text-decoration: underline; }
                a:link { text-decoration: underline; }
                a:hover { background: #5F5F5F; color: #FF0; padding: 6px; text-decoration: none; }
                a:visited, .sphome a { text-decoration: none; }
                body { background: #DCDCDC; color: #007; font: 100% Verdana,Arial,Helvetica,sans-serif; margin: 0 auto; padding: 0; text-align: left; width: 750px; border:1px solid #FFFFCC;}
                h1 { background: #BDE4D4; color: #007; font-size: 1em; margin: 0px auto; padding: 10px; text-align: center; border:2px solid #FFFFCC; }
                hr, hr.powered { background-color: #91A681; border: none; color: #91A682; height: 2px; margin-left: 0px; margin-right: 0px; }
                html { height: 100%; margin-bottom: 1px; }
                li { font-size: 0.8em; line-height: 1.1em; list-style: none; margin: 0; padding: 0px 0px 8px 0px; vertical-align: baseline; }
                li.indented { margin-left: 6em; }
                p, .txt { font-size: 0.8em; font-weight: normal; text-align: left; }
                table { border: 1px solid #070; border-collapse: collapse; border-spacing: 2px; empty-cells: show; margin: 0 auto; padding: 0; }
                table tr td { font-size: 0.8em; padding: 5px; }
                table tr td.bd { text-align: right; vertical-align: baseline; }
                td { border: 1px solid #070; }
                ul { margin: 0; padding: 4px; }
                .tblhead { background: url(hdline.jpg) #D0E3D1; border: 1px solid #070; color: #007; font-weight: bold; padding: 6px; text-align: center; }
                .title { font-size: 1em; line-height: 1.4em; margin: 0; padding: 3px 2px 3px 0px; text-align: left; }
                .url { background: transparent; font-size: 0.7em; color: #7E7E7E; margin: 2px 0 5px 20px; padding: 0;  }
                .w60 { margin: 0 auto; width: 60%; }
                .w75 { margin: 0 auto; width: 75%; }
                .warnadmin, .red { background: #FEFF04; color: #EE3C00; }
                .warn{ background: #bbb; color: #EE3C00; }
                .warn, .red .warnok, .green, .links, .blue { font-weight: bold; padding: 0px 2px; }
                .warnok, .green { background: transparent; color: #008001; }
            </style>", $msg);

        //  kill links to admin.php. Links are not available for log-file in subfolder xyz
        $msg = str_replace("<p class='evrow'><br /><a class='bkbtn' href='admin.php?cancel=1' title='Abort and jump back to Admin'><strong>Cancel this index procedure</strong></a><br /><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Database will be updated, repaired, optimized and flushed. Please remain patient, as it may take some time to work out all.</p>", '', $msg);
        $msg = str_replace("<p class='evrow'><a class='bkbtn' href='admin.php' title='Go back to Admin'>Back to admin</a></p>", '', $msg);
        //  Replace the 'Start indexer' buttons for multithreaded indexing with a 'Close window' button
        $msg = preg_replace("@<div class='no_write'>.*?</div>@si", "<form  class='cntr'>
            <table class='closeWindow'>
                <tr>
                    <td>
                    <input type='submit' value='Close this window' 'title='Return to Log File output' onclick='window.close()'>
                    </td>
                </tr>
            </table>
        </form>
        <br /><br />",$msg);

        //$end =strpos($msg, '</body>');
        if (strpos($msg, '</body>') > '1') {

            //      create 'close window' button for log output.
            $msg = "<br /><br />
        <form  class='cntr'>
            <table class='closeBox'>
                <tr>
                    <td>
                    <input type='submit' value='Close this window' 'title='Return to Admin site' onclick='window.close()'>
                    </td>
                </tr>
            </table>
        </form>
        <br /><br />
        </body>
        </html>";
        }

        if($keep_log) {
            if (!$log_handle) {
                die ("Cannot open file for logging. ");
            }

            if (fwrite($log_handle, $msg) === FALSE) {
                die ("Cannot write to file for logging. ");
            }

            if($real_log == '1' && $copy == '1') {              //      if selected,, update also the real-time log
                //$msg = preg_replace("@\[.*?\]@si", "<br /><p><a href='javascript:ScrollDown()'>Jump to end of page</a></p><br />",$msg);   //      replace 'Back to Admin' links with 'Jump to end of page' for rel-time output
                $msg = preg_replace("@\[.*?\]@si", "",$msg);   //      supress 'Back to Admin' links  for rel-time output
                $msg = preg_replace("@<p class='evrow'><a class='bkbtn'.*?Back</a></p>@si", "",$msg);   //      supress 'Back' links for rel-time output
                if (strpos($msg, "</html>") ) {

                    //      create 'close window' button for real-time output.
                    $msg = "
        <a class='navup'  href='javascript:JumpUp()' title='Jump to Page Top'>Top</a><br /><br />
        <form  class='cntr'>
            <table class='closeWindow'>
                <tr>
                    <td>
                    <input type='submit' value='Close this window' 'title='Return to Log File output' onclick='window.close()'>
                    </td>
                </tr>
            </table>
        </form>
        <br /><br />
                        ";
                }

                if ($db_con) {
                    $sql_query = "SELECT * from ".$mysql_table_prefix."real_log  LIMIT 1";
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
                    $log = $result->fetch_array(MYSQLI_ASSOC);
                    $old_log = stripslashes($log["real_log"]);  //  get previous real-log data
                    $msg = addslashes("".$old_log."".$msg."");  // build updated real-log data

                    //  save the updated log data
                    $sql_query = "UPDATE ".$mysql_table_prefix."real_log set `real_log`='$msg' LIMIT 1";
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
                    clean_resource($result);
                    unset ($old_log, $msg);
                    error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE & ~E_STRICT);
                }
            }
        }
    }

    function printStandardReport($type, $cl, $no_log) {
        global $db_con, $log_format, $messages, $copy, $multi_indexer;

        error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE & ~E_STRICT);
        if ($no_log == '0' || $cl != '1') {
            if($multi_indexer > '1'){
                $cl = '0';
            }
            if ($no_log == '0') {
                print str_replace('%cur_time', date("H:i:s"), $messages[$type][$cl]);
                @ob_flush();
                @flush();
            }
        }

        if ($log_format=="html") {
            writeToLog(str_replace('%cur_time', date("H:i:s"), $messages[$type][0]), $copy);
        } else {
            writeToLog(str_replace('%cur_time', date("H:i:s"), $messages[$type][1]), $copy);
        }
    }

    function printConsumedReport($type, $cl, $no_log, $consumed) {
        global $db_con, $log_format, $messages, $copy, $multi_indexer;


        if ($no_log == '0' || $cl != '1') {
            if($multi_indexer > '1'){
                $cl = '0';
            }

            print str_replace('%spent', $consumed, $messages[$type][$cl]);
            @ob_flush();
            @flush();
        }

        if ($log_format=="html") {
            writeToLog(str_replace('%spent', $consumed, $messages[$type][0]), $copy);
        } else {
            writeToLog(str_replace('%spent', $consumed, $messages[$type][1]), $copy);
        }
    }

    function printDB_errorReport($type, $cl, $no_log) {
        global $db_con, $log_format, $messages, $copy;

        error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE & ~E_STRICT);
        print str_replace('%cur_time', date("H:i:s"), $messages[$type][$cl]);
        //@ob_flush();
        //@flush();


        if ($log_format=="html") {
            writeToLog(str_replace('%cur_time', date("H:i:s"), $messages[$type][0]), '1');
        } else {
            writeToLog(str_replace('%cur_time', date("H:i:s"), $messages[$type][1]), '1');
        }
    }

    function printBlackLink($url, $title, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        $log_msg_txt = "\n$url found. \nTitle ignored, as it met the blacklist.\n";
        $log_msg_html = "<br />Link:&nbsp;&nbsp;$url&nbsp;&nbsp;detected.<br />Title: $title<br /><span class='warnadmin'>Ignored, as the title met the blacklist.</span><br />\n";
        if ($no_log == '0') {
            if ($cl != 1) {
                print $log_msg_html;
            } else {
                print $log_msg_txt;
            }
            @ob_flush();
            @flush();
        }
        if ($log_format=="html") {
            writeToLog($log_msg_html, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }

    }

    function printWhiteLink($url, $title, $cl) {
        global $db_con, $log_format, $copy, $no_log;

        $log_msg_txt = "\n$url found. \nTitle ignored, as it did not meet the whitelist.\n";
        $log_msg_html = "<br />Link:&nbsp;&nbsp;$url&nbsp;&nbsp;detected.<br />Title: $title<br /><span class='warnadmin'>Ignored, as the title did not meet the whitelist.</span><br />\n";
        if ($no_log == '0') {
            if ($cl != 1) {
                print $log_msg_html;
            } else {
                print $log_msg_txt;
            }
            @ob_flush();
            @flush();
        }
        if ($log_format=="html") {
            writeToLog($log_msg_html, $copy);
        } else {
            writeToLog($log_msg_txt, $copy);
        }

    }

?>
