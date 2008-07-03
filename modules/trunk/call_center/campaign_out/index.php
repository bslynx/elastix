<?php
//bin/bash: indent: command not found
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
  $Id: new_campaign.php $ */

require_once "libs/paloSantoForm.class.php";
require_once "libs/paloSantoTrunk.class.php";
require_once "libs/misc.lib.php";
include_once "libs/paloSantoConfig.class.php";
include_once "libs/paloSantoGrid.class.php";
include_once "modules/form_designer/libs/paloSantoDataForm.class.php";
require_once "libs/xajax/xajax.inc.php";


/*
CREATE TABLE campaign
(
    id              INTEGER  PRIMARY KEY,
    name            varchar(64)     NOT NULL,
    datetime_init   date    NOT NULL,
    datetime_end    date    NOT NULL,
    daytime_init    time    NOT NULL,
    daytime_end     time    NOT NULL,
    retries         int unsigned    NOT NULL    DEFAULT 1,
    trunk           varchar(16)     NOT NULL,
    context         varchar(32)     NOT NULL,
    queue           varchar(16)     NOT NULL,
    max_canales     int unsigned    NOT NULL DEFAULT 0,
    num_completadas int unsigned,
    promedio        int unsigned,
    desviacion      int unsigned,
    script          TEXT NOLL NULL,
    estatus         varchar(1) NOT NULL DEFAULT 'A'
);

CREATE TABLE IF NOT EXISTS calls
(
    id          INTEGER  PRIMARY KEY,
    id_campaign int unsigned    NOT NULL,
    phone       varchar(32)     NOT NULL,
    status      varchar(32),
    
    Uniqueid    varchar(32),
    
    FOREIGN KEY (id_campaign)   REFERENCES campaign(id)
);

CREATE TABLE IF NOT EXISTS call_attribute
(
    id          INTEGER    PRIMARY KEY,
    id_call     int unsigned    NOT NULL,
    key         varchar(32)     NOT NULL,
    value       varchar(128)    NOT NULL,
    
    FOREIGN KEY (id_call)   REFERENCES calls(id)
);

CREATE TABLE current_calls
(
    id          INTEGER     PRIMARY KEY,
    fecha_inicio datetime   NOT NULL,
    Uniqueid    varchar(32) NOT NULL,
    queue       varchar(16) NOT NULL,
    agentnum    varchar(16) NOT NULL,    
    id_call     int unsigned NOT NULL,
    
    event       varchar(32) NOT NULL,
    
    FOREIGN KEY (id_call) REFERENCES calls(id)
);

CREATE TABLE campaign_form
(
    id_campaign    int unsigned NOT NULL,
    id_form        int unsigned NOT NULL,
    FOREIGN KEY (id_campaign) REFERENCES campaign(id),
    FOREIGN KEY (id_form) REFERENCES form(id)
);

CREATE TABLE agent (
    id              INTEGER PRIMARY KEY,
    number          VARCHAR(40) NOT NULL,
    name            VARCHAR(250) NOT NULL,
    password        VARCHAR(250) NOT NULL 
);

CREATE TABLE break (
    id             INTEGER PRIMARY KEY,
    name           VARCHAR(250) NOT NULL,
    description    VARCHAR(250)

, status varchar(1) Not NULL default 'A');


CREATE TABLE audit (
    id              INTEGER PRIMARY KEY,
    id_agent        int unsigned NOT NULL,
    id_break        int unsigned NOT NULL,
    datetime_init   date    NOT NULL,
    datetime_end    date    NOT NULL default '',
    daytime_init    time    NOT NULL,
    daytime_end     time    NOT NULL default '',
    FOREIGN KEY (id_agent) REFERENCES agent(id),
    FOREIGN KEY (id_break) REFERENCES break(id)
);
*/

