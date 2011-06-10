<html>
    <head>
        <title>Elastix</title>
	{$HEADER_LIBS_JQUERY}
	{$HEADER_MODULES}
	<link rel="stylesheet" href="{$path}themes/{$THEMENAME}/styles.css">
	<link rel="stylesheet" href="{$path}themes/{$THEMENAME}/help.css">
    </head>
    <body>
	<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center" id="tableFilterPop">
	    <tr class="letra12">
		<td width="10%" align="left">&nbsp;&nbsp;</td>
		<td width="30%" align="right">
		    {$filter_field.LABEL}:&nbsp;&nbsp;{$filter_field.INPUT}&nbsp;&nbsp;{$filter_value.INPUT}
		    <input class="button" type="submit" id="show" name="show" value="{$SHOW}" />
		</td>
	    </tr>
	</table>
    </body>
</html>