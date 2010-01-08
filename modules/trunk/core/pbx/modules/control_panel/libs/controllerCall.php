<?php 
require_once("/var/www/html/libs/misc.lib.php");
require_once("/var/www/html/configs/default.conf.php");
require_once($arrConf['basePath']."/libs/paloSantoDB.class.php");
require_once($arrConf['basePath']."/modules/control_panel/configs/default.conf.php");
require_once($arrConf['basePath']."/modules/control_panel/libs/paloSantoControlPanel.class.php");
require_once( "/var/www/html/libs/paloSantoForm.class.php");

global $arrConf;
global $arrConfModule;

$action     = getParameter('action');
$number_org = getParameter('extStart');
$number_dst = getParameter('extFinish');
$id_area = getParameter('area');
$height = getParameter('height');
$width =  getParameter('width');
$description = getParameter('description');
$type = getParameter('type');
$queue = getParameter('queue');

if ($action == "call" & !is_null($number_org) & !is_null($number_dst)){
    $pDB1 = new paloDB($arrConfModule['dsn_conn_database1']);
    $pDB2 = new paloDB($arrConfModule['dsn_conn_database2']);
    $pControlPanel = new paloSantoControlPanel($pDB1,$pDB2);
    $pControlPanel->makeCalled($number_org, $number_dst);
}
else if ($action == "voicemail" & !is_null($number_org)){
    $pDB1 = new paloDB($arrConfModule['dsn_conn_database1']);
    $pDB2 = new paloDB($arrConfModule['dsn_conn_database2']);
    $pControlPanel = new paloSantoControlPanel($pDB1,$pDB2);
    $number_dst = "*98";
    $pControlPanel->makeCalled($number_org, $number_dst);
}
else if ($action == "hangup" & !is_null($number_org)){
    $pDB1 = new paloDB($arrConfModule['dsn_conn_database1']);
    $pDB2 = new paloDB($arrConfModule['dsn_conn_database2']);
    $pControlPanel = new paloSantoControlPanel($pDB1,$pDB2);
    $pControlPanel->hangupCalled($number_org);

}
else if ($action == "refresh"){
    $pDB1 = new paloDB($arrConfModule['dsn_conn_database1']);
    $pDB2 = new paloDB($arrConfModule['dsn_conn_database2']);
    $pControlPanel = new paloSantoControlPanel($pDB1,$pDB2);
    echo $pControlPanel->getAllDevicesXML();
}
else if ($action == "savechange"){
    $pDB1 = new paloDB($arrConfModule['dsn_conn_database1']);
    $pDB2 = new paloDB($arrConfModule['dsn_conn_database2']);
    $pControlPanel = new paloSantoControlPanel($pDB1,$pDB2);
    $pControlPanel->saveChangeArea($number_org,$id_area);
}
else if ($action == "savechange2"){
    $pDB1 = new paloDB($arrConfModule['dsn_conn_database1']);
    $pDB2 = new paloDB($arrConfModule['dsn_conn_database2']);
    $pControlPanel = new paloSantoControlPanel($pDB1,$pDB2);
    $pControlPanel->saveChangeArea2($number_org,$number_dst); 
}
else if ($action == "saveresize"){
    $pDB1 = new paloDB($arrConfModule['dsn_conn_database1']);
    $pDB2 = new paloDB($arrConfModule['dsn_conn_database2']);
    $pControlPanel = new paloSantoControlPanel($pDB1,$pDB2);
    
    if($width>2071) $num=10;
    elseif($width>1882 && $width<2072) $num=10;
    elseif($width>1693 && $width<1883) $num=9;
    elseif($width>1504 && $width<1694) $num=8;
    elseif($width>1315 && $width<1505) $num=7;
    elseif($width>1126 && $witdth<1316) $num=6;
    elseif($width>936 && $witdth<1127) $num=5;
    elseif($width>747 && $width<937) $num=4;
    elseif($width>559 && $width<748) $num=3;
    elseif($width>370 && $width<560) $num=2;
    elseif($width>184 && $width<371) $num=1;

    if($type!="alsoResize")
        $pControlPanel->updateResizeArea($height,$width,$num,$id_area);
    else
        $pControlPanel->updateResizeArea2($height,$width,$num,$id_area);
}
else if ($action == "loadArea"){
    $pDB1 = new paloDB($arrConfModule['dsn_conn_database1']);
    $pDB2 = new paloDB($arrConfModule['dsn_conn_database2']);
    $pControlPanel = new paloSantoControlPanel($pDB1,$pDB2);
    echo $pControlPanel->getAllAreasXML();
    
}
else if ($action == "saveEdit"){
    $pDB1 = new paloDB($arrConfModule['dsn_conn_database1']);
    $pDB2 = new paloDB($arrConfModule['dsn_conn_database2']);
    $pControlPanel = new paloSantoControlPanel($pDB1,$pDB2);
    echo $pControlPanel->updateDescriptionArea($description,$id_area);
}
else if ($action == "addExttoQueue"){
    $pDB1 = new paloDB($arrConfModule['dsn_conn_database1']);
    $pDB2 = new paloDB($arrConfModule['dsn_conn_database2']);
    $pControlPanel = new paloSantoControlPanel($pDB1,$pDB2);
    
    $pControlPanel->queueAddMember($queue, $number_org);
}

?>
