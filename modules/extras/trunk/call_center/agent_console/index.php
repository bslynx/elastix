<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 0.5                                                  |f
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
include_once "libs/paloSantoConfig.class.php";


function _moduleContent(&$smarty, $module_name)
{
    global $arrConf;
    global $arrLang;
    global $arrConfig;
  
    // incluir el archivo de idioma de acuerdo al que este seleccionado
    // si el archivo de idioma no existe incluir el idioma por defecto
    $lang=get_language();
    $script_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $lang_file="modules/$module_name/lang/$lang.lang";

    if (file_exists("$script_dir/$lang_file"))
        include_once($lang_file);
    else
        include_once("modules/$module_name/lang/en.lang");

    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "libs/xajax/xajax.inc.php";

    $_SESSION['ip_asterisk'] = $acceso_asterisk["ip"];
    $_SESSION['user_asterisk'] = $acceso_asterisk["user"];
    $_SESSION['pass_asterisk'] = $acceso_asterisk["pass"];
    $_SESSION['ext_parqueo'] = $acceso_asterisk["ext_parqueo"];
    $_SESSION["hardware"] = $acceso_asterisk["hardware"];

//global $acceso_asterisk;
//echo "** "; print_r($acceso_asterisk);

    session_name("elastixSessionAgent");

    require_once "modules/$module_name/libs/paloSantoAgentConsole.class.php";
  
    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConfig['templates_dir']))?$arrConfig['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
    $arrConfig = $pConfig->leer_configuracion(false);

    // si el usuario se ha deslogoneado ya sea de la aplicación o desde el telefono
    if(isset($_POST['logout_agent'])) {
        disconnet_agent();
        $_SESSION['elastix_agent_user']=null;
        $_SESSION['elastix_extension']=null;
    }
    //instanciamos el objeto de la clase xajax
    $xajax = new xajax();
    $smarty->assign("MODULE_NAME", $module_name);
    $xajax->registerFunction("colgarLlamada");
    $xajax->registerFunction("colgarLlamadaEntrante");
    $xajax->registerFunction("wait_login");
    $xajax->registerFunction("evento_cerrar_navegador");

    // se consultan las extensiones de la base del asterisk para mostrarlas en el combo
    $extensions = getExtensions($arrConfig);
    $extensions = convertir_extensiones_validas($extensions);

    // si esta logoneado el agente
    if(isset($_SESSION['elastix_agent_user']) && isset($_SESSION['elastix_extension'])) {
        //asociamos la función creada anteriormente al objeto xajax
        $xajax->registerFunction("notificaLlamada");
        $xajax->registerFunction("pausar_llamadas");
        $xajax->registerFunction("guardar_informacion_cliente");
        $xajax->registerFunction("getDataContacto");
        $xajax->registerFunction("confirmar_cedula_contacto");
        $xajax->registerFunction("transferirLlamadaCiega");
        $xajax->registerFunction("hold");
        $xajax->registerFunction("marcarLlamada");
        $xajax->registerFunction("sacar_hold");

        $smarty->assign('BODYPARAMS', 'onbeforeunload="ConfirmarCierre()" onunload="ManejadorCierre()"');

        //El objeto xajax tiene que procesar cualquier petición
        $xajax->processRequests();
        $smarty->assign("SCRIPT_AJAX", $xajax->printJavascript("libs/xajax/"));

        // Texto de los botones
        $smarty->assign("HANGUP", $arrLan["Hangup"]);
        $smarty->assign("TRANFER", $arrLan["Tranfer"]);

        $smarty->assign("llamada", $arrLan["Call"]);
        $smarty->assign("script", $arrLan["Script"]);
        $smarty->assign("formulario", $arrLan["Form"]);
        $smarty->assign("title", $arrLan["Agent Console"]);

        // Conexión a la base de datos
        $pDB = getDB();
        if (!is_object($pDB->conn) || $pDB->errMsg!="") {
            $smarty->assign("mb_message", $pDB->errMsg);
        }

        //Consulta a la base para obtener los datos del agente.
        $nombre_agent = "";
        $informacion_agente = obtener_informacion_agente($pDB,$_SESSION['elastix_agent_user']);
        if($informacion_agente != null && is_array($informacion_agente) && count($informacion_agente) >0)
            $nombre_agent = $informacion_agente['name'];
        //fin de los datos del agente
        $smarty->assign("name_agent", $arrLan["Agent"].": ".$nombre_agent);
        $smarty->assign("number_agent", $arrLan["Agent Number"].": ".$_SESSION['elastix_agent_user']);
        $smarty->assign("logout", $arrLang["Logout"]);
        $smarty->assign("link_logout", "?menu=$module_name&logout_agent=yes");
        $smarty->assign("prefijo_objeto", $prefijo_objeto["prefijo"]);
        $smarty->assign("TOMAR_BREAK", $arrLan["Take Break"]);
        $smarty->assign("CANCEL", $arrLang["Cancel"]);
        $smarty->assign("ALL_BREAK", obtener_break());


        // codigo agregado para la transferencia y el marcado de llamadas

        $arrTipo = getTipoLlamada($pDB,$msj);

        //$extensions = getExtensions($arrConfig);

        $opcion_select_extension = crearSelect($extensions);
        $smarty->assign("LLAMAR", $arrLan["Accept"]);
        $smarty->assign("CONSULTAR_LLAMADA",$arrLan["consultar_llamada"]);
        $smarty->assign("opcion_select_extension", $opcion_select_extension);

        /*if( $arrTipo['tipo']== "ENTRANTE" || $arrTipo['tipo']== "SALIENTE" ) {
            $estilo_transfer ="boton_tranfer_activo";
            $smarty->assign("DESHABILITAR_TRANSFER","");
        } else {
            $estilo_transfer ="boton_tranfer_inactivo";
            $smarty->assign("DESHABILITAR_TRANSFER","disabled");
        }*/
        //$smarty->assign("ESTILO_TRANSFER",$estilo_transfer);
        /*
        if( $arrTipo['tipo']== "SALIENTE" ) {
            $estilo_marcado ="boton_marcar_activo";
            $smarty->assign("DESHABILITAR_MARCADO","");
        } else {
            $estilo_marcado ="boton_marcar_inactivo";
            $smarty->assign("DESHABILITAR_MARCADO","disabled=true");
        }*/

        //$estilo_marcado ="boton_marcar_activo";
        $smarty->assign("DESHABILITAR_MARCADO","");

        //$respuesta->addAssign( "document.getElementById('marcar').disabled  " );
        //$smarty->assign("ESTILO_MARCADO",$estilo_marcado);

        $smarty->assign("MARCAR",$arrLan['Marcar']);
        $smarty->assign("BTN_MARCAR",$arrLan['Marcar']);
        $smarty->assign("BTN_CANCELAR",$arrLan['Cancel']);

// fin de codigo agregado para la transferencia y el marcado de llamadas


        // PARA IMPLEMENTACIÓN A LA FUNCIÓN HOLD. AÚN NO ESTÁ IMPLEMENTADA POR FALLO
        if (is_null($_SESSION['channel_active'])) {
            $etiqueta_hold = $arrLan["Hold"];
            $estilo_hold = 'boton_break';
        } else {
            $etiqueta_hold = $arrLan["UnHold"];
            $estilo_hold = 'boton_unbreak';
        }
        $smarty->assign("LABEL_HOLD",$etiqueta_hold);
        $smarty->assign("STYLE_HOLD",$estilo_hold);
        // FIN PARA IMPLEMENTACIÓN A LA FUNCIÓN HOLD.

        // para el script
        $script = "";
        $smarty->assign("DATOS_SCRIPT", $script);

        // para el formulario
        $smarty->assign("formularios", $arrLan["Form"]);
        $smarty->assign("fill_fields", $arrLan["Fill the fields"]);
        $smarty->assign("SAVE", $arrLang["Save"]);
        $smarty->assign("option_form", "combo");

        $arr_objetos = "";
        $smarty->assign("DATOS_FORMULARIO", $arr_objetos);

        //Se hace esto para cuando el usuario haga un page reload en la consola, 
        //esto controla que el boton break se matenga con su estilo y accion correcta.
        $agentnum = $_SESSION['elastix_agent_user'];

        if (!estaAgenteEnPausa(null,$agentnum)) {
            $name_pausa = $arrLan["Break"];
            $style_pause = 'boton_break';
         }
        else {
            $name_pausa = $arrLan["UnBreak"];
            $style_pause = 'boton_unbreak';
        }
        //PARA EL CRONOMETRO //HAY QUE VER COMO SOLUCIONAR PORQUE ESTA FUNCION SE LLAMA CADA 4 SEGUNDOS (SOLUCIONADO CON $soloUnaVez)
        //VARIABLE GLOBLA PARA CONTROLAR EL LAMADO INNECESARIO DE LA FUNCION obtener_tiempo_acumulado_break
        //AL REFRESCAR DE NUEVO CONSULTA Y ESO TRAIA PROBLEMAS DE MULTIPLES LLAMADO A LAFUNCION POR MOTIVOS DE
        //LA PERSISTENCIA DEL CRONOMETRO 
        $_SESSION['elastix_agent_soloUnaVez'] = null;
        $smarty->assign("PAUSE",$name_pausa);
        $smarty->assign("STYLE_PAUSE", $style_pause);

        $contenidoModulo=$smarty->fetch("file:$local_templates_dir/new.tpl");

    // este caso contrario es para cuando el agente aún no ha iniciado sesión en la consola del agente
    } else {

        //asociamos la función creada anteriormente al objeto xajax
        $xajax->registerFunction("loginAgente");
        $xajax->processRequests();
        $smarty->assign("SCRIPT_AJAX", $xajax->printJavascript("libs/xajax/"));

        $smarty->assign("WELCOME_AGENT", $arrLan["Welcome to Console Agent"]);
        $smarty->assign("ENTER_USER_PASSWORD", $arrLan["Please enter your number agent"]);
        $smarty->assign("USERNAME", $arrLan["Number Agent"]);
        $smarty->assign("SUBMIT", $arrLan["Enter Agent"]);

        $id_extension_channel="";
        $extensions_name = array_keys($extensions);

        // se lo usaba para obtener la extension según la que le pertenece al usuario del Elastix, pero está dando problemas
        //$id_extension = getExtensionActual($_SESSION['elastix_user']);

        $id_extension_channel = isset($_POST['input_extension'])?$_POST['input_extension']:"";

        $smarty->assign("EXT_VALUE", $extensions);
        $smarty->assign("EXT_NAME", $extensions_name);
        $smarty->assign("agent_user_aux", isset($_POST['input_agent_user'])?$_POST['input_agent_user']:"");
        $smarty->assign("ID_EXTENSION", $id_extension_channel);
        $smarty->assign("EXTENSION", $arrLan["Extension"]);

        if(isset($_POST['submit_agent_login']))
            $smarty->assign("llamar_conectar_extension", true); 
        else   
            $smarty->assign("llamar_conectar_extension", false); 

        $contenidoModulo=$smarty->fetch("file:$local_templates_dir/login_agent.tpl");
    }

    return $contenidoModulo;
}
?>
