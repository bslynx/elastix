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
  $Id: paloSantoGrid.class.php,v 1.1.1.1 2007/07/06 21:31:55 gcarrillo Exp $ */
global $arrConf;
require_once "{$arrConf['basePath']}/libs/xajax/xajax.inc.php";
require_once "{$arrConf['basePath']}/libs/paloSantoPDF.class.php";


class paloSantoGrid {

    var $enableExport;
    var $limit;
    var $total;
    var $offset;
    var $end;
    var $tplFile;

    //Implementation AJAX
    var $withAjax;
    var $functionNameAjax;
    var $prefixAjax;
    var $pagingShow;
    var $nameFile_Export;

    function paloSantoGrid($smarty)
    {
        $this->smarty = $smarty;
        $this->enableExport = false;
        $this->offset = 0;
        $this->end = 0;
        $this->limit = 0;
        $this->total = 0;
        $this->withAjax = 0;
        $this->functionNameAjax = "";
        $this->prefixAjax = "xajax_";
        $this->pagingShow = 1;
        $this->tplFile = "_common/_list.tpl";
        $this->nameFile_Export = "Report-".date("YMd.His");
    }

    function withAjax()
    {
        $this->withAjax = 1;
    }
 
    function pagingShow($show)
    {
        $this->pagingShow = (int)$show;
    }

    function setTplFile($tplFile)
    {
        $this->tplFile  = $tplFile;
    }

    function withoutAjax()
    {
        $this->withAjax = 0;
    }

    function fetchGrid($arrGrid, $arrData,$arrLang=array())
    {
        $export = $this->exportType();

        switch($export){
            case "csv":
                $content = $this->fetchGridCSV($arrGrid, $arrData);
                break;
            case "pdf":
                $content = $this->fetchGridPDF($arrGrid, $arrData);
                break;
            case "xls":
                $content = $this->fetchGridXLS($arrGrid, $arrData);
                break;
            default: //html
                $content = $this->fetchGridHTML($arrGrid, $arrData, $arrLang);
                break;
        }
        return $content;
    }

    function fetchGridCSV($arrGrid, $arrData)
    {
        header("Cache-Control: private");
        header("Pragma: cache");
        header("Content-Type: application/octec-stream");
        header("Content-disposition: inline; filename={$this->nameFile_Export}.csv");
        header("Content-Type: application/force-download");

        $numColumns=count($arrGrid["columns"]);

        $this->smarty->assign("title", $arrGrid['title']);
        $this->smarty->assign("icon",  $arrGrid['icon']);
        $this->smarty->assign("width", $arrGrid['width']);

        $this->smarty->assign("start", $arrGrid['start']);
        $this->smarty->assign("end",   $arrGrid['end']);
        $this->smarty->assign("total", $arrGrid['total']);

        $this->smarty->assign("url",   isset($arrGrid['url'])?$arrGrid['url']:'');

        $this->smarty->assign("header",  $arrGrid["columns"]);

        $this->smarty->assign("arrData", $arrData);
        $this->smarty->assign("numColumns", $numColumns);
        return $this->smarty->fetch("_common/listcsv.tpl");
    }

    function fetchGridPDF($arrGrid, $arrData)
    {
	    $pdf= new paloPDF();
            $pdf->setOrientation("L");
            $pdf->setFormat("A3");            
	    //$pdf->setLogoHeader("themes/elastixwave/images/logo_elastix.gif");
	    $pdf->setColorHeader(array(5,68,132));
	    $pdf->setColorHeaderTable(array(227,83,50));
	    $pdf->setFont("Verdana");
            $pdf->printTable("{$this->nameFile_Export}.pdf",$arrGrid['title'],$arrGrid['columns'],$arrData);
        return "";
    }

    function fetchGridXLS($arrGrid, $arrData)
    {
        //header ("Expires: 0");
        header ("Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0");
        header ("Pragma: no-cache");
        //header("Pragma: public");
        header ("Content-Type: application/force-download");
        header ("Content-Type: application/vnd.ms-excel;");   // This should work for IE & Opera
        header ("Content-type: application/x-msexcel");       // This should work for the rest
        header ("Content-Type: application/octet-stream");
        header ("Content-Type: application/download");
        header ("Content-Type: charset=UTF-8");
        header ("Content-Transfer-Encoding: none");
        //header ("Content-Transfer-Encoding: binary");
        header ("Content-Disposition: attachment; filename={$this->nameFile_Export}.xls");

        $tmp = $this->xlsBOF();
        # header
        foreach($arrGrid["columns"] as $i => $header)
            $tmp .= $this->xlsWriteCell(0,$i,$header["name"]);

        #data
        foreach($arrData as $k => $row) {
            foreach($row as $i => $cell){
                $tmp .= $this->xlsWriteCell($k+1,$i,$cell);
            }
        }
        $tmp .= $this->xlsEOF();
        echo $tmp;
    }

