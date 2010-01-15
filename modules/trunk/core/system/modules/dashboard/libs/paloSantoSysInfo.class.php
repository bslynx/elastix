<?php
global $arrConf;
require_once "{$arrConf['basePath']}/libs/misc.lib.php";
require_once "{$arrConf['basePath']}/libs/paloSantoDB.class.php";
require_once "{$arrConf['basePath']}/libs/paloSantoSampler.class.php";
require_once "{$arrConf['basePath']}/libs/paloSantoTrunk.class.php";
require_once "/var/lib/asterisk/agi-bin/phpagi-asmanager.php";

class paloSantoSysInfo
{
    var $arrSysInfo;

    function paloSantoSysInfo()
    {
        $this->arrSysInfo = obtener_info_de_sistema();
    }

    function getSysInfo()
    {
        return $this->arrSysInfo;
    }

    function ObtenerInfo_Particion($value)
    {
        $result = array();

        $result['ATTRIBUTES'] = array('TITLE'=>'','TYPE'=>'plot3d','SIZE'=>"220,100",'POS_LEYEND' => "0.06,0.3", "COLOR" => "#fafafa", "SIZE_PIE" => "50", "MARGIN_COLOR" => "#fafafa");
        $result['MESSAGES'] = array('ERROR'=>'Error','NOTHING_SHOW'=>'Nada que mostrar');

        $arrTemp = array();
        for($i=1; $i<=2; $i++){
            $data = array();
            $data['VALUES'] = ($i==1) ? array('VALUE'=>$value) : array('VALUE'=>100-$value);
            $data['STYLE'] = array('COLOR'=> ($i==1) ? '#3333cc' : '#9999cc','LEYEND'=> ($i==1) ? 'Used' : 'Free');
            $arrTemp["DAT_$i"] = $data;
        }

        $result['DATA'] = $arrTemp;

        return $result;

    }

    function ObtenerInfo_CPU_Usage()
    {
        $value = $this->arrSysInfo['CpuUsage'];

        $result = array();
        $result['ATTRIBUTES'] = array('TYPE'=>'bar','SIZE'=>"90,20");
        $result['MESSAGES'] = array('ERROR'=>'Error','NOTHING_SHOW'=>'Nada que mostrar');

        $temp = array();
        $temp['DAT_1'] = array('VALUES'=>array("value"=>$value));
        $result['DATA'] = $temp;

        return $result;
    }

	 function ObtenerInfo_Asterisk_Channel_internalCalls()
    {
        //external_calls] => 0 [internal_calls] => 0 [total_calls] => 0 [total_channels]
		  $values = $this->getAsterisk_Channels();
		  $total = $values['total_calls'];
		  $internalCalls = $values['internal_calls'];
		  if($total!=0)
		  		$valor = $internalCalls * 100 / $total;
		  else
			   $valor = 0.0;
        $result = array();
        $result['ATTRIBUTES'] = array('TYPE'=>'bar','SIZE'=>"90,10");
        $result['MESSAGES'] = array('ERROR'=>'Error','NOTHING_SHOW'=>'Nada que mostrar');

        $temp = array();
        $temp['DAT_1'] = array('VALUES'=>array("value"=>$valor));
        $result['DATA'] = $temp;
        return $result;
    }

	 function ObtenerInfo_Asterisk_Channel_totalChannels()
    {
        //external_calls] => 0 [internal_calls] => 0 [total_calls] => 0 [total_channels]
		  $values = $this->getAsterisk_Channels();
		  $total = $values['total_channels'];
		  if($total!=0)
		  		$valor = $total * 100 / $total;
		  else
			   $valor = 0.0;
        $result = array();
        $result['ATTRIBUTES'] = array('TYPE'=>'bar','SIZE'=>"90,10");
        $result['MESSAGES'] = array('ERROR'=>'Error','NOTHING_SHOW'=>'Nada que mostrar');

        $temp = array();
        $temp['DAT_1'] = array('VALUES'=>array("value"=>$valor));
        $result['DATA'] = $temp;
        return $result;
    }

