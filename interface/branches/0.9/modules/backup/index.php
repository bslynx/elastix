<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 0.5                                                  |
  | http://www.elastix.org                                               |
  +----------------------------------------------------------------------+
  | Copyright (c) 2007 Palosanto Solutions S. A.                         |
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
  $Id: index.php,v 1.1 2007/08/10 01:32:52 gcarrillo Exp $ */

require_once "libs/paloSantoDB.class.php";

define("MYSQL_ROOT_PASSWORD","eLaStIx.2oo7");
function _moduleContent($smarty, $module_name)
{
    require_once "libs/misc.lib.php";
    require_once "libs/paloSantoForm.class.php";
    require_once "libs/paloSantoConfig.class.php";

    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    global $arrConf;
    global $arrLang;
    //folder path for custom templates

    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConfig['templates_dir']))?$arrConfig['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];
    $sContenido='';
    $strErrorMsg = '';
    $bSaveBackup=false;
    $arrBackupOptions=array(
                        "elastix_db"=>array("desc"=>$arrLang["Elastix Database"],"check"=>"","msg"=>""),
                        "sounds"=> array("desc"=>$arrLang["Sounds"],"check"=>"","msg"=>""),
                        "config_files"=> array("desc"=>$arrLang["Configuration Files"],"check"=>"","msg"=>""),
                        "fax"=> array("desc"=>$arrLang["Fax"],"check"=>"","msg"=>""),
                        "voicemail"=> array("desc"=>$arrLang["Voicemails"],"check"=>"","msg"=>""),
                        "monitors"=> array("desc"=>$arrLang["Monitors"],"check"=>"","msg"=>""),
                        "tftp"=> array("desc"=>$arrLang["tFTP"],"check"=>"","msg"=>""),
                        "email"=> array("desc"=>$arrLang["Email Acccounts"],"check"=>"","msg"=>""),
                        );

  //obtener la version de mysql
   // define('MYSQL_INT_VERSION', " 5.0.22");
  //

    $smarty->assign("title", $arrLang["Backup"]);

    $smarty->assign("PROCESS_BACKUP", $arrLang["Process"]);
    $smarty->assign("LBL_TODOS", $arrLang["All options"]);

    $arrSelectedOptions=array();
    $backup_all=false;
//print_r($_POST);
    if (isset($_POST["process"]))
    {
        #realizar el respaldo de lo que está seleccionado
        if (isset($_POST["backup_total"]))
        {
            $arrSelectedOptions=array_keys($arrBackupOptions);
            foreach ($arrBackupOptions as $key=>$arrOption) $arrBackupOptions[$key]["check"]="checked";
            $backup_all=true;
        }
        else
        {
            #verificar sobre cuales hacer respaldo
            foreach ($arrBackupOptions as $key=>$arrOption)
            {
                #verifica si ha seleccionado esa opcion
                if (isset($_POST[$key]))
                {
                    #le pongo checked
                    $arrBackupOptions[$key]["check"]="checked";
                    #lo agrego al arreglo
                    $arrSelectedOptions[]=$key;
                }
            }
        }

        #verifico que haya seleccionado al menos una opcion
        if (!count($arrSelectedOptions)>0)
        {
           #no ha seleccionado opcion
            $smarty->assign("ERROR_MSG", $arrLang["Choose an option to backup"]);
        }
        else
        {

            #crear la carpeta donde se va a copiar el respaldo que se realice
            $dir_respaldo = "backup";
            //$timestamp=time();
            $valor_unico = "-".date("YmdHis")."-".substr(session_id(), 0, 1).substr(session_id(), -1, 1);
            $carpeta_respaldo = "backup";
            $timestamp= $carpeta_respaldo.$valor_unico;
            $ruta_respaldo="$dir_respaldo/$timestamp";

            $ruta_respaldo_sin_valor_unico = "$dir_respaldo/$carpeta_respaldo";

            //asegurarme que ya no exista la carpeta
            //si ya existe BORRO contenido
            if (file_exists($ruta_respaldo_sin_valor_unico)){
            exec("rm -rf $ruta_respaldo_sin_valor_unico",$output,$retval);
            }
            mkdir($ruta_respaldo_sin_valor_unico); // ??

            #hacer el respaldo de las opciones seleccionadas
            #tengo que mostrar cuales de las opciones seleccionadas, se hizo el respaldo correctamente por eso envio $arrBackupOptions
            process_backup($arrSelectedOptions,$ruta_respaldo_sin_valor_unico,$arrBackupOptions);
            #en la carpeta backup ya deberia tener los respaldos
            #comprimo la carpeta
            #y la envio al navegador
            exec("tar -C $dir_respaldo -cvzf $dir_respaldo/elastix$timestamp.tgz $carpeta_respaldo ",$output,$retval);
            if ($retval<>0) //no se pudo generar el archivo comprimido
                $errMsg= $arrLang["Could not generate backup file"]." : $dir_respaldo/elastix$timestamp.tgz\n";
            else{
                #mensaje que se ha completado el backup
                $smarty->assign("ERROR_MSG", $arrLang["Backup Complete!"]." : $dir_respaldo/elastix$timestamp.tgz");
             /*   #lo envio al browser
                header("Cache-Control: private");
                header("Pragma: cache");
                header("Content-Type: application/octet-stream\n");
                header("Content-Disposition: attachment; filename=elastixBackup.tgz"); 
                header ("Content-Length: ".filesize("$dir_respaldo/elastixBackup.tgz"));
                print file_get_contents("$dir_respaldo/elastixBackup.tgz");
                $bSaveBackup=true;*/
             //   exit();
          #      print "Backup file location: $dir_respaldo/elastixBackup.tgz\n";
            }
            //borro la carpeta de backup
            exec("rm $ruta_respaldo_sin_valor_unico -rf");
         //   exec("rm $dir_respaldo/elastixBackup.tgz");
//            rmdir($ruta_respaldo_sin_valor_unico);
        }
    }
    $all_checked=$backup_all?"checked":"";
    $smarty->assign("all_checked", $all_checked);
    $smarty->assign("backup_options", $arrBackupOptions);
    if ($bSaveBackup) exit();
    $sContenido .= $smarty->fetch("$local_templates_dir/backup.tpl");
    return $sContenido;
}

