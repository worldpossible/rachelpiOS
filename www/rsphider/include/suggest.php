<?php
/*******************************************************
 If 'Suggestion' is enabled ($min_sug_chars > 0),
 this script takes over to fetch the suggestion from Sphider-plus databases.
 If 'Media only' is selected in Search form, only Media keywords will be suggested.
 Query input will be delivered via the JavaScript file 'jQuery' .
 Suggestions will  be placed beyond the search input field 'query' by AJAX functionality,
 performed again via the JavaScript file 'jQuerry' .

 ***********************************************************/

    error_reporting(0);  	// suppress  PHP messages
    define("_SECURE",1);    // define secure constant

    $values         = array();
    $sug_array      = array();
    $settings_dir   = "../settings";
    $language_dir   = "../languages";

    require_once("$settings_dir/database.php");

    // no term passed - just exit early with no response
    if (empty($_GET['term'])) exit ;

    $keyword    = trim(substr($_GET['term'], 0, 255));
    $media_only = trim(substr(@$_GET['media_only'], 0, 1));
    $type       = trim(substr(@$_GET['type'], 0,6));
    $catid      = trim(substr(@$_GET['catid'], 0, 20));
    $category   = trim(substr(@$_GET['category'], 0, 20));
    $db         = trim(substr(@$_GET['db'], 0, 1));
    $prefix     = trim(substr(@$_GET['prefix'], 0, 20));
