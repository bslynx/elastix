<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.4-2                                               |
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
  $Id: index.php,v 1.1 2008-09-11 03:09:47 Jonathan jvega@palosanto.com Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoDB.class.php";
include_once "modules/sec_ports/libs/paloSantoPortService.class.php";
include_once "libs/paloSantoJSON.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoRules.class.php";

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
    //$str_conn = "sqlite3:////var/www/db/iptables.db";
    $pDB = new paloDB($arrConf['dsn_conn_database']);

    //actions
    $accion = getAction();
    switch($accion){
        case "new":
            $content = newRules($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, array());
            break;
        case "save":
            $content = saveRules($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case "getPorts":
            $content = getPorts($pDB);
            break;
        case "change":
            $content = change($pDB);
            break;
        case "exec":
            $content = execRules($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case "delete":
            $content = deleteFilter($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        default:
            $content = reportRules($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
    }
    return $content;
}

function newRules($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrValues, $action="")
{
    $pRules = new paloSantoRules($pDB);
    $arrFormRules = createFieldForm($pDB,$arrValues);
    $oForm = new paloForm($smarty,$arrFormRules);
    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("IMG", "images/list.png");
    $traffic = isset($arrValues['id_traffic']) ? $arrValues['id_traffic'] : "";
    $select_traffic_1 = ($traffic == "INPUT"  ) ? "selected" : "";
    $select_traffic_2 = ($traffic == "OUTPUT" ) ? "selected" : "";
    $select_traffic_3 = ($traffic == "FORWARD") ? "selected" : "";
    //************************************************************************
    $traffic_html =
        "<select id='id_traffic' name='id_traffic' onClick='showElementByTraffic();' >".
            "<option value='INPUT'   $select_traffic_1>"._tr("INPUT")."</option>".
            "<option value='OUTPUT'  $select_traffic_2>"._tr("OUTPUT")."</option>".
            "<option value='FORWARD' $select_traffic_3>"._tr("FORWARD")."</option>".
        "</select>";

    $smarty->assign("traffic_html", $traffic_html);
    $smarty->assign("traffic_label", _tr("Traffic"));
    //************************************************************************
    $protocol = isset($arrValues['id_protocol']) ? $arrValues['id_protocol'] : "";
    $protocol1 = ($protocol == "ALL") ? "selected" : "";
    $protocol2 = ($protocol == "TCP") ? "selected" : "";
    $protocol3 = ($protocol == "UDP") ? "selected" : "";
    $protocol4 = ($protocol == "ICMP") ? "selected" : "";
    $protocol5 = ($protocol == "IP") ? "selected" : "";
    $protocol_html =
        "<select id='id_protocol' name='id_protocol' onClick='showElementByProtocol();' >".
            "<option value='ALL' $protocol1>"._tr("ALL")."</option>".
            "<option value='TCP' $protocol2>TCP</option>".
            "<option value='UDP' $protocol3>UDP</option>".
            "<option value='ICMP' $protocol4>ICMP</option>".
            "<option value='IP' $protocol5>IP</option>".
        "</select>";

    $smarty->assign("protocol_html", $protocol_html);
    $smarty->assign("protocol_label", _tr("Protocol"));
    //************************************************************************
    $arrValues['ip_source'] = (isset($arrValues['ip_source'])) ? $arrValues['ip_source'] : "0.0.0.0";
    $arrValues['mask_source'] = (isset($arrValues['mask_source'])) ? $arrValues['mask_source'] : "24";
    $arrValues['ip_destin'] = (isset($arrValues['ip_destin'])) ? $arrValues['ip_destin'] : "0.0.0.0";
    $arrValues['mask_destin'] = (isset($arrValues['mask_destin'])) ? $arrValues['mask_destin'] : "24";
    if($action == "edit")
        $title = _tr("Edit Rule");
    else
        $title = _tr("New Rule");
    $htmlForm = $oForm->fetchForm("$local_templates_dir/new.tpl",$title, $arrValues);
    $contenidoModulo = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $contenidoModulo;
}

function createFieldForm($pDB,$arrValues = array())
{
    $oPort = new paloSantoPortService($pDB);
    $pRules = new paloSantoRules($pDB);
    if(isset($arrValues['id_protocol']))
        $Ports = ($arrValues['id_protocol'] == "TCP") ? $oPort->getTCPortNumbers() : $oPort->getUDPortNumbers();
    else
        $Ports = $oPort->getTCPortNumbers();
    $type = $oPort->getICMPType();
    $protocol_number = $oPort ->getIPProtNumber();
    $arrInterface['ANY'] = _tr('ANY');
    $arrInterfacetmp = $pRules->obtener_nombres_interfases_red();
    foreach($arrInterfacetmp as $key => $value)
        $arrInterface[$key] = $value;
    $arrTarget    = array("ACCEPT" => _tr("ACCEPT"), "DROP" => _tr("DROP"), "REJECT" => _tr("REJECT"));
    $arrType['ANY'] = _tr('ANY');
    foreach($type as $key => $value){
        $tmp = explode(":",$value['details']);
        $arrType[$tmp[0]] = $tmp[0];
    }
    $arrPort['ANY'] = _tr('ANY');
    foreach($Ports as $key => $value){
        $arrPort[$value['details']] = $value['details'];
    }
    $arrIP['ANY'] = _tr('ANY');    
    foreach($protocol_number as $key => $value){
        $arrIP[$value['details']] = $value['details'];
    }
    $arrFields = array(
            "interface_in"    => array( "LABEL"                  => _tr("Interface IN"),
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "SELECT",
                                        "INPUT_EXTRA_PARAM"      => $arrInterface,
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => "",
                                        "EDITABLE"               => "yes",
                                            ),
            "interface_out"   => array( "LABEL"                  => _tr("Interface OUT"),
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "SELECT",
                                        "INPUT_EXTRA_PARAM"      => $arrInterface,
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => "",
                                        "EDITABLE"               => "yes",
                                            ),
            "ip_source"       => array( "LABEL"                  => _tr("Source IP"),
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => array("style" => "width:90px"),
                                        "VALIDATION_TYPE"        => "ip",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "mask_source"     => array( "LABEL"                  => "mask_source",
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => array("style" => "width:20px"),
                                        "VALIDATION_TYPE"        => "numeric",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "ip_destin"      => array(  "LABEL"                  => _tr("Destine IP"),
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => array("style" => "width:90px"),
                                        "VALIDATION_TYPE"        => "ip",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "mask_destin"     => array( "LABEL"                  => "mask_destiny",
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => array("style" => "width:20px"),
                                        "VALIDATION_TYPE"        => "numeric",
                                        "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "port_in"         => array( "LABEL"                  => _tr("Port Source"),
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "SELECT",
                                        "INPUT_EXTRA_PARAM"      => $arrPort,
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => "",
                                        "EDITABLE"               => "yes",
                                            ),
            "port_out"        => array( "LABEL"                  => _tr("Port Destine"),
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "SELECT",
                                        "INPUT_EXTRA_PARAM"      => $arrPort,
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => "",
                                        "EDITABLE"               => "yes",
                                            ),
            "type_icmp"       => array( "LABEL"                  => _tr("Type"),
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "SELECT",
                                        "INPUT_EXTRA_PARAM"      => $arrType,
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => "",
                                        "EDITABLE"               => "yes",
                                            ),
            "id_ip"           => array( "LABEL"                  => _tr("ID"),
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "SELECT",
                                        "INPUT_EXTRA_PARAM"      => $arrIP,
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => "",
                                        "EDITABLE"               => "yes",
                                            ),
            //REJECT, ACCEPT, DROP
            "target"          => array( "LABEL"                  => _tr("Target"),
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "SELECT",
                                        "INPUT_EXTRA_PARAM"      => $arrTarget,
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => "",
                                        "EDITABLE"               => "yes",
                                            ),

            "orden"           => array(  "LABEL"                  => _tr("Order"),
                                         "REQUIRED"               => "no",
                                         "INPUT_TYPE"             => "TEXT",
                                         "INPUT_EXTRA_PARAM"      => "",
                                         "VALIDATION_TYPE"        => "text",
                                         "VALIDATION_EXTRA_PARAM" => "",
                                         "EDITABLE"               => "yes",
                                            ),
            "id"              => array(  "LABEL"                  => "",
                                         "REQUIRED"               => "no",
                                         "INPUT_TYPE"             => "TEXT",
                                         "INPUT_EXTRA_PARAM"      => "",
                                         "VALIDATION_TYPE"        => "text",
                                         "VALIDATION_EXTRA_PARAM" => "",
                                         "EDITABLE"               => "yes",
                                            )
            
            );
    return $arrFields;
}

function saveRules($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $arrValues = array();

    $str_error = "";
    $arrFormNew = createFieldForm($pDB);
    $oForm = new paloForm($smarty, $arrFormNew);
    $id = getParameter("id");
    $arrValues['id'] = $id;
    if($id == "")
        $state = "new";
    else
        $state = "edit";
    //************************************************************************************************************
    //** TRAFFIC **
    //************************************************************************************************************ 
    $arrValues['traffic'] = getParameter("id_traffic");
    if( $arrValues['traffic'] == "INPUT" ){
        $arrValues['interface_in'] = getParameter("interface_in");
        if( strlen($arrValues['interface_in']) == 0 )
            $str_error .= ( strlen($str_error) == 0 ) ? "interface_in" : ", interface_in" ;

        $arrValues['interface_out'] = null;
    }
    else if( $arrValues['traffic'] == "OUTPUT" ){
        $arrValues['interface_out'] = getParameter("interface_out");
        if( strlen($arrValues['interface_out']) == 0 )
            $str_error .= ( strlen($str_error) == 0 ) ? "interface_out" : ", interface_out" ;

        $arrValues['interface_in'] = null;
    }
    else if( $arrValues['traffic'] == "FORWARD" )
    {
        $arrValues['interface_in'] = getParameter("interface_in");
        if( strlen($arrValues['interface_in']) == 0 )
            $str_error .= ( strlen($str_error) == 0 ) ? "interface_in" : ", interface_in" ;

        $arrValues['interface_out'] = getParameter("interface_out");
        if( strlen($arrValues['interface_out']) == 0 )
            $str_error .= ( strlen($str_error) == 0 ) ? "interface_out" : ", interface_out" ;
    }

    //************************************************************************************************************
    //** SOURCE **
    //************************************************************************************************************

    $arrValues['ip_source'] = getParameter("ip_source");
    $arrValues['mask_source'] = ($arrValues['ip_source'] == "0.0.0.0") ? "0" : getParameter("mask_source");
    $arrValues['ip_destin'] = getParameter("ip_destin");
    $arrValues['mask_destin'] = ($arrValues['ip_destin'] == "0.0.0.0") ? "0" : getParameter("mask_destin");

    //************************************************************************************************************
    //** PROTOCOL **
    //************************************************************************************************************

    $arrValues['protocol'] = getParameter("id_protocol");
    if( $arrValues['protocol'] == 'TCP' || $arrValues['protocol'] == 'UDP' )
    {
        $arrValues['port_in'] = getParameter("port_in");
        if( strlen($arrValues['port_in']) == 0 ) $str_error .= ( strlen($str_error) == 0 ) ? "port_in" : ", port_in" ;

        $arrValues['port_out'] = getParameter("port_out");
        if(strlen($arrValues['port_out']) == 0) $str_error .= ( strlen($str_error) == 0 ) ? "port_out" : ", port_out" ;

        $arrValues['type_icmp'] = null;
        $arrValues['id_ip'] = null;
    }
    else if( $arrValues['protocol'] == 'ICMP' )
    {
        $arrValues['port_in'] = null;
        $arrValues['port_out'] = null;

        $arrValues['type_icmp'] = getParameter("type_icmp");
        if( strlen($arrValues['type_icmp']) == 0 ) $str_error .= ( strlen($str_error) == 0) ? "type" : ", type";

        $arrValues['id_ip'] = null;
    }
    else if( $arrValues['protocol'] == 'IP' )
    {
        $arrValues['port_in'] = null;
        $arrValues['port_out'] = null;
        $arrValues['type_icmp'] = null;

        $arrValues['id_ip'] = getParameter("id_ip");
        if( strlen($arrValues['id_ip']) == 0 ) $str_error .= ( strlen($str_error) == 0) ? "id" : ", id";
    }
    else
    {
        $arrValues['port_in'] = "*";
        $arrValues['port_out'] = "*";
        $arrValues['type_icmp'] = "*";
        $arrValues['id_ip'] = "*";
    }

    //************************************************************************************************************
    //** TARGET **
    //************************************************************************************************************

    $arrValues['target'] = getParameter("target");
    if( strlen($arrValues['target']) == 0 ) $str_error .= ( strlen($str_error) == 0) ? "target" : ", target";

    $arrValues['orden'] = getParameter("orden");
    //**********************
    //MENSSAGE ERROR
    //**********************

    if( strlen($str_error) != 0 ){
        $smarty->assign("mb_title", "ERROR");
        $smarty->assign("mb_message", $str_error);

        return newRules($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrValues, $state);
    }

    if(!$oForm->validateForm($_POST)) {
        // Falla la validación básica del formulario
        $strErrorMsg = "<b>"._tr('The following fields contain errors').":</b><br/>";
        $arrErrores = $oForm->arrErroresValidacion;
        if(is_array($arrErrores) && count($arrErrores) > 0){
            foreach($arrErrores as $k=>$v) {
                $strErrorMsg .= "$k: [$v[mensaje]] <br /> ";
            }
        }
        $smarty->assign("mb_title", _tr("Validation Error"));
        $smarty->assign("mb_message", $strErrorMsg);
        return newRules($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrValues, $state);
    }
    else if($arrValues['mask_source'] > 32 || $arrValues['mask_destin'] > 32){
        $smarty->assign("mb_title", _tr("Validation Error"));
        $smarty->assign("mb_message", _tr("The bit masks must be values less than 33"));
        return newRules($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrValues, $state);
    }
    else if(($arrValues['ip_source'] != "0.0.0.0" && $arrValues['ip_source'] != "" && $arrValues['mask_source'] == "0")||($arrValues['ip_destin'] != "0.0.0.0" && $arrValues['ip_destin'] != "" && $arrValues['mask_destin'] == "0")){
        $smarty->assign("mb_title", _tr("Validation Error"));
        $smarty->assign("mb_message", _tr("Wrong Mask"));
        return newRules($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrValues, $state);
    }

    $oPalo = new paloSantoRules($pDB);
    if($arrValues['ip_source'] != "0.0.0.0" && $arrValues['mask_source'] != "" && $arrValues['ip_source'] != ""){
        $arrValues['ip_source'] = $oPalo->getNetAdress($arrValues['ip_source'],$arrValues['mask_source']);
    }
    if($arrValues['ip_destin'] != "0.0.0.0" && $arrValues['mask_destin'] != "" && $arrValues['ip_destin'] != ""){
        $arrValues['ip_destin'] = $oPalo->getNetAdress($arrValues['ip_destin'],$arrValues['mask_destin']);
    }
    if($id == ""){
        if( $oPalo->saveRule( $arrValues ) == true )
        {
            $smarty->assign("mb_title", "MESSAGE");
            $smarty->assign("mb_message", _tr("Successful Save"));
        }
        else
        {
            $smarty->assign("mb_title", "ERROR");
            $smarty->assign("mb_message", $oPalo->errMsg);
            return newRules($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrValues, $state);
        }
    } else {
        if( $oPalo->updateRule($arrValues,$id) == true )
        {
            $smarty->assign("mb_title", "MESSAGE");
            $smarty->assign("mb_message", _tr("Successful Update"));
        }
        else
        {
            $smarty->assign("mb_title", "ERROR");
            $smarty->assign("mb_message", $oPalo->errMsg);
            return newRules($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrValues, $state);
        }
    }
    return reportRules($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
}

function reportRules($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    
    $pRules = new paloSantoRules($pDB);
    $action = getParameter("action");
    $id     = getParameter("id");
    $smarty->assign("ID", $id);
    $oFilterForm = new paloForm($smarty,array());
    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);
    //$oGrid->setTplFile("$local_templates_dir/_list.tpl");
    $totalRules = $pRules->ObtainNumRules();
    $error ="";
    if($action == 'edit'){
        $arrtmp = $pRules->getRule($id);
        $arripsource = explode("/",$arrtmp['ip_source']);
        $arripdst = explode("/",$arrtmp['ip_destiny']);
        $arrValues['id_traffic']=$arrtmp['traffic'];
        $arrValues['interface_in']=$arrtmp['eth_in'];
        $arrValues['interface_out']=$arrtmp['eth_out'];
        $arrValues['ip_source']=$arripsource[0];
        $arrValues['mask_source']=$arripsource[1];
        $arrValues['port_in']=$arrtmp['sport'];
        $arrValues['ip_destin']=$arripdst[0];
        $arrValues['mask_destin']=$arripdst[1];
        $arrValues['port_out']=$arrtmp['dport'];
        $arrValues['type_icmp']=$arrtmp['icmp_type'];
        $arrValues['id_ip']=$arrtmp['number_ip'];
        $arrValues['id_protocol']=$arrtmp['protocol'];
        $arrValues['target']=$arrtmp['target'];
        $arrValues['orden']=$arrtmp['rule_order'];
        $arrValues['id']=$id;
        $content = newRules($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrValues, $action);
        return $content;
    }elseif($action == 'Activate'){
        $pRules->setActivated($id);
    }
    elseif($action == 'Desactivate'){
        $pRules->setDesactivated($id);
    }
    $limit  = 20;
    $total  = $totalRules;
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $oGrid->setTitle(_tr("Rule List"));
    $oGrid->pagingShow(true);
    $offset = $oGrid->calculateOffset();
    $url    = "?menu=$module_name";
    $oGrid->setURL($url);
    $arrData = null;
    $arrResult = $pRules->ObtainRules($limit,$offset);
    $button_eliminar = "<input class=\"button\" type=\"submit\" name=\"delete\" value=\""._tr("Delete")."\" ".
                       " onclick=\"return confirmSubmit('"._tr("Are you sure you wish to delete the Rule")."?');\" >";
    $arrColumns = array($button_eliminar,_tr("Order"),_tr("Traffic"),_tr("Target"),_tr("Interface"),_tr("IP Source"),_tr("IP Destiny"),_tr("Protocol"),_tr("Details"),"","");
    $oGrid->setColumns($arrColumns);
    if(is_array($arrResult) && $total>0){
        foreach($arrResult as $key => $value){
            $arrTmp[0] = "<input type='checkbox' name='id_".$value['id']."' />";
            $arrTmp[1] = "<div id='div_$value[id]' style='width: 22px; font-size: 14pt;color:#E35332;float:left;text-align:right'>$value[rule_order] </div>"."<a href='javascript:void(0);' class='up' id='rulerup_$value[id]_$value[rule_order]'>"."<img src='modules/$module_name/images/up.gif' border=0 title='"._tr('Up')."'</a>"."<a href='javascript:void(0);' class='down' id='rulerdown_$value[id]_$value[rule_order]'>"."<img src='modules/$module_name/images/down.gif' border=0 title='"._tr('Down')."'</a>";
            if($value['traffic'] == "INPUT"){
                $image = "modules/$module_name/images/fw_input.gif";
                $title = _tr("INPUT");
                $arrTmp[4] = _tr("IN").": $value[eth_in]";
            }elseif($value['traffic'] == "OUTPUT"){
                $image = "modules/$module_name/images/fw_output.gif";
                $title = _tr("OUTPUT");
                $arrTmp[4] = _tr("OUT").": $value[eth_out]";
            }else{
                $image = "modules/$module_name/images/fw_forward.gif";
                $title = _tr("FORWARD");
                $arrTmp[4] = _tr("IN").":  $value[eth_in]<br />"._tr("OUT").": $value[eth_out]";
            }
	        $arrTmp[2] = "&nbsp;<a>"."<img src='$image' border=0 title='"._tr($title)."'</a>";
	        if($value['target'] == "ACCEPT"){
                $image = "modules/$module_name/images/target_accept.gif";
                $title = _tr("ACCEPT");
            }elseif($value['target'] == "DROP"){
                $image = "modules/$module_name/images/target_drop.gif";
                $title = _tr("DROP");
            }else{
                $image = "modules/$module_name/images/target_drop.gif";
                $title = _tr("REJECT");
            }
            $arrTmp[3] = "&nbsp;<a>"."<img src='$image' border=0 title='"._tr($title)."'</a>";
            $arrTmp[5] = $value['ip_source'];
            $arrTmp[6] = $value['ip_destiny'];
            $arrTmp[7] = $value['protocol'];
            if($value['protocol'] == "ICMP")
                $arrTmp[8] = _tr("Type").": $value[icmp_type]";
            else if($value['protocol'] == "IP")
                $arrTmp[8] = _tr("Number Protocol IP").": $value[number_ip]";
            else if($value['protocol'] == "TCP" || $value['protocol'] == "UDP")
                $arrTmp[8] = _tr("Source Port").": $value[sport]"."<br />"._tr("Destiny Port").": $value[dport]";
            else
                $arrTmp[8] = "";            
            if($value['activated'] == 1){
                $image = "modules/$module_name/images/foco_on.gif";
                $activated = "Desactivate";
            }
            else{
                $image = "modules/$module_name/images/foco_off.gif";
                $activated = "Activate";
            }
            $arrTmp[9] = "&nbsp;<a href='?menu=$module_name&action=".$activated."&id=".$value['id']."'>"."<img src='$image' border=0 title='"._tr($activated)."'</a>";
            $arrTmp[10] = "&nbsp;<a href='?menu=$module_name&action=edit&id=".$value['id']."'>"."<img src='modules/$module_name/images/edit.gif' border=0 title='"._tr('Edit')."'</a>";
            
            $arrData[] = $arrTmp;
        }
        $arrData[] = array("ctrl" => "separator_line", "start" => 0);

    }
    $oGrid->setData($arrData);
    $smarty->assign("new", _tr("New Rule"));
    $smarty->assign("exec", _tr("Execute Rules"));
    if($pRules->isExecutedInSystem())
        $smarty->assign("executed_in_sys", "");
    else
        $smarty->assign("executed_in_sys", _tr("There have been changes and have not been executed in the system"));

    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl","",$_POST);
    //end section filter

    $oGrid->showFilter(trim($htmlFilter));
    $contenidoModulo = "<form  method='POST' style='margin-bottom:0;' action=$url>".$oGrid->fetchGrid()."</form>";
    //end grid parameters

    return $contenidoModulo;
}

function execRules($smarty, $module_name, $local_templates_dir, $pDB, $arrConf)
{
    $pRules = new paloSantoRules($pDB);
    $activatedRules = $pRules->getActivatedRules();
    $error = "";
    if(!$pRules->flushRules()){
        $smarty->assign("mb_title", "ERROR");
        $smarty->assign("mb_message", _tr("Error during execution of rules"));
        return reportRules($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
    }
    if(!$pRules->activateRules($activatedRules,$error)){
        $smarty->assign("mb_title", "ERROR");
        if($error == ""){
            $smarty->assign("mb_message", _tr("Error during execution of rules"));
        }
        else
            $smarty->assign("mb_message", $error);
        return reportRules($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
    }
    $smarty->assign("mb_title", "MESSAGE");
    $smarty->assign("mb_message", _tr("The rules have been executed in the system"));
    $pRules->updateExecutedInSystem();
    return reportRules($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
}

function deleteFilter($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $pRules = new paloSantoRules($pDB);
    foreach($_POST as $key => $values){
        if(substr($key,0,3) == "id_")
        {
            $ID = substr($key, 3);
            $ID = str_replace("_",".",$ID);
            $pRules->deleteRule($ID);
         
        }
    }
    $content = reportRules($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
    return $content;
}

function getPorts($pDB)
{
    $jsonObject = new PaloSantoJSON();
    $oPort = new paloSantoPortService($pDB);
    
    $protocol = getParameter("protocol");
    if($protocol == "TCP")
        $Ports = $oPort->getTCPortNumbers();
    else
        $Ports = $oPort->getUDPortNumbers();
    $arrPort['ANY'] = _tr('ANY');
    foreach($Ports as $key => $value){
        $arrPort[$value['details']] = $value['details'];
    }
    $jsonObject->set_message($arrPort);
    return $jsonObject->createJSON();
}

function change($pDB)
{
    $jsonObject = new PaloSantoJSON();
    $pRules = new paloSantoRules($pDB);
    $neighborrow = getParameter("neighborrow");
    $actualrow = getParameter("actualrow");
    if($neighborrow != ""){
        $tmp = explode("_",$neighborrow);
        $neighbor_id = $tmp[1];
        $neighbor_order = $tmp[2];
        $tmp = explode("_",$actualrow);
        $actual_id = $tmp[1];
        $actual_order = $tmp[2];
        $Exito1 = $pRules->updateOrder($actual_id,$neighbor_order);
        $Exito2 = $pRules->updateOrder($neighbor_id,$actual_order);
        if($Exito1 && $Exito2)
            $jsonObject->set_status(_tr("Successful Change").":"._tr("There have been changes and have not been executed in the system")); 
        else
            $jsonObject->set_error($pRules->errMsg);
        
    }else
        $jsonObject->set_status(_tr("Invalid Action"));
    return $jsonObject->createJSON();
}


function getAction()
{
    if(getParameter("new"))
        return "new";
    else if(getParameter("save"))
        return "save";
    else if(getParameter("action") == "getPorts")
        return "getPorts";
    else if(getParameter("action") == "change")
        return "change";
    else if(getParameter("delete"))
        return "delete";
    else if(getParameter("exec"))
        return "exec";
    else if(getParameter("action")=="show") //Get parameter by GET (command pattern, links)
        return "show";
    else
        return "report";
}
?>