var frmvalidator = null;
$( document ).ready(function() {

      $('#language_vm').addClass("form-control");

// config callforward
      if($('input[name=callForwardOpt]:checked').val()=='no'){
        //alert($('input[name=callForwardOpt]:checked').val()); 
        $('input[name=callForwardInp]').attr('disabled', true);
      }

      $('input[name=callForwardOpt]').click(function() {
        if($(this).val()=='yes'){
            $('input[name=callForwardInp]').attr('disabled', false);                                                
        }else{
            $('input[name=callForwardInp]').attr('disabled', true);                    
        }
      });

// call forward unavailable
      if($('input[name=callForwardUnavailableOpt]:checked').val()=='no'){
        //alert($('input[name=callForwardOpt]:checked').val());
        $('input[name=callForwardUnavailableInp]').attr('disabled', true);
      }

      $('input[name=callForwardUnavailableOpt]').click(function() {
        if($(this).val()=='yes'){
        $('input[name=callForwardUnavailableInp]').attr('disabled', false);                                                
        }else{
        $('input[name=callForwardUnavailableInp]').attr('disabled', true);                    
        }
      });

//call forward busy
      if($('input[name=callForwardBusyOpt]:checked').val()=='no'){
        $('input[name=callForwardBusyInp]').attr('disabled', true);
      }

      $('input[name=callForwardBusyOpt]').click(function() {
        if($(this).val()=='yes'){
        $('input[name=callForwardBusyInp]').attr('disabled', false);                                                
        }else{
        $('input[name=callForwardBusyInp]').attr('disabled', true);                    
        }
      });

//config voicemail
      if($('input[name=status_vm]:checked').val()=='no'){
        $('input[name=email_vm]').attr('disabled', true);   
        $('input[name=password_vm]').attr('disabled', true);
        $('input[name=emailAttachment_vm]').button({disabled:true});
        $('input[name=playCid_vm]').button({disabled:true});
        $('input[name=playEnvelope_vm]').button({disabled:true});
        $('input[name=deleteVmail]').button({disabled:true});
      }
        
      $('input[name=status_vm]').click(function() {
        if($(this).val()=='yes'){
            $('input[name=email_vm]').attr('disabled', false);   
            $('input[name=password_vm]').attr('disabled', false); 
            $('input[name=emailAttachment_vm]').button({disabled:false}); 
            $('input[name=playCid_vm]').button({disabled:false});     
            $('input[name=playEnvelope_vm]').button({disabled:false}); 
            $('input[name=deleteVmail]').button({disabled:false});                                        
        }else{  
            $('input[name=email_vm]').attr('disabled', true);   
            $('input[name=password_vm]').attr('disabled', true);
            $('input[name=emailAttachment_vm]').button({disabled:true});
            $('input[name=playCid_vm]').button({disabled:true});
            $('input[name=playEnvelope_vm]').button({disabled:true});
            $('input[name=deleteVmail]').button({disabled:true});                   
        }
      });


/* oculta el mensaje que aprece al cargar la pagina, siempre y cuando haya un error con el efecto slide*/
    $(".close").click(function() {
        $("#initial_message_area").slideUp();   
        $("#message_area").slideUp();      
    });

    $(".close").click(function() {
        $("#message_area").slideUp();      
    });



});



$(function() {
  $( "#radio_do_not_disturb" ).buttonset();
  $( "#radio_call_waiting" ).buttonset();
  $( "#radio_call_forward" ).buttonset();
  $( "#radio_call_forward_unavailable" ).buttonset();
  $( "#radio_call_forward_busy" ).buttonset();
  $( "#radio_record_incoming" ).buttonset();
  $( "#radio_record_outgoing" ).buttonset();
  $( "#radio_status" ).buttonset();
  $( "#radio_email_attachment" ).buttonset();
  $( "#radio_play_cid" ).buttonset();
  $( "#radio_play_envelope" ).buttonset();
  $( "#radio_delete_vmail" ).buttonset();
});

function editExten(){
    showElastixUFStatusBar("Saving...");
    var arrAction = new Array();
    arrAction["menu"]="my_extension";
    arrAction["action"]="editExten";
    arrAction["secretExtension"]=$("input[name='secretExtension']").val();
    arrAction["doNotDisturb"]=$("input[name='doNotDisturb']:checked").val();
    arrAction["callWaiting"]=$("input[name='callWaiting']:checked").val();
    arrAction["callForwardOpt"]=$("input[name='callForwardOpt']:checked").val();
    arrAction["callForwardUnavailableOpt"]=$("input[name='callForwardUnavailableOpt']:checked").val();
    arrAction["callForwardBusyOpt"]=$("input[name='callForwardBusyOpt']:checked").val();
    arrAction["callForwardInp"]=$("input[name='callForwardInp']").val();
    arrAction["callForwardUnavailableInp"]=$("input[name='callForwardUnavailableInp']").val();
    arrAction["callForwardBusyInp"]=$("input[name='callForwardBusyInp']").val();
    arrAction["recordIncoming"]=$("input[name='recordIncoming']:checked").val();
    arrAction["recordOutgoing"]=$("input[name='recordOutgoing']:checked").val();
    arrAction["status_vm"]=$("input[name='status_vm']:checked").val();
    arrAction["email_vm"]=$("input[name='email_vm']").val();
    arrAction["password_vm"]=$("input[name='password_vm']").val();  
    arrAction["language_vm"]=$("select[name='language_vm'] option:selected").val();
    arrAction["emailAttachment_vm"]=$("input[name='emailAttachment_vm']:checked").val();
    arrAction["playCid_vm"]=$("input[name='playCid_vm']:checked").val();
    arrAction["playEnvelope_vm"]=$("input[name='playEnvelope_vm']:checked").val();
    arrAction["deleteVmail"]=$("input[name='deleteVmail']:checked").val();
    

    arrAction["rawmode"]="yes";
    request("index.php", arrAction, false,
        function(arrData,statusResponse,error){
            hideElastixUFStatusBar();
            if(error!=""){
                //alert(error);
                $("#message_area").slideDown();
                $("#msg-text").removeClass("alert-success").addClass("alert-danger");
                $("#msg-text").html(error);
            }else{
                //alert(arrData);
                $("#message_area").slideDown();
                $("#msg-text").removeClass("alert-danger").addClass("alert-success");
                $("#msg-text").html(arrData);  
            }
    });
}
