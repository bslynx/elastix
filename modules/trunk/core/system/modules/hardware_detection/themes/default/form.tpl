<link   rel ="stylesheet"      href="modules/hardware_detection/themes/style.css" />

<table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="moduleTitle">
        <td class="moduleTitle" valign="middle" colspan='2'>&nbsp;&nbsp;<img src="{$IMG}" border="0" align="absmiddle">&nbsp;&nbsp;{$title}</td>
    </tr>
    <tr class="letra12">
        {if $mode eq 'input'}
        <td align="left">
            <input class="button" type="submit" name="save_new" value="{$SAVE}">&nbsp;&nbsp;
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        {elseif $mode eq 'view'}
        <td align="left">
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        {elseif $mode eq 'edit'}
        <td align="left">
            <input class="button" type="submit" name="save_edit" value="{$EDIT}">&nbsp;&nbsp;
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        {/if}
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
    </tr>
</table>
<table class="tabForm" style="font-size: 16px;" width="100%" >
    <input type="hidden" name="idCard" value="{$DESC_ID}" />

    <label id="port_desc">{$CARD} # {$ID}: {$TIPO} {$ADICIONAL}</label>
    <div id="form">
    <!--<table border="0" width="40%" cellspacing="0" style="border:1px solid black">-->
        {foreach key=key item=echocancel name=arrPortsEchoInfo from=$arrPortsEcho}
        <tr class="letra12">
            <td> </td>
            <td> </td>
            <td align="left"><b>{$key}</b>  {$echocancel.name_port}: </td>
            <td width="15%" align="left">
                <select id='typeecho_{$key}' name='typeecho_{$key}'>
                    <option value='{$echocancel.type_echo}'>{$echocancel.type_echo}</option>
                    <option value='none'>none</option>
                    <option value='OSLEC'>OSLEC</option>
                    <option value='MG2'>MG2</option>
                    <option value='KBL'>KBL</option>
                    <option value='SEC2'>SEC2</option>
                    <option value='SEC'>SEC</option>
                </select>
            </td>
            <input type="hidden" value="{$echocancel.type_echo}" name="tmpTypeEcho{$key}" />
            <td> </td>
            <td> </td>
            <td> </td>
            <td> </td>
            <td> </td>
            <td> </td>
        </tr>
        {/foreach}
    <!--</table>-->
    </div>
</table>
<input class="button" type="hidden" name="id" value="{$ID}" />