    function fetchGridHTML($arrGrid, $arrData,$arrLang=array())
    {
        $this->smarty->assign("withAjax",$this->withAjax);
        $this->smarty->assign("functionName",$this->prefixAjax.$this->functionNameAjax);
        /*if($this->pagingShow)
            $this->smarty->assign("pagingShow",1);
        else{
            if($this->total > $this->limit)        
                $this->smarty->assign("pagingShow",1);
            else            
                $this->smarty->assign("pagingShow",0);
            }*/
        $this->smarty->assign("pagingShow",$this->pagingShow);

        $numColumns=count($arrGrid["columns"]);
        $this->smarty->assign("title", $arrGrid['title']);
        $this->smarty->assign("icon",  $arrGrid['icon']);
        $this->smarty->assign("width", $arrGrid['width']);

        $this->smarty->assign("start", $arrGrid['start']);
        $this->smarty->assign("end",   $arrGrid['end']);
        $this->smarty->assign("total", $arrGrid['total']);

        if(isset($arrGrid['url']))
            $this->smarty->assign("url", $arrGrid['url']);

        $this->smarty->assign("header",  $arrGrid["columns"]);

        $this->smarty->assign("arrData", $arrData);
        $this->smarty->assign("numColumns", $numColumns);

        $this->smarty->assign("enableExport", $this->enableExport);
        //dar el valor a las etiquetas segun el idioma
        $etiquetas=array('Export','Start','Previous','Next','End');
        foreach ($etiquetas as $etiqueta)
        {
            $this->smarty->assign("lbl$etiqueta", (isset($arrLang[$etiqueta])?$arrLang[$etiqueta]:$etiqueta));
        }
        return $this->smarty->fetch($this->tplFile);
    }

    function showFilter($htmlFilter)
    {
        $this->smarty->assign("contentFilter", $htmlFilter);
    }

    function calculatePagination($accion, $start)
    {
        $this->setOffsetValue($this->getOffSet($this->getLimit(),$this->getTotal(),$accion,$start));
        $this->setEnd(($this->getOffsetValue() + $this->getLimit()) <= $this->getTotal() ? $this->getOffsetValue() + $this->getLimit() : $this->getTotal());
    }

    function getOffSet($limit,$total,$accion,$start)
    {
        // Si se quiere avanzar a la sgte. pagina
        if(isset($accion) && $accion=="next") {
            $offset = $start + $limit - 1;
        }
        // Si se quiere retroceder
        else if(isset($accion) && $accion=="previous") {
            $offset = $start - $limit - 1;
        }
        else if(isset($accion) && $accion=="end") {
            if(($total%$limit)==0) 
                $offset = $total - $limit;
            else 
                $offset = $total - $total%$limit;
        }
        else if(isset($accion) && $accion=="start") {
            $offset = 0;
        }
        else $offset = 0;
        return $offset;
    }

    function enableExport()
    {
        $this->enableExport = true;
    }

    function setLimit($limit)
    {
        $this->limit = $limit;
    }

    function setTotal($total)
    {
        $this->total = $total;
    }

    function setOffsetValue($offset)
    {
        $this->offset = $offset;
    }

    function setEnd($end)
    {
        $this->end = $end;
    }

    function setPrefixAjax($prefixAjax)
    {
        $this->prefixAjax = $prefixAjax;
    }

    function setFunctionNameAjax($functionName)
    {
        $this->functionNameAjax = $functionName;
    }
 
    function getLimit()
    {
        return $this->limit;
    }

    function getTotal()
    {
        return $this->total;
    }

    function getOffsetValue()
    {
        return $this->offset;
    }

    function getEnd()
    {
        return $this->end;
    }

    function exportType()
    {
        if($this->getParameter2("exportcsv") == "yes")
            return "csv";
        else if($this->getParameter2("exportpdf") == "yes")
            return "pdf";
        else if($this->getParameter2("exportspreadsheet") == "yes")
            return "xls";
        else
            return "html";
    }

    function setNameFile_Export($nameFile)
    {
        $this->nameFile_Export = $nameFile;
    }

    function xlsBOF()
    {
        $data = pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
        return $data;
    }

    function xlsEOF()
    {
        $data = pack("ss", 0x0A, 0x00);
        return $data;
    }

    function xlsWriteNumber($Row, $Col, $Value)
    {
        $data  = pack("sssss", 0x203, 14, $Row, $Col, 0x0);
        $data .= pack("d", $Value);
        return $data;
    }

    function xlsWriteLabel($Row, $Col, $Value )
    {
        $Value2UTF8=utf8_decode($Value);
        $L = strlen($Value2UTF8);
        $data  = pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
        $data .= $Value2UTF8;
        return $data;
    }

    function xlsWriteCell($Row, $Col, $Value )
    {
        if(is_numeric($Value))
            return $this->xlsWriteNumber($Row, $Col, $Value);
        else
            return $this->xlsWriteLabel($Row, $Col, $Value);
    }
    
    function getParameter2($parameter)
	{
		if(isset($_POST[$parameter]))
			return $_POST[$parameter];
		else if(isset($_GET[$parameter]))
			return $_GET[$parameter];
		else
			return null;
	}
}
?>
