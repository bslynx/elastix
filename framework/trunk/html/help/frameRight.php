<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 0.5                                                  |
  | http://www.elastix.org                                               |
  +----------------------------------------------------------------------+
  | Copyright (c) 2006 Palosanto Solutions S. A.                         |
  +----------------------------------------------------------------------+
  | Cdla. Nueva Kennedy Calle E 222 y 9na. Este                          |
  | Telfs. 2283-268, 2294-440, 2284-356                                  |
  | Guayaquil - Ecuador                                                  |
  | http://www.palosanto.com                                             |
  +----------------------------------------------------------------------+
  | The contents of this file are subject to the General Public License  |
  | (GPL) Version 2 (the "License"); you may not use this file except in |
  | compliance with the License. You may obtain a copy of the License at |
  | http://www.opensource.org/licenses/gpl-license.php                   |
  |                                                                      |
  | Software distributed under the License is distributed on an "AS IS"  |
  | basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See  |
  | the License for the specific language governing rights and           |
  | limitations under the License.                                       |
  +----------------------------------------------------------------------+
  | The Original Code is: Elastix Open Source.                           |
  | The Initial Developer of the Original Code is PaloSanto Solutions    |
  +----------------------------------------------------------------------+
  $Id: frameRight.php,v 1.1.1.1 2007/07/06 21:31:56 gcarrillo Exp $
  $Id: frameRight.php,v 1.1.1.2 2012/03/29 10:24:26 labarca Exp $ */

include_once ("../libs/misc.lib.php");
include_once "../configs/default.conf.php";
include      "../configs/languages.conf.php";

session_name("elastixSession");
session_start();

require_once("../libs/smarty/libs/Smarty.class.php");
$smarty = new Smarty();
$smarty->template_dir = "../themes/" . $arrConf['mainTheme'];
$smarty->compile_dir =  "../var/templates_c/";
$smarty->config_dir =   "../configs/";
$smarty->cache_dir =    "../var/cache/";
$smarty->assign("THEMENAME", $arrConf['mainTheme']);

//$lang=get_language_global();

// Nombres válidos de módulos son alfanuméricos y subguión
if (!preg_match('/^\w+$/', $_GET['id_nodo'])) {
    unset($_GET['id_nodo']);
}

if(!empty($_GET['id_nodo'])){
    $idMenuMostrar = $_GET['id_nodo'];
    if(!empty($_GET['name_nodo'])){
	    $nameMenuMostrar = $_GET['name_nodo'];
        $smarty->assign("node_name", $nameMenuMostrar);
        $lang = confirmexistenceLang($idMenuMostrar);
        $smarty->assign("lang", $lang);
    }
                
    // Si no existe el archivo de ayuda y se trata de un menu "padre",
    // muestro el menu hijo que encuentre primero
/*
    $resArchivoExiste = existeArchivoAyuda($idMenuMostrar); 
    if($resArchivoExiste == 3 || $resArchivoExiste == 4) {
		$idMenuMostrar = menuHijoPorOmision($idMenuMostrar);
        $resArchivoExiste = existeArchivoAyuda($idMenuMostrar);
    }
    		
    if($resArchivoExiste == 1) {
       $smarty->assign("node_id", $idMenuMostrar);     
       $smarty->display($_SERVER["DOCUMENT_ROOT"]."/modules/$idMenuMostrar/help/$idMenuMostrar.hlp");
    }else if($resArchivoExiste == 2) {
       $smarty->assign("node_id", $idMenuMostrar);    
       $smarty->display($_SERVER["DOCUMENT_ROOT"]."/help/content/$idMenuMostrar.hlp");
    } else      
       echo "The help file for the selected menu does not exists";
*/
    $sRuta = rutaArchivoAyuda($idMenuMostrar);
    if (is_null($sRuta)) {
    	$idMenuMostrar = menuHijoPorOmision($idMenuMostrar);
        $sRuta = rutaArchivoAyuda($idMenuMostrar);
    }
    if (is_null($sRuta)) {
        echo  '<html><body><div><img src="../help/images/oops.jpg" border="0" width="773px" height="350px"></div>';
        echo '<h2>The help file information its not shown in a Folder like this.</h2></body></html>';    	
        //echo '<html><body>The help file for selected menu does not exist.</body></html>';
    } else {
       $smarty->assign("node_id", $idMenuMostrar);    
       $smarty->display($sRuta);
    }
} else {
    echo "The selected menu is not valid.";
}

