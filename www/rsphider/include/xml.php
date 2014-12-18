<?php

    //  build XML output array for media results
    function media_xml($media_results, $media_count, $query, $time) {
        global $clear;

        error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE & ~E_STRICT);
        $xml_result = array();
        $now        = date('Y-m-d h:i:s A');   //  current date and time
        $ip         = $_SERVER['REMOTE_ADDR']; //   Users calling IP

        if(intval($ip)>0){
            $hostname = @gethostbyaddr($ip);    //  very slow! comment this row, if not required
            if ($hostname == $ip) {
                $hostname = "Unknown host" ;
            }
        } else {
            $hostname = "Unknown host" ; // a bad address.
        }
        // prepare the XML media output
        $xml_result['query']            = $query;
        $xml_result['ip']               = $ip;
        $xml_result['host_name']        = $hostname;
        $xml_result['query_time']       = $now;
        $xml_result['consumed']         = $time;
        $xml_result['total_results']    = $media_count;

        $i = 0;
        while ($i < $media_count) {
            $xml_result[$i]['num']    =  $i+1 ;
            $xml_result[$i]['type']   = $media_results[$i]['6'];
            $xml_result[$i]['url']    = $media_results[$i]['2'];
            $xml_result[$i]['link']   = $media_results[$i]['3'];
            $xml_result[$i]['title']  = $media_results[$i]['5'];
            if ($media_results[$i]['7']) $xml_result[$i]['x_size'] = $media_results[$i]['7'];
            if ($media_results[$i]['8']) $xml_result[$i]['y_size'] = $media_results[$i]['8'];

            //  if EXIF info should be stored in XML output file, uncomment the following row
            //if ($media_results[$i]['12']) $xml_result[$i]['exif']   = mysql_real_escape_string($media_results[$i]['12']);

            $i++;
        }
        convert_xml($xml_result, 'media');

        if ($clear == 1) {
            $xml_result = array();
        }

        return ;
    }

    //  build XML output array for text results containing all available results
    function text_xml($text_results, $text_count, $query, $time){
        global $db_con, $clear;

        error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE & ~E_STRICT);
        foreach ($query['hilight'] as $q) {
            $q_string .= "".$q." ";
        }

        $now    = date('Y-m-d h:i:s A');   //  current date and time
        $ip     = $_SERVER['REMOTE_ADDR']; //  Users calling IP

        if(intval($ip)>0){
            $hostname = @gethostbyaddr($ip);    //  very slow! comment this row, if not required
            if ($hostname == $ip) {
                $hostname = "Unknown host" ;
            }
        } else {
            $hostname = "Unknown host" ; // a bad address.
        }

        // prepare the XML text output
        $xml_result  = array();
        $xml_result['query']            = $q_string;
        $xml_result['ip']               = $ip;
        $xml_result['host_name']        = $hostname;
        $xml_result['query_time']       = $now;
        $xml_result['consumed']         = $time;
        $xml_result['total_results']    = $text_count;

        $i = 0;
        while ($i < $text_count) {
            $xml_result[$i]['num']          = $i+1 ;
            $xml_result[$i]['weight']       = $text_results[$i]['weight'];
            $xml_result[$i]['url']          = $text_results[$i]['url'];
            $xml_result[$i]['title']        = $db_con->real_escape_string($text_results[$i]['title']);
            $xml_result[$i]['fulltxt']      = $db_con->real_escape_string($text_results[$i]['fulltxt']);
            $xml_result[$i]['size']         = $text_results[$i]['size'];
            $xml_result[$i]['domain_name']  = $text_results[$i]['domain_name'];

            $i++;
        }

        convert_xml($xml_result, 'text');

        if ($clear == 1) {
            $xml_result = array();
        }

        return ;
    }

    //  build XML output array for multiple link results, when searching for site:link
    function multiple_link_xml($num_rows, $res, $urlquery, $start_all){
        global $db_con, $clear;

        error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE & ~E_STRICT);
        $xml_result = array();
        $now        = date('Y-m-d h:i:s A');   //  current date and time
        $ip         = $_SERVER['REMOTE_ADDR']; //   Users calling IP

        if(intval($ip)>0){
            $hostname = @gethostbyaddr($ip);    //  very slow! comment this row, if not required
            if ($hostname == $ip) {
                $hostname = "Unknown host" ;
            }
        } else {
            $hostname = "Unknown host" ; // a bad address.
        }
        // prepare the XML media output
        $xml_result['query']            = $urlquery;
        $xml_result['ip']               = $ip;
        $xml_result['host_name']        = $hostname;
        $xml_result['query_time']       = $now;
        $xml_result['consumed']         = round(getmicrotime() - $start_all, 3);
        $xml_result['total_results']    = $num_rows;

        $i = 0;
        while ($i < $num_rows) {
            $xml_result[$i]['num']          = $i+1 ;
            $xml_result[$i]['link']         = $res["$i"]["url"];
            $xml_result[$i]['indexdate']    = $res["$i"]["indexdate"];

            $i++;
        }

        convert_xml($xml_result, 'multiple_link');

        if ($clear == 1) {
            $xml_result = array();
        }
        return ;
    }

    //  build XML output array for link results
    function link_xml($result, $query, $urlquery, $start_all){
        global $db_con, $clear;

        $xml_result = array();
        $num_rows   = count($result);

        if ($num_rows == 1) {   //      No links found
            $xml_result['query'] = $query;
            $xml_result['time'] = $endtime;
            $xml_result['total_results'] = '1'; // only the calling URL is available
        } else {
            // prepare the XML media output
            $xml_result['query'] = $query;
            $xml_result['time'] =  round(getmicrotime() - $start_all, 3);
            $xml_result['total_results'] = $num_rows;

            $i = 0;
            while ($i < $num_rows) {
                $xml_result[$i]['num']          = $i+1 ;
                $xml_result[$i]['link']         = $result["$i"]["url"];
                $xml_result[$i]['title']        = $result["$i"]["title"];
                $xml_result[$i]['description']  = $result["$i"]["description"];
                $xml_result[$i]['size']         = $result["$i"]["size"];
                $xml_result[$i]['url']          = $urlquery;

                $i++;
            }



        }

        convert_xml($xml_result, 'link');

        if ($clear == 1) {
            $xml_result = array();
        }
        return ;
    }

    //  convert the result array to XML and store the result file in subfolder .../xml/
    function convert_xml($xml_result, $what) {
        global $xml_dir, $xml_name;

        error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE & ~E_STRICT);
        $now = date('_Y.m.d_h-i-s_A_');   //  current date and time , but writeable as file name on all OS

        //  convert the result array to XML file
        $array2XML = new Array2xml();
        $array2XML->setArray($xml_result);

        //  store the online XML result
        if ($array2XML->saveArray("".$xml_dir."/".$what."_".$xml_name."", "".$what."_results")){

        } else {
            if ($debug == '2') {
                echo "<small>Attention: Unable to create the XML result file <span class='blue'>$xml_name</span> in subfolder <span class='blue'>$xml_dir</span></small><br />";
            }
        }

        //  store the XML cached result
        if ($array2XML->saveArray("".$xml_dir."/stored/".$now."".$what."_".$xml_name."", "".$now."".$what."_results")){

        } else {
            if ($debug == '2') {
                echo "<small>Attention: Unable to create the XML cache result file <span class='blue'>$xml_name</span> in subfolder <span class='blue'>$xml_dir</span></small><br />";
            }
        }

        return ;
    }

    class Array2xml {
        /*
         * associative array to xml transformation class
         * @author	Johnny Brochard
         * @ver	0001.0002
         * @date	25/08/04
         */

        private $XMLArray;
        private $arrayOK;
        private $XMLFile;
        private $fileOK;
        private $doc;

        public function __construct(){

        }

        /**
         * saveArray
         * @access public
         * @param string $XMLFile
         * @return bool
         */
        public function saveArray($XMLFile, $rootName="", $encoding="utf-8"){
            global $debug;
            $this->doc = new domdocument("1.0", $encoding);
            $arr = array();
            if (count($this->XMLArray) > 1){
                if ($rootName != ""){
                    $root = $this->doc->createElement($rootName);
                }else{
                    $root = $this->doc->createElement("root");
                    $rootName = "root";
                }
                $arr = $this->XMLArray;
            } else {
                $key = key($this->XMLArray);
                $val = $this->XMLArray[$key];

                if (!is_int($key)){
                    $root = $this->doc->createElement($key);
                    $rootName = $key;
                }else{
                    if ($rootName != ""){
                        $root = $this->doc->createElement($rootName);
                    }else{
                        $root = $this->doc->createElement("root");
                        $rootName = "root";
                    }
                }
                $arr = $this->XMLArray[$key];
            }

            $root = $this->doc->appendchild($root);
            $this->addArray($arr, $root, $rootName);

            if ($this->doc->save($XMLFile) == 0){
                return false;
            }else{
                return true;
            }
        }

        /**
         * addArray recursive function
         * @access public
         * @param array $arr
         * @param DomNode &$n
         * @param string $name
         */
        function addArray($arr, &$n, $name=""){
            foreach ($arr as $key => $val){
                if (is_int($key)){
                    if (strlen($name)>1){
                        $newKey = substr($name, 0, strlen($name)-1);
                    }else{
                        $newKey="item";
                    }
                }else{
                    $newKey = $key;
                }

                $node = $this->doc->createElement($newKey);
                if (is_array($val)){
                    $this->addArray($arr[$key], $node, $key);
                }else{
                    $nodeText = $this->doc->createTextNode($val);
                    $node->appendChild($nodeText);
                }
                $n->appendChild($node);
            }
        }

        /**
         * setArray
         * @access public
         * @param array $XMLArray
         * @return bool
         */
        public function setArray($XMLArray){
            if (is_array($XMLArray) && count($XMLArray) != 0){
                $this->XMLArray = $XMLArray;
                $this->arrayOK = true;
            }else{
                $this->arrayOK = false;
            }
            return $this->arrayOK;
        }

    }
?>