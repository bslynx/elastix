<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.0-1                                               |
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
  $Id: paloSantoConfEcho.class.php,v 1.1 2009-09-14 10:12:09 ecueva onavarrete@palosanto.com Exp $ */
class paloSantoConfEcho {
    var $_DB;
    var $errMsg;

    function paloSantoConfEcho(&$pDB)
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

    function addEchoCanceller($data)
    {
        $queryInsert = $this->_DB->construirInsert('echo_canceller', $data);
        $result = $this->_DB->genQuery($queryInsert);

        return $result;
    }

    function deleteEchoCanceller(){
        $query = "DELETE FROM echo_canceller";
        $result = $this->_DB->genQuery($query);
    }

    function getEchoCancellerByIdCard($id_card)
    {
        $query = "SELECT num_port, name_port, echocanceller FROM echo_canceller WHERE id_card=$id_card";
        
        $result=$this->_DB->fetchTable($query, true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        //$count=0;
        $arrEchoCan = array();
        foreach($result as $key => $value){
            //$count++;
            $arrEchoCan[$value['num_port']]['name_port'] = $value['name_port'];
            $arrEchoCan[$value['num_port']]['type_echo'] = $value['echocanceller'];
        }
        return $arrEchoCan;
    }

    function getEchoCancellerByIdCard2($id_card)
    {
        $query = "SELECT num_port, name_port, echocanceller FROM echo_canceller WHERE id_card=$id_card";
        
        $result=$this->_DB->fetchTable($query, true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }

        return $result;
    }

//     function updateEchoCanceller($data,$where)
//     {
//         exec("echo 'Entro' > /tmp/oscar");
//         $queryUpdate = $this->_DB->construirUpdate('echo_canceller', $data, $where);
//         $result = $this->_DB->genQuery($queryUpdate);
// 
//         return $result;
//     }
// 
//     function updateEchoCanceller2($type, $num)
//     {
//         $query = "update echo_canceller set echocanceller='$type' where num_port='$num'";
//         exec("echo '$query' > /tmp/oscar");
//         $result=$this->_DB->genQuery($query);
//         if($result==FALSE){
//             $this->errMsg = $this->_DB2->errMsg;
//             return $message = "There are someone error, Please try again or report at the root";
//         }
//         return $message= "Saved Successful!";
//     }

    function readFileSystemConfig()
    {
        $myFile='/etc/dahdi/system.conf';
        $fh = fopen($myFile, 'r');

        return $fh;
    }

    function saveChangeFileSystemConfig($text)
    {
        $fp = fopen('/etc/dahdi/system.conf', 'w');

        fwrite($fp, $text);
        fclose($fp);
    }


    function replaceEchoSystemConf($typePass, $typeNew, $numport){
        $FILE='/etc/dahdi/system.conf';
        exec("sudo -u root chown -R asterisk.asterisk /etc/dahdi/system.conf");
        $text = "";
        $fp = fopen($FILE,'r');
        $i=0;
        while ($line = fgets($fp, filesize($FILE)))
        {
            if(eregi("echocanceller", $line)){
                $arrLine = split('[,]',$line);
                if(count($arrLine)>2){
                    $arrRang1 = split('[-]',trim($arrLine[1]));
                    $arrRang2 = split('[-]',trim($arrLine[2]));
                    //exec("echo 'Dato: ".trim($arrRang1[1])."' > /tmp/oscar");
                    if(($numport > $arrRang1[0] && $numport < $arrRang1[1]) || ($numport > $arrRang2[0] && $numport < $arrRang2[1])){
                        //exec("echo '$arrRang1[0]' > /tmp/oscar");
                        if(eregi($typeNew, $line))
                            $text .= $line;//si es el mismo al anterior que igual
                        else{
                            $nmaxnew = $numport-1;
                            $nminnew = $numport+1;
//                             if($numport==$arrang1[0])
//                             if($numport==$arrRang1[1])
//                             if($numport==$arrRang2[0])
//                             if($numport==$arrRang2[1])
                            if($numport >= $arrRang1[0] && $numport <= $arrRang1[1]){
                                exec("echo 'Rango1 $numport' > /tmp/oscar");
                                $linechange1 = "echocanceller=".$typePass.",".$arrRang1[0]."-".$nmaxnew.",".$nminnew."-".$arrRang1[1].",".$arrRang2[0]."-".$arrRang2[1]."\n";
                            }if($numport >= $arrRang2[0] && $numport <= $arrRang2[1]){
                                exec("echo 'Rango2 $numport' > /tmp/oscar");
                                $linechange2 = "echocanceller=".$typePass.",".$arrRang1[0]."-".$arrRang1[1].",".$arrRang2[0]."-".$nmaxnew.",".$nminnew."-".$arrRang2[1]."\n";
                            }
                            $line = $linechange1."echocanceller=".$typeNew.",".$numport;
                            $text .= $line;
                        }
                    }else{
                        $text .= $line;
                    }
                }else{
                    if(eregi($numport, $line)){
                        $line = str_ireplace(strtolower($typePass), strtolower($typeNew), $line);
                        $text .= $line;
                    }else{
                        $text .= $line;
                    }
                }
      
            }else{
                $text .= $line;
            }
        }
        //$this->saveChangeFileSystemConfig($text);
        fclose($fp);
        exec("sudo -u root chown -R root.root /etc/dahdi/system.conf");
        //exec("sudo -u root service dahdi restart");
    }

}
?>