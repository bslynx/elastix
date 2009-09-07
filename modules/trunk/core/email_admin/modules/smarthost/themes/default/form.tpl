<table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="moduleTitle">
        <td class="moduleTitle" valign="middle" colspan='2'>&nbsp;&nbsp;<img src="{$IMG}" border="0" align="absmiddle">&nbsp;&nbsp;{$title}</td>
    </tr>
    <tr class="letra12">
        <td align="left">
        {if $Show}
            <input class="button" type="submit" name="show" value="{$SHOW}" onclick="return confirm('Are you sure you wish to continue.');">&nbsp;&nbsp;&nbsp;&nbsp;
        {elseif $Edit}
            <input class="button" type="submit" name="edit" value="{$EDIT}">&nbsp;&nbsp;&nbsp;&nbsp;
        {elseif $Commit}
            <input class="button" type="submit" name="commit" value="{$SAVE}" onclick="return confirm('Are you sure you wish to continue.');">&nbsp;&nbsp;&nbsp;&nbsp;
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        {/if}
            
        </td>
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
    </tr>
</table>
<table class="tabForm" style="font-size: 16px;" COLS="4" width="100%" >
    <tr>   
    <td>
    <table border="0" width="100%" cellspacing="0" cellpadding="8" style="border:1px solid black">
        <tr class="letra12">
            {if $Relay_host}
            <td align="left"><b>{$relay_host.LABEL}: <span class="required">*</span></b></td>
            <td align="left">{$relay_host.INPUT}</td>
            <input type="hidden" value="{$RELAY_HOST}" name="tmpRelay_host" />
            {/if}

            {if $Port}
            <td align="left"><b>{$port.LABEL}: <span class="required">*</span></b></td>
            <td align="left">{$port.INPUT}</td>
            <input type="hidden" value="{$PORT}" name="tmpPort" />
            {/if}
        </tr>
    
        <tr class="letra12">
            <td align="left"><b>{$user.LABEL}: <span class="required">*</span></b></td>
            <td align="left">{$user.INPUT}</td>

            <td align="left"><b>{$password.LABEL}: <span class="required">*</span></b></td>
            <td align="left">{$password.INPUT}</td>
        </tr>
    </table>
    </td>
    </tr>

    <tr>
    <td>
    Authenticate Data
    <table border="0" width="100%" cellspacing="0" cellpadding="8" style="border:1px solid black">
    
    <tr class="letra12">
        <td align="left"><b>{$smtpd_password.LABEL}: <span class="required">*</span></b></td>
        <td align="left">{$smtpd_password.INPUT}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>

        <td align="left"><b>{$smtpd_country_name.LABEL}: <span class="required">*</span></b></td>
        <td align="left">{$smtpd_country_name.INPUT}</td>
    </tr>

    <tr class="letra12">
        <td align="left"><b>{$smtpd_province_name.LABEL}: <span class="required">*</span></b></td>
        <td align="left">{$smtpd_province_name.INPUT}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        
        <td align="left"><b>{$smtpd_locality_name.LABEL}: <span class="required">*</span></b></td>
        <td align="left">{$smtpd_locality_name.INPUT}</td>
    </tr>

    <tr class="letra12">
        <td align="left"><b>{$smtpd_organization_name.LABEL}: <span class="required">*</span></b></td>
        <td align="left">{$smtpd_organization_name.INPUT}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        
        <td align="left"><b>{$smtpd_organizational_unit_name.LABEL}: <span class="required">*</span></b></td>
        <td align="left">{$smtpd_organizational_unit_name.INPUT}</td>
    </tr>

    <tr class="letra12">
        <td align="left"><b>{$smtpd_common_name.LABEL}: <span class="required">*</span></b></td>
        <td align="left">{$smtpd_common_name.INPUT}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
    </tr>

    </table>
    </td>
    </tr>


    {if $NewMaincf}
    <tr>
    <td>
    <table border="0" width="100%" cellspacing="0" cellpadding="8" style="border:1px solid black">
    
    <tr class="letra12">
        {if $Smtp_sasl_auth_enable}
        <td align="left"><b>{$smtp_sasl_auth_enable.LABEL}: </b></td>
        <td align="left">{$smtp_sasl_auth_enable.INPUT}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <input type="hidden" value="{$SMTP_SASL_AUTH_ENABLE}" name="tmpSmtp_sasl_auth_enable" />
        {/if}

        {if $Smtp_sasl_password_maps}
        <td align="left"><b>{$smtp_sasl_password_maps.LABEL}: </b></td>
        <td align="left">{$smtp_sasl_password_maps.INPUT}</td>
        <input type="hidden" value="{$SMTP_SASL_PASSWORD_MAPS}" name="tmpSmtp_sasl_password_maps" />
        {/if}
    </tr>

    <tr class="letra12">
        {if $Smtp_sasl_security_options}
        <td align="left"><b>{$smtp_sasl_security_options.LABEL}: </b></td>
        <td align="left">{$smtp_sasl_security_options.INPUT}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <input type="hidden" value="{$SMTP_SASL_SECURITY_OPTIONS}" name="tmpSmtp_sasl_security_options" />
        {/if}

        {if $Smtpd_tls_auth_only}
        <td align="left"><b>{$smtpd_tls_auth_only.LABEL}: </b></td>
        <td align="left">{$smtpd_tls_auth_only.INPUT}</td>
        <input type="hidden" value="{$SMTPD_TLS_AUTH_ONLY}" name="tmpSmtpd_tls_auth_only" />
        {/if}
    </tr>

    <tr class="letra12">
        {if $Smtp_use_tls}
        <td align="left"><b>{$smtp_use_tls.LABEL}: </b></td>
        <td align="left">{$smtp_use_tls.INPUT}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <input type="hidden" value="{$SMTP_USE_TLS}" name="tmpSmtp_use_tls" />
        {/if}

        {if $Smtpd_use_tls}
        <td align="left"><b>{$smtpd_use_tls.LABEL}: </b></td>
        <td align="left">{$smtpd_use_tls.INPUT}</td>
        <input type="hidden" value="{$SMTPD_USE_TLS}" name="tmpSmtpd_use_tls" />
        {/if}
    </tr>

    <tr class="letra12">
        {if $Smtp_tls_note_starttls_offer}
        <td align="left"><b>{$smtp_tls_note_starttls_offer.LABEL}: </b></td>
        <td align="left">{$smtp_tls_note_starttls_offer.INPUT}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <input type="hidden" value="{$SMTPD_TLS_norte_STARTTLS_OFFER}" name="tmpSmtp_tls_yeste_starttls_offer" />
        {/if}

        {if $Smtpd_tls_key_file}
        <td align="left"><b>{$smtpd_tls_key_file.LABEL}: </b></td>
        <td align="left">{$smtpd_tls_key_file.INPUT}</td>
        <input type="hidden" value="{$SMTPD_TLS_KEY_FILE}" name="tmpSmtpd_tls_key_file" />
        {/if}
    </tr>

    <tr class="letra12">
        {if $Smtpd_tls_cert_file}
        <td align="left"><b>{$smtpd_tls_cert_file.LABEL}: </b></td>
        <td align="left">{$smtpd_tls_cert_file.INPUT}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <input type="hidden" value="{$SMTPD_TLS_CERT_FILE}" name="tmpSmtpd_tls_cert_file" />
        {/if}

        {if $Smtp_tls_CAfile}
        <td align="left"><b>{$smtp_tls_CAfile.LABEL}: </b></td>
        <td align="left">{$smtp_tls_CAfile.INPUT}</td>
        <input type="hidden" value="{$SMTPD_TLS_CAfile}" name="tmpSmtp_tls_CAfile" />
        {/if}
    </tr>

    <tr class="letra12">
        {if $Smtpd_tls_loglevel}
        <td align="left"><b>{$smtpd_tls_loglevel.LABEL}: </b></td>
        <td align="left">{$smtpd_tls_loglevel.INPUT}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <input type="hidden" value="{$SMTPD_TLS_LOGLEVEL}" name="tmpSmtpd_tls_loglevel" />
        {/if}

        {if $Smtpd_tls_received_header}
        <td align="left"><b>{$smtpd_tls_received_header.LABEL}: </b></td>
        <td align="left">{$smtpd_tls_received_header.INPUT}</td>
        <input type="hidden" value="{$SMTPD_TLS_RECEIVED_HEADER}" name="tmpSmtpd_tls_received_header" />
        {/if}
    </tr>

    <tr class="letra12">
        {if $Smtpd_tls_session_cache_timeout}
        <td align="left"><b>{$smtpd_tls_session_cache_timeout.LABEL}: </b></td>
        <td align="left">{$smtpd_tls_session_cache_timeout.INPUT}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <input type="hidden" value="{$SMTPD_TLS_SESSION_CACHE_TIMEOUT}" name="tmpSmtpd_tls_session_cache_timeout" />
        {/if}

        {if $Tls_random_source}
        <td align="left"><b>{$tls_random_source.LABEL}: </b></td>
        <td align="left">{$tls_random_source.INPUT}</td>
        <input type="hidden" value="{$TLS_RANDOM_SOURCE}" name="tmpTls_random_source" />
        {/if}
    </tr>

    <tr class="letra12">
        {if $Tls_daemon_random_source}
        <td align="left"><b>{$tls_daemon_random_source.LABEL}: </b></td>
        <td align="left">{$tls_daemon_random_source.INPUT}</td>
        <input type="hidden" value="{$TLS_DAEMON_RANDON_SOURCE}" name="tmpTls_daemon_random_source" />
        {/if}
    </tr>

    </table>
    </td>
    </tr>
    {/if}
</table>

<input type="hidden" value="{$Modified}" name="control" />

{literal}
<script language="JavaScript">
    <!--
    function displayForm(){
      var box = document.getElementById("select");

      if(box.value=="yes"){
        document.getElementById("form").style.display = "block";
        
      }else{
        document.getElementById("form").style.display = "none";
        
      }
    }
    -->
    </script>
{/literal}