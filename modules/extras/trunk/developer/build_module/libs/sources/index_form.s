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
    //$pDB = new paloDB($arrConf['dsn_conn_database']);
    $pDB = "";


    //actions
    $accion = getAction();
    $content = "";

    switch($accion){
        /*
        case "save":
            $content = save{NAME_CLASS}($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        */
        default:
            $content = form{NAME_CLASS}($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
    }
    return $content;
}

function form{NAME_CLASS}($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $p{NAME_CLASS} = new paloSanto{NAME_CLASS}($pDB);
    $arrForm{NAME_CLASS} = createFieldForm($arrLang);
    $oForm = new paloForm($smarty,$arrForm{NAME_CLASS});

    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("IMG", "images/list.png");

    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",$arrLang["{NEW_MODULE_NAME}"], $_POST);
    $contenidoModulo = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $contenidoModulo;
}

function createFieldForm($arrLang)
{
    $arrOptions = array('val1' => 'Value 1', 'val2' => 'Value 2', 'val3' => 'Value 3');

    $arrFields = array(
{ARR_FIELDS_FORM}
            );
    return $arrFields;
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
    if(getParameter("save"))
        return "save";
    else if(getParameter("new"))
        return "new";
    else if(getParameter("action")=="show") //Get parameter by GET (command pattern, links)
        return "show";
    else
        return "report";
}
