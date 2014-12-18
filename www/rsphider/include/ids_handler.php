<?php

    // set the include path properly for IDS
    set_include_path(
    get_include_path()
    . PATH_SEPARATOR
    . "$include_dir/"
    );

    if (!session_id()) {
        session_start();
    }

    require_once 'IDS/Init.php';
    $result = '';
    try {

        //  define what to scan
        $request = array(
                'REQUEST' => $_REQUEST,
                'GET' => $_GET,
                'POST' => $_POST,
                'COOKIE' => $_COOKIE
        );

        // Initialise the IDS and fetch the results
        $init = IDS_Init::init(dirname(__FILE__) . "/IDS/Config/Config.ini.php");

        $init->config['General']['base_path'] = dirname(__FILE__) . "/IDS/";
        $init->config['General']['use_base_path'] = true;
        $init->config['Caching']['caching'] = true;

        $ids = new IDS_Monitor($request, $init);
        $result = $ids->run();

        if (!$result->isEmpty()) {
            //  prepare the log file
            require_once 'IDS/Log/File.php';
            //require_once 'IDS/Log/Email.php';
            require_once 'IDS/Log/Composite.php';

            $compositeLog = new IDS_Log_Composite();
            $compositeLog->addLogger(IDS_Log_File::getInstance($init));
            //$compositeLog->addLogger(IDS_Log_File::getInstance($init),IDS_Log_Email::getInstance($init));
            $compositeLog->execute($result);
        }

    } catch (Exception $e) {
        //  if the IDS init went wrong
        printf(
                'An internal error occured in the \'Intrusion Detection System\': %s',
        $e->getMessage()
        );
        die ();
    }

?>