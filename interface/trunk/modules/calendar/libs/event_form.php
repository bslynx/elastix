<?php
/*
   Copyright 2002 - 2005 Sean Proctor, Nathan Poiro

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

function event_form()
{
    global  $vars, $day, $month, $year, $db, $config, $phpc_script,
            $month_names, $event_types, $view_events, $general,
            $min_year, $max_year;

    $asterisk_call = '';
    if(isset($vars['id'])) {
        // modifying
        $id = $vars['id'];

        $title = $view_events['Editing Event'].sprintf(' #%d', $id);

        $row = get_event_by_id($id);

        $subject = htmlspecialchars(stripslashes($row['subject']));
        $desc = htmlspecialchars(stripslashes($row['description']));

        $year = $row['year'];
        $month = $row['month'];
        $day = $row['day'];

        $hour = date('H', strtotime($row['starttime']));
        $minute = date('i', strtotime($row['starttime']));

        $end_year = $row['end_year'];
        $end_month = $row['end_month'];
        $end_day = $row['end_day'];

        if(!$config['hours_24']) {
            if($hour > 12) {
                $pm = true;
                $hour = $hour - 12;
            } elseif($hour == 12) {
                                $pm = true;
                        } else {
                                $pm = false;
                        }
                }

        $typeofevent = $row['eventtype'];
        $asterisk_call = $row['asterisk_call']=='on'?'checked':'';
        $recording = $row['recording'];

    } else {
        // case "add":
        $title = $view_events['Adding event to calendar'];

        $subject    = isset($vars['subject'])?$vars['subject']:'';
        $desc       = isset($vars['description'])?$vars['description']:'';

        if($day == date('j') && $month == date('n') && $year == date('Y')) {
            if($config['hours_24']) {
                $hour = isset($vars['hour'])?$vars['hour']:date('G');
            } else {
                $hour = isset($vars['hour'])?$vars['hour']:date('g');
                $pm = isset($vars['pm'])?$vars['pm']:(date('a') == 'pm')?true:false;
            }
        } else {
            $hour = isset($vars['hour'])?$vars['hour']:6;
            $pm = isset($vars['pm'])?$vars['pm']:true;
        }

        $minute = isset($vars['minute'])?$vars['minute']:0;
        $end_day = $day;
        $end_month = $month;
        $end_year = $year;
        $typeofevent = isset($vars['typeofevent'])?$vars['typeofevent']:1;
        $asterisk_call = (isset($vars['asterisk_call']) && $vars['asterisk_call']=='on')?'checked':'';
        $recording = isset($vars['recording'])?$vars['recording']:'';
    }

    if($config['hours_24']) {
        $hour_sequence = create_sequence(0, 23);
    } else {
        $hour_sequence = create_sequence(1, 12);
    }
    $minute_sequence = create_sequence(0, 59, 5, 'minute_pad');
    $year_sequence = create_sequence($min_year, $max_year);

    $html_time = tag('td',
            create_select('hour', $hour_sequence, $hour),
            tag('b', ':'),
            create_select('minute', $minute_sequence, $minute));

    if(!$config['hours_24']) {
        if($pm) {
            $value = 1;
        } else {
            $value = 0;
        }
        $html_time->add(create_select('pm', array($general['AM'], $general['PM']),
                                        $value));
    }

    if(isset($id)) $input = create_hidden('id', $id);
    else $input = '';

    $attributes = attributes('class="phpc-main"');

    $arrRecordings = Obtain_Recordings();

    $day_of_month_sequence = get_day_of_month_sequence($month, $year);
    return tag('form', attributes("action=\"$phpc_script\""),
            tag('table', $attributes,
                tag('caption', $title),
                tag('tfoot',
                    tag('tr',
                        tag('td', attributes( 'colspan="2"'),
                            $input,
                            create_submit($view_events["Submit Event"]),
                            create_hidden('action', 'event_submit')))),
                tag('tbody',
                    tag('tr',
                        tag('th', $view_events['Subject'].' ('.$config['subject_max'].' '.$view_events['chars max'].')'),
                        tag('td', tag('input', attributes('type="text"', "size=\"{$config['subject_max']}\"", "maxlength=\"{$config['subject_max']}\"", 'name="subject"', "value=\"$subject\"")))),
                    tag('tr',
                        tag('th', $view_events['Event type']),
                        tag('td',
                            create_select('typeofevent',
                                $event_types, $typeofevent))),
                    tag('tr',
                        tag('th', $view_events['Date of event']),
                        tag('td',
                            create_select('day', $day_of_month_sequence, $day),
                            create_select('month', $month_names, $month),
                            create_select('year', $year_sequence, $year))),
                    tag('tr',
                        tag('th', $view_events['Date multiple day event ends']),
                        tag('td',
                            create_select('endday', $day_of_month_sequence, $end_day),
                            create_select('endmonth', $month_names, $end_month),
                            create_select('endyear', $year_sequence, $end_year))),
                    tag('tr',
                        tag('th',  $view_events['Time']),
                        $html_time),
                    tag('tr',
                        tag('th',  $view_events['Description']),
                        tag('td',
                            tag('textarea', attributes('rows="5"',
                                    'cols="50"',
                                    'name="description"'),
                                $desc))),
                    tag('tr',
                        tag('th', $view_events['Asterisk Call Me']),
                        tag('td',
                            tag('input', attributes('type="checkbox"',
                                                    'name="asterisk_call"',
                                                    $asterisk_call
                                        )
                            )
                        )
                    ),
                    tag('tr',
                        tag('th', $view_events['Recordings']),
                        tag('td',
                            create_select('recording', $arrRecordings, $recording),
                            "<label style='font-size:8pt;'>".$view_events['To create new recordings click']." <a href='?menu=pbxconfig&display=recordings'> ".$view_events['Here']."</a></label>"
                        )
                    )
                )));
}

function Obtain_Recordings()
{
    $archivos = array();

    $path = "/var/lib/asterisk/sounds/custom";
    if ($handle = opendir($path)) {
        while (false !== ($dir = readdir($handle))) {
            if (ereg("(.*)\.[gsm$|wav$]", $dir, $regs)) {
                $archivos[$regs[1]] = $regs[1];
            }
        }
    }
    return $archivos;
}
?>