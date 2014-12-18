<?php

    $db_admin = 'admin';
    $db_admin_pw = 'admin';

    session_start();

    if (isset($_POST['db_user']) && isset($_POST['db_pass'])) {

        $username = substr(trim($_POST['db_user']),0,255);
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

        $password = substr(trim($_POST['db_pass']),0,255);
        //      prevent SQL-injection
        $password = str_replace('\\','\\\\', $password);
        $password = str_replace('"','\"', $password);

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

        if (($username == $db_admin) && ($password ==$db_admin_pw)) {
            $_SESSION['db_admin'] = $username;
            $_SESSION['db_admin_pw'] = $password;
        }
        header("Location: admin.php?f=database&sel=1");
    } elseif (
    (isset($_SESSION['db_admin']) && isset($_SESSION['db_admin_pw']) &&$_SESSION['db_admin'] == $db_admin && $_SESSION['db_admin_pw'] == $db_admin_pw ) || ($_SERVER['REMOTE_ADDR']=="")
    ) {
    } else {
        echo "    <div class='submenu cntr'>| Database Management|</div>
            <br />
            <div class='panel x3'>
                <form class='txt' action='auth_db.php' method='post'>
                    <fieldset>
                        <legend>[ Database Login ]</legend>
                        <label for='user'>[ Name ]</label>
                        <input type='text' name='db_user' id='db_user' size='15' maxlength='15'
                        title='Required - Enter your database user name here' onfocus='this.value=\"\"' value=''/>
                        <label for='pass'>[ Password for database access ]</label>
                        <input type='password' name='db_pass' id='db_pass' size='15' maxlength='15'
                        title='Required - Enter your database password here' onfocus='this.value=\"\"' value=''/>
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
