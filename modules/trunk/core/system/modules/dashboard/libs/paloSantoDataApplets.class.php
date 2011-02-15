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
  $Id: paloSantoDataApplets.class.php,v 1.1.1.1 2011/02/11 Alberto Santos 21:31:55  Exp $ */

include_once "libs/paloSantoGraphImage.lib.php";
include_once "paloSantoSysInfo.class.php";
include_once "paloSantoDashboard.class.php";
require_once "libs/magpierss/rss_fetch.inc";
require_once "libs/paloSantoDB.class.php";

class paloSantoDataApplets
{
    var $arrConf;
    var $icon;
    var $title;
    var $module_name;

    function paloSantoDataApplets($module_name,$arrConf)
    {
        $this->arrConf = $arrConf;
        $this->module_name = $module_name;
        $icon = "";
        $title = "";
    }

    function getDataApplet_HardDrives()
    {
        $oPalo = new paloSantoSysInfo();
        $arrSysInfo = $oPalo->getSysInfo();
        $arrParticiones = $arrSysInfo['particiones'];
        $str = ""; $val = null;
        foreach( $arrParticiones as $key => $particion )
        {
            $val_1 = ( ereg("^([[:digit:]]{1,2}(\.[[:digit:]]{1,4})?)%$", trim($particion['uso_porcentaje']), $arrReg) )
                    ? $arrReg[1]: NULL;
            $val_2 = number_format($particion['num_bloques_total'] / 1024 / 1024, 2);
            $content = "<div class='infoDisc'>
                            <div class='type'><b>"._tr('Partition Name').":</b></div>
                            <div align='center' class='detail'>".$particion['fichero']."</div>
                            <div class='type'>"._tr('Capacity').":</div>
                            <div align='center' class='detail'>".$val_2."GB</div>
                            <div class='type'>"._tr('Usage').":</div>
                            <div align='center' class='detail'>".$val_1."%</div>
                            <div class='type'>"._tr('Mount point')."</div>
                            <div align='center' class='detail'>".$particion['punto_montaje']."</div>
                        </div>
                        <div class='imgDisc'>".$this->getImage_Disc_Usage($val_1)."</div>";
        }
        return $content;
    }

    function getDataApplet_PerformanceGraphic()
    {
        return "<div class='tabFormTable'>".$this->getImage_Hit()."</div>";
    }

    function getDataApplet_News()
    {
        $RSS = $this->arrConf['dir_RSS'];
        $str = "";
        //$url = "http://sourceforge.net/export/rss2_projnews.php?group_id=161807";
        $url = $RSS;
        $rss = @fetch_rss($url);
        $message = magpie_error();
        if(preg_match("/HTTP Error: connection failed/",$message,$match)){
            $str2 = _tr("Could not get web server information. You may not have internet access or the web server is down");
        }
        else{
            $str2 = "";
            if(!empty($rss)){
                $str2  = "<font color = 'Black'><b>".$rss->channel['title'] . "</b></font><p>";
                $str2 .= "<div id='rss_elastix'>";
                $n = 0;
                if(is_array($rss->items) & count($rss->items)>0){
                    foreach ($rss->items as $item) {
                            $href  = $item['link'];
                            $title = $item['title'];
                            $str2 .= "<div class='scrollEl' align='center'><a href=$href target='_blank'><span>$title</span></a></div>";
                        $n++;
                        if($n == 4) break;
                    }
                    $str2 .= "</div>";
                }
            }
            else{
                $str2 = "<span>"._tr('No News to display')."</span>";
            }
        }
        return   "<div id='wrapper'>
                        <div id='vertical'>
                            $str2
                        </div>
                    </div>";
    }

    function getDataApplet_ProcessesStatus()
    {
        $oPalo = new paloSantoSysInfo();
        $arrServices = $oPalo->getStatusServices();
        $str = "";
        $servicesStatus = "";
        $color = "";
        
        foreach($arrServices as $key=>$value){
            if($value["status_service"]=="OK"){
                $status = "<font color='green'><i>"._tr('Running')."</i></font>";
                $serStatus = _tr('OK1');
                $color = "#10ED00";
            }
            elseif($value["status_service"]=="Shutdown"){
                $status = "<font color='blue'><i>"._tr('Not running')."</i></font>";
                $serStatus = _tr('Shutdown');
                $color = "#0043EC";
            }
            else{
                $status = "<font color='blue'><i>"._tr('Not installed')."</i></font>";
                $serStatus = _tr('Shutdown');
                $color = "#0043EC";
            }
            $servicesStatus .= "<div class='services'>"._tr($value['name_service'])."&nbsp;  ($key): &nbsp;&nbsp; "."$status</div><div align='center' style='background-color:".$color.";' class='status' >$serStatus</div>";
        }
        return "<div class='tabFormTable'>$servicesStatus</div>";
    }

