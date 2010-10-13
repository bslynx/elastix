<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF8" />
        <title>Elastix</title>
        <link rel="stylesheet" href="themes/{$THEMENAME}/styles.css" />
        <link rel="stylesheet" href="themes/{$THEMENAME}/help.css" />
	{$HEADER_LIBS_JQUERY}
        <script type='text/javascript' src="libs/js/base.js"></script>
        <script type='text/javascript' src="libs/js/iframe.js"></script>
        {$HEADER}
	{$HEADER_MODULES}
    </head>
    <body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" {$BODYPARAMS}>
        {$MENU} <!-- Viene del tpl menu.tlp-->
                <td align="left" valign="top">
                    {if !empty($mb_message)}
                        <!-- Message board -->
                        <!--<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" class="message_board">
                            <tr>
                                <td id="mb_title" valign="middle" class="mb_title">&nbsp;{$mb_title}</td>
                            </tr>
                            <tr>
                                <td id="mb_message" valign="middle" class="mb_message"><p>{$mb_message}</td>
                            </tr>
                        </table><br />-->
                        <div style="background-color: rgb(255, 238, 255);" id="message_error">
                            <table width="100%">
                                <tr>
                                    <td align="left"><b style="color:red;">{$mb_title} </b>{$mb_message}</td>
                                    <td align="right"><input type="button" onclick="hide_message_error();" value="{$md_message_title}"/></td>
                                </tr>
                            </table>
                        </div>
                        <!-- end of Message board -->
                    {/if}
                    <table border="0" cellpadding="2" cellspacing="1" width="100%">
                        <tr>
                            <td>
                            {$CONTENT}
                            </td>
                        </tr>
                    </table><br />
                    <div align="center" class="copyright"><a href="http://www.elastix.org" target='_blank'>Elastix</a> is licensed under <a href="http://www.opensource.org/licenses/gpl-license.php" target='_blank'>GPL</a> by <a href="http://www.palosanto.com" target='_blank'>PaloSanto Solutions</a>. 2006 - {$currentyear}.</div>
                    <br />
                </td>
            </tr>
        </table>
    </body>
</html>
