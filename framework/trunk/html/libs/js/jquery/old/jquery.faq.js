(function($) {
  jQuery.fn.faq = function(tog) {
  	return this.each(function () {
        var dl = $(this).addClass('faq')
  		var dt = $('dt', dl).css('cursor', 'pointer').addClass('faqClosed').click(function(e){
  			$(this).toggleClass('faqClosed').toggleClass('faqOpen');
            var sc = false;

            dt.each(function(){
                if ($(this).hasClass('faqClosed')) sc = true;
            });

            if(!sc) $('.faqShow').text('Hide All').toggleClass('faqShow').toggleClass('faqHide');
            else $('.faqHide').text('Show All').toggleClass('faqShow').toggleClass('faqHide');
                $(this).next().slideToggle();
  		});

  		var dd = $('dd', dl).hide().append('<a href="#faqtop" class="faqToTop"></a>');

        $('<a href="#">Show All</a>').addClass('faqShow').click(function(){
            if ($(this).hasClass('faqShow')) {
                $('.faqShow').text('Hide All').toggleClass('faqShow').toggleClass('faqHide');
                dt.filter('[class=faqClosed]').each(function(){
                    $(this).toggleClass('faqClosed').toggleClass('faqOpen');
                    $(this).next().slideToggle();
                });
            } else {
                $('.faqHide').text('Show All').toggleClass('faqShow').toggleClass('faqHide');
                dt.filter('[class=faqOpen]').each(function(){
                    $(this).toggleClass('faqClosed').toggleClass('faqOpen');
                    $(this).next().slideToggle();
                });
            };
            return false;
        }).prependTo(dl).clone(true).appendTo(dl);

		$('<a id="faqtop" style="display:none;"></a>').prependTo(dl);

        if(typeof tog == 'number') $('dt:eq('+tog+')').trigger('click');
  	});
  };
})(jQuery);