    function getDataApplet_TelephonyHardware()
    {
        $oPalo = new paloSantoSysInfo();
        $arrCards = $oPalo->checkRegistedCards();
        $str = "";
        $cardsStatus = "";
        $color = "";
        $i = 1;
        if(count($arrCards)>0 && $arrCards!=null){
            foreach($arrCards as $key=>$value){
                if($value["num_serie"]==""){
                    $serStatus = "<a id='editMan1_$value[hwd]' style='text-decoration:none;color:white; cursor:pointer;' onClick ='jfunction(\"editMan1_$value[hwd]\");'>"._tr('No Registered')."</a>";
                    $color = "#FF0000";
                    $image = "modules/hardware_detector/images/card_no_registered.gif";
                }
                else{
                    $serStatus = "<a id='editMan2_$value[hwd]' style='text-decoration:none;color:white;cursor:pointer;' onClick = 'jfunction(\"editMan2_$value[hwd]\");'>"._tr('Registered')."</a>";
                    $color = "#10ED00";
                    $image = "modules/hardware_detector/images/card_registered.gif";
                }
                $cardsStatus .= "<div class='services'>$i.-&nbsp;".$value['card']." ($value[vendor]): &nbsp;&nbsp; </div>
                                <div align='center' style='background-color:".$color.";' class='status' >$serStatus</div>";
                $i++;
            }
        }else{
            $cardsStatus="<br /><div align='center' style='color:red;'><strong>"._tr('Cards no found')."</strong></div>";
        }
        return "<div class='tabFormTable'>$cardsStatus</div>
                    <div id='layerCM'>
                        <div class='layer_handle' id='closeCM'></div>
                        <div id='layerCM_content'></div>
                    </div>";
    }

