<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 0.5                                                  |
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
  $Id: index.php,v 1.2 2007/07/07 22:50:39 admin Exp $ */

//LIBRERIA GRAFICA
include_once "libs/paloSantoGraph.class.php";
require_once "libs/magpierss/rss_fetch.inc"; 
include_once "libs/paloSantoForm.class.php";

function _moduleContent($smarty, $module_name)
{
    require_once "libs/misc.lib.php";

    //include module files
    include_once "modules/$module_name/libs/paloSantoSysInfo.class.php";
    include_once "modules/$module_name/libs/paloSantoDashboard.class.php";
    include_once "modules/$module_name/configs/default.conf.php";

    //include file language agree to elastix configuration
    //if file language not exists, then include language by default (en)
    $lang=get_language();
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $lang_file="modules/$module_name/lang/$lang.lang";
    if (file_exists("$base_dir/$lang_file")) include_once "$lang_file";
    else include_once "modules/$module_name/lang/en.lang";


    //global variables
    global $arrConf;
    global $arrConfModule;
    global $arrLang;
    global $arrLangModule;
    $arrConf = array_merge($arrConf,$arrConfModule);
    $arrLang = array_merge($arrLang,$arrLangModule);

    //folder path for custom templates
    $templates_dir=(isset($arrConf['templates_dir']))?$arrConf['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    $oPalo = new paloSantoSysInfo();
    $arrSysInfo = $oPalo->getSysInfo();


    //CPU INFO
    $smarty->assign("cpu_info", $arrSysInfo['CpuVendor'] . " " . $arrSysInfo['CpuModel']);
    $cpu_info = $arrSysInfo['CpuVendor'] . " " . $arrSysInfo['CpuModel'];

    //CPU USAGE
    $img = getImage_CPU_Usage($module_name);
    $inf = number_format($arrSysInfo['CpuUsage']*100, 2)."{$arrLang['% used of']} ".number_format($arrSysInfo['CpuMHz'], 2)." MHz";
    $smarty->assign("cpu_usage", $img."&nbsp;&nbsp;&nbsp;".$inf);
	 $cpu_usage =  $img."&nbsp;&nbsp;&nbsp;".$inf;

    //MEMORY USAGE
    $mem_usage  = ($arrSysInfo['MemTotal'] - $arrSysInfo['MemFree'] - $arrSysInfo['Cached'] - $arrSysInfo['MemBuffers'])/$arrSysInfo['MemTotal'];
    $img = getImage_MEM_Usage($module_name);
    $inf = number_format($mem_usage*100, 2)."{$arrLang['% used of']} ".number_format($arrSysInfo['MemTotal']/1024, 2)." Mb";
    $smarty->assign("mem_usage", $img."&nbsp;&nbsp;&nbsp;".$inf);
    $mem_usage = $img."&nbsp;&nbsp;&nbsp;".$inf;

    //SWAP USAGE
    $swap_usage = ($arrSysInfo['SwapTotal'] - $arrSysInfo['SwapFree'])/$arrSysInfo['SwapTotal'];
    $img = getImage_Swap_Usage($module_name);
    $inf = number_format($swap_usage*100, 2)."{$arrLang['% used of']} ".number_format($arrSysInfo['SwapTotal']/1024, 2)." Mb";
    $smarty->assign("swap_usage", $img."&nbsp;&nbsp;&nbsp;".$inf );
    $swap_usage = $img."&nbsp;&nbsp;&nbsp;".$inf;

    //UPTIME
    $smarty->assign("uptime",  $arrSysInfo['SysUptime']);
	 $uptime = $arrSysInfo['SysUptime'];

    // DASHBOARD

 	$callsRows   =$arrLang["Error at read yours calls."];
	$faxRows     =$arrLang["Error at read yours faxes."];
	$voiceMails  =$arrLang["Error at read yours voicemails."];
	$mails       =$arrLang["Error at read yours mails."];
	$systemStatus=$arrLang["Error at read status system."];
	$eventsRows  =$arrLang["Error at read your calendar."];

	$pDB2 = conectionAsteriskCDR();
	if($pDB2){
	    $objUserInfo = new paloSantoDashboard($pDB2);
	    $arrData     = $objUserInfo->getDataUserLogon($_SESSION["elastix_user"]);

	    if(is_array($arrData) && count($arrData)>0){
		$extension = isset($arrData['extension'])?$arrData['extension']:"";
		$email     = "{$arrData['login']}@{$arrData['domain']}";
		$passw     = isset($arrData['password'])?$arrData['password']:"";
		$numRegs   = 8;

		$callsRows   = $objUserInfo->getLastCalls($extension,$numRegs);
		$faxRows     = $objUserInfo->getLastFaxes($extension,$numRegs);
		$voiceMails  = $objUserInfo->getVoiceMails($extension,$numRegs);
		$mails       = $objUserInfo->getMails($email,$passw,$numRegs);
		$systemStatus= $objUserInfo->getSystemStatus($email,$passw);
		$eventsRows  = $objUserInfo->getEventsCalendar($arrData['id'], $numRegs);
	    }
	}

	$smarty->assign("userInf",$arrLang["Dashboard"]);
	$smarty->assign("calls",$arrLang["Calls"]);
	$smarty->assign("emails",$arrLang["Em@ils"]);
	$smarty->assign("faxes",$arrLang["Faxes"]);
	$smarty->assign("voicemails",$arrLang["Voicem@ils"]);
	$smarty->assign("calendar",$arrLang["Calendar"]);
	$smarty->assign("system",$arrLang["System"]);

	///////////////////////

	/////////////News_RSS////////////////////////////

	
    $arrParticiones = array();
    $i=0;

	
//print_r($oPalo->getAsterisk_Connections());
//print_r($oPalo->getAsterisk_Channels());////
//print_r($oPalo->getNetwork_TrafficAverage());////
//print_r($oPalo->getAsterisk_QueueWaiting());

		$arrParticiones1 = $arrSysInfo['particiones'];
		$arrServices = $oPalo->getStatusServices();
		////////////////////////SYSTEM RESOURCE/////////////////
		$SYSTEM_INFO_TITLE1 = $arrLang['System Resources'];
      $CPU_INFO_TITLE = $arrLang['CPU Info'];
      $UPTIME_TITLE = $arrLang['Uptime'];
      $CPU_USAGE_TITLE = $arrLang['CPU usage'];
      $MEMORY_USAGE_TITLE = $arrLang['Memory usage'];
      $SWAP_USAGE_TITLE = $arrLang['Swap usage'];
	    $system_resource = getSystemResource($SYSTEM_INFO_TITLE1,$CPU_INFO_TITLE,$cpu_info,$UPTIME_TITLE,$uptime,$CPU_USAGE_TITLE,$cpu_usage,$MEMORY_USAGE_TITLE,$mem_usage,$SWAP_USAGE_TITLE,$swap_usage);
		////////////////////////////////////////////////////////

// $oPalo->asteriskActivity();
    //asignar los valores del idioma
    $smarty->assign("SYSTEM_INFO_TITLE1",  $arrLang['System Resources']);
    $smarty->assign("CPU_INFO_TITLE",  $arrLang['CPU Info']);
    $smarty->assign("UPTIME_TITLE",  $arrLang['Uptime']);
    $smarty->assign("CPU_USAGE_TITLE",  $arrLang['CPU usage']);
    $smarty->assign("MEMORY_USAGE_TITLE",  $arrLang['Memory usage']);
    $smarty->assign("SWAP_USAGE_TITLE",  $arrLang['Swap usage']);
    $smarty->assign("SYSTEM_INFO_TITLE2",  $arrLang['Hard Drives']);
	//new
    $smarty->assign("News",  $arrLang['News']);
    $smarty->assign("Expand",  $arrLang['Expand']);
    $smarty->assign("Collapse",  $arrLang['Collapse']);
	 $smarty->assign("NoConnection",  $arrLang['NoConnection']);
	 $smarty->assign("Performance_Graphic",  $arrLang['Performance Graphic']);
	 $smarty->assign("Processes_Status",  $arrLang['Processes Status']);
	 $smarty->assign("Communication_Activity", $arrLang['Communication Activity']);
    $smarty->assign("module_name",  $module_name);
    $imagen_hist = getImage_Hit($module_name);
    $smarty->assign("imagen_hist", $imagen_hist);
///////////////////////////////////////////////////////////////////////////////////
	 /*$smarty->assign("system_resource", $system_resource);
    $smarty->assign("callsRows",$callsRows);
	 $smarty->assign("faxRows",$faxRows);
	 $smarty->assign("calendarEvents",$calendarEvents);
    $smarty->assign("rss2", $str2);
	 $smarty->assign("mails",$mailsPanel);
	 $smarty->assign("voiceMails",$voiceMails);
	 $smarty->assign("systemStatus",$systemStatus);*/
//////////////////////////////////////////////////////////////////////////////////
// fin de todo
	  $arrPaneles = $oPalo->getAppletsActivated($_SESSION["elastix_user"]);
	  $AppletsPanels = createApplesTD($arrPaneles, $module_name, $voiceMails, $faxRows, $mails, $callsRows, $arrServices, $arrParticiones1, $eventsRows, $systemStatus, $system_resource);
	  $smarty->assign("AppletsPanels",$AppletsPanels);
//////////////////////////////////////////////////////////////////////////////////
    $action = getParameter("save_new");
    if(isset($action))
     $app = saveApplets_Admin();
    else $app = showApplets_Admin();
    $smarty->assign("APPLET_ADMIN",$app);

    return $smarty->fetch("file:$local_templates_dir/sysinfo.tpl");
}

function buildInfoImage_Discs($arrParticiones, $module_name)
{
    Global $arrLang;
    $str = ""; $val = null;
    foreach( $arrParticiones as $key => $particion )
    {
        $val_1 = ( ereg("^([[:digit:]]{1,2}(\.[[:digit:]]{1,4})?)%$", trim($particion['uso_porcentaje']), $arrReg) )
                 ? $arrReg[1]: NULL;
        $val_2 = number_format($particion['num_bloques_total'] / 1024 / 1024, 2);
		//getImage_Disc_Usage($module_name, $val_1)
	$str = "	<div class='portlet'>
					<div class='portlet_topper'>
						<div width='100%'>
							<div class='imgapplet' width='10%' style='float:left;'>
								<img src='images/hd.png' border='0' align='absmiddle' />
							</div>
							<div class='tabapplet' width='80%' style='float:left;'>
								".$arrLang['Hard Drives']."
							</div>
							<div class='closeapplet' align='right' width='10%'>
								<a href='#' class='toggle'>
									<img id='imga2'  class='ima'  src='/images/arrow_top.gif' border='0' align='absmiddle' />
								</a>
							</div>
						</div>
					</div>
					<div class='portlet_content'>
						<div class='infoDisc'>
							<div class='type'><b>".$arrLang['Partition Name'].":</b></div>
							<div align='center' class='detail'><b>".$particion['fichero']."</b></div>
							<div class='type'>".$arrLang['Capacity'].":</div>
							<div align='center' class='detail'>".$val_2."GB</div>
							<div class='type'>".$arrLang['Usage'].":</div>
							<div align='center' class='detail'>".$val_1."%</div>
							<div class='type'>".$arrLang['Mount point']."</div>
							<div align='center' class='detail'>".$particion['punto_montaje']."</div>
						</div>
						<div class='imgDisc'>".getImage_Disc_Usage($module_name, $val_1)."</div>
					</div>
				</div>";
    }
    return $str;
}

function performanceGrafic($arrParticiones, $module_name)
{
    Global $arrLang;
	 $str = "<div class='portlet'>
					<div class='portlet_topper'>
						<div width='100%'>
							<div class='imgapplet' width='10%' style='float:left;'>
								<img src='modules/$module_name/images/graf.gif' border='0' align='absmiddle' />
							</div>
							<div class='tabapplet' width='80%' style='float:left;'>
								".$arrLang['Performance Graphic']."
							</div>
							<div class='closeapplet' align='right' width='10%'>
								<a href='#' class='toggle'>
									<img id='imga8'  class='ima' src='/images/arrow_top.gif' border='0' align='absmiddle' />
								</a>
							</div>
						</div>
					</div>
					<div class='portlet_content' align='center'>
						<div class='tabFormTable'>".getImage_Hit($module_name)."</div>
					</div>
				</div>";
    return $str;
}

function createNews($module_name){
	$str = "";
	//$url = "http://sourceforge.net/export/rss2_projnews.php?group_id=161807";
    $url = "http://www.elastix.org/component/option,com_rss/feed,RSS2.0/no_html,1/lang,en";
	$rss = fetch_rss($url);
    global $arrLang;
	$str2 = "";
	if(!empty($rss)){
		$str2 = "Channel Title: " . $rss->channel['title'] . "<p>";
		$str2 .= "<div id='myScroll1'>";
		if(is_array($rss->items) & count($rss->items)>0){
			foreach ($rss->items as $item) {
					$href = $item['link'];
					$title = $item['title'];
					$str2 .= "<div class='scrollEl' style='background-color:#cc9900'><a href=$href target='_blank'><span>$title</span></a></div>";
			}
			$str2 .= "</div>";
		}
	}
	else{
		$str2 = "<span>".$arrLang['NoConnection']."</span>";
	}
	// asignar los links para publicar
	$str = " <div class='portlet'>
					<div class='portlet_topper'>
						<div width='100%'>
							<div class='imgapplet' width='10%' style='float:left;'>
								<img src='modules/$module_name/images/RSS.png' border='0' align='absmiddle'>
							</div>
							<div class='tabapplet' width='80%' style='float:left;'>
								".$arrLang['News']."
							</div>
							<div class='closeapplet' align='right' width='10%'><a href='#' class='toggle'>
								<a href='#' class='toggle'><img id='imga7'  class='ima'  src='/images/arrow_top.gif' border='0' align='absmiddle'></a>
							</div>
						</div>
					</div>
					<div class='portlet_content'>
						<div id='wrapper'>
							<div id='vertical'>
								$str2
								<div id='controls1'>
									<div class='prev'><img src='modules/{$module_name}/images/above.png' border='0' align='absmiddle' /></div>
									<div class='next'><img src='modules/{$module_name}/images/down.png' border='0' align='absmiddle' /></div>
								</div>
							</div>
						</div>
					</div>
				</div>";
	return $str;
}

function createSystem($systemStatus,$module_name){
	global $arrLang;
	$str = " <div class='portlet'>
					<div class='portlet_topper'>
						<div width='100%'>
							<div class='imgapplet' width='10%' style='float:left;'>
								<img src='modules/$module_name/images/system.gif' border='0' align='absmiddle' />
							</div>
							<div class='tabapplet' width='80%' style='float:left;'>
								".$arrLang["System"]."
							</div>
							<div class='closeapplet' align='right' width='10%'>
								<a href='#' class='toggle'>
									<img id='imga12'  class='ima'  src='/images/arrow_top.gif' border='0' align='absmiddle' />
								</a>
							</div>
						</div>
					</div>
					<div class='portlet_content'>
						$systemStatus
					</div>
				</div>";
	return $str;
}

function process_status($module_name, $arrServices)
{
   global $arrLang;
   $str = "";
   $servicesStatus = "";
   $color = "";
   foreach($arrServices as $key=>$value){
		    if($value["status_service"]=="OK"){
			    $status = $arrLang['Running'];
			    $color = "#10ED00";
		    }
		    elseif($value["status_service"]=="Shutdown"){
			    $status = $arrLang['Not running'];
			    $color = "#0043EC";
		    }
		    else{
			    $status = $arrLang['Not installed'];
			    $color = "#0043EC";
		    }
		    $servicesStatus .= "<div class='services'>".$arrLang[$value['name_service']]."&nbsp;  ($key): &nbsp;&nbsp; "."<font color='green'><i>$status</i></font></div><div align='center' style='background-color:".$color.";' class='status' >".$value['status_service']."</div>";
	}
	
    $str = "<div class='portlet'>
					<div class='portlet_topper'>
						<div width='100%'>
							<div class='imgapplet' width='10%' style='float:left;'>
								<img src='modules/$module_name/images/semaf.gif' border='0' align='absmiddle' />
							</div>
							<div class='tabapplet' width='80%' style='float:left;'>
								".$arrLang['Processes Status']."
							</div>
							<div class='closeapplet' align='right' width='10%'>
								<a href='#' class='toggle'>
									<img id='imga3'  class='ima'  src='/images/arrow_top.gif' border='0' align='absmiddle'/>
								</a>
							</div>
						</div>
					</div>
					<div class='portlet_content'>
						<div class='tabFormTable'>$servicesStatus</div>
					</div>
				</div>";
    return $str;
}

function createCallRows($callsRows,$module_name){
   Global $arrLang;
	$str = " <div class='portlet'>
					<div class='portlet_topper'>
						<div width='100%'>
							<div class='imgapplet' width='10%' style='float:left;'>
								<img src='modules/$module_name/images/call.gif' border='0' align='absmiddle' />
							</div>
							<div class='tabapplet' width='80%' style='float:left;'>
								".$arrLang["Calls"]."
							</div>
							<div class='closeapplet' align='right' width='10%'>
								<a href='#' class='toggle'>
									<img id='imga4'  class='ima' src='/images/arrow_top.gif' border='0' align='absmiddle' />
								</a>
							</div>
						</div>
					</div>
					<div class='portlet_content'>
						$callsRows
					</div>
				</div>";
	return $str;
}

function createFaxRows($faxRows,$module_name){
   Global $arrLang;
	$str = " <div class='portlet'>
					<div class='portlet_topper'>
						<div width='100%'>
							<div class='imgapplet' width='10%' style='float:left;'>
								<img src='modules/$module_name/images/fax.gif' border='0' align='absmiddle' />
							</div>
							<div class='tabapplet' width='80%' style='float:left;'>
								".$arrLang["Faxes"]."
							</div>
							<div class='closeapplet' align='right' width='10%'>
								<a href='#' class='toggle'>
									<img id='imga5'  class='ima'  src='/images/arrow_top.gif' border='0' align='absmiddle' />
								</a>
							</div>
						</div>
					</div>
					<div class='portlet_content'>
						$faxRows
					</div>
				</div>";
	return $str;
}

function createCalendarEvents($eventsRows,$module_name){
	Global $arrLang;
	$str = "<div class='portlet'>
					<div class='portlet_topper'>
						<div width='100%'>
							<div class='imgapplet' width='10%' style='float:left;'>
								<img src='modules/$module_name/images/calendar.gif' border='0' align='absmiddle' />
							</div>
							<div class='tabapplet' width='80%' style='float:left;'>
								".$arrLang["Calendar"]."
							</div>
							<div class='closeapplet' align='right' width='10%'>
								<a href='#' class='toggle'>
									<img id='imga6'  class='ima'  src='/images/arrow_top.gif' border='0' align='absmiddle' />
								</a>
							</div>
						</div>
					</div>
					<div class='portlet_content'>
						$eventsRows
					</div>
				</div>";
	return $str;
}

function createEmails($mails,$module_name){
 	Global $arrLang;
	$str = " <div class='portlet'>
					<div class='portlet_topper'>
						<div width='100%'>
							<div class='imgapplet' width='10%' style='float:left;'>
								<img src='modules/$module_name/images/email.gif' border='0' align='absmiddle' />
							</div>
							<div class='tabapplet' width='80%' style='float:left;'>
								".$arrLang["Em@ils"]."
							</div>
							<div class='closeapplet' align='right' width='10%'>
								<a href='#' class='toggle'>
									<img id='imga10'  class='ima'  src='/images/arrow_top.gif' border='0' align='absmiddle' />
								</a>
							</div>
						</div>
					</div>
					<div class='portlet_content'>
						$mails
					</div>
				</div>";

	return $str;
}
function createVoicemails($voiceMails,$module_name){
	Global $arrLang;
	$str = " <div class='portlet'>
					<div class='portlet_topper'>
						<div width='100%'>
							<div class='imgapplet' width='10%' style='float:left;'>
								<img src='modules/$module_name/images/voice.gif' border='0' align='absmiddle' />
							</div>
							<div class='tabapplet' width='80%' style='float:left;'>
								".$arrLang["Voicem@ils"]."
							</div>
							<div class='closeapplet' align='right' width='10%'>
								<a href='#' class='toggle'>
									<img id='imga11'  class='ima'  src='/images/arrow_top.gif' border='0' align='absmiddle' />
								</a>
							</div>
						</div>
					</div>
					<div class='portlet_content'>
						$voiceMails
					</div>
				</div>";
	return $str;
}

function communicationActivity($module_name)
{
    Global $arrLang;
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
	
//     if($total == 1)	$total = $total." ".$arrLang['call'];
// 	 else	$total = $total." ".$arrLang['calls'];

	 if($internal == 1)	$internal = $internal." ".$arrLang['call'];
	 else	$internal = $internal." ".$arrLang['calls'];

	 if($external == 1)	$external = $external." ".$arrLang['call'];
	 else	$external = $external." ".$arrLang['calls'];

	 if($channel == 1)	$channel = $channel." ".$arrLang['channel'];
	 else	$channel = $channel." ".$arrLang['channels'];

//// asterisk connection
   $sip_Ext_ok  = $connections['sip']['ext']['ok'];
   $sip_Ext_nok = $connections['sip']['ext']['no_ok'];
	$total_sip_Ext = $sip_Ext_ok + $sip_Ext_nok;

	$sip_trunk_ok = $connections['sip']['trunk']['ok'];
	$sip_trunk_nok = $connections['sip']['trunk']['no_ok'];
	$total_sip_trunk = $sip_trunk_ok + $sip_trunk_nok;

	$sip_trunk_reg_ok = $connections['sip']['trunk_registry']['ok'];
	$sip_trunk_reg_nok= $connections['sip']['trunk_registry']['no_ok'];
	$total_sip_trunk_reg = $sip_trunk_reg_ok + $sip_trunk_reg_nok;

	$iax_Ext_ok  = $connections['iax']['ext']['ok'];
   $iax_Ext_nok = $connections['iax']['ext']['no_ok'];
	$total_iax_Ext = $iax_Ext_ok + $iax_Ext_nok;

	$iax_trunk_ok = $connections['iax']['trunk']['ok'];
	$iax_trunk_nok = $connections['iax']['trunk']['no_ok'];
	$total_iax_trunk = $iax_trunk_ok + $iax_trunk_nok;

	$iax_trunk_reg_ok = $connections['iax']['trunk_registry']['ok'];
	$iax_trunk_reg_nok= $connections['iax']['trunk_registry']['no_ok'];
	$total_iax_trunk_reg = $iax_trunk_reg_ok + $iax_trunk_reg_nok;

	$total_trunks_ok = $sip_trunk_ok + $iax_trunk_ok;
	$total_trunks_nok = $sip_trunk_nok + $iax_trunk_nok;
	$total_trunks_reg_ok = $sip_trunk_reg_ok + $iax_trunk_reg_ok;
	$total_trunks_reg_nok = $sip_trunk_reg_nok + $iax_trunk_reg_nok;
	$total_trunks = $total_trunks_ok + $total_trunks_nok;
	$total_trunks_reg = $total_trunks_reg_ok + $total_trunks_reg_nok;
///////traffic network
   $rx_bytes = $network['rx_bytes'];
	$tx_bytes = $network['tx_bytes'];
	$rx_packets = $network['rx_packets'];
	$tx_packets = $network['tx_packets'];

    $str = "<div class='portlet'>
					<div class='portlet_topper'>
						<div width='100%'>
							<div class='imgapplet' width='10%' style='float:left;'>
								<img src='modules/$module_name/images/communication.gif' border='0' align='absmiddle' />
							</div>
							<div class='tabapplet' width='80%' style='float:left;'>
								".$arrLang['Communication Activity']."
							</div>
							<div class='closeapplet' align='right' width='10%'>
								<a href='#' class='toggle'>
									<img id='imga9'  class='ima'  src='/images/arrow_top.gif' border='0' align='absmiddle' />
								</a>
							</div>
						</div>
					</div>
					<div class='portlet_content'>
						<div class='tabFormTable'>
							<div class='infoActivity'>
								<div class='typeActivity'>
									<b>".$arrLang['Total_calls'].": </b>
								</div>
								<div align='left' class='detailText'>
									<font color='blue'>".$arrLang['calls']."</font><b>($total)</b>
									<font color='green'>".$arrLang['internal_calls']."</font> <b>(".$internal.")</b> <font color='red'>".$arrLang['external_calls']."</font> <b>(".$external.")</b>
								</div>
								<div class='typeActivity'>
									<b>".$arrLang['total_channels'].": </b>
								</div>
								<div align='left' class='detailActivity'>".$channel."</div>
								<div class='typeActivity'>
									<b>".$arrLang['Queues_waiting'].": </b>
								</div>
								<div align='left' class='detailActivity'>".$totalQueues." ".$arrLang['Waiting']."</div>
								<div class='typeActivity'><b>".$arrLang['Extensions'].": </b></div>
								<div class='detailText'>".$arrLang['sip_extensions']." <b>($total_sip_Ext) </b>: <font color='green'>($sip_Ext_ok ".$arrLang['OK'].")</font> <font color='red'>($sip_Ext_nok ".$arrLang['NO_OK'].")</font></div>
								<div class='typeActivity'></div>
								<div class='detailText'>".$arrLang['iax_extensions']." <b>($total_iax_Ext) </b>: <font color='green'>($iax_Ext_ok ".$arrLang['OK'].")</font> <font color='red'>($iax_Ext_nok ".$arrLang['NO_OK'].")</font></div>
								<div class='typeActivity'><b>".$arrLang['Trunks'].": </b></div>
								<div class='detailText'>".$arrLang['Trunks']." <b>($total_trunks) </b>: <font color='green'>($total_trunks_ok ".$arrLang['OK'].")</font> <font color='red'>($total_trunks_nok ".$arrLang['NO_OK'].")</font></div>
								<div class='typeActivity'><b>".$arrLang['Trunks_register'].": </b></div>
								<div class='detailText'>".$arrLang['Trunks_register']." <b>($total_trunks_reg) </b>: <font color='green'>($total_trunks_reg_ok ".$arrLang['OK'].")</font> <font color='red'>($total_trunks_reg_nok ".$arrLang['NO_OK'].")</font></div>
								<div class='typeActivity'><b>".$arrLang['Network_traffic'].": </b></div>
								<div class='detailText'>".$arrLang['rx_bytes']."<b>(".$rx_bytes."kB/s)</b>  ".$arrLang['tx_bytes']."<b>(".$tx_bytes."kB/s)</b></div>
							</div>
						</div>
					</div>
				</div>";
    return $str;
}

function createApplesTD($arrPaneles, $module_name, $voiceMails, $faxRows, $mails, $callsRows, $arrServices, $arrParticiones, $eventsRows, $systemStatus, $system_resource){
	$str1 = "<td>";
	$str2 = "<td>";
	$idApplet = "";
	for($i=0; $i<count($arrPaneles); $i++){
		$idApplet = $arrPaneles[$i]; 
		if(($i%2)==0){
			$str1 .= returnAppletPannel($idApplet,$module_name, $voiceMails, $faxRows, $mails, $callsRows, $arrServices, $arrParticiones, $eventsRows, $systemStatus, $system_resource);
		}else{
			$str2 .= returnAppletPannel($idApplet,$module_name, $voiceMails, $faxRows, $mails, $callsRows, $arrServices, $arrParticiones, $eventsRows, $systemStatus, $system_resource);
		}
	}
	$str1 .= "</td>";
	$str2 .= "</td>";
	$str = $str1.$str2;
	return $str;
}

function returnAppletPannel($idApplet, $module_name, $voiceMails, $faxRows, $mails, $callsRows, $arrServices, $arrParticiones, $eventsRows, $systemStatus, $system_resource){
	$str = "";
	switch ($idApplet)
	{
		case "sys_resource":
			$str = $system_resource;
		break;
		case "news":
			$str = createNews($module_name);
		break;
		case "hard_drivers":
			$str = buildInfoImage_Discs($arrParticiones, $module_name);
		break;
		case "performance":
			$str = performanceGrafic($arrParticiones, $module_name);
		break;
		case "process_status":
			$str = process_status($module_name, $arrServices);
		break;
		case "asterisk_calls":
			$str = createCallRows($callsRows, $module_name);
		break;
		case "emails":
			$str = createEmails($mails, $module_name);
		break;
		case "faxes":
			$str = createFaxRows($faxRows, $module_name);
		break;
		case "voicemails":
			$str = createVoicemails($voiceMails, $module_name);
		break;
		case "calendar":
			$str = createCalendarEvents($eventsRows,$module_name);
		break;
		case "system":
			$str = createSystem($systemStatus,$module_name);
		break;
		default:
			$str = communicationActivity($module_name);
		break;
	} 
	return $str;
}

function getSystemResource($SYSTEM_INFO_TITLE1,$CPU_INFO_TITLE,$cpu_info,$UPTIME_TITLE,$uptime,$CPU_USAGE_TITLE,$cpu_usage,$MEMORY_USAGE_TITLE,$mem_usage,$SWAP_USAGE_TITLE,$swap_usage){
	$str = "<div class='portlet'>
					<div class='portlet_topper'>
						<div width='100%'>
							<div class='imgapplet' width='10%' style='float:left;'>
								<img src='images/memory.png' align='absmiddle' />
							</div>
							<div class='tabapplet' width='80%' style='float:left;'>
								$SYSTEM_INFO_TITLE1
							</div>
							<div class='closeapplet' align='right' width='10%'>
								<a href='#' class='toggle'>
									<img id='imga1' class='ima'  src='/images/arrow_top.gif' border='0' align='absmiddle' />
								</a>
							</div>
						</div>
					</div>
					<div class='portlet_content'>
						<div>
							<div class='type'>$CPU_INFO_TITLE: </div>
							<div class='detail'>$cpu_info	    </div>
							<div class='type'>$UPTIME_TITLE:     </div>
							<div class='detail'>$uptime	    </div>
							<div class='type'>$CPU_USAGE_TITLE:  </div>
							<div class='detail'>$cpu_usage	    </div>
							<div class='type'>$MEMORY_USAGE_TITLE:</div>
							<div class='detail'>$mem_usage          </div>
							<div class='type'>$SWAP_USAGE_TITLE:  </div>
							<div class='detail'>$swap_usage         </div>
						</div>
					</div>
				</div>";
	return $str;
}

function getImage_Hit($module_name)
{
    $arrParameters = array();
    //$oPaloGraph = new paloSantoGraph($module_name,"paloSantoSysInfo","prueba",$arrParameters,"functionCallback");
    $oPaloGraph = new paloSantoGraph($module_name,"paloSantoSysInfo","CallsMemoryCPU",$arrParameters,"functionCallback");
    return $oPaloGraph->getGraph();
}

function getImage_CPU_Usage($module_name)
{
    $arrParameters = array();
    $oPaloGraph = new paloSantoGraph($module_name,"paloSantoSysInfo","ObtenerInfo_CPU_Usage",$arrParameters);
    return $oPaloGraph->getGraph();
}

function getImage_MEM_Usage($module_name)
{
    $arrParameters = array();
    $oPaloGraph = new paloSantoGraph($module_name,"paloSantoSysInfo","ObtenerInfo_MemUsage",$arrParameters);
    return $oPaloGraph->getGraph();
}

function getImage_Swap_Usage($module_name)
{
    $arrParameters = array();
    $oPaloGraph = new paloSantoGraph($module_name,"paloSantoSysInfo","ObtenerInfo_SwapUsage",$arrParameters);
    return $oPaloGraph->getGraph();
}

function getImage_Disc_Usage($module_name, $value)
{
    $arrParameters = array($value);
    $oPaloGraph = new paloSantoGraph($module_name,"paloSantoSysInfo","ObtenerInfo_Particion",$arrParameters);
    return $oPaloGraph->getGraph();
}

//////////////////////////funciones para communicaction activity ////////////////////////////
function getImage_Asterisk_Channel_Calls($module_name,$type)
{
	 $oPalo = new paloSantoSysInfo();
    $arrParameters = array();
	 $oPalo->getAsterisk_Channels();
	 if($type=="total")
    	$oPaloGraph = new paloSantoGraph($module_name,"paloSantoSysInfo","ObtenerInfo_Asterisk_Channel_totalCalls",$arrParameters);
	 elseif($type=="internals")
		$oPaloGraph = new paloSantoGraph($module_name,"paloSantoSysInfo","ObtenerInfo_Asterisk_Channel_internalCalls",$arrParameters);
	 elseif($type=="externals")
		$oPaloGraph = new paloSantoGraph($module_name,"paloSantoSysInfo","ObtenerInfo_Asterisk_Channel_externalCalls",$arrParameters);
	 else
		$oPaloGraph = new paloSantoGraph($module_name,"paloSantoSysInfo","ObtenerInfo_Asterisk_Channel_totalChannels",$arrParameters);
	 
    return $oPaloGraph->getGraph();
}

function getImage_Asterisk_QueueWaiting($module_name)
{
	 $oPalo = new paloSantoSysInfo();
    $arrParameters = array();
	 $oPalo->getAsterisk_Channels();
    	$oPaloGraph = new paloSantoGraph($module_name,"paloSantoSysInfo","ObtenerInfo_Asterisk_Channel_totalCalls",$arrParameters);
	 
    return $oPaloGraph->getGraph();
}


/*************************************************Funciones del dashboard**************************************************/
/** Start Implementation ajax*/
function startXajaxRefresh($local_templates_dir,$module_name)
{
    $xajax = new xajax();
    $xajax->registerFunction("refreshDashboard");
    $xajax->processRequests();

    $id_xajax_content = 
    "<div id='xajax_content'> </div>
     <script type='text/javascript'> 
        xajax_refreshDashboard('$local_templates_dir','$module_name');
        /*function ejecutarAjax()
        {
            xajax_refreshDashboard('$local_templates_dir','$module_name');
            setTimeout(ejecutarAjax(),10000);
        }*/
     </script>";
    $contenido = $xajax->printJavascript("libs/xajax/");
    return $contenido.$id_xajax_content;
}

function refreshDashboard($local_templates_dir,$module_name)
{
    $respuesta = new xajaxResponse();
    $contenido = getDashboard($local_templates_dir,$module_name);
    $respuesta->addAssign("xajax_content","innerHTML",$contenido);
    return $respuesta;
}
/** End Implementation ajax*/

function conectionAsteriskCDR()
{
    include_once "libs/paloSantoConfig.class.php";
    $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
    $arrConfig = $pConfig->leer_configuracion(false);
    $dsnAsteriskCDR = $arrConfig['AMPDBENGINE']['valor']."://".
                      $arrConfig['AMPDBUSER']['valor']. ":".
                      $arrConfig['AMPDBPASS']['valor']. "@".
                      $arrConfig['AMPDBHOST']['valor']."/asteriskcdrdb";
    $pDB = new paloDB($dsnAsteriskCDR);

    if(!empty($pDB->errMsg)) 
        return false;
    else
        return $pDB;
}


////////////////////// Begin Funciones para Applets Admin /////////////////////////////////
function showApplets_Admin()
{
    global $smarty;
    global $arrLang;
    global $arrConf;
    $module_name = "dashboard"; //$_SESSION["menu"];

    $oPalo = new paloSantoSysInfo();
    $oForm = new paloForm($smarty,array());

    $arrApplets = $oPalo->getApplets_User($_SESSION["elastix_user"]);

    $smarty->assign("applets",$arrApplets);
    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("Applet", $arrLang["Applet"]);
    $smarty->assign("Activated", $arrLang["Activated"]);
    $smarty->assign("IMG", "images/list.png");

    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConf['templates_dir']))?$arrConf['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];
    $htmlForm = $oForm->fetchForm("$local_templates_dir/applet_admin.tpl",$arrLang["Applet Admin"], $_POST);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}

function saveApplets_Admin()
{
    global $smarty;
    global $arrLang;
    $arrIDs_DAU = null;
    $module_name = "dashboard"; //$_SESSION["menu"];

    if(is_array($_POST) & count($_POST)>0){
        foreach($_POST as $key => $value){
            if(substr($key,0,7) == "chkdau_")
                $arrIDs_DAU[] = substr($key,7);
        }
    }

    $oPalo = new paloSantoSysInfo();
    $ok = $oPalo->setApplets_User($arrIDs_DAU, $_SESSION["elastix_user"]);

    if(!$ok){
        $smarty->assign("mb_title", $arrLang["Validation Error"]);
        $smarty->assign("mb_message", $pprueba_applets->errMsg);
    }
    //return showApplets_Admin();
    header("Location: /index.php?menu=$module_name");
}
function getParameter($parameter)
{
    if(isset($_POST[$parameter]))
        return $_POST[$parameter];
    else if(isset($_GET[$parameter]))
        return $_GET[$parameter];
    else
        return null;
}
////////////////////// End Funciones para Applets Admin /////////////////////////////////
?>
