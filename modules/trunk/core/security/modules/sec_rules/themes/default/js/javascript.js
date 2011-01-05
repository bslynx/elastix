$(document).ready(function(){
    showElementByTraffic();
    showElementByProtocol();

    $('#id_protocol').change(function(){
        var valor = $('#id_protocol option:selected').val();
        var arrAction              = new Array();
            arrAction["action"]    = "getPorts";
            arrAction["rawmode"]   = "yes";
            arrAction["protocol"]  =  valor;
            request("index.php",arrAction,false,
                function(arrData,statusResponse,error)
                {   
                    var html = "";
                    $('#port_in').html("");
                    $('#port_out').html("");
                    var key = "";
                    for(key in arrData){
                        valor = arrData[key];
                        html += "<option value = "+key+">"+valor+"</option>";
                    }
                    $('#port_in').html(html);
                    $('#port_out').html(html);
                }
            );
   
    });

    $(".up,.down").click(function(){
        var msg = document.getElementById("msg_status");
        msg.style.color = '#E35332';
        var adv = document.getElementById("message");
        var tab = document.getElementById("table_message");    
        var row  = $(this).parents("tr:first");
        var info = $(this).attr("id");
        //alert(info);
        var neighborrow = "";
        var changing = "";
        var p1 = "";
        if ($(this).is(".up")) {
            if(row.prev().attr("class") != "table_title_row"){               
                p1 = row.prev().children().contents();
                neighborrow = p1.next().attr("id");
                row.insertBefore(row.prev());
                changing = "rulerup";
            }
        } else {
            if(row.next().next().attr("class") != "table_navigation_row"){
                p1 = row.next().children().contents();
                neighborrow = p1.next().attr("id");
                row.insertAfter(row.next());
                changing = "rulerdown";
            }
        }

        var arrAction                    = new Array();
            arrAction["action"]          = "change";
            arrAction["rawmode"]         = "yes";
            arrAction["neighborrow"]     = neighborrow;
            arrAction["actualrow"]       = info;
            request("index.php",arrAction,false,
                function(arrData,statusResponse,error)
                {
                    if(error)
                        alert(error);
                    else if(p1!=""){
                        response = statusResponse.split(':');
                        $("#msg_status").html(response[0]);
                       // adv.html(response[1]);
                        setTimeout('$("#msg_status").html("")',300);
                        adv.style.display = '';
                        tab.style.border = '1px solid';
                        tab.style.color = '#AAAAAA';
                        adv.innerHTML = response[1] + "&nbsp;&nbsp;&nbsp;&nbsp;<input class='button' type='submit' name='exec' value='"+response[2]+"'>";
                        neighborrow = neighborrow.split('_');
                        actualrow = info.split('_');

                        p1.next().attr("id","rulerup_" + neighborrow[1] + "_" + actualrow[2]);
                        p1.next().next().attr("id","rulerdown_" + neighborrow[1] + "_" + actualrow[2]);

                        $("#div_"+actualrow[1]).html(neighborrow[2]);
                        $("#div_"+neighborrow[1]).html(actualrow[2]);

                        var nodo = $("#"+info);
                        
                        if(changing == "rulerup"){
                            nodo.attr("id","rulerup_" + actualrow[1] + "_" + neighborrow[2]);
                            nodo.next().attr("id","rulerdown_" + actualrow[1] + "_" + neighborrow[2]);
                        }
                        else{
                            nodo.attr("id","rulerdown_" + actualrow[1] + "_" + neighborrow[2]);
                            nodo.prev().attr("id","rulerup_" + actualrow[1] + "_" + neighborrow[2]);
                        }
                    }else{
                        $("#msg_status").html(statusResponse);
                        setTimeout('$("#msg_status").html("")',300);
                    }
                }
            );

    });

});

function showElementByTraffic()
{
    var traffic = document.getElementById('id_traffic');

    if(traffic){
        if( traffic.value == 'INPUT' ){
            document.getElementById('id_interface_in').style.display = '';
            document.getElementById('id_interface_out').style.display = 'none';
        }
        else if( traffic.value == 'OUTPUT' ){
            document.getElementById('id_interface_in').style.display = 'none';
            document.getElementById('id_interface_out').style.display = '';
        }
        else if( traffic.value == 'FORWARD' ){
            document.getElementById('id_interface_in').style.display = '';
            document.getElementById('id_interface_out').style.display = '';
        }
    }
}

function showElementByProtocol()
{
    var protoc = document.getElementById('id_protocol');

    if(protoc){
        if( protoc.value == 'TCP' ){
            document.getElementById('id_port_in').style.display = '';
            document.getElementById('id_port_out').style.display = '';
            document.getElementById('id_type_icmp').style.display = 'none';
            document.getElementById('id_id_ip').style.display = 'none';
        }
        else if( protoc.value == 'UDP' ){
            document.getElementById('id_port_in').style.display = '';
            document.getElementById('id_port_out').style.display = '';
            document.getElementById('id_type_icmp').style.display = 'none';
            document.getElementById('id_id_ip').style.display = 'none';
        }
        else if( protoc.value == 'ICMP' ){
            document.getElementById('id_port_in').style.display = 'none';
            document.getElementById('id_port_out').style.display = 'none';
            document.getElementById('id_type_icmp').style.display = '';
            document.getElementById('id_id_ip').style.display = 'none';
        }
        else if( protoc.value == 'IP' ){
            document.getElementById('id_port_in').style.display = 'none';
            document.getElementById('id_port_out').style.display = 'none';
            document.getElementById('id_type_icmp').style.display = 'none';
            document.getElementById('id_id_ip').style.display = '';
        }
        else if( protoc.value == 'ALL' ){
            document.getElementById('id_port_in').style.display = 'none';
            document.getElementById('id_port_out').style.display = 'none';
            document.getElementById('id_type_icmp').style.display = 'none';
            document.getElementById('id_id_ip').style.display = 'none';
        }
    }
}
