
<table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="moduleTitle">
        <td class="moduleTitle" valign="middle" colspan='2'>&nbsp;&nbsp;<img src="{$IMG}" border="0" align="absmiddle">&nbsp;&nbsp;{$title}</td>
    </tr>
    <tr class="letra12">
        {if $mode eq 'input'}
        <td align="left">
            <input id="save" class="button" type="submit" name="save_new" value="{$SAVE}">&nbsp;&nbsp;
            <input id="cancel" class="button" type="button" name="cancel" value="{$CANCEL}">
        </td>
        {elseif $mode eq 'view'}
        <td align="left">
            <input id="edit" class="button" type="submit" name="edit" value="{$EDIT}">
            <input id="delete" class="button" type="submit" name="delete" value="{$DELETE}">
            <input id="cancel" class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        {elseif $mode eq 'edit'}
        <td align="left">
            <input id="save" class="button" type="submit" name="save_edit" value="{$SAVE}">&nbsp;&nbsp;
            <input id="cancel" class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        {/if}
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
    </tr>
</table>

<table style="font-size: 16px;" width="99%" border="0">
    <tr>
        <td align="left" colspan=2>
            <fieldset class="fielform">
                <legend class="sombreado">{$New_Event}</legend>
                <table style="font-size: 16px;" width="99%" border="0">
                    <tr class="letra12">
                        <td align="left" width="23%"><b>{$event.LABEL}: <span  class="required">*</span></b></td>
                        <td align="left">{$event.INPUT}</td>
                    </tr>
                    <tr class="letra12">
                        <td align="left"><b>{$description.LABEL}: <span  class="required">*</span></b></td>
                        <td align="left">{$description.INPUT}</td>
                    </tr>
                </table>
            </fieldset>
        </td>
    </tr>
    <tr>
        <td align="left" colspan=2 width="50%">
            <fieldset class="fielform">
                <legend class="sombreado">{$Date_event}</legend>
                <table style="font-size: 16px;" width="99%" border="0">
                    <tr class="letra12">
                        <td align="left" width="23%"><b>{$Start_date}: <span  class="required">*</span></b></td>
                        <td align="left">
                            {$date.INPUT}
                        </td>
                        <td align="left" width="23%"><b>{$Hour_ini}: <span  class="required">*</span></b></td>
                        <td align="left">
                            {$hora1.INPUT}&nbsp;<b>:</b>&nbsp;{$minuto1.INPUT}
                        </td>
                    </tr>
                    <tr class="letra12">
                        <td align="left"><b>{$End_date}: <span  class="required">*</span></b></td>
                        <td align="left">
                            {$to.INPUT}
                        </td>
                        <td align="left"><b>{$Hour_end}: <span  class="required">*</span></b></td>
                        <td align="left"  width="21%">
                            {$hora2.INPUT}&nbsp;<b>:</b>&nbsp;{$minuto2.INPUT}
                        </td>
                    </tr>
                    <tr class="letra12">
                        <td align="left"><b>{$it_repeat.LABEL}: <span  class="required">*</span></b></td>
                        <td align="left">{$it_repeat.INPUT}</td>
                        <td align="left" class="repeat" style="{$visibility_repeat}"><b>{$repeat.LABEL}: <span  class="required">*</span></b></td>
                        <td align="left" class="repeat" style="{$visibility_repeat}">{$repeat.INPUT}&nbsp;<b id="type_repeat">{$repeat_date}</b></td>
                    </tr>
                    <tr class="repeat" style="{$visibility_repeat}">
                        <td align="left">&nbsp;&nbsp;</td>
                        <td align="left" colspan="2">
                            <table style="font-size: 16px;" width="99%" border="0" align="center">
                                <tr class="letra12">
                                    <td align="left" width="5%"><b>{$Su}</b></td>
                                    <td align="left" width="5%"><b>{$Mo}</b></td>
                                    <td align="left" width="5%"><b>{$Tu}</b></td>
                                    <td align="left" width="5%"><b>{$We}</b></td>
                                    <td align="left" width="5%"><b>{$Th}</b></td>
                                    <td align="left" width="5%"><b>{$Fr}</b></td>
                                    <td align="left" width="5%"><b>{$Sa}</b></td>
                                </tr>
                                <tr class="letra12">
                                    <td align="left" width="5%"><b>{$Sunday.INPUT}</b></td>
                                    <td align="left"><b>{$Monday.INPUT}</b></td>
                                    <td align="left"><b>{$Tuesday.INPUT}</b></td>
                                    <td align="left"><b>{$Wednesday.INPUT}</b></td>
                                    <td align="left"><b>{$Thursday.INPUT}</b></td>
                                    <td align="left"><b>{$Friday.INPUT}</b></td>
                                    <td align="left"><b>{$Saturday.INPUT}</b></td>
                                </tr>
                            </table>
                        </td>
                        <td align="left">&nbsp;&nbsp;</td>
                    </tr>
                </table>
            </fieldset>
        </td>
    </tr>
    <tr>
        <td align="left" colspan=2>
            <fieldset class="fielform">
                <legend class="sombreado">{$Call_alert}</legend>
                <table style="font-size: 16px;" width="99%" border="0">
                    <tr class="letra12">
                        <td align="left" width="23%"><b>{$asterisk_call_me.LABEL}: </b></td>
                        <td align="left" id="asterisk_call">{$asterisk_call_me.INPUT}</td>
                    </tr>
                    <tr class="letra12">
                        <td align="right" colspan="2"><div id="label_call"></td>
                    </tr>
                    <tr class="letra12" id="check">
                        <td align="left"><b>{$call_to.LABEL}: <span  class="required">*</span></b></td>
                        <td align="left">{$call_to.INPUT}&nbsp;&nbsp;
                            <span id="add_phone">
                                {if $mode eq 'input'}
                                    {$add_phone}<a href="javascript: popup_phone_number('modules/{$module_name}/phone_numbers.php');"> Here</a>
                                {/if}
                            </span>
                        </td>
                    </tr>
                    <tr class="letra12">
                        <td align="left"><b>{$recording.LABEL}: <span  class="required">*</span></b></td>
                        <td align="left">{$recording.INPUT}&nbsp;&nbsp;
                        {if $mode eq 'input'}
                            {$new_recording}
                        {/if}
                        </td>
                    </tr>
                </table>
            </fieldset>
        </td>
    </tr>
    <tr>
        <td align="left" colspan=2>
            <fieldset class="fielform">
                <legend class="sombreado">Notification Alert</legend>
                <table style="font-size: 16px;" width="99%" border="0">
                    <tr class="letra12">
                        <td align="left" width="23%"><b>{$notification.LABEL}: </b></td>
                        <td align="left" id="noti">{$notification.INPUT}</td>
                    </tr>
                    <tr class="letra12" id="notification_email" style="{$visibility}">
                        <td align="left"><b id="notification_email_label">{$notification_email}: <span  class="required">*</span></b></td>
                        <td align="left">
                            <div>
                                <select id="select2" name="select2">
                                    {$options_emails}
                                </select>
                            </div>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </td>
    </tr>
</table>

<input class="button" type="hidden" name="id" value="{$ID}" id="id" />
<input class="button" type="hidden" name="id_event" value="{$id_event}" id="id_event" />
<input type="hidden" id="phone_type" name="phone_type" value="" />
<input type="hidden" id="phone_id" name="phone_id" value="" />
<input type="text" id="emails" name="emails" value="" style="display: none;" />