function _moduleContent(&$smarty, $module_name)
{
    
    #incluir el archivo de idioma de acuerdo al que este seleccionado
    #si el archivo de idioma no existe incluir el idioma por defecto
    $lang=get_language();
    $script_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $lang_file="modules/$module_name/lang/$lang.lang";
    if (file_exists("$script_dir/$lang_file"))
        include_once($lang_file);
    else
        include_once("modules/$module_name/lang/en.lang");
    
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";

    global $arrConf;
    global $arrLang;
    global $arrConfig;
    //global $cadena_dsn;
    require_once "modules/$module_name/libs/paloSantoCampaignCC.class.php";
    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConfig['templates_dir']))?$arrConfig['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];
    $relative_dir_rich_text = "modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
    $arrConfig = $pConfig->leer_configuracion(false);

    $dsn     = $arrConfig['AMPDBENGINE']['valor'] . "://" . $arrConfig['AMPDBUSER']['valor'] . ":" . $arrConfig['AMPDBPASS']['valor'] . "@" . $arrConfig['AMPDBHOST']['valor'] . "/asterisk";

    $oDB = new paloDB($dsn);
    // se obtiene los truks de la central
    $arrDataTrunks=array();
    $arrTrunks=getTrunks($oDB); //obtener la lista de trunks
    if (is_array($arrTrunks)){
        foreach($arrTrunks as $trunk) {
            $arrDataTrunks[$trunk[1]] = $trunk[1];
        }
    }
    // para obtener el listado de colas
    include "libs/paloSantoQueue.class.php";
    $oQueue = new paloQueue($oDB);
    $arrQueues = $oQueue->getQueue();

    //print_r($arrConfig);
    //echo $arrConfig['cadena_dsn'];
//antes hacemos un query que trae las colas que estan en la tabla queue_call_entry
    $pDB = new paloDB($arrConf['cadena_dsn']);
    $query_call_entry = "SELECT queue FROM queue_call_entry WHERE estatus='A'";
    $arr_call_entry = $pDB->fetchTable($query_call_entry, true);

    $arreglo_colas = array();
    foreach($arr_call_entry as $cola){
    foreach($cola as $row){
         array_push($arreglo_colas,$row);//llenamos el arreglo de colas que estan en queue_call_entry
    }
    }
 
    if (is_array($arrQueues)){
        foreach($arrQueues as $queue) {
            if (!in_array($queue[0],$arreglo_colas)){//si la cola de queue_call_entry no esta siendo usada la asignamos al combo
            $arrDataQueues[$queue[0]] = $queue[1];
            }
        }          
    }


    // se conecta a la base
    //$pDB = new paloDB("sqlite3:////var/www/db/campaign.db");
    $pDB = new paloDB($arrConf["cadena_dsn"]);
    if (!is_object($pDB->conn) || $pDB->errMsg!="") {
        $smarty->assign("mb_message", $arrLang["Error when connecting to database"]." ".$pDB->errMsg);
    }
//     if(!empty($pDB->errMsg)) {
//         $smarty->assign("mb_message", $arrLang["Error when connecting to database"]."<br/>".$pDB->errMsg);
//     }

    //CARGO TODOS LOS FORMULARIOS
    $oDataForm = new paloSantoDataForm($pDB); 
    $arrDataForm = $oDataForm->getFormularios(NULL,'A');
    $arrSelectForm = array();
    foreach($arrDataForm as $key => $form)
        $arrSelectForm[$form['id']] = $form['nombre'];

    // Definición del formulario de nueva campaña
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("APPLY_CHANGES", $arrLang["Apply changes"]);
    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("EDIT", $arrLang["Edit"]);
    $smarty->assign("DELETE", $arrLang["Delete"]);
    $smarty->assign("CONFIRM_CONTINUE", $arrLang["Are you sure you wish to continue?"]);
    $smarty->assign("CONFIRM_DELETE", $arrLan["Are you sure you wish to delete campaign?"]);
    $smarty->assign("DESCATIVATE", $arrLan["Desactivate"]);
    $smarty->assign("relative_dir_rich_text", $relative_dir_rich_text);

    $horas = array();
    $i = 0;
    for( $i=-1;$i<24;$i++)
    {
        if($i == -1)     $horas["HH"] = "HH";
        else if($i < 10) $horas["0$i"] = '0'.$i;
        else             $horas[$i] = $i;
    }

    $minutos = array();
    $i = 0;
    for( $i=-1;$i<60;$i++)
    {
        if($i == -1)     $minutos["MM"] = "MM";
        else if($i < 10) $minutos["0$i"] = '0'.$i;
        else             $minutos[$i] = $i;
    }

    $formCampos = array(
        'nombre'    =>    array(
            "LABEL"                => $arrLan["Name Campaign"],
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "TEXT",
            "INPUT_EXTRA_PARAM"      => "",
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => "",
        ),

        'trunk'       => array(
            "LABEL"                  => $arrLan["Trunk"],
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "SELECT",
            "INPUT_EXTRA_PARAM"      => $arrDataTrunks,
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => ""
        ),
        "fecha_str"       => array(
            "LABEL"                  => $arrLan["Range Date"],
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "",
            "INPUT_EXTRA_PARAM"      => "",
            "VALIDATION_TYPE"        => '',
            "VALIDATION_EXTRA_PARAM" => ''
        ),
        "fecha_ini"       => array(
            "LABEL"                  => $arrLan["Start"],
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "DATE",
            //"INPUT_EXTRA_PARAM"      => array("TIME" => true, "FORMAT" => "%d %b %Y %H:%M","TIMEFORMAT" => "24"),
            "INPUT_EXTRA_PARAM"      => array("TIME" => false, "FORMAT" => "%d %b %Y"),
            "VALIDATION_TYPE"        => 'ereg',
            "VALIDATION_EXTRA_PARAM" => '^[[:digit:]]{2}[[:space:]]+[[:alpha:]]{3}[[:space:]]+[[:digit:]]{4}$'
        ),
        "fecha_fin"       => array(
            "LABEL"                  => $arrLan["End"],
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "DATE",
            //"INPUT_EXTRA_PARAM"      => array("TIME" => true, "FORMAT" => "%d %b %Y %H:%M","TIMEFORMAT" => "24"),
            "INPUT_EXTRA_PARAM"      => array("TIME" => false, "FORMAT" => "%d %b %Y"),
            "VALIDATION_TYPE"        => 'ereg',
            "VALIDATION_EXTRA_PARAM" => '^[[:digit:]]{2}[[:space:]]+[[:alpha:]]{3}[[:space:]]+[[:digit:]]{4}$'
        ),
        "hora_str"       => array(
            "LABEL"                  => $arrLan["Schedule per Day"],
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "",
            "INPUT_EXTRA_PARAM"      => "",
            "INPUT_EXTRA_PARAM"      => "",
            "VALIDATION_TYPE"        => '',
            "VALIDATION_EXTRA_PARAM" => ''
        ),
        "hora_ini_HH"   => array(
            "LABEL"                  => $arrLan["Start time"],
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "SELECT",
            "INPUT_EXTRA_PARAM"      => $horas,
            "VALIDATION_TYPE"        => 'numeric',
            "VALIDATION_EXTRA_PARAM" => '',
         ),
        "hora_ini_MM"   => array(
            "LABEL"                  => $arrLan["Start time"],
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "SELECT",
            "INPUT_EXTRA_PARAM"      => $minutos,
            "VALIDATION_TYPE"        => 'numeric',
            "VALIDATION_EXTRA_PARAM" => '',
         ),
         "hora_fin_HH"   => array(
            "LABEL"                  => $arrLan["End time"],
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "SELECT",
            "INPUT_EXTRA_PARAM"      => $horas,
            "VALIDATION_TYPE"        => 'numeric',
            "VALIDATION_EXTRA_PARAM" => '',
         ),
         "hora_fin_MM"   => array(
            "LABEL"                  => $arrLan["End time"],
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "SELECT",
            "INPUT_EXTRA_PARAM"      => $minutos,
            "VALIDATION_TYPE"        => 'numeric',
            "VALIDATION_EXTRA_PARAM" => '',
         ),
         'formulario'       => array(
            "LABEL"                  => $arrLan["Form"],
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "SELECT",
            "INPUT_EXTRA_PARAM"      => $arrSelectForm,
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => "",
            "MULTIPLE"               => true,
            "SIZE"                   => "5"
        ),
        'formularios_elegidos'       => array(
            "LABEL"                  => $arrLan["Form"],
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "SELECT",
            "INPUT_EXTRA_PARAM"      => "",
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => "",
            "MULTIPLE"               => true,
            "SIZE"                   => "5"
        ),
        "reintentos" => array(
            "LABEL"                  => $arrLan["Retries"],
            "REQUIRED"               => "yes", 
            "INPUT_TYPE"             => "TEXT",
            "INPUT_EXTRA_PARAM"      => "",
            "VALIDATION_TYPE"        => "numeric",
            "VALIDATION_EXTRA_PARAM" => ""
        ),
        "context" => array(
            "LABEL"                  => $arrLan["Context"],
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "TEXT",
            "INPUT_EXTRA_PARAM"      => "",
            "VALIDATION_TYPE"        => "ereg",
            "VALIDATION_EXTRA_PARAM" => "^[[:alpha:]-]+$"
        ),
        "queue" => array(
            "LABEL"                  => $arrLan["Queue"],
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "SELECT",
            "INPUT_EXTRA_PARAM"      => $arrDataQueues,
            "VALIDATION_TYPE"        => "numeric",
            "VALIDATION_EXTRA_PARAM" => ""
        ),
        "script" => array(
            "LABEL"                  => $arrLan["Script"],
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "TEXT",
            "INPUT_EXTRA_PARAM"      => "",
            "VALIDATION_TYPE"        => "",
            "VALIDATION_EXTRA_PARAM" => ""
        ),
    );
    $oForm = new paloForm($smarty, $formCampos);

    $xajax = new xajax();
    $xajax->registerFunction("desactivar_campania");
    $xajax->processRequests();
    $smarty->assign("xajax_javascript",$xajax->printJavascript("libs/xajax/"));
    
    if (isset($_POST['submit_create_campaign'])) {
        $contenidoModulo = new_campaign($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm);
    } else if (isset($_POST['save'])) {

    //verificamos nuevamente si la cola fue elegida
    /*$error_cola=0; 
    $call_entry = "SELECT queue FROM queue_call_entry WHERE estatus='A'";
    $r_call_entry = $pDB->fetchTable($call_entry, true);
    $arr_colas = array();

        foreach($r_call_entry as $colas){
            foreach($colas as $rows){
                    array_push($arr_colas,$rows);//llenamos el arreglo de colas que estan en queue_call_entry
            }
        }

    $query = "select * from campaign where estatus='A';";
    if (is_array($arrDataQueues)){
         foreach($arrDataQueues as $queue) {
            if (in_array($queue[0],$arr_colas)){
            $error_cola = 1;       
            }
         }
        }
    if($error_cola==1) $smarty->assign("mb_message","Esta cola ya ha sido seleccionada por otro usuario, seleccione otra cola");
        else*/ $contenidoModulo = save_campaign($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm);
    } else if (isset($_POST['edit'])) {
        $contenidoModulo = edit_campaign($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm);
    } else if (isset($_POST['apply_changes'])) {
        $contenidoModulo = update_campaign($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm);
    }  else if (isset($_POST['id_campaign']) && isset($_POST['delete'])) {
        $contenidoModulo = delete_campaign($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm);
    } else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action']=="view") {
        $contenidoModulo = view_campaign($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm);
    } else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action']=="activar") {
        $contenidoModulo = activar_campaign($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm);
    } else {
        $contenidoModulo = listadoCampaign($pDB, $smarty, $module_name, $local_templates_dir);
    }

    if(is_null($contenidoModulo))
        return "";
    else
        return $contenidoModulo;
}


