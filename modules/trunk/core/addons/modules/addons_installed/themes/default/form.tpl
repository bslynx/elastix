<table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="moduleTitle">
        <td class="moduleTitle" valign="middle" colspan='2'>&nbsp;&nbsp;<img src="{$IMG}" border="0" align="absmiddle">&nbsp;&nbsp;{$title}</td>
    </tr>
</table>
<table class="tabForm" style="font-size: 16px;" width="100%" >
    <tr class="letra12">
        <td>
            {if $SHOW_PROGRESS}
            <div id="events">
                <fieldset class="fieldform">
                    <legend class="sombreado">{$actual_progress}</legend>
                    {$divs_packages}
                </fieldset>
            </div>
            {/if}
        </td>
    </tr>
    <tr>
        <td>{$ADDONS_INSTALLED}</td>
    </tr>
</table>
<input class="button" type="hidden" id="status" name="status" value="none" />
<input type="hidden" id="installing" name="installing" value="none" />
<input type="hidden" id="data_text" name="data_text" value="none" />