<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.1-4                                                |
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
  $Id: default.conf.php,v 1.1 2008-07-08 11:07:07 jvega Exp $ */

function _moduleContent(&$smarty, $module_name)
{
    //include elastix framework
    include_once "libs/paloSantoGrid.class.php";
    include_once "libs/paloSantoForm.class.php";

    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoLanguageAdmin.class.php";

    //include lang local module
    $lang=get_language();
    $lang_file="modules/$module_name/lang/$lang.lang";
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    if (file_exists("$base_dir/$lang_file"))
        include_once($lang_file);
    else
        include_once("modules/$module_name/lang/en.lang");

    global $arrConf;
    global $arrLang;
    global $arrLangModule;

    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConfig['templates_dir']))?$arrConfig['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    $accion = getAction();

    $content = "";
    $arrFormConference = createFieldForm($arrLangModule);
    $oForm = new paloForm($smarty,$arrFormConference);
    switch($accion)
    {
        case "show":
            $_POST['nav'] = null; $_POST['start'] = null;
            $content = showLanguages($smarty, $module_name, $local_templates_dir, $arrLang, $arrLangModule);
            break;
        case "new":
            $content = newLanguage($smarty, $module_name, $local_templates_dir, $arrLang, $arrLangModule);
            break;
        case "save_language":
            $content = saveLanguage($smarty,$module_name, $local_templates_dir, $arrLang, $arrLangModule, $oForm);
            break;
        case "cancel_language":
            $content = showLanguages($smarty, $module_name, $local_templates_dir, $arrLang, $arrLangModule);
            break;
        case "save_all":
            $content = saveAll($smarty, $module_name, $local_templates_dir, $arrLang, $arrLangModule, $oForm);
            break;
        default://report_Languages
            $content = showLanguages($smarty, $module_name, $local_templates_dir, $arrLang, $arrLangModule);
            break;
    }

    return $content;
}

function saveLanguage($smarty, $module_name, $local_templates_dir, $arrLang, $arrLangModule, $oForm)
{
    $oPalo = new paloSantoLanguageAdmin();
    $option = $_POST['option'];

    if(isset($option) && $option=='select_language'){
        $newLang = getParameter("language_new");
        $bandera = $oPalo->saveNewLanguage($newLang);

        if( !$bandera ){
            $smarty->assign("mb_title",$oPalo->errMsg['head'].":");
            $smarty->assign("mb_message",$oPalo->errMsg['body']);
        }
        else{
            $smarty->assign("mb_title",$arrLangModule["Message"]);
            $smarty->assign("mb_message",$arrLangModule["Language saved succetiful"]);
        }
    }
    else{
        $module_name_L   = getParameter("select_module");
        $lang_name_L     = getParameter("select_lang");
        $lang_english_L  = getParameter("lang_english");
        $lang_traslate_L = getParameter("lang_traslate");

        $bandera = $oPalo->saveTraslate($module_name_L, $lang_name_L, $lang_english_L, $lang_traslate_L);
        if( !$bandera ){
            $smarty->assign("mb_title",$oPalo->errMsg['head'].":");
            $smarty->assign("mb_message",$oPalo->errMsg['body']);
        }
        else{
            $smarty->assign("mb_title",$arrLangModule["Message"]);
            $smarty->assign("mb_message",$arrLangModule["Traslate saved succetiful"]);
        }
    }
    return newLanguage($smarty,$module_name, $local_templates_dir, $arrLang, $arrLangModule);
}

