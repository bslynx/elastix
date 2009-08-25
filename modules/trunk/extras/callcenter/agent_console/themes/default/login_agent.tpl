<script src="modules/{$MODULE_NAME}/libs/js/base.js">
</script>
{$SCRIPT_AJAX}
<form method="POST" name="frm_login_agent" action="/?menu={$MODULE_NAME}">
<p>&nbsp;</p>
<p>&nbsp;</p>
<table width="400" border="0" cellspacing="0" cellpadding="0" align="center">
  <tr>
    <td width="498"  class="menudescription">
      <table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
        <tr>
          <td>
              <div align="left"><font color="#ffffff">&nbsp;&raquo;&nbsp;{$WELCOME_AGENT}</font></div>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td width="498" bgcolor="#ffffff">
      <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tabForm">
        <tr>
          <td colspan="2">
            <div align="center">{$ENTER_USER_PASSWORD}<br><br></div>
          </td>
        </tr>
        <tr>
          <td colspan="2">
            <div align="center" height='1'><img id="reloj" src="images/1x1.gif" border="0" alt=""></div>
            <font color="#FF0000"><div id='mensaje' align="center"></div></font>
          </td>
        </tr>
        <tr>
          <td>
              <div align="right">{$USERNAME}:</div>
          </td>
          <td>
            <input type="text" id="input_agent_user" name="input_agent_user" style="color:#000000; FONT-FAMILY: verdana, arial, helvetica, sans-serif; FONT-SIZE: 8pt; font-weight: none; text-decoration: none; background: #fbfeff; border: 1 solid #000000;" value='{$agent_user_aux}'>
          </td>
        </tr>
        <tr>
          <td>
              <div align="right">{$EXTENSION}:</div>
          </td>
          <td>  <!--html_options options=$LIST_EXTENSIONS selected=$ID_EXTENSION!-->
               <select align="center" name="input_extension" id="input_extension">
                    {
                        html_options values=$EXT_VALUE output=$EXT_NAME selected=$ID_EXTENSION
                    }
                </select>
          </td>
        </tr>
        <tr>
          <td colspan="2" align="center">
            <input type="submit" name="submit_agent_login" value="{$SUBMIT}" class="botton" />
            <input type="hidden" name="status_login" id='status_login' value=0>
            <input type="hidden" name="pregunta_logoneo" id='pregunta_logoneo' value="1">
            <input type="hidden" name="error_igual_numero_agente" id='error_igual_numero_agente' value="0">
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</form>
{if $llamar_conectar_extension}
    <script type='text/javascript'>
        conectar_extension();
    </script>
{/if}
