<?php
/*
   Copyright 2002, 2005 Sean Proctor, Nathan Poiro

   This file is part of PHP-Calendar.

   PHP-Calendar is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 2 of the License, or
   (at your option) any later version.

   PHP-Calendar is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with PHP-Calendar; if not, write to the Free Software
   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

if ( !defined('IN_PHPC') ) {
       die("Hacking attempt");
}

function event_submit()
{
    global $calendar_name, $day, $month, $year, $db, $vars, $config,
           $phpc_script, $view_events, $smarty;

    $sDirectorioBase = '/tmp';
    $dir_outgoing = "/var/spool/asterisk/outgoing";

        /* Validate input */
    if(isset($vars['id'])) {
        $id = $vars['id'];
        $modify = 1;
    } else {
        $modify = 0;
    }

    if(isset($vars['description'])) {
        $description = $vars['description'];
    } else {
        $description = '';
    }

    if(isset($vars['subject'])) {
        $subject = $vars['subject'];
    } else {
        $subject = '';
    }

    if(empty($vars['day']))
    {
        require_once "event_form.php";
        $smarty->assign("mb_title", $view_events["Validation Error"]);
        $smarty->assign("mb_message", $view_events['No day was given']);
        return event_form();
    }

    if(empty($vars['month']))
    {
        require_once "event_form.php";
        $smarty->assign("mb_title", $view_events["Validation Error"]);
        $smarty->assign("mb_message", $view_events['No month was given']);
        return event_form();
    }

    if(empty($vars['year']))
    {
        require_once "event_form.php";
        $smarty->assign("mb_title", $view_events["Validation Error"]);
        $smarty->assign("mb_message", $view_events['No year was given']);
        return event_form();
    }

    if(isset($vars['hour'])) {
        $hour = $vars['hour'];
    } else {
        require_once "event_form.php";
        $smarty->assign("mb_title", $view_events["Validation Error"]);
        $smarty->assign("mb_message", $view_events['No hour was given']);
        return event_form();
    }

    if(!$config['hours_24'])
    {
        if (array_key_exists('pm', $vars) && $vars['pm']) {
            if ($hour < 12) {
                    $hour += 12;
            }
        } elseif($hour == 12) {
                $hour = 0;
        }
    }

    if(array_key_exists('minute', $vars)) {
        $minute = $vars['minute'];
    } else {
        require_once "event_form.php";
        $smarty->assign("mb_title", $view_events["Validation Error"]);
        $smarty->assign("mb_message", $view_events['No minute was given']);
        return event_form();
    }

    if(isset($vars['typeofevent']))
        $typeofevent = $vars['typeofevent'];
    else {
        require_once "event_form.php";
        $smarty->assign("mb_title", $view_events["Validation Error"]);
        $smarty->assign("mb_message", $view_events['No type of event was given']);
        return event_form();
    }

    if(isset($vars['endday']))
        $end_day = $vars['endday'];
    else {
        require_once "event_form.php";
        $smarty->assign("mb_title", $view_events["Validation Error"]);
        $smarty->assign("mb_message", $view_events['No end day was given']);
        return event_form();
    }

    if(isset($vars['endmonth']))
        $end_month = $vars['endmonth'];
    else {
        require_once "event_form.php";
        $smarty->assign("mb_title", $view_events["Validation Error"]);
        $smarty->assign("mb_message", $view_events['No end month was given']);
        return event_form();
    }

    if(isset($vars['endyear']))
        $end_year = $vars['endyear'];
    else {
        require_once "event_form.php";
        $smarty->assign("mb_title", $view_events["Validation Error"]);
        $smarty->assign("mb_message", $view_events['No end year was given']);
        return event_form();
    }

    if(strlen($subject) > $config['subject_max']) {
        require_once "event_form.php";
        $smarty->assign("mb_title", $view_events["Validation Error"]);
        $strErrorMsg = $view_events['Your subject was too long'];
        $strErrorMsg .= ". {$config['subject_max']} ".$view_events['characters max'];
        $strErrorMsg .= ".";
        $smarty->assign("mb_message", $strErrorMsg);
        return event_form();
    }

    if(isset($vars['asterisk_call']))
        $asterisk_call = 'on';
    else $asterisk_call = 'off';

    if(isset($vars['recording']))
        $recording = $vars['recording'];
    else $recording = '';

    $user = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
    $uid = Obtain_UID_From_User($user);

    $startstamp = mktime($hour, $minute, 0, $month, $day, $year);
    $date_startstamp = "'" . date('Y-m-d H:i:s', $startstamp) . "'";
    $endstamp = mktime(0, 0, 0, $end_month, $end_day, $end_year);
    $date_endstamp   = "'" . date('Y-m-d', $endstamp) . "'";

    if($endstamp < mktime(0, 0, 0, $month, $day, $year)) {
        require_once "event_form.php";
        $smarty->assign("mb_title", $view_events["Validation Error"]);
        $smarty->assign("mb_message", $view_events['The start of the event cannot be after the end of the event']);
        return event_form();
    }

    $startdate = "strftime('%Y-%m-%d', $date_startstamp)";
    $starttime = "strftime('%Y-%m-%d %H:%M:%S', $date_startstamp)";

    $enddate = "strftime('%Y-%m-%d', $date_endstamp)";

    $table = SQL_PREFIX . 'events';

    if($modify) {
        //Eliminar las llamadas anteriores
        if($modify) {
            $event = get_event_by_id($id);
            if($event['asterisk_call']=='on')
            {
                $start_stamp = strtotime($event['startdate']);
                $end_stamp   = strtotime($event['enddate']);
                if($event['eventtype']==1 || $event['eventtype']==5)
                {
                    if($event['eventtype']==1)
                    {
                        $segundos = 86400;
                        $num_dias = (($end_stamp-$start_stamp)/$segundos)+1;//Sumo 1 para incluir el ultimo dia
                    }else if($event['eventtype']==5)
                    {
                        $segundos = 604800;
                        $num_dias = (($end_stamp-$start_stamp)/$segundos)+1;//Sumo 1 para incluir la ultima semana
                        $num_dias = (int)$num_dias;
                    }

                    for($i=0; $i<$num_dias; $i++)
                    {
                        $filename = $dir_outgoing."/event_{$id}_{$i}.call";
                        if(file_exists($filename))
                            unlink($filename);
                    }
                }else if($event['eventtype'] ==6)
                {
                    $i=0;
                    while($start_stamp <= $end_stamp)
                    {
                        $filename = $dir_outgoing."/event_{$id}_{$i}.call";
                        $start_stamp = strtotime("+1 months", $start_stamp);
                        if(file_exists($filename))
                            unlink($filename);
                        $i++;
                    }
                }
            }
        }

        $query = "UPDATE $table\n"
            ."SET startdate=$startdate,\n"
            ."enddate=$enddate,\n"
            ."starttime=$starttime,\n"
            ."subject='$subject',\n"
            ."description='$description',\n"
            ."eventtype='$typeofevent',\n"
            ."asterisk_call='$asterisk_call',\n"
            ."recording='$recording'\n"
            ."WHERE id='$id'";
    } else {
        $query = "INSERT INTO $table\n"
            ."(uid, startdate, enddate, starttime,"
            ." subject, description, eventtype, asterisk_call, recording"
            .")\n"
            ."VALUES ('$uid', $startdate, $enddate,"
            ."$starttime, '$subject',"
            ."'$description', '$typeofevent', '$asterisk_call', '$recording'"
            .")";
    }

    $result = $db->genQuery($query);
    if(!$result)
    {
        require_once "event_form.php";
        $smarty->assign("mb_title", $view_events["Validation Error"]);
        $strErrorMsg = $view_events['Error processing event']."<br />".$db->errMsg;
        $smarty->assign("mb_message", $strErrorMsg);
        return event_form();
    }
    else{
        if(!$modify) {
            $query = "SELECT last_Insert_rowid() AS id_events;";
            $result = $db->getFirstRowQuery($query, true);
            if(!$result)
            {
                require_once "event_form.php";
                $smarty->assign("mb_title", $view_events["Validation Error"]);
                $strErrorMsg = $view_events['Error processing event']."<br />".$db->errMsg;
                $smarty->assign("mb_message", $strErrorMsg);
                return event_form();
            }
            else $id = $result['id_events'];
        }
    }

    //$affected = $db->Affected_Rows($result);
    //if($affected < 1) return tag('div', $view_events['No changes were made.']);

    //AÑADIDO PARA ELASTIX
    //else
    {
        $asterisk_call = "off";
        if(isset($vars['asterisk_call']))
            $asterisk_call = $vars['asterisk_call'];

        if($asterisk_call=="on")
        {
            $iRetries = 2;
            //Obtener datos sobre quien esta usando el sistema
            //Channel, description, extension
            $result = Obtain_Protocol_Current_User();

            $sContenido = "";
            if($result!=FALSE)
            {
                $sContenido =   //"Channel: $sTrunk/$tuplaTelf[phone]\n".
                                "Channel: {$result['dial']}\n".
                                "CallerID: Calendar Event <{$result['id']}>\n".
                                "MaxRetries: $iRetries\n".
                                "RetryTime: 60\n".
                                "WaitTime: 30\n".
                                "Context: calendar-event\n".
                                "Extension: *7899\n".
                                "Priority: 1\n".
                                "Set: FILE_CALL=custom/$recording\n".
                                "Set: ID_EVENT_CALL=$id\n";
            }

            $endstamp = mktime($hour, $minute, 0, $end_month, $end_day, $end_year);

            $iStartTimestamp = $startstamp;

            if($typeofevent ==1 || $typeofevent==5)
            {
                if($typeofevent==1)
                {
                    $segundos = 86400;
                    $num_dias = (($endstamp-$startstamp)/$segundos)+1;//Sumo 1 para incluir el ultimo dia
                }else if($typeofevent==5)
                {
                    $segundos = 604800;
                    $num_dias = (($endstamp-$startstamp)/$segundos)+1;//Sumo 1 para incluir la ultima semana
                    $num_dias = (int)$num_dias;
                }

                for($i=0; $i<$num_dias; $i++)
                {
                    $filename = "event_{$id}_{$i}.call";
                    $sFechaInicio = date('Y-m-d H:i:s', $iStartTimestamp);
                    $iStartTimestamp += $segundos;
                    $hArchivo = fopen("$sDirectorioBase/$filename", 'w');
                    if (!$hArchivo) {
                        $bExito = FALSE;
                        $this->errMsg = "No se puede crear archivo de llamada $filename";
                        break;
                    } else {
                        fwrite($hArchivo, $sContenido);
                        fclose($hArchivo);
                        system("touch -d '$sFechaInicio' $sDirectorioBase/$filename");
                        system("mv $sDirectorioBase/$filename $dir_outgoing/");
                    }
                }
            }else if($typeofevent==6)
            {
                $i=0;
                while($iStartTimestamp <= $endstamp)
                {
                    $filename = "event_{$id}_{$i}.call";
                    $sFechaInicio = date('Y-m-d H:i:s', $iStartTimestamp);

                    $hArchivo = fopen("$sDirectorioBase/$filename", 'w');
                    if (!$hArchivo) {
                        $bExito = FALSE;
                        $this->errMsg = "No se puede crear archivo de llamada $filename";
                        break;
                    } else {
                        fwrite($hArchivo, $sContenido);
                        fclose($hArchivo);
                        system("touch -d '$sFechaInicio' $sDirectorioBase/$filename");
                        system("mv $sDirectorioBase/$filename $dir_outgoing/ ");
                    }

                    $iStartTimestamp = strtotime("+1 months", $iStartTimestamp);
                    $i++;
                }
            }
        }
    }
    ////////////ELASTIX

    session_write_close();

    redirect("$phpc_script?action=display&id=$id");
    $affected = isset($affected)?$affected:'';
    return tag('div', attributes('class="box"'), $view_events['Date updated'].": $affected");
}

