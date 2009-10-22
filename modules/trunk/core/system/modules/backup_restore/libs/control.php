<?php 
require_once("/var/www/html/libs/misc.lib.php");
require_once("/var/www/html/configs/default.conf.php");
require_once($arrConf['basePath']."/libs/paloSantoDB.class.php");
require_once($arrConf['basePath']."/modules/backup_restore/configs/default.conf.php");
require_once($arrConf['basePath']."/modules/backup_restore/libs/paloSantoFTPBackup.class.php");
require_once( "/var/www/html/libs/paloSantoForm.class.php");

global $arrConf;
global $arrConfModule;

$dir = $arrConfModule['dir'];

$action  = getParameter('action');
$file    = getParameter('file');
$lista   = getParameter('lista'); //identifica en que lista se hace el drop

if ($action == "upload"){
    $array = obtainList($file);
    $pDB1 = new paloDB($arrConfModule['dsn_conn_database']);
    $pFTPBackup = new paloSantoFTPBackup($pDB1);
    $info = $pFTPBackup->getFTPBackupById(1);
    $user = $info['user'];
    $password = $info['password'];
    $host = $info['server'];
    $port = $info['port'];
    $path = $info['pathServer'];

    $files_names = $pFTPBackup->getExternalNames($user, $password, $host, $port, $path);
     if($lista == 'droptrue2' & $array[0] == 'out')
            echo "Please Drag and Drop a file between lists";
        else{
            if(!$files_names)   echo 'There was a problem with the connection';
            else{   if(!$array[1]) echo "Please Drag and Drop a file between lists";
                    else{
                        if($files_names == 'empty'){
                            $local_file = $array[1];
                            $remote_file = $array[1];
                            $val = $pFTPBackup->uploadFile($local_file,$remote_file,$user, $password, $host, $port, $path);
                            if ($val) {
                                echo "Successfully uploaded $local_file\n";
                            } else {
                                echo "There was a problem uploading $local_file\n";
                            }
                        }
                        else{
                            $local_file = $array[1];
                            $remote_file = $array[1];
                            $val = $pFTPBackup->uploadFile($local_file,$remote_file,$user, $password, $host, $port, $path);
                            if ($val) {
                                echo "Successfully uploaded $local_file\n";
                            } else {
                                echo "There was a problem uploading $local_file\n";
                            }
                        }
                    }
                }
            }
}
else if ($action == "download" ){
        $array = obtainList($file);
        $pDB1 = new paloDB($arrConfModule['dsn_conn_database']);
        $pFTPBackup = new paloSantoFTPBackup($pDB1);
        $info = $pFTPBackup->getFTPBackupById(1);
        $user = $info['user'];
        $password = $info['password'];
        $host = $info['server'];
        $port = $info['port'];
        $path = $info['pathServer'];

        $local_files = $pFTPBackup->obtainFiles($dir);

        if($lista == 'droptrue' & $array[0] == 'inn')
            echo "Please Drag and Drop a file between lists";
        else{
            if(!$local_files)   echo 'There was a problem with the local connection';
            else{   if(!$array[1]) echo "Please Drag and Drop a file between lists";
                    else{
                        if($local_files == 'empty'){
                            $local_file = $array[1];
                            $remote_file = $array[1];
                            $val = $pFTPBackup->downloadFile($local_file,$remote_file,$user, $password, $host, $port, $path);
                            if ($val) {
                                echo "Successfully written to $local_file\n";
                            } else {
                                echo "There was a problem downloading $local_file\n";
                            }
                        }
                        else{
                            $local_file = $array[1];
                            $remote_file = $array[1];
                            $val = $pFTPBackup->downloadFile($local_file,$remote_file,$user, $password, $host, $port, $path);
                            if ($val) {
                                echo "Successfully written to $local_file\n";
                            } else {
                                echo "There was a problem downloading $remote_file\n";
                            }
                        }
                    }
            }
        }
}

function getListUp($fileUP, $fileRemote){// fileUp toda la sita que se envia
    $up = "";
    $i = 0;
    $k = 0;
    $repetidos = "";
    $fileUP = array_unique($fileUP);
    for($j=0; $j<count($fileUP); $j++){
        if(!in_array($fileUP[$j],$fileRemote)){
            $up[$i] = $fileUP[$j];
            $i++;
        }else {
                if(filesRepeted($fileUP[$j],$fileRemote) > 0){
                    $repetidos[$k] = $fileUP[$j];
                    $k++;
                }
        }
    }
    $sal[0] = $i;
    $sal[1] = $up;
    $sal[2] = $repetidos;
    return $sal;
}

function filesRepeted($filename,$fileRemote){
    $i=0;
    $j=0;
    for($i=0; $i<count($fileRemote); $i++){
        if(in_array($filename,$fileRemote))
            $j++;
    }
    return $j;
}

function obtainList($fileString)
{
    $token = strtok($fileString, "_");
    $out = "";
    $i = 0;
    while ($token != false)
    {
        $out[$i] = $token;
        $token = strtok(";");
        $i++;
    }
    return $out;
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
?>