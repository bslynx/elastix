<form method="POST">
<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr class="moduleTitle">
  <td class="moduleTitle" valign="middle">&nbsp;&nbsp;<img src="images/1x1.gif" border="0" align="absmiddle">&nbsp;&nbsp;{$title}</td>
</tr>
<tr>
  <td>
    <table width="100%" cellpadding="4" cellspacing="0" border="0">
      <tr>
        <td align="left">
          {if $mode eq 'input'}
          <input class="button" type="submit" name="save" value="{$SAVE}">
          <input class="button" type="submit" name="cancel" value="{$CANCEL}"></td>
          {elseif $mode eq 'edit'}
          <input class="button" type="submit" name="apply_changes" value="{$APPLY_CHANGES}">
          <input class="button" type="submit" name="cancel" value="{$CANCEL}"></td>
          {else}
         {* <input class="button" type="submit" name="edit" value="{$EDIT}"> *}
          <input class="button" type="submit" name="delete" value="{$DELETE}"  onClick="return confirmSubmit('{$CONFIRM_CONTINUE}')">
	  <input class="button" type="submit" name="cancel" value="{$CANCEL}"></td>
          {/if}          
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
     </tr>
   </table>
  </td>
</tr>
<tr>
  <td>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm">
      <tr>
	<td width="15%">{$domain_name.LABEL}: <span  class="required">*</span></td>
	<td width="35%">{$domain_name.INPUT}</td>
      </tr>
    </table>
    </td>
  </tr>
</table>
<input type="hidden" name="id_domain" value="{$id_domain}">
</form>
