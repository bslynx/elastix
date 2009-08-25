<?php
include ("libs/jpgraph.php");
include ("libs/jpgraph_line.php");
include ("dataGraphic/graphic.php");

$datay1 = $arrayTodas;
$datay2 = $arrayExitosas;
$datay3 = $arrayAbandonadas;
/*$datahours = array("00:00","01:00","02:00","03:00","04:00","05:00","06:00","07:00","08:00","09:00","10:00",
      "11:00","12:00","13:00","14:00","15:00","16:00","17:00","18:00","19:00","20:00","21:00",
      "22:00","23:00");*/
$datahours = array("00","01","02","03","04","05","06","07","08","09","10",
      "11","12","13","14","15","16","17","18","19","20","21",
      "22","23");
// Setup the graph
$graph = new Graph(800,500);
$graph->SetMarginColor('white');
$graph->SetScale("textlin");
$graph->SetFrame(true);
$graph->SetMargin(60,50,30,30);

$graph->title->Set('Llamadas');


$graph->yaxis->HideZeroLabel();
$graph->ygrid->SetFill(true,'#EFEFEF@0.5','#BBCCFF@0.5');
$graph->xgrid->Show();

$graph->xaxis->SetTickLabels($datahours);

// Create the first line
$p1 = new LinePlot($datay1);
$p1->SetColor("navy");
$p1->SetLegend('All');
$p1->SetWeight(7);
$graph->Add($p1);

// Create the second line
$p2 = new LinePlot($datay2);
$p2->SetColor("red");
$p2->SetLegend('Success');
$p2->SetWeight(3);
$graph->Add($p2);

// Create the third line
$p3 = new LinePlot($datay3);
$p3->SetColor("orange");
$p3->SetLegend('Abandoned');
$p3->SetWeight(3);
$graph->Add($p3);



$graph->legend->SetShadow('gray@0.4',5);
$graph->legend->SetPos(0.1,0.1,'right','top');
// Output line
$graph->Stroke();

?>


