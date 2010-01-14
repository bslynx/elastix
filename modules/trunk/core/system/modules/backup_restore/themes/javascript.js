/**
 * Interface Elements for jQuery
 * 
 * http://interface.eyecon.ro
 * 
 * Copyright (c) 2006 Stefan Petre
 * Dual licensed under the MIT (MIT-LICENSE.txt) 
 * and GPL (GPL-LICENSE.txt) licenses.
 *   
 *
 */
$(document).ready((function() {

    $("ul.droptrue").sortable({
        connectWith: 'ul',
    });

    $("ul.droptrue2").sortable({
        connectWith: 'ul',
    });

    $("ul.droptrue").droppable({
        drop: function(event, ui) {
            var lista = document.getElementById("sortable1");
            var items = lista.childNodes;
            var i = 0;
            var file = ";";
            for(i=0; i<items.length; i++){
                if(items[i].className == "ui-state-highlight"){
                    file += items[i].childNodes[0].firstChild.nodeValue + ";";
                }
            }
            file = $(ui.draggable).attr("id");
            var types = file.substring(4,file.length);
            var order = '&action=download&menu=backup_restore&file='+file+'&lista=droptrue';
            $.post("modules/backup_restore/libs/control.php", order, function(theResponse){
                    alert(theResponse);
            });
        }
    });

    $("ul.droptrue2").droppable({
        drop: function(event, ui) {
            lista = document.getElementById("sortable2");
            var items = lista.childNodes;
            var i = 0;
            var file = "";
            for(i=0; i<items.length; i++){
                if(items[i].className == "ui-state-default"){
                    file += items[i].childNodes[0].firstChild.nodeValue + ";";
                }
            }
            file = $(ui.draggable).attr("id");
            var types = file.substring(4,file.length);
            var order = '&action=upload&menu=backup_restore&file='+file+'&lista=droptrue2';
            $.post("modules/backup_restore/libs/control.php", order, function(theResponse){
                      alert(theResponse);
            });
        }
    });

    $("#sortable1, #sortable2").disableSelection();

    $("a[rel*=facebox]").facebox();
}));

