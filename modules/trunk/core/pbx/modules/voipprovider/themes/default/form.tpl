
<table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="moduleTitle">
        <td class="moduleTitle" valign="middle" colspan='2'>&nbsp;&nbsp;<img src="{$IMG}" border="0" align="absmiddle">&nbsp;&nbsp;{$title}</td>
    </tr>
    <tr class="letra12">
        {if $mode eq 'input'}
        <td align="left">
            <input class="button" type="submit" name="save_new" value="{$SAVE}">&nbsp;&nbsp;
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        {elseif $mode eq 'view'}
        <td align="left">
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        {elseif $mode eq 'edit'}
        <td align="left">
            <input class="button" type="submit" name="save_edit" value="{$EDIT}">&nbsp;&nbsp;
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        {/if}
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
    </tr>
</table>

<div class="tabForm" style="font-size: 16px; height: 400px" width="100%">
    <div id="header_detail">
        <fieldset class="fielform">
            <legend class="sombreado">{$General_Setting}</legend>
            <table border="0" width="90%" cellspacing="0" cellpadding="8" >
                <tr class="letra12">
                    <td align="left"><b>{$type_provider_voip.LABEL}: <span  class="required">*</span></b></td>
                    <td>{$type_provider_voip.INPUT}</td>
                </tr>
				{if $mode eq 'edit'}
				<tr class="letra12">
                    <td align="left"><b>{$status.LABEL}: <span  class="required">*</span></b></td>
                    <td align="left">{$status.INPUT}</td>
                </tr>
				{/if}
                <tr class="letra12">
                    <td align="left"><b>{$account_name.LABEL}: <span  class="required">*</span></b></td>
                    <td align="left">{$account_name.INPUT}</td>
                </tr>
                <tr class="letra12">
                    <td align="left"><b>{$username.LABEL}: <span  class="required">*</span></b></td>
                    <td align="left">{$username.INPUT}</td>
                </tr>
                <tr class="letra12">
                    <td align="left"><b>{$secret.LABEL}: <span  class="required">*</span></b></td>
                    <td align="left">{$secret.INPUT}</td>
                </tr>
            </table>
        </fieldset>
    </div>

    <div id="detail">
        <fieldset class="fielform">
            <legend class="sombreado">{$PEER_Details}</legend>
            <table border="0" width="95%" id="formContainer" align="center" cellspacing="0" cellpadding="8">
                <tr class="letra12">
                    <td align="left"><b> {$type.LABEL}:</b><span  class="required">*</span></td>
                    <td align="left">{$type.INPUT}</td>
                </tr>
                <tr class="letra12">
                    <td align="left"><b><label> {$qualify.LABEL}:</label></b></td>
                    <td >{$qualify.INPUT}</td>
                </tr>
                <tr class="letra12">
                    <td align="left"><b><label> {$insecure.LABEL}:</label></b></td>
                    <td >{$insecure.INPUT}</td>
                </tr>
                <tr class="letra12" >
                    <td align="left"><b><label> {$host.LABEL}:</label><span class="required">*</span></b></td>
                    <td >{$host.INPUT}</td>
                </tr>
                <tr class="letra12" >
                    <td align="left"><b><label> {$fromuser.LABEL}:</label></b></td>
                    <td >{$fromuser.INPUT}</td>
                </tr>
                <tr class="letra12" >
                    <td align="left"><b><label> {$fromdomain.LABEL}:</label></b></td>
                    <td >{$fromdomain.INPUT}</td>
                </tr>
                <tr class="letra12" >
                    <td align="left"><b><label> {$dtmfmode.LABEL}:</label></b></td>
                    <td >{$dtmfmode.INPUT}</td>
                </tr>
                <tr class="letra12" >
                    <td align="left"><b><label> {$disallow.LABEL}:</label></b></td>
                    <td >{$disallow.INPUT}</td>
                </tr>
                <tr class="letra12" >
                    <td align="left"><b><label> {$context.LABEL}:</label><span class="required">*</span></b></td>
                    <td >{$context.INPUT}</td>
                </tr>
                <tr class="letra12" >
                    <td align="left"><b><label> {$allow.LABEL}:</label></b></td>
                    <td >{$allow.INPUT}</td>
                </tr>
                <tr class="letra12" >
                    <td align="left"><b><label> {$trustrpid.LABEL}:</label></b></td>
                    <td >{$trustrpid.INPUT}</td>
                </tr>
                <tr class="letra12" >
                    <td align="left"><b><label> {$sendrpid.LABEL}:</label></b></td>
                    <td >{$sendrpid.INPUT}</td>
                </tr>
                <tr class="letra12" >
                    <td align="left"><b><label> {$canreinvite.LABEL}:</label></b></td>
                    <td >{$canreinvite.INPUT}</td>
                </tr>
                <tr class="letra12" >
                    <td align="left"><b><label> {$technology.LABEL}:</label></b></td>
                    <td >{$technology.INPUT}</td>
                </tr>
            </table>
        </fieldset>
    </div>
</div>
<input class="button" type="hidden" name="id" value="{$ID}" />
<input class="button" type="hidden" id="module_name" name="module_name" value="{$Module_name}" />

{if $mode eq 'edit'}
<input class="button" type="hidden" name="editStatus" id="editStatus" value="on" />
{else}
<input class="button" type="hidden" name="editStatus" id="editStatus" value="off" />
{/if}