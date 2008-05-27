<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.0                                                  |
  | http://www.elastix.org                                               |
  +----------------------------------------------------------------------+
  | Copyright (c) 2006 Palosanto Solutions S. A.                         |
  +----------------------------------------------------------------------+
  | Cdla. Nueva Kennedy Calle E 222 y 9na. Este                          |
  | Telfs. 2283-268, 2294-440, 2284-356                                  |
  | Guayaquil - Ecuador                                                  |
  | http://www.palosanto.com                                             |
  +----------------------------------------------------------------------+
  | The contents of this file are subject to the General Public License  |
  | (GPL) Version 2 (the "License"); you may not use this file except in |
  | compliance with the License. You may obtain a copy of the License at |
  | http://www.opensource.org/licenses/gpl-license.php                   |
  |                                                                      |
  | Software distributed under the License is distributed on an "AS IS"  |
  | basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See  |
  | the License for the specific language governing rights and           |
  | limitations under the License.                                       |
  +----------------------------------------------------------------------+
  | The Original Code is: Elastix Open Source.                           |
  | The Initial Developer of the Original Code is PaloSanto Solutions    |
  +----------------------------------------------------------------------+
  $Id: index.php,v 1.0 2008/02/30 15:55:57 bmacias Exp $ */

function _moduleContent(&$smarty, $module_name)
{
    //include elastix framework
    include_once "libs/paloSantoAjax.class.php";
    include_once "libs/paloSantoGrid.class.php";
    include_once "libs/paloSantoForm.class.php";
    include_once "libs/misc.lib.php";

    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoExample.class.php";

    //include lang local module
    $lang=get_language();
    $lang_file="modules/$module_name/lang/$lang.lang";
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    if (file_exists("$base_dir/$lang_file"))
        include_once($lang_file);
    else
        include_once("modules/$module_name/lang/en.lang");

    //call to global array ()
    global $arrConf;
    global $arrLang;
    global $arrLangModule;

    //folder path for custom templates
    $templates_dir=(isset($arrConfig['templates_dir']))?$arrConfig['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    $arrForm = createFieldForm($arrLangModule);
    $oForm   = new paloForm($smarty,$arrForm);

    //Action Selector 
    $content = "";
    $accion  = getAction();

    $oAjax   = new paloSantoAjax($smarty);
    switch($accion){
        default:
            $content = $oAjax->process("reportExample",array("null","0"));
        break;
    }
    return $content;
}

function showFilter()
{
    
}

function reportExample($action_ajax, $start_ajax)
{
    global $arrLang;
    global $smarty;
    global $arrConf;

    $functionAjax = "reportExample";
    $action = (isset($action_ajax))?$action_ajax:NULL;
    $start  = (isset($start_ajax))?$start_ajax:NULL;

    $oAjax   = new paloSantoAjax($smarty);
    $contenidoModulo = showExample($smarty, $arrLang, $arrConf, "example", $action, $start, $functionAjax);
    return $oAjax->sendResponse($functionAjax,$contenidoModulo);
}

function showExample($smarty, $arrLang, $arrConf, $module_name, $action, $start, $functionAjax)
{
    $dsnExample = "mysql://root:eLaStIx.2oo7@localhost/asteriskcdrdb";
    $pDB     = new paloDB($dsnExample);
    if(!empty($pDB->errMsg)) {
        $smarty->assign("mb_title",$arrLang['ERROR']);
        $smarty->assign("mb_message", $arrLang["Error when connecting to database"]."<br/>".$pDB->errMsg);
    }

    $oGrid  = new paloSantoGrid($smarty);
    $objExample = new paloSantoExample($pDB);
    $totalCDRs = $objExample->getTotalCDRs();

    $limit  = 10;
    $total  = $totalCDRs;
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);

    $oGrid->withAjax();
    $oGrid->setPrefixAjax("xajax_");
    $oGrid->setFunctionNameAjax($functionAjax);

    $oGrid->calculatePagination($action,$start);

    $offset = $oGrid->getOffset();
    $end    = $oGrid->getEnd();
    $arrCDRs   = $objExample->getCDRs($offset,$limit);

    if(is_array($arrCDRs) && count($arrCDRs)>0){
        foreach($arrCDRs as $key => $value){
            $arrTmp[0] = $value['uniqueid'];
            $arrTmp[1] = $value['calldate'];
            $arrTmp[2] = $value['src'];
            $arrTmp[3] = $value['dst'];
            $arrTmp[4] = $value['duration'];
            $arrData[] = $arrTmp;
        }
    }

    $arrGrid = array("title"       => "Titulo",
                        "icon"     => "images/conference.png",
                        "width"    => "99%",
                        "start"    => ($total==0) ? 0 : $offset + 1, //se puede quitar
                        "end"      => $end, //se puede quitar
                        "total"    => $total, //se puede quitar
                        "url"      => "?menu=$module_name",
                        "columns"  => array(0 => array("name"      => "",
                                                    "property1" => ""),
                                            1 => array("name"      => $arrLang["Conference #"],
                                                    "property1" => ""),
                                            2 => array("name"      => $arrLang["Conference Name"],
                                                    "property1" => ""),
                                            3 => array("name"      => $arrLang["Starts"],
                                                    "property1" => ""),
                                            4 => array("name"      => $arrLang["Ends"],
                                                    "property1" => "")
                                           )
                    );


/***********************************/
    $smarty->assign("SHOW", $arrLang["Show"]);
    $smarty->assign("NEW_CONFERENCE", $arrLang["New Conference"]);

    $arrConference = array("Past_Conferences" => $arrLang["Past Conferences"], "Current_Conferences" => $arrLang["Current Conferences"], "Future_Conferences" => $arrLang["Future Conferences"]);

    $arrFormElements = array(
                                "conference"  => array(  "LABEL"                  => $arrLang["State"],
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrConference,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => "",
                                                    "EDITABLE"               => "no",
                                                    "SIZE"                   => "1"),

                                "filter" => array(  "LABEL"                  => $arrLang["Filter"],
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => "",
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                                );

    $oFilterForm = new paloForm($smarty, $arrFormElements);
    $htmlFilter = $oFilterForm->fetchForm("/var/www/html/modules/example/themes/default/filtro.tpl", "", "");
    $oGrid->showFilter(trim($htmlFilter));
/*******************************************/

    $contenidoModulo = $oGrid->fetchGrid($arrGrid, $arrData,$arrLang);
    return $contenidoModulo;
}

/** Start Function utilities **/
function createFieldForm($arrLangModule)
{
    $arrFields = array();
    return $arrFields;
}


function getAction()
{
    if(getParameter("show")) 
        return "show";
    else if(getParameter("create"))
        return "create";
    else if(getParameter("delete"))
        return "delete";
    else 
        return "show";
}

function getParameter($parameter)
{
    if(isset($_POST[$parameter]))
        return $_POST[$parameter];
    else if(isset($_GET[$parameter]))
        return $_GET[$parameter];
    else
        return false;
}
/** End Function utilities **/
?>