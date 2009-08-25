<?php
include_once("../../../libs/misc.lib.php");
include_once("../../../configs/default.conf.php");
include_once("../../../libs/paloSantoDB.class.php");
require_once("../../../libs/smarty/libs/Smarty.class.php");

load_language('../../../');
load_language('../');
//load_theme("../../../");

realizar_archivo_csv();

function realizar_archivo_csv()
{
    global $arrLang;
    global $arrConf;
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        return false;
    }

    $smarty = getSmarty();
    $pDB = new paloDB($arrConf["cadena_dsn"]);
    if(!empty($pDB->errMsg)) {
        $smarty->assign("mb_message", $arrLang["Error when connecting to database"]."<br/>".$pDB->errMsg);
    }

    header("Cache-Control: private");
    header("Pragma: cache");
    header('Content-Type: text/csv; charset=iso-8859-1; header=present');
    header("Content-disposition: attachment; filename=".$_GET['name_campania'].".csv");
    echo csv_data_recolected_campaign($pDB,$smarty,$_GET['id']);
}

function getSmarty() {
    global $arrConf;
    $smarty = new Smarty();
    $smarty->template_dir = "themes/".$arrConf['mainTheme']."/";
    $smarty->compile_dir =  "var/templates_c/";
    $smarty->config_dir =   "configs/";
    $smarty->cache_dir =    "var/cache/";
    return $smarty;
}

