<script type="text/javascript" src ="/modules/realtime/themes/js/javascript.js"></script>
<link   rel ="stylesheet"      href="modules/realtime/themes/style.css" />

<table width="99%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="moduleTitle">
        <td class="moduleTitle" valign="middle">&nbsp;&nbsp;<img src="{$IMG}" border="0" align="absmiddle">&nbsp;&nbsp;{$title}</td>
        <td></td>
    </tr>
    <tr class="letra12">
        <td align="left">
            {if $Show}
            <input class="button" type="submit" name="save_iax" value="{$SAVE}">&nbsp;&nbsp;&nbsp;&nbsp;
            {elseif $Edit}
            <input class="button" type="submit" name="edit_iax" value="{$EDIT}">&nbsp;&nbsp;&nbsp;&nbsp;
            {elseif $Commit}
            <input class="button" type="submit" name="commit_iax" value="{$SAVE}">&nbsp;&nbsp;&nbsp;&nbsp;
            {/if}
            <input class="button" type="submit" name="cancel_iax" value="{$CANCEL}">
        </td>
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
    </tr>
</table>

<div id="extgeneral">
<fieldset id="fielform">
    <legend id="sombreado">Extension</legend>
        <table class="tabForm" style="font-size: 16px;" width="100%" >
            <tr class="letra12">
                <td align="left" width="50%"><b>{$name.LABEL}: <span class="required">*</span></b></td>
                <td align="left">{$name.INPUT}</td>
                <td align="left"><span id="text" onclick="showAllParameters();">Show All</span></td>
            </tr>
            <tr class="letra12">
                <td align="left" width="50%"><b>{$type.LABEL}: </b></td>
                <td align="left"><select id='type' name='type'>
                    {html_options options=$selec_type selected=$selected_type }
                </select></td>
            </tr>

            <tr class="letra12">
                <td align="left" width="50%"><b>{$context.LABEL}: <span  class="required">*</span></b></td>
                <td align="left">{$context.INPUT}</td>
            </tr>

            <tr class="letra12">
                <td align="left" width="50%"><b>{$user_name.LABEL}: <span  class="required">*</span></b></td>
                <td align="left">{$user_name.INPUT}</td>
            </tr>      
        </table>

    <div id="all" style="display:none">
        <table class="tabForm" style="font-size: 16px;" width="100%" >
            <tr class="letra12">
                <td align="left" width="50%"><b>{$dbsecret.LABEL}: </b></td>
                <td align="left">{$dbsecret.INPUT}</td>
                <td align="left"><span id="text" onclick="hideAllParameters();">Hide All</span></td>
            </tr>
            <tr class="letra12">
                <td align="left" width="50%"><b>{$notransfer.LABEL}: </b></td>
                <td align="left">{$notransfer.INPUT}</td>
            </tr>
            <tr class="letra12">
                <td align="left" width="50%"><b>{$inkeys.LABEL}: </b></td>
                <td align="left">{$inkeys.INPUT}</td>
            </tr>
            <tr class="letra12">
                <td align="left" width="50%"><b>{$outkey.LABEL}: </b></td>
                <td align="left">{$outkey.INPUT}</td>
            </tr>
            <tr class="letra12">
                <td align="left" width="50%"><b>{$auth.LABEL}: </b></td>
                <td align="left">{$auth.INPUT}</td>
            </tr>
            <tr class='letra12'>
                <td align="left" width="50%"><b>{$host.LABEL}: </b></td>
                <td align="left">{$host.INPUT}</td> 
            </tr>
            <tr class="letra12">
                <td align="left" width="50%"><b>{$accountcode.LABEL}: </b></td>
                <td align="left">{$accountcode.INPUT}</td>
            </tr>
            <tr class="letra12">
                <td align="left" width="50%"><b>{$amaflags.LABEL}: </b></td>
                <td align="left"><select id='amaflags' name='amaflags'>
                    {html_options options=$selec_amaflags selected=$selected_amaflags }
                </select></td>
            </tr>
            <tr class="letra12">
                <td align="left" width="50%"><b>{$caller_id.LABEL}: </b></td>
                <td align="left">{$caller_id.INPUT}</td>
            </tr>
            <tr class="letra12">
                <td align="left" width="50%"><b>{$default_tip.LABEL}: </b></td>
                <td align="left">{$default_tip.INPUT}</td>
            </tr>
            <tr class="letra12">
                <td align="left" width="50%"><b>{$language.LABEL}: </b></td>
                <td align="left">{$language.INPUT}</td>
            </tr>
            <tr class="letra12">
                <td align="left" width="50%"><b>{$mailbox.LABEL}: </b></td>
                <td align="left">{$mailbox.INPUT}</td>
            </tr>
            <tr class="letra12">
                <td align="left" width="50%"><b>{$md5secret.LABEL}: </b></td>
                <td align="left">{$md5secret.INPUT}</td>
            </tr>
            <tr class="letra12">
                <td align="left" width="50%"><b>{$deny.LABEL}: </b></td>
                <td align="left">{$deny.INPUT}</td>
            </tr>
            <tr class="letra12">
                <td align="left" width="50%"><b>{$permit.LABEL}: </b></td>
                <td align="left">{$permit.INPUT}</td>
            </tr>
            <tr class="letra12">
                <td align="left" width="50%"><b>{$qualify.LABEL}: </b></td>
                <td align="left">{$qualify.INPUT}</td>
            </tr>
            <tr class="letra12">
                <td align="left" width="50%"><b>{$disallow.LABEL}: </b></td>
                <td align="left">{$disallow.INPUT}</td>
            </tr>
            <tr class="letra12">
                <td align="left" width="50%"><b>{$allow.LABEL}: </b></td>
                <td align="left"><select id='allow' name='allow'>
                    {html_options options=$selec_allow selected=$selected_allow } 
                </select></td>
            </tr>
            <tr class="letra12">
                <td align="left" width="50%"><b>{$ipaddr.LABEL}: </b></td>
                <td align="left">{$ipaddr.INPUT}</td>
            </tr>
            <tr class="letra12">
                <td align="left" width="50%"><b>{$port.LABEL}: </b></td>
                <td align="left">{$port.INPUT}</td>
            </tr>
            <tr class="letra12">
                <td align="left" width="50%"><b>{$reg_seconds.LABEL}: </b></td>
                <td align="left">{$reg_seconds.INPUT}</td>
            </tr>
        </table>
    </div>
