<?php /* Smarty version 2.6.14, created on 2008-04-29 06:26:57
         compiled from file:/var/www/html/modules/sysinfo/themes/default/sysinfo.tpl */ ?>
<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr class="moduleTitle">
  <td class="moduleTitle" valign="middle">&nbsp;&nbsp;<img src="images/memory.png" border="0" align="absmiddle">&nbsp;&nbsp;<?php echo $this->_tpl_vars['SYSTEM_INFO_TITLE1']; ?>
</td>
</tr>
<tr>
  <td>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm">
      <tr>
	<td width="15%"><?php echo $this->_tpl_vars['CPU_INFO_TITLE']; ?>
: </td>
	<td width="35%"><?php echo $this->_tpl_vars['cpu_info']; ?>
</td>
	<td colspan="2" rowspan="5" width="50%" align="left"><img src="images/plot.php?id_graph=1" border="0"></td>
      </tr>
      <tr>
	<td><?php echo $this->_tpl_vars['UPTIME_TITLE']; ?>
:</td>
	<td><?php echo $this->_tpl_vars['uptime']; ?>
</td>
      </tr>
      <tr>
	<td><?php echo $this->_tpl_vars['CPU_USAGE_TITLE']; ?>
:</td>
	<td><?php echo $this->_tpl_vars['cpu_usage']; ?>
</td>
      </tr>
      <tr>
	<td><?php echo $this->_tpl_vars['MEMORY_USAGE_TITLE']; ?>
:</td>
	<td><?php echo $this->_tpl_vars['mem_usage']; ?>
</td>
      </tr>
      <tr>
	<td><?php echo $this->_tpl_vars['SWAP_USAGE_TITLE']; ?>
:</td>
	<td><?php echo $this->_tpl_vars['swap_usage']; ?>
</td>
      </tr>
    </table>
  </td>
</tr>
</table>
<br>
<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr class="moduleTitle">
  <td class="moduleTitle" valign="middle">&nbsp;&nbsp;<img src="images/hd.png" border="0" align="absmiddle">&nbsp;&nbsp;<?php echo $this->_tpl_vars['SYSTEM_INFO_TITLE2']; ?>
</td>
</tr>
<tr>
  <td>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm">
      <?php $_from = $this->_tpl_vars['arrParticiones']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['particiones'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['particiones']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['particion']):
        $this->_foreach['particiones']['iteration']++;
?>
      <tr>
	<td width="15%"><img src="images/arrow-8.gif">&nbsp;<b><?php echo $this->_tpl_vars['PARTICION_NAME_TITLE']; ?>
:</b></td>
        <td width="35%"><b><?php echo $this->_tpl_vars['particion']['fichero']; ?>
</b></td>
        <td width="50%" rowspan="5" align="left"><img src='images/pie2.php?du=<?php echo $this->_tpl_vars['particion']['uso']; ?>
%' border='0'></td>
      </tr>
      <tr>
        <td width="15%">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $this->_tpl_vars['CAPACITY_TITLE']; ?>
:</td>
        <td width="35%"><?php echo $this->_tpl_vars['particion']['total_bloques']; ?>
GB</td>
      </tr>
      <tr>
        <td width="15%">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $this->_tpl_vars['USAGE_TITLE']; ?>
:</td>
        <td width="35%"><?php echo $this->_tpl_vars['particion']['uso']; ?>
%</td>
      </tr>
      <tr>
        <td width="15%">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $this->_tpl_vars['MOUNT_POINT_TITLE']; ?>
:</td>
        <td width="35%"><?php echo $this->_tpl_vars['particion']['punto_montaje']; ?>
</td>
      </tr>
      <tr>
        <td width="15%">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <td width="35%">&nbsp;</td>
      </tr>
      <?php endforeach; endif; unset($_from); ?>
    </table>
  </td>
</tr>
</table>