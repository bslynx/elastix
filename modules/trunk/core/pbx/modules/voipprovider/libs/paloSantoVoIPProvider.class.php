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
require_once "/var/lib/asterisk/agi-bin/phpagi-asmanager.php";

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

    //Mode Xml
    function getConfigByType($type)
    {
        $data="";
        $textXMLConfig = "";
        $textXMLConfig .= "<?xml version=\"1.0\"?>\n";
        $textXMLConfig .= "<configs>\n";
        $textXMLConfig .= "<attribute>\n";
        if($type=="Net2Phone") $data = $this->getVoIPProviderByProvider(1);
        else if($type=="camundaNET") $data = $this->getVoIPProviderByProvider(2);
        else if($type=="Vitelity") $data = $this->getVoIPProviderByProvider(3);
        else if($type=="NuFoneIAX") $data = $this->getVoIPProviderByProvider(4);
        else if($type=="StarVox") $data = $this->getVoIPProviderByProvider(5);

        if(is_array($data) && count($data)>1){
            if($data['username']!=null) $textXMLConfig .= "<username>{$data['username']}</username>\n";
            else $textXMLConfig .= "<username> </username>\n";

            if($data['type']!=null) $textXMLConfig .= "<type>{$data['type']}</type>\n";
            else $textXMLConfig .= "<type> </type>\n";

            if($data['password']!=null) $textXMLConfig .= "<secret>{$data['password']}</secret>\n";
            else $textXMLConfig .= "<secret> </secret>\n";

            if($data['qualify']!=null) $textXMLConfig .= "<qualify>{$data['qualify']}</qualify>\n";
            else $textXMLConfig .= "<qualify> </qualify>\n";

            if($data['insecure']!=null) $textXMLConfig .= "<insecure>{$data['insecure']}</insecure>\n";
            else $textXMLConfig .= "<insecure> </insecure>\n";

            if($data['host']!=null) $textXMLConfig .= "<host>{$data['host']}</host>\n";
            else $textXMLConfig .= "<host> </host>\n";
    
            if($data['fromuser']!=null) $textXMLConfig .= "<fromuser>{$data['fromuser']}</fromuser>\n";
            else $textXMLConfig .= "<fromuser> </fromuser>\n";

            if($data['fromdomain']!=null) $textXMLConfig .= "<fromdomain>{$data['fromdomain']}</fromdomain>\n";
            else $textXMLConfig .= "<fromdomain> </fromdomain>\n";
            
            if($data['dtmfmode']!=null) $textXMLConfig .= "<dtmfmode>{$data['dtmfmode']}</dtmfmode>\n";
            else $textXMLConfig .= "<dtmfmode> </dtmfmode>\n";

            if($data['disallow']!=null) $textXMLConfig .= "<disallow>{$data['disallow']}</disallow>\n";
            else $textXMLConfig .= "<disallow> </disallow>\n";

            if($data['context']!=null) $textXMLConfig .= "<context>{$data['context']}</context>\n";
            else $textXMLConfig .= "<context> </context>\n";

            if($data['allow']!=null) $textXMLConfig .= "<allow>{$data['allow']}</allow>\n";
            else $textXMLConfig .= "<allow> </allow>\n";

            if($data['trustrpid']!=null) $textXMLConfig .= "<trustrpid>{$data['trustrpid']}</trustrpid>\n";
            else $textXMLConfig .= "<trustrpid> </trustrpid>\n";

            if($data['sendrpid']!=null) $textXMLConfig .= "<sendrpid>{$data['sendrpid']}</sendrpid>\n";
            else $textXMLConfig .= "<sendrpid> </sendrpid>\n";

            if($data['canreinvite']!=null) $textXMLConfig .= "<canreinvite>{$data['canreinvite']}</canreinvite>\n";
            else $textXMLConfig .= "<canreinvite> </canreinvite>\n";
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

    function saveChangeFileExtensionCustom($text) {
        $fp = fopen('/etc/asterisk/extensions_custom.conf', 'w');

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

    function saveChangeFileSipCustom($text) {
        $fp = fopen('/etc/asterisk/sip_custom.conf', 'a');

        fwrite($fp, $text);
        fclose($fp);
    }

    function updateChangeFileSipCustom($text) {
        //$fp = fopen('/etc/asterisk/sip_additional.conf', 'w');
        $fp = fopen('/etc/asterisk/sip_custom.conf', 'w');

        fwrite($fp, $text);
        fclose($fp);
    }

    function saveChangeFileSipRegistrations($text) {
        //$fp = fopen('/etc/asterisk/sip_registrations.conf', 'a');
        $fp = fopen('/etc/asterisk/sip_registrations_custom.conf', 'a');        

        fwrite($fp, $text);
        fclose($fp);
    }

    function updateChangeFileSipRegistrations($text) {
        //$fp = fopen('/etc/asterisk/sip_registrations.conf', 'w');
        $fp = fopen('/etc/asterisk/sip_registrations_custom.conf', 'w');

        fwrite($fp, $text);
        fclose($fp);
    }

    function saveChangeFileIaxAdditional($text) {
        $fp = fopen('/etc/asterisk/iax_additional.conf', 'a');
    
        fwrite($fp, $text);
        fclose($fp);
    }

    function saveChangeFileIaxCustom($text) {
        $fp = fopen('/etc/asterisk/iax_custom.conf', 'a');
        
        if(empty($fp) || $fp==null){ 
            exec("mkdir /etc/asterisk/iax_custom.conf");
            exec("sudo -u root chown asterisk.asterisk /etc/asterisk/iax_custom.conf");
            $fp = fopen('/etc/asterisk/iax_custom.conf', 'a');
        }

        fwrite($fp, $text);
        fclose($fp);
    }

    function updateChangeFileIaxAdditional($text) {
        $fp = fopen('/etc/asterisk/iax_additional.conf', 'w');

        fwrite($fp, $text);
        fclose($fp);
    }

    function updateChangeFileIaxCustom($text) {
        $fp = fopen('/etc/asterisk/iax_custom.conf', 'w');

        fwrite($fp, $text);
        fclose($fp);
    }

    function saveChangeFileIaxRegistrations($text) {
        //$fp = fopen('/etc/asterisk/iax_registrations.conf', 'a');
        $fp = fopen('/etc/asterisk/iax_registrations_custom.conf', 'a');
        
        if(empty($fp) || $fp==null){ 
            exec("mkdir /etc/asterisk/iax_registrations_custom.conf");
            exec("sudo -u root chown asterisk.asterisk /etc/asterisk/iax_registrations_custom.conf");
            $fp = fopen('/etc/asterisk/iax_registrations_custom.conf', 'a');
        }

        fwrite($fp, $text);
        fclose($fp);
    }

    function updateChangeFileIaxRegistrations($text) {
        //$fp = fopen('/etc/asterisk/iax_registrations.conf', 'w');
        $fp = fopen('/etc/asterisk/iax_registrations_custom.conf', 'w');

        fwrite($fp, $text);
        fclose($fp);
    }


    function AddConfFileExtensionCustom($nameTrunk, $typeTrunk){
        $FILE='/etc/asterisk/extensions_custom.conf';
        $text = "";
        $fp = fopen($FILE,'r');
        $line_conf1 = "";
        $line_conf2 = "";
        $i=0;
        $is_empty=FALSE;
        $arrTrunksExtAdd = $this->getIndexTrunkExtenAdd();
        $arrTrunksExtCust = $this->getIndexTrunkExtenCust();
        $num_lines = count(file("/etc/asterisk/extensions_custom.conf"));
        $c=0;
        if(!empty($arrTrunksExtCust)){
            if($arrTrunksExtAdd[count($arrTrunksExtAdd)]['index'] > $arrTrunksExtCust[count($arrTrunksExtCust)]['index'])
                $num_last_trunk = $arrTrunksExtAdd[count($arrTrunksExtAdd)]['index'];
            else
                $num_last_trunk = $arrTrunksExtCust[count($arrTrunksExtCust)]['index'];

            $filter1="OUTDISABLE_".$num_last_trunk;
            foreach($arrTrunksExtCust as $value) {
                if($value['type']=="SIP" || $value['type']=="IAX2") {
                    $filter2="from-trunk-".strtolower($value['type'])."-".$value['name'];
                    $i=1;
                }else {
                    $filter2 = " ";/*Falta revisar en el archivo de configuracion 62,3*/
                    $i=0;
                }
            }
            $num_nextTrunk = $num_last_trunk + 1;

        }else {
            $filter1 = "from-internal-custom";
            $filter2 = " ";/*Falta revisar en el archivo de configuracion 62,3*/
            $is_empty = TRUE;
            $num_nextTrunk = $arrTrunksExtAdd[count($arrTrunksExtAdd)]['index']+1;
        }
        while($line = fgets($fp, filesize($FILE)))
        {
            if(eregi($filter1, $line) && $is_empty) {
                $line_conf1 .= "[globals]\n";
                $line_conf1 .= "OUT_$num_nextTrunk = ".strtoupper($typeTrunk)."/$nameTrunk\n";
                $line_conf1 .= "OUTPREFIX_$num_nextTrunk =\n";
                $line_conf1 .= "OUTMAXCHANS_$num_nextTrunk =\n";
                $line_conf1 .= "OUTCID_$num_nextTrunk =\n";
                $line_conf1 .= "OUTKEEPCID_$num_nextTrunk = off\n";
                $line_conf1 .= "OUTFAIL_$num_nextTrunk =\n";
                $line_conf1 .= "OUTDISABLE_$num_nextTrunk = on\n";//en la 160 tiene on
                $line_conf1 .= "\n";
                $line_conf1 .= ";end of [globals]\n\n";
                $line_conf1 .= $line;
                $text .= $line_conf1;
                $c++;
            }else if(eregi($filter1, $line) && !$is_empty) {
                $line_conf1 .= "OUT_$num_nextTrunk = ".strtoupper($typeTrunk)."/$nameTrunk\n";
                $line_conf1 .= "OUTPREFIX_$num_nextTrunk =\n";
                $line_conf1 .= "OUTMAXCHANS_$num_nextTrunk =\n";
                $line_conf1 .= "OUTCID_$num_nextTrunk =\n";
                $line_conf1 .= "OUTKEEPCID_$num_nextTrunk = off\n";
                $line_conf1 .= "OUTFAIL_$num_nextTrunk =\n";
                $line_conf1 .= "OUTDISABLE_$num_nextTrunk = on\n";//en la 160 tiene on
                $line .= $line_conf1;
                $text .= $line;
                $c++;
            }else if(($c==$num_lines-1) && $is_empty) {
                $line_conf2 .= "\n\n";
                $line_conf2 .= "[from-trunk-$typeTrunk-$nameTrunk]\n";
                $line_conf2 .= "include => from-trunk-$typeTrunk-$nameTrunk-custom\n";
                $line_conf2 .= "exten => _.,1,Set(GROUP()=OUT_$num_nextTrunk)\n";
                $line_conf2 .= "exten => _.,n,Goto(from-trunk,\${EXTEN},1)\n";
                $line_conf2 .= "\n";
                $line_conf2 .= "; end of [from-trunk-$typeTrunk-$nameTrunk]\n";
                $line .= $line_conf2;
                $text .= $line;
                $c++;
            }else if(eregi($filter2, $line) && !$is_empty) {
                if($i==3){
                    $line_conf2 .= "\n\n";
                    $line_conf2 .= "[from-trunk-$typeTrunk-$nameTrunk]\n";
                    $line_conf2 .= "include => from-trunk-$typeTrunk-$nameTrunk-custom\n";
                    $line_conf2 .= "exten => _.,1,Set(GROUP()=OUT_$num_nextTrunk)\n";
                    $line_conf2 .= "exten => _.,n,Goto(from-trunk,\${EXTEN},1)\n";
                    $line_conf2 .= "\n";
                    $line_conf2 .= "; end of [from-trunk-$typeTrunk-$nameTrunk]\n";
                    $line .= $line_conf2;
                    $text .= $line;
                    $c++;
                }else{
                    $text .= $line;
                    $c++;
                }
                $i++;
            }else {
                $text .= $line;
                $c++;
            }
        }
        $this->saveChangeFileExtensionCustom($text);
        fclose($fp);
    }


    //solo guarda las troncales que contienen reglas
    function addConfFileLocalPrefixes($type_provider)
    {
        $arrTrunks = $this->getIndexTrunkExtenCust();
        //if($num == null) $num=1;
        $text = "";
        $index = 1;
        if(is_array($arrTrunks) && count($arrTrunks)>1){
            for($i=1 ; $i<=count($arrTrunks); $i++){
                if($arrTrunks[$i]['name'] == $type_provider)
                    $index=$arrTrunks[$i]['index'];
            }
        }
        //$num_nextTrunk = count($arrTrunks)+1;
        $text .="\n[trunk-$index]\n";
        $this->saveChangeFileLocalPrefixes($text);
    }


    //function addConfFileSipAdditional($type)
    function addConfFileSipCustom($type)
    {
        $text = "\n";
        $text .="[$type]\n";
        //Peer Detail 
        if($type=="Net2Phone") $data = $this->getVoIPProviderByProvider(1);
        else if($type=="camundaNET") $data = $this->getVoIPProviderByProvider(2);
        else if($type=="Vitelity") $data = $this->getVoIPProviderByProvider(3);
        else if($type=="StarVox") $data = $this->getVoIPProviderByProvider(5);

        if($data['username']!=null) $text .= "username={$data['username']}\n";
        if($data['type']!=null) $text .= "type={$data['type']}\n";
        if($data['password']!=null) $text .= "secret={$data['password']}\n";
        if($data['qualify']!=null) $text .= "qualify={$data['qualify']}\n";
        if($data['insecure']!=null) $text .= "insecure=port,invite\n";//very
        if($data['host']!=null) $text .= "host={$data['host']}\n";
        if($data['fromuser']!=null) $text .= "fromuser={$data['fromuser']}\n";
        if($data['fromdomain']!=null) $text .= "fromdomain={$data['fromdomain']}\n";
        if($data['dtmfmode']!=null) $text .= "dtmfmode={$data['dtmfmode']}\n";
        if($data['disallow']!=null) $text .= "disallow={$data['disallow']}\n";
        if($data['context']!=null) $text .= "context={$data['context']}\n";
        if($data['allow']!=null) $text .= "allow={$data['allow']}\n";
        if($data['trustrpid']!=null) $text .= "trustrpid={$data['trustrpid']}\n";
        if($data['sendrpid']!=null) $text .= "sendrpid={$data['sendrpid']}\n";
        if($data['canreinvite']!=null) $text .= "canreinvite={$data['canreinvite']}\n\n";

        $this->saveChangeFileSipCustom($text);
        exec("sudo /etc/rc.d/init.d/asterisk reload");
    }

    //function updateFileSipAdditional($type)
    function updateFileSipCustom($type)
    {
        $FILE='/etc/asterisk/sip_custom.conf';
        $text_sipadd = "";
        $find="false";
        $fp = fopen($FILE,'r');

        $arrTrunks = $this->getIndexSipCustom();

        while($line = fgets($fp, filesize($FILE)))
        {
        $count=1;
        $i=0;
            while($count <= count($arrTrunks)) {
                if(eregi($type, $line) && $find=="false") {
                    if($type=="Net2Phone") $data = $this->getVoIPProviderByProvider(1);
                    else if($type=="camundaNET") $data = $this->getVoIPProviderByProvider(2);
                    else if($type=="Vitelity") $data = $this->getVoIPProviderByProvider(3);
                    else if($type=="StarVox") $data = $this->getVoIPProviderByProvider(5);
                    
                    $text = "\n";
                    $text = $line;
                    if($data['username']!=null) $text .= "username={$data['username']}\n";
                    if($data['type']!=null) $text .= "type={$data['type']}\n";
                    if($data['password']!=null) $text .= "secret={$data['password']}\n";
                    if($data['qualify']!=null) $text .= "qualify={$data['qualify']}\n";
                    if($data['insecure']!=null) $text .= "insecure=port,invite\n";//very
                    if($data['host']!=null) $text .= "host={$data['host']}\n";
                    if($data['fromuser']!=null) $text .= "fromuser={$data['fromuser']}\n";
                    if($data['fromdomain']!=null) $text .= "fromdomain={$data['fromdomain']}\n";
                    if($data['dtmfmode']!=null) $text .= "dtmfmode={$data['dtmfmode']}\n";
                    if($data['disallow']!=null) $text .= "disallow={$data['disallow']}\n";
                    if($data['context']!=null) $text .= "context={$data['context']}\n";
                    if($data['allow']!=null) $text .= "allow={$data['allow']}\n";
                    if($data['trustrpid']!=null) $text .= "trustrpid={$data['trustrpid']}\n";
                    if($data['sendrpid']!=null) $text .= "sendrpid={$data['sendrpid']}\n";
                    if($data['canreinvite']!=null) $text .= "canreinvite={$data['canreinvite']}\n";
                    $text .= "\n\n";
                    $text_sipadd .=$text;
                    $i++;
                    $find = "true";
                    
                }else if(!eregi($arrTrunks[$count], $line) && $find=="true") {
                    $count++;
                }else {
                    if($i<1) {
                        $text_sipadd .= $line;
                        $find = "false";
                        $i++;
                    }
                    $count++;
                }
            }
        }
        $this->updateChangeFileSipCustom($text_sipadd);
        exec("sudo /etc/rc.d/init.d/asterisk reload");
        fclose($fp);
    }


    function addConfFileIaxCustom($type)
    {
        $text = "";
        $text .="[$type]\n";
        if($type=="NuFoneIAX"){
            $data = $this->getVoIPProviderByProvider(4);
            if($data['username']!=null) $text .= "username={$data['username']}\n";
            if($data['type']!=null) $text .= "type={$data['type']}\n";
            if($data['password']!=null) $text .= "secret={$data['password']}\n";
            if($data['qualify']!=null) $text .= "qualify={$data['qualify']}\n";
            if($data['insecure']!=null) $text .= "insecure=port,invite\n";//very
            if($data['host']!=null) $text .= "host={$data['host']}\n";
            if($data['fromuser']!=null) $text .= "fromuser={$data['fromuser']}\n";
            if($data['fromdomain']!=null) $text .= "fromdomain={$data['fromdomain']}\n";
            if($data['dtmfmode']!=null) $text .= "dtmfmode={$data['dtmfmode']}\n";
            if($data['disallow']!=null) $text .= "disallow={$data['disallow']}\n";
            if($data['context']!=null) $text .= "context={$data['context']}\n";
            if($data['allow']!=null) $text .= "allow={$data['allow']}\n";
            if($data['trustrpid']!=null) $text .= "trustrpid={$data['trustrpid']}\n";
            if($data['sendrpid']!=null) $text .= "sendrpid={$data['sendrpid']}\n";
            if($data['canreinvite']!=null) $text .= "canreinvite={$arr_data['canreinvite']}\n";
            $text .= "\n";
        }
        $this->saveChangeFileIaxCustom($text);
        exec("sudo /etc/rc.d/init.d/asterisk reload");
    }

    function updateFileIaxCustom($type)
    {
        $FILE='/etc/asterisk/iax_custom.conf';
        $text_iaxadd = "";
        $fp = fopen($FILE,'r');
        //$link="none";
        $find="false";
        $arrTrunks = $this->getIndexIaxCustom();
    
        while($line = fgets($fp, filesize($FILE)))
        {
        $count=1;
        $i=0;
            while($count <= count($arrTrunks)) {
                if(eregi($type, $line) && $find=="false"){
                    if("NuFoneIAX") $data = $this->getVoIPProviderByProvider(4);
                    $text = "\n";
                    $text = $line;
                    if($data['username']!=null) $text .= "username={$data['username']}\n";
                    if($data['type']!=null) $text .= "type={$data['type']}\n";
                    if($data['password']!=null) $text .= "secret={$data['password']}\n";
                    if($data['qualify']!=null) $text .= "qualify={$data['qualify']}\n";
                    if($data['insecure']!=null) $text .= "insecure=port,invite\n";//very
                    if($data['host']!=null) $text .= "host={$data['host']}\n";
                    if($data['fromuser']!=null) $text .= "fromuser={$data['fromuser']}\n";
                    if($data['fromdomain']!=null) $text .= "fromdomain={$data['fromdomain']}\n";
                    if($data['dtmfmode']!=null) $text .= "dtmfmode={$data['dtmfmode']}\n";
                    if($data['disallow']!=null) $text .= "disallow={$data['disallow']}\n";
                    if($data['context']!=null) $text .= "context={$data['context']}\n";
                    if($data['allow']!=null) $text .= "allow={$data['allow']}\n";
                    if($data['trustrpid']!=null) $text .= "trustrpid={$data['trustrpid']}\n";
                    if($data['sendrpid']!=null) $text .= "sendrpid={$data['sendrpid']}\n";
                    if($data['canreinvite']!=null) $text .= "canreinvite={$arr_data['canreinvite']}\n";
                    $text .= "\n";
                    $text_iaxadd .=$text;
                    $i++;
                    $find = "true";
                }else if(!eregi($arrTrunks[$count], $line) && $find=="true") {
                    $count++;
                }else{
                    if($i<1) {
                        $text_iaxadd .= $line;
                        $find = "false";
                        $i++;
                    }
                    $count++;
                }
            }
        }
        $this->updateChangeFileIaxCustom($text_iaxadd);
        exec("sudo /etc/rc.d/init.d/asterisk reload");
        fclose($fp);
    }
    

    function addConfFileSipRegistrations($username, $secret, $host)
    {
        //$FILE='/etc/asterisk/sip_registrations.conf';
        $FILE='/etc/asterisk/sip_registrations_custom.conf';
        $text = "";
        $fp = fopen($FILE,'r');

        $text .="register=$username:$secret@$host/$username\n";
        $this->saveChangeFileSipRegistrations($text);
        fclose($fp);
    }

    function addConfFileIaxRegistrations($username, $secret, $host)
    {
        //$FILE='/etc/asterisk/iax_registrations.conf';
        $FILE='/etc/asterisk/iax_registrations_custom.conf';
        $text = "";
        $fp = fopen($FILE,'r');

        $text .="register=$username:$secret@$host\n";
        $this->saveChangeFileIaxRegistrations($text);
        fclose($fp);
    }

    function updateFileSipRegistrations($username, $secret, $host)
    {
        //$FILE='/etc/asterisk/sip_registrations.conf';
        $FILE='/etc/asterisk/sip_registrations_custom.conf';
        $text = "";
        $fp = fopen($FILE,'r');
        //$text .="\n";
        while($line = fgets($fp, filesize($FILE)))
        {
            if(eregi($host, $line)){
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
        //$FILE='/etc/asterisk/iax_registrations.conf';
        $FILE='/etc/asterisk/iax_registrations_custom.conf';
        $text = "";
        $fp = fopen($FILE,'r');
        //$text .="\n";
        while($line = fgets($fp, filesize($FILE)))
        {
            if(eregi($host, $line)){
                $text .="register=$username:$secret@$host\n";
            }else{
                $text .= $line;
            }
        }
        $this->updateChangeFileIaxRegistrations($text);
        fclose($fp);
    }

    function getIndexTrunkExtenAdd()
    {
        $FILE='/etc/asterisk/extensions_additional.conf';
        $count = 0;
        $last = "";
        $dataTrunk = array();
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

    function getIndexTrunkExtenCust()
    {
        $FILE='/etc/asterisk/extensions_custom.conf';
        $count = 0;
        $last = "";
        $dataTrunk = array();
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
    
    function getIndexSipCustom()
    {
        $dataTrunk = array();
        $count = 0;
//         $FILE='/etc/asterisk/sip_additional.conf';
        $FILE='/etc/asterisk/sip_custom.conf';
        $fp = fopen($FILE, 'r');
        while($line = fgets($fp, filesize($FILE)))
        {
            if(ereg("([[a-zA-Z_0-9]+])", $line, $arrReg)){//obtengo datos solo dentro de corchetes
                $count++;
                if(ereg("([a-zA-Z_0-9]+)", $arrReg[1], $arrSubReg))
                    $dataTrunk[$count] = $arrSubReg[1];
            }
        }
        fclose($fp);
        return $dataTrunk;
    }

    function getIndexIaxCustom()
    {
        $dataTrunk = array();
        $count = 0;

        $FILE='/etc/asterisk/iax_custom.conf';
        $fp = fopen($FILE, 'r');
        while($line = fgets($fp, filesize($FILE)))
        {
            if(ereg("([[a-zA-Z_0-9]+])", $line, $arrReg)){//obtengo datos solo dentro de corchetes
                $count++;
                if(ereg("([a-zA-Z_0-9]+)", $arrReg[1], $arrSubReg))
                    $dataTrunk[$count] = $arrSubReg[1];
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
    function findTrunkInExtensionCustom($type_provider){
        //$FILE='/etc/asterisk/extensions_additional.conf';
        $FILE='/etc/asterisk/extensions_custom.conf';
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
        $arrTrunks = $this->getIndexTrunkExtenCust();
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
        while($line = fgets($fp, filesize($FILE)))
        {
            if(eregi($filter, $line)){
                $found = "true";
            }
        }
        fclose($fp);
        return $found;
    }

    function findTrunkInSipCustom($type_provider){
        //$FILE='/etc/asterisk/sip_additional.conf';
        $FILE='/etc/asterisk/sip_custom.conf';
        $fp = fopen($FILE,'r');
        $found = "false";
        while($line = fgets($fp, filesize($FILE)))
        {
            if(eregi($type_provider, $line)){
                $found = "true";
            }
        }
        fclose($fp);
        return $found;
    }

    function findTrunkInIaxCustom($type_provider){
        //$FILE='/etc/asterisk/iax_additional.conf';
        $FILE='/etc/asterisk/iax_custom.conf';
        $fp = fopen($FILE,'r');
        $found = "false";
        while($line = fgets($fp, filesize($FILE)))
        {
            if(eregi($type_provider, $line)){
                $found = "true";
            }
        }
        fclose($fp);
        return $found;
    }

    function findTrunkInSipRegistrations($host){
        //$FILE='/etc/asterisk/sip_registrations.conf';
        $FILE='/etc/asterisk/sip_registrations_custom.conf';
        $fp = fopen($FILE,'r');
        $found = "false";
        while($line = fgets($fp, filesize($FILE)))
        {
            if(eregi($host, $line)){
                $found = "true";
            }
        }
        fclose($fp);
        return $found;
    }

    function findTrunkInIaxRegistrations($host){
        //$FILE='/etc/asterisk/iax_registrations.conf';
        $FILE='/etc/asterisk/iax_registrations_custom.conf';
        $fp = fopen($FILE,'r');
        $found = "false";
        while($line = fgets($fp, filesize($FILE)))
        {
            if(eregi($host, $line)){
                $found = "true";
            }
        }
        fclose($fp);
        return $found;
    }

    function validateFormEmpty($arrConfigTrunk){
        $anyone_empty=false;
        
        if($arrConfigTrunk['username']==" " || $arrConfigTrunk['username']==null){
            $anyone_empty=true;
            return $anyone_empty;
        }else if($arrConfigTrunk['secret']==" " || $arrConfigTrunk['secret']==null){
            $anyone_empty=true;
            return $anyone_empty;
        }else if($arrConfigTrunk['type']==" " || $arrConfigTrunk['type']==null){
            $anyone_empty=true;
            return $anyone_empty;
        }else if($arrConfigTrunk['host']==" " || $arrConfigTrunk['host']==null){
            $anyone_empty=true;
            return $anyone_empty;
        }else if($arrConfigTrunk['context']==" " || $arrConfigTrunk['context']==null){
            $anyone_empty=true;
            return $anyone_empty;
        }
        return $anyone_empty;
    }
}
?>