<?
require_once "libs/paloSantoForm.class.php";
require_once "libs/paloSantoTrunk.class.php";
include_once "libs/paloSantoConfig.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once("libs/paloSantoGrid.class.php");
    require_once "libs/misc.lib.php";
    global $arrConf;
    global $arrLang;
    global $arrConfig;
    
    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConfig['templates_dir']))?$arrConfig['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
    $formCampos= array();

        
////codigo para mostrar la lista de archivos
    $path=$arrOtro['etc_asterisk'];

//para formar el arreglo de todos los archivos
    if (is_dir($path) && file_exists($path)) {
        $directorio=dir($path);
        $arreglo_archivos = array();
        while ($archivo = $directorio->read())
        {   
            if ($archivo!="." && $archivo!=".."){
                array_push($arreglo_archivos, $archivo);
            }
        }
        $directorio->close();
    } else {
        $smarty->assign("msj_err",$arrLang['This is not a valid directory']);
    }

//para mostrar la lista de archivos
    $arrData=array();
    if (is_array($arreglo_archivos)) {
        foreach($arreglo_archivos as $item){
            $arrTmp    = array();
            $arrTmp[0] = "&nbsp;<a href='?menu=$module_name&action=EditarArchivo&archivo=$item'>".$item."</a>" ;
            $arrData[] = $arrTmp;
        }
    }

//fin
    if(isset($_GET['archivo']) && $_GET['archivo']!="") {
      	$sAccion='editar';
    }
    
    if(isset($_POST['back'])) {
        $sAccion='regresar';
        $_POST['action'] = $_GET['action'] = "";
        $_POST['archivo'] = $_GET['archivo'] = "";
    }

////PARA EL PAGINEO
     $total=count($arreglo_archivos);
    // LISTADO
        
        $limit = 25;
        $offset = 0;
    
        // Si se quiere avanzar a la sgte. pagina
        if($_GET['nav']=="end") {
    
            // Mejorar el sgte. bloque.
            if(($total%$limit)==0) {
                $offset = $total - $limit;
            } else {
                $offset = $total - $total%$limit;
            }
        }
    
        // Si se quiere avanzar a la sgte. pagina
        if($_GET['nav']=="next") {
            $offset = $_GET['start'] + $limit - 1;
            
        }
    
        // Si se quiere retroceder
        if($_GET['nav']=="previous") {
            $offset = $_GET['start'] - $limit - 1;
        }
    
        // Construyo el URL base
        if(is_array($arreglo_archivos) and count($arreglo_archivos)>0) {
           
            $url = construirURL($arreglo_archivos, array("nav", "start"));
        } else {
            $url = construirURL(array(), array("nav", "start")); 
        }
        $smarty->assign("url", $url);
    
        $inicio = ($total==0) ? 0 : $offset + 1;
        $fin = ($offset+$limit)<=$total ? $offset+$limit : $total;
        $leng=$fin-$inicio;
        //muestro los registros correspondientes al offset
        $arr_archivos_final=array_slice($arrData,$inicio-1,$leng+1);
        
////FIN DEL PAGINEO


    $arrGrid = array("title"    => $arrLang["File Editor"],
                         "icon"     => "images/kfaxview.png",
                         "width"    => "99%",
                         "start"    => $inicio,
                         "end"      => $fin,
                         "total"    => $total,
                         "columns"  => array(0 => array("name"      => $arrLang["File List"],
                                                        "property1" => ""),
                                            
                                            )
                    );
    
    $oGrid = new paloSantoGrid($smarty);
    $oForm = new paloForm($smarty, $formCampos);
    $smarty->assign("module_name",$module_name);
    $contenidoModulo = $oGrid->fetchGrid($arrGrid,$arr_archivos_final,$arrLang);
    $contenidoModulo .= $oForm->fetchForm("$local_templates_dir/new.tpl", $arrLang["File Editor"], $_POST);
   
    


    ////PARA EJECUTAR EL ACTION DEL TPL
    switch ($sAccion) {
    case "editar":
       $contenidoModulo=EscribirArchivo($arrOtro,$contenidoModulo,$arrLang,$_GET,$_POST);
       break;

    case "regresar":


       return $contenidoModulo;
       break;
    }
   
    return $contenidoModulo;

}


function EscribirArchivo($arrOtro,$contenidoModulo,$arrLang,$_GET,$_POST){
   
    $fichero = $_GET['archivo'];
    $texto = $_POST["archivo_textarea"];
    $msj_no_escritura3="";$msj_no_lectura2="";
    $bandera=1;

//para el mensaje cuando se guarde
    if($_POST['guardar'])
        $se_guardo = $arrLang["The changes was saved in the file"];
    else
        $se_guardo ="";


  $ruta_archivo = $arrOtro['etc_asterisk'].$fichero;

 //para saber si es escribible
    if(is_writable($ruta_archivo)){ 
    
        if($texto != ''){
            if($fp = fopen($ruta_archivo,"w+")){
                fwrite($fp,stripslashes($texto));
                $msj_no_escritura3 = "";
                $bandera=1;
            }
            else{
                $msj_no_escritura3 = $arrLang["This file doesn't have permisses to write"];
                $bandera=0;
            }
            fclose($fp);
        }
    }
    else{
        $msj_no_escritura3 = $arrLang["This file doesn't have permisses to write"];
        $bandera=0;
    }
        
 //para saber si es de lectura   
    if(is_readable($ruta_archivo)){ 
        if($fp = fopen($ruta_archivo,"r")){ 
            $contenido = fread ($fp, filesize ($ruta_archivo));
            $msj_no_lectura2 = "";
            
        }
        else{
            $msj_no_lectura2 = $arrLang["This file doesn't have permisses to read"];
            
        }
        
        fclose($fp);
    }
else{
        $msj_no_lectura2 = $arrLang["This file doesn't have permisses to read"];
        $msj = $arrLang["Doesn't have permisses to read"];
        $contenidoModulo='<center><table class="message_board" align="center"><tr><td class="mb_message"><b>'.$fichero .' '.$msj.'</b></td></tr>'.$contenidoModulo.'</table></center>';
        return $contenidoModulo;
    }


    if($bandera==0){
        $contenidoModulo ='<form method="POST" enctype="multipart/form-data"><table class="message_board" width="99%" border="0" cellspacing="0" cellpadding="0" >
        <tr><td class="mb_message"><font size="2px">'.$se_guardo.'<br>'.$msj_no_escritura3.'<br>'.$msj_no_lectura2.'</font></td></tr></table><br><center><b>'.$fichero.'</b><br><textarea cols="60" rows="17" name="archivo_textarea">'.$contenido.'</textarea><br><br><input type="submit" name="back" id="back" onclick="" value='.'<<&nbsp;'. $arrLang["Back"].'></center></form>';
    }
    else{
        $contenidoModulo ='<form method="POST" enctype="multipart/form-data"><table class="message_board"  width="99%" border="0" cellspacing="0" cellpadding="0" >
        <tr><td class="mb_message"><font size="2px">'.$se_guardo.'<br>'.$msj_no_escritura3.'<br>'.$msj_no_lectura_2.'</font></td></tr></table></br><center><b>'.$fichero.'</b><br><textarea cols="60" rows="17" name="archivo_textarea">'.$contenido.'</textarea><br><br><input type="submit" name="back" id="back"  onclick="" value='.'<<&nbsp;'. $arrLang["Back"].'>'.'&nbsp;&nbsp;'.'<input type="submit" name="guardar" onclick=" " value="Saved"/></center></form>';
    
    }
    return $contenidoModulo;
    
}



?>