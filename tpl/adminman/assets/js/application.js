var app = function() {

    var init = function() {

        tooltips();
       // toggleMenuLeft();
       // toggleMenuRight();
        menu();
        togglePanel();
        closePanel();
    };

    var tooltips = function() {
        $('#toggle-left').tooltip();
    };

    var togglePanel = function() {
        $('.actions > .fa-chevron-down').click(function() {
            $(this).parent().parent().next().slideToggle('fast');
            $(this).toggleClass('fa-chevron-down fa-chevron-up');
        });

    };

    var toggleMenuLeft = function() {
        $('#toggle-left').bind('click', function(e) {
          /*  if (!$('.sidebarRight').hasClass('.sidebar-toggle-right')) {
                $('.sidebarRight').removeClass('sidebar-toggle-right');
                $('.main-content-wrapper').removeClass('main-content-toggle-right');
            }*/
            $('.sidebar').toggleClass('sidebar-toggle');
            $('.main-content-wrapper').toggleClass('main-content-toggle-left');
            e.stopPropagation();
        });
    };

    /*var toggleMenuRight = function() {
        $('#toggle-right').bind('click', function(e) {

            if (!$('.sidebar').hasClass('.sidebar-toggle')) {
                $('.sidebar').addClass('sidebar-toggle');
                $('.main-content-wrapper').addClass('main-content-toggle-left');
            }

            $('.sidebarRight').toggleClass('sidebar-toggle-right animated bounceInRight');
            $('.main-content-wrapper').toggleClass('main-content-toggle-right');

            if ( $(window).width() < 660 ) {
                $('.sidebar').removeClass('sidebar-toggle');
                $('.main-content-wrapper').removeClass('main-content-toggle-left main-content-toggle-right');
             };

            e.stopPropagation();
        });
    };*/

    var closePanel = function() {
        $('.actions > .fa-times').click(function() {
            $(this).parent().parent().parent().fadeOut();
        });

    }

    var menu = function() {
        $("#leftside-navigation .sub-menu > a").click(function(e) {
            $("#leftside-navigation ul ul").slideUp();
            if (!$(this).next().is(":visible")) {
                $(this).next().slideDown();
            }
              e.stopPropagation();
        });
    };
    //End functions

    //Dashboard functions
    var timer = function() {
        $('.timer').countTo();
    };

    //Sliders
    var sliders = function() {
        $('.slider-span').slider()
    };

    //return functions
    return {
        init: init,
        timer: timer
    };
}();

//Load global functions
$(document).ready(function() {
    app.init();

    var objSidebar = $('.sidebar');
    // Show sidebar
    function showSidebar(){
        objSidebar.removeClass('sidebar-toggle');
        $('.main-content-wrapper').removeClass('main-content-toggle-left');
        $.cookie('sidebar-open', true, { expires: 30 });
    }

    // Hide sidebar
    function hideSidebar(){
        objSidebar.addClass('sidebar-toggle');
        $('.main-content-wrapper').addClass('main-content-toggle-left');
        $.removeCookie('sidebar-open');
    }

    // Toggle sidebar
    $('#toggle-left').click(function(e){
        if ( objSidebar.hasClass('sidebar-toggle') ) showSidebar();
        else hideSidebar();
        e.stopPropagation();
    });

    // Load preference
    if ($.cookie('sidebar-open')) {
        showSidebar();
    };
    $('#container').show();

    // fermeture auto des alertes
    window.setTimeout(function() { $(".autoclose").alert('close'); }, 10000);
});
