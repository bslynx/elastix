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

    function addCardParameter($data)
    {
        $queryInsert = $this->_DB->construirInsert('card', $data);
        $result = $this->_DB->genQuery($queryInsert);

        return $result;
    }

    function deleteEchoCanceller(){
        $query = "DELETE FROM echo_canceller";
        $result = $this->_DB->genQuery($query);
    }
    
    function deleteCardParameter(){
        $query = "DELETE FROM card";
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

    function getCardParameterById($id_card){
        $query = "SELECT * FROM card WHERE id_card=$id_card";

        $result=$this->_DB->getFirstRowQuery($query,true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
        
    }
/*
    function readFileSystemConfig()
    {
        $myFile='/etc/dahdi/system.conf';
        $fh = fopen($myFile, 'r');

        return $fh;
    }
*/
    private function saveChangeFileSystemConfig($text)
    {
        exec("sudo -u root chown asterisk.asterisk /etc/dahdi/system.conf");
        $fp = fopen('/etc/dahdi/system.conf', 'w');

        fwrite($fp, $text);
        fclose($fp);
        exec("sudo -u root chown root.root /etc/dahdi/system.conf");
    }


    function replaceEchoSystemConf($typePass, $typeNew, $numport, $typeCard){
        $FILE='/etc/dahdi/system.conf';
        exec("sudo -u root chown -R asterisk.asterisk /etc/dahdi/system.conf");
        $text = "";
        $fp = fopen($FILE,'r');
        $j=0;
        $line_concat="";
        $already="false";
        $enc="false";
        $find="false";
        $filled="false";
        while($line = fgets($fp, filesize($FILE)))
        {
            $getin="false";
            $find="false";
            if(eregi("echocanceller", $line)){
                $arrLine = preg_split('/[,]/',$line);
                $arrRang = preg_split('/[-]/',trim($arrLine[1]));//new

                if(count($arrLine)>2){
                    $arrRang=array();
                    for($i=0 ; $i< count($arrLine); $i++){
                        $arrRang[$i] = preg_split('/[-]/',trim($arrLine[$i]));
                    }
                    $lineConf =  preg_split('/[=]/',$arrRang['0'][0]);

                    if($typePass=="none" && $typeNew!="none"){
                        $min = $numport-1;
                        $max = $numport+1;
                        if($lineConf[1]==strtolower($typeNew)){                
                            $line_concat="".$arrRang['0'][0];
                            
                            for($i=1; $i<count($arrRang); $i++){
                                if($min==$arrRang[$i][1] || $max==$arrRang[$i][0]){
                                    if($min==$arrRang[$i][1]){
                                        $line_concat .= ",".$arrRang[$i][0]."-".$numport; 
                                    }else if($max==$arrRang[$i][0]){
                                        $line_concat .= ",".$numport."-".$arrRang[$i][1];
                                    }
                                }else{
                                    $line_concat .= ",".$arrRang[$i][0]."-".$arrRang[$i][1];
                                }
                            }
                            $line = $line_concat."\n";
                        }else{ 
                            for($i=1; $i<count($arrRang); $i++){
                                if($min==$arrRang[$i][1] || $max==$arrRang[$i][0]){
                                    $find="true";
                                }
                            }
                            if($find=="true"){
                                $line_concat = "echocanceller=".strtolower($typeNew).",".$numport."\n";
                                $line = $line."".$line_concat;
                            }
                        }
                       
                        $text .= $line;
                    }else{ 
                        if((strtolower($typePass) == strtolower($typeNew))){
                            $text .= $line;//si es el mismo al anterior queda igual
                        }
                        else{
                            $line_concat="".$arrRang['0'][0];//=>echocanceller=oslec
                            for($i=1; $i<count($arrRang); $i++){
                                if($numport > $arrRang[$i][0] && $numport < $arrRang[$i][1]){//Rangos
                                    $nmaxnew = $numport-1;
                                    $nminnew = $numport+1;
                                    if($nmaxnew == $arrRang[$i][0]){
                                        $line_alone = "\n".$arrRang['0'][0].",".$arrRang[$i][0];
                                        $line_concat .= ",".$nminnew."-".$arrRang[$i][1].$line_alone;
                                    }else if($nminnew == $arrRang[$i][1]){
                                        $line_alone = "\n".$arrRang['0'][0].",".$arrRang[$i][1];
                                        $line_concat .= ",".$arrRang[$i][0]."-".$nmaxnew.$line_alone;
                                    }else{
                                        $line_concat .= ",".$arrRang[$i][0]."-".$nmaxnew.",".$nminnew."-".$arrRang[$i][1];
                                    }
                                    $getin="true";
                                }else if(($numport==$arrRang[$i][0]) || ($numport==$arrRang[$i][1])){//Extremo
                                    if($numport==$arrRang[$i][0]){
                                        $nminnew = $numport+1;
                                        $line_concat .= ",".$nminnew."-".$arrRang[$i][1];       
                                        $getin="true";
                                    }else if($numport==$arrRang[$i][1]){
                                        $nmaxnew = $numport-1;
                                        $line_concat .= ",".$arrRang[$i][0]."-".$nmaxnew;
                                        $getin="true";
                                    }
                                }else{
                                    $line_concat .= ",".$arrRang[$i][0]."-".$arrRang[$i][1];
                                }
                            }
                            if($getin=="true"){
                                if($typeNew!="none")
                                    $line = $line_concat."\n"."echocanceller=".strtolower($typeNew).",".$numport."\n";
                                else
                                    $line = $line_concat."\n";
                            }else{ $line = $line_concat."\n"; }
                            $getin="false";
                            $text .= $line;
                        }
                    //fin
                    }
                }else if(count($arrRang)==2){//nuevo
                    $arrRang=array();
                    for($i=0 ; $i< count($arrLine); $i++){
                        $arrRang[$i] = preg_split('/[-]/',trim($arrLine[$i]));
                    }
                    $lineConf =  preg_split('/[=]/',$arrRang['0'][0]);

                    if($typePass=="none" && $typeNew!="none"){
                        $min = $numport-1;
                        $max = $numport+1;
                        if($lineConf[1]==strtolower($typeNew)){              
                            $line_concat="".$arrRang['0'][0];
                            
                            for($i=1; $i<count($arrRang); $i++){
                                if($min==$arrRang[$i][1] || $max==$arrRang[$i][0]){
                                    if($min==$arrRang[$i][1]){
                                        $line_concat .= ",".$arrRang[$i][0]."-".$numport; 
                                    }else if($max==$arrRang[$i][0]){
                                        $line_concat .= ",".$numport."-".$arrRang[$i][1];
                                    }
                                }else{
                                    $line_concat .= ",".$arrRang[$i][0]."-".$arrRang[$i][1];
                                }
                            }
                            $line = $line_concat."\n";
                        }else{ 
                            for($i=1; $i<count($arrRang); $i++){
                                if($min==$arrRang[$i][1] || $max==$arrRang[$i][0]){
                                    $find="true";
                                }
                            }
                            if($find=="true"){
                                $line_concat = "echocanceller=".strtolower($typeNew).",".$numport."\n";
                                $line = $line."".$line_concat;
                            }
                        }
                       
                        $text .= $line;
                    }else{ 
                        if((strtolower($typePass) == strtolower($typeNew))){
                            $text .= $line;//si es el mismo al anterior queda igual
                        }
                        else{
                            $line_concat="".$arrRang['0'][0];//=>echocanceller=oslec
                            for($i=1; $i<count($arrRang); $i++){
                                if($numport > $arrRang[$i][0] && $numport < $arrRang[$i][1]){//Rangos
                                    $nmaxnew = $numport-1;
                                    $nminnew = $numport+1;
                                    if($nmaxnew == $arrRang[$i][0]){
                                        $line_alone = "\n".$arrRang['0'][0].",".$arrRang[$i][0];
                                        $line_concat .= ",".$nminnew."-".$arrRang[$i][1].$line_alone;
                                    }else if($nminnew == $arrRang[$i][1]){
                                        $line_alone = "\n".$arrRang['0'][0].",".$arrRang[$i][1];
                                        $line_concat .= ",".$arrRang[$i][0]."-".$nmaxnew.$line_alone;
                                    }else{
                                        $line_concat .= ",".$arrRang[$i][0]."-".$nmaxnew.",".$nminnew."-".$arrRang[$i][1];
                                    }
                                    $getin="true";
                                }else if(($numport==$arrRang[$i][0]) || ($numport==$arrRang[$i][1])){//Extremo
                                    if($numport==$arrRang[$i][0]){
                                        $nminnew = $numport+1;
                                        $line_concat .= ",".$nminnew."-".$arrRang[$i][1];       
                                        $getin="true";
                                    }else if($numport==$arrRang[$i][1]){
                                        $nmaxnew = $numport-1;
                                        $line_concat .= ",".$arrRang[$i][0]."-".$nmaxnew;
                                        $getin="true";
                                    }
                                }else{
                                    $line_concat .= ",".$arrRang[$i][0]."-".$arrRang[$i][1];
                                }
                            }
                            if($getin=="true"){
                                if($typeNew!="none")
                                    $line = $line_concat."\n"."echocanceller=".strtolower($typeNew).",".$numport."\n";
                                else
                                    $line = $line_concat."\n";
                            }else{ $line = $line_concat."\n"; }
                            $getin="false";
                            $text .= $line;
                        }
                    //fin
                    }
                   
                }/*else if(!eregi($numport, $line) && ($typePass=="none" && $typeNew!="none")){
                    $enc="true";
                    $text .= $line;
                }*/else{
                    $num_neigh = $numport+1;
                    $num_neigh2 = $numport-1;//NEW
                    if(eregi($numport, $line) && $already=="false"){
                        $already="true";
                        if($typePass=="none" && $typeNew!="none"){ //para no repetir el 2 con 25
                            $text .= $line;
                        }else if($typeNew!="none"){
                            $line = str_ireplace(strtolower($typePass), strtolower($typeNew), $line);
                            $text .= $line;
                        }//caso contrario no pone el ($text .= $line;) xq se omite
                    }else if(($typePass=="none" && $typeNew!="none") && (eregi($num_neigh1, $line) || eregi($num_neigh2, $line)) && $filled=="false"){
                        $filled="true";
                        $line_concat = "echocanceller=".strtolower($typeNew).",".$numport."\n";
                        if(eregi($num_neigh1, $line)) $line = $line_concat."".$line;
                        else $line = $line."".$line_concat;
                        $text .= $line;
                    }else{
                        $text .= $line;
                    }
                }

            }else{
                $text .= $line;
            }
            $line_concat="";
        }
        if($enc="false" && ($typePass=="none" && $typeNew!="none")){
            $line_concat = "\nechocanceller=".strtolower($typeNew).",".$numport."\n";
            $text .= $line_concat;
        }

        $this->saveChangeFileSystemConfig($text);
        fclose($fp);
        //exec("sudo -u root chown -R root.root /etc/dahdi/system.conf");
        exec("sudo -u root service dahdi restart");
    }

    function updateEchoCancellerCard($id_card, $num_port, $echocanceller)
    {
        $data   = array($echocanceller, $num_port, $id_card);
        $query  = "UPDATE echo_canceller SET echocanceller = ? WHERE num_port = ? AND id_card = ? ";
        $result = $this->_DB->genQuery($query, $data);

        if($result == FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return FALSE;
        }else
            return TRUE;
    }


}
?>