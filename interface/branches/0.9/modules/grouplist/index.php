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
  $Id: index.php,v 1.1.1.1 2007/07/06 21:31:56 afigueroa Exp $ */

function _moduleContent(&$smarty, $module_name)
{
    include_once("libs/paloSantoDB.class.php");
    include_once("libs/paloSantoConfig.class.php");
    include_once("libs/paloSantoGrid.class.php");
    include_once("libs/paloSantoACL.class.php");
    global $arrLang;
    $pDB = new paloDB("sqlite3:////var/www/db/acl.db");

/////conexion a php
//include module files
    include_once "modules/$module_name/configs/default.conf.php";
    global $arrConf;
    global $arrLang;
    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConfig['templates_dir']))?$arrConfig['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];
    
    $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
    $arrConfig = $pConfig->leer_configuracion(false);

    $dsn = $arrConfig['AMPDBENGINE']['valor'] . "://" . $arrConfig['AMPDBUSER']['valor'] . ":" . $arrConfig['AMPDBPASS']['valor'] . "@" . $arrConfig['AMPDBHOST']['valor'] . "/asterisk";
    $pDBa     = new paloDB($dsn);

////////////////////

    if(!empty($pDB->errMsg)) {
        echo "ERROR DE DB: $pDB->errMsg <br>";
    }

    $arrData = array();
    $pACL = new paloACL($pDB);
    if(!empty($pACL->errMsg)) {
        echo "ERROR DE ACL: $pACL->errMsg <br>";
    }

    $arrFormElements = array("description" => array("LABEL"                  => $arrLang["Description"],
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => "",
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "group"       => array("LABEL"                  => $arrLang["Group"],
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => "",
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => "")
    );

//description  id  name

    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("APPLY_CHANGES", $arrLang["Apply changes"]);
    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("EDIT", $arrLang["Edit"]);
    $smarty->assign("DELETE", $arrLang["Delete"]);
    $smarty->assign("CONFIRM_CONTINUE", $arrLang["Are you sure you wish to continue?"]);
    if(isset($_POST['submit_create_group'])) {
        // Implementar
        include_once("libs/paloSantoForm.class.php");
        $arrFillGroup['group']       = '';
        $arrFillGroup['description'] = '';
        $oForm = new paloForm($smarty, $arrFormElements);
        $contenidoModulo=$oForm->fetchForm("$local_templates_dir/grouplist.tpl", $arrLang["New Group"],$arrFillGroup);
    } else if(isset($_POST['edit'])) {

        // Tengo que recuperar la data del usuario
        $pACL = new paloACL($pDB);

        $arrGroup = $pACL->getGroups($_POST['id_group']);

        $arrFillGroup['group'] = $arrGroup[0][1];
        $arrFillGroup['description'] = $arrGroup[0][2];

        // Implementar
        include_once("libs/paloSantoForm.class.php");
        $oForm = new paloForm($smarty, $arrFormElements);

        $oForm->setEditMode();
        $smarty->assign("id_group", $_POST['id_group']);
        $contenidoModulo=$oForm->fetchForm("$local_templates_dir/grouplist.tpl", "{$arrLang['Edit Group']} \"" . $arrFillGroup['group'] . "\"", $arrFillGroup);

    } else if(isset($_POST['submit_save_group'])) {

        include_once("libs/paloSantoForm.class.php");

        $oForm = new paloForm($smarty, $arrFormElements);

        if($oForm->validateForm($_POST)) {
            // Exito, puedo procesar los datos ahora.
            $pACL = new paloACL($pDB);

            // Creo el Grupo
            $pACL->createGroup($_POST['group'], $_POST['description']);

            if(!empty($pACL->errMsg)) {
                // Ocurrio algun error aqui
                $smarty->assign("mb_message", "ERROR: $pACL->errMsg");
                $contenidoModulo=$oForm->fetchForm("$local_templates_dir/grouplist.tpl", $arrLang["New Group"], $_POST);
            } else {
                header("Location: ?menu=grouplist");
            }
        } else {
            // Error
            $smarty->assign("mb_title", $arrLang["Validation Error"]);
            $arrErrores=$oForm->arrErroresValidacion;
            $strErrorMsg = "<b>{$arrLang['The following fields contain errors']}:</b><br>";
            foreach($arrErrores as $k=>$v) {
                $strErrorMsg .= "$k, ";
            }
            $strErrorMsg .= "";
            $smarty->assign("mb_message", $strErrorMsg);
            $contenidoModulo=$oForm->fetchForm("$local_templates_dir/grouplist.tpl", $arrLang["New Group"], $_POST);
        }

    } else if(isset($_POST['submit_apply_changes'])) {

        $arrGroup = $pACL->getGroups($_POST['id_group']);
        $group = $arrGroup[0][1];
        $description = $arrGroup[0][2];

        include_once("libs/paloSantoForm.class.php");
        $oForm = new paloForm($smarty, $arrFormElements);

        $oForm->setEditMode();
        if($oForm->validateForm($_POST)) {

            // Exito, puedo procesar los datos ahora.
            $pACL = new paloACL($pDB);

            if(!$pACL->updateGroup($_POST['id_group'], $_POST['group'],$_POST['description']))
            {
                // Ocurrio algun error aqui
                $smarty->assign("mb_message", "ERROR: $pACL->errMsg");
                $contenidoModulo=$oForm->fetchForm("$local_templates_dir/grouplist.tpl", $arrLang["Edit Group"], $_POST);
            } else {
                header("Location: ?menu=grouplist");
            }
        } else {
            // Manejo de Error
            $smarty->assign("mb_title", $arrLang["Validation Error"]);
            $arrErrores=$oForm->arrErroresValidacion;
            $strErrorMsg = "<b>{$arrLang['The following fields contain errors']}:</b><br>";
            foreach($arrErrores as $k=>$v) {
                $strErrorMsg .= "$k, ";
            }
            $strErrorMsg .= "";
            $smarty->assign("mb_message", $strErrorMsg);

            $arrFillGroup['group']       = $_POST['group'];
            $arrFillGroup['description'] = $_POST['description'];
            $smarty->assign("id_group", $_POST['id_group']);
            $contenidoModulo=$oForm->fetchForm("$local_templates_dir/grouplist.tpl", $arrLang["Edit Group"], $arrFillGroup);
        }
    } else if(isset($_GET['action']) && $_GET['action']=="view") {

        include_once("libs/paloSantoForm.class.php");

        $oForm = new paloForm($smarty, $arrFormElements);

        //- TODO: Tengo que validar que el id sea valido, si no es valido muestro un mensaje de error

        $oForm->setViewMode(); // Esto es para activar el modo "preview"
        $arrGroup = $pACL->getGroups($_GET['id']);

        // Conversion de formato
        $arrTmp['group']        = $arrGroup[0][1];
        $arrTmp['description']  = $arrGroup[0][2];

        $smarty->assign("id_group", $_GET['id']);
        $contenidoModulo=$oForm->fetchForm("$local_templates_dir/grouplist.tpl", $arrLang["View Group"], $arrTmp); // hay que pasar el arreglo
    } else {
        if (isset($_POST['delete'])) {
           //- TODO: Validar el id de group
            if(isset($_POST['id_group']) && $_POST['id_group']=='1') {
                // No se puede eliminar al grupo admin
                $smarty->assign("mb_message", $arrLang["The administrator group cannot be deleted because is the default Elastix Group. You can delete any other group."]);
            } else if ($pACL->HaveUsersTheGroup($_POST['id_group'])==TRUE){
                $smarty->assign("mb_message", $arrLang["The Group have users assigned. You can delete any group that does not have any users assigned in it."]);
            } else {
                $pACL->deleteGroup($_POST['id_group']);
            }
        }

        $arrGroups = $pACL->getGroups();

        $end = count($arrGroups);
        $arrData = array();
        foreach($arrGroups as $group) {
            $arrTmp    = array();

            $arrTmp[0] = "&nbsp;<a href='?menu=grouplist&action=view&id=" . $group[0] . "'>" . $group[1] . "</a>";//id_group   name
            $arrTmp[1] = $group[2];//description
            $arrData[] = $arrTmp;
        }

        $arrGrid = array("title"    => $arrLang["Group List"],
                         "icon"     => "images/user.png",
                         "width"    => "99%",
                         "start"    => ($end==0) ? 0 : 1,
                         "end"      => $end,
                         "total"    => $end,
                         "columns"  => array(0 => array("name"      => $arrLang["Group"],
                                                        "property1" => ""),
                                             1 => array("name"      => $arrLang["Description"],
                                                        "property1" => "")
                                            )
                        );

        $oGrid = new paloSantoGrid($smarty);
        $oGrid->showFilter("<form style='margin-bottom:0;' method='POST' action='?menu=grouplist'>" .
                           "<input type='submit' name='submit_create_group' value='{$arrLang['Create New Group']}' class='button'></form>");
        $contenidoModulo = $oGrid->fetchGrid($arrGrid, $arrData,$arrLang);
    }

    return $contenidoModulo;
}
?>