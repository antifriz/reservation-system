$(function() {
    var pull 		= $('#pull');
    menu 		= $('nav ul');

    $(pull).on('click', function(e) {
        e.preventDefault();
        menu.slideToggle();
    });
/*
    $(window).resize(function(){
        var w = $(this).width();

        if(w > 800 && menu.is(':hidden')) {
            menu.removeAttr('style');
        }
    });*/

    $('nav ul li').on('click', function(e) {
        var w = $(window).width();
        if(w < 800 ) {
            menu.slideToggle();
        }
    });

   /* $('.panel').height($(window).height());*/

});

