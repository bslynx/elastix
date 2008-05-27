<?php /* Smarty version 2.6.14, created on 2008-05-23 06:03:58
         compiled from file:/var/www/html/modules/load_module/themes/default/load_module.tpl */ ?>
<?php echo '
<script type="text/javascript">
function removeContent(d) 
{
    document.getElementById(d).style.display = "none";
}

function insertContent(d) 
{
    document.getElementById(d).style.display = "";
}

function show_form_menu()
{
  type_val = document.getElementById(\'SELECT_MENU\');
  indice = type_val.selectedIndex
  valor = type_val.options[indice].value
  if (valor == 0){
     removeContent(\'fila_extended1\');
     insertContent(\'fila_extended0\');
  }
  else{
     removeContent(\'fila_extended0\');
     insertContent(\'fila_extended1\');

  }
}
</script>
'; ?>

<form method="POST" enctype="multipart/form-data">
<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr class="moduleTitle">
  <td class="moduleTitle" valign="middle">&nbsp;&nbsp;<img src="images/1x1.gif" border="0" align="absmiddle">&nbsp;&nbsp;<?php echo $this->_tpl_vars['title']; ?>
</td>
</tr>
<tr>
  <td>
    <table width="100%" cellpadding="4" cellspacing="0" border="0">
      <tr>
        <td align="left">
          <?php if ($this->_tpl_vars['refresh']): ?>
          <input class="button" type="submit" name="refresh" value="<?php echo $this->_tpl_vars['REFRESH']; ?>
">
          <?php else: ?>
          <input class="button" type="submit" name="save" value="<?php echo $this->_tpl_vars['SAVE']; ?>
">
          <?php endif; ?>
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> <?php echo $this->_tpl_vars['REQUIRED_FIELD']; ?>
</span></td>
     </tr>
   </table>
  </td>
</tr>
<tr>
  <td>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm">
        <tr>
	<td><?php echo $this->_tpl_vars['label_module_file']; ?>
&nbsp;(module.tar.gz):<span  class="required">*</span></td>
	<td><input type='file' name='module_file'></td>
      </tr>          
      </table>
    </td>
  </tr>
</table>
</form>