/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function(){

    if($('#type_provider_voip').val() == 'custom')
        $('#technology').removeAttr("disabled");
    else
        $('#technology').attr("disabled","disabled");


    if($('#editStatus').val() == 'on')
        $('#type_provider_voip').attr("disabled","disabled")
    else
        $('#type_provider_voip').removeAttr("disabled");


    $('#type_provider_voip').change(function(){
        var type_provider = $("#type_provider_voip option:selected").val();
        setFieldAccount();
        if(type_provider != "custom"){
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
        }
    });
});

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

