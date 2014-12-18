<?PHP
    class odt_reader{
        public function odt_unzip($file, $save = false){
            if(!function_exists('zip_open'))
            {
                die('NO ZIP FUNCTIONS DETECTED. Do you have the PECL ZIP extensions loaded?');
            }
            if($zip = zip_open($file))
            {
                while ($zip_entry = zip_read($zip))
                {
                    $filename = zip_entry_name($zip_entry);
                    if(zip_entry_name($zip_entry) == 'content.xml' and zip_entry_open($zip, $zip_entry, "r"))
                    {
                        $content = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                        zip_entry_close($zip_entry);
                    }
                    if(preg_match('Pictures/', $filename) and !preg_match('Object', $filename)  and zip_entry_open($zip, $zip_entry, "r"))
                    {
                        $img[$filename] = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                        zip_entry_close($zip_entry);
                    }
                }
                if(isset($content)){
                    if($save == false){
                        return array($content, $img);

                    }else{
                        file_put_contents('content.xml', $content);
                        if(is_array($img)){
                            if(!is_dir('Pictures')){
                                mkdir('Pictures');
                            }
                            foreach($img as $key => $val){
                                file_put_contents($key, $val);
                            }
                        }
                    }
                }
            }
        }

        public function odt_convert($content, $ver){
            if(strip_tags($content) == $content and preg_match('/\.xml/', $content)){
                $data = file_get_contents($content);
            }else{
                $data = $content;
            }
            if($ver == 1){
                if(preg_match('/table\:table/', $data)){
                    $data = str_replace('<table:table', '<text:p text:style-name="RKRK"><table:table', $data);
                    $data = str_replace('</table:table>', '</table:table></text:p>', $data);
                    $data = preg_replace('#<table:table(.*?)</table:table>#es', "base64_encode('\\1')", $data);
                }
                // get styles
                preg_match_all('#<odt_reader:automatic-styles>(.*?)</odt_reader:automatic-styles>#es', $data, $style);
                // get data
                preg_match_all('#<text:p text:style-name="([a-z A-Z_0-9]*)">(.*?)</text:p>#es', $data, $text);
                // get the XML parts
                $styles = $style[0];
                $texts1 = $text[1];
                $texts2 = $text[2];
                // take out the trash
                unset($data);
                unset($style);
                unset($text);
                // make xml strings
                $styles = implode('', $styles);
                $styles = strtr($styles, array(':' => '_', '-' => '_', '>' => ">\n"));
                $xml = simplexml_load_string($styles);
                $iter = 0;
                ob_start();
                foreach($xml->style_style as $style)
                {
                    // MAKE a CSS definition
                    if($xml->style_style[$iter]['style_family'] == 'paragraph' or $xml->style_style[$iter]['style_family'] == 'text'){
                        echo "\n.".$xml->style_style[$iter]['style_name'].' {
                            font-family: '.$xml->style_style[$iter]->style_properties['style_font_name'].', verdana;
                            font-size: '.$xml->style_style[$iter]->style_properties['fo_font_size'].';';
                        if(isset($xml->style_style[$iter]->style_properties['fo_color'])){
                            echo "\ncolor: ".$xml->style_style[$iter]->style_properties['fo_color'].';';
                        }
                        if(isset($xml->style_style[$iter]->style_properties['fo_text_align'])){
                            echo "\ntext-align: ".$xml->style_style[$iter]->style_properties['fo_text_align'].';';
                        }
                        if(isset($xml->style_style[$iter]->style_properties['fo_background_color'])){
                            echo "\nbackground-color: ".$xml->style_style[$iter]->style_properties['fo_background_color'].';';
                        }
                        if($xml->style_style[$iter]->style_properties['style_text_underline'] == 'single'){
                            echo "\ntext-decoration: underline;";
                        }
                        echo "\nfont-weight: ".$xml->style_style[$iter]->style_properties['fo_font_weight'].';
                            font-style: '.$xml->style_style[$iter]->style_properties['fo_font_style'].'
                            } ';
                    }elseif(isset($xml->style_style[$iter]->style_properties['style_horizontal_pos'])){
                        echo "\n.".$xml->style_style[$iter]['style_name'].' {';
                        echo "\ntext-align: ".$xml->style_style[$iter]->style_properties['style_horizontal_pos'].';';
                        echo '}';
                    }
                    $iter++;
                }
                $r_styles = ob_get_contents();
                ob_end_clean();
                // take out the trash
                unset($xml);
                unset($styles);
                unset($iter);
                // Show the document
                ob_start();
                foreach($texts1 as $key => $val){
                    //image
                    if(pregh_match('/xlink\:href=\"#Pictures/([a-z .A-Z_0-9]*)/', $texts2[$key], $tab1) and preg_match('/draw\:style-name=\"([a-zA-Z_0-9]*)\"/', $texts2[$key], $tab2)){
                        echo '<div class="'.$tab2[1].'"><img src="Pictures/'.$tab1[1].'"></div>';
                    }
                    elseif($val == 'RKRK'){
                        $table = base64_decode($texts2[$key]);
                        $table = stripslashes($table);
                        $table = strtr($table, array('</table:table>' => '</table>', '<table:table-row>' => '<tr>', '</table:table-row>' => '</tr>', '</table:table-cell>' => '</td>', '</table:table-header-rows>' => '', '<table:table-header-rows>' => '', '>' => ">\n", '</text:p>'  => ''));

                        preg_match_all('#table:name="([a-z A-Z_0-9]*)" table:style-name="([a-z A-Z_0-9]*)">#es', $table, $repl);
                        foreach($repl[0] as $val){
                            $table = str_replace($val, '<table border="1"><tr><td>', $table);
                        }
                        preg_match_all('#<text:p text:style-name="([a-z A-Z_0-9]*)">#es', $table, $repl);
                        foreach($repl[0] as $key => $val){
                            $table = str_replace($val, '', $table);
                        }
                        preg_match_all('#<table:table-column (.*?)">#es', $table, $repl);
                        foreach($repl[0] as $val){
                            $table = str_replace($val, '', $table);
                        }
                        preg_match_all('#<table:table-cell table:style-name="([\.a-z A-Z_0-9]*)" table:value-type="([a-z A-Z_0-9]*)">#es', $table, $repl);
                        foreach($repl[0] as $val){
                            $table = str_replace($val, '<td>', $table);
                        }
                        if(preg_match('/xlink\:href=\"Pictures/([a-z .A-Z_0-9]*)/', $table, $tab1)){
                            $table = str_replace('<draw:image xlink:href="Pictures/'.$tab1[1].'" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/>', '<img src="Pictures/'.$tab1[1].'">', $table);
                        }

                        echo '<BR>'.$table.'<BR>';
                        // Text
                    } else {
                        echo '<div class="'.$val.'">'.$texts2[$key].'</div>';
                    }
                }
                $r_text = ob_get_contents();
                ob_end_clean();
                unset($texts1);
                unset($texts2);
                unset($tab1);
                unset($tab2);
            }elseif($ver == 2){
                // we have tables, encode it here so all <text:p in them don't get preg_match_all few lines later
                if(preg_match('/table\:table/', $data)){
                    $data = str_replace('<table:table', '<text:p text:style-name="RKRK"><table:table', $data);
                    $data = str_replace('</table:table>', '</table:table></text:p>', $data);
                    $data = preg_replace('#<table:table(.*?)</table:table>#es', "base64_encode('\\1')", $data);
                }
                // get styles
                preg_match_all('#<odt_reader:automatic-styles>(.*?)</odt_reader:automatic-styles>#es', $data, $style);
                // get data
                preg_match_all('#<text:p text:style-name="([a-z A-Z_0-9]*)">(.*?)</text:p>#es', $data, $text);

                // get the XML parts
                $styles = $style[0];
                $texts1 = $text[1];
                $texts2 = $text[2];
                // take out the trash
                unset($data);
                unset($style);
                unset($text);
                // make xml strings
                $styles = implode('', $styles);
                $styles = strtr($styles, array(':' => '_', '-' => '_', '>' => ">\n"));
                $xml = simplexml_load_string($styles);
                $iter = 0;
                ob_start();
                foreach($xml->style_style as $style){
                    // MAKE a CSS definition
                    if( $xml->style_style[$iter]['style_family'] == 'paragraph' or  $xml->style_style[$iter]['style_family'] == 'text'){
                        echo "\n.".$xml->style_style[$iter]['style_name'].' {
                            font-family: '.$xml->style_style[$iter]->style_text_properties['style_font_name'].', verdana;';
                        if(isset($xml->style_style[$iter]->style_text_properties['fo_font_size'])){
                            echo 'font-size: '.$xml->style_style[$iter]->style_text_properties['fo_font_size'].';';
                        }
                        if(isset($xml->style_style[$iter]->style_text_properties['fo_color'])){
                            echo "\ncolor: ".$xml->style_style[$iter]->style_text_properties['fo_color'].';';
                        }
                        if(isset($xml->style_style[$iter]->style_paragraph_properties['fo_text_align'])){
                            echo "\ntext-align: ".$xml->style_style[$iter]->style_paragraph_properties['fo_text_align'].';';
                        }
                        if(isset($xml->style_style[$iter]->style_paragraph_properties['fo_background_color'])){
                            echo "\nbackground-color: ".$xml->style_style[$iter]->style_paragraph_properties['fo_background_color'].';';
                        }elseif(isset($xml->style_style[$iter]->style_text_properties['fo_background_color'])){
                            echo "\nbackground-color: ".$xml->style_style[$iter]->style_text_properties['fo_background_color'].';';
                        }
                        if($xml->style_style[$iter]->style_text_properties['style_text_underline_style'] == 'solid'){
                            echo "\ntext-decoration: underline;";
                        }
                        echo "\nfont-weight: ".$xml->style_style[$iter]->style_text_properties['fo_font_weight'].';';
                        if(isset($xml->style_style[$iter]->style_text_properties['fo_font_style'])){
                            echo 'font-style: '.$xml->style_style[$iter]->style_text_properties['fo_font_style'].'';
                        }
                        echo '} ';
                    }elseif(isset($xml->style_style[$iter]->style_graphic_properties['style_horizontal_pos'])){
                        echo "\n.".$xml->style_style[$iter]['style_name'].' {';
                        echo "\ntext-align: ".$xml->style_style[$iter]->style_graphic_properties['style_horizontal_pos'].';';
                        echo '}';
                    }
                    $iter++;
                }
                $r_styles = ob_get_contents();
                ob_end_clean();
                // take out the trash
                unset($xml);
                unset($styles);
                unset($iter);
                // Show the document
                ob_start();
                foreach($texts1 as $key => $val){
                    //image
                    if(preg_match('/xlink\:href=\"Pictures\/([a-z .A-Z_0-9]*)/', $texts2[$key], $tab1) and preg_match('/draw\:style-name=\"([a-zA-Z_0-9]*)\"/', $texts2[$key], $tab2)){
                        echo '<div class="'.$tab2[1].'"><img src="Pictures/'.$tab1[1].'"></div>';
                    } elseif ($val == 'RKRK'){
                        $table = base64_decode($texts2[$key]);
                        $table = stripslashes($table);
                        $table = strtr($table, array('</table:table>' => '</table>', '<table:table-row>' => '<tr>', '</table:table-row>' => '</tr>', '</table:table-cell>' => '</td>', '</table:table-header-rows>' => '', '<table:table-header-rows>' => '', '>' => ">\n", '</text:p>'  => ''));

                        preg_match_all('#table:name="([a-z A-Z_0-9]*)" table:style-name="([a-z A-Z_0-9]*)">#es', $table, $repl);
                        foreach($repl[0] as $val){
                            $table = str_replace($val, '<table border="1"><tr><td>', $table);
                        }
                        preg_match_all('#<text:p text:style-name="([a-z A-Z_0-9]*)">#es', $table, $repl);
                        foreach($repl[0] as $key => $val){
                            $table = str_replace($val, '', $table);
                        }
                        preg_match_all('#<table:table-column (.*?)">#es', $table, $repl);
                        foreach($repl[0] as $val){
                            $table = str_replace($val, '', $table);
                        }
                        preg_match_all('#<table:table-cell table:style-name="([\.a-z A-Z_0-9]*)" odt_reader:value-type="([a-z A-Z_0-9]*)">#es', $table, $repl);
                        foreach($repl[0] as $val){
                            $table = str_replace($val, '<td>', $table);
                        }
                        if(preg_match('/xlink\:href=\"Pictures/([a-z .A-Z_0-9]*)/', $table, $tab1)){
                            $table = str_replace('<draw:image xlink:href="Pictures/'.$tab1[1].'" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/>', '<img src="Pictures/'.$tab1[1].'">', $table);
                        }
                        echo '<BR>'.$table.'<BR>';
                        //  text
                    } else {

                        echo '<div class="'.$val.'">'.$texts2[$key].'</div>';
                    }
                }

                $r_text = ob_get_contents();
                ob_end_clean();
                unset($texts1);
                unset($texts2);
                unset($tab1);
                unset($tab2);
            }
            preg_match_all('#<text:span text:style-name="([A-Z 0-9]*)">#es', $r_text, $repl);
            foreach($repl[0] as $val){
                $r_text = str_replace($val, '', $r_text);
            }

            $r_text  = str_replace('</text:span>', '', $r_text);

            if(isset($r_styles) and isset($r_text)){
                return array($r_styles, $r_text);
            }else{
                return false;
            }
        }

        public function odt_to_file($filename, $content, $ver){
            $x = odt_reader::odt_convert($content, $ver);
            $file = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body><style>'.$x[0].'</style>'.$x[1].'</body></html>';
            if(is_array($x))
            {
                $wskaz = fopen($filename, "w");
                fwrite($wskaz, $file);
                fclose($wskaz);
                return true;
            }
            else
            {
                return false;
            }
        }

        public function odt_read($content, $ver){
            $x = odt_reader::odt_convert($content, $ver);
            return '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body><style>'.$x[0].'</style>'.$x[1].'</body></html>';
        }

        public function abi_read($filename, $patch = './'){
            $data = file_get_contents($filename);
            // tekst
            preg_match_all('#<section(.*?)>(.*?)</section>#es', $data, $text);
            $texts = $text[2];
            unset($text);
            // grafika
            preg_match_all('#<data>(.*?)</data>#es', $data, $extra);
            $extras = $extra[0];
            unset($extra);
            unset($data);

            $texts = implode('', $texts);
            if(is_array($extras)){
                // jeżeli są grafiki /załączniki to pojedź dalej
                $extras = implode('', $extras);
                $xml = simplexml_load_string($extras);
                $iter = 0;
                if(is_object($xml->d)){
                    foreach($xml->d as $d){
                        if(preg_match('/image\/([a-z]*)/', $xml->d['mime-type'], $tab)){
                            $filename = md5($xml->d[$iter]['name']);
                            $image_content = base64_decode($xml->d[$iter]);
                            $images[$filename] = $patch.$filename.'.'.$tab[1];
                            file_put_contents($patch.$filename.'.'.$tab[1], $image_content);
                            $iter++;
                        }
                    }
                    unset($iter);
                    // przerabiamy wywołania do grafik na img src
                    preg_match_all('#<image dataid="(.*?)" (.*?)"/>#es', $texts, $img);
                    foreach($img[1] as $key => $val){
                        $val = md5($val);
                        $texts = str_replace($img[0][$key], '<img src="'.$images[$val].'">', $texts);
                    }
                    unset($img);
                }
            }
            // tabelki
            preg_match_all('#<cell props="bot-attach:([0-9]*); left-attach:([0-9]*); right-attach:([0-9]*); top-attach:([0-9]*)">#es', $texts, $table);
            $tr = 0;
            foreach($table[0] as $key => $val){
                if($tr != $table[4][$key]){
                    $texts = str_replace($val, '</tr><tr><td>', $texts);
                    $tr = $table[4][$key];
                }else{
                    $texts = str_replace($val, '<td>', $texts);
                }
            }
            unset($table);
            unset($tr);
            // wypunktowanie
            preg_match_all('#<c props="([a-zA-Z0-9 _;:\-]*) list-style:Bullet List; ([a-zA-Z0-9 _;:\-]*)">(.*?)</c>#es', $texts, $li);
            foreach($li[0] as $key => $val){
                $texts = str_replace($val, '<LI>'.$li[3][$key].'</LI>', $texts);
            }

            preg_match_all('#<p level="(.*?) list-style:Numbered List; ([a-zA-Z0-9 _;:\-]*)">(.*?)</p>#es', $texts, $li2);
            foreach($li2[0] as $key => $val){
                $texts = str_replace($val, '<LI>'.$li2[3][$key].'</LI>', $texts);
            }
            unset($li2);
            // czyszczenie, poprawki
            $texts= preg_replace('#<field (.*?)></field>#es', '', $texts);
            $texts = strtr($texts, array('<p style="Normal"></p>' => '', '<c props' => '<div style', '</c>' => '</div>', 'props="' => 'style="', '</cell>' => '</td>', '<table>' => '<table border="1" width="100%"><tr>', '</table>' => '</tr></table>', '</field>' => ''));
            return '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>'.$texts;
        }
    }
?>