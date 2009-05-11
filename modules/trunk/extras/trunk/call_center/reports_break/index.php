<?php

require_once "libs/paloSantoForm.class.php";
require_once "libs/paloSantoDB.class.php";
require_once "libs/paloSantoGrid.class.php";
require_once "libs/misc.lib.php";

function _moduleContent(&$smarty,$module_name) {
    require_once "modules/$module_name/configs/default.config.php";
    require_once "modules/$module_name/libs/paloSantoReportsBreak.class.php";
    // obtengo la ruta del template a utilizar para generar el filtro.
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($config['templates_dir']))?$config['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$config['theme'];
    // obtengo el idioma actual utilizado en la aplicacion.
    $language=get_language();
    $script_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $lang_file="modules/$module_name/lang/$language.lang";
    // consulto si existe el archivo de idiomas elegido y por default se toma el español
    if (file_exists("$script_dir/$lang_file")) {
        include_once($lang_file);
    } else {
        include_once("modules/$module_name/lang/en.lang");
    }
    // se crea el objeto conexion a la base de datos
    $pDB = new paloDB($cadena_dsn);
    // valido lacreacion del objeto conexion, presentando un mensaje deerror si es invalido.
    if (!is_object($pDB->conn) || $pDB->errMsg!="") {
            $smarty->assign("mb_title", $lang["Error"]);
            $smarty->assign("mb_message", $lang["Error when connecting to database"]." ".$pDB->errMsg);
    // sio el objeto coenxion a la base no tiene problemas, consulto si se han seteado las variables GET.
    }elseif(isset($_GET['exportcsv']) && $_GET['exportcsv']=='yes') {
        // consulto si se ha escogido una fecha
        if(empty($_GET['txt_fecha_init'])) {
            // si no hay ninguna fecha seleccionada tomo la fecha actual del sistema.
            $fecha_init = date("Y-m-d") . " 00:00:00"; 
            // si hay una fecha seleccionada se almacena el valor.
        } else {
            $fecha_init = translateDate($_GET['txt_fecha_init']) . " 00:00:00";
        }
        if(empty($_GET['txt_fecha_end'])) { 
            $fecha_end = date("Y-m-d") . " 23:59:59"; 
        } else {
            $fecha_end  = translateDate($_GET['txt_fecha_end']) . " 23:59:59";
        }

        $arrFilterExtraVars = array("txt_fecha_init" => $fecha_init,
                                    "txt_fecha_end" => $fecha_end,
                                     );
    // ------ cabeceras necesarias para enviar la data a un archivo.  ------ //
        // para que los datos de un formulario no se pierdan
        header("Cache-Control: private");
        // obliga a la pagina a que sea cacheada
        header("Pragma: cache");
        // origina de un flujo de datos.
        header('Content-Type: application/octec-stream');
        // nombre del archivo del reporte a generarse, en base a la fecha seleccionada del reporte.
        $title = "\"".$fecha_init."-".$fecha_end.".csv\"";
        // hace que la data se guarde en un archivo con el nombre especificado en el parametro filename
        header("Content-disposition: inline; filename={$title}");
        // fuerza a que el archivo sea download
        header('Content-Type: application/force-download');
    // sino hay variables GET seteadas....
    } else {
        // creo un arreglo con la informacion necesario para el filtro que se desea definir.
        $arrFormElements = array
        (
            // en este caso solo se desea hacer un filtro por una fecha en particular, txt_fecha_init
            "txt_fecha_init"  => array
            (
                "LABEL"                     => $lang['Start Date'],
                "REQUIRED"                  => "yes",
                "INPUT_TYPE"                => "DATE",
                "INPUT_EXTRA_PARAM"         => "",
                "VALIDATION_TYPE"           => "ereg",
                "VALIDATION_EXTRA_PARAM"    => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"
            ),
            "txt_fecha_end"  => array
            (
                "LABEL"                     => $lang['End Date'],
                "REQUIRED"                  => "yes",
                "INPUT_TYPE"                => "DATE",
                "INPUT_EXTRA_PARAM"         => "",
                "VALIDATION_TYPE"           => "ereg",
                "VALIDATION_EXTRA_PARAM"    => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"
            ),
        );
        // obtengo la fecha actual del sistema.
        $fecha_init = date("d M Y");
        $fecha_end = date("d M Y");
        // nombre del boton que me permitira enviar los valores del formulario.
        $smarty->assign("btn_consultar",$lang['query']);
        // nombre del modulo actual.
        $smarty->assign("module_name",$module_name);
        // creo un objeto paloForm para crear el filtro del formulario.
        $oFilterForm = new paloForm($smarty, $arrFormElements);

        // valido si se ha presionado el boton "Consultar", cuyo name es "submit_fecha".
        if(isset($_POST['submit_fecha'])) {
            // valido la informacion obtenida del formulario.
            if($oFilterForm->validateForm($_POST)) {
                // si la informacion es correcta  procedo a procesar los datos,
                // en este caso la fecha del reporte deseado es asignada a una variable.
                $fecha_init = $_POST['txt_fecha_init']; 
                $fecha_end = $_POST['txt_fecha_end']; 
                // Envio al arreglo la fecha obtenida del formulario.
                // txt_fecha_init es el nombre del campo de texto en el quese guarda la fecha
                $arrFilterExtraVars = array("txt_fecha_init" => $fecha_init,
                                            "txt_fecha_init" => $fecha_end,
                                            );   // es neceasrio ya que esto me permite
                                                                            // crear el url para asignar la
                                                                            // fecha al GET
            } else {
                // si la informacion es invalida presento un mensaje de error con la cadena "Error de validacion"
                // dependiendo del idioma.
                $smarty->assign("mb_title", $lang["Validation Error"]);
                // en este arreglo se guarada los posibles errores de validacion generados.
                $arrErrores=$oFilterForm->arrErroresValidacion;
                // cadena que almacena el mensaje de error a mostrarse en la pantalla.
                $strErrorMsg = "<b>{$lang['The following fields contain errors']}:</b><br>";
                // se recorre el arreglo para revisar todos los errores encontrados en la informacion del formulario
                foreach($arrErrores as $k=>$v) {
                    // se concatena los mensajes de error encontrados.
                    $strErrorMsg .= "$k, ";
                }
                $strErrorMsg .= "";
                // se presenta la cadena de error en la pantalla.
                $smarty->assign("mb_message", $strErrorMsg);
            }
            // se asigna el template elegido , asi como tambien la variable super global $_POST, al filtro.
            $htmlFilter = $contenidoModulo=$oFilterForm->fetchForm("$local_templates_dir/form.tpl", "", $_POST);
        // si existe una fecha en el GET...
        }else if( isset( $_GET['txt_fecha_init']) && isset( $_GET['txt_fecha_end'])) {
                // se toma la fecha del GET
                $fecha_init = $_GET['txt_fecha_init']; 
                $fecha_end = $_GET['txt_fecha_end']; 
                // envio la fecha obtenida.  txt_fecha_init es el nombre del campo de texto en el quese guarda la fecha
                $arrFilterExtraVars = array("txt_fecha_init" => $fecha_init,
                                            "txt_fecha_init" => $fecha_end,
                                            ); 
                // seteo el template elegido junto con la variable GET que contine los datos del formulario(txt_fecha_init).
                $htmlFilter = $contenidoModulo=$oFilterForm->fetchForm("$local_templates_dir/form.tpl", "", $_GET);
            // sino se ha presionado el boton Consultar y no se ha clickado en el enlace "Export".
        } else {
            // asigno el template deseado, y obtengo la fecha actual del sistema, y se la envio junto al template
            // en un array asociativo .
            // txt_fecha_init es el nombre del campo de texto en el quese guarda la fecha.
            $htmlFilter = $contenidoModulo=$oFilterForm->fetchForm("$local_templates_dir/form.tpl", "", 
                            array('txt_fecha_init' => date("d M Y"),
                                  'txt_fecha_end' => date("d M Y")
                             ));
        }
        // genero la url
        if(isset($arrFilterExtraVars) && is_array($arrFilterExtraVars) and count($arrFilterExtraVars)>0) {
            // esta url contiene la informacion de lso campos del formulario pasados como GET,
            // a traves de un enlace.
            $url = construirURL($arrFilterExtraVars); 
        } else {
            // esta url contiene el nombre del modulo actual.
            $url = construirURL(); 
        }
        // asigno la url al template
        $smarty->assign("url", $url);

    } 

    // creo el objeto $oReportsBreak, que me ayudara a construir el reporte.
    $oReportsBreak  = new paloSantoReportsBreak($pDB);
    // valido la creacion del objeto
    if( !$oReportsBreak ) {
        $smarty->assign("mb_title", $lang["Error"]);
        $smarty->assign("mb_message", $lang["Error when creating object paloSantoReportsBreak"]);
    }else {
        // creo un arreglo para el grid
        $arrGrid = array();
        // envio el arreglo por referencia a la funcion para que esta se encargue de defnirlo. La funcion 
        // me retorna un arreglo con la informacion del reporte y ha generado el grid, 
        // el cual es devuelto por referencia.
        $arrData = generarReporte($smarty,$fecha_init,$fecha_end,$oReportsBreak,$lang,$arrGrid);
        // creo el objeto GRID
        $oGrid = new paloSantoGrid($smarty);
        // habilito la opcion Export con la que se procedera a exportar la data a un archivo.
        $oGrid->enableExport();
        // evaluo si se ha dado click en el enlace Export para generar el archivo
        if(  isset( $_GET['exportcsv'] ) && $_GET['exportcsv']=='yes' ) {
            // se envia los datos a la funcion l cual genera el archivo 
            return $oGrid->fetchGridCSV($arrGrid, $arrData);
        }
        // sino se muestran los datos en la pantalla.
        else {
            $oGrid->showFilter($htmlFilter);
            return $oGrid->fetchGrid($arrGrid, $arrData,$lang);
        }
    }
}