    function getDataApplet_CommunicationActivity()
    {
        $oPalo = new paloSantoSysInfo();
        $channels = $oPalo->getAsterisk_Channels();
        $queues = $oPalo->getAsterisk_QueueWaiting();
        $connections = $oPalo->getAsterisk_Connections();
        $network = $oPalo->getNetwork_TrafficAverage();
        $total = $channels['total_calls'];
        $internal = $channels['internal_calls'];
        $external = $channels['external_calls'];
        $channel = $channels['total_channels'];
        $totalQueues = 0;
        // sum queues
        foreach($queues as $key=>$value){
            $totalQueues += $value;
        }
        
    //     if($total == 1)  $total = $total." ".$arrLang['call'];
    //   else   $total = $total." ".$arrLang['calls'];
    
        if($internal == 1) $internal = $internal." "._tr('call');
        else   $internal = $internal." "._tr('calls');
    
        if($external == 1) $external = $external." "._tr('call');
        else   $external = $external." "._tr('calls');
    
        if($channel == 1)  $channel = $channel." "._tr('channel');
        else   $channel = $channel." "._tr('channels');
    
    //// asterisk connection
        $sip_Ext_ok  = $connections['sip']['ext']['ok'];
        $sip_Ext_nok = $connections['sip']['ext']['no_ok'];
        $total_sip_Ext = $sip_Ext_ok + $sip_Ext_nok;
    
        $sip_trunk_ok  = $connections['sip']['trunk']['ok'];
        $sip_trunk_nok = $connections['sip']['trunk']['no_ok'];
        $sip_trunk_unk = $connections['sip']['trunk']['unknown'];
        $total_sip_trunk = $sip_trunk_ok + $sip_trunk_nok + $sip_trunk_unk;
    
        //$sip_trunk_reg_ok = $connections['sip']['trunk_registry']['ok'];
        //$sip_trunk_reg_nok= $connections['sip']['trunk_registry']['no_ok'];
        //$total_sip_trunk_reg = $sip_trunk_reg_ok + $sip_trunk_reg_nok;
    
        $iax_Ext_ok  = $connections['iax']['ext']['ok'];
        $iax_Ext_nok = $connections['iax']['ext']['no_ok'];
        $total_iax_Ext = $iax_Ext_ok + $iax_Ext_nok;
    
        $iax_trunk_ok  = $connections['iax']['trunk']['ok'];
        $iax_trunk_nok = $connections['iax']['trunk']['no_ok'];
        $iax_trunk_unk = $connections['iax']['trunk']['unknown'];
        $total_iax_trunk = $iax_trunk_ok + $iax_trunk_nok + $iax_trunk_unk;
    
        //$iax_trunk_reg_ok = $connections['iax']['trunk_registry']['ok'];
        //$iax_trunk_reg_nok= $connections['iax']['trunk_registry']['no_ok'];
        //$total_iax_trunk_reg = $iax_trunk_reg_ok + $iax_trunk_reg_nok;
    
        $total_trunks_ok  = $sip_trunk_ok  + $iax_trunk_ok;
        $total_trunks_nok = $sip_trunk_nok + $iax_trunk_nok;
        $total_trunks_unk = $sip_trunk_unk + $iax_trunk_unk;
        //$total_trunks_reg_ok = $sip_trunk_reg_ok + $iax_trunk_reg_ok;
        //$total_trunks_reg_nok = $sip_trunk_reg_nok + $iax_trunk_reg_nok;
        $total_trunks = $total_sip_trunk + $total_iax_trunk;
        //$total_trunks_reg = $total_trunks_reg_ok + $total_trunks_reg_nok;
        ///////traffic network
        $rx_bytes = $network['rx_bytes'];
        $tx_bytes = $network['tx_bytes'];
        $rx_packets = $network['rx_packets'];
        $tx_packets = $network['tx_packets'];
        return "<div class='tabFormTable'>
                        <div class='infoActivity'>
                            <div class='typeActivity'>
                                <b>"._tr('Total_calls').": </b>
                            </div>
                            <div align='left' class='detailText'>
                                <font color='blue'>"._tr('calls')."</font><b>($total)</b>
                                <font color='green'>"._tr('internal_calls')."</font> <b>(".$internal.")</b> <font color='red'>"._tr('external_calls')."</font> <b>(".$external.")</b>
                            </div>
                            <div class='typeActivity'>
                                <b>"._tr('total_channels').": </b>
                            </div>
                            <div align='left' class='detailActivity'>".$channel."</div>
                            <div class='typeActivity'>
                                <b>"._tr('Queues_waiting').": </b>
                            </div>
                            <div align='left' class='detailActivity'>".$totalQueues." "._tr('Waiting')."</div>
                            <div class='typeActivity'><b>"._tr('Extensions').": </b></div>
                            <div class='detailText'>"._tr('sip_extensions')." <b>($total_sip_Ext) </b>: <font color='green'>($sip_Ext_ok "._tr('OK').")</font> <font color='red'>($sip_Ext_nok "._tr('NO_OK').")</font></div>
                            <div class='typeActivity'></div>
                            <div class='detailText'>"._tr('iax_extensions')." <b>($total_iax_Ext) </b>: <font color='green'>($iax_Ext_ok "._tr('OK').")</font> <font color='red'>($iax_Ext_nok "._tr('NO_OK').")</font></div>
                            <div class='typeActivity'><b>"._tr('Trunks').": </b></div>
                            <div class='detailText'>"._tr('Trunks')." <b>($total_trunks) </b>: <font color='green'>($total_trunks_ok "._tr('OK').")</font> <font color='red'>($total_trunks_nok "._tr('NO_OK').")</font> </font> <font color='gray'>($total_trunks_unk "._tr('Unknown').")</font></div>".
                            "<div class='typeActivity'><b>"._tr('Network_traffic').": </b></div>
                            <div class='detailText'>"._tr('Bytes')." <b>(".$rx_bytes."kB/s)</b> <= RX | TX =>  <b>(".$tx_bytes."kB/s)</b></div>
                        </div>
                    </div>";
    }

