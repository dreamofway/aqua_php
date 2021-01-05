$(document).ready(function(){
  var swiper = new Swiper('.swiper-container', {
     pagination: {
       el: '.swiper-pagination',
       type: 'progressbar',
     },
     navigation: {
       nextEl: '.swiper-button-next',
       prevEl: '.swiper-button-prev',
     },
   });

   $(function(){
		setTimeout(function() {
			$('.sub_txt').fadeIn();
			setTimeout(function() {
				$('.main_txt').fadeIn();
    		}, 500);
    	}, 300);
    });



    $('.home_menu li a, .m_menu li a').on('click', function(e){
      e.preventDefault();
      var targetHref = $(this).attr('href');
      var headerHeight = $('.header').outerHeight();
    	$('html, body').animate({
    		scrollTop: $(targetHref).offset().top - 80
    	}, 600);
    })


    var lstId;
    var $sections = $('section');
    $(window).scroll(function(){
      var currentScroll = $(this).scrollTop();
      var $currentSection;

      $sections.each(function(){
        var divPosition = $(this).offset().top -80;
        if( divPosition - 1 < currentScroll ){
          $currentSection = $(this);
        }
        var id = $currentSection.attr('id');
        lstId = id;
      });

      var navAct = $('.home_menu li a');
      navAct.each(function(){
        currentNav= $(this).attr('href');
        if(currentNav == '#'+lstId){
          $('.home_menu li').removeClass('on');
          $(this).parent('li').addClass('on');
        }
        if(lstId == undefined){
          $('.home_menu li').removeClass('on');
        }
      });


      var showMenuPos = 300;
      if(currentScroll > showMenuPos){
        $('.home_menu').fadeIn();
      }else{
        $('.home_menu').fadeOut();
      }
    });


      $('.gnb-menu-list, .gnb-sub-wrap').on("mouseenter", function() {
          $('.gnb-sub-wrap').stop(true, false).slideDown();
      }).on("mouseleave",  function() {
        $('.gnb-sub-wrap').stop(true, false).slideUp();

      });


      $('.btn').on('click', function(){
        let txtScript = document.getElementById('txtScript');

        if(txtScript.style.display == 'block'){
          $('.txt_script_wrap .fas').removeClass('fa-angle-up')
          txtScript.style.display = 'none';
        }else{
          $('.txt_script_wrap .fas').addClass('fa-angle-up');
          txtScript.style.display = 'block';
        }
      });


    // mobile
    $('.btn_menu').on('click', function(){
      $('.m_menu').slideToggle();
    });

    $('.m_menu li').on('click', function(){
      $(this).parent('ul').hide();
    });
});
