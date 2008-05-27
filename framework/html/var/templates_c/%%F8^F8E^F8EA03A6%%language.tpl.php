<?php /* Smarty version 2.6.14, created on 2008-05-23 06:03:59
         compiled from file:/var/www/html/modules/language/themes/default/language.tpl */ ?>
<form method="POST">
<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr class="moduleTitle">
  <td class="moduleTitle" valign="middle">&nbsp;&nbsp;<img src="images/1x1.gif" border="0" align="absmiddle">&nbsp;&nbsp;<?php echo $this->_tpl_vars['title']; ?>
</td>
</tr>
<tr>
  <td>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm">
      <tr>
	<td width="15%"><?php echo $this->_tpl_vars['language']['LABEL']; ?>
:</td>
	<td width="35%"><?php echo $this->_tpl_vars['language']['INPUT']; ?>
</td>
        <td>
        <?php if ($this->_tpl_vars['conectiondb']): ?>
        <input class="button" type="submit" name="save_language" value="<?php echo $this->_tpl_vars['CAMBIAR']; ?>
" >
        <?php else: ?>
        <?php echo $this->_tpl_vars['MSG_ERROR']; ?>

        <?php endif; ?>
        </td>
      </tr>
    </table>
  </td>
</tr>
</table>
</form>