function Obtain_Protocol_Current_User()
{
    require_once "libs/paloSantoACL.class.php";
    require_once "libs/paloSantoConfig.class.php";

    global $arrConf;

    $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
    $arrConfig = $pConfig->leer_configuracion(false);

    $dsnAsterisk = $arrConfig['AMPDBENGINE']['valor']."://".
                   $arrConfig['AMPDBUSER']['valor']. ":".
                   $arrConfig['AMPDBPASS']['valor']. "@".
                   $arrConfig['AMPDBHOST']['valor']."/asterisk";

    $pDB_acl = new paloDB($arrConf['elastix_dsn']['acl']);

    $pACL = new paloACL($pDB_acl);
    $id_user = $pACL->getIdUser($_SESSION["elastix_user"]);
    if($id_user != FALSE)
    {
        $user = $pACL->getUsers($id_user);
        if($user != FALSE)
        {
            $extension = $user[0][3];
            if($extension != "")
            {
                $pDB = new paloDB($dsnAsterisk);

                $query = "SELECT dial, description, id FROM devices WHERE id=$extension";
                $result = $pDB->getFirstRowQuery($query, TRUE);
                if($result != FALSE)
                    return $result;
                else return FALSE;
            }else return FALSE;
        }else return FALSE;
    }else return FALSE;
}
?>