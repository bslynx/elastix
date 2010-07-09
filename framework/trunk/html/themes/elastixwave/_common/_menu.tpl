<div id="fullMenu">
        <table cellspacing="0" cellpadding="0" width="100%" border="0" class="fondomenu_headertop">
            <tr>
                <td width="20%">
                    <table cellspacing="0" cellpadding="0" border="0" height="65px">
                        <tr>
                            <td class="menulogo"  valign="top">
                                <a href='http://www.elastix.org' target='_blank'>
                                    <img alt="" src="themes/{$THEMENAME}/images/logo_elastix.gif" border='0' />
                                </a>
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="50%" valign="top">
                    <table cellspacing="0" cellpadding="0" border="0" align="center" width="100%" height="74">
                        <tr>
                            {foreach from=$arrMainMenu key=idMenu item=menu name=menuMain}
                                {if $idMenu eq $idMainMenuSelected}
									<td width="4px">&nbsp;</td>
									<td align="center" id="menu_selected">
                                         <table cellspacing='0' cellpadding='0' border='0' style='position:relative;top:18px'>
                                            <tr>
                                                <td valign='top'><img alt="" border='0' align='absmiddle' src="themes/{$THEMENAME}/images/fondo_boton_on_left.gif"/></td>
                                                <td class='menutabletabon2' nowrap='nowrap'>
                                                    <a class='menutable2' href="index.php?menu={$idMenu}">{$menu.Name}</a>
                                                </td>
                                                <td valign='top'><img alt="" border='0' align='absmiddle' src="themes/{$THEMENAME}/images/fondo_boton_on_right.gif"/></td>
                                            </tr>
                                        </table>
										<!--<table cellspacing="0" cellpadding="0" border="0" width="69px" id="table_on">
											<tr>
												<td class="menutabletabon">
													<img alt="" src="themes/{$THEMENAME}/images/{$idMenu}_icon.gif" border="0" alt="" />
													<a class="menutableon" href="index.php?menu={$idMenu}">{$menu.Name}</a>
                                                    <input type="hidden" name="desc_menu" id="desc_menu" value="{$THEMENAME},{$idMenu},{$menu.Name}" />
												</td>
											</tr>
										</table>-->
									</td>
                                {else}
                                    <td width="4px">&nbsp;</td>
									<td align="center">
										<table cellspacing="0" cellpadding="0" border="0" style="position:relative;top:18px">
											<tr>
												<td valign="top"><img alt="" border="0" align="absmiddle" src="themes/elastixwave/images/fondo_boton_left.gif"/></td>
												<td class="menutabletaboff" nowrap="nowrap">
													<a class="menutable" href="index.php?menu={$idMenu}">{$menu.Name}</a>
												</td>
												<td valign="top"><img alt="" border="0" align="absmiddle" src="themes/elastixwave/images/fondo_boton_right.gif"/></td>
											</tr>
										</table> 
									</td>
                                 {/if}
                            {/foreach}
                                    <td width="69px">&nbsp;</td>
                        </tr>
                    </table>
                </td>
                <td width="30%" nowrap="nowrap">
                    <div id="menu_float" class="background">
                        <div id="logout_in">
                            <span><a class="logout" href="javascript:mostrar();">{$ABOUT_ELASTIX2}</a></span>&nbsp;
                            <span class="menuguion">*</span>&nbsp;
                            <span><a class="logout" href="javascript:openWindow('help/?id_nodo={$idSubMenuSelected}&amp;name_nodo={$nameSubMenuSelected}')">{$HELP}</a></span>&nbsp;
                            <span class="menuguion">*</span>&nbsp;
                            <span><a class="logout" href="?logout=yes">{$LOGOUT} (<font color='#c0d0e0'>{$USER_LOGIN}</font>)</a></span>&nbsp;
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="menudescription" colspan="3">
                  <table cellspacing="0" cellpadding="0" width="100%">
                    <tr>
                        <td><!--{$arrMenuTotalChildren}-->
                            <table cellspacing="1" cellpadding="5" border="0">
                                <tr>
                                {foreach from=$arrSubMenuByParents key=idSubMenu item=subMenu}
                                {if $idSubMenu eq $idSubMenuSelected}
                                <td title="" class="botonon">
                                        <a href="?menu={$idSubMenu}" class="submenu_on">{$subMenu.Name}</a>
                                </td>
                                {else}
                                <td title="" class="botonoff"><a href="index.php?menu={$idSubMenu}">{$subMenu.Name}</a></td>
                                {/if}
                                {/foreach}
                                </tr>
                            </table>
                        </td>
                        <td align="right" valign="middle"><a href="javascript:openWindow('help/?id_nodo={$idSubMenuSelected}&amp;name_nodo={$nameSubMenuSelected}')"><img alt=""
                            src="themes/{$THEMENAME}/images/help_bottom.gif" border="0" /></a>&nbsp;&nbsp;<a href="javascript:changeMenu()"><img alt=""
                            src="themes/{$THEMENAME}/images/arrow_top.gif" border="0" /></a>&nbsp;&nbsp;</td>
                    </tr>
                  </table>
                </td>
            </tr>
        </table>