function newLanguage($smarty,$module_name, $local_templates_dir, $arrLang, $arrLangModule)
{
    $arrFormLanguage = createFieldForm($arrLangModule);
    $oForm = new paloForm($smarty,$arrFormLanguage);

    $option = getParameter('option');
    if($option == 'select_traslate' ){
        $smarty->assign("check_language", "");
        $smarty->assign("check_traslate", "checked='checked'");
    }
    else{
        $smarty->assign("check_language", "checked='checked'");
        $smarty->assign("check_traslate", "");
    }
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("new_language", $arrLangModule["New Language"]);
    $smarty->assign("new_traslate", $arrLangModule["New Traslate"]);
    $smarty->assign("new_language_ej", $arrLangModule["Ej: For English: en.lang"]);

    $tmpLangEnglish   = getParameter('lang_english');
    $tmpLangTranslate = getParameter('lang_traslate');
    $_POST['lang_english'] = htmlspecialchars($tmpLangEnglish);
    $_POST['lang_traslate'] = htmlspecialchars($tmpLangTranslate);

    $htmlForm = $oForm->fetchForm("$local_templates_dir/new_language.tpl",$arrLangModule["Language Admin"], $_POST);

    $contenidoModulo = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $contenidoModulo;
}

function createFieldForm($arrLangModule)
{
    $oPaloSanto = new paloSantoLanguageAdmin();
    $arrFields = array(
        "select_module"  => array(  "LABEL"                 => $arrLangModule["Select Module"],
                                    "REQUIRED"              => "yes",
                                    "INPUT_TYPE"            => "SELECT",
                                    "INPUT_EXTRA_PARAM"     => $oPaloSanto->leer_directorio_modulos(),
                                    "VALIDATION_TYPE"       => "text",
                                    "VALIDATION_EXTRA_PARAM"=> ""),
        "select_lang"    => array(  "LABEL"                 => $arrLangModule["Select Language"],
                                    "REQUIRED"              => "yes",
                                    "INPUT_TYPE"            => "SELECT",
                                    "INPUT_EXTRA_PARAM"     => $oPaloSanto->leer_directorio_lenguajes(),
                                    "VALIDATION_TYPE"       => "text",
                                    "VALIDATION_EXTRA_PARAM"=> ""),
        "lang_english"   => array(  "LABEL"                 => $arrLangModule["Input Language English"],
                                    "REQUIRED"              => "yes",
                                    "INPUT_TYPE"            => "TEXT",
                                    "INPUT_EXTRA_PARAM"     => "",
                                    "VALIDATION_TYPE"       => "text",
                                    "VALIDATION_EXTRA_PARAM"=> ""),
        "lang_traslate"  => array(  "LABEL"                 => $arrLangModule["Input Traslate"],
                                    "REQUIRED"              => "yes",
                                    "INPUT_TYPE"            => "TEXT",
                                    "INPUT_EXTRA_PARAM"     => "",
                                    "VALIDATION_TYPE"       => "text",
                                    "VALIDATION_EXTRA_PARAM"=> ""),
        "language_new"   => array(  "LABEL"                 => $arrLangModule["Input New Language"],
                                    "REQUIRED"              => "yes",
                                    "INPUT_TYPE"            => "TEXT",
                                    "INPUT_EXTRA_PARAM"     => "",
                                    "VALIDATION_TYPE"       => "ereg",
                                    "VALIDATION_EXTRA_PARAM"=> "*.lang"),
        );
    return $arrFields;
}

