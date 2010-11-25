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
  $Id: new_campaign.php $ */

include_once("libs/paloSantoDB.class.php");

/* Clase que implementa breaks */
class PaloSantoBreaks
{
    var $_DB; // instancia de la clase paloDB
    var $errMsg;

    function PaloSantoBreaks(&$pDB)
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
     * Procedimiento para obtener el listado de los breaks existentes. Si
     * se especifica id, el listado contendrá únicamente el break
     * indicada por el valor. De otro modo, se listarán todas los breaks.
     *
     * @param int   $id_break    Si != NULL, indica el id del break a recoger
     *
     * @return array    Listado de breaks en el siguiente formato, o FALSE en caso de error:
     *  array(
     *      array(id,name,description),....,
     *  )
     */
    function getBreaks($id_break = NULL,$estatus='all')
    {
        $arr_result = FALSE;
        $where = " 1 ";
        if(!is_null($id_break))
            $where = "id = $id_break ";
        if($estatus=='all')
            $where .=" and 1 ";
        else if($estatus=='I')
            $where .=" and status='I' ";
        else if($estatus=='A')
            $where .=" and status='A' ";

        if (!is_null($id_break) && !ereg('^[[:digit:]]+$', "$id_break")) {
            $this->errMsg = _tr("Break ID is not valid");
        } 
        else {
            $this->errMsg = "";
            $sPeticionSQL = "SELECT id, name, description,status from break where $where and tipo='B'"; 
            $arr_result =& $this->_DB->fetchTable($sPeticionSQL, true);
            if (!is_array($arr_result)) {
                $arr_result = FALSE;
                $this->errMsg = $this->_DB->errMsg;
            }
        }
        return $arr_result;
    }

    /**
     * Procedimiento para crear un nuevo Break.
     *
     * @param   $sNombre            Nombre del Break
     * @param   $sDescripcion       Un detalle del break
     * 
     * @return  bool    true or false si inserto o no
     */
    function createBreak($sNombre, $sDescripcion)
    {
        $sNombre = trim("$sNombre");
        if ($sNombre == '')
            $this->errMsg = _tr("Name Break can't be empty");
        else {
            $recordset =& $this->_DB->fetchTable("SELECT * FROM break WHERE name = ".paloDB::DBCAMPO($sNombre));
            if (is_array($recordset) && count($recordset) > 0) 
                $this->errMsg = _tr("Name Break already exists");
            else {
                // Construir y ejecutar la orden de inserción SQL
                $sPeticionSQL = paloDB::construirInsert(
                    "break",
                    array(
                        "name"          =>  paloDB::DBCAMPO($sNombre),
                        "description"   =>  paloDB::DBCAMPO($sDescripcion),
                    )
                );
                $result = $this->_DB->genQuery($sPeticionSQL);
                if ($result)
                    return true;
                else {
                    $this->errMsg = $this->_DB->errMsg."<br/>$sPeticionSQL";
                    return false;
                }
            }
        }   
    }   

    /**
     * Procedimiento para actualizar un break dado
     *
     * @param   $idBreak        id del Break
     * @param   $sNombre        Nombre del Break
     * @param   $sDescripcion   Detalle del Break
     * 
     * @return  bool    true or false si actualizo o no
     */
    function updateBreak($idBreak, $sNombre, $sDescripcion)
    {
        $sNombre = trim("$sNombre");
        if ($sNombre == '')
            $this->errMsg = _tr("Name Break can't be empty");
        else if (!isset($idBreak))
            $this->errMsg = _tr("Id Break is empty");
        else {
            // Construir y ejecutar la orden de update SQL
            $sPeticionSQL = paloDB::construirUpdate(
                "break",
                array(
                    "name"          =>  paloDB::DBCAMPO($sNombre),
                    "description"   =>  paloDB::DBCAMPO($sDescripcion)),
                "id = $idBreak"
            );
            $result = $this->_DB->genQuery($sPeticionSQL);
            if ($result)
                return true;
            else {
                $this->errMsg = $this->_DB->errMsg."<br/>$sPeticionSQL";
                return false;
            }
        } 
    }

     /**
     * Procedimiento para poner en estado activo o inactivo un break
     * Activo = 'A'   ,  Inactivo = 'I'
     *
     * @param   $idBreak        id del Break
     * @param   $activate        Activo o Inactivo
     * 
     * @return  bool    true or false si actualizo o no el estatus
     */
    function activateBreak($idBreak,$activate)
    {
         $sPeticionSQL = paloDB::construirUpdate(
             "break",
             array("status"       =>  paloDB::DBCAMPO($activate)),
             " id=$idBreak "
            );
 
        $result = $this->_DB->genQuery($sPeticionSQL);
        if ($result) 
            return true;
        else 
            $this->errMsg = $this->_DB->errMsg."<br/>$sPeticionSQL";
        return false;
    } 
}

//FUNCIONES AJAX
 /**
     * Procedimiento para desactivar un break
     *
     * @param   $idBreak        id del Break
     * 
     * @return  xajaxResponse    Respuesta de un requerimineto ajax
     */
function desactivateBreak($idBreak)
{
    global $arrConf;
    $respuesta = new xajaxResponse();
    
    // se conecta a la base
    $pDB = new paloDB($arrConf["cadena_dsn"]);
    if(!empty($pDB->errMsg)) {
        $respuesta->addAssign("mb_message","innerHTML",_tr("Error when connecting to database")."<br/>".$pDB->errMsg);
    }

    $oBreaks = new PaloSantoBreaks($pDB);
    if($oBreaks->activateBreak($idBreak,'I'))
        $respuesta->addScript("window.open('?menu=break_administrator','_parent')");
    else{
        $respuesta->addAssign("mb_title","innerHTML",_tr("Desactivate Error")); 
        $respuesta->addAssign("mb_message","innerHTML",_tr("Error when desactivating the Break")); 
    }
    
    return $respuesta;
}
?>