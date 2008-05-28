<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.0                                                  |
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
  $Id: index.php,v 1.1 2008/01/30 15:55:57 bmacias Exp $ */

function _moduleContent(&$smarty, $module_name)
{
    //include elastix framework
    include_once "libs/paloSantoGrid.class.php";
    include_once "libs/paloSantoValidar.class.php";
    include_once "libs/paloSantoConfig.class.php";
    include_once "libs/misc.lib.php";
    include_once "libs/paloSantoForm.class.php";

    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoAdressBook.class.php";
    global $arrConf;
    global $arrLang;

    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConfig['templates_dir']))?$arrConfig['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    $pDB = new paloDB("sqlite3:////var/www/db/address_book.db");

    $action = getAction();
    $content = "";

    switch($action)
    {
        case "new":
            $content = new_adress_book($smarty,$module_name, $local_templates_dir, $pDB, $arrLang, $arrConfig);
            break;
        case "cancel":
            header("Location: ?menu=$module_name");
            break;
    case "commit":
            $content = save_adress_book($smarty,$module_name, $local_templates_dir, $pDB, $arrLang, $arrConfig,true);
            break;
    case "edit":
            $content = view_adress_book($smarty,$module_name, $local_templates_dir, $pDB, $arrLang, $arrConfig);
            break;
        case "show":
            $content = view_adress_book($smarty,$module_name, $local_templates_dir, $pDB, $arrLang, $arrConfig);
            break;
    case "save":
        $content = save_adress_book($smarty,$module_name, $local_templates_dir, $pDB, $arrLang, $arrConfig);
            break;
    case "delete":
        $content = deleteContact($smarty,$module_name, $local_templates_dir, $pDB, $arrLang, $arrConfig);
            break;
        default:
            $content = report_adress_book($smarty,$module_name, $local_templates_dir, $pDB, $arrLang, $arrConfig);
            break;
    }

    return $content;
}

function new_adress_book($smarty, $module_name, $local_templates_dir, $pDB, $arrLang, $arrConfig)
{
    $arrFormadress_book = createFieldForm($arrLang);
    $oForm = new paloForm($smarty,$arrFormadress_book);

    $smarty->assign("Show", 1);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("TITLE", $arrLang["Address Book"]);

    $padress_book = new paloAdressBook($pDB);

    $htmlForm = $oForm->fetchForm("$local_templates_dir/new_adress_book.tpl", "", $_POST);

    $contenidoModulo = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $contenidoModulo;
}

