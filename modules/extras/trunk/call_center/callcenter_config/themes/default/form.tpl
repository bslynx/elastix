<table width="99%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="moduleTitle">
        <td class="moduleTitle" valign="middle">&nbsp;&nbsp;<img src="{$IMG}" border="0" align="absmiddle">&nbsp;&nbsp;{$TITLE}</td>
        <td></td>
    </tr>
    <tr class="letra12">
        <td align="left"><input class="button" type="submit" name="save" value="{$SAVE}"></td>
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
    </tr>
</table>
<table width="100%">
    <tr><td width="100%">
        <table class="tabForm" style="font-size: 16px;" width="100%" >
			<tr class="letra12">
				<td align="center" colspan="2"><b>Asterisk Connection</b></td>
				<td align="center" colspan="2"><b>Dialer Parameters</b></td>
			</tr>
			<tr class="letra12">
				<td align="right">{$asterisk_asthost.LABEL}:</td><td align="left">{$asterisk_asthost.INPUT}</td>
				<td align="right">{$dialer_llamada_corta.LABEL}:</td><td align="left">{$dialer_llamada_corta.INPUT}</td>				
			</tr>
			<tr class="letra12">
				<td align="right">{$asterisk_astuser.LABEL}:</td><td align="left">{$asterisk_astuser.INPUT}</td>
				<td align="right">{$dialer_tiempo_contestar.LABEL}:</td><td align="left">{$dialer_tiempo_contestar.INPUT}</td>				
			</tr>
			<tr class="letra12">
				<td align="right">{$asterisk_astpass_1.LABEL}:</td><td align="left">{$asterisk_astpass_1.INPUT}</td>
				<td align="right">{$dialer_debug.LABEL}:</td><td align="left">{$dialer_debug.INPUT}</td>				
			</tr>
			<tr class="letra12">
				<td align="right">{$asterisk_astpass_2.LABEL}:</td><td align="left">{$asterisk_astpass_2.INPUT}</td>
				<td align="right">{$dialer_allevents.LABEL}:</td><td align="left">{$dialer_allevents.INPUT}</td>				
			</tr>
        </table>
    </td></tr>
    <tr><td align="center">
        <table class="tabForm" style="font-size: 16px;" >
			<tr class="letra12">
				<td align="center" colspan="2"><b>{$DIALER_STATUS_MESG}</b></td>
			</tr>
			<tr class="letra12">
				<td align="right">{$CURRENT_STATUS}:</td><td>{$DIALER_STATUS}</td>
			</tr>
			<tr class="letra12">
				<td align="center" colspan="2"><input class="button" type="submit" name="dialer_action" value="{$DIALER_ACTION}"></td>
			</tr>
		</tr>
    </td></tr>
</table>
