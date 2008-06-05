<input type='hidden' name='id' value='{$ID}'>
<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
    <tr class="moduleTitle">
        <td class="moduleTitle" valign="middle">&nbsp;&nbsp;<img src="images/list.png" border="0" align="absmiddle">&nbsp;&nbsp;{$TITLE}</td>
    </tr>
    <tr>
        <td align="left">
            {if $Show}
                <input class="button" type="submit" name="save" value="{$SAVE}">&nbsp;&nbsp;&nbsp;&nbsp;
            {elseif $Edit}
                <input class="button" type="submit" name="edit" value="{$EDIT}">&nbsp;&nbsp;&nbsp;&nbsp;
            {elseif $Commit}
                <input class="button" type="submit" name="commit" value="{$SAVE}">&nbsp;&nbsp;&nbsp;&nbsp;
            {/if}
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
    </tr>
    <tr>
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
    </tr>
    <tr>
        <table width="100%" cellpadding="4" cellspacing="0" border="0" class="tabForm">
            <tr>
                <td align="left" width="20%"><b>{$name.LABEL}: <span  class="required">*</span></b></td>
                <td class="required" align="left">{$name.INPUT}</td>
            </tr>
            <tr>
                <td align="left" width="20%"><b>{$last_name.LABEL}: <span  class="required">*</span></b></td>
                <td class="required" align="left">{$last_name.INPUT}</td>
            </tr>
            <tr>
                {if $Edit}
                    <td align="left" width="20%"><b>{$type_2.LABEL}: <span  class="required">*</span></b></td>
                    <td class="required" align="left">{$type_2.INPUT}</td>
                {else}
                    {$type}
                {/if}
            </tr>
            <tr id='tr_phone'>
                <td align="left" width="20%"><b>{$telefono.LABEL}: <span  class="required">*</span></b></td>
                <td class="required" align="left">{$telefono.INPUT}</td>
            </tr>
            <tr id='tr_extension'>
                <td align="left" width="20%"><b>{$extension.LABEL}: <span  class="required">*</span></b></td>
                <td class="required" align="left">{$extension.INPUT}</td>
            </tr>
            <tr>
                <td align="left"><b>{$email.LABEL}: </b></td>
                <td align="left">{$email.INPUT}</td>
            </tr>
        </table>
    </tr>
</table>
{literal}
    <script type="text/javascript">
        display_inputs();

        function display_inputs()
        {
            var select = document.getElementById('s_type');
            var valor = select.options[select.selectedIndex].value;

            var phone = document.getElementById('tr_phone');
            var span_ext = document.getElementById('span_ext');
            if(valor == "internal")
            {
                phone.setAttribute('style', 'display:none;');
                span_ext.setAttribute('style', 'display:display;');
            }
            else{
                phone.setAttribute('style', 'display:display;');
                span_ext.setAttribute('style', 'display:none;');
            }
        }
    </script>
{/literal}