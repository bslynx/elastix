<?php
/*
  vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2003 Palosanto Solutions S. A.                    |
  +----------------------------------------------------------------------+
  | Cdla. Nueva Kennedy Calle E 222 y 9na. Este                          |
  | Telfs. 2283-268, 2294-440, 2284-356                                  |
  | Guayaquil - Ecuador                                                  |
  +----------------------------------------------------------------------+
  | Este archivo fuente está sujeto a las políticas de licenciamiento    |
  | de Palosanto Solutions S. A. y no está disponible públicamente.      |
  | El acceso a este documento está restringido según lo estipulado      |
  | en los acuerdos de confidencialidad los cuales son parte de las      |
  | políticas internas de Palosanto Solutions S. A.                      |
  | Si Ud. está viendo este archivo y no tiene autorización explícita    |
  | de hacerlo, comuníquese con nosotros, podría estar infringiendo      |
  | la ley sin saberlo.                                                  |
  +----------------------------------------------------------------------+
  | Autores: Alex Villacís Lasso <a_villacis@palosanto.com>              |
  +----------------------------------------------------------------------+
  $Id: index.php,v 1.1 2007/01/09 23:49:36 alex Exp $
*/
require_once "libs/paloSantoForm.class.php";
require_once "libs/paloSantoGrid.class.php";
require_once 'libs/paloSantoIncomingCampaign.class.php';

if (!function_exists('_tr')) {
    function _tr($s)
    {
        global $arrLang;
        return isset($arrLang[$s]) ? $arrLang[$s] : $s;
    }
}
if (!function_exists('load_language_module')) {
    function load_language_module($module_id, $ruta_base='')
    {
        $lang = get_language($ruta_base);
        include_once $ruta_base."modules/$module_id/lang/en.lang";
        $lang_file_module = $ruta_base."modules/$module_id/lang/$lang.lang";
        if ($lang != 'en' && file_exists("$lang_file_module")) {
            $arrLangEN = $arrLangModule;
            include_once "$lang_file_module";
            $arrLangModule = array_merge($arrLangEN, $arrLangModule);
        }

        global $arrLang;
        global $arrLangModule;
        $arrLang = array_merge($arrLang,$arrLangModule);
    }
}
function _moduleContent(&$smarty, $module_name)
{
    $script_dir=dirname($_SERVER['SCRIPT_FILENAME']);

    load_language_module($module_name);
    
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";

    global $arrConf;
    global $arrConfig;

    //folder path for custom templates
    $base_dir = dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir = (isset($arrConfig['templates_dir']))?$arrConfig['templates_dir']:'themes';
    $local_templates_dir = "$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];
    $relative_dir_rich_text = "modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];
    $smarty->assign("relative_dir_rich_text", $relative_dir_rich_text);

    // Conexión a la base de datos CallCenter
    $pDB = new paloDB($arrConf['cadena_dsn']);

    // Mostrar pantalla correspondiente
    $contenidoModulo = '';
    $sAction = 'list_campaign';
    if (isset($_GET['action'])) $sAction = $_GET['action'];
    switch ($sAction) {
    case 'new_campaign':
        $contenidoModulo = newCampaign($pDB, $smarty, $module_name, $local_templates_dir);
        break;
    case 'edit_campaign':
        $contenidoModulo = editCampaign($pDB, $smarty, $module_name, $local_templates_dir);
        break;
    case 'csv_data':
        $contenidoModulo = displayCampaignCSV($pDB, $smarty, $module_name, $local_templates_dir);
        break;
    case 'list_campaign':
    default:
        $contenidoModulo = listCampaign($pDB, $smarty, $module_name, $local_templates_dir);
        break;
    }

    return $contenidoModulo;
}

