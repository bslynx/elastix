<?php 
require_once("/var/www/html/libs/misc.lib.php");
require_once("/var/www/html/configs/default.conf.php");
require_once($arrConf['basePath']."/libs/paloSantoDB.class.php");
require_once($arrConf['basePath']."/modules/voipprovider/configs/default.conf.php");
require_once($arrConf['basePath']."/modules/voipprovider/libs/paloSantoVoIPProvider.class.php");
require_once( "/var/www/html/libs/paloSantoForm.class.php");

global $arrConf;
global $arrConfModule;

$action     = getParameter('action');
$type = getParameter("type");

if ($action == "setConfig"){
    header("Content-type: text/xml");
    $pDB = new paloDB($arrConfModule['dsn_conn_database']);
    $pControlPanel = new paloSantoVoIPProvider($pDB);
    echo $pControlPanel->getConfigByType($type);
}


?>
