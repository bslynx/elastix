<?php /* Smarty version 2.6.14, created on 2008-05-05 05:05:13
         compiled from _common/login.tpl */ ?>
<html>
<head>
<title>Elastix - <?php echo $this->_tpl_vars['PAGE_NAME']; ?>
</title>
<!--<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">-->
<link rel="stylesheet" href="themes/al/styles.css">
</head>

<body bgcolor="#ffffff" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
  <table cellspacing=0 cellpadding=0 width="100%" border=0>
    <tr>
      <td>
        <table cellSpacing="0" cellPadding="0" width="100%" border="0">
          <tr>
            <td class="menulogo" width=380><img src="images/logo_elastix.png" width="233" height="75" /></td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
<form method="POST">
<p>&nbsp;</p>
<p>&nbsp;</p>
<table width="400" border="0" cellspacing="0" cellpadding="0" align="center">
  <tr>
    <td width="498" bgcolor="#688a02">
      <table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
        <tr>
          <td>
              <div align="left"><font color="#ffffff">&nbsp;&raquo;&nbsp;<?php echo $this->_tpl_vars['WELCOME']; ?>
</font></div>
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
            <div align="center"><?php echo $this->_tpl_vars['ENTER_USER_PASSWORD']; ?>
<br><br></div>
          </td>
        </tr>
        <tr>
          <td>
              <div align="right"><?php echo $this->_tpl_vars['USERNAME']; ?>
:</div>
          </td>
          <td>
            <input type="text" name="input_user" style="color:#000000; FONT-FAMILY: verdana, arial, helvetica, sans-serif; FONT-SIZE: 8pt;
             font-weight: none; text-decoration: none; background: #fbfeff; border: 1 solid #000000;">
          </td>
        </tr>
        <tr>
          <td>
              <div align="right"><?php echo $this->_tpl_vars['PASSWORD']; ?>
:</div>
          </td>
          <td>
            <input type="password" name="input_pass" style="color:#000000; FONT-FAMILY: verdana, arial, helvetica, sans-serif; FONT-SIZE: 8pt;
             font-weight: none; text-decoration: none; background: #fbfeff; border: 1 solid #000000;">
          </td>
        </tr>
        <tr>
          <td colspan="2" align="center">
            <input type="submit" name="submit_login" value="<?php echo $this->_tpl_vars['SUBMIT']; ?>
" class="button">
          </td>
        </tr>
        <tr>
            <td colspan="2"><img src="<?php echo $this->_tpl_vars['RUTA_IMG']; ?>
/0.gif" width="1" height="5"></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</form>
<br>
<div align="center" class="copyright">Copyright &copy; 2006 by <a href="http://www.palosanto.com">PaloSanto Solutions</a></div>
<br>
</body>
</html>