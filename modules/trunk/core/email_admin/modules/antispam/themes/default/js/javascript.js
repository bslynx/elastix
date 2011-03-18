$(document).ready(function (){
    verifySieve();
    changeActivateDefault();

    $(":checkbox").iButton({
        labelOn: "On",
        labelOff: "Off",
        change: function ($input){
            $("#status").val($input.is(":checked") ? "active" : "disactive");
        }
    }).trigger("change");

    $( "#slider-range-max" ).slider({
            range: "max",
            min: 1,
            max: 10,
            value: $('#levelnum').val(),
            slide: function( event, ui ) {
                $("#amount").text(ui.value);
                $("#levelnum").val(ui.value);
            }
    });

    $("#amount").text($("#slider-range-max").slider("value"));
    $("#levelnum").val($("#slider-range-max").slider("value"));

    $("#politica").change(function(){
        var opcion = $("#politica option:selected").val();
        if(opcion == "capturar_spam"){
            //$("input[name=header]").attr("style","visibility: hidden;");
            $("input[name=header]").hide();
        }else{
            //$("input[name=header]").attr("style","visibility: visible;");
            $("input[name=header]").show();
        }
    });

});

function changeActivateDefault()
{
    var status = $('#statusSpam').val();
    if(status=="active"){
        $("input[name=chkoldstatus]").attr("checked", "checked");
        $("#status").val("activate");
    }else{
        $("input[name=chkoldstatus]").removeAttr("checked");
        $("#status").val("disactive");
    }
}

function verifySieve()
{
    var status = $('#statusSieve').val();
    if(status == "on"){
        $("#politica option").each(function(){
            var opcion = $(this).val();
            if(opcion == "capturar_spam")
                $(this).attr("selected", "selected");
            else
                $(this).removeAttr("selected");
        });
        //$("input[name=header]").attr("style","visibility: hidden;");
        $("input[name=header]").hide();
    }else{
        $("#politica option").each(function(){
            var opcion = $(this).val();
            if(opcion == "marcar_asusto")
                $(this).attr("selected", "selected");
            else
                $(this).removeAttr("selected");
        });
        //$("input[name=header]").attr("style","visibility: visible;");
        $("input[name=header]").show();
    }
}