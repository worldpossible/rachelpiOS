<?php
    //error_reporting(E_ERROR | E_PARSE);
    $admin = 'admin';
    $admin_pw = 'admin';

    define("_SECURE",1) ;    // define secure constant
    session_start();

    if (isset($_POST['user']) && isset($_POST['pass'])) {
        $username = $admin;
        $password = $admin_pw;

        if (($username == $admin) && ($password ==$admin_pw)) {
            $_SESSION['admin'] = $username;
            $_SESSION['admin_pw'] = $password;
        }
        header("Location: admin.php");
    } elseif ((isset($_SESSION['admin']) && isset($_SESSION['admin_pw']) &&$_SESSION['admin'] == $admin && $_SESSION['admin_pw'] == $admin_pw ) || (getenv("REMOTE_ADDR")=="")) {

    }
    $settings_dir = "../settings";
    include "$settings_dir/database.php";
?>