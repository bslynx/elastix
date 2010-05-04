<link rel="stylesheet" type="text/css" media="all" href="libs/js/jscalendar/calendar-win2k-2.css" />
<script type="text/javascript" src="libs/js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="libs/js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="libs/js/jscalendar/calendar-setup.js"></script>
<script src="modules/{$MODULE_NAME}/libs/js/base.js"></script>
<script type="text/javascript" >
	// La variable activar_unload se define en modules/{$MODULE_NAME}/libs/js/base.js
	activar_unload = true; 
</script>
<link rel="stylesheet" href="modules/{$MODULE_NAME}/themes/styles.css">
{literal}
<style type='text/css'>
.boton_normal{
    width:85px;
    height:65px;
    font-size:16px;
    font-weight:bold;
/*     background-color:gray; */
}
/* Boton Break */
.boton_unbreak{
    width:85px;
    height:65px;
    font-size:16px;
    font-weight:bold;
    background-color:#BD0000;
    color: #FFFFFF;
}
.boton_break{
    width:85px;
    height:65px;
    font-size:16px;
    font-weight:bold;
    background-color:#094895;   
    color: #FFFFFF;
}
input.boton_unbreak:focus{
    background-color:#BD0000;
    border-color:#BD0000;
}
input.boton_break:focus{
    background-color:#094895;
    border-color:#094895;
}
/* Fin Boton Break */
/* Boton Hangup */
.boton_activo{
    width:85px;
    height:65px;
    font-size:16px;
    font-weight:bold;
    background-color:#06640D;
    color: #FFFFFF;
}
.boton_desactivo{
    width:85px;
    height:65px;
    font-size:16px;
    font-weight:bold;
    background-color:#D2C0CF;
    color:#FFFFFF;
}
input.boton_activo:focus{
    background-color:#06640D;
    border-color:#06640D;
}
input.boton_desactivo:focus{
     background-color:#D2C0CF;
    border-color:#D2C0CF;
}
/* Fin Boton Hangup */
/* Boton Tranfer */
.boton_tranfer_activo{
    width:85px;
    height:65px;
    font-size:16px;
    font-weight:bold;
    background-color:#094895;
    color: #FFFFFF;
}
input.boton_tranfer_activo:focus{
    background-color:#094895;
    border-color:#094895;
}

.boton_tranfer_inactivo{
    width:85px;
    height:65px;
    font-size:16px;
    font-weight:bold;
    background-color:#D2C0CF;
    color:#FFFFFF;
}

input.boton_tranfer_inactivo:focus{
    background-color:#D2C0CF;
    border-color:#094895;
}

/* Fin Boton Tranfer */
#div_list{
    position:absolute;
    top:292px;
    left:105px;
    float:left;
    width:120px;
    height:105px;
    text-align:center;
    background-color:#094895;   
    font-size:16px;
    font-weight:bold;
    color: #FFFFFF;
    border:1px black solid;
}
/*inicio transferencia llamada*/
#div_transfer_list{
    position:absolute;
    top:500px; /*460px;*/
    left:105px;
    float:left;
    width:150px;
    height:105px;
    text-align:center;
    background-color:#094895;   
    font-size:16px;
    font-weight:bold;
    color: #FFFFFF;
    border:1px black solid;
}
/*fin transferencia llamada*/
/* inicio hold llamada */
.boton_hold{
    width:85px;
    height:65px;
    font-size:16px;
    font-weight:bold;
    background-color:blue;
}
.boton_unhold{
    width:85px;
    height:65px;
    font-size:16px;
    font-weight:bold;
    background-color:red;
}
/* fin hold llamada */
/* inicio Marcar llamada*/

#div_marcar_list {
    position:absolute;
    top:400px; /*460px;*/
    left:105px;
    float:left;
    width:150px;
    height:105px;
    text-align:center;
    background-color:#094895;   
    font-size:16px;
    font-weight:bold;
    color: #FFFFFF;
    border:1px black solid;
}


.boton_marcar_activo{
    width:85px;
    height:65px;
    font-size:16px;
    font-weight:bold;
    background-color:#094895;
    color: #FFFFFF;
}

input.boton_marcar_activo:focus{
    background-color:#094895;
    border-color:#094895;
}

.boton_marcar_inactivo{
    width:85px;
    height:65px;
    font-size:16px;
    font-weight:bold;
    background-color:#D2C0CF;
    color:#FFFFFF;
}

input.boton_marcar_inactivo:focus{
    background-color:#D2C0CF;
    border-color:#094895;
}
/* fin Marcar llamada*/


