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
  $Id: index.php,v 1.2 2008/06/07 06:28:13 cbarcos Exp $ */

require_once("libs/paloSantoGrid.class.php");
require_once("libs/Agentes.class.php");

function _moduleContent(&$smarty, $module_name)
{
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
        $arrLan = array_merge($arrLanEN, $arrLangModule);
    }
    
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/agent_console/configs/default.conf.php"; // For asterisk AMI credentials

    global $arrConf;
    global $arrLang;
    global $arrConfig;
    $arrLang = array_merge($arrLang, $arrLan);

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
    $sAction = 'list_agents';
    if (isset($_GET['action'])) $sAction = $_GET['action'];
    switch ($sAction) {
    case 'new_agent':
        $contenidoModulo = newAgent($pDB, $smarty, $module_name, $local_templates_dir);
        break;
    case 'edit_agent':
        $contenidoModulo = editAgent($pDB, $smarty, $module_name, $local_templates_dir);
        break;
    case 'list_agents':
    default:
        $contenidoModulo = listAgent($pDB, $smarty, $module_name, $local_templates_dir);
        break;
    }

    return $contenidoModulo;
}

function listAgent($pDB, $smarty, $module_name, $local_templates_dir)
{
    global $arrLan;
    global $arrLang;
    
    $oAgentes = new Agentes($pDB);

    // Operaciones de manipulación de agentes
    if (isset($_POST['reparar_db']) && ereg('^[[:digit:]]+$', $_POST['reparar_db'])) {
        // Hay que agregar el agente al archivo de configuración de Asterisk
        $infoAgente = $oAgentes->getAgents($_POST['reparar_db']);
        if (!is_array($infoAgente)) {
            $smarty->assign(array(
                'mb_title'      =>  'DB Error',
                'mb_message'    =>  $oAgentes->errMsg,
            ));
        } elseif (count($infoAgente) == 0) {
            // Agente no existe en DB, no se hace nada
        } elseif (!$oAgentes->addAgentFile(array(
            $infoAgente['number'],
            $infoAgente['password'],
            $infoAgente['name'],
            ))) {
            $smarty->assign(array(
                'mb_title'      =>  $arrLan["Error saving agent in file"],
                'mb_message'    =>  $oAgentes->errMsg,
            ));
        }
    } elseif (isset($_POST['reparar_file']) && ereg('^[[:digit:]]+$', $_POST['reparar_file'])) {
        // Hay que remover el agente del archivo de configuración de Asterisk
        if (!$oAgentes->deleteAgentFile($_POST['reparar_file'])) {
            $smarty->assign(array(
                'mb_title'      =>  $arrLan["Error when deleting agent in file"],
                'mb_message'    =>  $oAgentes->errMsg,
            ));
        }
    } elseif (isset($_POST['delete']) && isset($_POST['agent_number']) && ereg('^[[:digit:]]+$', $_POST['agent_number'])) {
        // Borrar el agente indicado de la base de datos, y del archivo
        if (!$oAgentes->deleteAgent($_POST['agent_number'])) {
            $smarty->assign(array(
                'mb_title'      =>  $arrLan["Error Delete Agent"],
                'mb_message'    =>  $oAgentes->errMsg,
            ));
        }
    } elseif (isset($_POST['disconnect']) && isset($_POST['agent_number']) && ereg('^[[:digit:]]+$', $_POST['agent_number'])) {
        // Desconectar agentes. El código en Agentes.class.php puede desconectar
        // varios agentes a la vez, pero aquí sólo se desconecta uno.
        $arrAgentes = array($_POST['agent_number']);
        if (!$oAgentes->desconectarAgentes($arrAgentes)) {
            $smarty->assign(array(
                'mb_title'      =>  'Unable to disconnect agent',
                'mb_message'    =>  $oAgentes->errMsg,
            ));
        }
    }

    // Estados posibles del agente
    $sEstadoAgente = 'All';
    $listaEstados = array(
        "All"       =>  $arrLan["All"],
        "Online"    =>  $arrLan["Online"],
        "Offline"   =>  $arrLan["Offline"],
        "Repair"    =>  $arrLan["Repair"],
    );
    if (isset($_GET['cbo_estado'])) $sEstadoAgente = $_GET['cbo_estado'];
    if (isset($_POST['cbo_estado'])) $sEstadoAgente = $_POST['cbo_estado'];
    if (!in_array($sEstadoAgente, array_keys($listaEstados))) $sEstadoAgente = 'All';

    // Leer los agentes activos y comparar contra la lista de Asterisk
    $listaAgentesCallCenter = $oAgentes->getAgents();
    function get_agente_num($t) { return $t['number']; }
    $listaNumAgentesCallCenter = array_map('get_agente_num', $listaAgentesCallCenter);
    $listaNumAgentesAsterisk = $oAgentes->getAgentsFile();
    $listaNumSobrantes = array_diff($listaNumAgentesAsterisk, $listaNumAgentesCallCenter);
    $listaNumFaltantes = array_diff($listaNumAgentesCallCenter, $listaNumAgentesAsterisk);
    
    /* La variable $listaNumSobrantes tiene ahora todos los IDs de agente que 
       constan en Asterisk y no en la tabla call_center.agent como activos.
       La variable $listaNumFaltantes tiene los agentes que constan en 
       call_center.agent y no en Asterisk. El código posterior asume que el 
       archivo de agentes de Asterisk debería cambiarse para que refleje la
       tabla call_center.agent .
    */
    // Campo sync debe ser OK, o ASTERISK si consta en Asterisk pero no en 
    // CallCenter, o CC si consta en CallCenter pero no en Asterisk.
    foreach (array_keys($listaAgentesCallCenter) as $k) {
        $listaAgentesCallCenter[$k]['sync'] =
            in_array($listaAgentesCallCenter[$k]['number'], $listaNumFaltantes) 
                ? 'CC' : 'OK';
    }
    
    // Lista de todos los agentes conocidos, incluyendo los sobrantes.
    $listaAgentes = $listaAgentesCallCenter;
    foreach ($listaNumSobrantes as $idSobrante) {
        $listaAgentes[] = array(
            'id'        =>  NULL,
            'number'    =>  $oAgentes->arrAgents[$idSobrante][0],
            'name'      =>  $oAgentes->arrAgents[$idSobrante][2],
            'password'  =>  $oAgentes->arrAgents[$idSobrante][1],
            'estatus'   =>  NULL,
            'sync'      =>  'ASTERISK',
        );
    }

    // Listar todos los agentes que están conectados
    $listaOnline = $oAgentes->getOnlineAgents();
    if (is_array($listaOnline)) {
        foreach (array_keys($listaAgentes) as $k) {
            $listaAgentes[$k]['online'] = in_array($listaAgentes[$k]['number'], $listaOnline);
        }
    } else {
        $smarty->assign("mb_title", 'Unable to read agent');
        $smarty->assign("mb_message", 'Cannot read agent - '.$oAgentes->errMsg);
        foreach (array_keys($listaAgentes) as $k) 
            $listaAgentes[$k]['online'] = NULL;
    }
    
    // Filtrar los agentes conocidos según el estado que se requiera
    function estado_Online($t)  { return ($t['sync'] == 'OK' && $t['online']); }
    function estado_Offline($t) { return ($t['sync'] == 'OK' && !$t['online']); }
    function estado_Repair($t)  { return ($t['sync'] != 'OK'); }
    if ($sEstadoAgente != 'All') $listaAgentes = array_filter($listaAgentes, "estado_$sEstadoAgente");
    
    $arrData = array();
    $sImgVisto = "<img src='modules/$module_name/themes/images/visto.gif' border='0' />";
    $sImgErrorCC = "<img src='modules/$module_name/themes/images/error_small.png' border='0' title=\"".$arrLan["Agent doesn't exist in configuration file"]."\" />";
    $sImgErrorAst = "<img src='modules/$module_name/themes/images/error_small.png' border='0' title=\"".$arrLan["Agent doesn't exist in database"]."\" />";
    $smarty->assign(array(
        'PREGUNTA_BORRAR_AGENTE_CONF'   =>  $arrLan["To rapair is necesary delete agent from configuration file. Do you want to continue?"],
        'PREGUNTA_AGREGAR_AGENTE_CONF'  =>  $arrLan["To rapair is necesary add an agent in configuration file. Do you want to continue?"],
    ));
    foreach ($listaAgentes as $tuplaAgente) {
        $tuplaData = array(
            "<input class=\"button\" type=\"radio\" name=\"agent_number\" value=\"{$tuplaAgente["number"]}\" />",
            NULL,
            $tuplaAgente['number'],
            $tuplaAgente['name'],
            (($tuplaAgente['sync'] != 'CC') ? ($tuplaAgente['online'] ? $arrLan["Online"] : $arrLan["Offline"]) : '&nbsp;'),
            "<a href='?menu=agents&amp;action=edit_agent&amp;id_agent=" . $tuplaAgente["number"] . "'>[".$arrLang["Edit"]."]</a>",
        );
        switch ($tuplaAgente['sync']) {
        case 'OK':
            $tuplaData[1] = $sImgVisto;
            break;
        case 'ASTERISK':
            $tuplaData[1] = $sImgErrorAst.
                "&nbsp;<a href='javascript:preguntar_por_reparacion(\"".
                $tuplaAgente['number'].
                "\",\"reparar_file\", pregunta_borrar_agente_conf)'>{$arrLan['Repair']}</a>";
            $tuplaData[5] = '&nbsp;';   // No mostrar opción de editar agente que no está en DB
            break;
        case 'CC':
            $tuplaData[1] = $sImgErrorCC.
                "&nbsp;<a href='javascript:preguntar_por_reparacion(\"".
                $tuplaAgente['number'].
                "\",\"reparar_db\", pregunta_agregar_agente_conf)'>{$arrLan['Repair']}</a>";
            break;
        }
        $arrData[] = $tuplaData;
    }

    $limit = 50;
    $offset = 0;
    $url = construirURL()."&amp;cbo_estado=$sEstadoAgente";
    $smarty->assign("url", $url);

    if( is_array($arrData) ) {
        $arrData = array_slice($arrData,$offset);
    }
    $total = count($arrData);

    // Si se quiere avanzar a la sgte. pagina
    if(isset($_GET['nav']) && $_GET['nav']=="end") {
        $totalCalls  = count($arrData);
        // Mejorar el sgte. bloque.
        if(($totalCalls%$limit)==0) {
            $offset = $totalCalls - $limit;
        } else {
            $offset = $totalCalls - $totalCalls%$limit;
        }
    }

    // Si se quiere avanzar a la sgte. pagina
    if(isset($_GET['nav']) && $_GET['nav']=="next") {
        //if (isset(estado']))
        $offset = $_GET['start'] + $limit - 1;
    }

    // Si se quiere retroceder
    if(isset($_GET['nav']) && $_GET['nav']=="previous") {
        $offset = $_GET['start'] - $limit - 1;
    }

    if( is_array($arrData) ) {
        $arrData = array_slice($arrData,$offset,$limit);
    }
    $end = count($arrData);

    // Construir el reporte de los agentes activos
    $arrGrid = array("title"    => $arrLan["Agent List"],
                     "icon"     => "images/user.png",
                     "width"    => "99%",
                     "start"    => ($total==0) ? 0 : $offset + 1,
                     "end"      => ($offset+$limit)<=$total ? $offset+$limit : $total,
                     "total"    => $total,
                     "columns"  => array(
                                        0 => array("name"       => '&nbsp;',
                                                    "property1" => ""),
                                        1 => array("name"       => $arrLan["Configure"],
                                                    "property1" => ""),
                                        2 => array("name"       => $arrLan["Number"],
                                                    "property1" => ""),
                                        3 => array("name"       => $arrLang["Name"],
                                                    "property1" => ""),
                                        4 => array("name"       => $arrLang["Status"],
                                                    "property1" => ""),
                                        5 => array("name"       => $arrLang["Options"],
                                                    "property1" => ""),
                                        )
                    );
    $oGrid = new paloSantoGrid($smarty);
    $smarty->assign(array(
        'LABEL_STATE'           =>  $arrLang['Status'],
        'LABEL_CREATE_AGENT'    =>  $arrLan["New agent"],
        'estados'               =>  $listaEstados,
        'estado_sel'            =>  $sEstadoAgente,
        'MODULE_NAME'           =>  $module_name,
        'LABEL_WITH_SELECTION'  =>  $arrLan['With selection'],
        'LABEL_DISCONNECT'      =>  $arrLan['Disconnect'],
        'LABEL_DELETE'          =>  $arrLang['Delete'],
        'MESSAGE_CONTINUE_DELETE' => $arrLang["Are you sure you wish to continue?"],
    ));
    $oGrid->showFilter($smarty->fetch("$local_templates_dir/filter-list-agents.tpl"));
    return 
        "<form id='form_agents'style='margin-bottom:0;' method='POST' action='?menu=agents'>".
        $oGrid->fetchGrid($arrGrid, $arrData,$arrLang).
        "</form>";
}