function process_backup($arrSelectedOptions,$ruta_respaldo,&$arrBackupOptions)
{
    foreach ($arrSelectedOptions as $option)
    {
        $bExito=true;
        #hago case de option
        switch ($option){
        case "elastix_db":
            #voy a hacer mysqldump de cada una de las bases de datos
            respaldar_mysql_db($ruta_respaldo);
            $error="";
            $arrInfoRespaldo['folder_path']=$ruta_respaldo;
            $arrInfoRespaldo['folder_name']="mysqldb";
            $arrInfoRespaldo['nombre_archivo_respaldo']="mysqldb.tgz";
            $bExito=respaldar_carpeta($arrInfoRespaldo,$ruta_respaldo,$error);
            #voy a borrar la carpeta de respaldo mysqldb
            exec("rm $ruta_respaldo/mysqldb -rf");
            #para las bases de sqlite genero el schema y los insert
            #respaldar bases sqlite
            respaldar_sqlite_db($ruta_respaldo);
            $error="";
            $arrInfoRespaldo['folder_path']=$ruta_respaldo;
            $arrInfoRespaldo['folder_name']="db";
            $arrInfoRespaldo['nombre_archivo_respaldo']="var.www.db.tgz";
            $bExito=respaldar_carpeta($arrInfoRespaldo,$ruta_respaldo,$error);
            #voy a borrar la carpeta de respaldo db
            exec("rm $ruta_respaldo/db -rf");
        break;
        case "sounds":
            $error="";
            $arrInfoRespaldo['folder_path']="/var/lib/asterisk";
            $arrInfoRespaldo['folder_name']="sounds";
            $arrInfoRespaldo['nombre_archivo_respaldo']="var.lib.asterisk.sounds.tgz";
            $bExito=respaldar_carpeta($arrInfoRespaldo,$ruta_respaldo,$error);
        break;
        case "config_files":
            $error="";
            $arrInfoRespaldo['folder_path']="/etc";
            $arrInfoRespaldo['folder_name']="asterisk";
            $arrInfoRespaldo['nombre_archivo_respaldo']="etc.asterisk.tgz";
            $bExito=respaldar_carpeta($arrInfoRespaldo,$ruta_respaldo,$error);
        break;
        case "fax":
            #por ahora se respaldara la base de datos, fax.db
            #si no ha seleccionado respaldar base de datos respaldo el archivo
            $dir_resp_fax="$ruta_respaldo/fax";
            mkdir($dir_resp_fax);

                #copio el archivo fax.db
            respaldar_base_sqlite($dir_resp_fax,"/var/www/db","fax.db");
            $arrInfoRespaldo['folder_path']="$ruta_respaldo";
            $arrInfoRespaldo['folder_name']="fax";
            $arrInfoRespaldo['nombre_archivo_respaldo']="fax.tgz";
            $bExito=respaldar_carpeta($arrInfoRespaldo,$ruta_respaldo,$error);
            exec("rm $dir_resp_fax -rf");

        break;
        case "voicemail":
            $error="";
            $arrInfoRespaldo['folder_path']="/var/spool/asterisk";
            $arrInfoRespaldo['folder_name']="voicemail";
            $arrInfoRespaldo['nombre_archivo_respaldo']="var.spool.asterisk.voicemail.tgz";
            $bExito=respaldar_carpeta($arrInfoRespaldo,$ruta_respaldo,$error);
        break;
        case "monitors":
            $error="";
            $arrInfoRespaldo['folder_path']="/var/spool/asterisk";
            $arrInfoRespaldo['folder_name']="monitor";
            $arrInfoRespaldo['nombre_archivo_respaldo']="var.spool.asterisk.monitor.tgz";
            $bExito=respaldar_carpeta($arrInfoRespaldo,$ruta_respaldo,$error);
        break;
        case "tftp":
            $error="";
            $bExito=false;
            $arrInfoRespaldo['folder_path']="/";
            $arrInfoRespaldo['folder_name']="tftpboot";
            $arrInfoRespaldo['nombre_archivo_respaldo']="tftpboot.tgz";
            #tengo que cambiarle los permisos a la carpeta (con sudo) por que sino no voy a poder hacerle backup
            $comando="sudo -u root /bin/chown asterisk:asterisk /tftpboot -R";
            exec($comando,$output,$retval);
            if ($retval==0){
                $bExito=respaldar_carpeta($arrInfoRespaldo,$ruta_respaldo,$error);
                #cambio de nuevo a root
                $comando="sudo -u root /bin/chown root:root /tftpboot -R";
                exec($comando,$output,$retval);
            }
        break;
        case "email":
            #por ahora se respaldara la base de datos, email.db
            #si no ha seleccionado respaldar base de datos respaldo el archivo sqlite email.db y la base de mysql de roundcube (webmail)
            $dir_resp_email="$ruta_respaldo/email";
            mkdir($dir_resp_email);
            if (!in_array("elastix_db",$arrSelectedOptions))
            {
                //voy a crear una carpeta email para copiar los archivos a respaldar
                respaldar_base_sqlite($dir_resp_email,"/var/www/db","email.db");
                /*$comando="tar -C /var/www/db -cvzf $ruta_respaldo/email.tgz email.db ";
                exec($comando,$output,$retval);
                if ($retval<>0) $bExito=false;*/
                #hago dump de la base de mysql
                respaldar_base_mysql($dir_resp_email,"roundcubedb");
            }
            #respaldar los  mailboxes ruta /var/spool/imap
            #primero cambiar los permisos a la carpeta
            $comando="sudo -u root /bin/chown asterisk:asterisk /var/spool/imap -R";
            exec($comando,$output,$retval);
            if ($retval==0){
            $arrInfoRespaldo['folder_path']="/var/spool";
            $arrInfoRespaldo['folder_name']="imap";
            $arrInfoRespaldo['nombre_archivo_respaldo']="var.spool.imap.tgz";
            $bExito=respaldar_carpeta($arrInfoRespaldo,"$ruta_respaldo/email",$error);
            #cambio de nuevo a cyrus
            $comando="sudo -u root /bin/chown cyrus:mail /var/spool/imap -R";
            exec($comando,$output,$retval);
            }


            $arrInfoRespaldo['folder_path']="$ruta_respaldo";
            $arrInfoRespaldo['folder_name']="email";
            $arrInfoRespaldo['nombre_archivo_respaldo']="email.tgz";
            $bExito=respaldar_carpeta($arrInfoRespaldo,$ruta_respaldo,$error);
            exec("rm $dir_resp_email -rf");
        break;


        }
        if ($bExito) $msge="[ OK ]";
        else $msge="[ FAILED ]";
        $arrBackupOptions[$option]["msg"]=$msge;
    }
}

