<table width="{$width}" align="center" border="0" cellpadding="0" cellspacing="0">
    <tr class="moduleTitle">
        <td class="moduleTitle" colspan="3" valign="middle">&nbsp;&nbsp;<img src="{$icon}" border="0" align="absmiddle">&nbsp;&nbsp;{$title}</td>
    </tr>
    <tr>
        <td colspan="3" style="padding: 10px 10px 5px 10px;">
            <input type='button' name='submit_harware_detect' value='{$HARDWARE_DETECT}'  onclick="detectar()" class='button' />
        </td>
    </tr>
    <tr class="filterForm">
        <td class="filterForm" width="350px" valign="top" align="left" style="padding: 2px 2px 2px 30px;">
            <table border="0">
                <tr>
                    <td><input type="checkbox" name="chkAdvance" id="chkAdvance" />&nbsp; <b>Advanced</b></td>
                </tr>
                <tr>
                    <td class="filterForm2 secAdv" id="secAdvance">
                        <div id="optionsAdvance" style="visibility: hidden;">
                            <input type='checkbox' name='chk_dahdi_replace' id='chk_dahdi_replace' />&nbsp; <b>{$CHAN_DAHDI_REPLACE}</b> &nbsp;&nbsp;&nbsp;&nbsp;<br />
                            <input type='checkbox' name='chk_there_is_sangoma' id='chk_there_is_sangoma' />&nbsp; <b>{$DETECT_SANGOMA}</b> &nbsp;&nbsp;&nbsp;&nbsp;<br/>
                            {if $isInstalled_mISDN == 0 }
                                <input type='checkbox' name='chk_misdn_hardware' id='chk_misdn_hardware'  disabled='disabled' />&nbsp; {$DETECT_mISDN} <font color="#0043EC">({$MSG_isInstalled_mISDN})</font>
                            {else}
                                <input type='checkbox' name='chk_misdn_hardware' id='chk_misdn_hardware'  />&nbsp; <b>{$DETECT_mISDN}</b> <font color="#00CC00">({$MSG_isInstalled_mISDN})</font>
                            {/if}
                        </div>
                    </td>
                </tr>
            </table>
        </td>
        <td class="filterForm" align="center">
            <div class='hourglass' style="display:none" id='relojArena'>
                <img src='images/hourglass.gif' align='absmiddle' /> 
                <font style='font-size:12px; color:red'>{$detectandoHardware}...</font>
            </div>
        </td>
        <td class="filterForm" valign="middle" width="300px" align="right">
            <fieldset class="fielform">
                <legend class="sombreado">{$Status_ports}</legend>
                <table border="0" align="left">
                    <tr>
                        <td><img src='modules/{$MODULE_NAME}/images/conn_alarm_icon.png' align='absmiddle' /></td>
                        <td>{$Channel_detected_notused}</td>
                    </tr>
                    <tr>
                        <td><img src='modules/{$MODULE_NAME}/images/conn_ok_icon.png' align='absmiddle' /></td>
                        <td>{$Channel_detected_use}</td>
                    </tr>
                    <tr>
                        <td><img src='modules/{$MODULE_NAME}/images/conn_unkown_icon.png' align='absmiddle' /></td>
                        <td>{$Undetected_Channel}</td>
                    </tr>
                </table>
            </fieldset>
        </td>
    </tr>
  <tr>
    <td colspan="3">
<!-- cards -->
      <table border ='0' align="left" cellspacing="0" cellpadding="0" width="100%">
        {foreach from=$arrData key=k item=data name=filas}
            {if $data.DESC.TIPO ne $CARD_NO_MOSTRAR}
                <tr>
                    <td>
                        <fieldset class="fielformSpam">
                            <legend class="sombreado">{$CARD} # {$data.DESC.ID}: {$data.DESC.TIPO} {$data.DESC.ADICIONAL} </legend>
                            <table border ='0' align="left" cellspacing="0" cellpadding="3" width="100%">
                                <tr>
                                    <!--<td class="textConf" width="25%" style="background-color:#EFEFEF">
                                        <div valign="middle">
                                            <div>{$SET_PARAMETERS_PORTS}.</div>
                                            <div align="center"><a id="confSPAN{$data.DESC.ID}" class="confSPAN">Configurar</a></div>
                                        </div>
                                    </td>-->
                                    <td width="5px"></td> <!-- Espàcio -->
                                    <td align="right" style="background-color:#EFEFEF">
                                        <table border ='0' align="right" cellspacing="0" cellpadding="0">
