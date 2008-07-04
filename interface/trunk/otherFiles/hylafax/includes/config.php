<?php	
    $dsn = "sqlite:////var/www/db/fax.db";

    try {
        $db_object = new PDO($dsn);
        //print_r($db_object->conn);
    } catch (PDOException $e) {
        die ($e->getMessage());
    }



    $RESERVED_FAX_NUM = "XXXXXXX";
    // if you need to change the document size (in lowercase)
	if (!isset($PAPERSIZE))
		$PAPERSIZE = 'a4';
	// tiff
	$TIFFPS        = "/usr/bin/tiff2ps -2ap";
	// imagemagick
	$CONVERT       = "/usr/bin/convert"; // a source install may put this in /usr/local/bin/
	// ghostscript
	$GS	       = "/usr/bin/gs";
    $GSR       = "$GS -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -dCompatibility=1.3 -sPAPERSIZE=$PAPERSIZE"; // tiff2pdf (faxrcvd)
        // hylafax
	$FAXINFO       = "/usr/sbin/faxinfo";

    //ruta de los faxes en elastic
    $faxes_path = "/var/www/html/faxes";
?>