function respaldar_carpeta($arrInfoRespaldo,$ruta_respaldo,&$error)
{
    $bExito=true;
    $comando="tar -C ".$arrInfoRespaldo['folder_path'] .
             " -cvzf $ruta_respaldo/$arrInfoRespaldo[nombre_archivo_respaldo] ".
             $arrInfoRespaldo['folder_name'];
    exec($comando,$output,$retval);
    if ($retval<>0) $bExito=false;

    return $bExito;
}



function respaldar_sqlite_db($ruta_respaldo){
    $bExito=true;
    $arrArchivos = array();
    $ruta_db="/var/www/db";
   // $noBackTables=array('acl_group','acl_action','acl_group_permission',
    //                    'acl_membership','acl_resource','acl_user_permission');
    $noBackTables=array();

    //voy a crear una carpeta db
    mkdir("$ruta_respaldo/db");
    $dir_resp_db="$ruta_respaldo/db";
    //obtener listado de todas los archivos de ese directorio .db
    if (file_exists($ruta_db)){
        //existe el archivo, así es que leo el contenido
        $directorio=dir($ruta_db);
        while ($archivo = $directorio->read()) {
            if ($archivo!="." && $archivo!=".." && ereg("(.*)\.db$",$archivo)) $arrArchivos[] = $archivo;
        }
    }
    foreach ($arrArchivos as $archivoDB)
    {
        //por cada archivo db, voy a crear un archivo de texto que va a tener el script de la base, creación de tablas e inserts de datos
        respaldar_base_sqlite($dir_resp_db,$ruta_db,$archivoDB);
    }

    return $bExito;
}

