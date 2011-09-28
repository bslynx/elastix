$(document).ready(function(){
    if(document.getElementById("filter_value"))
	document.onkeypress = pressKey;
});

function pressKey(e)
{
    var keycode;
    if (window.event) keycode = window.event.keyCode;
    else if (e) keycode = e.which;
    else return true;
    if(keycode == 13){
	$("form").submit();
	return false;
    }
}