    function getDataApplet_SystemResources()
    {
        $oPalo = new paloSantoSysInfo();
        $arrSysInfo = $oPalo->getSysInfo();
        //CPU INFO
        $cpu_info = $arrSysInfo['CpuVendor'] . " " . $arrSysInfo['CpuModel'];
    
        //CPU USAGE
        $img = $this->getImage_CPU_Usage($this->module_name);
        $inf = number_format($arrSysInfo['CpuUsage']*100, 2)._tr('% used of')." ".number_format($arrSysInfo['CpuMHz'], 2)." MHz";
        $cpu_usage =  $img."&nbsp;&nbsp;&nbsp;".$inf;
    
        //MEMORY USAGE
        $mem_usage  = ($arrSysInfo['MemTotal'] - $arrSysInfo['MemFree'] - $arrSysInfo['Cached'] - $arrSysInfo['MemBuffers'])/$arrSysInfo['MemTotal'];
        $img = $this->getImage_MEM_Usage();
        $inf = number_format($mem_usage*100, 2)._tr('% used of')." ".number_format($arrSysInfo['MemTotal']/1024, 2)." Mb";
        $mem_usage = $img."&nbsp;&nbsp;&nbsp;".$inf;
    
        //SWAP USAGE
        $swap_usage = ($arrSysInfo['SwapTotal'] - $arrSysInfo['SwapFree'])/$arrSysInfo['SwapTotal'];
        $img = $this->getImage_Swap_Usage();
        $inf = number_format($swap_usage*100, 2)._tr('% used of')." ".number_format($arrSysInfo['SwapTotal']/1024, 2)." Mb";
        $swap_usage = $img."&nbsp;&nbsp;&nbsp;".$inf;
    
        //UPTIME
        $uptime = $arrSysInfo['SysUptime'];
        return     "<div>
                        <div class='type'>"._tr('CPU Info').": </div>
                        <div class='detail'>$cpu_info</div>
                        <div class='type'>"._tr('Uptime').":</div>
                        <div class='detail'>$uptime</div>
                        <div class='type'>"._tr('CPU usage').":</div>
                        <div class='detail'>$cpu_usage</div>
                        <div class='type'>"._tr('Memory usage').":</div>
                        <div class='detail'>$mem_usage</div>
                        <div class='type'>"._tr('Swap usage').":</div>
                        <div class='detail'>$swap_usage</div>
                    </div>";
    }

    function getDataApplet_Faxes()
    {
        $faxRows = _tr("Error at read yours faxes.");

        $pDB2 = $this->conectionAsteriskCDR();
        if($pDB2){
            $objUserInfo = new paloSantoDashboard($pDB2);
            $arrData     = $objUserInfo->getDataUserLogon($_SESSION["elastix_user"]);

            if(is_array($arrData) && count($arrData)>0){
                $extension = isset($arrData['extension'])?$arrData['extension']:"";
                $numRegs   = 8;
                $faxRows   = $objUserInfo->getLastFaxes($extension,$numRegs);
            }
        }
        return $faxRows;
    }

    function getDataApplet_System()
    {
        $systemStatus=_tr("Error at read status system.");

        $pDB2 = $this->conectionAsteriskCDR();
        if($pDB2){
            $objUserInfo = new paloSantoDashboard($pDB2);
            $arrData     = $objUserInfo->getDataUserLogon($_SESSION["elastix_user"]);

            if(is_array($arrData) && count($arrData)>0){
                $email     = "{$arrData['login']}@{$arrData['domain']}";
                $passw     = isset($arrData['password'])?$arrData['password']:"";
                $systemStatus= $objUserInfo->getSystemStatus($email,$passw);
            }
        }
        return $systemStatus;
    }

    function getDataApplet_Calls()
    {
        $callsRows   =_tr("Error at read yours calls.");
        $pDB2 = $this->conectionAsteriskCDR();
        if($pDB2){
            $objUserInfo = new paloSantoDashboard($pDB2);
            $arrData     = $objUserInfo->getDataUserLogon($_SESSION["elastix_user"]);

            if(is_array($arrData) && count($arrData)>0){
                $extension = isset($arrData['extension'])?$arrData['extension']:"";
                $numRegs   = 8;
                $callsRows   = $objUserInfo->getLastCalls($extension,$numRegs);
            }
        }
        return $callsRows;
    }

