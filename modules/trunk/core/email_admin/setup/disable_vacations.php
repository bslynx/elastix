#!/usr/bin/php
<?php
  // script para eliminar el script de vacaciones si ya se ha pasado el periodo de vacaciones
    $module_name = "vacations";
    $module_name2  = "antispam";
    include_once "/var/www/html/libs/misc.lib.php";
    include_once "/var/www/html/libs/paloSantoDB.class.php";
    include_once "/var/www/html/configs/email.conf.php";
    include_once "/var/www/html/modules/$module_name/libs/paloSantoVacations.class.php";
    include_once "/var/www/html/modules/$module_name2/libs/paloSantoAntispam.class.php";

    $pDB = new paloDB("sqlite3:////var/www/db/email.db");
    $pVacations  = new paloSantoVacations($pDB);
    $objAntispam = new paloSantoAntispam("", "", "", "");
    // obteniendo todas las cuentas de correos con el script de vacaciones activado.
    $emails = $pVacations->getEmailsVacationON();
    $timestamp1 = mktime(0,0,0,date("m"),date("d"),date("Y"));

    load_language("/var/www/html/");
    global $arrLang;
    
    if(count($emails)>0){
	foreach($emails as $key => $value){
	    $id       = $value['id'];
	    $email    = $value['account'];
	    $subject  = $value['subject'];
	    $body     = $value['body'];
	    $ini_date = $value['ini_date'];
	    $end_date = $value['end_date'];
	    $day_ini   = date("d",strtotime($ini_date));
	    $month_ini = date("m",strtotime($ini_date));
	    $year_ini  = date("Y",strtotime($ini_date));
	    $day_end   = date("d",strtotime($end_date));
	    $month_end = date("m",strtotime($end_date));
	    $year_end  = date("Y",strtotime($end_date));
	    $timestamp0 = mktime(0,0,0,$month_ini,$day_ini,$year_ini);
	    $timestamp2 = mktime(0,0,0,$month_end,$day_end,$year_end);
	    $spamCapture = false;
	    $seconds0 = $timestamp1 - $timestamp0;

	    //resto a una fecha la otra
	    $seconds = $timestamp1 - $timestamp2;
	    $dias = $seconds / (60 * 60 * 24);
	    $dias = abs($dias);
	    $dias = floor($dias);
	    $scripts = $objAntispam->existScriptSieve($email, "scriptTest.sieve");

	    // verifica que usuarios no tienen activado el script de vacaciones
	    if($seconds0 >= 0 && $seconds <= 0){// si la fecha inicial >= fecha actual entonces se debe subir el script
		$spamCapture0 = false;
		if(preg_match("/scriptTest.sieve/",$scripts['actived']) && $scripts['status']) // si CapturaSpam=? y Vacations=ON
		    $spamCapture0 = true;
		$status = $pVacations->uploadVacationScript($email, $subject, $body, $objAntispam, $spamCapture0, $arrLang);
	    }

	    // elimina el script de vacaciones si el tiempo de sus vacaciones ya expiro
	    if($scripts['actived'] != ""){
		if(preg_match("/vacations.sieve/",$scripts['actived']) && $scripts['status']) // si CapturaSpam=? y Vacations=ON
		    $spamCapture = true;
		if($seconds > 0){// si es positivo entonces la fecha actual es mayor que la fecha final del script
		    $res = $pVacations->updateMessageByUser($email, $subject, $body, $ini_date, $end_date, "no");
		    if($res)
			$status = $pVacations->deleteVacationScript($email, $objAntispam, $spamCapture, $arrLang);
		    if(!$status)
			echo $pVacations->errMsg;
		}
	    }
	}
    }
    

?>