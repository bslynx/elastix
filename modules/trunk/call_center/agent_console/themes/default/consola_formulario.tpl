<table width="100%" id="TABLA_FORMULARIO">
  <tr>
    <td valign='top'>{$formularios}</td>
    <td>
        <select id='select_form' name='select_form' onChange='mostrarFormularioSeleccionado(this.value);'>{html_options values=$option_form.VALUE output=$option_form.NAME selected=$option_form.SELECTED}
        </select> <input type="hidden" name="id_formularios" id="id_formularios" value="{$id_formularios}">
    </td>
  </tr>
  <tr>
    <td valign='top' colspan='2'><hr></td>
  </tr>
  <tr class="moduleTitle">
    <td valign='top' class="moduleTitle" colspan='2'>{$fill_fields}</td>
  </tr>
  <tr>
    <td valign='top' class="mb_message" colspan='2'><div id="mensaje"><div></td>
  </tr>
  <tr>
    <td valign='top' height='15' colspan='2'>
      <br><input type="button" name="guardar" id="guardar" value="{$SAVE}" onClick="guardar_informacion_cliente();">
    </td>
  </tr>
</table>
<table width="100%" border=0 class="tabForm" height="400">
  <tr>
    <td valign='top'>
          <table cellpadding="2" cellspacing="0" width="100%" border="0"><tr><td height="10">
          {foreach key=indice item=campo from=$FORMULARIO}
            {if $campo.ID_FORM neq ''}
                </table><table cellpadding="2" cellspacing="0" width="100%" border="0" id="{$campo.ID_FORM}">
            {/if}
            {if $campo.TYPE eq 'LABEL'}
                <tr>
                    <td height='15' colspan='2' width='100%'><center>{$campo.INPUT} {$campo.ID_FORM}</center></td>
                </tr>
            {else} 
                <tr>
                    <td height='15' width='15%' valign="top"><span style='color:#666666; FONT-SIZE: 12px;'>{$campo.TAG}</span></td>
                    <td height='15' width='85%'>{$campo.INPUT} {$campo.ID_FIELD}</td>
                </tr>
            {/if}
          {/foreach}
          </table>
    </td>
  </tr>
</table>