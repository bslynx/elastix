<?php

class paloSantoReportsBreak {

    var $pDB;               // variable de conexion con la base de datos.
    var $msgError = "";     // variable de mensajes de error.

    /*
        Constructor de la clase.
    */
    function paloSantoReportsBreak($pDB) {

        if( is_object($pDB->conn) || $pDB->errMsg!="" ) {
            $this->pDB = $pDB;
        }else {
            $this->msgError = "Error de conexion a la base de datos";
            return false;
        }
    }

    /*
        Devuelve la conexion a la base de datos
    */
    function getConexion(){
        return $this->pDB;
    }
    /*
        Esta funcion retorna un reporte detallado de los break tomados por los agentes.
    */
    function getReporteDetalladoBreak() {

        $SQLConsulta = "select agent.name as Agente,break.name as Break,audit.datetime_init as Inicio,datetime_end as Fin,audit.duration as Duracion from audit,agent,break where agent.id=audit.id_agent and audit.id_break=break.id order by (datetime_init)";

        echo $SQLConsulta."<br>";
        
        $resConsulta = $this->pDB->fetchTable($SQLConsulta,true);

        $sResultado = "";
        if(!$resConsulta || !is_array($resConsulta)) {
            $this->msgError = "La consulta fallo : ";
        }elseif ( count($resConsulta) > 0) {

            $sFilas = "";
            foreach($resConsulta as $valor) {
                $sFilas .= $this->procesarResultado($valor);
            }
            $sResultado = $sFilas;
        }else  {
            $this->msgError = "La consulta no produjo resultados";
        }

        if($sResultado=="") {
            $sResultado = "No hay datos que presentar";
        }

        return $sResultado;
    }
    
    /*
        Esta funcion retorna un la informacion de un reporte detallado de los break tomados
        por los agentes. La cadena devuelta esta en codgo html, lista para ser incrustada entre
        los tag de inicio y cierre de una tabla.
    */
    function procesarResultado($arrRes) {
 
        $sColumnas = ""; 
        $sFilas = "";

        $sColumnas .= $this->crearColumna($arrRes['Agente']);
        $sColumnas .= $this->crearColumna($arrRes['Break']);
        $sColumnas .= $this->crearColumna($arrRes['Inicio']);
        $sColumnas .= $this->crearColumna($arrRes['Fin']);
        $sColumnas .= $this->crearColumna($arrRes['Duracion']);

        $sFilas = $this->crearFilas($sColumnas);

        return $sFilas;

    }
    
    /*
        Esta funcion retorna una cadena que representa las columnas de una tabla en codigo html. Estas columnascontienen informacion del break de los agentes.
    */
    function crearColumna($valor) {
        if( $valor!="" && !is_null($valor) ) {
            $cadena = "<td>$valor</td>";
            return $cadena;
        }else {
            $this->msgError("El campo es vacio o es null");
            return false;
        }
    }
    
    /*
        Esta funcion retorna una cadena que representa las filas de una tabla en codigo html. Estas filas
        contienen informacion del break de los agentes.
    */
    function crearFilas($valor) {
        if( $valor!="" && !is_null($valor) ) {
            $cadena = "<tr>$valor</tr>";
            return $cadena;
        }else {
            $this->msgError("No hay columnas para esta fila");
            return false;
        }
    }

    /**
     *  Esta funcion retorna un listado de los tipos de break en el sistema.
     *
     * @result  mixed   Arreglo de breaks en el formato [id, name] (posiblemente vacío)
     *                  en caso de éxito, o FALSE en caso de error
     */
    function getTiposBreak()
    {
        $SQLConsulta = "select id,name from break";
        $resConsulta = $this->pDB->fetchTable($SQLConsulta,true);
        $sResultado = "";

        if (!is_array($resConsulta)) {
            $this->msgError = "Error en la consulta";
            return false;
        } else {
            return $resConsulta;
        }
    }

    /**
     * Esta funcion retorna un listado de agentes en el sistema.
     *
     * @param   mixed   $limit      Número máximo de registros a devolver, o 
     *                              NULL si no hay límite. Si NULL, se ignora
     *                              el valor de $offset.
     * @param   mixed   $offset	    Offset de registro desde el cual empezar
     *                              a leer, o NULL. Si NULL, se devuelve desde
     *                              el primer registro.
     *
     * @result	mixed               Arreglo de registros en el formato
     *                              [id_de_agente, nombre_de_agente, numero_de_agente]
     *                              (el cual puede estar vacío) en éxito, o una
     *                              cadena en error.
     */
    function getAgents($limit=null,$offset=null)
    {
        // Validar parámetros de límite y offset
        if (!empty($limit) && !ereg('^[[:digit:]]+$', $limit)) $limit = NULL;
        if (!empty($offset) && empty($limit)) $offset = NULL;
        if (!empty($offset) && !ereg('^[[:digit:]]+$', $offset)) $offset = NULL;
		
        $limite = "";

        if( !empty($limit) ){
            $limite = "limit {$limit} ";
            if ( !empty($offset) ) {
                $limite .= "offset {$offset}";
            }
        }

        $sResultado="";
        $SQLConsulta = "select id,name,number from agent ".$limite;
        $resConsulta = $this->pDB->fetchTable($SQLConsulta,true);

        if(!$resConsulta || !is_array($resConsulta)) {
            $this->msgError = "Error en la consulta";
        } else {
            return $resConsulta;
        }

        if($sResultado=="") {
            $sResultado = "No hay datos que presentar";
        }
        return $sResultado;
    }

