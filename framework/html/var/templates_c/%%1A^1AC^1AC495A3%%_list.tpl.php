<?php /* Smarty version 2.6.14, created on 2008-05-23 06:03:57
         compiled from _common/_list.tpl */ ?>
<table width="<?php echo $this->_tpl_vars['width']; ?>
" align="center" border="0" cellpadding="0" cellspacing="0">
  <tr class="moduleTitle">
    <td class="moduleTitle" valign="middle">&nbsp;&nbsp;<img src="<?php echo $this->_tpl_vars['icon']; ?>
" border="0" align="absmiddle">&nbsp;&nbsp;<?php echo $this->_tpl_vars['title']; ?>
</td>
  </tr>
  <?php if (! empty ( $this->_tpl_vars['contentFilter'] )): ?>
  <tr>
    <td><table width="100%" border="0" cellspacing="0" cellpadding="0" class="filterForm"><tr><td><?php echo $this->_tpl_vars['contentFilter']; ?>
</td></tr></table>
    </td>
  </tr>
  <?php endif; ?>
  <tr>
    <td>
      <table class="table_data" align="center" cellspacing="0" cellpadding="0" width="100%">
        <tr class="table_navigation_row">
          <td colspan="<?php echo $this->_tpl_vars['numColumns']; ?>
" class="table_navigation_row">
            <table border="0" cellpadding="0" cellspacing="0" width="100%" class="table_navigation_text">
              <tr>
                <td align="left">&nbsp;<?php if ($this->_tpl_vars['enableExport'] == true): ?><a href="<?php echo $this->_tpl_vars['url']; ?>
&exportcsv=yes&rawmode=yes"><img src="/images/export.gif" border="0">&nbsp;<font class="letranodec"><?php echo $this->_tpl_vars['lblExport']; ?>
</font></a><?php endif; ?></td>
                <td align="right"> 
                  <?php if ($this->_tpl_vars['start'] <= 1): ?>
                   <img
                   src='/images/start_off.gif' alt='<?php echo $this->_tpl_vars['lblStart']; ?>
' align='absmiddle'
                   border='0' width='13' height='11'>&nbsp;<?php echo $this->_tpl_vars['lblStart']; ?>
&nbsp;&nbsp;<img 
                   src='/images/previous_off.gif' alt='<?php echo $this->_tpl_vars['lblPrevious']; ?>
' align='absmiddle' border='0' width='8' height='11'>
                  <?php else: ?>
                    <?php if ($this->_tpl_vars['withAjax'] == 1): ?>
                        <a href="javascript:void(0);" onclick="javascript:<?php echo $this->_tpl_vars['functionName']; ?>
('start',<?php echo $this->_tpl_vars['start']; ?>
)"
                    <?php else: ?>
                        <a href="<?php echo $this->_tpl_vars['url']; ?>
&nav=start&start=<?php echo $this->_tpl_vars['start']; ?>
">
                    <?php endif; ?>
                   <img
                   src='/images/start.gif' alt='<?php echo $this->_tpl_vars['lblStart']; ?>
' align='absmiddle'
                   border='0' width='13' height='11'></a>&nbsp;<?php echo $this->_tpl_vars['lblStart']; ?>
&nbsp;&nbsp;
                    <?php if ($this->_tpl_vars['withAjax'] == 1): ?>
                        <a href="javascript:void(0);" onclick="javascript:<?php echo $this->_tpl_vars['functionName']; ?>
('previous',<?php echo $this->_tpl_vars['start']; ?>
)"
                    <?php else: ?>
                        <a href="<?php echo $this->_tpl_vars['url']; ?>
&nav=previous&start=<?php echo $this->_tpl_vars['start']; ?>
">
                    <?php endif; ?>
                   <img 
                   src='/images/previous.gif' alt='<?php echo $this->_tpl_vars['lblPrevious']; ?>
' align='absmiddle' border='0' width='8' height='11'></a>
                  <?php endif; ?>
                  &nbsp;<?php echo $this->_tpl_vars['lblPrevious']; ?>
