<script type="text/javascript" src ="/libs/js/jquery/js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src ="/libs/js/jquery/js/jquery-ui-1.7.2.custom.min.js"></script>

<link   rel ="stylesheet"      href="modules/{$module_name}/themes/style.css" />
<script type="text/javascript" src ="/modules/{$module_name}/themes/javascript.js"></script>

<table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="moduleTitle">
        <td class="moduleTitle" valign="middle" colspan='2'>&nbsp;&nbsp;<img src="{$IMG}" border="0" align="absmiddle">&nbsp;&nbsp;{$title}</td>
    </tr>
</table>

<div id="table_center" class="tabForm2">
    <div class="letra12">
        <b><br/>{$TITLE}</b>
    </div>
    <div id="content">
        <div class="divs">
            <table style="font-size: 26px;" width="75%" >
                <tr class="letra12">
                    <td align="left"><b>{$local.LABEL}: </b></td>
                    <td align="center"><b>{$server_ftp.LABEL}: </b></td>
                </tr>
            </table>
        </div>
        <div id="home">
            <div id="lef">
                <ul id="sortable1" class='droptrue'>{$LOCAL_LI}</ul>
            </div>
            <div id="med">
            </div>
            <div id="cen">
                <ul id="sortable2" class='droptrue2'>{$REMOTE_LI}</ul>
            </div>
            <div id="rig">
                <table style="font-size: 16px;" width="25%" >
                    <tr class="letra12">
                        <td align="left">
                            <input class="button" type="submit" name="save_new_FTP" value="{$SAVE}">&nbsp;&nbsp;
                            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
                        </td>
                    </tr>
                    <tr class="letra12">
                        <td align="left"><b>{$server.LABEL}: <span  class="required">*</span></b></td>
                    </tr>
                    <tr class="letra12">
                        <td align="left">{$server.INPUT}</td>
                    </tr>
                    <tr class="letra12">
                        <td align="left"><b>{$port.LABEL}: <span  class="required">*</span></b></td>
                    </tr>
                    <tr class="letra12">
                        <td align="left">{$port.INPUT}</td>
                    </tr>
                    <tr class="letra12">
                        <td align="left"><b>{$user.LABEL}: <span  class="required">*</span></b></td>
                    </tr>
                    <tr class="letra12">
                        <td align="left">{$user.INPUT}</td>
                    </tr>
                    <tr class="letra12">
                        <td align="left"><b>{$password.LABEL}: <span  class="required">*</span></b></td>
                    </tr>
                    <tr class="letra12">
                        <td align="left">{$password.INPUT}</td>
                    </tr>
                    <tr class="letra12">
                        <td align="left"><b>{$pathServer.LABEL}: <span  class="required">*</span></b></td>
                    </tr>
                    <tr class="letra12">
                        <td align="left">{$pathServer.INPUT}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
