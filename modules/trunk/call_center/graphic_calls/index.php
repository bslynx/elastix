<?php
//bin/bash: indent: command not found
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

require_once "libs/paloSantoForm.class.php";
require_once "libs/misc.lib.php";
include_once "libs/paloSantoConfig.class.php";
include_once "libs/paloSantoGrid.class.php";
include_once "modules/form_designer/libs/paloSantoDataForm.class.php";
require_once "libs/xajax/xajax.inc.php";



function _moduleContent(&$smarty, $module_name)
{
    #incluir el archivo de idioma de acuerdo al que este seleccionado
    #si el archivo de idioma no existe incluir el idioma por defecto
    $lang=get_language();
    $script_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $lang_file="modules/$module_name/lang/$lang.lang";
    if (file_exists("$script_dir/$lang_file"))
        include_once($lang_file);
    else
        include_once("modules/$module_name/lang/en.lang");

    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    global $arrConf;
    global $arrLang;

    require_once "modules/$module_name/libs/paloSantoCallsHour.class.php";
    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConfig['templates_dir']))?$arrConfig['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];
    $relative_dir_rich_text = "modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
    $arrConfig = $pConfig->leer_configuracion(false);

    $dsn     = $arrConfig['AMPDBENGINE']['valor'] . "://" . $arrConfig['AMPDBUSER']['valor'] . ":" . $arrConfig['AMPDBPASS']['valor'] . "@" . $arrConfig['AMPDBHOST']['valor'] . "/asterisk";
    $oDB = new paloDB($dsn);


    // se conecta a la base
    $pDB = new paloDB($arrConf["cadena_dsn"]);
    if (!is_object($pDB->conn) || $pDB->errMsg!="") {
        $smarty->assign("mb_message", $arrLang["Error when connecting to database"]." ".$pDB->errMsg);
    }elseif(isset($_GET['exportcsv']) && $_GET['exportcsv']=='yes') {
        $fechaActual = date("d M Y");
        header("Cache-Control: private");
        header("Pragma: cache");
        header('Content-Type: application/octec-stream');
        $title = "\"".$fechaActual.".csv\"";
        header("Content-disposition: inline; filename={$title}");
        header('Content-Type: application/force-download');
    }
    
    if(isset($arrFilterExtraVars) && is_array($arrFilterExtraVars) and count($arrFilterExtraVars)>0) {
	$url = construirURL($arrFilterExtraVars); 
    } else {
	$url = construirURL(); 
    }

    $smarty->assign("url", $url);
    $oGrid = new paloSantoGrid($smarty);
    $arrGrid = array();
    $arrData = array();
    //llamamos a funcion que construye la vista
    $contenidoModulo = listadoCalls($pDB, $smarty, $module_name, $local_templates_dir,$oGrid,$arrGrid,$arrData);

    if(  isset( $_GET['exportcsv'] ) && $_GET['exportcsv']=='yes' ) {
	return $oGrid->fetchGridCSV($arrGrid, $arrData);
    }else {
	$oGrid->showFilter($htmlFilter);
	return $contenidoModulo;//$oGrid->fetchGrid($arrGrid, $arrData,$lang);
    }

}