function menuHijoPorOmision($idMenu)
{
    $arrMenu = array();
    if(isset($_SESSION['elastix_user_permission']))
        $arrMenu = $_SESSION['elastix_user_permission'];
    if(is_array($arrMenu))
    {
        foreach($arrMenu as $k => $menu) {
            if($menu['IdParent']==$idMenu) {
                echo "<h1>".$menu['Name']."</h1>";
				return $k;
                break;
            }
        }
    }
    return false;
}

function obtenerMenuPadre($idMenu)
{
    $arrMenu = $_SESSION['elastix_user_permission'];
    return $arrMenu[$idMenu]['IdParent'];
}
/*
function existeArchivoAyuda($idMenu)
{
    if(file_exists($_SERVER["DOCUMENT_ROOT"]."/modules/$idMenu/help/$idMenu.hlp")) {
        return 1;
    } else if(file_exists($_SERVER["DOCUMENT_ROOT"]."/help/content/$idMenu.hlp")) {
        return 2;
    } else if(!file_exists($_SERVER["DOCUMENT_ROOT"]."/help/content/$idMenu.hlp")){
        return 3;
    }else
        return 4;
}
*/

function rutaArchivoAyuda($idMenu)
{
    $lang=get_language_global();
    $serverDir = dirname($_SERVER['SCRIPT_FILENAME']).'/..';
    $listaRutas = array(
	    "$serverDir/modules/$idMenu/help/$idMenu.$lang.hlp",
        "$serverDir/help/content/$idMenu.$lang.hlp",
    );
    foreach ($listaRutas as $sRuta) {
    	if (file_exists($sRuta)){ return $sRuta;}
        else if (!file_exists($sRuta)){
        $serverDir = dirname($_SERVER['SCRIPT_FILENAME']).'/..';
        $listaRutas = array(
	    "$serverDir/modules/$idMenu/help/$idMenu.en.hlp",
        "$serverDir/help/content/$idMenu.en.hlp",
    );
        foreach ($listaRutas as $sRuta) {
            if (file_exists($sRuta)){ return $sRuta;}
            }
        return NULL;
        }    
    }
        return NULL;
}

function get_language_global($ruta_base='')
{
    require_once $ruta_base."../configs/default.conf.php";
    include $ruta_base."../configs/languages.conf.php";

    global $arrConf;
    $lang="";

    //conectarse a la base de settings para obtener el idioma actual
    $pDB = new paloDB($arrConf['elastix_dsn']['settings']);
    if(empty($pDB->errMsg)) {
        $lang=get_key_settings($pDB,'language');
    }
    //si no se encuentra tomar del archivo de configuracion
    if (empty($lang)) $lang=isset($arrConf['language'])?$arrConf['language']:"en";

    //verificar que exista en el arreglo de idiomas, sino por defecto en
    if (!array_key_exists($lang,$languages)) $lang="en";
    return $lang;   
}

function confirmexistenceLang($idMenu){
$lang1=get_language_global();
$serverDir = dirname($_SERVER['SCRIPT_FILENAME']).'/..';
    if(file_exists("$serverDir/modules/$idMenu/help/$idMenu.$lang1.hlp")||file_exists("$serverDir/help/content/$idMenu.$lang1.hlp")) {
            $lang1=get_language_global();
            return $lang1;}
        else{
            $lang1="en";
            return $lang1;
            }
}
?>
