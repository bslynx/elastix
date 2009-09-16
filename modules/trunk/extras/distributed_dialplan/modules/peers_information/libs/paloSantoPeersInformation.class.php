<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.4-1                                               |
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
  $Id: paloSantoPeersInformation.class.php,v 1.1 2008-08-03 11:08:42 Andres Flores aflores@palosanto.com Exp $ */
class paloSantoPeersInformation {
    var $_DB;
    var $errMsg;

    function paloSantoPeersInformation(&$pDB)
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

    /*HERE YOUR FUNCTIONS*/
	function addInformationPeer($dataPeer)
    {
        $arrTmp = array();
        foreach($dataPeer as $key => $value)
           $arrTmp[$key] = $this->_DB->DBCAMPO($value);   

        $queryInsert = $this->_DB->construirInsert('peer', $arrTmp);
        $result = $this->_DB->genQuery($queryInsert);
        return $result;
    }

    function getNameCertificate($mac)
    {
        $root_certicate = "/var/lib/asterisk/keys";
        $macCertificate = "CER".str_replace(":","",$mac);
        if(file_exists("$root_certicate/$macCertificate.pub")){
           return $macCertificate;
        }else 
           return "No Found";
      
    }

    function  addInfoRequest($mac, $ip, $company, $comment, $certificate,$key)
    {
        $ip_request = $this->_DB->DBCAMPO($ip);
        $company_request = $this->_DB->DBCAMPO($company);
        $comment_request = $this->_DB->DBCAMPO($comment);
        $mac_request = $this->_DB->DBCAMPO($mac);
        $certificate_request = $this->_DB->DBCAMPO($certificate);
        $key_request = $this->_DB->DBCAMPO($key);

        $query = "INSERT INTO peer(mac, model, host, inkey , outkey, status, his_status, key, comment, company)VALUES($mac_request,'symmetric',$ip_request,$certificate_request,'','Requesting connection','waiting response',$key_request,$comment_request, $company_request)";

        $result=$this->_DB->fetchTable($query, true);
        if($result==FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;      
     
    }

   function UpdateOutKey($certificate ,$peerId)
   {
      $local_outkey = $this->_DB->DBCAMPO($certificate);
      $query = "UPDATE peer SET outkey=$local_outkey where id=$peerId";
      $result=$this->_DB->fetchTable($query, true);
      if($result==FALSE)
      {
          $this->errMsg = $this->_DB->errMsg;
          return array();
      }
      return $result;  
   }

   function hostExist($mac)
   {
      $exist_mac = $this->_DB->DBCAMPO($mac);
      $query = "SELECT host FROM peer where mac=$exist_mac";
      $result=$this->_DB->getFirstRowQuery($query, true);
      if($result==FALSE)
      {
         $this->errMsg = $this->_DB->errMsg;
         return array();
      }
      return $result;

   }
   

    function uploadInformationPeer($data, $where)
    {

        $arrTmp = array();
        foreach($data as $key => $value)
           $arrTmp[$key] = $this->_DB->DBCAMPO($value);   

        $queryInsert = $this->_DB->construirUpdate("peer", $arrTmp, "id=$where");
        $result = $this->_DB->genQuery($queryInsert);
        return $result;
    }

	function addInformationParameter($dataParameter, $id)
    {
       
       $arrTmp = array();
       $result = "";
       foreach($dataParameter as $key => $value)
          $arrTmp[$key] = $this->_DB->DBCAMPO($value); 
       
       foreach($arrTmp as $name => $value)
       {
         $queryInsert = "INSERT INTO parameter(name, value, id_peer) VALUES($name, $value, $id)";
         $result = $this->_DB->genQuery($queryInsert);
         //hay que manejar el error en el caso de que no pueda insertar

       }
       return $result;
    }

   function createPeerParameter()
   {
     $dataPeer = array();
     $dataParameter = array();
     //$dataParameter["'precache'"]  = "outbound";
     $dataParameter["'include'"]   = "priv";
     $dataParameter["'permit'"]    = "priv";
     $dataParameter["'quality'"]   = "yes";
     $dataParameter["'order'"]   = "primary";
     return $dataParameter;  

   }

   function asteriskReload()
   { 
     $root = "/usr/sbin";
     exec("$root/asterisk -rx 'reload'",$arrReg, $arrFlag);
     /*if($arrFlag == 0)
        return true;
     else
        return false;*/
     return true;
   }

     function obtainForeignKey($idPeer)
    {
        $query = "SELECT inkey FROM peer WHERE id=$idPeer";
        $result=$this->_DB->getFirstRowQuery($query, true);
        if($result==FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result['inkey'];
    }

    function deleteInformationPeer($idPeer)
    {
        // obtain the foreign public key
        $nameKey = $this->obtainForeignKey($idPeer);
        $root = " /var/lib/asterisk/keys/".$nameKey.'.pub';
        exec("rm -rf $root");
        $query = "DELETE FROM peer WHERE id=$idPeer";
        $result = $this->_DB->genQuery($query);
        return $result;
    }

	function deleteInformationParameter($idPeer)
    {
        $query = "DELETE FROM parameter WHERE id_peer=$idPeer";
        $result = $this->_DB->genQuery($query);
        return $result;
    }

    function getHostStatus($host)
    {
      $host_remote = $this->_DB->DBCAMPO($host);
      $query = "SELECT * FROM peer where host=$host_remote and (status='waiting response' or status='request accepted' or status='connected' or status='disconnected' or status='request delete)" ;
      $result=$this->_DB->getFirstRowQuery($query, true);
      if($result==FALSE)
      {
         $this->errMsg = $this->_DB->errMsg;
         return false;
      }
      return true;   
    }

	function getIdPeer($MAC=null)
    {
      $where = "";
      $tmpMac = "";
      $query = "SELECT id FROM peer";
      if($MAC != null){
         $tmpMac =  $this->_DB->DBCAMPO($MAC);
         $query .= " WHERE mac=$tmpMac";
      }

      $result=$this->_DB->getFirstRowQuery($query, true);
      if($result==FALSE)
      {
         $this->errMsg = $this->_DB->errMsg;
         return array();
      }
      return $result;
      
    
    }


    //Funcion que crea el archivo dundi_peers_custom_elastix.conf
    function createFileDPCE($peers,$arrLang)
    {
       $dundi_file = "/etc/asterisk/dundi_peers_custom_elastix.conf";
       $fh = fopen($dundi_file, "w+");
       if($fh){
         if(fwrite($fh, $peers) == false){
		   $this->errMsg = $arrLang["Unabled write file"];
           fclose($fh);
           return false;
         }
       }else{
          $this->errMsg = $arrLang["Unabled open file"];
          return false;
       }
       return true;        
    }


    function ObtainNumPeersInformation()
    {
        //Here your implementation
        $query   = "SELECT COUNT(*) FROM peer";
        
        $result=$this->_DB->getFirstRowQuery($query);
        if($result==FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return 0;
        }
        return $result[0];
    }

    function ObtainPeersInformation($limit=null, $offset=null, $field_pattern=null)
    {
        //Here your implementation
        $query   = "SELECT * FROM peer";
        
        $result=$this->_DB->fetchTable($query, true);
        if($result==FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    function StatusDisconnect($peerId)
    {

        $query = "UPDATE peer SET status ='disconnected' WHERE id=$peerId";
        $result=$this->_DB->fetchTable($query, true);
        if($result==FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;      
    }

    function StatusConnect($peerId)
    {
        $query = "UPDATE peer SET status ='connected' WHERE id=$peerId";
        $result=$this->_DB->fetchTable($query, true);
        if($result==FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;      
    }

    function hisStatusConnect($ip_answer, $action)
    {
        $his_status = "";
        if($action == 5)
            $his_status = "connected";
        else if($action == 6)
                    $his_status = "disconnected";

        $query = "UPDATE peer SET his_status ='$his_status' WHERE host='$ip_answer'";
        $result=$this->_DB->fetchTable($query, true);
        if($result==FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;      
    }

   function UpdateInfoRequest($ip, $mac, $key, $company, $comment)
   {
     $ip_answer = $this->_DB->DBCAMPO($ip);
     $mac_answer = $this->_DB->DBCAMPO($mac);
     $key_answer = $this->_DB->DBCAMPO($key);
     $company_answer = $this->_DB->DBCAMPO($company);
     $comment_answer = $this->_DB->DBCAMPO($comment);
     $macCertificate = "CER".str_replace(":","",$mac);
     $query = "UPDATE peer SET mac=$mac_answer, inkey='$macCertificate', status='request accepted',his_status='disconnected', key=$key_answer, comment=$comment_answer, company=$company_answer where host=$ip_answer and status='waiting response'";
     
     $result=$this->_DB->fetchTable($query, true);
        if($result==FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;

   }

   function UpdateInfoForReject($ip_answer, $action)
   {
      $status = "";
      $his_status = "";
      if($action == 3){
         $status = "request reject";
         $his_status = "connection rejected";
      }
      else{ if($action == 4){
                $status = "request delete";
                $his_status = "connection deleted";
           }
      }
      $query = "UPDATE peer SET status='$status', his_status='$his_status' where host='$ip_answer' and status='waiting response' or status='request accepted' or status='connected' or status='Requesting connection'";

      $result=$this->_DB->fetchTable($query, true);
      if($result==FALSE)
      {
          $this->errMsg = $this->_DB->errMsg;
          return array();
      }
      return $result;
   }
   //crea la clave publica del peer del quien esta solicitando la coneccion
   function createKeyPubServer($key_answer, $mac_answer)
   {
     $pub_key = "CER".str_replace(":","",$mac_answer);
 
     $fh = fopen("/var/lib/asterisk/keys/$pub_key.pub", "w+");
     if($fh){
        if(fwrite($fh, "$key_answer") == false){
           echo "Unabled write file";
           fclose($fh);           
         }
     }else
          echo "Unabled open file";     

   }

    function GenKeyPub($company)
    {
      $root = "/var/lib/asterisk/keys";
      exec("/usr/sbin/astgenkey -q -n $root/$company",$arrReg,$arrFlag);
      if($arrFlag == 0)
        return true;
      else
        return false ;
    }

 
    function ObtainPeersDataById($id)
    {
        //Here your implementation
        $query   = "SELECT * FROM peer WHERE id=$id";
        
        $result=$this->_DB->getFirstRowQuery($query, true);
        if($result==FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    function ObtainPeersParametersById($id)
    {
        //Here your implementation
        $query   = "SELECT name, value FROM parameter WHERE id_peer=$id";
        $result=$this->_DB->fetchTable($query, true);
        if($result==FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }
   function AddServerRequest($ip, $mac, $status, $certificate)
   {
      $host_remote = $this->_DB->DBCAMPO($ip);
      $status_remote = $this->_DB->DBCAMPO($status);
      $name_certificate_local = $this->_DB->DBCAMPO($certificate);
      $query = "INSERT INTO peer(mac,model,host,inkey,outkey,status,his_status)VALUES('','symmetric',$host_remote,'',$name_certificate_local,$status_remote,'Requesting connection')";
      $result=$this->_DB->fetchTable($query, true);
        if($result==FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
   }

}
?>
