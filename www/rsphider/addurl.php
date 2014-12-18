<?php
    //error_reporting (E_ALL);    //  use this for script debugging
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE & ~E_STRICT);

    $ddt        = '';
    $ddt_set    = '';
    $ddt = @date_default_timezone_get();    //  try to read the server defaults
    if ($ddt) {
        $ddt_set = @date_default_timezone_set($ddt);
        if (!$ddt_set){    //  this will prevent 'STRICT' error messages for date() and time() functions
            die ("The Sphider-plus scripts are unable to set the date_default_timezone on your server.<br />Please enable this PHP function. Script execution aborted for security reasons.");
        }
    } else {
        die("The Sphider-plus scripts are unable to read the date_default_timezone from your server.<br />Please enable this PHP function. Script execution aborted for security reasons.");
    }

    define("_SECURE",1);    // define secure constant

    $admin_dir 		= "./admin";
    $include_dir 	= "./include";
    $template_dir 	= "./templates";
    $settings_dir 	= "./settings";
    $language_dir 	= "./languages";

    require_once	("$settings_dir/database.php");

    //      get active database
    if ($dbs_act == '1') {
        $db_con = dbadd_connect($mysql_host1, $mysql_user1, $mysql_password1, $database1);
        $mysql_table_prefix = $mysql_table_prefix1;
    }

    if ($dbs_act == '2') {
        $db_con = dbadd_connect($mysql_host2, $mysql_user2, $mysql_password2, $database2);
        $mysql_table_prefix = $mysql_table_prefix2;
    }

    if ($dbs_act == '3') {
        $db_con = dbadd_connect($mysql_host3, $mysql_user3, $mysql_password3, $database3);
        $mysql_table_prefix = $mysql_table_prefix3;
    }

    if ($dbs_act == '4') {
        $db_con = dbadd_connect($mysql_host4, $mysql_user4, $mysql_password4, $database4);
        $mysql_table_prefix = $mysql_table_prefix4;
    }

    if ($dbs_act == '5') {
        $db_con = dbadd_connect($mysql_host5, $mysql_user5, $mysql_password5, $database5);
        $mysql_table_prefix = $mysql_table_prefix5;
    }

    $def_config = '';
    $plus_nr    = '';
    @include "".$settings_dir."/db".$dbs_act."/conf_".$mysql_table_prefix.".php";
    if (!$plus_nr) {
        $def_config = '1';
        include "/settings/backup/Sphider-plus_default-configuration.php";
    }

    if ($debug == '0') {
        if (function_exists("ini_set")) {
            ini_set("display_errors", "0");
        }
        error_reporting(0);  //     suppress  PHP messages
    }

    $result     = '';
    $ids_result = '';
    if ($use_ids == 1 && $def_config != 1){ // if Intrusion Detection System should be used
        require_once ("$include_dir/ids_handler.php");
    }
    $ids_result = $result;

    require_once	("$include_dir/searchfuncs.php");
    require_once	("$include_dir/categoryfuncs.php");
    require_once    ("$include_dir/commonfuncs.php");

    //error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    $date           = strftime("%d.%m.%Y");                                 //      Format for date
    $time           = date("H:i");                                        //      Format for time
    $mailer         = "$mytitle Addurl-mailer";                             //      Name of mailer
    $subject1       = "A new site suggestion arrived for Sphider-plus";     //      Subject for administrator e-mail when a new suggestion arrived
    $category_id    = '';
    $B1             = '';
    $authent        = 'not yet defined';

    //  do we have categories defined by our Admin?
    $category   = '';
    $sql_query  = "SELECT * from ".$mysql_table_prefix."categories";
    $result     = $db_con->query($sql_query);

    if (!$rows = $result->num_rows){
        $category = -1;
    }

    if ($auto_lng == 1) {   //  if enabled in Admin settings, get country code of calling client
        if ( isset ( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
            $cc = substr( htmlspecialchars($_SERVER['HTTP_ACCEPT_LANGUAGE']), 0, 2);
            $handle = @fopen ("$language_dir/$cc-language.php","r");
            if ($handle) {
                $language = $cc; // if available set language to users slang

            }
            else {
                include "$language_dir/$language-language.php";
            }
            @fclose($handle);
        }
        else {
            include "$language_dir/$language-language.php";
        }
    }
    require_once    ("$language_dir/$language-language.php");

    extract(getHttpVars());

    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
        <!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
        <html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
        <head>
            <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
            <title>$mytitle. Suggest a new site</title>
            <meta name=\"public\" content=\"all\" />
            <link rel='stylesheet' type='text/css' href='$template_dir/$template/userstyle.css' />
            <script type=\"text/javascript\">
                function getObject(obj) {
                  var theObj;
                  if(document.all) {
                    if(typeof obj==\"string\") {
                      return document.all(obj);
                    } else {
                      return obj.style;
                    }
                  }
                  if(document.getElementById) {
                    if(typeof obj==\"string\") {
                      return document.getElementById(obj);
                    } else {
                      return obj.style;
                    }
                  }
                  return null;
                }
                function charCounter(input,output,texto,characters) {
                  var inputObj=getObject(input);
                  var outputObj=getObject(output);
                  var longitud=characters - inputObj.value.length;
                  if(longitud <= 0) {
                    longitud=0;
                    texto='<span class=\"warnadmin\">'+texto+'</span>';
                    inputObj.value=inputObj.value.substr(0,characters);
                  } else {
                    texto='<span class=\"em evrow\">'+texto+'</span>';
                  }
                  outputObj.innerHTML = texto.replace(\"{CHAR}\",longitud);
                }
            </script>
        </head>
        <body>
        ";

    //IDS detected an attack?
    if (strlen($ids_result) > 13 && $def_config != 1) {
        //  get impact of intrusion
        $len = strpos($result, "<")-13;
        $res = trim(substr($result, '1', $len));
        if ($res >= $ids_warn) {
            echo "
                    <br /><br />
                    <div class='headline cntr'>
                        IDS result message
                    </div>
                    <br /><br />
                    $ids_result
                    <br />
                    <div class='cntr warnadmin'>
                        <br />
                        Further input blocked by the Sphider-plus supervisor, because the
                        <br /><br />
                        Intrusion Detection System noticed the above attempt to attack this search engine.
                        <br /><br />
                    </div>
                    <div class='headline cntr'>
                    &nbsp;
                    </div>
                    <br /><br />
                </body>
            </html>
                ";
                    die();
        }
    }

    //  already known as an eval IP by the IDS ?
    if ($ids_blocked == 1 && $def_config != 1) {
        $blocked = '';
        if ( isset ( $_SERVER['REMOTE_ADDR'] ) ) {      //  get actual IP from user
            $new_ip = htmlspecialchars($_SERVER['REMOTE_ADDR']);
            $handle = @fopen ("$include_dir/IDS/tmp/phpids_log.txt","r");
            if ($handle) {      //      read IDS log-file
                $lines = @file("$include_dir/IDS/tmp/phpids_log.txt");
                @fclose($handle);
            }

            foreach ($lines as $thisline) {                             //  analyze all stored intrusion attempts
                preg_match("@\"(.*?)\",(.*?),(.*?),@",$thisline, $regs);
                if ($new_ip == $regs[1] && $regs[3] >= $ids_stop) {     //  if actual IP is known to be eval and impact was significant
                    $blocked = '1';
                }
            }

            if ($blocked) {
                echo "
                        <br /><br />
                        <div class='headline cntr'>
                            IDS message: known eval IP due to former attacks
                        </div>
                        <br /><br />
                        <div class='cntr warnadmin'>
                            <br />
                            Further access blocked by the Sphider-plus supervisor, because the
                            <br /><br />
                            Intrusion Detection System already noticed an attempt to attack this search engine.
                            <br /><br />
                        </div>
                        <div class='headline cntr'>
                        &nbsp;
                        </div>
                        <br /><br />
                    </body>
                </html>
                    ";
                die();
            }
        }
    }

    if ($B1 == $sph_messages['submit']) {

        if($captcha == 1) {     // if Admin selected, evaluate Captcha
            error_reporting(E_ERROR);
            session_start();

            if ($_SESSION['CAPTCHAString'] != $_POST['captchastring']){
                echo "  <h1>$mytitle</h1><br />
                    <p class='em cntr warnadmin'>
                    ".$sph_messages['invalidCaptcha']."
                    <br />
                    </p>
                    <br />
                    <a class='bkbtn' href='addurl.php' title='Go back to Suggest form'>".$sph_messages['BackToSubForm']."</a>
                </body>
            </html>
                    ";
                die ('');
            }

            if ($debug == '0') {
                error_reporting(0);  //     suppress  PHP messages
            } else {
                error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE & ~E_STRICT);
            }
            session_destroy();
        }

        // 	clean input
        if (strpos($url, "?")) {
            $url = substr($url, 0, strpos($url, "?"));   //  remove arguments
        }
        $title          = $db_con->real_escape_string($title);
        $description    = $db_con->real_escape_string($description);
        $url 		    = cleaninput(cleanup_text(trim(substr ($url, 0,1024))));
        $title 		    = trim(substr ($title, 0,255));
        $description    = nl2br(trim(substr ($description, 0,255)));
        $email 		    = cleaninput(cleanup_text(trim(substr ($email, 0,255))));

        $url_1 = stripslashes($url);    //  make it human readable
        //	check URL
        if($whois_user) {   //  perform a WHOIS check for suggested URL

            require_once "$include_dir/domain_whois.php";

            $list       = "";
            $whois      = new whois();  //new class
            $whois_res  = $whois->lookup($url, $whois_ext, $list);
            unset($whois);

            $whois_server   = $whois_res['whoisserver'];
            $whois_result   = $whois_res['result'];
            $whois_answer   =  $whois_res['answer'];

            if ($whois_result != "okay") {
                echo "  <h1>$mytitle</h1>
                            <p>&nbsp;<p>
                            <p class='warnadmin cntr'><br />Invalid URL input. <br />$whois_answer<br /><br /></p>
                            <p>&nbsp;</p>
                            <a class='bkbtn' href='addurl.php' title='Go back to Submission Form'>Back</a></p>
                        </body>
                    </html>
                        ";
                die ('');

            }
        } else {
            if (!$idna) {   //  if not accepted internationalized URLs, do a standard check
                $input  = $url;
                validate_url($input);
                $url = $input;
            } else {
                require_once "$include_dir/idna_converter.php";
                // Initialize the converter class
                $IDN = new idna_convert(array('idn_version' => 2008));
                // The input string, if input is not UTF-8 or UCS-4, it must be converted before
                //$input = utf8_encode($url);
                // Encode it to its punycode presentation
                $punycode_url = $IDN->encode($url);

                $regex = "=(https?|ftp)://[a-z0-9]([a-z0-9-]*[a-z/0-9])?\.[a-z0-9]=i";

                if(!preg_match($regex, $punycode_url)) {
                    echo "<br />
                            <p>&nbsp;<p>
                            <p class='warnadmin cntr'><br />Invalid URL input. <br />$url<br /><br /></p>
                            <p>&nbsp;</p>
                            <a class='bkbtn' href='addurl.php' title='Go back to Submission Form'>Back</a></p>
                            </body>
                            </html>
                        ";
                    exit;
                }
            }
        }

        //	check Title input
        if(strlen($title) < 5 || strlen($title) > 100) {
            echo "  <h1>$mytitle</h1>
                    <p>&nbsp;<p>
                    <p class='em cntr warnadmin'><br />
                    ".$sph_messages['InvTitle']."
                    <br /><br />
                    </p>
                    <p>&nbsp;<p>
                    <a class='bkbtn' href='addurl.php' title='Go back to Suggest form'>".$sph_messages['BackToSubForm']."</a>
                </body>
            </html>
                ";
            die ('');
        }

        //	check Description input
        if(strlen($description) < 5 || strlen($description) > 100) {
            echo "  <h1>$mytitle</h1>
                    <p>&nbsp;<p>
                    <p class='em cntr warnadmin'><br />
                    ".$sph_messages['InvDesc']."
                    <br /><br />
                    </p>
                    <p>&nbsp;<p>
                    <a class='bkbtn' href='addurl.php' title='Go back to Suggest form'>".$sph_messages['BackToSubForm']."</a>
                </body>
            </html>
                ";
            die ('');
        }

        //	check e-mail account
        $input  = $email;
        validate_email($input);
        $email = $input;

        //  reduce the URL to name and TLD
        $urlparts   = parse_url($url);

        $odm_url    = str_replace("www.", "", $urlparts['host']);
        $new_url    = '';

        //	Is the new URL banned as domain?
        $sql_query = "SELECT * FROM ".$mysql_table_prefix."banned where domain like '%$odm_url%'";
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
            echo "  <h1>$mytitle</h1>
                    <p>&nbsp;<p>
                    <p class='em'>
                    Thank you for your suggestion.<br />
                    But the site you suggested is banned from this search engine.<br />
                    </p>
                    <p>&nbsp;<p>
                    <a class='bkbtn' href='$search_script' title='Go back to Sphider-plus'>Back to Sphider-plus</a>
                </body>
            </html>
                ";
            die();
        } else {
            $new_url = 1;
        }

        //	suggested URL is already indexed as domain?
        $sql_query = "SELECT * FROM ".$mysql_table_prefix."sites where url like '%$odm_url%'";
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
            echo "  <h1>$mytitle</h1>
                    <p>&nbsp;<p>
                    <p class='em'>
                    Thank you for your suggestion.<br />
                    But the suggested site is already indexed by this search engine.<br />
                    </p>
                    <p>&nbsp;<p>
                    <a class='bkbtn' href='$search_script' title='Go back to Sphider-plus'>Back to Sphider-plus</a>
                </body>
            </html>
                ";
            die();
        } else {
            $new_url = 1;
        }

        //	suggested URL was already suggested before?
        $sql_query = "SELECT * FROM ".$mysql_table_prefix."addurl where url like '%$odm_url%'";
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
            echo "  <h1>$mytitle</h1>
                    <p>&nbsp;<p>
                    <p class='em'>
                    Thank you for your suggestion.<br />
                    But this domain was already suggested by someone else before.<br />
                    </p>
                    <p>&nbsp;<p>
                    <a class='bkbtn' href='$search_script' title='Go back to Sphider-plus'>Back to Sphider-plus</a>
                </body>
            </html>
                ";
            die();
        } else {
            $new_url = 1;
        }

        if ($new_url) {
            //	Time to store all into database and output a thanks for suggestion
            $sql_query = "INSERT INTO ".$mysql_table_prefix."addurl (url, title, description, category_id, account, authent)
                                                            VALUES ('".$url."', '".$title."', '".$description."', '".$category_id."', '".$email."', '".$authent."')";
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

            echo "  <h1>$mytitle</h1>
                    <p>&nbsp;<p>
                    <p class='em'>
                    Thank you very much.<br />
                    We will check your suggestion " .$url_1. " within the next future.<br />
                    If the new site fulfills all requirements of this search engine, it will be indexed shortly.<br />
                    About our decission we will inform you by e-mail.<br />
                    Thanks again for your effort.<br />
                    </p>
                    <p>&nbsp;<p>
                    <a class='bkbtn' href='$search_script' title='Go back to Sphider-plus'>Back to Sphider-plus</a>
                ";

            //	Finally inform the administrator about the new suggestion
            $title  = str_replace ('\\','',$title);			//	recover title
            $title	= str_replace ('&quot','"',$title);

            $description	= str_replace ('\\','',$description);   //	recover description
            $description	= str_replace ('&quot','"',$description);
            $cat ='';

            if ($category_id != 0) {
                $sql_query = "SELECT * FROM ".$mysql_table_prefix."categories WHERE category_id = $category_id";
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

                $cat ='';
                if ($result !=0) {
                    $row = $result->fetch_array(MYSQLI_ASSOC);
                    $cat = $row['category'];            //      fetch name of category
                }
            }
            $header = "from: $mailer<".$dispatch_email.">\r\n";
            $header .= "Reply-To: ".$dispatch_email."\r\n";
            $subject1    = "A new site suggestion arrived for Sphider-plus";  //  Subject for e-mail to administrator when suggestion arrived

            if ($addurl_info == 1) { //  should we inform the admin by e-mail?
                //      Text for e-mail to administrator when suggestion arrived
                $text1 = "On $date at $time a new site was suggested!\n
    The following dates were submitted:\n\n
    URL           : $url\n
    Titel         : $title\n
    Description   : $description\n
    Category      : $cat\n
    E-mail account: $email\n\n
    This mail was automatically generated by: $mailer.\n\n";

                if (mail($admin_email,$subject1,$text1,$header) or die ("<br /><br /><br />Error to inform the administrator of this site ( $admin_email )<br /><br />Never the less your data was stored on our database.<br /><br />They will be checked within the next future.<br /><br />About the result you will be informed as soon as possible by e-mail.<br /><br />"));
            }
        }
    } else {    //  Here we start the output of the Submission form

        //      get active database
        if ($dbu_act == '1') {
            $db_con = db_connect($mysql_host1, $mysql_user1, $mysql_password1, $database1);
            $mysql_table_prefix = $mysql_table_prefix1;
        }

        if ($dbu_act == '2') {
            $db_con = db_connect($mysql_host2, $mysql_user2, $mysql_password2, $database2);
            $mysql_table_prefix = $mysql_table_prefix2;
        }

        if ($dbu_act == '3') {
            $db_con = db_connect($mysql_host3, $mysql_user3, $mysql_password3, $database3);
            $mysql_table_prefix = $mysql_table_prefix3;
        }

        if ($dbu_act == '4') {
            $db_con = db_connect($mysql_host4, $mysql_user4, $mysql_password4, $database4);
            $mysql_table_prefix = $mysql_table_prefix4;
        }

        if ($dbu_act == '5') {
            $db_con = db_connect($mysql_host5, $mysql_user5, $mysql_password5, $database5);
            $mysql_table_prefix = $mysql_table_prefix5;
        }

        echo "    <h1> $mytitle<br /><br />".$sph_messages['SubForm']."</h1>
            <br />
            <div class=\"cntr\">
                <p class='advsrch'>".$sph_messages['SubmitHeadline']."</p>
                <p class='advsrch'>( ".$sph_messages['AllFields']." ! )</p>
                <br />
                <div class='panel w75'>
                    <form  class='txt' name='add_url' action='addurl.php'  method='post'>
                        <table  class='searchBox'>
                            <tr>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr >
                                <td class='em evrow'>".$sph_messages['New_url']."</td>
                                <td><input type='text' name='url' value='http://' size='60' maxlength='255' /></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td class='em evrow'>".$sph_messages['Title']."</td>
                                <td><input type='text' name='title' id='title' size='60' maxlength='100' onkeyup=\"charCounter('title','titleComplete','<br />&nbsp;&nbsp;".$sph_messages['still']." {CHAR} ".$sph_messages['charLeft']."&nbsp;&nbsp;',100);\" /><div id='titleComplete' class='cntr bd'><span class='em evrow' ><br />&nbsp;&nbsp;".$sph_messages['still']." 100 ".$sph_messages['charLeft']."&nbsp;&nbsp;</span></div></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td class='em evrow'>".$sph_messages['Description']."</td>
                                <td><textarea wrap='physical' class='farbig'  rows='5' cols='43' name='description' id='description' onkeyup=\"charCounter('description','descriptionComplete','<br />&nbsp;&nbsp;".$sph_messages['still']." {CHAR} ".$sph_messages['charLeft']."&nbsp;&nbsp;',250);\"></textarea><div id='descriptionComplete' class='cntr bd'><span class='em evrow' ><br />&nbsp;&nbsp;".$sph_messages['still']." 250 ".$sph_messages['charLeft']."&nbsp;&nbsp;</span></div></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                            </tr>
            ";

        if($show_categories =='1' && $category != -1) {     // if Admin selected, show categories
            echo "          <tr>
                                <td class='em evrow'>".$sph_messages['Category']."</td>
                                <td><select name=\"category_id\" size=\"1\">
                ";

            list_categories (0, 0, "white", "","");
                echo "      </select>
                                </td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                </tr>
                ";
        }

        echo "                <tr>
                                <td class='em evrow'>".$sph_messages['Account']."</td>
                                <td><input type='text' name='email' size='60' maxlength='100' value='your@account' /></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                            </tr>
            ";

        if($captcha == 1) {     // if Admin selected, show Captcha
            echo "                <tr>
                                <td class='em evrow'>".$sph_messages['enterCaptcha']."</td>
                                <td>
                                    <br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src='$include_dir/make_captcha.php?.png' name='capimage' alt='Captcha' border='1' />
                                    <br /><br />
                                    <input type='text' name='captchastring' size='28' value='' />
                                    <br /><br />
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                            </tr>
                ";
        }
        $submit = $sph_messages['submit'];
        echo "                <tr>
                                <td  class='em evrow'></td>
                                <td><input class='submit-button' type='submit' value='$submit' name='B1' /><br /><br /></td>
                            </tr>
                        </table>
                    </form>
                </div>
                <br />
                ";
    }

    footer();

    echo "
            </div>
        </body>
    </html>
        ";

    function list_categories($parent, $lev, $color, $message, $category_id) {
        global $mysql_table_prefix, $debug, $db_con;

        if ($lev == 0) {
            print "\n";
        }
        $space = "";
        $id = "";
        for ($x = 0; $x < $lev; $x++)
        $space .= "&nbsp;&nbsp;&nbsp;-&nbsp;";

        $sql_query = "SELECT * FROM ".$mysql_table_prefix."categories  ORDER BY category LIMIT 0 , 300";
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
            print "<option ".$selected." value=\"0\">&nbsp;&nbsp;none</option>\n";  //select no category
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $id = $row['category_id'];
                $cat = $row['category'];
                $selected = " selected ";
                if ($category_id != $id) { $selected = ""; }
                print "<option ".$selected." value=\"".$id."\">".$space.stripslashes($cat)."</option>\n";
            }
        } else {    //      if no category has been created up to now
            print "<option ".$selected." value=\"".$id."\">".$space.stripslashes($cat)."</option>\n";
        }
        return ;
    }

    // Database1-5 connection
    function dbadd_connect($mysql_host, $mysql_user, $mysql_password, $database) {

        $db_con = new mysqli($mysql_host, $mysql_user, $mysql_password, $database);
        /* check connection */
        if ($db_con->connect_errno) {
            printf("<p><span class='red'>&nbsp;MySQL Connect failed: %s\n&nbsp;<br /></span></p>", $db_con->connect_error);

        }

        /* define character set to utf8 */
        if (!$db_con->set_charset("utf8")) {
            printf("Error loading character set utf8: %s\n", $db_con->error);

            /* Print current character set */
            $charset = $db_con->character_set_name();
            printf ("<br />Current character set is: %s\n", $charset);

            $db_con->close();
            exit;
        }

        return ($db_con);
    }
