<table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="moduleTitle">
        <td class="moduleTitle" valign="middle" colspan='2'>&nbsp;&nbsp;<img src="{$IMG}" border="0" align="absmiddle">&nbsp;&nbsp;{$title}</td>
    </tr>
    <tr class="letra12">
        <td>
            <input class="button" name="in_actualizar_conf" value="{$CONFIGURATION_UPDATE}" type="submit" />&nbsp;&nbsp;
            <input type="hidden"  value="{$ACTIVATED}" name="status" />
            {if $ACTIVATED_BUTTON}
                <input class="button" name="enabled"   value="{$ENABLE}"  type="submit" />
            {else}
                <input class="button" name="disabled"  value="{$DISABLE}" type="submit" />
            {/if}
        </td>
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
    </tr>
</table>
<table class="tabForm" style="font-size: 16px;" cellspacing="0" cellpadding="0" width="100%" >
    <tr class="letra12">
        <td align="left" width="16%"><b>{$STATUS}:</b></td>
        <td align="left" width="34%"><b>{$STATUS_VALUE}</b></td>
        <td rowspan='5' width="40%">{$MSG_REMOTE_SMTP}</td>
        <td rowspan="5" width="10%"></td>
    </tr>
    <tr class="letra12">
        <td align="left"><b>{$relayhost.LABEL}: <span class="required">*</span></b></td>
        <td align="left">{$relayhost.INPUT}</td>
    </tr>
    <!--<tr class="letra12">
        <td align="left"><b>{$myhostname.LABEL}:</b></td>
        <td align="left"> {$myhostname.INPUT}</td>
    </tr>-->
    <tr class="letra12">
        <td align="left"><b>{$port.LABEL}:</b></td>
        <td align="left">{$port.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td align="left"><b>{$user.LABEL}:</b></td>
        <td align="left">{$user.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td align="left"><b>{$password.LABEL}:</b></td>
        <td align="left">{$password.INPUT}</td>
    </tr>
</table>
