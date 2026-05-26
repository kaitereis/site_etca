/*-----------------------------------------------------------------------------------

    Theme Name: Farco
    Theme URI: http://
    Description: The Multi-Purpose Onepage Template
    Author: UI-ThemeZ
    Author URI: http://themeforest.net/user/UI-ThemeZ
    Version: 1.0

-----------------------------------------------------------------------------------*/


$(function () {

    "use strict";

    var wind = $(window);



    // scrollIt
    $.scrollIt({
        upKey: 38,                // key code to navigate to the next section
        downKey: 40,              // key code to navigate to the previous section
        easing: 'swing',          // the easing function for animation
        scrollTime: 600,          // how long (in ms) the animation takes
        activeClass: 'active',    // class given to the active nav element
        onPageChange: null,       // function(pageIndex) that is called when page is changed
        topOffset: -80            // offste (in px) for fixed top navigation
    });



    // navbar scrolling background
    wind.on("scroll", function () {

        var bodyScroll = wind.scrollTop(),
            navbar = $(".navbar"),
            logo = $(".navbar .logo> img");

        if (bodyScroll > 100) {

            navbar.addClass("nav-scroll");
            logo.attr('src', 'img/logo-dark.svg');

        } else {

            navbar.removeClass("nav-scroll");
            logo.attr('src', 'img/logo-dark.svg');
        }
    });


    // close navbar-collapse when a  clicked
    $(".navbar-nav a").on('click', function () {
        $(".navbar-collapse").removeClass("show");
    });


    // underline effect
    $(".underline").on("click", ".nav-item", function () {

        $(this).addClass("active").siblings().removeClass("active");

    });



    // progress bar
    wind.on('scroll', function () {
        $(".skill-progress .progres").each(function () {
            var bottom_of_object =
                $(this).offset().top + $(this).outerHeight();
            var bottom_of_window =
                $(window).scrollTop() + $(window).height();
            var myVal = $(this).attr('data-value');
            if (bottom_of_window > bottom_of_object) {
                $(this).css({
                    width: myVal
                });
            }
        });
    });



    // sections background image from data background
    var pageSection = $(".bg-img, section");
    pageSection.each(function (indx) {

        if ($(this).attr("data-background")) {
            $(this).css("background-image", "url(" + $(this).data("background") + ")");
        }
    });


    // === owl-carousel === //

    // Testimonials owlCarousel
    $('.testimonials .owl-carousel').owlCarousel({
        loop: true,
        center: true,
        margin: 15,
        mouseDrag: false,
        autoplay: true,
        smartSpeed: 500,
        responsiveClass: true,
        responsive: {
            0: {
                items: 1
            },
            700: {
                items: 2
            },
            1000: {
                items: 3
            }
        }
    });

    // team owlCarousel
    $('.team .owl-carousel').each(function() {
        var isSingle = $(this).hasClass('single-item');
        $(this).owlCarousel({
            loop: true,
            margin: isSingle ? 0 : 30,
            mouseDrag: true,
            autoplay: true,
            autoplayTimeout: isSingle ? 3000 : 5000,
            dotsEach: true,
            responsiveClass: true,
            responsive: isSingle ? {
                0: { items: 1 },
                600: { items: 1 },
                1000: { items: 1 }
            } : {
                0: { items: 1 },
                600: { items: 2 },
                1000: { items: 3 }
            }
        });
    });

    // === End owl-carousel === //


    // magnificPopup disabled for projects section to avoid conflicts with new modal
    /*
    $('.gallery').magnificPopup({
        delegate: '.popimg',
        type: 'image',
        gallery: {
            enabled: true
        }
    });
    */

    // Modal de Projetos (Saiba mais)
    $('.saiba-mais-btn').on('click', function (e) {
        e.preventDefault();
        var cardContainer = $(this).closest('.items');
        var title = cardContainer.find('.card-body h6').text();
        var desc = cardContainer.find('.project-full-desc').html();
        
        // Popular dados do modal
        $('#modal-project-title').text(title);
        $('#modal-project-desc').html(desc);
        
        // Popular galeria
        var galleryContainer = $('#modal-project-gallery');
        galleryContainer.empty();
        galleryContainer.removeClass('cols-1 cols-2 cols-3');
        
        var images = cardContainer.find('.project-gallery-images img');
        if (images.length > 0) {
            $('.modal-gallery-title').show();
            galleryContainer.show();
            galleryContainer.addClass('cols-' + Math.min(images.length, 3));
            images.each(function () {
                var src = $(this).attr('src');
                var alt = $(this).attr('alt') || title;
                galleryContainer.append('<img src="' + src + '" alt="' + alt + '">');
            });
        } else {
            $('.modal-gallery-title').hide();
            galleryContainer.hide();
        }
        
        // Exibir modal
        $('#project-modal').addClass('active');
        $('body').css('overflow', 'hidden'); // Travar scroll da página
    });

    // Fechar modal
    function closeProjectModal() {
        $('#project-modal').removeClass('active');
        $('body').css('overflow', ''); // Destravar scroll
    }

    $('#close-project-modal').on('click', function () {
        closeProjectModal();
    });

    // Fechar modal ao clicar fora do conteúdo
    $(window).on('click', function (e) {
        if ($(e.target).is('#project-modal')) {
            closeProjectModal();
        }
    });


    // countUp
    $('.numbers .count').countUp({
        delay: 10,
        time: 1500
    });


    // Tabs
    $(".price-gat").on("click", "li", function () {

        var myID = $(this).attr("id");

        $(this).addClass("active").siblings().removeClass("active");

        $("#" + myID + "-content").addClass("show").removeClass("hide")
            .siblings().addClass("hide").removeClass("show");

    });


});