	 function ObtenerInfo_Asterisk_Channel_totalCalls()
    {
        //external_calls] => 0 [internal_calls] => 0 [total_calls] => 0 [total_channels]
		  $values = $this->getAsterisk_Channels();
		  $total = $values['total_calls'];
		  $internalCalls = $values['internal_calls'];
		  if($total!=0)
		  		$valor = $total * 100 / $total;
		  else
			   $valor = 0.0;
        $result = array();
        $result['ATTRIBUTES'] = array('TYPE'=>'bar','SIZE'=>"90,10");
        $result['MESSAGES'] = array('ERROR'=>'Error','NOTHING_SHOW'=>'Nada que mostrar');

        $temp = array();
        $temp['DAT_1'] = array('VALUES'=>array("value"=>$valor));
        $result['DATA'] = $temp;

        return $result;
    }

	 function ObtenerInfo_Asterisk_Channel_externalCalls()
    {
        //external_calls] => 0 [internal_calls] => 0 [total_calls] => 0 [total_channels]
		  $values = $this->getAsterisk_Channels();
		  $total = $values['total_calls'];
		  $internalCalls = $values['external_calls'];
		  if($total!=0)
		  		$valor = $internalCalls * 100 / $total;
		  else
			   $valor = 0.0;
        $result = array();
        $result['ATTRIBUTES'] = array('TYPE'=>'bar','SIZE'=>"90,10");
        $result['MESSAGES'] = array('ERROR'=>'Error','NOTHING_SHOW'=>'Nada que mostrar');

        $temp = array();
        $temp['DAT_1'] = array('VALUES'=>array("value"=>$valor));
        $result['DATA'] = $temp;

        return $result;
    }

    function ObtenerInfo_MemUsage()
    {
        $value = ($this->arrSysInfo['MemTotal'] - $this->arrSysInfo['MemFree'] - $this->arrSysInfo['Cached'] - $this->arrSysInfo['MemBuffers'])/$this->arrSysInfo['MemTotal'];

        $result = array();
        $result['ATTRIBUTES'] = array('TYPE'=>'bar','SIZE'=>"90,20");
        $result['MESSAGES'] = array('ERROR'=>'Error','NOTHING_SHOW'=>'Nada que mostrar');

        $temp = array();
        $temp['DAT_1'] = array('VALUES'=>array("value"=>$value));
        $result['DATA'] = $temp;

        return $result;
    }

    function ObtenerInfo_SwapUsage()
    {
        $value = ($this->arrSysInfo['SwapTotal'] - $this->arrSysInfo['SwapFree'])/$this->arrSysInfo['SwapTotal'];

        $result = array();
        $result['ATTRIBUTES'] = array('TYPE'=>'bar','SIZE'=>"90,20");
        $result['MESSAGES'] = array('ERROR'=>'Error','NOTHING_SHOW'=>'Nada que mostrar');

        $temp = array();
        $temp['DAT_1'] = array('VALUES'=>array("value"=>$value));
        $result['DATA'] = $temp;

        return $result;
    }

    function CallsMemoryCPU()
    {
        $arrayResult = array();

        $oSampler = new paloSampler();

        //retorna
        //Array ( [0] => Array ( [id] => 1 [name] => Sim. calls [color] => #00cc00 [line_type] => 1 )
        $arrLines = $oSampler->getGraphLinesById(1);

        //retorna
        //Array ( [name] => Simultaneous calls, memory and CPU )
        $arrGraph = $oSampler->getGraphById(1);

        $endtime = time();
        $starttime = $endtime - 26*60*60;
        $oSampler->deleteDataBeforeThisTimestamp($starttime);

        $arrayResult['ATTRIBUTES'] = array('TITLE' => $arrGraph['name'],'TYPE'=>'lineplot_multiaxis',
            'LABEL_X'=>"Etiqueta X",'LABEL_Y'=>'Etiqueta Y','SHADOW'=>false,'SIZE'=>"519,170",'MARGIN'=>"50,230,30,50",
            'COLOR' => "#fafafa",'POS_LEYEND'=> "0.02,0.5");

        $arrayResult['MESSAGES'] = array('ERROR' => 'Error', 'NOTHING_SHOW' => 'Nada que mostrar');

        //$oSampler->getSamplesByLineId(1)
        //retorna
        //Array ( [0] => Array ( [timestamp] => 1230562202 [value] => 2 ), ....... 

        $i = 1;
        $arrData = array();
        foreach($arrLines as $num => $line)
        {
            $arraySample = $oSampler->getSamplesByLineId($line['id']);

            $arrDat_N = array();

            $arrValues = array();
            foreach( $arraySample as $num => $time_value )
                $arrValues[ $time_value['timestamp'] ] = (int)$time_value['value'];

            $arrStyle = array();
            $arrStyle['COLOR'] = $line['color'];
            $arrStyle['LEYEND'] = $line['name'];
            $arrStyle['STYLE_STEP'] = true;
            $arrStyle['FILL_COLOR'] = ($i==1)?true:false;

            $arrDat_N["VALUES"] = $arrValues;
            $arrDat_N["STYLE"] = $arrStyle;

            $arrData["DAT_$i"] = $arrDat_N;

            $i++;
        }
        $arrayResult['DATA'] = $arrData;

        return $arrayResult;
    }

