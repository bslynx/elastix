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

function _moduleContent(&$smarty, $module_name)
{
    require_once "modules/$module_name/libs/paloSantoCampaignCC.class.php";    

    #incluir el archivo de idioma de acuerdo al que este seleccionado
    #si el archivo de idioma no existe incluir el idioma por defecto
    $lang=get_language();
    $script_dir=dirname($_SERVER['SCRIPT_FILENAME']);

    // Include language file for EN, then for local, and merge the two.
    $arrLan = NULL;
    include_once("modules/$module_name/lang/en.lang");
    $lang_file="modules/$module_name/lang/$lang.lang";
    if (file_exists("$script_dir/$lang_file")) {
        $arrLanEN = $arrLan;
        include_once($lang_file);
        $arrLan = array_merge($arrLanEN, $arrLan);
    }
    
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";

    global $arrConf;
    global $arrLang;
    global $arrConfig;

    //folder path for custom templates
    $base_dir = dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir = (isset($arrConfig['templates_dir']))?$arrConfig['templates_dir']:'themes';
    $local_templates_dir = "$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];
    $relative_dir_rich_text = "modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];
    $smarty->assign("relative_dir_rich_text", $relative_dir_rich_text);

    // Conexión a la base de datos CallCenter
    $pDB = new paloDB($arrConf['cadena_dsn']);

    // Mostrar pantalla correspondiente
    $contenidoModulo = '';
    $sAction = 'list_campaign';
    if (isset($_GET['action'])) $sAction = $_GET['action'];
    switch ($sAction) {
    case 'new_campaign':
        $contenidoModulo = newCampaign($pDB, $smarty, $module_name, $local_templates_dir);
        break;
    case 'edit_campaign':
        $contenidoModulo = editCampaign($pDB, $smarty, $module_name, $local_templates_dir);
        break;
    case 'csv_data':
        $contenidoModulo = displayCampaignCSV($pDB, $smarty, $module_name, $local_templates_dir);
        break;
    case 'list_campaign':
    default:
        $contenidoModulo = listCampaign($pDB, $smarty, $module_name, $local_templates_dir);
        break;
    }

    return $contenidoModulo;
}

