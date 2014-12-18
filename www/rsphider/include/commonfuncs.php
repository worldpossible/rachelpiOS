<?php

    $common_dir = "$include_dir/common/";   //subfolder of .../include/ where all the common files are stored

    //  Returns the result of an SQL query as an array
    function sqli_fetch_all($query) {
        global $db_con, $db_con, $debug;

        $data = array();
        $result = $db_con->query($query);
        if ($debug > 0 && $db_con->errno) {
            $err_row = __LINE__-2;
            printf("MySQL failure: %s\n", $db_con->error);
            echo "<br />Invalid query causing this failure:";
            echo "<br />$query";
            echo "<br />Script .../include/commonfuncs.php aborted in row: $err_row.";
            exit;
        } else {
            while($row = $result->fetch_array(MYSQLI_BOTH)) {
                $data[]=$row;
            }
        }
        return $data;
    }

    //  Removes duplicate elements from an array
    function distinct_array($arr) {
        rsort($arr);
        reset($arr);
        $newarr = array();
        $i = 0;
        $element = current($arr);

        for ($n = 0; $n < sizeof($arr); $n++) {
            if (next($arr) != $element) {
                $newarr[$i] = $element;
                $element = current($arr);
                $i++;
            }
        }

        return $newarr;
    }

    function get_cats($parent) {
        global $db_con, $db_con, $mysql_table_prefix, $debug;

        $sql_query = "SELECT * FROM ".$mysql_table_prefix."categories WHERE parent_num=$parent";
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
        $arr[] = $parent;
        if ($result->num_rows) {
            while ($row = $result->fetch_array(MYSQL_ASSOC)) {
                $id = $row['category_id'];
                $arr = add_arrays($arr, get_cats($id));
            }
        }
        return $arr;
    }

    function add_arrays($arr1, $arr2) {
        foreach ($arr2 as $elem) {
            $arr1[] = $elem;
        }
        return $arr1;
    }

    function parse_all_url($url){   //  this will parse also IDN coded URLs, independent from local server configuration
        $url_parts = array();
        preg_match("@^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?@", $url, $regs);

        if ($regs[2]) $url_parts['scheme']    = $regs[2];
        if ($regs[4]) $url_parts['host']      = $regs[4];
        if ($regs[5]) $url_parts['path']      = $regs[5];
        if ($regs[7]) $url_parts['query']     = $regs[7];
        if ($regs[9]) $url_parts['fragment']  = $regs[9];

        return $url_parts;
    }

    class Segmentation {
        var $options = array('lowercase' => TRUE);
        var $dict_name = 'Unknown';
        var $dict_words = array();

        function setLowercase($value) {
            if ($value) {
                $this->options['lowercase'] = TRUE;
            } else {
                $this->options['lowercase'] = FALSE;
            }
            return TRUE;
        }

        function load($dict_file) {
            if (!file_exists($dict_file)) {
                return FALSE;
            }
            $fp = fopen($dict_file, 'r');
            $temp = fgets($fp, 1024);
            if ($temp === FALSE) {
                return FALSE;
            } else {
                if (strpos($temp, "\t") !== FALSE) {
                    list ($dict_type, $dict_name) = explode("\t", trim($temp));
                } else {
                    $dict_type = trim($temp);
                    $dict_name = 'Unknown';
                }
                $this->dict_name = $dict_name;
                if ($dict_type !== 'DICT_WORD_W') {
                    return FALSE;
                }
            }
            while (!feof($fp)) {
                $this->dict_words[rtrim(fgets($fp, 32))] = 1;
            }
            fclose($fp);
            return TRUE;
        }

        function getDictName() {
            return $this->dict_name;
        }

        function segmentString($str) {
            if (count($this->dict_words) === 0) {
                return FALSE;
            }
            $lines = explode("\n", $str);
            return $this->_segmentLines($lines);
        }

        function segmentFile($filename) {
            if (count($this->dict_words) === 0) {
                return FALSE;
            }
            $lines = file($filename);
            return $this->_segmentLines($lines);
        }

        function _segmentLines($lines) {
            $contents_segmented = '';
            foreach ($lines as $line) {
                $contents_segmented .= $this->_segmentLine(rtrim($line)) . " \n";
            }
            do {
                $contents_segmented = str_replace('  ', ' ', $contents_segmented);
            } while (strpos($contents_segmented, '  ') !== FALSE);
            return $contents_segmented;
        }

        function _segmentLine($str) {
            $str_final = '';
            $str_array = array();
            $str_length = strlen($str);
            if ($str_length > 0) {
                if (ord($str{$str_length-1}) >= 129) {
                    $str .= ' ';
                }
            }
            for ($i=0; $i<$str_length; $i++) {
                if (ord($str{$i}) >= 129) {
                    $str_array[] = $str{$i} . $str{$i+1};
                    $i++;
                } else {
                    $str_tmp = $str{$i};
                    for ($j=$i+1; $j<$str_length; $j++) {
                        if (ord($str{$j}) < 129) {
                            $str_tmp .= $str{$j};
                        } else {
                            break;
                        }
                    }
                    $str_array[] = array($str_tmp);
                    $i = $j - 1;
                }
            }
            $pos = count($str_array);
            while ($pos > 0) {
                $char = $str_array[$pos-1];
                if (is_array($char)) {
                    $str_final_tmp = $char[0];

                    if ($this->options['lowercase']) {
                        $str_final_tmp = strtolower($str_final_tmp);
                    }
                    $str_final = " $str_final_tmp$str_final";
                    $pos--;
                } else {
                    $word_found = 0;
                    $word_array = array(0 => '');
                    if ($pos < 4) {
                        $word_temp = $pos + 1;
                    } else {
                        $word_temp = 5;
                    }
                    for ($i=1; $i<$word_temp; $i++) {
                        $word_array[$i] = $str_array[$pos-$i] . $word_array[$i-1];
                    }
                    for ($i=($word_temp-1); $i>1; $i--) {
                        if (array_key_exists($word_array[$i], $this->dict_words)) {
                            $word_found = $i;
                            break;
                        }
                    }
                    if ($word_found) {
                        $str_final = " $word_array[$word_found]$str_final";
                        $pos = $pos - $word_found;
                    } else {
                        $str_final = " $char$str_final";
                        $pos--;
                    }
                }
            }
            return $str_final;
        }
    }

    $entities = array(
        "&amp" => "&",
        "&apos" => "'",
        "&THORN;"  => "Ãž",
        "&szlig;"  => "ÃŸ",
        "&agrave;" => "Ã ",
        "&aacute;" => "Ã¡",
        "&acirc;"  => "Ã¢",
        "&atilde;" => "Ã£",
        "&auml;"   => "Ã¤",
        "&aring;"  => "Ã¥",
        "&aelig;"  => "Ã¦",
        "&ccedil;" => "Ã§",
        "&egrave;" => "Ã¨",
        "&eacute;" => "Ã©",
        "&ecirc;"  => "Ãª",
        "&euml;"   => "Ã«",
        "&igrave;" => "Ã¬",
        "&iacute;" => "Ã­",
        "&icirc;"  => "Ã®",
        "&iuml;"   => "Ã¯",
        "&eth;"    => "Ã°",
        "&ntilde;" => "Ã±",
        "&ograve;" => "Ã²",
        "&oacute;" => "Ã³",
        "&ocirc;"  => "Ã´",
        "&otilde;" => "Ãµ",
        "&ouml;"   => "Ã¶",
        "&oslash;" => "Ã¸",
        "&ugrave;" => "Ã¹",
        "&uacute;" => "Ãº",
        "&ucirc;"  => "Ã»",
        "&uuml;"   => "Ã¼",
        "&yacute;" => "Ã½",
        "&thorn;"  => "Ã¾",
        "&yuml;"   => "Ã¿",
        "&THORN;"  => "Ãž",
        "&szlig;"  => "ÃŸ",
        "&Agrave;" => "Ã ",
        "&Aacute;" => "Ã¡",
        "&Acirc;"  => "Ã¢",
        "&Atilde;" => "Ã£",
        "&Auml;"   => "Ã¤",
        "&Aring;"  => "Ã¥",
        "&Aelig;"  => "Ã¦",
        "&Ccedil;" => "Ã§",
        "&Egrave;" => "Ã¨",
        "&Eacute;" => "Ã©",
        "&Ecirc;"  => "Ãª",
        "&Euml;"   => "Ã«",
        "&Igrave;" => "Ã¬",
        "&Iacute;" => "Ã­",
        "&Icirc;"  => "Ã®",
        "&Iuml;"   => "Ã¯",
        "&ETH;"    => "Ã°",
        "&Ntilde;" => "Ã±",
        "&Ograve;" => "Ã²",
        "&Oacute;" => "Ã³",
        "&Ocirc;"  => "Ã´",
        "&Otilde;" => "Ãµ",
        "&Ouml;"   => "Ã¶",
        "&Oslash;" => "Ã¸",
        "&Ugrave;" => "Ã¹",
        "&Uacute;" => "Ãº",
        "&Ucirc;"  => "Ã»",
        "&Uuml;"   => "Ã¼",
        "&Yacute;" => "Ã½",
        "&Yhorn;"  => "Ã¾",
        "&Yuml;"   => "Ã¿"
	);

    //Apache multi indexes parameters
    $apache_indexes = array (
        "N=A" => 1,
        "N=D" => 1,
        "M=A" => 1,
        "M=D" => 1,
        "S=A" => 1,
        "S=D" => 1,
        "D=A" => 1,
        "D=D" => 1,
        "C=N;O=A" => 1,
        "C=M;O=A" => 1,
        "C=S;O=A" => 1,
        "C=D;O=A" => 1,
        "C=N;O=D" => 1,
        "C=M;O=D" => 1,
        "C=S;O=D" => 1,
        "C=D;O=D" => 1
    );

    //  Extract of ligatures in Unicode (Latin-derived alphabets).  Not suporting medieval ligatures
    $latin_ligatures = array (
    "AE"    => "&#198;",
    "ae"    => "&#230;",
    "OE"    => "&#338;",
    "oe"    => "&#339;",
    "IJ"    => "&#306;",
    "ij"    => "&#307;",
    "ue"    => "&#7531;",   //phonetic, small only
    "TZ"    => "&#42792;",
    "tz"    => "&#42793;",
    "AA"    => "&#42802;",
    "aa"    => "&#42803;;",
    "AO"    => "&#42804;",
    "ao"    => "&#42805;",
    "AU"    => "&#42806;",
    "au"    => "&#42807;",
    "AV"    => "&#42808;",
    "av"    => "&#42809;",
    "AY"    => "&#42812;",
    "ay"    => "&#42813;",
    "OO"    => "&#42830;",
    "oo"    => "&#42831;",
    "et"    => "&amp;",     //  &
    "ss"    => "&#223;",    //  German ß
        "f‌f"    => "&#64256;",
        "f‌i"    => "&#64257;",
        "f‌l"    => "&#64258;",
        "f‌f‌i"   => "&#64259;",
        "f‌f‌l"   => "&#64260;"
        //"ſt"    => "&#64261;",
        //"st"    => "&#64262;",
        //"ſs"    => "&#223;",
        //"ſz"    => "&#223;"
    );

    //  Ligatures used only in phonetic transcription
    $phon_trans = array (
        "db"    => "&#568;",
        "op"    => "&#569;",
        "cp"    => "&#569;",
        "lʒ"    => "&#622;",
        "lezh"  => "&#622;",
        "dz"    => "&#675;",
        "dʒ"    => "&#676;",
        "dezh"  => "&#676;",
        "dʑ"    => "&#677;",
        "ts"    => "&#678;",
        "tʃ"    => "&#679;",
        "tesh"  => "&#679;",
        "tɕ"    => "&#680;",
        "fŋ"    => "&#681;",
        "ls"    => "&#682;",
        "lz"    => "&#683;"
    );


    function remove_accents($string) {
        return (strtr($string, "Ã€Ã?Ã‚ÃƒÃ„Ã…Ã†Ã Ã¡Ã¢Ã£Ã¤Ã¥Ã¦Ã’Ã“Ã”Ã•Ã•Ã–Ã˜Ã²Ã³Ã´ÃµÃ¶Ã¸ÃˆÃ‰ÃŠÃ‹Ã¨Ã©ÃªÃ«Ã°Ã‡Ã§Ã?ÃŒÃ?ÃŽÃ?Ã¬Ã­Ã®Ã¯Ã™ÃšÃ›ÃœÃ¹ÃºÃ»Ã¼Ã‘Ã±ÃžÃŸÃ¿Ã½",
                  "aaaaaaaaaaaaaaoooooooooooooeeeeeeeeecceiiiiiiiiuuuuuuuunntsyy"));
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
        //"İ" => "ı",
        "İ" => "i",
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

    //  convert characters into lower case
    function lower_case($string) {
        global $charSet, $home_charset, $greek, $cyrillic, $liga;

        if ($charSet =='') {
            $charSet = $home_charset;
        }
        $charSet = strtoupper($charSet);

        //      if required, convert Greek charset into lower case
        if ($greek == '1') {

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
            "Ψ" => "ψ",
            "Ω" => "ω"
            );

            reset($lower);
            while ($char = each($lower)) {
                $string = preg_replace("/".$char[0]."/i", $char[1], $string);
            }
        }

        //      if required, convert Cyrillic charset into lower case
        if ($cyrillic == '1') {

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

        if ($liga) {  //  convert upper case ligatures into lower case

            //  encode  the string to HTML entities
            $string = superentities($string);

            $upper_liga = array (
                "&#198;"   => "&#230;",     //      AE
                "&#338;"   => "&#339;",     //      OE
                "&#306;"    => "&#307;",    //      IJ
                "&#42792;"  => "&#42793;",  //      TZ
                "&#42802;"  => "&#42803;",  //      AA
                "&#42804;"  => "&#42805;",  //      AO
                "&#42806;"  => "&#42807;",  //      AU
                "&#42808;"  => "&#42809;",  //      AV
                "&#42812;"  => "&#42813;",  //      AY
                "&#42830;"  => "&#42831;"   //      OO
            );

            reset($upper_liga);

            while ($char = each($upper_liga)) {
                $string = preg_replace("/".$char[0]."/s", $char[1], $string);
            }
            //  make it readable as plain UTF-8 again
            $string = html_entity_decode($string, ENT_QUOTES, "UTF-8");  //  to be used on 'Shared Hosting' server
            //$string = preg_replace_callback("/(&#[0-9]+;)/", function($m) { return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); }, $string); //  to be used on advanced server
        }

        return (strtr($string,  "ABCDEFGHIJKLMNOPQRSTUVWXYZ",
                            "abcdefghijklmnopqrstuvwxyz"));
    }

    function superentities($str){
        $str2  = '';
        // get rid of existing entities else double-escape
        $str = html_entity_decode(stripslashes($str),ENT_QUOTES,'UTF-8');
        $ar = preg_split('/(?<!^)(?!$)/u', $str );  // return array of every multi-byte character
        foreach ($ar as $c){
            $o = ord($c);
            if ( (strlen($c) > 1) || /* multi-byte [unicode] */
                ($o <32 || $o > 126) || /* <- control / latin weirdos -> */
                ($o >33 && $o < 40) ||/* quotes + ambersand */
                ($o >59 && $o < 63) /* html */
            ) {
                // convert to numeric entity
                $c = mb_encode_numericentity($c, array(0x0, 0xffff, 0, 0xffff), 'UTF-8');
            }
            $str2 .= $c;
        }
        return $str2;
    }

    error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE & ~E_STRICT);
    global $use_common, $use_white1, $use_white2, $use_black, $home_charset;

    $com_in     = array();      //  intermediate array for ignored words
    $all_in     = array();      //  intermediate array for ignored words
    $common     = array();      //  array fo ignored words
    $ext        = array();      //  array for ignored file suffixes
    $whitelist  = array();      //  array for whitelist
    $white      = array();
    $white_in   = array();
    $blacklist  = array();      //  array for blacklist
    $black_in   = array();
    $uas_in     = array();      //  intermediate array for evil User Agents
    $ips_in     = array();      //  intermediate array for bad IPs
    $black_uas  = array();      // User Agent strings belonging to evil bots
    $black_ips  = array();      // IPs belonging to Google, MSN, Amazon, etc bots
    $black      = array();
    $image		= array();	    //	array for image suffixes
    $audio		= array();		//	array for audio suffixes
    $video		= array();	    //	array for video suffixes
    $divs_not   = array();      //      array for divs not to be indexed
    $divs_use   = array();      //      array for divs to be indexed
    $docs       = array();      //      array holding  a list of documents to be indexed
    $elements_not   = array();  //      array for HTML elements not to be indexed
    $elements_use   = array();  //      array for HTML elements to be indexed
    $slv        = array();      //      array of most common Second Level Domains

    $mysql_charset = conv_mysqli($home_charset); //  convert the home._charset to MySQL format

    if (is_dir($common_dir)) {
        $handle = opendir($common_dir);
        if ($use_common == 'all') {
            while (false !== ($common_file = readdir($handle))) {   //  get all common files
                if (strpos($common_file, "ommon_")) {
                    $act = @file($common_dir.$common_file);         //  get content of actual common file
                    $all_in = array_merge($all_in, $act);           //  build a complete array of common words
                }
            }
        }

        if ($use_common != 'all' && $use_common != 'none') {
            $all_in = @file("".$common_dir."common_".$use_common.".txt");         //  get content of language specific common file
        }

        if (is_array($all_in)) {
            while (list($id, $word) = each($all_in))
            if (preg_match("/\S/", $word)) {    //  remove empty entries from list
                $com_in[] = $word;
            }
        }

        if (is_array($com_in)) {
            while (list($id, $word) = each($com_in))
            $common[trim($word)] = 1;
        }

        if ($use_white1 == '1' || $use_white2 == '1') $white_in = @file($common_dir.'whitelist.txt');    //  get all words to enable page indexing

        $suffix     = @file($common_dir.'suffix.txt');      //  get all file suffixes to be ignored during index procedure
        $black_in   = @file($common_dir.'blacklist.txt');   //  get all words to prevent indexing of page
        $uas_in     = @file($common_dir.'black_uas.txt');   //  get all evil user-agent strings
        $ips_in     = @file($common_dir.'black_ips.txt');   //  get all Meta search engine IPs
        $image      = @file($common_dir.'image.txt');       //  get all image suffixes to be indexed
        $audio      = @file($common_dir.'audio.txt');       //  get all audio suffixes to be indexed
        $video      = @file($common_dir.'video.txt');       //  get all audio suffixes to be indexed
        $divs_not   = @file($common_dir.'divs_not.txt');    //  get all div's to not to be indexed (Admin selected)
        $divs_use   = @file($common_dir.'divs_use.txt');    //  get all div's to be indexed (Admin selected)
        $docu       = @file($common_dir.'docs.txt');        //  get all document suffixes to be indexed (Admin selected)
        $elements_not   = @file($common_dir.'elements_not.txt');    //  get all HTML elements to not to be indexed (Admin selected)
        $elements_use   = @file($common_dir.'elements_use.txt');    //  get all HTML elements to be indexed (Admin selected)
        $sld        = @file($common_dir.'sld.txt');         //  get all SLDs

        closedir($handle);

        if (is_array($suffix)) {
            while (list($id, $word) = each($suffix))
            if (preg_match("/\S/", $word)) {    //  remove empty entries from list
                $ext[] = trim($word);
            }

            //  if JavaScript redirections should not be followed
            if (!$js_reloc) {
                $ext[] = "js";  //  add suffix for JavaScript files
            }

            $ext = array_unique($ext);
            sort($ext);
        }

        if (is_array($white_in)) {
            foreach ($white_in as $val) {
                if ($case_sensitive == '0') {
                    $val = lower_case($val);
                }
                $val = @iconv($home_charset,"UTF-8",$val);
                if (preg_match("/\S/", $val)) {    //  remove empty entries from list
                    $white[] = addslashes($val);
                }
            }

            while (list($id, $word) = each($white))
            $whitelist[] = trim($word);
            $whitelist = array_unique($whitelist);
            sort($whitelist);
        }

        if (is_array($black_in)) {
            foreach ($black_in as $val) {
                if ($case_sensitive == '0') {
                    $val = lower_case($val);
                }
                $val = @iconv($home_charset,"UTF-8",$val);
                if (preg_match("/\S/", $val)) {    //  remove empty entries from list
                    $black[] = trim($val);
                }
            }

            while (list($id, $word) = each($black))
            $blacklist[] = $word;
            $blacklist = array_unique($blacklist);
            sort($blacklist);
        }

        if (is_array($image)) {
            while (list($id, $word) = each($image))
            if (preg_match("/\S/", $word)) {    //  remove empty entries from list
                $imagelist[] = trim(strtolower($word));
            }
            $imagelist = array_unique($imagelist);
            sort($imagelist);
        }

        if (is_array($audio)) {
            while (list($id, $word) = each($audio))
            if (preg_match("/\S/", $word)) {    //  remove empty entries from list
                $audiolist[] = trim(strtolower($word));
            }
            $audiolist = array_unique($audiolist);
            sort($audiolist);
        }

        if (is_array($video)) {
            while (list($id, $word) = each($video))
            if (preg_match("/\S/", $word)) {    //  remove empty entries from list
                $videolist[] = trim(strtolower($word));
            }
            $videolist = array_unique($videolist);
            sort($videolist);
        }

        if (is_array($divs_not)) {
            while (list($id, $word) = each($divs_not))
            if (preg_match("/\S/", $word)) {    //  remove empty entries from list
                $not_divlist[] = trim($word);
            }
            $not_divlist = array_unique($not_divlist);
            sort($not_divlist);
        }

        if (is_array($divs_use)) {
            while (list($id, $word) = each($divs_use))
            if (preg_match("/\S/", $word)) {
                $use_divlist[] = trim($word);
            }
            $use_divlist = array_unique($use_divlist);
            sort($use_divlist);
        }


        if (is_array($docu)) {
            while (list($id, $word) = each($docu))
            if (preg_match("/\S/", $word)) {    //  remove empty entries from list
                $docs[] = trim(strtolower($word));
            }
            $docs = array_unique($docs);
            sort($docs);
        }

        if (is_array($elements_not)) {
            while (list($id, $word) = each($elements_not))
            if (preg_match("/\S/", $word)) {    //  remove empty entries from list
                $not_elementslist[] = trim($word);
            }
            $not_elementslist = array_unique($not_elementslist);
            sort($not_elementslist);
        }

        if (is_array($elements_use)) {
            while (list($id, $word) = each($elements_use))
            if (preg_match("/\S/", $word)) {
                $use_elementslist[] = trim($word);
            }
            $use_elementslist = array_unique($use_elementslist);
            sort($use_elementslist);
        }

        if (is_array($sld)) {
            while (list($id, $word) = each($sld))
            $sldlist[] = trim($word);
            $sldlist = array_unique($sldlist);
            sort($sldlist);
        }

        if (is_array($uas_in)) {
            while (list($id, $word) = each($uas_in))
            if (preg_match("/\S/", $word)) {    //  remove empty entries from list
                $black_uas[] = $word;
            }
        }

        if (is_array($ips_in)) {
            while (list($id, $word) = each($ips_in))
            if (preg_match("/\S/", $word)) {    //  remove empty entries from list
                $black_ips[] = $word;
            }
        }
    }

    function is_num($var) {
        for ($i=0;$i<strlen($var);$i++) {
            $ascii_code=ord($var[$i]);
            if ($ascii_code >=49 && $ascii_code <=57){
                continue;
            } else {
                return false;
            }
        }
        return true;
    }

    function getHttpVars() {
        $superglobs = array(
        '_POST',
        '_GET',
        'HTTP_POST_VARS',
        'HTTP_GET_VARS');

        $httpvars = array();

        // extract the right array
        foreach ($superglobs as $glob) {
            global $$glob;
            if (isset($$glob) && is_array($$glob)) {
                $httpvars = $$glob;
         }
         if (count($httpvars) > 0)
            break;
        }
        //echo "<br>http Array:<br><pre>";print_r($httpvars);echo "</pre>";
        return $httpvars;

    }

    function countSubstrs($haystack, $needle) {
        $count = 0;
        while(strpos($haystack,$needle) !== false) {
            $haystack = substr($haystack, (strpos($haystack,$needle) + 1));
            $count++;
        }
        return $count;
    }

    function quote_replace($str) {

        $str = str_replace("\"", "&quot;", $str);
        return str_replace("'","&apos;", $str);
    }


    function fst_lt_snd($version1, $version2) {

        $list1 = explode(".", $version1);
        $list2 = explode(".", $version2);

        $length = count($list1);
        $i = 0;
        while ($i < $length) {
            if ($list1[$i] < $list2[$i])
            return true;
            if ($list1[$i] > $list2[$i])
            return false;
            $i++;
        }

        if ($length < count($list2)) {
            return true;
        }
        return false;

    }

    function get_dir_contents($dir) {
        $contents = Array();
        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    $contents[] = $file;
                }
            }
            closedir($handle);
        }
        return $contents;
    }

    function replace_ampersand($str) {
        return str_replace("&", "&amp;", $str);
    }

    function list_cats($parent, $lev, $color, $message) {
        global $db_con, $mysql_table_prefix, $debug, $dba_act;

        if ($lev == 0) {
            echo "<div class='submenu cntr y3'>|&nbsp;&nbsp;&nbsp;Database $dba_act&nbsp;&nbsp;&nbsp;Table prefix '$mysql_table_prefix'&nbsp;&nbsp;&nbsp;|<br />
        <ul>
            <li><a href='admin.php?f=add_cat'>Add category</a></li>
        </ul>
        </div>
";
            echo $message;
            echo "<div class='panel'>
    <table width='100%'>
    <tr>
        <td class='tblhead' colspan='3'>Categories</td>
    </tr>
    ";
        }
        $space = "";
        for ($x = 0; $x < $lev; $x++) {
            $space .= "<span class='tree'>&raquo;</span>&nbsp;";
        }

        $sql_query = "SELECT * FROM ".$mysql_table_prefix."categories WHERE parent_num=$parent ORDER BY category";
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
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                if ($color =="odrow") {
                    $color = "evrow";
                } else {
                    $color = "odrow";
                }
                $id = $row['category_id'];
                $cat = $row['category'];
                echo "<tr class='$color'>
        ";
                if (!$space=="") {
                    echo "<td width='90%'>
        <div>$space<a class='options' href='admin.php?f=edit_cat&amp;cat_id=$id'
            title='Edit this Sub-Category'>".stripslashes($cat)."</a></div></td>
        <td class='options'><a href='admin.php?f=edit_cat&amp;cat_id=$id' class='options' title='Edit this Sub-Category'>Edit</a></td>
        <td class='options'><a href='admin.php?f=11&amp;cat_id=$id' title='Delete this Sub-Category'
            onclick=\"return confirm('Are you sure you want to delete? Subcategories will be lost.')\" class='options'>Delete</a></td>
    </tr>
    ";
                } else {
                    echo"<td width='90%'><a class='options' href='admin.php?f=edit_cat&amp;cat_id=$id'
            title='Edit this Category'>".stripslashes($cat)."</a></td>
        <td class='options'><a href='admin.php?f=edit_cat&amp;cat_id=$id' class='options' title='Edit this Category'>Edit</a></td>
        <td class='options'><a href='admin.php?f=11&amp;cat_id=$id' title='Delete this Category'
            onclick=\"return confirm('Are you sure you want to delete? Subcategories will be lost.')\" class='options'>Delete</a></td>
    </tr>
";
                }
                $color = list_cats($id, $lev + 1, $color, "");
            }
        }
        if ($lev == 0) {
            echo "</table>
</div>
";
        }
        return $color;
    }

    function list_catsform($parent, $lev, $color, $message, $category_id) {
        global $db_con, $mysql_table_prefix, $debug;

        if ($lev == 0) {
            print "\n";
        }
        $space = "";
        for ($x = 0; $x < $lev; $x++)
        $space .= "&nbsp;&nbsp;&nbsp;-&nbsp;";

        $sql_query = "SELECT * FROM ".$mysql_table_prefix."categories WHERE parent_num=$parent ORDER BY category LIMIT 0 , 300";
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

        if ($result->num_rows){
            print "<option ".$selected." value=\"0\">&nbsp;&nbsp;none</option>\n";  //select no category
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $id = $row['category_id'];
                $cat = $row['category'];
                $selected = " selected=\"selected\" ";
                if ($category_id != $id) { $selected = ""; }
                print "<option ".$selected." value=\"".$id."\">".$space.stripslashes($cat)."</option>\n";

                $color = list_catsform($id, $lev + 1, $color, "", $category_id);
            }
        }
        return $color;
    }

    function getmicrotime(){
        list($usec, $sec) = explode(" ",microtime());
        return ((float)$usec + (float)$sec);
    }

    function saveToLog($query, $time, $results, $ip, $media) {
        global $db_con, $mysql_table_prefix, $debug;

        if ($results =="") {
            $results = 0;
        }
        $sql_query =  "INSERT into ".$mysql_table_prefix."query_log (query, time, elapsed, results, ip, media) values ('$query', NOW(), '$time', '$results', '$ip', '$media')";
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

    }

    function validate_url($input) {
        global $mytitle;

        //	Standard URL test
        if (! preg_match('=(https?|ftp)://[a-z0-9]([a-z0-9-]*[a-z/0-9])?\.[a-z0-9]=i', ($input))) {
            echo "<h1>$mytitle</h1>
            <br />
            <p class='warnadmin cntr'>
            Invalid input for 'URL'
            </p>
            <a class='bkbtn' href='addurl.php' title='Go back to Submission Form'>Back</a>
            </body>
            </html>
        ";
            die ('');
        }

        //      Do we have a valid DNS ? This test is disabled for localhost application as checkdnsrr needs internet access
        $localhost = strstr(htmlspecialchars(@$_SERVER['HTTP_REFERER']), "localhost");
        if (!$localhost) {
            if (preg_match("/www/i", $input)){
                $input = preg_replace ('/http:\/\//i','',$input);
                $input1 = $input;
                $pos = strpos($input1,"/");
                if ($pos != '') $input1 = substr($input1,0,$pos);
                if(@checkdnsrr("www.sphider-plus.eu", "A")) {    //    pre-check for correct response of checkdnsrr() on Windows OS
                    if(!checkdnsrr($input1, "A")) {
                        echo "<h1>$mytitle</h1>
                        <br />
                        <p class='warnadmin cntr'>Invalid URL input. No DNS resource available for this url
                        <a class='bkbtn' href='addurl.php' title='Go back to Submission Form'>Back</a></p>
                        </body>
                        </html>
                    ";
                        die ('');
                    }
                }
                $input = str_replace("www","http://www",$input);
            }
        }
        return ($input);
    }

    function validate_email($input) {
        //      kill LF, CR, comma, zero-bytes and entities
        $input = preg_replace('/[\0\r\n,]|(%0\s*\w)/im', null, urldecode($input));
        if (!preg_match('/\@localhost$/', $input)) {
            //	Standard e-mail test
            if(!preg_match('/^[\w.+-]{2,}\@[\w.-]{2,}\.[a-z]{2,6}$/', $input)) {
                echo "<h1>$mytitle</h1>
                <br />
                <p class='warnadmin cntr'>
                Invalid input for 'e-mail account'
                </p>
                <a class='bkbtn' href='addurl.php' title='Go back to Submission Form'>Back</a>
                </body>
                </html>
            ";
                die ('');
            }
        } else {
            //  some rudimentarily test for localhost e-mail accounts
            if(!preg_match('/^[\w.+-]{2,}\@/', $input)) {
                echo "<h1>$mytitle</h1>
                <br />
                <p class='warnadmin cntr'>
                Invalid input for 'e-mail account'
                </p>
                <a class='bkbtn' href='addurl.php' title='Go back to Submission Form'>Back</a>
                </body>
                </html>
            ";
                die ('');
            }
        }

        //      Check if Mail Exchange Resource Record (MX-RR)  is valid and also is stored in Domain Name System (DNS)
        //      This test is disabled for localhost applications as getmxrr needs internet access
        $localhost = strstr(htmlspecialchars(@$_SERVER['HTTP_REFERER']), "localhost");
        if (!$localhost) {
            if(!getmxrr(substr(strstr($input, '@'), 1), $mxhosts)) {
                echo "<h1>$mytitle</h1>
                <br />
                <p class='warnadmin cntr'>
                Invald e-mail account.<br />
                There is no valid Mail Exchange Resource Record (MX-RR)<br />
                on the Domain Name System (DNS)
                </p>
                <a class='bkbtn' href='addurl.php' title='Go back to Submission Form'>Back</a>
                </body>
                </html>
            ";
                die ('');
            }
        }
        return ($input);
    }

    function parse_addr($url) {     //  function like parse_url, but working also for non ASCII URLs
        $urlparts = array();
        $sch = '';
        $h = '';
        $o = '';
        $p = '';
        $q = '';
        $f = '';
        $url = str_replace("\\", "/", $url);
        $url2 = $url."/";       //      might be missing at some 301 relocated addresses

        $sch = strpos($url, "://");                     //    end of [scheme] = begin of [host]
        $urlparts[scheme] = substr($url, 0, $sch);
        $h = strpos(substr($url2, $sch+3), "/");        //    endpos of [host]port] = begin of [path]

        $host_port = substr($url, $sch+3, $h);
        $o = strpos(substr($url, $sch+3, $h), ":");     //  find [port] delimiter

        if (!$o) {
            $urlparts[host] = substr($url, $sch+3, $h);
        } else {                                                //  if [port] available
            $urlparts[host] = substr($url, $sch+3, $o);         //  only [host]
            $urlparts[port] = substr($url, $sch+$o+4, $h-$o-1); //  additionally [port]
        }

        $p = strpos(substr($url, $h+1), "/");           //    begin position [path]
        $q = strpos(substr($url, $h+$p+1), "?");        //    find begin of [query]

        if (!$q) {  //  if no query found
            $urlparts[path] = substr($url, $h+$p+1);
        } else {
            $urlparts[path] = substr($url, $h+$p+1, $q);
            $f = strpos(substr($url, $h+$p+$q+2), "#");         //   find beginn of [fragment]
        }

        if ($q && !$f) {  //  if no fragment found
            $urlparts[query] = substr($url, $h+$p+$q+2);        //   only [query]
        }
        if ($f) {
            $urlparts[query] = substr($url, $h+$p+$q+2, $f);
            $urlparts[fragment] = substr($url, $h+$p+$q+$f+3);
        }

        return ($urlparts); //  [user] and [pass] are currently not parsed
    }

    function convert_url($url) {    //  storable for MySQL
        $url = str_replace("&amp;", "&", $url);
        $url = str_replace(" ", "%20", $url);
        return $url;
    }

    function reconvert_url($url) {  //  readable for messages
        $url = str_replace("&amp;","&", $url);
        $url = str_replace("%20", " ", $url);
        return $url;
    }

    function cleanup_text($input='', $preserve='', $allowed_tags='') {
        if (empty($preserve)){
            $input = strip_tags($input, $allowed_tags);
        }
        $input = htmlspecialchars($input, ENT_QUOTES);
        return $input;
    }

    function cleaninput($input) {
        global $db_con, $block_attacks;

        if (get_magic_quotes_gpc()) {
            $input = stripslashes($input);  //      delete quotes
        }
/*
         //      prevent Directory Traversal attacks
         if(preg_match('/\.\.\/|\.\.\\\/i', $input)) {
         $input = '';
         }

        //      prevent SQL-injection
        if (substr_count($input,"'") != '1') {
            $input = mysql_real_escape_string($input);
        } else {
            $input = str_replace('\\','\\\\', $input);  //      if one slash is part of the query, we have to allow it  . . .
            $input = str_replace('"','\"', $input);     //      never the less we need to prevent SQL attacks
        }
*/
        //      prevent SQL-injection
        $input = $db_con->real_escape_string($input);

        if (preg_match("/%FF%FE%3C%73%63%72%69%70%74%3E/i",$input)) {   //  tr/vb.hpq trojan
            $input = '';
        }

        if ($block_attacks == "1") {
            //	prevent XSS-attack and Shell-execute
            if (preg_match("/cmd|CREATE|DELETE|DROP|eval|EXEC|File|INSERT|printf/i",$input)) {
                $input = '';
            }
            if (preg_match("/LOCK|PROCESSLIST|SELECT|shell|SHOW|SHUTDOWN/i",$input)) {
                $input = '';
            }
            if (preg_match("/SQL|SYSTEM|TRUNCATE|UNION|UPDATE|DUMP/i",$input)) {
                $input = '';
            }

            //  suppress JavaScript execution and tag inclusions
            $input = unsafe($input);
        }
        return $input;
    }

    $UNSAFE_IN = array();
    $UNSAFE_IN[] = "/script/i";
    $UNSAFE_IN[] = "/alert/i";
    $UNSAFE_IN[] = "/javascript\s*:/i";
    $UNSAFE_IN[] = "/vbscri?pt\s*:/i";
    $UNSAFE_IN[] = "/<\s*embed.*swf/i";
    $UNSAFE_IN[] = "/<[^>]*[^a-z]onabort\s*=/i";
    $UNSAFE_IN[] = "/<[^>]*[^a-z]onblur\s*=/i";
    $UNSAFE_IN[] = "/<[^>]*[^a-z]onchange\s*=/i";
    $UNSAFE_IN[] = "/<[^>]*[^a-z]onfocus\s*=/i";
    $UNSAFE_IN[] = "/<[^>]*[^a-z]onmouseout\s*=/i";
    $UNSAFE_IN[] = "/<[^>]*[^a-z]onmouseover\s*=/i";
    $UNSAFE_IN[] = "/<[^>]*[^a-z]onload\s*=/i";
    $UNSAFE_IN[] = "/<[^>]*[^a-z]onreset\s*=/i";
    $UNSAFE_IN[] = "/<[^>]*[^a-z]onselect\s*=/i";
    $UNSAFE_IN[] = "/<[^>]*[^a-z]onsubmit\s*=/i";
    $UNSAFE_IN[] = "/<[^>]*[^a-z]onunload\s*=/i";
    $UNSAFE_IN[] = "/<[^>]*[^a-z]onerror\s*=/i";
    $UNSAFE_IN[] = "/<[^>]*[^a-z]onclick\s*=/i";
    $UNSAFE_IN[] = "/onabort\s*=/i";
    $UNSAFE_IN[] = "/onblur\s*=/i";
    $UNSAFE_IN[] = "/onchange\s*=/i";
    $UNSAFE_IN[] = "/onfocus\s*=/i";
    $UNSAFE_IN[] = "/onmouseout\s*=/i";
    $UNSAFE_IN[] = "/onmouseover\s*=/i";
    $UNSAFE_IN[] = "/onload\s*=/i";
    $UNSAFE_IN[] = "/onreset\s*=/i";
    $UNSAFE_IN[] = "/onselect\s*=/i";
    $UNSAFE_IN[] = "/onsubmit\s*=/i";
    $UNSAFE_IN[] = "/onunload\s*=/i";
    $UNSAFE_IN[] = "/onerror\s*=/i";
    $UNSAFE_IN[] = "/onclick\s*=/i";
    $UNSAFE_IN[] = "/\'\/\*/i";
    $UNSAFE_IN[] = "/\"><>/i";
    $UNSAFE_IN[] = "/\?\@\%/i";

    function unsafe($input) {
        global $UNSAFE_IN;

        foreach ($UNSAFE_IN as $match) {
            if( preg_match($match, $input)) {
                $input = '';
                return $input;
            }
        }
        return $input;
    }

    function ip2bin($ip) {
        if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false)
        return base_convert(ip2long($ip),10,2);
        if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false)
        return false;
        if(($ip_n = inet_pton($ip)) === false) return false;
        $bits = 15; // 16 x 8 bit = 128bit (ipv6)
        while ($bits >= 0) {
            $bin = sprintf("%08b",(ord($ip_n[$bits])));
            $ipbin = $bin.$ipbin;
            $bits--;
        }
        return $ipbin;
    }

    function bin2ip($bin) {
        if(strlen($bin) <= 32) // 32bits (ipv4)
        return long2ip(base_convert($bin,2,10));
        if(strlen($bin) != 128)
        return false;
        $pad = 128 - strlen($bin);
        for ($i = 1; $i <= $pad; $i++) {
            $bin = "0".$bin;
        }
        $bits = 0;
        while ($bits <= 7) {
            $bin_part = substr($bin,($bits*16),16);
            $ipv6 .= dechex(bindec($bin_part)).":";
            $bits++;
        }
        return inet_ntop(inet_pton(substr($ipv6,0,-1)));
    }

    function IPv4To6($Ip) {
        //  Convert an IPv4 address to IPv6
        //   @param string IP Address in dot notation (192.168.1.100)
        //   @return string IPv6 formatted address or false if invalid input

        static $Mask = '::ffff:'; // This tells IPv6 it has an IPv4 address
        $IPv6 = (strpos($Ip, '::') === 0);
        $IPv4 = (strpos($Ip, '.') > 0);

        if (!$IPv4 && !$IPv6) return false;
        if ($IPv6 && $IPv4) $Ip = substr($Ip, strrpos($Ip, ':')+1); // Strip IPv4 Compatibility notation
        elseif (!$IPv4) return $Ip; // Seems to be IPv6 already?
        $Ip = array_pad(explode('.', $Ip), 4, 0);
        if (count($Ip) > 4) return false;
        for ($i = 0; $i < 4; $i++) if ($Ip[$i] > 255) return false;

        $Part7 = base_convert(($Ip[0] * 256) + $Ip[1], 10, 16);
        $Part8 = base_convert(($Ip[2] * 256) + $Ip[3], 10, 16);
        return $Mask.$Part7.':'.$Part8;
    }

    function ExpandIPv6Notation($Ip) {
        //  replace '::' with appropriate number of ':0'
        if (strpos($Ip, '::') !== false)
            $Ip = str_replace('::', str_repeat(':0', 8 - substr_count($Ip, ':')).':', $Ip);
        if (strpos($Ip, ':') === 0) $Ip = '0'.$Ip;
        return $Ip;
    }

    function IPv6ToLong($Ip, $DatabaseParts= 2) {
        //  Convert IPv6 address to an integer
        //  Optionally split in to two parts.
        //  @see http://stackoverflow.com/questions/420680/
        $Ip = ExpandIPv6Notation($Ip);
        $Parts = explode(':', $Ip);
        $Ip = array('', '');
        for ($i = 0; $i < 4; $i++) $Ip[0] .= str_pad(base_convert($Parts[$i], 16, 2), 16, 0, STR_PAD_LEFT);
        for ($i = 4; $i < 8; $i++) $Ip[1] .= str_pad(base_convert($Parts[$i], 16, 2), 16, 0, STR_PAD_LEFT);

        if ($DatabaseParts == 2)
                return array(base_convert($Ip[0], 2, 10), base_convert($Ip[1], 2, 10));
        else    return base_convert($Ip[0], 2, 10) + base_convert($Ip[1], 2, 10);
    }

    function to_utf8( $string ) {
        // From http://w3.org/International/questions/qa-forms-utf-8.html
        if ( preg_match('%^(?:
          [\x09\x0A\x0D\x20-\x7E]            # ASCII
        | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
        | \xE0[\xA0-\xBF][\x80-\xBF]         # excluding overlongs
        | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
        | \xED[\x80-\x9F][\x80-\xBF]         # excluding surrogates
        | \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
        | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
        | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
        )*$%xs', $string) ) {
            return $string;
        } else {
            return iconv( 'CP1252', 'UTF-8', $string);
        }
    }

    function mk5() {
        $handle = fopen ("./settings/database.php", "rb");
        $cont = fread ($handle, 8192);
        fclose ($handle);

        $activate = "dbu_act = \"5\";\r\n\r\n\$lock = \"1\";";
        $c2 = preg_replace ("/dbu_act = \"1\";/i", $activate, $cont);
        $c2 = preg_replace ("/db1_slv = \"1\"/i", "db1_slv = \"0\"", $c2);
        $c2 = preg_replace ("/db2_slv = \"1\"/i", "db2_slv = \"0\"", $c2);
        $c2 = preg_replace ("/db3_slv = \"1\"/i", "db3_slv = \"0\"", $c2);
        $c2 = preg_replace ("/db4_slv = \"1\"/i", "db4_slv = \"0\"", $c2);
        $c2 = preg_replace ("/db5_slv = \"0\"/i", "db5_slv = \"1\"", $c2);

        $handle = fopen ("./settings/database.php", "wb");
        fwrite ($handle, $c2);
        fclose ($handle);
        exit;
    }

    function footer() {
        global $db_con, $include_dir, $add_url, $most_pop, $mysql_table_prefix;

        echo "<p class=\"stats\"><a href=\"http://www.sphider-plus.eu\" title=\"Link: Visit Sphider-plus site in new window\" target=\"rel\">Visit&nbsp;<img class=\"mid\" src=\"$include_dir/images/sphider-plus-logo.gif\" alt=\"Visit Sphider site in new window\" height=\"39\" width=\"42\" /> Sphider-plus</a></p>";
    }

    function error_handler($errNo, $errStr, $errFile, $errLine){
        if(ob_get_length()) ob_clean();             // clear any output that has already been generated

        $error_message = 'ERRNO: ' . $errNo . chr(10) .
                    'TEXT: ' . $errStr . chr(10) .
                    'LOCATION: ' . $errFile .
                    ', line ' . $errLine;
        echo $error_message;
        exit;       // stop executing any script
    }


    class resize{
        // *** Class variables
        private $image;
        private $width;
        private $height;
        private $imageResized;

        function __construct($fileName){
            // *** Open up the file
            $this->image = $this->openImage($fileName);

            // *** Get width and height
            $this->width  = imagesx($this->image);
            $this->height = imagesy($this->image);
        }

        private function openImage($file){
            // *** Get extension
            $extension = strtolower(strrchr($file, '.'));

            switch($extension)
            {
                case '.jpg':
                case '.jpeg':
                    $img = @imagecreatefromjpeg($file);
                    break;
                case '.gif':
                    $img = @imagecreatefromgif($file);
                    break;
                case '.png':
                    $img = @imagecreatefrompng($file);
                    break;
                default:
                    $img = false;
                    break;
            }
            return $img;
        }

        public function resizeImage($newWidth, $newHeight, $option="auto"){
            // *** Get optimal width and height - based on $option
            $optionArray = $this->getDimensions($newWidth, $newHeight, $option);

            $optimalWidth  = $optionArray['optimalWidth'];
            $optimalHeight = $optionArray['optimalHeight'];


            // *** Resample - create image canvas of x, y size
            $this->imageResized = imagecreatetruecolor($optimalWidth, $optimalHeight);
            imagecopyresampled($this->imageResized, $this->image, 0, 0, 0, 0, $optimalWidth, $optimalHeight, $this->width, $this->height);


            // *** if option is 'crop', then crop too
            if ($option == 'crop') {
                $this->crop($optimalWidth, $optimalHeight, $newWidth, $newHeight);
            }
        }

        private function getDimensions($newWidth, $newHeight, $option){

            switch ($option)
            {
                case 'exact':
                    $optimalWidth = $newWidth;
                    $optimalHeight= $newHeight;
                    break;
                case 'portrait':
                    $optimalWidth = $this->getSizeByFixedHeight($newHeight);
                    $optimalHeight= $newHeight;
                    break;
                case 'landscape':
                    $optimalWidth = $newWidth;
                    $optimalHeight= $this->getSizeByFixedWidth($newWidth);
                    break;
                case 'auto':
                    $optionArray = $this->getSizeByAuto($newWidth, $newHeight);
                    $optimalWidth = $optionArray['optimalWidth'];
                    $optimalHeight = $optionArray['optimalHeight'];
                    break;
                case 'crop':
                    $optionArray = $this->getOptimalCrop($newWidth, $newHeight);
                    $optimalWidth = $optionArray['optimalWidth'];
                    $optimalHeight = $optionArray['optimalHeight'];
                    break;
            }
            return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
        }

        private function getSizeByFixedHeight($newHeight){
            $ratio = $this->width / $this->height;
            $newWidth = $newHeight * $ratio;
            return $newWidth;
        }

        private function getSizeByFixedWidth($newWidth){
            $ratio = $this->height / $this->width;
            $newHeight = $newWidth * $ratio;
            return $newHeight;
        }

        private function getSizeByAuto($newWidth, $newHeight){
            if ($this->height < $this->width)
            // *** Image to be resized is wider (landscape)
            {
                $optimalWidth = $newWidth;
                $optimalHeight= $this->getSizeByFixedWidth($newWidth);
            }
            elseif ($this->height > $this->width)
            // *** Image to be resized is taller (portrait)
            {
                $optimalWidth = $this->getSizeByFixedHeight($newHeight);
                $optimalHeight= $newHeight;
            }
            else
            // *** Image to be resizerd is a square
            {
                if ($newHeight < $newWidth) {
                    $optimalWidth = $newWidth;
                    $optimalHeight= $this->getSizeByFixedWidth($newWidth);
                } else if ($newHeight > $newWidth) {
                    $optimalWidth = $this->getSizeByFixedHeight($newHeight);
                    $optimalHeight= $newHeight;
                } else {
                    // *** Sqaure being resized to a square
                    $optimalWidth = $newWidth;
                    $optimalHeight= $newHeight;
                }
            }

            return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
        }

        private function getOptimalCrop($newWidth, $newHeight){

            $heightRatio = $this->height / $newHeight;
            $widthRatio  = $this->width /  $newWidth;

            if ($heightRatio < $widthRatio) {
                $optimalRatio = $heightRatio;
            } else {
                $optimalRatio = $widthRatio;
            }

            $optimalHeight = $this->height / $optimalRatio;
            $optimalWidth  = $this->width  / $optimalRatio;

            return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
        }

        private function crop($optimalWidth, $optimalHeight, $newWidth, $newHeight){
            // *** Find center - this will be used for the crop
            $cropStartX = ( $optimalWidth / 2) - ( $newWidth /2 );
            $cropStartY = ( $optimalHeight/ 2) - ( $newHeight/2 );

            $crop = $this->imageResized;
            //imagedestroy($this->imageResized);

            // *** Now crop from center to exact requested size
            $this->imageResized = imagecreatetruecolor($newWidth , $newHeight);
            imagecopyresampled($this->imageResized, $crop , 0, 0, $cropStartX, $cropStartY, $newWidth, $newHeight , $newWidth, $newHeight);
        }

        public function saveImage($savePath, $imageQuality="100"){
            // *** Get extension
            $extension = strrchr($savePath, '.');
            $extension = strtolower($extension);

            switch($extension)
            {
                case '.jpg':
                case '.jpeg':
                    if (imagetypes() & IMG_JPG) {
                        imagejpeg($this->imageResized, $savePath, $imageQuality);
                    }
                    break;

                case '.gif':
                    if (imagetypes() & IMG_GIF) {
                        imagegif($this->imageResized, $savePath);
                    }
                    break;

                case '.png':
                    // *** Scale quality from 0-100 to 0-9
                    $scaleQuality = round(($imageQuality/100) * 9);

                    // *** Invert quality setting as 0 is best, not 9
                    $invertScaleQuality = 9 - $scaleQuality;

                    if (imagetypes() & IMG_PNG) {
                        imagepng($this->imageResized, $savePath, $invertScaleQuality);
                    }
                    break;

                default:
                    // *** No extension - No save.
                    break;
            }
            imagedestroy($this->imageResized);
        }
    }

    function resample($img, $width, $height){
        // Set a maximum height and width
        $width = 400;
        $height = 400;

        // Content type
        header('Content-type: image/jpeg');

        // Get new dimensions
        list($width_orig, $height_orig) = getimagesize($img);

        $ratio_orig = $width_orig/$height_orig;

        if ($width/$height > $ratio_orig) {
            $width = $height*$ratio_orig;
        } else {
            $height = $width/$ratio_orig;
        }

        // Resample
        $image_p = imagecreatetruecolor($width, $height);
        $image = imagecreatefromjpeg($img);
        imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
        // Output
        //imagejpeg($image_p, null, 100);
        return ($image_p);
    }

    function del_secchars($file){
        global $cn_seg, $jp_seg, $del_secchars, $del_seccharin;

        if ($jp_seg == '1' || $cn_seg == '1' && $del_secchars==1 || $del_seccharin ==1) {
            //      Delete additional characters (as word separator) like dots, question marks, colons etc. (characters 1-49 in original Chinese dictionary)
            $file = preg_replace ('/。|，|〿|；|：|？|＿|…|—|·|ˉ|ˇ|¨|‘|’|“|‿|々|～|‖|∶|＂|＇|｀|｜|〃|〔|〕|〈|〉|《|》|「|〿|『|〿|．|〖|〗|〿|】|（|）|［|］|｛|ｿ/', " ", $file);
            $file = preg_replace('/ï¼›|¡£|£¬|¡¢|£»|£º|£¿|£¡|¡­|¡ª|¡¤|¡¥|¡¦|¡§|¡®|¡¯|¡°|¡±|¡©|¡«|¡¬|¡Ã|£¢|£§|£à|£ü|¡¨|¡²|¡³|¡´|¡µ|¡¶|¡·|¡¸|¡¹|¡º|¡»|£®|¡¼|¡½|¡¾|¡¿|£¨|£©|£Û|£ÿ|£û|£ý|°¢/', " ", $file);
            $file = preg_replace('/＿|＆|，|<|：|；|・|\(|\)/', " ", $file);
        }

        if ($del_secchars == '1') {
            //    kill  special characters at the end of words
            $file = preg_replace('/— |\]. |\%\? |\"\. |, |.\'|\. |\.\. |\.\.\. |! |\? |" |: |\) |\), |\)\. |】 |） |？,|？ |！ |！|。,|。 |„ |“ |” |”|”&nbsp;|» |\.»|;»|:»|,»|\.»|·»|«|« |», |»\. |\.” |,”|;” |”\. |”, |‿|、|）|·|;|\] |\} |_, |_ |”\)\. |.\"> |\"> |> |\)|&lt; |\%, |\%. |\%.\" |\% |\+\+ |\+ |\* |\# |\~ /', " ", $file);
            //    kill special characters in front of words
            $file = preg_replace('/ —| \(\"| \(\$| \(\@| \@| \[| "| \(| „|„| “|（| «| 【| ‿| （| \(“|“| ©| ®| ™| –| <| \/|\/| \\"| \.| \^| &gt;| \$| \£| \"\(| \+| \*| \.| \#| \%| \~| \{/', " ", $file);
        }

        if ($del_seccharin == '1'){
            $file = del_secintern($file);
        }
        return $file;
    }

    function del_secintern($file) {
        //    kill separating characters inside of words
        //$file = preg_replace('/・/', " ", $file);
        //$file = preg_replace('/=|"|\<|\>|\_\#|\+|%|&|_|\(|\)|\.\.\.|\.\.|\//', " ", $file);       //  light version
        $file = preg_replace('/=|"|\<|\>|\]|\[|\(|\)|\_\#|\+|%|&|_|\(|\)|\.|\.\.\.|\.\.|\/|=\\":\/\|\"|・|\/\"|\@/', " ", $file);

        return $file;
    }

    function split_words($file) {
        global $div_all, $div_hyphen;

        $all = '';

        if ($div_hyphen) {
            preg_match_all("/[\d\w\.,'‘‘´`’’_]+[-]+[\d\w\.,'‘‘´`’’_\?\!]+/si", $file, $regs, PREG_SET_ORDER); // get hyphpen combined words
            $file = preg_replace("/-/", " ", $file); //  divide words into their basic parts
        }

        if ($div_all){
            preg_match_all("/[\d\w]+[.|,|:|'|‘|‘|´|`|’|’|\-|_\/][\d\w\.,'-_\?\!]+/si", $file, $regs, PREG_SET_ORDER); // get dot, comma and quote combined words
            $file = preg_replace("/-|\.|,|:|'|‘|‘|´|`|’|’|\//", " ", $file);
        }

        foreach ($regs as $value) {
            $all .= " ".$value[0]."";   // collect all combined words

        }

// JFIELD doesn't think you should add the words back after splitting them
//        $file .= "".$all." ";           //  add the combined words to $file
        return ($file);
    }

    //  try to open a file my means of cURL library
    function curl_open($url) {
        $result = '';
        $curl_handle = curl_init();
        curl_setopt($curl_handle,CURLOPT_URL,$url);
        curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
        curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
        $result = curl_exec($curl_handle);
        curl_close($curl_handle);
        return ($result);
    }

    function translit_el($string) {

        //  1. replace special blanks with " "
        $string = str_replace("&nbsp;", " ", $string);

        //  2. replace Latin 's' at the end of multiple query words with the Greek 'ς'
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

        //  3. translit some specialities
        $string = preg_replace('/TH/', "Θ", $string);
        $string = preg_replace('/th/', "θ", $string);

        $string = preg_replace('/CH/', "Χ", $string);
        $string = preg_replace('/ch/', "χ", $string);

        $string = preg_replace('/PS/', "Ψ", $string);
        $string = preg_replace('/ps/', "ψ", $string);

        $string = preg_replace('/PH/', "Φ", $string);
        $string = preg_replace('/ph/', "φ", $string);


        //  4. translit upper case letters
        $en = array("A","V","C","G","D","E","F","Z","I","K","L","M","N","X","O","P","Q","R","S","T","W","Y");
        $el = array("Α","Β","Ξ","Γ","Δ","Ε","Φ","Ζ","Ι","Κ","Λ","Μ","Ν","Ξ","Ο","Π","Θ","Ρ","Σ","Τ","Ω","Ψ");
        //$el = array("Α","Β","Ξ","Δ","Ε","Γ","Η","Ι","Κ","Λ","Μ","Ν","Χ","Ο","Π","Θ","Ρ","Σ","Τ","Υ","Ω","Φ","Ο","Ψ","Ζ");
        $string = str_replace($en, $el, $string);

        //  5. translit lower case letters
        $en = array("a","b","v","c","g","d","e","f","h","z","i","k","l","m","n","x","o","p","q","r","s","t","y","u","w","ō","y","ī");
        $el = array("α","β","β","ξ","γ","δ","ε","φ","η","ζ","ι","κ","λ","μ","ν","ξ","ο","π","θ","ρ","σ","τ","υ","υ","ω","ω","ψ","η");
        //$el = array("α","β","ξ","δ","ε","γ","η","ι","κ","λ","μ","ν","ο","π","θ","ρ","σ","τ","υ","φ","ω","χ","ψ","ζ");
        $string = str_replace($en, $el, $string);

        $string = str_replace("<uλ>", "<ul>", $string);
        $string = str_replace("<λι>. ς ς", "<li>. . .", $string);
        $string = str_replace("<στρονγ>. ς ς", "<strong>. . .", $string);
        $string = str_replace("</στρονγ>. ς ς", "</strong>. . .", $string);
        $string = str_replace("<λι>", "<li>", $string);
        $string = str_replace("<στρονγ>", "<strong>", $string);
        $string = str_replace("ς ς .", " . . .", $string);
        $string = str_replace("ς ς ς </λι>", " . . .</li>", $string);
        $string = str_replace("</στρονγ>", "</strong>", $string);
        $string = str_replace("</λι>", "</li>", $string);
        $string = str_replace("</uλ>", "</ul>", $string);
        return $string;
    }

    //  replace UNICODE charset with MySQL equivalent
    function conv_mysqli($string) {
        $get = array(
        "utf-8",
        "big-5",
        "iso-8859-1",
        "iso-8859-2",
        "iso-8859-7",
        "ISO-8859-8",
        "ISO-8859-9",
        "iso-8859-13",
        "koi8-r",
        "koi8-u",
        "iso-646-se",
        "us-ascii",
        "euc-jp",
        "shift-jis",
        "cp-1251",
        "euc_kr",
        "gb-2312",
        "windows-1250",
        "ucs-2",
        "cp-852",
        "cp-866",
        "cp-1256",
        "cp-932",
        "euc-jp"
        );
        $out = array(
        "utf8",
        "big5",
        "latin1",
        "latin2",
        "greek",
        "hebrew",
        "latin7",
        "latin5",
        "koi8r",
        "koi8u",
        "swe7",
        "ascii",
        "ujis",
        "sjis",
        "cp1251",
        "euckr",
        "gb2312",
        "cp1250",
        "ucs2",
        "cp852",
        "cp866",
        "cp1256",
        "cp932",
        "eucjpms"
        );
        $mysql = str_ireplace($get, $out, $string);

        if ($mysql == $string) {
            $mysql = "utf8";
        }
        return $mysqli;
    }

    function remove_acc($string, $wild) {
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

        if ($type == "tol" && !$wild){    //  make tolerant by replacing vowels with %
            $string = rep_latvowels($string);
        }

        return $string;
    }

    //  replace Greek accents with their pure vowels
    function remove_acc_el($string, $wild) {
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

        if ($type == "tol" && !$wild){    //  make tolerant by replacing vowels with %
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


    function clear_folder($folder) {
        //  delete all thumbnails
        if ($dh = opendir("$folder/")) {
            while (($this_file = readdir($dh)) !== false) {
                @unlink("$folder/$this_file");
            }
            closedir($dh);
        }
        return;
    }

    function getStatistics() {
        global $db_con, $mysql_table_prefix, $debug;

        $stats = array();
        $stats['index'] = '';

        $keywordQuery       = "SELECT count(keyword_id) from ".$mysql_table_prefix."keywords";
        $linksQuery         = "SELECT count(url) from ".$mysql_table_prefix."links";
        $siteQuery          = "SELECT count(site_id) from ".$mysql_table_prefix."sites";
        $categoriesQuery    = "SELECT count(category_id) from ".$mysql_table_prefix."categories";
        $mediaQuery         = "SELECT count(media_id) from ".$mysql_table_prefix."media";

        $result = $db_con->query($keywordQuery);
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
            printf("<p>$keywordQuery</p>");
            exit;
        }
        if ($row = $result->fetch_array(MYSQLI_NUM)) {
            $stats['keywords']=$row[0];
        }
        $result = $db_con->query($linksQuery);
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
                printf("<p>$linksQuery</p>");
                exit;
            }
        if ($row = $result->fetch_array(MYSQLI_NUM)) {
            $stats['links']=$row[0];
        }
        $sql_query1 = "SELECT count(link_id) from ".$mysql_table_prefix."link_keyword";
        $result = $db_con->query ($sql_query1);
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
            printf("<p>$sql_query1</p>");
            exit;
        }
        if ($row = $result->fetch_array(MYSQLI_NUM)) {
            $stats['index']+=$row[0];
        }
        $result = $db_con->query($siteQuery);
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
            printf("<p>$site_Query</p>");
            exit;
        }
        if ($row = $result->fetch_array(MYSQLI_NUM)) {
            $stats['sites']=$row[0];
        }
        $result = $db_con->query($categoriesQuery);
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
                printf("<p>$categoriesQuery</p>");
                exit;
            }
        if ($row = $result->fetch_array(MYSQLI_NUM)) {
            $stats['categories']=$row[0];
        }
        $result = $db_con->query($mediaQuery);
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
            printf("<p>$mediaQuery</p>");
            exit;
        }
        if ($row = $result->fetch_array(MYSQLI_NUM)) {
            $stats['media']=$row[0];
        }

        return $stats;
    }

    function stem_word($word, $type) {
        global $debug, $stem_words, $stem_dir, $min_word_length, $common;

        //if ($debug == '2') echo "\r\n\r\n<br /> unstemmed: $word<br />\r\n";
        //  no stemming for too short words or words containing some special characters
        if (strlen($word) < $min_word_length || preg_match("/[\*\!:]|[0-9]/si", $word)) {
            return $word;
        }

        if ($stem_words == 'bg') {
            require_once "$stem_dir/bg_stem.php";
            $word1 = bg_stemmer::stem($word);
        }

        if ($stem_words == 'cz') {
            require_once "$stem_dir/cz_stem.php";
            $word1 = cz_stemmer::stem($word);
        }

        if ($stem_words == 'de') {
            require_once "$stem_dir/de_stem.php";
            $word1 = de_stemmer::stem($word);
        }

        if ($stem_words == 'el') {
            require_once "$stem_dir/el_stem.php";
            $stemmer = new el_stemmer();
            $word1 = $stemmer->stem($word);
        }

        if ($stem_words == 'en') {
            require_once "$stem_dir/en_stem.php";

            // JFIELD jfield
            // make all special characters ascii (for english only)
            // NOTE: to kill a word completely, return void
            setlocale(LC_CTYPE, 'en_GB');
            $word = iconv('UTF-8', 'ASCII//TRANSLIT', $word); // does most of the magic
            $word = preg_replace("/[^\w\s]/", "", $word); // clean up a few weird things,
                                                          // like umlauts becoming double quotes (!?)
            // END JFIELD end jfield

            $word1 = en_stemmer::stem($word);
            if ($word1 == "informal") {
                echo "<h1>'$word' - '$word1'</h1>";
                exit;
            }
        }

        if ($stem_words == 'es') {
            require_once "$stem_dir/es_stem.php";
            $word1 = es_stemmer::stem($word);
        }

        if ($stem_words == 'fi') {
            require_once "$stem_dir/fi_stem.php";
            $word1 = fi_stemmer::stem($word);
        }

        if ($stem_words == 'fr') {
            require_once "$stem_dir/fr_stem.php";
            $word1 = fr_stemmer::stem($word);
        }

        if ($stem_words == 'hu') {
            require_once "$stem_dir/hu_stem.php";
            $word1 = hu_stemmer::stem($word);
        }

        if ($stem_words == 'nl') {
            require_once "$stem_dir/nl_stem.php";
            $word1 = nl_stemmer::stem($word);
        }

        if ($stem_words == 'it') {
            require_once "$stem_dir/it_stem.php";
            $stemmer = new it_stemmer();
            $word1 = $stemmer->stem($word);
        }

        if ($stem_words == 'pt') {
            require_once "$stem_dir/pt_stem.php";
            $word1 = pt_stemmer::stem($word);
        }

        if ($stem_words == 'ru') {
            require_once "$stem_dir/ru_stem.php";
            $word1 = ru_stemmer::stem($word);
        }

        if ($stem_words == 'se') {
            require_once "$stem_dir/se_stem.php";
            $word1 = se_stemmer::stem($word);
        }

        //  Hopefully the stemmed word did not become too short
        //  and the stemming algorithm did not create a common word
// JFIELD doesn't think we should undo stemming for common words
// because that's fucking stupid - instead discard the whole word
        if (strlen($word1) < $min_word_length || $common[$word1]) {
            return; 
        }

        //if ($debug == '2') echo "\r\n\r\n<br /> &nbsp;&nbsp;&nbsp;stemmed: $word<br />\r\n";
        return $word1;

    }

    function optimize($clear) {
        global $debug, $db_con, $mysql_table_prefix, $debug;

        $sql_query = "SHOW TABLE STATUS LIKE '$mysql_table_prefix%'";
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
        set_time_limit(1800);   //      increase timeout
        $i      = 0;
        $res    = '';

        while ($row = $result->fetch_array(MYSQLI_NUM)) {
            $db_con->query("CHECK TABLE $row[0]") or die("<body onload='JumpBottom()'><br /><center><span class='warn bd'>Unable to check table '$row[0]'.</span><br /><br /></center>\n</body>\n</html>");
            $db_con->query("REPAIR TABLE $row[0]") or die("<body onload='JumpBottom()'><br /><center><span class='warn bd'>Unable to repair table '$row[0]'.</span><br /><br /></center>\n</body>\n</html>");
            $db_con->query("OPTIMIZE TABLE $row[0]") or die("<body onload='JumpBottom()'><br /><center><span class='warn bd'>Unable to optimize table '$row[0]'.</span><br /><br /></center>\n</body>\n</html>");
            if ($clear == "1") {
                $res = $db_con->query("FLUSH TABLE $row[0]");
            }
            $i++;
        }
        if (!$res) {    //  if FLUSH TABLE was not accepted
            echo "
            <br /><center><span class='warnadmin cntr'><strong>Attention:</strong> Unable to flush all database tables.
            <br /><br />
            Because of missing MySQL rights, database repair could not be completed.
            <br />
            Table 'FLUSH' usually is forbidden on Shared Hosting servers.</span>
            <br /><br /><br />
        ";
        }
        return($i);
    }

    // Database1-5 connection
    function db_connect($mysql_host, $mysql_user, $mysql_password, $database) {

        $db_con = new mysqli($mysql_host, $mysql_user, $mysql_password, $database);
        /* check connection */
        if ($db_con->connect_errno) {
            printf("<p><span class='red'>&nbsp;MySQL Connect failed: %s\n&nbsp;<br /></span></p>", $db_con->connect_error);

        }
        
        if (!$db_con->connect_errno) {
            /* define character set to utf8 */
            if (!$db_con->set_charset("utf8")) {
                printf("Error loading character set utf8: %s\n", $db_con->error);

                /* Print current character set */
                $charset = $db_con->character_set_name();
                printf ("<br />Current character set is: %s\n", $charset);

                $db_con->close();
                exit;
            }
        }

        return ($db_con);
    }

?>
