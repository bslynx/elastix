<?php


class DataQueue
{
    var $pDB;
    var $lang;
    
    /*
        Constructor de la clase, recibe la conexion a la base
    */
    function DataQueue($pDB) {
        if(is_object($pDB) && !is_null($pDB)) {
            $this->pDB = $pDB;
        }else {
            return false;
        }
    }
    
    /*
        Esta funcion recibe un queue y s script y valida si debe ser ingresado en la tabal o no
    */
    function guardarQueue($queue,$script) {
       
        if($queue=="") return false;
        if(is_null($queue)) return false;

        $valido = $this->validarQueue($queue);
        if($valido) {
            if( !$this->buscarQueueCampaniaActiva($queue) ) {
                $this->registrarQueue($queue,$script);
            }
        }else {
            return "La cola no es valida : $queue ";
        }
        return true;
    }
    
    /*
        Esta funcion recibe un queue y su script y decide si lo inserta o si lo actualiza
    */
    function registrarQueue($queue,$script) {
        $SQLConsultaQueue = "select id,estatus from queue_call_entry where queue=".paloDB::DBCAMPO($queue);

        $resConsultaQueue = $this->pDB->fetchTable($SQLConsultaQueue,true);

        if( !is_array($resConsultaQueue) ) {
            return "Error en la sentencia SQL: $SQLConsulta";
        }elseif( count($resConsultaQueue) > 0 ) {
                return $this->actualizarQueue($queue,$script);
        }else {
            return $this->insertarQueue($queue,$script);
        }

    } 

    /*
        Esta funcion inserta un queue y su correspondiente script , devuelve true en 
        caso de exito y false si ha fallado
    */
    function insertarQueue($queue,$script) {

        $sPeticionSQL = paloDB::construirInsert(
            "queue_call_entry", array(
                "queue"          =>  paloDB::DBCAMPO($queue),
                "script"         =>  paloDB::DBCAMPO($script),
            )
        );

        $result = $this->pDB->genQuery($sPeticionSQL);
        
        if(!$result) {
            return false;
        }
        return true;
    }

    /*
        Esta funcion actualiza el script de un queue, devuelve true en caso de exito y false 
        si ha fallado
    */
    function actualizarQueue($queue,$script) {

        $activate = 'A';
        $sPeticionSQL = paloDB::construirUpdate(
            "queue_call_entry",array(
                "script"       =>  paloDB::DBCAMPO($script),
                "estatus"       =>  paloDB::DBCAMPO($activate) ),
                " queue=$queue "
        );

        $result = $this->pDB->genQuery($sPeticionSQL);
        
        if(!$result) {
            return false;
        }
        return true;
    }

    /*
        Esta funcion devuelve true si el queue es valido
    */
    function validarQueue($queue){

        if(is_null($queue)) return false;
        if($queue == "") return false;

        return true;
    }

    /*
        Esta funcion devuelve true si el queue esta en la tabla quer_call_entry con estatus='A'
    */
    function buscarQueue($queue) {
        $SQLConsultaQueue = "select id from queue_call_entry where estatus = 'A' and queue=".paloDB::DBCAMPO($queue);
        $resConsultaQueue = $this->pDB->fetchTable($SQLConsultaQueue,true);
        
        // si no hubo error en la consulta del queue
        if( !is_array($resConsultaQueue) ) {
            return "Error en la sentencia SQL: $SQLConsulta";
        }elseif( count($resConsultaQueue) > 0 ) {

            return true;
        }else {

            return false;
        }
    }
    
    /*
        Esta funcion devuelve true si el queue esta siendo utilizada en una campania
    */
    function buscarQueueCampaniaActiva($queue) {

        $SQLConsulta = "select id from campaign where estatus='A' and queue=$queue";
        $resConsulta = $this->pDB->fetchTable($SQLConsulta,true);

        if(!is_array($resConsulta)) {
            return "Error en la sentencia SQL: $SQLConsulta";
        }elseif( count($resConsulta) > 0 ) {

            return true;
        }else {

            return false;
        }
    }

    /*
        Esta funcion devuelve true si la cola esta siendo utilizada en una campania o si se encuentra en la tabla query_call_entry con estatus='A'
    */
    function esQueueUsado($queue) {

        if( !$this->buscarQueue($queue) && !$this->buscarQueueCampaniaActiva($queue) ) {
            return false;
        }

        return true;
    }

    /*
        Esta funcion devuelve una lista de queues
    */
    function getQueues($id=null,$estatus='all') {

        $where = "";
        if($estatus=='all') {
            $where .= " where 1";
        } elseif($estatus=='A') {
            $where .= " where estatus='A'";
        } elseif($estatus=='I') {
            $where .= " where estatus='I'";
        }

        if($id!=null) {
            $SQLConsulta = "select id,queue,estatus,script from queue_call_entry where id=".$id;
        }else {
            $SQLConsulta = "select id,queue,estatus,script from queue_call_entry".$where;
        }

        $resConsulta = $this->pDB->fetchTable($SQLConsulta,true);

        if(!is_array($resConsulta)) {
            return false;
        }else {
            return $resConsulta;
        }
    }
    
    /*
        Esta funcion recibe un queue y actualiza es estatus en la tabla query_call_entry
    */
    function activar_queue($id_queue,$estatus) {
        $sPeticionSQL = paloDB::construirUpdate(
            "queue_call_entry",array(
                "estatus"       =>  paloDB::DBCAMPO($estatus)),
                " id={$id_queue}"
        );
        $result = $this->pDB->genQuery($sPeticionSQL);

        if (!$result) {
            return false;
        } else {
            return true;
        }
    } 

    /*
        Esta funcion toma una matriz  y los convierte en una cadena que despues sera enviamda al tpl
        para formar un select
    */
    function crearSelect($arrOp) {

        $cadenaOp = "";

        if(!is_array($arrOp)) {
            return false;
        }elseif( count($arrOp)==0 ) {
                $cadenaOp .= "<option value='-1'>No hay colas disponibles</option>";
        }else {
            foreach($arrOp as $i)
                $cadenaOp .= "<option value='$i[0]' >$i[1]</option>";
        }
        return $cadenaOp;
    }

}

// FUNCIONES AJAX
/*
    Esta funcion modifica el estatus de una cola
*/
function desactivar_queue($id_queue)
{
    require "modules/ingoing_calls/configs/default.config.php";
    require "modules/ingoing_calls/lang/en.lang";

    $respuesta = new xajaxResponse();
    $pDB = new paloDB($cadena_dsn);

    if($pDB->errMsg != "") {
        $respuesta->addAssign("mb_message","innerHTML",$lang["Error when connecting to database"]."<br/>".$pDB->errMsg);
    }
    $oData = new DataQueue($pDB);
    if($oData->activar_queue($id_queue,'I')) {
        $respuesta->addScript("window.open('?menu=ingoing_calls','_parent')");
    }else {
        $respuesta->addAssign("mb_title","innerHTML",$lang["Desactivate Error"]."<br/>".$pDB->errMsg);
        $respuesta->addAssign("mb_message","innerHTML",$lang["Error when desactivating the Queue"]."<br/>".$pDB->errMsg);
    }
    return $respuesta;
}



?>
