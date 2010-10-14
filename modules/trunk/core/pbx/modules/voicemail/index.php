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
  $Id: index.php,v 1.1.1.1 2007/07/06 21:31:56 gcarrillo Exp $ */

function _moduleContent(&$smarty, $module_name)
{
    include_once "libs/paloSantoGrid.class.php";
    include_once "libs/paloSantoConfig.class.php";
    include_once "libs/paloSantoACL.class.php";
    include_once "libs/paloSantoForm.class.php";
    require_once "libs/misc.lib.php";

    include_once "lib/paloSantoVoiceMail.class.php";

    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    
    $lang=get_language();
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $lang_file="modules/$module_name/lang/$lang.lang";
    if (file_exists("$base_dir/$lang_file")) include_once "$lang_file";
    else include_once "modules/$module_name/lang/en.lang";

    //global variables
    global $arrConf;
    global $arrConfModule;
    global $arrLang;
    global $arrLangModule;
    $arrConf = array_merge($arrConf,$arrConfModule);
    $arrLang = array_merge($arrLang,$arrLangModule);

    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConf['templates_dir']))?$arrConf['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    //segun el usuario que esta logoneado consulto si tiene asignada extension para buscar los voicemails
    $pDB = new paloDB($arrConf['elastix_dsn']['acl']);

    $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
    $arrAMP  = $pConfig->leer_configuracion(false);

    $dsnAsterisk = $arrAMP['AMPDBENGINE']['valor']."://".
                   $arrAMP['AMPDBUSER']['valor']. ":".
                   $arrAMP['AMPDBPASS']['valor']. "@".
                   $arrAMP['AMPDBHOST']['valor']. "/asterisk";
    $pDB_ast = new paloDB($dsnAsterisk);


    if (!empty($pDB->errMsg)) {
        echo "ERROR DE DB: $pDB->errMsg <br>";
    }

    $arrData = array();
    $pACL = new paloACL($pDB);
    if (!empty($pACL->errMsg)) {
        echo "ERROR DE ACL: $pACL->errMsg <br>";
    }
    $arrVoiceData = array();
    $inicio= $fin = $total = 0;
    $extension = $pACL->getUserExtension($_SESSION['elastix_user']); $ext = $extension; 
    $esAdministrador = $pACL->isUserAdministratorGroup($_SESSION['elastix_user']);
    if($esAdministrador)
        $extension = "[[:digit:]]+";

    $smarty->assign("menu","voicemail");
    $smarty->assign("Filter",$arrLang['Filter']);
    $smarty->assign("CONFIG",$arrLang["Configuration"]);
    //formulario para el filtro
    $arrFormElements = createFieldFormVoiceList($arrLang);
    $oFilterForm = new paloForm($smarty, $arrFormElements);
        // Por omision las fechas toman el sgte. valor (la fecha de hoy)
    $date_start = date("Y-m-d")." 00:00:00"; 
    $date_end   = date("Y-m-d")." 23:59:59";

    if( getParameter('filter') ){
        if($oFilterForm->validateForm($_POST)) {
            // Exito, puedo procesar los datos ahora.
            $date_start = translateDate($_POST['date_start'])." 00:00:00"; 
            $date_end   = translateDate($_POST['date_end'])." 23:59:59";
            $arrFilterExtraVars = array("date_start" => $_POST['date_start'], "date_end" => $_POST['date_end'] );
        } else {
            // Error
            $smarty->assign("mb_title", $arrLang["Validation Error"]);
            $arrErrores=$oFilterForm->arrErroresValidacion;
            $strErrorMsg = "<b>{$arrLang['The following fields contain errors']}:</b><br>";
            foreach($arrErrores as $k=>$v) {
                $strErrorMsg .= "$k, ";
            }
            $strErrorMsg .= "";
            $smarty->assign("mb_message", $strErrorMsg);
        }
        $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", $_POST);
    } else if (isset($_GET['date_start']) AND isset($_GET['date_end'])) {
        $date_start = translateDate($_GET['date_start']) . " 00:00:00";
        $date_end   = translateDate($_GET['date_end']) . " 23:59:59";

        $arrFilterExtraVars = array("date_start" => $_GET['date_start'], "date_end" => $_GET['date_end']);
        $htmlFilter = $contenidoModulo=$oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", $_GET);
    } else {
        $htmlFilter = $contenidoModulo=$oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", 
        array('date_start' => date("d M Y"), 'date_end' => date("d M Y")));
    }

    if( getParameter('submit_eliminar') ) {
        borrarVoicemails();
        if($oFilterForm->validateForm($_POST)) {
            // Exito, puedo procesar los datos ahora.
            $date_start = translateDate($_POST['date_start']) . " 00:00:00"; 
            $date_end   = translateDate($_POST['date_end']) . " 23:59:59";
            $arrFilterExtraVars = array("date_start" => $_POST['date_start'], "date_end" => $_POST['date_end']);
        }
        $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", $_POST);
    }

    if( getParameter('config') ){
        return form_config($smarty, $module_name, $local_templates_dir, $arrLang, $ext, $pDB_ast);
    }

    if( getParameter('save') ){
        if( !save_config($smarty, $module_name, $local_templates_dir, $arrLang, $ext, $pDB_ast) )
            return form_config($smarty, $module_name, $local_templates_dir, $arrLang, $ext, $pDB_ast);
    }

    if( getParameter('action') == "display_record"){

        $file = getParameter("name");
        $ext  = getParameter("ext");        
        $user = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
        $extension = $pACL->getUserExtension($user);
        $esAdministrador = $pACL->isUserAdministratorGroup($user);
        $path = "/var/spool/asterisk/voicemail/default";
        $voicemailPath = "$path/$ext/INBOX/".base64_decode($file);
        $tmpfile = basename($voicemailPath);
        $filetmp = "$path/$ext/INBOX/$tmpfile";
        if(!is_file($filetmp)){
            die("<b>404 ".$arrLang["no_file"]."</b>");
        }
        if(!$esAdministrador){
            if($extension != $ext){
                 die("<b>404 ".$arrLang["no_file"]."</b>");
            }
            $voicemailPath = "$path/$extension/INBOX/".base64_decode($file);
        }

        if (isset($file) && preg_match("/^[[:alpha:]]+[[:digit:]]+\.(wav|WAV|Wav|mp3|gsm)$/",base64_decode($file))) {
            if (!is_file($voicemailPath)) { 
                die("<b>404 ".$arrLang["no_file"]."</b>");
            }
            $sContenido="";

            $sContenido=<<<contenido
                    <embed src='index.php?menu=$module_name&action=download&ext=$ext&name=$file&rawmode=yes' width=300, height=20 autoplay=true loop=false></embed><br>
contenido;

            $smarty->assign("CONTENT", $sContenido);
            $smarty->display("_common/popup.tpl");
        }else{
            die("<b>404 ".$arrLang["no_file"]."</b>");
        }
        return;
    }

    if( getParameter('action') == "download"){
        $user = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
        $extension = $pACL->getUserExtension($user);
        $esAdministrador = $pACL->isUserAdministratorGroup($user);
        $record = getParameter("name");
        $ext  = getParameter("ext");
        $record = base64_decode($record);
        $path = "/var/spool/asterisk/voicemail/default";
        $voicemailPath = "$path/$ext/INBOX/".$record;//"$path/$record";
        $tmpfile = basename($voicemailPath);
        $filetmp = "$path/$ext/INBOX/$tmpfile";
        if(!is_file($filetmp)){
            die("<b>404 ".$arrLang["no_file"]."</b>");
        }
        if(!$esAdministrador){
            if($extension != $ext){
                 die("<b>404 ".$arrLang["no_extension"]."</b>");
            }
            $voicemailPath = "$path/$extension/INBOX/".$record;
        }
        if (isset($record) && preg_match("/^[[:alpha:]]+[[:digit:]]+\.(wav|WAV|Wav|mp3|gsm)$/",$record)) {
        // See if the file exists

            if (!is_file($voicemailPath)) { 
                die("<b>404 ".$arrLang["no_file"]."</b>");
            }
        // Gather relevent info about file
            $size = filesize($voicemailPath);
            $name = basename($voicemailPath);
    
        //$extension = strtolower(substr(strrchr($name,"."),1));
            $extension=substr(strtolower($name), -3); 

        // This will set the Content-Type to the appropriate setting for the file
            $ctype ='';
            switch( $extension ) {
    
                case "mp3": $ctype="audio/mpeg"; break;
                case "wav": $ctype="audio/x-wav"; break;
                case "Wav": $ctype="audio/x-wav"; break;
                case "WAV": $ctype="audio/x-wav"; break;
                case "gsm": $ctype="audio/x-gsm"; break;
                // not downloadable
                default: die("<b>404 ".$arrLang["no_file"]."</b>"); break ;
            }
    
        // need to check if file is mislabeled or a liar.
            $fp=fopen($voicemailPath, "rb");
            if ($size && $ctype && $fp) {
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Cache-Control: public");
                header("Content-Description: wav file");
                header("Content-Type: " . $ctype);
                header("Content-Disposition: attachment; filename=" . $name);
                header("Content-Transfer-Encoding: binary");
                header("Content-length: " . $size);
                fpassthru($fp);
            }
        }else{
            die("<b>404 ".$arrLang["no_file"]."</b>");
        }
        return;
    }

    $end = 0;
    //si tiene extension consulto sino, muestro un mensaje de que no tiene asociada extension
    $archivos=array();
    if (!is_null($extension)){
        $path = "/var/spool/asterisk/voicemail/default";
        $folder = "INBOX";

        if($esAdministrador)
        {
            if ($handle = opendir($path)) {
                while (false !== ($dir = readdir($handle))) {
                    if ($dir != "." && $dir != ".." && ereg($extension, $dir, $regs) && is_dir($path."/".$dir)) {
                        $directorios[] = $dir;
                    }
                }
            }
        }else $directorios[] = $extension;

        $arrData = array();
        foreach($directorios as $directorio)
        {
            $voicemailPath = "$path/$directorio/$folder";
            if (file_exists($voicemailPath)) {
                if ($handle = opendir($voicemailPath)) {
                    $bExito=true;
                    while (false !== ($file = readdir($handle))) {
                        //no tomar en cuenta . y ..
                        //buscar los archivos de texto (txt) que son los que contienen los datos de las llamadas
                        if ($file!="." && $file!=".." && ereg("(.+)\.[txt|TXT]",$file,$regs)) {
                            //leer la info del archivo
                            $pConfig = new paloConfig($voicemailPath, $file, "=", "[[:space:]]*=[[:space:]]*");
                            $arrVoiceMailDes=array();
                            $arrVoiceMailDes = $pConfig->leer_configuracion(false);

                            //verifico que tenga datos
                            if (is_array($arrVoiceMailDes) && count($arrVoiceMailDes)>0 && isset($arrVoiceMailDes['origtime']['valor'])){
                                //uso las fechas del filtro
                                //si la fecha de llamada esta dentro del rango, la muestro
                                $fecha = date("Y-m-d",$arrVoiceMailDes['origtime']['valor']);
                                $hora = date("H:i:s",$arrVoiceMailDes['origtime']['valor']);

                                if (strtotime("$fecha $hora")<=strtotime($date_end) && strtotime("$fecha $hora")>=strtotime($date_start)){
                                    $arrTmp[0] = "<input type='checkbox' name='".utf8_encode("voc-".$file).",$directorio' />";
                                    $arrTmp[1] = $fecha;
                                    $arrTmp[2] = $hora;
                                    $arrTmp[3] = $arrVoiceMailDes['callerid']['valor'];
                                    $arrTmp[4] = $arrVoiceMailDes['origmailbox']['valor'];
                                    $arrTmp[5] = $arrVoiceMailDes['duration']['valor'].' sec.';

                                    //$pathRecordFile="$voicemailPath/".$regs[1].'.wav';
                                    $pathRecordFile=base64_encode($regs[1].'.wav');
                                    $recordingLink = "<a href='#' onClick=\"javascript:popUp('index.php?menu=$module_name&action=display_record&ext=$directorio&name=$pathRecordFile&rawmode=yes',350,100); return false;\">{$arrLang['Listen']}</a>&nbsp;";
                                    $recordingLink .= "<a href='?menu=$module_name&action=download&ext=$directorio&name=$pathRecordFile&rawmode=yes'>{$arrLang['Download']}</a>";

                                    $arrTmp[6] = $recordingLink;
                                    $arrData[] = $arrTmp;
                                }
                            }
                        }
                    }
                    closedir($handle);
                }
            } else {
                // No vale la ruta
            }
        }
        /*
        function sort_voicemails_hora_desc($a, $b) { return ($a[2] == $b[2]) ? 0 : (($a[2] < $b[2]) ? 1 : -1); }
        function sort_voicemails_fecha_desc($a, $b) { return ($a[1] == $b[1]) ? 0 : (($a[1] < $b[1]) ? 1 : -1); }
        usort($arrData, 'sort_voicemails_hora_desc');
        usort($arrData, 'sort_voicemails_fecha_desc');
        */
        $fechas = array();
        $horas  = array();
        foreach ($arrData as $llave => $fila) {
            $fechas[$llave]  = $fila[1];
            $horas[$llave]   = $fila[2];
        }
        array_multisort($fechas,SORT_DESC,$horas,SORT_DESC,$arrData);

        //Paginacion
        $limit  = 15;
        $total  = count($arrData);

        $oGrid  = new paloSantoGrid($smarty);
        $offset = $oGrid->getOffSet($limit,$total,(isset($_GET['nav']))?$_GET['nav']:NULL,(isset($_GET['start']))?$_GET['start']:NULL);

        $end    = ($offset+$limit)<=$total ? $offset+$limit : $total;

        // Construyo el URL base
        if(isset($arrFilterExtraVars) && is_array($arrFilterExtraVars) and count($arrFilterExtraVars)>0) {
            $url = construirURL($arrFilterExtraVars, array("nav", "start"));
        } else {
            $url = construirURL(array(), array("nav", "start")); 
        }
        $smarty->assign("url", $url);
        //Fin Paginacion

        $arrVoiceData=array_slice($arrData, $offset, $limit);
    } //fin if (!is_null(extension))
    else {
        $smarty->assign("mb_message", "<b>".$arrLang["You don't have extension number associated with user"]."</b>");
    }

    $arrGrid = array("title"   => $arrLang["Voicemail List"],
                     "icon"    => "images/record.png",
                     "width"   => "99%",
                     "start"   => ($total==0) ? 0 : $offset + 1,
                     "end"     => $end,
                     "total"   => $total,
                     "columns" => array(0 => array("name"      => "<input type='submit' onClick=\"return confirmSubmit('{$arrLang["Are you sure you wish to delete voicemails?"]}');\" name='submit_eliminar' value='{$arrLang["Delete"]}' class='button' />",
                                                   "property1" => ""),
                                        1 => array("name"      => $arrLang["Date"],
                                                   "property1" => ""),
                                        2 => array("name"      => $arrLang["Time"],
                                                   "property1" => ""),
                                        3 => array("name"      => $arrLang["CallerID"],
                                                   "property1" => ""),
                                        4 => array("name"      => $arrLang["Extension"],
                                                   "property1" => ""),
                                        5 => array("name"      => $arrLang["Duration"],
                                                   "property1" => ""),
                                        6 => array("name"      => $arrLang["Message"],
                                                   "property1" => ""),
                                        )
                    );

    $contenidoModulo  = "<form style='margin-bottom:0;' method='POST' action='?menu=$module_name'>";
    $oGrid = new paloSantoGrid($smarty);
    $oGrid->showFilter($htmlFilter);
    $contenidoModulo  .= $oGrid->fetchGrid($arrGrid, $arrVoiceData,$arrLang);
    $contenidoModulo .= "</form>";
    return $contenidoModulo;
}

