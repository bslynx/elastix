<?php
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
  $Id: index.php,v 1.1 2008/01/30 15:55:57 afigueroa Exp $ */

include_once "libs/paloSantoConfig.class.php";
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoFax.class.php";
include_once "libs/paloSantoEmail.class.php";

include_once "libs/cyradm.php";
include_once "configs/email.conf.php";

define("MYSQL_ROOT_PASSWORD","eLaStIx.2oo7");
function _moduleContent(&$smarty, $module_name)
{
//include elastix framework
    include_once "libs/paloSantoValidar.class.php";
    include_once "libs/misc.lib.php";
    include_once "libs/paloSantoForm.class.php";

    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    //include_once "modules/$module_name/libs/paloSantoConference.php";
    global $arrConf;
    global $arrLang;

    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConfig['templates_dir']))?$arrConfig['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    $dir_backup = $arrConf["dir_backup"];

    if (isset($_POST["delete_backup"])) $accion = "delete_backup";
    else if (isset($_POST["backup"])) $accion = "backup";
    else if (isset($_POST["submit_restore"])) $accion = "submit_restore";
    else if (isset($_POST["process"]) && $_POST["option_url"]=="backup")  $accion = "process_backup";
    else if (isset($_POST["process"]) && $_POST["option_url"]=="restore") $accion = "process_restore";
    else $accion ="report_backup_restore";
    $content = "";
    switch($accion)
    {
        case 'delete_backup':
            $content = delete_backup($smarty, $module_name, $local_templates_dir, $arrLang, $dir_backup);
            break;
        case 'backup':
            $content = backup_form($smarty, $local_templates_dir, $arrLang);
            break;
        case 'submit_restore':
            $content = restore_form($smarty, $local_templates_dir, $arrLang, $dir_backup);
            break;
        case 'process_backup':
            $content = process_backup($smarty, $local_templates_dir, $arrLang);
            break;
        case 'process_restore':
            $content = process_restore($smarty, $local_templates_dir, $arrLang, $dir_backup);
            break;
        default:
            $content = report_backup_restore($smarty, $module_name, $local_templates_dir, $arrLang, $dir_backup);
            break;
    }

    return $content;
}

function report_backup_restore($smarty, $module_name, $local_templates_dir, $arrLang, $dir_backup)
{
    $nombre_archivos = array();
    $nombre_archivos = Obtener_Backups($dir_backup, $smarty);

    //Paginacion
    $limit  = 15;
    $total  = count($nombre_archivos);

    $oGrid  = new paloSantoGrid($smarty);
    $offset = $oGrid->getOffSet($limit,$total,(isset($_GET['nav']))?$_GET['nav']:NULL,(isset($_GET['start']))?$_GET['start']:NULL);

    $end    = ($offset+$limit)<=$total ? $offset+$limit : $total;

    $url = "?menu=$module_name";
    $smarty->assign("url", $url);
    //Fin Paginacion

    $arrData = null;
    if(is_array($nombre_archivos) && $total>0){
        foreach($nombre_archivos as $key => $nombre_archivo){
            $arrTmp[0] = "<input type='checkbox' name='chk[".$nombre_archivo."]' id='chk[".$nombre_archivo."]'>";
            $arrTmp[1] = "<a href='backup/$nombre_archivo'>$nombre_archivo</a>";
            $fecha="";
            // se parsea el archivo para obtener la fecha
            if(ereg("[[:alnum:]]-([[:digit:]]{4})([[:digit:]]{2})([[:digit:]]{2})([[:digit:]]{2})([[:digit:]]{2})([[:digit:]]{2})-([[:alnum:]]{2}).[[:alnum:]]", $nombre_archivo, $data)) {
                $fecha = "$data[3]/$data[2]/$data[1] $data[4]:$data[5]:$data[6]";
                $id= "$data[1]$data[2]$data[3]$data[4]$data[5]$data[6]-$data[7]";
            }
            $arrTmp[2] = $fecha;
            $arrTmp[3] = "<input type='submit' name='submit_restore[".$nombre_archivo."]' value='{$arrLang['Restore']}' class='button'>";
            $arrData[] = $arrTmp;
        }
    }

    $arrGrid = array("title"    => $arrLang["Backup List"],
                        "icon"     => "images/list.png",
                        "width"    => "99%",
                        "start"    => ($total==0) ? 0 : $offset + 1,
                        "end"      => $end,
                        "total"    => $total,
                        "columns"  => array(0 => array("name"      => "<input type='submit' name='delete_backup' value='{$arrLang["Delete"]}' class='button' onclick=\" return confirmSubmit('{$arrLang["Are you sure you wish to delete backup (s)?"]}');\" />",
                                                    "property1" => ""),
                                            1 => array("name"      => $arrLang["Name Backup"],
                                                    "property1" => ""),
                                            2 => array("name"      => $arrLang["Date"],
                                                    "property1" => ""),
                                            3 => array("name"      => $arrLang["Action"],
                                                    "property1" => ""),
                                        )
                    );

    //Button Backup
    $htmlFilter = "<input class=\"button\" type=\"submit\" name=\"backup\" value=\"". $arrLang["Backup"]."\">";

    $oGrid->showFilter(trim($htmlFilter));
    $contenidoModulo = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$oGrid->fetchGrid($arrGrid, $arrData,$arrLang)."</form>";

    return $contenidoModulo;
}

function Obtener_Backups($dir_backup, $smarty)
{
    $nombre_archivos = array();
    // INICIO: se obtiene el listado de archivos de backup que hay en la carpeta backup
    // se carga en dir el directorio de backups
    $dir = dir($dir_backup);
    if (file_exists($dir_backup)) {
        // se obtienen todos los archivos del directorio si son archivos tgz
        while ($archivo = $dir->read()) {
            if ($archivo != "." && $archivo != ".." && eregi('\.(tar)$',$archivo))
                $nombre_archivos[] = $archivo;
        }
    } else {
        $smarty->assign("mb_message", $arrConf["Folder backup doesn't exist"]);
    }
    // se ordenan por fecha los archivos
    rsort($nombre_archivos);

    return $nombre_archivos;
}