//funcion que construye la vista del reporte
function listadoCalls($pDB, $smarty, $module_name, $local_templates_dir,&$oGrid,&$arrGrid,&$arrData) {
    global $arrLang;
    global $arrLan;
    $arrData = array();
    $oCalls = new paloSantoCallsHour($pDB);
    $fecha_init = date("d M Y");
    $fecha_end  = date("d M Y");

    // preguntamos por el TIPO del filtro (Entrante/Saliente)
    if (!isset($_POST['cbo_tipos']) || $_POST['cbo_tipos']=="") {
        $_POST['cbo_tipos'] = "E";//por defecto las consultas seran de Llamadas Entrantes
    }

    $tipo = 'E'; $entrantes = 'T'; $salientes = 'T';
    if(isset($_POST['cbo_tipos']))
        $tipo = $_POST['cbo_tipos'];
    if(isset($_POST['cbo_estado_entrantes']))
        $entrantes = $_POST['cbo_estado_entrantes'];
    if(isset($_POST['cbo_estado_salientes']))
        $salientes = $_POST['cbo_estado_salientes'];

       //validamos la fecha
    if( isset($_POST['txt_fecha_init']) && isset($_POST['txt_fecha_end']) ) {
        $fecha_init_actual = $_POST['txt_fecha_init'];
        $fecha_end_actual = $_POST['txt_fecha_end'];
    }elseif(isset($_GET['txt_fecha_init']) && isset($_GET['txt_fecha_end'])){
        $fecha_init_actual = $_GET['txt_fecha_init'];
        $fecha_end_actual = $_GET['txt_fecha_end'];
    } 
    else {
        $fecha_init_actual  = $fecha_init;
        $fecha_end_actual   = $fecha_end;
    }

    $sValidacion = "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$";
    if( isset($_POST['submit_fecha']) || isset($_POST['cbo_tipos'] )) {
        // si se ha presionado el boton pregunto si hay una fecha de inicio elegida
        if ( (isset( $_POST['txt_fecha_init']) && $_POST['txt_fecha_init']!="" && isset( $_POST['txt_fecha_end']) && $_POST['txt_fecha_end']!="")  ) {
            // sihay una fecha de inicio pregunto si es valido el formato de la fecha
            if ( ereg( $sValidacion , $_POST['txt_fecha_init'] ) ) {
                // si el formato es valido procedo a convertir la fecha en un arreglo que contiene 
                // el anio , mes y dia seleccionados
                $fecha_init = $fecha_init_actual;//$_POST['txt_fecha_init'];
                $arrFecha_init = explode('-',translateDate($fecha_init));
            }else {
                // si la fecha esta en un formato no valido se envia un mensaje de error
                $smarty->assign("mb_title", $arrLan["Error"]);
                $smarty->assign("mb_message", $arrLan["Debe ingresar una fecha valida"]);
            }
            // pregunto si es valido el formato de la fecha final
                if ( ereg( $sValidacion , $_POST['txt_fecha_end'] ) ) {
                    // si el formato es valido procedo a convertir la fecha en un arreglo que contiene 
                // el anio , mes y dia seleccionados
                    $fecha_end = $fecha_end_actual;//$_POST['txt_fecha_end'];
                    $arrFecha_end = explode('-',translateDate($fecha_end));
                }else {
                    // si la fecha esta en un formato no valido se envia un mensaje de error
                    $smarty->assign("mb_title", $arrLan["Error"]);
                    $smarty->assign("mb_message", $arrLan["Debe ingresar una fecha valida"]);
                }

        //PRUEBA

            $arrFilterExtraVars = array("cbo_tipos" => $tipo,
                                    "cbo_estado_entrantes" => $entrantes,
                                    "cbo_estado_salientes" => $salientes,
                                    "txt_fecha_init" => $_POST['txt_fecha_init'], 
                                    "txt_fecha_end" => $_POST['txt_fecha_end'], 
                                    );
        //PRUEBA
        } elseif( (isset( $_GET['txt_fecha_init']) && $_GET['txt_fecha_init']!="" && isset( $_GET['txt_fecha_end']) && $_GET['txt_fecha_end']!="") ){
            if ( ereg( $sValidacion , $_GET['txt_fecha_init'] ) ) {
                // si el formato es valido procedo a convertir la fecha en un arreglo que contiene 
                // el anio , mes y dia seleccionados
                $fecha_init = $fecha_init_actual;//$_POST['txt_fecha_init'];
                $arrFecha_init = explode('-',translateDate($fecha_init));
            }else {
                // si la fecha esta en un formato no valido se envia un mensaje de error
                $smarty->assign("mb_title", $arrLan["Error"]);
                $smarty->assign("mb_message", $arrLan["Debe ingresar una fecha valida"]);
            }
            // pregunto si es valido el formato de la fecha final
                if ( ereg( $sValidacion , $_GET['txt_fecha_end'] ) ) {
                    // si el formato es valido procedo a convertir la fecha en un arreglo que contiene 
                // el anio , mes y dia seleccionados
                    $fecha_end = $fecha_end_actual;//$_POST['txt_fecha_end'];
                    $arrFecha_end = explode('-',translateDate($fecha_end));
                }else {
                    // si la fecha esta en un formato no valido se envia un mensaje de error
                    $smarty->assign("mb_title", $arrLan["Error"]);
                    $smarty->assign("mb_message", $arrLan["Debe ingresar una fecha valida"]);
                }

            $tipo =  $_GET['cbo_tipos'];
            $entrantes =  $_GET['cbo_estado_entrantes'];
            $salientes = $_GET['cbo_estado_salientes'];

            $arrFilterExtraVars = array("cbo_tipos" => $_GET['cbo_tipos'],
                                    "cbo_estado_entrantes" => $_GET['cbo_estado_entrantes'],
                                    "cbo_estado_salientes" => $_GET['cbo_estado_salientes'],
                                    "txt_fecha_init" => $_GET['txt_fecha_init'], 
                                    "txt_fecha_end" => $_GET['txt_fecha_end'], 
                                    );

        }
        elseif(!isset($fecha_init) && !isset($fecha_end)) {
            // si se ha presionado el boton para listar por fechas, y no se ha ingresado una fecha
            // se le muestra al usuario un mensaje de error
            $smarty->assign("mb_title", $arrLan["Error"]);
            $smarty->assign("mb_message", $arrLan["Debe ingresar una fecha inicio/fin"]);
        }
    }

//para el pagineo
       // LISTADO
        $limit =2;//100000;
        $offset = 0;

        // Si se quiere avanzar a la sgte. pagina
        if(isset($_GET['nav']) && $_GET['nav']=="end") {
            $arrCallsTmp  = $oCalls->getCalls($tipo,$entrantes, $salientes,translateDate($fecha_init),translateDate($fecha_end), $limit, $offset);
            $totalCalls  = $arrCallsTmp['NumRecords'];
            // Mejorar el sgte. bloque.
            if(($totalCalls%$limit)==0) {
                $offset = $totalCalls - $limit;
            } else {
                $offset = $totalCalls - $totalCalls%$limit;
            }
        }

        // Si se quiere avanzar a la sgte. pagina
        if(isset($_GET['nav']) && $_GET['nav']=="next") {
            $offset = $_GET['start'] + $limit - 1;
        }

        // Si se quiere retroceder
        if(isset($_GET['nav']) && $_GET['nav']=="previous") {
            $offset = $_GET['start'] - $limit - 1;
        }


        // Construyo el URL base
        if(isset($arrFilterExtraVars) && is_array($arrFilterExtraVars) && count($arrFilterExtraVars)>0) {
            $url = construirURL($arrFilterExtraVars, array("nav", "start")); 
        } else {
            $url = construirURL(array(), array("nav", "start")); 
        }
        $smarty->assign("url", $url);

//fin de pagineo


    $arrData = armarArrayGrafico($oCalls,$tipo,'T','T',$fecha_init, $fecha_end, $limit, $offset);
    
    //Armar archivo para el grafico
    $queue = $_POST['cbo_queue'];
    archivoGraficar($oCalls,'E',$queue,$fecha_init, $fecha_end, $limit, $offset);

    //Llenamos las cabeceras
    $arrGrid = array("title"    => $arrLan["Graphic Calls per hour"],
        "icon"     => "images/list.png",
        "width"    => "99%",
        "start"    => ($end==0) ? 0 : $offset + 1,//($end==0) ? 0 : 1,
        "end"      => ($offset+$limit)<=$end ? $offset+$limit : $end,//$end,
        "total"    => $end,
        "columns"  => array(0 => array("name"      => $arrLan["Cola"],
                                       "property1" => ""),
                            1 => array("name"      => "00:00", 
                                       "property1" => ""),
                            2 => array("name"      => "01:00", 
                                       "property1" => ""),
                            3 => array("name"      => "02:00", 
                                       "property1" => ""),
                            4 => array("name"      => "03:00",
                                       "property1" => ""),
                            5 => array("name"      => "04:00", 
                                       "property1" => ""),
                            6 => array("name"      => "05:00", 
                                       "property1" => ""),
                            7 => array("name"      => "06:00", 
                                       "property1" => ""),
                            8 => array("name"      => "07:00",
                                       "property1" => ""),
                            9 => array("name"      => "08:00", 
                                       "property1" => ""),
                            10 => array("name"     => "09:00", 
                                       "property1" => ""),
                            11 => array("name"     => "10:00", 
                                       "property1" => ""),
                            12 => array("name"     => "11:00", 
                                       "property1" => ""),
                            13 => array("name"     => "12:00", 
                                       "property1" => ""),
                            14 => array("name"     => "13:00", 
                                       "property1" => ""),
                            15 => array("name"     => "14:00", 
                                       "property1" => ""),
                            16 => array("name"     => "15:00", 
                                       "property1" => ""),
                            17 => array("name"     => "16:00", 
                                       "property1" => ""),
                            18 => array("name"     => "17:00", 
                                       "property1" => ""),
                            19 => array("name"     => "18:00", 
                                       "property1" => ""),
                            20 => array("name"     => "19:00", 
                                       "property1" => ""),
                            21 => array("name"     => "20:00", 
                                       "property1" => ""),
                            22 => array("name"     => "21:00", 
                                       "property1" => ""),
                            23 => array("name"     => "22:00", 
                                       "property1" => ""),
                            24 => array("name"     => "23:00", 
                                       "property1" => ""),
                            25 => array("name"     => $arrLan["Total Calls"], 
                                       "property1" => ""),

                        ));

    //Para el combo de tipos
    $tipos = array("E"=>$arrLan["Ingoing"]/*, "S"=>$arrLan["Outgoing"]*/);
    $combo_tipos = "<select name='cbo_tipos' id='cbo_tipos' onChange='submit();'>".combo($tipos,$_POST['cbo_tipos'])."</select>";
    $array_queue=array();
    for ($i=0; $i<count($arrData); $i++)
        $array_queue[$arrData[$i][0]]=$arrData[$i][0];
    $combo_queue = "<select name='cbo_queue' id='cbo_queue' onChange='submit();'>".combo($array_queue,$_POST['cbo_queue'])."</select>";

    $oGrid->showFilter( insertarCabeceraCalendario()."

        <form style='margin-bottom:0;' method='POST' action='?menu=$module_name'>
            <table width='100%' border='0'>
                <tr>
                    <td align='left'>
                        <table>
                        <tr>
                            <td class='letra12'>
                                {$arrLan["Date Init"]}
                                <span  class='required'>*</span>
                            </td>
                            <td>
                                ".insertarDateInit($fecha_init_actual)."
                            </td>
                            <td class='letra12'>
                                &nbsp;
                            </td>
                            <td class='letra12'>
                                {$arrLan["Date End"]}
                                <span  class='required'>*</span>
                            </td>
                            <td>
                                ".insertarDateEnd($fecha_end_actual)."
                            </td>

                        </tr>

                        <tr>
                            <td class='letra12' align='left'>{$arrLan["Tipo"]}</td>
                            <td>$combo_tipos</td>
                            <td class='letra12'>
                                &nbsp;
                            </td>
                            ".$td."
                            <td class='letra12' align='left'>{$arrLan["Cola"]}</td>
                            <td>$combo_queue</td>
                            <td class='letra12'>
                                &nbsp;
                            </td>
                            <td>
                                <input type='submit' name='submit_fecha' value={$arrLan["Find"]} class='button'>
                            </td>
                        </tr>
                        </table>
                    </td>
                </tr>
                <tr align='left'>
                    <td>
                        <img src='/modules/graphic_calls/filledgridex.php' />
                    </td>
                </tr>
            </table>
        </form>

        ");

    $oGrid->enableExport();
    $contenidoModulo = $oGrid->fetchGrid($arrGrid, $arrData,$arrLang);
    return $contenidoModulo;
}
function armarArrayGrafico($oCalls,$tipo,$entrantes,$salientes,$fecha_init, $fecha_end, $limit, $offset)
{
    global $arrLan;
    //llamamos  a la función que hace la consulta  a la base según los criterios de búsqueda
    $arrCalls = $oCalls->getCalls($tipo,$entrantes, $salientes,translateDate($fecha_init),translateDate($fecha_end), $limit, $offset);

    $end = $arrCalls['NumRecords'];
//Llenamos el contenido de las columnas
    $arrTmp    = array();
    $arrData   = array();

    if (is_array($arrCalls)) {
        foreach($arrCalls['Data'] as $calls) {
            $arrTmp[0] = $calls['cola'];
            //primeramente enceramos los valores de horas
            $arrTmp[1]=0;  $arrTmp[2]=0;  $arrTmp[3]=0; 
            $arrTmp[4]=0;  $arrTmp[5]=0;  $arrTmp[6]=0;
            $arrTmp[7]=0;  $arrTmp[8]=0;  $arrTmp[9]=0; 
            $arrTmp[10]=0; $arrTmp[11]=0; $arrTmp[12]=0;
            $arrTmp[13]=0; $arrTmp[14]=0; $arrTmp[15]=0; 
            $arrTmp[16]=0; $arrTmp[17]=0; $arrTmp[18]=0; 
            $arrTmp[19]=0; $arrTmp[20]=0; $arrTmp[21]=0;
            $arrTmp[22]=0; $arrTmp[23]=0; $arrTmp[24]=0;
                foreach($calls as $hora=>$num_veces){
                    if($hora>="0" && $hora<"1")
                        $arrTmp[1] = $num_veces;
                    elseif($hora>="1" && $hora<"2") 
                        $arrTmp[2] = $num_veces;
                    elseif($hora>="2" && $hora<"3") 
                        $arrTmp[3] = $num_veces;
                    elseif($hora>="3" && $hora<"4") 
                        $arrTmp[4] = $num_veces;
                    elseif($hora>="4" && $hora<"5") 
                        $arrTmp[5] = $num_veces;
                    elseif($hora>="5" && $hora<"6") 
                        $arrTmp[6] = $num_veces;
                    elseif($hora>="6" && $hora<"7") 
                        $arrTmp[7] = $num_veces;
                    elseif($hora>="7" && $hora<"8") 
                        $arrTmp[8] = $num_veces;
                    elseif($hora>="8" && $hora<"9") 
                        $arrTmp[9] = $num_veces;
                    elseif($hora>="9" && $hora<"10") 
                        $arrTmp[10] = $num_veces;
                    elseif($hora>="10" && $hora<"11") 
                        $arrTmp[11] = $num_veces;
                    elseif($hora>="11" && $hora<"12") 
                        $arrTmp[12] = $num_veces;
                    elseif($hora>="12" && $hora<"13") 
                        $arrTmp[13] = $num_veces;
                    elseif($hora>="13" && $hora<"14") 
                        $arrTmp[14] = $num_veces;
                    elseif($hora>="14" && $hora<"15") 
                        $arrTmp[15] = $num_veces;
                    elseif($hora>="15" && $hora<"16") 
                        $arrTmp[16] = $num_veces;
                    elseif($hora>="16" && $hora<"17")
                        $arrTmp[17] = $num_veces;
                    elseif($hora>="17" && $hora<"18") 
                        $arrTmp[18] = $num_veces;
                    elseif($hora>="18" && $hora<"19") 
                        $arrTmp[19] = $num_veces;
                    elseif($hora>="19" && $hora<"20") 
                        $arrTmp[20] = $num_veces;
                    elseif($hora>="20" && $hora<"21") 
                        $arrTmp[21] = $num_veces;
                    elseif($hora>="21" && $hora<"22") 
                        $arrTmp[22] = $num_veces;
                    elseif($hora>="22" && $hora<"23") 
                        $arrTmp[23] = $num_veces;
                    elseif($hora>="23" && $hora<"24")
                        $arrTmp[24] = $num_veces;
                    $arrTmp[25] = $arrCalls['NumCalls'];// sumNumCalls($arrTmp);
                }
            $arrData[] = $arrTmp;
        }
        $arrTmp[0] = $arrLan["All"];
        for($j=1;$j<=25;$j++) {
            $sum = 0;
	    for($i=0;$i<count($arrData);$i++) {
		$sum = $sum + $arrData[$i][$j];
	    }
            $arrTmp[$j] = $sum;
        }
        
        $arrData[] = $arrTmp;
        
    }

    return $arrData;
}
function archivoGraficar($oCalls,$tipo,$queue,$fecha_init, $fecha_end, $limit, $offset)
{
    $array_vacio = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
    
    //Obtengo toda las listas de colas
    $arrayTodas = armarArrayGrafico($oCalls,$tipo,'T',$salientes,$fecha_init, $fecha_end, $limit, $offset);
    if(!is_array($arrayTodas))
        $arrayTodas=$array_vacio;

    $arrayExitosas = armarArrayGrafico($oCalls,$tipo,'E',$salientes,$fecha_init, $fecha_end, $limit, $offset);
    if(!is_array($arrayExitosas))
        $arrayExitosas=$array_vacio;

    $arrayAbandonadas = armarArrayGrafico($oCalls,$tipo,'A',$salientes,$fecha_init, $fecha_end, $limit, $offset);
    if(!is_array($arrayAbandonadas))
        $arrayAbandonadas=$array_vacio;

    //Escribir Archivo
    $gestor = fopen("modules/graphic_calls/dataGraphic/graphic.php", "w");

    //Contenido del Archivo
    $contenido = '<?php global $arrayTodas,$arrayExitosas,$arrayAbandonadas;';

    //Array
    $indiceTodas = indice($arrayTodas,$queue);
    if($indiceTodas!=-1)
         $contenido .= '$arrayTodas='.var_export(array_slice($arrayTodas[$indiceTodas],1,24),TRUE).";";
    else
        $contenido .= '$arrayTodas='.var_export($array_vacio,TRUE).";";

    $indiceExitosas = indice($arrayExitosas,$queue);
//     echo "Indice".$indiceExitosas;
//     print_r($arrayExitosas);
    if($indiceExitosas!=-1)
        $contenido .= '$arrayExitosas='.var_export(array_slice($arrayExitosas[$indiceExitosas],1,24),TRUE)."; ";
    else
        $contenido .= '$arrayExitosas='.var_export($array_vacio,TRUE).";";

    $indiceAbandonadas = indice($arrayAbandonadas,$queue);
    if($indiceAbandonadas!=-1)
        $contenido .= '$arrayAbandonadas='.var_export(array_slice($arrayAbandonadas[$indiceAbandonadas],1,24),TRUE)."; ";
    else
        $contenido .= '$arrayAbandonadas='.var_export($array_vacio,TRUE).";";

    $contenido .= "?>";
    if (fwrite($gestor, $contenido) === FALSE) {
            echo "Error al escribir archivo";
    }
    fclose($gestor);

}

function indice($array,$queue)
{
    $indice = -1;
    for ($i=0 ; $i<count($array) ; $i++){
        if($array[$i][0]==$queue){
            $indice=$i;
            break;
        }
    }
    return $indice;
}
/*    Esta funcion inserta el codigo necesario para visualizar el control fecha inicio
*/
function insertarDateInit($fecha_init) {
    return 
    " <input style='width: 10em; color: #840; background-color: #fafafa; border: 1px solid #999999;text-align: center' name='txt_fecha_init' value='{$fecha_init}' id='f-calendar-field-1' type='text' editable='false' class='button'/> "
    .
    insertarCalendario(1);
}

/*
    Esta funcion inserta el codigo necesario para visualizar el control fecha fin
*/
function insertarDateEnd($fecha_end) {
    return 
    " <input style='width: 10em; color: #840; background-color: #fafafa; border: 1px solid #999999;text-align: center' name='txt_fecha_end' value='{$fecha_end}' id='f-calendar-field-2' type='text' editable='false' class='button'/> "
    .
    insertarCalendario(2);
}

/*
    Esta funcion inserta el codigo necesario para visualizar y utilizar un calendario par escoger
    una fecha determinada.
*/
function insertarCalendario($index) {

    return 
    "<a href='#' id='f-calendar-trigger-$index'>
        <img align='middle' border='0' src='/libs/js/jscalendar/img.gif' alt='' />
    </a>
    
    <script type='text/javascript'>
        Calendar.setup(
            {
                'ifFormat':'%d %b %Y',
                'daFormat':'%Y-%m-%d',
                'firstDay':1,
                'showsTime':true,
                'showOthers':true,
                'timeFormat':24,
                'inputField':'f-calendar-field-$index',
                'button':'f-calendar-trigger-$index'
            }
        );
    </script> " ;
}

/*
    Esta funcion inserta las dependencias necesarias para el calendario
*/
function insertarCabeceraCalendario() {

    return 
    "<link rel='stylesheet' type='text/css' media='all' href='/libs/js/jscalendar/calendar-win2k-2.css' />
        <script type='text/javascript' src='/libs/js/jscalendar/calendar_stripped.js'></script>
        <script type='text/javascript' src='/libs/js/jscalendar/lang/calendar-en.js'></script>
        <script type='text/javascript' src='/libs/js/jscalendar/calendar-setup_stripped.js'></script>
    ";
}

function sumNumCalls($arrTmp){

    $sumCalls = 0;

    for($i=1;$i<=24;$i++) {
        $sumCalls = $sumCalls + $arrTmp[$i];
    }
    return $sumCalls;

}
?>
