<?php
/*
  vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2003 Palosanto Solutions S. A.                    |
  +----------------------------------------------------------------------+
  | Cdla. Nueva Kennedy Calle E 222 y 9na. Este                          |
  | Telfs. 2283-268, 2294-440, 2284-356                                  |
  | Guayaquil - Ecuador                                                  |
  +----------------------------------------------------------------------+
  | Este archivo fuente está sujeto a las políticas de licenciamiento    |
  | de Palosanto Solutions S. A. y no está disponible públicamente.      |
  | El acceso a este documento está restringido según lo estipulado      |
  | en los acuerdos de confidencialidad los cuales son parte de las      |
  | políticas internas de Palosanto Solutions S. A.                      |
  | Si Ud. está viendo este archivo y no tiene autorización explícita    |
  | de hacerlo, comuníquese con nosotros, podría estar infringiendo      |
  | la ley sin saberlo.                                                  |
  +----------------------------------------------------------------------+
  | Autores:      Gladys Carrillo B.   <gcarrillo@palosanto.com>         |
  | Modificación: Adonis Figueroa A.   <afigueroa@palosanto.com>         |
  +----------------------------------------------------------------------+
  $Id: paloSantoModuloXML.class.php,v 1.1 2007/09/05 00:25:25 gcarrillo Exp $
  $Id: paloSantoModuloXML.class.php,v 1.1 2008/05/29 11:25:25 afigueroa Exp $
*/


class ModuloXML
{
    var $_arbolMenu;// Árbol de menú construido a partir de archivo XML
    
    var $_tempMenuList;
    var $_rutaArchivo;
    var $_errMsg;
    /**
     * Constructor del objeto ModuloXML
     * 
     * @param string    $sRutaArchivo   Ruta al archivo donde se encuentra el menú XML
     */
    function ModuloXML($sRutaArchivo)
    {
        $this->_rutaArchivo=$sRutaArchivo;
        $this->_privado_construirArbolMenu();
    }

    function _privado_construirArbolMenu()
    {
        $this->_arbolMenu = array();

        $xmlDoc = new DOMDocument();
        $xmlDoc->load($this->_rutaArchivo);

        //copio el archivo en memoria
        $root = $xmlDoc->documentElement;//apunto a el tag raiz

        $arrMenuItem = $root->getElementsByTagName("menuitem");
        $menu = array();
        foreach($arrMenuItem as $menuitem)
        {
            $attID      = $menuitem->getAttribute("menuid");
            $attDesc    = $menuitem->getAttribute("desc");
            $attParent  = $menuitem->getAttribute("parent");
            $attModule  = $menuitem->getAttribute("module");
     	    $attLink    = isset($menuitem->getAttribute("link"))?$menuitem->getAttribute("link"):"";
            $attOrder   = isset($menuitem->getAttribute("order"))?$menuitem->getAttribute("order"):"-1";

            $menu[] = array(
                            'menuid'    => $attID,
                            'desc'      => $attDesc,
                            'parent'    => $attParent,
                            'module'    => $attModule,
                            'link'      => $attLink,
                            'order'     => $attOrder,
                        );
        }

        $this->_arbolMenu = $menu;
    }
}
?>
