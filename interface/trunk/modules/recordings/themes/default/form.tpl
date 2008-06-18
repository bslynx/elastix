<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
    <tr class="moduleTitle">
        <td class="moduleTitle" valign="middle">&nbsp;&nbsp;<img src="{$IMG}" border="0" align="absmiddle">&nbsp;&nbsp;{$TITLE}</td>
    </tr>
    <tr>
        <table class="tabForm" style="font-size: 16px;" width="100%" >
            <tr class="letra12">
                <td>
                    <input type="radio" name="option_record" id="record_by_phone" value="by_record" {$check_record} onclick="Activate_Option_Record()" />
                    {$record}
                </td>
                <td>
                    <input type="radio" name="option_record" id="record_by_file" value="by_file" {$check_file} onclick="Activate_Option_Record()" />
                    {$file_upload}
                </td>
            </tr>
            <tr class="letra12">
                <td width="15%" align="left"><b>{$recording_name_Label}</b></td>
                <td width="40%" align="left">
                    <input name="recording_name" id="recording_name" type="text" value="{$filename}" />[.gsm|.wav] <input class="button" type="submit" name="record" id="record" value="{$Record}" />
                </td>
                <td></td>
            </tr>
            <tr class="letra12">
                <td align="left"><b>{$record_Label}</b></td>
                <td align="left">
                    <input name="file_record" id="file_record" type="file" value="{$file_record_name}" size='30' />
                </td>
                <td></td>
            </tr>
            <tr class="letra12">
                <td></td>
                <td align="left"><input class="button" type="submit" name="save" value="{$SAVE}" /></td>
            </tr>
        </table>
    <tr>
</table>
<input type='hidden' name='filename' value='{$filename}' />

{literal}
    <script type="text/javascript">
        Activate_Option_Record();

        function Activate_Option_Record()
        {
            var record_by_phone = document.getElementById('record_by_phone');
            var record_by_file = document.getElementById('record_by_file');
            if(record_by_phone.checked==true)
            {
                document.getElementById('file_record').disabled = true;
                document.getElementById('recording_name').disabled = false;
                document.getElementById('record').disabled = false;
            }
            else
            {
                document.getElementById('file_record').disabled = false;
                document.getElementById('recording_name').disabled = true;
                document.getElementById('record').disabled = true;
            }
        }
    </script>
{/literal}