function armar_queries_insert($pDB, $table_name, $schema)
{
//con el esquema obtener los campos para formar el query
    $descTabla=array();
    $campos = array();
    $arrQuerys=array();

    if (eregi("CREATE TABLE $table_name([[:space:]]\n*)\((.*)\)",$schema,$regs))
    {
        $arrCampos = split(",",$regs[2]);
        foreach ($arrCampos as $campo)
        {
            $descCampos = split(" ",trim($campo));
            //omitir el campo num_digits
            if (trim($descCampos[0])!="num_digits"){
                $arrCampo['Field']=trim($descCampos[0]);
                $campos[]=trim($descCampos[0]);
                $arrCampo['Type']=trim($descCampos[1]);
                $descTabla[]=$arrCampo;
            }
        }
    //    print_r($descTabla);
    }
    if (count($campos)>0){
        $strCampos=implode(",",$campos);
        $queryInsert = "INSERT INTO $table_name ($strCampos) VALUES ";
        $values='';
        //obtener los registros
        $sQuery="SELECT $strCampos FROM $table_name ";
        $result=$pDB->fetchTable($sQuery);
        if (is_array($result) && count($result)>0){
            foreach ($result as $columnas){
                //escapar los caracteres en los valores de la columna
                $columnValues='';
                foreach ($columnas as $columna){
                    $valor_columna=paloDB::DBCAMPO($columna);
                    $columnValues.=empty($columnValues)?$valor_columna:",$valor_columna";

                }
                $values="($columnValues)";
            //agrego el query a la lista
                $arrQuerys[]="$queryInsert $values";
            }
        }
      //  print_r($arrQuerys);
    }
    return $arrQuerys;
}

function respaldar_mysql_db($ruta_respaldo)
{
    //voy a crear una carpeta mysqldb
    mkdir("$ruta_respaldo/mysqldb");
    $dir_resp_db="$ruta_respaldo/mysqldb";

    $arrDatabasesMySQL=array(
                    "asterisk",
                    "asteriskcdrdb",
                    "asteriskrealtime",
                    "endpoints",
                    "mya2billing",
                    "mysql",
                    "roundcubedb",
                    "sugarcrm",
                    "vtigercrm503",
            );

            #TODO: asegurar si se va a exportar la estructura tambien
    foreach ($arrDatabasesMySQL as $base){
        #hago mysqldump a cada base de datos
        # -t :no-create-info
        #voy a omitir el esquema de las tablas
        $retorno=respaldar_base_mysql($dir_resp_db,$base);
        
        if ($retorno==0){#todo bien
        }
    }
}