function form_config($smarty, $module_name, $local_templates_dir, $arrLang, $ext, $pDB)
{
    $arrForm = createFieldFormConfig($arrLang);
    $oForm = new paloForm($smarty, $arrForm);

    $smarty->assign("REQUIRED_FIELD", $arrLang["Required Field"]);
    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);

    $paloVoice = new paloSantoVoiceMail($pDB);
    $arrDat = $paloVoice->loadConfiguration($ext);

    if( !isset($_POST['save']) ){
        if( $arrDat == null ){
            $_POST['status'] = "Disable";
            $_POST['email_attach']  = "No";
            $_POST['play_cid']      = "No";
            $_POST['play_envelope'] = "No";
            $_POST['delete_vmail']  = "No";
        }
        else{
            $_POST['status'] = "Enable";
            
            $_POST['password']        = $arrDat[1];
            $_POST['password_confir'] = $arrDat[1];
            //$Name = $arrDat[2];
            $_POST['email']           = $arrDat[3];
            $_POST['pager_email']     = $arrDat[4];
            //$VM_Options = $arrDat[5];
            $_POST['email_attach']    = ($arrDat[6] == 'yes')?"Yes":"No";
            $_POST['play_cid']        = ($arrDat[7] == 'yes')?"Yes":"No";
            $_POST['play_envelope']   = ($arrDat[8] == 'yes')?"Yes":"No";
            $_POST['delete_vmail']    = ($arrDat[9] == 'yes')?"Yes":"No";
        }
    }
    
    $htmlForm = $oForm->fetchForm("$local_templates_dir/configuration.tpl",$arrLang["Configuration"], $_POST);

    $contenidoModulo = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $contenidoModulo;
}

