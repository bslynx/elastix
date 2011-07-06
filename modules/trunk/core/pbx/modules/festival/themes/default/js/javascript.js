$(document).ready(function (){
    $("#start").val("0");
    $(":checkbox").iButton({
        labelOn: "On",
        labelOff: "Off",
        change: function ($input){
            $("#status").val($input.is(":checked") ? "activate" : "deactivate");
            if($input.is(":checked"))
		var festival_activate = "activate";
	    else
		var festival_activate = "deactivate";
	    if($("#start").val() != "0"){
		var arrAction            = new Array();
		    arrAction["action"]  = "change";
		    arrAction["menu"]	 = "festival";
		    arrAction["rawmode"] = "yes";
		    arrAction["status"]  = festival_activate;
		    request("index.php",arrAction,false,
			function(arrData,statusResponse,error)
			{   
			    if(arrData["mb_title"] && arrData["mb_message"]){
				$("#message_error").remove();
				var message= "<div style='background-color: rgb(255, 238, 255);' id='message_error'><table width='100%'><tr><td align='left'><b style='color:red;'>" +
					      arrData['mb_title'] + "</b>" + arrData['mb_message'] + "</td> <td align='right'><input type='button' onclick='hide_message_error();' value='" +
					      arrData['button_title']+ "'/></td></tr></table></div>";
				$("body > table > tbody > tr").children(":nth-child(7)").prepend(message);
			    }
			}
		    );
	    }
	    else
	      $("#start").val("1");
        }
    }).trigger("change");
});