.style_label{
    text-align:center;
    font-size:16px;
    font-weight:bold;
    text-decoration:underline;
}
.classCronometro{
    font-size:24px;
    font-weight:bold;
    padding:0px 0px 0px 0px;
    margin:0px 0px 0px 0px;
    color: #FFFFFF;
}
.normal {
    font-family: verdana, arial, helvetica, sans-serif;
    font-size: 15px;
    color: #000000;
    font-weight: bold;
    text-decoration: underline;
}
a.normal:link, a.normal:visited {
    text-decoration: underline;
    color: #444444;
}
</style>
{/literal}
{$SCRIPT_AJAX}
<form method="POST" name="frm_agent_console" id="frm_agent_console">
<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr class="moduleTitle">
  <td class="moduleTitle" valign="middle">&nbsp;&nbsp;<img src="images/call.png" border="0" align="absmiddle" >&nbsp;&nbsp;{$title}</td>
</tr>
<tr>
  <td valign="middle" class="mb_message">{$ERROR_DB}<div id="mensajes_informacion"></div></td>
</tr>
<tr>
  <td>
    <table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td width="100%">
            <table width="100%" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td width='85%' class="fondo_estatus_no_llamada" id="celda_estatus_actual"><div id="estatus_actual"></div></td>
                    <td width='15%' align='center' class='classCronometro' id='celda_cronometro'><label name='cronometro' id='cronometro' border='0'>00:00:00</label></td>
                </tr>
            </table>
        </td>
      </tr>
      <tr>
        <td width="100%">&nbsp;</td>
      </tr>
      <tr>
        <td width="100%">
          <table cellSpacing="0" cellPadding="0" width="100%" border="0">
            <tr>
              <td colspan='4'>
<!--            INICIO: ESTA ES LA TABLA QUE CONTIENE EL CONTENIDO PRINCIPAL DE LA PANTALLA      -->
                <TABLE border="0" cellpadding="0" cellspacing="0" width="100%">
                  <tr>
                    <td width="150" valign="top">
<!--                  INICIO: TABLA QUE CONTIENE LOS BOTONES QUE ESTAN AL LADO IZQUIERDO -->
                      <table cellpadding="2" cellspacing="0" height="100%" border="0">
                        <tr>
                          <td valign="top" height="400">
                            <table>
<!--                            COLGAR      -->
                                <tr>
                                    <td><input class='boton_desactivo' type='button' value='{$HANGUP}' id='hangup' onClick='colgar(); size="35"'></td>
                                </tr>
<!--                            BREAK      -->
                                <tr>
                                    <td>
                                        <input class='{$STYLE_PAUSE}' type='button' value='{$PAUSE}' id='pause' size="35" onClick="{literal} if(this.className=='boton_unbreak'){ pausar_llamadas(null); } else { mostrar_lista(); } {/literal}">
                                        <table border='0' cellpadding='0' cellspacing='0' id='div_list' style='display:none'>
                                                <tr>
                                                    <td><select name="input_break" id="input_break" style='width:100px'>{html_options options=$ALL_BREAK selected=$BREAK_SELECTED}</select><td>
                                                </tr>
                                                <tr>
                                                    <td><input type='button' name='tomar_break' value='{$TOMAR_BREAK}' onclick='pausar_llamadas(input_break.value)'/><td>
                                                </tr>
                                                <tr>
                                                    <td><input type='button' name='ocultar_breaks' value='{$CANCEL}' onclick="document.getElementById('div_list').style.display ='none';"/></td>
                                                </tr>
                                            </table>

                                        </div> 
                                    </td>
                                </tr>
<!--                            ESPERA: COMENTADA PORQUE AÚN NO FUNCIONA CORRECTAMENTE       -->
<!--                                <tr>
                                    <td>
                                        <input class='{$STYLE_HOLD}' type='button' value='{$LABEL_HOLD}' name='hold' id='hold' size="35" onClick="xajax_hold();">
                                    </td>
                                </tr>
-->

                                <!--  Marcado de llamadas     ************************************************************************* !-->