    function functionCallback($value)
    {
        return Date('H:i', $value);
    }

    function getStatusServices()
    {   // file pid service asterisk    is /var/run/asterisk/asterisk.pid
        // file pid service openfire    is /var/run/openfire.pid
        // file pid service hylafax     no founded but name services are hfaxd and faxq
        // file pid service iaxmodem    is /var/run/iaxmodem.pid
        // file pid service postfix     is /var/spool/postfix/pid/master.pid (can't to access to file by own permit,is better to use by CMD the serviceName is master)
        // file pid service mysql       is /var/run/mysqld/mysqld.pid (can't to access to file by own permit,is better to use by CMD the serviceName is mysqld)
        // file pid service apache      is /var/run/httpd.pid
        // file pid service call_center is /opt/elastix/dialer/dialerd.pid

        $arrSERVICES["Asterisk"]["status_service"] = $this->_existPID_ByFile("/var/run/asterisk/asterisk.pid");
        $arrSERVICES["Asterisk"]["name_service"]   = "Telephony Service";

        $arrSERVICES["OpenFire"]["status_service"] = $this->_existPID_ByFile("/var/run/openfire.pid");
        $arrSERVICES["OpenFire"]["name_service"]   = "Instant Messaging Service";

        $arrSERVICES["Hylafax"]["status_service"]  = $this->_existPID_ByCMD("hfaxd") & $this->_existPID_ByCMD("faxq");
        $arrSERVICES["Hylafax"]["name_service"]    = "Fax Service";

        $arrSERVICES["IAXModem"]["status_service"] = $this->_existPID_ByFile("/var/run/iaxmodem.pid");
        $arrSERVICES["IAXModem"]["name_service"]   = "IAXModem Service";

        $arrSERVICES["Postfix"]["status_service"]  = $this->_existPID_ByCMD("master");
        $arrSERVICES["Postfix"]["name_service"]    = "Email Service";

        $arrSERVICES["MySQL"]["status_service"]    = $this->_existPID_ByCMD("mysqld");
        $arrSERVICES["MySQL"]["name_service"]      = "Database Service";

        $arrSERVICES["Apache"]["status_service"]   = $this->_existPID_ByFile("/var/run/httpd.pid");
        $arrSERVICES["Apache"]["name_service"]     = "Web Server";

        $arrSERVICES["Dialer"]["status_service"]   = $this->_existPID_ByFile("/opt/elastix/dialer/dialerd.pid");
        $arrSERVICES["Dialer"]["name_service"]     = "Elastix Call Center Service";

        return $arrSERVICES;
    }

    function _existPID_ByFile($filePID)
    {
        if(file_exists($filePID)){
            $pid=trim(`cat $filePID`);
            $exist=`ps -p $pid | grep $pid`;
            if(isset($exist)) return "OK";
            else return "Shutdown";
        }
        return "Shutdown";
    }

