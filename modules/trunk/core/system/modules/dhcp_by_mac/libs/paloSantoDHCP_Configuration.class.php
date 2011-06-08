<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.6-12                                               |
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
  $Id: paloSantoDHCP_Configuration.class.php,v 1.1 2009-11-12 04:11:04 Oscar Navarrete onavarrete.palosanto.com Exp $ */
class paloSantoDHCP_Configuration {
    var $_DB;
    var $errMsg;

    function paloSantoDHCP_Configuration(&$pDB)
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

    function ObtainNumDHCP_Configuration($filter_field, $filter_value)
    {
        //Here your implementation
        $where = "";
        if(isset($filter_field) & $filter_field !="")
            $where = "where $filter_field like '$filter_value%'";

        $query   = "SELECT COUNT(*) FROM dhcp_conf $where";

        $result=$this->_DB->getFirstRowQuery($query);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return 0;
        }
        return $result[0];
    }

    function ObtainDHCP_Configuration($limit, $offset, $filter_field, $filter_value)
    {
        //Here your implementation
        $where = "";
        if(isset($filter_field) & $filter_field !="")
            $where = "where $filter_field like '$filter_value%'";

        $query = "SELECT * FROM dhcp_conf $where LIMIT $limit OFFSET $offset";

        $result=$this->_DB->fetchTable($query, true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    private function updateChangeFileDHCP_Conf($text) {
        exec("sudo chown asterisk.asterisk /etc/dhcpd.conf");
        $fp = fopen('/etc/dhcpd.conf', 'w');
        
        fwrite($fp, $text);
        exec("sudo -u root chown root.root /etc/dhcpd.conf");
        fclose($fp);
    }

    private function addDhcpConfigDB($data) {
        $queryInsert = $this->_DB->construirInsert('dhcp_conf', $data);
        $result = $this->_DB->genQuery($queryInsert);

        return $result;
    }

    function saveFileDhcpConfig($pDB){
        //exec("sudo chown asterisk.asterisk /etc/dhcpd.conf");
        $FILE='/etc/dhcpd.conf';
        $query = "DELETE FROM dhcp_conf";
        $this->_DB->genQuery($query);
        $count = 1;
        $i = 0;
        $data = array();
        $fp = fopen($FILE, 'r');

        while($line = fgets($fp, filesize($FILE)))
        {
            if(eregi("host", $line)) {
                if(ereg("^[[:space:]]*([a-z]+)[[:space:]]([a-zA-Z0-9_]+)", $line, $arrReg)){
                    $data['hostname'] = $pDB->DBCAMPO($arrReg[2]);
                }else $data['hostname'] = "";
                $i++;
            }elseif(eregi("hardware", $line)) {
                if(ereg("^[[:space:]]*([a-z]+)[[:space:]]([a-z]+)[[:space:]]([a-zA-Z0-9:]+)", $line, $arrReg)){
                    $data['macaddress'] = $pDB->DBCAMPO($arrReg[3]);
                }else $data['macaddress'] = "";
                $i++;
            }elseif(eregi("fixed", $line)) {
                if(ereg("^[[:space:]]*([a-z-]+)[[:space:]]([0-9.]+)", $line, $arrReg)){
                    $data['ipaddress'] = $pDB->DBCAMPO($arrReg[2]);
                }else $data['ipaddress'] = "";
                //$count++;
                $i++;
            }
            if($i==3){
                $result = $this->addDhcpConfigDB($data);
                if($result == false){
                    $this->errMsg = $this->_DB->errMsg;
                    return false;
                }
                $data = array();
                $i=0;
            }
        }
        //exec("sudo -u root chown root.root /etc/dhcpd.conf");
        fclose($fp);
        return $data;
    }


    function updateFileDhcpConfig($arrDhcpPost, $arrDhcpDB){
        $FILE='/etc/dhcpd.conf';
        $text = "";
        $fp = fopen($FILE,'r');

        $found_host="false";
        $found_hardware="false";
        $found_fixed="false";
        while($line = fgets($fp, filesize($FILE)))
        {
            if(eregi("host", $line) && $found_host=="false"){
                $line_mod = str_ireplace($arrDhcpDB['hostname'], $arrDhcpPost['hostname'], $line);
                if($line!=$line_mod){
                    $found_host="true";
                    $text .= $line_mod;
                }else
                    $text .= $line;
                
            }elseif(eregi("hardware", $line) && $found_hardware=="false"){
                $line_mod = str_ireplace($arrDhcpDB['macaddress'], $arrDhcpPost['macaddress'], $line);
                if($line!=$line_mod){
                    $found_hardware="true";
                    $text .= $line_mod;
                }else
                    $text .= $line;
                
            }elseif(eregi("fixed", $line) && $found_fixed=="false"){
                $line_mod = str_ireplace($arrDhcpDB['ipaddress'], $arrDhcpPost['ipaddress'], $line);
                if($line!=$line_mod){
                    $found_fixed="true";
                    $text .= $line_mod;
                }else
                    $text .= $line;
                
            }else
                $text .= $line;
        }
        $this->updateChangeFileDHCP_Conf($text);
        fclose($fp);
    }

    function addNewDhcpConfig($arrDhcpPost, $numDhcpConf){
        $FILE='/etc/dhcpd.conf';
        $text = "";
        $fp = fopen($FILE,'r');
        $text_added = "        }";
        
        $text_added .="\n";
        $text_added .="        host ".$arrDhcpPost['hostname']." {\n";
        $text_added .="                hardware ethernet ".$arrDhcpPost['macaddress'].";\n";
        $text_added .="                fixed-address ".$arrDhcpPost['ipaddress'].";\n";
        
        $count = 0;
        while($line = fgets($fp, filesize($FILE)))
        {
            if(eregi("host", $line)){
                $count++;
                $text .= $line;
            }else if(eregi("fixed", $line) && $count==$numDhcpConf) {
                $line .= $text_added;
                $text .= $line;
            }else {
                $text .= $line;
            }
        }
        $this->updateChangeFileDHCP_Conf($text);
        fclose($fp);
    }

    
    function getDuplicateDhcpConfig($arrDhcpPost){
        $FILE='/etc/dhcpd.conf';
        $fp = fopen($FILE,'r');
        $arrValidate = array();
//         $arrValidate['hostname']=false;
        $arrValidate['macaddress']=false;
        $arrValidate['ipaddress']=false; 
        $count = 0;
        while($line = fgets($fp, filesize($FILE)))
        {
            /*if(eregi("host", $line)) {
                if(eregi($arrDhcpPost['hostname'], $line)) $arrValidate['hostname']=true;
                $count++;
            }else */if(eregi("hardware", $line)) { 
                if(eregi($arrDhcpPost['macaddress'], $line)) $arrValidate['macaddress']=true;
                $count++;
            }else if(eregi("fixed", $line)) { 
                if(eregi($arrDhcpPost['ipaddress'], $line)) $arrValidate['ipaddress']=true;
                $count++;
            }

            //if($count==3){
            if($count==2){
                //if($arrValidate['hostname'] || $arrValidate['macaddress'] || $arrValidate['ipaddress']) break;
                if($arrValidate['macaddress'] || $arrValidate['ipaddress']) break;
                $count=0;
            }
        }
        fclose($fp);
        return $arrValidate;
    }

    function valitadeDuplicateDhcpConfig2($arrDhcpPost){
        $FILE='/etc/dhcpd.conf';
        $fp = fopen($FILE,'r');
        $exist_anyone=false;

        while($line = fgets($fp, filesize($FILE)))
        {
            /*if(eregi("host", $line) && eregi($arrDhcpPost['hostname'], $line)) {
                $exist_anyone=true;
                break;
            }*/if(eregi("hardware", $line) && eregi($arrDhcpPost['macaddress'], $line)) {
                $exist_anyone=true;
                break;
            }if(eregi("fixed", $line) && eregi($arrDhcpPost['ipaddress'], $line)) {
                $exist_anyone=true;
                break;
            }
        }
        fclose($fp);
        return $exist_anyone;
    }

    function deleteDhcpConfig($arrDhcpDB){
        $FILE='/etc/dhcpd.conf';
        $text = "";
        $fp = fopen($FILE,'r');
        $count = 0;        

        while($line = fgets($fp, filesize($FILE)))
        {
            if(eregi("host", $line)) {
                if(ereg("^[[:space:]]*([a-z]+)[[:space:]]([a-zA-Z0-9_]+)", $line, $arrReg)){
                    if($arrDhcpDB['hostname'] == $arrReg[2])
                        $count++;
                    else $text .= $line;
                }
            }elseif(eregi("hardware", $line)) {
                if(ereg("^[[:space:]]*([a-z]+)[[:space:]]([a-z]+)[[:space:]]([a-zA-Z0-9:]+)", $line, $arrReg)){
                    if($arrDhcpDB['macaddress'] == $arrReg[3])
                        $count++;
                    else $text .= $line;
                }
            }elseif(eregi("fixed", $line)) {
                if(ereg("^[[:space:]]*([a-z-]+)[[:space:]]([0-9.]+)", $line, $arrReg)){
                    if($arrDhcpDB['ipaddress'] == $arrReg[2])
                        $count++;
                    else $text .= $line;
                }
                
            }elseif($count==3) {
                $count=0;
            }else {
                $text .= $line;
            }
        }
        $this->updateChangeFileDHCP_Conf($text);
        fclose($fp);
    }

    function getDhcpConfigById($id)
    {
        $query   = "SELECT * FROM dhcp_conf ";
        $strWhere = "id=$id";

        // Clausula WHERE aqui
        if(!empty($strWhere)) $query .= "WHERE $strWhere ";

        $result=$this->_DB->getFirstRowQuery($query, true);
        return $result;
    }

}


?>