function newAgent($pDB, $smarty, $module_name, $local_templates_dir)
{
    return formEditAgent($pDB, $smarty, $module_name, $local_templates_dir, NULL);
}

function editAgent($pDB, $smarty, $module_name, $local_templates_dir)
{
    $id_agent = NULL;
    if (isset($_GET['id_agent']) && ereg('^[[:digit:]]+$', $_GET['id_agent']))
        $id_agent = $_GET['id_agent'];
    if (isset($_POST['id_campaign']) && ereg('^[[:digit:]]+$', $_POST['id_agent']))
        $id_agent = $_POST['id_agent'];
    if (is_null($id_agent)) {
        Header("Location: ?menu=$module_name");
        return '';
    } else {
        return formEditAgent($pDB, $smarty, $module_name, $local_templates_dir, $id_agent);
    }
}

function formEditAgent($pDB, $smarty, $module_name, $local_templates_dir, $id_agent)
{
    global $arrLang;
    global $arrLan;
    
    // Si se ha indicado cancelar, volver a listado sin hacer nada más
    if (isset($_POST['cancel'])) {
        Header("Location: ?menu=$module_name");
        return '';
    }

    // Leer los datos de la campaña, si es necesario
    $arrAgente = NULL;
    $oAgentes = new Agentes($pDB);
    if (!is_null($id_agent)) {
        $arrAgente = $oAgentes->getAgents($id_agent);
        if (!is_array($arrAgente) || count($arrAgente) == 0) {
            $smarty->assign("mb_title", 'Unable to read agent');
            $smarty->assign("mb_message", 'Cannot read agent - '.$oAgentes->errMsg);
            return '';
        }
    }

    require_once("libs/paloSantoForm.class.php");
    $arrFormElements = getFormAgent($smarty);

    // Valores por omisión para primera carga
    if (is_null($id_agent)) {
        // Creación de nuevo agente
        if (!isset($_POST['extension']))    $_POST['extension'] = '';
        if (!isset($_POST['description']))  $_POST['description'] = '';
        if (!isset($_POST['password1']))    $_POST['password1'] = '';
        if (!isset($_POST['password2']))    $_POST['password2'] = '';
    } else {
        // Modificación de agente existente
        if (!isset($_POST['extension']))    $_POST['extension'] = $arrAgente['number'];
        if (!isset($_POST['description']))  $_POST['description'] = $arrAgente['name'];
        if (!isset($_POST['password1']))    $_POST['password1'] = $arrAgente['password'];
        if (!isset($_POST['password2']))    $_POST['password2'] = $arrAgente['password'];
        
        // Volver opcional el cambio de clave de acceso
        $arrFormElements['password1']['REQUIRED'] = 'no';
        $arrFormElements['password2']['REQUIRED'] = 'no';
    }
    $oForm = new paloForm($smarty, $arrFormElements);
    if (!is_null($id_agent)) {
        $oForm->setEditMode();
        $smarty->assign("id_agent", $id_agent);
    }

    $bDoCreate = isset($_POST['submit_save_agent']);
    $bDoUpdate = isset($_POST['submit_apply_changes']);
    if ($bDoCreate || $bDoUpdate) {
        if(!$oForm->validateForm($_POST)) {
            // Falla la validación básica del formulario
            $smarty->assign("mb_title", $arrLang["Validation Error"]);
            $arrErrores = $oForm->arrErroresValidacion;
            $strErrorMsg = "<b>{$arrLang['The following fields contain errors']}:</b><br>";
            foreach($arrErrores as $k=>$v) {
                $strErrorMsg .= "$k, ";
            }
            $strErrorMsg .= "";
            $smarty->assign("mb_message", $strErrorMsg);
        } else {
            foreach (array('extension', 'password1', 'password2', 'description') as $k)
                $_POST[$k] = trim($_POST[$k]);
            if ($_POST['password1'] != $_POST['password2'] || ($bDoCreate && $_POST['password1'] == '')) {
                $smarty->assign("mb_title", $arrLang["Validation Error"]);
                $smarty->assign("mb_message", $arrLang["The passwords are empty or don't match"]);
            } elseif (!ereg('^[[:digit:]]+$', $_POST['password1'])) {
                $smarty->assign("mb_title", $arrLang["Validation Error"]);
                $smarty->assign("mb_message", $arrLan["The passwords aren't numeric values"]);
            } elseif (!ereg('^[[:digit:]]+$', $_POST['extension'])) {
                $smarty->assign("mb_title", $arrLang["Validation Error"]);
                $smarty->assign("mb_message", $arrLan["Error Agent Number"]);
            } else {
                $bExito = TRUE;
                
                if ($bDoUpdate && $_POST['password1'] == '')
                    $_POST['password1'] = $arrAgente['password'];
                $agente = array(
                    0 => $_POST['extension'],
                    1 => $_POST['password1'],
                    2 => $_POST['description'],
                );
                if ($bDoCreate) {
                    $bExito = $oAgentes->addAgent($agente);
                    if (!$bExito) $smarty->assign("mb_message",
                        "{$arrLan["Error Insert Agent"]} ".$oAgentes->errMsg);
                } elseif ($bDoUpdate) {
                    $bExito = $oAgentes->editAgent($agente);
                    if (!$bExito) $smarty->assign("mb_message",
                        "{$arrLan["Error Update Agent"]} ".$oAgentes->errMsg);
                }
                if ($bExito) header("Location: ?menu=$module_name");
            }
        }
    }

    $contenidoModulo = $oForm->fetchForm(
        "$local_templates_dir/new.tpl", 
        is_null($id_agent) ? $arrLan["New agent"] : $arrLan['Edit agent'].' "'.$_POST['description'].'"',
        $_POST);
    return $contenidoModulo;
}

