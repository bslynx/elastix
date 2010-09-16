$(document).ready(function(){
    var seleccion = $('input[name=chkoldautentification]').is(":checked");
    if(seleccion)   
        $("#activeCert").show();
    else    
        $("#activeCert").hide();
    $('input[name=chkoldautentification]').change(function(){
        if ($("input[name=chkoldautentification]").attr("checked")){
            $("#activeCert").show();
        }else{
            $("#activeCert").hide();
        }
    });
});