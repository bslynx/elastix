<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.0-15                                               |
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
  $Id: paloSantoAddonsModules.class.php,v 1.1 2010-03-06 11:03:53 Eduardo Cueva ecueva@palosanto.com Exp $ */
class paloSantoAddonsModules {
    var $_DB;
    var $errMsg;

    function paloSantoAddonsModules(&$pDB)
    {
        // Se recibe como parámetro una referencia a una conexión paloDB
        if (is_object($pDB)) {
            $this->_DB =& $pDB;
            $this->errMsg = $this->_DB->errMsg;
        } else {
            $dsn = (string)$pDB;
            $this->_DB = new paloDB($dsn);

            if (!$this->_DB->connStatus) {
                $this->errMsg = $this->_DB->errMsg;
                // debo llenar alguna variable de error
            } else {
                // debo llenar alguna variable de error
            }
        }
    }

    function getNumAddonsInstalled()
    {
        $query   = "SELECT COUNT(*) FROM addons";

        $result=$this->_DB->getFirstRowQuery($query);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return 0;
        }
        return $result[0];
    }

    function getAddonsInstalled($limit, $offset)
    {
        $query   = "SELECT * FROM addons LIMIT $limit OFFSET $offset";

        $result=$this->_DB->fetchTable($query, true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    function getAddonsInstalledALL()
    {
        $query   = "SELECT * FROM addons";

        $result=$this->_DB->fetchTable($query, true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    function getStatus($arrConf)
    {
        $status = $this->statusAddon($arrConf);
        if($status!=null){
            $arrStatus = split("\n",$status);
            $porcent_total_all = 0;
            $porcent_downl_all = 0;

            foreach($arrStatus as $k => $line){
                $arrLine = split(" ",$line);
                if($arrLine[0]=="status") $salida['status'] = $arrLine[1];

                else if($arrLine[0]=="action")  $salida['action'] = $arrLine[1];

                else if($salida['action']  == "confirm" || $salida['action']  == "downloading" || $salida['action']  == "applying"){
                    if($arrLine[0]=="package"){
                        if($arrLine[1] == "install" || $arrLine[1] == "update"){
                            $porcent_downl_all += $arrLine[4];
                            $porcent_total_all += $arrLine[3];
                            $salida['package'][] = array(
                                "action"    => $arrLine[1], //if is install or update
                                "name"      => $arrLine[2], //name's package
                                "lon_total" => $arrLine[3], //size in bytes
                                "lon_downl" => $arrLine[4], //size download in bytes
                                "status_pa" => $arrLine[5], //status package
                                "porcent_ins" => number_format($arrLine[4]*100/$arrLine[3],0), //percent
                            );
                        }
                        else if($arrLine[1] == "remove"){
                            $salida['package'][] = array(
                                "action"    => "remove", //if is install or update
                                "name"      => $arrLine[2], //name's package
                                "version"   => $arrLine[3],
                                "status_pa" => $arrLine[5], //status package
                            );
                        }
                    }
                }
                else if($salida['action']  == "checkinstalled"){
                    if($arrLine[0]=="installed"){
                        $salida['installed'][] = array(
                            "name"    => $arrLine[1], //name's package
                            "arch"    => $arrLine[2],
                            "epoch"   => $arrLine[3],
                            "version" => $arrLine[4],
                            "release" => $arrLine[5],
                        );
                    }
                }
            }

            if($salida['action']  == "confirm" || $salida['action']  == "downloading" || $salida['action']  == "applying"){
                if($porcent_total_all!=0){
                    $totalShow =  number_format(($porcent_downl_all*100/$porcent_total_all),0);
                    $salida['porcent_total_ins'] = $totalShow;
                }
                else
                    $salida['porcent_total_ins'] = 0;
            }
        }exec("echo 'salida : ".print_r($salida,true)."' >> /tmp/edu");
        return $salida;
    }

    function addAddon($arrConf, $addAddons)
    {
        return $this->commandAddons($arrConf, "add", $addAddons);
    }

    function updateAddon($arrConf, $updateAddons)
    {
        return $this->commandAddons($arrConf, "update", $updateAddons);
    }

    function removeAddon($arrConf, $removeAddons)
    {
        return $this->commandAddons($arrConf, "remove", $removeAddons);
    }

    function checkAddon($arrConf, $checkAddons)
    {
        return $this->commandAddons($arrConf, "check", $checkAddons);
    }

    function confirmAddon($arrConf)
    {
        return $this->commandAddons($arrConf, "confirm");
    }

    function clearAddon($arrConf)
    {
        return $this->commandAddons($arrConf, "clear");
    }

    function statusAddon($arrConf)
    {
        return $this->commandAddons($arrConf, "status");
    }

    function commandAddons($arrConf, $cmd, $parameters="")
    {
        $salida = null;
        try{
            $conexion = @fsockopen($arrConf['socket_conn_ip'],$arrConf['socket_conn_port'],$errno,$errstr,$timeout=30);
            if(!$conexion){
                //die($errstr.$errno);
                echo "Error: $errstr $errno";
            }
            else{
                fread($conexion,1024); // consumo el status que es default
                fputs($conexion,"$cmd $parameters\n");
                $salida = fread($conexion,1024);
                fputs($conexion,"exit\n");
            }
        }
        catch(Exception $e){
            $salida = null;
            echo $e->getMessage();
        }
        return $salida;
    }


    function insertAddons($name,$name_rpm,$version,$release){
        $query = "INSERT INTO addons(name,name_rpm,version,release) VALUES('$name','$name_rpm','$version','$release')";
        $result = $this->_DB->genQuery($query);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true; 
    }

    function exitAddons($name_rpm){
        $query = "SELECT id FROM addons WHERE name_rpm='$name_rpm'";
        $result = $this->_DB->getFirstRowQuery($query,true);
        if($result==FALSE || $result==null){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return $result['id'];
    }

    function getCheckAddonsInstalled(){
        $arrResult = $this->getAddonsInstalledALL();
        $sal = "";
        if(isset($arrResult) && $arrResult!=""){
            foreach($arrResult as $key => $value){
               $valor0 = $value['name_rpm'];
               $valor1 = $value['version'];
               $valor2 = $value['release'];
               $sal .= $valor0."|".$valor1."|".$valor2.",";
            }
        }
        return $sal;
    }

    function updateInDB($arrAddons)
    {
        foreach($arrAddons as $k => $name_rpm){
            $query = "update addons set update_st=1 where name_rpm='$name_rpm'";
            $result = $this->_DB->genQuery($query);
            if($result==FALSE){
                $this->errMsg = $this->_DB->errMsg;
                return false;
            }
        }
        return true; 
    }
}
?>