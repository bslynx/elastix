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
  $Id: paloSantoForm.class.php,v 1.1.1.1 2007/07/06 21:31:55 gcarrillo Exp $ */

/* A continuacion se ilustra como luce un tipico elemento del arreglo $this->arrFormElements

"telefono" => array("LABEL"                  => "Telefono",
                    "REQUIRED"               => "yes",
                    "INPUT_TYPE"             => "text",
                    "INPUT_EXTRA_PARAM"      => "",
                    "VALIDATION_TYPE"        => "number",
                    "VALIDATION_EXTRA_PARAM" => "");
*/

class paloForm
{
    var $smarty;
    var $arrFormElements;
    var $arrErroresValidacion;
    var $modo;

    function paloForm(&$smarty, $arrFormElements)
    {
        $this->smarty = &$smarty;
        $this->arrFormElements = $arrFormElements;
        $this->arrErroresValidacion = "";
        $this->modo = 'input'; // Modo puede ser 0 (Modo normal de formulario) o 1 (modo de vista o preview 
                               // de datos donde no se puede modificar.
    }

    // Esta funcion muestra un formulario. Para hacer esto toma una plantilla de 
    // formulario e inserta en ella los elementos de formulario.
    function fetchForm($templateName, $title, $arrPreFilledValues=array())
    {
        foreach($this->arrFormElements as $varName=>$arrVars) {
            $arrMacro = array();
            $strInput = "";

            switch($arrVars['INPUT_TYPE']) {
                case "TEXT":
                    if($this->modo=='input' or ($this->modo=='edit' and $arrVars['EDITABLE']!='no')) {
                        $strInput = "<input type='text' name='$varName' value='$arrPreFilledValues[$varName]'>";
                    } else {
                        $strInput = "$arrPreFilledValues[$varName]";
                    }
                    break;
                case "PASSWORD":
                    if($this->modo=='input' or ($this->modo=='edit' and $arrVars['EDITABLE']!='no')) {
                        $strInput = "<input type='password' name='$varName' value='$arrPreFilledValues[$varName]'>";
                    } else {
                        $strInput = "$arrPreFilledValues[$varName]";
                    }
                    break;
                case "HIDDEN":
                    $strInput = "<input type='hidden' name='$varName' value='$arrPreFilledValues[$varName]'>";
                    break;
                case "FILE":
                    if($this->modo=='input' or ($this->modo=='edit' and $arrVars['EDITABLE']!='no')) {
                        // Si viene un arreglo entonces puede ser que sea un submit de un campo tipo 'file'
                        if(is_array($arrPreFilledValues[$varName]) and $arrPreFilledValues[$varName]['error']==0 and 
                           !empty($arrPreFilledValues[$varName]['tmp_name']) and !empty($arrPreFilledValues[$varName]['name']) ) {

                            $tmpFilename = $arrPreFilledValues[$varName]['name'] . "_" . basename($arrPreFilledValues[$varName]['tmp_name']);

                            // Creo que no esta bien hacer esto aqui. Porque aqui se debe mostrar el formulario unicamente
                            // y naturalmente no se esperaria que se copie aqui el archivo. 
                            // Por ej. Qué pasa si este archivo no pasa alguna validación y por lo tanto no se desea guardarlo?
                            //         Qué pasa si el formulario paso las validaciones correctamente y por lo tanto no se pasa por
                            //         este bloque de codigo?
                            // O qué pasa si la copia da error, cómo notifico esto al programa?
                            copy($arrPreFilledValues[$varName]['tmp_name'], "/var/www/html/var/tmp/$tmpFilename");

                            $strInput = "<div id='showFile'><i>File: " . $arrPreFilledValues[$varName]['name'] . 
                                        //"</i>&nbsp;&nbsp;<input type='button' name='' value='Change file' class=button onClick=''>" . 
                                        "</i>" . 
                                        "<input type='hidden' name='$varName' value='" . $arrPreFilledValues[$varName]['name'] . "'>" .
                                        "<input type='hidden' name='_hidden_$varName' value='$tmpFilename'></div>";
                        // It's not and array, but can be a hidden field
                        } else if (!is_array($arrPreFilledValues[$varName]) and !empty($arrPreFilledValues[$varName]) and
                                   !empty($arrPreFilledValues["_hidden_" . $varName]) ) {
                            $strInput = "<div id='showFile'><i>File: " . $arrPreFilledValues[$varName] .
                                        //"</i>&nbsp;&nbsp;<input type='button' name='' value='Change file' class=button onClick=''>" .
                                        "</i>" . 
                                        "<input type='hidden' name='$varName' value='$arrPreFilledValues[$varName]'>" .
                                        "<input type='hidden' name='_hidden_$varName' value='" . $arrPreFilledValues["_hidden_" . $varName] . "'></div>";
                        // default. It's not an array and there is not hidden field
                        } else {
                            $strInput = "<input type='file' name='$varName'>";
                        }
                    } else {
                        $strInput = "$arrPreFilledValues[$varName]";
                    }
                    break;
                case "RADIO":
                    if($this->modo=='input' or ($this->modo=='edit' and $arrVars['EDITABLE']!='no')) {
                        $strInput = "";
                        if(is_array($arrVars['INPUT_EXTRA_PARAM'])) {
                            foreach($arrVars['INPUT_EXTRA_PARAM'] as $radioValue => $radioLabel) {
                                if($radioValue==$arrPreFilledValues[$varName]) {
                                    $strInput .= "<input type='radio' name='$varName' value='$radioValue' " .
                                                 "checked>&nbsp;$radioLabel&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                                } else {
                                    $strInput .= "<input type='radio' name='$varName' value='$radioValue'" .
                                                 ">&nbsp;$radioLabel&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                                }
                            }
                        }
                    } else {
                        $strInput = "$arrPreFilledValues[$varName]";
                    }
                    break;
                case "SELECT":
                    if($this->modo=='input' or ($this->modo=='edit' and $arrVars['EDITABLE']!='no')) {
                        $strInput  = "<select name='$varName'>";
                        if(is_array($arrVars['INPUT_EXTRA_PARAM'])) {
                            foreach($arrVars['INPUT_EXTRA_PARAM'] as $idSeleccion => $nombreSeleccion) {
                                if($idSeleccion==$arrPreFilledValues[$varName]) {
                                    $strInput .= "<option value='$idSeleccion' selected>$nombreSeleccion</option>";
                                } else {
                                    $strInput .= "<option value='$idSeleccion'>$nombreSeleccion</option>";
                                }
                            }
                        }
                        $strInput .= "</select>";
                    } else {
                        $idSeleccion = $arrPreFilledValues[$varName];
                        $strInput .= $arrVars['INPUT_EXTRA_PARAM'][$idSeleccion];
                    }
                    break;
                case "DATE":
                    if($this->modo=='input' or ($this->modo=='edit' and $arrVars['EDITABLE']!='no')) {

                        require_once("libs/js/jscalendar/calendar.php");    
                        $oCal = new DHTML_Calendar("/libs/js/jscalendar/", "en", "calendar-win2k-2", false);

                        $this->smarty->assign("HEADER", $oCal->load_files());

                        $strInput .= $oCal->make_input_field(
                                        array('firstDay'       => 1, // show Monday first
                                              'showsTime'      => true,
                                              'showOthers'     => true,
                                              'ifFormat'       => '%d %b %Y',
                                              'timeFormat'     => '12'),
                                        // field attributes go here
                                        array('style'          => 'width: 10em; color: #840; background-color: #fafafa; ' .
                                                                   'border: 1px solid #999999; text-align: center',
                                              'name'        => $varName,
                                              //'value'       => strftime('%d %b %Y', strtotime('now'))));
                                              'value'       => $arrPreFilledValues[$varName]));

                    } else {
                        $strInput = "$arrPreFilledValues[$varName]";
                    }
                    break;
                default:
                    $strInput = "";
            }
            $arrMacro['LABEL'] = $arrVars['LABEL'];
            $arrMacro['INPUT'] = $strInput;
            $this->smarty->assign($varName, $arrMacro);
        }
        $this->smarty->assign("title", $title);
        $this->smarty->assign("mode", $this->modo);
        return $this->smarty->fetch("file:$templateName");
    }
    
    function setViewMode()
    {
        $this->modo = 'view';
    }

    function setEditMode()
    {
        $this->modo = 'edit';
    }

    // TODO: No se que hacer en caso de que el $arrCollectedVars sea un arreglo vacio
    //       puesto que en ese caso la funcion devolvera true. Es ese el comportamiento esperado?
    function validateForm($arrCollectedVars)
    {
        include_once("libs/paloSantoValidar.class.php");
        $oVal = new PaloValidar();
        foreach($arrCollectedVars as $varName=>$varValue) {
            // Valido si la variable colectada esta en $this->arrFormElements
            if(@array_key_exists($varName, $this->arrFormElements)) {
                if($this->arrFormElements[$varName]['REQUIRED']=='yes' or ($this->arrFormElements[$varName]['REQUIRED']!='yes' AND !empty($varValue))) {
                    if($this->modo=='input' || ($this->modo=='edit' AND $this->arrFormElements[$varName]['EDITABLE']!='no')) {
                        $oVal->validar($this->arrFormElements[$varName]['LABEL'], $varValue, $this->arrFormElements[$varName]['VALIDATION_TYPE'], 
                                       $this->arrFormElements[$varName]['VALIDATION_EXTRA_PARAM']);
                    }
                }
            }
        }
        if($oVal->existenErroresPrevios()) {
            $this->arrErroresValidacion = $oVal->obtenerArregloErrores();
            return false;
        } else {
            return true;
        }
    }
}
?>