function getFormAgent(&$smarty)
{
    global $arrLan;
    global $arrLang;

    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("APPLY_CHANGES", $arrLang["Apply changes"]);
    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("EDIT", $arrLang["Edit"]);
    $smarty->assign("DELETE", $arrLang["Delete"]);
    $smarty->assign("CONFIRM_CONTINUE", $arrLang["Are you sure you wish to continue?"]);

    $arrFormElements = array(
        "description" => array(
            "LABEL"                  => "{$arrLang['Name']}",
            "EDITABLE"               => "yes",
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "TEXT",
            "INPUT_EXTRA_PARAM"      => "",
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => ""),
        "extension"   => array(
            "LABEL"                  => "{$arrLan["Agent Number"]}",
            "EDITABLE"               => "yes",
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "TEXT",
            "INPUT_EXTRA_PARAM"      => "",
            "VALIDATION_TYPE"        => "numeric",
            "VALIDATION_EXTRA_PARAM" => ""),
        "password1"   => array(
            "LABEL"                  => $arrLang["Password"],
            "EDITABLE"               => "yes",
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "PASSWORD",
            "INPUT_EXTRA_PARAM"      => "",
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => ""),
        "password2"   => array(
            "LABEL"                  => $arrLang["Retype password"],
            "EDITABLE"               => "yes",
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "PASSWORD",
            "INPUT_EXTRA_PARAM"      => "",
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => ""),
    );
    return $arrFormElements;
}
?>
