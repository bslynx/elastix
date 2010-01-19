<div id="fullMenu">
    <div id="">
        <table cellspacing="0" cellpadding="0" width="100%" border="0" class="fondomenu_headertop">
            <tr >
                <td width="24%">
                    <table cellSpacing="0" cellPadding="0" border="0" height="65px">
                        <tr>
                            <td class="menulogo"  valign="top">
                                <a href='http://www.elastix.org' target='_blank'>
                                    <img src="themes/{$THEMENAME}/images/logo_elastix.gif" border='0' />
                                </a>
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="52%" rowspan="2" valign="bottom">
                    <table cellSpacing="0" cellPadding="0" border="0" align="center" width="100%">
                        <tr>
                            {foreach from=$arrMainMenu key=idMenu item=menu name=menuMain}
                                {if $idMenu eq $idMainMenuSelected}
									{if $smarty.foreach.menuMain.first}
									<td valign="bottom" align="center">
										<table cellSpacing="0" cellPadding="0" border="0" width="69px">
											<tr>
											<td class="menutabletabon">
												<img src="themes/{$THEMENAME}/images/{$idMenu}_icon.gif" border="0" alt="" />
												<a class="menutableon" href="index.php?menu={$idMenu}">{$menu.Name}</a>
											</td>
											</tr>
										</table>
									</td>
									{else}
									<td valign="bottom">
										<table cellSpacing="0" cellPadding="0" border="0" width="100%">
											<tr>
												<td class="menutabletaboff2" valign="top"> . </td>
											</tr>
										</table> 
									</td>
									<td valign="bottom" align="center">
										<table cellSpacing="0" cellPadding="0" border="0" width="69px">
											<tr>
												<td class="menutabletabon" rowspan="2">
													<img src="themes/{$THEMENAME}/images/{$idMenu}_icon.gif" border="0" alt="" />
													<a class="menutableon" href="index.php?menu={$idMenu}">{$menu.Name}</a>
												</td>
											</tr>
										</table>
									</td>
									{/if}
                                {else}
									{if $smarty.foreach.menuMain.first}
									<td valign="bottom">
										<table cellSpacing="0" cellPadding="0" border="0" width="100%">
											<tr>
												<td class="menutabletaboff" nowrap="">
												<a class="menutable" href="index.php?menu={$idMenu}">{$menu.Name}</a>
												</td>
											</tr>
										</table> 
									</td>
									{else}
									<td valign="bottom">
										<table cellSpacing="0" cellPadding="0" border="0" width="100%">
											<tr>
												<td class="menutabletaboff2" valign="top"> . </td>
											</tr>
										</table> 
									</td>
									<td valign="bottom">
										<table cellSpacing="0" cellPadding="0" border="0" width="100%">
											<tr>
												<td class="menutabletaboff" nowrap="">
													<a class="menutable" href="index.php?menu={$idMenu}">{$menu.Name}</a>
												</td>
											</tr>
										</table> 
									</td>
									{/if}
                                 {/if}
                            {/foreach}
                        </tr>
                    </table>
                </td>
                <td width="24%">
                     <table cellSpacing="0" cellPadding="0" border="0" height="25">
                        <tr class="background">
							<td class="menuaftertab" width="19%" align="center">&nbsp;<a class="logout" href="javascript:openWindow('help/?id_nodo={$idSubMenuSelected}&name_nodo={$nameSubMenuSelected}')">{$HELP}</a></td>
							<td class="menuaftertab">-</td>
                            <td class="menuaftertab" width="32%" align="center">&nbsp;<a class="logout" href="javascript:mostrar();">{$ABOUT_ELASTIX2}</a></td>
							<td class="menuaftertab">-</td>
                            <td class="menuaftertab" width="55%" align="center">&nbsp;<a class="logout" href="?logout=yes">{$LOGOUT} (<font color='white'>{$USER_LOGIN}</font>)</a></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td width="20%" height="34"></td>
                <td width="50%" height="34"></td>
            </tr>
            <tr>
                <td class="menudescription" colspan="3">
                  <table cellspacing="0" cellpadding="0" width="100%">
                    <tr>
                        <td>
                            <table cellspacing="2" cellpadding="4" border="0">
                                <tr>
                                {foreach from=$arrSubMenu key=idSubMenu item=subMenu}
                                {if $idSubMenu eq $idSubMenuSelected}
                                <td title="" class="botonon">
                                        <a href="index.php?menu={$idSubMenu}" class="submenu_on">{$subMenu.Name}</a>
                                </td>
                                {else}
                                <td title="" class="botonoff"><a href="index.php?menu={$idSubMenu}">{$subMenu.Name}</a></td>
                                {/if}
                                {/foreach}
                                </tr>
                            </table>
                        </td>
                        <td align="right" valign="middle"><a href="javascript:openWindow('help/?id_nodo={$idSubMenuSelected}&name_nodo={$nameSubMenuSelected}')"><img
                            src="themes/{$THEMENAME}/images/help_bottom.gif" border="0"></a>&nbsp;&nbsp;<a href="javascript:changeMenu()"><img
                            src="themes/{$THEMENAME}/images/arrow_top.gif" border="0"></a>&nbsp;&nbsp;</td>
                    </tr>
                  </table>
                </td>
            </tr>
        </table>
    </div>