/*
$keyword = "o";      // findet man auf http://localhost/publizieren/achern.occhi/html/programm.html
//$keyword = "Nadel ist El";  //  findet man auf http://localhost/publizieren/nadel.occhi/html/rohlfs.html
$media_only = "0";
$category = "0";        //  -1      fetch without categories => present all suggestions
$catid = "2";
$type = "and";
$db = 0;
$prefix = 0;
*/
    // remove slashes if they were magically added
    if (get_magic_quotes_gpc()) $keyword = stripslashes($keyword);

    //      replace the well known category (like in former versions of Sphider-plus
    if ($category == 0 ) {
        $category = -1;
    }

    //      if requested by Search-form, overwrite default db number
    if ($db > 0 && $db <= 5) {
        $dbu_act = $db;
    }

    //      if requested by Search-form, overwrite default table prefix
    if ($prefix != 0 ) {
        $mysql_table_prefix = $prefix;
    }

    //      get active database
    if ($dbu_act == '1') {
        $db_con     = db_connect($mysql_host1, $mysql_user1, $mysql_password1, $database1);
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


    $plus_nr = '';
    @include "".$settings_dir."/db".$dbu_act."/conf_".$mysql_table_prefix.".php";   //  get configuration for this application
    if (!$plus_nr) {
        include "/admin/settings/backup/Sphider-plus_default-configuration.php";
    }

    $home_charset = strtoupper($home_charset);

    // try to get the currently valid language
    include "$language_dir/$language-language.php";
    if ($auto_lng == 1) {   //  if enabled in Admin settings get country code
        if ( isset ( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
            $cc = substr( htmlspecialchars($_SERVER['HTTP_ACCEPT_LANGUAGE']), 0, 2);
            $handle = @fopen ("$language_dir/$cc-language.php","r");
            if ($handle) {
                $language = $cc; // if available set language to users slang
                include "$language_dir/$language-language.php";
                @fclose($handle);
            }
        }
    }

    $keyword = addslashes($keyword);

    if ($case_sensitive == 0) {
        $keyword = lower_ent($keyword);
        $keyword = lower_case($keyword);
    }


    if ($translit_el && $media_only) {
        $keyword = translit_el($keyword);
    }

    if (strlen($keyword)< $min_sug_chars) {               //  if search string too small, do not search for keywords/phrases
        $suggest_phrases = false;
        $suggest_keywords = false;
    }

    $keyword = str_replace ("%20", " ", $keyword);       //  replace 'blank'

    if (!strpos($keyword,' ')) {                         //check if search string is phrase
        //$suggest_phrases = false;
    }

    //     remove control character
    $keyword = preg_replace("/!|\"|\*/", "",$keyword);

    //  convert all single quotes into standard quote
    if ($quotes == '1') {
        $all_quotes = array
        (
                "&#8216;"   => "\'",
                "&lsquo;"   => "\'",
                "&#8217;"   => "\'",
                "&rsquo;"   => "\'",
                "&#8242;"   => "\'",
                "&prime;"   => "\'",
                "‘"         => "\'",
                "‘"         => "\'",
                "´"         => "\'",
                "`"         => "\'"
                );

                reset($all_quotes);
                while ($char = each($all_quotes)) {
                    $keyword = preg_replace("/".$char[0]."/i", $char[1], $keyword);
                }
    }

    //  convert all double quotes into standard quotations
    if ($dup_quotes == '1') {
        $all_quotes = array
        (
                "“"   => "\"",
                "”"   => "\"",
                "„"   => "\""
                );

                reset($all_quotes);
                while ($char = each($all_quotes)) {
                    $file = preg_replace("/".$char[0]."/i", $char[1], $file);
                }
    }

    if(strlen($keyword) >= $min_sug_chars) {

//  *****   enter here for media suggestions
        if ($media_only =='1') {

            $q1 = $keyword;
            if ($keyword == '')         $q1 = '&nbsp;'; //    prevent blank results for media search
            if ($keyword == 'media:')   $q1 = '';       //    search for all media files in database /category
            //  get results from all involved databases
            $media_results = array();
            if ($db1_slv == 1) {
                $db_con     = db_connect($mysql_host1, $mysql_user1, $mysql_password1, $database1);
                $mysql_table_prefix = $mysql_table_prefix1;

                if ($category != '-1') {
                    //      find all valid link_id's for a given category
                    $category_list = getcat_links ($category, $mysql_table_prefix);
                }
                $media_results = get_mediasuggests($mysql_table_prefix, $q1, $category, $catid);
            }

            if ($db2_slv == 1) {
                $db_con = db_connect($mysql_host2, $mysql_user2, $mysql_password2, $database2);
                $mysql_table_prefix = $mysql_table_prefix2;

                if ($category != '-1') {
                    $category_list = getcat_links ($category, $mysql_table_prefix);
                }
                $media_results2 = get_mediasuggests($mysql_table_prefix, $q1, $category, $catid);
                $media_results = array_merge($media_results, $media_results2);
            }

            if ($db3_slv == 1) {
                $db_con = db_connect($mysql_host3, $mysql_user3, $mysql_password3, $database3);
                $mysql_table_prefix = $mysql_table_prefix3;

                if ($category != '-1') {
                    $category_list = getcat_links ($category, $mysql_table_prefix);
                }
                $media_results3 = get_mediasuggests($mysql_table_prefix, $q1, $category, $catid);
                $media_results = array_merge($media_results, $media_results3);
            }

            if ($db4_slv == 1) {
                $db_con = db_connect($mysql_host4, $mysql_user4, $mysql_password4, $database4);
                $mysql_table_prefix = $mysql_table_prefix4;

                if ($category != '-1') {
                    $category_list = getcat_links ($category, $mysql_table_prefix);
                }
                $media_results4 = get_mediasuggests($mysql_table_prefix, $q1, $category, $catid);
                $values = array_merge($media_results, $media_results4);
            }

            if ($db5_slv == 1) {
                $db_con = db_connect($mysql_host5, $mysql_user5, $mysql_password5, $database5);
                $mysql_table_prefix = $mysql_table_prefix5;

                if ($category != '-1') {
                    $category_list = getcat_links ($category, $mysql_table_prefix);
                }
                $media_results5 = get_mediasuggests($mysql_table_prefix, $q1, $category, $catid);
                $media_results = array_merge($media_results, $media_results5);
            }

            if ($media_results) {
                foreach ($media_results as $_key => $_val) {
                    if (strlen($_val[5]) > 5 && !stristr($_SERVER['HTTP_USER_AGENT'],'MSIE')) {
                        $_val[5]    = substr($_val[5], stripos($_val[5], $keyword)  , 80);
                        if (strstr($_val[5], " ")) {
                            $_val[5]    = substr($_val[5], 0, strrpos($_val[5], " "));  //  limit suggestion to final 'blank'
                        }
                        if(!in_array($_val[5], $sug_array)) {   //  if this suggestion is not yet part of the suggest array
                            $sug_array[]   = $_val[5];
                            $media_string .= $_val[5].", ";     //  only for debugging
                        }
                    }
                }

                echo json_encode($sug_array);   //  let jQuery take over with these suggestions
            }

        } else {

//   ********       enter here for text  suggestions
            $values         = array();
            $category_list  = array();
            //  get results from all involved databases
            if ($db1_slv == 1) {
                $db_con     = db_connect($mysql_host1, $mysql_user1, $mysql_password1, $database1);
                if ($prefix > '0' ) {    //      if requested by Search-form, overwrite default table prefix
                    $mysql_table_prefix = $prefix;
                } else {
                    $mysql_table_prefix = $mysql_table_prefix1;
                }
                if ($category != '-1') {
                    //      find all valid link_id's for a given category
                    $category_list = getcat_links ($category, $mysql_table_prefix);
                }
                $values = get_textsuggests($keyword, $type, $category, $category_list, $mysql_table_prefix);
            }

            if ($db2_slv == 1) {
                $db_con = db_connect($mysql_host2, $mysql_user2, $mysql_password2, $database2);
                if ($prefix > '0' ) {
                    $mysql_table_prefix = $prefix;
                } else {
                    $mysql_table_prefix = $mysql_table_prefix2;
                }

                if ($category != '-1') {
                    $category_list = getcat_links ($category, $mysql_table_prefix);
                }
                $values2 = get_textsuggests($keyword, $type, $category, $category_list, $mysql_table_prefix);
                $values = array_merge($values, $values2);
            }

            if ($db3_slv == 1) {
                $db_con = db_connect($mysql_host3, $mysql_user3, $mysql_password3, $database3);
                if ($prefix > '0' ) {
                    $mysql_table_prefix = $prefix;
                } else {
                    $mysql_table_prefix = $mysql_table_prefix3;
                }

                if ($category != '-1') {
                    $category_list = getcat_links ($category, $mysql_table_prefix);
                }
                $values3 = get_textsuggests($keyword, $type, $category, $category_list, $mysql_table_prefix);
                $values = array_merge($values, $values3);
            }

            if ($db4_slv == 1) {
                $db_con = db_connect($mysql_host4, $mysql_user4, $mysql_password4, $database4);
                if ($prefix > '0' ) {
                    $mysql_table_prefix = $prefix;
                } else {
                    $mysql_table_prefix = $mysql_table_prefix4;
                }

                if ($category != '-1') {
                    $category_list = getcat_links ($category, $mysql_table_prefix);
                }
                $values4 = get_textsuggests($keyword, $type, $category, $category_list, $mysql_table_prefix);
                $values = array_merge($values, $values4);
            }

            if ($db5_slv == 1) {
                $db_con = db_connect($mysql_host5, $mysql_user5, $mysql_password5, $database5);
                if ($prefix > '0' ) {
                    $mysql_table_prefix = $prefix;
                } else {
                    $mysql_table_prefix = $mysql_table_prefix5;
                }

                if ($category != '-1') {
                    $category_list = getcat_links ($category, $mysql_table_prefix);
                }

                $values5 = get_textsuggests($keyword, $type, $category, $category_list, $mysql_table_prefix);
                $values = array_merge($values, $values5);
            }

            if ($min_sug_chars != '0' && is_array($values)) {   // if we do have results, loop through them and add them to the output . Max. = $suggest_rows

                arsort($values);
                $val_array  = array_slice($values, 0, $suggest_rows);
                $sug_array  = array_keys($val_array);  //  we just need the suggestions, without weighting

                echo json_encode($sug_array);   //  let jQuery take over with these suggestions

                $sug_array2 = array();
                foreach ($val_array as $key=>$value) {
                    array_push($sug_array2, array("id"=>$key, "label"=>$key, "value" => strip_tags($key)));
                    $txt_string .= $key.", ";   //  only for debugging
                }
            }
        }
    }

    if ($debug_user) {   //  if activated in Admin backend, all debug info will be available in    .../include/tmp/suggest_debug.txt

        $content    = "\r\nInput from jQuery:\r\n";
        $content .= "  keyword:    ".$keyword."\r\n";
        $content .= "  media_only: ".$media_only."\r\n";
        $content .= "  type:       ".$type."\r\n";
        $content .= "  catid:      ".$catid."\r\n";
        $content .= "  category:   ".$category."\r\n";
        $content .= "  db:         ".$db."\r\n";
        $content .= "  prefix:     ".$prefix."\r\n";

        $content .= "\r\nVariables in suggest.php script:\r\n";
        $content .= "  suggest_history:  ".$suggest_history."\r\n";
        $content .= "  suggest_phrases:  ".$suggest_phrases."\r\n";
        $content .= "  suggest_keywords: ".$suggest_keywords."\r\n";
        $content .= "  cat_id list:      ".$category_list."\r\n";

        $content .= "\r\nResults:\r\n";
        $content .= "  text  suggestions: ".$txt_string."\r\n";
        $content .= "  media suggestions: ".$media_string."\r\n";
        $debug_file   = "tmp/suggest_debug.txt";
        file_put_contents($debug_file, $content);
    }
    exit;

    // Database 1-5 connection
    function db_connect($mysql_host, $mysql_user, $mysql_password, $database) {

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

    //  convert ISO-8859-x entities into their lower case equivalents
    function lower_ent($string) {
        $ent = array
        (
                "Č" => "č",
                "Ď" => "ď",
                "Ě" => "ě",
                "Ľ" => "ľ",
                "Ň" => "ň",
                "Ř" => "ř",
                "Š" => "š",
                "Ť" => "ť",
                "Ž" => "ž",

                "Ä" => "ä",
                "Ö" => "ö",
                "Ü" => "ü",
                "&Auml;" => "ä",
                "&#196;" => "ä",
                "&Ouml;" => "ö",
                "&#214;" => "ö",
                "&Uuml;" => "ü",
                "&#220;" => "ü",

                "À" => "à",
                "È" => "è",
                "Ì" => "ì",
                "Ò" => "ò",
                "Ù" => "ù",

                "É" => "é",
                "Í" => "í",
                "Ó" => "ó",
                "Ú" => "ú",

                "Ã" => "ã",
                "Ñ" => "ñ",
                "Õ" => "õ",
                "Ũ" => "ũ",

                "Â" => "â",
                "Ê" => "ê",
                "Î" => "î",
                "Ô" => "ô",
                "Û" => "û",

                "Å" => "å",
                "Ů" => "ů",

                "Æ" => "æ",
                "Ç" => "ç",
                "Ø" => "ø",
                "Ë" => "ë",
                "Ï" => "ï",

                "Ğ" => "ğ",
                "İ" => "ı",
                "Ş" => "ş",

                "Ħ" => "ħ",
                "Ĥ" => "ĥ",
                "Ĵ" => "ĵ",
                "Ż" => "ż",
                "Ċ" => "ċ",
                "Ĉ" => "ĉ",
                "Ŭ" => "ŭ",
                "Ŝ" => "ŝ",
                "Ă" => "ă",
                "Ő" => "ő",
                "Ĺ" => "ĺ",
                "Ć" => "ć",
                "Ű" => "ű",
                "Ţ" => "ţ",
                "Ń" => "ń",
                "Đ" => "đ",
                "Ŕ" => "ŕ",
                "Á" => "á",
                "Ś" => "ś",
                "Ź" => "ź",
                "Ł" => "ł",
                "˘" => "˛",

                "ĸ" => "˛",
                "Ŗ" => "ŗ",

                "Į" => "į",
                "Ę" => "ę",
                "Ė" => "ė",
                "Ð" => "ð",
                "Ņ" => "ņ",
                "Ō" => "ō",
                "Ų" => "ų",
                "Ý" => "ý",
                "Þ" => "þ",
                "Ą" => "ą",
                "Ē" => "ē",
                "Ģ" => "ģ",
                "Ī" => "ī",
                "Ĩ" => "ĩ",
                "Ķ" => "ķ",
                "Ļ" => "ļ",
                "Ŧ" => "ŧ",
                "Ū" => "ū",
                "Ŋ" => "ŋ",

                "Ā" => "ā",

                "Ḃ" => "ḃ",
                "Ḋ" => "ḋ",
                "Ẁ" => "ẁ",
                "Ẃ" => "ẃ",
                "Ṡ" => "ṡ",
                "Ḟ" => "ḟ",
                "Ṁ" => "ṁ",
                "Ṗ" => "ṗ",
                "Ẅ" => "ẅ",
                "Ŵ" => "ŵ",
                "Ṫ" => "ṫ",
                "Ŷ" => "ŷ"

                );
                reset($ent);
                while ($char = each($ent)) {
                    $string = preg_replace("/".$char[0]."/i", $char[1], $string);
                }
                return ($string);
    }

    function lower_case($string) {
        global $charSet, $home_charset, $greek, $cyrillic;

        if ($charSet =='') {
            $charSet = $home_charset;
        }
        $charSet = strtoupper($charSet);

        //      if required, convert Greek charset into lower case
        if ($greek == '1' && ($charSet == 'UTF-8' ||  $charSet == 'ISO-8859-7' || $charSet == 'WINDOWS-1253')) {

            $lower = array
            (
                    "Α" => "α",
                    "Β" => "β",
                    "Γ" => "γ",
                    "Δ" => "δ",
                    "Ε" => "ε",
                    "Ζ" => "ζ",
                    "Η" => "η",
                    "Θ" => "θ",
                    "Ι" => "ι",
                    "Κ" => "κ",
                    "Λ" => "λ",
                    "Μ" => "μ",
                    "Ν" => "ν",
                    "Ξ" => "ξ",
                    "Ο" => "ο",
                    "Π" => "π",
                    "Ρ" => "ρ",
                    "Σ" => "σ",
                    "Τ" => "τ",
                    "Υ" => "υ",
                    "Φ" => "φ",
                    "Χ" => "χ",
                    "Ψψ" => "",
                    "Ω" => "ω"
                    );

                    reset($lower);
                    while ($char = each($lower)) {
                        $string = preg_replace("/".$char[0]."/i", $char[1], $string);
                    }
        }

        //      if required, convert Cyrillic charset into lower case
        if ($cyrillic == '1' && ($charSet == 'UTF-8' || $charSet == 'ISO-8859-5' || $charSet == 'WINDOWS-1251' || $charSet == 'CP855')) {
            $lower = array
            (
                    "А" => "а",     //      basic Cyrillian alphabet
                    "Б" => "б",
                    "В" => "в",
                    "Г" => "г",
                    "Ґ" => "ґ",
                    "Ѓ" => "ѓ",
                    "Д" => "д",
                    "Ђ" => "ђ",
                    "Е" => "е",
                    "Ё" => "ё",
                    "Є" => "є",
                    "Ж" => "ж",
                    "З" => "з",
                    "Ѕ" => "ѕ",
                    "И" => "и",
                    "І" => "і",
                    "Ї" => "ї",
                    "Й" => "й",
                    "Ј" => "ј",
                    "К" => "к",
                    "Ќ" => "ќ",
                    "Л" => "л",
                    "Љ" => "љ",
                    "М" => "м",
                    "Н" => "н",

                    "Њ" => "њ",
                    "О" => "о",
                    "П" => "п",
                    "Р" => "р",
                    "С" => "с",
                    "Т" => "т",
                    "Ћ" => "ћ",
                    "У" => "у",
                    "Ў" => "ў",
                    "Ф" => "ф",
                    "Х" => "х",
            "Ѡ" => "ѡ",          //     ex Greek 'OMEGA'
                    "Ц" => "ц",
                    "Ч" => "ч",
                    "Џ" => "џ",
                    "Ш" => "ш",
                    "Щ" => "щ",
                    "Ъ" => "ъ",
                    "Ы" => "ы",
                    "Ь" => "ь",
                    "Ы" => "ы",
                    "Э" => "э",
                    "Ю" => "ю",
                    "Я" => "я",

            "Ѐ" => "ѐ",
            "Ђ" => "ђ",
            "Ї" => "ї",
            "Ѝ" => "ѝ",

            "Ѥ" => "ѥ",         //      extended Cyrillic
            "Ѧ" => "ѧ",
                    "Ѫ" => "ѫ",
                    "Ѩ" => "ѩ",
                    "Ѭ" => "ѭ",
                    "Ѯ" => "ѯ",
                    "Ѱ" => "ѱ",
                    "Ѳ" => "ѳ",
                    "Ѵ" => "ѵ",

                    "Ë" => "ë",
                    "Đ" => "đ",
                    "Ǵ" => "ǵ",
                    "Ê" => "ê",
                    "Ẑ" => "ẑ",
                    "Ì" => "ì",
                    "Ï" => "ï",
                    "Jˇ" => "ǰ",
                    "L̂" => "l̂",
                    "N̂" => "n̂",
                    "Ć" => "ć",
                    "Ḱ" => "ḱ",
                    "Ŭ" => "ŭ",
                    "D̂" => "d̂",
                    "Ŝ" => "ŝ",
                    "Û" => "û",
                    "Â" => "â",
                    "G̀" => "g",

                    "Ě" => "ě",
                    "G̀" => "g",
                    "Ġ" => "ġ",
                    "Ğ" => "ğ",
                    "Ž̦" => "ž",
                    "Ķ" => "ķ",
                    "K̄" => "k̄",
                    "Ṇ" => "ṇ",
                    "Ṅ" => "ṅ",
                    "Ṕ" => "ṕ",
                    "Ò" => "ò",
                    "Ç" => "ç",
                    "Ţ" => "ţ",
                    "Ù" => "ù",
                    "U" => "u",
                    "Ḩ" => "ḩ",
                    "C̄" => "c̄",
                    "Ḥ" => "ḥ",
                    "C̆" => "c̆",
                    "Ç̆" => "ç̆",
                    "Z̆" => "z̆",
                    "Ç" => "ç",
                    "Ă" => "ă",
                    "Ä" => "ä",
                    "Ĕ" => "ĕ",
                    "Z̄" => "z̄",
                    "Z̈" => "z̈",
                    "Ź" => "ź",
                    "Î" => "î",
                    "Ö" => "ö",
                    "Ô" => "ô",
                    "Ü" => "ü",
                    "Ű" => "ű",
                    "C̈" => "c̈",
                    "Ÿ" => "ÿ",

            "Ҋ" => "ҋ",
            "Ҍ" => "ҍ",
            "Ҏ" => "ҏ",
            "Ґ" => "ґ",
            "Ғ" => "ғ",
            "Ҕ" => "ҕ",
            "Җ" => "җ",
            "Ҙ" => "ҙ",
            "Қ" => "қ",
            "Ҝ" => "ҝ",
            "Ҟ" => "ҟ",
            "Ҡ" => "ҡ",
            "Ң" => "ң",
            "Ҥ" => "ҥ",
            "Ҧ" => "ҧ",
            "Ҩ" => "ҩ",
            "Ҫ" => "ҫ",
            "Ҭ" => "ҭ",
            "Ү" => "ү",
            "Ұ" => "ұ",
            "Ҳ" => "ҳ",
            "Ҵ" => "ҵ",
            "Ҷ" => "ҷ",
            "Ҹ" => "ҹ",
            "Һ" => "һ",
            "Ҽ" => "ҽ",
            "Ҿ" => "ҿ",
            "Ӂ" => "ӂ",
            "Ӄ" => "ӄ",
            "Ӆ" => "ӆ",
            "Ӈ" => "ӈ",
            "Ӊ" => "ӊ",
            "Ӌ" => "ӌ",
            "Ӎ" => "ӎ",
            "Ӑ" => "ӑ",
            "Ӓ" => "ӓ",
            "Ӕ" => "ӕ",
            "Ӗ" => "ӗ",
            "Ә" => "ә",
            "Ӛ" => "ӛ",
            "Ӝ" => "ӝ",
            "Ӟ" => "ӟ",
            "Ӡ" => "ӡ",
            "Ӣ" => "ӣ",
            "Ӥ" => "ӥ",
            "Ӧ" => "ӧ",
            "Ө" => "ө",
            "Ӫ" => "ӫ",
            "Ӭ" => "ӭ",
            "Ӯ" => "ӯ",
            "Ӱ" => "ӱ",
            "Ӳ" => "ӳ",
            "Ӵ" => "ӵ",
            "Ӷ" => "ӷ",
            "Ӹ" => "ӹ",
            "Ӽ" => "ӽ",
            "Ӿ" => "ӿ",

            "Ѡ" => "ѡ",         //      historical Cyrillic
            "Ѣ" => "ѣ",
            "Ѥ" => "ѥ",
            "Ѧ" => "ѧ",
            "Ѩ" => "ѩ",
            "Ѫ" => "ѫ",
            "Ѭ" => "ѭ",
            "Ѯ" => "ѯ",
            "Ѱ" => "ѱ",
            "Ѳ" => "ѳ",
            "Ѵ" => "ѵ",
            "Ѷ" => "ѷ",
            "Ѹ" => "ѹ",
            "Ѻ" => "ѻ",
            "Ѽ" => "ѽ",
            "Ѿ" => "ѿ",
            "Ҁ" => "ҁ",
            "Ǎ" => "ǎ",
            "F̀" => "f̀",
            "Ỳ" => "ỳ",

                    "Ð?" => "Ð°",
                    "Ð‘" => "Ð±",
                    "Ð’" => "Ð²",
                    "Ð“" => "Ð³",
                    "Ð”" => "Ð´",
                    "Ð•" => "Ðµ",
                    "Ð–" => "Ð¶",
                    "Ð—" => "Ð·",
                    "Ð˜" => "Ð¸",
                    "Ð™" => "Ð¹",
                    "Ðš" => "Ðº",
                    "Ð›" => "Ð»",
                    "Ðœ" => "Ð½",
                    "Ðž" => "Ð¾",
                    "ÐŸ" => "Ð¿",
                    "Ð " => "Ñ€",
                    "Ð¡" => "Ñ?",
                    "Ð¢" => "Ñ‚",
                    "Ð£" => "Ñƒ",
                    "Ð¤" => "Ñ„",
                    "Ð¥" => "Ñ…",
                    "Ð¦" => "Ñ†",
                    "Ð§" => "Ñ‡",
                    "Ð¨" => "Ñˆ",
                    "Ð©" => "Ñ‰",
                    "Ðª" => "ÑŠ",
                    "Ð«" => "Ñ‹",
                    "Ð¬" => "ÑŒ",
                    "Ð­" => "Ñ?",
                    "Ð®" => "ÑŽ",
                    "Ð¯" => "Ñ?",

                    "Ð?" => "Ñ‘",
                    "Ð‚" => "Ñ’",
                    "Ðƒ" => "Ñ“",
                    "Ð„" => "Ñ”",
                    "Ð…" => "Ñ•",
                    "Ð†" => "Ñ–",
                    "Ð‡" => "Ñ—",
                    "Ðˆ" => "Ñ˜",
                    "Ð‰" => "Ñ™",
                    "ÐŠ" => "Ñš",
                    "Ð‹" => "Ñ›",
                    "ÐŒ" => "Ñœ",
                    "ÐŽ" => "Ñž",
                    "Ð?" => "ÑŸ"
                    );

                    reset($lower);
                    while ($char = each($lower)) {
                        $string = preg_replace("/".$char[0]."/i", $char[1], $string);
                    }
        }
        return (strtr($string,  "ABCDEFGHIJKLMNOPQRSTUVWXYZ",
                                    "abcdefghijklmnopqrstuvwxyz"));

    }

    //      find valid link_id's for preselected category and all its sub-catecories
    function getcat_links($category, $mysql_table_prefix) {
        global $db_con, $debug;

        $allcats = get_catids($category, $mysql_table_prefix);
        $catlist = implode(",", $allcats);
        $sql_query1 = "SELECT link_id from ".$mysql_table_prefix."links, ".$mysql_table_prefix."sites, ".$mysql_table_prefix."categories, ".$mysql_table_prefix."site_category where ".$mysql_table_prefix."links.site_id = ".$mysql_table_prefix."sites.site_id and ".$mysql_table_prefix."sites.site_id = ".$mysql_table_prefix."site_category.site_id and ".$mysql_table_prefix."site_category.category_id in ($catlist)";
        $result = $db_con->query($sql_query1);

        if (!$result->num_rows) {
            $possible_to_find = 0;
        } else {
	        while ($row = $result->fetch_array(MYSQLI_NUM)) {
	            $category_list[] = $row[0];
	        }
        }
        return implode(",", array_unique($category_list));
    }

    //      extract all parent cat_id's (as there might be sub-categories)
    function get_catids($parent, $mysql_table_prefix) {
        global $db_con, $debug;

        $sql_query = "SELECT * FROM ".$mysql_table_prefix."categories WHERE parent_num=$parent";
        $result = $db_con->query($sql_query);

        $arr[] = $parent;
        if ($result->num_rows <> '') {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $id = $row["category_id"];
                $arr = sum_arrays($arr, get_catids($id));
            }
        }
        return $arr;
    }

    function translit_el($string) {

        //  1. replace Latin 's' at the end of multiple query words with the Greek 'ς'
        if(strstr($string, " ")){
            $str_array = explode(" ", $string); //  build an intermediate array

            foreach ($str_array as $this_word) {
                if (strrpos($this_word, "s") == strlen($this_word)-1) {     //  if 's' is last letter of this word
                    $str_array2[] = substr_replace($this_word, "ς", strlen($this_word)-1);
                } else {
                    $str_array2[] = $this_word;     //  no 's' as last letter was found, just use the input word
                }
            }
            $string = implode(" ", $str_array2);    //  rebuild the string
        } else {
            //  replace Latin 's' at the end of a single query word with the Greek 'ς'
            if (strrpos($string, "s") == strlen($string)-1) {
                $string = substr_replace($string, "ς", strlen($string)-1);
            }
        }

        //  2. translit some specialities
        $string = preg_replace('/TH/', "Θ", $string);
        $string = preg_replace('/th/', "θ", $string);

        $string = preg_replace('/CH/', "Χ", $string);
        $string = preg_replace('/ch/', "χ", $string);

        $string = preg_replace('/PS/', "Ψ", $string);
        $string = preg_replace('/ps/', "ψ", $string);

        $string = preg_replace('/PH/', "Φ", $string);
        $string = preg_replace('/ph/', "φ", $string);

        //  3. translit upper case letters
        $en = array("A","V","C","G","D","E","F","Z","I","K","L","M","N","X","O","P","Q","R","S","T","W","Y");
        $el = array("Α","Β","Ξ","Γ","Δ","Ε","Φ","Ζ","Ι","Κ","Λ","Μ","Ν","Ξ","Ο","Π","Θ","Ρ","Σ","Τ","Ω","Ψ");
        //$el = array("Α","Β","Ξ","Δ","Ε","Γ","Η","Ι","Κ","Λ","Μ","Ν","Χ","Ο","Π","Θ","Ρ","Σ","Τ","Υ","Ω","Φ","Ο","Ψ","Ζ");
        $string = str_replace($en, $el, $string);

        //  4. translit lower case letters
        $en = array("a","b","v","c","g","d","e","f","h","z","i","k","l","m","n","x","o","p","q","r","s","t","u","w","y","ī");
        $el = array("α","β","β","ε","γ","δ","ε","φ","η","ζ","ι","κ","λ","μ","ν","ε","ο","π","θ","ρ","σ","τ","υ","ω","ψ","η");
        //$el = array("α","β","ξ","δ","ε","γ","η","ι","κ","λ","μ","ν","ο","π","θ","ρ","σ","τ","υ","φ","ω","χ","ψ","ζ");
        $string = str_replace($en, $el, $string);

        return $string;
    }

    function remove_acc($string) {
        global $type;

        $acct_a = array("a;", "Ã ", "Ã¢", "å", "â", "ÃƒÂ¤", "Ã¤", "Ãƒ\"ž", "Ã„", "Ä", "ä", "Ã¡", "à",
                        "&agrave;", "á", "&aacute;", "À", "&Agrave;", "Á", "&Aacute;");
        $base_a = array("a", "a", "a", "a", "a", "a", "a", "A", "A", "A", "a", "a", "a",
                        "a", "a", "a", "A", "A", "A", "A");
        $string = str_ireplace($acct_a, $base_a, $string);

        $acct_c = array("č", "ç", "Ã§", "&ccedil;", "&Ccedil;", "Č");
        $base_c = array("c", "c", "c", "c", "C", "C");
        $string = str_ireplace($acct_c, $base_c, $string);

        $acct_e = array("ě", "Ãª", "Ã¨", "ê", "Ã©", "è", "&egrave;", "é", "&eacute;", "È", "&Egrave;", "É", "&Eacute;", "Ãˆ", "Ã‰", "Ě");
        $base_e = array("e", "e", "e", "e", "e", "e", "e", "e", "e", "E", "E", "E", "E", "E", "E", "E");
        $string = str_ireplace($acct_e, $base_e, $string);

        $acct_i = array("î", "ì", "&igrave;", "í", "&iacute;","&Igrave;", "Í", "&Iacute;",
                        "Ã±", "Â¡", "Ã'", "Â¿" );   //   "Ì" removed, because  replaces the letter Ü => I
        $base_i = array("i", "i", "i", "i", "i","I", "I", "I",
                        "ñ", "¡", "Ñ", "¿");
        $string = str_ireplace($acct_i, $base_i, $string);

        $acct_o = array("Ã´", "ø", "Ø", "ô", "ó", "ò", "õ", "Ã–", "ÃƒÂ¶", "Ã¶", "ã¶", "ö",
                        "Ã³", "ò","&ograve;", "ó", "&oacute;", "Ò", "&Ograve;", "Ó", "&Oacute;");
        $base_o = array("o", "o", "O", "o", "o", "o", "o", "O", "o", "o", "o", "o",
                        "O", "o", "O", "o", "o", "O", "O", "O", "O");
        $string = str_ireplace($acct_o, $base_o, $string);

        $acct_u = array("Âœ", "Ã»", "ù", "ú", "û", "ÃƒÂ¼", "Ã¼", "ÃƒÅ\“", "Ãœ", "Ü", "ü", "Ãº",
                        "ù", "&ugrave;", "ú", "&uacute;", "Ù", "&Ugrave;", "Ú", "&Uacute;");
        $base_u = array("u", "u", "u", "u", "u", "u", "u", "U", "U", "U", "u", "u",
                        "u", "u", "u", "u", "U", "U", "U", "U");
        $string = str_ireplace($acct_u, $base_u, $string);

        if ($type == "phrase" || $type == "tol"){    //  make tolerant by replacing vowels with %
            $string = rep_latvowels($string);
        }
        return $string;
    }

    //  replace Greek accents with their pure vowels
    function remove_acc_el($string) {
        global $type;

        $string = preg_replace('/α|ἀ|ἁ|ἂ|ἃ|ἄ|ἅ|ἆ|ἇ|ὰ|ά|ά|ᾀ|ᾁ|ᾂ|ᾃ|ᾄ|ᾅ|ᾆ|ᾇ|ᾰ|ᾱ|ᾲ|ᾳ|ᾴ|ᾶ|ᾷ|ά|ā/', "α", $string);
        $string = preg_replace('/Α|Ἀ|Ἁ|Ἂ|Ἃ|Ἄ|Ἅ|Ἆ|Ἇ|Ὰ|Ά|Ά|ᾈ|ᾉ|ᾊ|ᾋ|ᾌ|ᾍ|ᾎ|ᾏ|Ᾰ|Ᾱ|ᾼ|Ά/', "Α", $string);

        $string = preg_replace('/ε|ἐ|ἑ|ἒ|ἓ|ἔ|ἕ|ὲ|έ|έ|έ|ē/', "ε", $string);
        $string = preg_replace('/Ε|Ἐ|Ἑ|Ἒ|Ἓ|Ἔ|Ἕ|Ὲ|Έ|Έ|Έ/', "Ε", $string);

        $string = preg_replace('/η|ή|ἠ|ἡ|ἣ|ἣ|ἤ|ἥ|ἦ|ἧ|ὴ|ή|ή|ᾐ|ᾑ|ᾒ|ᾓ|ᾔ|ᾕ|ᾖ|ᾗ|ῂ|ῃ|ῄ|ῆ|ῇ/', "η", $string);
        $string = preg_replace('/Η|Ἠ|Ἡ|Ἢ|Ἣ|Ἤ|Ἥ|Ἦ|Ἧ|Ὴ|Ή|Ή|ᾘ|ᾙ|ᾚ|ᾛ|ᾜ|ᾝ|ᾞ|ᾞ|ῌ/', "Η", $string);

        $string = preg_replace('/ι|ἰ|ἱ|ἲ|ἳ|ἴ|ἵ|ἶ|ἷ|ὶ|ί|ί|ῐ|ῑ|ῖ|ϊ|ῒ|ΐ|ΐ|ῗ|ί|ΐ/', "ι", $string);
        $string = preg_replace('/Ἰ|Ἱ|Ἲ|Ἳ|Ἴ|Ἵ|Ἶ|Ἷ|Ὶ|Ί|Ί|Ῐ|Ῑ/', "Ἰ", $string);

        $string = preg_replace('/ω|ὠ|ὡ|ὢ|ὣ|ὤ|ὥ|ὦ|ὧ|ὼ|ώ|ώ|ᾠ|ᾡ|ᾢ|ᾣ|ᾤ|ᾥ|ᾦ|ᾧ|ῲ|ῳ|ῴ|ῶ|ῷ|ώ/', "ω", $string);
        $string = preg_replace('/Ω|Ὠ|Ὡ|Ὢ|Ὣ|Ὤ|Ὥ|Ὦ|Ὧ|Ὼ|Ώ|Ώ|ᾨ|ᾩ|ᾪ|ᾫ|ᾬ|ᾭ|ᾮ|ᾯ|ῼ/', "Ω", $string);

        $string = preg_replace('/ο|ὀ|ὁ|ὂ|ὃ|ὄ|ὅ|ὸ|ό|ό|ό|ò|ô|ō/', "ο", $string);
        $string = preg_replace('/Ο|Ὀ|Ὁ|Ὂ|Ὃ|Ὄ|Ὅ|Ὸ|Ό|Ό/', "Ο", $string);

        $string = preg_replace('/υ|ὐ|ὑ|ὒ|ὓ|ὔ|ὕ|ὖ|ὗ|ὺ|ύ|ύ|ῦ|ῠ|ῡ|ϋ|ῢ|ΰ|ΰ|ῧ|ύ/', "υ", $string);
        $string = preg_replace('/Υ|Ὑ|Ὓ|Ὕ|Ὗ|Ὺ|Ύ |Ύ|Ῠ|Ῡ/', "Υ", $string);

        $string = preg_replace('/ρ|ῤ|ῥ/', "ρ", $string);
        $string = preg_replace('/Ρ|Ῥ/', "Ρ", $string);

        if ($type == "tol"){    //  make tolerant by replacing vowels with %
            $string = rep_elvowels($string);
        }

        return $string;
    }


    //	replace Latin vowels with a (MySQL) wildcard
    function rep_latvowels($string) {
        $get = array("a", "c", "e", "i", "o", "u");
        $out = array("%", "%", "%", "%", "%", "%");
        $string = str_ireplace($get, $out, $string);
        return $string;
    }

    //  replace Greek vowels with a (MySQL) wildcard
    function rep_elvowels($string) {
        $get = array("α", "ε", "η", "ι", "ω", "ο", "υ", "υ");
        $out = array("%", "%", "%", "%", "%", "%", "%", "%");
        $string = str_ireplace($get, $out, $string);
        return $string;
    }

    function get_mediasuggests($mysql_table_prefix, $q1, $category, $catid){
        global $db_con, $suggest_id3, $case_sensitive, $suggest_rows, $debug, $home_charset, $delim;

        $all_media      = array();
        $media_results  = array();

        if ($suggest_id3 == '1') {      //      find suggestions also in ID3 tags
            if ($case_sensitive =='0') {
                $sql_query = "SELECT * from ".$mysql_table_prefix."media
                                            where LOWER(title) like '%".($db_con->real_escape_string($q1))."%'
                                            OR LOWER(id3) like '%".($db_con->real_escape_string($q1))."%'
                                            order by title, id3 ";

            } else {
                //  distinct results for UTF-8
                $sql_query = "SELECT * from ".$mysql_table_prefix."media
                                            where title like CONVERT(('%".($db_con->real_escape_string($q1))."%')USING utf8)
                                            or id3 like CONVERT(('%".($db_con->real_escape_string($q1))."%')USING utf8)
                                            order by title, id3 ";
            }
            $result = $db_con->query($sql_query);
        } else {
            if ($case_sensitive =='0') {
                $sql_query = "SELECT * from ".$mysql_table_prefix."media
                                            where CONVERT(LOWER(title)USING utf8) like '%".($db_con->real_escape_string($q1))."%'
                                            order by title, id3 ";
            } else {
                //  distinct results for UTF-8
                $sql_query = "SELECT * from ".$mysql_table_prefix."media
                                            where title like CONVERT(('%".($db_con->real_escape_string($q1))."%')USING utf8)
                                            order by title, id3 ";
            }
            $result = $db_con->query($sql_query);
        }

        //  collect all results
        while ($row = $result->fetch_array(MYSQLI_NUM)) {
            $row[5]         = substr($row[5], 0, strpos($row[5], $delim));
            $all_media[]    = $row;
        }

        //  if necessary, reduce to category valid links
        if ($category != '-1') {
            while (list($key, $value) = each($all_media)) {
                //  get site_id corresponding to this page
                $sql_query = "SELECT site_id from ".$mysql_table_prefix."links
                                                where url = '$value[2]'";
                $result = $db_con->query($sql_query);

                $all        = $result->fetch_array(MYSQLI_ASSOC);
                $site_id    = $all['site_id'];
                //  check for valid catid
                $sql_query1 = "SELECT * from ".$mysql_table_prefix."site_category
                                                where site_id = '$site_id' AND category_id ='$catid'";

                $res1 = $db_con->query($sql_query1);
                //  add valid link to result array
                if ($res1->num_rows) {
                    $media_results[] = $value;
                }
            }
        } else {
            $media_results = $all_media;    //  no category search
        }
        $media_results = array_slice($media_results, 0, $suggest_rows);     //      limit number of suggestions
        return $media_results;
    }

    function get_textsuggests($keyword, $type, $category, $category_list, $mysql_table_prefix){
        global $db_con, $suggest_history, $suggest_rows, $suggest_phrases, $case_sensitive, $suggest_keywords, $home_charset;
        global $vowels, $noacc_el, $translit_el;

        $values = array();              //  will contain all suggested keywords

        if ($suggest_history == '1') {
            $sql_query = "SELECT 	query as keyword, max(results) as results
                                                FROM {$mysql_table_prefix}query_log
                                                WHERE results > 0 AND (query LIKE '$keyword%' OR query LIKE '\"$keyword%')
                                                GROUP BY query ORDER BY results DESC
                                                LIMIT $suggest_rows";
            $result = $db_con->query($sql_query);

            if($result->num_rows){
                while($row = $result->fetch_array(MYSQLI_ASSOC))
                {
                    $values[$row['keyword']] = $row['result'];
                }
            }
        }

        //      ******      for 'phrase' search enter here
        if ($suggest_phrases && $type == 'phrase') {
            $values = fetch_phrasuggests($keyword, $category, $mysql_table_prefix, $category_list);
/*
             if ($translit_el == '1'){    //  try to find a keyword transliterated into Greek
             $el_values  = array();
             $keyword    = translit_el($keyword);

             $el_values  = fetch_phrasuggests($keyword, $category, $mysql_table_prefix);

             if (is_array($el_values)) {
             $values = array_merge($values, $el_values);
             }
             }
*/
        } elseif ($suggest_keywords) {

            //  ********      for single keyword search  enter here
            $values = fetch_txtsuggests($keyword, $category, $mysql_table_prefix, $category_list);

            if ($vowels == '1'){    //  try to find a keyword without accents
                $acc_values = array();
                $keyword    = remove_acc($keyword);
                $acc_values = fetch_txtsuggests($keyword, $category, $mysql_table_prefix, $category_list);
                if ($values && $acc_values) {
                    $values = array_merge($values, $acc_values);
                } else {
                    $values = $acc_values;
                }
            }

            if ($noacc_el == '1'){    //  try to find a keyword without Greek accents
                $nel_values = array();
                $keyword    = remove_acc_el($keyword);
                $nel_values = fetch_txtsuggests($keyword, $category, $mysql_table_prefix, $category_list);
                if ($nel_values) {
                    $values = array_merge($values, $nel_values);
                }
            }

            if ($translit_el == '1'){    //  try to find a keyword transliterated into Greek
                if (!is_array($values)) {   //if nothing found up to now
                    $values = array();
                }
                $el_values  = array();
                $keyword    = translit_el($keyword);
                $el_values  = fetch_txtsuggests($keyword, $category, $mysql_table_prefix, $category_list);
                if (is_array($el_values)) {
                    $values = array_merge($values, $el_values);
                }
            }
        }

        return $values;
    }

    function fetch_txtsuggests($keyword, $category, $mysql_table_prefix, $category_list) {
        global $db_con, $case_sensitive, $suggest_rows;

        if ($category != -1) {
            $sql_query = "SELECT *, count(keyword) as results
                                            FROM {$mysql_table_prefix}keywords INNER JOIN {$mysql_table_prefix}link_keyword USING (keyword_id)
                                            WHERE keyword LIKE '$keyword%' AND link_id in ($category_list)
                                            GROUP BY keyword
                                            ORDER BY results desc
                                            LIMIT $suggest_rows";
        } else {
            $sql_query = "SELECT *, count(keyword) as results
                                                FROM {$mysql_table_prefix}keywords INNER JOIN {$mysql_table_prefix}link_keyword USING (keyword_id)
                                                WHERE keyword LIKE '$keyword%'
                                                GROUP BY keyword
                                                ORDER BY results desc
                                                LIMIT $suggest_rows";
        }
        $result = $db_con->query($sql_query);
        if($result->num_rows) {
            while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $values[$row['keyword']] = $row['results'];
            }
        }
        return $values;
    }

    function fetch_phrasuggests($keyword, $category, $mysql_table_prefix, $category_list) {
        global $db_con, $case_sensitive, $suggest_rows;

        $_words = substr_count($keyword,' ') + 1;

        //      first try to find a phrase in full text
        if ($category != -1) {
            if ($case_sensitive == '0') {
                $sql_query = "SELECT count(link_id) as results, SUBSTRING_INDEX(SUBSTRING(fulltxt,LOCATE('$keyword',CONVERT(LOWER(fulltxt)USING utf8))), ' ', '$_words') as keyword, link_id as link_id FROM {$mysql_table_prefix}links where CONVERT(LOWER(fulltxt)USING utf8) like '%$keyword%' AND link_id in ($category_list)
                                                    GROUP BY SUBSTRING_INDEX( SUBSTRING( CONVERT(LOWER(fulltxt)USING utf8), LOCATE( '$keyword', CONVERT(LOWER(fulltxt)USING utf8) ) ) , ' ', '$_words' ) LIMIT $suggest_rows";

            } else {
                $sql_query = "SELECT count(link_id) as results, SUBSTRING_INDEX(SUBSTRING(fulltxt,LOCATE('$keyword', fulltxt)), ' ', '$_words') as keyword , link_id as link_id FROM {$mysql_table_prefix}links where fulltxt like '%$keyword%' AND link_id in ($category_list)
                                                    GROUP BY SUBSTRING_INDEX( SUBSTRING( fulltxt, LOCATE( '$keyword', fulltxt ) ) , ' ', '$_words' ) LIMIT $suggest_rows";
            }
        } else {
            if ($case_sensitive == '0') {
                $sql_query = "SELECT count(link_id) as results, SUBSTRING_INDEX(SUBSTRING(fulltxt,LOCATE('$keyword',CONVERT(LOWER(fulltxt)USING utf8))), ' ', '$_words') as keyword, link_id as link_id FROM {$mysql_table_prefix}links where CONVERT(LOWER(fulltxt)USING utf8) like '%$keyword%'
                                                    GROUP BY SUBSTRING_INDEX( SUBSTRING( CONVERT(LOWER(fulltxt)USING utf8), LOCATE( '$keyword', CONVERT(LOWER(fulltxt)USING utf8) ) ) , ' ', '$_words' ) LIMIT $suggest_rows";

            } else {
                $sql_query = "SELECT count(link_id) as results, SUBSTRING_INDEX(SUBSTRING(fulltxt,LOCATE('$keyword', fulltxt)), ' ', '$_words') as keyword FROM {$mysql_table_prefix}links where fulltxt like '%$keyword%'
                                                    GROUP BY SUBSTRING_INDEX( SUBSTRING( fulltxt, LOCATE( '$keyword', fulltxt ) ) , ' ', '$_words' ) LIMIT $suggest_rows";
            }
        }



        $result = $db_con->query($sql_query);

        if($result->num_rows) {
            while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $values[$row['keyword']] = $row['results'];

            }
        }

        //      additionally try to find the phrase in title tag
        if ($category != -1) {
            if ($case_sensitive == '0') {
               $sql_query  = "SELECT count(link_id) as results, SUBSTRING_INDEX(SUBSTRING(title,LOCATE('$keyword',CONVERT(LOWER(title)USING utf8))), ' ', '$_words') as keyword, link_id as link_id FROM {$mysql_table_prefix}links where CONVERT(LOWER(title)USING utf8) like '%$keyword%' AND link_id in ($category_list)
                                                    GROUP BY SUBSTRING_INDEX( SUBSTRING( LOWER(title), LOCATE( '$keyword', LOWER(title) ) ) , ' ', '$_words' ) LIMIT $suggest_rows";
            } else {
                $sql_query = "SELECT count(link_id) as results, SUBSTRING_INDEX(SUBSTRING(title,LOCATE('$keyword',title))), ' ', '$_words') as keyword, link_id as link_id FROM {$mysql_table_prefix}links where title like '%$keyword%' AND link_id in ($category_list)
                                                    GROUP BY SUBSTRING_INDEX( SUBSTRING( CONVERT((title)USING utf8), LOCATE( '$keyword', title ) ) ) , ' ', '$_words' ) LIMIT $suggest_rows";
            }
        } else {
            if ($case_sensitive == '0') {
               $sql_query  = "SELECT count(link_id) as results, SUBSTRING_INDEX(SUBSTRING(title,LOCATE('$keyword',CONVERT(LOWER(title)USING utf8))), ' ', '$_words') as keyword FROM {$mysql_table_prefix}links where CONVERT(LOWER(title)USING utf8) like '%$keyword%'
                                                    GROUP BY SUBSTRING_INDEX( SUBSTRING( LOWER(title), LOCATE( '$keyword', LOWER(title) ) ) , ' ', '$_words' ) LIMIT $suggest_rows";
            } else {
                $sql_query = "SELECT count(link_id) as results, SUBSTRING_INDEX(SUBSTRING(title,LOCATE('$keyword',title))), ' ', '$_words') as keyword FROM {$mysql_table_prefix}links where title like '%$keyword%'
                                                    GROUP BY SUBSTRING_INDEX( SUBSTRING( CONVERT((title)USING utf8), LOCATE( '$keyword', title ) ) ) , ' ', '$_words' ) LIMIT $suggest_rows";
            }
        }
        $result = $db_con->query($sql_query);

        if($result->num_rows) {
            while($row =$result->fetch_array(MYSQLI_ASSOC)) {
                $values[$row['keyword']] = $row['results'];
            }
        }

        return $values;
    }


    function sum_arrays($arr1, $arr2) {
        foreach ($arr2 as $elem) {
            $arr1[] = $elem;
        }
        return $arr1;
    }

?>
