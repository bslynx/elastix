{$xajax_javascript}
<script language="JavaScript" type="text/javascript" src="{$relative_dir_rich_text}/richtext/html2xhtml.js"></script>
<script language="JavaScript" type="text/javascript" src="{$relative_dir_rich_text}/richtext/richtext_compressed.js"></script>
<script language="JavaScript" type="text/javascript">
//Usage: initRTE(imagesPath, includesPath, cssFile, genXHTML, encHTML)
initRTE("./{$relative_dir_rich_text}/richtext/images/", "./{$relative_dir_rich_text}/richtext/", "", true);
var rte_script = new richTextEditor('rte_script');
</script>




<form method="POST" enctype="multipart/form-data" onsubmit="return submitForm();">
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
                <input class="button" type="submit" name="save"   value="{$SAVE}" >
                <input class="button" type="submit" name="cancel" value="{$CANCEL}"></td>
               
            {elseif $mode eq 'edit'}
                <input class="button" type="submit" name="apply_changes" value="{$APPLY_CHANGES}" >
                <input class="button" type="submit" name="cancel" value="{$CANCEL}"></td>
            {else}
                <input class="button" type="submit" name="edit" value="{$EDIT}">

                <input class="button" type="button" name="desactivar" value="{$DESACTIVATE}"  onClick="if(confirmSubmit('{$CONFIRM_CONTINUE}'))desactivar_queue();">        
       
                <input class="button" type="button" name="cancel_view" value="{$CANCEL}" onclick="window.open('?menu=queues','_parent');"></td>
               
            {/if}
        
        </tr>
        </table>
    </td>
</tr>

<tr>
    <td>
        <table width="100%" border="1" cellspacing="0" cellpadding="0" class="tabForm">
        {if $mode eq 'input'}
        <tr>
            <td>{$LABEL_SELECT}: <span  class="required">*</span></td>
            <td><select name='select_queue'>{$INPUT_SELECT}</select></td>
        </tr>
        {else}
        <tr>
            <td>{$QUEUE} <span  class="required">*</span></td>
            <td>{$LABEL_SELECTED}</td>
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
<input type="hidden" name="id_queue" id='id_queue' value="{$id_queue}" />
<input type="hidden" name="queue"    id='queue'    value="{$queue}"    />
<input type="hidden" name="estado" id='estado' value="{$estatus_cbo_estado}">
</form>

{literal}
<script type="text/javascript">

    function desactivar_queue()
    {
        var id_queue = document.getElementById("id_queue").value;
        xajax_desactivar_queue(id_queue);
    }

    function submitForm() {	
	updateRTEs();	
	return true;
    }

</script>
{/literal}