function save_config($smarty, $module_name, $local_templates_dir, $arrLang, $ext, $pDB_ast)
{
    $paloVoice = new paloSantoVoiceMail($pDB_ast);
    $arrDat = $paloVoice->loadConfiguration($ext);

    $arrForm = createFieldFormConfig($arrLang);
    $oForm = new paloForm($smarty, $arrForm);

    if(!$oForm->validateForm($_POST) || $_POST['password'] != $_POST['password_confir']){
        $smarty->assign("mb_title", "Validation Error");
        $arrErrores = $oForm->arrErroresValidacion;
        $strErrorMsg = "<b>'The following fields contain errors':</b><br/>";
        if(is_array($arrErrores) && count($arrErrores) > 0){
            foreach($arrErrores as $k=>$v)
                $strErrorMsg .= "$k, ";
        }

        if($_POST['password'] != $_POST['password_confir']) $strErrorMsg .= "Confirm Password";

        $smarty->assign("mb_message", $strErrorMsg);
        return false;
    }

    $option = ($_POST['status'] == "Enable")?1:0;

    $Ext                 = $ext;
    $VoiceMail_PW        = $_POST['password'];
    $password_2          = $_POST['password_confir'];
    $Name                = $arrDat[2];
    $VM_Email_Address    = $_POST['email'];
    $VM_Pager_Email_Addr = $_POST['pager_email'];
    $VM_Options          = $arrDat[5];
    $VM_EmailAttachment  = ($_POST['email_attach'] == 'Yes') ?'yes':'no';
    $VM_Play_CID         = ($_POST['play_cid'] == 'Yes')     ?'yes':'no';
    $VM_Play_Envelope    = ($_POST['play_envelope'] == 'Yes')?'yes':'no';
    $VM_Delete_Vmail     = ($_POST['delete_vmail'] == 'Yes') ?'yes':'no';

    $bandera = $paloVoice->writeFileVoiceMail($Ext,$Name,$VoiceMail_PW,$VM_Email_Address, $VM_Pager_Email_Addr,
                                              $VM_Options, $VM_EmailAttachment, $VM_Play_CID, $VM_Play_Envelope,
                                              $VM_Delete_Vmail, $option);

    if( $bandera == true )
        return true;
    else
        return false;
}

