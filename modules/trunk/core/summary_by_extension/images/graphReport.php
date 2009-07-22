<?php
$module_name = "summary_by_extension";
include_once "/var/www/html/libs/paloSantoGraph.class.php";//lib paloGrapf

$ext      = $_GET['ext'];
$date_ini = $_GET['dini'];
$date_end = $_GET['dfin'];
$num_in   = $_GET['num_in'];
$num_out  = $_GET['num_out'];

$imagen1 = reportTop10Incoming($module_name, $date_ini, $date_end, $ext, $num_in);//PLOT3D
$imagen2 = reportTop10Outgoing($module_name, $date_ini, $date_end, $ext, $num_out);//PLOT3D

echo "<table width='100%' border='0' cellspacing='0' cellpadding='0' align='center'>
        <tr>
          <td align='center'>$imagen1</td>
        </tr>
        <br/>
        <tr>
          <td align='center'>$imagen2</td>
        </tr>
      </table>";

function reportTop10Outgoing($module_name, $date_ini_tmp, $date_end_tmp, $ext, $num_out)//PLOT3D
{
    $arrParameterCallbyGraph = array($date_ini_tmp, $date_end_tmp, $ext, $num_out);
    $oPaloGraph = new paloSantoGraph($module_name,"paloSantoReportCall","callbackTop10Salientes",$arrParameterCallbyGraph);
    return $oPaloGraph->getGraph("../../../");
}

function reportTop10Incoming($module_name, $date_ini_tmp, $date_end_tmp, $ext, $num_in)//PLOT3D
{
    $arrParameterCallbyGraph = array($date_ini_tmp, $date_end_tmp, $ext, $num_in);
    $oPaloGraph = new paloSantoGraph($module_name,"paloSantoReportCall","callbackTop10Entrantes",$arrParameterCallbyGraph);
    return $oPaloGraph->getGraph("../../../");
}
?>