{literal}
<script type="text/javascript">
function desactivateBreak()
{
    var id_break = document.getElementById("id_break").value;
    xajax_desactivateBreak(id_break);
}
</script>
{/literal}
{$xajax_javascript}
<form method="POST" enctype="multipart/form-data">
<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr class="moduleTitle">
  <td class="moduleTitle" valign="middle">&nbsp;&nbsp;<img src="images/kfaxview.png" border="0" align="absmiddle" />&nbsp;&nbsp;{$title}</td>
</tr>
<tr>
  <td>
    <table width="100%" cellpadding="4" cellspacing="0" border="0">
      <tr>
        <td align="left">
          {if $mode eq 'input'}
          <input class="button" type="submit" name="save" value="{$SAVE}" />
          <input class="button" type="submit" name="cancel" value="{$CANCEL}"></td>
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
          {elseif $mode eq 'edit'}
          <input class="button" type="submit" name="apply_changes" value="{$APPLY_CHANGES}" />
          <input class="button" type="submit" name="cancel" value="{$CANCEL}" /></td>
          {else}
          <input class="button" type="submit" name="edit" value="{$EDIT}" />
          <input class="button" type="button" name="desactivar" value="{$DESACTIVATE}"  onClick="if(confirmSubmit('{$CONFIRM_CONTINUE}'))desactivateBreak();" />
          <input class="button" type="button" name="cancel_view" value="{$CANCEL}" onclick="window.open('?menu=break_administrator','_parent');" /></td>
          {/if}          
     </tr>
   </table>
  </td>
</tr>
<tr>
  <td>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm">
        <tr>
		<td width="20%">{$nombre.LABEL}: <span  class="required">*</span></td>
		<td width="80%">{$nombre.INPUT}</td>
        </tr>
        <tr>
		<td width="20%">{$descripcion.LABEL}: <span  class="required">*</span></td>
		<td width="80%">{$descripcion.INPUT}</td>
        </tr> 
      </table>
    </td>
  </tr>
</table>
<input type="hidden" name="id_break" id='id_break' value="{$id_break}" />
</form>