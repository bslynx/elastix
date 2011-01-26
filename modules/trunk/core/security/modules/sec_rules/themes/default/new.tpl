
<table width="99%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="moduleTitle">
        <td class="moduleTitle" valign="middle">&nbsp;&nbsp;<img src="{$IMG}" border="0" align="absmiddle">&nbsp;&nbsp;{$title}</td>
        <td></td>
    </tr>
    <tr class="letra12">
        <td align="left"><input class="button" type="submit" name="save" value="{$SAVE}">&nbsp; <input class="button" type="submit" name="cancel" value="{$CANCEL}"></td>
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
    </tr>
</table>

<div class="tabForm" style="font-size: 16px; height: 350px" width="100%">
<br />
    <div id="ip_detail" >
        <fieldset class="fielform">
        <table style="font-size: 16px;" width="100%" cellspacing="0" cellpadding="8">
            <!--*****************************************-->
            <tr class="letra12">
                <td align="left" width="40%"><b>{$traffic_label}:</span></b></td>
                <td align="left">{$traffic_html}</td>
            </tr>
            <tr class="letra12" id="id_interface_in">
                <td align="left"><b>{$interface_in.LABEL}:</span></b></td>
                <td align="left">{$interface_in.INPUT}</td>
            </tr>
            <tr class="letra12" id="id_interface_out">
                <td align="left"><b>{$interface_out.LABEL}:</b></td>
                <td align="left">{$interface_out.INPUT}</td>
            </tr>
            <tr class="letra12" id="id_source">
                <td align="left"><b>{$ip_source.LABEL}:</b></td>
                <td align="left">{$ip_source.INPUT}&nbsp;/&nbsp;{$mask_source.INPUT}</td>
            </tr>
            <tr class="letra12" id="id_destin">
                <td align="left"><b>{$ip_destin.LABEL}:</b></td>
                <td align="left">{$ip_destin.INPUT}&nbsp;/&nbsp;{$mask_destin.INPUT}</td>
            </tr>
        </table>
        </fieldset>
    </div>
<br />
    <div id="protocol_detail">
        <fieldset class="fielform">
        <table style="font-size: 16px;" width="100%" cellspacing="0" cellpadding="8">
            <!--*****************************************-->
            <tr class="letra12">
                <td align="left" width="40%"><b>{$protocol_label}:</b></td>
                <td align="left">{$protocol_html}</td>
            </tr>
            <tr class="letra12" id="id_port_in">
                <td align="left"><b>{$port_in.LABEL}:</b></td>
                <td align="left">{$port_in.INPUT}</td>
            </tr>
            <tr class="letra12" id="id_port_out">
                <td align="left"><b>{$port_out.LABEL}:</b></td>
                <td align="left">{$port_out.INPUT}</td>
            </tr>
            <tr class="letra12" id="id_type_icmp">
                <td align="left"><b>{$type_icmp.LABEL}:</b></td>
                <td align="left">{$type_icmp.INPUT}</td>
            </tr>
            <tr class="letra12" id="id_id_ip">
                <td align="left"><b>{$id_ip.LABEL}:</b></td>
                <td align="left">{$id_ip.INPUT}</td>
            </tr>
            <tr class="letra12" id="id_established">
                <td align="left"><b>{$established.LABEL}:</b></td>
                <td align="left">{$established.INPUT}</td>
            </tr>
            <tr class="letra12" id="id_related">
                <td align="left"><b>{$related.LABEL}:</b></td>
                <td align="left">{$related.INPUT}</td>
            </tr>
        </table>
        </fieldset>
    </div>
<br />
    <div id="action_detail" >
        <fieldset class="fielform">
        <table style="font-size: 16px;" width="100%" cellspacing="0" cellpadding="8">
            <!--*****************************************-->
           <tr class="letra12" id="id_target">
                <td align="left" width="40%"><b>{$target.LABEL}:</b></td>
                <td align="left">{$target.INPUT}</td>
            </tr>
            <tr style = "display:none;" class="letra12" id="id">
                <td align="left"><b>{$id.LABEL}:</b></td>
                <td align="left">{$id.INPUT}</td>
            </tr>
            <tr style = "display:none;" class="letra12" id="state">
                <td align="left"><b>{$state.LABEL}:</b></td>
                <td align="left">{$state.INPUT}</td>
            </tr>
            <tr style = "display:none;" class="letra12" id="orden">
                <td align="left"><b>{$orden.LABEL}:</b></td>
                <td align="left">{$orden.INPUT}</td>
            </tr>
        </table>
        </fieldset>
    </div>
 </div>