    function _existPID_ByCMD($serviceName)
    {
        $pid=trim(`/sbin/pidof $serviceName`);
        $exist=`ps -p $pid | grep $pid`;
        if(isset($exist)) return "OK";
        else return "Shutdown";
    }

    function _existElastixModule($nameModule)
    {

    }

    function getAsterisk_Connections()
    {
        //SIPs
        $arrActivity["sip"]["ext"]["ok"]=0;
        $arrActivity["sip"]["ext"]["no_ok"]=0;
        $arrActivity["sip"]["trunk"]["ok"]=0;
        $arrActivity["sip"]["trunk"]["no_ok"]=0;
        $arrActivity["sip"]["trunk_registry"]["ok"]=0;
        $arrActivity["sip"]["trunk_registry"]["no_ok"]=0;
        //IAXs
        $arrActivity["iax"]["ext"]["ok"]=0;
        $arrActivity["iax"]["ext"]["no_ok"]=0;
        $arrActivity["iax"]["trunk"]["ok"]=0;
        $arrActivity["iax"]["trunk"]["no_ok"]=0;
        $arrActivity["iax"]["trunk_registry"]["ok"]=0;
        $arrActivity["iax"]["trunk_registry"]["no_ok"]=0;

        //1.- get all trunk in asterisk
        $arrTrunks = $this->_getAll_Trunk();

        //2.- get sip peers.
        $arrSIPs = $this->AsteriskManager_Command("sip show peers");
        if(is_array($arrSIPs) & count($arrSIPs)>0){
            foreach($arrSIPs as $key => $line){
                //ex: Name/username              Host            Dyn Nat ACL Port     Status
                //    412/412                    192.168.1.82     D   N   A  5060     OK (17 ms)
                if(eregi("^(([[:alnum:]\-_\.]*)[[:alnum:]/\-_\.]*)[[:space:]]*([[:alnum:]\.\(\)]+)[[:space:]]*([a-zA-Z]*)[[:space:]]*([a-zA-Z]*)[[:space:]]*([a-zA-Z]*)[[:space:]]*([0-9]+)[[:space:]]*([[:alnum:]\ \(\)]+)$",$line,$arrToken)){
                    if(eregi("OK",$arrToken[8])){ // estado OK
                        if(in_array($arrToken[2],$arrTrunks)) // es una troncal?, registrada
                            $arrActivity["sip"]["trunk"]["ok"]++;
                        else
                            $arrActivity["sip"]["ext"]["ok"]++;
                    }
                    else{
                        if(in_array($arrToken[2],$arrTrunks)) // es una troncal?, no registrada
                            $arrActivity["sip"]["trunk"]["no_ok"]++;
                        else
                            $arrActivity["sip"]["ext"]["no_ok"]++;
                    }
                }
            }
        }

        //3.- get iax peers
        $arrIAXs = $this->AsteriskManager_Command("iax2 show peers");
        if(is_array($arrIAXs) & count($arrIAXs)>0){
            foreach($arrIAXs as $key => $line){
                //ex: Name/Username    Host                 Mask             Port          Status
                //    512              127.0.0.1       (D)  255.255.255.255  40002         OK (3 ms)
                if(eregi("^(([[:alnum:]\-_\.]*)[[:alnum:]/\-_\.]*)[[:space:]]*([[:alnum:]\.\(\)]+)[[:space:]]*([a-zA-Z\(\)]*)[[:space:]]*([[:alnum:]\.\(\)]+)[[:space:]]*([0-9]+)[[:space:]]*([[:alnum:]\ \(\)]+)$",$line,$arrToken)){
                    if(eregi("OK",$arrToken[7])){ // estado OK
                        if(in_array($arrToken[2],$arrTrunks)) // es una troncal?, registrada
                            $arrActivity["iax"]["trunk"]["ok"]++;
                        else
                            $arrActivity["iax"]["ext"]["ok"]++;
                    }
                    else{
                        if(in_array($arrToken[2],$arrTrunks)) // es una troncal?, no registrada
                            $arrActivity["iax"]["trunk"]["no_ok"]++;
                        else
                            $arrActivity["iax"]["ext"]["no_ok"]++;
                    }
                }
            }
        }

        //4.- get sip registry
        $arrSIPsRegistry = $this->AsteriskManager_Command("sip show registry");
        if(is_array($arrSIPsRegistry) & count($arrSIPsRegistry)>0){
            foreach($arrSIPsRegistry as $key => $line){
                if(ereg("^([[:digit:]\:\.]+).*(Registered*)",$line,$arrToken))
                    $arrActivity["sip"]["trunk_registry"]["ok"]++;
                else if(ereg("^([[:digit:]\:\.]+)",$line))
                    $arrActivity["sip"]["trunk_registry"]["no_ok"]++;
            }
        }

        //5.- get sip registry
        $arrIAXsRegistry = $this->AsteriskManager_Command("iax2 show registry");
        if(is_array($arrIAXsRegistry) & count($arrIAXsRegistry)>0){
            foreach($arrIAXsRegistry as $key => $line){
                if(ereg("^([[:digit:]\:\.]+).*(Registered*)",$line,$arrToken))
                    $arrActivity["iax"]["trunk_registry"]["ok"]++;
                else if(ereg("^([[:digit:]\:\.]+)",$line))
                    $arrActivity["iax"]["trunk_registry"]["no_ok"]++;
            }
        }
        return $arrActivity;
    }

