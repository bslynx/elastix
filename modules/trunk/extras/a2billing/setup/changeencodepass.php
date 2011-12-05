<?php
    $libsPath = "/var/www/html";
    require_once("$libsPath/libs/misc.lib.php");
    require_once("$libsPath/configs/default.conf.php");
    require_once("$libsPath/libs/paloSantoDB.class.php");
    require_once("$libsPath/libs/paloSantoConfig.class.php");


    $dsn_conn_database = generarDSNSistema('root', 'mya2billing',"$libsPath/");
    $pDBa2billing = new paloDB($dsn_conn_database);
    $QUERY ="SELECT pwd_encoded, userid, login FROM cc_ui_authen";
    $arr_result = $pDBa2billing->fetchTable($QUERY,true);

    // obteniendo la clave de administracion del usuario admin
    $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
    $listaParam = $pConfig->leer_configuracion(FALSE);
    $admin_pass = $listaParam['AMPDBPASS']['valor'];
    $admin_pass_enc = hash('whirlpool', $admin_pass);
	$old_pass_arr = array("changepassword", "myroot");

    if(is_array($arr_result) && count($arr_result) > 0){
        foreach($arr_result as $rowid) {
            $OldPassword = $rowid['pwd_encoded'];
			$Password = hash('whirlpool', $OldPassword);
            if(strlen($OldPassword) < 128){
                if($rowid['login'] == "root" || $rowid['login'] == "admin"){
					if(in_array($OldPassword, $old_pass_arr)){
						$Password = $admin_pass_enc;
					}
				}
				if($pDBa2billing->genQuery("update cc_ui_authen set pwd_encoded='$Password' where userid='$rowid[userid]';")){
					echo "Successfull, user PASSWORS were changed\n";
				}else echo "Error: user PASSWORS weren't changed\n";
            }else{
                // verificar la clave del usuario admin o root es una de las claves del arreglo de claves antiguas
                if($rowid['login'] == "root" || $rowid['login'] == "admin"){ 
					for($i=0; $i < count($old_pass_arr); $i++){
						$old_pass = $old_pass_arr[$i];
						$old_pass_enc = hash('whirlpool', $old_pass);
						if($OldPassword === $old_pass_enc){ // si es igual a una de las claves antiguas entonces se coloca la nueva clave
							if($pDBa2billing->genQuery("update cc_ui_authen set pwd_encoded='$admin_pass_enc' where userid='$rowid[userid]';")){
								echo "Successfull, user PASSWORS were changed\n";
							}else echo "Error: user PASSWORS weren't changed\n";
						}
					}
				}
            }
        }
    }

	removeRootUser($pDBa2billing);


	function removeRootUser($pDBa2billing){
		// verificando si existe el usuario admin.
		$query = "SELECT userid FROM cc_ui_authen WHERE login='admin';";
		$result = $pDBa2billing->getFirstRowQuery($query);
		if(count($result) > 0 && isset($query)){
			$query = "DELETE FROM cc_ui_authen WHERE login='root';";
			if($pDBa2billing->genQuery($query))
				echo "User root of a2billing was removed and user admin is the unique administrator\n";
			else
				echo "Error: User root of a2billing does not exist\n";
		}else{
			$query = "UPDATE cc_ui_authen SET login='admin' WHERE login='root';";
			if($pDBa2billing->genQuery($query))
				echo "Login of user root was changed to admin\n";
			else
				echo "Error: User root of a2billing does not exist\n";
		}
	}
?>
