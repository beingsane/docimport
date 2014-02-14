<?php
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

/**
 * Incoming var from the include
 * @var     string  $option     Component name, ie com_foobar
 * @var     int     $id         Id of the current record
 */

// ------------------  standard plugin initialize function - don't change ---------------------------
global $sh_LANG;
$sefConfig = & shRouter::shGetConfig();  
$shLangName = '';
$shLangIso = '';
$title = array();
$shItemidString = '';
$dosef = shInitializePlugin( $lang, $shLangName, $shLangIso, $option);
if ($dosef == false) return;
// ------------------  standard plugin initialize function - don't change ---------------------------

// ------------------  load language file - adjust as needed ----------------------------------------
//$shLangIso = shLoadPluginLanguage( 'com_XXXXX', $shLangIso, '_SEF_SAMPLE_TEXT_STRING');
// ------------------  load language file - adjust as needed ----------------------------------------


if (!function_exists( 'shDocimportMenuName'))
{
    function shDocimportMenuName($task, $Itemid, $option, $shLangName)
    {
        $sefConfig           = &shRouter::shGetConfig();
        $shArsDownloadName = shGetComponentPrefix($option);

        if( empty($shArsDownloadName) ) $shArsDownloadName = getMenuTitle($option, $task, $Itemid, null, $shLangName);
        if( empty($shArsDownloadName) || $shArsDownloadName == '/' ) $shArsDownloadName = 'DocImport';

        return str_replace( '.', $sefConfig->replacement, $shArsDownloadName );
    }
}

global $shGETVars;

$task    = isset($task) ? @$task : null;
$Itemid  = isset($Itemid) ? @$Itemid : null;
$title[] = shDocimportMenuName($task, $Itemid, $option, $shLangName);

require_once JPATH_ROOT.'/components/com_docimport/router.php';

// I have to copy the global var otherwise DocImport router will remove "too much"
$test = $shGETVars;

$title = array_merge($title, docimportBuildRoute($test));
$title[] = '/';

shRemoveFromGETVarsList('lang');
shRemoveFromGETVarsList('option');
shRemoveFromGETVarsList('view');
shRemoveFromGETVarsList('Itemid');
shRemoveFromGETVarsList('id');

// ------------------  standard plugin finalize function - don't change ---------------------------  
if ($dosef){
   $string = shFinalizePlugin( $string, $title, $shAppendString, $shItemidString, 
      (isset($limit) ? @$limit : null), (isset($limitstart) ? @$limitstart : null), 
      (isset($shLangName) ? @$shLangName : null));
}      
// ------------------  standard plugin finalize function - don't change ---------------------------
  