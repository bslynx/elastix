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

    function getVoIPProviders()
    {
        $query = "SELECT name FROM provider";
        $providers = array();
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

    //Mode Text
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

        }else if($type=="NuFoneIAX"){
            $textConfig .= "username=elastixnufonegye\n";
            $textConfig .= "type=peer\n";
            $textConfig .= "secret=palosanto.2007nufone\n";
            $textConfig .= "host=switch-1.nufone.net";

        }
        return $textConfig;
    }

    //Mode Xml
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

        }else if($type=="NuFoneIAX"){
            $data = $this->getVoIPProviderByProvider(4);

            $textXMLConfig .= "<username>{$data['username']}</username>\n";
            $textXMLConfig .= "<type>{$data['type']}</type>\n";
            $textXMLConfig .= "<secret>{$data['password']}</secret>\n";
            $textXMLConfig .= "<host>{$data['host']}</host>\n";
        }else if($type=="to_starvox"){
            $data = $this->getVoIPProviderByProvider(5);

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
        $num_nextTrunk = 0;
        $line_conf1 = "";
        $line_conf2 = "";
        $fp = fopen($FILE,'r');
        $arrTrunks = $this->getIndexTrunk();
        //$name_pass = $arrTrunks['name'];
        $num_trunks = count($arrTrunks);
        if($num_trunks > 1){
            $filter1="OUTDISABLE_".$arrTrunks[$num_trunks]['index'];
            foreach($arrTrunks as $value){
                if($value['type']=="SIP" || $value['type']=="IAX2")
                    $filter2="from-trunk-".strtolower($value['type'])."-".$value['name'];
                else
                    $filter2="from-did-direct-ivr";//eregi no acepta corchetes
            }
            $num_nextTrunk = $arrTrunks[$num_trunks]['index']+1;
        }else{
            $filter1="RECORDING_STATE";
            $filter2="from-did-direct-ivr";
            $filter3="ext-did-catchall";
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
                $i++;
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
        //if($num == null) $num=1;
        //$FILE='/etc/asterisk/localprefixes.conf';
        $text = "";
        //$fp = fopen($FILE,'r');
        //$num_nextTrunk = $data_trunk['index']+1;
        $num_nextTrunk = count($data_trunk)+1;
        //$text .="\n\n";
        $text .="\n[trunk-$num_nextTrunk]\n\n";
        
        $this->saveChangeFileLocalPrefixes($text);
    }

    function addConfFileSipAdditional($type)
    {
        //$FILE='/etc/asterisk/sip_additional.conf';
        $text = "";
        //$fp = fopen($FILE,'r');
        $text .="[$type]\n";
        //Peer Detail 
        if($type=="net2phone"){
            $arr_data = $this->getVoIPProviderByProvider(1);
            //exec("echo '".print_r($arr_data, true)."' > /tmp/oscar");
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

        }else if($type=="to_starvox"){
            $data = $this->getVoIPProviderByProvider(5);

            $text .= "username={$data['username']}\n";
            $text .= "type={$data['type']}\n";
            $text .= "secret={$data['password']}\n";
            $text .= "host={$data['host']}\n";
        }
        //exec("echo '$text' > /tmp/oscar");
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
        $link1="none";
        $link2="none";
        $link3="none";
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
                    $link3 = "to_starvox";
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
                    $link3 = "to_starvox";
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
                    $link3 = "to_starvox";
                }else if($type=="to_starvox"){
                    $data = $this->getVoIPProviderByProvider(5);
                    $text = $line;
                    $text .= "username={$data['username']}\n";
                    $text .= "type={$data['type']}\n";
                    $text .= "secret={$data['password']}\n";
                    $text .= "host={$data['host']}\n\n";
                    $link1 = "vitelity";
                    $link2 = "net2phone";
                    $link3 = "to_camundanet";
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
        $count = 0;
        $last = "";
        $dataTrunk = array();
//         $dataTrunk[0]['index'] = 0;
//         $dataTrunk[0]['type'] = "";
//         $dataTrunk[0]['name'] = "";
        $fp = fopen($FILE,'r');
        while($line = fgets($fp, filesize($FILE)))
        {
            if(ereg("^([[:alnum:]]+)_([[:digit:]]+)[[:space:]]=[[:space:]]([[:alnum:]]+)/([a-zA-Z_0-9]+)", $line, $arrReg)){
                if("OUT" == $arrReg[1]){
                    $count++;
                    $dataTrunk[$count]['index'] = $arrReg[2];
                    $dataTrunk[$count]['type'] = $arrReg[3];
                    $dataTrunk[$count]['name'] = $arrReg[4];
                }
            }
        }
        fclose($fp);
        return $dataTrunk;
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

    function findTrunkInLocalPrefixes($type_provider){
        $FILE='/etc/asterisk/localprefixes.conf';
        $fp = fopen($FILE,'r');
        $arrTrunks = $this->getIndexTrunk();
        $num_trunks = count($arrTrunks);
        //$num_last_trunk = $arrTrunks[$num_trunks]['index'];
        $index = 0;
        $found = "false";
        if(is_array($arrTrunks) && count($arrTrunks)>1){
            for($i=1 ; $i<=$num_trunks; $i++){
                if($arrTrunks[$i]['name'] == $type_provider){
                    $index=$arrTrunks[$i]['index'];
                }
            }
        }
        $filter = "trunk-".$index;
        //exec("echo '$filter' > /tmp/oscar");
        while($line = fgets($fp, filesize($FILE)))
        {
            if(ereg($filter, $line)){
                $found = "true";
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

}
?>