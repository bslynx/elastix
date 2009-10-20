<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.5.2-2                                               |
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
  $Id: paloSantoVoIPProvider.class.php,v 1.1 2009-09-29 05:09:50 Oscar Navarrete onavarrete@palosanto.com Exp $ */
class paloSantoVoIPProvider {
    var $_DB;
    var $errMsg;

    function paloSantoVoIPProvider(&$pDB)
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

    function getNumVoIPProvider($filter_field, $filter_value)
    {
        $where = "";
        if(isset($filter_field) & $filter_field !="")
            $where = "where $filter_field like '$filter_value%'";

        $query   = "SELECT COUNT(*) FROM trunk $where";

        $result=$this->_DB->getFirstRowQuery($query);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return 0;
        }
        return $result[0];
    }

    function getVoIPProvider($limit, $offset, $filter_field, $filter_value)
    {
        $where = "";
        if(isset($filter_field) & $filter_field !="")
            $where = "where $filter_field like '$filter_value%'";

        $query   = "SELECT * FROM trunk $where LIMIT $limit OFFSET $offset";

        $result=$this->_DB->fetchTable($query, true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    function getVoIPProviderById($id)
    {
        $query = "SELECT * FROM trunk WHERE id=$id";

        $result=$this->_DB->getFirstRowQuery($query,true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }

    function getVoIPProviderByProvider($id)
    {
        $query = "SELECT t.name, t.username, t.password, a.* FROM trunk t INNER join attribute a ON t.id=a.id_trunk WHERE t.id_provider=$id";

        $result=$this->_DB->getFirstRowQuery($query,true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }

    function getConfigByType($type)
    {
        $textConfig = "";
        if($type=="net2phone"){
            $textConfig .= "username=4332252819\n";
            $textConfig .= "type=peer\n";
            $textConfig .= "secret=2395\n";
            $textConfig .= "qualify=yes\n";
            $textConfig .= "insecure=very\n";
            $textConfig .= "host=ippbx.net2phone.com\n";
            $textConfig .= "context=from-pstn\n";
            $textConfig .= "canreinvite=no";

        }else if($type=="to_camundanet"){
            $textConfig .= "username=5412303\n";
            $textConfig .= "type=friend\n";
            $textConfig .= "secret=elastix5545\n";
            $textConfig .= "qualify=yes\n";
            $textConfig .= "insecure=very\n";
            $textConfig .= "host=sip.camundanet.com\n";
            $textConfig .= "fromuser=5412303\n";
            $textConfig .= "fromdomain=camundanet.com\n";
            $textConfig .= "dtmfmode=rfc2833\n";
            $textConfig .= "disallow=all\n";
            $textConfig .= "context=from-pstn\n";
            $textConfig .= "allow=gsm";

        }else if($type=="vitelity"){
            $textConfig .= "username=elastix\n";
            $textConfig .= "type=friend\n";
            $textConfig .= "trustrpid=yes\n";
            $textConfig .= "sendrpid=yes\n";
            $textConfig .= "secret=06E1AsT1X22\n";
            $textConfig .= "qualify=yes\n";
            $textConfig .= "host=outbound1.vitelity.net\n";
            $textConfig .= "fromuser=elastix\n";
            $textConfig .= "context=from-trunk\n";
            $textConfig .= "canreinvite=no";

        }else if("NuFoneIAX"){
            $textConfig .= "username=elastixnufonegye\n";
            $textConfig .= "type=peer\n";
            $textConfig .= "secret=palosanto.2007nufone\n";
            $textConfig .= "host=switch-1.nufone.net";

        }
        return $textConfig;
    }

    function getConfigByType2($type)
    {
        $textXMLConfig = "";
        $textXMLConfig .= "<?xml version=\"1.0\"?>\n";
        $textXMLConfig .= "<configs>\n";
        $textXMLConfig .= "<attribute>\n";
        if($type=="net2phone"){
            $data = $this->getVoIPProviderByProvider(1);
            
            $textXMLConfig .= "<username>{$data['username']}</username>\n";
            $textXMLConfig .= "<type>{$data['type']}</type>\n";
            $textXMLConfig .= "<secret>{$data['password']}</secret>\n";
            $textXMLConfig .= "<qualify>{$data['qualify']}</qualify>\n";
            $textXMLConfig .= "<insecure>{$data['insecure']}</insecure>\n";
            $textXMLConfig .= "<host>{$data['host']}</host>\n";
            $textXMLConfig .= "<context>{$data['context']}</context>\n";
            $textXMLConfig .= "<canreinvite>{$data['canreinvite']}</canreinvite>\n";

        }else if($type=="to_camundanet"){
            $data = $this->getVoIPProviderByProvider(2);

            $textXMLConfig .= "<username>{$data['username']}</username>\n";
            $textXMLConfig .= "<type>{$data['type']}</type>\n";
            $textXMLConfig .= "<secret>{$data['password']}</secret>\n";
            $textXMLConfig .= "<qualify>{$data['qualify']}</qualify>\n";
            $textXMLConfig .= "<insecure>{$data['insecure']}</insecure>\n";
            $textXMLConfig .= "<host>{$data['host']}</host>\n";
            $textXMLConfig .= "<fromuser>{$data['fromuser']}</fromuser>\n";
            $textXMLConfig .= "<fromdomain>{$data['fromdomain']}</fromdomain>\n";
            $textXMLConfig .= "<dtmfmode>{$data['dtmfmode']}</dtmfmode>\n";
            $textXMLConfig .= "<disallow>{$data['disallow']}</disallow>\n";
            $textXMLConfig .= "<context>{$data['context']}</context>\n";
            $textXMLConfig .= "<allow>{$data['allow']}</allow>\n";

        }else if($type=="vitelity"){
            $data = $this->getVoIPProviderByProvider(3);

            $textXMLConfig .= "<username>{$data['username']}</username>\n";
            $textXMLConfig .= "<type>{$data['type']}</type>\n";
            $textXMLConfig .= "<trustrpid>{$data['trustrpid']}</trustrpid>\n";
            $textXMLConfig .= "<sendrpid>{$data['sendrpid']}</sendrpid>\n";
            $textXMLConfig .= "<secret>{$data['password']}</secret>\n";
            $textXMLConfig .= "<qualify>{$data['qualify']}</qualify>\n";
            $textXMLConfig .= "<host>{$data['host']}</host>\n";
            $textXMLConfig .= "<fromuser>{$data['fromuser']}</fromuser>\n";
            $textXMLConfig .= "<context>{$data['context']}</context>\n";
            $textXMLConfig .= "<canreinvite>{$data['canreinvite']}</canreinvite>\n";

        }else if("NuFoneIAX"){
            $data = $this->getVoIPProviderByProvider(4);

            $textXMLConfig .= "<username>{$data['username']}</username>\n";
            $textXMLConfig .= "<type>{$data['type']}</type>\n";
            $textXMLConfig .= "<secret>{$data['password']}</secret>\n";
            $textXMLConfig .= "<host>{$data['host']}</host>\n";
        }
        $textXMLConfig .= "</attribute>\n";
        $textXMLConfig .= "</configs>\n";
        
        return $textXMLConfig;
    }

    function saveChangeFileExtensionAdditional($text) {
        $fp = fopen('/etc/asterisk/extensions_additional.conf', 'w');

        fwrite($fp, $text);
        fclose($fp);
    }

    function saveChangeFileLocalPrefixes($text) {
        $fp = fopen('/etc/asterisk/localprefixes.conf', 'a');

        fwrite($fp, $text);
        fclose($fp);
    }

    function saveChangeFileSipAdditional($text) {
        $fp = fopen('/etc/asterisk/sip_additional.conf', 'a');

        fwrite($fp, $text);
        fclose($fp);
    }

    function updateChangeFileSipAdditional($text) {
        $fp = fopen('/etc/asterisk/sip_additional.conf', 'w');

        fwrite($fp, $text);
        fclose($fp);
    }

    function saveChangeFileSipRegistrations($text) {
        $fp = fopen('/etc/asterisk/sip_registrations.conf', 'a');

        fwrite($fp, $text);
        fclose($fp);
    }

    function updateChangeFileSipRegistrations($text) {
        $fp = fopen('/etc/asterisk/sip_registrations.conf', 'w');

        fwrite($fp, $text);
        fclose($fp);
    }

    function saveChangeFileIaxAdditional($text) {
        $fp = fopen('/etc/asterisk/iax_additional.conf', 'a');

        fwrite($fp, $text);
        fclose($fp);
    }

    function updateChangeFileIaxAdditional($text) {
        $fp = fopen('/etc/asterisk/iax_additional.conf', 'w');

        fwrite($fp, $text);
        fclose($fp);
    }

    function saveChangeFileIaxRegistrations($text) {
        $fp = fopen('/etc/asterisk/iax_registrations.conf', 'a');

        fwrite($fp, $text);
        fclose($fp);
    }

    function updateChangeFileIaxRegistrations($text) {
        $fp = fopen('/etc/asterisk/iax_registrations.conf', 'w');

        fwrite($fp, $text);
        fclose($fp);
    }

    function AddConfFileExtensionAdditional($nameTrunk, $typeTrunk)
    {
        $FILE='/etc/asterisk/extensions_additional.conf';
        $text = "";
        $num_nextTrunk = "";
        $line_conf1 = "";
        $line_conf2 = "";
        $fp = fopen($FILE,'r');
        //exec("echo 'Entro' > /tmp/oscar");
        $data_trunk = $this->getIndexTrunk();
        $name_pass = $data_trunk['name'];
        //if(empty($data_trunk)) exec("echo 'No existe Archivo' > /tmp/oscar");
        if($data_trunk['index'] > 1){
            $filter1="OUTDISABLE_{$data_trunk['index']}";
            $filter2="from-trunk-$typeTrunk-{$data_trunk['name']}";
            $num_nextTrunk = $data_trunk['index']+1;
        }else{
            $filter1="RECORDING_STATE";
            if($typeTrunk=="sip") $filter2="end of [from-did-direct-ivr]";
            else $filter2="end of [ext-did-catchall]";
        }
        $i=0;
        while($line = fgets($fp, filesize($FILE)))
        {
            if(eregi($filter1, $line)){
                //$line_conf1 = "\n";
                $line_conf1 .= "OUT_$num_nextTrunk = ".strtoupper($typeTrunk)."/$nameTrunk\n";
                $line_conf1 .= "OUTPREFIX_$num_nextTrunk =\n";
                $line_conf1 .= "OUTMAXCHANS_$num_nextTrunk =\n";
                $line_conf1 .= "OUTCID_$num_nextTrunk =\n";
                $line_conf1 .= "OUTKEEPCID_$num_nextTrunk = off\n";
                $line_conf1 .= "OUTFAIL_$num_nextTrunk =\n";
                $line_conf1 .= "OUTDISABLE_$num_nextTrunk = off\n";
                $line .= $line_conf1;
                $text .= $line;
            }else if(eregi($filter2, $line)){
            //}else if(ereg("^;[[:space:]][a-zA-Z][[:space:]][a-zA-Z][[:space:]]([a-zA-Z]+)-([a-zA-Z]+)-([a-zA-Z]+)-($name_pass)",$line, $arrReg)){
                $i++;
                if($i==3){
                    $line_conf2 .= "\n\n";
                    $line_conf2 .= "[from-trunk-$typeTrunk-$nameTrunk]\n";
                    $line_conf2 .= "include => from-trunk-$typeTrunk-$nameTrunk-custom]\n";
                    $line_conf2 .= "exten => _.,1,Set(GROUP()=OUT_$num_nextTrunk)\n";
                    $line_conf2 .= "exten => _.,n,Goto(from-trunk,\${EXTEN},1)\n";
                    $line_conf2 .= "\n";
                    $line_conf2 .= "; end of [from-trunk-$typeTrunk-$nameTrunk]\n";
                    $line .= $line_conf2;
                    $text .= $line;
                }else
                    $text .= $line;
            }else{
                $text .= $line;
            }
        }

        $this->saveChangeFileExtensionAdditional($text);
        fclose($fp);
        
    }

    //solo guarda las troncales que contienen reglas
    function addConfFileLocalPrefixes()
    {
        $data_trunk = $this->getIndexTrunk();
        if($num == null) $num=1;
        $FILE='/etc/asterisk/localprefixes.conf';
        $text = "";
        $fp = fopen($FILE,'r');
        $num_nextTrunk = $data_trunk['index']+1;
        //$text .="\n\n";
        $text .="\n[trunk-$num_nextTrunk]\n\n";
        
        $this->saveChangeFileLocalPrefixes($text);
    }

    function addConfFileSipAdditional($type)
    {
        $FILE='/etc/asterisk/sip_additional.conf';
        $text = "";
        $fp = fopen($FILE,'r');        
        $text .="[$type]\n";
        //Peer Detail 
        if($type=="net2phone"){
            $arr_data = $this->getVoIPProviderByProvider(1);
            
            $text .= "username={$arr_data['username']}\n";
            $text .= "type={$arr_data['type']}\n";
            $text .= "secret={$arr_data['password']}\n";
            $text .= "qualify={$arr_data['qualify']}\n";
            $text .= "insecure=port,invite\n";//very
            $text .= "host={$arr_data['host']}\n";
            $text .= "context={$arr_data['context']}\n";
            $text .= "canreinvite={$arr_data['canreinvite']}\n\n";

        }else if($type=="to_camundanet"){
            $data = $this->getVoIPProviderByProvider(2);
            
            $text .= "username={$data['username']}\n";
            $text .= "type={$data['type']}\n";
            $text .= "secret={$data['password']}\n";
            $text .= "qualify={$data['qualify']}\n";
            $text .= "insecure=port,invite\n";//very
            $text .= "host={$data['host']}\n";
            $text .= "fromuser={$data['fromuser']}\n";
            $text .= "fromdomain={$data['fromdomain']}\n";
            $text .= "dtmfmode={$data['dtmfmode']}\n";
            $text .= "disallow={$data['disallow']}\n";
            $text .= "context={$data['context']}\n";
            $text .= "allow={$data['allow']}\n\n";

        }else if($type=="vitelity"){
            $data = $this->getVoIPProviderByProvider(3);

            $text .= "username={$data['username']}\n";
            $text .= "type={$data['type']}\n";
            $text .= "trustrpid={$data['trustrpid']}\n";
            $text .= "sendrpid={$data['sendrpid']}\n";
            $text .= "secret={$data['password']}\n";
            $text .= "qualify={$data['qualify']}\n";
            $text .= "host={$data['host']}\n";
            $text .= "fromuser={$data['fromuser']}\n\n";
        }
        $this->saveChangeFileSipAdditional($text);
        fclose($fp);
    }

    function addConfFileIaxAdditional($type)
    {
        $FILE='/etc/asterisk/iax_additional.conf';
        $text = "";
        $fp = fopen($FILE,'r');
        $text .="[$type]\n";

        if($type=="NuFoneIAX"){
            $data = $this->getVoIPProviderByProvider(4);

            $text .= "username={$data['username']}\n";
            $text .= "type={$data['type']}\n";
            $text .= "secret={$data['password']}\n";
            $text .= "host={$data['host']}\n\n";
        }
        $this->saveChangeFileIaxAdditional($text);
        fclose($fp);
    }

    function updateFileSipAdditional($type)
    {
        $FILE='/etc/asterisk/sip_additional.conf';
        $text_sipadd = "";
        $fp = fopen($FILE,'r');
        $link="none";
        $find="false";
        $i=0;
        
        while($line = fgets($fp, filesize($FILE)))
        {
            if(eregi($type, $line) && $find=="false"){
                if($type=="net2phone"){
                    $arr_data = $this->getVoIPProviderByProvider(1);
                    $text = $line;
                    $text .= "username={$arr_data['username']}\n";
                    $text .= "type={$arr_data['type']}\n";
                    $text .= "secret={$arr_data['password']}\n";
                    $text .= "qualify={$arr_data['qualify']}\n";
                    $text .= "insecure=port,invite\n";//very
                    $text .= "host={$arr_data['host']}\n";
                    $text .= "context={$arr_data['context']}\n";
                    $text .= "canreinvite={$arr_data['canreinvite']}\n\n";
                    $text_sipadd .=$text;
                    $link1 = "to_camundanet";
                    $link2 = "vitelity";
                }else if($type=="to_camundanet"){
                    $data = $this->getVoIPProviderByProvider(2);
                    $text = $line;
                    $text .= "username={$data['username']}\n";
                    $text .= "type={$data['type']}\n";
                    $text .= "secret={$data['password']}\n";
                    $text .= "qualify={$data['qualify']}\n";
                    $text .= "insecure=port,invite\n";//very
                    $text .= "host={$data['host']}\n";
                    $text .= "fromuser={$data['fromuser']}\n";
                    $text .= "fromdomain={$data['fromdomain']}\n";
                    $text .= "dtmfmode={$data['dtmfmode']}\n";
                    $text .= "disallow={$data['disallow']}\n";
                    $text .= "context={$data['context']}\n";
                    $text .= "allow={$data['allow']}\n\n";
                    $text_sipadd .=$text;
                    $link1 = "vitelity";
                    $link2 = "net2phone";
                }else if($type=="vitelity"){
                    $data = $this->getVoIPProviderByProvider(3);
                    $text = $line;
                    $text .= "username={$data['username']}\n";
                    $text .= "type={$data['type']}\n";
                    $text .= "trustrpid={$data['trustrpid']}\n";
                    $text .= "sendrpid={$data['sendrpid']}\n";
                    $text .= "secret={$data['password']}\n";
                    $text .= "qualify={$data['qualify']}\n";
                    $text .= "host={$data['host']}\n";
                    $text .= "fromuser={$data['fromuser']}\n\n";
                    $text_sipadd .=$text;
                    $link1 = "to_camundanet";
                    $link2 = "net2phone";
                }
                $find = "true";
            }else if((!eregi($link1, $line) || !eregi($link2, $line)) && $find=="true"){
                //no guarda la configuracion anterior
                $i++;
            }else{
                $text_sipadd .= $line;
                $find = "false";
            }
        }
        
        $this->updateChangeFileSipAdditional($text_sipadd);
        fclose($fp);
    }

    function updateFileIaxAdditional($type)
    {
        $FILE='/etc/asterisk/iax_additional.conf';
        $text_sipadd = "";
        $fp = fopen($FILE,'r');
        $link="none";
        $find="false";
        $i=0;
        while($line = fgets($fp, filesize($FILE)))
        {
            if(eregi($type, $line) && $find=="false"){
                if("NuFoneIAX"){
                    $data = $this->getVoIPProviderByProvider(4);
                    $text = $line;
                    $text .= "username={$data['username']}\n";
                    $text .= "type={$data['type']}\n";
                    $text .= "secret={$data['password']}\n";
                    $text .= "host={$data['host']}\n\n";
                    $text_sipadd .=$text;
                    $link = "none";
                }
                $find = "true";
            }else if(!eregi($link, $line) && $find=="true"){
                //no guarda la configuracion anterior
                $i++;
            }else{
                $text_sipadd .= $line;
                $find = "false";
            }
        }

        $this->updateChangeFileIaxAdditional($text_sipadd);
        fclose($fp);
    }
    
    function addConfFileSipRegistrations($username, $secret, $host)
    {
        $FILE='/etc/asterisk/sip_registrations.conf';
        $text = "";
        $fp = fopen($FILE,'r');

        $text .="register=$username:$secret@$host/$username\n";

        $this->saveChangeFileSipRegistrations($text);
        fclose($fp);
    }

    function addConfFileIaxRegistrations($username, $secret, $host)
    {
        $FILE='/etc/asterisk/iax_registrations.conf';
        $text = "";
        $fp = fopen($FILE,'r');

        $text .="register=$username:$secret@$host\n";

        $this->saveChangeFileIaxRegistrations($text);
        fclose($fp);
    }

    function updateFileSipRegistrations($username, $secret, $host)
    {
        $FILE='/etc/asterisk/sip_registrations.conf';
        $text = "";
        $fp = fopen($FILE,'r');
        
        //$text .="\n";
        while($line = fgets($fp, filesize($FILE)))
        {
            if(ereg($host, $line)){
                $text .="register=$username:$secret@$host/$username\n";
            }else{
                $text .= $line;
            }
        }
        $this->updateChangeFileSipRegistrations($text);
        fclose($fp);
    }

    function updateFileIaxRegistrations($username, $secret, $host)
    {
        $FILE='/etc/asterisk/iax_registrations.conf';
        $text = "";
        $fp = fopen($FILE,'r');
        //$text .="\n";
        while($line = fgets($fp, filesize($FILE)))
        {
            if(ereg($host, $line)){
                $text .="register=$username:$secret@$host\n";
            }else{
                $text .= $line;
            }
        }
        $this->updateChangeFileIaxRegistrations($text);
        fclose($fp);
    }

    function getIndexTrunk()
    {
        $FILE='/etc/asterisk/extensions_additional.conf';
        $count = "";
        $last = "";
        $data = array();
        $data['index'] = 0;
        $data['name'] = "";
        $fp = fopen($FILE,'r');
        while($line = fgets($fp, filesize($FILE)))
        {
            if(ereg("^([[:alnum:]]+)_([[:digit:]]+)[[:space:]]=[[:space:]]([[:alnum:]]+)/([a-zA-Z_]+)",$line, $arrReg)){
                if("OUT" == $arrReg[1]){
                    $count++;
                    $data['index'] = $arrReg[2];
                    $data['name'] = $arrReg[4];
                }
            }
        }
        fclose($fp);
        return $data;
    }

    function updateTrunkAttribute($data, $where)
    {
        $queryUpdate = $this->_DB->construirUpdate('attribute', $data, $where);
        $result = $this->_DB->genQuery($queryUpdate);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    function updateTrunkParameter($data, $where)
    {
        $queryUpdate = $this->_DB->construirUpdate('trunk', $data, $where);
        $result = $this->_DB->genQuery($queryUpdate);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    //Validacion
    function findTrunkInExtensionAdditional($type_provider){
        $FILE='/etc/asterisk/extensions_additional.conf';

        $fp = fopen($FILE,'r');
        $found = "false";
        while($line = fgets($fp, filesize($FILE)))
        {
            if(ereg($type_provider, $line)){
                $found = "true";
            }
        }
        fclose($fp);
        return $found;
    }

    function findTrunkInLocalPrefixes(){
        $FILE='/etc/asterisk/localprefixes.conf';
        
        $fp = fopen($FILE,'r');
        $data_trunk = $this->getIndexTrunk();
        $num_trunk = $data_trunk['index'];
        $i="";
        $found = "false";
        while($line = fgets($fp, filesize($FILE)))
        {
            for($i=1 ; $i<=$num_trunk; $i++){
                $filter = "trunk-$i";
                if(ereg($filter, $line)){
                    $found = "true";
                }
            }
        }
        fclose($fp);
        return $found;
    }

    function findTrunkInSipAdditional($type_provider){
        $FILE='/etc/asterisk/sip_additional.conf';

        $fp = fopen($FILE,'r');
        $found = "false";
        while($line = fgets($fp, filesize($FILE)))
        {
            if(ereg($type_provider, $line)){
                $found = "true";
            }
        }
        fclose($fp);
        return $found;
    }

    function findTrunkInIaxAdditional($type_provider){
        $FILE='/etc/asterisk/iax_additional.conf';

        $fp = fopen($FILE,'r');
        $found = "false";
        while($line = fgets($fp, filesize($FILE)))
        {
            if(ereg($type_provider, $line)){
                $found = "true";
            }
        }
        fclose($fp);
        return $found;
    }

    function findTrunkInSipRegistrations($host){
        $FILE='/etc/asterisk/sip_registrations.conf';

        $fp = fopen($FILE,'r');
        $found = "false";
        while($line = fgets($fp, filesize($FILE)))
        {
            if(ereg($host, $line)){
                $found = "true";
            }
        }
        fclose($fp);
        return $found;
    }

    function findTrunkInIaxRegistrations($host){
        $FILE='/etc/asterisk/iax_registrations.conf';

        $fp = fopen($FILE,'r');
        $found = "false";
        while($line = fgets($fp, filesize($FILE)))
        {
            if(ereg($host, $line)){
                $found = "true";
            }
        }
        fclose($fp);
        return $found;
    }

    function init(){
        //exec("mkdir /etc/asterisk/chan_dahdi_additional.conf");
        //exec("mkdir /etc/asterisk/extensions_additional.conf"); //<-
        //exec("mkdir /etc/asterisk/features_applicationmap_additional.conf");
        //exec("mkdir /etc/asterisk/features_futuremap_additional.conf");//<-
        //exec("mkdir /etc/asterisk/features_general_additional.conf");//<-
        //exec("mkdir /etc/asterisk/iax_additional.conf");//<-
        //exec("mkdir /etc/asterisk/iax_registrations.conf");//<-
        //exec("mkdir /etc/asterisk/indications.conf");//duda en nuevo archivo tiene menos lineas
        exec("mkdir /etc/asterisk/localprefixes.conf");
        //exec("mkdir /etc/asterisk/meetme_additional.conf");//<-
        //exec("mkdir /etc/asterisk/queues_additional.conf");//<-
        //exec("mkdir /etc/asterisk/queues_general_additional.conf");//<-
        //exec("mkdir /etc/asterisk/sip_additional.conf");//<-
        //exec("mkdir /etc/asterisk/sip_general_additional.conf");//<-
        //exec("mkdir /etc/asterisk/sip_registrations.conf");//<-
    }

//     function addConfIniExtenAdditional(){
//         $text ="";
//         $text .="[globals]\n";
//         $text .="DNDDEVSTATE = TRUE\n";
//         $text .="FMDEVSTATE = TRUE\n";
//         $text .="INTERCOMCODE = nointercom\n";
//         $text .="CALLFILENAME = \"\"\n";
//         $text .="DIAL_OPTIONS = tr\n";
//         $text .="TRUNK_OPTIONS =\n"; 
//         $text .="DIAL_OUT = 9\n";
//         $text .="FAX =\n"; 
//         $text .="FAX_RX = system\n";
//         $text .="FAX_RX_EMAIL = fax@mydomain.com\n";
//         $text .="FAX_RX_FROM = freepbx@gmail.com\n";
//         $text .="INCOMING = group-all\n";
//         $text .="NULL = \"\"\n";
//         $text .="OPERATOR =\n"; 
//         $text .="OPERATOR_XTN =\n"; 
//         $text .="PARKNOTIFY = SIP/200\n";
//         $text .="RECORDEXTEN = \"\"\n";
//         $text .="RINGTIMER = 15\n";
//         $text .="DIRECTORY = last\n";
//         $text .="AFTER_INCOMING =\n"; 
//         $text .="IN_OVERRIDE = forcereghours\n";
//         $text .="REGTIME = 7:55-17:05\n";
//         $text .="REGDAYS = mon-fri\n";
//         $text .="DIRECTORY_OPTS =\n"; 
//         $text .="DIALOUTIDS = 1\n";
//         $text .="OUT_1 = DAHDI/g0\n";
//         $text .="VM_PREFIX = *\n";
//         $text .="VM_OPTS =\n"; 
//         $text .="VM_GAIN =\n"; 
//         $text .="VM_DDTYPE = u\n";
//         $text .="TIMEFORMAT = kM\n";
//         $text .="TONEZONE = us\n";
//         $text .="ALLOW_SIP_ANON = no\n";
//         $text .="VMX_CONTEXT = from-internal\n";
//         $text .="VMX_PRI = 1\n";
//         $text .="VMX_TIMEDEST_CONTEXT =\n"; 
//         $text .="VMX_TIMEDEST_EXT = dovm\n";
//         $text .="VMX_TIMEDEST_PRI = 1\n";
//         $text .="VMX_LOOPDEST_CONTEXT =\n"; 
//         $text .="VMX_LOOPDEST_EXT = dovm\n";
//         $text .="VMX_LOOPDEST_PRI = 1\n";
//         $text .="VMX_OPTS_TIMEOUT =\n"; 
//         $text .="VMX_OPTS_LOOP =\n"; 
//         $text .="VMX_OPTS_DOVM =\n"; 
//         $text .="VMX_TIMEOUT = 2\n";
//         $text .="VMX_REPEAT = 1\n";
//         $text .="VMX_LOOPS = 1\n";
//         $text .="TRANSFER_CONTEXT = from-internal-xfer\n";
//         $text .="MIXMON_FORMAT = wav\n";
//         $text .="MIXMON_DIR =\n"; 
//         $text .="MIXMON_POST =\n"; 
//         $text .="RECORDING_STATE = ENABLED\n";//punto de busqueda
//         $text .="ASTETCDIR = /etc/asterisk\n";
//         $text .="ASTMODDIR = /usr/lib/asterisk/modules\n";
//         $text .="ASTVARLIBDIR = /var/lib/asterisk\n";
//         $text .="ASTAGIDIR = /var/lib/asterisk/agi-bin\n";
//         $text .="ASTSPOOLDIR = /var/spool/asterisk\n";
//         $text .="ASTRUNDIR = /var/run/asterisk\n";
//         $text .="ASTLOGDIR = /var/log/asterisk\n";
//         $text .="CWINUSEBUSY = true\n";
//         $text .="AMPMGRUSER = admin\n";
//         $text .="AMPMGRPASS = elastix456\n";
//         $text .="ASTVERSION = 1.4.26.1\n";
//         $text .="ASTCHANDAHDI = 1\n";
//         $text .="ALLINGPRES_0 = allowed_not_screened\n";
//         $text .="CALLINGPRES_1 = allowed_passed_screen\n";
//         $text .="CALLINGPRES_2 = allowed_failed_screen\n";
//         $text .="CALLINGPRES_3 = allowed\n";
//         $text .="CALLINGPRES_32 = prohib_not_screened\n";
//         $text .="CALLINGPRES_33 = prohib_passed_screen\n";
//         $text .="CALLINGPRES_34 = prohib_failed_screen\n";
//         $text .="CALLINGPRES_35 = prohib\n";
//         $text .="CALLINGPRES_67 = unavailable\n";
//         $text .="PREFIX_TRUNK_2 = 1\n";
//         $text .="#include globals_custom.conf\n";
//         $text .="\n";
//         $text .=";end of [globals]\n";
//         $text .="\n";
//         $text .="[app-pbdirectory]\n";
//         $text .="include => app-pbdirectory-custom\n";
//         $text .="exten => 411,1,Answer\n";
//         $text .="exten => 411,n,Wait(1)\n";
//         $text .="exten => 411,n,Goto(pbdirectory,1)\n";
//         $text .="exten => pbdirectory,1,Macro(user-callerid,)\n";
//         $text .="exten => pbdirectory,n,AGI(pbdirectory)\n";
//         $text .="exten => pbdirectory,n,GotoIf($[\"\${dialnumber}\"=\"\"]?hangup,1)\n";
//         $text .="exten => pbdirectory,n,Noop(Got number to dial: \${dialnumber})\n";
//         $text .="exten => pbdirectory,n,Dial(Local/\${dialnumber}@from-internal/n,)\n";
//         $text .="exten => hangup,1,Hangup\n";
//         $text .="\n";
//         $text .="; end of [app-pbdirectory]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[macro-autoanswer]\n";
//         $text .="include => macro-autoanswer-custom\n";
//         $text .="exten => s,1,Set(DIAL=\${DB(DEVICE/\${ARG1}/dial)})\n";
//         $text .="exten => s,n,GotoIf($[\"\${DB(DEVICE/\${ARG1}/autoanswer/macro)}\" != \"\" ]?macro)\n";
//         $text .="exten => s,n,Set(phone=\${SIPPEER(\${CUT(DIAL,/,2)}:useragent)})\n";
//         $text .="exten => s,n,Set(SIPURI=)\n";
//         $text .="exten => s,n,Set(ALERTINFO=Alert-Info: Ring Answer)\n";
//         $text .="exten => s,n,Set(CALLINFO=Call-Info: <uri>\;answer-after=0)\n";
//         $text .="exten => s,n,Set(SIPURI=intercom=true)\n";
//         $text .="exten => s,n,Set(DOPTIONS=A(beep))\n";
//         $text .="exten => s,n,Set(DTIME=5)\n";
//         $text .="exten => s,n,Set(ANSWERMACRO=)\n";
//         $text .="exten => s,n,ExecIf($[\"\${phone:0:5}\" = \"Mitel\"],Set,CALLINFO=Call-Info: <sip:broadworks.net>\;answer-after=0)\n";
//         $text .="exten => s,n,GotoIf($[\"\${ANSWERMACRO}\" != \"\"]?macro2)\n";
//         $text .="exten => s,n,ExecIf($[\"\${ALERTINFO}\" != \"\"],SipAddHeader,\${ALERTINFO})\n";
//         $text .="exten => s,n,ExecIf($[\"\${CALLINFO}\" != \"\"],SipAddHeader,\${CALLINFO})\n";
//         $text .="exten => s,n,ExecIf($[\"\${SIPURI}\" != \"\"],Set,__SIP_URI_OPTIONS=\${SIPURI})\n";
//         $text .="exten => s,n+2(macro),Macro(\${DB(DEVICE/\${ARG1}/autoanswer/macro)},\${ARG1})\n";
//         $text .="exten => s,n+2(macro2),Macro(\${ANSWERMACRO},\${ARG1})\n";
//         $text .="\n";
//         $text .="; end of [macro-autoanswer]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[ext-paging]\n";
//         $text .="include => ext-paging-custom\n";
//         $text .="exten => _PAGE.,1,GotoIf(\$[ \${AMPUSER} = \${EXTEN:4} ]?skipself)\n";
//         $text .="exten => _PAGE.,n,GotoIf(\$[ \${FORCE_PAGE} != 1 ]?AVAIL)\n";
//         $text .="exten => _PAGE.,n,Set(AVAILSTATUS=not checked)\n";
//         $text .="exten => _PAGE.,n,Goto(SKIPCHECK)\n";
//         $text .="exten => _PAGE.,n(AVAIL),ChanIsAvail(\${DB(DEVICE/\${EXTEN:4}/dial)},js)\n";
//         $text .="exten => _PAGE.,n(SKIPCHECK),Noop(Seems to be available (state = \${AVAILSTATUS})\n";
//         $text .="exten => _PAGE.,n,Macro(autoanswer,\${EXTEN:4})\n";
//         $text .="exten => _PAGE.,n,Dial(\${DIAL},\${DTIME},\${DOPTIONS})\n";
//         $text .="exten => _PAGE.,n(skipself),Noop(Not paging originator)\n";
//         $text .="exten => _PAGE.,n,Hangup\n";
//         $text .="exten => _PAGE.,AVAIL+101,Noop(Channel \${AVAILCHAN} is not available (state = \${AVAILSTATUS}))\n";
//         $text .="\n";
//         $text .="; end of [ext-paging]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-blacklist-check]\n";
//         $text .="include => app-blacklist-check-custom\n";
//         $text .="exten => s,1,LookupBlacklist()\n";
//         $text .="exten => s,n,GotoIf($[\"\${LOOKUPBLSTATUS}\"=\"FOUND\"]?blacklisted)\n";
//         $text .="exten => s,n,Return()\n";
//         $text .="exten => s,n(blacklisted),Answer\n";
//         $text .="exten => s,n,Wait(1)\n";
//         $text .="exten => s,n,Zapateller()\n";
//         $text .="exten => s,n,Playback(ss-noservice)\n";
//         $text .="exten => s,n,Hangup\n";
//         $text .="\n";
//         $text .="; end of [app-blacklist-check]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-blacklist]\n";
//         $text .="include => app-blacklist-custom\n";
//         $text .="exten => *30,1,Goto(app-blacklist-add,s,1)\n";
//         $text .="exten => *32,1,Goto(app-blacklist-last,s,1)\n";
//         $text .="exten => *31,1,Goto(app-blacklist-remove,s,1)\n";
//         $text .="\n";
//         $text .="; end of [app-blacklist]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-blacklist-add]\n";
//         $text .="include => app-blacklist-add-custom\n";
//         $text .="exten => s,1,Answer\n";
//         $text .="exten => s,n,Wait(1)\n";
//         $text .="exten => s,n,Playback(enter-num-blacklist)\n";
//         $text .="exten => s,n,Set(TIMEOUT(digit)=5)\n";
//         $text .="exten => s,n,Set(TIMEOUT(response)=60)\n";
//         $text .="exten => s,n,Read(blacknr,then-press-pound,,,,)\n";
//         $text .="exten => s,n,SayDigits(\${blacknr})\n";
//         $text .="exten => s,n,Playback(if-correct-press&digits/1)\n";
//         $text .="exten => s,n,Noop(Waiting for input)\n";
//         $text .="exten => s,n(end),WaitExten(60,)\n";
//         $text .="exten => s,n,Playback(sorry-youre-having-problems&goodbye)\n";
//         $text .="exten => 1,1,Set(DB(blacklist/\${blacknr})=1)\n";
//         $text .="exten => 1,n,Playback(num-was-successfully&added)\n";
//         $text .="exten => 1,n,Wait(1)\n";
//         $text .="exten => 1,n,Hangup\n";
//         $text .="\n";
//         $text .="; end of [app-blacklist-add]\n";
//         $text .="\n";
//         $text .="[app-blacklist-last]\n";
//         $text .="include => app-blacklist-last-custom\n";
//         $text .="exten => s,1,Answer\n";
//         $text .="exten => s,n,Wait(1)\n";
// //         $text .="exten => s,n,Set(lastcaller=${DB(CALLTRACE/${CALLERID(number)})})\n";
//         $text .="exten => s,n,GotoIf($[ $[ \"\${lastcaller}\" = \"\" ] | $[ \"\${lastcaller}\" = \"unknown\" ] ]?noinfo)\n";
//         $text .="exten => s,n,Playback(privacy-to-blacklist-last-caller&telephone-number)\n";
//         $text .="exten => s,n,SayDigits(\${lastcaller})\n";
//         $text .="exten => s,n,Set(TIMEOUT(digit)=3)\n";
//         $text .="exten => s,n,Set(TIMEOUT(response)=7)\n";
//         $text .="exten => s,n,Playback(if-correct-press&digits/1)\n";
//         $text .="exten => s,n,Goto(end)\n";
//         $text .="exten => s,n(noinfo),Playback(unidentified-no-callback)\n";
//         $text .="exten => s,n,Hangup\n";
//         $text .="exten => s,n,Noop(Waiting for input)\n";
//         $text .="exten => s,n(end),WaitExten(60,)\n";
//         $text .="exten => s,n,Playback(sorry-youre-having-problems&goodbye)\n";
//         $text .="exten => 1,1,Set(DB(blacklist/\${lastcaller})=1)\n";
//         $text .="exten => 1,n,Playback(num-was-successfully)\n";
//         $text .="exten => 1,n,Playback(added)\n";
//         $text .="exten => 1,n,Wait(1)\n";
//         $text .="exten => 1,n,Hangup\n";
//         $text .="\n";
//         $text .="; end of [app-blacklist-last]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-blacklist-remove]\n";
//         $text .="include => app-blacklist-remove-custom\n";
//         $text .="exten => s,1,Answer\n";
//         $text .="exten => s,n,Wait(1)\n";
//         $text .="exten => s,n,Playback(entr-num-rmv-blklist)\n";
//         $text .="exten => s,n,Set(TIMEOUT(digit)=5)\n";
//         $text .="exten => s,n,Set(TIMEOUT(response)=60)\n";
//         $text .="exten => s,n,Read(blacknr,then-press-pound,,,,)\n";
//         $text .="exten => s,n,SayDigits(\${blacknr})\n";
//         $text .="exten => s,n,Playback(if-correct-press&digits/1)\n";
//         $text .="exten => s,n,Noop(Waiting for input)\n";
//         $text .="exten => s,n(end),WaitExten(60,)\n";
//         $text .="exten => s,n,Playback(sorry-youre-having-problems&goodbye)\n";
//         $text .="exten => 1,1,dbDel(blacklist/\${blacknr})\n";
//         $text .="exten => 1,n,Playback(num-was-successfully&removed)\n";
//         $text .="exten => 1,n,Wait(1)\n";
//         $text .="exten => 1,n,Hangup\n";
//         $text .="\n";
//         $text .="; end of [app-blacklist-remove]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[ivr-3]\n";
//         $text .="include => ivr-3-custom\n";
//         $text .="include => from-did-direct-ivr\n";
//         $text .="include => app-directory\n";
//         $text .="exten => #,1,dbDel(\${BLKVM_OVERRIDE})\n";
//         $text .="exten => #,n,Set(__NODEST=)\n";
//         $text .="exten => #,n,Goto(app-directory,#,1)\n";
//         $text .="exten => h,1,Hangup\n";
//         $text .="exten => s,1,Set(MSG=)\n";
//         $text .="exten => s,n,Set(LOOPCOUNT=0)\n";
//         $text .="exten => s,n,Set(__DIR-CONTEXT=default)\n";
//         $text .="exten => s,n,Set(_IVR_CONTEXT_\${CONTEXT}=\${IVR_CONTEXT})\n";
//         $text .="exten => s,n,Set(_IVR_CONTEXT=\${CONTEXT})\n";
// //         $text .="exten => s,n,GotoIf($[\"${CDR(disposition)}\" = \"ANSWERED\"]?begin)\n";
//         $text .="exten => s,n,Answer\n";
//         $text .="exten => s,n,Wait(1)\n";
//         $text .="exten => s,n(begin),Set(TIMEOUT(digit)=3)\n";
//         $text .="exten => s,n,Set(TIMEOUT(response)=10)\n";
//         $text .="exten => s,n,Set(__IVR_RETVM=)\n";
//         $text .="exten => s,n,ExecIf($[\"\${MSG}\" != \"\"],Background,\${MSG})\n";
//         $text .="exten => s,n,WaitExten(,)\n";
//         $text .="exten => hang,1,Playback(vm-goodbye)\n";
//         $text .="exten => hang,n,Hangup\n";
//         $text .="exten => i,1,Playback(invalid)\n";
//         $text .="exten => i,n,Goto(loop,1)\n";
//         $text .="exten => t,1,Goto(loop,1)\n";
//         $text .="exten => loop,1,Set(LOOPCOUNT=$[\${LOOPCOUNT} + 1])\n";
//         $text .="exten => loop,n,GotoIf($[\${LOOPCOUNT} > 2]?hang,1)\n";
//         $text .="exten => loop,n,Goto(ivr-3,s,begin)\n";
//         $text .="exten => return,1,Set(MSG=)\n";
//         $text .="exten => return,n,Set(_IVR_CONTEXT=\${CONTEXT})\n";
// //         $text .="exten => return,n,Set(_IVR_CONTEXT_\${CONTEXT}=${IVR_CONTEXT_\${CONTEXT}})\n";
//         $text .="exten => return,n,Goto(ivr-3,s,begin)\n";
//         $text .="exten => fax,1,Goto(ext-fax,in_fax,1)\n";
//         $text .="\n";
//         $text .="; end of [ivr-3]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-calltrace]\n";
//         $text .="include => app-calltrace-custom\n";
//         $text .="exten => *69,1,Goto(app-calltrace-perform,s,1)\n";
//         $text .="\n";
//         $text .="; end of [app-calltrace]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-calltrace-perform]\n";
//         $text .="include => app-calltrace-perform-custom\n";
//         $text .="exten => s,1,Answer\n";
//         $text .="exten => s,n,Wait(1)\n";
//         $text .="exten => s,n,Macro(user-callerid,)\n";
//         $text .="exten => s,n,Playback(info-about-last-call&telephone-number)\n";
// //         $text .="exten => s,n,Set(lastcaller=${DB(CALLTRACE/\${AMPUSER})})\n";
//         $text .="exten => s,n,GotoIf($[ $[ \"\${lastcaller}\" = \"\" ] | $[ \"\${lastcaller}\" = \"unknown\" ] ]?noinfo)\n";
//         $text .="exten => s,n,SayDigits(\${lastcaller})\n";
//         $text .="exten => s,n,Set(TIMEOUT(digit)=3)\n";
//         $text .="exten => s,n,Set(TIMEOUT(response)=7)\n";
//         $text .="exten => s,n,Background(to-call-this-number&press-1)\n";
//         $text .="exten => s,n,Goto(fin)\n";
//         $text .="exten => s,n(noinfo),Playback(from-unknown-caller)\n";
//         $text .="exten => s,n,Macro(hangupcall,)\n";
//         $text .="exten => s,n(fin),Noop(Waiting for input)\n";
//         $text .="exten => s,n,WaitExten(60,)\n";
//         $text .="exten => s,n,Playback(sorry-youre-having-problems&goodbye)\n";
//         $text .="exten => 1,1,Goto(from-internal,\${lastcaller},1)\n";
//         $text .="exten => i,1,Playback(vm-goodbye)\n";
//         $text .="exten => i,n,Macro(hangupcall,)\n";
//         $text .="exten => t,1,Playback(vm-goodbye)\n";
//         $text .="exten => t,n,Macro(hangupcall,)\n";
//         $text .="\n";
//         $text .="; end of [app-calltrace-perform]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-directory]\n";
//         $text .="include => app-directory-custom\n";
//         $text .="exten => #,1,Answer\n";
//         $text .="exten => #,n,Wait(1)\n";
// //         $text .="exten => #,n,AGI(directory,${DIR-CONTEXT},from-did-direct,${DIRECTORY:0:1}\${DIRECTORY_OPTS})\n";
//         $text .="exten => #,n,Playback(vm-goodbye)\n";
//         $text .="exten => #,n,Hangup\n";
//         $text .="exten => i,1,Playback(privacy-incorrect)\n";
//         $text .="\n";
//         $text .="; end of [app-directory]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-echo-test]\n";
//         $text .="include => app-echo-test-custom\n";
//         $text .="exten => *43,1,Answer\n";
//         $text .="exten => *43,n,Wait(1)\n";
//         $text .="exten => *43,n,Playback(demo-echotest)\n";
//         $text .="exten => *43,n,Echo()\n";
//         $text .="exten => *43,n,Playback(demo-echodone)\n";
//         $text .="exten => *43,n,Hangup\n";
//         $text .="\n";
//         $text .="; end of [app-echo-test]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-speakextennum]\n";
//         $text .="include => app-speakextennum-custom\n";
//         $text .="exten => *65,1,Answer\n";
//         $text .="exten => *65,n,Wait(1)\n";
//         $text .="exten => *65,n,Macro(user-callerid,)\n";
//         $text .="exten => *65,n,Playback(your)\n";
//         $text .="exten => *65,n,Playback(extension)\n";
//         $text .="exten => *65,n,Playback(number)\n";
//         $text .="exten => *65,n,Playback(is)\n";
//         $text .="exten => *65,n,SayDigits(\${AMPUSER})\n";
//         $text .="exten => *65,n,Wait(2)\n";
//         $text .="exten => *65,n,Hangup\n";
//         $text .="\n";
//         $text .="; end of [app-speakextennum]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-speakingclock]\n";
//         $text .="include => app-speakingclock-custom\n";
//         $text .="exten => *60,1,Answer\n";
//         $text .="exten => *60,n,Wait(1)\n";
//         $text .="exten => *60,n,Set(NumLoops=0)\n";
//         $text .="exten => *60,n(start),Set(FutureTime=$[\${EPOCH} + 11])\n";
//         $text .="exten => *60,n,Playback(at-tone-time-exactly)\n";
//         $text .="exten => *60,n,GotoIf($[\"\${TIMEFORMAT}\" = \"kM\"]?hr24format)\n";
//         $text .="exten => *60,n,SayUnixTime(\${FutureTime},,IM \'and\' S \'seconds\' p)\n";
//         $text .="exten => *60,n,Goto(waitloop)\n";
//         $text .="exten => *60,n(hr24format),SayUnixTime(\${FutureTime},,kM \'and\' S \'seconds\')\n";
//         $text .="exten => *60,n(waitloop),Set(TimeLeft=$[\${FutureTime} - \${EPOCH}])\n";
//         $text .="exten => *60,n,GotoIf($[\${TimeLeft} < 1]?playbeep)\n";
//         $text .="exten => *60,n,Wait(1)\n";
//         $text .="exten => *60,n,Goto(waitloop)\n";
//         $text .="exten => *60,n(playbeep),Playback(beep)\n";
//         $text .="exten => *60,n,Wait(5)\n";
//         $text .="exten => *60,n,Set(NumLoops=$[\${NumLoops} + 1])\n";
//         $text .="exten => *60,n,GotoIf($[\${NumLoops} < 5]?start)\n";
//         $text .="exten => *60,n,Playback(goodbye)\n";
//         $text .="exten => *60,n,Hangup\n";
//         $text .="\n";
//         $text .="; end of [app-speakingclock]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[macro-speeddial-lookup]\n";
//         $text .="include => macro-speeddial-lookup-custom\n";
//         $text .="exten => s,1,GotoIf($[\"\${ARG2}\"=\"\"]]?lookupsys)\n";
//         $text .="exten => s,n,Set(SPEEDDIALNUMBER=)\n";
// //         $text .="exten => s,n(lookupuser),Set(SPEEDDIALNUMBER=${DB(AMPUSER/\${ARG2}/speeddials/\${ARG1})})\n";
//         $text .="exten => s,n,GotoIf($[\"\${SPEEDDIALNUMBER}\"=\"\"]?lookupsys)\n";
//         $text .="exten => s,n,Noop(Found speeddial \${ARG1} for user \${ARG2}: \${SPEEDDIALNUMBER})\n";
//         $text .="exten => s,n,Goto(end)\n";
// //         $text .="exten => s,lookupuser+101(lookupsys),Set(SPEEDDIALNUMBER=${DB(sysspeeddials/\${ARG1})})\n";
//         $text .="exten => s,n,GotoIf($[\"\${SPEEDDIALNUMBER}\"=\"\"]?failed)\n";
//         $text .="exten => s,n,Noop(Found system speeddial \${ARG1}: \${SPEEDDIALNUMBER})\n";
//         $text .="exten => s,n,Goto(end)\n";
//         $text .="exten => s,lookupsys+101(failed),Noop(No system or user speeddial found)\n";
//         $text .="exten => s,n(end),Noop(End of Speeddial-lookup)\n";
//         $text .="\n";
//         $text .="; end of [macro-speeddial-lookup]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-speeddial]\n";
//         $text .="include => app-speeddial-custom\n";
//         $text .="exten => _*0.,1,Macro(user-callerid,)\n";
// //         $text .="exten => _*0.,n,Set(SPEEDDIALLOCATION=${EXTEN:2})\n";
//         $text .="exten => _*0.,n(lookup),Macro(speeddial-lookup,\${SPEEDDIALLOCATION},\${AMPUSER})\n";
//         $text .="exten => _*0.,n,GotoIf($[\"\${SPEEDDIALNUMBER}\"=\"\"]?failed)\n";
//         $text .="exten => _*0.,n,Dial(Local/\${SPEEDDIALNUMBER}@from-internal/n,)\n";
//         $text .="exten => _*0.,lookup+101(failed),Playback(speed-dial-empty)\n";
//         $text .="exten => _*0.,n,Congestion()\n";
//         $text .="exten => *75,1,Goto(app-speeddial-set,s,1)\n";
//         $text .="\n";
//         $text .="; end of [app-speeddial]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-speeddial-set]\n";
//         $text .="include => app-speeddial-set-custom\n";
//         $text .="exten => s,1,Macro(user-callerid,)\n";
//         $text .="exten => s,n(setloc),Read(newlocation,speed-enterlocation,,,,)\n";
//         $text .="exten => s,n(lookup),Macro(speeddial-lookup,\${newlocation},\${AMPUSER})\n";
//         $text .="exten => s,n(lookup),GotoIf($[\"\${SPEEDDIALNUMBER}\"!=\"\"]?conflicts)\n";
//         $text .="exten => s,n(setnum),Read(newnum,speed-enternumber,,,,)\n";
//         $text .="exten => s,n(success),Set(DB(AMPUSER/\${AMPUSER}/speeddials/\${newlocation})=\${newnum})\n";
//         $text .="exten => s,n,Playback(speed-dial)\n";
//         $text .="exten => s,n,SayDigits(\${newlocation})\n";
//         $text .="exten => s,n,Playback(is-set-to)\n";
//         $text .="exten => s,n,SayDigits(\${newnum})\n";
//         $text .="exten => s,n,Hangup\n";
//         $text .="exten => s,n(conflicts),Playback(speed-dial)\n";
//         $text .="exten => s,n,SayDigits(\${newlocation})\n";
//         $text .="exten => s,n,Playback(is-in-use)\n";
//         $text .="exten => s,n,Background(press-1&to-listen-to-it&press-2&to-enter-a-diff&location&press-3&to-change&telephone-number)\n";
//         $text .="exten => s,n,WaitExten(60,)\n";
//         $text .="exten => 1,1,Playback(speed-dial)\n";
//         $text .="exten => 1,n,SayDigits(\${newlocation})\n";
//         $text .="exten => 1,n,Playback(is-set-to)\n";
//         $text .="exten => 1,n,SayDigits(\${SPEEDDIALNUMBER})\n";
//         $text .="exten => 1,n,Goto(s,conflicts)\n";
//         $text .="exten => 2,1,Goto(s,setloc)\n";
//         $text .="exten => 3,1,Goto(s,setnum)\n";
//         $text .="exten => t,1,Congestion()\n";
//         $text .="\n";
//         $text .="; end of [app-speeddial-set]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-dnd-off]\n";
//         $text .="include => app-dnd-off-custom\n";
//         $text .="exten => *79,1,Answer\n";
//         $text .="exten => *79,n,Wait(1)\n";
//         $text .="exten => *79,n,Macro(user-callerid,)\n";
//         $text .="exten => *79,n,dbDel(DND/\${AMPUSER})\n";
//         $text .="exten => *79,n,Set(STATE=NOT_INUSE)\n";
//         $text .="exten => *79,n,Gosub(app-dnd-off,sstate,1)\n";
//         $text .="exten => *79,n,Playback(do-not-disturb&de-activated)\n";
//         $text .="exten => *79,n,Macro(hangupcall,)\n";
//         $text .="exten => sstate,1,Set(DEVSTATE(Custom:DND\${AMPUSER})=\${STATE})\n";
// //         $text .="exten => sstate,n,Set(DEVICES=${DB(AMPUSER/\${AMPUSER}/device)})\n";
//         $text .="exten => sstate,n,GotoIf($[\"\${DEVICES}\" = \"\" ]?return)\n";
// //         $text .="exten => sstate,n,Set(LOOPCNT=${FIELDQTY(DEVICES,&)})\n";
//         $text .="exten => sstate,n,Set(ITER=1)\n";
// //         $text .="exten => sstate,n(begin),Set(DEVSTATE(Custom:DEVDND${CUT(DEVICES,&,\${ITER})})=\${STATE})";
//         $text .="exten => sstate,n,Set(ITER=$[\${ITER} + 1])\n";
//         $text .="exten => sstate,n,GotoIf($[\${ITER} <= \${LOOPCNT}]?begin)\n";
//         $text .="exten => sstate,n(return),Return()\n";
//         $text .="\n";
//         $text .="; end of [app-dnd-off]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-dnd-on]\n";
//         $text .="include => app-dnd-on-custom\n";
//         $text .="exten => *78,1,Answer\n";
//         $text .="exten => *78,n,Wait(1)\n";
//         $text .="exten => *78,n,Macro(user-callerid,)\n";
//         $text .="exten => *78,n,Set(DB(DND/\${AMPUSER})=YES)\n";
//         $text .="exten => *78,n,Set(STATE=BUSY)\n";
//         $text .="exten => *78,n,Gosub(app-dnd-on,sstate,1)\n";
//         $text .="exten => *78,n,Playback(do-not-disturb&activated)\n";
//         $text .="exten => *78,n,Macro(hangupcall,)\n";
//         $text .="exten => sstate,1,Set(DEVSTATE(Custom:DND\${AMPUSER})=\${STATE})\n";
// //         $text .="exten => sstate,n,Set(DEVICES=${DB(AMPUSER/\${AMPUSER}/device)})\n";
//         $text .="exten => sstate,n,GotoIf($[\"\${DEVICES}\" = \"\" ]?return)\n";
// //         $text .="exten => sstate,n,Set(LOOPCNT=${FIELDQTY(DEVICES,&)})\n";
//         $text .="exten => sstate,n,Set(ITER=1)\n";
// //         $text .="exten => sstate,n(begin),Set(DEVSTATE(Custom:DEVDND${CUT(DEVICES,&,\${ITER})})=\${STATE})\n";
//         $text .="exten => sstate,n,Set(ITER=$[\${ITER} + 1])\n";
//         $text .="exten => sstate,n,GotoIf($[\${ITER} <= \${LOOPCNT}]?begin)\n";
//         $text .="exten => sstate,n(return),Return()\n";
//         $text .="\n";
//         $text .="; end of [app-dnd-on]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-dnd-toggle]\n";
//         $text .="include => app-dnd-toggle-custom\n";
//         $text .="exten => *76,1,Answer\n";
//         $text .="exten => *76,n,Wait(1)\n";
//         $text .="exten => *76,n,Macro(user-callerid,)\n";
// //         $text .="exten => *76,n,GotoIf($[\"${DB(DND/\${AMPUSER})}\" = \"\"]?activate:deactivate)\n";
//         $text .="exten => *76,n(activate),Set(DB(DND/\${AMPUSER})=YES)\n";
//         $text .="exten => *76,n,Set(STATE=BUSY)\n";
//         $text .="exten => *76,n,Gosub(app-dnd-toggle,sstate,1)\n";
//         $text .="exten => *76,n,Playback(do-not-disturb&activated)\n";
//         $text .="exten => *76,n,Macro(hangupcall,)\n";
//         $text .="exten => *76,n(deactivate),dbDel(DND/\${AMPUSER})\n";
//         $text .="exten => *76,n,Set(STATE=NOT_INUSE)\n";
//         $text .="exten => *76,n,Gosub(app-dnd-toggle,sstate,1)\n";
//         $text .="exten => *76,n,Playback(do-not-disturb&de-activated)\n";
//         $text .="exten => *76,n,Macro(hangupcall,)\n";
//         $text .="exten => sstate,1,Set(DEVSTATE(Custom:DND\${AMPUSER})=\${STATE})\n";
// //         $text .="exten => sstate,n,Set(DEVICES=${DB(AMPUSER/\${AMPUSER}/device)})\n";
//         $text .="exten => sstate,n,GotoIf($[\"\${DEVICES}\" = \"\" ]?return)\n";
// //         $text .="exten => sstate,n,Set(LOOPCNT=${FIELDQTY(DEVICES,&)})\n";
//         $text .="exten => sstate,n,Set(ITER=1)\n";
// //         $text .="exten => sstate,n(begin),Set(DEVSTATE(Custom:DEVDND${CUT(DEVICES,&,\${ITER})})=\${STATE})\n";
//         $text .="exten => sstate,n,Set(ITER=$[\${ITER} + 1])\n";
//         $text .="exten => sstate,n,GotoIf($[\${ITER} <= \${LOOPCNT}]?begin)\n";
//         $text .="exten => sstate,n(return),Return()\n";
//         $text .="\n";
//         $text .="; end of [app-dnd-toggle]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[cidlookup]\n";
//         $text .="include => cidlookup-custom\n";
//         $text .="exten => cidlookup_return,1,LookupCIDName\n";
//         $text .="exten => cidlookup_return,n,Return()\n";
//         $text .="\n";
//         $text .="; end of [cidlookup]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-fmf-toggle]\n";
//         $text .="include => app-fmf-toggle-custom\n";
//         $text .="exten => *21,1,Goto(app-fmf-toggle,s,start)\n";
//         $text .="exten => s,1(start),Answer\n";
//         $text .="exten => s,n,Wait(1)\n";
//         $text .="exten => s,n,Macro(user-callerid,)\n";
//         $text .="exten => s,n,GotoIf($[\"\${DB(AMPUSER/\${AMPUSER}/followme/ddial)}\" = \"EXTENSION\"]?activate)\n";
//         $text .="exten => s,n,GotoIf($[\"\${DB(AMPUSER/\${AMPUSER}/followme/ddial)}\" = \"DIRECT\"]?deactivate:end)\n";
//         $text .="exten => s,n(deactivate),Set(DB(AMPUSER/\${AMPUSER}/followme/ddial)=EXTENSION)\n";
//         $text .="exten => s,n,Set(STATE=NOT_INUSE)\n";
//         $text .="exten => s,n,Gosub(app-fmf-toggle,sstate,1)\n";
//         $text .="exten => s,n,Playback(followme&de-activated)\n";
//         $text .="exten => s,n(end),Macro(hangupcall,)\n";
//         $text .="exten => s,n(activate),Set(DB(AMPUSER/\${AMPUSER}/followme/ddial)=DIRECT)\n";
//         $text .="exten => s,n,Set(STATE=INUSE)\n";
//         $text .="exten => s,n,Gosub(app-fmf-toggle,sstate,1)\n";
//         $text .="exten => s,n,Playback(followme&activated)\n";
//         $text .="exten => s,n,Macro(hangupcall,)\n";
// //         $text .="exten => sstate,1,Set(DEVICES=${DB(AMPUSER/\${AMPUSER}/device)})\n";
//         $text .="exten => sstate,n,GotoIf($[\"\${DEVICES}\" = \"\" ]?return)\n";
// //         $text .="exten => sstate,n,Set(LOOPCNT=${FIELDQTY(DEVICES,&)})\n";
//         $text .="exten => sstate,n,Set(ITER=1)\n";
// //         $text .="exten => sstate,n(begin),Set(DEVSTATE(Custom:FOLLOWME${CUT(DEVICES,&,\${ITER})})=\${STATE})\n";
//         $text .="exten => sstate,n,Set(ITER=$[\${ITER} + 1])\n";
//         $text .="exten => sstate,n,GotoIf($[\${ITER} <= \${LOOPCNT}]?begin)\n";
//         $text .="exten => sstate,n(return),Return()\n";
//         $text .="\n";
//         $text .="; end of [app-fmf-toggle]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-dialvm]\n";
//         $text .="include => app-dialvm-custom\n";
//         $text .="exten => *98,1,Answer\n";
//         $text .="exten => *98,n(start),Wait(1)\n";
//         $text .="exten => *98,n,Noop(app-dialvm: Asking for mailbox)\n";
//         $text .="exten => *98,n,Read(MAILBOX,vm-login,,,3,2)\n";
//         $text .="exten => *98,n(check),Noop(app-dialvm: Got Mailbox \${MAILBOX})\n";
//         $text .="exten => *98,n,Macro(get-vmcontext,\${MAILBOX})";
//         $text .="exten => *98,n,MailBoxExists(\${MAILBOX}@\${VMCONTEXT})\n";
//         $text .="exten => *98,n,GotoIf($[\"\${VMBOXEXISTSSTATUS}\" = \"SUCCESS\"]?good:bad)\n";
//         $text .="exten => *98,n,Macro(hangupcall,)\n";
//         $text .="exten => *98,n(good),Noop(app-dialvm: Good mailbox \${MAILBOX}@\${VMCONTEXT})\n";
//         $text .="exten => *98,n,VoiceMailMain(\${MAILBOX}@\${VMCONTEXT})\n";
//         $text .="exten => *98,n,GotoIf($[\"\${IVR_RETVM}\" = \"RETURN\" & \"\${IVR_CONTEXT}\" != \"\"]?playret)\n";
//         $text .="exten => *98,n,Macro(hangupcall,)\n";
//         $text .="exten => *98,n(bad),Noop(app-dialvm: BAD mailbox \${MAILBOX}@\${VMCONTEXT})\n";
//         $text .="exten => *98,n,Wait(1)\n";
//         $text .="exten => *98,n,Noop(app-dialvm: Asking for password so people can't probe for existence of a mailbox)\n";
//         $text .="exten => *98,n,Read(FAKEPW,vm-password,,,3,2)\n";
//         $text .="exten => *98,n,Noop(app-dialvm: Asking for mailbox again)\n";
//         $text .="exten => *98,n,Read(MAILBOX,vm-incorrect-mailbox,,,3,2)\n";
//         $text .="exten => *98,n,Goto(check)\n";
//         $text .="exten => *98,n,Macro(hangupcall,)\n";
//         $text .="exten => *98,n(playret),Playback(beep&you-will-be-transfered-menu&silence/1)\n";
//         $text .="exten => *98,n,Goto(\${IVR_CONTEXT},return,1)\n";
//         $text .="exten => _*98.,1,Answer\n";
//         $text .="exten => _*98.,n,Wait(1)\n";
// //         $text .="exten => _*98.,n,Macro(get-vmcontext,${EXTEN:3})\n";
// //         $text .="exten => _*98.,n,VoiceMailMain(${EXTEN:3}@\${VMCONTEXT})\n";
//         $text .="exten => _*98.,n,GotoIf($[\"\${IVR_RETVM}\" = \"RETURN\" & \"\${IVR_CONTEXT}\" != \"\"]?\${IVR_CONTEXT},return,1)\n";
//         $text .="exten => _*98.,n,Macro(hangupcall,)\n";
//         $text .="\n";
//         $text .="; end of [app-dialvm]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-vmmain]\n";
//         $text .="include => app-vmmain-custom\n";
//         $text .="exten => *97,1,Answer\n";
//         $text .="exten => *97,n,Wait(1)\n";
//         $text .="exten => *97,n,Macro(user-callerid,)\n";
//         $text .="exten => *97,n,Macro(get-vmcontext,\${AMPUSER})\n";
//         $text .="exten => *97,n(check),MailBoxExists(\${AMPUSER}@\${VMCONTEXT})\n";
//         $text .="exten => *97,n,GotoIf($[\"\${VMBOXEXISTSSTATUS}\" = \"SUCCESS\"]?mbexist)\n";
//         $text .="exten => *97,n,VoiceMailMain()\n";
//         $text .="exten => *97,n,GotoIf($[\"\${IVR_RETVM}\" = \"RETURN\" & \"\${IVR_CONTEXT}\" != \"\"]?playret)\n";
//         $text .="exten => *97,n,Macro(hangupcall,)\n";
//         $text .="exten => *97,check+101(mbexist),VoiceMailMain(\${AMPUSER}@\${VMCONTEXT})\n";
//         $text .="exten => *97,n,GotoIf($[\"\${IVR_RETVM}\" = \"RETURN\" & \"\${IVR_CONTEXT}\" != \"\"]?playret)\n";
//         $text .="exten => *97,n,Macro(hangupcall,)\n";
//         $text .="exten => *97,n(playret),Playback(beep&you-will-be-transfered-menu&silence/1)\n";
//         $text .="exten => *97,n,Goto(\${IVR_CONTEXT},return,1)\n";
//         $text .="\n";
//         $text .="; end of [app-vmmain]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-cf-busy-off]\n";
//         $text .="include => app-cf-busy-off-custom\n";
//         $text .="exten => *91,1,Answer\n";
//         $text .="exten => *91,n,Wait(1)\n";
//         $text .="exten => *91,n,Macro(user-callerid,)\n";
//         $text .="exten => *91,n,dbDel(CFB/\${AMPUSER})\n";
//         $text .="exten => *91,n,Playback(call-fwd-on-busy&de-activated)\n";
//         $text .="exten => *91,n,Macro(hangupcall,)\n";
//         $text .="exten => _*91.,1,Answer\n";
//         $text .="exten => _*91.,n,Wait(1)\n";
// //         $text .="exten => _*91.,n,Set(fromext=${EXTEN:3})\n";
//         $text .="exten => _*91.,n,dbDel(CFB/\${fromext})\n";
//         $text .="exten => _*91.,n,Playback(call-fwd-on-busy&for&extension)\n";
//         $text .="exten => _*91.,n,SayDigits(\${fromext})\n";
//         $text .="exten => _*91.,n,Playback(cancelled)\n";
//         $text .="exten => _*91.,n,Macro(hangupcall,)\n";
//         $text .="\n";
//         $text .="; end of [app-cf-busy-off]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-cf-busy-off-any]\n";
//         $text .="include => app-cf-busy-off-any-custom\n";
//         $text .="exten => *92,1,Answer\n";
//         $text .="exten => *92,n,Wait(1)\n";
//         $text .="exten => *92,n,Playback(please-enter-your&extension)\n";
//         $text .="exten => *92,n,Read(fromext,then-press-pound,,,,)\n";
//         $text .="exten => *92,n,Wait(1)\n";
//         $text .="exten => *92,n,dbDel(CFB/\${fromext})\n";
//         $text .="exten => *92,n,Playback(call-fwd-on-busy&for&extension)\n";
//         $text .="exten => *92,n,SayDigits(\${fromext})\n";
//         $text .="exten => *92,n,Playback(cancelled)\n";
//         $text .="exten => *92,n,Macro(hangupcall,)\n";
//         $text .="\n";
//         $text .="; end of [app-cf-busy-off-any]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-cf-busy-on]\n";
//         $text .="include => app-cf-busy-on-custom\n";
//         $text .="exten => *90,1,Answer\n";
//         $text .="exten => *90,n,Wait(1)\n";
//         $text .="exten => *90,n,Macro(user-callerid,)\n";
//         $text .="exten => *90,n,Playback(call-fwd-on-busy)\n";
//         $text .="exten => *90,n,Playback(please-enter-your&extension)\n";
//         $text .="exten => *90,n,Read(fromext,then-press-pound,,,,)\n";
// //         $text .="exten => *90,n,Set(fromext=${IF($[\"foo\${fromext}\"=\"foo\"]?\${AMPUSER}:\${fromext})})\n";
//         $text .="exten => *90,n,Wait(1)\n";
//         $text .="exten => *90,n(startread),Playback(ent-target-attendant)\n";
//         $text .="exten => *90,n,Read(toext,then-press-pound,,,,)\n";
//         $text .="exten => *90,n,GotoIf($[\"foo\${toext}\"=\"foo\"]?startread)\n";
//         $text .="exten => *90,n,Wait(1)\n";
//         $text .="exten => *90,n,Set(DB(CFB/\${fromext})=\${toext})\n";
//         $text .="exten => *90,n,Playback(call-fwd-on-busy&for&extension)\n";
//         $text .="exten => *90,n,SayDigits(\${fromext})\n";
//         $text .="exten => *90,n,Playback(is-set-to)\n";
//         $text .="exten => *90,n,SayDigits(\${toext})\n";
//         $text .="exten => *90,n,Macro(hangupcall,)\n";
//         $text .="exten => _*90.,1,Answer\n";
//         $text .="exten => _*90.,n,Wait(1)\n";
//         $text .="exten => _*90.,n,Macro(user-callerid,)\n";
// //         $text .="exten => _*90.,n,Set(DB(CFB/\${AMPUSER})=${EXTEN:3})\n";
//         $text .="exten => _*90.,n,Playback(call-fwd-on-busy&for&extension)\n";
//         $text .="exten => _*90.,n,SayDigits(\${AMPUSER})\n";
//         $text .="exten => _*90.,n,Playback(is-set-to)\n";
// //         $text .="exten => _*90.,n,SayDigits(${EXTEN:3})\n";
//         $text .="exten => _*90.,n,Macro(hangupcall,)\n";
//         $text .="\n";
//         $text .="; end of [app-cf-busy-on]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-cf-off]\n";
//         $text .="include => app-cf-off-custom\n";
//         $text .="exten => *73,1,Answer\n";
//         $text .="exten => *73,n,Wait(1)\n";
//         $text .="exten => *73,n,Macro(user-callerid,)\n";
//         $text .="exten => *73,n,dbDel(CF/\${AMPUSER})\n";
//         $text .="exten => *73,n,Playback(call-fwd-unconditional&de-activated)\n";
//         $text .="exten => *73,n,Macro(hangupcall,)\n";
//         $text .="exten => _*73.,1,Answer\n";
//         $text .="exten => _*73.,n,Wait(1)\n";
// //         $text .="exten => _*73.,n,Set(fromext=${EXTEN:3})\n";
//         $text .="exten => _*73.,n,dbDel(CF/\${fromext})\n";
//         $text .="exten => _*73.,n,Playback(call-fwd-unconditional&for&extension)\n";
//         $text .="exten => _*73.,n,SayDigits(\${fromext})\n";
//         $text .="exten => _*73.,n,Playback(cancelled)\n";
//         $text .="exten => _*73.,n,Macro(hangupcall,)\n";
//         $text .="\n";
//         $text .="; end of [app-cf-off]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-cf-off-any]\n";
//         $text .="include => app-cf-off-any-custom\n";
//         $text .="exten => *74,1,Answer\n";
//         $text .="exten => *74,n,Wait(1)\n";
//         $text .="exten => *74,n,Playback(please-enter-your&extension)\n";
//         $text .="exten => *74,n,Read(fromext,then-press-pound,,,,)\n";
//         $text .="exten => *74,n,Wait(1)\n";
//         $text .="exten => *74,n,dbDel(CF/\${fromext})\n";
//         $text .="exten => *74,n,Playback(call-fwd-unconditional&for&extension)\n";
//         $text .="exten => *74,n,SayDigits(\${fromext})\n";
//         $text .="exten => *74,n,Playback(cancelled)\n";
//         $text .="exten => *74,n,Macro(hangupcall,)\n";
//         $text .="\n";
//         $text .="; end of [app-cf-off-any]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-cf-on]\n";
//         $text .="include => app-cf-on-custom\n";
//         $text .="exten => *72,1,Answer\n";
//         $text .="exten => *72,n,Wait(1)\n";
//         $text .="exten => *72,n,Macro(user-callerid,)\n";
//         $text .="exten => *72,n,Playback(call-fwd-unconditional)\n";
//         $text .="exten => *72,n,Playback(please-enter-your&extension)\n";
//         $text .="exten => *72,n,Read(fromext,then-press-pound,,,,)\n";
// //         $text .="exten => *72,n,Set(fromext=${IF($[\"foo\${fromext}\"=\"foo\"]?\${AMPUSER}:\${fromext})})\n";
//         $text .="exten => *72,n,Wait(1)\n";
//         $text .="exten => *72,n(startread),Playback(ent-target-attendant)\n";
//         $text .="exten => *72,n,Read(toext,then-press-pound,,,,)\n";
//         $text .="exten => *72,n,GotoIf($[\"foo\${toext}\"=\"foo\"]?startread)\n";
//         $text .="exten => *72,n,Wait(1)\n";
//         $text .="exten => *72,n,Set(DB(CF/\${fromext})=\${toext})\n";
//         $text .="exten => *72,n,Playback(call-fwd-unconditional&for&extension)\n";
//         $text .="exten => *72,n,SayDigits(\${fromext})\n";
//         $text .="exten => *72,n,Playback(is-set-to)\n";
//         $text .="exten => *72,n,SayDigits(\${toext})\n";
//         $text .="exten => *72,n,Macro(hangupcall,)\n";
//         $text .="exten => _*72.,1,Answer\n";
//         $text .="exten => _*72.,n,Wait(1)\n";
//         $text .="exten => _*72.,n,Macro(user-callerid,)\n";
// //         $text .="exten => _*72.,n,Set(DB(CF/\${AMPUSER})=${EXTEN:3})\n";
//         $text .="exten => _*72.,n,Playback(call-fwd-unconditional&for&extension)\n";
//         $text .="exten => _*72.,n,SayDigits(\${AMPUSER})\n";
//         $text .="exten => _*72.,n,Playback(is-set-to)\n";
// //         $text .="exten => _*72.,n,SayDigits(${EXTEN:3})\n";
//         $text .="exten => _*72.,n,Macro(hangupcall,)\n";
//         $text .="\n";
//         $text .="; end of [app-cf-on]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-cf-unavailable-off]\n";
//         $text .="include => app-cf-unavailable-off-custom\n";
//         $text .="exten => *53,1,Answer\n";
//         $text .="exten => *53,n,Wait(1)\n";
//         $text .="exten => *53,n,Macro(user-callerid,)\n";
//         $text .="exten => *53,n,dbDel(CFU/\${AMPUSER})\n";
//         $text .="exten => *53,n,Playback(call-fwd-no-ans&de-activated)\n";
//         $text .="exten => *53,n,Macro(hangupcall,)\n";
//         $text .="exten => _*53.,1,Answer\n";
//         $text .="exten => _*53.,n,Wait(1)\n";
// //         $text .="exten => _*53.,n,Set(fromext=${EXTEN:3})\n";
//         $text .="exten => _*53.,n,dbDel(CFU/\${fromext})\n";
//         $text .="exten => _*53.,n,Playback(call-fwd-no-ans&for&extension)\n";
//         $text .="exten => _*53.,n,SayDigits(\${fromext})\n";
//         $text .="exten => _*53.,n,Playback(cancelled)\n";
//         $text .="exten => _*53.,n,Macro(hangupcall,)\n";
//         $text .="\n";
//         $text .="; end of [app-cf-unavailable-off]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-cf-unavailable-on]\n";
//         $text .="include => app-cf-unavailable-on-custom\n";
//         $text .="exten => *52,1,Answer\n";
//         $text .="exten => *52,n,Wait(1)\n";
//         $text .="exten => *52,n,Macro(user-callerid,)\n";
//         $text .="exten => *52,n,Playback(call-fwd-no-ans)\n";
//         $text .="exten => *52,n,Playback(please-enter-your&extension)\n";
//         $text .="exten => *52,n,Read(fromext,then-press-pound,,,,)\n";
// //         $text .="exten => *52,n,Set(fromext=${IF($[\"foo\${fromext}\"=\"foo\"]?\${AMPUSER}:\${fromext})})\n";
//         $text .="exten => *52,n,Wait(1)\n";
//         $text .="exten => *52,n(startread),Playback(ent-target-attendant)\n";
//         $text .="exten => *52,n,Read(toext,then-press-pound,,,,)\n";
//         $text .="exten => *52,n,GotoIf($[\"foo\${toext}\"=\"foo\"]?startread)\n";
//         $text .="exten => *52,n,Wait(1)\n";
//         $text .="exten => *52,n,Set(DB(CFU/\${fromext})=\${toext})\n";
//         $text .="exten => *52,n,Playback(call-fwd-no-ans&for&extension)\n";
//         $text .="exten => *52,n,SayDigits(\${fromext})\n";
//         $text .="exten => *52,n,Playback(is-set-to)\n";
//         $text .="exten => *52,n,SayDigits(\${toext})\n";
//         $text .="exten => *52,n,Macro(hangupcall,)\n";
//         $text .="exten => _*52.,1,Answer\n";
//         $text .="exten => _*52.,n,Wait(1)\n";
//         $text .="exten => _*52.,n,Macro(user-callerid,)\n";
// //         $text .="exten => _*52.,n,Set(DB(CFU/\${AMPUSER})=${EXTEN:3})\n";
//         $text .="exten => _*52.,n,Playback(call-fwd-no-ans&for&extension)\n";
//         $text .="exten => _*52.,n,SayDigits(\${AMPUSER})\n";
//         $text .="exten => _*52.,n,Playback(is-set-to)\n";
// //         $text .="exten => _*52.,n,SayDigits(${EXTEN:3})\n";
//         $text .="exten => _*52.,n,Macro(hangupcall,)\n";
//         $text .="\n";
//         $text .="; end of [app-cf-unavailable-on]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-callwaiting-cwoff]\n";
//         $text .="include => app-callwaiting-cwoff-custom\n";
//         $text .="exten => *71,1,Answer\n";
//         $text .="exten => *71,n,Wait(1)\n";
//         $text .="exten => *71,n,Macro(user-callerid,)\n";
//         $text .="exten => *71,n,dbDel(CW/\${AMPUSER})\n";
//         $text .="exten => *71,n,Playback(call-waiting&de-activated)\n";
//         $text .="exten => *71,n,Macro(hangupcall,)\n";
//         $text .="\n";
//         $text .="; end of [app-callwaiting-cwoff]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-callwaiting-cwon]\n";
//         $text .="include => app-callwaiting-cwon-custom\n";
//         $text .="exten => *70,1,Answer\n";
//         $text .="exten => *70,n,Wait(1)\n";
//         $text .="exten => *70,n,Macro(user-callerid,)\n";
//         $text .="exten => *70,n,Set(DB(CW/\${AMPUSER})=ENABLED)\n";
//         $text .="exten => *70,n,Playback(call-waiting&activated)\n";
//         $text .="exten => *70,n,Macro(hangupcall,)\n";
//         $text .="\n";
//         $text .="; end of [app-callwaiting-cwon]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-dictate-record]\n";
//         $text .="include => app-dictate-record-custom\n";
//         $text .="exten => *34,1,Answer\n";
//         $text .="exten => *34,n,Macro(user-callerid,)\n";
//         $text .="exten => *34,n,Noop(CallerID is \${AMPUSER})\n";
// //         $text .="exten => *34,n,Set(DICTENABLED=${DB(AMPUSER/\${AMPUSER}/dictate/enabled)})\n";
//         $text .="exten => *34,n,GotoIf($[$[\"x\${DICTENABLED}\"=\"x\"]|$[\"x\${DICTENABLED}\"=\"xdisabled\"]]?nodict:dictok)\n";
//         $text .="exten => *34,n(nodict),Playback(feature-not-avail-line)\n";
//         $text .="exten => *34,n,Hangup\n";
//         $text .="exten => *34,n(dictok),Dictate(/var/lib/asterisk/sounds/dictate/\${AMPUSER})\n";
//         $text .="exten => *34,n,Macro(hangupcall,)\n";
//         $text .="\n";
//         $text .="; end of [app-dictate-record]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-dictate-send]\n";
//         $text .="include => app-dictate-send-custom\n";
//         $text .="exten => *35,1,Answer\n";
//         $text .="exten => *35,n,Macro(user-callerid,)\n";
//         $text .="exten => *35,n,Noop(CallerID is \${AMPUSER})\n";
// //         $text .="exten => *35,n,Set(DICTENABLED=${DB(AMPUSER/\${AMPUSER}/dictate/enabled)})\n";
//         $text .="exten => *35,n,GotoIf($[$[\"x\${DICTENABLED}\"=\"x\"]|$[\"x\${DICTENABLED}\"=\"xdisabled\"]]?nodict:dictok)\n";
//         $text .="exten => *35,n(nodict),Playback(feature-not-avail-line)\n";
//         $text .="exten => *35,n,Hangup\n";
//         $text .="exten => *35,n(dictok),Read(DICTFILE,enter-filename-short,,,,)\n";
// //         $text .="exten => *35,n,Set(DICTEMAIL=${DB(AMPUSER/\${AMPUSER}/dictate/email)})\n";
// //         $text .="exten => *35,n,Set(DICTFMT=${DB(AMPUSER/\${AMPUSER}/dictate/format)})\n";
// //         $text .="exten => *35,n,Set(NAME=${DB(AMPUSER/\${AMPUSER}/cidname)})\n";
//         $text .="exten => *35,n,Playback(dictation-being-processed)\n";
//         $text .="exten => *35,n,System(/var/lib/asterisk/bin/audio-email.pl --file /var/lib/asterisk/sounds/dictate/\${AMPUSER}/\${DICTFILE}.raw --attachment dict-\${DICTFILE} --format \${DICTFMT} --to \${DICTEMAIL} --subject \"Dictation from \${NAME} Attached\")\n";
//         $text .="exten => *35,n,Playback(dictation-sent)\n";
//         $text .="exten => *35,n,Macro(hangupcall,)\n";
//         $text .="\n";
//         $text .="; end of [app-dictate-send]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-recordings]\n";
//         $text .="include => app-recordings-custom\n";
//         $text .="exten => *77,1,Macro(user-callerid,)\n";
//         $text .="exten => *77,n,Wait(2)\n";
//         $text .="exten => *77,n,Macro(systemrecording,dorecord)\n";
//         $text .="exten => *99,1,Macro(user-callerid,)\n";
//         $text .="exten => *99,n,Wait(2)\n";
//         $text .="exten => *99,n,Macro(systemrecording,docheck)\n";
//         $text .="\n";
//         $text .="; end of [app-recordings]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-userlogonoff]\n";
//         $text .="include => app-userlogonoff-custom\n";
//         $text .="exten => *12,1,Macro(user-logoff,)\n";
//         $text .="exten => *12,n,Hangup\n";
//         $text .="exten => *11,1,Macro(user-logon,)\n";
//         $text .="exten => *11,n,Hangup\n";
// //         $text .="exten => _*11.,1,Macro(user-logon,${EXTEN:3},)\n";
//         $text .="exten => _*11.,n,Hangup\n";
//         $text .="\n";
//         $text .="; end of [app-userlogonoff]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-pickup]\n";
//         $text .="include => app-pickup-custom\n";
// //         $text .="exten => _**.,1,Noop(Attempt to Pickup ${EXTEN:2} by ${CALLERID(num)})\n";
// //         $text .="exten => _**.,n,Pickup(${EXTEN:2})\n";
// //         $text .="exten => _**.,n,Pickup(${EXTEN:2}@from-internal)\n";
// //         $text .="exten => _**.,n,Pickup(${EXTEN:2}@from-did-direct)\n";
// //         $text .="exten => _**.,n,Pickup(FMPR-${EXTEN:2})\n";
// //         $text .="exten => _**.,n,Pickup(FMPR-${EXTEN:2}@from-internal)\n";
// //         $text .="exten => _**.,n,Pickup(FMPR-${EXTEN:2}@from-did-direct)\n";
//         $text .="exten => _**.,n,Hangup\n";
//         $text .="\n";
//         $text .="; end of [app-pickup]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-zapbarge]\n";
//         $text .="include => app-zapbarge-custom\n";
//         $text .="exten => 888,1,Macro(user-callerid,)\n";
// //         $text .="exten => 888,n,Set(GROUP()=${CALLERID(number)})\n";
//         $text .="exten => 888,n,Answer\n";
//         $text .="exten => 888,n,Wait(1)\n";
//         $text .="exten => 888,n,DAHDIBarge()\n";
//         $text .="exten => 888,n,Hangup\n";
//         $text .="\n";
//         $text .="; end of [app-zapbarge]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[app-chanspy]\n";
//         $text .="include => app-chanspy-custom\n";
//         $text .="exten => 555,1,Macro(user-callerid,)\n";
//         $text .="exten => 555,n,Answer\n";
//         $text .="exten => 555,n,Wait(1)\n";
//         $text .="exten => 555,n,ChanSpy()\n";
//         $text .="exten => 555,n,Hangup\n";
//         $text .="\n";
//         $text .="; end of [app-chanspy]\n";
//         $text .="\n";
//         $text .="\n"
// //         $text .="[ext-test]\n";
// //         $text .="include => ext-test-custom\n";
// //         $text .="exten => 7777,1,Goto(from-pstn,\${EXTEN},1)\n";
// //         $text .="exten => 666,1,Goto(ext-fax,in_fax,1)\n";
// //         $text .="exten => h,1,Macro(hangupcall,)\n";
//         $text .="\n";
//         $text .="; end of [ext-test]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[ext-did-0001]\n";
//         $text .="include => ext-did-0001-custom\n";
//         $text .="exten => fax,1,Goto(ext-fax,in_fax,1)\n";
//         $text .="\n";
//         $text .="; end of [ext-did-0001]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[ext-did-0002]\n";
//         $text .="include => ext-did-0002-custom\n";
//         $text .="exten => fax,1,Goto(ext-fax,in_fax,1)\n";
//         $text .="\n";
//         $text .="; end of [ext-did-0002]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[ext-did]\n";
//         $text .="include => ext-did-custom\n";
//         $text .="include => ext-did-0001\n";
//         $text .="include => ext-did-0002\n";
//         $text .="exten => fax,1,Goto(ext-fax,in_fax,1)\n";
//         $text .="\n";
//         $text .="; end of [ext-did]\n";
//         $text .="\n";
//         $text .="\n";
//         $text .="[ext-did-catchall]\n";
//         $text .="include => ext-did-catchall-custom\n";
//         $text .="exten => s,1,Noop(No DID or CID Match)\n";
//         $text .="exten => s,n(a2),Answer\n";
//         $text .="exten => s,n,Wait(2)\n";
//         $text .="exten => s,n,Playback(ss-noservice)\n";
//         $text .="exten => s,n,SayAlpha(\${FROM_DID})\n";
//         $text .="exten => s,n,Hangup\n";
//         $text .="exten => _.,1,Set(__FROM_DID=\${EXTEN})\n";
//         $text .="exten => _.,n,Noop(Received an unknown call with DID set to \${EXTEN})\n";
//         $text .="exten => _.,n,Goto(s,a2)\n";
//         $text .="exten => h,1,Hangup\n";
//         $text .="\n";
//         $text .="; end of [ext-did-catchall]\n";
//         $text .="\n";
//         $text .="\n";
//     }

}
?>
