<form method="POST" style="margin-bottom:0;" action="?menu={$menu}">
<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr>
  <td>
    <table width="100%" cellpadding="4" cellspacing="0" border="0">
      <tr class="letra12">
        <td width="8%" align="right">{$date_start.LABEL}: <span  class="required">*</span></td>
        <td width="12%" align="left" nowrap>{$date_start.INPUT}</td>
        <td width="12%" align="right">{$date_end.LABEL}: <span  class="required">*</span></td>
        <td width="12%" align="left" nowrap>{$date_end.INPUT}</td>
        <td width="10%" align="right">{$field_pattern.LABEL}: </td>
        <td width="12%" align="left" nowrap>{$field_name.INPUT}&nbsp;{$field_pattern.INPUT}</td>
        <td width="12%" align="center"><input class="button" type="submit" name="filter" value="{$Filter}" ></td>
      </tr>
   </table>
  </td>
</tr>
</table>
</form>