<script language="JavaScript" type="text/javascript" src="{$relative_dir_rich_text}/richtext/html2xhtml.js"></script>
<script language="JavaScript" type="text/javascript" src="{$relative_dir_rich_text}/richtext/richtext_compressed.js"></script>
<script language="JavaScript" type="text/javascript">
//Usage: initRTE(imagesPath, includesPath, cssFile, genXHTML, encHTML)
initRTE("./{$relative_dir_rich_text}/richtext/images/", "./{$relative_dir_rich_text}/richtext/", "", true);
var rte_script = new richTextEditor('rte_script');
</script>

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
          <input class="button" type="submit" name="save" value="{$SAVE}" onclick="return enviar_datos();">
          <input class="button" type="submit" name="cancel" value="{$CANCEL}"></td>
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
          {elseif $mode eq 'edit'}
          <input class="button" type="submit" name="apply_changes" value="{$APPLY_CHANGES}" onclick="return enviar_datos();">
          <input class="button" type="submit" name="cancel" value="{$CANCEL}"></td>
          {else}
          <input class="button" type="submit" name="edit" value="{$EDIT}">
          <input class="button" type="button" name="desactivar" value="{$DESCATIVATE}"  onClick="if(confirmSubmit('{$CONFIRM_CONTINUE}'))desactivar_campania();">

          <input class="button" type="submit" name="delete" value="{$DELETE}"  onClick="return confirmSubmit('{$CONFIRM_DELETE}');">

          <input class="button" type="button" name="cancel_view" value="{$CANCEL}" onclick="window.open('?menu=campaign_out','_parent');"></td>
          {/if}          
     </tr>
   </table>
  </td>
</tr>
<tr>
  <td>
    <table width="100%" border="1" cellspacing="0" cellpadding="0" class="tabForm">
      <tr>
		<td width="20%">{$nombre.LABEL}: <span  class="required">*</span></td>
		<td width="80%">{$nombre.INPUT}</td>
	  </tr>
      <tr>
		<td width="20%">{$fecha_ini.LABEL}: <span  class="required">*</span></td>
		<td width="80%">{$fecha_ini.INPUT}</td>
      </tr>
      <tr>
		<td width="20%">{$fecha_fin.LABEL}: <span  class="required">*</span></td>
		<td width="80%">{$fecha_fin.INPUT}</td>
      </tr>
     <!-- <tr>
		<td>{$hora_ini.LABEL}: <span  class="required">*</span></td>
		<td>{$hora_ini.INPUT}</td>
	  </tr>
      <tr>
		<td>{$hora_fin.LABEL}: <span  class="required">*</span></td>
		<td>{$hora_fin.INPUT}</td>
	  </tr>-->
      <tr>
		<td>{$formulario.LABEL}: <span  class="required">*</span></td>
                <td>
                    {if $mode eq 'edit' or $mode eq 'input'}
                        <table border='0' cellpadding='0' cellspacing='0'>
                            <tr>
                                <td rowspan='2'>{$formulario.INPUT}</td>
                                <td><input type='button' name='agregar_formulario' value="&gt;&gt;" onclick='add_form()'/></td>
                                <td rowspan='2'>{$formularios_elegidos.INPUT}</td>
                            </tr>
                            <tr>
                                <td><input type='button' name='quitar_formulario' value="&lt;&lt;" onclick='drop_form()'/></td>
                            </tr>
                        </table>  
                    {else}
                        {$formulario.INPUT}
                    {/if}         
                </td>
	  </tr>
      <tr>
		<td width="20%">{$trunk.LABEL}: <span  class="required">*</span></td>
		<td width="80%">{$trunk.INPUT}</td>
      </tr>
      <tr>
		<td width="20%">{$context.LABEL}: <span  class="required">*</span></td>
		<td width="80%">{$context.INPUT}</td>
      </tr>
      <tr>
		<td width="20%">{$queue.LABEL}: <span  class="required">*</span></td>
		<td width="80%">{$queue.INPUT}</td>
      </tr>
      <tr>
	<td>{$reintentos.LABEL}: <span  class="required">*</span></td>
	<td>{$reintentos.INPUT}</td>
      </tr>
      {if $mode eq 'input'}
      <tr>
    	<td>Archivo de Llamadas: <span  class="required">*</span></td>
    	<td><input type='file' name='phonefile'></td>
      </tr>
      {/if}
      <tr>
        <td>{$script.LABEL}: <span  class="required">*</span></td>
        <td> 
            {if $mode eq 'edit' or $mode eq 'input'}
               <script language="JavaScript" type="text/javascript">
                   rte_script.html ="{$rte_script}";
                   rte_script.toggleSrc = false;
                   rte_script.build();
               </script>
            {else}
                {$script.INPUT}
            {/if} 
        </td>
      </tr>
      </table>
    </td>
  </tr>
</table>
<input type="hidden" name="id_campaign" id='id_campaign' value="{$id_campaign}" />
<input type="hidden" name="values_form" id='values_form' value="" />    
</form>

{literal}
<script type="text/javascript">
function desactivar_campania()
{
    var id_campaign = document.getElementById("id_campaign").value;
    xajax_desactivar_campania(id_campaign);
}

function delete_campania() {
}

function leer_select_values(nombre)
{
    var select_form = document.getElementsByName(nombre)[0];
    var values = "";
    
    for(var i=0;i<select_form.length;i++){
//         if(select_form[i].selected)
            values = values + select_form[i].value + ",";
    }
    if(values!="")
        values = values.substring(0,values.length-1);
    return values;
}

function enviar_datos()
{   
    document.getElementById("values_form").value = leer_select_values('formularios_elegidos');
    //boton.type='submit';
    updateRTEs();
    //boton.submit();
    return true;
}

function add_form()
{
    var select_formularios = document.getElementsByName('formulario')[0];
    var select_formularios_elegidos = document.getElementsByName('formularios_elegidos')[0];

    for(var i=0;i<select_formularios.length;i++){
        if(select_formularios[i].selected){
            var option_tmp = document.createElement("option");
            option_tmp.value = select_formularios[i].value;
            option_tmp.appendChild(document.createTextNode(select_formularios[i].firstChild.data));
            select_formularios_elegidos.appendChild(option_tmp);
        }
    }

    for(var i=select_formularios.length-1;i>=0;i--){
        if(select_formularios[i].selected){
            select_formularios.removeChild(select_formularios[i]);
        }
    }
}


function drop_form()
{
    var select_formularios = document.getElementsByName('formulario')[0];
    var select_formularios_elegidos = document.getElementsByName('formularios_elegidos')[0];

    for(var i=0;i<select_formularios_elegidos.length;i++){
        if(select_formularios_elegidos[i].selected){
            var option_tmp = document.createElement("option");
            option_tmp.value = select_formularios_elegidos[i].value;
            option_tmp.appendChild(document.createTextNode(select_formularios_elegidos[i].firstChild.data));
            select_formularios.appendChild(option_tmp);
        }
    }

    for(var i=select_formularios_elegidos.length-1;i>=0;i--){
        if(select_formularios_elegidos[i].selected){
            select_formularios_elegidos.removeChild(select_formularios_elegidos[i]);
        }
    }
}
</script>
{/literal}
{$xajax_javascript}