function csv_data_recolected_campaign($pDB,$smarty,$idCampaign)
{
    global $arrLang;
    global $arrLan;
    $resultado_csv="";
    $bandera = true; //sirve para saber si s deben agregar las cabeceras que no tiene nigun valor en las llamadas (campos no llenados) 
    //PASO 1 OBTENGO TODAS LAS LLAMADAS CON ESTADO SUCCESS O FAILURE
    $sql_telefonos = "
        select
            a.number number,
            c.phone telefono,
            c.id,
            c.status estado,
            c.start_time fecha_hora,
            c.duration duracion,
            ca_nombre.value nombre_valor,
            ca_direccion.value direccion_valor
        from
            calls c
                left join
            call_attribute ca_nombre on c.id = ca_nombre.id_call and ca_nombre.column_number = 1
                left join
            call_attribute ca_direccion on c.id = ca_direccion.id_call and ca_direccion.column_number = 3
                left join
            agent a on c.id_agent = a.id
        where
            c.id_campaign=$idCampaign and
            (c.status='Success' or c.status='Failure' or c.status='ShortCall')
        order by
            telefono
        asc";
    $result_telefonos = $pDB->fetchTable($sql_telefonos,true);
    if (is_array($result_telefonos) && count($result_telefonos)>0){
        $grupo_cabeceras[] = "";
        $grupo_cabeceras[] = "";
        $grupo_cabeceras[] = "";
        $grupo_cabeceras[] = "";
        $grupo_cabeceras[] = "";
        $grupo_cabeceras[] = "";
        $grupo_cabeceras[] = "";
        $cabeceras[] = $arrLan['Phone Customer'];
        $cabeceras[] = $arrLan['Name Customer'];
        $cabeceras[] = $arrLan['Address'];
        $cabeceras[] = $arrLan['Status Call'];
        $cabeceras[] = "Agente";
        $cabeceras[] = $arrLan['Date & Time'];
        $cabeceras[] = $arrLan['Duration'];


        // INICIO: ESTO ES PARA AGREGAR LAS CABECERAS EN ORDEN
        $sql_datos_recolected_previo = "
            select
                ff.etiqueta campo_nombre,
                f.nombre formulario_nombre
            from
                campaign_form cf
                    inner join
                form f on cf.id_form = f.id
                    inner join
                form_field ff on f.id = ff.id_form
            where
                cf.id_campaign = $idCampaign
                and ff.tipo<>'LABEL'
            order by
                f.id, ff.orden asc;";
        $valor_recolectado_previo[] =  "";
        $valor_recolectado_previo[] =  "";
        $valor_recolectado_previo[] =  "";
        $valor_recolectado_previo[] =  "";
        $valor_recolectado_previo[] =  "";
        $valor_recolectado_previo[] =  "";
        $result_data_recolected_previo = $pDB->fetchTable($sql_datos_recolected_previo,true);

        if (is_array($result_data_recolected_previo) && count($result_data_recolected_previo)>0){
            foreach($result_data_recolected_previo as $key_data_recolected_previo => $value_data_recolected_previo){
                $indice = tratar_cabecera($cabeceras,$value_data_recolected_previo['campo_nombre'],$grupo_cabeceras,$value_data_recolected_previo['formulario_nombre']);
                //tratar_valor_recolectado($valor_recolectado,$indice,"rr");
            }
        }
        // FIN: ESTO ES PARA AGREGAR LAS CABECERAS EN ORDEN


        //PASO 2 OBTENGO LOS DATOS DE UN LLAMDA EN COMUN (DATOS RECOLECTADOS)
        foreach($result_telefonos as $key_telefono => $value_telefono){
           $sql_datos_recolected = "
                select
                    c.id_agent agentnum,
                    c.phone telefono,
                    ff.etiqueta campo_nombre,
                    ff.tipo campo_tipo,
                    fdr.value campo_valor,
                    f.nombre formulario_nombre
                from
                    campaign_form cf
                        inner join
                    form f on cf.id_form = f.id
                        inner join
                    form_field ff on f.id = ff.id_form
                        left join
                    form_data_recolected fdr on ff.id = fdr.id_form_field
                        left join
                    calls c on fdr.id_calls = c.id
                where
                    cf.id_campaign = $idCampaign and
                    c.id = ".$value_telefono['id']."
                order by
                    telefono
                asc;";

            $valor_recolectado[] =  $value_telefono['telefono'];
            $valor_recolectado[] =  $value_telefono['nombre_valor'];
            $valor_recolectado[] =  $value_telefono['direccion_valor'];
            $valor_recolectado[] =  $arrLan[$value_telefono['estado']];
            $valor_recolectado[] =  $value_telefono['number'];
            $valor_recolectado[] =  $value_telefono['fecha_hora'];
            $valor_recolectado[] =  $value_telefono['duracion'];
            $result_data_recolected = $pDB->fetchTable($sql_datos_recolected,true);

            //PASO 3 TRATAMIENTO DE LOS DATOS PARA FORMAR EL FORMATO DE DATOS CABECERAS Y VALORES
            //CABECERAS SON : $value_data_recolected['campo_nombre']
            //VALORES SON   : $value_data_recolected['campo_valor']
            if (is_array($result_data_recolected) && count($result_data_recolected)>0){
                foreach($result_data_recolected as $key_data_recolected => $value_data_recolected){
                    $value_data_recolected['campo_valor'] = str_replace("\n"," ",$value_data_recolected['campo_valor']);
                    $value_data_recolected['campo_valor'] = str_replace("\r"," ",$value_data_recolected['campo_valor']);
                    $indice = tratar_cabecera($cabeceras,$value_data_recolected['campo_nombre'],$grupo_cabeceras,$value_data_recolected['formulario_nombre']);
                    tratar_valor_recolectado($valor_recolectado,$indice,$value_data_recolected['campo_valor']);
                }
            }
            $valores_recolectados[]=$valor_recolectado;
            $valor_recolectado = array();
        }
    }
    else{
        $resultado_csv = $arrLang['No Data Found'];
        $bandera = false;
    }
//     if($bandera) //PASO 4 AGREGO LAS CABECERAS QUE NO HAN TENIDO NINGUN VALOR, CON NIGUNA LLAMADA (CABECERAS QUE NO SE LLENARON EN NIGUNA LLAMADA)
//         agregar_cabeceras_que_faltan($pDB,$cabeceras,$grupo_cabeceras);

    //PASO 5 CONVIERTO LOS ARREGLOS A CSV
    //FORMATO CSV:
    //LINEA 1   : "TELEFONO","ESTADO LLAMADA",......,......,......,
    //LINEA 2-N : "361820","SE REALIZO",.....,....,

//     FORMATO ANTERIOR DEL CSV SEPARADO POR COMAS Y TEXTO CON COMILLAS DOBLES

     if(is_array($grupo_cabeceras) && count($grupo_cabeceras)>0){
        for($i=0;$i<count($grupo_cabeceras)-1;$i++)
            $resultado_csv .= "\"".str_replace("\"","'",$grupo_cabeceras[$i])."\",";
        $resultado_csv .= "\"".str_replace("\"","'",$grupo_cabeceras[count($grupo_cabeceras)-1])."\" \n";
    }
    if(is_array($cabeceras) && count($cabeceras)>0){
        for($i=0;$i<count($cabeceras)-1;$i++)
            $resultado_csv .= "\"".str_replace("\"","'",$cabeceras[$i])."\",";
        $resultado_csv .= "\"".str_replace("\"","'",$cabeceras[count($cabeceras)-1])."\" \n";
    }
    if(is_array($valores_recolectados) && count($valores_recolectados)>0){
        foreach($valores_recolectados as $key_recolectado => $value_recolectado){
            hacer_homogeneo_array($value_recolectado,count($cabeceras));
            for($i=0;$i<count($value_recolectado)-1;$i++)
                $resultado_csv .= "\"".str_replace("\"","'",$value_recolectado[$i])."\",";
            $resultado_csv .= "\"".str_replace("\"","'",$value_recolectado[count($value_recolectado)-1])."\" \n";
        }
    }

//     FORMATO ACTUAL DEL CSV SEPARADO POR PAI (|) Y TEXTO SIN NADA
//     if(is_array($cabeceras) && count($cabeceras)>0){
//         for($i=0;$i<count($cabeceras)-1;$i++)
//             $resultado_csv .= $cabeceras[$i]."| ";
//         $resultado_csv .= $cabeceras[count($cabeceras)-1]."\n ";
//     }
//     if(is_array($valores_recolectados) && count($valores_recolectados)>0){
//         foreach($valores_recolectados as $key_recolectado => $value_recolectado){
//             hacer_homogeneo_array($value_recolectado,count($cabeceras));
//             for($i=0;$i<count($value_recolectado)-1;$i++)
//                 $resultado_csv .= $value_recolectado[$i]."| ";
//             $resultado_csv .= $value_recolectado[count($value_recolectado)-1]."\n ";
//         }
//     }
    return $resultado_csv;
}

