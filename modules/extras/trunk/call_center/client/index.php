<?php
    
    require_once "libs/paloSantoForm.class.php";

    function _moduleContent(&$smarty,$module_name) {

        require_once "modules/$module_name/libs/paloSantoUploadFile.class.php";

        $language=get_language();
        $script_dir=dirname($_SERVER['SCRIPT_FILENAME']);
        $lang_file="modules/$module_name/lang/$language.lang";

        if (file_exists("$script_dir/$lang_file")) {
            include_once($lang_file);
        } else {
            include_once("modules/$module_name/lang/en.lang");
        }

        $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
        $templates_dir=(isset($arrConfig['templates_dir']))?$arrConfig['templates_dir']:'themes';
        $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'."default";

        $smarty->assign("MODULE_NAME",$lang['Load File']);
        $smarty->assign("NAME_FIELD_FILE",$lang['fileCRM']);
        $smarty->assign("LABEL_MESSAGE",$lang['Select file upload']);
        $botonSubmit = "<input class='button' type = 'submit' name='cargar_datos'
                        value='{$lang['Upload']}' onClick=\"return validarFile(this.form.fileCRM.value)\" />";

        $smarty->assign("Format_File",$lang['Format File']);
        $smarty->assign("File",$lang['File']);
        $smarty->assign("NAME_BUTTON",$botonSubmit);

        $form_campos = array(
            'file'    =>    array(
                "LABEL"                  => $lang['File'],
                "REQUIRED"               => "yes",
                "INPUT_TYPE"             => "FILE",
                "INPUT_EXTRA_PARAM"      => "",
                "VALIDATION_TYPE"        => "text",
                "VALIDATION_EXTRA_PARAM" => "",
            ),
        );

        $oForm = new paloForm($smarty,$form_campos);

        if ( is_object($oForm) ) {
            $fContenido = mostrarFormulario($local_templates_dir,$oForm,$lang);
            if (isset($_FILES['fileCRM'])) {
                $file = $_FILES['fileCRM'];
                if (isset($_POST["cargar_datos"])) {
                    $cargaDatos = new Cargar_File($file);
                    if( is_object($cargaDatos) )  {
                        $nameFile=$cargaDatos->getFileName();
                        $cargaDatos->guardarDatosClientes($nameFile,$module_name);
                        $msgResultado = $cargaDatos->getMsgResultado();
                        $oForm->setViewMode();
                        $smarty->assign("mb_title",$lang['Result']);
                        $smarty->assign("mb_message",$msgResultado);
                        $fContenido = $oForm->fetchForm("$local_templates_dir/form.tpl", $lang['Load File'] ,null);
                    } else { 
                        $smarty->assign("mb_title",$lang['Error']);
                        $smarty->assign("mb_message",$lang['Error when is loading file']);
                    }
                }
            }
        }
        return $fContenido;
    }

    /*
        Esta funcion muestra el formulario para cargar el archivo con los datos a ser subidos a a la base call_center
        en la tabla contact
    */
    function mostrarFormulario($local_templates_dir,$oForm,$lang) {
        $_POST["file"] = "";
        $fContenido = $oForm->fetchForm("$local_templates_dir/form.tpl", $lang['Load File'] ,$_POST);
        return $fContenido;
    }

?>