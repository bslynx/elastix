<?php

    require_once "libs/paloSantoForm.class.php";
    include_once "libs/paloSantoQueue.class.php";
    include_once "libs/paloSantoConfig.class.php";
    require_once "libs/paloSantoDB.class.php";
    require_once "libs/paloSantoGrid.class.php";
    require_once "libs/xajax/xajax.inc.php";

    function _moduleContent(&$smarty,$module_name) {

        require_once "modules/$module_name/configs/default.config.php";
        require_once "modules/$module_name/libs/paloSantoDataQueue.class.php";

        $Language=get_language();
        $script_dir=dirname($_SERVER['SCRIPT_FILENAME']);
        $arrLangModule_file="modules/$module_name/lang/$Language.lang";

        if (file_exists("$script_dir/$arrLangModule_file")) {
            include_once($arrLangModule_file);
        } else {
            include_once("modules/$module_name/lang/en.lang");
        }
	global $arrLangModule;	
	$arrLangModule=$arrLangModule;
        $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
        $templates_dir=(isset($config['templates_dir']))?$config['templates_dir']:'themes';
        $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$config['theme'];
        $relative_dir_rich_text = "modules/$module_name/".$templates_dir.'/'.$config['theme'];

        $smarty->assign("BTN_SELECT_QUEUE",$arrLangModule['Select Queue']);

        $smarty->assign("SAVE",$arrLangModule['guardar']);
        $smarty->assign("LABEL_QUEUE",$arrLangModule['label_choice']);
        $smarty->assign("EDIT",$arrLangModule['edit']);
        $smarty->assign("DESACTIVATE",$arrLangModule['dasactivate']);
        $smarty->assign("CONFIRM_CONTINUE",$arrLangModule['confirm continue']);
        $smarty->assign("CANCEL",$arrLangModule['cancelar']);
        $smarty->assign("relative_dir_rich_text", $relative_dir_rich_text);
        $smarty->assign("APPLY_CHANGES",$arrLangModule['apply_changes']);
        $smarty->assign("QUEUE",$arrLangModule['Queue']);

        $smarty->assign("LABEL_SELECT",$arrLangModule["Select Queue"]);

        $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
        $config = $pConfig->leer_configuracion(false);

        $dsn = $config['AMPDBENGINE']['valor'] . "://" . $config['AMPDBUSER']['valor'] . ":" . $config['AMPDBPASS']['valor'] . "@" . $config['AMPDBHOST']['valor'] . "/asterisk";

        $oDB = new paloDB($dsn);
        $oQueue = new paloQueue($oDB);
        $arrQueues = $oQueue->getQueue();

        $pDB = new paloDB($cadena_dsn);
        $arrValor = array();
        if (!is_object($pDB->conn) || $pDB->errMsg!="") {
            $smarty->assign("mb_message", $arrLangModule["Error when connecting to database"]." ".$pDB->errMsg);
        }else {
            $arrDataQueues = array();
            $oData = new DataQueue($pDB);
            $i = 0;
            if (is_array($arrQueues)) {
                foreach($arrQueues as $queue) {
                    
                    if( !$oData->esQueueUsado( $queue[0] ) ) {
                        $i++;
                        $arrValor[$i][0] = $queue[0];
                        $arrValor[$i][1] = $queue[1];
                    }
                }
            }

            $selectOp = $oData->crearSelect($arrValor); 
            $smarty->assign("INPUT_SELECT",$selectOp);
            $form_campos = array(
                "script" => array(
                    "LABEL"                  => $arrLangModule["Script"],
                    "REQUIRED"               => "yes",
                    "INPUT_TYPE"             => "TEXT",
                    "INPUT_EXTRA_PARAM"      => "",
                    "VALIDATION_TYPE"        => "",
                    "VALIDATION_EXTRA_PARAM" => ""
                ),
            );

            $oForm = new paloForm($smarty,$form_campos);

            $xajax = new xajax();
            $xajax->registerFunction("desactivar_queue");
            $xajax->processRequests();
            $smarty->assign("xajax_javascript",$xajax->printJavascript("libs/xajax/"));

            if(isset($_POST['submit_select_queue'])) { 
                $contenido = newQueue($arrLangModule,$oForm,$local_templates_dir);
            }else if (isset($_POST['save'])) {
                $contenido = guardarQueue($smarty,$arrLangModule,$oForm,$local_templates_dir,$oData);
            }else if (isset($_POST['edit'])) {
                $contenido = editQueue($smarty,$arrLangModule,$oForm,$local_templates_dir,$oData);
            }else if (isset($_POST['apply_changes'])) { 
                $contenido = updateQueue($smarty,$arrLangModule,$oForm,$local_templates_dir,$oData);
            }else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action']=="view") {
                $contenido = viewQueue($smarty,$arrLangModule,$oForm,$local_templates_dir,$oData);
            }else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action']=="activar") {
                $contenidoModulo = activar_queue($smarty,$oData);
            }else {
                $contenido = listadoQueue($smarty,$arrLangModule,$oForm,$local_templates_dir,$oData,$module_name,$_POST);
            }
        }
        return $contenido;
    }

    function newQueue($arrLangModule,$oForm,$local_templates_dir) {

        $contenido = $oForm->fetchForm("$local_templates_dir/form.tpl",$arrLangModule["Select Queue"],null);
        return $contenido;
    }

    function guardarQueue($smarty,$arrLangModule,$oForm,$local_templates_dir,$oData) {
        $valido = true;

        if (  !isset($_POST['select_queue'] ) || $_POST['select_queue']=="-1"  ) {
            $smarty->assign( "mb_title", "Error" );
            $smarty->assign( "mb_message", "Debe seleccionar una cola" );
            $valido = false;
        }
        if ( !isset($_POST['rte_script'] ) || $_POST['rte_script']==""  ) {
            $smarty->assign( "mb_title", "Error" );
            $smarty->assign( "mb_message", "Debe ingresar un texto" );
            $valido = false;
        }
        if($valido) {
            if(!$oData->guardarQueue($_POST['select_queue'],$_POST['rte_script'])) { 
                $smarty->assign( "mb_title", "Error" );
                $smarty->assign( "mb_message", "Error de insert" );
            }else {
                header("Location: ?menu=queues");
            }
        }
        $contenido = $oForm->fetchForm("$local_templates_dir/form.tpl",$arrLangModule["Select Queue"],null);
        return $contenido;
    }

    function editQueue($smarty,$arrLangModule,$oForm,$local_templates_dir,$oData) {

        $arrTmp=array();
        $oForm->setEditMode();

        $arrQueue = $oData->getQueues($_GET['id']);

        $smarty->assign("LABEL_SELECTED",$arrQueue[0]['queue']);

        $arrTmp['script']    = "";

        $smarty->assign("rte_script",adaptar_formato_rte($arrQueue[0]['script']));
        $smarty->assign("queue", $arrQueue[0]['queue']);
        $contenido = $oForm->fetchForm("$local_templates_dir/form.tpl",$arrLangModule["Edit Queue"],$arrTmp);
        return $contenido;
    }

    function updateQueue($smarty,$arrLangModule,$oForm,$local_templates_dir,$oData) {

        $valido = true;
        $arrTmp=array();

        $oForm->setEditMode();

        if (!isset($_POST['rte_script'] ) || $_POST['rte_script']==""  ) {
            $smarty->assign( "mb_title", "Error" );
            $smarty->assign( "mb_message", "Debe ingresar un texto" );
            $valido = false;
        }
        if($valido){

            $arrQueue = $oData->getQueues($_GET['id']);
            $smarty->assign("LABEL_SELECTED",$arrQueue[0]['queue']);

            if(!$oData->guardarQueue($_POST['queue'],$_POST['rte_script'])) { 
                $smarty->assign( "mb_title", "Error" );
                $smarty->assign( "mb_message", "Error de insert" );
            }else {
                $smarty->assign("rte_script",adaptar_formato_rte($_POST['rte_script']));
                header("Location: ?menu=queues");
            }
        }

        $contenido = $oForm->fetchForm("$local_templates_dir/form.tpl",$arrLangModule["Edit Queue"],null);
        return $contenido;

    }

    function viewQueue($smarty,$arrLangModule,$oForm,$local_templates_dir,$oData) {

        $oForm->setViewMode(); 

        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            return false;
        }

        $arrQueue = $oData->getQueues($_GET['id']);

        $smarty->assign("LABEL_SELECTED",$arrQueue[0]['queue']);

        $arrTmp=array();
        $arrTmp['script'] = $arrQueue[0]['script'];

        $smarty->assign("id_queue", $_GET['id']);

        $contenido = $oForm->fetchForm("$local_templates_dir/form.tpl",$arrLangModule["View Queue"],$arrTmp);
        return $contenido;

    }

    function activar_queue($smarty,$oData) {

        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            return false;
        }

        if($oData->activar_queue($_GET['id'],'A')) {
            header("Location: ?menu=queues&cbo_estado=I");
        }
        else
        {
            $smarty->assign("mb_title",$arrLangModule['Activate Error']);
            $smarty->assign("mb_message",$arrLangModule['Error when Activating the Queue']);
        }

    }

    function listadoQueue($smarty,$arrLangModule,$oForm,$local_templates_dir,$oData,$module_name,$_POST) {

        if (isset($_GET['cbo_estado']) && $_GET['cbo_estado']=="I") {
            $_POST['cbo_estado'] = 'I';
        } else if (!isset($_POST['cbo_estado']) || $_POST['cbo_estado']=="") {
            $_POST['cbo_estado'] = "A";
        }

        $arrDataQueues = $oData->getQueues(NULL, $_POST['cbo_estado']);
        $end = count($arrDataQueues);
        $arrGrid = array("title"    => $arrLangModule["Queue List"],
            "icon"     => "images/list.png",
            "width"    => "99%",
            "start"    => ($end==0) ? 0 : 1,
            "end"      => $end,
            "total"    => $end,
            "columns"  => array(0 => array("name"       => $arrLangModule["Name Queue"],
                                        "property1"  => ""),
                            1 => array("name"       => $arrLangModule["Status"], 
                                       "property1"  => ""),
                            2 => array("name"       => $arrLangModule["Options"], 
                                       "property1"  => "" ))); 
    
        $estados = array("all"=>$arrLangModule['all'], "A"=>$arrLangModule['active'], "I"=>$arrLangModule['inactive']);
        $combo_estados = "<select name='cbo_estado' id='cbo_estado' onChange='submit();'>".combo($estados,$_POST['cbo_estado'])."</select>";
        
        $oGrid = new paloSantoGrid($smarty);
        $oGrid->showFilter(
                "<form style='margin-bottom:0;' method='POST' action='?menu=$module_name'>" .
                "<table width='100%' border='0'><tr>".
                "<td><input type='submit' name='submit_select_queue' value='{$arrLangModule['Select Queue']}' class='button'></td>".
                "<td class='letra12' align='right'>{$arrLangModule['Status']}&nbsp;$combo_estados </td>".
                "</tr></table>".
                "</form>");
        $arrData    = array();
        if (is_array($arrDataQueues)) {
            foreach($arrDataQueues as $queue) {
                
                $arrTmp[0] = $queue['queue'];

                $ver_queue = "&nbsp;<a href='?menu=$module_name&action=view&id=".$queue['id']."'>{$arrLangModule['View']}</a>";
                if($queue['estatus']=='I') {
                    $arrTmp[1] = $arrLangModule['Inactive'];
                    $arrTmp[2] = "&nbsp;<a href='?menu=$module_name&action=activar&id=".$queue['id']."'>{$arrLangModule['Activate']}</a>";
                } elseif($queue['estatus']=='A') {
                    $arrTmp[1] = $arrLangModule['Active'];
                    $arrTmp[2] = $ver_queue;
                }
                $arrData[] = $arrTmp;
            }
        }

        $contenido = $oGrid->fetchGrid($arrGrid, $arrData,$arrLangModule);
        return $contenido;
    }

    function adaptar_formato_rte($strText) {
            //returns safe code for preloading in the RTE
            $tmpString = $strText;

            //convert all types of single quotes
            $tmpString = str_replace(chr(145), chr(39), $tmpString);
            $tmpString = str_replace(chr(146), chr(39), $tmpString);
            $tmpString = str_replace("'", "&#39;", $tmpString);

            //convert all types of double quotes
            $tmpString = str_replace(chr(147), chr(34), $tmpString);
            $tmpString = str_replace(chr(148), chr(34), $tmpString);

            //replace carriage returns & line feeds
            $tmpString = str_replace(chr(10), " ", $tmpString);
            $tmpString = str_replace(chr(13), " ", $tmpString);

            //replace comillas dobles por una
            $tmpString = str_replace("\"", "'", $tmpString);

            return $tmpString;
    }


?>
