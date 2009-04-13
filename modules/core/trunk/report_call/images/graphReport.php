<?php

$module_name = "report_call";

include_once "/var/www/html/libs/paloSantoGraph.class.php";//lib paloGrapf

$ext      = $_GET['ext'];
$date_ini = $_GET['dini'];
$date_end = $_GET['dfin'];

$imagen1 = reportTopMoreCalls($module_name, $date_ini, $date_end, $ext);//PLOT3D
$imagen2 = reportBillingsByTrunks($module_name, $date_ini, $date_end, $ext);//PLOT3D

echo "<table width='100%' border='0' cellspacing='0' cellpadding='0' align='center'>";
echo "  <tr>";
echo "      <td align='center'>";
echo "          $imagen1";
echo "      </td>";
echo "  </tr>";
echo "<br/>";
echo "  <tr>";
echo "      <td align='center'>";
echo "          $imagen2";
echo "      </td>";
echo "  </tr>";
echo "</table>";

function reportTopMoreCalls($module_name, $date_ini_tmp, $date_end_tmp, $ext)//PLOT3D
{
    $arrParameterCallbyGraph = array($date_ini_tmp, $date_end_tmp, $ext);
    $oPaloGraph = new paloSantoGraph($module_name,"paloSantoReportCall","callbackTopMoreCalls",$arrParameterCallbyGraph);
    return $oPaloGraph->getGraph("../../../");
}

function reportBillingsByTrunks($module_name, $date_ini_tmp, $date_end_tmp, $ext)//PLOT3D
{
    $arrParameterCallbyGraph = array($date_ini_tmp, $date_end_tmp, $ext);
    $oPaloGraph = new paloSantoGraph($module_name,"paloSantoReportCall","callbackBillingsByTrunks",$arrParameterCallbyGraph);
    return $oPaloGraph->getGraph("../../../");
}

?>