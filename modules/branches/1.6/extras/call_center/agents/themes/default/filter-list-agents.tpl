<table width="100%" border="0">
{*
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
*}
<tr>
	<td class="letra12" width="20%" align="right"><b>{$LABEL_STATE|escape:html}:</b></td>
    <td>{html_options name=cbo_estado id=cbo_estado options=$estados selected=$estado_sel onchange='submit();'}</td>
    <td align="right"><a href="?menu={$MODULE_NAME}&amp;action=new_agent">{$LABEL_CREATE_AGENT|escape:html}&nbsp;&raquo;</a></td>
</tr>
<tr>
    <td class='letra12' width='20%' align="right"><b>{$LABEL_WITH_SELECTION|escape:html}:</b></td>
    <td colspan='2'>
        <input class="button" type="submit" name="disconnect" value="{$LABEL_DISCONNECT|escape:html}" />&nbsp;
        <input class="button" type="submit" name="delete" value="{$LABEL_DELETE|escape:html}" onclick="return confirmSubmit('{$MESSAGE_CONTINUE_DELETE}')" />
     </td>
</tr>
</table>
<input type="hidden" name="reparar_file" id="reparar_file" value="" />
<input type="hidden" name="reparar_db" id="reparar_db" value="" />
<script language='JavaScript' type='text/javascript'>
var pregunta_borrar_agente_conf = "{$PREGUNTA_BORRAR_AGENTE_CONF}";
var pregunta_agregar_agente_conf = "{$PREGUNTA_AGREGAR_AGENTE_CONF}";
{literal}
    function preguntar_por_reparacion(id_agente, tipo, pregunta) {
        if (confirm(pregunta)) {
/*
            if (tipo != '') {
                window.open('?menu=agents&action='+tipo+'&id='+id_agente,'_parent');
            }
*/
			var reparar = document.getElementById(tipo);
			reparar.value = id_agente;
			document.getElementById("form_agents").submit();
        }
    }
{/literal}
</script>