    function getAsterisk_Channels() {
        $arrChann["external_calls"]=0;
        $arrChann["internal_calls"]=0;
        $arrChann["total_calls"]=0;
        $arrChann["total_channels"]=0;

        $arrChannels = $this->AsteriskManager_Command("core show channels");
        if(is_array($arrChannels) & count($arrChannels)>0){
            foreach($arrChannels as $line){
                if(ereg("s@macro-dialout",$line))
                    $arrChann["external_calls"]++;
                else if(ereg("s@macro-dial:",$line))
                    $arrChann["internal_calls"]++;
                else if(ereg("^([0-9]+) active call",$line,$arrToken))
                    $arrChann["total_calls"] = $arrToken[1];
                else if(ereg("^([0-9]+) active channel",$line,$arrToken))
                    $arrChann["total_channels"] = $arrToken[1];
            }
        }
        return $arrChann;
    }

    function getAsterisk_QueueWaiting() {
        $arrQueues = $this->AsteriskManager_Command("queue show");
        $arrQue = array();

        if(is_array($arrQueues) & count($arrQueues)>0){
            foreach($arrQueues as $line){
                if(ereg("^([0-9]+)[[:space:]]*has ([0-9]+)",$line,$arrToken))
                    $arrQue[$arrToken[1]] = $arrToken[2];
            }
        }
        return $arrQue;
    }

    function getNetwork_Traffic()
    {
        $results = array();
        $data = `cat /proc/net/dev`;

        if(isset($data)){
            $arrData = explode("\n", $data);
            foreach($arrData as $line){
                if(preg_match('/:/', $line)) {
                    list($dev, $stats_list) = preg_split('/:/', $line, 2);
                    $stats = preg_split('/\s+/', trim($stats_list));
                    $dev = trim($dev);
                    $results[$dev]['rx_bytes']   = $stats[0];
                    $results[$dev]['rx_packets'] = $stats[1];
                    $results[$dev]['tx_bytes']   = $stats[8];
                    $results[$dev]['tx_packets'] = $stats[9];
                } 
            }
        }
        return $results["eth0"];
    }

    function getNetwork_TrafficAverage()
    {
        $r1 = $this->getNetwork_Traffic();
        sleep(3);
        $r2 = $this->getNetwork_Traffic();

        $result['rx_bytes']   = number_format((($r2['rx_bytes']   - $r1['rx_bytes'])/1000),2);
        $result['rx_packets'] = number_format((($r2['rx_packets'] - $r1['rx_packets'])/1000),2);
        $result['tx_bytes']   = number_format((($r2['tx_bytes']   - $r1['tx_bytes'])/1000),2);
        $result['tx_packets'] = number_format((($r2['tx_packets'] - $r1['tx_packets'])/1000),2);
        return $result;
    }

