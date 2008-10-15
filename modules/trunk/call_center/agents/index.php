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



function _moduleContent(&$smarty, $module_name)
{

    include_once("libs/paloSantoGrid.class.php");

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
    include_once "modules/agent_console/configs/default.conf.php";
    global $arrConf;
    global $arrLang;
    global $arrLan;

    $_SESSION['ip_asterisk'] = $acceso_asterisk["ip"];
    $_SESSION['user_asterisk'] = $acceso_asterisk["user"];
    $_SESSION['pass_asterisk'] = $acceso_asterisk["pass"];

   //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConfig['templates_dir']))?$arrConfig['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    $agents_file="/etc/asterisk/agents.conf";
//    include "libs/paloSantoQueue.class.php";

    // para obtener el listado de colas
    include_once "libs/paloSantoConfig.class.php";
    $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");

    $arrConfig = $pConfig->leer_configuracion(false);
    $dsn     = $arrConfig['AMPDBENGINE']['valor'] . "://" . $arrConfig['AMPDBUSER']['valor'] . ":" . $arrConfig['AMPDBPASS']['valor'] . "@" . $arrConfig['AMPDBHOST']['valor'] . "/asterisk";
    $aDB = new paloDB($dsn);

//     $oQueue = new paloQueue($aDB);
//     $arrQueues = $oQueue->getQueue();

    $contenidoModulo="";

/*     if (is_array($arrQueues) && count ($arrQueues)>0){
         foreach($arrQueues as $queue) {
             $arrDataQueues[$queue[0]] = $queue[1];
         }
     }
*/


    include_once("libs/Agentes.class.php");
    $oAgentes = new Agentes($agents_file);
    $arrFormElements = array("description" => array("LABEL"                  => "{$arrLang['Name']}",
                                                    "EDITABLE"               => "yes",
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => "",
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
 			     "extension"   => array("LABEL"                  => "{$arrLan["Agent Number"]}",
                                                    "EDITABLE"               => "yes",
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => "",
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "password1"   => array("LABEL"                  => $arrLang["Password"],
                                                    "EDITABLE"               => "yes",
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "PASSWORD",
                                                    "INPUT_EXTRA_PARAM"      => "",
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "password2"   => array("LABEL"                  => $arrLang["Retype password"],
                                                    "EDITABLE"               => "yes",
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "PASSWORD",
                                                    "INPUT_EXTRA_PARAM"      => "",
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
/*                              "queue"      => array("LABEL"                  => $arrLan["Queue"],
                                                     "REQUIRED"               => "yes",
                                                     "INPUT_TYPE"             => "SELECT",
                                                     "INPUT_EXTRA_PARAM"      => $arrDataQueues,
                                                     "VALIDATION_TYPE"        => "text",
                                                     "VALIDATION_EXTRA_PARAM" => ""),*/

    );


    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("APPLY_CHANGES", $arrLang["Apply changes"]);
    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("EDIT", $arrLang["Edit"]);
    $smarty->assign("DELETE", $arrLang["Delete"]);
    $smarty->assign("CONFIRM_CONTINUE", $arrLang["Are you sure you wish to continue?"]);

    include_once("libs/paloSantoForm.class.php");
    $oForm = new paloForm($smarty, $arrFormElements);


    if(isset($_POST['submit_create_agent'])) {
        // Implementar
        $arrFillagent['description'] = '';
        $arrFillagent['extension']   = '';
	$arrFillagent['password1']   = '';
	$arrFillagent['password2']   = '';
//	$arrFillagent['queue']       = '';
        $contenidoModulo=$oForm->fetchForm("$local_templates_dir/new.tpl", $arrLan["New agent"],$arrFillagent);

    } else if(isset($_POST['edit'])) {

        // Tengo que recuperar la data del usuario
        $oForm->setEditMode();

        $oAgentes = new Agentes($agents_file);
        $arragent = $oAgentes->getAgents($_POST['id_agent']);
        $arrFillagent['extension'] = $arragent["number"]; //ext
        $arrFillagent['description'] = $arragent["name"]; //desc
        $arrFillagent['password1'] = $arragent["password"];
        $arrFillagent['password2'] = $arragent["password"];
//        $arrFillagent['queue'] = $arragent["queue"];

        $arrFormElements['password1']['REQUIRED']='no';
        $arrFormElements['password2']['REQUIRED']='no';
        $smarty->assign("id_agent", $_POST['id_agent']);
        $contenidoModulo=$oForm->fetchForm("$local_templates_dir/new.tpl", "{$arrLan['Edit agent']} \"" . $arrFillagent['description'] . "\"", $arrFillagent);

    } else if(isset($_POST['submit_save_agent'])) {

        if($oForm->validateForm($_POST)) {

            $_POST['extension'] = trim($_POST['extension']);
            $_POST['password1'] = trim($_POST['password1']);
            $_POST['description'] = trim($_POST['description']);
//            $_POST['queue'] = trim($_POST['queue']);

            // Exito, puedo procesar los datos ahora.
            $oAgent = new paloACL($pDB);
            if(empty($_POST['password1']) or ($_POST['password1']!=$_POST['password2'])) {
                // Error claves
                $smarty->assign("mb_message", $arrLang["The passwords are empty or don't match"]);
                $contenidoModulo=$oForm->fetchForm("$local_templates_dir/new.tpl", $arrLan["New agent"], $_POST);
            }elseif( !is_numeric( $_POST['password1'] )) {
                $smarty->assign("mb_message", $arrLan["The passwords aren't numeric values"]);
                $contenidoModulo=$oForm->fetchForm("$local_templates_dir/new.tpl", $arrLan["New agent"], $_POST);
	    }else if(!is_numeric($_POST['extension'])) {
                // Error grupo
                $smarty->assign("mb_message", "{$arrLan["Error Agent Number"]}");
                $contenidoModulo=$oForm->fetchForm("$local_templates_dir/new.tpl", $arrLan["New agent"], $_POST);
            } else {


                $oAgentes = new Agentes($agents_file);
                $agente=array(
                    0 => $_POST['extension'],
                    1 => $_POST['password1'],
                    2 => $_POST['description'],
//                    3 => $_POST['queue']
                );
    
                if (!$oAgentes->addAgent($agente,$message)) {
                    $smarty->assign("mb_message", "{$arrLan["Error Insert Agent"]} $message");
                    $contenidoModulo=$oForm->fetchForm("$local_templates_dir/new.tpl", $arrLan["New agent"], $_POST);
                } else {
                    header("Location: ?menu=agents");
                }
            }
        } else {
            // Error
            $smarty->assign("mb_title", $arrLang["Validation Error"]);
            $arrErrores=$oForm->arrErroresValidacion;
            $strErrorMsg = "<b>{$arrLang['The following fields contain errors']}:</b><br>";
            foreach($arrErrores as $k=>$v) {
                $strErrorMsg .= "$k, ";
            }
            $strErrorMsg .= "";
            $smarty->assign("mb_message", $strErrorMsg);
            $contenidoModulo=$oForm->fetchForm("$local_templates_dir/new.tpl", $arrLan["New agent"], $_POST);
        }

    } else if(isset($_POST['submit_apply_changes'])) {

        $oAgentes = new Agentes($agents_file);

        $arragent = $oAgentes->getAgents($_POST['id_agent']);
        $agentname = $arragent["name"];
        $arrFormElements['password1']['REQUIRED']='no';
        $arrFormElements['password2']['REQUIRED']='no';
        $arrFormElements['password2']['EDITABLE']='yes';
        $arrFormElements['password1']['EDITABLE']='yes';

        $oForm->setEditMode();
        if($oForm->validateForm($_POST)) {

            if(!empty($_POST['password1']) && ($_POST['password1']!=$_POST['password2'])) {
                // Error claves
                $smarty->assign("mb_title", $arrLang["Validation Error"]);
                $smarty->assign("mb_message", $arrLang["The passwords are empty or don't match"]);
                $smarty->assign("id_agent", $_POST['id_agent']);
                $arrFillagent['description'] = $_POST['description'];
                $arrFillagent['password1']   = $_POST['password1'];    
                $arrFillagent['password2']   = $_POST['password1'];
                $arrFillagent['extension']   = $_POST['id_agent'];
//                $arrFillagent['queue']       = $_POST['queue'];
		
                $contenidoModulo=$oForm->fetchForm("$local_templates_dir/new.tpl", $arrLan["Edit agent"], $arrFillagent);
            } elseif( !is_numeric( $_POST['password1'] )) {
                $smarty->assign("mb_message", $arrLan["The passwords aren't numeric values"]);
                $contenidoModulo=$oForm->fetchForm("$local_templates_dir/new.tpl", $arrLan["New agent"], $_POST);
	    }else {
    
                // Exito, puedo procesar los datos ahora.

                if (empty($_POST['password1']))
                    $_POST['password1']=$arragent["password"];

                $agente=array(
                        0 => $_POST['id_agent'],
                        1 => $_POST['password1'],
                        2 => $_POST['description'],
//                        3 => $_POST['queue']
                );

                //- La updateagent no es la adecuada porque pide el agentname. Deberia
                //- hacer una que no pida agentname en la proxima version

                if (!$oAgentes->editAgent($agente)) {
                    $smarty->assign("mb_message", "{$arrLan["Error Update Agent"]}");
                    $contenidoModulo=$oForm->fetchForm("$local_templates_dir/new.tpl", $arrLan["New agent"], $_POST);                    
                } else {
                    header("Location: ?menu=agents");
                }

            }

        } else {
            // Manejo de Error
            $smarty->assign("mb_title", $arrLang["Validation Error"]);
            $arrErrores=$oForm->arrErroresValidacion;
            $strErrorMsg = "<b>{$arrLang['The following fields contain errors']}:</b><br>";
            foreach($arrErrores as $k=>$v) {
                $strErrorMsg .= "$k, ";
            }
            $strErrorMsg .= "";
            $smarty->assign("mb_message", $strErrorMsg);
            $arrFillagent['description']= $_POST['description'];
            $arrFillagent['extension']  = $_POST['extension'];
//            $arrFillagent['queue']      = $_POST['queue'];
            $smarty->assign("id_agent", $_POST['id_agent']);
            $contenidoModulo=$oForm->fetchForm("$local_templates_dir/new.tpl", $arrLan["Edit agent"], $arrFillagent);
            /////////////////////////////////
        }

    } else if(isset($_GET['action']) && $_GET['action']=="view") {

        $oForm->setViewMode(); // Esto es para activar el modo "preview"
        $oAgentes = new Agentes($agents_file);
        $arrTmp=array();
        if (!is_null($arragent = $oAgentes->getAgents($_GET['id']))) {
            // Conversion de formato
            $arrTmp['description']        = $arragent["name"];
            $arrTmp['extension'] = $arragent["number"];
            $arrTmp['password1'] = $arragent["password"];
            $arrTmp['password2'] = $arragent["password"];
//            $arrTmp['queue'] = $arragent["queue"];

            $smarty->assign("id_agent", $_GET['id']);
            $contenidoModulo=$oForm->fetchForm("$local_templates_dir/new.tpl", $arrLan["View agent"], $arrTmp); // hay que pasar el arreglo
        }
            $contenidoModulo=$oForm->fetchForm("$local_templates_dir/new.tpl", $arrLan["View agent"], $arrTmp); // hay que pasar el arreglo

    } else {
        $error="";
        if (isset($_GET['action']) && $_GET['action']=="reparar_db" && isset($_GET['id'])) {
            $oAgentes = new Agentes($agents_file);
            $agent_db = $oAgentes->getAgents($_GET['id']);
            if ($agent_db) {
                $agent[0] = $agent_db["number"];
                $agent[1] = $agent_db["password"];
                $agent[2] = $agent_db["name"];
                if(!$oAgentes->addAgentFile($agent, $msj)) {
                    $error = $msj;
                }
            } else {
                $error = $arrLang[""];
            }
        } elseif (isset($_GET['action']) && $_GET['action']=="reparar_file") {
            $oAgentes = new Agentes($agents_file);

            if(!$oAgentes->deleteAgentFile($_GET['id'])) {
                $error = $msj;
            }

        } elseif (isset($_POST['delete'])) {
           //- TODO: Validar el id de agent
            $oAgentes = new Agentes($agents_file);
            if (!$oAgentes->deleteAgent($_POST['id_agent'])) {
                $smarty->assign("mb_message", "ERROR: {$arrLan["Error Delete Agent"]}");
                $contenidoModulo=$oForm->fetchForm("$local_templates_dir/new.tpl", $arrLan["New agent"], $_POST);
            } else {
                header("Location: ?menu=agents");
            }

        }

        if (!isset($_POST['cbo_estado']) || $_POST['cbo_estado']=="") {
            $_POST['cbo_estado'] = $arrLan["All"];
        }

        $arragents=array();
        $oAgentes = new Agentes($agents_file);
        $arrAgents = $oAgentes->getAgents();
        $arragents=$arrAgents;
        $end = count($arrAgents);
        $arrData = array();

        $indice = 0;
        $arrDesconectar = array();
        if( isset($_POST['btn_desconectar']) ) {
            foreach($arragents as $key=>$agent) {
                $item = "chk_".$agent["number"];

                if( isset($_POST[$item]) ) {
                    $arrDesconectar[$indice] = $agent["number"];
                    $indice++;
                }
            }
            $msj = "";
            if(is_array($arrDesconectar) && count($arrDesconectar)>0) {
                if(!$oAgentes->desconectarAgentes($cadena_dsn,$arrDesconectar,$msj)) {
                    $smarty->assign("mb_message", $msj);
                }
            }
        }
        $arr_numberAgentDB = array();
        if (isset($arragents) && is_array($arragents)) {
            foreach($arragents as $key=>$agent) {
                $arrTmp    = array();
                $arrTmp[2] = $agent["number"];
                $arrTmp[3] = $agent["name"];
//                $arrTmp[6] = $agent["queue"];
    
                if ($oAgentes->existAgent($agent["number"], $msj)) {
                    $arrTmp[1] = "<img src='modules/$module_name/themes/images/visto.gif' border='0'>";
                    $reparar = "";
                } else {
                    $arrTmp[1] = "<img src='modules/$module_name/themes/images/error_small.png' border='0' title=\"".$arrLan["Agent doesn't exist in configuration file"]."\">";
                    //$reparar = "&nbsp;<a href='?menu=agents&action=reparar_en_db&id=" . $agent["number"] . "'>{$arrLan['Repair']}</a>";
                    $reparar = "&nbsp;<a href='javascript:preguntar_por_reparacion(\"".$agent["number"]."\",\"reparar_db\",\"".$arrLan["To rapair is necesary add an agent in configuration file. Do you want to continue?"]."\")'>{$arrLan['Repair']}</a>";
                }
    
                if( $oAgentes->isAgentOnline( $agent["number"]) ) {
                    $arrTmp[0] = "<input type='checkbox'  name='chk_{$agent["number"]}' id='chk_{$agent["number"]}'>";
                    $arrTmp[4] = $arrLan["Online"];
                }else{
                    $arrTmp[0] = $arrTmp[0] = "<input type='checkbox' name='chk_{$agent["number"]}' id='chk_{$agent["number"]}' disabled>";
                    $arrTmp[4] = $arrLan["Offline"];
                }
    
                $arr_numberAgentDB[] = $agent["number"];
    
                $arrTmp[5] = "&nbsp;<a href='?menu=agents&action=view&id=" . $agent["number"] . "'>".$arrLang["View"]."</a>".$reparar;
                $arrData[] = $arrTmp;
            }
        }

        // OBTENIENDO DIFERENCIAS ENTRE BASE DE DATOS Y ARCHIVO. SI HAY MAS EN EL ARCHIVO QUE EN LA DB
        $msj="";
        $arr_numberAgentFile = $oAgentes->getAgentsFile($msj);
        $arr_diferencias_DB_File = array_diff($arr_numberAgentFile, $arr_numberAgentDB);
        if (is_array($arr_diferencias_DB_File)) {
            foreach($arr_diferencias_DB_File as $key=>$diferencia) {
                $arr_agent_diff = $oAgentes->existAgent($diferencia, $msj);
                if ($arr_agent_diff) {
                    $arrTmp[0] = $arrTmp[0] = "<input type='checkbox' name='chk_{$agent["number"]}' id='chk_{$agent["number"]}' disabled>";
                    $arrTmp[2] = $arr_agent_diff[0];
                    $arrTmp[3] = $arr_agent_diff[2];
                    //$arrTmp[4] = "&nbsp;";
                    $arrTmp[1] = "<img src='modules/$module_name/themes/images/error_small.png' border='0' title=\"".$arrLan["Agent doesn't exist in database"]."\">";
                    $arrTmp[4] = "&nbsp;";
                    $arrTmp[5] = "&nbsp;<a href='javascript:preguntar_por_reparacion(\"".$arr_agent_diff[0]."\",\"reparar_file\",\"".$arrLan["To rapair is necesary delete agent from configuration file. Do you want to continue?"]."\")'>{$arrLan['Repair']}</a>";
                   
                    $arrData[] = $arrTmp;
            
                }
            }
        }

        // para el pagineo
        if( isset($_GET['cbo_estado']) ) {
            $url = construirURL()."&cbo_estado={$_GET['cbo_estado']}";
        } else {
            $url = construirURL()."&cbo_estado={$_POST['cbo_estado']}";
        }
        $smarty->assign("url", $url);

        $limit = 50;
        $offset = 0;

        if(isset($_GET['cbo_estado'])) {
            $_POST['cbo_estado'] = $_GET['cbo_estado'];
            $arrData = mostrarAgentes($_GET['cbo_estado'],$arrData);
        } else {
            $arrData = mostrarAgentes($_POST['cbo_estado'],$arrData);
        }

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
        // fin datos pagineo
        $boton_desconectar = "<input type='submit' name='btn_desconectar' value='{$arrLan['Disconect']}' class='button' >";

        $arrGrid = array("title"    => $arrLan["Agent List"],
                         "icon"     => "images/user.png",
                         "width"    => "99%",
                         "start"    => ($total==0) ? 0 : $offset + 1,
                         "end"      => ($offset+$limit)<=$total ? $offset+$limit : $total,
                         "total"    => $total,
                         "columns"  => array(
                                            0 => array("name"       => $boton_desconectar,
                                                        "property1" => ""),
                                            1 => array("name"       => $arrLan["Configure"],
                                                        "property1" => ""),
                                            2 => array("name"       => $arrLan["Number"],
                                                        "property1" => ""),
                                            3 => array("name"       => $arrLang["Name"],
                                                        "property1" => ""),
/*                                            6 => array("name"       => $arrLan["Queue"],
                                                        "property1" => ""),*/
                                            4 => array("name"       => $arrLang["Status"],
                                                        "property1" => ""),
                                            5 => array("name"       => $arrLang["Options"],
                                                        "property1" => ""),
                                            )
                        );
        $codigo_script = "
            <script language='JavaScript' type='text/javascript'>
                function preguntar_por_reparacion(id_agente, tipo, pregunta) {
                    if (confirm(pregunta)) {
                        if (tipo != '') {
                            window.open('?menu=agents&action='+tipo+'&id='+id_agente,'_parent');
                        }
                    }
                }
            </script>";


        $oGrid = new paloSantoGrid($smarty);

        $estados = array(
                "All"=>$arrLan["All"],
                "Online"=>$arrLan["Online"],
                "Offline"=>$arrLan["Offline"],
                "Repair"=>$arrLan["Repair"],
        );

        $combo_estados = "<select name='cbo_estado' id='cbo_estado' onChange='submit();'>".
                            combo($estados,$_POST['cbo_estado'])."</select>";

        $oGrid->showFilter("
                <form id='form_agents'style='margin-bottom:0;' method='POST' action='?menu=agents'>
                    <input type='submit' name='submit_create_agent' value='{$arrLan["New agent"]}' class='button'>
                    <td align='right'>{$arrLang["Status"]}&nbsp;$combo_estados</td>
        ");

        $contenidoModulo = $oGrid->fetchGrid($arrGrid, $arrData,$arrLang)."</form> $codigo_script";
    }

    return $contenidoModulo;
}



function mostrarAgentes($opcion_estado,$arrData) {//,$arrLan) {
//<font color='green'>Conectado</font>
    global $arrLan;
    $index = 0;
    $arr = array();
    // agentes que estan en logeados a la consola del agente
    if($opcion_estado == "Online") {
        for ($i=0;$i<count($arrData);$i++) {
            if($arrData[$i][5]== $arrLan["Online"]) {
                $arrData[$i][5] = "<font color='green'>".$arrData[$i][5]."</font>";
                $arr[$index] = $arrData[$i];
                $index++;
            }
        }
    // agentes que no estan en logeados a la consola del agente
    } elseif($opcion_estado == "Offline") {
        for ($i=0;$i<count($arrData);$i++) {
            if($arrData[$i][5]== $arrLan["Offline"]) {
                $arr[$index] = $arrData[$i];
                $index++;
            }
        }
    // agentes cuyos configuraciones poseen problemas
    } elseif($opcion_estado == "Repair") {
        for ($i=0;$i<count($arrData);$i++) {
            if($arrData[$i][5]=="&nbsp;") { 
                $arr[$index] = $arrData[$i];
                $index++;
            }
        }
    // todos los agentes registrados en el sistema
    } else {
        for ($i=0;$i<count($arrData);$i++) {
            if($arrData[$i][5]== $arrLan["Online"]) {
                $arrData[$i][5] = "<font color='green'>".$arrData[$i][5]."</font>";
            }
            $arr[$index] = $arrData[$i];
            $index++;
        }
    }
    return $arr;
}

/*BASE Campaign */
/*
CREATE TABLE agent (
    id              INTEGER PRIMARY KEY,
    number          VARCHAR(40) NOT NULL,
    name            VARCHAR(250) NOT NULL,
    password        VARCHAR(250) NOT NULL 
    queue           VARCHAR(16) NOT NULL 
);
*/
?>
