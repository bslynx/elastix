<table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="moduleTitle">
        <td class="moduleTitle" valign="middle" colspan='2'>&nbsp;&nbsp;<img src="{$IMG}" border="0" align="absmiddle">&nbsp;&nbsp;{$title}</td>
    </tr>
    <tr class="letra12">
        {if $mode eq 'input'}
        <td align="left">
            <input class="button" type="submit" name="save_newList" value="{$SAVE}">&nbsp;&nbsp;
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
    {if $StatusNew}
    <tr class="letra12">
        <td align="left"><b>{$emailmailman.LABEL}: <span  class="required">*</span></b></td>
        <td align="left">{$emailmailman.INPUT}</td>
    </tr>

    <tr class="letra12">
        <td align="left"><b>{$passwdmailman.LABEL}: <span  class="required">*</span></b></td>
        <td align="left">{$passwdmailman.INPUT}</td>
    </tr>
    {/if}

    <tr class="letra12">
        <td align="left"><b>{$domain.LABEL}: <span  class="required">*</span></b></td>
        <td align="left">{$domain.INPUT}</td>
    </tr>

    <tr class="letra12">
        <td align="left"><b>{$namelist.LABEL}: <span  class="required">*</span></b></td>
        <td align="left">{$namelist.INPUT}</td>
    </tr>

    <tr class="letra12">
        <td align="left"><b>{$emailadmin.LABEL}: <span  class="required">*</span></b></td>
        <td align="left">{$emailadmin.INPUT}</td>
    </tr>

    <tr class="letra12">
        <td align="left"><b>{$password.LABEL}: <span  class="required">*</span></b></td>
        <td align="left">{$password.INPUT}</td>
    </tr>

    <tr class="letra12">
        <td align="left"><b>{$passwordconfirm.LABEL}: <span  class="required">*</span></b></td>
        <td align="left">{$passwordconfirm.INPUT}</td>
    </tr>

</table>

<input class="button" type="hidden" name="id" value="{$ID}" />