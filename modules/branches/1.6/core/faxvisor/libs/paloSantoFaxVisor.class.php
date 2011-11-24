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
  $Id: paloSantoFaxVisor.class.php,v 1.1.1.1 2008/12/09 18:00:00 aflores Exp $ */

/*-
CREATE TABLE info_fax_recvq
(
    id           INTEGER  PRIMARY KEY,
    pdf_file    varchar(255)   NOT NULL DEFAULT '',
    modemdev     varchar(255)   NOT NULL DEFAULT '',
    status       varchar(255)   NOT NULL DEFAULT '',
    commID       varchar(255)   NOT NULL DEFAULT '',
    errormsg     varchar(255)   NOT NULL DEFAULT '',
    company_name varchar(255)   NOT NULL DEFAULT '',
    company_fax  varchar(255)   NOT NULL DEFAULT '',
    fax_destiny_id       INTEGER NOT NULL DEFAULT 0,
    date     timestamp  NOT NULL ,
    FOREIGN KEY (fax_destiny_id)   REFERENCES fax(id)
);
*/

class paloFaxVisor
{
    var $_db;
    var $errMsg;

    function paloFaxVisor()
    {
        global $arrConf;
        
        //instanciar clase paloDB
        $pDB = new paloDB("sqlite3:///$arrConf[elastix_dbdir]/fax.db");
    	if (!empty($pDB->errMsg)) {
            $this->errMsg = $pDB->errMsg;
    	} else{
       		$this->_db = $pDB;
    	}
    }

    function obtener_faxes($company_name, $company_fax, $fecha_fax, $offset, $cantidad, $type)
    {
        if (empty($company_name)) $company_name = NULL;
        if (empty($company_fax)) $company_fax = NULL;
        if (empty($fecha_fax)) $fecha_fax = NULL;
        if (empty($type)) $type = NULL;
        if (!is_null($fecha_fax) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_fax)) {
            $this->errMsg = '(internal) Invalid date for query, expected yyyy-mm-dd';
        	return NULL;
        }
        if (!ctype_digit("$offset") || !ctype_digit("$cantidad")) {
        	$this->errMsg = '(internal) Invalid offset/limit';
            return NULL;
        }
        if (!is_null($type)) {
        	$type = strtolower($type);
            if (!in_array($type, array('in', 'out'))) $type = NULL;
        }
        
        $sPeticionSQL = 
            'SELECT r.id, r.pdf_file, r.modemdev, r.commID, r.errormsg, '.
                'r.company_name, r.company_fax, r.fax_destiny_id, r.date, '.
                'r.type, r.faxpath, f.name destiny_name, f.extension destiny_fax '.
            'FROM info_fax_recvq r, fax f '.
            'WHERE ';
        $listaWhere = array('f.id = r.fax_destiny_id');
        $paramSQL = array();
        if (!is_null($company_name)) {
        	$listaWhere[] = 'company_name LIKE ?';
            $paramSQL[] = "%$company_name%";
        }
        if (!is_null($company_fax)) {
            $listaWhere[] = 'company_fax LIKE ?';
            $paramSQL[] = "%$company_fax%";
        }
        if (!is_null($fecha_fax)) {
            $listaWhere[] = 'date BETWEEN ? AND ?';
            $paramSQL[] = "$fecha_fax 00:00:00";
            $paramSQL[] = "$fecha_fax 23:59:59";
        }
        if (!is_null($type)) {
        	$listaWhere[] = 'type = ?';
            $paramSQL[] = $type;
        }
        $sPeticionSQL .= implode(' AND ', $listaWhere).' ORDER BY r.id desc LIMIT ? OFFSET ?';
        $paramSQL[] = $cantidad; $paramSQL[] = $offset;
        
