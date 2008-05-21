<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
    <tr class="moduleTitle">
        <td class="moduleTitle" valign="middle">&nbsp;&nbsp;<img src="images/adress_book.png" border="0" align="absmiddle">&nbsp;&nbsp;{$TITLE}</td>
    </tr>
    <tr>
        <td align="left">
            {if $Show}
                <input class="button" type="submit" name="save" value="{$SAVE}">&nbsp;&nbsp;&nbsp;&nbsp;
            {/if}
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
    </tr>
    <tr>
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
    </tr>
    <tr>
        <table width="100%" cellpadding="4" cellspacing="0" border="0" class="tabForm">
            <tr>
                <td align="left" width="20%"><b>{$name.LABEL}: <span  class="required">*</span></b></td>
                <td class="required" align="left">{$name.INPUT}</td>
            </tr>
	    <tr>
                <td align="left" width="20%"><b>{$last_name.LABEL}: <span  class="required">*</span></b></td>
                <td class="required" align="left">{$last_name.INPUT}</td>
            </tr>
            <tr>
                <td align="left"><b>{$telefono.LABEL}: </b></td>
                <td align="left">{$telefono.INPUT}</td>
            </tr>
            <tr>
                <td align="left"><b>{$extension.LABEL}: <span  class="required">*</span></b></td>
                <td align="left">{$extension.INPUT}</td>
            </tr>
            <tr>
                <td align="left"><b>{$email.LABEL}: <span  class="required">*</span></b></td>
                <td align="left">{$email.INPUT}</td>
            </tr>
        </table>
    </tr>
</table>
