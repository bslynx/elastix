<table width="99%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="moduleTitle">
        <td colspan="5" class="moduleTitle" valign="middle">&nbsp;&nbsp;<img src="images/list.png" border="0" align="absmiddle">&nbsp;&nbsp;{$title}</td>
    </tr>
    <tr class="letra12">
        <td width="30%" align="right">{$date_from.LABEL}:</b></td>
        <td width="20%" align="left" nowrap>{$date_from.INPUT} </td>
        <td width="20%" onload="show_elements();">
            <select id="classify_by" name="classify_by" size="1" onChange="show_elements();" >
                <option value="Number"{$SELECTED_1} >Extention(Number)</option>
                <option value="Queue" {$SELECTED_2} >Queue</option>
                <option value="Trunk" {$SELECTED_3} >Trunk</option>
            </select>
        </td>
    </tr>
    <tr class="letra12">
        <td align="right">{$date_to.LABEL}: </td>
        <td align="left" nowrap>{$date_to.INPUT}</td>

        <td align="left" nowrap id="td_link">{$call_to.INPUT}
            <a href='javascript: popup_phone_number("?menu=calendar&action=phone_numbers&rawmode=yes");'>{$HERE}</a>
        </td>
        <td id="id_vacio">&nbsp;</td>
        <td id="id_trunk">{$trunks.INPUT}</td>

        <td><input class="button" type="submit" name="show" value="{$SHOW}"></td>
    </tr>
</table>
<table class="tabForm" style="font-size: 16px;" width="100%" border="0" height="160px">
        {$ruta_img}
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
    show_elements();
    function popup_phone_number(url_popup)
    {
        var ancho = 600;
        var alto = 400;
        my_window = window.open(url_popup,"my_window","width="+ancho+",height="+alto+",location=yes,status=yes,resizable=yes,scrollbars=yes,fullscreen=no,toolbar=yes");
        my_window.moveTo((screen.width-ancho)/2,(screen.height-alto)/2);
        my_window.document.close();
    }

    function show_elements()
    {
        var number = document.getElementById('classify_by');

        if( number.value == 'Number' )
        {
            document.getElementById('td_link').style.display = '';
            document.getElementById('id_trunk').style.display = 'none';
            document.getElementById('id_vacio').style.display = 'none';
        }
        else if( number.value == 'Queue' )
        {
            document.getElementById('td_link').style.display = 'none';
            document.getElementById('id_vacio').style.display = '';
            document.getElementById('id_trunk').style.display = 'none';
            
        }
        else
        {
            document.getElementById('td_link').style.display = 'none';
            document.getElementById('id_vacio').style.display = 'none';
            document.getElementById('id_trunk').style.display = '';
        }
    }
</script>
{/literal}