<!--                                <tr>
                                    <td>
                                        <input class='boton_marcar_activo' type='button' value='{$MARCAR}' id='marcar2' size="40" 
                                        onClick=" {literal} mostrar_teclado(); {/literal} "  $DESHABILITAR_MARCADO >
                                
                                        <table border='0' cellpadding='0' cellspacing='0' id='div_marcar_list' style='display:none'>
                                            <tr>
                                                <td><input type='text' id='txtMarcar' name='txtMarcar'></td>
                                            </tr>
                                            <tr>
                                                <td align='Center'>
                                                    <table >
                                                        <tr>
                                                            <td><input type='button' id='btn_1' name='btn_1' value='1' 
                                                            onClick='{literal} capturarValor(this); {/literal}'></td>
                                                            <td><input type='button' id='btn_2' name='btn_2' value='2' 
                                                            onClick='{literal} capturarValor(this); {/literal}'></td>
                                                            <td><input type='button' id='btn_3' name='btn_3' value='3' 
                                                            onClick='{literal} capturarValor(this); {/literal}'></td>
                                                        </tr>
                                                        <tr>
                                                            <td><input type='button' id='btn_4' name='btn_4' value='4' 
                                                            onClick='{literal} capturarValor(this); {/literal}'></td>
                                                            <td><input type='button' id='btn_5' name='btn_5' value='5' 
                                                            onClick='{literal} capturarValor(this); {/literal}'></td>
                                                            <td><input type='button' id='btn_6' name='btn_6' value='6' 
                                                            onClick='{literal} capturarValor(this); {/literal}'></td>
                                                        </tr>
                                                        <tr>
                                                            <td><input type='button' id='btn_7' name='btn_7' value='7' 
                                                            onClick='{literal} capturarValor(this); {/literal}'></td>
                                                            <td><input type='button' id='btn_8' name='btn_8' value='8' 
                                                            onClick='{literal} capturarValor(this); {/literal}'></td>
                                                            <td><input type='button' id='btn_9' name='btn_9' value='9' 
                                                            onClick='{literal} capturarValor(this); {/literal}'></td>
                                                        </tr>
                                                        <tr>
                                                            <td><input type='button' id='btn_aterisco' name='btn_aterisco' value='*' 
                                                            onClick='{literal} capturarValor(this); {/literal}'></td>
                                                            <td><input type='button' id='btn_0' name='btn_0' value='0' 
                                                            onClick='{literal} capturarValor(this); {/literal}'></td>
                                                            <td><input type='button' id='btn_numeral' name='btn_numeral' value='#' 
                                                            onClick='{literal} capturarValor(this); {/literal}'></td>
                                                        </tr> 
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align='Center'><input type='button' id='btn_marcar' name='btn_marcar' value='{$BTN_MARCAR}' 
                                                onClick='{literal} marcarLLamada(); {/literal}' ></td>
                                            </tr>
                                            <tr>
                                                <td align='Center'><input type='button' id='btn_cancelar' name='btn_cancelar' value='{$BTN_CANCELAR}' 
                                                onClick="{literal} cancelarMarcado(); {/literal}" ></td>
                                            </tr>
                                        </table>
                                        
                                    </td>
                                </tr>
-->
                                <!-- Fin Marcar de llamadas     ************************************************************************* !-->

<!--                            TRANSFER      -->
                                <tr>
                                    <td>
                                        <input class='boton_tranfer_activo' type='button' value='{$TRANFER}' id='transfer' size="35" onClick="{literal}  mostrar_lista_transferencia();  {/literal}" {$DESHABILITAR_TRANSFER}>

                                            <table border='0' cellpadding='0' cellspacing='0' id='div_transfer_list' style='display:none'>
                                                <tr>
                                                    <td>
                                                        <select name="input_transfer" id="input_transfer" >
                                                            {$opcion_select_extension}
                                                        </select>
                                                    <td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input id='input_select_tipo_transferencia' type='hidden' value='ciega'>
                                                    </td>  
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type='button' id='id_hacer_llamada' name='hacer_llamada' value='{$LLAMAR}' onclick='{literal} transferirLlamadaCiega(input_transfer.value); {/literal}'/>
                                                    <td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type='button' name='cancelar_llamada' value='{$CANCEL}' onclick="document.getElementById('div_transfer_list').style.display ='none';"/>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div> 
                                    </td> 
                                </tr>
<!--                            VTIGERCRM      -->
                                <tr>
                                    <td><input class='boton_normal' type='button' name='vtigercrm' id='vtigercrm' value='Vtiger' onClick='window_open("/vtigercrm/","vtigercrm")' size="35"></td>
                                </tr>
<!--                            LOGOUT      -->
                                <tr>
                                    <td><input class='boton_normal' type='submit' name='logout_agent' id='logout_agent' value='{$logout}' size="35"></td>
                                </tr>
                                <tr>
                                    <td><div id='respuesta_evento'></div></td>
                                </tr>
                            </table>
                          </td>
                        </tr>
                      </table>
<!--                  FIN: TABLA QUE CONTIENE LOS BOTONES QUE ESTAN AL LADO IZQUIERDO -->
                    </td>
                    <td width="100%"  valign="top">
