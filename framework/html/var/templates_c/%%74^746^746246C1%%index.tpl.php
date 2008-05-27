<?php /* Smarty version 2.6.14, created on 2008-04-29 06:26:54
         compiled from _common/index.tpl */ ?>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF8" />
  <title>Elastix</title>
  <link rel="stylesheet" href="themes/<?php echo $this->_tpl_vars['THEMENAME']; ?>
/styles.css">
  <link rel="stylesheet" href="themes/<?php echo $this->_tpl_vars['THEMENAME']; ?>
/help.css">
  <script src="libs/js/base.js"></script>
  <script src="libs/js/iframe.js"></script>
  <?php echo $this->_tpl_vars['HEADER']; ?>

</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" <?php echo $this->_tpl_vars['BODYPARAMS']; ?>
  onbeforeunload="ConfirmarCierre()" onunload="ManejadorCierre()">
<?php echo $this->_tpl_vars['MENU']; ?>

<td align="left" valign="top"><?php if (! empty ( $this->_tpl_vars['mb_message'] )): ?>
<!-- Message board -->
<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" class="message_board">
  <tr>
    <td valign="middle" class="mb_title">&nbsp;<?php echo $this->_tpl_vars['mb_title']; ?>
</td>
  </tr>
  <tr>
    <td valign="middle" class="mb_message"><?php echo $this->_tpl_vars['mb_message']; ?>
</td>
  </tr>
</table><br>
<!-- end of Message board -->
<?php endif; ?>
<table border="0" cellpadding="6" width="100%">
  <tr>
    <td>
    <?php echo $this->_tpl_vars['CONTENT']; ?>

    </td>
  </tr>
</table>
<br>
<div align="center" class="copyright">Elastix is licensed under <a href="http://www.opensource.org/licenses/gpl-license.php" target='_blank'>GPL</a> by <a href="http://www.palosanto.com" target='_blank'>PaloSanto Solutions</a>. 2006, 2007.</div>
<br>
</td></tr></table>
</body>
</html>