function delete_backup($smarty, $module_name, $local_templates_dir, $arrLang, $dir_backup)
{
    // se obtiene en un arreglo el listado de archivos a borrar
    $archivos_borrar = isset($_POST["chk"])?$_POST["chk"]:array();
    if (count($archivos_borrar)>0){
        $error = false;
        if (is_array($archivos_borrar)) {
            //cambiar de usuario a la carpeta y a los archivos de backup para poder borrarlos
            //no dejo los permisos como estaban porque estos archivos deben pertenecer al usuario
            //asterisk, y si no fuera asi es incorrecto.
            $comando="sudo -u root /bin/chown asterisk:asterisk $dir_backup -R";
            exec($comando,$output,$retval);
            if ($retval==0) {
                // se hace un ciclo para borrar todos los archivos del respaldo checados
                foreach($archivos_borrar as $archivo=>$estatus) {
                    $ruta_archivo = $dir_backup."/".$archivo;
                    // se verifica que el archivo exista para evitar errores en el unlink
                    if (file_exists($ruta_archivo)) {
                        // unlink borra el archivo
                        if (!unlink($ruta_archivo)) {
                            $error = true;
                            break;
                        }
                    }
                }
            } else {
                $error = true;
            }
        }

        if ($error) {
            $smarty->assign("mb_message", $arrLang["Error when deleting backup file"]);
        }
    } else {
        $smarty->assign("mb_message", $arrLang["There are not backup file selected"]);
    }
    return report_backup_restore($smarty, $module_name, $local_templates_dir, $arrLang, $dir_backup);
}

function form_general($smarty, $local_templates_dir, $arrLang, $arrBackupOptions)
{
    $smarty->assign("PROCESS",$arrLang["Process"]);
    $smarty->assign("LBL_TODOS", $arrLang["All options"]);
    $smarty->assign("TODO_FAX", $arrLang["All options (Fax)"]);
    $smarty->assign("TODO_EMAIL", $arrLang["All options (Email)"]);
    $smarty->assign("TODO_ENDPOINT", $arrLang["All options (Endpoint)"]);
    $smarty->assign("TODO_ASTERISK", $arrLang["All options (Asterisk)"]);
    $smarty->assign("TODO_OTROS", $arrLang["All options (Others)"]);
    $smarty->assign("BACK", "<< ".$arrLang["Backup List"]);
    $smarty->assign("WARNING", $arrLang["This process could take several minutes"]);

    /*****************/
    $smarty->assign("FAX", $arrLang["Fax"]);
    $smarty->assign("EMAIL", $arrLang["Email"]);
    $smarty->assign("ENDPOINT", $arrLang["Endpoint"]);
    $smarty->assign("ASTERISK", $arrLang["Asterisk"]);
    $smarty->assign("OTROS", $arrLang["Others"]);
    /*****************/

    $smarty->assign("backup_fax", $arrBackupOptions['fax']);
    $smarty->assign("backup_email", $arrBackupOptions['email']);
    $smarty->assign("backup_endpoint", $arrBackupOptions['endpoint']);
    $smarty->assign("backup_asterisk", $arrBackupOptions['asterisk']);
    $smarty->assign("backup_otros", $arrBackupOptions['otros']);

    return $smarty->fetch("$local_templates_dir/backup.tpl");
}

function backup_form($smarty, $local_templates_dir, $arrLang)
{
    $arrBackupOptions = Array_Options($arrLang, "");

    $smarty->assign("title", $arrLang["Backup"]);
    $smarty->assign("OPTION_URL", "backup");

    return form_general($smarty, $local_templates_dir, $arrLang, $arrBackupOptions);
}

function restore_form($smarty, $local_templates_dir, $arrLang, $path_backup)
{
    $arrBackupOptions = Array_Options($arrLang, "disabled='disabled'");

    if(isset($_POST["submit_restore"]))
    {
        $arr = array_keys($_POST["submit_restore"]);
        $archivo_post = $arr[0];
    }else $archivo_post = isset($_POST["backup_file"])?$_POST["backup_file"]:"";

    $dir_respaldo = "/var/www/html/$path_backup";
    $comando="tar xvf $dir_respaldo/$archivo_post backup/a_options.xml";
    exec($comando,$output,$retval);
    if ($retval==0)
    {
        $xmlDoc = new DOMDocument();
        $xmlDoc->load("$dir_respaldo/a_options.xml");

        //copio el archivo en memoria
        $root = $xmlDoc->documentElement;//apunto a el tag raiz

        $optionsList = $root->getElementsByTagName("options");
        foreach($optionsList as $optionGeneral) {
            $attributeID = $optionGeneral->getAttribute("id");
            $option = $optionGeneral->getElementsByTagName("option");
            foreach($option as $value) {
                $arrBackupOptions[$attributeID][$value->nodeValue]["disable"] = "";
            }
        }
    }

    //$_ruta_archivo_ = $archivo_post;
    $smarty->assign("BACKUP_FILE", $archivo_post);
    $smarty->assign("title", $arrLang["Restore"]. ": $archivo_post");
    $smarty->assign("OPTION_URL", "restore");

    return form_general($smarty, $local_templates_dir, $arrLang, $arrBackupOptions);
}

function process_backup($smarty, $local_templates_dir, $arrLang)
{
    $arrBackupOptions = Array_Options($arrLang);
    $arrSelectedOptions=array();

    $xml_backup = "<raiz>\n";
    foreach ($arrBackupOptions as $key_general=>$arrOptionGeneral)
    {
        $xml_backup .= "\t<options id=\"$key_general\">\n";
        foreach($arrOptionGeneral as $key=>$arrOption)
        {
            //verifica si ha seleccionado esa opcion
            if (isset($_POST[$key]))
            {
                //le pongo checked
                $arrBackupOptions[$key]["check"]="checked";
                $xml_backup .= "\t\t<option>$key</option>\n";
                //lo agrego al arreglo
                $arrSelectedOptions[]=$key;
            }
        }
        $xml_backup .= "\t</options>\n";
    }
    $xml_backup .= "</raiz>";

    //verifico que haya seleccionado al menos una opcion
    if (!count($arrSelectedOptions)>0)
    {
        //no ha seleccionado opcion
        $smarty->assign("ERROR_MSG", $arrLang["Choose an option to backup"]);
    }
    else
    {
        //crear la carpeta donde se va a copiar el respaldo que se realice
        $dir_respaldo = "backup";
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

        //Guardar xml para saber que opciones fueron respaldadas y obviar las que no en el momento de hacer el restore
        $gestor = fopen($ruta_respaldo_sin_valor_unico."/a_options.xml", "w");
        fwrite($gestor, $xml_backup);
        fclose($gestor);

        //hacer el respaldo de las opciones seleccionadas
        //tengo que mostrar cuales de las opciones seleccionadas, se hizo el respaldo correctamente por eso envio $arrBackupOptions
        process_each_backup($arrSelectedOptions,$ruta_respaldo_sin_valor_unico,$arrBackupOptions);
        //en la carpeta backup ya deberia tener los respaldos
        //comprimo la carpeta
        //y la envio al navegador
        exec("tar -C $dir_respaldo -cvf $dir_respaldo/elastix$timestamp.tar $carpeta_respaldo ",$output,$retval);
        if ($retval<>0) //no se pudo generar el archivo comprimido
            $errMsg= $arrLang["Could not generate backup file"]." : $dir_respaldo/elastix$timestamp.tar\n";
        else{
            //mensaje que se ha completado el backup
            $smarty->assign("ERROR_MSG", $arrLang["Backup Complete!"]." : $dir_respaldo/elastix$timestamp.tar");
            #print "Backup file location: $dir_respaldo/elastixBackup.tgz\n";
        }
        //borro la carpeta de backup
        exec("rm $ruta_respaldo_sin_valor_unico -rf");
    }

    return backup_form($smarty, $local_templates_dir, $arrLang);
}