<!--                  INICIO: TABLA QUE CONTIENE EL TABULADOR -->
                      <table cellpadding="0" cellspacing="0" width="100%" height="100%" border="0">
                        <tr>
                          <td class="headlinkon" valign="bottom" id='TAB_PESTANIA_LLAMADA' width="60">
                            <table border="0" cellpadding="2" cellspacing="0" height="29" width="100%">
                            <tr>
                                <td class="menutabletaboff" title="" nowrap="nowrap">
                                <a class="menutable" href="javascript:activar('LLAMADA');">{$llamada}</a></td>
                            </tr>
                            </table>
                          </td>
                          <td class="headlink" valign="bottom" id='TAB_PESTANIA_SCRIPT' width="60">
                            <table border="0" cellpadding="2" cellspacing="0" height="29" width="100%">
                            <tr>
                                <td class="menutabletaboff" title="" nowrap="nowrap">
                                <a class="menutable" href="javascript:activar('SCRIPT');">{$script}</a></td>
                            </tr>
                            </table>
                          </td>
                          <td class="headlink" valign="bottom" id='TAB_PESTANIA_FORMULARIO' width="60">
                            <table border="0" cellpadding="2" cellspacing="0" height="29" width="100%">
                            <tr>
                                <td class="menutabletaboff" title="" nowrap="nowrap">
                                <a class="menutable" href="javascript:activar('FORMULARIO');">{$formulario}</a></td>
                            </tr>
                            </table>
                          </td>
                          <td class="headlink" valign="bottom"><input type='hidden' id='pestania' value='LLAMADA'><input type='hidden' id='control' readOnly size="3" value='1'>&nbsp;</td>
                          <td class="headlink" valign="bottom" align="right">
                            <table border="0" cellpadding="2" cellspacing="0" height="29" width="100%">
                                <tr>
                                    <td align="right" class="titulorojo" width='75%'><b>{$name_agent}</b></td>
                                    <td align="right" class="titulorojo" width='25%'><b>{$number_agent}</b></td>
                                </tr>
                            </table>
                          </td>
                        </tr>
                        <tr>
                          <td valign="top" height="400" colspan="5">
                            <table cellpadding="2" cellspacing="0" width="100%" height="100%" style="BORDER-LEFT: #555555 1px solid; BORDER-RIGHT: #555555 1px solid; BORDER-BOTTOM: #555555 1px solid;">
<!--                              <tr>
                                <td><div id="contenedor"></div></td>
                              </tr>-->
                              <tr>
                                <td valign="top">
<!--                              TABLA NUMERO TELEFONICO    -->
                                    <table cellpadding="2" cellspacing="0" width="100%" height='25' border="0">
                                    <tr>
                                        <td valign='top' width='80%'><div id="numero_telefono"></div></td>
                                        <td valign='bottom' id='link_crm'>&nbsp;</td>
                                    </tr>
                                    </table><hr>
<!--                              TABLA NUMERO TELEFONICO    -->
<!--                              TABLA LLAMADA    -->
                                    <table cellpadding="2" cellspacing="0" width="100%" height='400' border="1" id="TABLA_LLAMADA">
                                    <tr>
                                        <td valign='top'><div id="contenedor_llamada">{$DATOS_LLAMADA}</div></td>
                                    </tr>
                                    </table>
<!--                              TABLA LLAMADA    -->
<!--                              TABLA SCRIPT    -->
                                    <table cellpadding="2" cellspacing="0" width="100%" height='400' border="1" id="TABLA_SCRIPT">
                                    <tr>
                                        <td valign='top'><div id="contenedor_script">{$DATOS_SCRIPT}</div></td>
                                    </tr>
                                    </table> 
<!--                              TABLA SCRIPT    -->
<!--                              TABLA FORMULARIO    -->
                                    <table width="100%" border="1" class="tabForm" height="400" id="TABLA_FORMULARIO">
                                    <tr>
                                        <td valign='top'><div id="contenedor_formulario">&nbsp;</div></td>
                                    </tr>
                                    </table>
<!--                              TABLA FORMULARIO    -->
                                </td>
                              </tr>
                          </td>
                        </tr>
                      </table>
<!--                  FIN: TABLA QUE CONTIENE EL TABULADOR -->
                    </td>
                  </tr>
                </TABLE>
<!--            FIN: ESTA ES LA TABLA QUE CONTIENE EL CONTENIDO PRINCIPAL DE LA PANTALLA      -->
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </td>
</tr>
</table>
<input type="hidden" name="prefijo_objeto" id="prefijo_objeto" value="{$prefijo_objeto}">
<input type="hidden" name="nueva_llamada" id="nueva_llamada" value="">
<input type="hidden" name="nuevo_script" id="nuevo_script" value="">
<input type="hidden" name="nuevo_form" id="nuevo_form" value="">
<input type="hidden" name="accion_anterior" id="accion_anterior" value="{$accion_anterior}">
<input type="hidden" name="tipo_llamada" id="tipo_llamada" value="{$tipo_llamada}">
<div id="prueba"></div>
</form>
<!-- <input type="text" id="prueba_sql" size="100"> -->
{literal}
<script>
document.getElementById("TABLA_LLAMADA").style.display="";
document.getElementById("TABLA_SCRIPT").style.display="none";
document.getElementById("TABLA_FORMULARIO").style.display="none";
refrescar();
</script>
{/literal}
