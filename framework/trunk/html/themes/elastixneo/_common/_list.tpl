<form  method="POST" style="margin-bottom:0;" action="{$url}">
    <table width="{$width}" align="center" border="0" cellpadding="0" cellspacing="0">
    {if !empty($contentFilter)}
    <br />
    <tr>
        <td><table width="100%" border="0" cellspacing="0" cellpadding="0" class="filterForm"><tr><td>{$contentFilter}</td></tr></table>
        </td>
    </tr>
    {/if}
    <tr>
        <td> <br />
        <table class="table_data" align="center" cellspacing="0" cellpadding="0" width="100%">
            <tr class="table_navigation_row">
            <td colspan="{$numColumns}" class="table_navigation_row_top">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="table_navigation_text">
                <tr>
                    <td align="left">
                        &nbsp;
                        {if $enableExport==true}
			    <div id="export_button" role="button" act="10" tabindex="0" class="exportButton exportShadow" aria-expanded="false" aria-haspopup="true" aria-activedescendant=""><img src="images/export.gif" border="0" align="absmiddle" />&nbsp;
                            <font class="letranodec">{$lblExport}</font>&nbsp;&nbsp;<img src="images/arrow_down.png" border="0" align="absmiddke" /></div>
			    <div id="subMenuExport" class="subMenu neo-display-none" role="menu" aria-haspopup="true" aria-activedescendant=""><div class="items">
				<div class="menuItem" role="menuitem" id="CSV" aria-disabled="false"><div><a href="{$url}&exportcsv=yes&rawmode=yes"><img src="images/csv.gif" border="0" align="absmiddle" title="CSV" />&nbsp;&nbsp;CSV</a></div></div>
				<div class="menuItem" role="menuitem" id="Spread_Sheet" aria-disabled="false"><div><a href="{$url}&exportspreadsheet=yes&rawmode=yes"><img src="images/spreadsheet.gif" border="0" align="absmiddle" title="SPREAD SHEET" />&nbsp;&nbsp;SPREAD SHEET</a></div></div>
				<div class="menuItem" role="menuitem" id="PDF" aria-disabled="false"><div><a href="{$url}&exportpdf=yes&rawmode=yes"><img src="images/pdf.png" border="0" align="absmiddle" title="PDF" />&nbsp;&nbsp;PDF</a></div></div>
			    </div></div>
                        {/if}
                    </td>
                    <td align="left" id="msg_status"></td>
                    <td align="right"> 
                    {if $pagingShow}  
                        {if $start<=1}
                        <img
                        src='images/start_off.gif' alt='{$lblStart}' align='absmiddle'
                        border='0' width='13' height='11'>&nbsp;{$lblStart}&nbsp;&nbsp;<img 
                        src='images/previous_off.gif' alt='{$lblPrevious}' align='absmiddle' border='0' width='8' height='11'>
                        {else}
                        <a href="{$url}&nav=start&start={$start}"><img
                        src='images/start.gif' alt='{$lblStart}' align='absmiddle'
                        border='0' width='13' height='11'></a>&nbsp;{$lblStart}&nbsp;&nbsp;<a href="{$url}&nav=previous&start={$start}"><img 
                        src='images/previous.gif' alt='{$lblPrevious}' align='absmiddle' border='0' width='8' height='11'></a>
                        {/if}
                        &nbsp;{$lblPrevious}&nbsp;<span 
                        class='pageNumbers'>({$start} - {$end} of {$total})</span>&nbsp;{$lblNext}&nbsp;
                        {if $end==$total}
                        <img 
                        src='images/next_off.gif'
                        alt='{$lblNext}' align='absmiddle' border='0' width='8' height='11'>&nbsp;{$lblEnd}&nbsp;<img 
                        src='images/end_off.gif' alt='{$lblEnd}' align='absmiddle' border='0' width='13' height='11'>
                        {else}
                        <a href="{$url}&nav=next&start={$start}"><img
                        src='images/next.gif' 
                        alt='{$lblNext}' align='absmiddle' border='0' width='8' height='11'></a>&nbsp;{$lblEnd}&nbsp;<a 
                        href="{$url}&nav=end&start={$start}"><img 
                        src='images/end.gif' alt='{$lblEnd}' align='absmiddle' border='0' width='13' height='11'></a>
                        {/if}
                    {/if}
                    </td>
                </tr>
                </table>
            </td>
            </tr>
            <tr class="table_title_row">
            {section name=columnNum loop=$numColumns start=0 step=1}
		{if $smarty.section.columnNum.index == 0}
		    <td class="table_title_row_first">{$header[$smarty.section.columnNum.index].name}&nbsp;</td>
		{elseif $smarty.section.columnNum.last}
		    <td class="table_title_row_last">{$header[$smarty.section.columnNum.index].name}&nbsp;</td>
		{else}
		    <td class="table_title_row">{$header[$smarty.section.columnNum.index].name}&nbsp;</td>
		{/if}
            {/section}
            </tr>
            {foreach from=$arrData key=k item=data name=filas}
                {if $data.ctrl eq 'separator_line'}
                    <tr>
                        {if $data.start > 0}
                            <td colspan="{$data.start}"></td>
                        {/if}
                        {assign var="data_start" value="`$data.start`"}
                        <td colspan="{$numColumns-$data.start}" style='background-color:#AAAAAA;height:1px;'></td>
                    </tr>
                {else}
                    <tr onMouseOver="this.style.backgroundColor='#f2f2f2';" onMouseOut="this.style.backgroundColor='#dddddd';" style="background-color:#dddddd;">
                        {if $smarty.foreach.filas.last}
                            {section name=columnNum loop=$numColumns start=0 step=1}
			    {if $smarty.section.columnNum.first}
                            <td class="table_data_last_row_left">{if $data[$smarty.section.columnNum.index] eq ''}&nbsp;{/if}{$data[$smarty.section.columnNum.index]}</td>
			    {elseif $smarty.section.columnNum.last}
			    <td class="table_data_last_row_right">{if $data[$smarty.section.columnNum.index] eq ''}&nbsp;{/if}{$data[$smarty.section.columnNum.index]}</td>
			    {else}
			    <td class="table_data_last_row">{if $data[$smarty.section.columnNum.index] eq ''}&nbsp;{/if}{$data[$smarty.section.columnNum.index]}</td>
			    {/if}
                            {/section}
                        {else}
                            {section name=columnNum loop=$numColumns start=0 step=1}
			    {if $smarty.section.columnNum.first}
			    <td class="table_data_left">{if $data[$smarty.section.columnNum.index] eq ''}&nbsp;{/if}{$data[$smarty.section.columnNum.index]}</td>
			    {elseif $smarty.section.columnNum.last}
			    <td class="table_data_right">{if $data[$smarty.section.columnNum.index] eq ''}&nbsp;{/if}{$data[$smarty.section.columnNum.index]}</td>
			    {else}
			    <td class="table_data">{if $data[$smarty.section.columnNum.index] eq ''}&nbsp;{/if}{$data[$smarty.section.columnNum.index]}</td>
			    {/if}
                            {/section}
                        {/if}
                    </tr>
                {/if}
            {/foreach}
            <tr class="table_navigation_row">
            <td colspan="{$numColumns}" class="table_navigation_row_bottom">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="table_navigation_text">
                <tr>
                    <td align="left">&nbsp;</td>
                    <td align="right">
                    {if $pagingShow}  
			{if $start<=1}
                        <img
                        src='images/start_off.gif' alt='{$lblStart}' align='absmiddle'
                        border='0' width='13' height='11'>&nbsp;{$lblStart}&nbsp;&nbsp;<img
                        src='images/previous_off.gif' alt='{$lblPrevious}' align='absmiddle' border='0' width='8' height='11'>
                        {else}
                        <a href="{$url}&nav=start&start={$start}"><img
                        src='images/start.gif' alt='{$lblStart}' align='absmiddle'
                        border='0' width='13' height='11'></a>&nbsp;{$lblStart}&nbsp;&nbsp;<a href="{$url}&nav=previous&start={$start}"><img
                        src='images/previous.gif' alt='{$lblPrevious}' align='absmiddle' border='0' width='8' height='11'></a>
                        {/if}
                        &nbsp;{$lblPrevious}&nbsp;<span
                        class='pageNumbers'>({$start} - {$end} of {$total})</span>&nbsp;{$lblNext}&nbsp;
                        {if $end==$total}
                        <img
                        src='images/next_off.gif'
                        alt='{$lblNext}' align='absmiddle' border='0' width='8' height='11'>&nbsp;{$lblEnd}&nbsp;<img
                        src='images/end_off.gif' alt='{$lblEnd}' align='absmiddle' border='0' width='13' height='11'>
                        {else}
                        <a href="{$url}&nav=next&start={$start}"><img
                        src='images/next.gif'
                        alt='{$lblNext}' align='absmiddle' border='0' width='8' height='11'></a>&nbsp;{$lblEnd}&nbsp;<a
                        href="{$url}&nav=end&start={$start}"><img
                        src='images/end.gif' alt='{$lblEnd}' align='absmiddle' border='0' width='13' height='11'></a>
                        {/if}
                    {/if}
                    </td>
                </tr>
                </table>
            </td>
            </tr>
        </table>
        </td>
    </tr>
    </table>
</form>