function process_restore($smarty, $local_templates_dir, $arrLang, $path_backup)
{
    $arrRestoreOptions = Array_Options($arrLang);
    $arrSelectedOptions=array();

    foreach ($arrRestoreOptions as $key_general=>$arrOptionGeneral)
    {
        foreach($arrOptionGeneral as $key=>$arrOption)
        {
            //verifica si ha seleccionado esa opcion
            if (isset($_POST[$key]))
            {
                //le pongo checked
                $arrRestoreOptions[$key]["check"]="checked";
                //lo agrego al arreglo
                $arrSelectedOptions[]=$key;
            }
        }
    }

    //verifico que haya seleccionado al menos una opcion
    if (!count($arrSelectedOptions)>0)
    {
        //no ha seleccionado opcion
        $smarty->assign("ERROR_MSG", $arrLang["Choose an option to restore"]);
    }
    else
    {
        //verificar que existe el archivo de respaldo
        $backup_file=$_POST['backup_file'];
        if (empty($backup_file))
            $smarty->assign("ERROR_MSG", $arrLang["Backup file path can't be empty"]);
        else
        {
            $path_file_backup = "/var/www/html/$path_backup/$backup_file";
            //verificar que el archivo existe
            if (!file_exists($path_file_backup))
            {
                $smarty->assign("ERROR_MSG", $arrLang["File doesn't exist"]);
            }else
            {
                //crear la carpeta donde se va a descomprimir el archivo de respaldo
                $dir_respaldo = "/var/www/html/$path_backup";
                $timestamp=time();
                $ruta_restaurar="/var/www/html/$path_backup/restore";
                $ruta_respaldo="$ruta_restaurar/backup";
                if (!file_exists($ruta_restaurar)) mkdir($ruta_restaurar);
                //descomprimir el archivo
                $comando="tar -C $ruta_restaurar -pxvf $path_file_backup ";
                exec($comando,$output,$retval);
                if ($retval==0)
                {
                    //se descomprimio
                    //al descomprimir se debe crear una carpeta con nombre backup
                    //hacer el restore de lo elegido
                    process_each_restore($arrSelectedOptions,$ruta_respaldo,$ruta_restaurar,$arrRestoreOptions);
                    //borro la carpeta de restore
                    exec("rm $ruta_restaurar -rf");
                    $smarty->assign("ERROR_MSG", $arrLang["Restore Complete!"]);
                }
            }
        }
    }
    return restore_form($smarty, $local_templates_dir, $arrLang, $path_backup);
}
function Array_Options($arrLang, $disabled="")
{
    $arrBackupOptions = array(
            "asterisk"      =>  array(
                                    "as_db"             =>  array("desc"=>$arrLang["Database"],"check"=>"","msg"=>"","disable"=>"$disabled"),
                                    "as_config_files"   =>  array("desc"=>$arrLang["Configuration Files"],"check"=>"","msg"=>"","disable"=>"$disabled"),
                                    "as_monitor"        =>  array("desc"=>$arrLang["Monitors"],"check"=>"","msg"=>"","disable"=>"$disabled"),
                                    "as_voicemail"      =>  array("desc"=>$arrLang["Voicemails"],"check"=>"","msg"=>"","disable"=>"$disabled"),
                                    "as_sounds"         =>  array("desc"=>$arrLang["Sounds"],"check"=>"","msg"=>"","disable"=>"$disabled"),
                                    "as_zaptel"         =>  array("desc"=>$arrLang["Zaptel Configuration"],"check"=>"","msg"=>"","disable"=>"$disabled"),
                                ),
            "fax"           =>  array(
                                    "fx_db"             =>  array("desc"=>$arrLang["Database"],"check"=>"","msg"=>"","disable"=>"$disabled"),
                                    "fx_pdf"            =>  array("desc"=>$arrLang["PDF"],"check"=>"","msg"=>"","disable"=>"$disabled"),
                                ),
            "email"         =>  array(
                                    "em_db"             =>  array("desc"=>$arrLang["Database"],"check"=>"","msg"=>"","disable"=>"$disabled"),
                                    "em_config_files"   =>  array("desc"=>$arrLang["Configuration Files"],"check"=>"","msg"=>"","disable"=>"$disabled"),
                                ),
            "endpoint"      =>  array(
                                    "ep_db"             =>  array("desc"=>$arrLang["Database"],"check"=>"","msg"=>"","disable"=>"$disabled"),
                                    "ep_config_files"   =>  array("desc"=>$arrLang["Configuration Files"],"check"=>"","msg"=>"","disable"=>"$disabled"),
                                ),
            "otros"         =>  array(
                                    "sugar_db"          =>  array("desc"=>$arrLang["SugarCRM Database"],"check"=>"","msg"=>"","disable"=>"$disabled"),
                                    "vtiger_db"         =>  array("desc"=>$arrLang["VtigerCRM Database"],"check"=>"","msg"=>"","disable"=>"$disabled"),
                                    "a2billing_db"      =>  array("desc"=>$arrLang["A2billing Database"],"check"=>"","msg"=>"","disable"=>"$disabled"),
                                    "mysql_db"          =>  array("desc"=>$arrLang["Mysql Database"],"check"=>"","msg"=>"","disable"=>"$disabled"),
                                    "menus_permissions" =>  array("desc"=>$arrLang["Menus and Permissions"],"check"=>"","msg"=>"","disable"=>"$disabled"),
                                ),
    );
    return $arrBackupOptions;
}

/* ------------------------------------------------------------------------------- */
/* FUNCIONS PARA EL BACKUP*/
/* ------------------------------------------------------------------------------- */

