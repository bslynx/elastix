<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.2.0-29                                               |
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
  $Id: paloSantoExtensions.class.php,v 1.1 2012-07-18 11:50:00 Rocio Mera rmera@palosanto.com Exp $ */
    include_once "/var/www/html/libs/paloSantoACL.class.php";
	include_once "/var/www/html/libs/paloSantoAsteriskConfig.class.php";
	include_once "/var/www/html/libs/paloSantoPBX.class.php";
	global $arrConf;
class paloQueuePBX extends paloAsteriskDB{
    protected $code;
    protected $domain;
        
    function paloQueuePBX(&$pDB,$domain){
        parent::__construct($pDB);
        
        if(!preg_match("/^(([[:alnum:]-]+)\.)+([[:alnum:]])+$/", $domain)){
            $this->errMsg="Invalid domain format";
        }else{
            $this->domain=$domain;

            $result=$this->getCodeByDomain($domain);
            if($result==false){
                $this->errMsg .=_tr("Can't create a new instace of paloQueuePBX").$this->errMsg;
            }else{
                $this->code=$result["code"];
            }
        }
    }
    
    function validateQueuePBX(){
        //validamos que la instancia de paloDevice que se esta usando haya sido creda correctamente
        if(is_null($this->code) || is_null($this->domain))
            return false;
        return true;
    }
    
