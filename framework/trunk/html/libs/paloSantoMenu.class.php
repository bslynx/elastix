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
  $Id: paloSantoMenu.class.php,v 1.2 2007/09/05 00:25:25 gcarrillo Exp $ */

if (isset($arrConf['basePath'])) {
    include_once($arrConf['basePath'] . "/libs/paloSantoDB.class.php");
} else {
    include_once("libs/paloSantoDB.class.php");
}

class paloMenu {

    var $_DB; // instancia de la clase paloDB
    var $errMsg;

    function paloMenu(&$pDB)
    {
        // Se recibe como parámetro una referencia a una conexión paloDB
        if (is_object($pDB)) {
            $this->_DB =& $pDB;
            $this->errMsg = $this->_DB->errMsg;
        } else {
            $dsn = (string)$pDB;
            $this->_DB = new paloDB($dsn);

            if (!$this->_DB->connStatus) {
                $this->errMsg = $this->_DB->errMsg;
                // debo llenar alguna variable de error
            } else {
                // debo llenar alguna variable de error
            }
        }
    }

    /**
     * Procedimiento para obtener el listado de los menus
     *
     * @return array    Listado de menus
     */
    function getRootMenus()
    {
        $this->errMsg = "";
        $listaMenus = array();
	$sQuery = "SELECT Id, Name FROM menu WHERE IdParent=''";
	$arrMenus = $this->_DB->fetchTable($sQuery);
        if (is_array($arrMenus)) {
	   foreach ($arrMenus as $menu)
            {
                $listaMenus[$menu[0]]=$menu[1];
            }
        }else
        {
            $this->errMsg = $this->_DB->errMsg;
        }
        return $listaMenus;

    }

    /**
     * Procedimiento para crear un nuevo menu 
     *
     * @param string    $id       
     * @param string    $name   
     * @param string    $id_parent       
     * @param string    $type         
     * @param string    $link  
     * @param string    $order
     *
     * @return bool     VERDADERO si el menu se crea correctamente, FALSO en error
     */

    function createMenu($id,$name, $id_parent, $type='module', $link='', $order=-1)
    {
        $bExito = FALSE;
        if ($id == "" && $name == "") {
            $this->errMsg = "ID and module name can't be empty";
        } else {
            //verificar que no exista el mismo menu
            $sPeticionSQL = "SELECT id FROM menu ".
                " WHERE id = '$id' AND Name='$name' AND IdParent='$id_parent'";
            $arr_result =& $this->_DB->fetchTable($sPeticionSQL);
            if (is_array($arr_result) && count($arr_result)>0) {
                $bExito = FALSE;
                $this->errMsg = "Menu already exists";
            }else{
                if($order!=-1)
                  $order = "\"order_no\"  =>  ".paloDB::DBCAMPO($order);
                else $order="";

                $sPeticionSQL = paloDB::construirInsert(
                    "menu",
                    array(
                        "id"        =>  paloDB::DBCAMPO($id),
                        "Name"      =>  paloDB::DBCAMPO($name),
                        "Type"      =>  paloDB::DBCAMPO($type),
                        "Link"      =>  paloDB::DBCAMPO($link),
                        "IdParent"  =>  paloDB::DBCAMPO($id_parent),
                        $order
                    )
                );
                if ($this->_DB->genQuery($sPeticionSQL)) {
                    $bExito = TRUE;
                } else {
                    $this->errMsg = $this->_DB->errMsg;
                }
            }
        }

        return $bExito;
    }

    function existeMenu($id_menu){
        $bExiste=false;
            //verificar que no exista el mismo menu
        $sPeticionSQL = "SELECT id FROM menu WHERE id = '$id_menu'";
        $arr_result =& $this->_DB->getFirstRowQuery($sPeticionSQL);
        if (count($arr_result)>0)
        {
            $bExiste=true;
        }
    }


}
?>