/*
    Esta funcion genera un reporte por fecha y devuelve un arreglo con la informacion deseada
*/
function generarReporte($smarty,$fecha_init,$fecha_end,$oReportsBreak,$lang,&$arrGrid=null) {

    $fecha_init = translateDate($fecha_init)." 00:00:00";
    $fecha_end  = translateDate($fecha_end)." 23:59:59";

    $limit = 50;
    $offset = 0;


    $arrAgentes = $oReportsBreak->getAgents(null,$offset);
    $total = count($arrAgentes);
    $arrData = array();
 
    // Si se quiere avanzar a la sgte. pagina
    if(isset($_GET['nav']) && $_GET['nav']=="end") {
        $totalAgents  = count($arrAgentes);
        // Mejorar el sgte. bloque.
        if(($totalAgents%$limit)==0) {
            $offset = $totalAgents - $limit;
        } else {
            $offset = $totalAgents - $totalAgents%$limit;
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

    //echo $fecha_init."<---------->".$fecha_end."<br>";
    // datos del reporte
    $rptBreaks  = $oReportsBreak->getReportesBreak($fecha_init,$fecha_end);
    // listado de tipos de break
    $arrBreaks  = $oReportsBreak->getTiposBreak();
    // listado de agentes 
    $arrAgentes = $oReportsBreak->getAgents($limit,$offset);
    // obtengo el numero de agentes que se presentaran por pagina
    $end = count($arrAgentes);
    // valido la informacion obtenida de los agentes, tipos de break y del reporte de break
    $arrColumnas[0] = array('name'=> $lang['Agent Number'],'property1'  => '' );
    $arrColumnas[1] = array('name'=> $lang['Agent Name'],'property1'  => '' );

    if(!$arrBreaks) {
        $smarty->assign("mb_title", $lang["Warning"]);
        $smarty->assign("mb_message", $lang["No register break in database"]);
        $arrBreaks = array();
    }elseif(!$arrAgentes) {
        $smarty->assign("mb_title", $lang["Error"]);
        $smarty->assign("mb_message", $lang["Error cargando listado de agentes"]);
        $arrBreaks = array();
    }elseif(!$rptBreaks) {
        $smarty->assign("mb_title", $lang["Error"]);
        $smarty->assign("mb_message", $lang["Error cargando listado de breaks tomados"]);
        $rptBreaks = array();
    }else {
        for($i=0;$i<count($arrBreaks);$i++) {
            //if ($arrBreaks[$i]['name']!="HOLD")
            $arrColumnas[$i+2] = array('name'=> $arrBreaks[$i]['name'],'property1'=>'');
        }
        $arrColumnas[$i+2] = array('name'=> $lang['Total'],'property1'=>'');

        // lleno el arreglo temporal con los datos que se obtuvieron del reporte de breaks
        foreach($arrAgentes as $agente) {
            //lleno el arreglo con la informacion de los agentes
            $arrTmp[0] = $agente['number'];
            $arrTmp[1] = $agente['name'];
            // lleno el arreglo temporal con la informacion que se obtuvo de los breaks
            $indice=2;
            $sumaTiempo = "00:00:00";
            foreach($arrBreaks as $break) {
                if ($rptBreaks[ $agente['id'] ][ $break['id'] ]!= "00:00:00") {
                    $tiempo = "<font color='green'>".$rptBreaks[ $agente['id'] ][ $break['id'] ]."</font>";
                } else {
                    $tiempo = $rptBreaks[ $agente['id'] ][ $break['id'] ];
                }
                $arrTmp[$indice] = $tiempo;
                $valorTime = $rptBreaks[ $agente['id'] ][ $break['id'] ];
                $arrTime = array(array ('duration'=>$sumaTiempo),array('duration'=>$valorTime));
                $sumaTiempo = $oReportsBreak->sumarTiempos($arrTime);
                $indice++;
            }
            $arrTmp[$indice] = $sumaTiempo;
            // asigno los valores del reporte al arreglo que contendra toda la informacion del reporte obtenido

            $arrData[] = $arrTmp;
        }

        $arrTmp[0] = "<b>".$lang["Total"]."</b>";
        $arrTmp[1] = "";
        $indiceTotal = 2;
	foreach($arrBreaks as $break) {

	    $sumTotal = "00:00:00";
	    foreach($arrAgentes as $agente) {
		$valorArr = $rptBreaks[ $agente['id'] ][ $break['id'] ];
		$arrTimeTotal = array(array("duration"=>$sumTotal),array("duration"=>$valorArr));
		$sumTotal = $oReportsBreak->sumarTiempos($arrTimeTotal);
	    }
	    $arrTmp[$indiceTotal] = "<b>".$sumTotal."</b>";
	    $indiceTotal++;
	}
        $arrTmp[$indiceTotal] = "";
        $arrData[] = $arrTmp;
    }
    // defino la cabecera del grid
    $arrGrid = array("title"    =>  $lang['Reports Break'],
	    "icon"     => "images/list.png",
	    "width"    => "99%",
	    "start"    => ($total==0) ? 0 : $offset + 1,
	    "end"      => ($offset+$limit)<=$total ? $offset+$limit : $total,
	    "total"    => $total,
	    "columns"  => $arrColumnas
	    );

    return $arrData;
}




?>
