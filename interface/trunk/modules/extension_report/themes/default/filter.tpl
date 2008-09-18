<!-- <table class="tabForm" style="font-size: 16px;" width="100%" border="0"> -->
<table width="99%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="moduleTitle">
        <td colspan="9" class="moduleTitle" valign="middle">&nbsp;&nbsp;<img src="images/list.png" border="0" align="absmiddle">&nbsp;&nbsp;{$title}</td>
    </tr>
    <tr class="letra12">
        <td width="10%" align="right">{$date_from.LABEL}: <span class="required">*</span></b></td>
        <td width="12%" align="left" nowrap>{$date_from.INPUT} </td>
        <td width="10%" align="right">{$date_to.LABEL}: </td>
        <td width="12%" align="left" nowrap>{$date_to.INPUT}</td>
        <td width="10%" align="right" nowrap>{$extensions.LABEL} <span class="required">*</span></b></td>
<!--         <td width="12%" align="left" nowrap>{$extensions.INPUT}</td> -->
        <td width="12%" align="left" nowrap>{$call_to.INPUT}<a href='javascript: popup_phone_number("modules/calendar/libs/phone_numbers.php");'> Here</a></td>
<!--         <td width="10%" align="right" nowrap>{$calls.LABEL}:</td> -->
<!--         <td>{$calls.INPUT}</td> -->
        <td align="center"><input class="button" type="submit" name="show" value="{$SHOW}"></td>
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
    </tr>
</table>
<table class="tabForm" style="font-size: 16px;" width="100%" border="0">
    <tr class="letra12">
        <td align="center"><img src='modules/{$module_name}/libs/grafic.php?du={$VALUE}%&in={$in}&out={$out}&ext={$ext}&tot={$tot}' border='0'></td>
    </tr>
</table>
<input type="hidden" name="nav" value="{$nav_value}" />
<input type="hidden" name="start" value="{$start_value}" />
<input type="hidden" name="date_1" value="{$date_1}" />
<input type="hidden" name="date_2" value="{$date_2}" />

<!--// solo para que pase el error llamado del popup --> 
<input type="hidden" name="phone_type" id="phone_type"  value="" />
<input type="hidden" name="phone_id" id="phone_id" value="" />

{literal}
<script type= "text/javascript">
    function popup_phone_number(url_popup)
    {
        var ancho = 600;
        var alto = 400;
        my_window = window.open(url_popup,"my_window","width="+ancho+",height="+alto+",location=yes,status=yes,resizable=yes,scrollbars=yes,fullscreen=no,toolbar=yes");
        my_window.moveTo((screen.width-ancho)/2,(screen.height-alto)/2);
        my_window.document.close();
        
    }
</script>
{/literal}