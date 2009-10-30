<table width="100%" border="0">
<tr>
    <td class="letra12" width="20%" ><b>{$LABEL_CAMPAIGN_STATE|escape:html}:</b></td>
    <td>{html_options name=cbo_estado id=cbo_estado options=$estados selected=$estado_sel onchange='submit();'}</td>
    <td align="right"><a href="?menu={$MODULE_NAME}&amp;action=new_campaign">{$LABEL_CREATE_CAMPAIGN|escape:html}&nbsp;&raquo;</a></td>
</tr>
<tr>
    <td class='letra12' width='20%'><b>{$LABEL_WITH_SELECTION|escape:html}:</b></td>
    <td colspan='2'><input class="button" type="submit" name="activate" value="{$LABEL_ACTIVATE|escape:html}" />&nbsp;
        <input class="button" type="submit" name="deactivate" value="{$LABEL_DEACTIVATE|escape:html}" onclick="return confirmSubmit('{$MESSAGE_CONTINUE_DEACTIVATE|escape:html}')" />&nbsp;
        <input class="button" type="submit" name="delete" value="{$LABEL_DELETE|escape:html}" onclick="return confirmSubmit('{$MESSAGE_CONTINUE_DELETE|escape:html}')" />
     </td>
</tr>
</table>

