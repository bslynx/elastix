<script type="text/javascript" src ="/libs/js/jquery/js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src ="/libs/js/jquery/js/jquery-ui-1.7.2.custom.min.js"></script>
<script type="text/javascript" src="modules/{$module_name}/themes/js/jquery.color.js"></script>
<script type="text/javascript" src="modules/{$module_name}/themes/js/jquery.easing.1.3.js"></script>
<script type="text/javascript" src="modules/{$module_name}/themes/js/mbScrollable.js"></script>
<script type="text/javascript" src="modules/{$module_name}/themes/js/javascript.js"></script>
<script type="text/javascript" src="modules/{$module_name}/themes/js/interface.js"></script>

<link href="modules/{$module_name}/themes/css/mb.scrollable.css" rel="stylesheet" type="text/css" />
<link href="modules/{$module_name}/themes/css/style.css" rel="stylesheet" type="text/css" />
<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
		<td width="100%" colspan="2">
			<!--<div id="header">
				<span id="controls">
					<a href="#" id="all_open" title="Open">[ + ]</a>
					<a href="#" id="all_close" title="Close">[ x ]</a>
				</span>
				<a href="#" id="all_expand">{$Expand}</a> ~
				<a href="#" id="all_collapse">{$Collapse}</a> ~
                <a href="#" id="applet_admin" title="Applet Admin">Admin</a>
			</div>
            <center>{$APPLET_ADMIN}</center><br />-->
			<table width="100%" cellspacing="0" id="columns">
				<tr>
					{$AppletsPanels}
				</tr>
			</table>
		</td>
	</tr>
</table>