function listCampaign($pDB, $smarty, $module_name, $local_templates_dir)
{
    global $arrLang;
    global $arrLan;
    $arrData = '';
    $oCampaign = new paloSantoCampaignCC($pDB);

    // Recoger ID de campaña para operación
    $id_campaign = NULL;
    if (isset($_POST['id_campaign']) && ereg('^[[:digit:]]+$', $_POST['id_campaign']))
        $id_campaign = $_POST['id_campaign'];

    // Revisar si se debe de borrar una campaña elegida
    if (isset($_POST['delete']) && !is_null($id_campaign)) {
        if($oCampaign->delete_campaign($id_campaign)) {
            if ($oCampaign->errMsg!="") {
                $smarty->assign("mb_title",$arrLang['Validation Error']);
                $smarty->assign("mb_message", $oCampaign->errMsg);
            } else {
            }
        } else {
            $msg_error = ($oCampaign->errMsg!="") ? "<br/>".$oCampaign->errMsg:"";
            $smarty->assign("mb_title", $arrLan['Delete Error']);
            $smarty->assign("mb_message", $arrLan['Error when deleting the Campaign'].$msg_error);
        }
    }

    // Revisar si se debe activar una campaña elegida
    if (isset($_POST['activate']) && !is_null($id_campaign)) {
        if(!$oCampaign->activar_campaign($id_campaign, 'A')) {
            $smarty->assign("mb_title", $arrLan['Activate Error']);
            $smarty->assign("mb_message", $arrLan['Error when Activating the Campaign']);
        }
    }

    // Revisar si se debe desactivar una campaña elegida
    if (isset($_POST['deactivate']) && !is_null($id_campaign)) {
        if(!$oCampaign->activar_campaign($id_campaign, 'I')) {
            $smarty->assign("mb_title", $arrLan["Desactivate Error"]);
            $smarty->assign("mb_message", $arrLan["Error when desactivating the Campaign"]);
        }
    }

    // Validar el filtro por estado de actividad de la campaña
    $estados = array(
        "all" => $arrLan["All"], 
        "A" => $arrLan["Active"], 
        "T" => $arrLan["Finish"],
        "I" => $arrLan["Inactive"]
    );
    $sEstado = 'A';
    if (isset($_GET['cbo_estado']) && isset($estados[$_GET['cbo_estado']])) {
        $sEstado = $_GET['cbo_estado'];
    }
    if (isset($_POST['cbo_estado']) && isset($estados[$_POST['cbo_estado']])) {
        $sEstado = $_POST['cbo_estado'];
    }

    // para el pagineo
    $limit = 50;
    $offset = 0;

    $url = construirURL()."&cbo_estado=$sEstado";
    $smarty->assign("url", $url);

    $arrCampaign = $oCampaign->getCampaigns(null, $offset, NULL, $sEstado);
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

    $arrCampaign = $oCampaign->getCampaigns($limit, $offset, NULL, $sEstado);

    $end = count($arrCampaign);

    if (is_array($arrCampaign)) {
        foreach($arrCampaign as $campaign) {
            $arrTmp    = array();
            $arrTmp[0] = "<input class=\"button\" type=\"radio\" name=\"id_campaign\" value=\"$campaign[id]\" />";
            $arrTmp[1] = $campaign['name'];
            $arrTmp[2] = $campaign['datetime_init'].' - '.$campaign['datetime_end'];
            $arrTmp[3] = $campaign['daytime_init'].' - '.$campaign['daytime_end'];
            $arrTmp[4] = ($campaign['retries']!="")?$campaign['retries']:"&nbsp;";
            $arrTmp[5] = is_null($campaign['trunk']) ? '(Dialplan)' : $campaign['trunk'];
            $arrTmp[6] = $campaign['queue'];
            $arrTmp[7] = ($campaign['num_completadas']!="") ? $campaign['num_completadas'] : "N/A";
            $arrTmp[8] = ($campaign['promedio']!="") ? number_format($campaign['promedio'],0) : "N/A";

            $csv_data = "&nbsp;<a href='?menu=$module_name&amp;action=csv_data&amp;id_campaign=".$campaign['id']."&amp;rawmode=yes'>[{$arrLan['CSV Data']}]</a>";
            $ver_campania = "&nbsp;<a href='?menu=$module_name&amp;action=edit_campaign&amp;id_campaign=".$campaign['id']."'>[{$arrLang['Edit']}]</a>";
            if($campaign['estatus']=='I'){
                $arrTmp[9] = $arrLan['Inactive'];
                $arrTmp[10] = $ver_campania.$csv_data;
            } elseif($campaign['estatus']=='A'){
                $arrTmp[9] = $arrLan['Active'];
                $arrTmp[10] = $ver_campania.$csv_data;
            } elseif ($campaign['estatus']=='T') {
                $arrTmp[9] = $arrLan['Finish'];
                $arrTmp[10] = $ver_campania.$csv_data;
            }
            $arrData[] = $arrTmp;
        }
    }

    // Definición de la tabla de las campañas
    $arrGrid = array("title"    => $arrLan["Campaigns List"],
        "icon"     => "images/list.png",
        "width"    => "99%",
        "start"    => ($total==0) ? 0 : $offset + 1,
        "end"      => ($offset+$limit)<=$total ? $offset+$limit : $total,
        "total"    => $total,
        "columns"  => array(
                            0 => array("name"      => '',
                                       "property1" => ""),
                            1 => array("name"      => $arrLan["Name Campaign"],
                                       "property1" => ""),
                            2 => array("name"      => $arrLan["Range Date"], 
                                       "property1" => ""),
                            3 => array("name"      => $arrLan["Schedule per Day"], 
                                       "property1" => ""),
                            4 => array("name"      => $arrLan["Retries"], 
                                       "property1" => ""),
                            5 => array("name"      => $arrLan["Trunk"], 
                                       "property1" => ""),
                            6 => array("name"      => $arrLan["Queue"], 
                                       "property1" => ""),
                            7 => array("name"      => $arrLan["Completed Calls"],
                                       "property1" => ""),
                            8 => array("name"      => $arrLan["Average Time"], 
                                       "property1" => ""),
                            9 => array("name"     => $arrLan["Status"], 
                                       "property1" => ""),
                            10 => array("name"     => $arrLan["Options"], 
                                       "property1" => "")));

    // Construir el HTML del filtro
    $smarty->assign(array(
        'MODULE_NAME'                   =>  $module_name,
        'LABEL_CAMPAIGN_STATE'          =>  $arrLan['Campaign state'],
        'estados'                       =>  $estados,
        'estado_sel'                    =>  $sEstado,
        'LABEL_CREATE_CAMPAIGN'         =>  $arrLan['Create New Campaign'],
        'LABEL_WITH_SELECTION'          =>  $arrLan['With selection'],
        'LABEL_ACTIVATE'                =>  $arrLan['Activate'],
        'LABEL_DEACTIVATE'              =>  $arrLan['Desactivate'],
        'LABEL_DELETE'                  =>  $arrLang['Delete'],
        'MESSAGE_CONTINUE_DEACTIVATE'   =>  $arrLang["Are you sure you wish to continue?"],
        'MESSAGE_CONTINUE_DELETE'       =>  $arrLan["Are you sure you wish to delete campaign?"],
    ));
    $oGrid = new paloSantoGrid($smarty);
    $oGrid->showFilter($smarty->fetch("$local_templates_dir/filter-list-campaign.tpl"));
    $contenidoModulo = 
        "<form style='margin-bottom:0;' method='post' action='?menu=$module_name'>".
        $oGrid->fetchGrid($arrGrid, $arrData,$arrLang).
        "</form>";
    return $contenidoModulo;
}

