$(document).ready(function (){
    $(":checkbox").iButton({
        labelOn: "On",
        labelOff: "Off",
        change: function ($input){
            $("#status").val($input.is(":checked") ? "active" : "disactive");
        }
    }).trigger("change");
});