function showLanguages($smarty, $module_name, $local_templates_dir, $arrLang, $arrLangModule)
{
    $oPaloSanto = new paloSantoLanguageAdmin();
    $arrFormElements = array(
        "module"            => array(   "LABEL"                  => $arrLangModule["Select Module"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "SELECT",
                                        "INPUT_EXTRA_PARAM"      => $oPaloSanto->leer_directorio_modulos(),
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""),
        "language"           => array(   "LABEL"                 => $arrLangModule["Select Language"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "SELECT",
                                        "INPUT_EXTRA_PARAM"      => $oPaloSanto->leer_directorio_lenguajes(),
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => "")
                            );

    $oFilterForm = new paloForm($smarty, $arrFormElements);
    $smarty->assign("SHOW", $arrLang["Show"]);
    $smarty->assign("NEW", $arrLangModule["Add"]);
    $smarty->assign("SAVE_ALL", "Save All");

    $module   = getParameter("module");
    $language = getParameter("language");

    $action = getParameter('nav');
    $start = getParameter('start');
    
    $smarty->assign("start_value", $start);
    $smarty->assign("nav_value", $action);

    $_POST["module"] = $module;
    $_POST["language"] = $language;
    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", $_POST);

    $pLanguages = new paloSantoLanguageAdmin();

    //Paginacion
    $limit  = 15;
    $total_datos = $pLanguages->ObtainNumLanguages($module, $language);
    $total  = $total_datos;

    $oGrid  = new paloSantoGrid($smarty);

    $offset = $oGrid->getOffSet($limit,$total,$action,$start);

    $end    = ($offset+$limit)<=$total ? $offset+$limit : $total;

    $url = "?menu=$module_name&module=$module&language=$language";

    $arrLangMod = $pLanguages->obtainLanguages($limit, $offset, $module, $language);
    $arrData = array();

    if(is_array($arrLangMod) && count($arrLangMod)>0)
        foreach($arrLangMod as $key => $value){
            $tmpKey = htmlspecialchars($key);
            $tmpValue = htmlspecialchars($value);

            $arrTmp[0]  = $tmpKey;
            $arrTmp[1]  = "<input class='table_data' style='width:450px' type='text' name=\"lang_$tmpKey\" id=\"lang_$tmpKey\" value=\"$tmpValue\" />";
            $arrData[] = $arrTmp;
        }

    $arrGrid = array("title"    => $arrLangModule["Language Admin"],
                     "icon"     => "images/list.png",
                     "width"    => "99%",
                     "start"    => ($total==0) ? 0 : $offset + 1,
                     "end"      => $end,
                     "total"    => $total,
                     "url"      => $url,
                     "columns"  => array(0 => array("name"      => "Clave",
                                                    "property1" => ""),
                                         1 => array("name"      => "Value",
                                                    "property1" => "")
                                        )
                    );

    $oGrid->showFilter(trim($htmlFilter));
    $contenidoModulo = "<form  method='POST' style='margin-bottom:0;' action=$url>".$oGrid->fetchGrid($arrGrid, $arrData,$arrLang)."</form>";

    return $contenidoModulo;
}

function saveAll($smarty, $module_name, $local_templates_dir, $arrLang, $arrLangModule, $oForm)
{
    $oPalo = new paloSantoLanguageAdmin();
    $arrayLangTrasl = array();
    $module = getParameter("module");
    $language = getParameter("language");

    foreach($_POST as $key => $value)
    {
        if( substr($key,0,5) == "lang_" )
            $arrayLangTrasl[str_replace('_',' ',substr($key,5))] = $value;
    }

    $bandera = $oPalo->saveAll($arrayLangTrasl, $module, $language);

    if( !$bandera ){
        $smarty->assign("mb_title",$oPalo->errMsg['head'].":");
        $smarty->assign("mb_message",$oPalo->errMsg['body']);
    }
    else{
        $smarty->assign("mb_title",$arrLangModule["Message"]);
        $smarty->assign("mb_message", "Traslates saved succetiful");
    }

    return showLanguages($smarty,$module_name, $local_templates_dir, $arrLang, $arrLangModule);
}

function getParameter($parameter)
{
    if(isset($_POST[$parameter]))
        return $_POST[$parameter];
    else if(isset($_GET[$parameter]))
        return $_GET[$parameter];
    else
        return null;
}

function getAction()
{
    if(getParameter("show")) //Get parameter by POST (submit)
        return "show";
    else if(getParameter("new"))
        return "new";
    else if(getParameter("save"))
        return "save_language";
    else if(getParameter("save_all"))
        return "save_all";
    else if(getParameter("action")=="show") //Get parameter by GET (command pattern, links)
        return "show";
    else
        return "report";
}
?>