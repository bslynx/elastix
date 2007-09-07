<?php
	require_once "config.php";     

        function obtener_nombre($ruta){
            if(strpos($ruta,"/")===false)
                return $ruta;
            else
                return substr($ruta,(strpos($ruta,"/") + 1));
        }	

	function faxes_log ($text, $echo = false) {
                global $db_object;
		  $db_object->query ("INSERT INTO SysLog (logtext,logdate) VALUES ('$text',datetime('now','localtime'))");
		if ($echo) echo "$text\n";
	}

        function fax_info_insert ($tiff_file,$modemdev,$commID,$errormsg,$company_name,$company_fax) {
		global $db_object;
		$id_destiny=obtener_id_destiny($modemdev);
		$db_object->query ("INSERT INTO info_fax_recvq (pdf_file,modemdev,commID,errormsg,company_name,company_fax,fax_destiny_id,date) 
                                    VALUES ('$tiff_file','$modemdev','$commID','$errormsg','$company_name','$company_fax',$id_destiny,datetime('now','localtime'))");
	} 

	function obtener_id_destiny($modemdev)
	{
		global $db_object;
		$sql= "select id from fax where ttyIAX=?";
		$recordset =& $db_object->query($sql, array($modemdev));
		while ($tupla = $recordset->fetchRow(DB_FETCHMODE_OBJECT)) 
        		$id = $tupla->id;
		return $id;
	}
    
        function obtener_mail_destiny($modemdev)
	{
		global $db_object;
		$sql= "select email from fax where ttyIAX=?";
		$recordset =& $db_object->query($sql, array($modemdev));
		while ($tupla = $recordset->fetchRow(DB_FETCHMODE_OBJECT)) 
        		$id = $tupla->email;
		return $id;
	}

	function clean_faxnum ($fnum) {
		if (get_magic_quotes_gpc()) {
			$fnum = stripslashes($fnum);
		}
	
		$fnum = preg_replace ("/\W/", "", $fnum); // strip non alpha num
		return $fnum;
	}
	
	// return faxinfo from tiff file, return false on error
	function faxinfo ($path, &$sender, &$pages, &$date, &$format) {
		global $FAXINFO, $RESERVED_FAX_NUM;
		
		//  /\s*(\w*): (.*)/
		exec ("$FAXINFO -n $path", $array);
		
		$values = array ();
		
		foreach ($array as $key=>$val) {
			list ($left, $right) = explode (": ", $val);
			$values[trim ($left)] = trim ($right);
		}
		
		if (isset($values['Sender'])) {
			$sender = strtolower (clean_faxnum ($values['Sender']));
		}

		if (isset($values['Pages'])) {
			$pages = $values['Pages'];
		} 
		
		if (isset($values['Received'])) {
			$date = $values['Received'];
 		}
		
		if (isset($values['Page'])) {
			$format = $values['Page'];
 		}
		
		if (preg_match ("/unknown/i", $sender) or preg_match ("/unspecified/i", $sender) or !$sender) {
			$sender = $RESERVED_FAX_NUM;
			faxes_log ("faxinfo> XDEBUG CHECK sender '$sender' in faxfile '$path'");
		}
		
		if ($sender && $pages && $date) return true;
		return false;
	}
	
	// enviar_mail_adjunto ()
	function enviar_mail_adjunto(
                $destinatario="test@prueba.com",
                $titulo="Prueba de envio de adjunto",
                $contenido="Hola a todos",
                $remite="bmacias@palosanto.com",
                $remitente="Bruno Macias",
                $archivo="/tmp/fax.pdf",
                $archivo_name="fax.pdf"
            )
        {
            $un_enter="\r\n";
            $dos_enter="\r\n\r\n";
            
            $mensaje="<html><head></head><body bgcolor=\"#0000ff\">";
            $mensaje .="<font face=\"Arial\" size=6>$contenido</font>";
            $mensaje .="</body></html>";   
            
            $separador = "_separador_de_trozos_".md5 (uniqid (rand())); 
            
            $cabecera  = "Date: ".date("l j F Y, G:i").$un_enter; 
            $cabecera .= "MIME-Version: 1.0".$un_enter; 
            $cabecera .= "From: ".$remitente."<".$remite.">".$un_enter;
            $cabecera .= "Return-path: ". $remite.$un_enter;
            $cabecera .= "Reply-To: ".$remite.$un_enter;
            $cabecera .= "X-Mailer: PHP/". phpversion().$un_enter;
            $cabecera .= "Content-Type: multipart/mixed;".$un_enter; 
            $cabecera .= " boundary=$separador".$dos_enter; 
            
            // Parte primera -Mensaje en formato HTML 
            $texto ="--$separador".$un_enter; 
            $texto .="Content-Type: text/html; charset=\"ISO-8859-1\"".$un_enter; 
            $texto .="Content-Transfer-Encoding: 7bit".$dos_enter; 
            $texto .= $mensaje;
            $adj1 = $un_enter."--$separador".$un_enter; 
            
            // Parte segunda -Fichero adjunto nº 1 
            $adj1 .="Content-Type: application/pdf; name=\"$archivo\"".$un_enter;  
            $adj1 .="Content-Disposition: attachment; filename=\"$archivo_name\"".$un_enter;
            $adj1 .="Content-Transfer-Encoding: base64".$dos_enter; 
            
            # lectura  del fichero adjunto  
            $fp = fopen($archivo, "r"); 
            $buff = fread($fp, filesize($archivo)); 
            fclose($fp); 
            # codificación del fichero adjunto  
            $adj1 .=chunk_split(base64_encode($buff)); 
            
            $mensaje=$texto.$adj1; 
            // envio del mensaje 
            if(mail($destinatario, $titulo, $mensaje,$cabecera)){
                faxes_log ("enviar_mail_adjunto> SE envio correctamenete el mail ".$titulo);
            }
            else faxes_log ("enviar_mail_adjunto> Error al enviar el mail ".$titulo);
        }
	
	// -- convert tiff to pdf and check for corruption
	function tiff2pdf ($tiff_file, $pdf) {
		global $CONVERT, $TIFFPS, $GSR;

		// start timing how long it takes to convert faxes
		$time_start = microtime(true);

		chmod ($tiff_file, 0666);
		
		// run tiff file through convert in order to remove any weird stuff
		//print "Convert is rotating file $tiff_file to $tiff_file.tif\n";
		system ("$CONVERT -rotate 0 $tiff_file $tiff_file.tif");
		//print "Renaming $tiff_file.tif to $tiff_file\n";
		rename ("$tiff_file.tif", $tiff_file);

		// check for corruption
		if (!faxinfo ($tiff_file, $sender, $pages, $date, $format)) {
			echo "tiff2pdf:  Found corrupted fax\n";
			faxes_log ("tiff2pdf> failed: $tiff_file corrupted");
			exit;
		}
		
		system ("$TIFFPS $tiff_file | $GSR -sOutputFile=$pdf - -c quit 2>/dev/null");

		$time_end = microtime(true);
		chmod ($pdf, 0666);
		
		if (!is_file ($pdf)) { faxes_log ("tiff2pdf> failed to create $pdf"); }
		
		$time = $time_end - $time_start;
		return $time;
	}
?>
