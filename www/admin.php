<?php

# perform a cheap version of basic auth
if ($_SERVER['PHP_AUTH_USER'] != "root"
    || $_SERVER['PHP_AUTH_PW'] != "rachel"
) {
    header('WWW-Authenticate: Basic realm="My Realm"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'You need permission to view this page.';
    exit;
}

# if we've got a list of moddirs, we update the DB to
# reflect that ordering
if (isset($_GET['moddirs'])) {
    $position = 1;
    try {
        $db = new SQLite3("admin.sqlite");
        # figure out which modules to hide
        $hidden= array();
        foreach (explode(",", $_GET['hidden']) as $moddir) {
            $hidden[$moddir] = 1;
        }
        # go to the DB and set the new order and new hidden state
        foreach (explode(",", $_GET['moddirs']) as $moddir) {
            $moddir = $db->escapeString($moddir);
            if (isset($hidden[$moddir])) { $is_hidden = 1; } else { $is_hidden = 0; }
            $rv = $db->query(
                "UPDATE modules SET position = '$position', hidden = '$is_hidden'" .
                " WHERE moddir = '$moddir'"
            );
            if (!$rv) { throw new Exception('Query Failed'); }
            ++$position;
        }
    } catch (Exception $ex) {
        header("HTTP/1.1 500 Internal Server Error");    
        exit;
    }
    header("HTTP/1.1 200 OK");    
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>RACHEL Admin</title>
<link rel="stylesheet" href="css/normalize-1.1.3.css">
<link rel="stylesheet" href="css/ui-lightness/jquery-ui-1.10.4.custom.min.css">
<style>
    body { margin: 10px; }
    button { margin: 3px; padding: .25em 1em; }
    .ui-icon { background-image: url(css/ui-lightness/images/ui-icons_ef8c08_256x240.png); }
    #sortable { list-style-type: none; margin: 0; padding: 0; }
    #sortable li {
        margin: 0 3px 3px 3px;
        padding: .25em;
        padding-left: 1.5em;
        height: 1em; width: 40em;
        overflow: hidden;
        position: relative;
    }
    #sortable li span { position: absolute; margin-left: -1.3em; }
    #sortable .checkbox { position: absolute; right: 10px; top: 5px; font-size: small; color: gray; }
</style>
<script src="js/jquery-1.10.2.min.js"></script>
<script src="js/jquery-ui-1.10.4.custom.min.js"></script>
<script>
    $(function() {
        $( "#sortable" ).sortable({
            change: function(event, ui) {
                $("button").css("color", "");
                $("button").html("Save Changes");
                $("button").prop("disabled", false);
            }
        });
        $( "#sortable" ).disableSelection();
        $(":checkbox").change( function() {
                $("button").css("color", "");
                $("button").html("Save Changes");
                $("button").prop("disabled", false);
        });
    });
    function saveState() {
        $("button").html("Saving...");
        $("button").prop("disabled", true);
        var ordered = $("#sortable").sortable("toArray");
        var hidden = [];
        for (var i = 0; i < ordered.length; ++i) {
            if ($("#"+ordered[i]+"-hidden").prop("checked")) {
                hidden.push(ordered[i]);
            }
        }
        //alert("admin.php?moddirs=" + ordered.join(",") + "&hidden=" + hidden.join(","));
        $.ajax({
            url: "admin.php?moddirs=" + ordered.join(",")
                + "&hidden=" + hidden.join(","),
            success: function() {
                $("button").css("color", "green");
                $("button").html("&#10004; Saved");
            },
            error: function() {
                $("button").css("color", "red");
                $("button").html("X Not Saved - Internal Error");
            }
        });
    }
</script>
</head>
<body>

<div style="float: right;"><a href="<?php echo "http://x:x@$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" ?>">logout</a></div>
<h1>RACHEL Admin</h1>

<?php

$basedir = "modules";

# if there's no modules directory, we can't do anything
if (is_dir($basedir)) {

    # first we get a list of all the modules from the filesystem
    $fsmods = array();
    $nohtmlf = array();
    $handle = opendir($basedir);
    #echo "<p>Found in FS:</p><ul>";
    while ($moddir = readdir($handle)) {
        if (preg_match("/^\./", $moddir)) continue; // skip hidden
        if (is_dir("$basedir/$moddir")) { // look in dirs
            if (file_exists("$basedir/$moddir/index.htmlf")) { // check for index fragment
                $content = file_get_contents("$basedir/$moddir/index.htmlf");
                preg_match("/<h2>(.+)<\/h2>/", $content, $match);
                $title = preg_replace("/<?php.+?>/", "", $match[1]);
                $title = preg_replace("/<.+?>/", "", $title);
                if (!$title) { $title = $moddir; }
            } else {
                array_push($nohtmlf, $moddir);
                continue;
            }
            $fsmods{ $moddir } = array(
                'moddir'   => $moddir,
                'title'    => $title,
                'position' => 0,
                'hidden'   => 0,
            );
            #echo "<li> $moddir</li>";
        }
    }
    closedir($handle);
    #echo "</ul>";

    # next we get a list of all modules in the database,
    # initializing things as needed
    try {
        $db = new SQLite3("admin.sqlite");
    } catch (Exception $ex) {
        echo "<h2>" . $ex->getMessage() . "</h2>" .
             "<h3>You may need to change permissions on the RACHEL " .
             "root directory using: chmod 777</h3>";
    }

    # opening the DB worked
    if (!isset($ex)) {

        # in case this is the first time
        $db->query("
            CREATE TABLE IF NOT EXISTS modules (
                module_id INTEGER PRIMARY KEY,
                moddir    VARCHAR(255),
                title     VARCHAR(255),
                position  INTEGER,
                hidden    INTEGER
            )
        ");

        # get that db module list, and populate fsmods with
        # the position info from the database
        $rv = $db->query("SELECT * FROM modules");
        $dbmods = array();
        #echo "<p>Found in DB:</p><ul>";
        while ($row = $rv->fetchArray()) {
            $dbmods[$row['moddir']] = $row;
            if (isset($fsmods[$row['moddir']])) {
                $fsmods[$row['moddir']]['position'] = $row['position'];
                $fsmods[$row['moddir']]['hidden'] = $row['hidden'];
            }
            #echo "<li> $row[moddir] - $row[position]</li>";
        }
        #echo "</ul>";

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

        # display the sortable list
        $disabled = " disabled";
        echo "<p>Found in /modules/:</p><ul id=\"sortable\">";
        foreach (array_keys($fsmods) as $moddir) {
            echo "<li id=\"$moddir\" class=\"ui-state-default\">";
            if ($fsmods[$moddir]['hidden']) {
                $checked = " checked";
            } else {
                $checked = "";
            }
            echo "<span class=\"checkbox\"><input type=\"checkbox\" id=\"$moddir-hidden\"$checked> ";
            echo "<label for=\"$moddir-hidden\">hide</label></span>";
            echo "<span class=\"ui-icon ui-icon-arrowthick-2-n-s\"></span>";
            echo "$moddir - " . $fsmods[$moddir]['title'];
            if ($fsmods[$moddir]['position'] < 1) {
                echo " <small style=\"color: green;\">(new)</small>";
                $disabled = "";
            }
            echo "</li>";
        }
        echo "</ul>";
        
        echo "<button onclick=\"saveState();\"$disabled>Save Changes</button>";

        echo "<h3>The following modules were ignored because they had no index.htmlf</h3><ul>";
        foreach ($nohtmlf as $moddir) {
            echo "<li> $moddir </li>";
        }
        echo "</ul>";

        # insert anything we found in the fs that wasn't in the db
        #echo "<p>Added to DB:</p><ul>";
        foreach (array_keys($fsmods) as $moddir) {
            if (!isset($dbmods[$moddir])) {
                $db_moddir =   $db->escapeString($moddir);
                $db_title  =   $db->escapeString($fsmods[$moddir]['title']);
                $db_position = $db->escapeString($fsmods[$moddir]['position']);
                $db->query(
                    "INSERT into modules (moddir, title, position, hidden) " .
                    "VALUES ('$db_moddir', '$db_title', '$db_position', '0')"
                );
                #echo "<li> $moddir</li>";
            }
        }
        #echo "</ul>";
        # delete anything from the db that wasn't in the fs
        #echo "<p>Removed from DB:</p><ul>";
        foreach (array_keys($dbmods) as $moddir) {
            if (!isset($fsmods[$moddir])) {
                $db_moddir =   $db->escapeString($moddir);
                $db->query("DELETE FROM modules WHERE moddir = '$db_moddir'");
                #echo "<li> $moddir</li>";
            }
        }
        #echo "</ul>";


    }

} else {

    echo "<h2>No module directory found.</h2>\n";

}

?>

</body>
</html>
