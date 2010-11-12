<?php

include_once("/var/www/html/libs/paloSantoDB.class.php");

global $path, $template_module;

$path = "/var/www/html";
$module_name = "agent_console";


include_once("$path/libs/misc.lib.php");
include_once "$path/configs/default.conf.php";

// Load smarty
require_once("$path/libs/smarty/libs/Smarty.class.php");
$smarty = new Smarty();

$smarty->template_dir = "$path/themes/" . $arrConf['mainTheme'];
$smarty->compile_dir =  "$path/var/templates_c/";
$smarty->config_dir =   "$path/configs/";
$smarty->cache_dir =    "$path/var/cache/";

if (!function_exists('getParameter')) {
function getParameter($parameter)
{
    if(isset($_POST[$parameter]))
        return $_POST[$parameter];
    else if(isset($_GET[$parameter]))
        return $_GET[$parameter];
    else
        return null;
}
}

$html = _moduleContent($smarty, $module_name);
$smarty->assign("CONTENT", $html);
$smarty->assign("THEMENAME", $arrConf['mainTheme']);
$smarty->assign("MODULE_NAME", $module_name);
$smarty->assign("path", "../../../");
$smarty->display("$path/modules/$module_name/$template_module/programar_llamadas.tpl");	

function getDB() {
    global $arrConf;
    $pDB = new paloDB($arrConf["cadena_dsn"]);
    return $pDB;
}


function _moduleContent(&$smarty, $module_name)
{
    global $path, $template_module, $module_calendar;
    //include elastix framework
    include_once "$path/libs/paloSantoGrid.class.php";
    include_once "$path/libs/paloSantoValidar.class.php";
    include_once "$path/libs/paloSantoConfig.class.php";
    include_once "$path/libs/misc.lib.php";
    include_once "$path/libs/paloSantoForm.class.php";

    //include module files
    include_once "$path/modules/$module_name/configs/default.conf.php";

    global $arrConf;
    load_language("../../../");
    global $arrLang;
    $lang = "";
    $script_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $lang_file="$path/modules/$module_name/lang/$lang.lang";
    if (file_exists("$script_dir/$lang_file"))
        include_once($lang_file);
    else
        include_once("$path/modules/$module_name/lang/en.lang");

    //include module files
    include_once "$path/modules/$module_name/configs/default.conf.php";
    global $arrConf;

	//folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);

    $templates_dir=(isset($arrConfig['templates_dir']))?$arrConfig['templates_dir']:'themes';
    $local_templates_dir="$path/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    $template_module = $templates_dir.'/'.$arrConf['theme'];

    $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
    $arrConfig = $pConfig->leer_configuracion(false);
	
    $dsn_agi_manager['password'] = $arrConfig['AMPMGRPASS']['valor'];
    $dsn_agi_manager['host'] = $arrConfig['AMPDBHOST']['valor'];
    $dsn_agi_manager['user'] = 'admin';
	
    $action = getAction();
    $id_campana = $id_call = $num_telefono = "";
    //validamos el di campaña y id call
    if(isset($_GET['id_campana'])){
        $id_campana = $_GET['id_campana'];
    }
    if(isset($_GET['id_call'])){
        $id_call = $_GET['id_call'];
    }

    if(isset($_POST['numero'])){
        $num_telefono = $_POST['numero'];
    }
    else{
        $num_telefono = $_GET['num_telefono'];
    }

//lo mismo para el nombre del cliente
    if(isset($_POST['cliente'])){
        $cliente = $_POST['cliente'];
    }
    else{
        $cliente = $_GET['cliente'];
    }

    $_POST['numero'] = $num_telefono;
    $_POST['cliente'] = $cliente;

    $smarty->assign("id_campana_hidden", $id_campana);
    $smarty->assign("id_call_hidden", $id_call);
    $smarty->assign("num_telefono_hidden", $num_telefono);
    $smarty->assign("cliente_hidden", $cliente);

    $content = "";
    switch($action){
        default:
            $content = obtener_formulario($smarty,$module_name, $local_templates_dir, $arrLang, $id_campana,  $id_call);
            break;
    }

	return $content;
}

