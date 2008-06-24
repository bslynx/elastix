<div id='error' name='error'></div>
<div>
<table width="99%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="moduleTitle">
        <td class="moduleTitle" valign="middle">&nbsp;&nbsp;<img src="images/conference.png" border="0" align="absmiddle">&nbsp;&nbsp;{$TITLE}</td>
        <td></td>
    </tr>
    <tr>
        <td align="left"><input class="button" type="button" name="save" value="{$SAVE}" onclick="save_module()"></td>
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
    </tr>
</table>
<table class="tabForm" style="font-size: 16px;" width="100%" >
    <tr class="letra12">
        <td align="left"><b>{$module_name_label}: <span  class="required">*</span></b></td>
        <td align="left"><input type='text' name='module_name' id='module_name' value=''></td>
        <td width=10%></td>
        <td align="left"><b>{$id_module_label}: <span  class="required">*</span></b></td>
        <td align="left"><input type='text' name='id_module' id='id_module' value=''></td>
    </tr>
    <tr class="letra12">
        <td align="left"><b>{$group_permissions.LABEL}:</b></td>
        <td align="left">
            <select id='group_permissions' name='group_permissions' multiple='multiple' size='3'>
                {foreach key="key" from=$arrGroups item="value"}
                    {if $value=='administrator'}
                        <option value='{$key}' selected="selected">{$value}</option>
                    {else}
                        <option value='{$key}'>{$value}</option>
                    {/if}
                {/foreach}
            </select>
        </td>
        <td width=10%></td>
        <td align="left"><b>{$your_name_label}: <span  class="required">*</span></b></td>
        <td align="left"><input type='text' name='your_name' id='your_name' value=''></td>
    </tr>
    <tr class="letra12">
        <td align="left"><b>{$module_type}: <span  class="required">*</span></b></td>
        <td align="left">
            <select id='module_type' name='module_type'>
                <option value='grid' >{$type_grid}</option>
                <option value='form' >{$type_form}</option>
            </select>
        </td>
        <td width=10%></td>
        <td></td>
        <td></td>
    </tr>
    <tr class="letra12">
        <td align="left"><b>{$module_level}: <span  class="required">*</span></b></td>
        <td align="left">
            <select id='module_level_options' name='module_level_options' onchange='mostrar_menu()'>
                <option value='level_2' >{$level_2}</option>
                <option value='level_3' >{$level_3}</option>
            </select>
        </td>
        <td width=10%></td>
        <td></td>
        <td></td>
    </tr>
    <tr class="letra12">
        <td align="left"><b>{$parent_1_exists}: <span  class="required">*</span></b></td>
        <td align="left">
            <select id='parent_1_existing_option' name='parent_1_existing_option' onchange='mostrar_menu()'>
                <option value='{$peYes}'>{$peYes}</option>
                <option value='{$peNo}' selected="selected">{$peNo}</option>
            </select>
        </td>
        <td></td>
        <td align="left" id='label_level2'></td>
        <td align="left" id='level2_exist'></td>
    </tr>

    <tr class="letra12" id='parent_menu_1'>
        <td align='left'><b>{$level_1_parent_name}: <span  class='required'>*</span></b></td>
        <td align='left'><input type='text' name='parent_1_name' id='parent_1_name' value='' ></td>
        <td></td>
        <td align='left'><b>{$level_1_parent_id}: <span  class='required'>*</span></b></td>
        <td align='left'><input type='text' name='parent_1_id' id='parent_1_id' value='' ></td>
    </tr>

    <tr class="letra12" id='parent_menu_2'></tr>
</table>
{literal}
<script type="text/javascript">
    function mostrar_menu()
    {
        var level = document.getElementById("module_level_options").selectedIndex;
        var parent_1_existing = document.getElementById("parent_1_existing_option").selectedIndex;

        var parent_2_existing;
        if(document.getElementById("parent_2_existing_option") != null)
            parent_2_existing = document.getElementById("parent_2_existing_option").selectedIndex;
        else parent_2_existing = -1;

        var id_parent = '';
        if(document.getElementById("parent_module") !=null)
        {
            var index = document.getElementById("parent_module").selectedIndex;
            id_parent = document.getElementById("parent_module").options[index].value;
        }

        xajax_mostrar_menu(level, parent_1_existing, parent_2_existing, id_parent);
    }

    function save_module()
    {
        var val_module_name = "", val_id_module = "";
        var val_selected_gp = new Array();
        var val_module_type = "";
        var val_level = -1, val_exists_p1 = -1, val_exists_p2 = -1;
        var val_parent_1_name = "", val_parent_1_id = "";
        var val_parent_2_name = "", val_parent_2_id = "";
        var val_selected_parent_1 = "", val_selected_parent_2 = "";
        var val_your_name = "";

        val_module_name = document.getElementById("module_name").value;
        val_id_module = document.getElementById("id_module").value;

        var group_permissions = document.getElementById("group_permissions");
        for (var i = 0; i < group_permissions.options.length; i++)
            if (group_permissions.options[ i ].selected)
                val_selected_gp.push(group_permissions.options[ i ].value);

        var module_type = document.getElementById("module_type");
        for (var i = 0; i < module_type.options.length; i++)
                if (module_type.options[ i ].selected)
                    val_module_type = module_type.options[ i ].value;

        val_your_name = document.getElementById("your_name").value;

        val_level = document.getElementById("module_level_options").selectedIndex;

        val_exists_p1 = document.getElementById("parent_1_existing_option").selectedIndex;

        var exits_p2_option = document.getElementById("parent_2_existing_option");
        if(exits_p2_option != null)
            val_exists_p2 = exits_p2_option.selectedIndex;

        var parent_1_name = document.getElementById("parent_1_name");
        if(parent_1_name != null)
            val_parent_1_name = parent_1_name.value;

        var parent_1_id = document.getElementById("parent_1_id");
        if(parent_1_id != null)
            val_parent_1_id = parent_1_id.value;

        var parent_2_name = document.getElementById("parent_2_name");
        if(parent_2_name != null)
            val_parent_2_name = parent_2_name.value;

        var parent_2_id = document.getElementById("parent_2_id");
        if(parent_2_id != null)
            val_parent_2_id = parent_2_id.value;

        var parent_module = document.getElementById("parent_module");
        if(parent_module != null)
        {
            for (var i = 0; i < parent_module.options.length; i++)
                if (parent_module.options[ i ].selected)
                    val_selected_parent_1 = parent_module.options[ i ].value;
        }

        var parent_module_2 = document.getElementById("parent_module_2");
        if(parent_module_2 != null)
        {
            for (var i = 0; i < parent_module_2.options.length; i++)
                if (parent_module_2.options[ i ].selected)
                    val_selected_parent_2 = parent_module_2.options[ i ].value;
        }

        xajax_save_module(val_module_name, val_id_module, val_selected_gp, val_module_type, val_your_name, val_level, val_exists_p1, val_exists_p2, val_parent_1_name, val_parent_1_id, val_parent_2_name, val_parent_2_id, val_selected_parent_1, val_selected_parent_2);
    }
</script>
{/literal}