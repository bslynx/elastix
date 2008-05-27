<?php /* Smarty version 2.6.14, created on 2008-04-29 06:26:54
         compiled from _common/_menu.tpl */ ?>
<div id="fullMenu">
  <table cellspacing=0 cellpadding=0 width="100%" border=0>
    <tr>
      <td>
        <table cellSpacing="0" cellPadding="0" width="100%" border="0" height="76">
          <tr>
            <td class="menulogo" width=380><a href='http://www.elastix.org' target='_blank'><img src="images/logo_elastix_new3.gif" border='0' /></a></td>
            <?php $_from = $this->_tpl_vars['arrMainMenu']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['idMenu'] => $this->_tpl_vars['menu']):
?>
            <?php if ($this->_tpl_vars['idMenu'] == $this->_tpl_vars['idMainMenuSelected']): ?>
            <td class="headlinkon" valign="bottom">
              <table cellSpacing="0" cellPadding="2" height="30" border="0">
                <tr><td class="menutabletabon_left" nowrap valign="top"><IMG src="/images/1x1.gif"></td><td class="menutabletabon" title="" nowrap><a
                        class="menutableon" href="/?menu=<?php echo $this->_tpl_vars['idMenu']; ?>
"><?php echo $this->_tpl_vars['menu']['Name']; ?>
</a></td><td class="menutabletabon_right" nowrap valign="top"><IMG src="/images/1x1.gif"></td>
                </tr>
              </table>
            </td>
            <?php else: ?>
            <td class="headlink" valign="bottom">
              <div style="position:absolute; z-index:200; top:65px;"><a href="javascript:mostrar_Menu('<?php echo $this->_tpl_vars['idMenu']; ?>
')"><img src="themes/al/images/corner.gif" border="0"></a></div>
              <input type="hidden" id="idMenu" value=""></input>
              <div class="vertical_menu_oculto" id="<?php echo $this->_tpl_vars['idMenu']; ?>
">
                <table cellpadding=0 cellspacing=0>
                <?php if ($this->_tpl_vars['idMenu'] == 'system'): ?>
                    <?php $_from = $this->_tpl_vars['arrMenuSystem']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['idSubMenu'] => $this->_tpl_vars['Submenu']):
?>
                        <tr><td>
                        <a href="/?menu=<?php echo $this->_tpl_vars['idSubMenu']; ?>
"><?php echo $this->_tpl_vars['Submenu']['Name']; ?>
</a>
                        </td></tr>
                    <?php endforeach; endif; unset($_from); ?>
                <?php endif; ?>
                <?php if ($this->_tpl_vars['idMenu'] == 'pbxconfig'): ?>
                    <?php $_from = $this->_tpl_vars['arrMenuPbx']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['idSubMenu'] => $this->_tpl_vars['Submenu']):
?>
                        <tr><td>
                        <a href="/?menu=<?php echo $this->_tpl_vars['idSubMenu']; ?>
"><?php echo $this->_tpl_vars['Submenu']['Name']; ?>
</a>
                        </td></tr>
                    <?php endforeach; endif; unset($_from); ?>
                <?php endif; ?>
                <?php if ($this->_tpl_vars['idMenu'] == 'fax'): ?>
                    <?php $_from = $this->_tpl_vars['arrMenuFax']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['idSubMenu'] => $this->_tpl_vars['Submenu']):
?>
                        <tr><td>
                        <a href="/?menu=<?php echo $this->_tpl_vars['idSubMenu']; ?>
"><?php echo $this->_tpl_vars['Submenu']['Name']; ?>
</a>
                        </td></tr>
                    <?php endforeach; endif; unset($_from); ?>
                <?php endif; ?>
                <?php if ($this->_tpl_vars['idMenu'] == 'email'): ?>
                    <?php $_from = $this->_tpl_vars['arrMenuEmail']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['idSubMenu'] => $this->_tpl_vars['Submenu']):
?>
                        <tr><td>
                        <a href="/?menu=<?php echo $this->_tpl_vars['idSubMenu']; ?>
"><?php echo $this->_tpl_vars['Submenu']['Name']; ?>
</a>
                        </td></tr>
                    <?php endforeach; endif; unset($_from); ?>
                <?php endif; ?>
                <?php if ($this->_tpl_vars['idMenu'] == 'im'): ?>
                    <?php $_from = $this->_tpl_vars['arrMenuIm']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['idSubMenu'] => $this->_tpl_vars['Submenu']):
?>
                        <tr><td>
                        <a href="/?menu=<?php echo $this->_tpl_vars['idSubMenu']; ?>
