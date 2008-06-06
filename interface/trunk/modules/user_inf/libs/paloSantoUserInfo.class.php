<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0                                                  |
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
  $Id: paloSantoUserInfo.class.php,v 1.1.1.1 2008/01/31 21:31:55  Exp $ */

class paloSantoUserInfo {
    var $_DB;
    var $errMsg;

    function paloSantoUserInfo(&$pDB)
    {
        // Se recibe como parámetro una referencia a una conexión paloDB
        if (is_object($pDB)) {
            $this->_DB =& $pDB;
            $this->errMsg = $this->_DB->errMsg;
        } else {
        $dsn = (string)$pDB;
            $this->_DB = new paloDB($dsn);

            if (!$this->_DB->connStatus) {
                $this->errMsg = $this->_DB->errMsg;
                // debo llenar alguna variable de error
            } else {
                // debo llenar alguna variable de error
            }
        }
    }

   function getSystemStatus($email,$passw){
    global $arrLang;
        global $arrConf;
    
    $dbEmails = new paloDB("sqlite3:////var/www/db/email.db");
    $imap = imap_open("{localhost:143}",$email,$passw);

    if (!$imap)
        return $arrLang["Imap: Connection error"];
    $quotainfo = imap_get_quotaroot($imap,"INBOX");
    imap_close($imap);

    $content = $arrLang["Quota asigned"]." $quotainfo[limit] KB<br>".$arrLang["Quota Used"]." $quotainfo[usage] KB<br>".$arrLang["Quota free space"]." ". (string)($quotainfo['limit'] - $quotainfo['usage']) . " KB";
    return $content;
   }

   function getMails($email,$passw,$numRegs){
    global $arrLang;
    
    $counter    = 0;
    $imap = imap_open("{localhost:143}INBOX",$email,$passw);

    if(!$imap)
        return $arrLang["Imap: Connection error"];

    $tmp = imap_check($imap);

    if($tmp->Nmsgs==0)
        return $arrLang["You don't recibed emails"];

    $result = imap_fetch_overview($imap,"1:{$tmp->Nmsgs}",0);
    
    $mails = array();
        //print_r($result);
    foreach ($result as $overview) {
        $mails[] = array("seen"=>$overview->seen,
                 "recent"=>$overview->recent,
                 "answered"=>$overview->answered,
                 "date"=>$overview->date,
                 "from"=>$overview->from,
                 "subject"=>$overview->subject);
    }
    
    imap_close($imap);
    
    $mails = array_slice($mails,-$numRegs,$numRegs);
        krsort($mails);

    $content = "";

    foreach($mails as $value){
        $temp = $arrLang["mail recived"];
                $temp = str_replace("{source}",$value["from"],$temp);
                $temp = str_replace("{date}",$value["date"],$temp);
                $temp = str_replace("{subject}",$value["subject"],$temp);

                $b = ($value["seen"] or $value["answered"])?false:true;
                if($b)
                    $temp = "<b>$temp</b>";
                $content.=$temp."<br>";
    }
    return $content;
    }
   
   function getVoiceMails($extension,$numRegs){
    global $arrLang;
    $exists = false;
    $count = 0;

    if(is_null($extension))
                return $arrLang["You haven't extension"];

    $voicePath = "/var/spool/asterisk/voicemail/default/$extension/INBOX";

    $exists = file_exists($voicePath);
    
        $result = array();
    if($exists)
        exec("ls -t $voicePath/*txt | head -n $numRegs",$result);
    
    $count = count($result);
    
    if(!$exists or ($count == 0))
        return $arrLang["You don't recibed voicemails"];
    $data ="";

    foreach ($result as $value){
        $content = array();
        $file = fopen($value,"r");
        if(!$file)
            return $arrLang["Unenabled to open file"];
        
        while($row = fgetcsv($file,4096,"=")){
            if(isset($row[1]))
                $content[$row[0]]=$row[1];
        }
        fclose($file);

        $date = date('Y/m/d H:i:s',$content["origtime"]);
        $source = ($content["callerid"]=="Unknown")?$arrLang["unknow"]:$content["callerid"];
        $duration = $content["duration"];

        $temp = $arrLang["voicemail recived"];
        $temp = str_replace("{source}",$source,$temp);
                $temp = str_replace("{date}",$date,$temp);
        $temp = str_replace("{duration}",$duration,$temp);
        $data.="$temp.<br>";
        
    }
    
    return $data;
   }