function listCampaign($pDB, $smarty, $module_name, $local_templates_dir)
{
    global $arrLang;
    $arrData = '';
    $oCampaign = new paloSantoIncomingCampaign($pDB);

    // Recoger ID de campaña para operación
    $id_campaign = NULL;
    if (isset($_POST['id_campaign']) && ereg('^[[:digit:]]+$', $_POST['id_campaign']))
        $id_campaign = $_POST['id_campaign'];

    // Revisar si se debe de borrar una campaña elegida
    if (isset($_POST['delete']) && !is_null($id_campaign)) {
        if($oCampaign->delete_campaign($id_campaign)) {
            if ($oCampaign->errMsg!="") {
                $smarty->assign("mb_title",_tr('Validation Error'));
                $smarty->assign("mb_message", $oCampaign->errMsg);
            } else {
            }
        } else {
            $msg_error = ($oCampaign->errMsg!="") ? "<br/>".$oCampaign->errMsg:"";
            $smarty->assign("mb_title", _tr('Delete Error'));
            $smarty->assign("mb_message", _tr('Error when deleting the Campaign').$msg_error);
        }
    }

    // Revisar si se debe activar una campaña elegida
    if (isset($_POST['activate']) && !is_null($id_campaign)) {
        if(!$oCampaign->activar_campaign($id_campaign, 'A')) {
            $smarty->assign("mb_title", _tr('Activate Error'));
            $smarty->assign("mb_message", _tr('Error when Activating the Campaign'));
        }
    }

    // Revisar si se debe desactivar una campaña elegida
    if (isset($_POST['deactivate']) && !is_null($id_campaign)) {
        if(!$oCampaign->activar_campaign($id_campaign, 'I')) {
            $smarty->assign("mb_title", _tr('Deactivate Error'));
            $smarty->assign("mb_message", _tr('Error when deactivating the Campaign'));
        }
    }

    // Validar el filtro por estado de actividad de la campaña
    $estados = array(
        "all" => _tr('All'), 
        "A" => _tr('Active'), 
        "I" => _tr('Inactive')
    );
    $sEstado = 'A';
    if (isset($_GET['cbo_estado']) && isset($estados[$_GET['cbo_estado']])) {
        $sEstado = $_GET['cbo_estado'];
    }
    if (isset($_POST['cbo_estado']) && isset($estados[$_POST['cbo_estado']])) {
        $sEstado = $_POST['cbo_estado'];
    }

    // para el pagineo
    $limit = 50;
    $offset = 0;

    $url = construirURL(
        array('menu' => $module_name, 'cbo_estado' => $sEstado),
        array('nav', 'start'));

    //$arrCampaign = $oCampaign->getCampaigns(null, $offset, NULL, $sEstado);
    $total = $oCampaign->countCampaigns($sEstado);
    if (is_null($total)) {
        $smarty->assign("mb_title", _tr('Read Error'));
        $smarty->assign("mb_message", _tr('Unable to count campaigns').' - '.$arrCampaign->errMsg);
        $total = 0;
    }
//    $total  = count($arrCampaign);

    // Si se quiere avanzar a la sgte. pagina
    if(isset($_GET['nav']) && $_GET['nav']=="end") {
        // Mejorar el sgte. bloque.
        if(($total%$limit)==0) {
            $offset = $total - $limit;
        } else {
            $offset = $total - $total%$limit;
        }
    }

    // Si se quiere avanzar a la sgte. pagina
    if(isset($_GET['nav']) && $_GET['nav']=="next") {
        $offset = $_GET['start'] + $limit - 1;
    }

    // Si se quiere retroceder
    if(isset($_GET['nav']) && $_GET['nav']=="previous") {
        $offset = $_GET['start'] - $limit - 1;
    }

    $arrCampaign = $oCampaign->getCampaigns($limit, $offset, NULL, $sEstado);
    if (is_null($arrCampaign)) {
        $smarty->assign("mb_title", _tr('Read Error'));
        $smarty->assign("mb_message", _tr('Unable to read campaigns').' - '.$arrCampaign->errMsg);
    }

    $end = count($arrCampaign);

    if (is_array($arrCampaign)) {
        foreach($arrCampaign as $campaign) {
            $arrTmp    = array();
            $arrTmp[0] = "<input class=\"button\" type=\"radio\" name=\"id_campaign\" value=\"$campaign[id]\" />";
            $arrTmp[1] = $campaign['name'];
            $arrTmp[2] = $campaign['datetime_init'].' - '.$campaign['datetime_end'];
            $arrTmp[3] = $campaign['daytime_init'].' - '.$campaign['daytime_end'];
            $arrTmp[4] = $campaign['queue'];
            $arrTmp[5] = ($campaign['num_completadas']!="") ? $campaign['num_completadas'] : "N/A";
            $arrTmp[6] = ($campaign['promedio']!="") ? number_format($campaign['promedio'],0) : "N/A";

            $csv_data = "&nbsp;<a href='?menu=$module_name&amp;action=csv_data&amp;id_campaign=".$campaign['id']."&amp;rawmode=yes'>["._tr('CSV Data')."]</a>";
            $ver_campania = "&nbsp;<a href='?menu=$module_name&amp;action=edit_campaign&amp;id_campaign=".$campaign['id']."'>["._tr('Edit')."]</a>";
            if($campaign['estatus']=='I'){
                $arrTmp[7] = _tr('Inactive');
                $arrTmp[8] = $ver_campania.$csv_data;
            } elseif($campaign['estatus']=='A'){
                $arrTmp[7] = _tr('Active');
                $arrTmp[8] = $ver_campania.$csv_data;
            }
            $arrData[] = $arrTmp;
        }
    }

    // Definición de la tabla de las campañas
    $arrGrid = array("title"    => _tr('Campaigns List'),
        "url"      => $url,
        "icon"     => "images/list.png",
        "width"    => "99%",
        "start"    => ($total==0) ? 0 : $offset + 1,
        "end"      => ($offset+$limit)<=$total ? $offset+$limit : $total,
        "total"    => $total,
        "columns"  => array(
                            0 => array("name"      => ''),
                            1 => array("name"      => _tr('Campaign Name')),
                            2 => array("name"      => _tr('Range Date')),
                            3 => array("name"      => _tr('Schedule per Day')),
                            4 => array("name"      => _tr('Queue')),
                            5 => array("name"      => _tr('Completed Calls')),
                            6 => array("name"      => _tr('Average Time')),
                            7 => array("name"     => _tr('Status')),
                            8 => array("name"     => _tr('Options'))
        )
    );

    // Construir el HTML del filtro
    $smarty->assign(array(
        'MODULE_NAME'                   =>  $module_name,
        'LABEL_CAMPAIGN_STATE'          =>  _tr('Campaign state'),
        'estados'                       =>  $estados,
        'estado_sel'                    =>  $sEstado,
        'LABEL_CREATE_CAMPAIGN'         =>  _tr('Create New Campaign'),
        'LABEL_WITH_SELECTION'          =>  _tr('With selection'),
        'LABEL_ACTIVATE'                =>  _tr('Activate'),
        'LABEL_DEACTIVATE'              =>  _tr('Deactivate'),
        'LABEL_DELETE'                  =>  _tr('Delete'),
        'MESSAGE_CONTINUE_DEACTIVATE'   =>  _tr('Are you sure you wish to continue?'),
        'MESSAGE_CONTINUE_DELETE'       =>  _tr('Are you sure you wish to delete campaign?'),
    ));
    $oGrid = new paloSantoGrid($smarty);
    $oGrid->showFilter($smarty->fetch("$local_templates_dir/filter-list-campaign.tpl"));
    $sContenido = $oGrid->fetchGrid($arrGrid, $arrData,$arrLang);
    if (strpos($sContenido, '<form') === FALSE)
        $sContenido = "<form  method=\"POST\" style=\"margin-bottom:0;\" action=\"$url\">$sContenido</form>";
    return $sContenido;
}

?>