    function getDataApplet_Calendar()
    {
        $eventsRows  =_tr("Error at read your calendar.");
        $pDB2 = $this->conectionAsteriskCDR();
        if($pDB2){
            $objUserInfo = new paloSantoDashboard($pDB2);
            $arrData     = $objUserInfo->getDataUserLogon($_SESSION["elastix_user"]);

            if(is_array($arrData) && count($arrData)>0){
                $numRegs   = 8;
                $eventsRows  = $objUserInfo->getEventsCalendar($arrData['id'], $numRegs);
            }
        }
        return $eventsRows;
    }

    function getDataApplet_Emails()
    {
        $mails =_tr("Error at read yours mails.");
        $pDB2 = $this->conectionAsteriskCDR();
        if($pDB2){
            $objUserInfo = new paloSantoDashboard($pDB2);
            $arrData     = $objUserInfo->getDataUserLogon($_SESSION["elastix_user"]);

            if(is_array($arrData) && count($arrData)>0){
                $email     = "{$arrData['login']}@{$arrData['domain']}";
                $passw     = isset($arrData['password'])?$arrData['password']:"";
                $numRegs   = 8;
                $mails     = $objUserInfo->getMails($email,$passw,$numRegs);
            }
        }
        return $mails;
    }

    function getDataApplet_Voicemails()
    {
        $voiceMails  =_tr("Error at read yours voicemails.");
        $pDB2 = $this->conectionAsteriskCDR();
        if($pDB2){
            $objUserInfo = new paloSantoDashboard($pDB2);
            $arrData     = $objUserInfo->getDataUserLogon($_SESSION["elastix_user"]);

            if(is_array($arrData) && count($arrData)>0){
                $extension = isset($arrData['extension'])?$arrData['extension']:"";
                $numRegs   = 8;
                $voiceMails  = $objUserInfo->getVoiceMails($extension,$numRegs);
            }
        }
        return $voiceMails;
    }

    function drawApplet($idApplet, $code)
    {
        $icon = $this->getIcon();
        $title = $this->getTitle();
        return  "<div class='portlet' id='applet-{$code}-{$idApplet}'>
                    <div class='portlet_topper'>
                        <div width='100%'>
                            <div class='imgapplet' width='10%' style='float:left;'>
                                <img src='modules/{$this->module_name}/images/$icon' border='0' align='absmiddle' />
                            </div>
                            <div class='tabapplet' width='80%' style='float:left;'>
                                $title
                            </div>
                            <div class='closeapplet' align='right' width='10%'>
                                <a href='#' class='toggle'>
                                    <img id='imga11'  class='ima'  src='modules/{$this->module_name}/images/flecha_up.gif' border='0' align='absmiddle' />
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class='portlet_content' id = '$code'>
                        <img class='ima' src='modules/{$this->module_name}/images/loading.gif' border='0' align='absmiddle' />&nbsp;
                        "._tr('Loading')."
                    </div>
                </div>";
    }

    function genericImage($sGraph, $extraParam = array())
    {
        return sprintf('<img alt="%s", src="%s" />', 
            $sGraph,
            construirURL(array_merge(array(
                'menu'      => $this->module_name,
                'action'    =>  'image',
                'rawmode'   =>  'yes',
                'image'     =>  $sGraph,
                ), $extraParam)));
    }

    function getImage_CPU_Usage()
    {
        return $this->genericImage("ObtenerInfo_CPU_Usage");
    }

    function getImage_Disc_Usage($value)
    {
        return $this->genericImage("ObtenerInfo_Particion", array('percent' => $value));
    }

    function getImage_Hit()
    {
        return $this->genericImage("CallsMemoryCPU");
    }

    function getImage_MEM_Usage()
    {
        return $this->genericImage("ObtenerInfo_MemUsage");
    }

    function getImage_Swap_Usage()
    {
        return $this->genericImage("ObtenerInfo_SwapUsage");
    }

    function conectionAsteriskCDR()
    {
        $dsnAsteriskCDR = generarDSNSistema("asteriskuser","asteriskcdrdb");
        $pDB = new paloDB($dsnAsteriskCDR);

        if(!empty($pDB->errMsg)) 
            return false;
        else
            return $pDB;
    }

    function getIcon()
    {
        return $this->icon;
    }

    function getTitle()
    {
        return $this->title;
    }

    function setIcon($icon)
    {
        $this->icon = $icon;
    }

    function setTitle($title)
    {
        $this->title = $title;
    }
}

?>