</div>
<div id="miniMenu" style="display: none;">
  <table cellspacing="0" cellpadding="0" width="100%" class="menumini">
    <tr>
      <td><img alt="" src="images/logo_elastix_mini.jpg" border="0" /></td>
      <td align="right" class="letra_gris" valign="middle">{$nameMainMenuSelected} &rarr; {$nameSubMenuSelected} {if !empty($idSubMenu2Selected)} &rarr; {$nameSubMenu2Selected} {/if}
          &nbsp;&nbsp;<a href="javascript:openWindow('help/?id_nodo={$idSubMenuSelected}&amp;name_nodo={$nameSubMenuSelected}')"><img alt="" src="themes/{$THEMENAME}/images/help_bottom.gif" border="0" 
          align="absmiddle" /></a>
          &nbsp;&nbsp;<a href="javascript:changeMenu()"><img alt="" src="themes/{$THEMENAME}/images/arrow_bottom.gif" border="0" align="absmiddle" /></a>&nbsp;&nbsp;
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
//<![CDATA[
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


function createMenuSelectedSplash()
{
    var desc_menu = document.getElementById("desc_menu").value;
    var arrDesc = desc_menu.split(",");

    var html  = "<table cellspacing='0' cellpadding='0' border='0' style='position:relative;top:17px'>";
        html += "   <tr>";
        html += "       <td valign='top'><img alt='' border='0' align='absmiddle' src='themes/" + arrDesc[0] + "/images/fondo_boton_on_left.gif'/></td>";
        html += "       <td class='menutabletabon2' nowrap='nowrap'>";
        html += "           <a class='menutable2' href='javascript:openMenu(\"" + arrDesc[1] + "\");'>" + arrDesc[2] + "</a>";
        html += "       </td>";
        html += "       <td valign='top'><img alt='' border='0' align='absmiddle' src='themes/" + arrDesc[0] + "/images/fondo_boton_on_right.gif'/></td>";
        html += "   </tr>";
        html += "</table>";

    var menu_selected = document.getElementById("menu_selected");
    menu_selected.innerHTML = html;
}
setTimeout("createMenuSelectedSplash()",1400);

// var cnt = 0;
// function load()
// {
//     if(cnt > 1)
//         createMenuSelectedSplash();
//     else {
//         setTimeout("load()",500);
//         cnt++;
//     }
// }
// load();

//]]>
</script>

<script type="text/javascript">
//<![CDATA[
    $(".menutabletaboff").mouseover(function(){
        $(this).attr("class","menutabletaboffover");
        $(this).find('a:first').attr("class","menutableOver");
        $(this).parent().find('img:first').attr("src","themes/elastixwave/images/fondo_boton_left2.gif");
        $(this).parent().find('img:last').attr("src","themes/elastixwave/images/fondo_boton_right2.gif");
    });

    $(".menutabletaboff").mouseout(function(){
        $(this).attr("class","menutabletaboff");
        $(this).find('a:first').attr("class","menutable");
        $(this).parent().find('img:first').attr("src","themes/elastixwave/images/fondo_boton_left.gif");
        $(this).parent().find('img:last').attr("src","themes/elastixwave/images/fondo_boton_right.gif");
    });
//]]>
</script>
{/literal}
