<script src="modules/{$MODULE_NAME}/libs/js/base.js"></script>
<table width="100%" cellpadding="1" cellspacing="1" height="100%" border=0>
    <tr class="moduleTitle">
        <td colspan="4" class="moduleTitle" align="left">
            <img src="images/list.png" border="0" align="absmiddle">&nbsp;&nbsp;{$MODULE_NAME}
        </td>
    </tr>

    <tr>
        <td>
            <form style='margin-bottom:0;' method="post" enctype="multipart/form-data">
            <table align='left' border=0 class="filterForm" cellspacing="0" cellpadding="0" width="100%">

                <tr>
                    <td class="letra12" width='60'><b>{$LABEL_MESSAGE}</b></td> 
                </tr>

                <tr>
                    <td class="letra12" width='60'>{$File}:</td>
                    <td colspan='2' align='left' width='15'><input name="{$NAME_FIELD_FILE}" type="file" size='45'  /></td>
                </tr>

                <tr>
                    <td colspan='4' align='left'>{$NAME_BUTTON}</td>
                </tr>

                <tr>
                    <td class="letra12" align='left'>&nbsp;</td>
                </tr>

                <tr>
                    <td class="letra12" align='left'><b>{$Format_File}:</b></td>
                </tr>
                
                <tr>
                    <td class="letra12" colspan='2' align='left'>"telefono","cedula/ruc","nombre","apellido"</td>
                </tr>

            </table>
            </form>
        </td>
    </tr>

</table>