function obtener_formulario(&$smarty, $module_name, $local_templates_dir, $arrLang, $id_campana,  $id_call)
{

    include_once "/var/www/html/libs/paloSantoForm.class.php";
    global $arrLangModule;
    $arr_programar_llamada = array('radio1' =>$arrLangModule["ProgramCalls"]);
    $arr_final_campana = array('radio2' => $arrLangModule["Final Call"]); 
	$contenidoModulo = '';

    //msj para alert cuando se presiona Add
    //$smarty->assign("no_guarda_fecha_hora", $arrLangModule["No data and no hour will be saved"]);

    $horas = array();
    $i = 0;
    for( $i=-1;$i<24;$i++)
    {
        if($i == -1)     $horas["HH"] = "HH";
        else if($i < 10) $horas["0$i"] = '0'.$i;
        else             $horas[$i] = $i;
    }

    $minutos = array();
    $i = 0;
    for( $i=-1;$i<60;$i++)
    {
        if($i == -1)     $minutos["MM"] = "MM";
        else if($i < 10) $minutos["0$i"] = '0'.$i;
        else             $minutos[$i] = $i;
    }
    $num_telef = "";
    //PARA QUE SE SETEE EL NUMERO ACTUAL AL Q SE ESTA LLAMANDO QUE VIENE POR GET, SE LO ASIGNA AL POST
    if(isset($_GET['num_telefono']))
        $num_telef = $_GET['num_telefono'];

    $arrFormElements = array(
                "numero"          => array( "LABEL" => $arrLangModule["Agent Number"],
                    "REQUIRED"              => "yes",
                    "INPUT_TYPE"            => "TEXT",
                    "INPUT_EXTRA_PARAM"     => "",
                    "VALIDATION_TYPE"       => "text",
                    "VALIDATION_EXTRA_PARAM"=> ""),

                "hora_ini_HH"   => array(
                    "LABEL"                  => $arrLangModule["Start time"],
                    "REQUIRED"               => "yes",
                    "INPUT_TYPE"             => "SELECT",
                    "INPUT_EXTRA_PARAM"      => $horas,
                    "VALIDATION_TYPE"        => 'numeric',
                    "VALIDATION_EXTRA_PARAM" => '',
                ),
                "hora_ini_MM"   => array(
                    "LABEL"                  => $arrLangModule["Start time"],
                    "REQUIRED"               => "yes",
                    "INPUT_TYPE"             => "SELECT",
                    "INPUT_EXTRA_PARAM"      => $minutos,
                    "VALIDATION_TYPE"        => 'numeric',
                    "VALIDATION_EXTRA_PARAM" => '',
                ),
                "hora_fin_HH"   => array(
                    "LABEL"                  => $arrLangModule["End time"],
                    "REQUIRED"               => "yes",
                    "INPUT_TYPE"             => "SELECT",
                    "INPUT_EXTRA_PARAM"      => $horas,
                    "VALIDATION_TYPE"        => 'numeric',
                    "VALIDATION_EXTRA_PARAM" => '',
                ),
                "hora_fin_MM"   => array(
                    "LABEL"                  => $arrLangModule["End time"],
                    "REQUIRED"               => "yes",
                    "INPUT_TYPE"             => "SELECT",
                    "INPUT_EXTRA_PARAM"      => $minutos,
                    "VALIDATION_TYPE"        => 'numeric',
                    "VALIDATION_EXTRA_PARAM" => '',
                ),

//                 PaloSanto- Agregado para registrar el nombre de la persona a la que vamos a llamar
                "cliente"          => array( "LABEL" => $arrLangModule["Name"],
                    "REQUIRED"              => "yes",
                    "INPUT_TYPE"            => "TEXT",
                    "INPUT_EXTRA_PARAM"     => "",
                    "VALIDATION_TYPE"       => "text",
                    "VALIDATION_EXTRA_PARAM"=> ""),

    );

    $oFilterForm = new paloForm($smarty, $arrFormElements);

    $smarty->assign("label_llamar_final", $arrLangModule["Final Call"]);
    $smarty->assign("label_programar", $arrLangModule["ProgramCalls"]);
    $smarty->assign("label_numero", $arrLangModule["Agent Number"]);

    $time_ini = $time_fin = "";
    if(isset($_POST['hora_ini_HH']) && isset($_POST['hora_ini_MM']))
        $time_ini = $_POST['hora_ini_HH'].":".$_POST['hora_ini_MM'];
    if(isset($_POST['hora_fin_HH']) && isset($_POST['hora_fin_MM']))
        $time_fin = $_POST['hora_fin_HH'].":".$_POST['hora_fin_MM'];

    $iHoraIni =  strtotime($time_ini);
    $iHoraFin =  strtotime($time_fin); 
    $hora_inicial = $time_ini;
    $hora_final = $time_fin;


    //VALIDACION DE FECHAS
    $fecha_init = date("d M Y");
    $fecha_end  = date("d M Y");

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
    //VALIDACION DE FECHAS FIN

    $smarty->assign("fecha_inicio",  insertarCabeceraCalendario()."
                        <tr>
                            <td class='letra12' width='15%'>
                                {$arrLangModule["Date Init"]}
                                <span  class='required'>*</span>
                            </td>
                            <td  width='30%'>
                                ".insertarDateInit($fecha_init_actual)."
                            </td>
 
                            <td class='letra12' width='15%'>
                                {$arrLangModule["Date End"]}
                                <span  class='required'>*</span>
                            </td>
                            <td width='30%'>
                                ".insertarDateEnd($fecha_end_actual)."
                            </td>

                        </tr>");

    $smarty->assign("AGREGAR", $arrLangModule["Add"]);
    $smarty->assign("AGREGAROTRO", $arrLangModule["Add Other"]);
    $smarty->assign("CANCEL", $arrLangModule["Cancel"]);

    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/programar_llamadas.tpl", "", $_POST);

    $action = getAction();

    switch($action){
        case "agregar":
        case "agregar_otro":
            //LLAMO A LA FUNCION AGREGAR QUE GUARDARA LA INFORMACION Y CERRARA LA VENTANA DESPUES DE GUARDAR UN NUMERO
            $contenidoModulo = agregar($smarty, $arrLangModule, $id_campana,  $id_call, $fecha_init_actual,$fecha_end_actual, $hora_inicial, $hora_final ,$local_templates_dir,$oFilterForm, $module_name);
            break;
/*
        case "agregar_otro":
            //LLAMO A LA FUNCION AGREGAR OTRO QUE GUARDARA LA INFORMACION Y DEJARA LA VENTANA LISTA PARA SEGUIR GUARDANDO MAS NUMEROS
            $contenidoModulo = agregar($smarty, $arrLangModule, $id_campana,  $id_call, $fecha_init_actual,$fecha_end_actual, $hora_inicial, $hora_final ,$local_templates_dir,$oFilterForm);
            break;
*/            
        case "cancel":
            break;
    }//fin del swtich

    return $contenidoModulo;
}


//Funcion Agregar
function agregar(&$smarty, $arrLangModule, $id_campana,  $id_call, $fecha_init_actual,$fecha_end_actual, $hora_inicial, $hora_final, $local_templates_dir, $oForm){
	$msgResultado = NULL;
	$msgResultadoBase = NULL;

        $bandera_llamar_final = $bandera_programar = $bandera_error = false;
        $hora_inicial = trim($hora_inicial);
        $hora_final = trim($hora_final);

        //ESTE IF  ES PARA PROGRAMAR LLAMADA - VALIDAMOS SI ESTA SETEADO EL Programar Fechas
        if(isset($_POST['new_accion']) && $_POST['new_accion']=="radio_programar" ){
            $bandera_programar = true;
            //enviamos este true al radio programar
            $smarty->assign("true_p", "checked");
            $smarty->assign("true_l", "");

            //VALIDACION NUEVA DE FECHAS
            $sValidacion = "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$";

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
                    $msgResultado = $arrLangModule["Invalid Start Date"];//'Fecha de inicio no es válida
                    $bandera_error = true;//seteamos true  a error
                }
                // pregunto si es valido el formato de la fecha final
                if ( ereg( $sValidacion , $_POST['txt_fecha_end'] ) ) {
                    // si el formato es valido procedo a convertir la fecha en un arreglo que contiene 
                    // el anio , mes y dia seleccionados
                    $fecha_end = $fecha_end_actual;//$_POST['txt_fecha_end'];
                    $arrFecha_end = explode('-',translateDate($fecha_end));
                }else {
                    // si la fecha esta en un formato no valido se envia un mensaje de error
                    $msgResultado =  $arrLangModule["Invalid End Date"];//'Fecha de final no es válida
                    $bandera_error = true;//seteamos true  a error
                }

                if ((ereg( $sValidacion , $_POST['txt_fecha_end'] ) && ereg( $sValidacion , $_POST['txt_fecha_init'] )) &&  ($_POST['txt_fecha_init']>$_POST['txt_fecha_end']) ) {
                    $msgResultado =  $arrLangModule["Start Date must be greater than End Date"];//'Fecha de inicio debe ser anterior a la fecha final';
                    $bandera_error = true;//seteamos true  a error
                } 
            }
            elseif(!isset($fecha_init) && !isset($fecha_end)) {
                // si se ha presionado el boton para listar por fechas, y no se ha ingresado una fecha
                // se le muestra al usuario un mensaje de error
                $msgResultado =  $arrLangModule["You must be enter a valid init/end date"];//'Fecha de final no es válida
                $bandera_error = true;//seteamos true  a error
            }


            //VALIDACION NUEVA DE FECHAS  FIN

            //VALIDACION DE HORAS
            if (!ereg('^[[:digit:]]{2}:[[:digit:]]{2}$', $hora_inicial)) {
                $msgResultado =  $arrLangModule["Invalid Start Time"];//'Hora de inicio no es válida (se espera hh:mm)';
                $bandera_error = true;//seteamos true  a error
            } elseif (!ereg('^[[:digit:]]{2}:[[:digit:]]{2}$', $hora_final)) {
                $msgResultado =  $arrLangModule["Invalid End Time"];//'Hora de final no es válida (se espera hh:mm)';
                $bandera_error = true;//seteamos true  a error
            } elseif (strcmp($fecha_init_actual,$fecha_end_actual)==0 && strcmp ($hora_inicial,$hora_final)>=0) {
                $msgResultado =  $arrLangModule["Start Time must be greater than End Time"];//'Hora de inicio debe ser anterior a la hora final';
                $bandera_error = true;//seteamos true  a error
            } 

        }//fin programar llamada

        //ESTE ELSE ES PARA LLAMAR AL FINAL DE CAMPAÑA
        else{//es radio Llamar al final de campaña
            $bandera_llamar_final = true;
            if(isset($_POST['new_accion']) && $_POST['new_accion']=="radio_llamar" ){
                //enviamos este true al radio llamar
                $smarty->assign("true_l", "checked");
                $smarty->assign("true_p", "");
            }
        }

        //VALIDAMOS CAMPO NUMERO Q NO ESTE VACIO
        if($_POST['numero']==""){
            $msgResultado = $arrLangModule["Number field can not be empty"];
            $smarty->assign("mb_message","*".$msgResultado);
            $bandera_error = true;//seteamos true  a error
        }else{
            //Si NO es nuemrico tambien genero mensaje error
            if(!is_numeric($_POST['numero']) ){
                $msgResultado = $arrLangModule["Number field can be numeric"];
                $bandera_error = true;//seteamos true  a error
            }
        }


        //CAMPOS OCULTOS
        if(isset($_POST['id_call_hidden'])){
            $id_call_ = $_POST['id_call_hidden'];
        }else{
            $_POST['id_call_hidden'] = $_GET['id_call'];
            $id_call_ = $_GET['id_call'];
        }
        if(isset($_POST['id_campana_hidden'])){
            $id_campana_ = $_POST['id_campana_hidden'];
        }else{
            $_POST['id_campana_hidden'] = $_GET['id_campana'];
            $id_campana_ = $_GET['id_campana'];
        }
        if(isset($_POST['num_telefono_hidden'])){
            $num_telefono_ = $_POST['num_telefono_hidden'];
        }else{
            $_POST['num_telefono_hidden'] =  $_GET['num_telefono'];
            $num_telefono_ = $_GET['num_telefono'];
        }
