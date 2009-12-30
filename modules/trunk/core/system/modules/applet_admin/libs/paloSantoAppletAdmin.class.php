<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.0-7                                               |
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
  $Id: paloSantoprueba_applets.class.php,v 1.1 2009-12-28 06:12:49 Bruno bomv.27 Exp $ */
class paloSantoAppletAdmin {
    var $errMsg;

    function paloSantoAppletAdmin()
    {
    }

    function getApplets_User($user)
    {
        global $arrConf;
        $dsn = "sqlite3:///$arrConf[elastix_dbdir]/dashboard.db";
        $pDB  = new paloDB($dsn);
        if($user!= "admin") $user="no_admin";

        $query = "select 
                    dau.id, a.name, ifnull(aau.id,0) activated, ifnull(aau.order_no,0) order_no
                  from 
                    applet a 
                        inner join 
                    default_applet_by_user dau on a.id=dau.id_applet 
                        left join 
                    activated_applet_by_user aau on dau.id = aau.id_dabu 
                  where 
                    dau.username='$user' 
                  order by dau.id asc;";

        $result=$pDB->fetchTable($query, true);

        if($result==FALSE){
            $this->errMsg = $pDB->errMsg;
            return array();
        } 
        return $result;
    }

    function setApplets_User($arrIDs_DAU, $user)
    {
        global $arrConf;
        $dsn = "sqlite3:///$arrConf[elastix_dbdir]/dashboard.db";
        $pDB  = new paloDB($dsn);

        if(is_array($arrIDs_DAU) & count($arrIDs_DAU)>0){
            if($user!= "admin") $user="no_admin";

            $pDB->beginTransaction();
            // Parte 1: Elimino todas las actuales
            $query1 = " delete from activated_applet_by_user 
                        where id_dabu in (select id from default_applet_by_user where username='$user')";
            $result1=$pDB->genQuery($query1);

            if($result1==FALSE){
                $this->errMsg = $pDB->errMsg;
                $pDB->rollBack();
                return false;
            }

            // Parte 2: Inserto todas las checked
            foreach($arrIDs_DAU as $key => $value){
                $query2 = "insert into activated_applet_by_user (id_dabu, order_no) values ($value,".($key+1).")";
                $result2=$pDB->genQuery($query2);

                    if($result2==FALSE){
                        $this->errMsg = $pDB->errMsg;
                        $pDB->rollBack();
                        return false;
                    }
            }
            $pDB->commit();
        }
        return true;
    }
}
?>