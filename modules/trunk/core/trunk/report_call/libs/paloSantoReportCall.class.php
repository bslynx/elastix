<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.4-1                                                |
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
  $Id: paloSantoReportCall.class.php,v 1.1 2009-01-06 09:01:38 jvega jvega@palosanto.com Exp $ */

class paloSantoReportCall {
    var $_DB_cdr;
    var $_DB_billing;
    var $errMsg;
    var $arrLang;

    function paloSantoReportCall(&$pDB_cdr, &$pDB_billing=null)
    {
        $this->CargarIdiomas();

        // Se recibe como parámetro una referencia a una conexión paloDB
        if (is_object($pDB_cdr)) {
            $this->_DB_cdr =& $pDB_cdr;
            $this->errMsg = $this->_DB_cdr->errMsg;
        } else {
            $dsn = (string)$pDB_cdr;
            $this->_DB_cdr = new paloDB($dsn);

            if (!$this->_DB_cdr->connStatus) {
                $this->errMsg = $this->_DB_cdr->errMsg;
                // debo llenar alguna variable de error
            } else {
                // debo llenar alguna variable de error
            }
        }

        if (is_object($pDB_billing)) {
            $this->_DB_billing =& $pDB_billing;
            $this->errMsg = $this->_DB_billing->errMsg;
        } else {
            $dsn = (string)$pDB_billing;
            $this->_DB_billing = new paloDB($dsn);

            if (!$this->_DB_billing->connStatus) {
                $this->errMsg = $this->_DB_billing->errMsg;
                // debo llenar alguna variable de error
            } else {
                // debo llenar alguna variable de error
            }
        }
    }

    function CargarIdiomas()
    {
        global $arrConf;

        include_once $arrConf['basePath']."/libs/misc.lib.php";
        $lang = get_language($arrConf['basePath'].'/');

        if( file_exists($arrConf['basePath']."/modules/report_call/lang/$lang.lang") )
            include_once $arrConf['basePath']."/modules/report_call/lang/$lang.lang";
        else
            include_once $arrConf['basePath']."/modules/report_call/lang/en.lang";

        global $arrLangModule;
        $this->arrLang = $arrLangModule;
    }

    function ObtainBillingByTrunk()
    {
        $query= "select * from rate";

        $result = $this->_DB_billing->fetchTable($query, true);

        if($result == FALSE){
            $this->errMsg = $this->_DB_billing->errMsg;
            return array();
        }

        return $result;
    }

    function ObtainNumberDevices($type, $value)
    {
        $extension   = "";
        $description = "";
        if( $type == 'Extention' ) $extension=$value;
        else if( $type == 'User' ) $description=$value;

        $query= "select count(*) from asterisk.devices d where d.id like '$extension%' AND d.description like '$description%'";
        $result = $this->_DB_cdr->getFirstRowQuery($query);

        if($result == FALSE){
            $this->errMsg = $this->_DB_cdr->errMsg;
            return 0;
        }
        return $result[0];
    }

    function ObtainReportCall($limit, $offset, $date_ini, $date_end, $type, $value, $order_by)
    {
        $extension   = "";
        $description = "";

        if( $type == 'Extention' ) $extension   = $value;
        else if( $type == 'User' ) $description = $value;

        //PASO 1: Obtengo datos salientes de todas las extensiones que estan en la tabla devices, por ello uso
        //        el RIGHT JOIN
        $query_outgoing_call="
            SELECT
                t_devices.id source,
                t_devices.description name,
                ifnull(t_cdr.num_outgoing_call,0) num_outgoing_call,
                ifnull(t_cdr.duration_outgoing_call,0) duration_outgoing_call
            FROM 
                (SELECT 
                    c.src source, 
                    count(c.src) num_outgoing_call, 
                    sum(c.billsec) duration_outgoing_call
                FROM 
                    asteriskcdrdb.cdr c 
                WHERE 
                    c.calldate>='$date_ini' AND 
                    c.calldate<='$date_end' AND
                    c.src like '$extension%'
                GROUP BY c.src) t_cdr 
            RIGHT JOIN 
                (SELECT 
                    d.id, 
                    d.description
                 FROM
                    asterisk.devices d
                 WHERE
                    d.id like '$extension%' AND
                    d.description like '$description%') t_devices
            ON t_devices.id=t_cdr.source 
            LIMIT $limit OFFSET $offset";

        //PASO 2: Obtengo datos entrantes de todas las extensiones que estan en la tabla devices, por ello uso
        //        el RIGHT JOIN, el numero de registros son iguales tanto en el paso 1 y paso2.
        $query_incoming_call="
            SELECT 
                t_devices.id destiny,
                t_devices.description name,
                ifnull(t_cdr.num_incoming_call,0) num_incoming_call, 
                ifnull(t_cdr.duration_incoming_call,0) duration_incoming_call 
            FROM 
                (SELECT 
                    c.dst destiny, 
                    count(c.dst) num_incoming_call, 
                    sum(c.billsec) duration_incoming_call 
                FROM 
                    asteriskcdrdb.cdr c 
                WHERE 
                    c.calldate>='$date_ini' AND 
                    c.calldate<='$date_end' AND
                    c.dst like '$extension%'
                GROUP BY c.dst ) t_cdr 
            RIGHT JOIN 
                (SELECT 
                    d.id, 
                    d.description
                 FROM
                    asterisk.devices d
                 WHERE
                    d.id like '$extension%' AND
                    d.description like '$description%') t_devices
            ON t_devices.id=t_cdr.destiny
            LIMIT $limit OFFSET $offset";

        //PASO 3: Uno ambos resultados.
        $query_extension_call="
            SELECT 
                t_outgoing_call.source extension,
                t_outgoing_call.name user_name,
                t_incoming_call.num_incoming_call,
                t_outgoing_call.num_outgoing_call,
                t_incoming_call.duration_incoming_call, 
                t_outgoing_call.duration_outgoing_call
            FROM 
                ($query_outgoing_call) t_outgoing_call
            INNER JOIN 
                ($query_incoming_call) t_incoming_call
            ON
                t_outgoing_call.source=t_incoming_call.destiny
            ORDER BY $order_by desc;";

        $result = $this->_DB_cdr->fetchTable($query_extension_call, true);

        if($result == FALSE){
            $this->errMsg = $this->_DB_cdr->errMsg;
            return array();
        }
        return $result;
    }

