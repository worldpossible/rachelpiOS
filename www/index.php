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
    <li><a href="local/">LOCAL CONTENT</a></li>
    <li><a href="uploads/">UPLOAD CONTENT</a></li>
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

    $moddir = "modules";

    if (is_dir($moddir)) {

        $handle = opendir($moddir);
        $count = 0;
        $modules = array();
        while ($file = readdir($handle)) {
            if (preg_match("/^\./", $file)) continue; // skip hidden
            if (is_dir("$moddir/$file")) { // look in dirs
                $dir = "$moddir/$file";
                if (file_exists("$moddir/$file/index.htmlf")) { // check for index fragment
                    $count++;
                    $frag = "index.htmlf";
                    $content = file_get_contents("$dir/$frag");
                    preg_match("/<!-- *position *\: *(\d+) *-->/", $content, $match);
                    array_push($modules, array(
                        'file' => $file,
                        'dir'  => $dir, // this is used by the include to know it's directory
                        'frag' => "$dir/$frag", // this is what is actually included
                        'position' => $match[1]
                    ));
                } else {
                    # there was no index fragment, so...
                    array_push($modules, array(
                        'file' => $file, // this is the name of the module
                        'dir'  => $dir, // this is the module's directory
                        'frag' => "nofrag.php", // we include a special fragment
                        'position' => 9999
                    ));
                }
            }
        }
        closedir($handle);

        function bypos($a, $b) {
            return $a['position'] - $b['position'];
        }

        if ($count == 0) {
            echo "No modules found.\n";
        } else {
            usort($modules, 'bypos');
            foreach ($modules as $mod) {
                $file = $mod['file']; // only matters for modules without a fragment
                $dir  = $mod['dir'];
                include $mod['frag'];
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
    <li><a href="local/">LOCAL CONTENT</a></li>
    <li><a href="uploads/">UPLOAD CONTENT</a></li>
    <li><a href+"mailto:info@worldpossible.org">email: info@worldpossible.org</a></li>
    </ul>
    <div id="footer_right">RACHEL - NOV 2014 version</div>
</div>

</body>
</html>
