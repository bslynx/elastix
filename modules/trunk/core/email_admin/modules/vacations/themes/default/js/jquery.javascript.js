    function popup_get_emails(url_popup){
        var ancho = 600;
        var alto = 400;
        my_window = window.open(url_popup,"my_window","width="+ancho+",height="+alto+",location=yes,status=yes,resizable=yes,scrollbars=yes,fullscreen=no,toolbar=yes");
        my_window.moveTo((screen.width-ancho)/2,(screen.height-alto)/2);
        my_window.document.close();
    }

    function getAccount(account,id){
	var subject  = $('#'+id).parent().next().next().children(":first-child").text();
	var body     = $('#'+id).parent().next().next().next().children(":first-child").text();
	var vacation = $('#'+id).parent().next().next().next().next().children(":first-child").text();
	window.opener.document.getElementById("email").value = account;
	window.opener.document.getElementById("subject").value = subject;
	window.opener.document.getElementById("body").value = body;
	if(vacation=="yes"){
	    var lblDisactivate = window.opener.document.getElementById("lblDisactivate").value;
	    window.opener.document.getElementById("actionVacation").value = lblDisactivate;
	    window.opener.document.getElementById("actionVacation").setAttribute("name", "disactivate");
	}else{
	    var lblActivate = window.opener.document.getElementById("lblActivate").value;
	    window.opener.document.getElementById("actionVacation").value = lblActivate;
	    window.opener.document.getElementById("actionVacation").setAttribute("name", "activate");
	}
	window.close();
    }

    // implement JSON.parse de-serialization
    function JSONtoString(str) {
        if (str === "") str = '""';
        eval("var p=" + str + ";");
        return p;
    }