    //PARA PLOT3D
    function callbackTopMoreCalls($date_ini, $date_end, $ext)
    {
        $result = $this->obtainTop10Salientes( $date_ini, $date_end, $ext );

        $arrColor = array('blue','red','yellow','brown','green','orange','pink','purple','gray','white');

        $arrT = array();
        $i = 0;
        foreach( $result as $num => $arrR ){
            $arrT["DAT_$i"] = array('VALUES' => array('VALUE'=>$arrR[0]),
                                    'STYLE'  => array('COLOR'=>$arrColor[$i], 'LEYEND'=>$arrR[1]." (".$arrR[0].")"));
            $i++;
        }

        return array( 
            'ATTRIBUTES' => array(
                //NECESARIOS
                'TITLE'   => $this->arrLang['Top 10 (Outgoing) ext']." ".$ext,
                'TYPE'    => 'plot3d',
                'SIZE'    => "500,250", 
                'MARGIN'  => "5,70,15,20",
            ),

            'MESSAGES'  => array(
                'ERROR' => 'Error', 
                'NOTHING_SHOW' => 'Nada que mostrar'
            ),
            //DATOS A DIBUJAR
            'DATA' => $arrT );
    }

    function obtainTop10Salientes( $date_ini, $date_end, $ext )
    {
        if( $ext == null) return array();

        $query = "SELECT count(dst) as num, dst ".
                 "FROM cdr ".
                 "WHERE calldate >= '$date_ini' AND ".
                       "calldate <= '$date_end' AND ".
                       "src = $ext ".
                 "GROUP BY dst ".
                 "ORDER BY 1 desc ".
                 "LIMIT 10 ";
        $result = $this->_DB_cdr->fetchTable($query, false);

        if($result == FALSE){
            $this->errMsg = $this->_DB_cdr->errMsg;
            print_r($this->errMsg);
            return array();
        }
        return $result;
    }

    function callbackBillingsByTrunks($date_ini, $date_end, $ext)
    {
        $result = $this->obtainTop10Entrantes( $date_ini, $date_end, $ext );

        $arrColor = array('blue','red','yellow','brown','green','orange','pink','purple','gray','white');

        $arrT = array();
        $i = 0;
        foreach( $result as $num => $arrR ){
            $arrT["DAT_$i"] = array('VALUES' => array('VALUE'=>$arrR[0]),
                                    'STYLE'  => array('COLOR'=>$arrColor[$i], 'LEYEND'=>$arrR[1]." (".$arrR[0].")"));
            $i++;
        }

        return array( 
            'ATTRIBUTES' => array(
                //NECESARIOS
                'TITLE'   => $this->arrLang['Top 10 (Incoming) ext']." ".$ext,
                'TYPE'    => 'plot3d',
                'SIZE'    => "500,250", 
                'MARGIN'  => "5,70,15,20",
            ),

            'MESSAGES'  => array(
                'ERROR' => 'Error', 
                'NOTHING_SHOW' => 'Nada que mostrar'
            ),
            //DATOS A DIBUJAR
            'DATA' => $arrT );
    }

    function obtainTop10Entrantes( $date_ini, $date_end, $ext )
    {
        if( $ext == null) return array();

        $query = "SELECT count(src) as num, src ".
                 "FROM cdr ".
                 "WHERE calldate >= '$date_ini' AND ".
                       "calldate <= '$date_end' AND ".
                       "dst = $ext ".
                 "GROUP BY src ".
                 "ORDER BY 1 desc ".
                 "LIMIT 10 ";
        $result = $this->_DB_cdr->fetchTable($query, false);

        if($result == FALSE){
            $this->errMsg = $this->_DB_cdr->errMsg;
            print_r($this->errMsg);
            return array();
        }
        return $result;
    }
}
?>