"><?php echo $this->_tpl_vars['Submenu']['Name']; ?>
</a>
                        </td></tr>
                    <?php endforeach; endif; unset($_from); ?>
                <?php endif; ?>
                <?php if ($this->_tpl_vars['idMenu'] == 'reports'): ?>
                    <?php $_from = $this->_tpl_vars['arrMenuReports']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['idSubMenu'] => $this->_tpl_vars['Submenu']):
?>
                        <tr><td>
                        <a href="/?menu=<?php echo $this->_tpl_vars['idSubMenu']; ?>
"><?php echo $this->_tpl_vars['Submenu']['Name']; ?>
</a>
                        </td></tr>
                    <?php endforeach; endif; unset($_from); ?>
                <?php endif; ?>
                <?php if ($this->_tpl_vars['idMenu'] == 'extras'): ?>
                    <?php $_from = $this->_tpl_vars['arrMenuExtras']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['idSubMenu'] => $this->_tpl_vars['Submenu']):
?>
                        <tr><td>
                        <a href="/?menu=<?php echo $this->_tpl_vars['idSubMenu']; ?>
"><?php echo $this->_tpl_vars['Submenu']['Name']; ?>
</a>
                        </td></tr>
                    <?php endforeach; endif; unset($_from); ?>
                <?php endif; ?>
                <?php if ($this->_tpl_vars['idMenu'] == 'call_center'): ?>
                    <?php $_from = $this->_tpl_vars['arrMenuCallCenter']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['idSubMenu'] => $this->_tpl_vars['Submenu']):
?>
                        <tr><td>
                        <a href="/?menu=<?php echo $this->_tpl_vars['idSubMenu']; ?>
"><?php echo $this->_tpl_vars['Submenu']['Name']; ?>
</a>
                        </td></tr>
                    <?php endforeach; endif; unset($_from); ?>
                <?php endif; ?>
                </table>
              </div>
              <table cellSpacing="0" cellPadding="2" height="29" border="0">
                <tr><td class="menutabletaboff_left" nowrap valign="top"><IMG src="/images/1x1.gif"></td><td class="menutabletaboff" title="" nowrap><a
                        class="menutable" href="/?menu=<?php echo $this->_tpl_vars['idMenu']; ?>
"><?php echo $this->_tpl_vars['menu']['Name']; ?>
</a></td><td class="menutabletaboff_right" nowrap valign="top"><IMG src="/images/1x1.gif"></td>
                </tr>
              </table> 
            </td>
            <?php endif; ?>
            <?php endforeach; endif; unset($_from); ?>
            <td>
                <div id='acerca_de'>
                    <table border='0' cellspacing="0" cellpadding="2" width='100%'>
                        <tr class="moduleTitle">
                            <td class="moduleTitle" align="center" colspan='2'>
                                <?php echo $this->_tpl_vars['ABOUT_ELASTIX']; ?>

                            </td>
                        </tr>
                        <!--<tr class="tabForm">
                            <td class="tabForm" >
                                <img src="images/logo_elastix_about.gif" />
                            </td>
                            <td class="tabForm" >
                                <img src="images/logo_palosanto_about.gif" />
                            </td>
                        </tr>-->
                        <tr class="tabForm" >
                            <td class="tabForm"  height='138' colspan='2' align='center'>
                                <?php echo $this->_tpl_vars['ABOUT_ELASTIX_CONTENT']; ?>
<br />
                                <a href='http://www.elastix.org' target='_blank'>www.elastix.org</a>
                            </td>
                        </tr>
                        <tr>
                            <td class="moduleTitle" align="center" colspan='2'>
                                <input type='button' value='<?php echo $this->_tpl_vars['ABOUT_CLOSED']; ?>
' onclick="javascript:cerrar();" />
                            </td>
                        </tr>
                    </table> 
                </div>
            </td>
            <td class="menuaftertab" width="40%" align="right">&nbsp;<a href="javascript:mostrar();"><?php echo $this->_tpl_vars['ABOUT_ELASTIX']; ?>
</a></td>
            <td class="menuaftertab" width="20%" align="right">&nbsp;<a href="/?logout=yes"><?php echo $this->_tpl_vars['LOGOUT']; ?>
</a></td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td class="menudescription">
        <table cellspacing="0" cellpadding="2" width="100%">
          <tr>
            <td>
              <table cellspacing="2" cellpadding="4" border="0">
                <tr>
                  <?php $_from = $this->_tpl_vars['arrSubMenu']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['idSubMenu'] => $this->_tpl_vars['subMenu']):
?>
                  <?php if ($this->_tpl_vars['idSubMenu'] == $this->_tpl_vars['idSubMenuSelected']): ?>
                  <td title="" class="botonon"><a href="/?menu=<?php echo $this->_tpl_vars['idSubMenu']; ?>
