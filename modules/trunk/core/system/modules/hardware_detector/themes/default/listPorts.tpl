

<table width="{$width}" align="center" border="0" cellpadding="0" cellspacing="0">
    <tr class="moduleTitle">
        <td class="moduleTitle" colspan="2" valign="middle">&nbsp;&nbsp;<img src="{$icon}" border="0" align="absmiddle">&nbsp;&nbsp;{$title}</td>
    </tr>
    <tr class="filterForm">
        <td class="filterForm" valign="middle" width="30%"> 
            <input type='checkbox' name='chk_dahdi_replace' id='chk_dahdi_replace' />&nbsp; <b>{$CHAN_DAHDI_REPLACE}<b> &nbsp;&nbsp;&nbsp;&nbsp;<br />
            <input type='checkbox' name='chk_there_is_sangoma' id='chk_there_is_sangoma' />&nbsp; <b>{$DETECT_SANGOMA}<b> &nbsp;&nbsp;&nbsp;&nbsp;<br/>
            <input type='checkbox' name='chk_misdn_hardware' id='chk_misdn_hardware' />&nbsp; <b>{$DETECT_mISDN}<b>
        </td>
        <td class="filterForm" valign="middle">
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
                    <td style='border: 1px #CCCCCC solid; font-size:12px;' align='left' class="moduleTitle">{$CARD} # {$data.DESC.ID}: {$data.DESC.TIPO} {$data.DESC.ADICIONAL} </td>
<!--
                    {if $data.DESC.MANUFACTURER eq 'yes'}
                    <td style='border: 1px #CCCCCC solid; width:30px;' align="center"> <span id="editMan{$data.DESC.ID}"> <img class="icon" src="modules/hardware_detector/images/card_registered.gif" /> </span> </td>
                    {elseif $data.DESC.MANUFACTURER  eq 'no'}
                    <td style='border: 1px #CCCCCC solid; width:30px;' align="center"> <span id="editMan{$data.DESC.ID}"> <img class="icon" src="modules/hardware_detector/images/card_no_registered.gif" /> </span> </td>
                    {/if}
-->
                    <td style='border: 1px #CCCCCC solid; width:30px;'> <span id="editArea{$data.DESC.ID}"> <img class="icon" src="modules/hardware_detector/images/icon1.png" /> </span> </td>
                    <td style='border: 1px #CCCCCC solid; width:30px;'><a href='?menu=hardware_detector&action=config_echo&cardId={$data.DESC.ID}'><img class="icon" src="modules/hardware_detector/images/icon_configecho.png" title="Config echo Canceller"/></a></td>
                </tr>
                <tr> 
                    <td colspan="4">
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

{counter start=1 skip=1 print=false assign=cnt}
{foreach from=$arrSpanConf key=k item=data name=filas}
<div id="layer{$cnt}" class="move">
    <div class="layer_handle">
        <a href="#" id="close{$cnt}">[ x ]</a>
        Preferences
    </div>
    <div id="layer{$cnt}_content">
        <form id="layer{$cnt}_form" method="post" action="">
            <legend >Span Settings</legend><br />
            <table>
                <tr>
                    <td><label style='font-size: 11px'>Timing source:</label></td>
                    <td><select id='tmsource_{$cnt}' name='tmsource_{$cnt}'>
                    {html_options options=$type_timing_source selected=$data.tmsource}
                    </select></td>
                </tr>
                <tr>
                    <td><label style='font-size: 11px'>Line build out:</label></td>
                    <td><select id='lnbuildout_{$cnt}' name='lnbuildout_{$cnt}'>
                    {html_options options=$type_lnbuildout selected=$data.lnbuildout}
                    </select></td>
                </tr>
                <tr>
                    <td><label style='font-size: 11px'>Framing:</label></td>
                    <td><select id='framing_{$cnt}' name='framing_{$cnt}'>
                    {html_options options=$type_framing selected=$data.framing}
                    </select></td>
                </tr>
                <tr>
                    <td><label style='font-size: 11px'>Coding:</label></td>
                    <td><select id='coding_{$cnt}' name='coding_{$cnt}'>
                    {html_options options=$type_coding selected=$data.coding}
                    </select></td>
                </tr>
                <input type="hidden" value="{$cnt}" name="idSpan_{$cnt}" />
                
                <tr>
                    <td></td>
                    <br>
                    <td align="right">
                        <input type="button" value="Save" onclick="saveSpanConfiguration({$cnt});" class="boton"/>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>
{counter}
{/foreach}

{counter start=1 skip=1 print=false assign=cnt2}
{foreach from=$arrCardManufacturer key=k2 item=data2 name=filas}
<div id="layerCM{$cnt2}" class="move">
    <div class="layer_handle">
        <a href="#" id="closeCM{$cnt2}">[ x ]</a>
        Card Register
    </div>
    <div id="layerCM{$cnt2}_content">
        <form id="layerCM{$cnt2}_form" method="post" action="">
            {if $data2.num_serie eq ' '}
                <font style="color:red">Card has not been Registered</font><br />
                <table>
                    <tr>
                        <td><label style='font-size: 11px'>Vendor:</label></td>
                        <td id="select_{$cnt2}" ><select id='manufacturer_{$cnt2}' name='manufacturer_{$cnt2}' onChange="addTextBox({$cnt2})">
                        {html_options options=$type_manufacturer selected=$data2.manufacturer }
                        </select></td>
                    </tr>
                    <tr>
                        <td><label style='font-size: 11px'>Serial Number:</label></td>
                        <td><input type="text" value="{$data2.num_serie}" name="noSerie_{$cnt2}" id="noSerie_{$cnt2}" /></td>
                    </tr>
                    <input type="hidden" value="{$cnt2}" name="idCard_{$cnt2}" />
                    <tr>
                        <td></td>
                        <br>
                        <td align="right">
                            <input type="button" value="Save" onclick="saveCardSpecification({$cnt2});" class="boton"/>
                        </td>
                    </tr>
                </table>
            {else}
                <font style="color:#32CD32" >Card has been Registered</font><br />
                <table>
                    <tr>
                        <td><label style='font-size: 11px'>Vendor:  {$data2.manufacturer}</label></td>
                    </tr>
                    <tr>
                        <td><label style='font-size: 11px'>Serial Number:  {$data2.num_serie}</label></td>
                    </tr>
                </table>
            {/if}
        </form>
    </div>
</div>
{counter}
{/foreach}  
