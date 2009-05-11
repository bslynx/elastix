<?php

//require_once "modules/client/configs/config.php";

     

class Cargar_File
{
    
    var $cFile;             // almacena el valor recibido en el constructor
    var $CADENA_MESSAGE = "";
    var $NUM_ERROR = "";
    /*
        Constructor de la clase, recibe el valor del arreglo global $_FILES['nombre_variable_file']
    */
    function Cargar_File($file) {

        if (!is_null($file))
            $this->cFile = $file;
        else
            $this->guardarMensaje("Error, el nombre del archivo no puede ser null");
    }

    /*
        Esta funccion abre un retorna el nombre del archivo seleccionado
    */    
    function getFileName() {
        if($this->cFile['error']==0 && $this->cFile['size']>0) {
            $nameFile = $this->cFile['tmp_name'];
            return $nameFile;
        } else {
            $this->guardarMensaje("Error al obtener el nombre del archivo : ".$this->cFile['error']);
        }
    }


    /*
        Esta funcion valida si los valores de los registros son validos
            se valida:
            si numero de cedula y telefono son numericos
            si los valores obtenidos del archivo no estan vacios
    */
    function validarValorCampos($nombre,$apellido,$telefono,$cedula) {
       
        if ( $nombre=="" || $apellido=="" || $telefono=="" || $cedula=="" ) {
            $this->NUM_ERROR++;
            return false;
        }
        
        if ( !is_numeric($telefono) || !is_numeric($cedula)) { 
            $this->NUM_ERROR++;
            return false;
        }

        return true;
    }

    /*
        Esta funcion crea o actualiza ujn registro en la base de datos
    */
    function procesarValorCampos($nombre,$apellido,$telefono,$cedula,$pDB,&$numActualizados,&$numInsertados) {

        $origen = 'file';
        $SQLConsultaCedulaInContact = 'select id,name from contact where cedula_ruc='.paloDB::DBCAMPO($cedula);
        $resConsultaCedulaInContact = $pDB->fetchTable($SQLConsultaCedulaInContact,true);

        // si no hubo error en la consulta del numero de cedula
        if( !is_array($resConsultaCedulaInContact) ) {
            $this->guardarMensaje("Error al consultar el numero de cedula $cedula ");
        }
        elseif( count($resConsultaCedulaInContact) > 0 ) {
            
            $sPeticionSQL = paloDB::construirUpdate(
                "contact", array(
                    "name"          =>  paloDB::DBCAMPO($nombre),
                    "apellido"      =>  paloDB::DBCAMPO($apellido),
                    "telefono"      =>  paloDB::DBCAMPO($telefono),
                    "origen"      =>  paloDB::DBCAMPO($origen),
                ),
                " cedula_ruc= $cedula "
            );

            $result = $pDB->genQuery($sPeticionSQL);

            if(!$result) {
                $this->guardarMensaje("Error al ingresar el registro " );
             }else {
                $numActualizados++;
             }
        }else {

            $sPeticionSQL = paloDB::construirInsert(
                "contact", array(
                    "name"          =>  paloDB::DBCAMPO($nombre),
                    "apellido"      =>  paloDB::DBCAMPO($apellido),
                    "telefono"      =>  paloDB::DBCAMPO($telefono),
                    "cedula_ruc"    =>  paloDB::DBCAMPO($cedula),
                    "origen"        =>  paloDB::DBCAMPO($origen),
                )
            );

            $result = $pDB->genQuery($sPeticionSQL);

            if(!$result) {
                $this->guardarMensaje("Error al ingresar el registro" );
            }else {
                $numInsertados++;
            }
        }
    }

    /*
        Esta funcion guarda los datos obtenidos de un archivo csv $resConsultaCedulaInContact = $pDB->getFirstRowQuery($SQLConsultaCedulaInContact,true);
    */
    function guardarDatosClientes($name_fileCSV,$module_name) {
       
        require_once "modules/$module_name/configs/config.php";
       
        $numActualizados    = 0;
        $numInsertados      = 0;
        $numErrores         = 0;
        
        $gestorFile = fopen($name_fileCSV,"r");
       
        if($gestorFile) {

            $pDB = new paloDB($arrConfig['cadena_dsn']);
            
            if (!is_object($pDB->conn) || $pDB->errMsg!="") {
                echo $pDB->errMsg;
            }else{
                $numRegistro = 0;
                $registrosValidos = false;
                while(!feof($gestorFile)  ) {
                    $numRegistro++;
                    $valorCampos = fgetcsv($gestorFile);

                    if (count($valorCampos)==4) {
                        $telefono   = $valorCampos[0];
                        $cedula     = $valorCampos[1];
                        $nombre     = $valorCampos[2];
                        $apellido   = $valorCampos[3];

                        $registrosValidos = $this->validarValorCampos($nombre,$apellido,$telefono,$cedula);

                        if($registrosValidos ) {
                                $this->procesarValorCampos($nombre,$apellido,$telefono,$cedula,$pDB,$numActualizados,$numInsertados);
                        } else {
                            $this->NUM_ERROR++;
                        }
                    }

                }

                $this->guardarMensaje("El numero de registros insertados es: $numInsertados");
                $this->guardarMensaje("El numero de registros actualizados es: $numActualizados");
            } 
            fclose($gestorFile);
        }
        
    }

   
    /*
        Esta funcion almacena los mensajes y errores producidos en el procedimiento de
        carga de datos del cliente
    */
    function guardarMensaje($cadena) {
        $this->CADENA_MESSAGE .= $cadena."<br>";
    }
    
    /*
        Esta funcion retorna los mensajes almacenados durante proceso de carga de datos del cliente
    */
    function getMsgResultado() {
        $this->getNumErrores();
        return $this->CADENA_MESSAGE;
    }

    function getNumErrores() {
         $this->guardarMensaje($this->NUM_ERROR);
    }

}
?>