function new_campaign($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm) {

    global $arrLan;

    if (!isset($_POST['nombre'])) $_POST['nombre']='';
    if (!isset($_POST['reintentos'])) $_POST['reintentos']='';

    if (!isset($_POST["context"]) || $_POST["context"]=="") {
        $_POST["context"] = "from-internal";
    }
    $contenidoModulo = $oForm->fetchForm("$local_templates_dir/new.tpl", $arrLan["New Campaign"],$_POST);
    return $contenidoModulo;
}

function save_campaign($pDBSQLite, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm) {
    global $arrLang;
    global $arrLan;

    // En esta implementación el formulario trabaja exclusivamente en modo 'input'
    // y por lo tanto proporciona el botón 'save'
    if(!$oForm->validateForm($_POST) || (!isset($_POST['rte_script']) || $_POST['rte_script']=='')) {
        // Falla la validación básica del formulario
        $smarty->assign("mb_title", $arrLang["Validation Error"]);
        $arrErrores=$oForm->arrErroresValidacion;
        $strErrorMsg = "<b>{$arrLang['The following fields contain errors']}:</b><br/>";
        if(is_array($arrErrores) && count($arrErrores) > 0){
            foreach($arrErrores as $k=>$v) {
                $strErrorMsg .= "$k, ";
            }
        }
        if(!isset($_POST['rte_script']) || $_POST['rte_script']=='')
            $strErrorMsg .= $arrLan["Script"];
        $strErrorMsg .= "";
        $smarty->assign("mb_message", $strErrorMsg);
    }else{
        $oCamp = new paloSantoCampaignCC($pDBSQLite);
        //$time_ini = obtenerDateTime('TIME',$_POST['fecha_ini']);
        //$time_fin = obtenerDateTime('TIME',$_POST['fecha_fin']);
        $time_ini = $_POST['hora_ini_HH'].":".$_POST['hora_ini_MM'];
        $time_fin = $_POST['hora_fin_HH'].":".$_POST['hora_fin_MM'];
        print_r($_POST);
        //$iFechaIni = strtotime(obtenerDateTime('DATE',$_POST['fecha_ini']));
        //$iFechaFin = strtotime(obtenerDateTime('DATE',$_POST['fecha_fin']));
        $iFechaIni = strtotime($_POST['fecha_ini']);
        $iFechaFin = strtotime($_POST['fecha_fin']);
        $iHoraIni =  strtotime($time_ini);
        $iHoraFin =  strtotime($time_fin); 
        if ($iFechaIni == -1 || $iFechaIni === FALSE) {
            $smarty->assign("mb_title", $arrLang["Validation Error"]);
            $smarty->assign("mb_message", $arrLan['Unable to parse start date specification']);
        } elseif ($iFechaFin == -1 || $iFechaFin === FALSE) {
            $smarty->assign("mb_title", $arrLang["Validation Error"]);
            $smarty->assign("mb_message", $arrLang['Unable to parse end date specification']);
        } elseif ($iHoraIni == -1 || $iHoraIni === FALSE) {
            $smarty->assign("mb_title", $arrLang["Validation Error"]);
            $smarty->assign("mb_message", $arrLan['Unable to parse start time specification']);
        } elseif ($iHoraFin == -1 || $iHoraFin === FALSE) {
            $smarty->assign("mb_title", $arrLang["Validation Error"]);
            $smarty->assign("mb_message", $arrLan['Unable to parse end time specification']);
        } else {

        if(!$pDBSQLite->genQuery("SET AUTOCOMMIT=0")) {
            $smarty->assign("mb_message", $pDBSQLite->errMsg);
        } else {
            $id_campaign = $oCamp->createEmptyCampaign(
                            $_POST['nombre'],
                            3,
                            $_POST['reintentos'],
                            $_POST['trunk'],
                            $_POST['context'],
                            $_POST['queue'],
                            date('Y-m-d', $iFechaIni),
                            date('Y-m-d', $iFechaFin),
                            $time_ini,
                            $time_fin,
                            $_POST['rte_script'],
                            $_POST['queue']);
            
            if (!is_null($id_campaign)) {
                $bExito1=false;

                if (isset($_POST['values_form']))
                    $bExito1 = $oCamp->addCampaignForm($id_campaign,$_POST['values_form']);
                if ($bExito1)
                    $bExito2 = $oCamp->addCampaignNumbersFromFile($id_campaign, $_FILES['phonefile']['tmp_name']);
                if ($bExito1 && $bExito2) {
                    $pDBSQLite->genQuery("COMMIT");
                     header("Location: ?menu=$module_name");
                } else {
                    $smarty->assign("mb_title", $arrLang["Validation Error"]);
                    $smarty->assign("mb_message", $oCamp->errMsg);
                }
            } else {
                $smarty->assign("mb_title", $arrLang["Validation Error"]);
                $smarty->assign("mb_message", $oCamp->errMsg);
            }
            $pDBSQLite->genQuery("ROLLBACK");
          }
          $pDBSQLite->genQuery("SET AUTOCOMMIT=1");
        }
    }
    $smarty->assign("rte_script",adaptar_formato_rte($_POST['rte_script']));
    $_POST['formulario']= split(",",$_POST['values_form']);
    $contenidoModulo = $oForm->fetchForm("$local_templates_dir/new.tpl", $arrLan["New Campaign"],$_POST);
    return $contenidoModulo;
}

