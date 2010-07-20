/*
 *         mbScrollable, developed by Matteo Bicocchi on JQuery
 *        � 2002-2009 Open Lab srl, Matteo Bicocchi
 *			    www.open-lab.com - info@open-lab.com
 *       	version 1.5
 *       	tested on: 	Explorer, Chrome, FireFox for PC
 *                  		FireFox and Safari for Mac Os X
 *                  		FireFox for Linux
 *         MIT (MIT-LICENSE.txt) licenses.
 */


$(function(){
	
      $("#myScroll1").mbScrollable({
        dir:"vertical",
        width:357,
        height:115,
        elementsInPage:4,
        elementMargin:1,
        shadow:"#999 2px 2px 2px",
        controls:"#controls1",
        slideTimer:600,
        autoscroll:true,
        scrollTimer:6000
      });

      $("#myScroll").mbScrollable({
        width:390,
        elementsInPage:1,
        elementMargin:2,
        shadow:"#999 2px 2px 2px",
        height:"auto",
        controls:"#controls",
        slideTimer:600,
        autoscroll:true,
        scrollTimer:4000
      });

      setTimeout(function(){$("#wrapper").fadeIn();},2000);
    });

function cambiarVertical(){
	$('#orizontal').fadeOut(500,function(){$('#vertical').fadeIn();});
}
function cambiarHorizontal(){
	$('#vertical').fadeOut(500,function(){$('#orizontal').fadeIn();});
}
(function($) {
  $.mbScrollable= {
    plugin:"mb.scroller",
    author:"MB",
    version:"1.0",
    defaults:{
      dir:"orizontal",
      width:500,
      elementsInPage:4,
      elementMargin:2,
      shadow:false,
      height:"auto",
      controls:"#controls",
      slideTimer:600,
      autoscroll:false,
      scrollTimer:6000
    },

    buildMbScrollable: function(options){

      return this.each (function (){
        this.options = {};
        $.extend (this.options, $.mbScrollable.defaults);
        $.extend (this.options, options);

        var mbScrollable=this;
        var el=this;
        this.isVertical= this.options.dir!="orizontal";
        var controls=$(this.options.controls);
        this.idx=1;
        this.scrollTo=0;
        this.elements= $(this).children();
        this.elements.addClass("scrollEl");
        controls.hide();

        $(this).children().each(function(){$(this).wrap("<div class='SECont'></div>");});
        if (this.options.shadow){
          $(this.elements).css("-moz-box-shadow",this.options.shadow);
          $(this.elements).css("-webkit-box-shadow",this.options.shadow);
        }
        this.elements= $(this).children();
        var eip= this.options.elementsInPage<this.elements.size()?this.options.elementsInPage:this.elements.size();
        if(this.isVertical){
          this.singleElDim= (this.options.height/eip)-this.options.elementMargin;
          $(this.elements).css({marginBottom:this.options.elementMargin, height:this.singleElDim, width:this.options.width});
        }else{
          this.singleElDim= (this.options.width/eip)-this.options.elementMargin;
          $(this.elements).css({marginRight:this.options.elementMargin, width:this.singleElDim, display:"inline-block" }); //float:"left"
        }
        this.elementsDim= (this.singleElDim*this.elements.size())+(this.options.elementMargin*this.elements.size());
        this.totalPages= Math.ceil(this.elements.size()/this.options.elementsInPage);

        var adj= $.browser.safari && el.options.elementsInPage>2?this.options.elementMargin/(this.options.elementsInPage):0;

        if(this.isVertical)
          $(mbScrollable).css({overflow:"hidden", height:this.options.height-adj,width:this.options.width, paddingRight:5, position:"relative"});
        else
          $(mbScrollable).css({overflow:"hidden", width:this.options.width-adj,height:this.options.height,paddingBottom:5, position:"relative"});

       var mbscrollableStrip=$("<div class='scrollableStrip'/>").css({width:this.elementsDim, position:"relative"});
        $(this.elements).wrapAll(mbscrollableStrip);
        this.mbscrollableStrip=$(this).find(".scrollableStrip");
        $(this.elements).hover(
                function(){
                  if($(mbScrollable)[0].autoScrollActive)
                    $(mbScrollable).mbStopAutoscroll();
                },
                function(){
                  if($(mbScrollable)[0].autoScrollActive)
                    $(mbScrollable).mbAutoscroll();
                }
                );
        if(this.options.autoscroll && this.elements.size()>this.options.elementsInPage){
          this.autoScrollActive=true;
          $(this).mbAutoscroll();
        }
        $(this).mbPageIndex();
        $(mbScrollable).mbActivateControls();
        setTimeout(function(){
          $(".scrollEl").fadeIn();
        },1000);
        $(mbScrollable).mbManageControls();
      });
    },
    mbNextPage: function(){
      var el= $(this).get(0);
      if(el.idx==el.totalPages){
        $(this).mbManageControls();
        return;
      }

      var adj=  $.browser.safari && el.options.elementsInPage>2?el.options.elementMargin/el.options.elementsInPage:0;

      el.scrollTo-=((el.singleElDim+el.options.elementMargin)*el.options.elementsInPage)-adj;

      if (el.isVertical){
        if ((el.scrollTo<-el.elementsDim+el.options.height))
          el.scrollTo=-el.elementsDim+el.options.height;
        $(el.mbscrollableStrip).animate({marginTop:el.scrollTo},el.options.slideTimer);
      }else{
        if ((el.scrollTo<-el.elementsDim+el.options.width))
          el.scrollTo=-el.elementsDim+el.options.width;
        $(el.mbscrollableStrip).animate({marginLeft:el.scrollTo},el.options.slideTimer);
      }
      el.idx+=1;
      $(this).mbManageControls();
    },
    mbPrevPage: function(){
      var el= $(this).get(0);
      if(el.idx==1){
        $(this).mbManageControls();
        return;
      }
      var adj=  $.browser.safari && el.options.elementsInPage>2?el.options.elementMargin/el.options.elementsInPage:0;

      el.scrollTo+=((el.singleElDim+el.options.elementMargin)*el.options.elementsInPage)+adj;

      if (el.isVertical){
        if (el.scrollTo>=0) el.scrollTo=0;
        $(el.mbscrollableStrip).animate({marginTop:el.scrollTo},el.options.slideTimer);
      }else{
        if (el.scrollTo>=0) el.scrollTo=0;
        $(el.mbscrollableStrip).animate({marginLeft:el.scrollTo},el.options.slideTimer);
      }
      el.idx-=1;
      $(this).mbManageControls();
    },
    mbFirstPage: function(){
      var el= $(this).get(0);
      el.scrollTo=0;
      if (el.isVertical){
        $(el.mbscrollableStrip).animate({marginTop:el.scrollTo},el.options.slideTimer);
      }else{
        $(el.mbscrollableStrip).animate({marginLeft:el.scrollTo},el.options.slideTimer);
      }
      el.idx=1;
      $(this).mbManageControls();
      $(el).mbStopAutoscroll();
    },
    mbLastPage: function(){
      var el= $(this).get(0);
      if (el.isVertical){
        el.scrollTo=-el.elementsDim+el.options.height;
        $(el.mbscrollableStrip).animate({marginTop:el.scrollTo},el.options.slideTimer);
      }else{
        el.scrollTo=-el.elementsDim+el.options.width;
        $(el.mbscrollableStrip).animate({marginLeft:el.scrollTo},el.options.slideTimer);
      }
      el.idx=el.totalPages;
      $(this).mbManageControls();
      $(el).mbStopAutoscroll();
    },
    mbPageIndex: function(){
      var el= $(this).get(0);
      var controls=$(el.options.controls);
      var pages=controls.find(".pageIndex");
      if (pages){
        function getPage(i){
          el.scrollTo=-((el.singleElDim+el.options.elementMargin)*(el.options.elementsInPage*(i-1)));
          if(el.isVertical){
            if (el.scrollTo<-el.elementsDim+el.options.height)
              el.scrollTo=-el.elementsDim+el.options.height;
            $(el.mbscrollableStrip).animate({marginTop:el.scrollTo},el.options.slideTimer);
          }else{
            if (el.scrollTo<-el.elementsDim+el.options.width)
              el.scrollTo=-el.elementsDim+el.options.width;
            $(el.mbscrollableStrip).animate({marginLeft:el.scrollTo},el.options.slideTimer);
          }
          el.idx = Math.floor(i);
          $(el).mbManageControls();
        }
        var n=0;
        for(var i=1;i<=el.totalPages;i++){
          n++;
          var p=$("<span class='page'> "+n+" <\/span>").bind("click",function(){
            getPage($(this).html());
            $(el).mbStopAutoscroll();
          });
          pages.append(p);
        };
      }
    },
    mbAutoscroll:function(){
      var dir= "next";
      var el= $(this).get(0);

      if(el.autoscroll) return;
      var timer=el.options.scrollTimer;
      el.autoscroll = true;
      el.auto = setInterval(function(){
        dir= el.idx==1?"next":el.idx==el.totalPages?"prev":dir;
        if(dir=="next")
          $(el).mbNextPage();
        else
          $(el).mbPrevPage();
      },timer);
      $(el).mbManageControls();
    },
    mbStopAutoscroll: function(){
      var el= $(this).get(0);
      el.autoscroll = false;
      clearInterval(el.auto);
      $(el).mbManageControls();
    },

    mbActivateControls: function(){
      var mbScrollable=this;
      var el= $(mbScrollable).get(0);
      var controls=$(el.options.controls);
      controls.find(".first").bind("click",function(){$(mbScrollable).mbFirstPage();});
      controls.find(".prev").bind("click",function(){$(mbScrollable).mbStopAutoscroll();$(mbScrollable).mbPrevPage();});
      controls.find(".next").bind("click",function(){$(mbScrollable).mbStopAutoscroll();$(mbScrollable).mbNextPage();});
      controls.find(".last").bind("click",function(){$(mbScrollable).mbLastPage();});
      controls.find(".start").bind("click",function(){$(mbScrollable).mbAutoscroll();$(mbScrollable)[0].autoScrollActive=true;});
      controls.find(".stop").bind("click",function(){$(mbScrollable).mbStopAutoscroll();$(mbScrollable)[0].autoScrollActive=false;});
    },

    mbManageControls:function(){
      var mbScrollable=this;
      var el= $(mbScrollable).get(0);
      var controls=$(el.options.controls);
      if (el.elements.size()<=el.options.elementsInPage){
        controls.hide();
      }else{
        controls.fadeIn();
      }
      if (el.idx==el.totalPages){
        controls.find(".last").addClass("disabled");
        controls.find(".next").addClass("disabled");
      }else{
        controls.find(".last").removeClass("disabled");
        controls.find(".next").removeClass("disabled");
      }

      if (el.idx==1){
        controls.find(".first").addClass("disabled");
        controls.find(".prev").addClass("disabled");
      }else{
        controls.find(".first").removeClass("disabled");
        controls.find(".prev").removeClass("disabled");
      }

      if (el.autoscroll){
        controls.find(".start").addClass("sel");
        controls.find(".stop").removeClass("sel");
      }else{
        controls.find(".start").removeClass("sel");
        controls.find(".stop").addClass("sel");
      }

      controls.find(".page").removeClass("sel");
      controls.find(".page").eq(el.idx-1).addClass("sel");
      controls.find(".idx").html(el.idx+" / "+el.totalPages);
    }
  };

  $.fn.mbScrollable=$.mbScrollable.buildMbScrollable;
  $.fn.mbNextPage=$.mbScrollable.mbNextPage;
  $.fn.mbPrevPage=$.mbScrollable.mbPrevPage;
  $.fn.mbFirstPage=$.mbScrollable.mbFirstPage;
  $.fn.mbLastPage=$.mbScrollable.mbLastPage;
  $.fn.mbPageIndex=$.mbScrollable.mbPageIndex;
  $.fn.mbAutoscroll=$.mbScrollable.mbAutoscroll;
  $.fn.mbStopAutoscroll=$.mbScrollable.mbStopAutoscroll;
  $.fn.mbActivateControls=$.mbScrollable.mbActivateControls;
  $.fn.mbManageControls=$.mbScrollable.mbManageControls;

})(jQuery);
/*
function applethidde(num) {alert("hola");
      if(num==1){
	var div1 = document.getElementById("tab1");
	var ima1 = document.getElementById("ima1");
	var accion1 = document.getElementById("a1");
	div1.style.visibility="hidden";
	ima1.src = "images/arrow_bottom.gif"
	accion1.href="javascript:applethidde('11')";
      }
      if(num==11){
	var div1 = document.getElementById("tab1");
	var ima1 = document.getElementById("ima1");
	var accion1 = document.getElementById("a1");
	div1.style.visibility="visible";
	ima1.src = "images/arrow_top.gif"
	accion1.href="javascript:applethidde('1')";
      }
      if(num==2){
   	var div2 = document.getElementById("tab2");
	var ima2 = document.getElementById("ima2");
	var accion2 = document.getElementById("a2");
	div2.style.visibility="hidden";
	ima2.src = "images/arrow_bottom.gif"
	accion2.href="javascript:applethidde('21')";
      }
      if(num==21){
	var div2 = document.getElementById("tab2");
	var ima2 = document.getElementById("ima2");
	var accion2 = document.getElementById("a2");
	div2.style.visibility="visible";
	ima2.src = "images/arrow_top.gif"
	accion2.href="javascript:applethidde('2')";
      }
      if(num==3){
	var div3 = document.getElementById("tab3");
	var div30 = document.getElementById("tab30");
	var ima3 = document.getElementById("ima3");
	var accion3 = document.getElementById("a3");
	div3.style.visibility="hidden";
	div30.style.visibility="hidden";
	ima3.src = "images/arrow_bottom.gif"
	accion3.href="javascript:applethidde('31')";
      }
      if(num==31){
	var div3 = document.getElementById("tab3");
	var div30 = document.getElementById("tab30");
	var ima3 = document.getElementById("ima3");
	var accion3 = document.getElementById("a3");
	div3.style.visibility="visible";
	div30.style.visibility="visible";
	ima3.src = "images/arrow_top.gif"
	accion3.href="javascript:applethidde('3')";
      }
      if(num==4){
   	var div4 = document.getElementById("tab4");
	var ima4 = document.getElementById("ima4");
	var accion4 = document.getElementById("a4");
	div4.style.visibility="hidden";
	ima4.src = "images/arrow_bottom.gif"
	accion4.href="javascript:applethidde('41')";
      }
      if(num==41){
   	var div4 = document.getElementById("tab4");
	var ima4 = document.getElementById("ima4");
	var accion4 = document.getElementById("a4");
	div4.style.visibility="visible";
	ima4.src = "images/arrow_top.gif"
	accion4.href="javascript:applethidde('4')";
      }
      if(num==5){
	var div5 = document.getElementById("tab5");
	var ima5 = document.getElementById("ima5");
	var accion5 = document.getElementById("a5");
	div5.style.visibility="hidden";
	ima5.src = "images/arrow_bottom.gif"
	accion5.href="javascript:applethidde('51')";
      }
      if(num==51){
	var div5 = document.getElementById("tab5");
	var ima5 = document.getElementById("ima5");
	var accion5 = document.getElementById("a5");
	div5.style.visibility="visible";
	ima5.src = "images/arrow_top.gif"
	accion5.href="javascript:applethidde('5')";
      }
      if(num==6){
	var div6 = document.getElementById("tab6");
	var ima6 = document.getElementById("ima6");
	var accion6 = document.getElementById("a6");
	div6.style.visibility="hidden";
	ima6.src = "images/arrow_bottom.gif"
	accion6.href="javascript:applethidde('61')";
      }
      if(num==61){
	var div6 = document.getElementById("tab6");
	var ima6 = document.getElementById("ima6");
	var accion6 = document.getElementById("a6");
	div6.style.visibility="visible";
	ima6.src = "images/arrow_top.gif"
	accion6.href="javascript:applethidde('6')";
      }
   }*/