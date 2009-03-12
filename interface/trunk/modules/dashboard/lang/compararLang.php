<?php

require_once('en.lang');
require_once('hr.lang');

//Siempre pon como $arrLang2 el que no sabes si esta completo
//$arrLang  -> en.lang por lo general el ingles esta siempre completo.
//$arrLang2 -> xx.lang
$existe = false;
$cont1 = 0;
$cont2 = 0;
foreach($arrLangModule as $key => $value){
	$existe = false;
        $cont1 +=1;
	foreach($arrLangModule2 as $key2 => $value2){
		if($key==$key2){
			$existe = true;	
			break;
		}
	}
	if($existe){
		echo "\"$key2\" => \"$value2\",\n";
               $cont2 +=1;
       } else
                echo "NO EXISTE: "." \"$key\" => \"$value\",\n";
}
echo "TOTAL en.lang: $cont1 - TOTAL otro.lang: $cont2\n";
?>