    function getQueues($limit=null,$offset=null){
        if($this->validateQueuePBX()==false){
            return false;
        }
        
        $arrParam=array($this->domain);
        $query="select name,queue_number,description,password_detail,monitor_format,strategy,timeout_detail,timeout from queue where organization_domain=?";
        $arrParam=array($this->domain);
        
        if(isset($limit) && isset($offset)){
            $query .=" limit ? offset ?";
            $arrParam[]=$limit;
            $arrParam[]=$offset;
        }
            
        $result=$this->_DB->fetchTable($query,true,$arrParam);
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }else
            return $result;
    }
    
    function getAllQueues($name=null,$limit=null,$offset=null){
        $arrParam=array();
        $search=$pagging="";
        if(isset($name) && $name!=""){
            $search="where name=?";
            $arrParam[]=$name;
        }
        
        if(isset($limit) && isset($offset)){
            $pagging=" limit ? offset ?";
            $arrParam[]=$limit;
            $arrParam[]=$offset;
        }
        
        $query="select name,queue_number,description,password_detail,monitor_format,strategy,timeout_detail,timeout from queue $search $pagging";
        $result=$this->_DB->fetchTable($query,true,$arrParam);
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }else
            return $result;
    }
    
    function getTotalQueues(){
        if($this->validateQueuePBX()==false){
            return false;
        }
            
        $query="select count(name) from queue where organization_domain=?";
        $result=$this->_DB->getFirstRowQuery($query,false,array($this->domain));
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }else
            return $result[0];
    }
    
    function getTotalAllQueues(){
        $query="select count(name) from queue";
        $result=$this->_DB->getFirstRowQuery($query,false);
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }else
            return $result[0];
    }
    
    function getQueueByName($name){
        if($this->validateQueuePBX()==false){
            return false;
        }
            
        $query="select * from queue where name=? and organization_domain=?";
        $result=$this->_DB->getFirstRowQuery($query,true,array($name,$this->domain));
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }else
            return $result;
    }
    
    function existQueue($name){
        $query="Select count(name) from queue where name=?";
        $result=$this->_DB->getFirstRowQuery($query,false,array($name));
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return true;
        }elseif($result[0]=="0"){
            return false;
        }else{
            $this->errMsg="Already exist a queue with the same name. ".$this->errMsg;
            return true;
        }
    }
    
    function insertQueueDB($arrProp){
        if($this->validateQueuePBX()==false){
            return false;
        }
        $code=$this->code;
        //valido que no exista otro dispositivo sip creado con el mismo nombre y que los cambios obligatorios esten seteados
        if(!isset($arrProp["name"])){
            $this->errMsg="Field queue can't be empty";
        }elseif(!$this->existQueue($code."_".$arrProp["name"])){
            $arrValues=array();
            $question="(";
            $Prop="(";
            $i=0;
            foreach($arrProp as $key => $value){
                if(isset($value) && $key!="_DB" && $key!="errMsg" && $value!="noset"){
                    switch ($key){
                        case "context":
                            $Prop .=$key.",";
                            $value = ($value!="")?$code."-".$value:null;
                            break;
                        case "name":
                            $Prop .=$key.",";
                            $value = $code."_".$value;
                            break;
                        default:
                            $Prop .=$key.",";
                            break;
                    }
                    $arrValues[$i]=$value;
                    $question .="?,";
                    $i++;
                }
            }

            $question=substr($question,0,-1).")";
            $Prop=substr($Prop,0,-1).")";

            $query="INSERT INTO queue $Prop value $question";
            if($this->executeQuery($query,$arrValues)){
                return true;
            }
        }
        return false;
    } 
    
    function updateQueueDB($arrProp){
        if($this->validateQueuePBX()==false){
            return false;
        }
        
        $arrQuery=array();
        $arrParam=array();
        $code=$this->code;
        if(!isset($arrProp["name"])){
            $this->errMsg="Field queue can't be empty";
        }elseif($this->existQueue($arrProp["name"])){
            foreach($arrProp as $key => $value){
                if(isset($value) && $key!="_DB" && $key!="errMsg" && $key!="name" && $key!="organization_domain" && $key!="queue_number"){
                    if($value=="" || $value=="noset"){
                        $value=NULL;
                    }
                    switch ($key){
                        case "context":
                            $arrQuery[]="$key=?";
                            $value = ($value!="")?$code."-".$value:null;
                            break;
                        default:
                            $arrQuery[]="$key=?";
                            break;
                    }
                    $arrParam[]=$value;
                }
            }
            
            if(count($arrQuery)>0){
                $query ="Update queue set ".implode(",",$arrQuery);
                $query .=" where name=? and organization_domain=?";
                $arrParam[]=$arrProp["name"];
                $arrParam[]=$this->domain;
                return $this->executeQuery($query,$arrParam);
            }else
                return true;
        }else{
            $this->errMsg=_tr("Queue doesn't exist");
        }
        
        return false;
    } 
    
    function createQueue($arrProp,$arrMembers){
        if($this->validateQueuePBX()==false){
            return false;
        }
        
        //validamos que el numero de la cola no este siendo usado dentro del plan de marcado
        if($this->existExtension($arrProp["name"],$this->domain)){
            return false;
        }
        
        if(empty($arrProp["name"])){
            $this->errMsg="Field queue can't be empty";
            return false;
        }
        
        $arrProp["organization_domain"]=$this->domain;
        $arrProp["queue_number"]=$arrProp["name"];
        
        if(isset($arrProp["musicclass"])){
            if($arrProp["musicclass"]=="ring"){
                $arrProp["ringing_detail"]="yes";
            }else{
                $arrProp["ringing_detail"]="no";
            }
            
            if($arrProp["musicclass"]=="ring" || $arrProp["musicclass"]=="inherit"){
                $arrProp["musicclass"]=null;
            }
        }
        
        if(isset($arrProp["monitor_format"])){
            if($arrProp["monitor_format"]=="no"){
                $arrProp["monitor_format"]=null;
            }
        }
        
        if(isset($arrProp["retry"])){
            if($arrProp["retry"]=="no_retry"){
                $arrProp["retry_detail"]="no";
                $arrProp["retry"]="0";
            }else{
                $arrProp["retry_detail"]="yes";
            }
        }
        
        if(isset($arrProp["skip_busy_detail"])){
            if($arrProp["skip_busy_detail"]=="0" || $arrProp["skip_busy_detail"]=="1"){
                $arrProp["ringinuse"]="yes";
            }else{
                $arrProp["ringinuse"]="no";
            }
        }
        
        if(isset($arrProp["min_announce_frequency"])){
            if($arrProp["min_announce_frequency"]=="0")
                $arrProp["min_announce_frequency"]=null;
        }
        
        if(isset($arrProp["announce_detail"])){
            if($arrProp["announce_detail"]=="none")
                $arrProp["announce_detail"]=null;
            else{
                //validamos que exista la grabacion y que pertenezca a dicha organizacion
                $file=$this->getFileRecordings($this->domain,$arrProp["announce_detail"]);
                if(is_null($file)){
                    $arrProp["announce_detail"]=null;
                }
            }
        }
        
        if(isset($arrProp["announce_caller_detail"])){
            if($arrProp["announce_caller_detail"]=="none")
                $arrProp["announce_caller_detail"]=null;
            else{
                //validamos que exista la grabacion y que pertenezca a dicha organizacion
                $file=$this->getFileRecordings($this->domain,$arrProp["announce_caller_detail"]);
                if(is_null($file)){
                    $arrProp["announce_caller_detail"]=null;
                }
            }
        }
        
        if(isset($arrProp["destination_detail"])){
            if($this->validateDestine($this->domain,$arrProp["destination_detail"])==false){
                $arrProp["destination_detail"]=null;
            }
        }
                
        if($this->insertQueueDB($arrProp)==false){
            $this->errMsg=_tr("Error trying created queue. ").$this->errMsg;
            return false;
        }
        
        $code=$this->code;
        //insertamos los miembros a la cola
        //los miembros pueden ser de tipo dinamico o estaticos
        //si son de tipo dinamico los tenemos que registrar en la base ast_db
        //si son de tipos estaticos se deben registrar en la tabla queue_member
        $queryExt="SELECT dial from extension where organization_domain=? and exten=?";
        $queryIn="INSERT INTO queue_member (queue_name,membername,interface,penalty,state_interface,exten) values(?,?,?,?,?,?)";
        if(isset($arrMembers["static_members"])){
            $arrMember=array();
            $members=explode("\n",$arrMembers["static_members"]);
            foreach($members as $value){
                if(preg_match("/([0-9]+)((,)([0-9]+)){0,1}/",$value,$match)){
                    $exten=$match[1];
                    $penalty=isset($match[4])?$match[4]:0;
                    $interfaz="Local/$exten@$code-from-queue/n";
                    //buscamos si el numero de extension introducio pertenece a una extension definida dentro del sistema
                    //en ese caso obtenemos la interfaz de la extension
                    $result=$this->_DB->getFirstRowQuery($queryExt,true,array($this->domain,$exten));
                    if($result!=false){
                        $state_int=$result["dial"];
                    }else{
                        $state_int="hint:$exten@$code-ext-local";
                    }
                    
                    $exito=$this->executeQuery($queryIn,array($code."_".$arrProp["name"],$exten,$interfaz,$penalty,$state_int,$exten));
                    if($exito==false){
                        $this->errMsg=_tr("Error insert queue_members. ").$this->errMsg;
                        return false;
                    }
                }
            }
        }
                
        $error=false;
        if(isset($arrMembers["dynamic_members"])){
            $astMang=AsteriskManagerConnect($errorM);
            if($astMang==false){
                $this->errMsg .=$errorM;
                return false;
            }
            $familia="QPENALTY/$code/$code"."_".$arrProp["name"]."/agents";
            $arrMember=array();
            $members=explode("\n",$arrMembers["dynamic_members"]);
            foreach($members as $value){
                if(preg_match("/([0-9]+)((,)([0-9]+)){0,1}/",$value,$match)){
                    $exten=$match[1];
                    $penalty=isset($match[4])?$match[4]:0;
                    $result=$astMang->database_put($familia,$exten,$penalty);
                    if(strtoupper($result["Response"]) == "ERROR"){
                        $error=true;
                        break;
                    }
                }
            }
        }
        
        $dinamic="no";
        if(isset($arrProp["restriction_agent"])){
            if($arrProp["restriction_agent"]=="yes")
                $dinamic="yes";
        }
        $result=$astMang->database_put("QPENALTY/$code/$code"."_".$arrProp["name"],"dynmemberonly",$dinamic);
        if(strtoupper($result["Response"]) == "ERROR"){
            $error=true;
        }
        
        if($error){
            $this->errMsg=_tr("Error setting queue_member. ").$this->errMsg;
            $result=$astMang->database_delTree("QPENALTY/$code/$code"."_".$arrProp["name"]);
            return false;
        }else
            return true;
    }
    
    function defaultOptions(){
        $arrProp=array();
        $arrProp["autofill"]="yes";
        $arrProp["servicelevel"]="60";
        $arrProp["musicclass"]="default";
        $arrProp["timeout"]="15";
        $arrProp["retry"]="5";
        $arrProp["timeoutpriority"]="app";
        $arrProp["announce_frequency"]="0";
        $arrProp["min_announce_frequency"]="0";
        $arrProp["announce_holdtime"]="no";
        $arrProp["announce_position"]="no";
        $arrProp["joinempty"]="yes";
        $arrProp["leavewhenempty"]="no";
        $arrProp["eventmemberstatus"]="yes";
        $arrProp["eventwhencalled"]="no";
        $arrProp["reportholdtime"]="no";
        $arrProp["cid_holdtime"]="no";
        $arrProp["restriction_agent"]="no";
        $arrProp["category"]="none";
        return $arrProp;
    }
    
    //funcion que devuelve un arreglo que cntiene una lista de los miembros estaticos y dinamicos
    //de una organizacion
    function getQueueMembers($name){
        //verificamos si la cola existe y pertenece a la organizacion
        $arrMember=array("statics"=>array(),"dynamics"=>array());
        if($this->existQueue($name)==false){
            $this->errMsg=_tr("Queue doesn't exist");
            return $arrMember;
        }
        
        
        //obtenemos la lista de miembros estaticos
        $query="SELECT exten,penalty from queue_member where queue_name=?";
        $result=$this->_DB->fetchTable($query,true,array($name));
        if($result===false){
            $this->errMsg=_tr($this->_DB->errMsg);
            return false;
        }else{
            foreach($result as $value){
                $tmp["exten"]=$value["exten"];
                $tmp["penalty"]=$value["penalty"];
                $arrMember["statics"][]=$tmp;
            }
        }
        
        $code=$this->code;
        $astMang=AsteriskManagerConnect($errorM);
        if($astMang==false){
            $this->errMsg .=$errorM;
            return false;
        }else{
            $result=$astMang->database_show("QPENALTY/$code/$name/agents");
            foreach($result as $key => $value){
                if(preg_match("/\\/QPENALTY\\/$code\\/$name\\/agents\\/([A-Za-z0-9]+)/",$key,$matches)){
                    $tmp["exten"]=$matches[1];
                    $tmp["penalty"]=$value[$key];
                    $arrMember["dynamics"][]=$tmp;
                }
            }
        }
        return $arrMember;
    }
    
    function updateQueue($arrProp,$arrMembers){
        if($this->validateQueuePBX()==false){
            return false;
        }
        
        if(empty($arrProp["name"])){
            $this->errMsg="Field queue can't be empty";
            return false;
        }
        
        if(isset($arrProp["musicclass"])){
            if($arrProp["musicclass"]=="ring"){
                $arrProp["ringing_detail"]="yes";
            }else{
                $arrProp["ringing_detail"]="no";
            }
            
            if($arrProp["musicclass"]=="ring" || $arrProp["musicclass"]=="inherit"){
                $arrProp["musicclass"]="";
            }
        }
        
        if(isset($arrProp["monitor_format"])){
            if($arrProp["monitor_format"]=="no"){
                $arrProp["monitor_format"]="";
            }
        }
        
        if(isset($arrProp["retry"])){
            if($arrProp["retry"]=="no_retry"){
                $arrProp["retry_detail"]="no";
                $arrProp["retry"]="0";
            }else{
                $arrProp["retry_detail"]="yes";
            }
        }
        
        if(isset($arrProp["skip_busy_detail"])){
            if($arrProp["skip_busy_detail"]=="0" || $arrProp["skip_busy_detail"]=="1"){
                $arrProp["ringinuse"]="yes";
            }else{
                $arrProp["ringinuse"]="no";
            }
        }
        
        if(isset($arrProp["min_announce_frequency"])){
            if($arrProp["min_announce_frequency"]=="0")
                $arrProp["min_announce_frequency"]="";
        }
        
        if(isset($arrProp["announce_detail"])){
            if($arrProp["announce_detail"]=="none")
                $arrProp["announce_detail"]="";
            else{
                //validamos que exista la grabacion y que pertenezca a dicha organizacion
                $file=$this->getFileRecordings($this->domain,$arrProp["announce_detail"]);
                if(is_null($file)){
                    $arrProp["announce_detail"]="";
                }
            }
        }
        
        if(isset($arrProp["announce_caller_detail"])){
            if($arrProp["announce_caller_detail"]=="none")
                $arrProp["announce_caller_detail"]="";
            else{
                //validamos que exista la grabacion y que pertenezca a dicha organizacion
                $file=$this->getFileRecordings($this->domain,$arrProp["announce_caller_detail"]);
                if(is_null($file)){
                    $arrProp["announce_caller_detail"]="";
                }
            }
        }
        
        if(isset($arrProp["destination_detail"])){
            if($this->validateDestine($this->domain,$arrProp["destination_detail"])==false){
                $arrProp["destination_detail"]="";
            }
        }else{
            $arrProp["destination_detail"]="";
        }
        
        
        if($this->updateQueueDB($arrProp)==false){
            $this->errMsg=_tr("Error trying updated queue. ").$this->errMsg;
            return false;
        }
        
        $code=$this->code;
        //actualizamos los miembros a la cola
        //los miembros pueden ser de tipo dinamico o estaticos
        //si son de tipo dinamico los tenemos que registrar en la base ast_db
        //si son de tipos estaticos se deben registrar en la tabla queue_member
        $queryExt="SELECT dial from extension where organization_domain=? and exten=?";
        $queryIn="INSERT INTO queue_member (queue_name,membername,interface,penalty,state_interface,exten) values(?,?,?,?,?,?)";
        $queryDel="Delete from queue_member where queue_name=?";
        if(isset($arrMembers["static_members"])){
            //borramos los miembros actuales de la cola
            $exito=$this->executeQuery($queryDel,array($arrProp["name"]));
            if($exito==false){
                return false;
            }
            //ingresamos los miembros seleccionado por el usuario
            $arrMember=array();
            $members=explode("\n",$arrMembers["static_members"]);
            foreach($members as $value){
                if(preg_match("/([0-9]+)((,)([0-9]+)){0,1}/",$value,$match)){
                    $exten=$match[1];
                    $penalty=isset($match[4])?$match[4]:0;
                    $interfaz="Local/$exten@$code-from-queue/n";
                    //buscamos si el numero de extension introducio pertenece a una extension definida dentro del sistema
                    //en ese caso obtenemos la interfaz de la extension
                    $result=$this->_DB->getFirstRowQuery($queryExt,true,array($this->domain,$exten));
                    if($result!=false){
                        $state_int=$result["dial"];
                    }else{
                        $state_int="hint:$exten@$code-ext-local";
                    }
                    
                    $exito=$this->executeQuery($queryIn,array($arrProp["name"],$exten,$interfaz,$penalty,$state_int,$exten));
                    if($exito==false){
                        $this->errMsg=_tr("Error insert queue_members. ").$this->errMsg;
                        return false;
                    }
                }
            }
        }
        
        $error=false;
        if(isset($arrMembers["dynamic_members"])){
            $astMang=AsteriskManagerConnect($errorM);
            if($astMang==false){
                $this->errMsg .=$errorM;
                return false;
            }
            $familia="QPENALTY/$code/".$arrProp["name"]."/agents";
            //borramos los miembros dinamicos actuales de la cola
            $result=$astMang->database_delTree("QPENALTY/$code/".$arrProp["name"]);
            //insertamos los nuevos miembros
            $arrMember=array();
            $members=explode("\n",$arrMembers["dynamic_members"]);
            foreach($members as $value){
                if(preg_match("/([0-9]+)((,)([0-9]+)){0,1}/",$value,$match)){
                    $exten=$match[1];
                    $penalty=isset($match[4])?$match[4]:0;
                    $result=$astMang->database_put($familia,$exten,$penalty);
                }
            }
        }
        
        $dinamic="no";
        if(isset($arrProp["restriction_agent"])){
            if($arrProp["restriction_agent"]=="yes")
                $dinamic="yes";
        }
        $result=$astMang->database_put("QPENALTY/$code/".$arrProp["name"],"dynmemberonly",$dinamic);
        
        return true;
    }
    
    function deleteQueue($qname){
        if($this->validateQueuePBX()==false){
            return false;
        }
        
        $query="SELECT count(name) from queue where name=? and organization_domain=?";
        $result=$this->_DB->getFirstRowQuery($query,false,array($qname,$this->domain));
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }else{
            if($result[0]=0){
                $this->errMsg=_tr("Queue doesn't exist");
                return false;
            }
        }
    
        //si existe la cola borramos los miembros estaticos que pertencen a esa cola
        $query="DELETE from queue_member where queue_name=?";
        $result=$this->executeQuery($query,array($qname));
        if($result){
            if($result==false){
                $this->errMsg=_tr("Error deleting Queue Members. ").$this->errMsg;
                return false;
            }
        }
        
        $query="DELETE from queue where name=? and organization_domain=?";
        $result=$this->executeQuery($query,array($qname,$this->domain));
        if($result){
            if($result==false){
                $this->errMsg=_tr("Error deleting Queue. ").$this->errMsg;
                return false;
            }
        }
        
        $code=$this->code;
        //borramos los datos de la cola de la base ast_db
        $astMang=AsteriskManagerConnect($errorM);
        if($astMang==false){
            $this->errMsg .=$errorM;
            return false;
        }else{
            $result=$astMang->database_delTree("QPENALTY/$code/$qname");
            if(strtoupper($result["Response"]) == "ERROR"){
                $this->errMsg .=_tr("Error deleting estatic members from queue. ");
                return false;
            }
        }
        return true;
    }
    
    function createDialPlanQueue(&$arrFromInt){
        if($this->validateQueuePBX()==false){
            return false;
        }
    
        $arrQ=array();
        $arrQcontext=array();
        //obtenemos una lista de las colas de cad organizacion
        $query="SELECT name,monitor_format,musicclass,ringing_detail,timeout_detail,password_detail,cid_prefix_detail,cid_holdtime_detail,alert_info_detail,announce_caller_detail,retry_detail,destination_detail,restriction_agent,calling_restriction,skip_busy_detail,queue_number from queue where organization_domain=?";
        $result=$this->_DB->fetchTable($query,true,array($this->domain));
        if($result===false){
            $this->errMsg=_tr("Error creating dialplan for queues. ").$this->_DB->errMsg; 
            return false;
        }else{
            foreach($result as $value){
                $exten=$value["queue_number"];
                $arrQ[]=new paloExtensions($exten,new ext_macro($this->code."-user-callerid"),"1");
                $arrQ[]=new paloExtensions($exten,new ext_answer());
                //se desea mostrar en el callerid de la llamada el tiempo que la persona a estado esperando
                //en al cola
                if($value["cid_holdtime_detail"]=="yes"){
                    $arrQ[]=new paloExtensions($exten, new ext_execif('$["${QUEUEWAIT}" = ""]', 'Set', '__QUEUEWAIT=${EPOCH}'));
                }
               
                $call_restriction=(isset($value['calling_restriction']))?$value['calling_restriction']:"0";
                //se bloquea que las llamadas sean redirigidas a los voicemails de losa agentes o extensiones
                if($call_restriction != '2') {
                    $arrQ[]=new paloExtensions($exten,new ext_setvar('__BLKVM_OVERRIDE', 'BLKVM/'.$this->code.'/${EXTEN}/${CHANNEL}'));
                    $arrQ[]=new paloExtensions($exten,new ext_setvar('__BLKVM_BASE', '${EXTEN}'));
                    $arrQ[]=new paloExtensions($exten,new ext_setvar('DB(${BLKVM_OVERRIDE})', 'TRUE'));
                    $arrQ[]=new paloExtensions($exten,new ext_execif('$["${REGEX("(M[(]'.$this->code.'-auto-blkvm[)])" ${'.$this->code.'_DIAL_OPTIONS})}" != "1"]', 'Set', '_'.$this->code.'_DIAL_OPTIONS=${'.$this->code.'_DIAL_OPTIONS}M('.$this->code.'-auto-blkvm)'));
                }
                $arrQ[]=new paloExtensions($exten,new ext_setvar('__NODEST', '${EXTEN}'));
                
                //se le quiere poner de prefijo un callerid a las llamadas de la cola
                if (isset($value["cid_prefix_detail"]) && $value["cid_prefix_detail"]!=''){
                    $arrQ[]=new paloExtensions($exten,new ext_gotoif('$["foo${RGPREFIX}" = "foo"]', 'REPCID'));
                    $arrQ[]=new paloExtensions($exten,new ext_gotoif('$["${RGPREFIX}" != "${CALLERID(name):0:${LEN(${RGPREFIX})}}"]', 'REPCID'));
                    $arrQ[]=new paloExtensions($exten,new ext_noop('Current RGPREFIX is ${RGPREFIX}....stripping from Caller ID'));
                    $arrQ[]=new paloExtensions($exten,new ext_setvar('_RGPREFIX', ''));
                    $arrQ[]=new paloExtensions($exten,new ext_noop('CALLERID(name) is ${CALLERID(name)}'),"n",'REPCID');
                    $arrQ[]=new paloExtensions($exten,new ext_setvar('_RGPREFIX', $value["cid_prefix_detail"]));
                    $arrQ[]=new paloExtensions($exten,new ext_setvar('CALLERID(name)','${RGPREFIX}${CALLERID(name)}'));
                }
                
                if(isset($value["alert_info_detail"]) && $value["alert_info_detail"]!=""){
                    $arrQ[]=new paloExtensions($exten,new ext_setvar('__ALERT_INFO', str_replace(';', '\;', $alertinfo)));
                }
                
                //se desean graban las llamadas que llegan a esta cola
                if(isset($value["monitor_format"])){
                    $arrQ[]=new paloExtensions($exten,new ext_setvar('MONITOR_FILENAME','/var/spool/asterisk/monitor/'.$this->domain.'/q${EXTEN}-${STRFTIME(${EPOCH},,%Y%m%d-%H%M%S)}-${UNIQUEID}'));
                }
                
                //si se desea reproducir un anuncio a la persona que llama antes de unirla a la cola
                if(isset($value["announce_caller_detail"]) && $value["announce_caller_detail"]!=""){
                    $file=$this->getFileRecordings($this->domain,$value["announce_caller_detail"]);
                    if(isset($file))
                        $arrQ[]=new paloExtensions($exten, new ext_playback($file));
                }
                
                //timeout -> maximo tiempo que una llamada puede estar en la cola
                //las opciones que se le pueden enviar a la cola
                // n ->  no retries on the timeout; will exit this application and go to the next step
                // t -> allow the called user to transfer the calling user.
                // r -> ring instead of playing MOH.
                //Queue(queuename,[options],[optionalurl],[announceoverride],[timeout])
                $options = 't';
                if ($value['ringing_detail'] == "yes") {
                    $options .= 'r';
                }
                if ($value['retry_detail'] == 'no'){
                    $options .= 'n';
                }
                
                $announceoverride="";
                //si se desea reproducir un anuncio a los agentes de la cola
                if(isset($value["announce_detail"]) && $value["announce_detail"]!=""){
                    $file=$this->getFileRecordings($this->domain,$value["announce_detail"]);
                    $announceoverride=(isset($file))?$file:"";
                }
                $timeout=isset($value["timeout_detail"])?$value["timeout_detail"]:"";
                
                if ($value['skip_busy_detail'] == 1 || $value['skip_busy_detail'] == 2 ) {
                    $arrQ[]=new paloExtensions($exten, new ext_setvar('__CWIGNORE', 'TRUE'));
                }
                
                if ($call_restriction=="1") {
                    $arrQ[]=new paloExtensions($exten, new ext_setvar('__CFIGNORE', 'TRUE'));
                    $arrQ[]=new paloExtensions($exten, new ext_setvar('__FORWARD_CONTEXT', 'block-cf'));
                }
                
                $arrQ[]=new paloExtensions($exten, new ext_queue($value["name"],$options,'',$announceoverride,$timeout));
                //la llamada salio de la cola
                //dejamos todos los valores como estaban antes de entrar a la cola
                $arrQ[]=new paloExtensions($exten,new ext_setvar('__NODEST', ''));
                if($value['calling_restriction'] != '2') {
                    $arrQ[]=new paloExtensions($exten,new ext_dbdel('${BLKVM_OVERRIDE}'));
                }
                
                if ($value['skip_busy_detail'] == 1 || $value['skip_busy_detail'] == 2 ) {
                    $arrQ[]=new paloExtensions($exten, new ext_setvar('__CWIGNORE', ''));
                }
                
                if ($call_restriction=="1") {
                    $arrQ[]=new paloExtensions($exten, new ext_setvar('__CFIGNORE', ''));
                    $arrQ[]=new paloExtensions($exten, new ext_setvar('__FORWARD_CONTEXT', 'from-internal'));
                }
                
                if(isset($value["destination_detail"])){
                    $goto=$this->getGotoDestine($this->domain,$value["destination_detail"]);
                    if($goto!=false)
                        $arrQ[]=new paloExtensions($exten, new extension("Goto(".$goto.")"));
                }
                
                //creamos los shortcuts para que los agentes dinamicos se puedan loguear y desloquear a la cola
                //si se tiene como restriccion solo extensiones entonces se manda como parametro EXTEN al macro agent-add para que se haga esa validacion
                if($call_restriction == '2') {
                    $arrQ[]=new paloExtensions($exten."*", new ext_macro($this->code.'-agent-add',$value["name"].",".$value['password_detail'].",EXTEN"),"1");
                }else{
                    $arrQ[]=new paloExtensions($exten."*", new ext_macro($this->code.'-agent-add',$value["name"].",".$value['password_detail']),"1");
                }
                $arrQ[]=new paloExtensions($exten."**", new ext_macro($this->code.'-agent-del',$value["name"]),"1");
                $arrQcontext[$exten]=$call_restriction;
            }
            $arrQ[]=new paloExtensions("h",new ext_macro($this->code.'-hangupcall'),"1");
            
            //creamos el contexto ext-queue
            $contextQ=new paloContexto($this->code,"ext-queues");
            if($contextQ===false){
                $contextQ->errMsg="ext-queues. Error: ".$contextQ->errMsg;
            }else{
                $contextQ->arrExtensions=$arrQ;
                $arrFromInt[]["name"]="ext-queues";
            }
            
            //creamos el contexto from-queue
            //este es el contexto que se usa para llamar a los agentes de la cola tomando el cuenta 
            //las restricciones de los agentes
            $arrFromQueue=array();
            foreach($arrQcontext as $key => $value){
                switch($value){
                    case "1":
                        $agent_context = $this->code."-from-queue-exten-internal";
                        break;
                    case "2":
                        $agent_context = $this->code."-from-queue-exten-only";
                        break;
                    default:
                        $agent_context = $this->code.'-from-internal';
                        break;
                }
                $arrFromQueue[]=new paloExtensions($key, new ext_goto('1','${QAGENT}',$agent_context),"1");
            }
            $arrFromQueue[]=new paloExtensions("_.", new ext_setvar('QAGENT','${EXTEN}'),"1");
            $arrFromQueue[]=new paloExtensions("_.", new ext_goto('1','${NODEST}'));
            
            //creamos el contexto from-queue
            $fromQueue=new paloContexto($this->code,"from-queue");
            if($fromQueue===false){
                $fromQueue->errMsg="from-queue. Error: ".$fromQueue->errMsg;
            }else{
                $fromQueue->arrExtensions=$arrFromQueue;
            }
            
             //creamos el contexto from_queue_exten_internal
            $fromQueueExtenInt[]=new paloExtensions('foo',new ext_noop('bar'),"1"); 
            $fromQueue2=new paloContexto($this->code,"from-queue-exten-internal");
            if($fromQueue2===false){
                $fromQueue2->errMsg="from-queue-exten-internal. Error: ".$fromQueue2->errMsg;
            }else{
                $fromQueue2->arrExtensions=$fromQueueExtenInt;
                $fromQueue2->arrInclude=array(array("name"=>"from-queue-exten-only","name"=>'from-internal'));
            }
            
             //creamos el contexto from_queue_exten_only
             /* create a context, from-queue-exten-only, that can be used for queues that want behavir similar to
             * ringgroup where only the agent's phone will be rung, no follow-me will be pursued.
             */
            $arrOnly=array(); 
            $arrDev=$this->getAllDevice($this->domain);
            foreach($arrDev as $value){
                $arrOnly[]=new paloExtensions($value["exten"],new ext_setvar('RingGroupMethod', 'none'),"1");
                $arrOnly[]=new paloExtensions($value["exten"],new ext_macro($this->code.'-record-enable',$value["exten"].",IN"));
                $arrOnly[]=new paloExtensions($value["exten"],new ext_macro($this->code.'-dial-one',',${'.$this->code.'_DIAL_OPTIONS},'.$value["exten"]));
                $arrOnly[]=new paloExtensions($value["exten"],new ext_hangup());
            }
            $arrOnly[]=new paloExtensions("h",new ext_macro($this->code.'-hangupcall'),"1");
            
            $fromQueue3=new paloContexto($this->code,"from-queue-exten-only");
            if($fromQueue3===false){
                $fromQueue3->errMsg="from-queue-exten-only. Error: ".$fromQueue3->errMsg;
            }else{
                $fromQueue3->arrExtensions=$arrOnly;
            }
            
            return array($contextQ,$fromQueue,$fromQueue2,$fromQueue3);
        }
    }
    
}
?>