function process_each_backup($arrSelectedOptions,$ruta_respaldo,&$arrBackupOptions)
{
    foreach ($arrSelectedOptions as $option)
    {
        $bExito=true;
        $error="";
        switch ($option){
        case "as_db":
            $dir_resp_db="$ruta_respaldo/mysqldb_asterisk";
            mkdir($dir_resp_db);

            //Hacer mysqldump de cada base de asterisk
            if(respaldar_base_mysql($dir_resp_db, "asterisk")!=0)
                $bExito = false;
            if(respaldar_base_mysql($dir_resp_db, "asteriskcdrdb")!=0)
                $bExito = false;
            if(respaldar_base_mysql($dir_resp_db, "asteriskrealtime")!=0)
                $bExito = false;

            //Respaldar carpeta con las bases
            $arrInfoRespaldo = array(   'folder_path'               =>  $ruta_respaldo,
                                        'folder_name'               =>  "mysqldb_asterisk",
                                        'nombre_archivo_respaldo'   =>  "mysqldb_asterisk.tgz"
                                );
            if(!respaldar_carpeta($arrInfoRespaldo,$ruta_respaldo,$error))
                $bExito = false;
            //borrar la carpeta de respaldo mysqldb
            exec("rm $ruta_respaldo/mysqldb_asterisk -rf");
            break;

        case "as_config_files":
            $arrInfoRespaldo = array(   'folder_path'               =>  "/etc",
                                        'folder_name'               =>  "asterisk",
                                        'nombre_archivo_respaldo'   =>  "etc.asterisk.tgz"
                                );
            if(!respaldar_carpeta($arrInfoRespaldo,$ruta_respaldo,$error))
                $bExito = false;
            break;

        case "as_monitor":
            $arrInfoRespaldo = array(   'folder_path'               =>  "/var/spool/asterisk",
                                        'folder_name'               =>  "monitor",
                                        'nombre_archivo_respaldo'   =>  "var.spool.asterisk.monitor.tgz"
                                );
            if(!respaldar_carpeta($arrInfoRespaldo,$ruta_respaldo,$error))
                $bExito = false;
            break;

        case "as_voicemail":
            $arrInfoRespaldo = array(   'folder_path'               =>  "/var/spool/asterisk",
                                        'folder_name'               =>  "voicemail",
                                        'nombre_archivo_respaldo'   =>  "var.spool.asterisk.voicemail.tgz"
                                );
            if(!respaldar_carpeta($arrInfoRespaldo,$ruta_respaldo,$error))
                $bExito = false;
            break;

        case "as_sounds":
            $arrInfoRespaldo = array(   'folder_path'               =>  "/var/lib/asterisk/sounds",
                                        'folder_name'               =>  "custom",
                                        'nombre_archivo_respaldo'   =>  "var.lib.asterisk.sounds.custom.tgz"
                                );
            if(!respaldar_carpeta($arrInfoRespaldo,$ruta_respaldo,$error))
                $bExito = false;
            break;

        case "as_zaptel":
            exec("cp /etc/zaptel.conf $ruta_respaldo", $output, $retval);
            if ($retval!=0) $bExito = false;
            break;

        case "fx_db":
            exec("cp /var/www/db/fax.db $ruta_respaldo", $output, $retval);
            if ($retval!=0) $bExito = false;
            break;

        case "fx_pdf":
            $arrInfoRespaldo = array(   'folder_path'               =>  "/var/www/html",
                                        'folder_name'               =>  "faxes",
                                        'nombre_archivo_respaldo'   =>  "var.www.html.faxes.tgz"
                                );
            if(!respaldar_carpeta($arrInfoRespaldo,$ruta_respaldo,$error))
                $bExito = false;
            break;

        case "em_db":
            if(respaldar_base_mysql($ruta_respaldo, "roundcubedb")!=0)
                $bExito = false;

            if (file_exists("$ruta_respaldo/roundcubedb.sql"))
            {
                $comando="tar -C $ruta_respaldo -cvzf $ruta_respaldo/roundcubedb_mysql.tgz roundcubedb.sql";
                exec($comando,$output,$retval);
                if ($retval!=0) $bExito = false;

                $comando="rm -f $ruta_respaldo/roundcubedb.sql";
                exec($comando,$output,$retval);
            }else if (file_exists("$ruta_respaldo/roundcubedb2.sql"))
            {
                //Si existe este archivo es porq la base esta vacia o no existe
                $comando="rm -f $ruta_respaldo/roundcubedb2.sql";
                exec($comando,$output,$retval);
            }else $bExito = false;

            $comando="cp /var/www/db/email.db $ruta_respaldo";
            exec($comando,$output,$retval);
            if ($retval!=0) $bExito = false;
            break;

        case "em_config_files":
            //respaldar los  mailboxes ruta /var/spool/imap
            //primero cambiar los permisos a la carpeta
            $comando="sudo -u root /bin/chown asterisk:asterisk /var/spool/imap -R";
            exec($comando,$output,$retval);
            if ($retval!=0) $bExito = false;
            else{
                $arrInfoRespaldo = array(   'folder_path'               =>  "/var/spool",
                                            'folder_name'               =>  "imap",
                                            'nombre_archivo_respaldo'   =>  "var.spool.imap.tgz"
                                );
                if(!respaldar_carpeta($arrInfoRespaldo,$ruta_respaldo,$error))
                    $bExito = false;
                //cambio de nuevo a cyrus
                $comando="sudo -u root /bin/chown cyrus:mail /var/spool/imap -R";
                exec($comando,$output,$retval);
            }
            break;

        case "ep_db":
            exec("cp /var/www/db/endpoint.db $ruta_respaldo", $output, $retval);
            if ($retval!=0) $bExito = false;
            break;

        case "ep_config_files":
            $arrInfoRespaldo = array(   'folder_path'               =>  "/",
                                        'folder_name'               =>  "tftpboot",
                                        'nombre_archivo_respaldo'   =>  "tftpboot.tgz"
                                );
            //Cambiar permisos a la carpeta (con sudo), sino no se puede hacer backup
            $comando="sudo -u root /bin/chown asterisk:asterisk /tftpboot -R";
            exec($comando,$output,$retval);
            if ($retval!=0) $bExito = false;
            else{
                if(!respaldar_carpeta($arrInfoRespaldo,$ruta_respaldo,$error))
                    $bExito = false;
                //cambio de nuevo a root
                $comando="sudo -u root /bin/chown root:root /tftpboot -R";
                exec($comando,$output,$retval);
            }
            break;

        case "sugar_db":
            if(respaldar_base_mysql($ruta_respaldo, "sugarcrm")!=0)
                $bExito = false;

            if (file_exists("$ruta_respaldo/sugarcrm.sql"))
            {
                $comando="tar -C $ruta_respaldo -cvzf $ruta_respaldo/sugarcrm_mysql.tgz sugarcrm.sql";
                exec($comando,$output,$retval);
                if ($retval!=0) $bExito = false;

                $comando="rm -f $ruta_respaldo/sugarcrm.sql";
                exec($comando,$output,$retval);
            }else if (file_exists("$ruta_respaldo/sugarcrm2.sql"))
            {
                //Si existe este archivo es porq la base esta vacia o no existe
                $comando="rm -f $ruta_respaldo/sugarcrm2.sql";
                exec($comando,$output,$retval);
            }else $bExito = false;
            break;

        case "vtiger_db":
            if(respaldar_base_mysql($ruta_respaldo, "vtigercrm503")!=0)
                $bExito = false;

            if (file_exists("$ruta_respaldo/vtigercrm503.sql"))
            {
                $comando="tar -C $ruta_respaldo -cvzf $ruta_respaldo/vtigercrm503_mysql.tgz vtigercrm503.sql";
                exec($comando,$output,$retval);
                if ($retval!=0) $bExito = false;

                $comando="rm -f $ruta_respaldo/vtigercrm503.sql";
                exec($comando,$output,$retval);
            }else if (file_exists("$ruta_respaldo/vtigercrm5032.sql"))
            {
                //Si existe este archivo es porq la base esta vacia o no existe
                $comando="rm -f $ruta_respaldo/vtigercrm5032.sql";
                exec($comando,$output,$retval);
            }else $bExito = false;
            break;

        case "a2billing_db":
            if(respaldar_base_mysql($ruta_respaldo, "mya2billing")!=0)
                $bExito = false;

            if (file_exists("$ruta_respaldo/mya2billing.sql"))
            {
                $comando="tar -C $ruta_respaldo -cvzf $ruta_respaldo/mya2billing_mysql.tgz mya2billing.sql";
                exec($comando,$output,$retval);
                if ($retval!=0) $bExito = false;

                $comando="rm -f $ruta_respaldo/mya2billing.sql";
                exec($comando,$output,$retval);
            }else if (file_exists("$ruta_respaldo/mya2billing2.sql"))
            {
                //Si existe este archivo es porq la base esta vacia o no existe
                $comando="rm -f $ruta_respaldo/mya2billing2.sql";
                exec($comando,$output,$retval);
            }else $bExito = false;
            break;

        case "mysql_db":
            if(respaldar_base_mysql($ruta_respaldo, "mysql")!=0)
                $bExito = false;

            if (file_exists("$ruta_respaldo/mysql.sql"))
            {
                $comando="tar -C $ruta_respaldo -cvzf $ruta_respaldo/mysql_mysql.tgz mysql.sql";
                exec($comando,$output,$retval);
                if ($retval!=0) $bExito = false;

                $comando="rm -f $ruta_respaldo/mysql.sql";
                exec($comando,$output,$retval);
            }else $bExito = false;
            break;

        case "menus_permissions":
            exec("cp /var/www/db/menu.db $ruta_respaldo", $output, $retval);
            if ($retval!=0) $bExito = false;
            exec("cp /var/www/db/acl.db $ruta_respaldo", $output, $retval);
            if ($retval!=0) $bExito = false;
            break;
        }
        if ($bExito) $msge="[ OK ]";
        else $msge="[ FAILED ]";
        $arrBackupOptions[][$option]["msg"]=$msge;
    }
}

