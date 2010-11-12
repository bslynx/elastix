{literal}
<script type="text/javascript">


 function inhabilitar(){
    ctrl = document.getElementById("form_programar").new_accion;
     if(ctrl[0].checked){
         document.getElementById("form_programar").txt_fecha_init.disabled=false;
         document.getElementById("form_programar").txt_fecha_end.disabled=false;
         document.getElementById("form_programar").hora_ini_HH.disabled=false;
         document.getElementById("form_programar").hora_ini_MM.disabled=false;
         document.getElementById("form_programar").hora_fin_HH.disabled=false;
         document.getElementById("form_programar").hora_fin_MM.disabled=false;
 
     }else{
         document.getElementById("form_programar").txt_fecha_init.disabled=true;
         document.getElementById("form_programar").txt_fecha_end.disabled=true;
         document.getElementById("form_programar").hora_ini_HH.disabled=true;
         document.getElementById("form_programar").hora_ini_MM.disabled=true;
         document.getElementById("form_programar").hora_fin_HH.disabled=true;
         document.getElementById("form_programar").hora_fin_MM.disabled=true;
     }
}


</script>
{/literal}

<html>
    <head>
        <title>Elastix</title>
	<link rel="stylesheet" href="{$path}themes/{$THEMENAME}/styles.css">
	<link rel="stylesheet" href="{$path}themes/{$THEMENAME}/help.css">
        <link rel="stylesheet" href="modules/{$MODULE_NAME}/themes/styles.css">
        <link rel='stylesheet' type='text/css' media='all' href='/libs/js/jscalendar/calendar-win2k-2.css' />
        <script type='text/javascript' src='/libs/js/jscalendar/calendar_stripped.js'></script>
        <script type='text/javascript' src='/libs/js/jscalendar/lang/calendar-en.js'></script>
        <script type='text/javascript' src='/libs/js/jscalendar/calendar-setup_stripped.js'></script>
    </head>
    <body>


    <!--<tr class="letra12">-->
        <form name="form_programar" id="form_programar" method='POST' style='margin-bottom:0;' action='?num_telefono={$num_telefono_hidden}&id_call={$id_call_hidden}&id_campana={$id_campana_hidden}'>



        <table width="99%" border="0" cellspacing="0" cellpadding="0"  class="tabForm">

        <!--mensaje de error-->
          <tr>
            {$mb_message}
          </tr>

          <tr>
            <td width="10%" align="left" >{$numero.LABEL}: </td>
            <td align='left' width='15'>
		    {$numero.INPUT}
            </td>

          </tr>


<!--PaloSanto- Agregado para guardar el nombre de la persona  a la que vamos a llamar-->
    <tr>
            <td width="10%" align="left" >{$cliente.LABEL}: </td>
            <td align='left' width='15'>
		    {$cliente.INPUT}
            </td>

    </tr>

<!--Fin PaloSanto- Agregado para guardar el nombre de la persona  a la que vamos a llamar-->

<!--radio Programar llamadas-->
          <tr>
            <td class="letra12" width='60' colspan="2">
                    <input type="radio" name="new_accion" id="new_accion" value="radio_programar" checked onClick="inhabilitar();"  >
                    {$label_programar}:
            </td>
          </tr>
<!--fin radio Programar llamadas-->

<!--fechas-->
          <tr>
                <td class='letra12' width='15%'>{$txt_fecha_init.LABEL}<span  class='required'>*</span></td>
                <td  width='30%'>{$txt_fecha_init.INPUT}</td>
                <td class='letra12' width='15%'>{$txt_fecha_end.LABEL}<span  class='required'>*</span></td>
                <td width='30%'>{$txt_fecha_end.INPUT}</td>
          </tr>
<!--fin fechas-->

<!--hora-->
            <tr height='30'>
                <td align='left' colspan='2'>{$hora_ini_HH.INPUT}&nbsp;:&nbsp;{$hora_ini_MM.INPUT}&nbsp;{$hora_ini_HH.LABEL}</td>
            
                <td align='left' colspan='2'>{$hora_fin_HH.INPUT}&nbsp;:&nbsp;{$hora_fin_MM.INPUT}&nbsp;{$hora_fin_HH.LABEL}</td>
            </tr>
<!--fin hora-->


<!--radio Llamar final de la campaña-->
          <tr>
            <td class="letra12" width='60' colspan="2">
                <input type="radio" name="new_accion" id="new_accion" value="radio_llamar" onClick="inhabilitar();"  >
                {$label_llamar_final}:
            </td>
          </tr>
<!--fin radio Llamar final de la campaña-->




    </table>


    <table width="99%" border="0" cellspacing="0" cellpadding="0"  class="tabForm">
          <tr height='30'>
                <!--boton agregar-->
                <td>
                    <input class="button" type="submit" name="agregar" value="{$AGREGAR}">
                </td>

                <!--boton agregar otro-->
                <td>
                    <input class="button" type="submit" name="agregar_otro" value="{$AGREGAROTRO}">
                </td>

                <!--boton cancel-->
                <td>
                    <input class="button" type="button" name="cancel" value="{$CANCEL}" onclick="window.close();">
                </td>
          </tr>
    </table>


<!--campos call, campaña y numero de telefono OCULTOS-->
    <input type="hidden" name="id_call_hidden" value="{$id_call_hidden}" />
    <input type="hidden" name="id_campana_hidden" value="{$id_campana_hidden}" />
    <input type="hidden" name="num_telefono_hidden" value="{$num_telefono_hidden}" />
    <input type="hidden" name="cliente_hidden" value="{$cliente_hidden}" />

</form>


    </body>
</html>