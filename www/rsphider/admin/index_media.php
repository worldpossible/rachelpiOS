<?php

    $all_media      = array ();
    $links          = array ();
    $regs           = array ();
    $valid_img      = array ();
    $trash1         = array("\r\n", "\n", "\r", "%20");     // to be replaced by 'blank'
    $trash2         = array("/", "<",);                     // to be deleted
    $title_trans    = '';
    $replace1       = ' ';
    $replace2       = '';

    //  get actual link_id of page in progress
    $clean_url = $db_con->real_escape_string($url);
    mysqltest();
    $sql_query = "SELECT link_id from ".$mysql_table_prefix."links where url='$clean_url'";
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
    $row = $result->fetch_array(MYSQLI_NUM);
    $link_id = $row[0];

    if ($link_id > '0') {     //      if valid link_id exists
        //$raw_file = preg_replace("@<head[^>]*>(.*?)<\/head>@si","", $raw_file);     //  remove header
        preg_match("@<body[^>]*>(.*?)<\/body\s*>@si",$raw_file, $regs);         //get body data only
        //$raw_file = $regs[1];
        $raw_file = preg_replace("@<!--.*?-->@si", " ",$raw_file);              //  kill comments in full text
        $raw_file = preg_replace("@src=\"//@si", "src=\"http://", $raw_file);   //  rebuild absolute URL

        //  get all links placed in <a> elements
        preg_match_all("/<a(.*?)>/si", $raw_file, $regs, PREG_SET_ORDER);
        foreach ($regs as $val) {
            $all_media[] = str_replace("  ","", str_replace($trash1, $replace1, $val));
        }

        //get all media placed in <object> elements
        $element = 'object';
        $all_media= get_elements($element, $all_media, $raw_file, $regs, $trash1, $replace1);

        //  also get all images placed in <img> elements
        preg_match_all("/<img(.*?)>/si", $raw_file, $regs, PREG_SET_ORDER);

        foreach ($regs as $val) {
            $all_media[] = str_replace("  ","", str_replace($trash1, $replace1, $val));
        }

        //  ********   find images     ********
        if ($index_image == '1') {
            $select         = $imagelist;   //  find only images as defined in file 'image.txt'
            $media_type     = 'image';      //  define that media type = image
            $title          = '';

            if ($debug == '2') {        //  if debug mode, show details
                printStandardReport('newImage', $command_line, $no_log);
            }

            foreach ($all_media as $thisimage) {
                $key = array_search($thisimage, $all_media);        //      remember the key of this image
                preg_match("/([\/]?value|[\/]?data|[\/]?classid|[\/]?src)\s*=\s*[\'\"](.*?)[\'\"]/si",$thisimage[0], $this_img);
                $valid_img[] = $thisimage[1];    //  collect all valid image objects
            }

            foreach ($valid_img as $this_obj) {
                $new_img = '';
                preg_match("/([\/]?value|[\/]?data|[\/]?classid|[\/]?src)\s*=\s*[\'\"](.*?)[\'\"]/si",$this_obj, $my_img);
                $new_img = $my_img[2];

                if(strlen($new_img) > "3") {  //  follow only links, not blanks etc.
                    if (($link = build_url($new_img, $url, $select)) != '') {   //  if valid URL was built
                        //echo "\r\n\r\n<br /> link: $link<br />\r\n";
                        $unreachable    = '';
                        $my_name    = '';
                        $new_md5    = md5_file($link);     //      calculate checksum of new remote image
                        $linkparts  = parse_url($link);
                        $suffix     = strtolower(substr($linkparts['path'], strrpos($linkparts['path'], ".")+1));
                        $command    = '';
                        if ($suffix =="php") {  //  use php file as command to download the image
                            //echo "<br />*****************************  php Treffer<br />\r\n";
                            $command = $suffix;
                            $status      = array();
                            $suffix     = '';
                            $path       = $linkparts['path'];

                            if (isset($linkparts['query'])) {
                                $path .= "?".$linkparts['query'];
                            }

                            $all                = "*/*"; //just to prevent "comment effect" in get accept
                            $host               = $linkparts['host'];
                            $port               = "80";
                            $errno              = 0;
                            $errstr             = "";
                            $fsocket_timeout    = "120";
                            $request            = "GET $path HTTP/1.1\r\nHost: $host\r\nAccept: $all\r\nUser-Agent: $user_agent\r\n\r\n";

                            $fp = fsockopen($host, $port, $errno, $errstr, $fsocket_timeout);   //  get connected to the host

                            $linkstate = "ok";
                            if (!$fp) {
                                if ($debug == '2') {
                                    print $errstr;
                                }
                                $unreachable = "5";
                            } else {
                                socket_set_timeout($fp, 60);
                                fputs($fp, $request);           //  send the request for the image to the server
                                $answer = fgets($fp, 4096);

                                if (strpos($answer, "500")) {  // try again after 1 second
                                    fclose($fp);    // close existing connection
                                    sleep(1);       //  might not be necessary to wait, but . . .
                                    $fsocket_timeout = 120;
                                    $errno = 0;
                                    $errstr = "";

                                    $fp = fsockopen($host, $port, $errno, $errstr, $fsocket_timeout);   //try to re-connect

                                    $linkstate = "ok";
                                    if (!$fp) {
                                        if ($debug == '2') {
                                            print $errstr;
                                        }
                                        $unreachable = "6";
                                    } else {
                                        //  use a browser user agent, as some server do not accept crawler
                                        $user_agent = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)";        //  use a browser user agent
                                        $request    = "GET $path HTTP/1.1\r\nHost: $host\r\nAccept: $all\r\nUser-Agent: $user_agent\r\n\r\n";

                                        fputs($fp, $request);
                                        $answer = fgets($fp, 4096);
                                    }
                                }

                                $regs = Array ();
                                if (preg_match("{HTTP/[0-9.]+ (([0-9])[0-9]{2})}i", $answer, $regs)) {
                                    $httpcode = $regs[2];
                                    $full_httpcode = $regs[1];

                                    if ($httpcode <> 2 && $httpcode <> 3) {
                                        $unreachable = "4";
                                        $linkstate = "Unreachable";
                                    } else {
                                        $linkstate ="okay";
                                    }
                                }

                                if ($linkstate != "Unreachable") {
                                    while ($answer) {       //  find name and suffix of the image to be doiwnloaded
                                        $answer = fgets($fp, 8192);
                                        if (preg_match("/Content-Disposition/i", $answer)) {
                                            //  extract name and suffix of this image
                                            $image      = substr($answer, strpos($answer, "''")+2);
                                            $my_name    = substr($image, 0, strrpos($image, "."));
                                            $suffix     = trim(strtolower(substr($image, strrpos($image, ".")+1)));
                                        }
                                    }
                                }
                            }

                        } else {    //  image could be opened directely
                            $my_name    = basename($linkparts['path'], $suffix);
                        }

                        //echo "\r\n\r\n<br /> my_name: $my_name<br />\r\n";
                        //echo "\r\n\r\n<br /> suffix: $suffix<br />\r\n";
                        //      try to find already indexed image with the same md5sum
                        $sql_query = "SELECT md5sum from ".$mysql_table_prefix."media where md5sum like '$new_md5'";
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

                        if ($result->num_rows && $new_md5) {   //      enter only, if PHP function md5_file()  is allowed for remote files
                            if ($dup_media == '1') {    //  if we should index duplicate media on different pages
                                //      get all data for this duplicate image
                                $sql_query = "SELECT * from ".$mysql_table_prefix."media where md5sum like '$new_md5'";
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
                                    //      store all information about actual (duplicate) image
                                    $var        = $result->fetch_array(MYSQLI_NUM);

                                    $sql_url        = $db_con->real_escape_string($url);
                                    $sql_link       = $db_con->real_escape_string($link);
                                    $thumb          = $var[4];
                                    $sql_thumb      = $db_con->real_escape_string($thumb);
                                    $title          = $var[5];
                                    $sql_title      = $db_con->real_escape_string($title);
                                    $id3_string     = $var[12];
                                    $sql_id3_string = $db_con->real_escape_string($id3_string);
                                    $my_name        = $var[14];
                                    $sql_my_name    = $db_con->real_escape_string($my_name);

                                    $sql_query = "INSERT into ".$mysql_table_prefix."media (link_id, link_addr, media_link, thumbnail, title, type, size_x, size_y, id3, md5sum, name, suffix) values
                                                                                          ('$link_id', '$sql_url', '$sql_link', '$sql_thumb', '$sql_title', '$var[6]', '$var[7]', '$var[8]', '$sql_id3_string', '$var[13]', '$sql_my_name', '$suffix')";
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
                            }
                            unset($all_media[$key]);        //      image was duplicate Delete this object

                        } else {

                            //  for new image objects we need to build a temporary file
                            mysqltest();
                            //$link = str_replace("?", "...", $link);             //  if file system does not accept question marks as part of the file name

                            if ($fp_remote = @fopen($link, 'rb')) {
                                $localtempfile = tempnam('./tmp', 'img');        //  create name and path for temp image
                                if ($fp_local = fopen($localtempfile, 'wb')) {
                                    while ($buffer = @fread($fp_remote, 8192)) {    //  read remote image
                                        //mysqltest();
                                        fwrite($fp_local, $buffer);                 //      write to local  temp image
                                    }
                                }
                                @fclose($fp_local);
                                @fclose($fp_remote);
                            } else {    //  if not impossible to open by PHP function 'fopen()', try to open this image by means of cURL library
                                if ($curl == '1') {    //  if cURL library
                                    if($buffer = curl_open($link)) {
                                        $localtempfile = tempnam('./tmp', 'img');
                                        if ($fp_local = fopen($localtempfile, 'wb')) {
                                            fwrite($fp_local, $buffer);
                                        } else {
                                            $unreachable = '1';    //   unable to write to temp-file
                                        }
                                        fclose($fp_local);
                                    } else {
                                        $unreachable = '2'; //  unable to open the remote file by cURL
                                    }
                                } else {
                                    $unreachable = '3'; //  no cURL library available
                                }
                                @fclose($fp_local);
                            }

                            $size = @getimagesize ($localtempfile);  // get geometrical info of temporary image
                            //echo "\r\n\r\n<br /> localtempfile: $localtempfile<br />\r\n";
                            //echo "\r\n\r\n<br>size Array:<br><pre>";print_r($size);echo "</pre>\r\n";
                            if ($size && ($size[0] >= $min_image_x && $size[1] >= $min_image_y)) {     //  if image is large enough
                                $localtempfile = $db_con->real_escape_string($localtempfile);
                                mysqltest();
                                $sql_query = "SELECT link_id from ".$mysql_table_prefix."media where link_id = $link_id AND media_link = '$link'";
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

                                //  if this image is new
                                if (!$result->num_rows) {

                                    $title = '0';
                                    //  extract file name
                                    $name = basename($link);
                                    if ($suppress_suffix) {     //  if activated in admin settings, suppress the file suffix
                                        $name = substr($name, 0, strrpos($name, "."));
                                    }

                                    //      get title of this image
                                    if ($command) {     //  for php command files we need to use the image name and its suffix
                                        $title  = "$my_name.$suffix";
                                        //$title  = $my_name;
                                        $name   = $image;
                                    } else {    //  find any available title for direct downloadable file
                                        //  first try to find within double quotations
                                        preg_match("/([\/]?title)\s*=\s*[\"](.*?)[\"]/si",$this_obj, $this_title);
                                        $title = $this_title[2];

                                        if (!$title) {  //  alternate search for title in single quotations
                                            preg_match("/([\/]?title)\s*=\s*[\'](.*?)[\']/si",$this_obj, $this_title);
                                            $title = $this_title[2];
                                        }

                                        //  try to get a title from alt tag with double quotations
                                        preg_match("/([\/]?alt)\s*=\s*[\"](.*?)[\"]/si",$this_obj, $this_alt_title);
                                        $alt_title = $this_alt_title[2];


                                        if (!$alt_title) {  //  alternate search for alt title in single quotations
                                            preg_match("/([\/]?alt)\s*=\s*[\'](.*?)[\']/si",$this_obj, $this_alt_title);
                                            $alt_title = $this_alt_title[2];
                                        }

                                        if (!$title) {  //  if no title was found, use alt tag
                                            $title = $alt_title;
                                        }

                                        if ($title != $alt_title && $index_alt) {   //  if activated in admin settings, add the alt tag to the title
                                            $title .= " ".$alt_title."";
                                        }

                                        if (!$title) {    //  because we didn't find any title (even in alt tag), we need to use filename as title
                                            $title = str_replace($trash1, $replace1, $name);
                                        }
                                    }

                                    //  extract EXIF info
                                    $id3_string = '0';
                                    if ($index_id3 == '1') {
                                        $id3_string = get_exif($localtempfile);
                                    }

                                    $title      = @iconv($charSet, "UTF-8//IGNORE", $title);
                                    $my_name    = @iconv($charSet, "UTF-8//IGNORE", $my_name);
                                    $id3_string = @iconv($charSet, "UTF-8//IGNORE", $id3_string);

                                    $title_orig     = $title;
                                    $title_trans    = $title;
                                    $title      .= $delim;

                                    //  kill all secondary characters from title tag
                                    if ($del_seccharin) {

                                        $title_rem      = $title_trans;
                                        $title_trans    = del_secintern($title_trans);

                                        if ($title_trans != $title_rem) {
                                            $title .= " ".$title_trans."";  //  add new words to title
                                        }
                                    }

                                    //   convert to lower case
                                    if ($case_sensitive =='0') {

                                        $title_rem = $title_trans;
                                        $title_trans = lower_case(lower_ent($title_trans));

                                        if ($title_trans != $title_rem) {
                                            $title .= " ".$title_trans."";  //  add new words to title
                                        }
                                    }

                                    //  remove Latin accents
                                    if ($vowels || $noacc_el) {

                                        $title_rem = $title_trans;
                                        $title_trans = remove_acc($title_trans, '0');

                                        if ($title_trans != $title_rem) {
                                            $title .= " ".$title_trans."";
                                        }
                                    }

                                    //  remove Greek accents
                                    if ($noacc_el) {

                                        $title_rem = $title_trans;
                                        $title_trans = remove_acc_el($title_trans, '0');

                                        if ($title_trans != $title_rem) {
                                            $title .= " ".$title_trans."";
                                        }
                                    }

                                    //  transliterate into Greek language
                                    if ($translit_el) {

                                        $title_rem = $title_trans;
                                        $title_trans = translit_el($title_trans, '0');

                                        if ($title_trans != $title_rem) {
                                            $title .= " ".$title_trans."";
                                        }
                                    }

                                    //      store all information about actual image
                                    $sql_url        = $db_con->real_escape_string($url);
                                    $sql_link       = $db_con->real_escape_string($link);
                                    $sql_title      = $db_con->real_escape_string($title);
                                    $sql_id3_string = $db_con->real_escape_string($id3_string);
                                    $sql_my_name    = $db_con->real_escape_string($my_name);

                                    mysqltest();
                                    $sql_query = "INSERT into ".$mysql_table_prefix."media (link_id, link_addr, media_link, title, type, size_x, size_y, id3, md5sum, name, suffix) values ('$link_id', '$sql_url', '$sql_link', '$sql_title', '$media_type', '$size[0]', '$size[1]', '$sql_id3_string', '$new_md5', '$sql_my_name', '$suffix')";
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

                                    //  get actual media_id
                                    $sql_query = "SELECT media_id from ".$mysql_table_prefix."media where media_link like '$sql_link'";
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
                                        $var = $result->fetch_array(MYSQLI_NUM);
                                        $media_id = $var[0];
                                    }

                                    $gd = gd_info();     //  try to get GD library info
                                    if (!$gd) {
                                        $output = "".$include_dir."/images/dummy.png";  // no GD-library support. Present dummy.gif as thumbnail
                                    } else {
                                        //  Create thumbnail
                                        // Set a maximum height and width for thumbnail
                                        $width  = '160';
                                        $height = '100';
                                        $output = '';
                                        $im = '';

                                        if (!$command) {    //  extract the suffix, but not for php command files
                                            $exts = preg_split("{[/\\.]}", strtolower($link)) ;
                                            $n = count($exts)-1;
                                            $suffix = $exts[$n];
                                        }
                                        //print "suffix: '$suffix'<br />";
                                        mysqltest();
                                        if($suffix == "jpeg" || $suffix == "jpg" || $suffix == "jif" || $suffix == "jpe"){
                                            $im = @imagecreatefromjpeg($localtempfile);
                                        }
                                        else if($suffix == "png"){
                                            $im = @imagecreatefrompng($localtempfile);
                                        }
                                        else if($suffix == "gif"){
                                            $im = @imagecreatefromgif($localtempfile);
                                        }
                                        else if($suffix == "gd"){
                                            $im = @imagecreatefromgd($localtempfile);
                                        }
                                        else if($suffix == "gd2"){
                                            $im = @imagecreatefromgd2($localtempfile);
                                        }
                                        else if($suffix == "wbmp"){
                                            $im = @imagecreatefromwbmp($localtempfile);
                                        }
                                        mysqltest();

                                        if (!$im) {
                                            $output = ".".$thumb_folder."/dummy.png";   // create name for thumbnail
                                            $im = @ImageCreate (150, 50) or die ('');
                                            $background_color = ImageColorAllocate ($im, 189, 228, 212);
                                            $text_color = ImageColorAllocate ($im, 255, 0, 0);
                                            ImageString ($im, 2, 8, 3, "No thumbnail available", $text_color);
                                            ImageString ($im, 2, 14, 18, "for this image type,", $text_color);
                                            ImageString ($im, 2, 16, 33, "or image not found.", $text_color);
                                            ImagePNG ($im,$output);

                                        } else {
                                            // calculate dimensions for thumbnails
                                            list($width_orig, $height_orig) = getimagesize($localtempfile);
                                            $ratio_orig = $width_orig/$height_orig;

                                            if ($width/$height > $ratio_orig) {
                                                $width = intval($height*$ratio_orig);
                                            } else {
                                                $height = intval($width/$ratio_orig);
                                            }
                                            //echo "\r\n\r\n<br /> width: $width<br />\r\n";
                                            //echo "\r\n\r\n<br /> height: $height<br />\r\n";
                                            // resample
                                            $image_p = imagecreatetruecolor($width, $height);
                                            imagecopyresampled($image_p, $im, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
                                            $thumb_file = substr($name, 0, strrpos($name, '.'));
                                            if (!preg_match("/(^[.a-zA-Z0-9-+_%&\$§~ ])/i", $thumb_file)) {
                                                $thumb_file = "non-ASCII";      //  replacement for non-ASCII names. Visable only as name in thumbs subfolder
                                            }
                                            mysqltest();

                                            //  define thumbnail file
                                            if (ImageTypes() & IMG_GIF) {           //  does gd-library support png ?
                                                $output = ".".$thumb_folder."/".$dba_act."...".$mysql_table_prefix."...".$link_id."_-_".$media_id."-_-".$thumb_file.".gif";  // create name for thumbnail
                                                imagegif($image_p,$output);         // make a .gif file
                                            } else {
                                                if (ImageTypes() & IMG_PNG) {       //  does gd-library support gif?
                                                    $output = ".".$thumb_folder."/".$dba_act."...".$mysql_table_prefix."...".$link_id."_-_".$media_id."-_-".$thumb_file.".png";
                                                    imagepng($image_p,$output);
                                                }
                                            }
                                            //echo "\r\n\r\n<br /> output: $output<br />\r\n";
                                            //echo "\r\n\r\n<br /> image_p1: $image_p<br />\r\n";
                                            if ($output =='') {
                                                $output = ".".$thumb_folder."/dummy.png";  // no GD-library support for gif and png
                                            }

                                            $thumb = $db_con->real_escape_string(file_get_contents($output));
                                            mysqltest();
                                            //      store actual image in database
                                            $sql_query = "UPDATE ".$mysql_table_prefix."media set thumbnail='$thumb' where media_link like '$sql_link' ";
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
                                    }

                                    imagedestroy ($im); // close the gd library

                                    //      output thumbnail, title and URL of actual image
                                    if ($debug == '2') {
                                        if ($unreachable) {
                                            if ($unreachable == '1') $error = "Unable to write to temp-file.";
                                            if ($unreachable == '2') $error = "Unable to open the remote image by cURL function.";
                                            if ($unreachable == '3') $error = "Unable to open image by means of PHP function fopen(), nor cURL library available.";
                                            if ($unreachable == '4') $error = "Image is unreachable: http $full_httpcode";
                                            if ($unreachable == '5') $error = "php command-file responds NOHOST (1.trial)";
                                            if ($unreachable == '6') $error = "php command-file responds NOHOST (2.trial)";

                                            if ($command_line ==1 || $cl == 1) { //  no thumbnail ouput for command line operation
                                                $all = "\r\n<br /> Title : $title_orig\r\n<br />Size : $size[0] x $size[1] pixel\r\n<br />Link : <a href='$link' target='rel' title='View link in new window'>".$link."</a>\r\n<br />No response, unreachable. $error\r\n<br />";
                                            } else {
                                                $all = "<span class='bd'>
                                                            <table width='100%'>
                                                                <tr>
                                                                    <td class='cntr x4'><a href='$link' target='rel' title='View image in new window'><img src=\"$output\" border=1 alt=\"Thumbnail\" /></a></td>
                                                                    <td> Title :&nbsp;&nbsp;$title_orig<br /><br />Size :&nbsp;&nbsp;$size[0] x $size[1] pixel<br /><br />Link : <a href='$link' target='rel' title='View link in new window'>".$link."</a><br /><br /><span class='warnadmin'>No response, unreachable. $error</span><br /></td>
                                                                </tr>
                                                            </table></span>
                                                            ";
                                            }
                                            printActMedia($all);
                                        } else {
                                            if ($command_line ==1 || $cl == 1) { //  no thumbnail ouput for command line operation
                                                $all = "Title : $title_orig\r\n<br />Size : $size[0] x $size[1] pixel<br />\r\nLink : <a href='$link' target='rel' title='View link in new window'>".$link."</a>\r\n<br />";
                                            } else {
                                                $all = "<span class='bd'>
                                                        <table width='100%'>
                                                            <tr>
                                                                <td class='cntr x4'><a href='$link' target='rel' title='View image in new window'><img src=\"$output\" border=1 alt=\"Thumbnail\" /></a></td>
                                                                <td> Title :&nbsp;&nbsp;$title_orig<br /><br />Size :&nbsp;&nbsp;$size[0] x $size[1] pixel<br /><br />Link : <a href='$link' target='rel' title='View link in new window'>".$link."</a><br /></td>
                                                            </tr>
                                                        </table></span>
                                                        ";
                                            }
                                            printActMedia($all);
                                        }
                                    }

                                    //unlink($output);
                                    unset($output, $all_media[$key]);    //      We got this image. Delete it from media array
                                }
                            } else {
                                unset($all_media[$key]);        //      image was too small. Delete this object
                            }
                        }
                    }
                }
                @unlink($localtempfile); // Delete temporary image file
            }
            $size = array();
        }

        sort($all_media);
        if ($clear == '1') {
            unset ($id3_string, $element, $thisimage, $title, $title_orig, $title_trans, $id3_string, $id3_rem, $name, $select, $all, $output);
        }

        //  ********* find audio streams  *********
        mysqltest();
        if ($index_audio == '1') {
            $select     = $audiolist;      //  find only audio streams as defined in file 'audio.txt'
            $element    = 'audio';
            $title      = '';

            //get media placed in <audio> elements
            $all_media= get_elements($element, $all_media, $raw_file, $regs, $trash1, $replace1, $handle, $store_file);

            if ($debug == '2') {         //  if debug mode, show details
                printStandardReport('newAudio', $command_line, $no_log);
            }

            mysqltest();
            foreach ($all_media as $thisaudio) {
                preg_match("/([\/]?value|[\/]?href|[\/]?data|[\/]?classid|[\/]?src)\s*=\s*[\'\"](.*?)[\'\"]/si",$thisaudio[0], $this_audio);
                if (($link = build_url($this_audio[2], $url, $select, $thisaudio[0], $handle, $store_file)) != '') { //  if valid URL was built

                    $link = $db_con->real_escape_string($link);
                    $handle = @fopen($link, "r");

                    if ($handle) {                      //  really existing audio, or dead link only
                        @fclose($handle);
                        $new_md5    = md5_file($link);     //      calculate checksum of new audio stream
                        $suffix     = strtolower(substr($link, strrpos($link, ".")));
                        $my_name    = basename($link, $suffix);
                        //      try to find already indexed audio streamwith the same md5sum
                        $sql_query = "SELECT md5sum from ".$mysql_table_prefix."media where md5sum like '$new_md5'";
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
                        if ($result->num_rows && $new_md5) {   //      enter only, if PHP function md5_file()  is allowed for remote files
                            if ($dup_media == '1') {    //  if we should index duplicate media on different pages
                                //      get all data for this duplicate audio
                                $sql_query = "SELECT * from ".$mysql_table_prefix."media where md5sum like '$new_md5'";
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
                                    //      store all information about actual (duplicate) audio
                                    $var = $result->fetch_array(MYSQLI_NUM);
                                    $db_con->query ("INSERT into ".$mysql_table_prefix."media (link_id, link_addr, media_link, thumbnail, title, type, size_x, size_y, id3, md5sum, name, suffix) values ('$link_id', '$url', '$var[3]', '$var[4]', '$var[5]', '$var[6]', '$var[7]', '$var[8]', '$var[12]', '$var[13]', '$my_name', '$suffix')");
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
                            unset($all_media[$key]);        //      audio was duplicate. Delete this object
                        } else {
                            $sql_query = "SELECT link_id from ".$mysql_table_prefix."media where link_id = $link_id AND media_link = '$link'";
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

                            if (!$result->num_rows) {       //  if this audio is new
                                $name = basename($link);    //  extract file name

                                //      get "title" of this audio
                                $title = '0';
                                if (stristr($thisaudio[1], "title")) {      //     if there is a title available
                                    preg_match("/title\s*=\s*[\'\"](.*?)\"|'/si",$thisaudio[1], $regs); //get 'title' data only
                                    if (strlen(trim($regs[1])) > 2) {
                                        $title = $regs[1];
                                    }
                                }

                                //      try to use text in object as title
                                if (strlen($title) < 3 && strrpos($thisaudio[1], ">")) {
                                    $title = trim(substr ($thisaudio[1], strpos($thisaudio[1], '">')+2));
                                }

                                if (strlen($title) < 3) {                   //  need to use filename as title
                                    $title = str_replace($trash1, $replace1, $name);
                                }

                                $id3_string = '';
                                if ($index_id3 == '1') {
                                    $id3_string = get_id3string($link, '1', $cl);
                                }

                                //      audio stream was reachable?
                                if ($id3_string != '') {   //      valid audio stream found

                                    $title      = @iconv($charSet, "UTF-8//IGNORE", $title);
                                    $my_name    = @iconv($charSet, "UTF-8//IGNORE", $my_name);
                                    $id3_string = @iconv($charSet, "UTF-8//IGNORE", $id3_string);

                                    $title_orig     = $title;
                                    $title_trans    = $title;
                                    $id3_trans      = $id3_string;
                                    $title      .= $delim;
                                    $id3_string .= $delim;

                                    //  kill all secondary characters from title tag and EXIF info
                                    if ($del_seccharin) {
                                        $title_rem = $title_trans;
                                        $id3_rem    = $id3_trans;

                                        $title_trans = del_secintern($title_trans);
                                        if ($title_trans != $title_rem) {
                                            $title .= " ".$title_trans."";  //  add new words to title
                                        }

                                        $id3_trans = del_secintern($id3_trans);
                                        if ($id3_trans != $id3_rem) {
                                            $id3_string .= " ".$id3_trans."";  //  add new words to EXIF info
                                        }
                                    }

                                    //   convert to lower case
                                    if ($case_sensitive =='0') {
                                        $title_rem = $title_trans;
                                        $id3_rem    = $id3_trans;

                                        $title_trans = lower_case(lower_ent($title_trans));
                                        if ($title_trans != $title_rem) {
                                            $title .= " ".$title_trans."";  //  add new words to title
                                        }

                                        $id3_trans = lower_case(lower_ent($id3_trans));
                                        if ($id3_trans != $id3_rem) {
                                            $id3_string .= " ".$id3_trans."";  //  add new words to EXIF info
                                        }
                                    }

                                    //  remove Latin accents
                                    if ($vowels || $noacc_el) {
                                        $title_rem = $title_trans;
                                        $id3_rem    = $id3_trans;

                                        $title_trans = remove_acc($title_trans, '0');
                                        if ($title_trans != $title_rem) {
                                            $title .= " ".$title_trans."";
                                        }

                                        $id3_trans = remove_acc($id3_trans, '0');
                                        if ($id3_trans != $id3_rem) {
                                            $id3_string .= " ".$id3_trans."";  //  add new words to EXIF info
                                        }
                                    }

                                    //  remove Greek accents
                                    if ($noacc_el) {
                                        $title_rem = $title_trans;
                                        $id3_rem    = $id3_trans;

                                        $title_trans = remove_acc_el($title_trans, '0');
                                        if ($title_trans != $title_rem) {
                                            $title .= " ".$title_trans."";
                                        }

                                        $id3_trans = remove_acc_el($id3_trans, '0');
                                        if ($id3_trans != $id3_rem) {
                                            $id3_string .= " ".$id3_trans."";  //  add new words to EXIF info
                                        }
                                    }

                                    //  transliterate into Greek language
                                    if ($translit_el) {
                                        $title_rem = $title_trans;
                                        $id3_rem    = $id3_trans;

                                        $title_trans = translit_el($title_trans, '0');
                                        if ($title_trans != $title_rem) {
                                            $title .= " ".$title_trans."";
                                        }

                                        $id3_trans = translit_el($id3_trans, '0');
                                        if ($id3_trans != $id3_rem) {
                                            $id3_string .= " ".$id3_trans."";  //  add new words to EXIF info
                                        }

                                    }

                                    //      store all information about actual audio
                                    $title = $db_con->real_escape_string($title);
                                    $link = str_replace(" ", "%20", $link);     //      replace invalid blanks in URL
                                    mysqltest();
                                    $sql_query = "INSERT into ".$mysql_table_prefix."media (link_id, link_addr, media_link, title, type, size_x, size_y, id3, md5sum, name, suffix) values ('$link_id', '$url', '$link', '$title', '$element', '$size[0]', '$size[1]', '$id3_string', '$new_md5', '$my_name', '$suffix')";
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

                                //      output title, name and URL of actual audio
                                if ($debug == '2') {
                                    $link = str_replace($trash1, $replace1, $link);
                                    if ($id3_string == '') {   //    unable to open the media link
                                        $output = "Title: $title_orig<br />Link : $link";
                                        printActMedia($output);
                                        printUrlStatus("No response, ID3 tag unreachable.<br />", $command_line, $no_log);
                                    } else {
                                        $output = "Title:&nbsp;&nbsp;$title_orig<br />Link :<a href=\"$link\">$link</a><br />";
                                        printActMedia($output);
                                    }
                                }
                                $key = array_search($thisaudio, $all_media);
                                unset($all_media[$key]);                    //      we have got it, delete this audio from media array
                            }
                        }
                    }
                }
            }
        }
        sort($all_media);
        if ($clear == '1') {
            unset ($id3_string, $element, $thisaudio, $title, $title_orig, $title_trans, $id3_string, $id3_rem, $name, $select);
        }

        //  ********** find videos **********
        mysqltest();
        if ($index_video == '1') {
            $select     = $videolist;   //  find only videos as defined in file 'video.txt'
            $element    = 'video';
            $title      = '';

            //get media placed in <video> elements
            $all_media= get_elements($element, $all_media, $raw_file, $regs, $trash1, $replace1, $handle, $store_file);

            if ($debug == '2') {        //  if debug mode, show details
                printStandardReport('newVideo', $command_line, $no_log);
            }

            foreach ($all_media as $thisvideo) {
                preg_match("/([\/]?value|[\/]?href|[\/]?data|[\/]?classid|[\/]?src)\s*=\s*[\'\"](.*?)[\'\"]/si",$thisvideo[0], $this_video);
                if (($link = build_url($this_video[2], $url, $select, $thisvideo[0], $handle, $store_file)) != '') { //  if valid URL was built

                    $link = $db_con->real_escape_string($link);
                    $handle = @fopen($link, "r");

                    if ($handle) {                      //  really existing video, or dead link only
                        @fclose($handle);
                        $new_md5    = md5_file($link);     //      calculate checksum of new video
                        $suffix     = strtolower(substr($link, strrpos($link, ".")));
                        $my_name    = basename($link, $suffix);
                        //      try to find already indexed video with the same md5sum
                        $sql_query = "SELECT md5sum from ".$mysql_table_prefix."media where md5sum like '$new_md5'";
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
                        if ($result->num_rows && $new_md5) {   //      enter only, if PHP function md5_file()  is allowed for remote files
                            if ($dup_media == '1') {    //  if we should index duplicate media on different pages
                                //      get all data for this duplicate video
                                $sql_query = "SELECT * from ".$mysql_table_prefix."media where md5sum like '$new_md5'";
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
                                    //      store all information about actual (duplicate) video
                                    $var = $result->fetch_array(MYSQLI_NUM);
                                    $db_con->query ("INSERT into ".$mysql_table_prefix."media (link_id, link_addr, media_link, thumbnail, title, type, size_x, size_y, id3, md5sum, name, suffix) values ('$link_id', '$url', '$var[3]', '$var[4]', '$var[5]', '$var[6]', '$var[7]', '$var[8]', '$var[12]', '$var[13]', '$my_name', '$suffix')");
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
                            unset($all_media[$key]);        //      video was duplicate Delete this object
                        } else {                            //      seems to be a new video
                            mysqltest();
                            $sql_query ="SELECT link_id from ".$mysql_table_prefix."media where link_id = $link_id AND media_link = '$link'";
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

                            if (!$result->num_rows) {                                       //  if this video is new
                                $name = basename($link);    //  extract file name

                                //      get "title" of this video
                                $title = '0';
                                if (stristr($thisvideo[1], "title")) {                                  //  if there is a title available
                                    preg_match("/title\s*=\s*[\'\"](.*?)\"|'/si",$thisvideo[1], $regs); //  get 'title' data only
                                    if (strlen(trim($regs[1])) > 2) {
                                        $title = $regs[1];
                                    }
                                }
                                //      try to use text in object as title
                                if (strlen($title) < 3 && strrpos($thisvideo[1], ">")) {
                                    $title = trim(substr ($thisvideo[1], strpos($thisvideo[1], '">')+2));
                                }

                                if (strlen($title) < 3) {    //  need to use filename as title
                                    $title = str_replace($trash1, $replace1, $name);
                                }

                                $id3_string = '';
                                if ($index_id3 == '1') {
                                    $id3_string = get_id3string($link, '1', $cl);
                                }

                                //      video stream was reachable?
                                if ($id3_string != '') {    //      valid video stream found

                                    $title      = @iconv($charSet, "UTF-8//IGNORE", $title);
                                    $my_name    = @iconv($charSet, "UTF-8//IGNORE", $my_name);
                                    $id3_string = @iconv($charSet, "UTF-8//IGNORE", $id3_string);

                                    $title_orig     = $title;
                                    $title_trans    = $title;
                                    $id3_trans      = $id3_string;
                                    $title      .= $delim;
                                    $id3_string .= $delim;

                                    //  kill all secondary characters from title tag and EXIF info
                                    if ($del_seccharin) {
                                        $title_rem = $title_trans;
                                        $id3_rem    = $id3_trans;

                                        $title_trans = del_secintern($title_trans);
                                        if ($title_trans != $title_rem) {
                                            $title .= " ".$title_trans."";  //  add new words to title
                                        }

                                        $id3_trans = del_secintern($id3_trans);
                                        if ($id3_trans != $id3_rem) {
                                            $id3_string .= " ".$id3_trans."";  //  add new words to EXIF info
                                        }
                                    }

                                    //   convert to lower case
                                    if ($case_sensitive =='0') {
                                        $title_rem = $title_trans;
                                        $id3_rem    = $id3_trans;

                                        $title_trans = lower_case(lower_ent($title_trans));
                                        if ($title_trans != $title_rem) {
                                            $title .= " ".$title_trans."";  //  add new words to title
                                        }

                                        $id3_trans = lower_case(lower_ent($id3_trans));
                                        if ($id3_trans != $id3_rem) {
                                            $id3_string .= " ".$id3_trans."";  //  add new words to EXIF info
                                        }
                                    }

                                    //  remove Latin accents
                                    if ($vowels || $noacc_el) {
                                        $title_rem = $title_trans;
                                        $id3_rem    = $id3_trans;

                                        $title_trans = remove_acc($title_trans, '0');
                                        if ($title_trans != $title_rem) {
                                            $title .= " ".$title_trans."";
                                        }

                                        $id3_trans = remove_acc($id3_trans, '0');
                                        if ($id3_trans != $id3_rem) {
                                            $id3_string .= " ".$id3_trans."";  //  add new words to EXIF info
                                        }
                                    }

                                    //  remove Greek accents
                                    if ($noacc_el) {
                                        $title_rem = $title_trans;
                                        $id3_rem    = $id3_trans;

                                        $title_trans = remove_acc_el($title_trans, '0');
                                        if ($title_trans != $title_rem) {
                                            $title .= " ".$title_trans."";
                                        }

                                        $id3_trans = remove_acc_el($id3_trans, '0');
                                        if ($id3_trans != $id3_rem) {
                                            $id3_string .= " ".$id3_trans."";  //  add new words to EXIF info
                                        }
                                    }

                                    //  transliterate into Greek language
                                    if ($translit_el) {
                                        $title_rem = $title_trans;
                                        $id3_rem    = $id3_trans;

                                        $title_trans = translit_el($title_trans, '0');
                                        if ($title_trans != $title_rem) {
                                            $title .= " ".$title_trans."";
                                        }

                                        $id3_trans = translit_el($id3_trans, '0');
                                        if ($id3_trans != $id3_rem) {
                                            $id3_string .= " ".$id3_trans."";  //  add new words to EXIF info
                                        }

                                    }

                                    $size_x = '';
                                    $size_y = '';
                                    $size_x = str_replace("<", '', substr($id3_string, strpos($id3_string,"n_x ;;")+6, 6 ));
                                    $size_y = str_replace("<", '', substr($id3_string, strpos($id3_string,"n_y ;;")+6, 6 ));


                                    //      store all information about actual video
                                    $title = $db_con->real_escape_string($title);
                                    $link = str_replace(" ", "%20", $link);     //      replace invalid blanks in URL
                                    mysqltest();
                                    $sql_query = "INSERT into ".$mysql_table_prefix."media (link_id, link_addr, media_link, title, type, size_x, size_y, id3, md5sum, name, suffix) values ('$link_id', '$url', '$link', '$title', '$element', '$size_x', '$size_y', '$id3_string', '$new_md5', '$my_name', '$suffix')";
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
                                //      output title, name and URL ofactual video
                                if ($debug == '2') {
                                    $link = str_replace($trash1, $replace1, $link);
                                    if ($id3_string == '') { //  unable to open the media link
                                        $output = "Title: $title_orig<br />Link : $link";
                                        printActMedia($output);
                                        printUrlStatus("No response, ID3 tag unreachable.<br />", $command_line, $no_log);
                                    } else {
                                        $output = "Title:&nbsp;&nbsp;$title_orig<br />Link :<a href=\"$link\">$link</a><br />";
                                        printActMedia($output);
                                    }
                                }
                                $key = array_search($thisvideo, $all_media);
                                unset($all_media[$key]);                        //      we have got it, delete this video from media array
                            }
                        }
                    }
                }
            }
        }
        if ($clear == '1') {
            unset ($all_media, $id3_string, $element, $thisvideo, $thisaudio, $thisimage, $title, $title_orig, $title_trans, $id3_string, $id3_rem, $name, $select);
        }
        mysqltest();
    }

?>