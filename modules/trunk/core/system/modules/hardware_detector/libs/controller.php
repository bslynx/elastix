<?php 
require_once("/var/www/html/libs/misc.lib.php");
require_once("/var/www/html/configs/default.conf.php");
require_once($arrConf['basePath']."/libs/paloSantoDB.class.php");
//require_once("/var/www/html/libs/libs/paloSantoDB.class.php");
require_once($arrConf['basePath']."/modules/hardware_detector/configs/default.conf.php");
require_once($arrConf['basePath']."/modules/hardware_detector/libs/PaloSantoHardwareDetection.class.php");
require_once("/var/www/html/libs/paloSantoForm.class.php");

global $arrConf;
global $arrConfModule;

$action     = getParameter('action');
//$arrSpanConf = getParameter("arrSpanConf");

if($action == "setConfig"){
    //header("Content-type: text/xml");
    $arrSpanConf = array();
    $idSpan = getParameter("idSpan");
    $arrSpanConf['tmsource'] = getParameter("tmsource");
    $arrSpanConf['lnbuildout'] = getParameter("lnbuildout");
    $arrSpanConf['framing'] = getParameter("framing");
    $arrSpanConf['coding'] = getParameter("coding");

    $pDB = new paloDB($arrConfModule['dsn_conn_database']);
    $oPortsDetails = new PaloSantoHardwareDetection();
    
    $oPortsDetails->updateFileSipCustom($idSpan, $arrSpanConf);

}else if($action == "setDataCard"){
    //header("Content-type: text/text");
    $arrCardParam = array();
    $idCard = getParameter("idCard");
    $pDB = new paloDB($arrConfModule['dsn_conn_database']);
    $oPortsDetails = new PaloSantoHardwareDetection();

    $arrCardParam['manufacturer'] = $pDB->DBCAMPO(getParameter("manufacturer"));
    $arrCardParam['num_serie'] = $pDB->DBCAMPO(trim(getParameter("num_serie")));
    
    $oPortsDetails->updateCardParameter($pDB, $arrCardParam, array("id_card"=>$idCard));
    echo "$idCard";
}


// function getParameter($parameter)
// {
//     if(isset($_POST[$parameter]))
//         return $_POST[$parameter];
//     else if(isset($_GET[$parameter]))
//         return $_GET[$parameter];
//     else
//         return null;
// }
?>