<?php
class paloSantoGraph
{
    var $_module;
    var $_class;
    var $_function;
    var $parametros;
    var $_functionCB;

    function paloSantoGraph( $module, $class, $function, $arrParameters, $functionCB="")
    {
        $this->_module   = $module; 
        $this->_class    = $class;
        $this->_function = $function;

        $str_res = '';
        if( $arrParameters!='' )
            foreach( $arrParameters as $num => $str )
                $str_res .= ($num == 0) ? $str: "@$str";

        $this->parametros = $str_res;
        $this->functionCB = $functionCB;
    }

    function getGraph($ruta="")
    {
        //$ruta -> ayuda a setear la ruta de la imagen en caso de que se pierda su direccion
        return "<img src='".$ruta."libs/paloSantoGraphImage.php?module=".$this->_module."&class=".$this->_class.
                   "&function=".$this->_function."&parameters=".$this->parametros."&functionCB=".$this->functionCB."'/>";
    }
}
?>