// function agregar_cabeceras_que_faltan($pDB,&$cabeceras,&$grupo_cabeceras)
// {
//     $sql = "select 
//                 ff.etiqueta campo_nombre, 
//                 f.nombre formulario_nombre
//             from 
//                 campaign_form cf
//                     inner join
//                 form f on cf.id_form = f.id
//                     inner join
//                 form_field ff on f.id = ff.id_form
//                     left join
//                 form_data_recolected fdr on ff.id = fdr.id_form_field
//                     left join
//                 calls c on fdr.id_calls = c.id
//             where 
//                 cf.id_campaign = 1 and c.phone is null;";
//     $result = $pDB->fetchTable($sql,true);
//     if(is_array($result) && count($result)>0){
//         foreach($result as $key => $value)
//             $indice = tratar_cabecera($cabeceras,$value['campo_nombre'],$grupo_cabeceras,$value['formulario_nombre']);
//         return true;
//     }
//     else false;
// }

function hacer_homogeneo_array(&$value_recolectado,$cantidad)
{
    if(is_array($value_recolectado) && count($value_recolectado)>0){ 
       if(count($value_recolectado) < $cantidad){
               for($i=count($value_recolectado);$i < $cantidad;$i++)
                    $value_recolectado[] ="";
       }
    }
}

function tratar_cabecera(&$cabeceras,$cabecera,&$grupo_cabeceras,$grupo)
{
    if(is_array($cabeceras) && count($cabeceras)>0){ 
        foreach($cabeceras as $key => $value){
            if($value==$cabecera)
                return $key; //ya existe la cabecera y retorno su indice
        }
        $cabeceras[count($cabeceras)] = $cabecera; //no existe la cabecera la agrego
        $grupo_cabeceras[] = $grupo; // agrego el grupo a la que pertence la nueva cabecera
        return count($cabeceras) - 1; //retorno el indice de la nueva cabecera
    }
    return -1;
}

function tratar_valor_recolectado(&$valor_recolectado,$indice,$valor)
{
    if(is_array($valor_recolectado) && count($valor_recolectado)>0){ 
        if(count($valor_recolectado)-1 < $indice){
            for($i=count($valor_recolectado);$i < $indice;$i++)
                $valor_recolectado[] = ""; //lleno de vacio
            $valor_recolectado[] = $valor;
            return count($valor_recolectado);
        }
        else if(count($valor_recolectado)-1 >= $indice){
            $valor_recolectado[$indice] = $valor;
            return $indice;
        }
    }
    return -1;
}
?>
