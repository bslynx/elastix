$(document).ready(function(){

    setSelectedDomain();

    $('#SMTP_Server').change(function(){
        var domain = $('#SMTP_Server option:selected').val();
        if(domain == "custom"){
            $('input[name=relayhost]').val("");
            $('input[name=port]').val("");
        }else{ 
            $('input[name=relayhost]').val(domain);
            $('input[name=port]').val("587");
        }
    });
});


function setSelectedDomain(){
    $('#SMTP_Server option').each(function(){
        var dominio = $('input[name=relayhost]').val();
        var relay   = $(this).text();
        var server  = "";
        if(/smtp\.gmail\.com/.test(dominio))
            server = "GMAIL";
        if(/smtp\.mail\.yahoo\.com/.test(dominio))
            server = "YAHOO";
        if(/smtp\.live\.com/.test(dominio))
            server = "HOTMAIL";

        if(relay==server)
            $(this).attr("selected", "selected");
        else
            $(this).removeAttr("selected");
    });
}