        $arrReturn = $this->_db->fetchTable($sPeticionSQL, TRUE, $paramSQL);
        if ($arrReturn == FALSE) {
            $this->errMsg = $this->_db->errMsg;
            return array();
        }
        return $arrReturn;
    }

    function obtener_fax($idFax)
    {
        $arrReturn = $this->_db->getFirstRowQuery(
            'SELECT * FROM info_fax_recvq WHERE id = ?', 
            TRUE, array($idFax));
        if ($arrReturn == FALSE){
            $this->errMsg = $this->_db->errMsg;
            return array();
        }
        return $arrReturn;
    }

    function obtener_cantidad_faxes($company_name, $company_fax, $fecha_fax, $type)
    {
        if (empty($company_name)) $company_name = NULL;
        if (empty($company_fax)) $company_fax = NULL;
        if (empty($fecha_fax)) $fecha_fax = NULL;
        if (empty($type)) $type = NULL;
        if (!is_null($fecha_fax) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_fax)) {
            $this->errMsg = '(internal) Invalid date for query, expected yyyy-mm-dd';
            return NULL;
        }
        if (!is_null($type)) {
            $type = strtolower($type);
            if (!in_array($type, array('in', 'out'))) $type = NULL;
        }

        $sPeticionSQL = 'SELECT COUNT(*) cantidad FROM info_fax_recvq';
        $listaWhere = array();
        $paramSQL = array();
        if (!is_null($company_name)) {
            $listaWhere[] = 'company_name LIKE ?';
            $paramSQL[] = "%$company_name%";
        }
        if (!is_null($company_fax)) {
            $listaWhere[] = 'company_fax LIKE ?';
            $paramSQL[] = "%$company_fax%";
        }
        if (!is_null($fecha_fax)) {
            $listaWhere[] = 'date BETWEEN ? AND ?';
            $paramSQL[] = "$fecha_fax 00:00:00";
            $paramSQL[] = "$fecha_fax 23:59:59";
        }
        if (!is_null($type)) {
            $listaWhere[] = 'type = ?';
            $paramSQL[] = $type;
        }
        if (count($listaWhere) > 0) $sPeticionSQL .= ' WHERE '.implode(' AND ', $listaWhere);
        
        $arrReturn = $this->_db->getFirstRowQuery($sPeticionSQL, TRUE, $paramSQL);

        if ($arrReturn == FALSE) {
            $this->errMsg = $this->_db->errMsg;
            return array();
        }
        return $arrReturn['cantidad'];
    }

    function deleteInfoFaxFromDB($pdfFileInfoFax)
    {
        $bExito = $this->_db->genQuery(
            'DELETE FROM info_fax_recvq WHERE pdf_file = ?',
            array($pdfFileInfoFax));
        if (!$bExito) {
            $this->errMsg = $this->_db->errMsg;
            return false;
        }
        return true;
    }

    function updateInfoFaxFromDB($idFax, $company_name, $company_fax)
    {
        if (!$this->_db->genQuery(
            'UPDATE info_fax_recvq SET company_name = ?, company_fax = ? WHERE id = ?', 
            array($company_name, $company_fax, $idFax))) {
            $this->errMsg = $this->_db->errMsg;
            return false;
        }
        return true;
    }

    private function updateFileFaxSend($oldfile, $newfile)
    {
        if (!$this->_db->genQuery(
            'UPDATE info_fax_recvq SET pdf_file = ? WHERE pdf_file = ?',
            array($newfile, $oldfile))) {
            $this->errMsg = $this->_db->errMsg;
            return false;
        }
        return true;
    }

    function testFile($file)
    {
        $temp_file = "";
        $return = "";
        exec("sudo -u root chmod 777 /var/spool/hylafax/docq/$file",$arrConsole,$flagStatus);
        if($flagStatus==0){
			exec("ls /var/spool/hylafax/docq/$file",$arrConsole2,$flagStatus);
            if($flagStatus == 0){ //existe por lo tanto ya esta completo
                $temp_file = basename($arrConsole2[0],".ps");
                if($this->updateFileFaxSend($file, $temp_file.".pdf"))
                    $return = $temp_file.".pdf";
                else
                    $return = "";
            }
        }
        exec("sudo -u root chmod 740 /var/spool/hylafax/docq/$file",$arrConsole,$flagStatus);
        return $return;
    }

    function deleteInfoFaxFromPathFile($path_file)
    {
        $file = "/var/www/faxes/$path_file/fax.pdf";
        return file_exists($file) ? unlink($file) : TRUE;
    }

    function getPathByPdfFile($pdfFile)
    {
        $arrReturn = $this->_db->getFirstRowQuery(
            'SELECT faxpath FROM info_fax_recvq WHERE pdf_file = ?',
            TRUE, array($pdfFile));
        if (!is_array($arrReturn)) {
            $this->errMsg = $this->_db->errMsg;
        	return '';
        }
        return (count($arrReturn) > 0) ? $arrReturn['faxpath'] : '';
    }
}
?>