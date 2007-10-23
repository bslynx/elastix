<table width="{$width}" align="center" border="0" cellpadding="0" cellspacing="0">
  <tr class="moduleTitle">
    <td class="moduleTitle" valign="middle">&nbsp;&nbsp;<img src="{$icon}" border="0" align="absmiddle">&nbsp;&nbsp;{$title}</td>
  </tr>
  <tr class="filterForm">
    <td class="filterForm" valign="middle">
        <form style='margin-bottom:0;' method='POST' action='?menu={$MODULE_NAME}'>
            <input type='submit' name='submit_harware_detect' value='{$HARDWARE_DETECT}'  onclick="return confirmSubmit('{$MENSAJE_CONFIRM}');" class='button' /> 
        </form>
    </td>
  </tr>
  <tr>
    <td class="table_navigation_row"></td>
  </tr>
  <tr>
    <td  class="table_navigation_row">
      <table border ='0' align="left" cellspacing="0" cellpadding="0" >
        {foreach from=$arrData key=k item=data name=filas}
            {if $data.DESC.TIPO ne $CARD_NO_MOSTRAR}
                <tr>
                    <td style='border:1px #CCCCCC solid' align='center' class="moduleTitle">{$CARD} # {$data.DESC.ID}: {$data.DESC.TIPO} {$data.DESC.ADICIONAL}</td>
                </tr>
                <tr> 
                    <td>
                    <table border ='0' align="center" cellspacing="0" cellpadding="0" class="table_title_row">
                        {if $data.PUERTOS}
                            <tr>
                                {foreach from=$data.PUERTOS key=q item=puerto name=filasPuerto}
                                    <td>
                                        <table style='border:1px #CCCCCC solid;padding:1px;background-color:white' border='0' callpadding='0' cellspacing='0' onMouseOver="this.style.backgroundColor='#f2f2f2';" onMouseOut="this.style.backgroundColor='#ffffff';">                              
                                            <tr><td  align='center'>{$NO_PUERTO} {$puerto.LOCALIDAD}</td></tr>
                                            <tr><td  align='center'>{$puerto.TIPO}</td></tr>
                                            <tr><td  align='center' style='background-color:{$puerto.COLOR}'>{$puerto.ESTADO}</td></tr>
                                        </table>
                                    </td>
                                {/foreach}
                            </tr>
                        {else}
                            <tr>
                                <td style='border:1px #CCCCCC solid;padding:1px;background-color:white'>{$PORT_NOT_FOUND}</td>
                            </tr>
                        {/if}
                    </table>
                    </td>
                </tr>
            {/if}
        {/foreach} 
      </table>
    </td>
  </tr>
</table>
<center><h3 style='color:#990033;font-size:14px'>{$CARDS_NOT_FOUNDS}</h3></center>