function borrarVoicemails()
{
    $voicemailPath = "";
    $path = "/var/spool/asterisk/voicemail/default";
    $folder = "INBOX";
    //$voicemailPath = "$path/$extension/$folder";

    if(is_array($_POST) && count($_POST) > 0){
        foreach($_POST as $name => $on){
            if(substr($name,0,4)=='voc-'){
                $arrData = split(",", $name);
                $file = substr($arrData[0],4);
                $voicemailPath = "$path/{$arrData[1]}/$folder";
                $pos = strrpos($file, '_');
                $file = substr($file, 0, strrpos($file, '_'));
                unlink("$voicemailPath/$file.txt");
                unlink("$voicemailPath/$file.wav");
                unlink("$voicemailPath/$file.WAV");
            }
        }
    }
}

function createFieldFormVoiceList($arrLang)
{
    $arrayFields = array(
        "date_start" => array("LABEL"                  => $arrLang["Start Date"],
                              "REQUIRED"               => "yes",
                              "INPUT_TYPE"             => "DATE",
                              "INPUT_EXTRA_PARAM"      => "",
                              "VALIDATION_TYPE"        => "ereg",
                              "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"),
        "date_end"   => array("LABEL"                  => $arrLang["End Date"],
                              "REQUIRED"               => "yes",
                              "INPUT_TYPE"             => "DATE",
                              "INPUT_EXTRA_PARAM"      => "",
                              "VALIDATION_TYPE"        => "ereg",
                              "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"),
       );
    return $arrayFields;
}