function obtenerDateTime($accion,$date_time)
{
    if(isset($date_time)){
        if($accion=='TIME'){
            if(ereg("[[:digit:]]{2}:[[:digit:]]{2}",$date_time,$reg)){
               return $reg[0];
            }
            else
                return "--:--";
        }
        if($accion=='DATE'){
            if(ereg("^[[:digit:]]{2}[[:space:]]+[[:alpha:]]{3}[[:space:]]+[[:digit:]]{4}",$date_time,$reg)){
                return $reg[0];
            }
            else
                return "-- --- ----";
        }
    }
    return "-- --- ---- --:--";
}
function view_campaign($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm) {
    global $arrLang;
    global $arrLan;

    $oForm->setViewMode(); // Esto es para activar el modo "preview"

    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        return false;
    }
    $oCampaign = new paloSantoCampaignCC($pDB);
    $arrCampaign = $oCampaign->getCampaigns(NULL, NULL, $_GET['id']);
    // Conversion de formato
    $arrTmp['nombre']       = $arrCampaign[0]['name'];
    $arrTmp['fecha_ini']    = date('d M Y',strtotime($arrCampaign[0]['datetime_init']));
    $arrTmp['fecha_fin']    = date('d M Y',strtotime($arrCampaign[0]['datetime_end']));
    $arrDateTimeInit = split(":",$arrCampaign[0]['daytime_init']);
    $arrDateTimeEnd  = split(":",$arrCampaign[0]['daytime_end']);
    $arrTmp['hora_ini_HH']  = isset($arrDateTimeInit[0])?$arrDateTimeInit[0]:"00";
    $arrTmp['hora_ini_MM']  = isset($arrDateTimeInit[1])?$arrDateTimeInit[1]:"00";
    $arrTmp['hora_fin_HH']  = isset($arrDateTimeEnd[0])?$arrDateTimeEnd[0]:"00";
    $arrTmp['hora_fin_MM']  = isset($arrDateTimeEnd[1])?$arrDateTimeEnd[1]:"00";
    $arrTmp['reintentos']   = $arrCampaign[0]['retries'];
    $arrTmp['trunk']        = $arrCampaign[0]['trunk'];
    $arrTmp['queue']        = $arrCampaign[0]['queue'];
    $arrTmp['context']      = $arrCampaign[0]['context'];
    $arrTmp['script']       = $arrCampaign[0]['script'];
    $arrTmp['formulario']   = $oCampaign->obtenerCampaignForm($_GET['id']);
    $arrTmp['formularios_elegidos'] = "";

    $smarty->assign("id_campaign", $_GET['id']);
    $contenidoModulo=$oForm->fetchForm("$local_templates_dir/new.tpl", $arrLan["View Campaign"], $arrTmp); // hay que pasar el arreglo
    return $contenidoModulo;
}

