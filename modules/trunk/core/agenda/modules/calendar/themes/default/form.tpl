
<table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="moduleTitle">
        <td class="moduleTitle" valign="middle" colspan='2'>&nbsp;&nbsp;<img src="{$IMG}" border="0" align="absmiddle">&nbsp;&nbsp;{$title}</td>
    </tr>
</table>

<table class="tabForm" style="font-size: 16px;" width="100%">
    <tr>
        <td align="left" valign="top" style="font-size:64%;">
            <div type="text" id="datepicker"></div>
            <div id="icals" >
                <a  href="index.php?menu={$module_name}&action=download_icals&rawmode=yes">
                    <span>{$Export_Calendar}</span>
                </a>
            </div>
        </td>
        <td align="right" width="90%" >
            <div id='calendar'></div>
        </td>
    </tr>
</table>
<div id="facebox_form">
</div>
<div id="box" style="display:none;">
    <div class="popup">
        <table>
            <tr>
                <td class="tl"/>
                <td class="b"/>
                <td class="tr"/>
            </tr>
            <tr>
                <td class="b"/>
                <td class="body">
                    <div class="content_box">
                        <div class='loading'>
                            <image src='modules/{$module_name}/images/loading.gif' />
                        </div>
                        <div id="table_box">
                            <table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
                                <tr class="moduleTitle">
                                    <td class="moduleTitle" valign="middle" colspan='2'>&nbsp;&nbsp;<img src="{$IMG}" border="0" align="absmiddle">&nbsp;&nbsp;<span id="title_box"></span></td>
                                </tr>
                                <tr class="letra12">
                                    <td align="left">
                                        <div id="new_box" style="display:none">
                                            <input id="save" class="button" type="submit" name="save_new" value="{$SAVE}">&nbsp;&nbsp;
                                            <input id="cancel" class="button" type="button" name="cancel" value="{$CANCEL}">
                                        </div>
                                        <div id="view_box" style="display:none">
                                            <input id="edit" class="button" type="button" name="edit" value="{$EDIT}">
                                            <input id="delete" class="button" type="button" name="delete" value="{$DELETE}">
                                            <input id="cancel" class="button" type="button" name="cancel" value="{$CANCEL}">
                                        </div>
                                        <div id="edit_box" style="display:none">
                                            <input id="save" class="button" type="submit" name="save_edit" value="{$SAVE}">&nbsp;&nbsp;
                                            <input id="cancel" class="button" type="button" name="cancel" value="{$CANCEL}">
                                        </div>
                                    </td>
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
                                                    <td align="left" width="20%"><b>{$Start_date}: <span  class="required">*</span></b></td>
                                                    <td align="left">
                                                        {$date.INPUT}
                                                    </td>
                                                    <td align="left"><b>{$Hour_ini}: <span  class="required">*</span></b></td>
                                                    <td align="left" width="23%">
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
                                                    <td align="left" class="repeat" style="{$visibility_repeat}">{$repeat.INPUT}&nbsp;<b id="type_repeat"></b></td>
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
                                                                {$add_phone}<a href="javascript: popup_phone_number('?menu={$module_name}&amp;action=phone_numbers&amp;rawmode=yes');"> Here</a>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <tr class="letra12">
                                                    <td align="left"><b>{$recording.LABEL}: <span  class="required">*</span></b></td>
                                                    <td align="left">{$recording.INPUT}&nbsp;&nbsp;
                                                        <div class="new_box_rec" style="display: inline;">
                                                            {$new_recording}
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </fieldset>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="left" colspan=2>
                                        <fieldset class="fielform">
                                            <legend class="sombreado">{$Notification_Alert}</legend>
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

                                                            </select>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr class="letra12" id="email_to" style="{$visibility_emails}">
                                                    <td align="center" colspan="2">
                                                        <table id="grilla" style="font-size: 16px;" width="90%" border="0">
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        </fieldset>
                                    </td>
                                </tr>
                            </table>

                            <input class="button" type="hidden" name="id" value="" id="id" />
                            <input class="button" type="hidden" name="id_event" value="" id="id_event" />
                            <input type="hidden" id="phone_type" name="phone_type" value="" />
                            <input type="hidden" id="phone_id" name="phone_id" value="" />
                            <input type="text" id="emails" name="emails" value="" style="display: none;" />
                        </div>
                    </div>
                    <div class="footer">
                        <a href="#" class="close_box">
                        <img src="modules/{$module_name}/images/closelabel.gif" title="close" class="close_image" />
                        </a>
                    </div>
                </td>
                <td class="b"/>
            </tr>
            <tr>
                <td class="bl"/>
                <td class="b"/>
                <td class="br"/>
            </tr>
        </table>
    </div>
</div>
{literal}
<script type="text/javascript">
$(document).ready(function() {
    //remove lupa
    $("#search").parent("div").remove();
});
</script>
{/literal}