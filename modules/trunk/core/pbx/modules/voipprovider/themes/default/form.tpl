<script type="text/javascript" src="/modules/voipprovider/themes/js/javascript.js"></script>

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
<table class="tabForm" style="font-size: 16px;" width="100%" >
    <tr>   
    <td>
    <table border="0" width="100%" cellspacing="0" cellpadding="8" style="border:1px solid black">
    <tr class="letra12">
        <td align="left"><b>{$type_provider_voip.LABEL}: <span  class="required">*</span></b></td>
        <td><select name="type_provider" onchange="displayConfig()" id="type_provider">
            <option value="none">[None]</option>
            <option value="net2phone">net2phone</option>
            <option value="to_camundanet">to_camundanet</option> 
            <option value="vitelity">vitelity</option> 
            <option value="NuFoneIAX">NuFoneIAX</option>
        </select></td>
    </tr>
    <tr class="letra12">
        <td align="left"><b>{$username.LABEL}: <span  class="required">*</span></b></td>
        <td align="left"><input type="text" id="username" value="" size="40" name="username"/></td>
    </tr>
    <tr class="letra12">
        <td align="left"><b>{$secret.LABEL}: <span  class="required">*</span></b></td>
        <td align="left"><input type="text" id="secret" value="" size="40" name="secret"/></td>
    </tr>

    <!--<tr class="letra12">
        <td align="left"><b>{$configuration.LABEL}: </b></td>
        <td><textarea cols="47" name="configuration" id="configuration" rows="6"></textarea></td>
    </tr>-->

    </table>
    </td>
    </tr>
    <div id="detail" style="display:none">
    <tr >
    <td>
    PEER Details
    <table border="0" width="100%" cellspacing="0" cellpadding="8" style="border:1px solid black">
    <div id="text_type" style="display:none"><tr class="letra12" >
        <td align="left"><b> {$type.LABEL}:</b></td>
        <td align="left"><input type="text" id="type" value="" size="40" name="type"/></td>
    </tr></div>

    <div id="text_qualify" style="display:none"><tr class="letra12">
        <td align="left"><b><label> {$qualify.LABEL}:</label></b></td>
        <td ><input type="text" id="qualify" value="" size="40" name="qualify"/></td>
    </tr></div>

    <div id="text_insecure" style="display:none"><tr class="letra12" >
        <td align="left"><b><label> {$insecure.LABEL}:</label></b></td>
        <td ><input type="text" id="insecure" value="" size="40" name="insecure"/></td>
    </tr></div>

    <div id="text_host" style="display:none"><tr class="letra12" >
        <td align="left"><b><label> {$host.LABEL}:</label></b></td>
        <td ><input type="text" id="host" value="" size="40" name="host"/></td>
    </tr></div>

    <div id="text_fromuser" style="display:none"><tr class="letra12" >
        <td align="left"><b><label> {$fromuser.LABEL}:</label></b></td>
        <td ><input type="text" id="fromuser" value="" size="40" name="fromuser"/></td>
    </tr></div>

    <div id="text_fromdomain" style="display:none"><tr class="letra12" >
        <td align="left"><b><label> {$fromdomain.LABEL}:</label></b></td>
        <td ><input type="text" id="fromdomain" value="" size="40" name="fromdomain"/></td>
    </tr></div>

    <div id="text_dtmfmode" style="display:none"><tr class="letra12" >
        <td align="left"><b><label> {$dtmfmode.LABEL}:</label></b></td>
        <td ><input type="text" id="dtmfmode" value="" size="40" name="dtmfmode"/></td>
    </tr></div>

    <div id="text_disallow" style="display:none"><tr class="letra12" >
        <td align="left"><b><label> {$disallow.LABEL}:</label></b></td>
        <td ><input type="text" id="disallow" value="" size="40" name="disallow"/></td>
    </tr></div>

    <div id="text_context" style="display:none"><tr class="letra12" >
        <td align="left"><b><label> {$context.LABEL}:</label></b></td>
        <td ><input type="text" id="context" value="" size="40" name="context"/></td>
    </tr></div>

    <div id="text_allow" style="display:none"><tr class="letra12" >
        <td align="left"><b><label> {$allow.LABEL}:</label></b></td>
        <td ><input type="text" id="allow" value="" size="40" name="allow"/></td>
    </tr></div>

    <div id="text_trustrpid" style="display:none"><tr class="letra12" >
        <td align="left"><b><label> {$trustrpid.LABEL}:</label></b></td>
        <td ><input type="text" id="trustrpid" value="" size="40" name="trustrpid"/></td>
    </tr></div>
    
    <div id="text_sendrpid" style="display:none"><tr class="letra12" >
        <td align="left"><b><label> {$sendrpid.LABEL}:</label></b></td>
        <td ><input type="text" id="sendrpid" value="" size="40" name="sendrpid"/></td>
    </tr></div>

    <div id="text_canreinvite" style="display:none"><tr class="letra12" >
        <td align="left"><b><label> {$canreinvite.LABEL}:</label></b></td>
        <td ><input type="text" id="canreinvite" value="" size="40" name="canreinvite"/></td>
    </tr></div>

    </table>
    </td>
    </tr>
    </div>
</table>
<input class="button" type="hidden" name="id" value="{$ID}" />