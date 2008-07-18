<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 0.5                                                  |
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
  $Id: paloSantoTrunk.class.php,v 1.1.1.1 2007/07/06 21:31:55 gcarrillo Exp $ */

if (isset($arrConf['basePath'])) {
    include_once($arrConf['basePath'] . "/libs/paloSantoDB.class.php");
} else {
    include_once("libs/paloSantoDB.class.php");
}

class paloTrunk {

    var $_DB; // instancia de la clase paloDB
    var $errMsg;

    function paloTrunk(&$pDB)
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


    /**
     * Procedimiento para guardar un arreglo de trunks para billing
     *
     * @param array    $listaTrunks       lista trunks para billing
     *
     * @return bool     VERDADERO si se guardaron correctamente, FALSO en error
     */
    function saveTrunksBill($listaTrunks)
    {
        $bExito = FALSE;
        if (!is_array($listaTrunks)) {
            $this->errMsg = "Values for trunks are invalid";
        } else {
            foreach ($listaTrunks as $trunk){
                $sPeticionSQL = paloDB::construirInsert(
                    "trunk_bill",
                    array(
                        "trunk"       =>  paloDB::DBCAMPO($trunk),
                    )
                );
                if ($this->_DB->genQuery($sPeticionSQL)) {
                    $bExito = TRUE;
                } else {
                    $this->errMsg = $this->_DB->errMsg;
                }
             }
         }

        return $bExito;
    }

    /**
     * Procedimiento para borrar una lista de trunks para billing
     *
     * @param array   $listaTrunks 
     *
     * @return bool VERDADERO si se pudieron borrar correctamente
     */
    function deleteTrunksBill($listaTrunks)
    {
        $bExito = FALSE;
        if (!is_array($listaTrunks)) {
            $this->errMsg = "Values for trunks are invalid";
        } 
        else {
            $this->errMsg = "";
            foreach ($listaTrunks as $trunk){
                $sPeticionSQL = 
                    "DELETE FROM trunk_bill WHERE trunk = ".paloDB::DBCAMPO($trunk);
                $bExito = TRUE;
                $bExito = $this->_DB->genQuery($sPeticionSQL);
                if (!$bExito) {print $sPeticionSQL;
                    $this->errMsg = $this->_DB->errMsg;
                    break;
                }
            }

        }
        return $bExito;
    }

    function getTrunksBill()
    {
        $trunks_bill = array();

        $this->errMsg = "";
        $sPeticionSQL = 
            "SELECT * FROM trunk_bill ";

        $arr_result =& $this->_DB->fetchTable($sPeticionSQL);
        if (!is_array($arr_result)) {
            $arr_result = FALSE;
            $this->errMsg = $this->_DB->errMsg;
        }else
        {
            foreach ($arr_result as $trunk)
                $trunks_bill[]=$trunk[0];
        }

        return $trunks_bill;
    }

    /**
     * Method to parse zapata file and resolve his group to trunks
     *
     * @param    string $zapata_file     zapata configuration file
     * @callback array  $grupos          group list
     *
     * @return   array  $troncales       array with resolved group
     */

    function getExtendedTrunksBill(&$grupos, $zapata_file='/etc/asterisk/zapata.conf')
    {
       $troncales=NULL;
       //leer el archivo /etc/zapata.conf para poder reemplazar para ZAP g#  con los respectivos canales
       $ultGrupo="";

       if (file_exists($zapata_file)){
           $contenido_archivo=file($zapata_file);
           foreach ($contenido_archivo as $linea){
               if (ereg("^(group|channel[[:space:]]*)=([[:space:]]*.*)",$linea,$regs)){
                   $regs_key=trim($regs[1]);
                   $regs_value=trim($regs[2]);
                   if ($regs_key=="group") $ultGrupo=$regs_value;
                   if ($regs_key=="channel"){
                       if (isset($ultGrupo)&&$ultGrupo!=""){
                           $channel=explode(',',$regs_value);
                           foreach ($channel as $item){
                              if ($item!=""){
                                   $item   = trim(preg_replace("%>| %","",$item));
                                   $range  = explode('-',$item);
                                   for ($i = min($range);$i<=max($range);$i++) {
                                        $canales[$ultGrupo][]=$i;
                                        $grupos[$i]=$ultGrupo;
                                   }
                              }
                           }
                       }
                   }
               }
           }
       }

       //reemplazo el id del grupo por el valor
       foreach ($this->getTrunksBill() as $trunkBill)
       {
           if (ereg("^ZAP/g([[:digit:]]+)",$trunkBill,$regs2))
           {
               $id_group=$regs2[1];
               if (isset($canales[$id_group])){
                  foreach($canales[$id_group] as $canal)
                   $troncales[]="ZAP/$canal";
               }
           }else
               $troncales[]=$trunkBill;
       }
        return $troncales;
    }

}


/**
* Procedimiento para obtener el listado de los trunks existentes. 
*
* @return array    Listado de trunks en el siguiente formato, o FALSE en caso de error:
*  array(
*      array(variable, valor),
*      ...
*  )
*/
function getTrunks($oDB)
{
    $arrResult = false;
    $arrTrunk = array();
    $sPeticionSQL =
            "SELECT * FROM globals ".
            "WHERE variable LIKE 'OUT\\\_%' ".
            "ORDER BY RIGHT( variable, LENGTH( variable ) - 4 )+0";
    $arrResult =& $oDB->fetchTable($sPeticionSQL);
    if (is_array($arrResult) && count($arrResult)>0) {
        foreach($arrResult as $key => $trunk)
                $arrTrunk[$key] = str_replace("AMP:","",$trunk);
        return $arrTrunk;
    }
    return false;
}

?>
