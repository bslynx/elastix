  // Shows and hides the sticky note

$(document).ready(function(){
  
  $("#neo-sticky-note").data("neo-sticky-note-status", "hidden");
  
  $(document).click(function() {
	 if($("#neo-sticky-note").data("neo-sticky-note-status")=="visible") {
	   $("#neo-sticky-note").addClass("neo-display-none");
       $("#neo-sticky-note").data("neo-sticky-note-status", "hidden");
	 }
  });

  $("#neo-sticky-note-text-edit-delete").click(function(){
	$("#neo-sticky-note").addClass("neo-display-none");
	$("#neo-sticky-note-text").removeClass("neo-display-none");
	$("#neo-sticky-note-text-edit").addClass("neo-display-none");
	$("#neo-sticky-note").data("neo-sticky-note-status", "hidden");
  });

  $("#neo-sticky-note").click(function(e) {
    e.stopPropagation();
  });


 /**
 * Esta funcion es un ajax que pide la informacion de la nota de un módulo
 */
  $('.togglestickynote').click(function(e) {
	e.stopPropagation(); // Para evitar q el click se propague hasta el "document"
	if($("#neo-sticky-note").data("neo-sticky-note-status")=="hidden") {
		var arrAction = new Array();
		arrAction["action"]  = "get_sticky_note";
		arrAction["rawmode"] = "yes";
		var urlImaLoading = "<div style='margin: 10px;'><div align='center'><img src='images/loading2.gif' /></div><div align='center'><span style='font-size: 14px; '>"+$('#get_note_label').val()+"</span></div></div>";
		$.blockUI({
		  message: urlImaLoading
		});
		request("index.php",arrAction,false,
			function(arrData,statusResponse,error)
			{
				$.unblockUI();
				var description = arrData;
				if(statusResponse == "OK"){
					if(description != "no_data"){
						if(description != "")
							$("#neo-sticky-note-text").text(description);
						else{
							var lbl_no_description = $("#lbl_no_description").val();
							$("#neo-sticky-note-text").text(lbl_no_description);
						}
						$("#neo-sticky-note-textarea").val(description);
						if($("#neo-sticky-note").data("neo-sticky-note-status")=="visible") {
							$("#neo-sticky-note").addClass("neo-display-none");
							$("#neo-sticky-note").data("neo-sticky-note-status", "hidden");
						} else {
							$("#neo-sticky-note").removeClass("neo-display-none");
							$("#neo-sticky-note").data("neo-sticky-note-status", "visible");
						}
					}
				}else{
					if(error != "no_data")
						alert(error);
					$("#neo-sticky-note-text").text(description);
					if($("#neo-sticky-note").data("neo-sticky-note-status")=="visible") {
						$("#neo-sticky-note").addClass("neo-display-none");
						$("#neo-sticky-note").data("neo-sticky-note-status", "hidden");
					} else {
						$("#neo-sticky-note").removeClass("neo-display-none");
						$("#neo-sticky-note").data("neo-sticky-note-status", "visible");
					}
				}
			}
		);
	}else{
		$("#neo-sticky-note").addClass("neo-display-none");
		$("#neo-sticky-note").data("neo-sticky-note-status", "hidden");
	}
  });
  
  $("#neo-sticky-note-text").click(function() {
	$("#neo-sticky-note-text").addClass("neo-display-none");
    $("#neo-sticky-note-text-edit").removeClass("neo-display-none");
	showCharCount();
  });

  $("#neo-sticky-note-textarea").keyup(function() {
    showCharCount();
  });

});

/**
 * Funcion que cuenta la cantidad de caracteres de un textarea para mostrar
 * la cantidad de caracteres que el usuario puede tipear.
 */
function showCharCount() {
	var charlimit        = 300;
	var textareacontent  = $("#neo-sticky-note-textarea").val();
	var textareanumchars = textareacontent.length;
	var charleft         = charlimit - textareanumchars;
	var lbl = $("#amount_char_label").val();
	if(textareanumchars>charlimit) {
	  $("#neo-sticky-note-textarea").val(textareacontent.substr(0,charlimit));
	  charleft = 0;
	}
	$("#neo-sticky-note-text-char-count").html(charleft + " " + lbl);
}

/**
 * Funcion que envia la peticion de guardar o editar una nota.
 */
function send_sticky_note(){
	var arrAction = new Array();
	arrAction["action"]  = "save_sticky_note";
	arrAction["description"]  = $("#neo-sticky-note-textarea").val();
	arrAction["rawmode"] = "yes";
	var urlImaLoading = "<div style='margin: 10px;'><div align='center'><img src='images/loading2.gif' /></div><div align='center'><span style='font-size: 14px; '>"+$('#save_note_label').val()+"</span></div></div>";
	$.blockUI({
	  message: urlImaLoading
	});
	request("index.php",arrAction,false,
		function(arrData,statusResponse,error)
		{
			$.unblockUI();
			if(statusResponse == "OK"){
				$("#neo-sticky-note").addClass("neo-display-none");
				$("#neo-sticky-note-text").removeClass("neo-display-none");
				$("#neo-sticky-note-text-edit").addClass("neo-display-none");
				$("#neo-sticky-note").data("neo-sticky-note-status", "hidden");
				var themeName = $('#elastix_theme_name').val();
				if(themeName == "elastixneo"){
					if(arrAction['description'] != ""){
						var imgName = "themes/elastixneo/images/tab_notes_on.png";
						$('#togglestickynote1').attr('src',imgName);
					}else{
						var imgName = "themes/elastixneo/images/tab_notes.png";
						$('#togglestickynote1').attr('src',imgName);
					}
				}
			}else{
				alert(error);
			}
		}
	);
}