function edit_campaign($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm) {
    global $arrLang;
    global $arrLan;
    
    //EDITAR CAMPAÑA
    // Tengo que recuperar los datos de la campaña
    $oCampaign = new paloSantoCampaignCC($pDB);
    $arrCampaign = $oCampaign->getCampaigns(null, null, $_GET['id']);

    $arrTmp['nombre']       = $arrCampaign[0]['name'];
    $arrTmp['fecha_ini']    = date('d M Y',strtotime($arrCampaign[0]['datetime_init']));
    $arrTmp['fecha_fin']    = date('d M Y',strtotime($arrCampaign[0]['datetime_end']));
    $arrDateTimeInit = split(":",$arrCampaign[0]['daytime_init']);
    $arrDateTimeEnd  = split(":",$arrCampaign[0]['daytime_end']);
    $arrTmp['hora_ini_HH']  = isset($arrDateTimeInit[0])?$arrDateTimeInit[0]:"00";
    $arrTmp['hora_ini_MM']  = isset($arrDateTimeInit[1])?$arrDateTimeInit[1]:"00";
    $arrTmp['hora_fin_HH']  = isset($arrDateTimeEnd[0])?$arrDateTimeEnd[0]:"00";
    $arrTmp['hora_fin_MM']  = isset($arrDateTimeEnd[1])?$arrDateTimeEnd[1]:"00";$arrTmp['reintentos']   = $arrCampaign[0]['retries'];
    $arrTmp['trunk']        = $arrCampaign[0]['trunk'];
    $arrTmp['queue']        = $arrCampaign[0]['queue'];
    $arrTmp['context']      = $arrCampaign[0]['context'];
    $smarty->assign("rte_script",adaptar_formato_rte($arrCampaign[0]['script'])); 
    $arrTmp['script'] = "";
    $arrTmp['formulario'] = "";
    $arrTmp['formularios_elegidos'] = "";

     //CARGO TODOS LOS FORMULARIOS Y LUEGO FILTRO POR LOS ELEGIDOS Y NO ELEGIDOS
    $oDataForm           = new paloSantoDataForm($pDB); 
    $arrDataForm         = $oDataForm->getFormularios(NULL,'A');
    $arrDataFormElegidos = $oCampaign->obtenerCampaignForm($_GET['id']);
    $arrElegidos = array();
    $arrNoElegidos = array();
    foreach($arrDataForm as $key => $form){
        $encontrado = false;
        foreach($arrDataFormElegidos as $keyElegido => $formElegido){
            if($form['id'] == $formElegido){
                $arrElegidos[$form['id']] = $form['nombre'];
                $encontrado = true;
            }
        }
        if(!$encontrado)
           $arrNoElegidos[$form['id']] = $form['nombre'];
    }
    
    $formCampos['formulario']['INPUT_EXTRA_PARAM']           = $arrNoElegidos;
    $formCampos['formularios_elegidos']['INPUT_EXTRA_PARAM'] = $arrElegidos;
    $oForm = new paloForm($smarty, $formCampos);
    $oForm->setEditMode();
    $smarty->assign("id_campaign", $_POST['id_campaign']);
    
    $contenidoModulo=$oForm->fetchForm("$local_templates_dir/new.tpl", $arrLan['Edit Campaign']." \"".$arrTmp['nombre']."\"", $arrTmp);
    return $contenidoModulo;
}