   function getLastFaxes($extension,$numRegs){
    global $arrLang;

    $dbFax = new paloDB("sqlite3:////var/www/db/fax.db");

    if(is_null($extension))
                return $arrLang["You haven't extension"];
    
    $result = $dbFax->fetchTable("select a.pdf_file,a.company_name,a.date from info_fax_recvq a,fax b where b.extension='$extension' and b.id=a.fax_destiny_id order by a.id desc limit $numRegs");
    if(!$result)
        return $arrLang["You don't recibed faxes"];

    $data = "";

    foreach($result as $value){
        $temp = $arrLang["fax recived"];
        $link="<a href='/faxes/recvq/$value[0]'>$value[0]</a>";
        $temp = str_replace("{file}",$link,$temp);
        $temp = str_replace("{source}",($value[1]=="XXXXXXX")?$arrLang["unknow"]:$value[1],$temp);
        $temp = str_replace("{date}",$value[2],$temp);  
        $data.= $temp."<br>";
    }   
    return $data;
   }
   
   function getLastCalls($extension,$numRegs){
    global $arrLang;

    if(is_null($extension))
        return $arrLang["You haven't extension"];
    
    $result = $this->_DB->fetchTable("select calldate,src,duration,disposition from cdr where dst='".$extension."'  order by calldate desc limit $numRegs");
    if(count($result)==0)
        return $arrLang["You don't recibed calls"];
    
    $data = "";
    foreach($result as $value){
        $answ=($value[3]=="ANSWERED") ? true:false;

        $status = ($answ)?$arrLang['answered']:$arrLang['missed'];
        $source = ($value[1]=="")?$arrLang['unknow']:$value[1];
        $duration = ($answ)?str_replace("{time}",$value[2],$arrLang["call duration"]):".";

        $temp = $arrLang["call record"];
        $temp = str_replace("{status}",$status,$temp);
        $temp = str_replace("{date}",$value[0],$temp);
        $temp = str_replace("{source}",$source,$temp);

        $data.=$temp . $duration."<br>";
    }
    
    return $data;
   }

    function getDataUserLogon($nameUser)
    {
        global $arrConf;
        //consulto datos del usuario logoneado
        $dbAcl = new paloDB($arrConf["elastix_dsn"]["acl"]);    
        $pACL  = new paloACL($dbAcl);

        $arrData = null;
        //paso 1: consulta de los datos de webmail si existen
        $userId  = $pACL->getIdUser($nameUser);
        $arrData = $this->leerPropiedadesWebmail($dbAcl,$userId);

        //paso 2: consulta de la extension si tiene asignada
        $extension = $pACL->getUserExtension($nameUser);
        if($extension)
            $arrData['extension'] = $extension;

        $arrData['id'] = $userId;
        return $arrData;
    }

    function leerPropiedadesWebmail($pDB, $idUser)
    {
        // Obtener la información del usuario con respecto al perfil "default" del módulo "webmail"
        $sPeticionPropiedades = 
            'SELECT pp.property, pp.value '.
            'FROM acl_profile_properties pp, acl_user_profile up, acl_resource r '.
            'WHERE up.id_user = ? '.
                'AND up.profile = "default" '.
                'AND up.id_profile = pp.id_profile '.
                'AND up.id_resource = r.id '.
                'AND r.name = "webmail"';
        $listaPropiedades = array();
        $tabla = $pDB->fetchTable($sPeticionPropiedades, FALSE, array($idUser));
        if ($tabla === FALSE) {
        print "ERROR DE DB: ".$pDB->errMsg;
        } else {
        foreach ($tabla as $tupla) {
            $listaPropiedades[$tupla[0]] = $tupla[1];
        }
        }
        return $listaPropiedades;
   }