&nbsp;<span 
                  class='pageNumbers'>(<?php echo $this->_tpl_vars['start']; ?>
 - <?php echo $this->_tpl_vars['end']; ?>
 of <?php echo $this->_tpl_vars['total']; ?>
)</span>&nbsp;<?php echo $this->_tpl_vars['lblNext']; ?>
&nbsp;
                  <?php if ($this->_tpl_vars['end'] == $this->_tpl_vars['total']): ?>
                   <img 
                   src='/images/next_off.gif'
                   alt='<?php echo $this->_tpl_vars['lblNext']; ?>
' align='absmiddle' border='0' width='8' height='11'>&nbsp;<?php echo $this->_tpl_vars['lblEnd']; ?>
&nbsp;<img 
                   src='/images/end_off.gif' alt='<?php echo $this->_tpl_vars['lblEnd']; ?>
' align='absmiddle' border='0' width='13' height='11'>
                  <?php else: ?>
                    <?php if ($this->_tpl_vars['withAjax'] == 1): ?>
                        <a href="javascript:void(0);" onclick="javascript:<?php echo $this->_tpl_vars['functionName']; ?>
('next','<?php echo $this->_tpl_vars['start']; ?>
')"
                    <?php else: ?>
                        <a href="<?php echo $this->_tpl_vars['url']; ?>
&nav=next&start=<?php echo $this->_tpl_vars['start']; ?>
">
                    <?php endif; ?>
                   <img
                   src='/images/next.gif' 
                   alt='<?php echo $this->_tpl_vars['lblNext']; ?>
' align='absmiddle' border='0' width='8' height='11'></a>&nbsp;<?php echo $this->_tpl_vars['lblEnd']; ?>
&nbsp;
                    <?php if ($this->_tpl_vars['withAjax'] == 1): ?>
                        <a href="javascript:void(0);" onclick="javascript:<?php echo $this->_tpl_vars['functionName']; ?>
('end',<?php echo $this->_tpl_vars['start']; ?>
)"
                    <?php else: ?>
                        <a href="<?php echo $this->_tpl_vars['url']; ?>
&nav=end&start=<?php echo $this->_tpl_vars['start']; ?>
">
                    <?php endif; ?>
                   <img 
                   src='/images/end.gif' alt='<?php echo $this->_tpl_vars['lblEnd']; ?>
' align='absmiddle' border='0' width='13' height='11'></a>
                  <?php endif; ?>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr class="table_title_row">
          <?php unset($this->_sections['columnNum']);
$this->_sections['columnNum']['name'] = 'columnNum';
$this->_sections['columnNum']['loop'] = is_array($_loop=$this->_tpl_vars['numColumns']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['columnNum']['start'] = (int)0;
$this->_sections['columnNum']['step'] = ((int)1) == 0 ? 1 : (int)1;
$this->_sections['columnNum']['show'] = true;
$this->_sections['columnNum']['max'] = $this->_sections['columnNum']['loop'];
if ($this->_sections['columnNum']['start'] < 0)
    $this->_sections['columnNum']['start'] = max($this->_sections['columnNum']['step'] > 0 ? 0 : -1, $this->_sections['columnNum']['loop'] + $this->_sections['columnNum']['start']);
else
    $this->_sections['columnNum']['start'] = min($this->_sections['columnNum']['start'], $this->_sections['columnNum']['step'] > 0 ? $this->_sections['columnNum']['loop'] : $this->_sections['columnNum']['loop']-1);
if ($this->_sections['columnNum']['show']) {
    $this->_sections['columnNum']['total'] = min(ceil(($this->_sections['columnNum']['step'] > 0 ? $this->_sections['columnNum']['loop'] - $this->_sections['columnNum']['start'] : $this->_sections['columnNum']['start']+1)/abs($this->_sections['columnNum']['step'])), $this->_sections['columnNum']['max']);
    if ($this->_sections['columnNum']['total'] == 0)
        $this->_sections['columnNum']['show'] = false;
} else
    $this->_sections['columnNum']['total'] = 0;
if ($this->_sections['columnNum']['show']):

            for ($this->_sections['columnNum']['index'] = $this->_sections['columnNum']['start'], $this->_sections['columnNum']['iteration'] = 1;
                 $this->_sections['columnNum']['iteration'] <= $this->_sections['columnNum']['total'];
                 $this->_sections['columnNum']['index'] += $this->_sections['columnNum']['step'], $this->_sections['columnNum']['iteration']++):
$this->_sections['columnNum']['rownum'] = $this->_sections['columnNum']['iteration'];
$this->_sections['columnNum']['index_prev'] = $this->_sections['columnNum']['index'] - $this->_sections['columnNum']['step'];
$this->_sections['columnNum']['index_next'] = $this->_sections['columnNum']['index'] + $this->_sections['columnNum']['step'];
$this->_sections['columnNum']['first']      = ($this->_sections['columnNum']['iteration'] == 1);
$this->_sections['columnNum']['last']       = ($this->_sections['columnNum']['iteration'] == $this->_sections['columnNum']['total']);
?>
          <td class="table_title_row"><?php echo $this->_tpl_vars['header'][$this->_sections['columnNum']['index']]['name']; ?>
&nbsp;</td>
          <?php endfor; endif; ?>
        </tr>
        <?php $_from = $this->_tpl_vars['arrData']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['filas'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['filas']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['data']):
        $this->_foreach['filas']['iteration']++;
?>
        <tr onMouseOver="this.style.backgroundColor='#f2f2f2';" onMouseOut="this.style.backgroundColor='#ffffff';">
          <?php if (($this->_foreach['filas']['iteration'] == $this->_foreach['filas']['total'])): ?>
            <?php unset($this->_sections['columnNum']);
$this->_sections['columnNum']['name'] = 'columnNum';
$this->_sections['columnNum']['loop'] = is_array($_loop=$this->_tpl_vars['numColumns']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['columnNum']['start'] = (int)0;
$this->_sections['columnNum']['step'] = ((int)1) == 0 ? 1 : (int)1;
$this->_sections['columnNum']['show'] = true;
$this->_sections['columnNum']['max'] = $this->_sections['columnNum']['loop'];
if ($this->_sections['columnNum']['start'] < 0)
    $this->_sections['columnNum']['start'] = max($this->_sections['columnNum']['step'] > 0 ? 0 : -1, $this->_sections['columnNum']['loop'] + $this->_sections['columnNum']['start']);
else
    $this->_sections['columnNum']['start'] = min($this->_sections['columnNum']['start'], $this->_sections['columnNum']['step'] > 0 ? $this->_sections['columnNum']['loop'] : $this->_sections['columnNum']['loop']-1);
if ($this->_sections['columnNum']['show']) {
    $this->_sections['columnNum']['total'] = min(ceil(($this->_sections['columnNum']['step'] > 0 ? $this->_sections['columnNum']['loop'] - $this->_sections['columnNum']['start'] : $this->_sections['columnNum']['start']+1)/abs($this->_sections['columnNum']['step'])), $this->_sections['columnNum']['max']);
    if ($this->_sections['columnNum']['total'] == 0)
        $this->_sections['columnNum']['show'] = false;
} else
    $this->_sections['columnNum']['total'] = 0;
if ($this->_sections['columnNum']['show']):

            for ($this->_sections['columnNum']['index'] = $this->_sections['columnNum']['start'], $this->_sections['columnNum']['iteration'] = 1;
                 $this->_sections['columnNum']['iteration'] <= $this->_sections['columnNum']['total'];
                 $this->_sections['columnNum']['index'] += $this->_sections['columnNum']['step'], $this->_sections['columnNum']['iteration']++):
$this->_sections['columnNum']['rownum'] = $this->_sections['columnNum']['iteration'];
$this->_sections['columnNum']['index_prev'] = $this->_sections['columnNum']['index'] - $this->_sections['columnNum']['step'];
$this->_sections['columnNum']['index_next'] = $this->_sections['columnNum']['index'] + $this->_sections['columnNum']['step'];
$this->_sections['columnNum']['first']      = ($this->_sections['columnNum']['iteration'] == 1);
$this->_sections['columnNum']['last']       = ($this->_sections['columnNum']['iteration'] == $this->_sections['columnNum']['total']);
?>
            <td class="table_data_last_row"><?php if ($this->_tpl_vars['data'][$this->_sections['columnNum']['index']] == ''): ?>&nbsp;<?php endif;  echo $this->_tpl_vars['data'][$this->_sections['columnNum']['index']]; ?>
</td>
            <?php endfor; endif; ?>
          <?php else: ?>
            <?php unset($this->_sections['columnNum']);
$this->_sections['columnNum']['name'] = 'columnNum';
$this->_sections['columnNum']['loop'] = is_array($_loop=$this->_tpl_vars['numColumns']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['columnNum']['start'] = (int)0;
$this->_sections['columnNum']['step'] = ((int)1) == 0 ? 1 : (int)1;
$this->_sections['columnNum']['show'] = true;
$this->_sections['columnNum']['max'] = $this->_sections['columnNum']['loop'];
if ($this->_sections['columnNum']['start'] < 0)
    $this->_sections['columnNum']['start'] = max($this->_sections['columnNum']['step'] > 0 ? 0 : -1, $this->_sections['columnNum']['loop'] + $this->_sections['columnNum']['start']);
else
    $this->_sections['columnNum']['start'] = min($this->_sections['columnNum']['start'], $this->_sections['columnNum']['step'] > 0 ? $this->_sections['columnNum']['loop'] : $this->_sections['columnNum']['loop']-1);
if ($this->_sections['columnNum']['show']) {
    $this->_sections['columnNum']['total'] = min(ceil(($this->_sections['columnNum']['step'] > 0 ? $this->_sections['columnNum']['loop'] - $this->_sections['columnNum']['start'] : $this->_sections['columnNum']['start']+1)/abs($this->_sections['columnNum']['step'])), $this->_sections['columnNum']['max']);
    if ($this->_sections['columnNum']['total'] == 0)
        $this->_sections['columnNum']['show'] = false;
} else
    $this->_sections['columnNum']['total'] = 0;
if ($this->_sections['columnNum']['show']):

            for ($this->_sections['columnNum']['index'] = $this->_sections['columnNum']['start'], $this->_sections['columnNum']['iteration'] = 1;
                 $this->_sections['columnNum']['iteration'] <= $this->_sections['columnNum']['total'];
                 $this->_sections['columnNum']['index'] += $this->_sections['columnNum']['step'], $this->_sections['columnNum']['iteration']++):
$this->_sections['columnNum']['rownum'] = $this->_sections['columnNum']['iteration'];
$this->_sections['columnNum']['index_prev'] = $this->_sections['columnNum']['index'] - $this->_sections['columnNum']['step'];
$this->_sections['columnNum']['index_next'] = $this->_sections['columnNum']['index'] + $this->_sections['columnNum']['step'];
$this->_sections['columnNum']['first']      = ($this->_sections['columnNum']['iteration'] == 1);
$this->_sections['columnNum']['last']       = ($this->_sections['columnNum']['iteration'] == $this->_sections['columnNum']['total']);
?>
            <td class="table_data"><?php if ($this->_tpl_vars['data'][$this->_sections['columnNum']['index']] == ''): ?>&nbsp;<?php endif;  echo $this->_tpl_vars['data'][$this->_sections['columnNum']['index']]; ?>
</td>
            <?php endfor; endif; ?>
          <?php endif; ?>
        </tr>
        <?php endforeach; endif; unset($_from); ?>
        <tr class="table_navigation_row">
          <td colspan="<?php echo $this->_tpl_vars['numColumns']; ?>
" class="table_navigation_row">
            <table border="0" cellpadding="0" cellspacing="0" width="100%" class="table_navigation_text">
              <tr>
                <td align="left">&nbsp;</td>
                <td align="right">
                  <?php if ($this->_tpl_vars['start'] <= 1): ?>
                   <img
                   src='/images/start_off.gif' alt='<?php echo $this->_tpl_vars['lblStart']; ?>
' align='absmiddle'
                   border='0' width='13' height='11'>&nbsp;<?php echo $this->_tpl_vars['lblStart']; ?>
&nbsp;&nbsp;<img
                   src='/images/previous_off.gif' alt='<?php echo $this->_tpl_vars['lblPrevious']; ?>
' align='absmiddle' border='0' width='8' height='11'>
                  <?php else: ?>
                   <?php if ($this->_tpl_vars['withAjax'] == 1): ?>
                        <a href="javascript:void(0);" onclick="javascript:<?php echo $this->_tpl_vars['functionName']; ?>
('start',<?php echo $this->_tpl_vars['start']; ?>
)"
                    <?php else: ?>
                        <a href="<?php echo $this->_tpl_vars['url']; ?>
&nav=start&start=<?php echo $this->_tpl_vars['start']; ?>
">
                    <?php endif; ?>
                   <img
                   src='/images/start.gif' alt='<?php echo $this->_tpl_vars['lblStart']; ?>
' align='absmiddle'
                   border='0' width='13' height='11'></a>&nbsp;<?php echo $this->_tpl_vars['lblStart']; ?>
&nbsp;&nbsp;
                    <?php if ($this->_tpl_vars['withAjax'] == 1): ?>
                        <a href="javascript:void(0);" onclick="javascript:<?php echo $this->_tpl_vars['functionName']; ?>
('previous',<?php echo $this->_tpl_vars['start']; ?>
)"
                    <?php else: ?>
                        <a href="<?php echo $this->_tpl_vars['url']; ?>
&nav=previous&start=<?php echo $this->_tpl_vars['start']; ?>
">
                    <?php endif; ?>
                   <img
                   src='/images/previous.gif' alt='<?php echo $this->_tpl_vars['lblPrevious']; ?>
' align='absmiddle' border='0' width='8' height='11'></a>
                  <?php endif; ?>
                  &nbsp;<?php echo $this->_tpl_vars['lblPrevious']; ?>
&nbsp;<span
                  class='pageNumbers'>(<?php echo $this->_tpl_vars['start']; ?>
 - <?php echo $this->_tpl_vars['end']; ?>
 of <?php echo $this->_tpl_vars['total']; ?>
)</span>&nbsp;<?php echo $this->_tpl_vars['lblNext']; ?>
&nbsp;
                  <?php if ($this->_tpl_vars['end'] == $this->_tpl_vars['total']): ?>
                   <img
                   src='/images/next_off.gif'
                   alt='<?php echo $this->_tpl_vars['lblNext']; ?>
' align='absmiddle' border='0' width='8' height='11'>&nbsp;<?php echo $this->_tpl_vars['lblEnd']; ?>
&nbsp;<img
                   src='/images/end_off.gif' alt='<?php echo $this->_tpl_vars['lblEnd']; ?>
' align='absmiddle' border='0' width='13' height='11'>
                  <?php else: ?>
                   <?php if ($this->_tpl_vars['withAjax'] == 1): ?>
                        <a href="javascript:void(0);" onclick="javascript:<?php echo $this->_tpl_vars['functionName']; ?>
('next','<?php echo $this->_tpl_vars['start']; ?>
')"
                    <?php else: ?>
                        <a href="<?php echo $this->_tpl_vars['url']; ?>
&nav=next&start=<?php echo $this->_tpl_vars['start']; ?>
">
                    <?php endif; ?>
                   <img
                   src='/images/next.gif'
                   alt='<?php echo $this->_tpl_vars['lblNext']; ?>
' align='absmiddle' border='0' width='8' height='11'></a>&nbsp;<?php echo $this->_tpl_vars['lblEnd']; ?>
&nbsp;
                    <?php if ($this->_tpl_vars['withAjax'] == 1): ?>
                        <a href="javascript:void(0);" onclick="javascript:<?php echo $this->_tpl_vars['functionName']; ?>
('end',<?php echo $this->_tpl_vars['start']; ?>
)"
                    <?php else: ?>
                        <a href="<?php echo $this->_tpl_vars['url']; ?>
&nav=end&start=<?php echo $this->_tpl_vars['start']; ?>
">
                    <?php endif; ?>
                   <img
                   src='/images/end.gif' alt='<?php echo $this->_tpl_vars['lblEnd']; ?>
' align='absmiddle' border='0' width='13' height='11'></a>
                  <?php endif; ?>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>