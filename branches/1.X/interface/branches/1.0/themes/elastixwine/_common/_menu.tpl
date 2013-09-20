<div id="fullMenu">
  <table cellspacing=0 cellpadding=0 width="100%" border=0>
    <tr>
      <td>
        <table cellSpacing="0" cellPadding="0" width="100%" border="0" height="76">
          <tr>
            <td class="menulogo" width=380><a href='http://www.elastix.org' target='_blank'><img src="images/logo_elastix_new3.gif" border='0' /></a></td>
            {foreach from=$arrMainMenu key=idMenu item=menu}
            {if $idMenu eq $idMainMenuSelected}
            <td class="headlinkon" valign="bottom">
              <table cellSpacing="0" cellPadding="2" height="30" border="0">
                <tr><td class="menutabletabon_left" nowrap valign="top"><IMG src="/images/1x1.gif"></td><td class="menutabletabon" title="" nowrap><a
                        class="menutableon" href="/?menu={$idMenu}">{$menu.Name}</a></td><td class="menutabletabon_right" nowrap valign="top"><IMG src="/images/1x1.gif"></td>
                </tr>
              </table>
            </td>
            {else}
            <td class="headlink" valign="bottom">
              <div style="position:absolute; z-index:200; top:65px;"><a href="javascript:mostrar_Menu('{$idMenu}')"><img src="/images/esquinita2.gif" border="0"></a></div>
              <input type="hidden" id="idMenu" value=""></input>
              <div class="vertical_menu_oculto" id="{$idMenu}">
                <table cellpadding=0 cellspacing=0>
                {if $idMenu eq "system"}
                    {foreach from=$arrMenuSystem key=idSubMenu item=Submenu}
                        <tr><td>
                        <a href="/?menu={$idSubMenu}">{$Submenu.Name}</a>
                        </td></tr>
                    {/foreach}
                {/if}
                {if $idMenu eq "pbxconfig"}
                    {foreach from=$arrMenuPbx key=idSubMenu item=Submenu}
                        <tr><td>
                        <a href="/?menu={$idSubMenu}">{$Submenu.Name}</a>
                        </td></tr>
                    {/foreach}
                {/if}
                {if $idMenu eq "fax"}
                    {foreach from=$arrMenuFax key=idSubMenu item=Submenu}
                        <tr><td>
                        <a href="/?menu={$idSubMenu}">{$Submenu.Name}</a>
                        </td></tr>
                    {/foreach}
                {/if}
                {if $idMenu eq "email"}
                    {foreach from=$arrMenuEmail key=idSubMenu item=Submenu}
                        <tr><td>
                        <a href="/?menu={$idSubMenu}">{$Submenu.Name}</a>
                        </td></tr>
                    {/foreach}
                {/if}
                {if $idMenu eq "im"}
                    {foreach from=$arrMenuIm key=idSubMenu item=Submenu}
                        <tr><td>
                        <a href="/?menu={$idSubMenu}">{$Submenu.Name}</a>
                        </td></tr>
                    {/foreach}
                {/if}
                {if $idMenu eq "reports"}
                    {foreach from=$arrMenuReports key=idSubMenu item=Submenu}
                        <tr><td>
                        <a href="/?menu={$idSubMenu}">{$Submenu.Name}</a>
                        </td></tr>
                    {/foreach}
                {/if}
                {if $idMenu eq "extras"}
                    {foreach from=$arrMenuExtras key=idSubMenu item=Submenu}
                        <tr><td>
                        <a href="/?menu={$idSubMenu}">{$Submenu.Name}</a>
                        </td></tr>
                    {/foreach}
                {/if}
                {if $idMenu eq "call_center"}
                    {foreach from=$arrMenuCallCenter key=idSubMenu item=Submenu}
                        <tr><td>
                        <a href="/?menu={$idSubMenu}">{$Submenu.Name}</a>
                        </td></tr>
                    {/foreach}
                {/if}
                </table>
              </div>
              <table cellSpacing="0" cellPadding="2" height="29" border="0">
                <tr><td class="menutabletaboff_left" nowrap valign="top"><IMG src="/images/1x1.gif"></td><td class="menutabletaboff" title="" nowrap><a
                        class="menutable" href="/?menu={$idMenu}">{$menu.Name}</a></td><td class="menutabletaboff_right" nowrap valign="top"><IMG src="/images/1x1.gif"></td>
                </tr>
              </table> 
            </td>
            {/if}
            {/foreach}
            <td>
                <div id='acerca_de'>
                    <table border='0' cellspacing="0" cellpadding="2" width='100%'>
                        <tr class="moduleTitle">
                            <td class="moduleTitle" align="center" colspan='2'>
                                {$ABOUT_ELASTIX}
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
            </td>
            <td class="menuaftertab" width="40%" align="right">&nbsp;<a href="javascript:mostrar();">{$ABOUT_ELASTIX}</a></td>
            <td class="menuaftertab" width="20%" align="right">&nbsp;<a href="/?logout=yes">{$LOGOUT}</a></td>
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
                  {foreach from=$arrSubMenu key=idSubMenu item=subMenu}
                  {if $idSubMenu eq $idSubMenuSelected}
                  <td title="" class="botonon"><a href="/?menu={$idSubMenu}" class="submenu_on">{$subMenu.Name}</td>
                  {else}
                  <td title="" class="botonoff"><a href="/?menu={$idSubMenu}">{$subMenu.Name}</a></td>
                  {/if}
                  {/foreach}
                </tr>
              </table>
            </td>
            <td align="right" valign="middle"><a href="javascript:openWindow('/help/?id_nodo={$idSubMenuSelected}')"><img
                src="images/help_top.gif" border="0"></a>&nbsp;&nbsp;<a href="javascript:changeMenu()"><img
                src="images/arrow_top.gif" border="0"></a>&nbsp;&nbsp;</td>
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
      <td align="right" class="letra_gris" valign="middle">{$nameMainMenuSelected} &rarr; {$nameSubMenuSelected} {if !empty($idSubMenu2Selected)} &rarr; {$nameSubMenu2Selected} {/if}
          &nbsp;&nbsp;<a href="javascript:openWindow('/help/?id_nodo={$idSubMenuSelected}')"><img src="images/help_bottom.gif" border="0" 
          align="absmiddle"></a>
          &nbsp;&nbsp;<a href="javascript:changeMenu()"><img src="images/arrow_bottom.gif" border="0" align="absmiddle"></a>&nbsp;&nbsp;
      </td>
    </tr>
  </table>
</div>
<table width="100%" cellpadding="0" cellspacing="0" height="100%">
  <tr>
    {if !empty($idSubMenu2Selected)}
    <td width="200px" align="left" valign="top" bgcolor="#f6f6f6" id="tdMenuIzq">
      <table cellspacing="0" cellpadding="0" width="100%" class="" align="left">
        {foreach from=$arrSubMenu2 key=idSubMenu2 item=subMenu2}
          {if $idSubMenu2 eq $idSubMenu2Selected}
          <tr><td title="" class="menuiz_botonon"><a href="/?menu={$idSubMenu2}">{$subMenu2.Name}</td></tr>
          {else}
          <tr><td title="" class="menuiz_botonoff"><a href="/?menu={$idSubMenu2}">{$subMenu2.Name}</a></td></tr>
          {/if}
        {/foreach}
      </table>
    </td>
    {/if}
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