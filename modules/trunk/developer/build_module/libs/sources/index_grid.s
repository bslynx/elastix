//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSanto{NAME_CLASS}.class.php";

    //include file language agree to elastix configuration
    //if file language not exists, then include language by default (en)
    $lang=get_language();
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $lang_file="modules/$module_name/lang/$lang.lang";
    if (file_exists("$base_dir/$lang_file")) include_once "$lang_file";
    else include_once "modules/$module_name/lang/en.lang";

    //global variables
    global $arrConf;
    global $arrConfModule;
    global $arrLang;
    global $arrLangModule;
    $arrConf = array_merge($arrConf,$arrConfModule);
    $arrLang = array_merge($arrLang,$arrLangModule);

    //folder path for custom templates
    $templates_dir=(isset($arrConf['templates_dir']))?$arrConf['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    //conexion resource
    $pDB = "";


    //actions
    $accion = getAction();
    $content = "";

    switch($accion){
        default:
            $content = report{NAME_CLASS}($smarty, $module_name, $local_templates_dir, $pBD, $arrConf, $arrLang);
            break;
    }
    return $content;
}

function report{NAME_CLASS}($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $p{NAME_CLASS} = new paloSanto{NAME_CLASS}($pDB);
    $field_pattern = getParameter("filter");
    $action = getParameter("nav");
    $start  = getParameter("start");


    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);
    $total{NAME_CLASS} = $p{NAME_CLASS}->ObtainNum{NAME_CLASS}();

    $limit  = 20;
    $total  = $total{NAME_CLASS};
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);

    $oGrid->calculatePagination($action,$start);
    $offset = $oGrid->getOffsetValue();
    $end    = $oGrid->getEnd();
    $url    = "?menu=$module_name&filter=$field_pattern";

    $arrData = null;
    $arrResult =$p{NAME_CLASS}->Obtain{NAME_CLASS}($limit, $offset, $field_pattern);

    if(is_array($arrResult) && $total>0){
        foreach($arrResult as $key => $value){
            $arrTmp[0] = $value['campo1'];
            $arrTmp[1] = $value['campo2'];
            $arrTmp[2] = $value['campo3'];
            $arrData[] = $arrTmp;
        }
    }


    $arrGrid = array("title"    => $arrLang["{NEW_MODULE_NAME}"],
                        "icon"     => "images/list.png",
                        "width"    => "99%",
                        "start"    => ($total==0) ? 0 : $offset + 1,
                        "end"      => $end,
                        "total"    => $total,
                        "url"      => $url,
                        "columns"  => array(0 => array("name"      => $arrLang["Field"]." 1",
                                                    "property1" => ""),
                                            1 => array("name"      => $arrLang["Field"]." 2",
                                                    "property1" => ""),
                                            2 => array("name"      => $arrLang["Field"]." 3",
                                                    "property1" => "")
                                        )
                    );


    //begin section filter
    $arrFormFilter{NAME_CLASS} = createFieldForm($arrLang);
    $oFilterForm = new paloForm($smarty, $arrFormFilter{NAME_CLASS});
    $smarty->assign("SHOW", $arrLang["Show"]);

    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl","",$_POST);
    //end section filter

    $oGrid->showFilter(trim($htmlFilter));
    $contenidoModulo = "<form  method='POST' style='margin-bottom:0;' action=$url>".$oGrid->fetchGrid($arrGrid, $arrData,$arrLang)."</form>";
    //end grid parameters

    return $contenidoModulo;
}


function createFieldForm($arrLang){
    $arrFormElements = array(
            "filter"    => array(   "LABEL"                  => $arrLang["Filter Example"],
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "TEXT",
                                    "INPUT_EXTRA_PARAM"      => "",
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => ""),
                    );
    return $arrFormElements;
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