//para cliente tambien
        if(isset($_POST['cliente_hidden'])){
            $cliente_ = $_POST['cliente_hidden'];
        }else{
            $_POST['cliente_hidden'] =  $_GET['cliente'];
            $cliente_ = $_GET['cliente'];
        }



        $_POST['id_call_hidden'] = $id_call_; 
        $_POST['id_campana_hidden'] = $id_campana_;

        if(isset($_POST['numero'])){
            $_POST['num_telefono_hidden'] = $_POST['numero'];
        }
        else{
            $_POST['num_telefono_hidden'] = $num_telefono_;
        }


        if(isset($_POST['cliente'])){
            $_POST['cliente_hidden'] = $_POST['cliente'];
        }
        else{
            $_POST['cliente_hidden'] = $cliente_;
        }

        //REALIZAMOS TODO ESTO SIEMPRE Y CUANDO NO HAYAN ERRORES
        if(!$bandera_error){
            //primero convertimos las fechas a un formato valido
            $fecha_inicio_convertida = date("Y-m-d", strtotime($fecha_init_actual));
            $fecha_fin_convertida = date("Y-m-d", strtotime($fecha_end_actual));

            //tambien la hora le anexamos los segundos
            $hora_inicial_convertida = $hora_inicial.":00";
            $hora_final_convertida = $hora_final.":00";

            //ACTUALIZAMOS EN LA BASE DE DATOS
            //COMSULTAMOS PARA SABER SI EL NUMERO ES  EL MISMO
            $pDB = getDB();
            $sQuery = " SELECT phone 
                        FROM calls 
                        WHERE id_campaign=$id_campana_ 
                        AND id= $id_call_";
            $result = $pDB->getFirstRowQuery($sQuery, true);
            $phone = isset($result['phone'])?$result['phone']:"";

            $existe = false;//bandera que indica si guardar o no en la BD

            //VALIDACION CUANDO SE  ELIJE PROGRAMAR LLAMADAS
            if($bandera_programar){
                if($phone==$_POST['num_telefono_hidden']){
                    $existe = $rango_valido = false;

                    //primero preguntamos si ya existe esa llamada a esa misma hora
                    $existe = existe_llamada($id_campana_, $fecha_inicio_convertida, $fecha_fin_convertida, $hora_inicial_convertida, $hora_final_convertida, $_POST['num_telefono_hidden'], $pDB, $bandera='programar');

                    //---O-J-O--- PRIMERO VERIFICAMOS SI EL RANGO PARA LA LLAMADA PROGRAMADA ES VALIDO
                    $rango_valido = llamada_rango_valido($id_campana_, $id_call_, $fecha_inicio_convertida, $hora_inicial_convertida, $fecha_fin_convertida, $hora_final_convertida, $pDB);
                    //si es FALSO NO se permite guardar esta llamada xq NO esta dentro del rango de la campaña

                    if($existe){
                        $msgResultadoBase = $arrLangModule["A call with the same date and the same hour already exist"];
                    }
                    elseif(!$rango_valido){
                        $msgResultadoBase = $arrLangModule["The scheduled call date range is not between the campaign date range"];
                    }
                    else{//si permite guardar la llamada programada
                        //CAMBIO DE  PLANES - AHORA SE REGISTRA UN NUEVA LLAMADA
                        $insert_nuevo = "INSERT into calls (id_campaign, phone, date_init, date_end, time_init, time_end) VALUES ($id_campana_, '$phone', '$fecha_inicio_convertida', '$fecha_fin_convertida', '$hora_inicial_convertida', '$hora_final_convertida')";
                        $result_insert_nuevo = $pDB->genQuery($insert_nuevo);

                        //para que sea el ultimo insertado el que se ingresa
                        $sQuery_ultimo_id= "SELECT LAST_INSERT_ID() id";
                        $result_ultimo_id = $pDB->fetchTable($sQuery_ultimo_id,true);
                        $id_ultimo = $result_ultimo_id[0]['id'];

//                         Y debe insertarse en call_attribute con el nombre dado en el textbox Name
                        $insert_call_attribute = "INSERT into call_attribute (id_call, columna, value, column_number) VALUES ($id_ultimo, 0, '$_POST[cliente]', 1) ";
                        $result_insert_persona = $pDB->genQuery($insert_call_attribute);
                    }
                }
                else{
                    $existe = $rango_valido = false;
                    //primero preguntamos si ya existe esa llamada a esa misma hora
                    $existe = existe_llamada($id_campana_, $fecha_inicio_convertida, $fecha_fin_convertida, $hora_inicial_convertida, $hora_final_convertida, $_POST['num_telefono_hidden'], $pDB, $bandera='programar');

                    //---O-J-O--- PRIMERO VERIFICAMOS SI EL RANGO PARA LA LLAMADA PROGRAMADA ES VALIDO
                    $rango_valido = llamada_rango_valido($id_campana_, $id_call_, $fecha_inicio_convertida, $hora_inicial_convertida, $fecha_fin_convertida, $hora_final_convertida, $pDB);
                    //si es FALSO NO se permite guardar esta llamada xq NO esta dentro del rango de la campaña
                    if(!$rango_valido){
                        $msgResultadoBase = $arrLangModule["The scheduled call date range is not between the campaign date range"];
                    }
                    elseif($existe){
                        $msgResultadoBase = $arrLangModule["A call with the same date and the same hour already exist"];
                    }
                    else{
                        //Programar llamada - caso Numero Diferente - hacemos INSERT en calls
                        //------------CASO NUMERO DIFERENTE--------- TABLA CALLS CREAR NUEVO REGISTRO Y LENAR LOS CAMPOS id_campaign, phone(con nuevo numero), date_init/end, time_init/end
                        $insert = "INSERT into calls (id_campaign, phone, date_init, date_end, time_init, time_end) VALUES ($id_campana_, '$_POST[num_telefono_hidden]', '$fecha_inicio_convertida', '$fecha_fin_convertida', '$hora_inicial_convertida', '$hora_final_convertida')";
                        $result_insert = $pDB->genQuery($insert);

                        //para que sea el ultimo insertado el que se ingresa
                        $sQuery_ultimo_id= "SELECT LAST_INSERT_ID() id";
                        $result_ultimo_id = $pDB->fetchTable($sQuery_ultimo_id,true);
                        $id_ultimo = $result_ultimo_id[0]['id'];

//                         Y debe insertarse en call_attribute con el nombre dado en el textbox Name
                        $insert_call_attribute = "INSERT into call_attribute (id_call, columna, value, column_number) VALUES ($id_ultimo, 0, '$_POST[cliente]', 1) ";
                        $result_insert_persona = $pDB->genQuery($insert_call_attribute);

                    }
                }
            }//fin bandera programar llamadas
    
            //VALIDACION CUANDO SE  ELIJE LLAMAR AL FINAL DE LA CAMPAÑA
            elseif($bandera_llamar_final){
                if($phone==$_POST['num_telefono_hidden']){
                    //LLamar al final de la campaña - Caso igual numero - hacemos INSERT en calls
                    //En tabla Calls CREAR nuevo registro y llenar campos id_campaign, phone(con mismo fono)
                    $insert2 = "INSERT into calls (id_campaign, phone) VALUES ($id_campana_, '$phone')";
                    $result_insert2 = $pDB->genQuery($insert2);

                    //para que sea el ultimo insertado el que se ingresa
                    $sQuery_ultimo_id= "SELECT LAST_INSERT_ID() id";
                    $result_ultimo_id = $pDB->fetchTable($sQuery_ultimo_id,true);
                    $id_ultimo = $result_ultimo_id[0]['id'];

                    //Y debe insertarse en call_attribute con el nombre dado en el textbox Name
                    $insert_call_attribute = "INSERT into call_attribute (id_call, columna, value, column_number) VALUES ($id_ultimo, 0, '$_POST[cliente]', 1) ";
                        $result_insert_persona = $pDB->genQuery($insert_call_attribute);
                }
                else{

                    //LLamar al final de la campaña - Caso diferentes numeros - hacemos INSERT en calls
                    //EN tabla calls CREAR nuevo registro y llenar campos id_campaign, phone(con nuevo fono)
                    $insert3 = "INSERT into calls (id_campaign, phone) VALUES ($id_campana_, '$_POST[num_telefono_hidden]')";
                    $result_insert3 = $pDB->genQuery($insert3);

                    //para que sea el ultimo insertado el que se ingresa
                    $sQuery_ultimo_id= "SELECT LAST_INSERT_ID() id";
                    $result_ultimo_id = $pDB->fetchTable($sQuery_ultimo_id,true);
                    $id_ultimo = $result_ultimo_id[0]['id'];

                    //Y debe insertarse en call_attribute con el nombre dado en el textbox Name
                    $insert_call_attribute = "INSERT into call_attribute (id_call, columna, value, column_number) VALUES ($id_ultimo, 0, '$_POST[cliente]', 1) ";
                    $result_insert_persona = $pDB->genQuery($insert_call_attribute);

                }
            }

        }//fin NO hay errores


        if($msgResultado!='' || $msgResultadoBase!="" )
            $smarty->assign("mb_message","<td class='letra12' colspan='4'><span class='required'>*</span>$msgResultado $msgResultadoBase</td>");


        //Para mensaje de alert cuando se presiona Add, para que salga exito si se grabo  correctamente
        if(isset($_POST['agregar']) && $_POST['agregar']=='Add' && !$bandera_error && !$existe) {
            echo '<script>alert(\''.$arrLangModule["The data has been saved"].'\');window.close();</script>';
        }

        $contenidoModulo = $oForm->fetchForm("$local_templates_dir/programar_llamadas.tpl", "",$_POST);
        return $contenidoModulo;
}