    function _getAll_Trunk()
    {
        $dsn = "mysql://root:eLaStIx.2oo7@localhost/asterisk";
        $pDBTrunk  = new paloDB($dsn);
        $arrTrunks = getTrunks($pDBTrunk);
        $trunks = array();
        if(empty($arrTrunks)) return $trunks;

        if(is_array($arrTrunks) & count($arrTrunks)>0){
            foreach($arrTrunks as $key => $trunk){
                $tmp = split("/",$trunk[1]);
                $trunks[] = $tmp[1];
            }
        }
        return $trunks;
    }

    function AsteriskManager_Command($command_data, $return_data=true) {
        global $arrLang;
        $salida = array();
        $astman = new AGI_AsteriskManager();

        if (!$astman->connect("127.0.0.1", "admin" , "elastix456")) {
            $this->errMsg = $arrLang["Error when connecting to Asterisk Manager"];
        } else{
            $salida = $astman->send_request('Command', array('Command'=>"$command_data"));
            $astman->disconnect();
            $salida["Response"] = isset($salida["Response"])?$salida["Response"]:"";
            if (strtoupper($salida["Response"]) != "ERROR") {
                if($return_data) return split("\n",$salida["data"]);
                else return split("\n", $salida["Response"]);
            }else return false;
        }
        return false;
    }

    function getAppletsActivated($user)
    {
        global $arrConf;
        $dsn = "sqlite3:///$arrConf[elastix_dbdir]/dashboard.db";
        $pDB  = new paloDB($dsn);
        $arrApplets = array();

        if($user!= "admin") $user="no_admin";

        $query = "select 
                    a.code 
                  from 
                    activated_applet_by_user aau 
                        inner join 
                    default_applet_by_user dau on aau.id_dabu=dau.id 
                        inner join 
                    applet a on dau.id_applet=a.id 
                  where 
                    dau.username='$user' 
                  order 
                    by aau.order_no asc";

        $result=$pDB->fetchTable($query,true);

        if($result==FALSE){
            $this->errMsg = $pDB->errMsg; 
            return array();
        }

        foreach($result as $value)
            $arrApplets[] = $value["code"];
        return $arrApplets;
    }

    function getApplets_User($user)
    {
        global $arrConf;
        $dsn = "sqlite3:///$arrConf[elastix_dbdir]/dashboard.db";
        $pDB  = new paloDB($dsn);
        if($user!= "admin") $user="no_admin";

        $query = "select 
                    dau.id, a.name, ifnull(aau.id,0) activated, ifnull(aau.order_no,0) order_no
                  from 
                    applet a 
                        inner join 
                    default_applet_by_user dau on a.id=dau.id_applet 
                        left join 
                    activated_applet_by_user aau on dau.id = aau.id_dabu 
                  where 
                    dau.username='$user' 
                  order by dau.id asc;";

        $result=$pDB->fetchTable($query, true);

        if($result==FALSE){
            $this->errMsg = $pDB->errMsg;
            return array();
        } 
        return $result;
    }

    function setApplets_User($arrIDs_DAU, $user)
    {
        global $arrConf;
        $dsn = "sqlite3:///$arrConf[elastix_dbdir]/dashboard.db";
        $pDB  = new paloDB($dsn);

        if(is_array($arrIDs_DAU) & count($arrIDs_DAU)>0){
            if($user!= "admin") $user="no_admin";

            $pDB->beginTransaction();
            // Parte 1: Elimino todas las actuales
            $query1 = " delete from activated_applet_by_user 
                        where id_dabu in (select id from default_applet_by_user where username='$user')";
            $result1=$pDB->genQuery($query1);

            if($result1==FALSE){
                $this->errMsg = $pDB->errMsg;
                $pDB->rollBack();
                return false;
            }

            // Parte 2: Inserto todas las checked
            foreach($arrIDs_DAU as $key => $value){
                $query2 = "insert into activated_applet_by_user (id_dabu, order_no) values ($value,".($key+1).")";
                $result2=$pDB->genQuery($query2);

                    if($result2==FALSE){
                        $this->errMsg = $pDB->errMsg;
                        $pDB->rollBack();
                        return false;
                    }
            }
            $pDB->commit();
        }
        return true;
    }
}
?>
