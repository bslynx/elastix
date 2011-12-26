{* Este DIV se usa para mostrar los mensajes de error *}
<div
    id="neo-addons-error-message"
    class="ui-corner-all"
    style="display: none;">
    <p>
        <span class="ui-icon" style="float: left; margin-right: .3em;"></span>
        <span id="neo-addons-error-message-text"></span>
    </p>
</div>
  <div class="neo-addons-header-row">
    <div class="neo-addons-header-row-filter">
      Filter by:
      <select id="filter_by" class="neo-addons-header-row-select" name="filter_by" onchange="javascript:do_listarAddons(null)">
        <option value="available">Available</option>
        <option value="installed">Installed</option>
        <option value="purchased">Purchased</option>
        <option value="update_available">Update Available</option>
      </select>
    </div>
    <div class="neo-addons-header-row-filter">  
      Sort by:
      <select class="neo-addons-header-row-select">
        <option>Alphabeticall</option>
        <option>Popularity</option>
      </select>
    </div>
    <div class="neo-addons-header-row-filter">  
      Name:
      <input type="text" id="filter_namerpm" value="" name="filter_namerpm" onkeypress="javascript:keyPressed(event)">
      <a onclick="javascript:do_listarAddons(null)" href="#">
      <img width="15" height="15" border="0" align="absmiddle" src="modules/addons_avalaibles/images/lupa.png" alt="">
      </a>
    </div>
    <div class="neo-addons-header-row-navigation">
        <img id="imgPrimero" style="cursor: pointer;" src="modules/{$module_name}/images/table-arrow-first.gif" width="16" height="16" alt='{$lblStart}' align='absmiddle' />
        <img id="imgAnterior"  style="cursor: pointer;" src="modules/{$module_name}/images/table-arrow-previous.gif" width="16" height="16" alt='{$lblPrevious}' align='absmiddle' />
        (Showing <span id="addonlist_start_range">?</span> - <span id="addonlist_end_range">?</span> of <span id="addonlist_total">?</span>)
        <img id="imgSiguiente" style="cursor: pointer;" src="modules/{$module_name}/images/table-arrow-next.gif" width="16" height="16" alt='{$lblNext}' align='absmiddle' />
        <img id="imgFinal" style="cursor: pointer;" src="modules/{$module_name}/images/table-arrow-last.gif" width="16" height="16" alt='{$lblEnd}' align='absmiddle' />
    </div>
  </div>
<div id="addonlist">
<div style="text-align: center; padding: 40px;">
<img src="images/loading.gif" />
</div>
</div>

<!-- Neo Progress Bar -->
<div class="neo-modal-box">
  <div id="container">
    <div class="neo-progress-bar-percentage"><span class="neo-progress-bar-percentage-tag"></span></div>
    <div class="neo-progress-bar"><div class="neo-progress-bar-progress"></div></div>
    <span class="neo-progress-bar-label"><img src="images/loading2.gif" align="absmiddle" />&nbsp;<span id="feedback"></span></span>
    <div class="neo-progress-bar-title"></div>
    <div class="neo-progress-bar-close"></div>
  </div>
</div>
<div class="neo-modal-blockmask"></div>