function createFieldFormConfig($arrLang)
{
    $arrFields = array(
        "email"             => array("LABEL"                  => $arrLang['Email'],
                                     "REQUIRED"               => "yes",
                                     "INPUT_TYPE"             => "TEXT",
                                     "INPUT_EXTRA_PARAM"      => "",
                                     "VALIDATION_TYPE"        => "email",
                                     "VALIDATION_EXTRA_PARAM" => ""),
        "pager_email"       => array("LABEL"                  => $arrLang['Pager Email Address'],
                                     "REQUIRED"               => "no",
                                     "INPUT_TYPE"             => "TEXT",
                                     "INPUT_EXTRA_PARAM"      => "",
                                     "VALIDATION_TYPE"        => "email",
                                     "VALIDATION_EXTRA_PARAM" => ""),
        "status"            => array("LABEL"                  => $arrLang['Status'],
                                     "REQUIRED"               => "no",
                                     "INPUT_TYPE"             => "SELECT",
                                     "INPUT_EXTRA_PARAM"      => array("Enable"=>$arrLang["Enable"],"Disable"=>$arrLang["Disable"]),
                                     "VALIDATION_TYPE"        => "text",
                                     "VALIDATION_EXTRA_PARAM" => ""),
        "password"          => array("LABEL"                  => $arrLang['Password'],
                                     "REQUIRED"               => "yes",
                                     "INPUT_TYPE"             => "PASSWORD",
                                     "INPUT_EXTRA_PARAM"      => "",
                                     "VALIDATION_TYPE"        => "text",
                                     "VALIDATION_EXTRA_PARAM" => ""),
        "password_confir"   => array("LABEL"                  => $arrLang['Confirm Password'],
                                     "REQUIRED"               => "yes",
                                     "INPUT_TYPE"             => "PASSWORD",
                                     "INPUT_EXTRA_PARAM"      => "",
                                     "VALIDATION_TYPE"        => "text",
                                     "VALIDATION_EXTRA_PARAM" => ""),
        "email_attach"      => array("LABEL"                  => $arrLang["Email Attachment"],
                                     "REQUIRED"               => "yes",
                                     "INPUT_TYPE"             => "RADIO",
                                     "INPUT_EXTRA_PARAM"      => array("Yes"=>$arrLang["Yes"],"No"=>$arrLang["No"]),
                                     "VALIDATION_TYPE"        => "text",
                                     "VALIDATION_EXTRA_PARAM" => ""),
        "play_cid"          => array("LABEL"                  => $arrLang["Play CID"],
                                     "REQUIRED"               => "yes",
                                     "INPUT_TYPE"             => "RADIO",
                                     "INPUT_EXTRA_PARAM"      => array("Yes"=>$arrLang["Yes"],"No"=>$arrLang["No"]),
                                     "VALIDATION_TYPE"        => "text",
                                     "VALIDATION_EXTRA_PARAM" => ""),
        "play_envelope"     => array("LABEL"                  => $arrLang["Play Envelope"],
                                     "REQUIRED"               => "yes",
                                     "INPUT_TYPE"             => "RADIO",
                                     "INPUT_EXTRA_PARAM"      => array("Yes"=>$arrLang["Yes"],"No"=>$arrLang["No"]),
                                     "VALIDATION_TYPE"        => "text",
                                     "VALIDATION_EXTRA_PARAM" => ""),
        "delete_vmail"     => array("LABEL"                  => $arrLang["Delete Vmail"],
                                     "REQUIRED"               => "yes",
                                     "INPUT_TYPE"             => "RADIO",
                                     "INPUT_EXTRA_PARAM"      => array("Yes"=>$arrLang["Yes"],"No"=>$arrLang["No"]),
                                     "VALIDATION_TYPE"        => "text",
                                     "VALIDATION_EXTRA_PARAM" => ""),
    );

    return $arrFields;
}

?>
