function _moduleContent(&$smarty, $module_name)
{
    //include elastix framework
    include_once "libs/paloSantoGrid.class.php";
    include_once "libs/paloSantoForm.class.php";

    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSanto{NAME_CLASS}.class.php";
    global $arrConf;
    global $arrLang;

    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConfig['templates_dir']))?$arrConfig['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    $accion = getAction();

    $content = "";
    switch($accion)
    {
        default:
            $content = report_{NAME_CLASS}($smarty, $module_name, $local_templates_dir, $arrLang);
            break;
    }

    return $content;
}

function report_{NAME_CLASS}($smarty, $module_name, $local_templates_dir, $arrLang)
{
    $arrFormElements = array(
            "filter"            => array(   "LABEL"                  => $arrLang["Filter Example"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""),
                                );

    $oFilterForm = new paloForm($smarty, $arrFormElements);
    $smarty->assign("SHOW", $arrLang["Show"]);

    $field_pattern = getParameter("filter");

    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", $_POST);

    $p{NAME_CLASS} = new paloSanto{NAME_CLASS}($pDB);
    $total_datos = $p{NAME_CLASS}->ObtainNum{NAME_CLASS}();

    //Paginacion
    $limit  = 15;
    $total  = $total_datos[0];

    $oGrid  = new paloSantoGrid($smarty);
    $offset = $oGrid->getOffSet($limit,$total,(isset($_GET['nav']))?$_GET['nav']:NULL,(isset($_GET['start']))?$_GET['start']:NULL);

    $end    = ($offset+$limit)<=$total ? $offset+$limit : $total;

    $url = "?menu=$module_name&filter=$field_pattern";
    $smarty->assign("url", $url);
    //Fin Paginacion

    $arrResult =$p{NAME_CLASS}->Obtain{NAME_CLASS}($limit, $offset, $field_pattern);

    $arrData = null;
    if(is_array($arrResult) && $total>0){
        foreach($arrResult as $key => $value){
            $arrTmp[0] = $value['campo1'];
            $arrTmp[1] = $value['campo2'];
            $arrTmp[2] = $value['campo3'];
            $arrData[] = $arrTmp;
        }
    }

    $arrGrid = array("title"    => "{NEW_MODULE_NAME}",
                        "icon"     => "images/list.png",
                        "width"    => "99%",
                        "start"    => ($total==0) ? 0 : $offset + 1,
                        "end"      => $end,
                        "total"    => $total,
                        "columns"  => array(0 => array("name"      => $arrLang["Field"]." 1",
                                                    "property1" => ""),
                                            1 => array("name"      => $arrLang["Field"]." 2",
                                                    "property1" => ""),
                                            2 => array("name"      => $arrLang["Field"]." 3",
                                                    "property1" => "")
                                        )
                    );

    $oGrid->showFilter(trim($htmlFilter));
    $contenidoModulo = "<form  method='POST' style='margin-bottom:0;' action=$url>".$oGrid->fetchGrid($arrGrid, $arrData,$arrLang)."</form>";

    return $contenidoModulo;
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

function getAction()
{
    if(getParameter("show")) //Get parameter by POST (submit)
        return "show";
    else if(getParameter("new"))
        return "new";
    else if(getParameter("action")=="show") //Get parameter by GET (command pattern, links)
        return "show";
    else
        return "report";
}