</fieldset>
</div>

<div id="extras">
<div id="extoptions">
<fieldset id="fielform">
<legend id="sombreado">Extension Options</legend>
    <table class="tabForm" style="font-size: 16px;" width="100%" >
        <tr class="letra12">
            <td align="left" width="50%"><b>{$ring_time.LABEL}: </b></td>
            <td align="left"> <select id='ring_time' name='ring_time'>
                    {html_options options=$selec_ring_time }
                </select> </td>
        </tr>
        <tr class="letra12">
            <td align="left" width="50%"><b>{$call_waiting.LABEL}: </b></td>
            <td align="left"><select id='call_waiting' name='call_waiting'>
                    {html_options options=$selec_call_waiting }
                </select> </td>
        </tr>
    </table>
</fieldset>
</div>

<div id="device">
<fieldset id="fielform">
<legend id="sombreado">Device Options</legend>
    <table class="tabForm" style="font-size: 16px;" width="100%" >
        <tr class="letra12">
            <td align="left" width="50%"><b>{$secret.LABEL}: <span class="required">*</span></b></td>
            <td align="left" >{$secret.INPUT}</td>
        </tr>
        
    </table>
</fieldset>
</div>

<div id="recording">
<fieldset id="fielform">
<legend id="sombreado">Recording Options</legend>
    <table class="tabForm" style="font-size: 16px;" width="100%" >
        <tr class="letra12">
            <td align="left" width="50%"><b>{$incoming.LABEL}: </b></td>
            <td align="left" ><select id='incoming' name='incoming'>
                    {html_options options=$selec_recording }
            </select> </td>
        </tr>
        <tr class="letra12">
            <td align="left" width="50%"><b>{$outgoing.LABEL}: </b></td>
            <td align="left" ><select id='outgoing' name='outgoing'>
                    {html_options options=$selec_recording }
            </select> </td>
        </tr>
    </table>
</fieldset>
</div>

<div id="voicemail">
<fieldset id="fielform">
<legend id="sombreado">Voicemail & Directory</legend>
    <table class="tabForm" style="font-size: 16px;" width="100%" >
        <tr class="letra12">
            <td align="left" width="50%"><b>{$status.LABEL}: </b></td>
            <td align="left" ><select id='incoming' name='incoming'>
                    {html_options options=$selec_status }
            </select> </td>
        </tr>
        <tr class="letra12">
            <td align="left" width="50%"><b>{$voicemailpassword.LABEL}: </b></td>
            <td align="left" >{$voicemailpassword.INPUT}</td>
        </tr>
        <tr class="letra12">
            <td align="left" width="50%"><b>{$emailaddress.LABEL}: </b></td>
            <td align="left" >{$emailaddress.INPUT}</td>
        </tr>
    </table>
</fieldset>
</div>
</div><!--End id extras -->
<input type="hidden" value="{$ID}" name="id" />