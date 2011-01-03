<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 0.5                                                  |
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
  */

include_once "libs/paloSantoJSON.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoConfEcho.class.php";

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

    require_once "modules/$module_name/libs/PaloSantoHardwareDetection.class.php";

    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConf['templates_dir']))?$arrConf['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];


    //conexion resource
    $pDB = new paloDB($arrConf['dsn_conn_database']);

    $action = getAction();
    $content = "";

    switch($action){
        case "config_echo":
            $content = viewFormConfEchoCard($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang); // para configurar echo canceler
            break;
        case "save_new":
            $content = saveNewConfEchoCard($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang); // save conf echo canceler
            break;
        case "setConfig":
            $content = setConfigHardware(); 
            break;
        case "detection":
            $content = hardwareDetect($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang); // detection button
            break;
        default:
            $content = listPorts($smarty, $module_name, $local_templates_dir, $pDB);
            break;
    }
    return $content;
}

function listPorts($smarty, $module_name, $local_templates_dir, $pDB) {

    global $arrLang;
    $oPortsDetails = new PaloSantoHardwareDetection();
    $contenidoModulo = "";

    $arrSpanConf = $oPortsDetails->getSpanConfig($pDB);
    $arrCardManufacturer = $oPortsDetails->getCardManufacturer($pDB);
    $smarty->assign("arrSpanConf",$arrSpanConf);
    $smarty->assign("arrCardManufacturer",$arrCardManufacturer);

    $smarty->assign("HARDWARE_DETECT",$arrLang['Hardware Detect']);
    $smarty->assign("CHAN_DAHDI_REPLACE",$arrLang['Replace file chan_dahdi.conf']);
    $smarty->assign("DETECT_SANGOMA", $arrLang['Detect Sangoma hardware']);
    $smarty->assign("DETECT_mISDN", $arrLang['Detect ISDN hardware']);
    $smarty->assign("MODULE_NAME",$module_name);
    $smarty->assign("detectandoHardware",$arrLang['Hardware Detecting']);
    $smarty->assign("CARD",$arrLang['Card']);
    $smarty->assign("CARD_MISDN",$arrLang['Misdn Card']);
    $smarty->assign("CARD_NO_MOSTRAR",'DAHDI');
    $smarty->assign("PORT_NOT_FOUND",$arrLang['Ports not Founds']);
    $smarty->assign("NO_PUERTO",$arrLang["Port"]." ");
    $smarty->assign("Channel_detected_notused",$arrLang['Channel detected and not used']);
    $smarty->assign("Channel_detected_use",$arrLang['Channel detected and in use']);
    $smarty->assign("Undetected_Channel",$arrLang['Undetected Channel']);
    $smarty->assign("SET_PARAMETERS_PORTS",$arrLang['You can set the parameters for these ports here']);
    $smarty->assign("Status_ports",$arrLang['Port Status']);
    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("EDIT", $arrLang["Edit"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("Configuration_Span", $arrLang["Configuration of Span"]);
    $smarty->assign("Span_Settings", $arrLang["Span Settings"]);
    $smarty->assign("Advanced", $arrLang["Advanced"]);
    $smarty->assign("Preferences", $arrLang["Preferences"]);
    $smarty->assign("Timing_source", $arrLang["Timing source"]);
    $smarty->assign("Line_build_out", $arrLang["Line build out"]);
    $smarty->assign("Framing", $arrLang["Framing"]);
    $smarty->assign("Coding", $arrLang["Coding"]);
    $smarty->assign("NoPorts",$arrLang["No Ports availables"]);
    $smarty->assign("LBL_LOADING",$arrLang["Loading SPAN"]);
    $smarty->assign("LBL_SAVING",$arrLang["Saving configuration"]);

    if($oPortsDetails->isInstalled_mISDN()){
        $smarty->assign("isInstalled_mISDN",true);
        $smarty->assign("MSG_isInstalled_mISDN",$arrLang['mISDN Driver Installed']);
    }
    else{
        $smarty->assign("isInstalled_mISDN",false);
        $smarty->assign("MSG_isInstalled_mISDN",$arrLang['mISDN Driver not Installed']);
    }

    $arrMisdnInfo = $oPortsDetails->getMisdnPortInfo();
    if(count($arrMisdnInfo)<=0)
        $arrMisdnInfo = "noMISDN";


    $arrPortsDetails = $oPortsDetails->getPorts($pDB);

    if(!(is_array($arrPortsDetails) && count($arrPortsDetails) >0)){
        $smarty->assign("CARDS_NOT_FOUNDS",$oPortsDetails->errMsg);
    }
    $arrGrid = array("title"    => $arrLang['Hardware Detector'],
            "icon"     => "images/pci.png",
            "width"    => "100%"
            );
    $contenidoModulo .= llenarTpl($local_templates_dir,$smarty,$arrGrid, $arrPortsDetails, $arrMisdnInfo);    
    return $contenidoModulo;
}

function llenarTpl($local_templates_dir,$smarty,$arrGrid, $arrData, $arrMisdn)
{
    $smarty->assign("title", $arrGrid['title']);
    $smarty->assign("icon",  $arrGrid['icon']);
    $smarty->assign("width", $arrGrid['width']);
    $smarty->assign("arrData", $arrData);
    $smarty->assign("arrMisdn", $arrMisdn);

    //Span Parameters
    $smarty->assign('type_timing_source', array(
                              '0' => '0',
                              '1' => '1',
                              '2' => '2',
                              '3' => '3',
                              '4' => '4',
                              '5' => '5',
                              '6' => '6',
                              '7' => '7'));

    $smarty->assign('type_lnbuildout', array(
                              '0' => '0',
                              '1' => '1',
                              '2' => '2',
                              '3' => '3',
                              '4' => '4',
                              '5' => '5',
                              '6' => '6',
                              '7' => '7'));

    $smarty->assign('type_framing', array(
                              'd4' => 'd4',
                              'esf' => 'esf',
                              'cas' => 'cas',
                              'ccs' => 'ccs',
                              'd4' => 'd4'));

    $smarty->assign('type_coding', array(
                              'ami' => 'ami',
                              'b8zs' => 'b8zs',
                              'hdb3' => 'hdb3'));

    //Card Manufacturer
    $smarty->assign('type_manufacturer', array(
                              'Digium' => 'Digium',
                              'OpenVox' => 'OpenVox',
                              'Rhino' => 'Rhino',
                              'Sangoma' => 'Sangoma',
                              'RedFone' => 'RedFone',
                              'XorCom' => 'XorCom',
                              'Dialogic' => 'Dialogic',
                              'Otros' => 'Otros' ));

    return $smarty->fetch($local_templates_dir."/listPorts.tpl");
}

function hardwareDetect($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $chk_dahdi_replace     = getParameter("chk_dahdi_replace");
    $there_is_sangoma_card = getParameter("there_is_sangoma_card");
    $there_is_misdn_card   = getParameter("there_is_misdn_card");

    $oHardwareDetect = new PaloSantoHardwareDetection();
    $resultado  = $oHardwareDetect->hardwareDetection($chk_dahdi_replace,"/etc/asterisk",$there_is_sangoma_card, $there_is_misdn_card);

    $jsonObject = new PaloSantoJSON();
    $msgResponse['msg'] = $resultado;
    $jsonObject->set_message($msgResponse);
    return $jsonObject->createJSON();
}

////////////NEW IMPLEMENTATION CODE FOR ECHO CANCELLER////////////////////////////
/*
function viewFormConfEcho($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $oPortsDetails = new PaloSantoHardwareDetection();
    $pconfEcho = new paloSantoConfEcho($pDB);
    $card_id     = getParameter("cardId");
    $arrPortsEcho = $pconfEcho->getEchoCancellerByIdCard($card_id);

    $arrFormprueba = createFieldForm($arrLang);
    $oForm = new paloForm($smarty,$arrFormprueba);

    //begin, Form data persistence to errors and other events.
    $_DATA  = $_POST;
    $action = getParameter("action");
    $id     = getParameter("id");
    
    if($action=="view")
        $oForm->setViewMode();
    else if($action=="view_edit" || getParameter("save_edit"))
        $oForm->setEditMode();

    if($action=="view" || $action=="view_edit"){ // the action is to view or view_edit.
        $dataprueba = $pconfEcho->getpruebaById($id);

        if(is_array($dataprueba) & count($dataprueba)>0)
            $_DATA = $dataprueba;
        else{
            $smarty->assign("mb_title", $arrLang["Error get Data"]);
            $smarty->assign("mb_message", $pconfEcho->errMsg);
        }
    }
    
    $smarty->assign("DESC_ID", $card_id);

    $dataCard = $pconfEcho->getCardParameterById($card_id);
    $smarty->assign("ID", $dataCard['id_card']);
    $smarty->assign("TIPO", $dataCard['type']);
    $smarty->assign("ADICIONAL", $dataCard['additonal']);
    $smarty->assign("MODULE_NAME",$module_name);

    if(is_array($arrPortsEcho) && count($arrPortsEcho)>1){
        $smarty->assign("arrPortsEcho", $arrPortsEcho);
        $i=1;
    }

    $smarty->assign('type_echo_names', array(
                              'none' => 'none',
                              'OSLEC' => 'OSLEC',
                              'MG2' => 'MG2',
                              'KBL' => 'KBL',
                              'SEC2' => 'SEC2',
                              'SEC' => 'SEC'));
    //$smarty->assign('typeecho_id', 1001);

    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("EDIT", $arrLang["Edit"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("IMG", "images/list.png");

    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",$arrLang["Configure Echo Cancellers"], $_DATA);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}*/

function viewFormConfEchoCard($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $oPortsDetails = new PaloSantoHardwareDetection();
    $pconfEcho     = new paloSantoConfEcho($pDB);
    $card_id       = getParameter("cardId");
    $card_id       = str_replace("confSPAN","",$card_id);
    $arrPortsEcho  = $pconfEcho->getEchoCancellerByIdCard($card_id);

    //begin, Form data persistence to errors and other events.
    $_DATA  = $_POST;
    $id     = getParameter("id");
    $card_id = str_replace("confSPAN","",$card_id);
    $dataCard = $pconfEcho->getCardParameterById($card_id);

    if(is_array($arrPortsEcho) && count($arrPortsEcho)>1){
        $smarty->assign("arrPortsEcho", $arrPortsEcho);
        $i=1;
    }

    $msgResponse['type_echo_names'] = array(
                              'none'  => 'none',
                              'OSLEC' => 'OSLEC',
                              'MG2'   => 'MG2',
                              'KBL'   => 'KBL',
                              'SEC2'  => 'SEC2',
                              'SEC'   => 'SEC');
    $msgResponse['arrPortsEcho'] = $arrPortsEcho;
    $jsonObject = new PaloSantoJSON();
    $msgResponse['card_id'] = $card_id;
    $msgResponse['msg'] = "";
    $jsonObject->set_message($msgResponse);
    return $jsonObject->createJSON();

}
/*
function saveNewConfEcho($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pconfEcho = new paloSantoConfEcho($pDB);
    $arrFormprueba = createFieldForm($arrLang);
    $oForm = new paloForm($smarty,$arrFormprueba);

    if(!$oForm->validateForm($_POST)){
        // Validation basic, not empty and VALIDATION_TYPE 
        $smarty->assign("mb_title", $arrLang["Validation Error"]);
        $arrErrores = $oForm->arrErroresValidacion;
        $strErrorMsg = "<b>{$arrLang['The following fields contain errors']}:</b><br/>";
        if(is_array($arrErrores) && count($arrErrores) > 0){
            foreach($arrErrores as $k=>$v)
                $strErrorMsg .= "$k, ";
        }
        $smarty->assign("mb_message", $strErrorMsg);
        $content = viewFormConfEcho($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
        return $content;
    }
    else{
        $id_card = getParameter("idCard");

        $arrPortsEcho = $pconfEcho->getEchoCancellerByIdCard2($id_card);
        $dataCard = $pconfEcho->getCardParameterById($id_card);
        foreach($arrPortsEcho as $key => $value){
            $num = $value['num_port'];
            $type_echo_pas = getParameter("tmpTypeEcho".$num);//para reemplazar
            $type_echo_selected = getParameter("typeecho_".$num);

            $data = array(); 
            $data['echocanceller'] = $pDB->DBCAMPO($type_echo_selected);

            $pconfEcho->replaceEchoSystemConf($type_echo_pas, $type_echo_selected, $num, $dataCard['type']);

            header("Location: ?menu=$module_name&action=report");
        }
    }
}
*/

function saveNewConfEchoCard($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pconfEcho = new paloSantoConfEcho($pDB);

    $id_card = getParameter("idCard");
    $type_echo_selected = getParameter("data");
    $type_echo_actual   = getParameter("data2");
    $arrPorts           = explode(",",$type_echo_selected);//typeecho_1|OSLEC,typeecho_2|OSLEC,typeecho_3|OSLEC,typeecho_4|OSLEC,
    $arrPortsActual     = explode(",",$type_echo_actual);//tmpTypeEcho1|OSLEC,tmpTypeEcho2|OSLEC,tmpTypeEcho3|OSLEC,tmpTypeEcho4|OSLEC,
    $arrEchoPort        = "";
    $arrEchoPortActual  = "";

    //{"0":"typeecho_1|OSLEC","1":"typeecho_2|OSLEC","2":"typeecho_3|OSLEC","3":"typeecho_4|OSLEC"}
    for($i=0; $i<count($arrPorts)-1; $i++){
        $arr = explode("|",$arrPorts[$i]);
        $arrEchoPort[$arr[0]] = $arr[1];
    }

    //{"0":"tmpTypeEcho1|OSLEC","1":"tmpTypeEcho2|OSLEC","2":"tmpTypeEcho3|OSLEC","3":"tmpTypeEcho4|OSLEC"}
    for($i=0; $i<count($arrPortsActual)-1; $i++){
        $arr = explode("|",$arrPortsActual[$i]);
        $arrEchoPortActual[$arr[0]] = $arr[1];
    }
    $arrPortsEcho = $pconfEcho->getEchoCancellerByIdCard2($id_card);
    $dataCard = $pconfEcho->getCardParameterById($id_card);
    foreach($arrPortsEcho as $key => $value){
        $num = $value['num_port'];
        $type_echo_pas      = $arrEchoPortActual["tmpTypeEcho".$num]; // antes
        $type_echo_selected = $arrEchoPort["typeecho_".$num]; // despues
        $data = array();
        $data['echocanceller'] = $pDB->DBCAMPO($type_echo_selected);
        $pconfEcho->updateEchoCancellerCard($id_card, $num, $type_echo_selected);
        $pconfEcho->replaceEchoSystemConf($type_echo_pas, $type_echo_selected, $num, $dataCard['type']);
    }
    $jsonObject = new PaloSantoJSON();
    $msgResponse['msg']   = $arrLang["Card Configured"];
    $jsonObject->set_message($msgResponse);
    return $jsonObject->createJSON();

}

function createFieldForm($arrLang)
{
    $arrTypeEcho = array('none' => 'none', 'OSLEC' => 'OSLEC', 'MG2' => 'MG2', 'KBL' => 'KBL', 'SEC2' => 'SEC2', 'SEC' => 'SEC');

    $arrFields = array(
        "0"   => array(         "LABEL"                  => "",
                                "REQUIRED"               => "no",
                                "INPUT_TYPE"             => "SELECT",
                                "INPUT_EXTRA_PARAM"      => $arrTypeEcho,
                                "VALIDATION_TYPE"        => "text",
                                "VALIDATION_EXTRA_PARAM" => "",
                                "EDITABLE"               => "si"
                        ),
    );

    return $arrFields;
}

function setConfigHardware(){
    $arrSpanConf = array();
    $idSpan = getParameter("idSpan");
    $arrSpanConf['tmsource']   = getParameter("tmsource");
    $arrSpanConf['lnbuildout'] = getParameter("lnbuildout");
    $arrSpanConf['framing']    = getParameter("framing");
    $arrSpanConf['coding']     = getParameter("coding");

    $oPortsDetails = new PaloSantoHardwareDetection();
    $oPortsDetails->updateFileSipCustom($idSpan, $arrSpanConf);
    return "";
}

function setDataCardHardware(&$pDB){
    $arrCardParam  = array();
    $idCard        = getParameter("idCard");
    $oPortsDetails = new PaloSantoHardwareDetection();

    $arrCardParam['manufacturer'] = $pDB->DBCAMPO(getParameter("manufacturer"));
    $arrCardParam['num_serie']    = $pDB->DBCAMPO(trim(getParameter("num_serie")));
    $oPortsDetails->updateCardParameter($pDB, $arrCardParam, array("id_card"=>$idCard));
    return $idCard;
}

function getAction()
{
    if(getParameter("save_new")) //Get parameter by POST (submit)
        return "save_new";
    else if(getParameter("save_edit"))
        return "save_edit";
    else if(getParameter("delete")) 
        return "delete";
    else if(getParameter("new_open")) 
        return "view_form";
    else if(getParameter("action")=="view")      //Get parameter by GET (command pattern, links)
        return "view_form";
    else if(getParameter("action")=="view_edit")
        return "view_form";
    else if(getParameter("action")=="config_echo")
        return "config_echo";
    else if(getParameter("action")=="setConfig")
        return "setConfig";
    else if(getParameter("action")=="detection")
        return "detection";
    else if(getParameter("action")=="save_echo")
        return "save_new";
    else
        return "report"; //cancel
}

?>