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
define("MYSQL_ROOT_PASSWORD","eLaStIx.2oo7");
//define("DIRECTORIO","/var/www/html/lang/")

include_once("libs/paloSantoDB.class.php");
require_once "paloSantoModuloXML.class.php";

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
    function createNewDatabaseMySQL($path_script_db, $db_name, $datos_conexion)
    {
        $db = 'mysql://root:'.MYSQL_ROOT_PASSWORD.'@localhost/';
        $pDB = new paloDB ($db);
        $sPeticionSQL = "CREATE DATABASE $db_name";
        $result = $pDB->genQuery($sPeticionSQL);
        if($datos_conexion['locate'] == "")
            $datos_conexion['locate'] = "localhost";
        $GrantSQL = "GRANT SELECT, INSERT, UPDATE, DELETE ON $db_name.* TO ";
        $GrantSQL .= $datos_conexion['user']."@".$datos_conexion['locate']." IDENTIFIED BY '".                          $datos_conexion['password']."'";
        $result = $pDB->genQuery($GrantSQL);
        $comando="mysql --password=".MYSQL_ROOT_PASSWORD." --user=root $db_name < $path_script_db";
        exec($comando,$output,$retval);
        return $retval;
    }

    function addModuleLanguage($tmpDir,$DocumentRoot)
    {
        require_once("configs/languages.conf.php");
        //array que incluye todos los lenguages que existan en /html/lang
        $languages = array_keys($languages);

        $oModuloXML= new ModuloXML("$tmpDir/module.xml");
        //Se recorre por cada lenguaje
        foreach ($languages as $lang)
        {
            if (file_exists("$DocumentRoot/lang/$lang.lang")) {
                require_once("$DocumentRoot/lang/$lang.lang");
                global $arrLang;
                //Se realiza por cada modulo
                if (count(($oModuloXML->_arbolMenu))>0) {
                    foreach (($oModuloXML->_arbolMenu) as $menulist) {
                        foreach ($menulist['ITEMS'] as $item_modules) {
                                $menuid = $item_modules['MENUID'];
        //                         echo "MENUID".$menuid;
                                if (!empty($menuid))
                                {
                                    $nodo = array($item_modules['DESC'] => $item_modules['DESC']);
                                    $result = array_merge($arrLang,$nodo);
                                    $arrLang = $result;
                                }
                        }
                    }
                }
                $gestor = fopen("$DocumentRoot/lang/$lang.lang", "w");
                $contenido = "<?php \nglobal \$arrLang; \n\$arrLang =";
                $contenido .= var_export($arrLang,TRUE)."?>";
                if (fwrite($gestor, $contenido) === FALSE) {
                        echo "Error al escribir archivo";
                }
                fclose($gestor);
            } else {
                echo "No existe";
            }
        }
    }

    function refresh($documentRoot='/var/www/html')
    {
        //STEP 1: Delete tmp templates of smarty.
        exec("rm -rf $documentRoot/var/templates_c/*",$arrConsole,$flagStatus); 

        //STEP 2: Update menus elastix permission.
        unset($_SESSION['elastix_user_permission']);

        return $flagStatus;
    }
}
?>