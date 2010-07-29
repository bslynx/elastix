<table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="moduleTitle">
        <td class="moduleTitle" valign="middle" colspan='2'>&nbsp;&nbsp;<img src="{$IMG}" border="0" align="absmiddle">&nbsp;&nbsp;{$title}</td>
    </tr>
    <tr class="letra12">
        {if $mode eq 'input'}
        <td align="left">
            <input class="button" type="submit" name="save_dhcp" value="{$SAVE}">&nbsp;&nbsp;
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        {elseif $mode eq 'view'}
        <td align="left">
            <input class="button" type="submit" name="edit_dhcpconf" value="{$EDIT}">&nbsp;&nbsp;
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        {elseif $mode eq 'edit'}
        <td align="left">
            <input class="button" type="submit" name="update_dhacp" value="{$EDIT}">&nbsp;&nbsp;
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        {/if}
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
    </tr>
</table>
<table class="tabForm" style="font-size: 16px;" width="100%" >
    <tr class="letra12">
        <td align="left"><b>{$hostname.LABEL}: <span class="required">*</span></b></td>
        <td align="left">{$hostname.INPUT}&nbsp;&nbsp;{$HOST_NAME}</td>
    </tr>

    <tr class="letra12">
        <td align="left"><b>{$ipaddress.LABEL}: <span class="required">*</span></b></td>
        <td align="left">{$ipaddress.INPUT}&nbsp;&nbsp;{$IP_ADDRESS}</td>
    </tr>

    <tr class="letra12">
        <td align="left"><b>{$macaddress.LABEL}: <span class="required">*</span></b></td>
        <td align="left">{$macaddress.INPUT}&nbsp;&nbsp;{$MAC_ADDRESS}</td>
    </tr>

</table>

<input class="button" type="hidden" name="id" value="{$ID}" />