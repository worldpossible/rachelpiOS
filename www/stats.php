<!DOCTYPE html">
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>RACHEL - STATS</title>
    <style>
        body { background-color: #ffc; font-family: sans-serif; }
        table { border-collapse: collapse; }
        td { border: 1px solid #cc9; padding: 5px; }
    </style>
</head>
<body>

<?php


    # configuration
    $rpi_logpath   = "/var/log/apache2/access.log.1";
    $uwamp_logpath = "../bin/apache/logs/access.log";

    if (file_exists($rpi_logpath)) {
        $alog = $rpi_logpath;
    } else if (file_exists($uwamp_logpath)) {
        $alog = $uwamp_logpath;
    } else {
        print "<h1>No log found</h1>\n</body>\n</html>\n";
        exit();
    }

    # more configuration
    $rachelroot = "./";
    $maxlines = 10000;

    # test configuration
    #$alog = "./access.log";
    #$rachelroot = "../RACHEL-UwAmp-2014-11-13/bin/www";

    # start timer
    $starttime = microtime(true);

    # read in the log file
    $content = file_get_contents($alog);

    # read query string (and display)
    if ($_GET && $_GET['module']) {
        $module = $_GET['module'];
        print "<h1><a href=\"stats.php\">RACHEL Stats</a></h1>\n";
        $dispmod = preg_replace("/\/modules\//", "", $module);
    } else {
        $module = "/modules";
        print "<h1>RACHEL Stats</h1>\n";
        $dispmod = "";
    }
    $modmatch = preg_quote($module, "/");
    
    # smart count incrementer
    function inc(&$array, $key) {
        if (isset($array[$key])) {
            ++$array[$key];
        } else {
            $array[$key] = 1;
        }
    }

    $nestcount = 0;
    while (1) {

        ++$nestcount;

        $count = 0;
        $errors = array();
        $stats = array();
        $start = "";
        foreach(preg_split("/((\r?\n)|(\r\n?))/", $content) as $line){

            # line count and limiting
            if ($maxlines && $count >= $maxlines) { break; }
            ++$count;

            # dates - [29/Mar/2015:06:25:15 -0700]
            preg_match("/\[(.+?) .+?\]/", $line, $date);
            if ($date) {
                if (!$start) {
                    $start = $date[1];
                }
                $end = $date[1];
            }

            # count errors
            preg_match("/\"GET.+?\" (\d\d\d) /", $line, $matches);
            if ($matches && $matches[1] >= 400) {
                inc($errors, $matches[1]);
            }

            # html pages only
            preg_match("/GET (.+?\.html?) /", $line, $matches);
            if ($matches) {
                $url = $matches[1];
                preg_match("/$modmatch\/([^\/]+)/", $url, $sub);
                if ($sub) {
                    inc($stats, $sub[1]);
                }
            }

        }
        
        # auto-descend into directories if there's only one item
        if (sizeof($stats) == 1) {
            # but not if the one thing is an html file
            if (preg_match("/\.html?/", array_keys($stats)[0])) {
                break; 
            }
            # and not if it's too deep
            if ($nestcount > 5) {
                print "<h1>ERROR descending nested directories</h1>\n";
                break;
            }
            $module .= "/" . array_keys($stats)[0];
            $modmatch = preg_quote($module, "/");
            $dispmod = preg_replace("/\/modules\//", "", $module);
        } else {
            break;
        }

    }

    # tell the user the path they're in
    if ($dispmod) {
        print "<h2>$dispmod</h2>\n";
    }

    # date & time formatting
    $start = preg_replace("/\:/", " ", $start, 1);
    $end   = preg_replace("/\:/", " ", $end, 1);
    $start = preg_replace("/\:\d\d$/", "", $start, 1);
    $end   = preg_replace("/\:\d\d$/", "", $end, 1);
    $start = preg_replace("/\//", " ", $start);
    $end   = preg_replace("/\//", " ", $end);
    print "<h3>$start - $end</h3>\n";

    # stats display
    arsort($stats);
    print "<table>\n";
    print "<tr><th>Hits</th><th>Content</th></tr>\n";
    foreach ($stats as $mod => $hits) {
        # html pages are links to the content
        if (preg_match("/\.html?$/", $mod)) {
            $url = "$rachelroot/$module/$mod";
            print( "<tr><td>$hits</td><td>$mod <small>(<a href=\"$url\">view</a>)</small></td></tr>\n" );
        # directories link to a drill-down
        } else {
            $url = "stats.php?module=" . urlencode("$module/$mod");
            print( "<tr><td>$hits</td><td><a href=\"$url\">$mod</a></td></tr>\n" );
        }
    }
    print "</table>\n";

    # timer readout
    $time = microtime(true) - $starttime;
    printf("<p><b>$count lines analyzed in %.2f seconds.</b></p>\n", $time);
    #print_r($errors);

?>

</body>
</html>