//funcion para controlar si la llamada existe ya guardada en la base de datos
function existe_llamada($id_campana, $f_inicio=null, $f_fin=null, $hora_ini=null, $hora_fin=null , $phone=null, $pDB, $bandera){

    if($bandera=='programar'){
        //preguntamos si ya existe esa llamada a esa misma hora
        $sql = "SELECT id FROM calls WHERE id_campaign = $id_campana AND date_init = '$f_inicio' AND date_end='$f_fin' AND time_init='$hora_ini' AND  time_end='$hora_fin' AND phone='$phone'";
    }
    $result = $pDB->getFirstRowQuery($sql, true);
    if(isset($result['id']))
        return true;
    else
        return false;

}


//Esta funcion verifica si la fecha en que se programa la llamada esta dentro de la Fecha de la Campaña
function llamada_rango_valido($id_campana_, $id_call_, $fecha_inicio_convertida, $hora_inicial_convertida, $fecha_fin_convertida, $hora_final_convertida, $pDB){
    //Primero traemos las fechas y horas de inicio y fin de la campaña
    $sql_camp = "   SELECT concat(datetime_init, ' ', daytime_init) as fecha_ini_campana,                                    concat(datetime_end, ' ', daytime_end ) as fecha_fin_campana
                    FROM campaign 
                    WHERE id = $id_campana_";
    $result = $pDB->getFirstRowQuery($sql_camp, true);

    if(isset($result['fecha_ini_campana']))
        $fecha_inicio_campana = $result['fecha_ini_campana'];
    if(isset($result['fecha_fin_campana']))
        $fecha_fin_campana = $result['fecha_fin_campana'];

    //Ahora vamos a  comparar con las fechas programadas en el popup
    //Concatenamos la fecha inicial y hora final en una sola var, lo msimo con fecha y hora final
    $fecha_inicio_programada = $fecha_inicio_convertida." ".$hora_inicial_convertida;
    $fecha_fin_programada = $fecha_fin_convertida." ".$hora_final_convertida;
    $sql_calls = "  SELECT id
                    FROM calls 
                    WHERE id_campaign=$id_campana_
                    AND id =  $id_call_
                    AND ('$fecha_inicio_programada' between '$fecha_inicio_campana' AND '$fecha_fin_campana')  
                    AND ('$fecha_fin_programada' between '$fecha_inicio_campana' AND '$fecha_fin_campana')";
    $result_calls = $pDB->getFirstRowQuery($sql_calls, true);

    if(isset($result_calls['id'])){
        return true;//es valido
    }else{
        return false;
    }

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
function getAction()
{
    if(getParameter("agregar")) //Get parameter by POST (submit)
        return "agregar";

    else if(getParameter("action")=="agregar") //Get parameter by GET (command pattern, links)
        return "agregar";

    else if(getParameter("agregar_otro"))
        return "agregar_otro";

    else if(getParameter("id_campana"))
        return "id_campana";
    else if(getParameter("id_call"))
        return "id_call";

    else
        return "cancel";
}
?>