</div>
<div id="miniMenu" style="display: none;">
  <table cellspacing="0" cellpadding="0" width="100%" class="menumini">
    <tr>
      <td><img src="images/logo_elastix_mini.jpg" border="0"></td>
      <td align="right" class="letra_gris" valign="middle">{$nameMainMenuSelected} &rarr; {$nameSubMenuSelected} {if !empty($idSubMenu2Selected)} &rarr; {$nameSubMenu2Selected} {/if}
          &nbsp;&nbsp;<a href="javascript:openWindow('help/?id_nodo={$idSubMenuSelected}&name_nodo={$nameSubMenuSelected}')"><img src="themes/{$THEMENAME}/images/help_bottom.gif" border="0" 
          align="absmiddle"></a>
          &nbsp;&nbsp;<a href="javascript:changeMenu()"><img src="themes/{$THEMENAME}/images/arrow_bottom.gif" border="0" align="absmiddle"></a>&nbsp;&nbsp;
      </td>
    </tr>
  </table>
</div>


<div id='acerca_de'>
    <table border='0' cellspacing="0" cellpadding="2" width='100%'>
        <tr class="moduleTitle">
            <td class="moduleTitle" align="center" colspan='2'>
                {$ABOUT_ELASTIX}
            </td>
        </tr>
        <tr class="tabForm" >
            <td class="tabForm"  height='138' colspan='2' align='center'>
                {$ABOUT_ELASTIX_CONTENT}<br />
                <a href='http://www.elastix.org' target='_blank'>www.elastix.org</a>
            </td>
        </tr>
        <tr>
            <td class="moduleTitle" align="center" colspan='2'>
                <input type='button' value='{$ABOUT_CLOSED}' onclick="javascript:cerrar();" />
            </td>
        </tr>
    </table> 
</div>


<table width="100%" cellpadding="0" cellspacing="0" height="100%">
  <tr>
    {if !empty($idSubMenu2Selected)}
    <td width="200px" align="left" valign="top" bgcolor="#f6f6f6" id="tdMenuIzq">
      <table cellspacing="0" cellpadding="0" width="100%" class="" align="left">
          <tr><td title="" class="menuiz_start">&nbsp;</td></tr>
        {foreach from=$arrSubMenu2 key=idSubMenu2 item=subMenu2}
          {if $idSubMenu2 eq $idSubMenu2Selected}
          <tr><td title="" class="menuiz_botonon"><a href="index.php?menu={$idSubMenu2}">{$subMenu2.Name}</td></tr>
          {else}
          <tr><td title="" class="menuiz_botonoff"><a href="index.php?menu={$idSubMenu2}">{$subMenu2.Name}</a></td></tr>
          {/if}
        {/foreach}
      </table>
    </td>
    {/if}
<!-- Va al tpl index.tlp-->

{literal}
<style type='text/css'>
#acerca_de{
    position:absolute; 
    width:440px;
    height:200px;
    border:1px solid #800000;
}
</style>
<script type='text/javascript'>
cerrar();
function cerrar()
{
    var div_contenedor = document.getElementById('acerca_de');
    div_contenedor.style.display = 'none';
}

function mostrar()
{
    var ancho = 440;
    var div_contenedor = document.getElementById('acerca_de');
    var eje_x=(screen.width - ancho) / 2;
    div_contenedor.setAttribute("style","left:"+ eje_x + "px; top:123px");
    div_contenedor.style.display = 'block';
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
{/literal}
