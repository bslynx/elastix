<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF8" />
        <title>Elastix</title>
        <link rel="stylesheet" href="themes/{$THEMENAME}/styles.css" />
        <link rel="stylesheet" href="themes/{$THEMENAME}/help.css" />
        <script type="text/javascript" src ="libs/js/jquery/js/jquery-1.3.2.min.js"></script>
        <script type="text/javascript" src ="libs/js/jquery/js/jquery-ui-1.7.2.custom.min.js"></script>
        <script src="libs/js/base.js"></script>
        <script src="libs/js/iframe.js"></script>
        {$HEADER}
    </head>
    <body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" {$BODYPARAMS}>
        {$MENU} <!-- Viene del tpl menu.tlp-->
                <td align="left" valign="top">
                    {if !empty($mb_message)}
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
                    <div align="center" class="copyright"><a href="http://www.elastix.org" target='_blank'>Elastix</a> is licensed under <a href="http://www.opensource.org/licenses/gpl-license.php" target='_blank'>GPL</a> by <a href="http://www.palosanto.com" target='_blank'>PaloSanto Solutions</a>. 2006 - 2010.</div>
                    <br>
                </td>
            </tr>
        </table>
    </body>
</html>