function update_campaign($pDBSQLite, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm) {

    global $arrLang;
    global $arrLan;
    if(!$oForm->validateForm($_POST) || (!isset($_POST['rte_script']) || $_POST['rte_script']=='')) {
        // Falla la validación básica del formulario
        $smarty->assign("mb_title", $arrLang["Validation Error"]);
        $arrErrores=$oForm->arrErroresValidacion;
        $strErrorMsg = "<b>{$arrLang['The following fields contain errors']}:</b><br/>";
        if(is_array($arrErrores) && count($arrErrores) > 0){
            foreach($arrErrores as $k=>$v) {
                $strErrorMsg .= "$k, ";
            }
        }
        if(!isset($_POST['rte_script']) || $_POST['rte_script']=='')
            $strErrorMsg .= $arrLan["Script"];
        $strErrorMsg .= "";
        $smarty->assign("mb_message", $strErrorMsg);
        $oForm->setEditMode();
    } else {
        $oCamp = new paloSantoCampaignCC($pDBSQLite);
        //$time_ini = obtenerDateTime('TIME',$_POST['fecha_ini']);
        //$time_fin = obtenerDateTime('TIME',$_POST['fecha_fin']);
        $time_ini = $_POST['hora_ini_HH'].":".$_POST['hora_ini_MM'];
        $time_fin = $_POST['hora_fin_HH'].":".$_POST['hora_fin_MM'];
        print_r($_POST);
        //$iFechaIni = strtotime(obtenerDateTime('DATE',$_POST['fecha_ini']));
        //$iFechaFin = strtotime(obtenerDateTime('DATE',$_POST['fecha_fin']));
        $iFechaIni = strtotime($_POST['fecha_ini']);
        $iFechaFin = strtotime($_POST['fecha_fin']);
        $iHoraIni =  strtotime($time_ini);
        $iHoraFin =  strtotime($time_fin); 

        if ($iFechaIni == -1 || $iFechaIni === FALSE) {
            $smarty->assign("mb_title", $arrLang["Validation Error"]);
            $smarty->assign("mb_message", $arrLan['Unable to parse start date specification']);
        } elseif ($iFechaFin == -1 || $iFechaFin === FALSE) {
            $smarty->assign("mb_title", $arrLang["Validation Error"]);
            $smarty->assign("mb_message", $arrLan['Unable to parse end date specification']);
        } elseif ($iHoraIni == -1 || $iHoraIni === FALSE) {
            $smarty->assign("mb_title", $arrLang["Validation Error"]);
            $smarty->assign("mb_message", $arrLan['Unable to parse start time specification']);
        } elseif ($iHoraFin == -1 || $iHoraFin === FALSE) {
            $smarty->assign("mb_title", $arrLang["Validation Error"]);
            $smarty->assign("mb_message", $arrLan['Unable to parse end time specification']);
        } else {

          if(!$pDBSQLite->genQuery("SET AUTOCOMMIT=0")) {
            $smarty->assign("mb_message", $pDBSQLite->errMsg); 
          } else {
            $bExito = $oCamp->updateCampaign(
                            $_POST['id_campaign'],
                            $_POST['nombre'],
                            3,
                            $_POST['reintentos'],
                            $_POST['trunk'],
                            $_POST['context'],
                            $_POST['queue'],
                            date('Y-m-d', $iFechaIni),
                            date('Y-m-d', $iFechaFin),
                            $time_ini,
                            $time_fin,
                            $_POST['rte_script']);

//             if (!is_null($id_campaign)) {
//                 $bExito = $oCamp->addCampaignNumbersFromFile($id_campaign, $_FILES['phonefile']['tmp_name']);
                 if ($bExito) {
                     $bExito1=false;
                     if(isset($_POST['values_form']))
                        $bExito1 = $oCamp->updateCampaignForm($_POST['id_campaign'],$_POST['values_form']);

                     if ($bExito1){
                        $pDBSQLite->genQuery("COMMIT");
                        header("Location: ?menu=$module_name&action=view&id=".$_POST['id_campaign']);
                     }
                     else{
                        $smarty->assign("mb_title", $arrLang["Validation Error"]." 3 ");
                        $smarty->assign("mb_message", $oCamp->errMsg);
                    }

                 } else {
                     $smarty->assign("mb_title", $arrLang["Validation Error"]." 1 ");
                     $smarty->assign("mb_message", $oCamp->errMsg);
                 }
//             } else {
//                 $smarty->assign("mb_title", $arrLang["Validation Error"]." 2 ");
//                 $smarty->assign("mb_message", $oCamp->errMsg);
//             }
             $pDBSQLite->genQuery("ROLLBACK");
          }
          $pDBSQLite->genQuery("SET AUTOCOMMIT=1");
        }
    } 
    $oForm->setEditMode();
    $smarty->assign("id_campaign", $_POST['id_campaign']);
    $smarty->assign("rte_script",adaptar_formato_rte($_POST['rte_script']));
    $_POST['formulario']= split(",",$_POST['values_form']);
    $contenidoModulo = $oForm->fetchForm("$local_templates_dir/new.tpl", $arrLan['Edit Campaign']." \"".$_POST['nombre']."\"",$_POST);
    return $contenidoModulo;
}