    /*
        Esta funcion retorna los tiempos que un agente ha tomado un break en una fecha indicada.
    */
    function getSumaTiempos($id_agent,$id_break,$fecha_init,$fecha_end) {
        $sResultado="";
       // $SQLConsulta = "select duration from audit where id_agent=".paloDB::DBCAMPO($id_agent)." and id_break=".paloDB::DBCAMPO($id_break)." and day(datetime_init) =".paloDB::DBCAMPO($day)." and month(datetime_init) =".paloDB::DBCAMPO($month)." and year(datetime_init) =".paloDB::DBCAMPO($year);

       $SQLConsulta = "select duration from audit where id_agent=".paloDB::DBCAMPO($id_agent)." and id_break=".paloDB::DBCAMPO($id_break)." and datetime_init between '$fecha_init' and  '$fecha_end'";

        //echo $SQLConsulta."<br>";
        $resConsulta = $this->pDB->fetchTable($SQLConsulta,true);

        if(!$resConsulta || !is_array($resConsulta)) {
                    $this->msgError = "Error en la consulta";
                }elseif(count($resConsulta)>0) {
                        $sResultado = $this->sumarTiempos($resConsulta);
                }else {
                    $this->msgError = "La consulta no produjo resultados";
                }

                if($sResultado=="") {
                    $sResultado = "00:00:00" ;
                }
                return $sResultado;
    }

    /*
        Esta funcion recibe un arreglo con los tiempos de break del mismo tipo y retorna
        el tiempo total que el agente ha estado en este break.
    */
    function sumarTiempos($arrTime) {

        if(count($arrTime)==1) {
            if( is_null($arrTime[0]['duration'] ) ) {
                return "00:00:00";
            }
            return $arrTime[0]['duration'];
        }elseif(count($arrTime)==2) {
            if( is_null($arrTime[0]['duration']) ) {
                $arrTime[0]['duration'] = "00:00:00";
            }
            if( is_null($arrTime[1]['duration']) ) {
                $arrTime[1]['duration'] = "00:00:00";
            }
            $SQLConsulta = "select addtime('".$arrTime[0]['duration']."','".$arrTime[1]['duration']."') duracion";
//echo $SQLConsulta."<br>";
            $resConsulta = $this->pDB->fetchTable($SQLConsulta,true);

            if(!$resConsulta)  {
                $this->msgError = $this->errMsg;
                return false;
            } else {
                 return $resConsulta[0]['duracion'];
            }
 
        }elseif(count($arrTime)>2) {
            if( is_null($arrTime[0]['duration']) ) {
                $arrTime[0]['duration'] = "00:00:00";
            }
            if( is_null($arrTime[1]['duration']) ) {
                $arrTime[1]['duration'] = "00:00:00";
            }
            $SQLConsulta = "select addtime('".$arrTime[0]['duration']."','".$arrTime[1]['duration']."') duracion";
            $resConsulta = $this->pDB->fetchTable($SQLConsulta,true);

            if(!$resConsulta)  {
                $this->msgError = $this->errMsg;
                return false;
            }else {
                $valorTime =$resConsulta[0]['duracion'];

                for($i =2 ;$i<count($arrTime) ; $i++) {
                    if( !is_null($arrTime[$i]['duration']) ) {
                        $SQLConsulta = "select addtime('".$valorTime."','".$arrTime[$i]['duration']."') duracion";
                        $resConsulta = $this->pDB->fetchTable($SQLConsulta,true);

                        if(!$resConsulta)  {
                            return false;
                        }else {
                            $valorTime =$resConsulta[0]['duracion'];
                        }
                    }
                }
                return $valorTime;
            }
        }
    }

    /**
     * Esta funcion retorna un arreglo con el reporte de break tomados por cada agente, dada una fecha.
     *
     * @param   string  $fecha_init     Fecha de inicio de rango, en formato 'yyyy-mm-dd hh:mm:ss'
     * @param   string  $fecha_end      Fecha de final de rango, en formato 'yyyy-mm-dd hh:mm:ss'
     *
     * @result  mixed   Arreglo de reporte de breaks de agentes, o FALSE en caso de error
     */
    function getReportesBreak($fecha_init,$fecha_end)
    {
        $sConvert="";
        $arrAgentes = $this->getAgents();
        $arrTiposBreak = $this->getTiposBreak();

        if (!is_array($arrAgentes) || !is_array($arrTiposBreak))
            return false;

        $result = array();
        foreach($arrAgentes as $agente) {
            foreach($arrTiposBreak as $break) {
                $tiempo = $this->getSumaTiempos($agente['id'],$break['id'],$fecha_init,$fecha_end);
                $result[$agente['id']][$break['id']] = $tiempo;
            }
        }
        return $result;
    }

    function generarCSVReporte($arrData,$module_name,$fecha) {
        $name_file = $fecha.".csv";
        $cadena="";
        foreach($arrData as $data) {
            $coma = "";
            foreach( $data as $valor) {
                $cadena .= $coma."'".$valor."'";
                $coma = ",";
            }
            $cadena .= "\n";
        }
        return( $this->crearArchivo($cadena,$module_name,$name_file) );
    }

    function crearArchivo($cadena,$module_name,$name_file) {
        $ruta_base = dirname($_SERVER['SCRIPT_FILENAME']);
        //$name_fileCSV = "$ruta_base/$module_name/reporte.txt";
        $name_fileCSV = "/var/www/html/modules/$module_name/reportes/$name_file";

        $gestorFile= fopen($name_fileCSV,"w");

        if($gestorFile){

            fputs($gestorFile,$cadena);
            fclose($gestorFile);
            return true;
        } else {
            return false;
        }

    }

}


?>
