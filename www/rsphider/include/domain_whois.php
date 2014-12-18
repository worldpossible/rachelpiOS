<?php

// the WHOIS class of Sphider-plus

    class whois {

        function lookup($url, $ext, $list) {

            if ($ext) { //  use extended TLD list
                $whoisservers = array(
                        "ac" 	=> "whois.nic.ac",
                        "ae" 	=> "whois.nic.ae",
                        "aero"	=> "whois.aero",
                        "ag" 	=> "whois.nic.ag",
                        "al" 	=> "whois.ripe.net",
                        "am" 	=> "whois.amnic.net",
                        "as" 	=> "whois.nic.as",
                        "asia" 	=> "whois.nic.asia",
                        "at" 	=> "whois.nic.at",
                        "au" 	=> "whois.aunic.net",
                        "az" 	=> "whois.ripe.net",
                        "ba" 	=> "whois.ripe.net",
                        "be" 	=> "whois.dns.be",
                        "bg" 	=> "whois.register.bg",
                        "bi" 	=> "whois.nic.bi",
                        "biz" 	=> "whois.neulevel.biz",
                        "bj" 	=> "whois.nic.bj",
                        "br" 	=> "whois.registro.br",
                        "bt" 	=> "whois.netnames.net",
                        "by" 	=> "whois.ripe.net",
                        "bz" 	=> "whois.belizenic.bz",
                        "ca" 	=> "whois.cira.ca",
                        "cat" 	=> "whois.cat",
                        "cc" 	=> "whois.nic.cc",
                        "cd" 	=> "whois.nic.cd",
                        "ch" 	=> "whois.nic.ch",
                        "ci" 	=> "whois.nic.ci",
                        "ck" 	=> "whois.nic.ck",
                        "cl" 	=> "whois.nic.cl",
                        "cn" 	=> "whois.cnnic.cn",
                        "com" 	=> "whois.internic.net",
                        "coop" 	=> "whois.nic.coop",
                        "cx" 	=> "whois.nic.cx",
                        "cy" 	=> "whois.ripe.net",
                        "cz" 	=> "whois.nic.cz",
                        "de" 	=> "whois.denic.de",
                        "dk" 	=> "whois.dk-hostmaster.dk",
                        "dm" 	=> "whois.nic.cx",
                        "dz" 	=> "whois.ripe.net",
                        "edu" 	=> "whois.educause.edu",
                        "ee" 	=> "whois.eenet.ee",
                        "eg" 	=> "whois.ripe.net",
                        "es" 	=> "whois.ripe.net",
                        "eu" 	=> "whois.eu",
                        "fi" 	=> "whois.ficora.fi",
                        "fo" 	=> "whois.ripe.net",
                        "fr" 	=> "whois.nic.fr",
                        "gb" 	=> "whois.ripe.net",
                        "gd" 	=> "whois.adamsnames.com",
                        "ge" 	=> "whois.ripe.net",
                        "gg" 	=> "whois.channelisles.net",
                        "gi" 	=> "whois2.afilias-grs.net",
                        "gl" 	=> "whois.ripe.net",
                        "gm" 	=> "whois.ripe.net",
                        "gov" 	=> "whois.nic.gov",
                        "gr" 	=> "whois.ripe.net",
                        "gs" 	=> "whois.nic.gs",
                        "gy" 	=> "whois.registry.gy",
                        "hk" 	=> "whois.hkirc.hk",
                        "hm" 	=> "whois.registry.hm",
                        "hn" 	=> "whois2.afilias-grs.net",
                        "hr" 	=> "whois.ripe.net",
                        "hu" 	=> "whois.nic.hu",
                        "ie" 	=> "whois.domainregistry.ie",
                        "il" 	=> "whois.isoc.org.il",
                        "in" 	=> "whois.inregistry.net",
                        "info" 	=> "whois.afilias.info",
                        "int" 	=> "whois.iana.org",
                        "io" 	=> "whois.nic.io",
                        "iq" 	=> "vrx.net",
                        "ir" 	=> "whois.nic.ir",
                        "is" 	=> "whois.isnic.is",
                        "it" 	=> "whois.nic.it",
                        "je" 	=> "whois.channelisles.net",
                        "jobs" 	=> "jobswhois.verisign-grs.com",
                        "jp" 	=> "whois.jprs.jp",
                        "ke" 	=> "whois.kenic.or.ke",
                        "kg" 	=> "www.domain.kg",
                        "kr" 	=> "whois.nic.or.kr",
                        "kz" 	=> "whois.nic.kz",
                        "la" 	=> "whois.nic.la",
                        "li" 	=> "whois.nic.li",
                        "lt" 	=> "whois.domreg.lt",
                        "lu" 	=> "whois.dns.lu",
                        "lv" 	=> "whois.nic.lv",
                        "ly" 	=> "whois.nic.ly",
                        "ma" 	=> "whois.iam.net.ma",
                        "mc" 	=> "whois.ripe.net",
                        "md" 	=> "whois.ripe.net",
                        "me" 	=> "whois.meregistry.net",
                        "mg" 	=> "whois.nic.mg",
                        "mn" 	=> "whois.nic.mn",
                        "mobi" 	=> "whois.dotmobiregistry.net",
                        "ms" 	=> "whois.nic.ms",
                        "mt" 	=> "whois.ripe.net",
                        "mu" 	=> "whois.nic.mu",
                        "museum"=> "whois.museum",
                        "mx" 	=> "whois.nic.mx",
                        "my" 	=> "whois.mynic.net.my",
                        "na" 	=> "whois.na-nic.com.na",
                        "name" 	=> "whois.nic.name",
                        "net" 	=> "whois.internic.net",
                        "nf" 	=> "whois.nic.nf",
                        "nl" 	=> "whois.domain-registry.nl",
                        "no" 	=> "whois.norid.no",
                        "nu" 	=> "whois.nic.nu",
                        "nz" 	=> "whois.srs.net.nz",
                        "org" 	=> "whois.publicinterestregistry.net",
                        "pl" 	=> "whois.dns.pl",
                        "pm" 	=> "whois.nic.pm",
                        "pr" 	=> "whois.uprr.pr",
                        "pro" 	=> "whois.registrypro.pro",
                        "pt" 	=> "whois.dns.pt",
                        "re" 	=> "whois.nic.re",
                        "ro" 	=> "whois.rotld.ro",
                        "ru" 	=> "whois.ripn.net",
                        "sa" 	=> "whois.nic.net.sa",
                        "sb" 	=> "whois.nic.net.sb",
                        "sc" 	=> "whois2.afilias-grs.net",
                        "se" 	=> "whois.iis.se",
                        "sg" 	=> "whois.nic.net.sg",
                        "sh" 	=> "whois.nic.sh",
                        "si" 	=> "whois.arnes.si",
                        "sk" 	=> "whois.ripe.net",
                        "sm" 	=> "whois.ripe.net",
                        "st" 	=> "whois.nic.st",
                        "su" 	=> "whois.ripn.net",
                        "tc" 	=> "whois.adamsnames.tc",
                        "tel" 	=> "whois.nic.tel",
                        "tf" 	=> "whois.nic.tf",
                        "th" 	=> "whois.thnic.net",
                        "tk" 	=> "whois.dot.tk",
                        "tl" 	=> "whois.nic.tl",
                        "tm" 	=> "whois.nic.tm",
                        "tn" 	=> "whois.ripe.net",
                        "to" 	=> "whois.tonic.to",
                        "tp" 	=> "whois.nic.tl",
                        "tr" 	=> "whois.nic.tr",
                        "travel"=> "whois.nic.travel",
                        "tv" 	=> "whois.nic.tv",
                        "tw" 	=> "whois.twnic.net.tw",
                        "ua" 	=> "whois.ua",
                        "ug" 	=> "whois.co.ug",
                        "uk" 	=> "whois.nic.uk",
                        "us" 	=> "whois.nic.us",
                        "uy" 	=> "nic.uy",
                        "uz" 	=> "whois.cctld.uz",
                        "va" 	=> "whois.ripe.net",
                        "vc" 	=> "whois2.afilias-grs.net",
                        "ve" 	=> "whois.nic.ve",
                        "vg" 	=> "whois.adamsnames.tc",
                        "wf" 	=> "whois.nic.wf",
                        "ws" 	=> "whois.nic.ws",
                        "yt" 	=> "whois.nic.yt",
                        "yu" 	=> "whois.ripe.net"
                        );

            } else {
                //  use gTLD list plus some other important
                $whoisservers = array (
                        "aero"  =>  "whois.aero",
                        "asia"  =>  "whois.dotasia.net",
                        "biz"   =>  "whois.neulevel.biz",
                        "cat"   =>  "whois.cat",
                        "cn"    =>  "whois.cnnic.cn",
                        "com"   =>  "whois.internic.net",
                        "coop"  =>  "whois.nic.coop",
                        "de"    =>  "whois.denic.de",
                        "edu"   =>  "whois.educause.net",
                        "es"    =>  "whois.ripe.net",
                        "eu"    =>  "whois.eu",
                        "fr"    =>  "whois.nic.fr",
                        "gov"   =>  "whois.nic.gov",
                        "info"  =>  "whois.afilias.net",
                        "int"   =>  "whois.iana.org",
                        "it"    =>  "whois.nic.it",
                        "jobs"  =>  "jobswhois.verisign-grs.com",
                        "me"    =>  "whois.meregistry.net",
                        "mobi"  =>  "whois.dotmobiregistry.net",
                        "museum"=>  "whois.museum",
                        "name"  =>  "whois.nic.name",
                        "net"   =>  "whois.internic.net",
                        "org"   =>  "whois.publicinterestregistry.net",
                        "pro"   =>  "whois.registrypro.pro",
                        "tel"   =>  "whois.nic.tel",
                        "travel"=>  "whois.nic.travel",
                        "tv"    =>  "whois.nic.tv",
                        "uk"    =>  "whois.nic.uk",
                        "us"    =>  "whois.nic.us"
                        );
            }

            $res_array = array();
            $url = strtolower(trim($url));

            if (!strpos($url, "ttp://")) {
                $url = "http://".$url;      //  if missing, add the scheme
            }

            $urlparts  = parse_url($url);
            $new_domain = @str_replace('www.', '', $urlparts['host']) ;

            //  if exist, remove sub-domains
            if(substr_count($new_domain, '.') > 1) {
                $no_suffix = substr($new_domain , 0, strrpos($new_domain, '.')) ;   //  remove suffix
                $new_domain = substr($new_domain , strrpos($no_suffix, '.')+1) ;    //  remove subdomains
            }
            //  extract the suffix
            $delim  = strrpos($new_domain, ".");
            $name   = substr($new_domain, 0, $delim);
            $suffix = substr($new_domain, $delim + 1);
            //  start preparing the result arry
            $res_array['url'] = $url;
            $res_array['domain_name'] = $name;
            $res_array['suffix'] = $suffix;

            if ($list) {
                //  present list of supported suffixes
                $supported = '';
                $all_suffixes = array_keys($whoisservers);
                if ($all_suffixes) {
                    for ($i = 0; $i < count($all_suffixes); $i++) {
                        $supported .= '&nbsp;.'.$all_suffixes [$i].'&nbsp;';
                    }
                    $res_array['result'] = "okay";
                    $res_array['answer'] = $supported;
                } else {
                    $res_array['result'] = "invalid array";
                    $res_array['answer'] = "server array not found, or empty";
                }
                return $res_array;

            } else {
                //  perform a WHOIS check
                //  first check for valid input
                if (!$delim) {
                    $res_array['result'] = "Invalid URL";
                    $res_array['answer'] = "Delimiter missing in URL";
                    return $res_array;
                } else {
                    if (!array_key_exists($suffix, $whoisservers)) {
                        $res_array['result'] = "Invalid URL";
                        $res_array['answer'] = "Suffix '$suffix' not supported";
                        return $res_array;
                    }
                }
                //  now  do the WHOIS query
                $answer     = '';
                $neg_answer = '';
                $server     = $whoisservers[$suffix];

                $request    =  fsockopen($server, 43, $errno, $errstr, 30);

                if (!$request) {
                    $answer = "$errstr ($errno)";
                } else {
                    fputs($request, "$new_domain\r\n");

                    while (!feof($request)) {
                        stream_set_timeout($request, 30);
                        $answer .= fread($request,128);
                    }
                    fclose ($request);
                }

                if (!$answer) {
                    $neg_answer = 1 ;
                } else {    //  check for any negative answer
                    $whois_string =preg_replace("/\s+/"," ",$answer);   //Replace whitespace with single space

                    foreach ($this->neg_response as $reject) {          //  test for all available negative answers
                        if (stripos(" ".$whois_string, $reject)) {
                            $neg_answer = 1 ;
                        }
                    }
                }

                if (!$neg_answer) {
                    $res_array['result']        = "okay";
                    $res_array['answer']        = $answer;
                    $res_array['whoisserver']   = $server;
                } else {
                    $res_array['result']        = "invalid, domain not found";
                    $res_array['answer']        = $answer;
                    $res_array['whoisserver']   = $server;
                }
                return $res_array;
            }
        }

        private $neg_response = array (
                "10060",
                "Connection refused",
                "does not exist",
                "domain name not known",
                "domain status: vailable",
                "error:101",
                "error for",
                "getaddrinfo failed",
                "is free",
                "is available",
                "is not registered",
                "no bbjects found",
                "no data found",
                "no data was found",
                "no domain records",
                "no entries found",
                "no existe",
                "no information available",
                "no match",
                "no match for",
                "nomatching",
                "nombre del Ddminio",
                "no records matching",
                "no such domain",
                "not available",
                "not found in database",
                "not registered",
                "not been registered",
                "not exist in database",
                "not found",
                "not have an entry",
                "nothing found",
                "object_not_found",
                "query_status: 500",
                "reject: not available",
                "status: avail",
                "status: available",
                "status: free",
                "to purchase",
                "(null)"
                );
    }

?>