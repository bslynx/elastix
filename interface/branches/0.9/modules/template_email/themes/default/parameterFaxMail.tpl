<form method="POST">
<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr class="moduleTitle">
  <td class="moduleTitle" valign="middle">&nbsp;&nbsp;<img src="images/icon-email.png" border="0" align="absmiddle">&nbsp;&nbsp;{$title}</td>
</tr>
<tr>
  <td>
    <table width="100%" cellpadding="4" cellspacing="0" border="0">
      <tr>
        <td align="left">
          {if $mode eq 'edit'}
          <input class="button" type="submit" name="submit_apply_change" value="{$SAVE}" >
          <input class="button" type="submit" name="cancel" value="{$CANCEL}"></td>
          {else}
          <input class="button" type="submit" name="submit_edit" value="{$EDIT_PARAMETERS}"></td>
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
	<td width="15%">{$remite.LABEL}: <span  class="required">*</span></td>
	<td width="30%">{$remite.INPUT}</td>
        <td width="10%" rowspan='3'>{$content.LABEL}: </td>
	<td width="30%" rowspan='3'>{$content.INPUT}</td>	
      </tr>
      <tr>
        <td width="15%">{$remitente.LABEL}: <span  class="required">*</span></td>
	<td width="30%">{$remitente.INPUT}</td>
     </tr>
      <tr>
	<td width="15%">{$subject.LABEL}: <span  class="required">*</span></td>
	<td width="30%">{$subject.INPUT}</td>
      </tr>
    </table>
  </td>
</tr>
</table>
</form>
{$VIRTUAL_FAX_LIST}
