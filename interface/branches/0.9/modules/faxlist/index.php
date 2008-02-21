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
  $Id: index.php,v 1.1.1.1 2007/07/06 21:31:56 gcarrillo Exp $ */

function _moduleContent($smarty, $module_name)
{
    include_once "libs/paloSantoFax.class.php";
    include_once "libs/paloSantoGrid.class.php";

    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    global $arrConf;
    global $arrLang;
    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConfig['templates_dir']))?$arrConfig['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    $contenidoModulo = listFax($smarty, $module_name, $local_templates_dir);
    return $contenidoModulo;
}

function listFax($smarty, $module_name, $local_templates_dir)
{
    global $arrLang;
    $arrData = array();
    $oFax    = new paloFax();
    $arrFax  = $oFax->getFaxList();

    $end = count($arrFax);
    $arrFaxStatus = $oFax->getFaxStatus();
 
        if(isset($_GET['action']) && $_GET['action']=='install') {
         $id=--$_GET['id'];
        extract($arrFax[$id]);
        if(ereg("(NT [[:digit:]])", $_SERVER['HTTP_USER_AGENT'], $arrReg) and $arrReg[0]==='NT 6'){
         $vbs_path='%systemroot%\System32\Printing_Admin_Scripts\en-US\prnmngr.vbs';
	 $ControlSetQuery="\"HKEY_LOCAL_MACHINE\SYSTEM\ControlSet001\Control\Print\Monitors\Winprint Hylafax\" /v Driver";
         $print_driver='Xerox DocuPrint 135 EPS PS3';
        } else {
         $vbs_path='%systemroot%\system32\prnmngr.vbs';
	 $ControlSetQuery="\"HKEY_LOCAL_MACHINE\SYSTEM\ControlSet001\Control\Print\Monitors\Winprint Hylafax\" /v Driver";
         $print_driver='Apple LaserWriter 12/640 PS';
	}
         $name=ereg_replace ('(^FAX |^Fax |^fax )','',$name);
	 $HylafaX='@reg add "HKEY_LOCAL_MACHINE\SYSTEM\ControlSet001\Control\Print\Monitors\Winprint Hylafax\Ports\HFAXttyIAX'.$dev_id.':" /f /v';
	 $tmpArrayp[]="@echo OFF";
	 $tmpArrayp[]="@cls";
	 $tmpArrayp[]="@SET TRYITHYLAFAX=1";
	 $tmpArrayp[]="@SET TRYITHYLAMSG=don`t";
	 $tmpArrayp[]="@SET AGAIN=.";
	 $tmpArrayp[]=":Start";
	 $tmpArrayp[]="@reg query ".$ControlSetQuery." >nul";
	 $tmpArrayp[]="@IF NOT \"%ERRORLEVEL%\" == \"1\" GOTO EndIf";
	 $tmpArrayp[]="@cd \"%ProgramFiles%\Internet Explorer\"";
	 $tmpArrayp[]="echo.";
	 $tmpArrayp[]="IF \"%TRYITHYLAFAX%\" == \"111\" GOTO END";
	 $tmpArrayp[]="@echo You %TRYITHYLAMSG% have HylaFax client on your computer!";
	 $tmpArrayp[]="@echo This script try get it now%AGAIN%";
	 $tmpArrayp[]="echo.";
	 $tmpArrayp[]="@echo Wait a while and install it manually and then";
	 $tmpArrayp[]="@iexplore http://prdownloads.sourceforge.net/winprinthylafax/WinprintHylaFAX-1.2.8.exe?download";
	 $tmpArrayp[]="IF NOT \"%TRYITHYLAFAX%\" == \"111\" @PAUSE";
	 $tmpArrayp[]="SET TRYITHYLAFAX=%TRYITHYLAFAX%1";
	 $tmpArrayp[]="SET TRYITHYLAMSG=still don`t";
	 $tmpArrayp[]="SET AGAIN= again.";
	 $tmpArrayp[]="echo.";
	 $tmpArrayp[]="echo.";
	 $tmpArrayp[]="GOTO Start";
	 $tmpArrayp[]=":EndIf";
	 $tmpArrayp[]="@echo HylaFax is instaled try to install printer!";
	 $tmpArrayp[]="$HylafaX \"Description\" /d \"WinPrint Hylafax Port\" > nul";
         $tmpArrayp[]="$HylafaX \"Server\" /d \"".$_SERVER['HTTP_HOST']."\" > nul";
         $tmpArrayp[]="$HylafaX \"Username\" /d \"".$extension."\" > nul";
         $tmpArrayp[]="$HylafaX \"Password\" /d \"".$secret."\" > nul";
         $tmpArrayp[]="$HylafaX \"DefaultEmail\" /d \"".$email."\" > nul";
         $tmpArrayp[]="$HylafaX \"Modem\" /d \"ttyIAX".$dev_id."\" > nul";
         $tmpArrayp[]="$HylafaX \"AddressBookPath\" /d \"%temp%\\\\\" > nul";
         $tmpArrayp[]="$HylafaX \"NotificationType\" /d \"Failure and Success\" > nul";
         $tmpArrayp[]="$HylafaX \"PageSize\" /d \"A4\" > nul";
         $tmpArrayp[]="$HylafaX \"IgnorePassiveIP\" /d \"0\" > nul";
         $tmpArrayp[]="$HylafaX \"AddressBookType\" /d \"Two Text Files\" > nul";
         $tmpArrayp[]="$HylafaX \"Resolution\" /d \"Fine\" > nul";
         $tmpArrayp[]="@CScript ".$vbs_path." -a -p \"FAX ".$name." (".$extension.")\" -m \"".$print_driver."\" -r HFAXttyIAX".$dev_id.":";
         $tmpArrayp[]='@if not exist %temp%\names.txt type nul > %temp%\names.txt';
         $tmpArrayp[]='@if not exist %temp%\numbers.txt type nul > %temp%\numbers.txt';
         $tmpArrayp[]="GOTO FINISH";
         $tmpArrayp[]=":END";
         $tmpArrayp[]="echo It was last chance to try install it auto magicaly :(";
         $tmpArrayp[]=":FINISH";
         $tmpArrayp[]="@PAUSE";
         $tmpArrayp[]="<? header(\"Cache-Control: private\"); ?>";
         $tmpArrayp[]="<? header(\"Pragma: cache\"); ?>";
         $tmpArrayp[]="<? header('Content-Type: application/octec-stream'); ?>";
         $tmpArrayp[]="<? header('Content-disposition: inline; filename=\"FAX ".$name." (".$extension.").bat\"') ?>";
         $tmpArrayp[]="<? header('Content-Type: application/force-download'); ?>";

         $fh=fopen('/var/www/html/modules/faxlist/batchfile.php',"w");
         foreach($tmpArrayp as $fax) fwrite($fh,$fax."\r\n");
         fclose($fh);
         header("Location: modules/faxlist/batchfile.php");
       }

    foreach($arrFax as $fax) {
        $arrTmp    = array();
        $arrTmp[0] = "&nbsp;<a href='?menu=faxnew&action=view&id=" . $fax['id'] . "'>" . $fax['name'] . "</a>";
	$arrTmp[1] = "&nbsp;<a href='?menu=faxlist&action=install&id=" . $fax['id']."' title='You can add this fax to your virtual printers.'>" . $fax['extension']. "</a>";
        $arrTmp[2] = $fax['secret'];
        $arrTmp[3] = $fax['email'];
        $arrTmp[4] = $fax['clid_name'] . "&nbsp;";
        $arrTmp[5] = $fax['clid_number'] . "&nbsp;";
        $arrTmp[6] = $arrFaxStatus['ttyIAX' . $fax['dev_id']].' on ttyIAX' . $fax['dev_id'];
        $arrData[] = $arrTmp;
    }
    
    $arrGrid = array("title"    => $arrLang["Virtual Fax List"],
                     "icon"     => "images/kfaxview.png",
                     "width"    => "99%",
                     "start"    => ($end==0) ? 0 : 1,
                     "end"      => $end,
                     "total"    => $end,
                     "columns"  => array(0 => array("name"      => $arrLang["Virtual Fax Name"],
                                                    "property1" => ""),
                                         1 => array("name"      => $arrLang["Fax Extension"], 
                                                    "property1" => ""),
                                         2 => array("name"      => $arrLang["Secret"],
                                                    "property1" => ""),
                                         3 => array("name"      => $arrLang["Destination Email"],
                                                    "property1" => ""),
                                         4 => array("name"      => $arrLang["Caller ID Name"],
                                                    "property1" => ""),
                                         5 => array("name"      => $arrLang["Caller ID Number"],
                                                    "property1" => ""),
                                         6 => array("name"      => $arrLang["Status"],
                                                    "property1" => "")
                                        )
                    );
    
    $oGrid = new paloSantoGrid($smarty);
    return $oGrid->fetchGrid($arrGrid, $arrData,$arrLang);
}
?>
