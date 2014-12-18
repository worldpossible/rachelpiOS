<?php

    $admin = 'admin';
    $admin_pw = 'admin';

    $include_dir    = "../include";
    $settings_dir   = "../settings";

    define("_SECURE",1);    // define secure constant

    $template_dir   = "../".$templ_dir."";
    $template_path  = "$template_dir/$template";

    $result = '';
    // if Intrusion Detection System should be used
    if ($use_ids == 1){
        require_once ("$include_dir/ids_handler.php");


        //IDS detected an attack?
        if (strlen($result) > 13) {
            //  get impact of intrusion
            $len = strpos($result, "<")-13;
            $res = trim(substr($result, '13', $len));
            if ($res >= $ids_warn) {
                echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
    <html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
        <head>
            <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
            <meta http-equiv='expires' content='0'>
            <meta http-equiv='pragma' content='no-cache'>
            <title>Sphider-plus administrator tools</title>
            <link rel='stylesheet' type='text/css' href='$template_path/adminstyle.css' />
            <script type='text/javascript' src='confirm.js'></script>
            <script type='text/javascript'>
                function JumpBottom () {
                    window.scrollTo(0,1000);
                }
            </script>
        </head>
        <body>
            <br /><br />
            <div class='headline cntr'>
                IDS result message
            </div>
            <br /><br />
            $result
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
    }

    if (!session_id()) {
        session_start();
    }

    if (isset($_POST['user']) && isset($_POST['pass'])) {
        $username = substr(trim($_POST['user']),0,255);
        //      prevent SQL-injection
        $username = str_replace('\\','\\\\', $username);
        $username = str_replace('"','\"', $username);

        //	prevent XSS-attack, Shell-execute and JavaScript execution
        if (preg_match("/cmd|CREATE|DELETE|DROP|eval|EXEC|File|INSERT|printf/i",$username)) {
            $username = '';
        }
        if (preg_match("/LOCK|PROCESSLIST|SELECT|shell|SHOW|SHUTDOWN/i",$username)) {
            $username = '';
        }
        if (preg_match("/SQL|SYSTEM|TRUNCATE|UNION|UPDATE|DUMP/i",$username)) {
            $username = '';
        }
        if (preg_match("/java|vbscri|embed|onclick|onmouseover|onfocus/i",$username)) {
            $username = '';
        }

        $password = substr(trim($_POST['pass']),0,255);
        //      prevent SQL-injection
        //$password = str_replace('\\','\\\\', $password);
        //$password = str_replace('"','\"', $password);
        //	prevent XSS-attack, Shell-execute and JavaScript execution
        if (preg_match("/cmd|CREATE|DELETE|DROP|eval|EXEC|File|INSERT|printf/i",$password)) {
            $password = '';
        }
        if (preg_match("/LOCK|PROCESSLIST|SELECT|shell|SHOW|SHUTDOWN/i",$password)) {
            $password = '';
        }
        if (preg_match("/SQL|SYSTEM|TRUNCATE|UNION|UPDATE|DUMP/i",$password)) {
            $password = '';
        }
        if (preg_match("/java|vbscri|embed|onclick|onmouseover|onfocus/i",$password)) {
            $password = '';
        }

        if (($username == $admin) && ($password ==$admin_pw)) {
            $_SESSION['admin'] = $username;
            $_SESSION['admin_pw'] = $password;
        }
        header("Location: admin.php");
    } elseif (
    (isset($_SESSION['admin']) && isset($_SESSION['admin_pw']) &&$_SESSION['admin'] == $admin && $_SESSION['admin_pw'] == $admin_pw ) || ($_SERVER['REMOTE_ADDR']=="")
    ) {
    } else {
        echo "     <noscript>
                <div id='main'>
                    <h1 class='cntr warn'>
                        <br />
                        Attention: Your browser does not support JavaScript.
                        <br /><br />
                        You will not get full functionality of Sphider-plus Administrator.
                        <br /><br />
                    </h1>
                </div>
            </noscript>
            <h1 class='cntr'>Sphider-plus v.$plus_nr</h1>
            <br />
            <br />
            <div class='panel x3'>
                <form class='txt' action='auth.php' method='post'>
                    <fieldset><legend>[ Sphider Admin Login ]</legend>
                        <label for='user'>[ Name ]</label>
                        <input type='text' name='user' id='user' size='15' maxlength='255' title='Required - Enter your user name here' onfocus='this.value=\"\"' value=''/>
                        <label for='pass'>[ Password ]</label>
                        <input type='password' name='pass' id='pass' size='15' maxlength='255' title='Required - Enter your password here' onfocus='this.value=\"\"' value=''/>
                    </fieldset>
                    <fieldset><legend>[ Log In ]</legend>
                        <input class='sbmt' type='submit' id='submit' value='&nbsp;Login &raquo;&raquo; ' title='Click to confirm'/>
                    </fieldset>
                </form>
            </div>
            <br />
            <br />
        </body>
    </html>
            ";
        exit();
    }

?>
