<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0                                               |
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
  $Id: paloSantoRules.class.php,v 1.2 2010-12-20 03:09:47 Alberto Santos asantos@palosanto.com Exp $ */

require_once "libs/paloSantoConfig.class.php";
class paloSantoRules {
    var $_DB;       // Reference to the active DB
    var $errMsg;    // Variable where the errors are stored

     /**
     * Constructor of the class, receives as a parameter the database, which is stored in the class variable $_DB
     *  .
     * @param string    $pDB     object of the class paloDB    
     */
    function paloSantoRules(&$pDB)
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

    /*HERE YOUR FUNCTIONS*/

     /**
     * Function that returns the number of rules (data) in the database
     *  .
     * @return integer  0 in case of an error or the number of rules in the database
     */
    function ObtainNumRules()
    {
        $query = "SELECT COUNT(*) FROM Filter ";
        
        $result = $this->_DB->getFirstRowQuery($query);
        if($result == FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return 0;
        }
        return $result[0];
    }

     /**
     * Function that returns all the rules in the database that are set as activated (1) order by the field rule_order
     *
     * @return array  empty if an error occurs or the data with the rules
     */
    function getActivatedRules()
    {
        $query   = "SELECT * FROM  filter WHERE activated = 1 ORDER BY rule_order";
        $result = $this->_DB->fetchTable($query, true);
        if($result == FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    /**
     * Function that returns an especific rule
     *
     * @param string     $id          id of the port to be searched
     *
     * @return array     empty if an error occurs or the data of the especific rule
     */
    function getRule($id)
    {
        $arrParam = array($id);
        $query = "SELECT * FROM filter where id=?";
        $result = $this->_DB->fetchTable($query, true, $arrParam);
        if($result == FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result[0];
    }

    /**
     * Function that returns all the rules in the database order by the field rule_order
     *
     * @param integer    $limit         Value to limit the result of the query
     * @param integer    $offset        Value for the offset of the query
     *
     * @return array     empty if an error occurs or an array with all the rules
     */
    function ObtainRules($limit,$offset)
    {
        $query   = "SELECT * FROM  filter ORDER BY rule_order LIMIT ? OFFSET ?";
        $arrParam = array($limit,$offset);
        $result = $this->_DB->fetchTable($query, true, $arrParam);
        if($result == FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    /**
     * Function that saves a new rule into the database
     *
     * @param array     $arrValues        Array with all the data of the rule to be saved
     *
     * @return bool     false if an error occurs or true if the port is correctly saved
     */
    function saveRule( $arrValues )
    {
        $traffic   = ($arrValues['traffic'] == null)       ? "" : $arrValues['traffic'];
        $eth_in    = ($arrValues['interface_in'] == null)  ? "" : $arrValues['interface_in'];
        $eth_out   = ($arrValues['interface_out'] == null) ? "" : $arrValues['interface_out'];

        $ip_s      = ($arrValues['ip_source'] == null)     ? "" : $arrValues['ip_source'];
        $ip_mask_s = ($arrValues['mask_source'] == null)   ? "" : $arrValues['mask_source'];
        if($ip_s != "")
            if($ip_mask_s != "")
                $source = $ip_s."/".$ip_mask_s;
            else
                $source = $ip_s;
        else
            $source = "";
        $ip_d      = ($arrValues['ip_destin'] == null)     ? "" : $arrValues['ip_destin'];
        $ip_mask_d = ($arrValues['mask_destin'] == null)   ? "" : $arrValues['mask_destin'];
        if($ip_d != "")
            if($ip_mask_d != "")
                $destino = $ip_d."/".$ip_mask_d;
            else
                $destino = $ip_d;
        else
            $destino = "";
        $protocol  = ($arrValues['protocol'] == null)      ? "" : $arrValues['protocol'];
        $port_in   = ($arrValues['port_in'] == null)       ? "" : $arrValues['port_in'];
        $port_out  = ($arrValues['port_out'] == null)      ? "" : $arrValues['port_out'];
        $type_icmp = ($arrValues['type_icmp'] == null)     ? "" : $arrValues['type_icmp'];
        $id_ip     = ($arrValues['id_ip'] == null)         ? "" : $arrValues['id_ip'];
        $state     =  $arrValues['state'];
        $target    = ($arrValues['target'] == null)        ? "" : $arrValues['target'];
        $Max = $this->getMaxOrder();
        $order = 1 + $Max['lastRule'];
        $query = "INSERT INTO filter(traffic, eth_in, eth_out, ip_source, ip_destiny, protocol, ".
                                    "sport, dport, icmp_type, number_ip, target, rule_order, activated, state) ".
                 "VALUES(?,?,?,?,?,?,?,?,?,?,?,?,1,?)";
        $arrParam = array($traffic,$eth_in,$eth_out,$source,$destino,$protocol,$port_in,$port_out,$type_icmp,$id_ip,$target,$order,$state);
        $result = $this->_DB->genQuery($query,$arrParam);

        if( $result == FALSE )
        {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return $this->updateNotExecutedInSystem();
    }

    /**
     * Function that updates the data of an existing port
     *
     * @param array      $arrValues         Array with all the new data of the rule
     * @param string     $id                id of the rule to be updated
     *
     * @return bool      false if an error occurs or true if the rule is correctly updated
     */
    function updateRule($arrValues,$id)
    {
        $traffic   = ($arrValues['traffic'] == null)       ? "" : $arrValues['traffic'];
        $eth_in    = ($arrValues['interface_in'] == null)  ? "" : $arrValues['interface_in'];
        $eth_out   = ($arrValues['interface_out'] == null) ? "" : $arrValues['interface_out'];

        $ip_s      = ($arrValues['ip_source'] == null)     ? "" : $arrValues['ip_source'];
        $ip_mask_s = ($arrValues['mask_source'] == null)   ? "" : $arrValues['mask_source'];
        if($ip_s != "")
            if($ip_mask_s != "")
                $source = $ip_s."/".$ip_mask_s;
            else
                $source = $ip_s;
        else
            $source = "";
        $ip_d      = ($arrValues['ip_destin'] == null)     ? "" : $arrValues['ip_destin'];
        $ip_mask_d = ($arrValues['mask_destin'] == null)   ? "" : $arrValues['mask_destin'];
        if($ip_d != "")
            if($ip_mask_d != "")
                $destino = $ip_d."/".$ip_mask_d;
            else
                $destino = $ip_d;
        else
            $destino = "";
        $protocol  = ($arrValues['protocol'] == null)      ? "" : $arrValues['protocol'];
        $port_in   = ($arrValues['port_in'] == null)       ? "" : $arrValues['port_in'];
        $port_out  = ($arrValues['port_out'] == null)      ? "" : $arrValues['port_out'];
        $type_icmp = ($arrValues['type_icmp'] == null)     ? "" : $arrValues['type_icmp'];
        $id_ip     = ($arrValues['id_ip'] == null)         ? "" : $arrValues['id_ip'];
        $state     =  $arrValues['state'];
        $target    = ($arrValues['target'] == null)        ? "" : $arrValues['target'];
        $orden     = ($arrValues['orden'] == null)         ?  0 : $arrValues['orden'];
        $query = "UPDATE filter SET traffic = ?, eth_in = ?, eth_out = ?, ip_source = ?, ip_destiny = ?, protocol = ?, sport = ?, dport = ?, icmp_type = ?, number_ip = ?, target = ?, rule_order = ?, state = ? WHERE id = ?";
        $arrParam = array($traffic,$eth_in,$eth_out,$source,$destino,$protocol,$port_in,$port_out,$type_icmp,$id_ip,$target,$orden,$state,$id);
        $result = $this->_DB->genQuery($query,$arrParam);

        if( $result == FALSE )
        {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return $this->updateNotExecutedInSystem();
    }

    /**
     * Function that returns the maximum number of order of all the rules in the database
     *  .
     * @return array     empty in case of an error or an array that contains the maximum order of all rules
     */
    function getMaxOrder()
    {
        $query = "SELECT MAX(rule_order) AS lastRule FROM filter";    
        $result = $this->_DB->fetchTable($query, true);
        if($result == FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result[0];
    }

    /**
     * Function that deletes a rule of the database
     *
     * @param string     $id         id of the rule to be deleted
     *
     * @return bool      false if an error occurs or true if the rule is correctly deleted
     */ 
    function deleteRule($id)
    {
        $arrParam = array($id);
        $query = "DELETE FROM filter WHERE id=?";
        $result = $this->_DB->genQuery($query,$arrParam);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $this->reorder();
    }

    /**
     * Function that reorder all the rules, if there is a jump between the order of one rule to the next one it eliminates that jump setting the * correct order 
     *
     * @return bool      false if an error occurs or true if the rules have been correctly reordered
     */
    function reorder()
    {
        $total = $this->ObtainNumRules();
        $result = $this->ObtainRules($total,0);
        foreach($result as $key => $value){
            if($value['rule_order'] != $key + 1)
                if(!$this->updateOrder($value['id'],$key+1))
                    return false;
        }
        return $this->updateNotExecutedInSystem();
    }

    /**
     * Function that returns the name of all the network interfaces available in the system
     *
     * @return array      Array with the name of the interfaces
     */
    function obtener_nombres_interfases_red() 
    {
        //Se buscan las descripciones en la base de datos
        $arr_datos=array();
        $arr_descrip=array();
        
        $sQuery="SELECT * FROM interfase";
        $result=$this->_DB->fetchTable($sQuery,true);
        if(is_array($result) && count($result)>0){
            foreach($result as $fila)
                $arr_descrip[$fila['dev']]=array("nombre"=>$fila['nombre'],"descripcion"=>$fila['descripcion']);
        }
        
        $arr_interfases=$this->obtener_interfases_red();    
        foreach($arr_interfases as $dev=>$datos){
            if(array_key_exists($dev,$arr_descrip))
                //$arr_datos[$dev]=$arr_descrip[$dev]['nombre']." - ".$datos['Name'];
                $arr_datos[$dev]=$arr_descrip[$dev]['nombre'];
            else
                $arr_datos[$dev]=$datos['Name'];
        }
                
        return $arr_datos;                                 
    }

    /**
     * Function that returns the network interfaces in the system
     *
     * @return array      Array with the interfaces
     */
    function obtener_interfases_red()
    {
        $str = shell_exec("/sbin/ifconfig");
    
        $arrIfconfig = explode("\n", $str);
    
        $arrModelosInterfasesRed = $this->obtener_modelos_interfases_red();
    
        foreach($arrIfconfig as $lineaIfconfig) {
    
            unset($arrReg);
    
            if(ereg("^eth(([[:digit:]]{1,3})(:([[:digit:]]{1,3}))?)[[:space:]]+", $lineaIfconfig, $arrReg)) {
                $interfaseActual = "eth" . $arrReg[1];
                $nombreInterfase = "Ethernet $arrReg[2]";
                if(!empty($arrReg[3])) {
                    $nombreInterfase .= " Alias $arrReg[4]";
                } else if(isset($arrModelosInterfasesRed[$interfaseActual])) {
                    $arrIf[$interfaseActual]["HW_info"] = $arrModelosInterfasesRed[$interfaseActual];        
                }
                $arrIf[$interfaseActual]["Name"] = $nombreInterfase;
            }
    
            if(ereg("^(lo)[[:space:]]+", $lineaIfconfig, $arrReg)) {
                    $interfaseActual = $arrReg[1];
                    $arrIf[$interfaseActual]["Name"] = "Loopback";
            }
    
            // debo tambien poder determinar cuando se termina una segmento de interfase
            // no solo cuando comienza como se hace en los dos parrafos anteriores
        
            if(ereg("HWaddr ([ABCDEF[:digit:]]{2}:[ABCDEF[:digit:]]{2}:[ABCDEF[:digit:]]{2}:" .
                    "[ABCDEF[:digit:]]{2}:[ABCDEF[:digit:]]{2}:[ABCDEF[:digit:]]{2})", $lineaIfconfig, $arrReg)) {
                    $arrIf[$interfaseActual]["HWaddr"] = $arrReg[1];
            }
    
            if(ereg("^[[:space:]]+inet addr:([[:digit:]]{1,3}\.[[:digit:]]{1,3}\.[[:digit:]]{1,3}\.[[:digit:]]{1,3})",
            $lineaIfconfig, $arrReg)) {
                    $arrIf[$interfaseActual]["Inet Addr"] = $arrReg[1];
            }
    
            if(ereg("[[:space:]]+Mask:([[:digit:]]{1,3}\.[[:digit:]]{1,3}\.[[:digit:]]{1,3}\.[[:digit:]]{1,3})$",
            $lineaIfconfig, $arrReg)) {
                    $arrIf[$interfaseActual]["Mask"] = $arrReg[1];
            }
    
            // TODO: El siguiente patron de matching es muy simple, cambiar
            if(ereg(" RUNNING ", $lineaIfconfig, $arrReg)) {
                    $arrIf[$interfaseActual]["Running"] = "Yes";
            }
    
            if(ereg("^[[:space:]]+RX packets:([[:digit:]]{1,20})", $lineaIfconfig, $arrReg)) {
                    $arrIf[$interfaseActual]["RX packets"] = $arrReg[1];
            }
    
            if(ereg("^[[:space:]]+RX bytes:([[:digit:]]{1,20})", $lineaIfconfig, $arrReg)) {
                    $arrIf[$interfaseActual]["RX bytes"] = $arrReg[1];
            }
    
            if(ereg("^[[:space:]]+TX packets:([[:digit:]]{1,20})", $lineaIfconfig, $arrReg)) {
                    $arrIf[$interfaseActual]["TX packets"] = $arrReg[1];
            }
    
            if(ereg("[[:space:]]+TX bytes:([[:digit:]]{1,20})", $lineaIfconfig, $arrReg)) {
                    $arrIf[$interfaseActual]["TX bytes"] = $arrReg[1];
            }
    
        }
        
        return $arrIf;
    }

    /**
     * Function that returns the model of the network interfaces in the system
     *
     * @return array      Array with the model of the interfaces
     */
    function obtener_modelos_interfases_red()
    {
        $arrSalida=array();
        $str = shell_exec("/bin/dmesg");
    
        $arrLineasDmesg = explode("\n", $str);
    
        foreach($arrLineasDmesg as $lineaDmesg) {
            //if(ereg("^(eth[[:digit:]]{1,3})", $lineaDmesg, $arrReg)) {
            //    echo $lineaDmesg;
            //}
            if(ereg("^(eth[[:digit:]]{1,3}):[[:space:]]+(.*)$", $lineaDmesg, $arrReg) and ereg(" at ", $lineaDmesg)) {
                $arrSalida[$arrReg[1]] = $arrReg[2];
            }
        }
        return $arrSalida;
    }

    /**
     * Function that sets an especific rule as activated (1) 
     *
     * @param string     $id         id of the rule to be activated
     *
     * @return bool      false if an error occurs or true if the rule is correctly activated
     */ 
    function setActivated($id)
    {
        $arrParam = array($id);
        $query = "UPDATE filter SET activated = 1 WHERE id = ?";
               
        $result = $this->_DB->genQuery($query,$arrParam);

        if( $result == FALSE )
        {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return $this->updateNotExecutedInSystem();
    }

    /**
     * Function that sets an especific rule as desactivated (0) 
     *
     * @param string     $id         id of the rule to be desactivated
     *
     * @return bool      false if an error occurs or true if the rule is correctly desactivated
     */ 
    function setDesactivated($id)
    {
        $arrParam = array($id);
        $query = "UPDATE filter SET activated = 0 WHERE id = ?";
               
        $result = $this->_DB->genQuery($query,$arrParam);

        if( $result == FALSE )
        {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return $this->updateNotExecutedInSystem();
    }

    function desactivateAll()
    {
        $query = "UPDATE filter SET activated = 0";
               
        $result = $this->_DB->genQuery($query);

        if( $result == FALSE )
        {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
    }

    /**
     * Function that converts a decimal number in a binary number of 8 digits ( 10 = 00001010)
     *
     * @param integer    $octeto         Number to be converted to binary
     *
     * @return string    A string with the equivalent in binary of the given number
     */ 
    function bitstr_8($octeto)
    {
        $octeto = ((int)$octeto) & 0x000000FF;
        $lista_bits = array_fill(0, 8, "0");
        for ($i = 0; $i < 8; $i++)
        {
            $mascara = 0x80 >> $i;
            if ($octeto & $mascara) $lista_bits[$i] = "1";
        }
        return implode("", $lista_bits);
    }

    /**
     * Function that returns the network address of the given ip for the given mask 
     *
     * @param string     $ip         ip address
     * @param string     $mask       mask of the ip address (in decimal format)    
     *
     * @return string    String with the network address
     */ 
    function getNetAdress($ip,$mask)
    {
        $octetos_ip = explode(".",$ip);
        for($k=0;$k<$mask;$k++)
            $octetos_mask[$k] = "1";
        $res = 32 - $k;
        for($k=0;$k<$res;$k++)
            $octetos_mask[] = "0";
        $k = 0;
        for($i=0;$i<4;$i++){
            $binary_octeto_ip = $this->bitstr_8($octetos_ip[$i]);
            for($j=0;$j<8;$j++){
                if($binary_octeto_ip[$j] && $octetos_mask[$k])
                    $netAddress_binary[] = "1";
                else
                    $netAddress_binary[] = "0";
                $k++;
            }
        }
       $netAddress_decimal = $this->binaryOctetos_to_decimalOctetos($netAddress_binary);
       return $netAddress_decimal; 
    }

    /**
     * Function that converts an ip address in binary format to decimal format 
     *
     * @param string     $binary         ip address in binary format 
     *
     * @return string    String with ip address in decimal format
     */     
    function binaryOctetos_to_decimalOctetos($binary)
    {
        $k=0;
        $decimalOctetos="";
        for($i=0;$i<4;$i++){
            $sum = 128;
            $result = 0;
            for($j=0;$j<8;$j++){
                if($binary[$k])
                    $result = $result + $sum;
                $k++;
                $sum = $sum/2;
            }
            $decimalOctetos.=$result;
            if($i!=3)
                $decimalOctetos.=".";
        }
        return $decimalOctetos;
    }

    /**
     * Function that sets a new order for an especific rule 
     *
     * @param string     $id         id of the rule
     * @param string     $order      New order to be set    
     *
     * @return bool      false if an error occurs or true if new order is set
     */ 
    function updateOrder($id,$order)
    {
        $arrParam = array($order,$id);
        $query = "UPDATE filter SET rule_order = ? WHERE id = ?";
        $result = $this->_DB->genQuery($query,$arrParam);
        if( $result == FALSE )
        {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return $this->updateNotExecutedInSystem();
    }

    /**
     * Function that deletes all the rules of the system
     *
     * @return bool    false if an error occurs or true if the rules are deleted of the system
     */ 
    function flushRules()
    {
        exec("sudo -u root /sbin/iptables -F", $salida, $retorno1);
        exec("sudo -u root /sbin/iptables -X", $salida, $retorno2);
        exec("sudo -u root /sbin/iptables -Z", $salida, $retorno3);
        exec("sudo -u root /sbin/iptables -t nat -F", $salida, $retorno4);
        if($retorno1 == 0 && $retorno2 == 0 && $retorno3 == 0 && $retorno4 == 0)
            return true;
        return false;
    }

    /**
     * Function that activates the rules in the system 
     *
     * @param array      $rules      Array with all the rules to be activated in the system
     * @param string     $error      Variable that stores all the errors that could occur    
     *
     * @return bool      false if an error occurs or true if the rules are correctly activated in the system
     */ 
    function activateRules($rules,&$error)
    {
        $i = 1;
        $this->verificar_cadenas_stickgate();
        foreach($rules as $key => $rule){
            if($rule['traffic'] == "INPUT")
                $traffic = "ELASTIX_INPUT";
            if($rule['traffic'] == "OUTPUT")
                $traffic = "ELASTIX_OUTPUT";
            if($rule['traffic'] == "FORWARD")
                $traffic = "ELASTIX_FORWARD";
            $comand = "/sbin/iptables -A";
            $parameters = "";
            if($rule['ip_destiny']!= "0.0.0.0/0" && $rule['ip_destiny'] != "")
                $parameters.= "-d $rule[ip_destiny] ";
            if($rule['ip_source']!= "0.0.0.0/0" && $rule['ip_source'] != "")
                $parameters.= "-s $rule[ip_source] ";
            if($rule['protocol'] == "TCP" || $rule['protocol'] == "UDP"){
                if($rule['sport'] != "ANY")
                    $parameters.= "-p $rule[protocol] --sport $rule[sport] ";
                if($rule['dport'] != "ANY")
                    $parameters.= "-p $rule[protocol] --dport $rule[dport] ";
            }
            if($rule['protocol'] == "ICMP")
                $parameters.= "-p icmp ";
            if($rule['protocol'] == "IP")
                $parameters.= "-p ip ";
            if($rule['protocol'] == "STATE")
                $parameters.= "-m state --state $rule[state]";
            if($rule['eth_in'] != "ANY" && $rule['eth_in'] != "")
                $parameters.= "-i $rule[eth_in] ";
            if($rule['eth_out'] != "ANY" && $rule['eth_out'] != "")
                $parameters.= "-o $rule[eth_out] ";

            $comand = "sudo -u root $comand $traffic $parameters -j $rule[target]";
            exec($comand,$action,$retorno[$key]);
            if($retorno[$key] == 0){
                $retorno[$key]= $this->iptables_save($error);
                if(!$retorno[$key])
                    return false;
            }else 
                return false;
            /*if($i == 18){
                $comand = "sudo -u root /sbin/iptables -A ELASTIX_INPUT -m state --state ESTABLISHED,RELATED -j ACCEPT";
                exec($comand,$action,$retorno[$key]);
                if($retorno[$key] == 0){
                    $retorno[$key]= $this->iptables_save($error);
                    if(!$retorno[$key])
                        return false;
                }else 
                    return false;
                }
            $i++;*/
        }
        return true;
    }

    /**
     * Function that verify if the stickgate chains exists or not. If not it create them
     */ 
    function verificar_cadenas_stickgate()
    {
        $cadenas=array("INPUT" => "ELASTIX_INPUT", "OUTPUT"=> "ELASTIX_OUTPUT","FORWARD"=>"ELASTIX_FORWARD");
        $comando= "/sbin/iptables";
        foreach ($cadenas as $key => $cadena){
          $existe=$this->buscarCadena($cadena);
          if (!$existe){//crear la cadena
                  exec("sudo -u root $comando -N $cadena", $salida, $retorno);
                  if ($retorno==0)
                  {
                          //ingresar la regla
                          exec("sudo -u root $comando -A $key -j $cadena", $salida, $retorno);
                  }           
            }
       }
    }

    /**
     * Function that searches a given chain in the system
     *
     * @param string     $cadena     chain to be searched 
     *
     * @return bool      false if the chain it is not defined in the system or true if the chain is already defined in the system
     */ 
    function buscarCadena($cadena)
    {
        $cadenas = $this->extraerCadenas();
        if (sizeof($cadenas) == 0)
            return "error";
        $i = 0;
        while ($i < sizeof($cadenas) and strcmp($cadenas[$i],$cadena) != 0)
            $i++;
        if ($i >= sizeof($cadenas))
            return false; // La cadena no se encuentra entre las ya definidas
        else
            return true;
    }

    /**
     * Function that returns all the chains of the system   
     *
     * @return array   Array with the chains
     */ 
    function extraerCadenas()
    {
        exec("sudo -u root /sbin/iptables -L -n", $salida, $retorno);
        if ($retorno != 0)
            return;
        else 
        {
            $pos = 0;
            for ($i=0; $i<sizeof($salida); $i++)
            {
                $palabras = explode(" ", $salida[$i]);
                if (strcmp($palabras[0],"Chain") == 0 and strcmp($palabras[1],"entrada_admin") != 0
                    and strcmp($palabras[1],"salida_admin") != 0)
                {
                    $cadenas[] = $palabras[1];
                    $pos++;
                }
            }
        }   
        return $cadenas;
    }

    /**
     * Function that saves the iptables rules of the system 
     *
     * @param string     $error         Variable that stores the errors if there exist  
     *
     * @return bool      false if an error occurs or true if the iptables rules are correctly saved
     */ 
    function iptables_save(&$error)
    {
        $bRetorno=FALSE;
        $bValido=TRUE;
        $archivo_existe=FALSE;
        $oConfig=new paloConfig("/etc/sysconfig","iptables","","");
        //respaldar el archivo /etc/sysconfig/iptables
        //comprobar que exista para copiarlo
        if (file_exists("/etc/sysconfig/iptables"))
        {
        //exec("/yb/bin/sudo -u root cp /etc/sysconfig/iptables /etc/sysconfig/iptables.old", $salida, $retorno);
        $bValido=$oConfig->respaldar_archivo("iptables.bck");
        if(!$bValido) $error=$oConfig->getMessage();
        $archivo_existe=TRUE;
        }
        if ($bValido){
            //Se deben cambiar los permisos del archivo para poder escribirlo
            exec("sudo -u root touch /etc/sysconfig/iptables");
            exec("sudo -u root /bin/chmod 777 /etc/sysconfig/iptables");
            exec("sudo -u root /sbin/iptables-save > /etc/sysconfig/iptables", $salida, $retorno);
            exec("sudo -u root /bin/chmod 744 /etc/sysconfig/iptables");
            if ($retorno != 0){
                $error = "Error al guardar los iptables";
                //$error=FIREWALL_MSG_ERROR_11;
                //hacer restore
                if ($archivo_existe){
                    $bValido=$oConfig->recuperar_archivo("iptables.bck");
                    exec("sudo -u root /sbin/iptables-restore /etc/sysconfig/iptables", $salida, $retorno);
                }
            }
            else
            {
                $bRetorno=TRUE;
                //borrar el archivo de respaldo
                //if ($archivo_existe)
                    //exec("/yb/bin/sudo -u root rm /etc/sysconfig/iptables.old", $salida, $retorno);
            }
        }
        return $bRetorno;
    }

    /**
     * Function that updates the database to indicate that something has not been executed on systen 
     *
     * @return bool      false if an error occurs or true if the update is successful
     */ 
    function updateNotExecutedInSystem()
    {
        $query = "UPDATE tmp_execute SET exec_in_sys = 0";
        $result = $this->_DB->genQuery($query);
        if( $result == FALSE )
        {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
    }

    /**
     * Function that updates the database to indicate that all has been executed on systen 
     *
     * @return bool      false if an error occurs or true if the update is successful
     */ 
    function updateExecutedInSystem()
    {
        $query = "UPDATE tmp_execute SET exec_in_sys = 1";
        $result = $this->_DB->genQuery($query);
        if( $result == FALSE )
        {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
    }

    /**
     * Function that indicates if everything has been executed on system or not 
     *
     * @return bool      false if something has not been executed on system or true if everything has
     */ 
    function isExecutedInSystem()
    {
        $query = "SELECT exec_in_sys from tmp_execute";
        $result = $this->_DB->fetchTable($query, true);
        if($result == FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return;
        }
        $data = $result[0];
        if($data['exec_in_sys'] == 0)
            return false;
        return true;
    }

    function isFirstTime()
    {
        $query = "SELECT first_time from tmp_execute";
        $result = $this->_DB->fetchTable($query, true);
        if($result == FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return;
        }
        $data = $result[0];
        if($data['first_time'] == 0)
            return false;
        return true;
    }

    function setFirstTime()
    {
        $query = "update tmp_execute set first_time = 1";
        $result = $this->_DB->genQuery($query, true);
        if($result == FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return $this->updateNotExecutedInSystem();
    }

    function noMoreFirstTime()
    {
        $query = "update tmp_execute set first_time = 0";
        $result = $this->_DB->genQuery($query, true);
        if($result == FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
    }

    function getPreviousRule($actual_order)
    {
	$previous_order = $actual_order - 1;
	$query = "select * from filter where rule_order=?";
	$arrParam = array($previous_order);
	$result = $this->_DB->fetchTable($query, true, $arrParam);
	if($result == FALSE){
	    $this->errMsg = $this->_DB->errMsg;
            return null;
	}
	return $result[0];
    }

    function getNextRule($actual_order)
    {
	$next_order = $actual_order + 1;
	$query = "select * from filter where rule_order=?";
	$arrParam = array($next_order);
	$result = $this->_DB->fetchTable($query, true, $arrParam);
	if($result == FALSE){
	    $this->errMsg = $this->_DB->errMsg;
            return null;
	}
	return (isset($result[0]))?$result[0]:array();
    }
}
?>