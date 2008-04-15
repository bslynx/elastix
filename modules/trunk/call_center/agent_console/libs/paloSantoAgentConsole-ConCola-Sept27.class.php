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
  $Id: new_campaign.php $ */

include_once("libs/paloSantoDB.class.php");
require_once("libs/smarty/libs/Smarty.class.php");
require_once "/var/lib/asterisk/agi-bin/phpagi-asmanager.php";
//require_once("libs/xajax/xajaxResponse.inc.php");
require_once("libs/js/jscalendar/calendar.php"); 
require_once("modules/break_administrator/libs/PaloSantoBreaks.class.php");

/*  FUNCION XAJAX:
    funcion que se llama cada 4 segundos para:
    - verificar que el usuario aun está conectado a una cola
    - verificar si hay una llamada conectada y si es asi traer los datos de la llamada.
      Se muestran diferentes datos dependiendo de la pestaña en que se encuentra.
*/

function notificaLlamada($pestania, $prefijo_objeto, $nueva_llamada, $id_formulario=NULL) {
    $respuesta = new xajaxResponse();

    $agentnum = $_SESSION['elastix_agent_user'];
    $extn     = $_SESSION['elastix_extension'];
    $cola     = $_SESSION['elastix_queue_agent'];
    //$agentnum = 8002;

    //instanciamos el objeto para generar la respuesta con ajax


    if (!estaAgenteConectado($agentnum,$extn,$mensaje)) {
        disconnet_agent();
        $_SESSION['elastix_agent_user'] = null;
        $_SESSION['elastix_extension']  = null;
        $_SESSION['elastix_queue_agent']= null;
        if (isset($mensaje) && $mensaje!="")
            $respuesta->addScript("alert('".$mensaje."');");
        $respuesta->addScript("document.getElementById('frm_agent_console').submit();");
    } else {
        // Conexión a la base de datos
        global $arrLang;
        $pDB = getDB();

        if (!is_object($pDB) || $pDB->errMsg!="") {
            $respuesta->addAssign("mensajes_informacion","innerHTML",$pDB->errMsg);
            return $respuesta;
        }

        $smarty = getSmarty(); // Load smarty 
        $colgar_disable = "true";
        $style= 'boton_desactivo';
        $arr_campania = getDataCampania($pDB,$agentnum);
        $template = "vacio.tpl";
        $texto_llamada = $texto_script = "";

        $actualizar_llamada = $actualizar_script = $actualizar_form = false; // sirve para controlar los addAssign de los formularios, llamadas y scripts
        if (is_array($arr_campania) && count($arr_campania)>0) {
            $id_call = $arr_campania["id_calls"];
            $texto = $arr_campania["script"];
            $colgar_disable = "false";
            $llamada = $arr_campania["phone"];
            $cliente = $arr_campania["nombre_cliente"];
            $tiempo_transcurso_llamada = explode(":",$arr_campania["duracion_llamada"]);
            $style= 'boton_activo';

            $numero_telefono  = "<table border='0'>";
            $numero_telefono .= "<tr>";
            $numero_telefono .= "<td with='350' class='celda_callcenter_grande'><b>".$arrLang['Call Number'].":</b></td>";
            $numero_telefono .= "<td class='celda_callcenter_grande'>".$llamada."</td>";
            $numero_telefono .= "</tr>";
            $numero_telefono .= "<tr>";
            $numero_telefono .= "<td with='350' class='celda_callcenter_grande'><b>".$arrLang['Name'].":</b></td>";
            $numero_telefono .= "<td class='celda_callcenter_grande'>".$cliente."</td>";
            $numero_telefono .= "</tr></table>";
            $respuesta->addAssign("numero_telefono","innerHTML",$numero_telefono);

            $codigo_js="";


                switch ($pestania) {
                    case 'LLAMADA':
                        if ($nueva_llamada["llamada"] == "" || $nueva_llamada["llamada"]!=$id_call) {
                            $respuesta->addScript("document.getElementById('nueva_llamada').value = '$id_call';");
                            $actualizar_pagina=true;
                            $arr_atributos = getAttributesCall($pDB, $id_call);
                            if (is_array($arr_atributos)) {
                                $texto_llamada = "<br><table border='0'>";
                                foreach ($arr_atributos as $id=>$atributo) {
                                    $texto_llamada .= "<tr>";
                                    $texto_llamada .= "<td with='350' class='celda_callcenter'><b>".$atributo["columna"].":</b></td>";
                                    $texto_llamada .= "<td class='celda_callcenter'>".$atributo["value"]."</td>";
                                    $texto_llamada .= "</tr>";
                                }
                                $texto_llamada .= "</table>";
                            }
                        } // fin del if q controla si hay nueva llamada
                        //$template = "consola_llamada.tpl";
                    break;
                    case 'SCRIPT':
                        if ($nueva_llamada["script"] == "" || $nueva_llamada["nuevo_script"]!=$id_call) {
                            $respuesta->addScript("document.getElementById('nuevo_script').value = '$id_call';");
                            $actualizar_script=true;
                            $arr_atributos = getAttributesCall($pDB, $id_call);
                            if (is_array($arr_atributos)) {
                                foreach ($arr_atributos as $id=>$atributo) {
                                    $texto = str_replace("{".$atributo['columna']."}", $atributo["value"], $texto);
                                }
                                $texto_script = $texto;
                                $texto_script = "<span class='celda_callcenter'>".$texto_script."</span>";
                            }
                        } // fin del if q controla si hay nueva llamada
                    break;
                    case 'FORMULARIO':
                        if ($nueva_llamada["form"] == "" || $nueva_llamada["form"]!=$id_call) {
                            $respuesta->addScript("document.getElementById('nuevo_form').value = '$id_call';");
                            $actualizar_form=true;
                            $mostrar_template=false;
                            $arr_form = obtener_formularios($pDB,$arr_campania['id_campaign']); 
        
                            if(is_array($arr_form) && count($arr_form)>0){
    
                                if($id_formulario==NULL)
                                    $id_formulario = obtener_primer_formulario($arr_form);
    
                                $list_id_form = "";
                                foreach ($arr_form as $key=>$form) {
                                    if ($list_id_form!="") $list_id_form .= ",";
                                    $list_id_form .= $form["id"];
                                }
        
                                $smarty_option = smarty_option($arr_form,$id_formulario);
                                $smarty->assign("option_form", $smarty_option);
                                $id_form = $id_formulario;
                                if (strlen(trim($list_id_form))>0) {
                                    $sQuery = "
                                    SELECT
                                        field.id id_field,
                                        field.id_form,
                                        field.etiqueta,
                                        field.tipo,
                                        field.value value_field,
                                        field.orden,
                                        data.id id_data,
                                        data.id_calls,
                                        data.id_form_field,
                                        data.value value_data
                                    FROM form_field field LEFT JOIN form_data_recolected data
                                        ON field.id = data.id_form_field and data.id_calls=$id_call
                                    WHERE field.id_form in ($list_id_form)
                                    ORDER BY field.id_form, field.orden";
        
                                    $arr_fields = $pDB->fetchTable($sQuery, true);
                                    if (is_array($arr_fields) && count($arr_fields)>0) {
                                        $break_id = $id = $arr_fields[0]["id_form"];
                                        $ids_formularios=$id;
                                        foreach($arr_fields as $key=>$field) {
                                            $funcion_js = "";
                                            $input = crea_objeto($smarty, $field, $prefijo_objeto, $funcion_js);
                                            $etiqueta = $field["etiqueta"];
                                            $tipo = $field["tipo"];
                                            if ($break_id != $field["id_form"]) {
                                                $break_id = $field["id_form"];
                                                $id = $break_id;
                                                $ids_formularios.="-".$id;
                                            }
        
                                            $data_field[] = array("TYPE" => $tipo, "TAG" => $etiqueta, "INPUT" => $input, "ID_FORM" => $id);
                                            $id = "";
                                            $codigo_js .= $funcion_js;
                                        }
                                        foreach ($data_field as $key=>$data) {
                                            $smarty->assign("FORMULARIO", $data);
                                        }
        
                                        $smarty->assign("FORMULARIO", $data_field);
                                        $smarty->assign("id_formularios", $ids_formularios);
                                        $smarty->assign("formularios", $arrLang["Form"]);
                                        $smarty->assign("fill_fields", $arrLang["Fill the fields"]);
                                        $smarty->assign("SAVE", $arrLang["Save"]);
                                        $mostrar_template=true;
                                    }
                                }
                                if ($mostrar_template) $template = "consola_formulario.tpl";
                                else $template = "vacio.tpl";
                            }
                            else{
                                global $arrLang;
                                $smarty->assign("no_definidos_formularios",$arrLang['Forms Nondefined']);
                                $template = "vacio.tpl";
                            }
                            $texto_formulario=$smarty->fetch("file:/var/www/html/modules/agent_console/themes/default/$template");
                        } // fin del if q controla si hay nueva llamada
                    break;
                } // fin del switch
     
                // SETEANDO MENSAJE DEL ESTATUS ACTUAL DE LA LLAMADA
                $respuesta->addAssign("estatus_actual","innerHTML",$arrLang["Calling"]);
                $respuesta->addScript("document.getElementById('celda_estatus_actual').className = 'fondo_estatus_llamada'; ");
                //PARA EL CRONOMETRO 
                if($tiempo_transcurso_llamada) {
                    $hora    = $tiempo_transcurso_llamada[0];
                    $minuto  = $tiempo_transcurso_llamada[1];
                    $segundo = $tiempo_transcurso_llamada[2];
                }
                else{
                    $hora    = 0;
                    $minuto  = 0;
                    $segundo = 0;
                }
                $respuesta->addScript(" var fecha_aux2 = breakCronometroSet(0,0,0,$hora,$minuto,$segundo);
                                                estado_cronometro('llamada',fecha_aux2);");
       } else {
            $actualizar_pagina=$actualizar_form=$actualizar_script=true;
            $respuesta->addScript("document.getElementById('nueva_llamada').value = '';
                                   document.getElementById('nuevo_script').value = '';
                                   document.getElementById('nuevo_form').value = '';");
            if (!estaAgenteEnPausa($agentnum,$cola)) {
                $estatus = $arrLang["Call no active"];
                $respuesta->addScript("document.getElementById('celda_estatus_actual').className = 'fondo_estatus_no_llamada';");
                //PARA EL CRONOMETRO
                $respuesta->addScript("estado_cronometro('noLlamada',null);\n");
            } else {
                $estatus = $arrLang["In Break"].": ".obtener_break_audit($pDB,$_SESSION['elastix_agent_audit']);
                $respuesta->addScript("document.getElementById('celda_estatus_actual').className = 'fondo_estatus_break';");          
                //PARA INGRESAR LA AUDITORIA DE BREAK
                if(is_null($_SESSION['elastix_agent_audit'])){
                    $id_audit = auditoria_break_insert($_SESSION['elastix_agent_break'],$agentnum);
                    if($id_audit!=null)
                        $_SESSION['elastix_agent_audit']=$id_audit;
                }
                //PARA EL CRONOMETRO //HAY QUE VER COMO SOLUCIONAR PORQUE ESTA FUNCION SE LLAMA CADA 4 SEGUNDOS (SOLUCIONADO CON $soloUnaVez)
                //VARIABLE GLOBLA PARA CONTROLAR EL LAMADO INNECESARIO DE LA FUNCION obtener_tiempo_acumulado_break
                //AL REFRESCAR DE NUEVO CONSULTA Y ESO TRAIA PROBLEMAS DE MULTIPLES LLAMADO A LAFUNCION POR MOTIVOS DE
                //LA PERSISTENCIA DEL CRONOMETRO  
                if(!isset($_SESSION['elastix_agent_soloUnaVez'])){
                    $_SESSION['elastix_agent_soloUnaVez']=true;
                    $tiempo_acumulado = obtener_tiempo_acumulado_break(date('Y-m-d'),$agentnum,$_SESSION['elastix_agent_break']);
                    
                    if($tiempo_acumulado) {
                        $hora    = $tiempo_acumulado[0];
                        $minuto  = $tiempo_acumulado[1];
                        $segundo = $tiempo_acumulado[2];
                    }
                    else{
                        $hora    = 0;
                        $minuto  = 0;
                        $segundo = 0;
                    }
                    $respuesta->addScript(" var fecha_aux = breakCronometroSet(0,0,0,$hora,$minuto,$segundo);
                                                estado_cronometro('enBreak',fecha_aux);");
                }
            }

            $texto_script = getScriptCampaniaActiva($pDB);
            if ($texto_script) {
                //$texto_script = $texto_script;
                $texto_script = "<span class='celda_callcenter'>".$texto_script."</span>";
            } else {
                $texto_script="";
            }

            $respuesta->addAssign("estatus_actual","innerHTML",$estatus);
            $texto = "<span style='color:#000000; FONT-SIZE: 13px;'><b>Agente $agentnum</b><br>En este momento no se ha comunicado con ningún número telefónico.</span>";
        } // fin del if que consulta datos de la llamada activa

        $pDB->disconnect();
        if ($actualizar_pagina) {
            $respuesta->addAssign("contenedor_llamada","innerHTML",$texto_llamada);
        }
        if ($actualizar_script) {
            $respuesta->addAssign("contenedor_script","innerHTML",$texto_script);
        }
        if ($actualizar_form) {
            $respuesta->addAssign("contenedor_formulario","innerHTML",$texto_formulario);
            if ($texto_formulario != "") {
                $respuesta->addScript("mostrarFormularioSeleccionado('$id_formulario');");
            }
        }

        $respuesta->addScript("document.getElementById('hangup').disabled=$colgar_disable; \n");
        $respuesta->addScript("document.getElementById('hangup').className='$style'; \n");

        if (isset($codigo_js) && trim($codigo_js)!="") {
           $respuesta->addScript($codigo_js);
        }
        $respuesta->addAssign("control","value","1");
    }
//$respuesta->addScript("alert('xxx');");
    //tenemos que devolver la instanciación del objeto xajaxResponse
    return $respuesta;
}

/*  FUNCION XAJAX:
    funcion que cuelga una llamada conectada
*/
function colgarLlamada() {

    //if (!$agentnum)
    $agentnum = $_SESSION['elastix_agent_user'];

    global $arrLang;
    $pDB = getDB();
    $smarty = getSmarty(); // Load smarty 

    $sQuery = "
        SELECT
          current_calls.id,
          current_calls.Channel channel,
          current_calls.fecha_inicio,
          calls.phone
        FROM
          current_calls,
          calls
        WHERE
          current_calls.agentnum='$agentnum'
          and current_calls.event='Link'
          and current_calls.id_call = calls.id";
    $arr_llamada = $pDB->getFirstRowQuery($sQuery, true);

    if (is_array($arr_llamada) && count($arr_llamada)>0) {
        if (isset($arr_llamada["channel"])) {
            // Conexión con el Asterisk
            $astman = new AGI_AsteriskManager();
            if (!$astman->connect("127.0.0.1", 'admin' , 'elastix456')) {
                $resultado = $arrLang["Error when connecting to database Call Center"];
            } else {
                $arr_resultado = $astman->Hangup($arr_llamada["channel"]);

//                 $arr_1 = array_keys($arr);
//                 $texto = implode(",",$arr_1);

                $resultado = $arr_resultado["Response"]." - ".$arr_resultado["Message"];
                $astman->disconnect();
            }
        } else {
            $resultado = $arrLang["Call no active"];
        }
    } else {
        $resultado = $arrLang["Call no active"];
    }
    //$pDB->disconnect();

    //instanciamos el objeto para generar la respuesta con ajax
    $respuesta = new xajaxResponse();
    //escribimos en la capa con id="respuesta" el texto que aparece en $salida
    //$respuesta->addAssign("respuesta_evento","innerHTML",$resultado);
    $respuesta->addAssign("control","value","1");

    //tenemos que devolver la instanciación del objeto xajaxResponse
    return $respuesta;
}

/*  FUNCION XAJAX:
    funcion que quita y añade a un agente de la cola, con el objetivo de evitar que por
    cierto tiempo el agente no reciba llamadas.
*/
function pausar_llamadas($id_break)
{
    global $arrLang;
    $respuesta = new xajaxResponse();
    $agentnum = $_SESSION['elastix_agent_user'];
    $cola = $_SESSION['elastix_queue_agent'];
    $member = "Agent/$agentnum";
    $smarty = getSmarty(); // Load smarty 

    $astman = new AGI_AsteriskManager( );	
    if (!$astman->connect("127.0.0.1", 'admin' , 'elastix456')) {
        $resultado = $arrLang["Error when connecting to Asterisk Manager"];
    } 
    else{
        if (!estaAgenteEnPausa($agentnum,$cola)) {
            $salida = $astman->QueuePause($cola,$member,"true");
            $resultado = $salida['Message'];
            $_SESSION['elastix_agent_break']=$id_break;
            $_SESSION['elastix_agent_soloUnaVez']=null;
            /*$id_audit = auditoria_break_insert($id_break,$agentnum); SE CAMBIO LA IMPLEMENTACION AL INSERTAR  A NOTIFICA LLAMADA POR RAZONES DE EXACTITUD EN EL TIEMPO DE INICIO DE BREAK
            if($id_audit!=null)
                $_SESSION['elastix_agent_audit']=$id_audit;*/
            $name_pausa = $arrLang["UnBreak"];
            $style = 'boton_unbreak';
            $respuesta->addScript("document.getElementById('div_list').style.display ='none'; \n");
        }
        else {
            $salida = $astman->QueuePause($cola,$member,"false");
            $resultado = $salida['Message'];
            if(!auditoria_break_update($_SESSION['elastix_agent_audit'])){
                $smarty->assign("mb_title", $arrLang["Audit Error"]);
                $smarty->assign("mb_message", $arrLang['Audit of break could not be inserted']);
            }    
            $_SESSION['elastix_agent_audit'] = null;
            $_SESSION['elastix_agent_break'] = null;
            $_SESSION['elastix_agent_soloUnaVez']=null;
            $name_pausa = $arrLang["Break"];
            $respuesta->addScript("estado_cronometro('unBreak',null);\n");
            $style = 'boton_break';
         }
        $respuesta->addScript("document.getElementById('pause').value='".$name_pausa."'; \n");
        $respuesta->addScript("document.getElementById('pause').className='".$style."'; \n");        
    }
    //$respuesta->addAssign("respuesta_evento","innerHTML",$resultado);
    return $respuesta;
}


/* Funcion que por seguridad cuando ingrese un agente ingrese como estado sin pausa*/
function entrar_agente_sin_pausa($agente,$cola)
{
    $member = "Agent/$agente";
    global $arrLang;
    $respuesta = new xajaxResponse();
    $astman = new AGI_AsteriskManager( );	

    if (!$astman->connect("127.0.0.1", 'admin' , 'elastix456')) {
        $resultado = $arrLang["Error when connecting to Asterisk Manager"];
    } 

    if(estaAgenteEnPausa($agente,$cola))
    {
         $salida = $astman->QueuePause($cola,$member,"false");
         if($salida['Response']=='Error')
            return $arrLang['Unable to pause Agent to queue: No such queue'];
         else return 'se_quito_pause';
    }
    else{
        return 'sin_pause';
    }
}
/*  FUNCION XAJAX:
    funcion hace que la extension que llega por parametro se logonee con el numero de agente
    que tambien llega por parámetro. Esto se lo hace originado una llamada desde la extension
    hacia el numero *8888 que es desde donde se conecta a la cola.
*/
function loginAgente($extn,$numAgente) {
    global $arrLang;    
    $result = 1;
    $respuesta = new xajaxResponse();
    // Conexión con el Asterisk
    $astman = new AGI_AsteriskManager();
    if (!$astman->connect("127.0.0.1", 'admin' , 'elastix456')) {
        $respuesta->addScript("alert('".$arrLang["Error when connecting to Asterisk Manager"]."')");
    } else {
        if ($extn!="0" && $numAgente!="") {
            $arr_resultado = $astman->Originate($extn, "*8888".$numAgente, 'from-internal',1,NULL,NULL, NULL, NULL, NULL, NULL,'yes', 'id_nada');
            $respuesta->addAssign("pregunta_logoneo", "value", "1");
            $astman->disconnect();
        } else {
            $respuesta->addAssign("mensaje","innerHTML",$arrLang["Please enter your number agent"]);
            $respuesta->addAssign("pregunta_logoneo", "value", "0");
        }
    }
    return $respuesta;
 
}

/*  FUNCION XAJAX:
    funcion que pregunta cada segundo y medio si el agente que llega por parametro esta logoneado en la extension que tambien llega por parametro.
*/
function wait_login($extn, $num_agent) {
    global $arrLang;
    $pDB = getDB();

    //instanciamos el objeto para generar la respuesta con ajax
    $respuesta = new xajaxResponse();
    //$cola = "8000";

    $sQuery = "SELECT queue FROM agent WHERE number=$num_agent";
    $queue = $pDB->getFirstRowQuery($sQuery, true);
    if (is_array($queue) && count($queue)) {
        $cola = $queue["queue"];
        //$respuesta->addScript("alert('Cola = ".$queue["queue"]."')");
        if (estaAgenteConectado($num_agent,$extn,$mensaje)) {
            $respuesta->addAssign("status_login", "value","1");
            $encolado = entrar_agente_sin_pausa($num_agent,$cola);
            if($encolado=='se_quito_pause' || $encolado=='sin_pause'){
                $_SESSION['elastix_agent_user'] = $num_agent;
                $_SESSION['elastix_extension'] = $extn;
                $_SESSION['elastix_queue_agent']= $cola;
            }
            else{
                $respuesta->addAssign("mensaje","innerHTML","$encolado");
                $respuesta->addAssign("error_igual_numero_agente","value",1);
                $respuesta->addScript("document.getElementById('input_agent_user').value=''");
            }
        } else {
            if ($mensaje!="" && $mensaje!=$arrLang["Agent isn't in Queue Asterisk"]) {
                $respuesta->addAssign("mensaje","innerHTML","$mensaje");
                $respuesta->addAssign("error_igual_numero_agente","value",1);
                $respuesta->addScript("document.getElementById('input_agent_user').value=''");
            }
            $respuesta->addAssign("status_login", "value","0");
        }
    } else {
        
    }

    return $respuesta;
}

/*  FUNCION XAJAX:
    Que guarda la informacion del cliente que es ingresada desde el formulario del callcenter.
*/
function guardar_informacion_cliente($data_cliente) {
    global $arrLang;
    //instanciamos el objeto para generar la respuesta con ajax
    $respuesta = new xajaxResponse();
    $pDB = getDB();
    $agentnum = $_SESSION['elastix_agent_user'];

    $arr_campania = getDataCampania($pDB, $agentnum);
    $valido=false;
    if (is_array($arr_campania) && count($arr_campania)>0) {
        if (is_array($data_cliente)) {
            $id_calls = $arr_campania["id_calls"];

            foreach($data_cliente as $indice=>$objeto) {
                $id_form_field = $objeto[0];
                $value = $objeto[1];
                $existe = existe_registro($pDB, $id_calls, $id_form_field);

                if ($existe) {
                    $sPeticionSQL = paloDB::construirUpdate(
                        "form_data_recolected",
                        array(
                            "value"          =>  paloDB::DBCAMPO($value)
                        ),
                        " id_calls=$id_calls and id_form_field=$id_form_field "
                        );
                         $result = $pDB->genQuery($sPeticionSQL);
                } else {
                    if (isset($value) && trim($value)!="") { 
                        $sPeticionSQL = paloDB::construirInsert(
                        "form_data_recolected",
                        array(
                            "id_calls"       =>  paloDB::DBCAMPO($id_calls),
                            "id_form_field"  =>  paloDB::DBCAMPO($id_form_field),
                            "value"          =>  paloDB::DBCAMPO($value)
                        ));
                        $result = $pDB->genQuery($sPeticionSQL);
                    } else {
                        $result=true;
                    }
                }

                if (!$result) {
                    $valido=false;
                    break;
                } else {
                    $valido=true;
                }
            }
        }
    }
    if ($valido) {
//        $respuesta->addAssign("mensaje","innerHTML",$arrLang["Information was saved"]);
        $respuesta->addScript("alert('".$arrLang["Information was saved"]."')");
    } else {
//        $respuesta->addAssign("mensaje","innerHTML",$arrLang["Error saving client information"]);
        $respuesta->addScript("alert('".$arrLang["Error saving client information"]."')");
    }
    //$pDB->disconnect();
    return $respuesta;
}


/* Funcion que se encarga de resolver acciones cuando se cierra el navegador incorecctamente
   como: No cerro un Break y no se hizo logoff del agente
 */
function evento_cerrar_navegador()
{
    $respuesta = new xajaxResponse();
    $resultado = disconnet_agent();
    if($resultado=='ok')
        $respuesta->addAlert("Agent logoff was make correct and audit break was save");
    else
        $respuesta->addAlert($resultado);
    return $respuesta;
}


/* FUNCIONES QUE NO SON EJECUTADAS DESDE AJAX */
/* ------------------------------------------ */


/* Funcion que retorna todos los breaks que puede tomar un agente
   Esta funcion se basa en la funcion getBreaks del modulo Breaks.class.php
*/
function obtener_break()
{
    $pDB = getDB();
    $oBreak = new PaloSantoBreaks($pDB);
    $arrBreaks = $oBreak->getBreaks(null,'A');
    if (is_array($arrBreaks) && count($arrBreaks)>0){
        foreach($arrBreaks as $id => $break){
            $allBreak[$break['id']] = $break['name'];  
        }
        return $allBreak;
    }
    else 
        return array();
}

/*
    Funcion que retorna el nombre del break que tiene una auditoria dada
    Es usada al momento de actualizar la fecha y hora fin de la auditoria
*/
function obtener_break_audit($pDB,$id_audit){
    $sQuery = " SELECT be.name 
                FROM 
                    audit au
                        inner join 
                    break be on au.id_break = be.id
                WHERE 
                    au.id = $id_audit;";
    $result = $pDB->getFirstRowQuery($sQuery, true);
    if (is_array($result) && count($result)>0){
        return $result['name'];
    }
    else 
        return "";
}

/* Funcion que inserta una nueva auditoria del break que tomo el agente
   se guarda la fecha actual y la hora exacta con segundos que ela gente tomo el break 
   Retorna el id de la auditoria recien ingresada y la funxion pausar_llamadas la guarda en la session 
   en la variable de session elastix_agent_audit*/
function auditoria_break_insert($id_break,$num_agent)
{
    global $arrLang;
    $pDB = getDB();
    $smarty = getSmarty(); // Load smarty 

    $informacion_agent = obtener_informacion_agente($pDB,$num_agent); 
    if($informacion_agent != null && is_array($informacion_agent) && count($informacion_agent) >0){
        $sPeticionSQL = paloDB::construirInsert(
        "audit",
        array(
            "id_agent"      =>  $informacion_agent['id'],
            "id_break"      =>  $id_break,
            "datetime_init" =>  "'".date("Y-m-d H:i:s")."'",
            "datetime_end"  =>  NULL,
            "duration"      =>  NULL
         ));
        $result = $pDB->genQuery($sPeticionSQL);
        
        if($result){
            $id_audit = $pDB->getFirstRowQuery("select last_insert_id() id_audit", true); //retorn el id recien insertado de la auditoria para actualizar las fechas de fin despues

            if($id_audit)
                return $id_audit['id_audit']; 
            else {
                $smarty->assign("mb_title", $arrLang["Audit Error"]);
                $smarty->assign("mb_message", $arrLang['Number of audit nonassigned']);
                return null;
            }
        }
        else{
            $smarty->assign("mb_title", $arrLang["Audit Error"]);
            $smarty->assign("mb_message", $arrLang['Audit of break could not be inserted']);
            return null;
        }
    }
    else{
        $smarty->assign("mb_title", $arrLang["Agent Error"]);
        $smarty->assign("mb_message", $arrLang['Id of the agent could not be obtained']);
        return null;    
    }
}

/* Funcion que actualiza la auditoria que se pasa para poner la 
   fecha y hora del break que termino de tomar el agente
   El id de la auditoria esta guardada en al session con elastix_agent_audit 
   si todo esta bien se le asigna nulo aesta variable 
   Esto lo realiza la funcion pausar_llamadas */
function auditoria_break_update($id_audit)
{
    global $arrLang;
    $pDB = getDB();
    $smarty = getSmarty(); // Load smarty 
    $now = date("Y-m-d H:i:s");
    $sPeticionSQL = paloDB::construirUpdate(
    "audit",
    array(
        "datetime_end"  =>  "'$now'",
        "duration"      =>  "timediff('$now',datetime_init)"
    ),
    "id = $id_audit");
    $result = $pDB->genQuery($sPeticionSQL);

    if($result)
        return true;
    else
        return false; 
}
/* Funcion que se encarga de obtener el tiempo consumido de un break dado
   en la tabla auditoria */
function obtener_tiempo_acumulado_break($fecha,$agentenum,$id_break)
{
    global $arrLang;
    $pDB = getDB();
    $smarty = getSmarty(); // Load smarty 

    //PASO 1 OBTENGO INFORMACION DEL AGENTE ESPECIALMENTE SU ID
    $informacion_agent = obtener_informacion_agente($pDB,$agentenum); 
    if($informacion_agent != null && is_array($informacion_agent) && count($informacion_agent) >0){

        $sQuery = " /*OBTENEMOS EL TIEMPO TOTAL DEL BREAL SUMANDO EL TIEMPO ACUMULADO DEL MISMO 
                      BREAK EN SUCESOS ANTERIORES DEL MISMO DIA + EL TIEMPO QUE TRANSCURRE DEL 
                      BREAK QUE TOMO RECIEN*/
                    select 
                        SEC_TO_TIME(ifnull(t.acumulado,0) + t.transcurso) tiempoBreak
                    from
                        /*PASO 2 OBTENGO LA SUMA ACUMULADA DE UN BREAK ESPECIFICO DE UN DIA*/
                        (select 
                            sum(TIME_TO_SEC(a.duration)) acumulado, 
                            /*PASO 3 OBTENGO LA AUDIT RECIEN REGISTRADA ESTO SE USA CUANDO REFRESCA EL AGENTE EL
                            NAVEGADOR ENTONCES SE TIENE QUE AÑADIR EL TIEMPO TRANSCURRIDO YA QUE AL REFRESCAR SE 
                            PIERDE EL TIEMPO TRANSCURRIDO DESDE QUE TOMO EL BREAK*/
                            (select
                                TIME_TO_SEC(timediff(now(),au.datetime_init)) 
                            from 
                                audit au
                            where 
                                au.id = ".$_SESSION['elastix_agent_audit'].") transcurso
                        from 
                            audit a 
                        where 
                            a.id_agent = ".$informacion_agent['id']." and 
                            a.id_break= ".$id_break." and 
                            a.datetime_init like concat('%','".$fecha."','%') /*and
                            a.duration is not null */
                        group by 
                            a.id_break,
                            a.id_agent
                        ) t";

        $result = $pDB->getFirstRowQuery($sQuery, true);
        if ($result!=null && is_array($result) && count($result)>0){
                $time  = explode(":",$result['tiempoBreak']);
                return $time;
        }
        else 
            return false;
    }
}

/* Funcion que obtiene el id del agente que se pasa, se pasa el numero del agente
   y retorna el id del agente */
function obtener_informacion_agente($pDB,$num_agente)
{
    global $arrLang;
    $sql = "select id,number,name,password from agent where number = '$num_agente';";
    $smarty = getSmarty(); // Load smarty 

    $result = $pDB->getFirstRowQuery($sql, true); 
    if(is_array($result) && count($result)>0)
        return $result; 
    else {
        return null;
    }    
}
function existe_registro($pDB, $id_calls, $id_form_field) {
    $sQuery = "SELECT id FROM form_data_recolected WHERE id_calls=$id_calls AND id_form_field=$id_form_field";
    $result = $pDB->getFirstRowQuery($sQuery, true);
    //$pDB->disconnect(); 
    if (is_array($result) && count($result)>0)
        return true;
    else 
        return false;
}

/*funcion que trae datos de la campaña que tiene asignada un agente, el cual llega por parametro*/
function getDataCampania($pDB, $agentnum) {
    $sQuery = "
    SELECT
      campaign.id id_campaign,
      campaign.script script,
      calls.id id_calls,
      calls.phone phone,
      call_attribute.value nombre_cliente,
      timediff(now(),current_calls.fecha_inicio) duracion_llamada
    FROM
      current_calls,
      calls,
      campaign,
      call_attribute
    WHERE
      current_calls.agentnum='$agentnum'
      and current_calls.event='Link'
      and current_calls.id_call = calls.id
      and calls.id_campaign = campaign.id
      and calls.id = call_attribute.id_call
      and call_attribute.column_number = '1'";
    $result = $pDB->getFirstRowQuery($sQuery, true);
    //$pDB->disconnect();
    return ($result);
}


function getAttributesCall($pDB, $id_call) {
    $sQuery = "SELECT columna, value FROM call_attribute where id_call=$id_call";
    $arr_atributos = $pDB->fetchTable($sQuery, true);
    //$pDB->disconnect();
    return $arr_atributos;
}

/* Funcion que trae el script de una campaña activa */
function getScriptCampaniaActiva($pDB) {
    $sQuery = "SELECT script FROM campaign where estatus='A'";
    $result = $pDB->getFirstRowQuery($sQuery, true);
    if (is_array($result) && count($result)>0) {
        return $result["script"];
    }
    return false;
}


/* Funcion que hace que el agente se deslogonee */
function disconnet_agent() {
    global $arrLang;
    $agentnum = $_SESSION['elastix_agent_user'];
    $cola     = $_SESSION['elastix_queue_agent'];
    $resultado = 'ok';
    // Conexión con el Asterisk
    $astman = new AGI_AsteriskManager();
    if (!$astman->connect("127.0.0.1", 'admin' , 'elastix456')) {
        $resultado = $arrLang["Error when connecting to Asterisk Manager"];
    } else {
        if(estaAgenteEnPausa($agentnum,$cola)){
            $salida = $astman->QueuePause($cola,"Agent/$agentnum","false");
            if($salida['Response']=='Error')
                $resultado = $arrLang['Unable to pause Agent to queue: No such queue'];
            if(!auditoria_break_update($_SESSION['elastix_agent_audit']))
                $resultado = $arrLang["Audit Error"].": ".$arrLang['Audit of break could not be inserted'];
            $_SESSION['elastix_agent_audit'] = null;
            $_SESSION['elastix_agent_break'] = null;
        }
        $arr_resultado = $astman->Agentlogoff($agentnum);
        $resultado = $arr_resultado["Response"]." - ".$arr_resultado["Message"];
        $astman->disconnect();
    }
    return $resultado;
}

/* Funcion que retorna un array con las extensiones creadas en el asterisk */
function getExtensions($arrConf) {
    $pDBa = getDBAsterisk($arrConf);

    $sQuery="select extension,
            (select count(*) from iax where iax.id=users.extension) as iax,
            (select count(*) from sip where sip.id=users.extension) as sip
            from users order by extension";
    $arrData = array();
    if (!$arrayResult = $pDBa->fetchTable($sQuery,true)){
        $error = $pDBa->errMsg;
    }else{	
	if (is_array($arrayResult) && count($arrayResult)>0) {
	    $arrData[] = "No extension";
            foreach($arrayResult as $item) {
                //si tiene iax mayor a 0 es IAX
                if ($item["iax"]>0) $device="IAX/";
                if ($item["sip"]>0) $device="SIP/";
                $arrData[$device.$item["extension"]] = $device.$item["extension"];	
            }
	}
    }
    $pDBa->disconnect();
    return $arrData;
}

/* Funcion que retorna la extension asignada al usuario que ingreso a al aplicacion elastix */
function getExtensionActual($username) {
    $pDB = getDB();
    $sQuery = "SELECT extension FROM acl_user WHERE name='$username'";
    $extension = $pDB->getFirstRowQuery($sQuery, true);
    if (is_array($extension) && count($extension)>0) {
        return $extension["extension"];
    }
    //$pDB->disconnect();
    return false;
}

/* funcion que recive el canal (Ej: SIP/405) y devuelve la extensión (Ej: 405) */
function getExtensionChannel($extensions, $id_extension) {
    if (is_array($extensions)) {
        foreach($extensions as $key=>$extension) {
            if (ereg("^[[:alnum:]]*/([[:digit:]]+)$",$extension,$regs)) {
                $ext = $regs[1];
                if ($ext == $id_extension)
                    return $extension;
            }
        }
    }
    return false;
}

/* funcion que retorna true si un agente esta conectado en una extension (estos datos llegan por parametro) */
function estaAgenteConectado($numAgente,$extn, & $mensaje)
{
    global $arrLang;
    $mensaje = "";
    $hardware = "SIP|IAX|ZAP|H323|OH323";
    //global $tipo_equipos; echo $tipo_equipos;
    $astman = new AGI_AsteriskManager();	
    if (!$astman->connect("127.0.0.1", 'admin' , 'elastix456')) {
        $mensaje = "4 ".$arrLang["Error when connecting to Asterisk Manager"];
    } else {
        $strAgentShow = $astman->Command(" agent show online");
        if ($strAgentShow["Response"] != "Error") {
            $astman->disconnect();
            $arrAgentShow = split("\n", $strAgentShow['data']);
            if (is_array($arrAgentShow) && count($arrAgentShow)>0) {
                foreach($arrAgentShow as $line) {
                    if(ereg("^[[:space:]]*([[:digit:]]{2,})", $line, $arrReg1)) {
                        // is la condicion es verdadera, quiere decir que el agente que llego como parametro ya esta conectado
                        if($numAgente == $arrReg1[1]) {
                            ereg("(($hardware)/([[:digit:]]{2,}))", $line, $arrReg2);
                            // si la condicion es verdadera quiere decir que el agente con la extension seleccionada ya esta conectado
                            if($extn == $arrReg2[1]) {
                                return true;
                            } else {
                                $mensaje = $arrLang["Number Agent already connected with extension"]." $extn";
                            }
                        }
                    }
                }
                $mensaje = $arrLang["Agent isn't in Queue Asterisk"];
            } else {
                $mensaje = $arrLang["Error when consulting Agent in Asterisk Manager"];
            }
        } else {
            $mensaje = $strAgentShow["Message"];
        }
    }
    return false;
}


/* funcion que retorna true si un agente esta en pause en la cola */
function estaAgenteEnPausa($numAgente,$cola) {
    global $arrLang;
    
    $astman = new AGI_AsteriskManager();	
    if (!$astman->connect("127.0.0.1", 'admin' , 'elastix456')) {
        $resultado = $arrLang["Error when connecting to Asterisk Manager"];
    } else {
        $strAgentShow = $astman->Command(" queue show $cola");
        $astman->disconnect();
        $arrAgentShow=array();
        if (is_array($strAgentShow))
            $arrAgentShow = split("\n", $strAgentShow['data']);

        foreach($arrAgentShow as $line) {
            if(ereg("[[:alnum:]]*/([[:digit:]]{2,})", $line, $arrReg1)) {
                // is la condicion es verdadera, quiere decir que el agente que llego como parametro ya esta conectado
                if($numAgente == $arrReg1[1]) {
                    if(strpos($line,"(paused)") === false)//busco si tiene estado de pausa
                        return false;
                    else return true;
                }
            }
        }
    }
    return false;
}
function crea_objeto(&$smarty, $field, $prefijo_objeto, &$funcion_js) {
    $tipo_objeto = $field["tipo"];
    $input="";
    switch ($tipo_objeto) {
        case "LIST":
            $listado = explode(",",$field["value_field"]);
            $input = "";
            $selected="";
            foreach($listado as $key=>$item) {
                if ($field["value_data"] == $item) $selected = "selected";
                else $selected="";
                $input .= "<option $selected value='$item'>$item</option>";
            }
            if ($input!="") {
                $input = "<select name='$prefijo_objeto"."$field[id_field]' id='$prefijo_objeto"."$field[id_field]' class='SELECT'>$input</select>";
            }
        break;
        case "DATE":
            $input = '<input style="width: 10em; color: #840; background-color: #fafafa; border: 1px solid #999999; text-align: center" name="'.$prefijo_objeto.$field["id_field"].'" value="" id="'.$prefijo_objeto.$field["id_field"].'" type="text" />
            <a href="#" id="calendar_'.$prefijo_objeto.$field["id_field"].'">
            <img align="middle" border="0" src="/libs/js/jscalendar/img.gif" alt="" />
            </a>';

            $funcion_js = 'Calendar.setup({"ifFormat":"%d %b %Y","daFormat":"%Y/%m/%d","firstDay":1,"showsTime":true,"showOthers":true,"timeFormat":12,"inputField":"'.$prefijo_objeto.$field['id_field'].'","button":"calendar_'.$prefijo_objeto.$field['id_field'].'"});';

        break;
        case "TEXTAREA":
            $input = "<textarea name='$prefijo_objeto"."$field[id_field]' id='$prefijo_objeto"."$field[id_field]' rows='3' cols='50'>$field[value_data]</textarea>";
        break;
        case "LABEL":
            $input = "<label class='style_label'>$field[etiqueta]</label>";
        break;
        default:
            $input = "<input type='text' name='$prefijo_objeto"."$field[id_field]' id='$prefijo_objeto"."$field[id_field]' value='$field[value_data]' class='INPUT'>";
    }
    return $input;
}


function obtener_formularios($pDB,$id_campania)
{
    $sQuery = " SELECT f.id, f.nombre 
                FROM 
                    campaign_form cf 
                        inner join 
                    form f on cf.id_form = f.id
                WHERE cf.id_campaign=$id_campania";
    $result = $pDB->fetchTable($sQuery, true);
    //$pDB->disconnect();
    if (is_array($result) && count($result)>0)
        return $result;
    else 
        return false;
}

function obtener_primer_formulario($arr_values)
{
    if(is_array($arr_values) && count($arr_values)>0){
        foreach($arr_values as $key => $value){
            return $value['id'];
        }
    }
    return 0;
}
function smarty_option($arr_values,$selected)
{
    $options = array();
    if(is_array($arr_values) && count($arr_values)>0){
        foreach($arr_values as $key => $value){
            $options['VALUE'][] =  $value['id'];
            $options['NAME'][] = $value['nombre'];
            if($selected == $value['id'])
                $options['SELECTED'] = $value['id'];
        }         
    }
    return $options;
}

/* Otras funciones */
function getSmarty() {
    global $arrConf;
    $smarty = new Smarty();
    $smarty->template_dir = "themes/default/";
    $smarty->compile_dir =  "var/templates_c/";
    $smarty->config_dir =   "configs/";
    $smarty->cache_dir =    "var/cache/";
    return $smarty;
}
function getDB() {
    global $arrConf;
    //$sConnStr = "sqlite3:////var/www/db/$tabla";
    $pDB = new paloDB($arrConf["cadena_dsn"]);
    return $pDB;
}
function getDB1() {
    global $arrConf;
    //$sConnStr = "sqlite3:////var/www/db/$tabla";
    $pDB = new paloDB($arrConf["cadena_dsn"]."d");
    return $pDB;
}
function getDBAsterisk($arrConfig) {
    $dsn = $arrConfig['AMPDBENGINE']['valor'] . "://" . $arrConfig['AMPDBUSER']['valor'] . ":" . $arrConfig['AMPDBPASS']['valor'] . "@" . $arrConfig['AMPDBHOST']['valor'] . "/asterisk";
    $pDBa     = new paloDB($dsn);
    return $pDBa;
}


/* ---------------------------------------- */
/* FUNCIONES QUE ACTUALMENTE NO SE UTILIZAN */
/* ---------------------------------------- */

/*  FUNCION XAJAX:
    funcion que encola al agente a una cola en donde este no se encuentre 
*/
function encolar_agente($agente,$cola)
{
//     $cola = $_SESSION['elastix_queue_agent'];
    $member = "Agent/$agente";
    global $arrLang;
    $respuesta = new xajaxResponse();
    $astman = new AGI_AsteriskManager( );	

    if (!$astman->connect("127.0.0.1", 'admin' , 'elastix456')) {
        $resultado = $arrLang["Error when connecting to Asterisk Manager"];
    } 

    if(!estaAgenteEnCola($agente,$cola))
    {
         $salida = $astman->QueueAdd($cola,$member);
         if($salida['Response']=='Error')
            return $arrLang['Unable to add Agent to queue: No such queue'];
         else return 'se_encolo';
    }
    else{
        return 'ya_encolado';
    }
}

/* funcion que retorna true si un agente esta agregado a la cola */
function estaAgenteEnCola($numAgente,$cola) {
    global $arrLang;
    //$cola = $_SESSION['elastix_queue_agent'];
    //$hardware = "SIP|IAX|ZAP|H323|OH323";
    //global $tipo_equipos; echo $tipo_equipos;
    $astman = new AGI_AsteriskManager();	
    if (!$astman->connect("127.0.0.1", 'admin' , 'elastix456')) {
        $resultado = $arrLang["Error when connecting to Asterisk Manager"];
    } else {
        $strAgentShow = $astman->Command(" queue show $cola");
        $astman->disconnect();
        $arrAgentShow=array();
        if (is_array($strAgentShow))
            $arrAgentShow = split("\n", $strAgentShow['data']);

        foreach($arrAgentShow as $line) {
            if(ereg("[[:alnum:]]*/([[:digit:]]{2,})", $line, $arrReg1)) {
                // is la condicion es verdadera, quiere decir que el agente que llego como parametro ya esta conectado
                if($numAgente == $arrReg1[1]) {
                    return true;
                }
            }
        }
    }
    return false;
}
?>