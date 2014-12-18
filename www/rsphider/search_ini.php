<?php
/*******************************************************************
 *
 *       Definition of language for user dialog.
 +       Attention:       needs to be modified only,
 *       if Admin settings should be overwritten
 *       for this application.
 *       Default: $user_lng    = "0";
 *
 *       Enter "fr" for French language
 *       Enter "it" for Italian language
 *       etc.
 */
    $user_lng    = "0";
/*
 *******************************************************************
 *
 *       Definition whether the advanced options of the 'Search form'
 *       should be shown or hidden.
 +       Attention:      needs to be modified only,
 *       if Admin settings should be overwritten
 *       for this application.
 *       Default:                    $adv_search   =   "";
 *       In order to hide:       $adv_search   =    "1";
 *       In order to show:      $adv_search   =    "2";
 */
    $adv_search = "";
/*
 *******************************************************************
 *
 *       Definition whether the search type of the 'Search form'
 *       should be overwritten.
 +       Attention:      needs to be modified only,
 *       if Search form should be overwritten
 *       for this application.
 *       Default:                                $type_search   =   "";
 *       In order to set to OR:            $type_search   =   "or";
 *       In order to set to AND:         $type_search   =   "and";
 *       In order to set to PHRASE:    $type_search   =   "phrase";
 *       In order to set to Tolerant:    $type_search   =   "tol";
 */
    $type_search = "";
/*
 *******************************************************************
 *
 *       Definition of database used for this application
 +       Attention:      needs to be modified only,
 *       if Admin settings should be overwritten
 *       for this application.
 *       Default: $user_db    = "0";
 */
    $user_db    = "0";
/*
 *******************************************************************
 *
 *       Definition of table prefix used for this application
 +       Attention:      needs to be modified only,
 *       if Admin settings should be overwritten
 *       for this application.
 *       Default: $user_prefix    = "0";
 */
    $user_prefix    = "0";
/*
 *******************************************************************
 *
 *       Variable for additional XML file output, holding the results.
 +       Usually needs not to be modified.
 *       Activate only, if an additional XML result file should be created
 *       Default:    $out    = "";
 *       Active:      $out    = "xml";
 */
    $out        = "xml";
/*
 *******************************************************************
 *
 *       Name of the additional XML result file
 *       Usually needs not to be modified.
 *       The name will be added by the prefixes 'text'
 *       and if activated in Admin backend also by 'media'
 *       Default:    $xml_name        = "results.xml"
 */
    $xml_name   = "results.xml";
/*
 *******************************************************************
 *
 *       Definition of subfolder for required search scripts.
 *       Usually needs not to be modified.
 */
    $include_dir    = "./include";
/*
  *******************************************************************
 *
 *       Definition for error handling
 */
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE & ~E_STRICT);
    define("_SECURE",1);    // define secure constant
/*
/********************************************************************/
?>