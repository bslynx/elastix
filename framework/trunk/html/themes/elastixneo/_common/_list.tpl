<form id="idformgrid" method="POST" style="margin-bottom:0;" action="{$url}">
    <div class="neo-table-header-row">
        {if $addNewShow}
            {if $addNewLink}
                <a href="{$addNewTask}" class="neo-table-action">
                    <div class="neo-table-header-row-filter">
                        <img border="0" src="images/plus2.png" align="absmiddle"  />&nbsp;{$addNewAlt}
                    </div>
                </a>
            {else}
                <div class="neo-table-header-row-filter" id="grid_task_add_new">
                    <img border="0" src="images/plus2.png" align="absmiddle"  />
                    <input type="submit" name="{$addNewTask}" value="{$addNewAlt}" class="neo-table-action" />
                </div>
            {/if}
        {/if}

        {if $deleteListShow}
            {if $deleteListLink}
                <a href="{$deleteListTask}" class="neo-table-action">
                    <div class="neo-table-header-row-filter">
                        <img border="0" src="images/delete5.png" align="absmiddle"  />&nbsp;{$addNewAlt}
                    </div>
                </a>
            {else}
                <div class="neo-table-header-row-filter">
                    <img  border="0" src="images/delete5.png" align="absmiddle" />
                    <input type="submit" name="{$deleteListTask}" value="{$deleteListAlt}" onclick="return confirmSubmit('{$deleteListMSG}')" class="neo-table-action" />
                </div>
            {/if}
        {/if}

        {if $customActionShow}
            {if $customActionLink}
                <a href="{$customActionTask}" class="neo-table-action">
                    <div class="neo-table-header-row-filter">
                        {if !empty($customActionIMG)}
                            <img border="0" src="{$customActionIMG}" align="absmiddle"  />&nbsp;
                        {/if}
                        {$customActionAlt}
                    </div>
                </a>
            {else}
                <div class="neo-table-header-row-filter">
                    {if !empty($customActionIMG)}
                        <img border="0" src="{$customActionIMG}" align="absmiddle"  />
                    {/if}
                    <input type="submit" name="{$customActionTask}" value="{$customActionAlt}" class="neo-table-action" />
                </div>
            {/if}
        {/if}

        {if !empty($contentFilter)}
            <div class="neo-table-header-row-filter" id="neo-tabla-header-row-filter-1">
                <img src="images/filter.png" align="absmiddle" /> {$FILTER_GRID} <img src="images/icon_arrowdown2.png" align="absmiddle" />
            </div>
        {/if}

        {if $enableExport==true}
            <div class="neo-table-header-row-filter" id="export_button" role="button" act="10" tabindex="0" class="exportButton exportShadow" aria-expanded="false" aria-haspopup="true" aria-activedescendant="" >
                <img src="images/download2.png" align="absmiddle" /> {$DOWNLOAD_GRID} <img src="images/icon_arrowdown2.png" align="absmiddle" />
            </div>
            <div id="subMenuExport" class="subMenu neo-display-none" role="menu" aria-haspopup="true" aria-activedescendant="">
                <div class="items">
                    <div class="menuItem" role="menuitem" id="CSV" aria-disabled="false">
                        <div>
                            <a href="{$url}&exportcsv=yes&rawmode=yes"><img src="images/csv.gif" border="0" align="absmiddle" title="CSV" />&nbsp;&nbsp;CSV</a>
                        </div>
                    </div>
                    <div class="menuItem" role="menuitem" id="Spread_Sheet" aria-disabled="false">
                        <div>
                            <a href="{$url}&exportspreadsheet=yes&rawmode=yes"><img src="images/spreadsheet.gif" border="0" align="absmiddle" title="SPREAD SHEET" />&nbsp;&nbsp;SPREAD SHEET</a>
                        </div>
                    </div>
                    <div class="menuItem" role="menuitem" id="PDF" aria-disabled="false">
                        <div>
                            <a href="{$url}&exportpdf=yes&rawmode=yes"><img src="images/pdf.png" border="0" align="absmiddle" title="PDF" />&nbsp;&nbsp;PDF</a>
                        </div>
                    </div>
                </div>
            </div>
        {/if}

        <div class="neo-table-header-row-navigation">
            {if $pagingShow}
                {if $start<=1}
                    <img src='images/table-arrow-first.gif' alt='{$lblStart}' align='absmiddle' border='0' width="16" height="16" style="opacity: 0.3;" />
                    <img src='images/table-arrow-previous.gif' alt='{$lblPrevious}' align='absmiddle' border='0' width="16" height="16" style="opacity: 0.3;" />
                {else}
                    <a href="{$url}&nav=start&start={$start}"><img src='images/table-arrow-first.gif' alt='{$lblStart}' align='absmiddle' border='0' width='16' height='16' style="cursor: pointer;" /></a>
                    <a href="{$url}&nav=previous&start={$start}"><img src='images/table-arrow-previous.gif' alt='{$lblPrevious}' align='absmiddle' border='0' width='16' height='16' style="cursor: pointer;" /></a>
                {/if}
                &nbsp;{$lblPage}&nbsp;
                <input type="text"  value="{$currentPage}" size="2" align="absmiddle" name="page" id="pageup" />&nbsp;{$lblof}&nbsp;{$numPage}
                <input type="hidden" value="bypage" name="nav" />
                {if $end==$total}
                    <img src='images/table-arrow-next.gif' alt='{$lblNext}' align='absmiddle' border='0' width="16" height="16" style="opacity: 0.3;" />
                    <img src='images/table-arrow-last.gif' alt='{$lblEnd}' align='absmiddle' border='0' width='16' height='16' style="opacity: 0.3;" />
                {else}
                    <a href="{$url}&nav=next&start={$start}"><img src='images/table-arrow-next.gif' alt='{$lblNext}' align='absmiddle' border='0' width='16' height='16' style="cursor: pointer;" /></a>
                    <a href="{$url}&nav=end&start={$start}"><img src='images/table-arrow-last.gif' alt='{$lblEnd}' align='absmiddle' border='0' width='16' height='16' style="cursor: pointer;" /></a>
                {/if}
            {/if}
        </div>
    </div>

    {if !empty($contentFilter)}
        <div id="neo-table-header-filterrow" class="neo-display-none">
            {$contentFilter}
        </div>
    {/if}

    <div id="neo-table-ref-table">
        <table align="center" cellspacing="0" cellpadding="0" width="100%" id="neo-table1" >
            <tr class="neo-table-title-row">
                {section name=columnNum loop=$numColumns start=0 step=1}
                    {if $smarty.section.columnNum.first}
                        <td class="neo-table-title-row" style="background:none;">{$header[$smarty.section.columnNum.index].name}&nbsp;</td>
                    {else}
                        <td class="neo-table-title-row">{$header[$smarty.section.columnNum.index].name}&nbsp;</td>
                    {/if}
                {/section}
            </tr>
            {if $numData > 0}
                {foreach from=$arrData key=k item=data name=filas}
                {if $data.ctrl eq 'separator_line'}
                    <tr class="neo-table-data-row">
                        {if $data.start > 0}
                            <td class="neo-table-data-row" colspan="{$data.start}"></td>
                        {/if}
                        {assign var="data_start" value="`$data.start`"}
                        <td class="neo-table-data-row" colspan="{$numColumns-$data.start}" style='background-color:#AAAAAA;height:1px;'></td>
                    </tr>
                {else}
                    <tr class="neo-table-data-row">
                        {if $smarty.foreach.filas.last}
                            {section name=columnNum loop=$numColumns start=0 step=1}
                                <td class="neo-table-data-row table_data_last_row">{if $data[$smarty.section.columnNum.index] eq ''}&nbsp;{/if}{$data[$smarty.section.columnNum.index]}</td>
                            {/section}
                        {else}
                            {section name=columnNum loop=$numColumns start=0 step=1}
                                <td class="neo-table-data-row table_data">{if $data[$smarty.section.columnNum.index] eq ''}&nbsp;{/if}{$data[$smarty.section.columnNum.index]}</td>
                            {/section}
                        {/if}
                    </tr>
                {/if}
                {/foreach}
            {else}
                <tr class="neo-table-data-row">
                    <td class="neo-table-data-row table_data" colspan="{$numColumns}" align="center">{$NO_DATA_FOUND}</td>
                </tr>
            {/if}
            {if $numData > 3}
                <tr class="neo-table-title-row">
                    {section name=columnNum loop=$numColumns start=0 step=1}
                        {if $smarty.section.columnNum.first}
                            <td class="neo-table-title-row" style="background:none;">{$header[$smarty.section.columnNum.index].name}&nbsp;</td>
                        {else}
                            <td class="neo-table-title-row">{$header[$smarty.section.columnNum.index].name}&nbsp;</td>
                        {/if}
                    {/section}
                </tr>
            {/if}
        </table>
    </div>

    {if $numData > 3}
        <div class="neo-table-footer-row">
            <div class="neo-table-header-row-navigation">
                {if $pagingShow}
                    {if $start<=1}
                        <img src='images/table-arrow-first.gif' alt='{$lblStart}' align='absmiddle' border='0' width="16" height="16" style="opacity: 0.3;" />
                        <img src='images/table-arrow-previous.gif' alt='{$lblPrevious}' align='absmiddle' border='0' width="16" height="16" style="opacity: 0.3;" />
                    {else}
                        <a href="{$url}&nav=start&start={$start}"><img src='images/table-arrow-first.gif' alt='{$lblStart}' align='absmiddle' border='0' width='16' height='16' style="cursor: pointer" /></a>
                        <a href="{$url}&nav=previous&start={$start}"><img src='images/table-arrow-previous.gif' alt='{$lblPrevious}' align='absmiddle' border='0' width='16' height='16' style="cursor: pointer" /></a>
                    {/if}
                    &nbsp;{$lblPage}&nbsp;
                    <input  type=text  value="{$currentPage}" size="2" align="absmiddle" name="page" id="pagedown" />&nbsp;{$lblof}&nbsp;{$numPage}&nbsp;({$total}&nbsp;{$lblrecords})
                    {if $end==$total}
                        <img src='images/table-arrow-next.gif' alt='{$lblNext}' align='absmiddle' border='0' width="16" height="16" style="opacity: 0.3;" />
                        <img src='images/table-arrow-last.gif' alt='{$lblEnd}' align='absmiddle' border='0' width='16' height='16' style="opacity: 0.3;" />
                    {else}
                        <a href="{$url}&nav=next&start={$start}"><img src='images/table-arrow-next.gif' alt='{$lblNext}' align='absmiddle' border='0' width='16' height='16' style="cursor: pointer" /></a>
                        <a href="{$url}&nav=end&start={$start}"><img src='images/table-arrow-last.gif' alt='{$lblEnd}' align='absmiddle' border='0' width='16' height='16' style="cursor: pointer" /></a>
                    {/if}
                {/if}
            </div>
        </div>
    {/if}
</form>

{literal}
<script type="text/Javascript">
    $(function(){
        $("#neo-table1").colResizable({
            liveDrag:true,
            marginLeft:"1px",
        });
    });

    $("[id^=page]").keypress(function(event) {
        if ( event.which == 13 ) {
            event.preventDefault();
            $("#idformgrid").submit();
        }
    });

    //   $(document).ready(function(){
    //     $("#neo-combo-example-ringgroup, #neo-combo-example-fieldname, #neo-combo-example-status").kendoComboBox();
    //   });

    $("#neo-tabla-header-row-filter-1").click(function() {
        if($("#neo-table-header-filterrow").data("neo-table-header-filterrow-status")=="visible") {
            $("#neo-table-header-filterrow").addClass("neo-display-none");
            $("#neo-table-header-filterrow").data("neo-table-header-filterrow-status", "hidden");
        } else {
            $("#neo-table-header-filterrow").removeClass("neo-display-none");
            $("#neo-table-header-filterrow").data("neo-table-header-filterrow-status", "visible");
        }
    });
</script>
{/literal}