/*
******** Funciones del modulo
*/
function report_adress_book($smarty, $module_name, $local_templates_dir, $pDB, $arrLang, $arrConfig)
{
    $arrComboElements = array("name"        =>$arrLang["Name"],
                  "last_name"   =>$arrLang["Last Name"],
                  "telefono"    =>$arrLang["Phone Number"]);

    $arrFormElements = array(   "field" => array(  "LABEL"                   => $arrLang["Filter"],
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrComboElements,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),

                "pattern" => array( "LABEL"          => "",
                            "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => "",
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                                );

    $oFilterForm = new paloForm($smarty, $arrFormElements);
    $smarty->assign("SHOW", $arrLang["Show"]);
    $smarty->assign("NEW_adress_book", $arrLang["New Contact"]);

    $field   = NULL;
    $pattern = NULL;

    if(isset($_POST['field']) and isset($_POST['pattern'])){
    $field      = $_POST['field'];
    $pattern    = $_POST['pattern'];
    }
    

    $startDate = $endDate = date("Y-m-d H:i:s");

    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter_adress_book.tpl", "", $_POST);

    $padress_book = new paloAdressBook($pDB);
    $total_datos =$padress_book->getAddressBook(NULL,NULL,$field,$pattern,TRUE);
 
    //Paginacion
    $limit  = 8;
    $total  = $total_datos[0]["total"];

    $oGrid  = new paloSantoGrid($smarty);
    $offset = $oGrid->getOffSet($limit,$total,(isset($_GET['nav']))?$_GET['nav']:NULL,(isset($_GET['start']))?$_GET['start']:NULL);

    $end    = ($offset+$limit)<=$total ? $offset+$limit : $total;

    $url = "?menu=$module_name&filter=$pattern";
    $smarty->assign("url", $url);
    //Fin Paginacion

    $arrResult =$padress_book->getAddressBook($limit, $offset, $field, $pattern);

    //echo "<pre>".print_r($arrResult,true)."</pre>";

    $arrData = null;
    if(is_array($arrResult) && $total>0){
        foreach($arrResult as $key => $adress_book){
            $arrTmp[0]  = "<input type='checkbox' name='contact_{$adress_book['id']}'  />";
            $arrTmp[1]  = "<a href='?menu=$module_name&action=show&id=".$adress_book['id']."'>{$adress_book['name']}</a>";
        $arrTmp[2]  = $adress_book['last_name'];
            $arrTmp[3]  = $adress_book['telefono'];
            $arrTmp[4]  = $adress_book['extension'];
            $arrTmp[5]  = $adress_book['email'];
            $arrData[]  = $arrTmp;
        }
    }

    $arrGrid = array(   "title"    => $arrLang["Address Book"],
                        "icon"     => "images/list.png",
                        "width"    => "99%",
                        "start"    => ($total==0) ? 0 : $offset + 1,
                        "end"      => $end,
                        "total"    => $total,
                        "columns"  => array(0 => array("name"      => "<input type='submit' name='delete' value='{$arrLang["Delete"]}' class='button' onclick=\" return confirmSubmit('{$arrLang["Are you sure you wish to delete the contact."]}');\" />",
                                                    "property1" => ""),
                                            1 => array("name"      => $arrLang["First Name"],
                            "property1" => ""), 
                            2 => array("name"      => $arrLang["Last Name"],
                                                    "property1" => ""),
                                            3 => array("name"      => $arrLang["Phone Number"],
                                                    "property1" => ""),
                                            4=> array("name"      => $arrLang["Extension"],
                                                    "property1" => ""),
                                            5=> array("name"      => $arrLang["Email"],
                                                    "property1" => "")
                                        )
                    );

    $oGrid->showFilter(trim($htmlFilter));
    $contenidoModulo = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$oGrid->fetchGrid($arrGrid, $arrData,$arrLang)."</form>";

    return $contenidoModulo;
}

function createFieldForm($arrLang)
{
    $arrFields =       array("name"      => array("LABEL"                  => $arrLang["First Name"],
                                                          "REQUIRED"               => "yes",
                                                          "INPUT_TYPE"             => "TEXT",
                                                          "INPUT_EXTRA_PARAM"      => array("style" => "width:300px;"),
                                                          "VALIDATION_TYPE"        => "text",
                                                          "VALIDATION_EXTRA_PARAM" => ""),
                 "last_name"              => array("LABEL"             => $arrLang["Last Name"],
                                                          "REQUIRED"               => "yes",
                                                          "INPUT_TYPE"             => "TEXT",
                                                          "INPUT_EXTRA_PARAM"      => array("style" => "width:300px;"),
                                                          "VALIDATION_TYPE"        => "text",
                                                          "VALIDATION_EXTRA_PARAM" => ""),
                             "telefono"      => array("LABEL"                  => $arrLang["Phone Number"],
                                                          "REQUIRED"               => "yes",
                                                          "INPUT_TYPE"             => "TEXT",
                                                          "INPUT_EXTRA_PARAM"      => "",
                                                          "VALIDATION_TYPE"        => "ereg",
                                                          "VALIDATION_EXTRA_PARAM" => "([[:digit:]]|-){1,}"),
                            "extension"      => array("LABEL"                  => $arrLang["Extension"],
                                                          "REQUIRED"               => "no",
                                                          "INPUT_TYPE"             => "TEXT",
                                                          "INPUT_EXTRA_PARAM"      => "",
                                                          "VALIDATION_TYPE"        => "ereg",
                                                          "VALIDATION_EXTRA_PARAM" => "[[:digit:]]{1,}"),
                "email"              => array("LABEL"                  => $arrLang["Email"],
                                                          "REQUIRED"               => "no",
                                                          "INPUT_TYPE"             => "TEXT",
                                                          "INPUT_EXTRA_PARAM"      => "",
                                                          "VALIDATION_TYPE"        => "ereg",
                                                          "VALIDATION_EXTRA_PARAM" => "([[:alnum:]]|.|_|-){1,}@([[:alnum:]]|.|_|-){1,}")
                        );
    return $arrFields;
}

function save_adress_book($smarty, $module_name, $local_templates_dir, $pDB, $arrLang, $arrConfig,$update=FALSE)
{
    $arrForm = createFieldForm($arrLang);
    $oForm = new paloForm($smarty, $arrForm);

    $bandera = true;

    if(!$oForm->validateForm($_POST)) {
        // Falla la validación básica del formulario
        $smarty->assign("mb_title", $arrLang["Validation Error"]);
        $arrErrores = $oForm->arrErroresValidacion;
        $strErrorMsg = "<b>{$arrLang['The following fields contain errors']}:</b><br/>";
        if(is_array($arrErrores) && count($arrErrores) > 0){
            foreach($arrErrores as $k=>$v) {
                $strErrorMsg .= "$k, ";
            }
        }

        $smarty->assign("mb_message", $strErrorMsg);

        $smarty->assign("Show", 1);
        $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
        $smarty->assign("SAVE", $arrLang["Save"]);
        $smarty->assign("CANCEL", $arrLang["Cancel"]);
        $smarty->assign("TITLE", $arrLang["Address Book"]);

        $htmlForm = $oForm->fetchForm("$local_templates_dir/new_adress_book.tpl", $arrLang, $_POST);

        $contenidoModulo = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

        return $contenidoModulo;
    }else{
        $data = array();

        $data['name']       = $pDB->DBCAMPO($_POST['name']);
    $data['last_name']  = $pDB->DBCAMPO($_POST['last_name']);
        $data['telefono']   = $pDB->DBCAMPO($_POST['telefono']);
        $data['extension']  = $pDB->DBCAMPO($_POST['extension']);
        $data['email']      = $pDB->DBCAMPO($_POST['email']);

        $padress_book = new paloAdressBook($pDB);
    
    if($update)
        $result = $padress_book->updateContact($data,array("id"=>$_POST['id']));
    else
            $result = $padress_book->addContact($data);

    if(!$result)
        return($pDB->errMsg);

    //'?menu=$module_name&action=show&id=".$adress_book['id']."'
    if($_POST['id'])
            header("Location: ?menu=$module_name&action=show&id=".$_POST['id']);
    else
        header("Location: ?menu=$module_name");
    }
}

function deleteContact($smarty, $module_name, $local_templates_dir, $pDB, $arrLang, $arrConfig)
{
    $padress_book = new paloAdressBook($pDB);

    foreach($_POST as $key => $values){
        if(substr($key,0,8) == "contact_")
        {
            $tmpBookID = substr($key, 8);
            $result = $padress_book->deleteContact($tmpBookID);
        }
    }
    $content = report_adress_book($smarty,$module_name, $local_templates_dir, $pDB, $arrLang, $arrConfig);

    return $content;
}

function view_adress_book($smarty, $module_name, $local_templates_dir, $pDB, $arrLang, $arrConfig)
{
    $arrFormadress_book = createFieldForm($arrLang);
    $oForm = new paloForm($smarty,$arrFormadress_book);

    if(isset($_POST["edit"])){
    $oForm->setEditMode();
        $smarty->assign("Commit", 1);
    $smarty->assign("SAVE",$arrLang["Save"]);
    }else{
        $oForm->setViewMode();
    $smarty->assign("Edit", 1);
    }
    $smarty->assign("EDIT", $arrLang["Edit"]);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("TITLE", $arrLang["Address Book"]);
    $smarty->assign("FirstName",$arrLang["First Name"]);
    $smarty->assign("LastName",$arrLang["Last Name"]);
    $smarty->assign("PhoneNumber",$arrLang["Phone Number"]);
    $smarty->assign("Extension",$arrLang["Extension"]);
    $smarty->assign("Email",$arrLang["Email"]);
    
    $padress_book = new paloAdressBook($pDB);
    $id = isset($_GET['id'])?$_GET['id']:(isset($_POST['id'])?$_POST['id']:"");

    $contactData = $padress_book->contactData($id);

    $smarty->assign("ID",$id);

    $arrData['id']          = $contactData['id'];
    $arrData['name']            = $contactData['name'];
    $arrData['last_name']       = $contactData['last_name'];
    $arrData['telefono']        = $contactData['telefono'];
    $arrData['extension']       = $contactData['extension'];
    $arrData['email']           = $contactData['email'];

    $htmlForm = $oForm->fetchForm("$local_templates_dir/new_adress_book.tpl", "", $arrData);

    $contenidoModulo = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $contenidoModulo;
}
/*
******** Fin
*/


function getAction()
{
    if(getParametro("edit"))
        return "edit"; 
    else if(getParametro("commit"))
        return "commit";
    else if(getParametro("show"))
        return "show";
    else if(getParametro("delete"))
        return "delete";
    else if(getParametro("new"))
        return "new";
    else if(getParametro("save"))
    return "save";
    else if(getParametro("delete"))
        return "delete";
    else if(getParametro("action")=="show")
    return "show";
    else
        return "report";
}

function getParametro($parametro)
{
    if(isset($_POST[$parametro]))
        return $_POST[$parametro];
    else if(isset($_GET[$parametro]))
        return $_GET[$parametro];
    else
        return null;
}
?>