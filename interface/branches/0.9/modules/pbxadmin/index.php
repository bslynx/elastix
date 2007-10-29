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
  $Id: index.php,v 1.3 2007/07/17 00:03:42 gcarrillo Exp $ */
 
function _moduleContent(&$smarty, $module_name)
{
    global $arrConf;

    $salida="";
    $fromElastixAdminPBX=1;
    $headerFreePBX="
    <link href='/modules/pbxadmin/themes/default/mainstyle.css' rel='stylesheet' type='text/css'>
    <!--[if IE]>
    <link href='/modules/pbxadmin/themes/default/ie.css' rel='stylesheet' type='text/css'>
    <![endif]-->	
    <script type='text/javascript' src='/modules/pbxadmin/js/script.js.php'></script>
    <script type='text/javascript' src='/modules/pbxadmin/js/script.legacy.js'></script>
    <script type='text/javascript' src='/modules/pbxadmin/js/libfreepbx.javascripts.js'></script>
    <!--[if IE]>
    <style type='text/css'>div.inyourface a{position:absolute;}</style>
    <![endif]-->";

    $dir_base = "/var/www/html/modules/$module_name/themes";
    $local_templates_dir=(file_exists("$dir_base/".$arrConf['theme']))?"$dir_base/".$arrConf['theme']:"$dir_base/default";

    // Obtengo el {$HEADER} anterior y le agrego lo nuevo
    $headerNuevo = @$smarty->fetch("HEADER");
    $headerNuevo .= $headerFreePBX;
    $smarty->assign("HEADER", $headerNuevo);

    // Obtengo el {$BODYPARAMS} anterior y le agrego lo nuevo
    $bodyParams = @$smarty->fetch("BODYPARAMS");
    $bodyParams .= "onload='body_loaded();'";
    $smarty->assign("BODYPARAMS", $bodyParams);

    $_SESSION["AMP_user"]=NULL;
    /* benchmark */
    function microtime_float() { list($usec,$sec) = explode(' ',microtime()); return ((float)$usec+(float)$sec); }
    $benchmark_starttime = microtime_float();
    /*************/
    
    $type = isset($_REQUEST['type'])?$_REQUEST['type']:'setup';
    // Ojo, modifique ligeramente la sgte. linea para que la opcion por omision sea extensions
    $display = isset($_REQUEST['display'])?$_REQUEST['display']:'extensions';
    $extdisplay = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
    $skip = isset($_REQUEST['skip'])?$_REQUEST['skip']:0;
    $action = isset($_REQUEST['action'])?$_REQUEST['action']:null;
    $quietmode = isset($_REQUEST['quietmode'])?$_REQUEST['quietmode']:'';
    
    // determine module type to show, default to 'setup'
    $type_names = array(
    	'tool'=>'Tools',
    	'setup'=>'Setup',
    	'cdrcost'=>'Call Cost',
    );

    /*************************************************************/
    /* Este bloque pertenece en su mayoria al archivo header.php */
    /* ya que no estaban registrando ciertas variables globales; */
    /* asi que lo repito aqui y evito parchar dicho archivo y    */
    /* otros mas.                                                */
    /*************************************************************/

    // include base functions
    require_once('/var/www/html/admin/functions.inc.php');
    require_once('/var/www/html/admin/common/php-asmanager.php');

    // Hack to avoid patching admin/functions.inc.php
    $GLOBALS['amp_conf_defaults'] = $amp_conf_defaults;

    // get settings
    $amp_conf       = parse_amportal_conf("/etc/amportal.conf");
    $asterisk_conf  = parse_asterisk_conf($amp_conf["ASTETCDIR"]."/asterisk.conf");
    $astman         = new AGI_AsteriskManager();

    // attempt to connect to asterisk manager proxy
    if (!isset($amp_conf["ASTMANAGERPROXYPORT"]) || !$res = $astman->connect("127.0.0.1:".$amp_conf["ASTMANAGERPROXYPORT"], $amp_conf["AMPMGRUSER"] , $amp_conf["AMPMGRPASS"])) {
            // attempt to connect directly to asterisk, if no proxy or if proxy failed
            if (!$res = $astman->connect("127.0.0.1:".$amp_conf["ASTMANAGERPORT"], $amp_conf["AMPMGRUSER"] , $amp_conf["AMPMGRPASS"])) {
                    // couldn't connect at all
                    unset( $astman );
            }
    }

    $GLOBALS['amp_conf'] = $amp_conf;
    $GLOBALS['asterisk_conf']  = $asterisk_conf;
    $GLOBALS['astman'] = $astman;

    // Hack to avoid patching common/db_connect.php
    // I suppose the used database is mysql
    require_once('DB.php'); //PEAR must be installed
    $db_user = $amp_conf["AMPDBUSER"];
    $db_pass = $amp_conf["AMPDBPASS"];
    $db_host = $amp_conf["AMPDBHOST"];
    $db_name = $amp_conf["AMPDBNAME"];

    $datasource = 'mysql://'.$db_user.':'.$db_pass.'@'.$db_host.'/'.$db_name;
    $db = DB::connect($datasource); // attempt connection

    $GLOBALS['db'] = $db;

    // Requiring header.php
    include('/var/www/html/admin/header.php');

    /*************************************************************/
    /* Fin del bloque                                            */
    /*************************************************************/

    $GLOBALS['title'] = $title;
    $GLOBALS['type']  = $type;
    $GLOBALS['display'] = $display;
    $GLOBALS['extdisplay'] = $extdisplay;
    $GLOBALS['skip'] = $skip;
    $GLOBALS['action'] = $action;
    $GLOBALS['quietmode'] = $quietmode;
    $GLOBALS['message'] = $message;
    $GLOBALS['fpbx_menu'] = $fpbx_menu;
 
    // handle special requests
    if (isset($_REQUEST['handler'])) {
    	switch ($_REQUEST['handler']) {
    		case 'reload':
    			/** AJAX handler for reload event
    			 */
    			include_once('/var/www/html/admin/common/json.inc.php');
    			$response = do_reload();
    			$json = new Services_JSON();
    			echo $json->encode($response);
    		break;
    		case 'file':
    			/** Handler to pass-through file requests 
    			 * Looks for "module" and "file" variables, strips .. and only allows normal filename characters.
    			 * Accepts only files of the type listed in $allowed_exts below, and sends the corresponding mime-type, 
    			 * and always interprets files through the PHP interpreter. (Most of?) the freepbx environment is available,
    			 * including $db and $astman, and the user is authenticated.
    			 */
	    		if (!isset($_REQUEST['module']) || !isset($_REQUEST['file'])) {
    				die_freepbx("unknown");
    			}
    			//TODO: this could probably be more efficient
    			$module = str_replace('..','.', preg_replace('/[^a-zA-Z0-9-\_\.]/','',$_REQUEST['module']));
    			$file = str_replace('..','.', preg_replace('/[^a-zA-Z0-9-\_\.]/','',$_REQUEST['file']));
    			
    			$allowed_exts = array(
    				'.js' => 'text/javascript',
    				'.js.php' => 'text/javascript',
    				'.css' => 'text/css',
    				'.css.php' => 'text/css',
    				'.html.php' => 'text/html',
    				'.jpg.php' => 'image/jpeg',
    				'.jpeg.php' => 'image/jpeg',
    				'.png.php' => 'image/png',
    				'.gif.php' => 'image/gif',
    			);
    			foreach ($allowed_exts as $ext=>$mimetype) {
    				if (substr($file, -1*strlen($ext)) == $ext) {
    					$fullpath = 'modules/'.$module.'/'.$file;
    					if (file_exists($fullpath)) {
    						// file exists, and is allowed extension
    
    						// image, css, js types - set Expires to an hour in advance so the client does
    						// not keep checking for them. Replace from header.php
    						if (!$amp_conf['DEVEL']) {
    							@header('Expires: '.gmdate('D, d M Y H:i:s', time()+3600).' GMT', true);
    							@header('Cache-Control: ',true); 
    							@header('Pragma: ', true); 
    						}
    						@header("Content-type: ".$mimetype);
    						include($fullpath);
    						exit();
    					}
    					break;
    				}
    			}
    			die_freepbx("not allowed");
    		break;
    	}
    	exit();
    }
    
    module_run_notification_checks();
    
    $framework_asterisk_running =  checkAstMan();
    
    // get all enabled modules
    // active_modules array used below and in drawselects function and genConf function
//    $active_modules = module_getinfo(false, MODULE_STATUS_ENABLED);
    $active_modules = module_getinfo(false);

    $GLOBALS['active_modules'] = $active_modules;

    // Esto lo he puesto aqui porque el modulo blacklist lo requiere
    // en caso de que o exista la clase extension
    require_once("/var/www/html/admin/extensions.class.php");
    
    $fpbx_menu = array();
    
    // pointer to current item in $fpbx_menu, if applicable
    $cur_menuitem = null;
    
    // add module sections to $fpbx_menu
    // Aqui lleno el arreglo fpbx_menu que es quien contiene los elementos del menu
    // Tambien cargo unas funciones y se hacen otras cositas mas como setear la variable
    // que contiene el item actual
    $types = array();
    if(is_array($active_modules)){
    	foreach($active_modules as $key => $module) {
    		//include module functions
    		if (is_file("/var/www/html/admin/modules/{$key}/functions.inc.php")) {
    			require_once("/var/www/html/admin/modules/{$key}/functions.inc.php");
    		}
    		
    		//create an array of module sections to display
    		// stored as [items][$type][$category][$name] = $displayvalue
    		if (isset($module['items']) && is_array($module['items'])) {
    			// loop through the types
    			foreach($module['items'] as $itemKey => $item) {
    
    				// check access, unless module.xml defines all have access
    				if (!isset($item['access']) || strtolower($item['access']) != 'all') {
    					if (!$_SESSION["AMP_user"]->checkSection($itemKey)) {
    						// no access, skip to the next 
    						continue;
    					}
    				}
    
    				if (!$framework_asterisk_running && 
    					  ((isset($item['needsenginedb']) && strtolower($item['needsenginedb'] == 'yes')) || 
    					  (isset($item['needsenginerunning']) && strtolower($item['needsenginerunning'] == 'yes')))
    				   )
    				{
    					$item['disabled'] = true;
    				} else {
    					$item['disabled'] = false;
    				}
    
    				if (!in_array($item['type'], $types)) {
    					$types[] = $item['type'];
    				}
    				
    				if (!isset($item['display'])) {
    					$item['display'] = $itemKey;
    				}
    				
    				// reference to the actual module
    				$item['module'] =& $active_modules[$key];
    				
    				// item is an assoc array, with at least array(module=> name=>, category=>, type=>, display=>)
    				$fpbx_menu[$itemKey] = $item;
    				
    				// allow a module to replace our main index page
    				if (($item['display'] == 'index') && ($display == '')) {
    					$display = 'index';
    				}
    				
	    			// check current item
    				if ($display == $item['display']) {
    					// found current menuitem, make a reference to it 
    					$cur_menuitem =& $fpbx_menu[$itemKey];
    				}
    			}
    		}
    	}
    }
    sort($types);

    // new gui hooks
    // Este bloque al parecer almacena en el arreglo configpageinits el nombre de ciertas funciones 
    // estandar que se deben cargar si es que se encuentran.
    // Al parecer no todo modulo las trae, es opcional. Por eso se buscan en todos los modulos...
    if(is_array($active_modules)){
    	foreach($active_modules as $key => $module) {
    		if (isset($module['items']) && is_array($module['items'])) {
    			foreach($module['items'] as $itemKey => $itemName) {
    				//list of potential _configpageinit functions
    				$initfuncname = $key . '_' . $itemKey . '_configpageinit';
    				if ( function_exists($initfuncname) ) {
    					$configpageinits[] = $initfuncname;
    				} 
    			}
    		}
    		//check for module level (rather than item as above) _configpageinit function
    		$initfuncname = $key . '_configpageinit';
    		if ( function_exists($initfuncname) ) {
    			$configpageinits[] = $initfuncname;
    		}
    	}
    }
    
    // extensions vs device/users ... this is a bad design, but hey, it works
    // Este bloque distingue entre si mostrar el menu de extensiones o en su lugar
    // mostrar los menus de devices y users. Esto es porque existe la posibilidad de 
    // disasociar dispositivos de usuarios.
    if (isset($amp_conf["AMPEXTENSIONS"]) && ($amp_conf["AMPEXTENSIONS"] == "deviceanduser")) {
    	unset($fpbx_menu["extensions"]);
    } else {
    	unset($fpbx_menu["devices"]);
    	unset($fpbx_menu["users"]);
    }
    
    // check access
    if (!is_array($cur_menuitem) && $display != "") {
    	showview("noaccess");
    	exit;
    }
    
    // load the component from the loaded modules
    if ( $display != '' && isset($configpageinits) && is_array($configpageinits) ) {
    
    	$currentcomponent = new component($display,$type);
        $GLOBALS['currentcomponent']=$currentcomponent;
    
    	// call every modules _configpageinit function which should just
    	// register the gui and process functions for each module, if relevent
    	// for this $display
    	foreach ($configpageinits as $func) {
    		$func($display);
    	}
    	// now run each 'process' function and 'gui' function
    	$currentcomponent->processconfigpage();
    	$currentcomponent->buildconfigpage();
    }
    //  note: we buffer all the output from the 'page' being loaded..
    // This may change in the future, with proper returns, but for now, it's a simple 
    // way to support the old page.item.php include module format.
    
    ob_start();

    $module_name = "";
    $module_page = "";
    $module_file = "";
    
    // hack to have our default display handler show the "welcome" view 
    // Note: this probably isn't REALLY needed if there is no menu item for "Welcome"..
    // but it doesn't really hurt, and it provides a handler in case some page links
    // to "?display=index"
    if (($display == 'index') && ($cur_menuitem['module']['rawname'] == 'builtin')) {
    	$display = '';
    }
    
    
    // show the appropriate page
    switch($display) {
    	default:
    		//display the appropriate module page
    		$module_name = $cur_menuitem['module']['rawname'];
    		$module_page = $cur_menuitem['display'];
    		$module_file = '/var/www/html/admin/modules/'.$module_name.'/page.'.$module_page.'.php';
    
    		//TODO Determine which item is this module displaying. Currently this is over the place, we should standarize on a "itemid" request var for now, we'll just cover all possibilities :-(
    		$possibilites = array(
    			'userdisplay',
    			'extdisplay',
    			'id',
    			'itemid',
    			'category',
    			'selection'
    		);
    		$itemid = '';
    		foreach($possibilites as $possibility) {
    			if ( isset($_REQUEST[$possibility]) && $_REQUEST[$possibility] != '' ) 
    				$itemid = $_REQUEST[$possibility];
    		}
    
    		// create a module_hook object for this module's page
    		$module_hook = new moduleHook;
    		
    		// populate object variables
    		$module_hook->install_hooks($module_page,$module_name,$itemid);
    
    		// let hooking modules process the $_REQUEST
    		$module_hook->process_hooks($itemid, $module_name, $module_page, $_REQUEST);
    
    
    		// include the module page
    		if (isset($cur_menuitem['disabled']) && $cur_menuitem['disabled']) {
    			showview("menuitem_disabled",$cur_menuitem);
    			break; // we break here to avoid the generateconfigpage() below
    		} else if (file_exists($module_file)) {
    			// load language info if available
    			if (extension_loaded('gettext')) {
    				if (is_dir("/var/www/html/admin/modules/{$module_name}/i18n")) {
    					bindtextdomain($module_name,"/var/www/html/admin/modules/{$module_name}/i18n");
    					bind_textdomain_codeset($module_name, 'utf8');
    					textdomain($module_name);
    				}
    			}
			// Aqui es donde se dibuja el GUI
        		include($module_file);
	    	} else {
    			// TODO: make this a showview()
			    echo "404 Not found";
    		}
    		
    		// global component
    		if ( isset($currentcomponent) ) {
    			echo $currentcomponent->generateconfigpage();
    		}
    
    	break;
    	case 'modules':
    		// set these to avoide undefined variable warnings later
    		//
    		$module_name = 'modules';
    		$module_page = $cur_menuitem['display'];
    		include '/var/www/html/admin/page.modules.php';
    	break;
    	case '':
	    	if ($astman) {
    			showview('welcome', array('AMP_CONF' => &$amp_conf));
    		} else {
    			// no manager, no connection to asterisk
    			showview('welcome_nomanager', array('mgruser' => $amp_conf["AMPMGRUSER"]));
    		}
    	break;
    }
    
    //$salida .= @ob_get_flush();
    $htmlFPBX .= @ob_get_contents();
    ob_end_clean();

    if(check_reload_needed()) {
        $salida .= "<table border=0 cellpadding=2 cellspacing=0 align='center' width='100%'><tr bgcolor='#f6bbbb'><td align='center'><a href='".$_SERVER['REQUEST_URI']."&handler=reload'>Apply Configuration Changes Here</a></td></tr></table>";
    }

    $smarty->assign("htmlFPBX", $htmlFPBX);
    $salida .= $smarty->fetch("$local_templates_dir/main.tpl");
    return $salida;
}
?>
