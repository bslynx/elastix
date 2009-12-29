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
	}
);

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