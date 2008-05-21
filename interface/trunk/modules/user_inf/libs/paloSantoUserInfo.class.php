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

require_once("libs/paloSantoACL.class.php");

class paloSantoUserInfo {
    var $_DB;
    var $numRegs;
    var $extension;
    var $errMsg;

    function paloSantoUserInfo(&$pDB,$calls=5)
    {
	$this->numRegs = $calls;

	$dbAcl= new paloDB("sqlite3:////var/www/db/acl.db");
	
	$pACL= new paloACL($dbAcl);

	$this->extension = $pACL->getUserExtension($_SESSION["elastix_user"]);
	$this->errMsg.=$pACL->errMsg;

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

   function getSystemStatus(){
	global $arrLang;
	
	$dbEmails = new paloDB("sqlite3:////var/www/db/email.db");

	$email = "afigueroa.dominio.org";
	$passw = "afigueroa";
	
	$imap = imap_open("{localhost:143}",$email,$passw);

	if (!$imap)
		return $arrLang["Imap: Connection error"];
	
	$quotainfo = imap_get_quotaroot($imap,"INBOX");

	imap_close($imap);

	$content = $arrLang["Quota asigned"]." $quotainfo[limit] KB<br>".$arrLang["Quota Used"]." $quotainfo[usage] KB<br>".$arrLang["Quota free space"]." ". (string)($quotainfo['limit'] - $quotainfo['usage']) . " KB";

	return $content;
   }

   function getMails(){
	global $arrLang;
	
	$counter	= 0;
	$email		= "afigueroa.dominio.org";
	$passw		= "afigueroa";
	
	$imap = imap_open("{localhost:143}INBOX",$email,$passw);

	if(!$imap)
		return $arrLang["Imap: Connection error"];

	$tmp = imap_check($imap);

	if($tmp->Nmsgs==0)
		return $arrLang["You don't recibed emails"];

	$result = imap_fetch_overview($imap,"1:{$tmp->Nmsgs}",0);
	
	$mails = array();

	foreach ($result as $overview) {
		$mails[] = array("seen"=>$overview->seen,
				 "recent"=>$overview->recent,
				 "answered"=>$overview->answered,
				 "date"=>$overview->date,
				 "from"=>$overview->from,
				 "subject"=>$overview->subject);
	}
	
	imap_close($imap);
	
	$mails = array_slice($mails,-$this->numRegs,$this->numRegs);
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
   
   function getVoiceMails(){
	global $arrLang;
	$exists = false;
	$count = 0;

	if(is_null($this->extension))
                return $arrLang["You haven't extension"];

	$voicePath = "/var/spool/asterisk/voicemail/default/$this->extension/INBOX";

	$exists = file_exists($voicePath);
	
	if($exists)
		exec("ls -t $voicePath/*txt | head -n $this->numRegs",$result);
	
	$count = count($result);
	
	if(!$exists or ($count == 0))
		return $arrLAng["You don't recibed voicemails"];
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

   function getLastFaxes(){
	global $arrLang;

	$dbFax = new paloDB("sqlite3:////var/www/db/fax.db");

	if(is_null($this->extension))
                return $arrLang["You haven't extension"];
	
	$result = $dbFax->fetchTable("select a.pdf_file,a.company_name,a.date from info_fax_recvq a,fax b where b.extension='".$this->extension."' and b.id=a.fax_destiny_id order by a.id desc limit $this->numRegs");

	if(!$result)
		return $arrLang["Query error"];

	if(count($result)==0)
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
   
   function getLastCalls(){
	global $arrLang;

	if(is_null($this->extension))
		return $arrLang["You haven't extension"];
	
	$result = $this->_DB->fetchTable("select calldate,src,duration,disposition from cdr where dst='".$this->extension."'  order by calldate desc limit $this->numRegs");
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
}
?>