" class="submenu_on"><?php echo $this->_tpl_vars['subMenu']['Name']; ?>
</td>
                  <?php else: ?>
                  <td title="" class="botonoff"><a href="/?menu=<?php echo $this->_tpl_vars['idSubMenu']; ?>
"><?php echo $this->_tpl_vars['subMenu']['Name']; ?>
</a></td>
                  <?php endif; ?>
                  <?php endforeach; endif; unset($_from); ?>
                </tr>
              </table>
            </td>
            <td align="right" valign="middle"><a href="javascript:openWindow('/help/?id_nodo=<?php echo $this->_tpl_vars['idSubMenuSelected']; ?>
')"><img
                src="themes/al/images/help_top.gif" border="0"></a>&nbsp;&nbsp;<a href="javascript:changeMenu()"><img
                src="themes/al/images/arrow_top.gif" border="0"></a>&nbsp;&nbsp;</td>
          </tr>
        </table>
      </td>
    </tr>
    <tr class="downshadow"><td><img src="images/1x1.gif" height="5"></td></tr>
  </table>
</div>
<div id="miniMenu" style="display: none;">
  <table cellspacing="0" cellpadding="0" width="100%" class="menumini">
    <tr>
      <td><img src="images/logo_elastix_mini.jpg" border="0"></td>
      <td align="right" class="letra_gris" valign="middle"><?php echo $this->_tpl_vars['nameMainMenuSelected']; ?>
 &rarr; <?php echo $this->_tpl_vars['nameSubMenuSelected']; ?>
 <?php if (! empty ( $this->_tpl_vars['idSubMenu2Selected'] )): ?> &rarr; <?php echo $this->_tpl_vars['nameSubMenu2Selected']; ?>
 <?php endif; ?>
          &nbsp;&nbsp;<a href="javascript:openWindow('/help/?id_nodo=<?php echo $this->_tpl_vars['idSubMenuSelected']; ?>
')"><img src="images/help_bottom.gif" border="0" 
          align="absmiddle"></a>
          &nbsp;&nbsp;<a href="javascript:changeMenu()"><img src="images/arrow_bottom.gif" border="0" align="absmiddle"></a>&nbsp;&nbsp;
      </td>
    </tr>
  </table>
</div>
<table width="100%" cellpadding="0" cellspacing="0" height="100%">
  <tr>
    <?php if (! empty ( $this->_tpl_vars['idSubMenu2Selected'] )): ?>
    <td width="200px" align="left" valign="top" bgcolor="#f6f6f6" id="tdMenuIzq">
      <table cellspacing="0" cellpadding="0" width="100%" class="" align="left">
        <?php $_from = $this->_tpl_vars['arrSubMenu2']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['idSubMenu2'] => $this->_tpl_vars['subMenu2']):
?>
          <?php if ($this->_tpl_vars['idSubMenu2'] == $this->_tpl_vars['idSubMenu2Selected']): ?>
          <tr><td title="" class="menuiz_botonon"><a href="/?menu=<?php echo $this->_tpl_vars['idSubMenu2']; ?>
"><?php echo $this->_tpl_vars['subMenu2']['Name']; ?>
</td></tr>
          <?php else: ?>
          <tr><td title="" class="menuiz_botonoff"><a href="/?menu=<?php echo $this->_tpl_vars['idSubMenu2']; ?>
"><?php echo $this->_tpl_vars['subMenu2']['Name']; ?>
</a></td></tr>
          <?php endif; ?>
        <?php endforeach; endif; unset($_from); ?>
      </table>
    </td>
    <?php endif;  echo '
<style type=\'text/css\'>
#acerca_de{
    position:absolute; 
    width:440px;
    height:200px;
    border:1px solid #800000;
}
</style>
<script type=\'text/javascript\'>
cerrar();
function cerrar()
{
    var div_contenedor = document.getElementById(\'acerca_de\');
    div_contenedor.style.display = \'none\';
}

function mostrar()
{
    var ancho = 440;
    var div_contenedor = document.getElementById(\'acerca_de\');
    var eje_x=(screen.width - ancho) / 2;
    div_contenedor.setAttribute("style","left:"+ eje_x + "px; top:123px");
    div_contenedor.style.display = \'block\';
}

function mostrar_Menu(element)
{
    var subMenu;

    var idMenu = document.getElementById("idMenu");
    if(idMenu.value!="")
    {
        subMenu = document.getElementById(idMenu.value);
        subMenu.setAttribute("class", "vertical_menu_oculto");
    }
    if(element != idMenu.value)
    {
        subMenu = document.getElementById(element);
        subMenu.setAttribute("class", "vertical_menu_visible");
        idMenu.setAttribute("value", element);
    }
    else idMenu.setAttribute("value", "");
}
</script>
'; ?>