function newCampaign($pDB, $smarty, $module_name, $local_templates_dir)
{
    return formEditCampaign($pDB, $smarty, $module_name, $local_templates_dir, NULL);
}

function editCampaign($pDB, $smarty, $module_name, $local_templates_dir)
{
    $id_campaign = NULL;
    if (isset($_GET['id_campaign']) && ereg('^[[:digit:]]+$', $_GET['id_campaign']))
        $id_campaign = $_GET['id_campaign'];
    if (isset($_POST['id_campaign']) && ereg('^[[:digit:]]+$', $_POST['id_campaign']))
        $id_campaign = $_POST['id_campaign'];
    if (is_null($id_campaign)) {
        Header("Location: ?menu=$module_name");
        return '';
    } else {
        return formEditCampaign($pDB, $smarty, $module_name, $local_templates_dir, $id_campaign);
    }
}

function formEditCampaign($pDB, $smarty, $module_name, $local_templates_dir, $id_campaign = NULL)
{
    include_once "libs/paloSantoQueue.class.php";
    include_once "modules/form_designer/libs/paloSantoDataForm.class.php";

    global $arrLan;
    global $arrLang;

    // Si se ha indicado cancelar, volver a listado sin hacer nada más
    if (isset($_POST['cancel'])) {
        Header("Location: ?menu=$module_name");
        return '';
    }

    // Leer los datos de la campaña, si es necesario
    $arrCampaign = NULL;
    $oCamp = new paloSantoCampaignCC($pDB);
    if (!is_null($id_campaign)) {
        $arrCampaign = $oCamp->getCampaigns(null, null, $id_campaign);
        if (!is_array($arrCampaign) || count($arrCampaign) == 0) {
            $smarty->assign("mb_title", 'Unable to read campaign');
            $smarty->assign("mb_message", 'Cannot read campaign - '.$oCamp->errMsg);
            return '';
        }
    }

    // Obtener y conectarse a base de datos de FreePBX
    $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
    $arrConfig = $pConfig->leer_configuracion(false);
    $dsn = $arrConfig['AMPDBENGINE']['valor'] . "://" . 
        $arrConfig['AMPDBUSER']['valor'] . ":" . 
        $arrConfig['AMPDBPASS']['valor'] . "@" . 
        $arrConfig['AMPDBHOST']['valor'] . "/asterisk";
    $oDB = new paloDB($dsn);

    // Leer las troncales que se han definido en FreePBX
    $arrDataTrunks = array(
        ''  =>  '('.$arrLan['By Dialplan'].')',
    );
    $arrTrunks = getTrunks($oDB); //obtener la lista de trunks
    if (is_array($arrTrunks)){
        foreach($arrTrunks as $trunk) {
            $arrDataTrunks[$trunk[1]] = $trunk[1];
        }
    }
    
    // Leer las colas que se han definido en FreePBX, y quitar las usadas
    // en campañas entrantes.
    $arrDataQueues = array();
    $oQueue = new paloQueue($oDB);
    $arrQueues = $oQueue->getQueue();   // Todas las colas, entrantes y salientes
    if (is_array($arrQueues)) {
        $query_call_entry = "SELECT queue FROM queue_call_entry WHERE estatus = 'A'";
        $arr_call_entry = $pDB->fetchTable($query_call_entry); // Las colas entrantes
        $colasEntrantes = array();
        foreach ($arr_call_entry as $row) $colasEntrantes[] = $row[0];
        foreach($arrQueues as $rowQueue) {
            if (!in_array($rowQueue[0], $colasEntrantes)) 
                $arrDataQueues[$rowQueue[0]] = $rowQueue[1];
        }
    }

    // Cargar la información de todos los formularios creados y activos
    $oDataForm = new paloSantoDataForm($pDB); 
    $arrDataForm = $oDataForm->getFormularios(NULL,'A');

    // Impedir mostrar el formulario si no se han definido colas o no
    // quedan colas libres para usar en campañas salientes.
    if (count($arrQueues) <= 0) {
        $formCampos = getFormCampaign($arrDataTrunks, $arrDataQueues, NULL, NULL);
        $oForm = new paloForm($smarty, $formCampos);
        $smarty->assign('no_queues', 1);
    } elseif (count($arrDataQueues) <= 0) {
        $formCampos = getFormCampaign($arrDataTrunks, $arrDataQueues, NULL, NULL);
        $oForm = new paloForm($smarty, $formCampos);
        $smarty->assign('no_outgoing_queues', 1);
    } elseif (count($arrDataForm) <= 0) {
        $formCampos = getFormCampaign($arrDataTrunks, $arrDataQueues, NULL, NULL);
        $oForm = new paloForm($smarty, $formCampos);
        $smarty->assign('no_forms', 1);
    } else {
        $smarty->assign('label_manage_trunks', $arrLan['Manage Trunks']);
        $smarty->assign('label_manage_queues', $arrLan['Manage Queues']);
        $smarty->assign('label_manage_forms',  $arrLan['Manage Forms']);
        
        // Definición del formulario de nueva campaña
        $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
        $smarty->assign("CANCEL", $arrLang["Cancel"]);
        $smarty->assign("SAVE", $arrLang["Save"]);
        $smarty->assign("APPLY_CHANGES", $arrLang["Apply changes"]);
        $smarty->assign('LABEL_CALL_FILE', $arrLan['Call File']);

        // Valores por omisión para primera carga
        $arrNoElegidos = array();   // Lista de selección de formularios elegibles
        $arrElegidos = array();     // Lista de selección de formularios ya elegidos
        $values_form = NULL;        // Selección hecha en el formulario
        if (is_null($id_campaign)) {
            if (!isset($_POST['nombre'])) $_POST['nombre']='';
            if (!isset($_POST["context"]) || $_POST["context"]=="") {
                $_POST["context"] = "from-internal";
            }
            if (!isset($_POST['max_canales']) || $_POST['max_canales'] == '')
                $_POST['max_canales'] = 23;
            if (!isset($_POST['reintentos']) || $_POST['reintentos'] == '')
                $_POST['reintentos'] = 5;
            if (!isset($_POST['rte_script'])) $_POST['rte_script'] = '';
            if (!isset($_POST['values_form'])) $_POST['values_form'] = '';
            //$_POST['formulario']= split(",", $_POST['values_form']);
            $values_form = split(",", $_POST['values_form']);

        } else {
            if (!isset($_POST['nombre']))       $_POST['nombre']       = $arrCampaign[0]['name'];
            if (!isset($_POST['fecha_ini']))    $_POST['fecha_ini']    = date('d M Y',strtotime($arrCampaign[0]['datetime_init']));
            if (!isset($_POST['fecha_fin']))    $_POST['fecha_fin']    = date('d M Y',strtotime($arrCampaign[0]['datetime_end']));
            $arrDateTimeInit = split(":",$arrCampaign[0]['daytime_init']);
            $arrDateTimeEnd  = split(":",$arrCampaign[0]['daytime_end']);
            if (!isset($_POST['hora_ini_HH']))  $_POST['hora_ini_HH']  = isset($arrDateTimeInit[0])?$arrDateTimeInit[0]:"00";
            if (!isset($_POST['hora_ini_MM']))  $_POST['hora_ini_MM']  = isset($arrDateTimeInit[1])?$arrDateTimeInit[1]:"00";
            if (!isset($_POST['hora_fin_HH']))  $_POST['hora_fin_HH']  = isset($arrDateTimeEnd[0])?$arrDateTimeEnd[0]:"00";
            if (!isset($_POST['hora_fin_MM']))  $_POST['hora_fin_MM']  = isset($arrDateTimeEnd[1])?$arrDateTimeEnd[1]:"00";
            if (!isset($_POST['reintentos']))   $_POST['reintentos']   = $arrCampaign[0]['retries'];
            if (!isset($_POST['trunk']))        $_POST['trunk']        = $arrCampaign[0]['trunk'];
            if (!isset($_POST['queue']))        $_POST['queue']        = $arrCampaign[0]['queue'];
            if (!isset($_POST['context']))      $_POST['context']      = $arrCampaign[0]['context'];
            if (!isset($_POST['max_canales']))  $_POST['max_canales']  = $arrCampaign[0]['max_canales'];
            //$_POST['script'] = "";
            if (!isset($_POST['rte_script']))   $_POST['rte_script'] = $arrCampaign[0]['script'];
            //if (!isset($_POST['formulario']))           $_POST['formulario'] = "";
            //if (!isset($_POST['formularios_elegidos'])) $_POST['formularios_elegidos'] = "";
            if (!isset($_POST['values_form'])) {
                $values_form = $oCamp->obtenerCampaignForm($id_campaign);
            } else {
                $values_form = split(",", $_POST['values_form']);
            }
        }

        // rte_script es un HTML complejo que debe de construirse con Javascript.
        $smarty->assign("rte_script", adaptar_formato_rte($_POST['rte_script']));

        // Clasificar los formularios elegidos y no elegidos
        foreach ($arrDataForm as $key => $form) {
            if (in_array($form['id'], $values_form))
                $arrElegidos[$form['id']] = $form['nombre'];
            else
                $arrNoElegidos[$form['id']] = $form['nombre'];
        }

        // Generación del objeto de formulario
        $formCampos = getFormCampaign($arrDataTrunks, $arrDataQueues, $arrNoElegidos, $arrElegidos);
        $oForm = new paloForm($smarty, $formCampos);
        if (!is_null($id_campaign)) {
            $oForm->setEditMode();
            $smarty->assign('id_campaign', $id_campaign);
        }


        // En esta implementación el formulario trabaja exclusivamente en modo 'input'
        // y por lo tanto proporciona el botón 'save'
        $bDoCreate = isset($_POST['save']);
        $bDoUpdate = isset($_POST['apply_changes']);
        if ($bDoCreate || $bDoUpdate) {
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
            } elseif ($_POST['max_canales'] <= 0) { 
                $smarty->assign("mb_message", 'At least 1 used channel must be allowed.');
            } else {
                $time_ini = $_POST['hora_ini_HH'].":".$_POST['hora_ini_MM'];
                $time_fin = $_POST['hora_fin_HH'].":".$_POST['hora_fin_MM'];
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

                    if(!$pDB->genQuery("SET AUTOCOMMIT=0")) {
                        $smarty->assign("mb_message", $pDB->errMsg);
                    } else {
                        $bExito = TRUE;
                        if ($bDoCreate) {
                            $id_campaign = $oCamp->createEmptyCampaign(
                                            $_POST['nombre'],
                                            $_POST['max_canales'],
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
                            if (is_null($id_campaign)) $bExito = FALSE;
                        } elseif ($bDoUpdate) {
                            $bExito = $oCamp->updateCampaign(
                                            $id_campaign,
                                            $_POST['nombre'],
                                            $_POST['max_canales'],
                                            $_POST['reintentos'],
                                            $_POST['trunk'],
                                            $_POST['context'],
                                            $_POST['queue'],
                                            date('Y-m-d', $iFechaIni),
                                            date('Y-m-d', $iFechaFin),
                                            $time_ini,
                                            $time_fin,
                                            $_POST['rte_script']);
                        }
                        
                        // Introducir o actualizar formularios
                        if ($bExito && isset($_POST['values_form'])) {
                            if ($bDoCreate) {
                                $bExito = $oCamp->addCampaignForm($id_campaign, $_POST['values_form']);
                            } elseif ($bDoUpdate) {
                                $bExito = $oCamp->updateCampaignForm($id_campaign, $_POST['values_form']);
                            }
                        }
                        
                        // Para creación, se introduce lista de valores CSV
                        if ($bExito && $bDoCreate) {
                            $bExito = $oCamp->addCampaignNumbersFromFile($id_campaign, $_FILES['phonefile']['tmp_name']);
                        }

                        // Confirmar o deshacer la transacción según sea apropiado
                        if ($bExito) {
                            $pDB->genQuery("COMMIT");
                            header("Location: ?menu=$module_name");
                        } else {
                            $pDB->genQuery("ROLLBACK");
                            $smarty->assign("mb_title", $arrLang["Validation Error"]);
                            $smarty->assign("mb_message", $oCamp->errMsg);
                        }
                    }
                    $pDB->genQuery("SET AUTOCOMMIT=1");
                }
            }
        }
    }

    $contenidoModulo = $oForm->fetchForm(
        "$local_templates_dir/new.tpl", 
        is_null($id_campaign) ? $arrLan["New Campaign"] : $arrLan["Edit Campaign"].' "'.$_POST['nombre'].'"',
        $_POST);
    return $contenidoModulo;
}