function respaldar_base_mysql($dir_resp_db,$base)
{
    $respaldo ="";
    $bContinuar = FALSE;
    $host="localhost";
    $user="root";
    $pass=MYSQL_ROOT_PASSWORD;
    $dsn     = "mysql://$user:$pass@$host/$base";
    $db=new paloDB($dsn);
#mysqldump solo para la estructura
    system("mysqldump -h $host -u $user -p$pass  $base -t -c  > $dir_resp_db/{$base}2.sql",$retorno);


    if ($retorno==0) $bContinuar = TRUE;
    
    if ($bContinuar){
        $sQuery="SHOW TABLES";
        $tablas=$db->fetchTable($sQuery);

        $num_tables=count($tablas);
        $i=0;
        $error="";
        while ($i < $num_tables) {
            $table = $tablas[$i][0];
            $respaldo.= "--\n-- Delete Rows for Table $table\n--\nDELETE FROM `$table`;\n\n";
            $i++;
        }
        if (!empty($error)){   
            $bContinuar = FALSE;
            //$sContenido.=$tpl->crearAlerta("error","Error",$error);
        } else {
            $bContinuar=TRUE;            
        }     
    }

    if ($bContinuar){
        system("mysqldump -h $host -u $user -p$pass  $base --skip-add-drop-table --no-data  > $dir_resp_db/{$base}.sql",$retorno);
        if ($retorno==0){

            $estructura="";
            //no hubo inconvenientes, se guardo la estructura
            //se carga el contenido del archivo            
            $estructura=file_get_contents("$dir_resp_db/{$base}.sql");            
            $estructura=str_replace("CREATE TABLE","CREATE TABLE IF NOT EXISTS",$estructura);    
            
            //borrar el archivo

            if (strlen(trim($estructura))>0){
                $respaldo=$estructura.$respaldo;
                $bContinuar=TRUE;
            }
            else{
                //si no hay estructura no se puede continuar con los datos
                $bContinuar=FALSE;
            }

        }else{
            $bContinuar = FALSE;
        }
    }

    if ($bContinuar){
       // file_put_contents("$dir_resp_db/{$base}.sql", $respaldo, FILE_APPEND);
        $open = fopen ("$dir_resp_db/{$base}2.sql","a+");
        $openSQL = fopen ("$dir_resp_db/{$base}.sql","w+");
        rewind($open);rewind($openSQL);
        $tamanio_linea=4096;
        $escribir = fwrite ($openSQL,$respaldo."\n");
        while ($linea = fgets($open,$tamanio_linea))  // [0]
        {        
	       $escribir = fwrite ($openSQL,$linea);
        }
        fclose($open);        fclose($openSQL);
        unlink("$dir_resp_db/{$base}2.sql");
    }

    return $bContinuar?0:($retorno>0?1:$retorno);
}

function respaldar_base_sqlite($dir_resp_db,$ruta_db,$archivoDB,$noBackTables=array())
{
    //abrir conexion paloDB
    $pDB = new paloDB("sqlite3:///$ruta_db/".trim($archivoDB));
    if (!empty($pDB->errMsg)) {
        echo "DB ERROR: $pDB->errMsg \n";
    }else
    {
        //obtener el esquema de la base para obtener las tablas
        $sQuery="SELECT name, sql FROM sqlite_master ".
                "WHERE type='table' ".
                "ORDER BY name";
        $result=$pDB->fetchTable($sQuery);
        if (is_array($result) && count($result)>0){
            $pathSQLdb="$dir_resp_db/$archivoDB.sql";
            if (file_exists($pathSQLdb)) unlink($pathSQLdb);
            $archivos_db[]=$pathSQLdb;
            foreach ($result as $tableDesc){
                $table_name=trim($tableDesc[0]);
                $table_schema=trim($tableDesc[1]);
            // print "$table_name - $table_schema\n";
                //obtener los datos de esa tabla
                //para la base acl solo obtengo la tabla acl_user
                if (!in_array($table_name,$noBackTables)){
                    //escribir DELETE de la tabla
                    $queryDelete="DELETE FROM $table_name;\n";
                    $arrQueryInsert=armar_queries_insert($pDB, $table_name, $table_schema);
                }
                //ya tengo el listado de querys 
                //guardo en el archivo .sql
                //solo necesito los datos porque las tablas ya estan creadas en la nueva version
                if (count($arrQueryInsert>0)){
                    $strQuerys=implode(";\n",$arrQueryInsert);
                    $strQuerys.=(empty($strQuerys))?'':";\n";
                    //REEMPLAZAR CREATE TABLE POR CREATE TABLE IF NOT EXISTS
                    $table_schema=str_replace("CREATE TABLE","CREATE TABLE IF NOT EXISTS",$table_schema);
                    $qTable="$table_schema;\n";
                    $qTable.=$queryDelete;
                    $qTable.=$strQuerys;
                    if (!$handle = fopen($pathSQLdb, 'a')) {
                        echo "Cannot open file ($pathSQLdb)";
                        return false;
                    }
                    if (fwrite($handle, $qTable) === FALSE) {
                        echo "Cannot write to file ($pathSQLdb)";
                        return false;
                    }
                    fclose($handle);
                }
            }
        }

    }
}


?>