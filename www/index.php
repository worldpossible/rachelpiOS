<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>RACHEL - HOME</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<link rel="stylesheet" type="text/css" href="common.css">
<link rel="stylesheet" type="text/css" href="style.css">
<!--[if IE]><script type="text/javascript" src="css3-multi-column.min.js"></script><![endif]-->

    <!-- live search suggestions -->
    <script type="text/javascript" src="rsphider/include/jQuery/jquery-1.9.1.js"></script>
    <script type="text/javascript" src="rsphider/include/jQuery/jquery-ui-1.10.2.custom.js"></script>
    <link rel="stylesheet" href="rsphider/templates/Sphider-plus/jquery-ui-1.10.2.custom.css" type="text/css">
    <script type="text/javascript">
    $(function() {
        $( "#main-search" ).autocomplete({
            source: "rsphider/include/suggest.php?media_only=0&type=and&catid=0&category=0&db=0&prefix=0",
            minLength: 1        });
    });
    $(function() {
        $( "#wiki-search" ).autocomplete({
            source: "rsphider/include/suggest.php?media_only=0&type=and&catid=3&category=3&db=0&prefix=0",
            minLength: 1        });
    });
    $(function() {
        $( "#hesp-search" ).autocomplete({
            source: "rsphider/include/suggest.php?media_only=0&type=and&catid=1&category=1&db=0&prefix=0",
            minLength: 1        });
    });
    $(function() {
        $( "#medl-search" ).autocomplete({
            source: "rsphider/include/suggest.php?media_only=0&type=and&catid=2&category=2&db=0&prefix=0",
            minLength: 1        });
    });
    $(function() {
        $( "#infonet-search" ).autocomplete({
            source: "rsphider/include/suggest.php?media_only=0&type=and&catid=2&category=5&db=0&prefix=0",
            minLength: 1        });
    });
    $(function() {
        $( "#practical-search" ).autocomplete({
            source: "rsphider/include/suggest.php?media_only=0&type=and&catid=2&category=6&db=0&prefix=0",
            minLength: 1        });
    });
    $(function() {
        $( "#oya-search" ).autocomplete({
            source: "modules/oya/search/suggest.php",
         });
    });
    $(function() {
        $( "#law_library-search" ).autocomplete({
            source: "modules/law_library/search/suggest.php",
         });
    });
    </script>
    <!--/live search suggestions -->

</head>

<body onload="$('#main-search').focus();">
<div id="rachel">
Rachel
<div id="ip">http://<?php echo gethostbyname(gethostname()); ?>/</div>
</div>

<div class="haut cf">
    <ul>
    <li><a href="index.php">HOME</a></li>
    <li><a href="about.html">ABOUT</a></li>
    <li><a href="local-frameset.html">LOCAL CONTENT</a></li>
    </ul>
    <form action="rsphider/search.php">
      <div>
      <input id="main-search" name="query_t" value="" size="50" autocomplete="off">
      <input type="submit" value="Search RACHEL">
      <input type="hidden" name="search" value="1">
      </div>
    </form>
</div>

<div id="content">

<?php

    $basedir = "modules";

    if (is_dir($basedir)) {

        $fsmods = array();
        $handle = opendir($basedir);
        while ($moddir = readdir($handle)) {
            if (preg_match("/^\./", $moddir)) continue; // skip hidden
            if (is_dir("$basedir/$moddir")) { // look in dirs
                if (file_exists("$basedir/$moddir/index.htmlf")) { // check for index fragment
                    $content = file_get_contents("$basedir/$moddir/index.htmlf");
                    $fsmods{ $moddir } = array(
                        'dir'      => "$basedir/$moddir", // this is so the include knows its directory
                        'position' => 0,
                        'hidden'   => 0,
                    );
                }
            }
        }
        closedir($handle);

        # next we go to the database to find the order and visibility state
        try {
            $db = new SQLite3("admin.sqlite");
        } catch (Exception $ex) {
            echo "<h2>" . $ex->getMessage() . "</h2>" .
                 "<h3>You may need to change permissions on the RACHEL " .
                 "root directory using: chmod 777</h3>";
        }

        if (!isset($ex)) {

            $rv = $db->query("SELECT * FROM modules");
            $dbmods = array();
            while ($row = $rv->fetchArray()) {
                $dbmods[$row['moddir']] = $row;
                if (isset($fsmods[$row['moddir']])) {
                    $fsmods[$row['moddir']]['position'] = $row['position'];
                    $fsmods[$row['moddir']]['hidden'] = $row['hidden'];
                }
            }

            # custom sorting function - sort by position, then moddir
            # handles unset positions
            function bypos($a, $b) {
                if (!isset($a['position'])) { $a['position'] = 0; }
                if (!isset($b['position'])) { $b['position'] = 0; }
                if ($a['position'] == $b['position']) {
                    return strcmp(strtolower($a['moddir']), strtolower($b['moddir']));
                } else {
                    return $a['position'] - $b['position'];
                }
            }
            uasort($fsmods, 'bypos');

            foreach (array_values($fsmods) as $mod) {
                if ($mod['hidden']) { continue; }
                $dir  = $mod['dir'];
                include "$mod[dir]/index.htmlf";
            }

        }

    } else {

        echo "No module directory found.\n";

    }

?>

</div>

<div class="haut cf" style="margin-bottom: 80px;">
    <ul>
    <li><a href="index.php">HOME</a></li>
    <li><a href="about.html">ABOUT</a></li>
    <li><a href="local-frameset.html">LOCAL CONTENT</a></li>
    </ul>
    <div id="footer_right">RACHEL - NOV 2013 version</div>
</div>

</body>
</html>
