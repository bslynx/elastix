/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function(){
    if(document.getElementById("account_name"))
        if(document.getElementById("account_name").value == "")
            changeToNet2Phone();
    if($('#type_provider_voip').val() == 'custom'){
        document.getElementById('advanced_options').style.display='';
        $('input[name=chkoldadvanced]').attr('disabled',true);
        $('#technology').removeAttr("disabled");
    }
    else
        $('#technology').attr("disabled","disabled");

    if($('#editStatus').val() == 'on')
        $('#type_provider_voip').attr("disabled","disabled")
    else
        $('#type_provider_voip').removeAttr("disabled");
    $('input[name=chkoldadvanced]').change(function(){
        if($(this).is(':checked'))
            document.getElementById('advanced_options').style.display='';
        else
            document.getElementById('advanced_options').style.display='none';
    });

    $('#type_provider_voip').change(function(){
        changeProvider();
    });
});

function changeProvider()
{
    var type_provider = $("#type_provider_voip option:selected").val();
    setFieldAccount();
    if(type_provider != "custom"){
        $('input[name=chkoldadvanced]').attr('disabled',false);
        if($('input[name=chkoldadvanced]').is(':checked'))
            document.getElementById('advanced_options').style.display='';
        else
            document.getElementById('advanced_options').style.display='none';
        var arrAction              = new Array();
        arrAction["action"]        = "getInfoProvider";
        arrAction["rawmode"]       = "yes";
        /*arrAction["menu"]          = $('#Module_name').val();*/
        arrAction["type_provider"] = type_provider;
        request("index.php",arrAction,false,
            function(arrData,statusResponse,error)
            {
                $('#type').val(arrData["type"]);
                $('#qualify').val(arrData["qualify"]);
                $('#insecure').val(arrData["insecure"]);
                $('#host').val(arrData["host"]);
                $('#fromuser').val(arrData["fromuser"]);
                $('#fromdomain').val(arrData["fromdomain"]);
                $('#dtmfmode').val(arrData["dtmfmode"]);
                $('#disallow').val(arrData["disallow"]);
                $('#context').val(arrData["context"]);
                $('#allow').val(arrData["allow"]);
                $('#trustrpid').val(arrData["trustrpid"]);
                $('#sendrpid').val(arrData["sendrpid"]);
                $('#canreinvite').val(arrData["canreinvite"]);
                $('#technology').val(arrData["type_trunk"]);
                $('#technology').attr("disabled","disabled");
            }
        );
    }else{
        $('input[name=chkoldadvanced]').attr('disabled',true);
        document.getElementById('advanced_options').style.display='';
    }
}

function changeToNet2Phone()
{
    $("#type_provider_voip option").each(function(){
        var val = $(this).val();
        if(val == "Net2Phone"){
            $(this).attr('selected',val);
        }
    });
    changeProvider();
}

function setFieldAccount(){
    $('#username').val("");
    $('#secret').val("");
    $('#type').val("");
    $('#qualify').val("");
    $('#insecure').val("");
    $('#host').val("");
    $('#fromuser').val("");
    $('#fromdomain').val("");
    $('#dtmfmode').val("");
    $('#disallow').val("");
    $('#context').val("");
    $('#allow').val("");
    $('#trustrpid').val("");
    $('#sendrpid').val("");
    $('#canreinvite').val("");
    $('#technology').val("");
    $('#technology').removeAttr("disabled");
    $('#account_name').val("");
}

