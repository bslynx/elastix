<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.5-9                                               |
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
  $Id: index.php,v 1.1 2009-03-27 12:03:59 Oscar Navarrete anavarre@espol.edu.ec Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoSip_buddies.class.php";
    include_once "modules/$module_name/libs/paloSantoIax_buddies.class.php";

    //include file language agree to elastix configuration
    //if file language not exists, then include language by default (en)
    $lang=get_language();
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $lang_file="modules/$module_name/lang/$lang.lang";
    if (file_exists("$base_dir/$lang_file")) include_once "$lang_file";
    else include_once "modules/$module_name/lang/en.lang";

    //global variables
    global $arrConf;
    global $arrConfModule;
    global $arrLang;
    global $arrLangModule;
    $arrConf = array_merge($arrConf,$arrConfModule);
    $arrLang = array_merge($arrLang,$arrLangModule);

    //folder path for custom templates
    $templates_dir=(isset($arrConf['templates_dir']))?$arrConf['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    //conexion resource
    $pDB = new paloDB($arrConf['dsn_conn_database']);

    //actions
    $accion = getAction();
    $content = "";

    switch($accion){
        case "new_sip_buddies":
            $content = new_sipBuddies($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        case "new_iax_buddies":
            $content = new_iaxBuddies($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        case "show_sip":
            $content = view_sip_buddies($smarty,$module_name, $local_templates_dir, $pDB, $arrLang);
            break;
        case "show_iax":
            $content = view_iax_buddies($smarty,$module_name, $local_templates_dir, $pDB, $arrLang);
            break;
        case "edit_sip":
            $content = view_sip_buddies($smarty,$module_name, $local_templates_dir, $pDB, $arrLang);
            break;
        case "edit_iax":
            $content = view_iax_buddies($smarty,$module_name, $local_templates_dir, $pDB, $arrLang);
            break;
        case "save_sip":
            $content = save_sip_buddies($smarty, $module_name, $local_templates_dir, $pDB, $arrLang);
            break;
        case "save_iax":
            $content = save_iax_buddies($smarty, $module_name, $local_templates_dir, $pDB, $arrLang);
            break;
        case "commit_sip":
            $content = save_sip_buddies($smarty, $module_name, $local_templates_dir, $pDB, $arrLang, true);
            break;
        case "commit_iax":
            $content = save_iax_buddies($smarty, $module_name, $local_templates_dir, $pDB, $arrLang, true);
            break;
        case "delete_sip":
            $content = delete_sip_buddies($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        case "delete_iax":
            $content = delete_iax_buddies($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        case "cancel_sip":
            header("Location: ?menu=$module_name");
            break;
        case "cancel_iax":
            $content = reportIax_buddies($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        case "show_reportIax":
            $content = reportIax_buddies($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        default:
            $content = reportSip_buddies($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
    }
    return $content;
}


function reportSip_buddies($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pSip_buddies = new paloSantoSip_buddies($pDB);
    $filter_field = getParameter("filter_field");
    $filter_value = getParameter("filter_value");
    $action = getParameter("nav");
    $start  = getParameter("start");
    
    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);
    $totalSip_buddies = $pSip_buddies->ObtainNumSip_buddies($filter_field, $filter_value);

    $limit  = 20;
    $total  = $totalSip_buddies;
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);

    $oGrid->calculatePagination($action,$start);
    $offset = $oGrid->getOffsetValue();
    //$oGrid->pagingShow(false);
    $end    = $oGrid->getEnd();
    $url    = "?menu=$module_name&filter_field=$filter_field&filter_value=$filter_value";

    $arrData = null;
    $arrResult =$pSip_buddies->ObtainSip_buddies($limit, $offset, $filter_field, $filter_value);

    if(is_array($arrResult) && $total>0){
        foreach($arrResult as $key => $value){
        $arrTmp[0]  = "<input type='checkbox' name='SipBubID_{$value['id']}'  />";
        $arrTmp[1] = "<a href='?menu=$module_name&action=show_sip&id=".$value['id']."'>{$value['name']}</a>";
        $arrTmp[2] = $value['context'];
        $arrTmp[3] = $value['secret'];
        $arrTmp[4] = $value['username'];
            $arrData[] = $arrTmp;
        }
    }

    $button = "<input type='submit' name='delete_sip' value='{$arrLang["Delete"]}' class='button' onclick=\" return confirmSubmit('{$arrLang["Are you sure you wish to delete the configuration(s) sip"]}');\" />";

    $arrGrid = array("title"    => $arrLang["Sip buddies"],
                        "icon"     => "images/list.png",
                        "width"    => "99%",
                        "start"    => ($total==0) ? 0 : $offset + 1,
                        "end"      => $end,
                        "total"    => $total,
                        "url"      => $url,
                        "columns"  => array(
            0 => array("name"      => $button,
                                   "property1" => ""),
            1 => array("name"      => $arrLang["name"],
                                   "property1" => ""),
            2 => array("name"      => $arrLang["context"],
                                   "property1" => ""),
            3 => array("name"      => $arrLang["secret"],
                                   "property1" => ""),
            4 => array("name"      => $arrLang["username"],
                                   "property1" => ""),
                                        )
                    );

    //begin section filter
    $arrFormFilterSip_buddies = createFieldFilter($arrLang);
    $oFilterForm = new paloForm($smarty, $arrFormFilterSip_buddies);
    $smarty->assign("SHOW", $arrLang["Show"]);
    $smarty->assign("NEW_SIPBUDDIES", $arrLang["New Sip Buddies"]);

    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl","",$_POST);
    //end section filter

    $oGrid->showFilter(trim($htmlFilter));
    $contenidoModulo = "<form method='POST' style='margin-bottom:0;' action=$url>".$oGrid->fetchGrid($arrGrid, $arrData,$arrLang)."</form>";
    //end grid parameters

    return $contenidoModulo;
}


function reportIax_buddies($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pIax_buddies = new paloSantoIax_buddies($pDB);
    $filter_field = getParameter("filter_field");
    $filter_value = getParameter("filter_value");
    $action = getParameter("nav");
    $start  = getParameter("start");
    
    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);
    $totalIax_buddies = $pIax_buddies->ObtainNumIax_buddies($filter_field, $filter_value);
    $arr_num = $pIax_buddies->generateNums();
    //exec("echo ".print_r($arr_num, true)."> /tmp/oscar");
    exec("echo '".print_r($arr_num, true)."' > /tmp/oscar");

    $limit  = 20;
    $total  = $totalIax_buddies;
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);

    $oGrid->calculatePagination($action,$start);
    $offset = $oGrid->getOffsetValue();
    //$oGrid->pagingShow(false);
    $end    = $oGrid->getEnd();
    $url    = "?menu=$module_name&filter_field=$filter_field&filter_value=$filter_value";

    $arrData = null;
    $arrResult =$pIax_buddies->ObtainIax_buddies($limit, $offset, $filter_field, $filter_value);

    if(is_array($arrResult) && $total>0){
        foreach($arrResult as $key => $value){
        $arrTmp[0]  = "<input type='checkbox' name='IaxBubID_{$value['id']}'  />";
        $arrTmp[1] = "<a href='?menu=$module_name&action=show_iax&id=".$value['id']."'>{$value['name']}</a>";
        $arrTmp[2] = $value['context'];
        $arrTmp[3] = $value['secret'];
        $arrTmp[4] = $value['username'];
            $arrData[] = $arrTmp;
        }
    }

    $button = "<input type='submit' name='delete_iax' value='{$arrLang["Delete"]}' class='button' onclick=\" return confirmSubmit('{$arrLang["Are you sure you wish to delete the configuration(s) iax"]}');\" />";

    $arrGrid = array("title"    => $arrLang["Iax buddies"],
                        "icon"     => "images/list.png",
                        "width"    => "99%",
                        "start"    => ($total==0) ? 0 : $offset + 1,
                        "end"      => $end,
                        "total"    => $total,
                        "url"      => $url,
                        "columns"  => array(
            0 => array("name"      => $button,
                                   "property1" => ""),
            1 => array("name"      => $arrLang["name"],
                                   "property1" => ""),
            2 => array("name"      => $arrLang["context"],
                                   "property1" => ""),
            3 => array("name"      => $arrLang["secret"],
                                   "property1" => ""),
            4 => array("name"      => $arrLang["username"],
                                   "property1" => ""),
                                        )
                    );

    //begin section filter
    $arrFormFilterSip_buddies = createFieldFilter($arrLang);
    $oFilterForm = new paloForm($smarty, $arrFormFilterSip_buddies);
    $smarty->assign("SHOW", $arrLang["Show"]);
    $smarty->assign("NEW_IAXBUDDIES", $arrLang["New Iax Buddies"]);

    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filterIax.tpl","",$_POST);
    //end section filter

    $oGrid->showFilter(trim($htmlFilter));
    $contenidoModulo = "<form  method='POST' style='margin-bottom:0;' action=$url>".$oGrid->fetchGrid($arrGrid, $arrData,$arrLang)."</form>";
    //end grid parameters

    return $contenidoModulo;
}


function createFieldFilter($arrLang)
{
    $arrFilter = array(
        "name" => $arrLang["name"],
        "context" => $arrLang["context"],
        "secret" => $arrLang["secret"],
        "username" => $arrLang["username"],
                    );

    $arrFormElements = array(
            "filter_field" => array("LABEL"                  => $arrLang["Search"],
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "SELECT",
                                    "INPUT_EXTRA_PARAM"      => $arrFilter,
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => ""),
            "filter_value" => array("LABEL"                  => "",
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "TEXT",
                                    "INPUT_EXTRA_PARAM"      => "",
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => ""),
                    );
    return $arrFormElements;
}


///////////////////////////////////
////Functions for new requests////
//////////////////////////////////

function new_sipBuddies($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang)
{ 
    $pSipBuddies = new paloSantoSip_buddies($pDB);
    $arrFormNewSipBuddies = createFieldForm($arrLang);
    $oForm = new paloForm($smarty,$arrFormNewSipBuddies);

    $smarty->assign("Show", 1);
    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("IMG", "images/list.png");
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    setListOptions($smarty);
 
    $htmlForm = $oForm->fetchForm("$local_templates_dir/new_sipBuddies.tpl", $arrLang["New Sip Buddies"], $_POST);
    $contenidoModulo = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $contenidoModulo;
}

function new_iaxBuddies($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang)
{ 
    $pIax_buddies = new paloSantoIax_buddies($pDB);
    $arrFormNewIaxBuddies = createFieldForm($arrLang);
    $oForm = new paloForm($smarty,$arrFormNewIaxBuddies);

    $smarty->assign("Show", 1);
    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("IMG", "images/list.png");
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    setListOptions($smarty);
 
    $htmlForm = $oForm->fetchForm("$local_templates_dir/new_iaxBuddies.tpl", $arrLang["New Iax Buddies"], $_POST);
    $contenidoModulo = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $contenidoModulo;
}


function setListOptions($smarty){
    $pIax_buddies = new paloSantoIax_buddies($pDB);
    $arr_num = $pIax_buddies->generateNums();
    $smarty->assign('selec_yesno', array(
                            '' => '',
                            'no' => 'no',
                            'yes' => 'yes',
                            ));
    $smarty->assign('selec_type', array(
                            'friend' => 'Friend',
                            'user' => 'User',
                            'peer' => 'Peer',                            
                            ));
    $smarty->assign('selec_nat', array(
                            'no' => 'no',
                            'yes' => 'yes',
                            'route' => 'route',
                            'never' => 'never',
                            ));
    $smarty->assign('selec_amaflags', array(
                            'default' => 'Default',
                            'omit' => 'Omit',
                            'billing' => 'Billing',
                            'documentation' => 'Documentation',
                            ));
    $smarty->assign('selec_ring_time', $arr_num);
    $smarty->assign('selec_call_waiting', array(
                            'disable' => 'Disable',
                            'enable' => 'Enable',)); 
    $smarty->assign('selec_dtmfmode', array(
                            'auto' => 'auto',
                            'rfc2833' => 'rfc2833',
                            'inband' => 'inband',
                            'info' => 'info',));
    $smarty->assign('selec_recording', array(
                            'ondemand' => 'On demand',
                            'always' => 'Always',
                            'never' => 'Never',)); 
    $smarty->assign('selec_status', array(
                            'disable' => 'Disable',
                            'enable' => 'Enable',));
    $smarty->assign('selec_allow', array(
                            'alaw' => 'alaw',
                            'g729' => 'g729',
                            'ilbc' => 'ilbc',
                            'gsm' => 'gsm',
                            'ulaw' => 'ulaw',
                            ));
}

function createFieldForm($arrLang)
{
    $arrOptions = array('all' => 'All', 'alaw' => 'Alaw', 'ulaw' => 'Ulaw');
    $arrOpYesNo = array('yes' => 'Yes', 'no' => 'No');

    $arrFields = array(
            "name"   => array(      "LABEL"                  => $arrLang["Name"],
                                        "REQUIRED"               => "yes",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "dbsecret"   => array(      "LABEL"                  => $arrLang["Dbsecret"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "notransfer"   => array(      "LABEL"                  => $arrLang["Notransfer"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "inkeys"   => array(      "LABEL"                  => $arrLang["Inkeys"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "outkey"   => array(      "LABEL"                  => $arrLang["Outkey"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "auth"   => array(      "LABEL"                  => $arrLang["Auth"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "host"   => array(      "LABEL"                  => $arrLang["Host"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "nat"   => array(      "LABEL"                  => $arrLang["Nat"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "type"   => array(      "LABEL"                  => $arrLang["Type"],
                                        "REQUIRED"               => "",
                                        "INPUT_TYPE"             => "",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "accountcode"   => array(      "LABEL"                  => $arrLang["Accound Code"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "amaflags"   => array(      "LABEL"                  => $arrLang["Amaflags"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "call_limit"   => array(      "LABEL"                  => $arrLang["Call Limit"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => "numeric"
                                        ),
            "call_group"   => array(      "LABEL"                  => $arrLang["Call Group"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "caller_id"   => array(      "LABEL"                  => $arrLang["Caller ID"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "can_call_forward"   => array(      "LABEL"                  => $arrLang["Can Call Forward"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "can_reinvite"   => array(      "LABEL"                  => $arrLang["Can Reinvite"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ), 
            "context"   => array(      "LABEL"                  => $arrLang["Context"],
                                        "REQUIRED"               => "yes",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "default_tip"   => array(      "LABEL"                  => $arrLang["Default Tip"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ), 
            "from_user"   => array(      "LABEL"                  => $arrLang["From User"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "from_domain"   => array(      "LABEL"                  => $arrLang["From Domain"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "insecure"   => array(      "LABEL"                  => $arrLang["Insecure"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "language"   => array(      "LABEL"                  => $arrLang["Language"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "mailbox"   => array(      "LABEL"                  => $arrLang["Mail Box"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "md5secret"   => array(      "LABEL"                  => $arrLang["Md5secret"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "deny"   => array(      "LABEL"                  => $arrLang["Deny"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "permit"   => array(      "LABEL"                  => $arrLang["Permit"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "mask"   => array(      "LABEL"                  => $arrLang["Mask"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "musiconhold"   => array(      "LABEL"                  => $arrLang["Music on hold"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "pickupgroup"   => array(      "LABEL"                  => $arrLang["Pick up group"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "qualify"   => array(      "LABEL"                  => $arrLang["Qualify"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "regexten"   => array(      "LABEL"                  => $arrLang["Regexten"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "restrictcid"   => array(      "LABEL"                  => $arrLang["Restric tc id"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "rtptimeout"   => array(      "LABEL"                  => $arrLang["Rtp time out"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "numeric",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "rtpholdtimeout"   => array(      "LABEL"                  => $arrLang["Rtp hold timeout"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "numeric",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "setvar"   => array(      "LABEL"                  => $arrLang["Setvar"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "disallow"   => array(      "LABEL"                  => $arrLang["Disallow"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "SELECT",
                                        "INPUT_EXTRA_PARAM"      => $arrOptions,
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => "",
                                        "EDITABLE"               => "si",
                                        ),
            "allow"   => array(      "LABEL"                  => $arrLang["Allow"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "",
                                        "VALIDATION_EXTRA_PARAM" => "",
                                        "EDITABLE"               => "si",
                                        ),
            "fullcontact"   => array(      "LABEL"                  => $arrLang["Full contact"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "ipaddr"   => array(      "LABEL"                  => $arrLang["Ip Address"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "port"   => array(      "LABEL"                  => $arrLang["Port"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "numeric",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "user_name"   => array(      "LABEL"                  => $arrLang["User Name"],
                                        "REQUIRED"               => "yes",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "reg_server"   => array(      "LABEL"                  => $arrLang["Reg Server"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            "reg_seconds"   => array(      "LABEL"                  => $arrLang["Reg Seconds"],
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "numeric",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                        ),
            //Extension Options
            "ring_time"   => array( "LABEL"                  => $arrLang["Ring Time"],
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "",
                                    "INPUT_EXTRA_PARAM"      => "",
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => ""
                                ),
            "call_waiting"   => array( "LABEL"                  => $arrLang["Call Waiting"],
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "",
                                    "INPUT_EXTRA_PARAM"      => "",
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => ""
                                ),
            //Devices Options
            "secret"   => array(    "LABEL"                  => $arrLang["Secret"],
                                    "REQUIRED"               => "yes",
                                    "INPUT_TYPE"             => "TEXT",
                                    "INPUT_EXTRA_PARAM"      => "",
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => ""
                                    ),
        
            "dtmfmode"   => array( "LABEL"                  => $arrLang["Dtmfmode"],
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "",
                                    "INPUT_EXTRA_PARAM"      => "",
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => ""
                                ),
            //Recording Options
            "incoming"   => array(    "LABEL"                  => $arrLang["Recording incoming"],
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "TEXT",
                                    "INPUT_EXTRA_PARAM"      => "",
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => ""
                                    ),
        
            "outgoing"   => array( "LABEL"                  => $arrLang["Recording outgoing"],
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "",
                                    "INPUT_EXTRA_PARAM"      => "",
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => ""
                                ),
            //Voicemail & Directory
            "status"   => array(    "LABEL"                  => $arrLang["Status"],
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "TEXT",
                                    "INPUT_EXTRA_PARAM"      => "",
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => ""
                                    ),
            "voicemailpassword"   => array(    "LABEL"                  => $arrLang["Voicemail Password"],
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "TEXT",
                                    "INPUT_EXTRA_PARAM"      => "",
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => ""
                                    ),
            "emailaddress"   => array(    "LABEL"                  => $arrLang["Email Address"],
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "TEXT",
                                    "INPUT_EXTRA_PARAM"      => "",
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => ""
                                    ),
            );
    return $arrFields;
}


function save_sip_buddies($smarty, $module_name, $local_templates_dir, $pDB, $arrLang,$update=FALSE)
{
    $arrFormNewSipBuddies = createFieldForm($arrLang);
    $oForm = new paloForm($smarty,$arrFormNewSipBuddies);
    
    if(!$oForm->validateForm($_POST)) {
        $smarty->assign("mb_title", $arrLang["Validation Error"]);

        $arrErrores = $oForm->arrErroresValidacion;
        $strErrorMsg = "<b>{$arrLang['The following fields contain errors']}:</b><br/>";
        if(is_array($arrErrores) && count($arrErrores) > 0){
            foreach($arrErrores as $k=>$v) {
                $strErrorMsg .= "$k, ";
            }
        }

        $smarty->assign("mb_message", $strErrorMsg);
        $smarty->assign("SAVE", $arrLang["Save"]);
        $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
        $smarty->assign("IMG", "images/list.png");
        $smarty->assign("CANCEL", $arrLang["Cancel"]);
        
        if($update){
            $_POST["edit"] = 'edit';
            return view_sip_buddies($smarty,$module_name, $local_templates_dir, $pDB, $arrLang);
        }else{
            $smarty->assign("Show", 1);
            $htmlForm = $oForm->fetchForm("$local_templates_dir/new_sipBuddies.tpl", $arrLang["New SipBuddies"], $_POST);
            $contenidoModulo = "<form  method='POST' enctype='multipart/form-data' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

            return $contenidoModulo;
        }

    }else{
        $data = array();        
    
        if($_POST['name']!="" || !empty($_POST['name'])) $data['name'] = $pDB->DBCAMPO($_POST['name']); 
        if($_POST['host']!="" || !empty($_POST['host'])) $data['host'] = $pDB->DBCAMPO($_POST['host']);
        if($_POST['nat']!="" || !empty($_POST['nat'])) $data['nat'] = $pDB->DBCAMPO($_POST['nat']);
        if($_POST['type']!="" || !empty($_POST['type'])) $data['type'] = $pDB->DBCAMPO(trim($_POST['type']));
        if($_POST['accountcode']!="" || !empty($_POST['accountcode'])) $data['accountcode'] = $pDB->DBCAMPO($_POST['accountcode']);
        if($_POST['amaflags']!="" || !empty($_POST['amaflags'])) $data['amaflags'] = $pDB->DBCAMPO($_POST['amaflags']);
        if($_POST['call_limit']!="" || !empty($_POST['call_limit'])) $data['call-limit'] = $pDB->DBCAMPO($_POST['call_limit']);
        if($_POST['call_group']!="" || !empty($_POST['call_group'])) $data['callgroup'] = $pDB->DBCAMPO($_POST['call_group']);
        if($_POST['caller_id']!="" || !empty($_POST['caller_id'])) $data['callerid'] = $pDB->DBCAMPO($_POST['caller_id']);
        if($_POST['can_call_forward']!="" || !empty($_POST['can_call_forward'])) $data['cancallforward'] = $pDB->DBCAMPO($_POST['can_call_forward']);
        if($_POST['can_reinvite']!="" || !empty($_POST['can_reinvite'])) $data['canreinvite'] = $pDB->DBCAMPO($_POST['can_reinvite']);
        if($_POST['context']!="" || !empty($_POST['context'])) $data['context'] = $pDB->DBCAMPO($_POST['context']);
        if($_POST['default_tip']!="" || !empty($_POST['default_tip'])) $data['defaultip'] = $pDB->DBCAMPO($_POST['default_tip']);
        if($_POST['dtmfmode']!="" || !empty($_POST['dtmfmode'])) $data['dtmfmode'] = $pDB->DBCAMPO($_POST['dtmfmode']);
        if($_POST['from_user']!="" || !empty($_POST['from_user'])) $data['fromuser'] = $pDB->DBCAMPO($_POST['from_user']);
        if($_POST['from_domain']!="" || !empty($_POST['from_domain'])) $data['fromdomain'] = $pDB->DBCAMPO($_POST['from_domain']);
        if($_POST['insecure']!="" || !empty($_POST['insecure'])) $data['insecure'] = $pDB->DBCAMPO($_POST['insecure']);
        if($_POST['language']!="" || !empty($_POST['language'])) $data['language'] = $pDB->DBCAMPO($_POST['language']);
        if($_POST['mailbox']!="" || !empty($_POST['mailbox'])) $data['mailbox'] = $pDB->DBCAMPO($_POST['mailbox']);
        if($_POST['md5secret']!="" || !empty($_POST['md5secret'])) $data['md5secret'] = $pDB->DBCAMPO($_POST['md5secret']);
        if($_POST['deny']!="" || !empty($_POST['deny'])) $data['deny'] = $pDB->DBCAMPO($_POST['deny']); else $data['deny'] = $pDB->DBCAMPO("0.0.0.0/0.0.0.0");
        if($_POST['permit']!="" || !empty($_POST['permit'])) $data['permit'] = $pDB->DBCAMPO($_POST['permit']);
        if($_POST['mask']!="" || !empty($_POST['mask'])) $data['mask'] = $pDB->DBCAMPO($_POST['mask']);
        if($_POST['musiconhold']!="" || !empty($_POST['musiconhold'])) $data['musiconhold'] = $pDB->DBCAMPO($_POST['musiconhold']);
        if($_POST['pickupgroup']!="" || !empty($_POST['pickupgroup'])) $data['pickupgroup'] = $pDB->DBCAMPO($_POST['pickupgroup']);
        if($_POST['qualify']!="" || !empty($_POST['qualify'])) $data['qualify'] = $pDB->DBCAMPO($_POST['qualify']);
        if($_POST['regexten']!="" || !empty($_POST['regexten'])) $data['regexten'] = $pDB->DBCAMPO($_POST['regexten']);
        if($_POST['restrictcid']!="" || !empty($_POST['restrictcid'])) $data['restrictcid'] = $pDB->DBCAMPO($_POST['restrictcid']);
        if($_POST['rtptimeout']!="" || !empty($_POST['rtptimeout'])) $data['rtptimeout'] = $pDB->DBCAMPO('rtptimeout');
        if($_POST['rtpholdtimeout']!="" || !empty($_POST['rtpholdtimeout'])) $data['rtpholdtimeout'] = $pDB->DBCAMPO($_POST['rtpholdtimeout']);
        if($_POST['secret']!="" || !empty($_POST['secret'])) $data['secret'] = $pDB->DBCAMPO($_POST['secret']);   
        if($_POST['setvar']!="" || !empty($_POST['setvar'])) $data['setvar'] = $pDB->DBCAMPO($_POST['setvar']);        
        if($_POST['disallow']!="" || !empty($_POST['disallow'])) $data['disallow'] = $pDB->DBCAMPO($_POST['disallow']);
        if($_POST['allow']!="" || !empty($_POST['allow'])) $data['allow'] = $pDB->DBCAMPO($_POST['allow']);
        if($_POST['fullcontact']!="" || !empty($_POST['fullcontact'])) $data['fullcontact'] = $pDB->DBCAMPO($_POST['fullcontact']);
        if($_POST['ipaddr']!="" || !empty($_POST['ipaddr'])) $data['ipaddr'] = $pDB->DBCAMPO($_POST['ipaddr']);
        if($_POST['port']!="" || !empty($_POST['port'])) $data['port'] = $pDB->DBCAMPO($_POST['port']);
        if($_POST['reg_server']!="" || !empty($_POST['reg_server'])) $data['regserver'] = $pDB->DBCAMPO($_POST['reg_server']);
        if($_POST['reg_seconds']!="" || !empty($_POST['reg_seconds'])) $data['regseconds'] = $pDB->DBCAMPO($_POST['reg_seconds']);
        if($_POST['user_name']!="" || !empty($_POST['user_name'])) $data['username'] = $pDB->DBCAMPO($_POST['user_name']);
                
        $pSipBuddies = new paloSantoSip_buddies($pDB);
        
        if($update)
            $result = $pSipBuddies->updateSipBuddies($data, array("id"=>$_POST['id']));
        else
            $result = $pSipBuddies->addSipBuddies($data);
        
        if(!$result)
            return($pDB->errMsg);
        else{
            $smarty->assign("mb_title", $arrLang["Result transaction"]);
            $smarty->assign("mb_message", "Saved successful");
        }

        //seteo de dato
        if($_POST['id'])
            header("Location: ?menu=$module_name&action=show_sip&id=".$_POST['id']);
        else
            header("Location: ?menu=$module_name");
    }

}


function save_iax_buddies($smarty, $module_name, $local_templates_dir, $pDB, $arrLang,$update=FALSE)
{
    $arrFormNewIaxBuddies = createFieldForm($arrLang);
    $oForm = new paloForm($smarty,$arrFormNewIaxBuddies);
    
    if(!$oForm->validateForm($_POST)) {
        $smarty->assign("mb_title", $arrLang["Validation Error"]);

        $arrErrores = $oForm->arrErroresValidacion;
        $strErrorMsg = "<b>{$arrLang['The following fields contain errors']}:</b><br/>";
        if(is_array($arrErrores) && count($arrErrores) > 0){
            foreach($arrErrores as $k=>$v) {
                $strErrorMsg .= "$k, ";
            }
        }

        $smarty->assign("mb_message", $strErrorMsg);
        $smarty->assign("SAVE", $arrLang["Save"]);
        $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
        $smarty->assign("IMG", "images/list.png");
        $smarty->assign("CANCEL", $arrLang["Cancel"]);
        
        if($update){
            $_POST["edit"] = 'edit';
            return view_iax_buddies($smarty,$module_name, $local_templates_dir, $pDB, $arrLang);
        }else{
            $smarty->assign("Show", 1);
            $htmlForm = $oForm->fetchForm("$local_templates_dir/new_iaxBuddies.tpl", $arrLang["New Iax Buddies"], $_POST);
            $contenidoModulo = "<form  method='POST' enctype='multipart/form-data' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

            return $contenidoModulo;
        }

    }else{
        $data = array();        
    
        if($_POST['name']!="" || !empty($_POST['name'])) $data['name'] = $pDB->DBCAMPO($_POST['name']); 
        if($_POST['host']!="" || !empty($_POST['host'])) $data['host'] = $pDB->DBCAMPO($_POST['host']);
        if($_POST['dbsecret']!="" || !empty($_POST['dbsecret'])) $data['dbsecret'] = $pDB->DBCAMPO(trim($_POST['dbsecret']));
        if($_POST['type']!="" || !empty($_POST['type'])) $data['type'] = $pDB->DBCAMPO(trim($_POST['type']));        
        if($_POST['notransfer']!="" || !empty($_POST['notransfer'])) $data['notransfer'] = $pDB->DBCAMPO($_POST['notransfer']);        
        if($_POST['caller_id']!="" || !empty($_POST['caller_id'])) $data['callerid'] = $pDB->DBCAMPO($_POST['caller_id']);
        if($_POST['inkeys']!="" || !empty($_POST['inkeys'])) $data['inkeys'] = $pDB->DBCAMPO($_POST['inkeys']);
        if($_POST['outkey']!="" || !empty($_POST['outkey'])) $data['outkey'] = $pDB->DBCAMPO($_POST['outkey']);
        if($_POST['auth']!="" || !empty($_POST['auth'])) $data['auth'] = $pDB->DBCAMPO($_POST['auth']);
        if($_POST['accountcode']!="" || !empty($_POST['accountcode'])) $data['accountcode'] = $pDB->DBCAMPO($_POST['accountcode']);
        if($_POST['amaflags']!="" || !empty($_POST['amaflags'])) $data['amaflags'] = $pDB->DBCAMPO($_POST['amaflags']);
        if($_POST['context']!="" || !empty($_POST['context'])) $data['context'] = $pDB->DBCAMPO($_POST['context']);
        if($_POST['default_tip']!="" || !empty($_POST['default_tip'])) $data['defaultip'] = $pDB->DBCAMPO($_POST['default_tip']);
        if($_POST['language']!="" || !empty($_POST['language'])) $data['language'] = $pDB->DBCAMPO($_POST['language']);
        if($_POST['mailbox']!="" || !empty($_POST['mailbox'])) $data['mailbox'] = $pDB->DBCAMPO($_POST['mailbox']);
        if($_POST['md5secret']!="" || !empty($_POST['md5secret'])) $data['md5secret'] = $pDB->DBCAMPO($_POST['md5secret']);
        if($_POST['deny']!="" || !empty($_POST['deny'])) $data['deny'] = $pDB->DBCAMPO($_POST['deny']); else $data['deny'] = $pDB->DBCAMPO("0.0.0.0/0.0.0.0");
        if($_POST['permit']!="" || !empty($_POST['permit'])) $data['permit'] = $pDB->DBCAMPO($_POST['permit']);
        if($_POST['qualify']!="" || !empty($_POST['qualify'])) $data['qualify'] = $pDB->DBCAMPO($_POST['qualify']);
        if($_POST['secret']!="" || !empty($_POST['secret'])) $data['secret'] = $pDB->DBCAMPO($_POST['secret']);   
        if($_POST['disallow']!="" || !empty($_POST['disallow'])) $data['disallow'] = $pDB->DBCAMPO($_POST['disallow']);
        if($_POST['allow']!="" || !empty($_POST['allow'])) $data['allow'] = $pDB->DBCAMPO($_POST['allow']);
        if($_POST['ipaddr']!="" || !empty($_POST['ipaddr'])) $data['ipaddr'] = $pDB->DBCAMPO($_POST['ipaddr']);
        if($_POST['port']!="" || !empty($_POST['port'])) $data['port'] = $pDB->DBCAMPO($_POST['port']);
        if($_POST['reg_seconds']!="" || !empty($_POST['reg_seconds'])) $data['regseconds'] = $pDB->DBCAMPO($_POST['reg_seconds']);
        if($_POST['user_name']!="" || !empty($_POST['user_name'])) $data['username'] = $pDB->DBCAMPO($_POST['user_name']);        
        $pIax_buddies = new paloSantoIax_buddies($pDB);
        
        if($update)
            $result = $pIax_buddies->updateIaxBuddies($data, array("id"=>$_POST['id']));
        else
            $result = $pIax_buddies->addIaxBuddies($data);
        
        if(!$result)
            return($pDB->errMsg);
        else{
            $smarty->assign("mb_title", $arrLang["Result transaction"]);
            $smarty->assign("mb_message", "Saved successful");
        }

        //seteo de dato
        if($_POST['id'])
            header("Location: ?menu=$module_name&action=show_iax&id=".$_POST['id']);
        else
            header("Location: ?menu=$module_name");
    }

}


function view_sip_buddies($smarty,$module_name, $local_templates_dir, $pDB, $arrLang){
    
    $pSipBuddies = new paloSantoSip_buddies($pDB);
    $arrFormNewSipBuddies = createFieldForm($arrLang);
    $oForm = new paloForm($smarty,$arrFormNewSipBuddies);
    
    setListOptions($smarty);
    if(isset($_POST["edit"])){
        $oForm->setEditMode();
        $smarty->assign("Commit", 1);
        $smarty->assign("SAVE",$arrLang["Save"]);
    }else{
        $oForm->setViewMode();
        $smarty->assign("Edit", 1);
    }

    $smarty->assign("IMG", "images/list.png");
    $smarty->assign("EDIT", $arrLang["Edit"]);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    
    $id = getParameter("id");

    $sipBuddiesData = $pSipBuddies->getSipBuddiesById($id);        
    
    $smarty->assign("ID",$id);

    $arrData['name']            = isset($_POST['name'])?$_POST['name']:$sipBuddiesData['name'];
    //$arrData['type']            = isset($_POST['type'])?$_POST['type']:$sipBuddiesData['type'];
    $smarty->assign("selected_type", trim($sipBuddiesData["type"]));
    $arrData['can_reinvite']    = isset($_POST['can_reinvite'])?$_POST['can_reinvite']:$sipBuddiesData['canreinvite'];
    $arrData['context']         = isset($_POST['context'])?$_POST['context']:$sipBuddiesData['context'];
    $arrData['user_name']       = isset($_POST['user_name'])?$_POST['user_name']:$sipBuddiesData['username'];
    $arrData['host']            = isset($_POST['host'])?$_POST['host']:$sipBuddiesData['host'];
    //$arrData['nat']           = isset($_POST['nat'])?$_POST['nat']:$sipBuddiesData['nat'];
    $smarty->assign("selected_nat", trim($sipBuddiesData["nat"]));
    $arrData['accountcode']     = isset($_POST['accountcode'])?$_POST['accountcode']:$sipBuddiesData['accountcode'];
    $smarty->assign("selected_amaflags", trim($sipBuddiesData["amaflags"]));
    $arrData['call_limit']      = isset($_POST['call_limit'])?$_POST['call_limit']:$sipBuddiesData['call-limit'];
    $arrData['call_group']      = isset($_POST['call_group'])?$_POST['call_group']:$sipBuddiesData['callgroup'];
    $arrData['caller_id']       = isset($_POST['caller_id'])?$_POST['caller_id']:$sipBuddiesData['callerid'];
    $smarty->assign("selected_can_call_forward", trim($sipBuddiesData["cancallforward"]));
    $smarty->assign("selected_can_reinvite", trim($sipBuddiesData["canreinvite"]));
    $arrData['default_tip']     = isset($_POST['default_tip'])?$_POST['default_tip']:$sipBuddiesData['defaultip'];
    $arrData['from_user']       = isset($_POST['from_user'])?$_POST['from_user']:$sipBuddiesData['fromuser'];
    $arrData['from_domain']     = isset($_POST['from_domain'])?$_POST['from_domain']:$sipBuddiesData['fromdomain'];
    $arrData['insecure']        = isset($_POST['insecure'])?$_POST['insecure']:$sipBuddiesData['insecure'];
    $arrData['language']        = isset($_POST['language'])?$_POST['language']:$sipBuddiesData['language'];
    $arrData['mailbox']         = isset($_POST['mailbox'])?$_POST['mailbox']:$sipBuddiesData['mailbox'];
    $arrData['md5secret']       = isset($_POST['md5secret'])?$_POST['md5secret']:$sipBuddiesData['md5secret'];
    $arrData['deny']            = isset($_POST['deny'])?$_POST['deny']:$sipBuddiesData['deny'];
    $arrData['permit']          = isset($_POST['permit'])?$_POST['permit']:$sipBuddiesData['permit'];
    $arrData['mask']            = isset($_POST['mask'])?$_POST['mask']:$sipBuddiesData['mask'];
    $arrData['musiconhold']     = isset($_POST['musiconhold'])?$_POST['musiconhold']:$sipBuddiesData['musiconhold'];
    $arrData['pickupgroup']     = isset($_POST['pickupgroup'])?$_POST['pickupgroup']:$sipBuddiesData['pickupgroup'];
    $arrData['qualify']         = isset($_POST['qualify'])?$_POST['qualify']:$sipBuddiesData['qualify'];
    $arrData['regexten']        = isset($_POST['regexten'])?$_POST['regexten']:$sipBuddiesData['regexten'];
    $arrData['restrictcid']     = isset($_POST['restrictcid'])?$_POST['restrictcid']:$sipBuddiesData['restrictcid'];
    $arrData['rtptimeout']      = isset($_POST['rtptimeout'])?$_POST['rtptimeout']:$sipBuddiesData['rtptimeout'];
    $arrData['rtpholdtimeout']  = isset($_POST['rtpholdtimeout'])?$_POST['rtpholdtimeout']:$sipBuddiesData['rtpholdtimeout'];
    $arrData['setvar']          = isset($_POST['setvar'])?$_POST['setvar']:$sipBuddiesData['setvar'];
    $arrData['disallow']        = isset($_POST['disallow'])?$_POST['disallow']:$sipBuddiesData['disallow'];
    $smarty->assign("selected_allow", trim($sipBuddiesData["allow"]));
    //$arrData['allow']           = isset($_POST['allow'])?$_POST['allow']:$sipBuddiesData['allow'];
    $arrData['fullcontact']     = isset($_POST['fullcontact'])?$_POST['fullcontact']:$sipBuddiesData['fullcontact'];
    $arrData['ipaddr']      = isset($_POST['ipaddr'])?$_POST['ipaddr']:$sipBuddiesData['ipaddr'];
    $arrData['port']            = isset($_POST['port'])?$_POST['port']:$sipBuddiesData['port'];
    $arrData['reg_server']      = isset($_POST['reg_server'])?$_POST['reg_server']:$sipBuddiesData['regserver'];
    $arrData['reg_seconds']     = isset($_POST['reg_seconds'])?$_POST['reg_seconds']:$sipBuddiesData['regseconds'];
    
    $arrData['secret']          = isset($_POST['secret'])?$_POST['secret']:$sipBuddiesData['secret'];
    $smarty->assign("selected_dtmfmode", trim($sipBuddiesData["dtmfmode"]));    

    $htmlForm = $oForm->fetchForm("$local_templates_dir/new_sipBuddies.tpl", $arrLang["Sip buddies"], $arrData);
    $contenidoModulo = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $contenidoModulo;
}

function view_iax_buddies($smarty,$module_name, $local_templates_dir, $pDB, $arrLang){
    
    $pIaxBuddies = new paloSantoIax_buddies($pDB);
    $arrFormNewSipBuddies = createFieldForm($arrLang);
    $oForm = new paloForm($smarty,$arrFormNewSipBuddies);
    
    setListOptions($smarty);
    if(isset($_POST["edit"])){
        $oForm->setEditMode();
        $smarty->assign("Commit", 1);
        $smarty->assign("SAVE",$arrLang["Save"]);
    }else{
        $oForm->setViewMode();
        $smarty->assign("Edit", 1);
    }

    $smarty->assign("IMG", "images/list.png");
    $smarty->assign("EDIT", $arrLang["Edit"]);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    
    $id = getParameter("id");

    $iaxBuddiesData = $pIaxBuddies->getIaxBuddiesById($id);        
    
    $smarty->assign("ID",$id);

    $arrData['name']            = isset($_POST['name'])?$_POST['name']:$iaxBuddiesData['name'];
    $smarty->assign("selected_type", trim($iaxBuddiesData["type"]));
    $arrData['context']         = isset($_POST['context'])?$_POST['context']:$iaxBuddiesData['context'];
    $arrData['dbsecret']        = isset($_POST['dbsecret'])?$_POST['dbsecret']:$iaxBuddiesData['dbsecret'];
    $arrData['notransfer']      = isset($_POST['notransfer'])?$_POST['notransfer']:$iaxBuddiesData['notransfer'];
    $arrData['inkeys']          = isset($_POST['inkeys'])?$_POST['inkeys']:$iaxBuddiesData['inkeys'];
    $arrData['outkey']          = isset($_POST['outkey'])?$_POST['outkey']:$iaxBuddiesData['outkey'];
    $arrData['auth']            = isset($_POST['auth'])?$_POST['auth']:$iaxBuddiesData['auth'];
    $arrData['user_name']       = isset($_POST['user_name'])?$_POST['user_name']:$iaxBuddiesData['username'];
    $arrData['host']            = isset($_POST['host'])?$_POST['host']:$iaxBuddiesData['host'];
    $arrData['accountcode']     = isset($_POST['accountcode'])?$_POST['accountcode']:$iaxBuddiesData['accountcode'];
    $smarty->assign("selected_amaflags", trim($iaxBuddiesData["amaflags"]));
    $arrData['caller_id']       = isset($_POST['caller_id'])?$_POST['caller_id']:$iaxBuddiesData['callerid'];
    $arrData['default_tip']     = isset($_POST['default_tip'])?$_POST['default_tip']:$iaxBuddiesData['defaultip'];
        
    $arrData['language']        = isset($_POST['language'])?$_POST['language']:$iaxBuddiesData['language'];
    $arrData['mailbox']         = isset($_POST['mailbox'])?$_POST['mailbox']:$iaxBuddiesData['mailbox'];
    $arrData['md5secret']       = isset($_POST['md5secret'])?$_POST['md5secret']:$iaxBuddiesData['md5secret'];
    $arrData['deny']            = isset($_POST['deny'])?$_POST['deny']:$iaxBuddiesData['deny'];
    $arrData['permit']          = isset($_POST['permit'])?$_POST['permit']:$iaxBuddiesData['permit'];
    $arrData['qualify']         = isset($_POST['qualify'])?$_POST['qualify']:$iaxBuddiesData['qualify'];    
    $arrData['disallow']        = isset($_POST['disallow'])?$_POST['disallow']:$iaxBuddiesData['disallow'];
    $smarty->assign("selected_allow", trim($iaxBuddiesData["allow"]));
    $arrData['ipaddr']      = isset($_POST['ipaddr'])?$_POST['ipaddr']:$iaxBuddiesData['ipaddr'];
    $arrData['port']            = isset($_POST['port'])?$_POST['port']:$iaxBuddiesData['port'];
    $arrData['reg_seconds']     = isset($_POST['reg_seconds'])?$_POST['reg_seconds']:$iaxBuddiesData['regseconds'];
    $arrData['secret']          = isset($_POST['secret'])?$_POST['secret']:$iaxBuddiesData['secret'];    

    $htmlForm = $oForm->fetchForm("$local_templates_dir/new_iaxBuddies.tpl", $arrLang["Iax buddies"], $arrData);
    $contenidoModulo = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $contenidoModulo;
}


function delete_sip_buddies($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang){
    
    $pSipBuddies = new paloSantoSip_buddies($pDB);

    foreach($_POST as $key => $values){
        if(substr($key,0,9) == "SipBubID_")
        {
            $tmpSipBID = substr($key, 9);
            $result = $pSipBuddies->deleteSipBuddies($tmpSipBID);
        }
    }
    $content = reportSip_buddies($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
    
    return $content;
}

function delete_iax_buddies($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang){
    
    $pSipBuddies = new paloSantoIax_buddies($pDB);

    foreach($_POST as $key => $values){
        if(substr($key,0,9) == "IaxBubID_")
        {
            $tmpSipBID = substr($key, 9);
            $result = $pSipBuddies->deleteIaxBuddies($tmpSipBID);
        }
    }
    $content = reportIax_buddies($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
    
    return $content;
}

function getAction()
{
    if(getParameter("new_sip_buddies")) 
        return "new_sip_buddies";
    else if(getParameter("new_iax_buddies")) 
        return "new_iax_buddies";
    else if(getParameter("edit_sip"))
        return "edit_sip";
    else if(getParameter("commit_sip"))
        return "commit_sip";
    else if(getParameter("commit_iax"))
        return "commit_iax";
    else if(getParameter("save_sip"))
        return "save_sip";
    else if(getParameter("save_iax"))
        return "save_iax";
    else if(getParameter("delete_sip"))
        return "delete_sip";
    else if(getParameter("delete_iax"))
        return "delete_iax";
    else if(getParameter("cancel_sip"))
        return "cancel_sip";
    else if(getParameter("cancel_iax"))
        return "cancel_iax";
    else if(getParameter("action")=="showIax")
        return "show_reportIax";
    else if(getParameter("action")=="show_iax")
        return "show_iax";
    else if(getParameter("action")=="show_sip")
        return "show_sip";
    else if(getParameter("showIax"))
        return "show_reportIax";
    else if(getParameter("action")=="show") //Get parameter by GET (command pattern, links)
        return "show";
    else
        return "filter";
}
?>