function getFormCampaign($arrDataTrunks, $arrDataQueues, $arrSelectForm, $arrSelectFormElegidos)
{
    global $arrLan;

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
            "REQUIRED"               => "no",
            "INPUT_TYPE"             => "SELECT",
            "INPUT_EXTRA_PARAM"      => $arrDataTrunks,
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => ""
        ),
        "max_canales" => array(
            "LABEL"                  => $arrLan['Max. used channels'],
            "REQUIRED"               => "yes", 
            "INPUT_TYPE"             => "TEXT",
            "INPUT_EXTRA_PARAM"      => "",
            "VALIDATION_TYPE"        => "numeric",
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
            "INPUT_EXTRA_PARAM"      => array("TIME" => false, "FORMAT" => "%d %b %Y"),
            "VALIDATION_TYPE"        => 'ereg',
            "VALIDATION_EXTRA_PARAM" => '^[[:digit:]]{2}[[:space:]]+[[:alpha:]]{3}[[:space:]]+[[:digit:]]{4}$'
        ),
        "fecha_fin"       => array(
            "LABEL"                  => $arrLan["End"],
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "DATE",
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
            "INPUT_EXTRA_PARAM"      => $arrSelectFormElegidos,
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
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => ""
        ),
    );

    return $formCampos;
}

