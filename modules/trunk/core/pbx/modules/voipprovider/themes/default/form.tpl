
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
                    <td>
                        <select name="type_provider" onchange="displayConfig()" id="type_provider">
                        {html_options options=$arrProviders selected=$type_provider_tmp}
                        </select>
                    </td>
                </tr>
                <tr class="letra12">
                    <td align="left"><b>{$username.LABEL}: <span  class="required">*</span></b></td>
                    <td align="left"><input type="text" id="username" value="{$username_post}" size="40" name="username" /></td>
                </tr>
                <tr class="letra12">
                    <td align="left"><b>{$secret.LABEL}: <span  class="required">*</span></b></td>
                    <td align="left"><input type="text" id="secret" value="{$secret_post}" size="40" name="secret" /></td>
                </tr>
            </table>
        </fieldset>
    </div>

    <!--<div id="detail" style="display:none">-->
    <div id="detail">
        <fieldset class="fielform">
            <legend class="sombreado">{$PEER_Details}</legend>
            <table border="0" width="95%" id="formContainer" align="center" cellspacing="0" cellpadding="8">
                <div id="text_type" style="display:none">
                    <tr class="letra12">
                        <td align="left"><b> {$type.LABEL}:</b><span  class="required">*</span></td>
                        <td align="left"><input type="text" id="type" value="{$type_post}" size="30" name="type" /></td>
                    </tr>
                </div>
        
                <div id="text_qualify" style="display:none">
                    <tr class="letra12">
                        <td align="left"><b><label> {$qualify.LABEL}:</label></b></td>
                        <td ><input type="text" id="qualify" value="{$qualify_post}" size="30" name="qualify"/></td>
                    </tr>
                </div>
        
                <div id="text_insecure" style="display:none">
                    <tr class="letra12">
                        <td align="left"><b><label> {$insecure.LABEL}:</label></b></td>
                        <td ><input type="text" id="insecure" value="{$insecure_post}" size="30" name="insecure"/></td>
                    </tr>
                </div>
            
                <div id="text_host" style="display:none">
                    <tr class="letra12" >
                        <td align="left"><b><label> {$host.LABEL}:</label><span class="required">*</span></b></td>
                        <td ><input type="text" id="host" value="{$host_post}" size="30" name="host"/></td>
                    </tr>
                </div>
            
                <div id="text_fromuser" style="display:none">
                    <tr class="letra12" >
                        <td align="left"><b><label> {$fromuser.LABEL}:</label></b></td>
                        <td ><input type="text" id="fromuser" value="{$fromuser_post}" size="30" name="fromuser"/></td>
                    </tr>
                </div>
            
                <div id="text_fromdomain" style="display:none">
                    <tr class="letra12" >
                        <td align="left"><b><label> {$fromdomain.LABEL}:</label></b></td>
                        <td ><input type="text" id="fromdomain" value="{$fromdomain_post}" size="30" name="fromdomain"/></td>
                    </tr>
                </div>
            
                <div id="text_dtmfmode" style="display:none">
                    <tr class="letra12" >
                        <td align="left"><b><label> {$dtmfmode.LABEL}:</label></b></td>
                        <td ><input type="text" id="dtmfmode" value="{$dtmfmode_post}" size="30" name="dtmfmode"/></td>
                    </tr>
                </div>
            
                <div id="text_disallow" style="display:none">
                    <tr class="letra12" >
                        <td align="left"><b><label> {$disallow.LABEL}:</label></b></td>
                        <td ><input type="text" id="disallow" value="{$disallow_post}" size="30" name="disallow"/></td>
                    </tr>
                </div>
            
                <div id="text_context" style="display:none">
                    <tr class="letra12" >
                        <td align="left"><b><label> {$context.LABEL}:</label><span class="required">*</span></b></td>
                        <td ><input type="text" id="context" value="{$context_post}" size="30" name="context"/></td>
                    </tr>
                </div>
            
                <div id="text_allow" style="display:none">
                    <tr class="letra12" >
                        <td align="left"><b><label> {$allow.LABEL}:</label></b></td>
                        <td ><input type="text" id="allow" value="{$allow_post}" size="30" name="allow"/></td>
                    </tr>
                </div>
            
                <div id="text_trustrpid" style="display:none">
                    <tr class="letra12" >
                        <td align="left"><b><label> {$trustrpid.LABEL}:</label></b></td>
                        <td ><input type="text" id="trustrpid" value="{$trustrpid_post}" size="30" name="trustrpid"/></td>
                    </tr>
                </div>
                
                <div id="text_sendrpid" style="display:none">
                    <tr class="letra12" >
                        <td align="left"><b><label> {$sendrpid.LABEL}:</label></b></td>
                        <td ><input type="text" id="sendrpid" value="{$sendrpid_post}" size="30" name="sendrpid"/></td>
                    </tr>
                </div>
            
                <div id="text_canreinvite" style="display:none">
                    <tr class="letra12" >
                        <td align="left"><b><label> {$canreinvite.LABEL}:</label></b></td>
                        <td ><input type="text" id="canreinvite" value="{$canreinvite_post}" size="30" name="canreinvite"/></td>
                    </tr>
                </div>
            </table>
        </fieldset>
    </div>
 
</div>
<input class="button" type="hidden" name="id" value="{$ID}" />
