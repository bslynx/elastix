<table width="{$width}" align="center" border="0" cellpadding="0" cellspacing="0">
    <tr class="moduleTitle">
        <td class="moduleTitle" colspan="2" valign="middle">&nbsp;&nbsp;<img src="{$icon}" border="0" align="absmiddle">&nbsp;&nbsp;{$title}</td>
    </tr>
    <tr class="filterForm">
        <td class="filterForm" valign="middle" width="30%"> 
            <input type='checkbox' name='chk_dahdi_replace' id='chk_dahdi_replace' />&nbsp; <b>{$CHAN_DAHDI_REPLACE}<b> &nbsp;&nbsp;&nbsp;&nbsp;<br />
            <input type='checkbox' name='chk_there_is_sangoma' id='chk_there_is_sangoma' disabled="disabled" />&nbsp; <b>{$DETECT_SANGOMA}<b> &nbsp;&nbsp;&nbsp;&nbsp;<br/>
            <input type='checkbox' name='chk_misdn_hardware' id='chk_misdn_hardware' />&nbsp; <b>{$DETECT_mISDN}<b>
        </td>
        <td class="filterForm" valign="middle"  >
            <input type='button' name='submit_harware_detect' value='{$HARDWARE_DETECT}'  onclick="detectar()" class='button' /> 
        </td>
    </tr>
    <tr>
        <td class="table_navigation_row" colspan="2" id='relojArena'>
        </td>
    </tr>
  <tr>
    <td  class="table_navigation_row" colspan="2">
      <table border ='0' align="left" cellspacing="0" cellpadding="0" >
        {foreach from=$arrData key=k item=data name=filas}
            {if $data.DESC.TIPO ne $CARD_NO_MOSTRAR}
                <tr>
                    <td style='border:1px #CCCCCC solid; font-size:12px;' align='left' class="moduleTitle">{$CARD} # {$data.DESC.ID}: {$data.DESC.TIPO} {$data.DESC.ADICIONAL}</td>
                </tr>
                <tr> 
                    <td>
                    <table border ='0' align="center" cellspacing="0" cellpadding="0" class="table_title_row" width='100%'>
                        {if $data.PUERTOS}
                            {counter start=0 skip=1 print=false assign=cnt}
                                {foreach from=$data.PUERTOS key=q item=puerto name=filasPuerto}
                                    {if $cnt%12==0}
                                        <tr>
                                    {/if}
                                            <td>
                                                <table style='border:1px #CCCCCC solid;padding:1px;background-color:white' border='0' callpadding='0' cellspacing='0' onMouseOver="this.style.backgroundColor='#f2f2f2';" onMouseOut="this.style.backgroundColor='#ffffff';" width='100%'>
                                                    <tr><td  align='center' style='font-size:10px;background-color:{$puerto.COLOR};'>{$puerto.LOCALIDAD} {$puerto.TIPO}</td></tr>                           
                                                    <tr><td  align='center' style='font-size:10px;background-color:{$puerto.COLOR};'>{$puerto.ESTADO}</td></tr>
                                                </table>
                                            </td>
                                    {if ($cnt+1)%12==0}
                                        </tr>
                                    {/if}
                                    {counter}
                                {/foreach}
                        {else}
                            <tr>
                                <td style='border:1px #CCCCCC solid;padding:1px;background-color:white'>{$PORT_NOT_FOUND}</td>
                            </tr>
                        {/if}
                    </table>
                    </td>
                </tr>
            {/if}
            <tr>
                <td height='8'></td>
            </tr>
        {/foreach} 
      </table>
    </td>
  </tr>
  <tr>
    <td class="table_navigation_row" colspan="2">
      {if $arrMisdn != "noMISDN"}
      <table border ='0' align="left" cellspacing="0" cellpadding="0" >
        <tr>
          <td style='border:1px #CCCCCC solid' align='center' class="moduleTitle">{$CARD_MISDN}</td>
        </tr>
        <tr> 
          <td>
            <table border ='0' align="center" cellspacing="0" cellpadding="0" class="table_title_row" width='100%'>
            <tr>
                 <td style='border:1px #CCCCCC solid;padding:1px;background-color:white;font-size:10;'>{foreach from=$arrMisdn item=info}{$info}<br/>{/foreach}
                 </td>
            </tr>
            </table>
         </td>
        </tr>
      </table>
      {/if}
    </td>
  </tr>
</table>
<center><h3 style='color:#990033;font-size:14px'>{$CARDS_NOT_FOUNDS}</h3></center>
<form id='form_dectect' style='margin-botom:0px;padding:0px' method='POST' action='?menu={$MODULE_NAME}'>
    <input type='hidden' id='estaus_reloj' value='apagado' />
</form>
{literal}
<script type='text/javascript'>
    function detectar()
    {
        var nodoReloj = document.getElementById('relojArena');
        var estatus   = document.getElementById('estaus_reloj');
        var chk_dahdi_replace   = document.getElementById('chk_dahdi_replace');
	    var chk_there_is_sangoma = document.getElementById('chk_there_is_sangoma');
        var chk_misdn_hardware = document.getElementById('chk_misdn_hardware');

        if(estatus.value=='apagado'){
            estatus.value='prendido';
            nodoReloj.innerHTML = "<img src='images/hourglass.gif' align='absmiddle' /> <br /> <font style='font-size:12px; color:red'>{/literal}{$detectandoHardware}{literal}...</font>";
            xajax_hardwareDetect(chk_dahdi_replace.checked,chk_there_is_sangoma.checked,chk_misdn_hardware.checked);
        }
        else alert("{/literal}{$accionEnProceso}{literal}");
    } 
</script>
{/literal}
