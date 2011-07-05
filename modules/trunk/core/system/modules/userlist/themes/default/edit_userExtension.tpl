<html>
    <head>
        <title>Elastix</title>
	<link rel="stylesheet" href="{$path}themes/{$THEMENAME}/styles.css">
	<link rel="stylesheet" href="{$path}themes/{$THEMENAME}/help.css">
	{$HEADER_LIBS_JQUERY}
	<script src="{$path}libs/js/base.js"></script>
	<script src="{$path}modules/{$MODULE_NAME}/themes/default/js/javascript.js"></script>
    </head>
    <body>
	<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="message_board">
	    <tbody style="display:none" id="table_error"><tr>
		<td valign="middle" class="mb_title" id="mb_title"></td>
	    </tr>
	    <tr>
		<td valign="middle" class="mb_message" id="mb_message"></td>
	    </tr>
	</tbody></table>
        {$CONTENT}
    </body>
</html>