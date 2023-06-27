
"use strict";

(function($) { "use strict";

	$(function() {
		var header = $(".start-style");
		$(window).scroll(function() {    
			var scroll = $(window).scrollTop();
		
			if (scroll >= 10) {
				header.removeClass('start-style').addClass("scroll-on");
			} else {
				header.removeClass("scroll-on").addClass('start-style');
			}
		});
	});		
	


	$(document).ready(function() {
		$('body.hero-anime').removeClass('hero-anime');
	});
	$('body').on('mouseenter mouseleave','.nav-item',function(e){
			if ($(window).width() > 750) {
				var _d=$(e.target).closest('.nav-item');_d.addClass('show');
				setTimeout(function(){
				_d[_d.is(':hover')?'addClass':'removeClass']('show');
				},1);
			}
	});	
	
	
	
	 
	
  })(jQuery); 


//   $('.owl-carousel').owlCarousel({
//   loop: true,
//   margin: 10,
//   nav: true,
//   navText: [
//     "<i class='fa fa-caret-left'></i>",
//     "<i class='fa fa-caret-right'></i>"
//   ],
//   autoplay: true,
//   autoplayHoverPause: true,
//   responsive: {
//     0: {
//       items: 1
//     },
//     600: {
//       items: 3
//     },
//     1000: {
//       items: 4
//     }
//   }
// })



var titleMain  = $("#animatedHeading");
var titleSubs  = titleMain.find("slick-active");

if (titleMain.length) {

  titleMain.slick({
    autoplay: false,
    arrows: true,
    dots: false,
    slidesToShow: 1,
     slidesToScroll: 1,
 
    draggable: false,
    // infinite: false,
    pauseOnHover: false,
    swipe: false,
    touchMove: false,
    vertical: true,
    // speed: 1000,
    // autoplaySpeed: 2000,
    useTransform: true,
     centerPadding: '60px',
    adaptiveHeight: true,
  });

  // On init
  $(".slick-dupe").each(function(index, el) {
    $("#animatedHeading").slick('slickAdd', "<div>" + el.innerHTML + "</div>");    
  });

  // Manually refresh positioning of slick
  titleMain.slick('slickPlay');
};



 $('.brands-slider').slick({
   slidesToShow: 4,
   slidesToScroll: 1,
   asNavFor: '.slider-nav',
   dots: true,
   focusOnSelect: true,
   responsive: [
    {
      breakpoint: 1024,
      settings: {
        slidesToShow: 4,
        slidesToScroll: 1,
        infinite: true,
        dots: true
      }
    },
    {
      breakpoint: 480,
      settings: {
        slidesToShow: 1,
        slidesToScroll: 1,
        autoplay: true
      }
    } ]
 });


 
 $('.owl-carousel').slick({
   slidesToShow: 3,
   slidesToScroll: 1,
   asNavFor: '.slider-nav',
   dots: true,
   focusOnSelect: true,
   nav:false,
   autoplay: true,
   autoplaySpeed: 2000,
   responsive: [
    {
      breakpoint: 1024,
      settings: {
        slidesToShow: 3,
        slidesToScroll: 3,
        infinite: true,
        dots: true
      }
    },
    {
      breakpoint: 800,
      settings: {
        slidesToShow: 2,
        slidesToScroll: 2
      }
    },
    {
      breakpoint: 600,
      settings: {
        slidesToShow: 2,
        slidesToScroll: 2
      }
    },
    {
      breakpoint: 480,
      settings: {
        slidesToShow: 1,
        slidesToScroll: 1,
        autoplay: false
      }
    } ]
 });

 $('.owl-testi').slick({
  slidesToShow: 1,
  slidesToScroll: 1,
  asNavFor: '.slider-nav',
  dots: true,
  focusOnSelect: false,
  nav:false,
  autoplay: true,
  autoplaySpeed: 2000
});


// ---------------- news letter subscribe ----------------------------

$(function () {
  $('#subscribeID').on('click', function () {
    var emailid = $('#email_subscribe').val();
    if(!$('#email_subscribe').val())
    {
      alertify.alert('Required', "Email ID is required");
    }
    else
    {
      $.ajax({
        url: 'https://api.homeoftraining.com/marketing/subscribe_newsLetter',
        method:'POST',
        data: {
          subscribeEmail: emailid
        },
        dataType: 'json', 
        success :  function(data)
            {
              alertify.alert('Message', data.success);
              var json = JSON.parse(data);
            },
        error: function (data,responseJSON, status, err) {
          alertify.alert('Error', 'Please check you email address');
        }
      });
    }
  });
});

// ---------------------  end -----------------------------------