   function getEventsCalendar($idUser, $numRegs)
   {
        global $arrLang;
        $db = new paloDB("sqlite3:////var/www/db/calendar.db");

        $actual_date = date("Y-m-d");
        $actual_date_hour = date("Y-m-d H:i:s");

        $query =     "SELECT id, subject, asterisk_call, startdate, enddate, starttime, eventtype "
                    ."FROM events "
                    ."WHERE uid=$idUser and enddate>='$actual_date' "
                    ."ORDER BY id desc;";

        $result = $db->fetchTable($query, TRUE);
        if(!$result)
            return $arrLang["You don't have events"];

        $data = "";

        $arrEventos = array();
        foreach($result as $value){
            $iStartTimestamp    = strtotime($value['starttime']);
            $endstamp           = strtotime($value['enddate']);
            $startstamp         = strtotime($value['startdate']);

            if($value['eventtype']==1 || $value['eventtype']==5)
            {
                if($value['eventtype']==1)
                {
                    $segundos = 86400;
                    $num_dias = (($endstamp-$startstamp)/$segundos)+1;//Sumo 1 para incluir el ultimo dia
                }else if($value['eventtype']==5)
                {
                    $segundos = 604800;
                    $num_dias = (($endstamp-$startstamp)/$segundos)+1;//Sumo 1 para incluir la ultima semana
                    $num_dias = (int)$num_dias;
                }

                for($i=0; $i<$num_dias; $i++)
                {
                    $sFechaEvento = date('Y-m-d H:i:s', $iStartTimestamp);
                    $iStartTimestamp += $segundos;
                    if($sFechaEvento >= $actual_date_hour)
                    {
                        $arrEventos[] = array(
                                            "date"      =>  $sFechaEvento,
                                            "subject"   =>  $value['subject'],
                                            "call"      =>  $value['asterisk_call'],
                                            "id"        =>  $value['id']
                                        );
                    }
                }
            }else if($value['eventtype']==6)
            {
                $i=0;
                while($iStartTimestamp <= $endstamp)
                {
                    $sFechaEvento = date('Y-m-d H:i:s', $iStartTimestamp);
                    $iStartTimestamp = strtotime("+1 months", $iStartTimestamp);
                    if($sFechaEvento >= $actual_date_hour)
                    {
                        $arrEventos[] = array(
                                            "date"      =>  $sFechaEvento,
                                            "subject"   =>  $value['subject'],
                                            "call"      =>  $value['asterisk_call'],
                                            "id"        =>  $value['id']
                                        );
                    }
                    $i++;
                }
            }
        }

        if(count($arrEventos)<1)
            return $arrLang["You don't have events"];

        //Ordenamiento por fechas en orden descendente (antiguos primero)
        $fechas = array();
        //$horas  = array();
        foreach ($arrEventos as $llave => $valor)
            $fechas[$llave] = $valor['date'];
        array_multisort($fechas,SORT_ASC,$arrEventos);

        $i=0;
        while($i<count($arrEventos) && $i<$numRegs)
        {
            $temp  = "<a href='?menu=calendar&action=display&id=".$arrEventos[$i]["id"]."'>".$arrEventos[$i]["subject"]."</a>";
            $temp .= "<br />";
            $temp .= "Date: ";
            $temp .= $arrEventos[$i]['date'];
            $temp .= " - Call: ";
            $temp .= $arrEventos[$i]['call'];
            $temp .= "<br>";
            $data .= $temp;
            $i++;
        }

        return $data;
   }
}
?>