// TODO: validar esta funcion para verificar para qué es necesario escapar.
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

function csv_replace($s)
{
    return ($s == '') ? '""' : '"'.str_replace('"',"'", $s).'"';
}

function displayCampaignCSV($pDB, $smarty, $module_name, $local_templates_dir)
{
    $sDatosCSV = '';
            
    $id_campaign = NULL;
    if (isset($_GET['id_campaign']) && ereg('^[[:digit:]]+$', $_GET['id_campaign']))
        $id_campaign = $_GET['id_campaign'];
    if (is_null($id_campaign)) {
        Header("Location: ?menu=$module_name");
        return '';
    }

    // Leer los datos de la campaña, si es necesario
    $oCamp = new paloSantoCampaignCC($pDB);
    $arrCampaign = $oCamp->getCampaigns(null, null, $id_campaign);
    if (!is_array($arrCampaign) || count($arrCampaign) == 0) {
        $smarty->assign("mb_title", 'Unable to read campaign');
        print 'Cannot read campaign - '.$oCamp->errMsg;
        return '';
    }

    $errMsg = NULL;
    $datosCampania =& $oCamp->getCompletedCampaignData($id_campaign);
    if (is_null($datosCampania)) {
        print $oCamp->errMsg;
    } else {
        header("Cache-Control: private");
        header("Pragma: cache");
        header('Content-Type: text/csv; charset=iso-8859-1; header=present');
        header("Content-disposition: attachment; filename=\"".$arrCampaign[0]['name'].".csv\"");

        if (count($datosCampania['BASE']['DATA']) <= 0) {
            $sDatosCSV = "No Data Found\r\n";
        } else {
            // Cabeceras del archivo CSV. Se omite la primera etiqueta 'id_call'
            $lineaCSV = array();
            $lineaEspaciador = array();
            $baseLabels = $datosCampania['BASE']['LABEL'];
            array_shift($baseLabels);
            $lineaCSV = array_merge($lineaCSV, array_map('csv_replace', $baseLabels));
            $lineaEspaciador = array_fill(0, count($baseLabels), '""');
            foreach (array_keys($datosCampania['FORMS']) as $id_form) {
                $lineaCSV = array_merge($lineaCSV, array_map('csv_replace', $datosCampania['FORMS'][$id_form]['LABEL']));
                $lineaEspaciador = array_merge(
                    $lineaEspaciador, 
                    array_fill(0, count($datosCampania['FORMS'][$id_form]['LABEL']), '"FORMULARIO"')); // TODO: internacionalizar
            }
            $sDatosCSV .= join(',', $lineaEspaciador)."\r\n";
            $sDatosCSV .= join(',', $lineaCSV)."\r\n";
            
            // Datos del archivo CSV
            foreach ($datosCampania['BASE']['DATA'] as $tuplaDatos) {
                $lineaCSV = array();

                // Datos base de la campaña. Se recoge el primer elemento para id.
                $id_call = array_shift($tuplaDatos);
                $lineaCSV = array_merge($lineaCSV, array_map('csv_replace', $tuplaDatos));
                
                // Datos de los formularios de la campaña
                foreach (array_keys($datosCampania['FORMS']) as $id_form) {
                    $dataList = NULL;
                    if (isset($datosCampania['FORMS'][$id_form]['DATA'][$id_call])) {
                        $dataList = $datosCampania['FORMS'][$id_form]['DATA'][$id_call];
                    } else {
                        $dataList = array_fill(0, count($datosCampania['FORMS'][$id_form]['LABEL']), NULL);
                    }
                    $lineaCSV = array_merge($lineaCSV, array_map('csv_replace', $dataList));
                }
                
                $sDatosCSV .= join(',', $lineaCSV)."\r\n";
            }
        }
    }

    return $sDatosCSV;
}


?>
