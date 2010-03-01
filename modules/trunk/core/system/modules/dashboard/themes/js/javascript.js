$(document).ready(
	function()
	{
		// Toggle Single Portlet
		$('a.toggle').click(function()
			{
				var p1 = $(this).parent('div');
				var p2 = p1.parent('div');
				var p3 = p2.parent('div').next('div').toggle();
				var imgarrow = $(this).children("img").attr("src");
				var id = $(this).children("img").attr("id");
				var valor = changeArrow(imgarrow,id);
				$(this).children("img").attr("src",valor);
				return false;
			}
		);

		// Invert All Portlets
		$('a#all_invert').click(function()
			{
				$('div.portlet_content').toggle();
				return false;
			}
		);

		// Expand All Portlets
		$('a#all_expand').click(function()
			{
				$('div.portlet_content:hidden').show();
				arrowsExpand();
				return false;
			}
		);

		// Collapse All Portlets
		$('a#all_collapse').click(function()
			{
				$('div.portlet_content:visible').hide();
				arrowsCollapse();
				return false;
			}
		);

		// Open All Portlets
		$('a#all_open').click(function()
			{
				$('div.portlet:hidden').show();
				$('a#all_open:visible').hide();
				$('a#all_close:hidden').show();
				return false;
			}
		);

		// Close All Portlets
		$('a#all_close').click(function()
			{
				$('div.portlet:visible').hide();
				$('a#all_close:visible').hide();
				$('a#all_open:hidden').show();
				return false;
			}
		);

		// Controls Drag + Drop
		$('#columns td').Sortable(
			{
				accept: 'portlet',
				helperclass: 'sort_placeholder',
				opacity: 0.7,
				tolerance: 'intersect'
			}
		);

        // Applet admin
        $('a#applet_admin,#close_applet_admin').click(function()
            { // variable statusDivAppletAdmin declarada en tpl applet_admin
                if(statusDivAppletAdmin=='open'){
                     $('div.portlet:hide').show();
                    $('a#all_close:hide').show();
                    $('div#div_applet_admin:visible').hide();
                    $('a#all_open:hide').show();
                    statusDivAppletAdmin='closed';
                }
                else{
                    $('div.portlet:visible').hide();
                    $('a#all_close:visible').hide();
                    $('div#div_applet_admin:hide').show();
                    $('a#all_open:visible').hide();
                    statusDivAppletAdmin='open';
                }
                return false;
            }
        );

        $('#closeCM').click(function() {
            $("#layerCM").hide();
        });
        $('[id=^editMan_],[id^=editMan1_],[id^=editMan2_]').click(function() {
            var arrID = $(this).attr("id").split("_"); 
            var a_id_card = arrID[1];

            if(arrID[0]=="editMan1") openWndMan1(a_id_card);
            else getDataCard(a_id_card);
            $("#layerCM").show();
        });
	}
);


function saveRegister(id_card)
{
    var vendor = document.getElementById("manufacturer").value;
    var num_se = document.getElementById("noSerie").value;

    var order = 'menu=dashboard&action=saveRegister&rawmode=yes&num_serie=' + num_se + '&hwd=' + id_card + '&vendor=' + vendor ;

    $.post("index.php", order,
        function(theResponse){ alert(theResponse);
            alert("Card has been registered");
            $("#layerCM").hide();
            window.open("index.php?menu=dashboard","_self");
    });
}

function getDataCard(id_card)
{
    var order = 'menu=dashboard&action=getRegister&rawmode=yes&hwd=' + id_card;

    $.post("index.php", order,
        function(theResponse){
            salida = theResponse.split(',');
            openWndMan2(salida[0],salida[1]);
    });
}

function openWndMan1(id_card)
{
     html = "<table>" +
                "<tr>" +
                    "<td colspan='2' style='font-size: 11px'>" +
                        "<font style='color:red'>Card has not been Registered</font>" +
                    "</td>" +
                "</tr>" +
                "<tr>" +
                    "<td><label style='font-size: 11px'>Vendor:</label></td>" +
                    "<td><select id='manufacturer' name='manufacturer' >" +
                        "<option value='Digium' label='Digium'>Digium</option>" +
                        "<option value='OpenVox' label='OpenVox'>OpenVox</option>" +
                        "<option value='Rhino' label='Rhino'>Rhino</option>" +
                        "<option value='Sangoma' label='Sangoma'>Sangoma</option>" +
                        "<option value='RedFone' label='RedFone'>RedFone</option>" +
                        "<option value='XorCom' label='XorCom'>XorCom</option>" +
                        "<option value='Dialogic' label='Dialogic'>Dialogic</option>" +
                        "<option value='Otros' label='Otros'>Otros</option>" +
                    "</select></td>" +
                "</tr> <tr>" +
                    "<td><label style='font-size: 11px'>Serial Number:</label></td>" +
                    "<td><input type='text' value='' name='noSerie' id='noSerie' /></td>" +
                "</tr> <tr>" +
                    "<td align='center' colspan='2'>" +
                        "<input type='button' value='Save' class='boton'onclick='saveRegister(\"" + id_card + "\");' />" +
                    "</td>" +
                "</tr>" +
            "</table>";

    document.getElementById("layerCM_content").innerHTML = html;
}


function openWndMan2(vendor,num_serie)
{
     html = "<table>" +
                "<tr>" +
                    "<td colspan='2' style='font-size: 11px'>" +
                        "<font style='color:green'>Card has been Registered</font>" +
                    "</td>" +
                "</tr>" +
                "<tr>" +
                    "<td><label style='font-size: 11px'>Vendor: "+ vendor + "</label></td>" +
                "</tr> <tr>" +
                    "<td><label style='font-size: 11px'>Serial Number: " + num_serie + "</label></td>" +
                "</tr>" +
            "</table>";
    document.getElementById("layerCM_content").innerHTML = html;
}


function changeArrow(urlimg,id){
  var sal = "";
  var imgID = document.getElementById(id);
  if(urlimg.indexOf('arrow_bottom.gif')!=-1){ 
    sal = "/images/arrow_top.gif";
  }
  else{
    sal = "/images/arrow_bottom.gif";
  }
  return sal;
}

function arrowsCollapse(){
  for(var i=1; i<=12; i++){
    var id = "imga"+i;
    var imgID = document.getElementById(id);
    imgID.src = "/images/arrow_bottom.gif";
  }
}

function arrowsExpand(){
  for(var i=1; i<=12; i++){
    var id = "imga"+i;
    var imgID = document.getElementById(id);
    imgID.src = "/images/arrow_top.gif";
  }
}