<!--                                            <tr>
                                                <td style='border: 1px #CCCCCC solid; font-size:12px;' align='left' class="moduleTitle">{$CARD} # {$data.DESC.ID}: {$data.DESC.TIPO} {$data.DESC.ADICIONAL} </td>-->




                            <!--
                                                {if $data.DESC.MANUFACTURER eq 'yes'}
                                                <td style='border: 1px #CCCCCC solid; width:30px;' align="center"> <span id="editMan{$data.DESC.ID}"> <img class="icon" src="modules/hardware_detector/images/card_registered.gif" /> </span> </td>
                                                {elseif $data.DESC.MANUFACTURER  eq 'no'}
                                                <td style='border: 1px #CCCCCC solid; width:30px;' align="center"> <span id="editMan{$data.DESC.ID}"> <img class="icon" src="modules/hardware_detector/images/card_no_registered.gif" /> </span> </td>
                                                {/if}
                            -->


                                               <!-- <td style='border: 1px #CCCCCC solid; width:30px;'> <span id="editArea{$data.DESC.ID}"> <img class="icon" src="modules/hardware_detector/images/icon1.png" /> </span> </td>
                                                <td style='border: 1px #CCCCCC solid; width:30px;'><a href='?menu=hardware_detector&action=config_echo&cardId={$data.DESC.ID}'><img class="icon" src="modules/hardware_detector/images/icon_configecho.png" title="Config echo Canceller"/></a></td>
                                            </tr>-->
                                            <tr> 
                                                <td colspan="4">
                                                <table border ='0' align="center" cellspacing="0" cellpadding="0" class="table_title_row">
                                                    {if $data.PUERTOS}
                                                        {counter start=0 skip=1 print=false assign=cnt}
                                                            {foreach from=$data.PUERTOS key=q item=puerto name=filasPuerto}
                                                                {if $cnt%12==0}
                                                                    <tr>
                                                                {/if}
                                                                        <td>
                                                                            <table style='border:1px #CCCCCC solid;padding:1px;background-color:white;' border='0' callpadding='0' cellspacing='0' onMouseOver="this.style.backgroundColor='#f2f2f2';" onMouseOut="this.style.backgroundColor='#ffffff';">
                                                                                <tr><td  align='center' style='font-size:11px;background-image:url(modules/hardware_detector/images/{$puerto.ESTADO_DAHDI});height:64px;width:68px;background-repeat:no-repeat;vertical-align:top'><p>{$puerto.LOCALIDAD} {$puerto.TIPO}</p></td></tr>
                                                                                <tr><td  align='center' style='font-size:11px;color:{$puerto.ESTADO_ASTERISK_COLOR}'>{$puerto.ESTADO_ASTERISK}</td></tr>
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
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </fieldset>
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
<!-- cards MISDN -->
    <td class="table_navigation_row" colspan="3">
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

<div id="boxConfSPANS" style="display: none;">
    <div class="popup">
        <table>
            <tr>
                <td class="tl"/>
                <td class="b"/>
                <td class="tr"/>
            </tr>
            <tr>
                <td class="b"/>
                <td class="body">
                    <div class="content_boxConfSPANS">
                        <div id="table_boxConfSPANS">
                           <table width="100%" border="1" cellspacing="0" cellpadding="4" align="center">
                                <tr class="moduleTitle">
                                    <td class="moduleTitle">&nbsp;&nbsp;Configuration of Span </td>
                                </tr>
                                <tr>
                                    <td>
                                        <table id="tableConfSPANS" width="100%" border="1" cellspacing="0" cellpadding="4" align="center">

                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="footer">
                        <a class="closeConfSPANS">
                            <img src="themes/{$THEMENAME}/images/closelabel.gif" title="close" class="close_boxConfSPANS" />
                        </a>
                    </div>
                </td>
                <td class="b"/>
            </tr>
            <tr>
                <td class="bl"/>
                <td class="b"/>
                <td class="br"/>
            </tr>
        </table>
    </div>
</div>