function respaldar_carpeta($arrInfoRespaldo,$ruta_respaldo,&$error)
{
    $bExito=true;
    $comando="tar -C ".$arrInfoRespaldo['folder_path'] .
             " -cvzf $ruta_respaldo/{$arrInfoRespaldo['nombre_archivo_respaldo']} ".
             $arrInfoRespaldo['folder_name'];
    exec($comando,$output,$retval);
    if ($retval<>0) $bExito=false;

    return $bExito;
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
    //mysqldump solo para la estructura
    system("mysqldump -h $host -u $user -p$pass  $base -t -c > $dir_resp_db/{$base}2.sql",$retorno);

    if ($retorno==0) $bContinuar = TRUE;
/*
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
*/
    if ($bContinuar){
        system("mysqldump -h $host -u $user -p$pass  $base --no-data  > $dir_resp_db/{$base}.sql",$retorno);
        //system("mysqldump -h $host -u $user -p$pass  $base --skip-add-drop-table --no-data  > $dir_resp_db/{$base}.sql",$retorno);
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




/* ------------------------------------------------------------------------------- */
/* FUNCIONS PARA EL RESTORE*/
/* ------------------------------------------------------------------------------- */
function process_each_restore($arrSelectedOptions,$ruta_respaldo,$ruta_restaurar,&$arrRestoreOptions)
{
    $error="";
    foreach ($arrSelectedOptions as $option)
    {
        $bExito=true;
        switch ($option){
        case "as_db":
            $ruta_respaldo_db = $ruta_restaurar."/mysqldb_asterisk";
            if (file_exists("$ruta_respaldo/mysqldb_asterisk.tgz"))
            {
                $comando="tar -C $ruta_restaurar -pxvzf $ruta_respaldo/mysqldb_asterisk.tgz";
                exec($comando,$output,$retval);
                if ($retval!=0) $bExito = false;
                else{
                    $directorio=dir($ruta_respaldo_db);
                    $arrArchivos = array();
                    while ($archivo = $directorio->read()) {
                        if ($archivo!="." && $archivo!=".." && ereg("(.*)\.sql$",$archivo,$regs))
                        {
                            $base = $regs[1];
                            $fileSQL = $archivo;
                            $comando="mysql --password=".MYSQL_ROOT_PASSWORD." --user=root $base < $ruta_respaldo_db/$fileSQL";
                            exec($comando,$output,$retval);
                            if ($retval!=0) $bExito = false;
                        }
                    }
                    //Recargar FOP
                    include_once "modules/extensions_batch/libs/paloSantoExtensionsBatch.class.php";
                    include_once "libs/paloSantoConfig.class.php";
                    $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
                    $arrAMP  = $pConfig->leer_configuracion(false);

                    $dsnAsterisk = $arrAMP['AMPDBENGINE']['valor'].":/".
                                $arrAMP['AMPDBUSER']['valor']. ":".
                                $arrAMP['AMPDBPASS']['valor']. "@".
                                $arrAMP['AMPDBHOST']['valor']. "/asterisk";
                    $pDB = new paloDB($dsnAsterisk);

                    $pConfig = new paloConfig($arrAMP['ASTETCDIR']['valor'], "asterisk.conf", "=", "[[:space:]]*=[[:space:]]*");
                    $arrAST  = $pConfig->leer_configuracion(false);

                    $pEX = new paloSantoLoadExtension($pDB);
                    $data_connection = array('host' => "127.0.0.1", 'user' => "admin", 'password' => "elastix456");
                    $pEX->do_reloadAll($data_connection, $arrAST, $arrAMP);
                }
            }
            break;

        case "as_config_files":
            //Respaldo carpeta /etc/asterisk en un tgz
            $comando="sudo -u root touch /etc/asterisk.tgz";
            exec($comando, $output, $retval);

            $comando="sudo -u root chmod 777 /etc/asterisk.tgz";
            exec($comando, $output, $retval);

            $comando="tar cvfz /etc/asterisk.tgz /etc/asterisk/";
            exec($comando, $output, $retval);
            if ($retval!=0) $bExito = false;
            else{
                $comando="rm -rf /etc/asterisk/*";
                exec($comando, $output, $retval);

                $arrInfoRestaurar = array(  'folder_path'               =>  "/etc",
                                            'folder_name'               =>  "asterisk",
                                            'nombre_archivo_respaldo'   =>  "etc.asterisk.tgz"
                                    );
                if(!restaurar_carpeta($arrInfoRestaurar,$ruta_respaldo,$error))
                    $bExito = false;
            }
            break;

        case "as_monitor":
            //Respaldo carpeta /var/spool/asterisk/monitor en un tgz
            $comando="tar cvfz /var/spool/asterisk/monitor.tgz /var/spool/asterisk/monitor/";
            exec($comando, $output, $retval);
            if ($retval!=0) $bExito = false;
            else{
                $comando="rm -rf /var/spool/asterisk/monitor/*";
                exec($comando, $output, $retval);

                $arrInfoRestaurar = array(  'folder_path'               =>  "/var/spool/asterisk",
                                            'folder_name'               =>  "monitor",
                                            'nombre_archivo_respaldo'   =>  "var.spool.asterisk.monitor.tgz"
                                    );
                if(!restaurar_carpeta($arrInfoRestaurar,$ruta_respaldo,$error))
                    $bExito = false;
            }
            break;

        case "as_voicemail":
            //Respaldo carpeta /var/spool/asterisk/voicemail en un tgz
            $comando="tar cvfz /var/spool/asterisk/voicemail.tgz /var/spool/asterisk/voicemail/";
            exec($comando, $output, $retval);
            if ($retval!=0) $bExito = false;
            else{
                $comando="rm -rf /var/spool/asterisk/voicemail/*";
                exec($comando, $output, $retval);

                $arrInfoRestaurar = array(  'folder_path'               =>  "/var/spool/asterisk",
                                            'folder_name'               =>  "voicemail",
                                            'nombre_archivo_respaldo'   =>  "var.spool.asterisk.voicemail.tgz"
                                    );
                if(!restaurar_carpeta($arrInfoRestaurar,$ruta_respaldo,$error))
                    $bExito = false;
            }
            break;

        case "as_sounds":
            //Respaldo carpeta /var/spool/asterisk/sounds en un tgz
            $comando="tar cvfz /var/lib/asterisk/sounds/custom.tgz /var/lib/asterisk/sounds/custom/";
            exec($comando, $output, $retval);
            if ($retval!=0) $bExito = false;
            else{
                $comando="rm -rf /var/lib/asterisk/sounds/custom/*";
                exec($comando, $output, $retval);

                $arrInfoRestaurar = array(  'folder_path'               =>  "/var/lib/asterisk/sounds",
                                            'folder_name'               =>  "custom",
                                            'nombre_archivo_respaldo'   =>  "var.lib.asterisk.sounds.custom.tgz"
                                    );
                if(!restaurar_carpeta($arrInfoRestaurar,$ruta_respaldo,$error))
                    $bExito = false;
            }
            break;

        case "as_zaptel":
            $comando="sudo -u root touch /etc/zaptel.conf";
            exec($comando, $output, $retval);

            $comando="sudo -u root chmod 777 /etc/zaptel.conf";
            exec($comando, $output, $retval);

            $comando="cat $ruta_respaldo/zaptel.conf > /etc/zaptel.conf";
            exec($comando, $output, $retval);
            //Solo en este verifico si se ejecuto correctamente pues aqui es donde se copia la info
            if ($retval!=0) $bExito = false;

            $comando="sudo -u root chmod 644 /etc/zaptel.conf";
            exec($comando, $output, $retval);
            break;

        case "fx_db":
            if (file_exists("$ruta_respaldo/fax.db"))
            {
                $base_fax_respaldo = "$ruta_respaldo/fax.db";
                $base_fax= "/var/www/db/fax.db";

                //consultar en la base para crear en el sistema
                crear_cuentas_fax($base_fax_respaldo,$base_fax);

                /*$comando="mv -f $base_fax_respaldo $base_fax";
                exec($comando,$output,$retval);
                if ($retval!=0) $bExito = false;
                */
                $comando="sudo -u root /bin/chmod 777 $base_fax";
                exec($comando,$output,$retval);
            }else $bExito = false;
            break;

        case "fx_pdf":
            //Respaldo carpeta /etc/asterisk en un tgz
            $comando="sudo -u root touch /var/www/html/faxes.tgz";
            exec($comando, $output, $retval);

            $comando="sudo -u root chmod 777 /var/www/html/faxes.tgz";
            exec($comando, $output, $retval);

            //Respaldo carpeta /var/spool/asterisk/sounds en un tgz
            $comando="tar cvfz /var/www/html/faxes.tgz /var/www/html/faxes/";
            exec($comando, $output, $retval);
            if ($retval!=0) $bExito = false;
            else{
                $comando="rm -rf /var/www/html/faxes/*";
                exec($comando, $output, $retval);

                $arrInfoRestaurar = array(  'folder_path'               =>  "/var/www/html",
                                            'folder_name'               =>  "faxes",
                                            'nombre_archivo_respaldo'   =>  "var.www.html.faxes.tgz"
                                    );
                if(!restaurar_carpeta($arrInfoRestaurar,$ruta_respaldo,$error))
                    $bExito = false;
            }
            break;

        case "em_db":
            //Primero eliminar todos los dominios existentes
            $pDB = new paloDB("sqlite3:////var/www/db/email.db");
            if(!empty($pDB->errMsg)) {
                echo "ERROR DE DB: $pDB->errMsg <br>";
            }
            $pEmail = new paloEmail($pDB);
            $arrDomain = $pEmail->getDomains();
            foreach($arrDomain as $key => $valor)
            {
                $arrTmp['domain_name']  = $valor[1];
                $arrTmp['id_domain']    = $valor[0];
                $bExito = eliminar_dominio($pDB,$arrTmp,$errMsg);
            }

            $archivo = "roundcubedb";
            if (file_exists("$ruta_respaldo/{$archivo}_mysql.tgz"))
            {
                $comando="tar -C $ruta_respaldo -xvzf $ruta_respaldo/{$archivo}_mysql.tgz";
                exec($comando,$output,$retval);

                $base = $archivo;
                $comando="mysql --password=".MYSQL_ROOT_PASSWORD." --user=root $base < $ruta_respaldo/$archivo.sql";
                exec($comando,$output,$retval);
                if ($retval!=0) $bExito = false;

            }else $bExito = false;

            if (file_exists("$ruta_respaldo/email.db"))
            {
                $base_email_respaldo = "$ruta_respaldo/email.db";
                $base_email = "/var/www/db/email.db";

                //consultar en la base para crear en el sistema
                if(!crear_cuentas_email($base_email_respaldo, $base_email))
                    $bExito = false;

                $comando="mv -f $base_email_respaldo $base_email";
                exec($comando,$output,$retval);
                if ($retval!=0) $bExito = false;

                $comando="sudo -u root /bin/chmod 777 $base_email";
                exec($comando,$output,$retval);
            }else $bExito = false;
            break;

        case "em_config_files"://user_buzon
            //Respaldo carpeta /var/spool/imap/ en un tgz
            $comando="sudo -u root touch /var/spool/imap.tgz";
            exec($comando, $output, $retval);

            $comando="sudo -u root chmod 777 /var/spool/imap.tgz";
            exec($comando, $output, $retval);

            $comando="sudo -u root /bin/chown asterisk:asterisk /var/spool/imap -R";
            exec($comando, $output, $retval);

            $comando="tar cvfz /var/spool/imap.tgz /var/spool/imap/";
            exec($comando, $output, $retval);
            if ($retval!=0) $bExito = false;
            else{
                $comando="rm -rf /var/spool/imap/*";
                exec($comando, $output, $retval);
                if ($retval!=0) $bExito = false;
                else{
                    $arrInfoRestaurar = array(  'folder_path'               =>  "/var/spool",
                                                'folder_name'               =>  "imap",
                                                'nombre_archivo_respaldo'   =>  "var.spool.imap.tgz"
                                        );
                    if(!restaurar_carpeta($arrInfoRestaurar,$ruta_respaldo,$error))
                        $bExito = false;

                    //cambio de nuevo los permisos
                    $comando="sudo -u root /bin/chown cyrus:mail /var/spool/imap -R";
                    exec($comando,$output,$retval);
                }
            }
            break;

        case "ep_db":
            $comando="cp $ruta_respaldo/endpoint.db /var/www/db/";
            exec($comando, $output, $retval);
            if ($retval!=0) $bExito = false;
            break;

        case "ep_config_files":
            //Respaldo carpeta /var/spool/imap/ en un tgz
            $comando="sudo -u root touch /tftpboot.tgz";
            exec($comando, $output, $retval);

            $comando="sudo -u root chmod 777 /tftpboot.tgz";
            exec($comando, $output, $retval);

            $comando="tar cvfz /tftpboot.tgz /tftpboot/";
            exec($comando, $output, $retval);
            if ($retval!=0) $bExito = false;
            else{
                //tengo que cambiarle los permisos a la carpeta (con sudo) por que sino no voy a poder hacerle el restore
                $comando="sudo -u root /bin/chown asterisk:asterisk /tftpboot -R";
                exec($comando,$output,$retval);
                if ($retval!=0) $bExito = false;
                else{
                    $comando="rm -rf /tftpboot/*";
                    exec($comando, $output, $retval);

                    $arrInfoRestaurar = array(  'folder_path'               =>  "/",
                                                'folder_name'               =>  "tftpboot",
                                                'nombre_archivo_respaldo'   =>  "tftpboot.tgz"
                                        );
                    if(!restaurar_carpeta($arrInfoRestaurar,$ruta_respaldo,$error))
                        $bExito = false;

                    //cambio de nuevo a root
                    $comando="sudo -u root /bin/chown root:root /tftpboot -R";
                    exec($comando,$output,$retval);
                }
            }
            break;

        case "sugar_db":
            $archivo = "sugarcrm";
            if (file_exists("$ruta_respaldo/{$archivo}_mysql.tgz"))
            {
                $comando="tar -C $ruta_respaldo -xvzf $ruta_respaldo/{$archivo}_mysql.tgz";
                exec($comando,$output,$retval);

                $base = $archivo;
                $comando="mysql --password=".MYSQL_ROOT_PASSWORD." --user=root $base < $ruta_respaldo/$archivo.sql";
                exec($comando,$output,$retval);
                if ($retval!=0) $bExito = false;

            }
            break;

        case "vtiger_db":
            $archivo = "vtigercrm503";
            if (file_exists("$ruta_respaldo/{$archivo}_mysql.tgz"))
            {
                $comando="tar -C $ruta_respaldo -xvzf $ruta_respaldo/{$archivo}_mysql.tgz";
                exec($comando,$output,$retval);

                $base = $archivo;
                $comando="mysql --password=".MYSQL_ROOT_PASSWORD." --user=root $base < $ruta_respaldo/$archivo.sql";
                exec($comando,$output,$retval);
                if ($retval!=0) $bExito = false;

            }
            break;

        case "a2billing_db":
            $archivo = "mya2billing";
            if (file_exists("$ruta_respaldo/{$archivo}_mysql.tgz"))
            {
                $comando="tar -C $ruta_respaldo -xvzf $ruta_respaldo/{$archivo}_mysql.tgz";
                exec($comando,$output,$retval);

                $base = $archivo;
                $comando="mysql --password=".MYSQL_ROOT_PASSWORD." --user=root $base < $ruta_respaldo/$archivo.sql";
                exec($comando,$output,$retval);
                if ($retval!=0) $bExito = false;

            }
            break;

        case "mysql_db":
            $archivo = "mysql";
            if (file_exists("$ruta_respaldo/{$archivo}_mysql.tgz"))
            {
                $comando="tar -C $ruta_respaldo -xvzf $ruta_respaldo/{$archivo}_mysql.tgz";
                exec($comando,$output,$retval);

                $base = $archivo;
                $comando="mysql --password=".MYSQL_ROOT_PASSWORD." --user=root $base < $ruta_respaldo/$archivo.sql";
                exec($comando,$output,$retval);
                if ($retval!=0) $bExito = false;

            }
            break;

        case "menus_permissions":
            $comando="cp $ruta_respaldo/menu.db $ruta_respaldo/acl.db /var/www/db/";
            exec($comando, $output, $retval);
            if ($retval!=0) $bExito = false;
            break;
        }
        if ($bExito) $msge="[ OK ]";
        else $msge="[ FAILED ]";
        $arrRestoreOptions[$option]["msg"]=$msge;
    }
}

function restaurar_carpeta($arrInfoRestaurar,$ruta_respaldo,&$error)
{
    $bExito=true;
    $comando="tar -C ".$arrInfoRestaurar['folder_path'] .
             " -pxvzf $ruta_respaldo/{$arrInfoRestaurar['nombre_archivo_respaldo']}";

    exec($comando,$output,$retval);
    if ($retval<>0) $bExito=false;

    return $bExito;
}

function crear_cuentas_fax($ruta_base_fax_respaldo,$base_fax)
{
    $result=array();
    $oFax = new paloFax(); 

    $pDB = new paloDB("sqlite3:///$ruta_base_fax_respaldo");
    if (!empty($pDB->errMsg)) {
        echo "DB ERROR: $pDB->errMsg \n";
    }
    else{
        #borrar las cuentas de fax de /var/www/db
        $pDBorig = new paloDB("sqlite3:///$base_fax");
        if (!empty($pDBorig->errMsg)) {
            echo "DB ERROR: $pDBorig->errMsg \n";
        }
        else{
            #TODO:
            #antes de borrar de la base de datos deberia seleccionar cada una e ir borrando del equipo
            $query="SELECT id FROM fax";
            $result=$pDBorig->fetchTable($query,true);
            if(is_array($result) && count($result) > 0){
                foreach($result as $key => $value)
                    $oFax->deleteFaxExtensionById($value['id']);
            }

            $query="SELECT * FROM fax";
            $result=$pDB->fetchTable($query,true);
            if (is_array($result) && count($result) > 0){
                foreach ($result as $arrFax)
                {
                    $arrFax['country_code'] = isset($arrFax['country_code'])?$arrFax['country_code']:"";
                    $arrFax['area_code']    = isset($arrFax['area_code'])?$arrFax['area_code']:"";
                    $oFax->createFaxExtension($arrFax['name'], $arrFax['extension'], $arrFax['secret'], $arrFax['email'],$arrFax['clid_name'], $arrFax['clid_number'], $arrFax['dev_id'], $arrFax['port'], $arrFax['country_code'], $arrFax['area_code']);
                }
            }
        }
    }
}

function crear_cuentas_email($ruta_base_email_respaldo,$base_email)
{
    $bExito=true;
    $result=array();
    $pDB = new paloDB("sqlite3:///$ruta_base_email_respaldo");
    if (!empty($pDB->errMsg)) {
        echo "DB ERROR: $pDB->errMsg \n";
    }
    else{
        #borrar las cuentas de dominos y el domino /var/www/db
        $pDBorig = new paloDB("sqlite3:///$base_email");
        if (!empty($pDBorig->errMsg)) {
            echo "DB ERROR: $pDBorig->errMsg \n";
        }
        else{
            $query="SELECT * FROM domain";
            $result=$pDBorig->fetchTable($query,true);
            if(is_array($result) && count($result) > 0){
                foreach($result as $key => $value){
                    $arrTmp['id_domain']= $value['id'];
                    $arrTmp['domain_name']= $value['domain_name'];
                    $bExito=eliminar_dominio($pDBorig,$arrTmp,$errMsg);
                }
            }

            if($bExito){
                #crear los dominios
                #seleccionar los dominios
                $sQuery="SELECT * from domain";
                $result=$pDB->fetchTable($sQuery,true);
                if(is_array($result) && count($result)>0)
                {
                    foreach ($result as $infoDominio)
                    {
                        guardar_dominio_sistema($infoDominio['domain_name'],$errMsg);
                    }
                    #crear las cuentas
                    $sQuery="SELECT a.*,d.domain_name from accountuser as a,domain as d where d.id=a.id_domain";
                    $result=$pDB->fetchTable($sQuery,true);
                    if (is_array($result)){
                        foreach ($result as $infoCuenta)
                        {
                            $username=$infoCuenta['username'];
                            $quota=$infoCuenta['quota'];
                            #armo el email
                            if (ereg("(.*)\.($infoCuenta[domain_name])",$username,$regs))
                            {
                                $email=$regs[1].'@'.$infoCuenta['domain_name'];
                                $password=$infoCuenta['password'];
                                $bExito=crear_usuario_correo_sistema($email,$username,$password,$errMsg);
                                if ($bExito){
                                    //crear el mailbox para la nueva cuenta
                                    $bReturn=crear_mailbox_usuario($email,$username,$quota,$errMsg);
                                    if(!$bReturn)
                                        $bReturn=eliminar_usuario_correo_sistema($username,$email,$errMsg);
                                }else{
                                    //tengo que borrar el usuario creado en el sistema
                                    $bReturn=eliminar_usuario_correo_sistema($username,$email,$errMsg);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    return $bExito;
}

function crear_mailbox_usuario($email,$username,$quota,&$error_msg){
    global $CYRUS;
    global $arrLang;
    $cyr_conn = new cyradm;
    $error=$cyr_conn->imap_login();
    if ($error===FALSE){
        $error_msg.="IMAP login error: $error <br>";
    }
    else{
        $seperator  = '/';
        $bValido=$cyr_conn->createmb("user" . $seperator . $username);
        if(!$bValido)
            $error_msg.="Error creating user:".$cyr_conn->getMessage()."<br>";
        else{
            $bValido=$cyr_conn->setacl("user" . $seperator . $username, $CYRUS['ADMIN'], "lrswipcda");
            if(!$bValido)
                $error_msg.="error:".$cyr_conn->getMessage()."<br>";
            else{
                $bValido = $cyr_conn->setmbquota("user" . $seperator . $username, $quota);
                if(!$bValido)
                    $error_msg.="error".$cyr_conn->getMessage()."<br>";
            }
        }

    }

    return TRUE;
}
?>