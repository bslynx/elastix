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
  | Autores: Gladys Carrillo B.   <gcarrillo@palosanto.com>              |
  +----------------------------------------------------------------------+
  $Id: paloSantoInstaller.class.php,v 1.1 2007/09/05 00:25:25 gcarrillo Exp $
*/

class Installer
{

    var $_errMsg;

    function Installer()
    {

    }

    function addMenu($oMenu,$arrTmp){
	//verificar si tiene que crear un nuevo menu raiz
	
	if ($arrTmp['type']=='menu')
        {
            $parentId="";
            $type="";
        }
        else {
            $parentId = $arrTmp['parent'];
            $type=$arrTmp['type'];
        }
	//creo el menu

	$bExito = $oMenu->createMenu($arrTmp['menuid'],$arrTmp['tag'],$parentId,$type);
	if (!$bExito){
            $this->_errMsg = $oMenu->errMsg;
            return false;
        }
	return true;
    }

    function addResourceMembership($oACL,$arrTmp){
        $bExito = $oACL->createResource($arrTmp['menuid'], $arrTmp['tag']);
        if ($bExito){
            //inserto en acl_group_permission
            //recupero el id del recurso insertado
            $resource_id= $oACL->getResourceId($arrTmp['menuid']);
            $bExito = false;
            if (!is_null($resource_id))
            {
                $bExito = $oACL->saveGroupPermission(1,array($resource_id));
            }
        }
        $this->_errMsg = $oACL->errMsg;
        return $bExito;
    }

    function createNewDatabase($path_script_db,$sqlite_db_path,$db_name)
    {
        $comando="cat $path_script_db | sqlite3 $sqlite_db_path/$db_name.db";
        exec($comando,$output,$retval);
        return $retval;
    }
}
?>