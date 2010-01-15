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
        case "show":
            $content = view_sip_buddies($smarty,$module_name, $local_templates_dir, $pDB, $arrLang);
            break;
        case "edit":
            $content = view_sip_buddies($smarty,$module_name, $local_templates_dir, $pDB, $arrLang);
            break;
        case "save":
            $content = save_sip_buddies($smarty, $module_name, $local_templates_dir, $pDB, $arrLang);
            break;
        case "commit":
            $content = save_sip_buddies($smarty, $module_name, $local_templates_dir, $pDB, $arrLang, true);
            break;
        case "delete":
            $content = delete_sip_buddies($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        case "cancel":
            header("Location: ?menu=$module_name");
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
	    $arrTmp[1] = "<a href='?menu=$module_name&action=show&id=".$value['id']."'>{$value['name']}</a>";
	    $arrTmp[2] = $value['context'];
	    $arrTmp[3] = $value['secret'];
	    $arrTmp[4] = $value['username'];
            $arrData[] = $arrTmp;
        }
    }

    $button = "<input type='submit' name='delete' value='{$arrLang["Delete"]}' class='button' onclick=\" return confirmSubmit('{$arrLang["Are you sure you wish to delete the contact."]}');\" />";

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
    
    $smarty->assign('selec_type', array(
                            'user' => 'User',
                            'peer' => 'Peer',
                            'friend' => 'Friend',
                            ));
    $smarty->assign('selec_nat', array(
                            'yes' => 'yes',
                            'route' => 'route',
                            'no' => 'no',
                            'never' => 'never',
                            ));
    $smarty->assign('selec_amaflags', array(
                            'default' => 'Default',
                            'omit' => 'Omit',
                            'billing' => 'Billing',
                            'documentation' => 'Documentation',
                            ));

    $smarty->assign('selec_ring_time', array ('default' => 'Default'));
    $smarty->assign('selec_ring_time', array(
                              '0' => '0',
                              '1' => '1',
                              '2' => '2',
                              '3' => '3',
                              '4' => '4',
                              '5' => '5',
                              '6' => '6',
                              '7' => '7'));
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
 
    $htmlForm = $oForm->fetchForm("$local_templates_dir/new_sipBuddies.tpl", $arrLang["New SipBuddies"], $_POST);
    $contenidoModulo = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $contenidoModulo;
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
                                            "REQUIRED"               => "",
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
                                            "INPUT_TYPE"             => "CHECKBOX",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "can_reinvite"   => array(      "LABEL"                  => $arrLang["Can Reinvite"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "RADIO",
                                            "INPUT_EXTRA_PARAM"      => $arrOpYesNo,
                                            "VALIDATION_TYPE"        => "text",
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
                                            "REQUIRED"               => "yes",
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
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrOptions,
                                            "VALIDATION_TYPE"        => "text",
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
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "user_name"   => array(      "LABEL"                  => $arrLang["User Name"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "reg_second"   => array(      "LABEL"                  => $arrLang["Reg Second"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
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
                                    "REQUIRED"               => "yes",
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
                                    "REQUIRED"               => "yes",
                                    "INPUT_TYPE"             => "TEXT",
                                    "INPUT_EXTRA_PARAM"      => "",
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => ""
                                    ),
            "voicemailpassword"   => array(    "LABEL"                  => $arrLang["Voicemail Password"],
                                    "REQUIRED"               => "yes",
                                    "INPUT_TYPE"             => "TEXT",
                                    "INPUT_EXTRA_PARAM"      => "",
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => ""
                                    ),
            "emailaddress"   => array(    "LABEL"                  => $arrLang["Email Address"],
                                    "REQUIRED"               => "yes",
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

        $data['name'] = $pDB->DBCAMPO($_POST['name']);
        $data['callerid'] = $pDB->DBCAMPO($_POST['caller_id']);
        $data['canreinvite'] = $pDB->DBCAMPO($_POST['can_reinvite']);
        $data['context'] = $pDB->DBCAMPO($_POST['context']);
        $data['host'] = $pDB->DBCAMPO($_POST['host']);
        $data['insecure'] = $pDB->DBCAMPO($_POST['insecure']);
        $data['nat'] = $pDB->DBCAMPO($_POST['nat']);
        $data['port'] = $pDB->DBCAMPO($_POST['port']);
        $data['secret'] = $pDB->DBCAMPO($_POST['secret']);
        $data['type'] = $pDB->DBCAMPO($_POST['type']);
        $data['username'] = $pDB->DBCAMPO($_POST['user_name']);
        $data['disallow'] = $pDB->DBCAMPO($_POST['disallow']);
        $data['allow'] = $pDB->DBCAMPO($_POST['allow']);
        $data['regseconds'] = $pDB->DBCAMPO($_POST['reg_second']);
        $data['ipaddr'] = $pDB->DBCAMPO($_POST['ip_address']);
        $data['cancallforward'] = $pDB->DBCAMPO($_POST['can_call_forward']);
        
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
            header("Location: ?menu=$module_name&action=show&id=".$_POST['id']);
        else
            header("Location: ?menu=$module_name");
    }

}



function view_sip_buddies($smarty,$module_name, $local_templates_dir, $pDB, $arrLang){
    
    $pSipBuddies = new paloSantoSip_buddies($pDB);
    $arrFormNewSipBuddies = createFieldForm($arrLang);
    $oForm = new paloForm($smarty,$arrFormNewSipBuddies);

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
    
//     $id = isset($_GET['id'])?$_GET['id']:(isset($_POST['id'])?$_POST['id']:"");
    $id = getParameter("id");

    $sipBuddiesData = $pSipBuddies->getSipBuddiesById($id);        
    
    $smarty->assign("ID",$id);

    $arrData['name']            = isset($_POST['name'])?$_POST['name']:$sipBuddiesData['name'];
    $arrData['caller_id']       = isset($_POST['caller_id'])?$_POST['caller_id']:$sipBuddiesData['callerid'];
    $arrData['can_reinvite']    = isset($_POST['can_reinvite'])?$_POST['can_reinvite']:$sipBuddiesData['canreinvite'];
    $arrData['context']         = isset($_POST['context'])?$_POST['context']:$sipBuddiesData['context'];
    $arrData['host']            = isset($_POST['host'])?$_POST['host']:$sipBuddiesData['host'];
    $arrData['insecure']        = isset($_POST['insecure'])?$_POST['insecure']:$sipBuddiesData['insecure'];
    $arrData['nat']             = isset($_POST['nat'])?$_POST['nat']:$sipBuddiesData['nat'];
    $arrData['port']            = isset($_POST['port'])?$_POST['port']:$sipBuddiesData['port'];
    $arrData['secret']          = isset($_POST['secret'])?$_POST['secret']:$sipBuddiesData['secret'];
    $arrData['type']            = isset($_POST['type'])?$_POST['type']:$sipBuddiesData['type'];
    $arrData['user_name']       = isset($_POST['user_name'])?$_POST['user_name']:$sipBuddiesData['username'];
    $arrData['disallow']        = isset($_POST['disallow'])?$_POST['disallow']:$sipBuddiesData['disallow'];
    $arrData['allow']           = isset($_POST['allow'])?$_POST['allow']:$sipBuddiesData['allow'];
    $arrData['reg_second']      = isset($_POST['reg_second'])?$_POST['reg_second']:$sipBuddiesData['regseconds'];
    $arrData['ip_address']      = isset($_POST['ip_address'])?$_POST['ip_address']:$sipBuddiesData['ipaddr'];
    $arrData['can_call_forward']= isset($_POST['can_call_forward'])?$_POST['can_call_forward']:$sipBuddiesData['cancallforward'];

    $htmlForm = $oForm->fetchForm("$local_templates_dir/new_sipBuddies.tpl", $arrLang["Sip buddies"], $arrData);
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

function getAction()
{
    if(getParameter("new_sip_buddies")) //Get parameter by POST (submit)
        return "new_sip_buddies";
    if(getParameter("show")) //Get parameter by POST (submit)
        return "show";
    if(getParameter("edit"))
        return "edit";
    if(getParameter("commit"))
        return "commit";
    else if(getParameter("save"))
        return "save";
    else if(getParameter("delete"))
        return "delete";
    else if(getParameter("action")=="show") //Get parameter by GET (command pattern, links)
        return "show";
    else
        return "filter";
}
?>