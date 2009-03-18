<?php


if (file_exists("modules/form_list/lang/en.lang")) {
    include_once("modules/form_list/lang/en.lang");
} else {
echo "bbb";
        include_once("modules/form_list/lang/es.lang");
}

$arrConfig['module_name'] = 'form_list';
$arrConfig['templates_dir'] = 'themes';
$arrConfig['theme'] = 'default';
$arrConfig['arr_type'] = array(
        "VALUE" => array (
                    "LABEL",
                    "TEXT",
                    "LIST",
                    "DATE",
                    "TEXTAREA"),
        "NAME"  => array (
                    $arrLan["Type Label"],
                    $arrLan["Type Text"],
                    $arrLan["Type List"],
                    $arrLan["Type Date"],
                    $arrLan["Type Text Area"]),
        "SELECTED" => "Text",     
        );

$arrConfig['cadena_dsn'] = "mysql://asterisk:asterisk@localhost/call_center";
?>