// === window When Loading === //

$(window).on("load", function () {

    var wind = $(window);

    // Preloader
    $(".loading").fadeOut(500);


    // stellar
    wind.stellar();


    // isotope
    $('.gallery').isotope({
        // options
        itemSelector: '.items'
    });

    var $gallery = $('.gallery').isotope({
        // options
    });

    // filter items on button click
    $('.filtering').on('click', 'span', function () {

        var filterValue = $(this).attr('data-filter');

        $gallery.isotope({ filter: filterValue });

    });

    $('.filtering').on('click', 'span', function () {

        $(this).addClass('active').siblings().removeClass('active');

    });


    // contact form validator
    $('#contact-form').validator();

    $('#contact-form').on('submit', function (e) {
        if (!e.isDefaultPrevented()) {
            var url = "contact.php";

            $.ajax({
                type: "POST",
                url: url,
                data: $(this).serialize(),
                dataType: 'json',
                success: function (data) {
                    var messageAlert = 'alert-' + data.type;
                    var messageText = data.message;

                    var alertBox = '<div class="alert ' + messageAlert + ' alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' + messageText + '</div>';
                    if (messageAlert && messageText) {
                        $('#contact-form').find('.messages').html(alertBox);
                        if (data.type === 'success') {
                            $('#contact-form')[0].reset();
                        }
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Erro no envio do formulário:", status, error);
                    console.log("Resposta do servidor:", xhr.responseText);
                    var alertBox = '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Ocorreu um erro ao enviar a mensagem. Por favor, tente novamente mais tarde.</div>';
                    $('#contact-form').find('.messages').html(alertBox);
                }
            });
            return false;
        }
    });

});


// Slider 
$(document).ready(function () {

    var owl = $('.header .owl-carousel');


    // Slider owlCarousel
    $('.slider .owl-carousel').owlCarousel({
        items: 1,
        loop: true,
        margin: 0,
        autoplay: true,
        smartSpeed: 500
    });

    // Slider owlCarousel
    $('.slider-fade .owl-carousel').owlCarousel({
        items: 1,
        loop: true,
        margin: 0,
        autoplay: true,
        smartSpeed: 500,
        animateOut: 'fadeOut'
    });

    owl.on('changed.owl.carousel', function (event) {
        var item = event.item.index - 2;     // Position of the current item
        $('h4').removeClass('animated fadeInLeft');
        $('h1').removeClass('animated fadeInRight');
        $('p').removeClass('animated fadeInUp');
        $('.butn').removeClass('animated zoomIn');
        $('.owl-item').not('.cloned').eq(item).find('h4').addClass('animated fadeInLeft');
        $('.owl-item').not('.cloned').eq(item).find('h1').addClass('animated fadeInRight');
        $('.owl-item').not('.cloned').eq(item).find('p').addClass('animated fadeInUp');
        $('.owl-item').not('.cloned').eq(item).find('.butn').addClass('animated zoomIn');
    });

});