function listadoCampaign($pDB, $smarty, $module_name, $local_templates_dir) {
    global $arrLang;
    global $arrLan;
    $arrData = '';
    $oCampaign = new paloSantoCampaignCC($pDB);

    //LISTADO DE RATES
    if (isset($_POST['delete'])) {
        $oCampaign->deleteCampaign($_POST['id_campaign']);
    }

    // preguntando por el estado del filtro
    if (!isset($_POST['cbo_estado']) || $_POST['cbo_estado']=="") {
        $_POST['cbo_estado'] = "A";
    }

    // para el pagineo
    $limit = 50;
    $offset = 0;

    if( isset($_GET['cbo_estado']) ) {
        $url = construirURL()."&cbo_estado={$_GET['cbo_estado']}";
    } else {
        $url = construirURL()."&cbo_estado={$_POST['cbo_estado']}";
    }
    $smarty->assign("url", $url);

   if(isset($_GET['cbo_estado'])) {
        $_POST['cbo_estado'] = $_GET['cbo_estado'];
        $arrCampaign = $oCampaign->getCampaigns(null, $offset, NULL, $_GET['cbo_estado']);
    } else {
        $arrCampaign = $oCampaign->getCampaigns(null, $offset, NULL, $_POST['cbo_estado']);
    } 

    $total = count($arrCampaign);

    // Si se quiere avanzar a la sgte. pagina
    if(isset($_GET['nav']) && $_GET['nav']=="end") {
        $totalCampaigns  = count($arrCampaign);
        // Mejorar el sgte. bloque.
        if(($totalCampaigns%$limit)==0) {
            $offset = $totalCampaigns - $limit;
        } else {
            $offset = $totalCampaigns - $totalCampaigns%$limit;
        }
    }

    // Si se quiere avanzar a la sgte. pagina
    if(isset($_GET['nav']) && $_GET['nav']=="next") {
        $offset = $_GET['start'] + $limit - 1;
    }

    // Si se quiere retroceder
    if(isset($_GET['nav']) && $_GET['nav']=="previous") {
        $offset = $_GET['start'] - $limit - 1;
    }

    if(isset($_GET['cbo_estado'])) {
        $_POST['cbo_estado'] = $_GET['cbo_estado'];
        $arrCampaign = $oCampaign->getCampaigns($limit, $offset, NULL, $_GET['cbo_estado']);
    } else {
        $arrCampaign = $oCampaign->getCampaigns($limit, $offset, NULL, $_POST['cbo_estado']);
    }

    $end = count($arrCampaign);

    if (is_array($arrCampaign)) {
        foreach($arrCampaign as $campaign) {
            $arrTmp    = array();
            $arrTmp[0] = $campaign['name'];
            $arrTmp[1] = $campaign['datetime_init'];
            $arrTmp[2] = $campaign['daytime_init'];
            $arrTmp[3] = $campaign['datetime_end'];
            $arrTmp[4] = $campaign['daytime_end'];
            $arrTmp[5] = ($campaign['retries']!="")?$campaign['retries']:"&nbsp;";
            $arrTmp[6] = $campaign['trunk'];
            $arrTmp[7] = $campaign['queue'];
            $arrTmp[8] = ($campaign['num_completadas']!="")?$campaign['num_completadas']:"&nbsp;";
            $arrTmp[9] = ($campaign['promedio']!="")?number_format($campaign['promedio'],0):"&nbsp;";

            $csv_data = "&nbsp;<a href='modules/$module_name/libs/archivo_cvs.php?id=".$campaign['id']."&module=$module_name&name_campania=".adaptar_nombre($campaign['name'])."'>{$arrLan['CSV Data']}</a>";
            $ver_campania = "&nbsp;<a href='?menu=$module_name&action=view&id=".$campaign['id']."'>{$arrLang['View']}</a>";
            if($campaign['estatus']=='I'){
                $arrTmp[10] = $arrLang['Inactive'];
                $arrTmp[11] = "&nbsp;<a href='?menu=$module_name&action=activar&id=".$campaign['id']."'>{$arrLang['Activate']}</a>".$ver_campania.$csv_data;
            } elseif($campaign['estatus']=='A'){
                $arrTmp[10] = $arrLan['Active'];
                $arrTmp[11] = $ver_campania.$csv_data;
            } elseif ($campaign['estatus']=='T') {
                $arrTmp[10] = $arrLan['Finish'];
                $arrTmp[11] = $ver_campania.$csv_data;
            }
            $arrData[] = $arrTmp;
        }
    }

    $arrGrid = array("title"    => $arrLan["Campaigns List"],
        "icon"     => "images/list.png",
        "width"    => "99%",
        "start"    => ($total==0) ? 0 : $offset + 1,
        "end"      => ($offset+$limit)<=$total ? $offset+$limit : $total,
        "total"    => $total,
        "columns"  => array(0 => array("name"      => $arrLan["Name Campaign"],
                                       "property1" => ""),
                            1 => array("name"      => $arrLan["Start Date"], 
                                       "property1" => ""),
                            2 => array("name"      => $arrLan["Start Time"], 
                                       "property1" => ""),
                            3 => array("name"      => $arrLan["End Date"], 
                                       "property1" => ""),
                            4 => array("name"      => $arrLan["End Time"],
                                       "property1" => ""),
                            5 => array("name"      => $arrLan["Retries"], 
                                       "property1" => ""),
                            6 => array("name"      => $arrLan["Trunk"], 
                                       "property1" => ""),
                            7 => array("name"      => $arrLan["Queue"], 
                                       "property1" => ""),
                            8 => array("name"      => $arrLan["Completed Calls"],
                                       "property1" => ""),
                            9 => array("name"      => $arrLan["Average Time"], 
                                       "property1" => ""),
                            10 => array("name"     => $arrLan["Status"], 
                                       "property1" => ""),
                            11 => array("name"     => $arrLan["Options"], 
                                       "property1" => "")));


    $estados = array("all"=>$arrLan["All"], "A"=>$arrLan["Active"], "T"=>$arrLan["Finish"], "I"=>$arrLan["Inactive"]);
    $combo_estados = "<select name='cbo_estado' id='cbo_estado' onChange='submit();'>".combo($estados,$_POST['cbo_estado'])."</select>";

    $oGrid = new paloSantoGrid($smarty);
    $oGrid->showFilter(
              "<form style='margin-bottom:0;' method='POST' action='?menu=$module_name'>" .
              "<table width='100%' border='0'><tr>".
              "<td><input type='submit' name='submit_create_campaign' value='{$arrLan['Create New Campaign']}' class='button'></td>".
              "<td class='letra12' align='right'>Estado&nbsp;$combo_estados</td>".
              "</tr></table>".
              "</form>");
//print_r($arrData);
    $contenidoModulo = $oGrid->fetchGrid($arrGrid, $arrData,$arrLang);
    return $contenidoModulo;
}

