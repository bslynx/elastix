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
        return "<div class='tabFormTable' style='text-align:center;'>".$this->getImage_Hit()."</div>";
    }

    function getDataApplet_News()
    {
        $infoRSS = @fetch_rss($this->arrConf['dir_RSS']);
        $sMensaje = magpie_error();
        if (preg_match("/HTTP Error: connection failed/", $sMensaje)) {
        	return _tr('Could not get web server information. You may not have internet access or the web server is down');
        }
        $sContentList = '<div class="neo-applet-news-row">'._tr('No News to display').'</div>';
        if (!empty($infoRSS) && is_array($infoRSS->items) && count($infoRSS->items) > 0) {
        	$sContentList = '';
            $sPlantilla = <<<PLANTILLA_RSS_ROW
<div class="neo-applet-news-row">
    <span class="neo-applet-news-row-date">%s</span>
    <a href="https://twitter.com/share?original_referer=%s&related=&source=tweetbutton&text=%s&url=%s&via=elastixGui"  target="_blank">
        <img src="modules/dashboard/images/twitter-icon.png" width="16" height="16" alt="tweet" />
    </a>
    <a href="%s" target="_blank">%s</a>
</div>
PLANTILLA_RSS_ROW;
            for ($i = 0; $i < 7 && $i < count($infoRSS->items); $i++) {
                $sContentList .= sprintf($sPlantilla, 
                    date('Y.m.d', $infoRSS->items[$i]['date_timestamp']),
                    urlencode('http://www.elastix.org'),
                    urlencode($infoRSS->items[$i]['title']),
                    urlencode($infoRSS->items[$i]['link']),
                    $infoRSS->items[$i]['link'],
                    htmlentities($infoRSS->items[$i]['title'], ENT_COMPAT, 'UTF-8'));
            }
        }
        return $sContentList;
    }

    function getDataApplet_ProcessesStatus()
    {
        $oPalo = new paloSantoSysInfo();
        $arrServices = $oPalo->getStatusServices();

        $sMsgStart = _tr('Start process');
        $sMsgStop = _tr('Stop process');
        $sMsgRestart = _tr('Restart process');
        $sListaServicios = <<<PLANTILLA_POSICIONABLE
<div class="neo-applet-processes-menu">
<input type="hidden" id="neo_applet_selected_process" value="" />
<div id="neo-applet-processes-controles">
<input style="width: 120px; display: block;" type="button" id="neo_applet_process_stop" value="$sMsgStop" />
<input style="width: 120px; display: block;" type="button" id="neo_applet_process_start" value="$sMsgStart" />
<input style="width: 120px; display: block;" type="button" id="neo_applet_process_restart" value="$sMsgRestart" />
</div>
<img id="neo-applet-processes-processing" src="modules/{$this->module_name}/images/reload.png" style="display: none;" alt="" />
</div>
PLANTILLA_POSICIONABLE;
        
        $listaIconos = array(
            'Asterisk'  =>  'icon_pbx.png',
            'OpenFire'  =>  'icon_im.png',
            'Hylafax'   =>  'icon_fax.png',
            'Postfix'   =>  'icon_email.png',
            'MySQL'     =>  'icon_db.png',
            'Apache'    =>  'icon_www.png',
            'Dialer'    =>  'icon_headphones.png',
        );
        $sIconoDesconocido = 'system.png';
        $sPlantilla = <<<PLANTILLA_PROCESS_ROW
<div class="neo-applet-processes-row">
    <div class="neo-applet-processes-row-icon"><img src="modules/dashboard/images/%s" width="32" height="28" alt="%s" /></div>
    <div class="neo-applet-processes-row-name">%s</div>
    <div class="neo-applet-processes-row-menu" 
        onclick="neoAppletProcesses_manejarMenu(this, '%s', '%s');">
        <img src="modules/dashboard/images/icon_arrowdown.png" width="15" height="15" alt="menu" />
    </div>
    <div class="neo-applet-processes-row-status-msg" style="color: %s">%s</div>
    <div class="neo-applet-processes-row-status-icon"></div></div>
PLANTILLA_PROCESS_ROW;
        foreach ($arrServices as $sServicio => $infoServicio) {
            switch ($infoServicio['status_service']) {
            case 'OK':
                $sDescStatus = _tr('Running');
                $sColorStatus = '#006600';
                break;
            case 'Shutdown':
                $sDescStatus = _tr('Not running');
                $sColorStatus = '#880000';
                break;
            default:
                $sDescStatus = _tr('Not installed');
                $sColorStatus = '#000088';
                break;
            }
            $sListaServicios .= sprintf($sPlantilla,
                isset($listaIconos[$sServicio]) ? $listaIconos[$sServicio] : $sIconoDesconocido,
                $sServicio,
                _tr($infoServicio['name_service']),
                $sServicio,
                $infoServicio['status_service'],
                $sColorStatus,
                $sDescStatus);
        }
        return $sListaServicios;
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
                    <div id='layerCM' style='position:relative'>
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
                            <div class='typeActivity'><b>"._tr('Trunks')." (SIP/IAX): </b></div>
                            <div class='detailText'>"._tr('Trunks')." <b>($total_trunks) </b>: <font color='green'>($total_trunks_ok "._tr('OK').")</font> <font color='red'>($total_trunks_nok "._tr('NO_OK').")</font> </font> <font color='gray'>($total_trunks_unk "._tr('Unknown').")</font></div>".
                            "<div class='typeActivity'><b>"._tr('Network_traffic').": </b></div>
                            <div class='detailText'>"._tr('Bytes')." <b>(".$rx_bytes."kB/s)</b> <= RX | TX =>  <b>(".$tx_bytes."kB/s)</b></div>
                        </div>
                    </div>";
    }

    function getDataApplet_SystemResources()
    {
		/*include("libs/pChart/libs/pData.class");
		include("libs/pChart/libs/pChart.class");
		include("libs/pChart/libs/MyHorBar.class.php");*/


        $oPalo = new paloSantoSysInfo();
        $arrSysInfo = $oPalo->getSysInfo();
        //CPU INFO    $arrSysInfo['CpuVendor'] 
        $cpu_info = $arrSysInfo['CpuModel'];
    
        //CPU USAGE
        $cpu_usage = $this->getImage_CPU_Usage("140,140");//$this->module_name,
		$inf1 = number_format($arrSysInfo['CpuUsage'] * 100.0, 2)." %";
        //$inf1 = number_format($arrSysInfo['CpuUsage']*100, 2)._tr('% used of')." ".number_format($arrSysInfo['CpuMHz'], 2)." MHz";
        //$cpu_usage =  $img."&nbsp;&nbsp;&nbsp;".$inf;
    
        //MEMORY USAGE
        $mem_usage_val  = number_format(100.0 * ($arrSysInfo['MemTotal'] - $arrSysInfo['MemFree'] - $arrSysInfo['Cached'] - $arrSysInfo['MemBuffers'])/$arrSysInfo['MemTotal'], 2);
        $mem_usage = $this->getImage_MEM_Usage("140,140");
		$inf2 = number_format($arrSysInfo['MemTotal']/1024, 2)." Mb";
        
        //$inf2 = number_format($mem_usage_val*100, 2)._tr('% used of')." ".number_format($arrSysInfo['MemTotal']/1024, 2)." Mb";
        //$mem_usage = $img."&nbsp;&nbsp;&nbsp;".$inf;
    
        //SWAP USAGE
        $swap_usage_val = number_format(100.0 * ($arrSysInfo['SwapTotal'] - $arrSysInfo['SwapFree'])/$arrSysInfo['SwapTotal'], 2);
        $swap_usage = $this->getImage_Swap_Usage("140,140");
		$inf3 = number_format($arrSysInfo['SwapTotal']/1024, 2)." Mb";
        //$inf3 = number_format($swap_usage_val, 2)." ".number_format($arrSysInfo['SwapTotal']/1024, 2)." Mb";
        //$swap_usage = $img."&nbsp;&nbsp;&nbsp;".$inf;
    
        //UPTIME
        $uptime = $arrSysInfo['SysUptime'];

        // CPU SPEED
        $speed = number_format($arrSysInfo['CpuMHz'], 2)." MHz";

/*
		// Dataset definition
		$DataSet = new pData;
		$DataSet->AddPoint(array(1,2),"Serie1");
		$DataSet->AddPoint(array(2,3),"Serie2");
		$DataSet->AddPoint(array(3,4),"Serie3");
		$DataSet->AddAllSeries();
		$DataSet->SetAbsciseLabelSerie();
		$DataSet->SetSerieName("January","Serie1");
		$DataSet->SetSerieName("February","Serie2");
		$DataSet->SetSerieName("March","Serie3");

		// Initialise the graph
		//$Test = new pChart(300,100);
		$Test = new MyHorBar(400,200);
		$Test->setFontProperties("Fonts/tahoma.ttf",8);
		$Test->setGraphArea(5,5,350,250);
		$Test->drawFilledRoundedRectangle(7,7,693,223,5,240,240,240);
		$Test->drawRoundedRectangle(5,5,695,225,5,230,230,230);
		//$Test->drawGraphArea(255,255,255,TRUE);
		//$Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,0,2,TRUE);
		$Test->drawHorScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,0,2,TRUE);
		$Test->drawHorGrid(10,TRUE,230,230,230,50);
		$Test->drawGrid(4,TRUE,230,230,230,50);

		// Draw the 0 line
		$Test->setFontProperties("Fonts/tahoma.ttf",6);
		$Test->drawTreshold(0,143,55,72,TRUE,TRUE);

		// Draw the bar graph
		//$Test->drawBarGraph($DataSet->GetData(),$DataSet->GetDataDescription(),TRUE);
        // Draw the bar graph
        $Test->drawHorBarGraph($DataSet->GetData(),$DataSet->GetDataDescription(),FALSE);
		// Finish the graph
		$Test->setFontProperties("Fonts/tahoma.ttf",8);
		$Test->drawLegend(596,150,$DataSet->GetDataDescription(),255,255,255);
		$Test->setFontProperties("Fonts/tahoma.ttf",10);
		$Test->drawTitle(50,22,"Prueba 1",50,50,50,585);

		$Test->Render($_SERVER['DOCUMENT_ROOT']."/libs/pChart/tmp/pruebaIMG.png");
*/

		$html ="<div style='height:165px; position:relative; text-align:center;'>
				  <div style='width:155px; float:left; position: relative;'>
					$cpu_usage
					<div style=\"position:absolute; top:80px; left:0px; color:#ccc; width:155px; text-align:center\">$inf1</div><div>"._tr('CPU')."</div>
				  </div>
				  <div style='width:154px; float:left; position: relative;'>
					$mem_usage
					<div style=\"position:absolute; top:80px; left:0px; color:#ccc; width:155px; text-align:center\">$mem_usage_val %</div><div>"._tr('RAM')."</div>
				  </div>
				  <div style='width:155px; float:right; position: relative;'>
					$swap_usage
				  <div style=\"position:absolute; top:80px; left:0px; color:#ccc; width:155px; text-align:center\">$swap_usage_val %</div><div>"._tr('SWAP')."</div>
				  </div>
				</div>
				<div class='neo-divisor'></div>
				<div class=neo-applet-tline>
				  <div class='neo-applet-titem'><strong>"._tr('CPU Info').":</strong></div>
				  <div class='neo-applet-tdesc'>$cpu_info</div>
				</div>
				<div class=neo-applet-tline>
				  <div class='neo-applet-titem'><strong>"._tr('Uptime').":</strong></div>
				  <div class='neo-applet-tdesc'>$uptime</div>
				</div>
				<div class='neo-applet-tline'>
				  <div class='neo-applet-titem'><strong>"._tr('CPU Speed').":</strong></div>
				  <div class='neo-applet-tdesc'>$speed</div>
				</div>
				<div class='neo-applet-tline'>
				  <div class='neo-applet-titem'><strong>"._tr('Memory usage').":</strong></div>
				  <div class='neo-applet-tdesc'>RAM: $inf2 SWAP: $inf3</div>
				</div>";
		return $html;

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
	global $arrConf;
        $systemStatus=_tr("Error at read status system.");

        $pDB2 = $this->conectionAsteriskCDR();
        if($pDB2){
            $objUserInfo = new paloSantoDashboard($pDB2);
            $arrData     = $objUserInfo->getDataUserLogon($_SESSION["elastix_user"]);

            if(is_array($arrData) && count($arrData)>0){
		if(isset($arrData['login']) && $arrData['login'] != "" && isset($arrData['domain']) && $arrData['domain'] != ""){
		    $email     = "{$arrData['login']}@{$arrData['domain']}";
		    if(file_exists("$arrConf[elastix_dbdir]/email.db")){
			$pDBemail = new paloDB("sqlite3:///$arrConf[elastix_dbdir]/email.db");
			$passw     = isset($arrData['password'])?$arrData['password']:"";
			if($this->emailExists($email,$pDBemail) && $this->isPasswordCorrect($email,$passw,$pDBemail)){	    
			    $systemStatus= $objUserInfo->getSystemStatus($email,$passw);
			}
			else
			    $systemStatus = "$email "._tr("does not exist locally or password is incorrect");
		    }
		    else
			$systemStatus = _tr("The following database could not be found").": $arrConf[elastix_dbdir]/email.db";
		}
		else
		    $systemStatus = _tr("You don't have a webmail account");
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
	global $arrConf;
        $mails =_tr("Error at read yours mails.");
        $pDB2 = $this->conectionAsteriskCDR();
	
        if($pDB2){
            $objUserInfo = new paloSantoDashboard($pDB2);
            $arrData     = $objUserInfo->getDataUserLogon($_SESSION["elastix_user"]);
            if(is_array($arrData) && count($arrData)>0){
		if(isset($arrData['login']) && $arrData['login'] != "" && isset($arrData['domain']) && $arrData['domain'] != ""){
		    $email     = "{$arrData['login']}@{$arrData['domain']}";
		    if(file_exists("$arrConf[elastix_dbdir]/email.db")){
			  $pDBemail = new paloDB("sqlite3:///$arrConf[elastix_dbdir]/email.db");
			  $passw    = isset($arrData['password'])?$arrData['password']:"";
			  if($this->emailExists($email,$pDBemail) && $this->isPasswordCorrect($email,$passw,$pDBemail)){	      
			      $numRegs   = 8;
			      $mails     = @$objUserInfo->getMails($email,$passw,$numRegs);
			  }
			  else
			      $mails = "$email "._tr("does not exist locally or password is incorrect");
		    }
		    else
			$mails = _tr("The following database could not be found").": $arrConf[elastix_dbdir]/email.db";
		}
		else
		    $mails = _tr("You don't have a webmail account");
            }
        }
        return $mails;
    }

    function emailExists($email,&$pDB)
    {
	$query = "select count(*) from accountuser where username=?";
	$result = $pDB->getFirstRowQuery($query,false,array($email));
	if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        if($result[0] > 0)
	    return true;
	else
	    return false;
    }
  
    function isPasswordCorrect($email,$password,&$pDB)
    {
	$query = "select password from accountuser where username=?";
	$result = $pDB->getFirstRowQuery($query,true,array($email));
	if($result===FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
	if($password == $result["password"])
	    return true;
	else
	    return false;
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
                        <div class='tabapplet' width='80%' style='float:left;'>
                            $title
                        </div>
                        <div class='closeapplet' align='right' width='10%'>
                            <a id='refresh_{$code}' style='cursor: pointer;' class='toggle' onclick='javascript:refresh(this)'>
                                <img id='imga11'  class='ima'  src='modules/{$this->module_name}/images/reload.png' border='0' align='absmiddle' />
                            </a>
                        </div>
                    </div>
                    <div class='portlet_content' id = '$code'>
                        <img class='ima' src='modules/{$this->module_name}/images/loading.gif' border='0' align='absmiddle' />&nbsp;
                        "._tr('Loading')."
                    </div>
                </div>";
    }

   function genericImage($sGraph, $extraParam = array(), $w = NULL, $h = NULL)
   {
         return sprintf('<img alt="%s" src="%s" %s />',
             $sGraph,
             construirURL(array_merge(array(
                  'menu'      => $this->module_name,
                  'action'    =>  'image',
                  'rawmode'   =>  'yes',
                  'image'     =>  $sGraph,
                   ), $extraParam)),
               (is_null($w) || is_null($h) ? '' : "width=\"$w\" height=\"$h\""));
   }

    function getImage_CPU_Usage($value = null)
    {
		if(isset($value))
			return $this->genericImage("ObtenerInfo_CPU_Usage", array('size' => $value), NULL, NULL);
		else
			return $this->genericImage("ObtenerInfo_CPU_Usage");
    }

    function getImage_Disc_Usage($value)
    {
        return $this->genericImage("ObtenerInfo_Particion", array('percent' => $value), 190, 190);
    }

    function getImage_Hit()
    {
        return $this->genericImage("CallsMemoryCPU");
    }

    function getImage_MEM_Usage($value = null)
    {
		if(isset($value)){
			return $this->genericImage("ObtenerInfo_MemUsage", array('size' => $value), null, null);
		}else
			return $this->genericImage("ObtenerInfo_MemUsage");
    }

    function getImage_Swap_Usage($value = null)
    {
		if(isset($value))
			return $this->genericImage("ObtenerInfo_SwapUsage", array('size' => $value), null, null);
		else
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
