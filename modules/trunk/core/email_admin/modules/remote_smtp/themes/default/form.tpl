<table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="moduleTitle">
        <td class="moduleTitle" valign="middle" colspan='2'>&nbsp;&nbsp;<img src="{$IMG}" border="0" align="absmiddle">&nbsp;&nbsp;{$title}</td>
    </tr>
    <tr class="letra12">
        <td>
            <input class="button" name="save" value="{$CONFIGURATION_UPDATE}" type="submit" />&nbsp;&nbsp;
        </td>
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
    </tr>
</table>
<table class="tabForm" style="font-size: 16px;" cellspacing="0" cellpadding="0" width="100%" >
    <tr class="letra12">
        <td align="left" width="16%"><b>{$status.LABEL}:</b></td>
        <td align="left" width="34%">{$status.INPUT}</td>
        <td rowspan='5' width="40%">{$MSG_REMOTE_SMTP}</td>
        <td rowspan="5" width="10%">&nbsp;</td>
    </tr>
    <tr class="letra12">
        <td align="left"><b>{$SMTP_Server.LABEL}:</b></td>
        <td align="left">{$SMTP_Server.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td align="left"><b>{$relayhost.LABEL}: <span class="required">*</span></b></td>
        <td align="left">{$relayhost.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td align="left"><b>{$port.LABEL}: <span class="required">*</span></b></td>
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
    <tr class="letra12">
        <td align="left"><b>{$autentification.LABEL}:</b></td>
        <td align="left">{$autentification.INPUT}{$MSG_REMOTE_AUT}</td>
    </tr>
</table>