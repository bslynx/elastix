{if $no_queues}
<p><b>No queues have been defined</b></p>
<p>For an outgoing campaign to be created, it is necessary to configure at least one queue. You can add queues <a href="?menu=pbxconfig&amp;display=queues">here</a>.</p>
{elseif $no_incoming_queues }
<p><b>No remaining queues for incoming campaings</b></p>
<p>No queues are currently reserved for incoming campaigns. For an incoming campaign to be created, it is necessary to have at least one reserved queue. You can add queues <a href="?menu=pbxconfig&amp;display=queues">here</a>.</p>
{elseif $no_forms }
<p><b>No active forms available</b></p>
<p>For an outgoing campaign to be created, it is necessary to have at least one active form. You can add forms <a href="?menu=form_designer">here</a>.</p>
{else}
<script language="JavaScript" type="text/javascript" src="{$relative_dir_rich_text}/richtext/html2xhtml.js"></script>
<script language="JavaScript" type="text/javascript" src="{$relative_dir_rich_text}/richtext/richtext_compressed.js"></script>
<script language="JavaScript" type="text/javascript">
//Usage: initRTE(imagesPath, includesPath, cssFile, genXHTML, encHTML)
initRTE("./{$relative_dir_rich_text}/richtext/images/", "./{$relative_dir_rich_text}/richtext/", "", true);
var rte_script = new richTextEditor('rte_script');
</script>

<form method="post" enctype="multipart/form-data" onsubmit="return submitForm();">
<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr class="moduleTitle">
  <td class="moduleTitle" valign="middle">&nbsp;&nbsp;<img src="images/kfaxview.png" border="0" align="absmiddle" />&nbsp;&nbsp;{$title}</td>
</tr>
<tr>
  <td>
    <table width="100%" valign="top" cellpadding="4" cellspacing="0" border="0">
      <tr>
          {if $mode eq 'input'}
        <td align="left">
          <input class="button" type="submit" name="save" value="{$SAVE}" />
          <input class="button" type="submit" name="cancel" value="{$CANCEL}" />
        </td>
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
          {elseif $mode eq 'edit'}
        <td align="left">
          <input class="button" type="submit" name="apply_changes" value="{$APPLY_CHANGES}" />
          <input class="button" type="submit" name="cancel" value="{$CANCEL}" />
        </td>
          {else}
        <td align="left">
          <input class="button" type="submit" name="edit" value="{$EDIT}" />
          <input class="button" type="submit" name="delete" value="{$DELETE}"  onClick="return confirmSubmit('{$CONFIRM_DELETE}');" />
          <input class="button" type="button" name="cancel_view" value="{$CANCEL}" onclick="window.open('?menu=campaign_out','_parent');" />
        </td>
          {/if}          
     </tr>
   </table>
  </td>
</tr>
<tr>
  <td>
    <table width="900" valign="top" border="0" cellspacing="0" cellpadding="0" class="tabForm">
      <tr height='50'>
          <td width="20%" align='right'>{$nombre.LABEL}: <span  class="required">*</span></td>
          <td colspan='2'>{$nombre.INPUT}</td>
      </tr>
      <tr>
          <td align='right'>{$fecha_str.LABEL}: <span  class="required">*</span></td>
          <td width="25%">{$fecha_ini.INPUT}&nbsp;{$fecha_ini.LABEL}</td>
          <td>{$fecha_fin.INPUT}&nbsp;{$fecha_fin.LABEL}</td>
      </tr>
      <tr height='10'>
          <td align='right' colspan='3'></td>
      </tr>
      <tr height='30'>
          <td align='right'>{$hora_str.LABEL}: <span  class="required">*</span></td>
          <td align='left' colspan='2'>{$hora_ini_HH.INPUT}&nbsp;:&nbsp;{$hora_ini_MM.INPUT}&nbsp;{$hora_ini_HH.LABEL}</td>
      </tr>
      <tr height='30'>
          <td>&nbsp;</td>
          <td align='left' colspan='2'>{$hora_fin_HH.INPUT}&nbsp;:&nbsp;{$hora_fin_MM.INPUT}&nbsp;{$hora_fin_HH.LABEL}</td>
      </tr>
      <tr height='10'>
          <td align='left' colspan='3'></td>
      </tr>
      <tr>
		<td align='right' valign='top'>
			{$formulario.LABEL}: <span  class="required">*</span>
			<br><br>
			<a href="?menu=form_designer">
			<b>{$label_manage_forms}</b>
			</a><br><br><hr>			
		</td>
          <td  colspan='2'>
{*
           {if $mode eq 'edit' or $mode eq 'input'}
                <table border='0' cellpadding='0' cellspacing='0'>
                    <tr>
                        <td rowspan='2'>{$formulario.INPUT}</td>
                        <td><input type='button' name='agregar_formulario' value="&gt;&gt;" onclick='add_form()'/></td>
                        <td width="50%" rowspan='2' >{$formularios_elegidos.INPUT}</td>
                        {if $label_manage_forms }
                        <td rowspan='2' align='right' valign='top'></td>
                        {/if}
                    </tr>
                    <tr>
                        <td><input type='button' name='quitar_formulario' value="&lt;&lt;" onclick='drop_form()'/></td>
                    </tr>
                </table>                
           {else}
               {$formulario.INPUT}
            {/if}
*}
            {$formulario.INPUT}            
            </td>
	  </tr>
      <tr height='30'>
		<td align='right'>{$queue.LABEL}: <span  class="required">*</span><br><br>
		<a href="?menu=pbxconfig&amp;display=queues">
		<b>{$label_manage_queues}</b></a><br><hr>
		</td>
		<td valign="top" colspan='2'>{$queue.INPUT}{if $label_manage_queues}&nbsp;{/if}</td>
      </tr>
      <tr>
        <td align='right' valign='top'>{$script.LABEL}: <span  class="required">*</span></td>
        <td  colspan='2'> 
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
</form>

{literal}
<script type="text/javascript">

/* Función para recoger todas las variables del formulario y procesarlas. Sólo
   se requiere atención especial para el RTF del script, y para la lista de 
   formularios elegidos. */
function submitForm() { 
    updateRTEs();   
    return true;
}


</script>
{/literal}
{$xajax_javascript}
{/if} {* $no_queues *}