function activar_campaign($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm)
{
    global $arrLang;
    global $arrLan;
     if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        return false;
    }
    $oCampaign = new paloSantoCampaignCC($pDB);
    if($oCampaign->activar_campaign($_GET['id'],'A'))
        header("Location: ?menu=campaign_out");
    else
    {
        $smarty->assign("mb_title",$arrLan['Activate Error']);
        $smarty->assign("mb_message",$arrLan['Error when Activating the Campaign']);
    }
}

function adaptar_nombre($nombre)
{
    if(isset($nombre)){
        $nombre = strtolower ($nombre);
        $nombre = str_replace(" ","_",$nombre);
        $nombre = str_replace("á","a",$nombre);
        $nombre = str_replace("é","e",$nombre);
        $nombre = str_replace("í","i",$nombre);
        $nombre = str_replace("ó","o",$nombre);
        $nombre = str_replace("ú","u",$nombre);
        $nombre = str_replace("ñ","ni",$nombre);        
    }
    return $nombre;
}

function adaptar_formato_rte($strText) {
    //returns safe code for preloading in the RTE
    $tmpString = $strText;
    
    //convert all types of single quotes
    $tmpString = str_replace(chr(145), chr(39), $tmpString);
    $tmpString = str_replace(chr(146), chr(39), $tmpString);
    $tmpString = str_replace("'", "&#39;", $tmpString);
    
    //convert all types of double quotes
    $tmpString = str_replace(chr(147), chr(34), $tmpString);
    $tmpString = str_replace(chr(148), chr(34), $tmpString);
//  $tmpString = str_replace("\"", "\"", $tmpString);
    
    //replace carriage returns & line feeds
    $tmpString = str_replace(chr(10), " ", $tmpString);
    $tmpString = str_replace(chr(13), " ", $tmpString);

        //replace comillas dobles por una
        $tmpString = str_replace("\"", "'", $tmpString);
    
    return $tmpString;
}

function delete_campaign($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm) {
    global $arrLang;
    if (!isset($_POST['id_campaign']) || !is_numeric($_POST['id_campaign'])) {
        return false;
    }

    $oCampaign = new paloSantoCampaignCC($pDB);
    if($oCampaign->delete_campaign($_POST['id_campaign'])) {
        if ($oCampaign->errMsg!="") {
            $smarty->assign("mb_title",$arrLang['Validation Error']);
            $smarty->assign("mb_message",$oCampaign->errMsg);
        } else {
            header("Location: ?menu=campaign_out");
        }
    } else {
        $msg_error = ($oCampaign->errMsg!="")?"<br>".$oCampaign->errMsg:"";
        $smarty->assign("mb_title",$arrLan['Delete Error']);
        $smarty->assign("mb_message",$arrLan['Error when deleting the Campaign'].$msg_error);
    }
    $sContenido = view_campaign($pDB, $smarty, $module_name, $local_templates_dir, $formCampos, $oForm);
    return $sContenido;
}

?>
