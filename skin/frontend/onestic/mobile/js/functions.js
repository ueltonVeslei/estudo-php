// Global vars
var upsellSlider;

;(function($) {
	
	$(document).ready(function() {
		
		fixedHeader();
		elementsResize();
		toggleMainMenu();
		headerCart();
		showHideContainer();
		slideHome();
		categoryViewEvents();
		productViewEvents();
		cmsEvents();
		checkoutEvents();
		footerEvents();
		
	});
	
	window.addEventListener('resize', function() {
		
		elementsResize();
		
	});
	
	function fixedHeader() {
		
		$(window).scroll(function () {
			if ($(this).scrollTop() > 0)
				$('#topo-site').addClass('f-header');
			else
				$('#topo-site').removeClass('f-header');
		});
		
	}
	
	function elementsResize() {
		
		var sizeLess = 45;
		
		// Menú
		var menuWidth  = $(window).width() - sizeLess;
		var menuHeight = $(window).height();
		$('.top-nav').width(menuWidth);
		$('.top-nav').height(menuHeight);
		
		// Cart
		var cartWidth  = $(window).width() - sizeLess;
		$('.minicart-wrapper').width(cartWidth);
		
		// Layout min height
		var htmlHeight    = $('html').height();
		var screenHeight  = menuHeight;
		var contentHeight = screenHeight - sizeLess;
		if(htmlHeight < screenHeight) {
			$('html').addClass('setHeight');
			$('.wrapper, .page').height(contentHeight);
		} else {
			$('html').removeClass('setHeight');
			$('.wrapper, .page').css('height', 'auto');
		}
		
		if(($('#my-orders-table').length > 0 || $('.sales-order-invoice .my-account .data-table').length > 0 || $('.sales-order-creditmemo .my-account .data-table').length > 0) && window.innerWidth < 550) {
			$('.customer-account-index #my-orders-table thead th:nth-child(5), .customer-account-index #my-orders-table tbody td:nth-child(5), .sales-order-history #my-orders-table thead th:nth-child(5), .sales-order-history #my-orders-table tbody td:nth-child(5)').hide();
			$('#my-orders-table thead th:nth-child(3), #my-orders-table tbody td:nth-child(3)').hide();
			$('.sales-order-invoice .my-account .data-table thead th:nth-child(3), .sales-order-invoice .my-account .data-table tbody td:nth-child(3)').hide();
			$('#my-orders-table tfoot td:first-child, .sales-order-invoice .my-account .data-table tfoot td:first-child').attr('colspan', 3);
			$('.sales-order-creditmemo .my-account .data-table thead th:nth-child(3), .sales-order-creditmemo .my-account .data-table tbody td:nth-child(3), .sales-order-creditmemo .my-account .data-table thead th:nth-child(5), .sales-order-creditmemo .my-account .data-table tbody td:nth-child(5)').hide();
			$('.sales-order-creditmemo .my-account .data-table tfoot td:first-child').attr('colspan', 4);	
		} else if($('#my-orders-table').length > 0 || $('.sales-order-invoice .my-account .data-table').length > 0 || $('.sales-order-creditmemo .my-account .data-table').length > 0) {
			$('.customer-account-index #my-orders-table thead th:nth-child(5), .customer-account-index #my-orders-table tbody td:nth-child(5), .sales-order-history #my-orders-table thead th:nth-child(5), .sales-order-history #my-orders-table tbody td:nth-child(5)').show();
			$('#my-orders-table thead th:nth-child(3), #my-orders-table tbody td:nth-child(3)').show();
			$('.sales-order-invoice .my-account .data-table thead th:nth-child(3), .sales-order-invoice .my-account .data-table tbody td:nth-child(3)').show();
			$('#my-orders-table tfoot td:first-child, .sales-order-invoice .my-account .data-table tfoot td:first-child').attr('colspan', 4);
			$('.sales-order-creditmemo .my-account .data-table thead th:nth-child(3), .sales-order-creditmemo .my-account .data-table tbody td:nth-child(3), .sales-order-creditmemo .my-account .data-table thead th:nth-child(5), .sales-order-creditmemo .my-account .data-table tbody td:nth-child(5)').show();
			$('.sales-order-creditmemo .my-account .data-table tfoot td:first-child').attr('colspan', 6);
		}
		
	}
	
	function toggleMainMenu() {
		
		$('#nav li.level0:even').addClass('even');
        $('#nav li.level0:odd').addClass('odd');
				
		$(document).on('click', '#menu-toggle', function() {
			$('html').toggleClass('hideOverflow');
			$(this).toggleClass('clicked');
			if($(this).hasClass('clicked')) {
				$('.top-nav').animate({'left': '0', 'opacity': 1}, 300);
			} else {
				$('#nav li.parent > a').removeClass('clicked');
				$('.top-nav').animate({'left': '100%', 'opacity': 0}, 300, function() {
					$('#nav ul.level0').stop(true, true).slideUp();
				});
			}
		});
		
		$(document).on('click', '.link-acesso .icon-error', function() {
			$('#menu-toggle').trigger('click');
		});
		
		$('.top-nav').bind('mousewheel DOMMouseScroll', function (e) {
		    var e0 = e.originalEvent,
		        delta = e0.wheelDelta || -e0.detail;
		    
		    this.scrollTop += ( delta < 0 ? 1 : -1 ) * 30;
		    e.preventDefault();
		});
		
		$('#nav li.parent').unbind('mouseover, mouseout');
		
		$('#nav li.level0.parent').each(function() {
			var parentHREF = $(this).find('> a').attr('href');
			$(this).find('> ul').prepend('<li><a class="view-all" href="' + parentHREF + '">' + viewAll + '</a></li>');
		});
		
		$('#nav li.level0.parent > a').on('click', function(event) {
			if(event) event.preventDefault();
			$('#nav li.level0.parent > a').not($(this)).removeClass('clicked');
			$(this).toggleClass('clicked');
			
			$('#nav ul.level0').not($(this).parent().find('ul.level0')).stop(true, true).slideUp();
			$(this).parent().find('ul.level0').stop(true, true).slideToggle();
		});
		
	}
	
	function headerCart() {
		
		$(document).on('click', '#cart-box', function() {
			$(this).toggleClass('clicked');
			if($(this).hasClass('clicked')) {
				$('.minicart-wrapper').animate({'right': '0', 'opacity': 1}, 300);
			} else {
				$('.minicart-wrapper').animate({'right': '-100%', 'opacity': 0}, 300);
			}
		});
		
	}
	
	function showHideContainer() {
		
		$(document).on('click', '#icon-search', function() {
			$(this).parent().find('.hide-content').animate({'left': 0, 'opacity': 1}, 300, function() {
				$('#search').focus().val('');
			});
		});
		
		$(document).on('focusout', '#search', function() {
			$(this).parents('#search-box').find('.hide-content').animate({'left': '100%', 'opacity': 0}, 300);
		});
		
		$(document).on('click', '#search-box .hide-content .icon-ic_close_black_24px', function() {
			$(this).parents('#search-box').find('.hide-content').animate({'left': '100%', 'opacity': 0}, 300);
		});
		
	}
	
	function slideHome() {
		
		$('.bx-slider-home').bxSlider({
		  	auto: true,
		  	hideControlOnEnd: true,
		  	pager: false,
		  	nextText: '',
		  	prevText: '',
		  	infiniteLoop: true
		});
		
		$('.bxslider-home-products').bxSlider({
		  	hideControlOnEnd: true,
		  	pager: false,
		  	nextText: 'seguinte',
		  	prevText: 'anterior',
		  	infiniteLoop: false
		});
		
		$('.bxslider-home-products li > div:even, .catalog-category-view .products-grid li:even').addClass('even');
        $('.bxslider-home-products li > div:odd, .catalog-category-view .products-grid li:odd').addClass('odd');
		
	}
	
	function categoryViewEvents() {
		
		$('.block-layered-nav .block-title').on('click', function() {
			$('.block-layered-nav .block-content').stop(true, true).slideToggle();
		});
		
		$('#narrow-by-list dt').on('click', function() {
			$('#narrow-by-list dt').not($(this)).removeClass('clicked');
			if($(this).hasClass('clicked')) {
				setTimeout(function() { $('#narrow-by-list dt').removeClass('clicked'); }, 400);	
			} else {
				$(this).addClass('clicked');
			}
			
			$('#narrow-by-list dd').not($(this).find('+ dd')).stop(true,true).slideUp();
			$(this).find('+ dd').slideToggle();
		});
		
		if($('.category-description').length > 0) {
			$('.category-description *').each(function() {
				$(this).removeAttr('style width height');
			});
		}
		
	}
	
	function productViewEvents() {
		
		$('.bxslider-product-view').bxSlider({
		  	hideControlOnEnd: true,
		  	pager: false,
		  	nextText: '',
		  	prevText: '',
		  	infiniteLoop: false
		});
		
		$('.listCards a').on('click', function(event) {
			if(event) event.preventDefault();
			$('.listCards a').not($(this)).removeClass('clicked');
			$(this).toggleClass('clicked');
            var target = '#' + $(this).attr('rel');
            $('.parcelas').not(target).stop(true,true).slideUp();
            $(target).stop(true,true).slideToggle();
            
            scrollToItemPosition($(this));
        });
        
        $('p.no-rating a').on('click', function(event) {
        	event.preventDefault();
        	$('body,html').animate({
				scrollTop: $('#customer-reviews').offset().top
			}, 200);
			setTimeout(function() {
				$('.avalie').trigger('click');
			}, 300);
			return false;
        });
        
        $('.collateral-box h4').on('click', function(event) {
        	if(event) event.preventDefault();
			$('.collateral-box h4').not($(this)).removeClass('clicked');
			$(this).toggleClass('clicked');
			
			$('.padder').not($(this).parents('.collateral-box').find('.padder')).removeClass('open');
			$(this).parents('.collateral-box').find('.padder').addClass('open');
			
			$('.padder').not($(this).parents('.collateral-box').find('.padder')).stop(true,true).slideUp();
			$(this).parents('.collateral-box').find('.padder').stop(true, true).slideToggle();
			
			scrollToItemPosition($(this));
		});
		
		upsellSlider = $('.bxslider-upsell').bxSlider({
		  	hideControlOnEnd: true,
		  	pager: false,
		  	nextText: 'seguinte',
		  	prevText: 'anterior',
		  	infiniteLoop: false
		});
		
		$('.titulo-upsell h4').on('click', function(event) {
        	if(event) event.preventDefault();
			$(this).toggleClass('clicked');
			$('#upsell-container').toggleClass('open');
			$('#upsell-container').stop(true, true).slideToggle(function() {
				upsellSlider.reloadSlider({
					hideControlOnEnd: true,
				  	pager: false,
				  	nextText: 'seguinte',
				  	prevText: 'anterior',
				  	infiniteLoop: false
				});				
			});
			
			scrollToItemPosition($(this));
		});
		
		$('.container-reviews a').on('click', function(event) {
        	if(event) event.preventDefault();
        	$('.container-reviews a').not($(this)).removeClass('clicked');
			$(this).toggleClass('clicked');
			
			$('.container-reviews > div').not($(this).parent().find('> div')).removeClass('open');
			$(this).parent().find('> div').addClass('open');
			
			$('.container-reviews > div').not($(this).parent().find('> div')).slideUp();
			$(this).parent().find('> div').stop(true, true).slideToggle();
			
			scrollToItemPosition($(this));
		});
		
		$('#review-form input[type="radio"]').on('click', function() {
			var itemValue = $(this).attr('data-index');
			$('#review-form input[type="radio"]').each(function() {
				$(this).removeAttr('checked');
				$(this).removeClass('icon-ic_favorite_black_18px');
			});
			for(i = 1; i <= itemValue; i++) {
				$('#review-form input[type="radio"]#Value_' + i).addClass('icon-ic_favorite_black_18px');
				$('#review-form input[type="radio"]#Value_' + i).attr('checked', 'checked');
			}
		});
		
	} 
	
	function scrollToItemPosition(item) {
		
		setTimeout(function() {
			$('body,html').animate({
				scrollTop: item.offset().top - 55
			}, 200);
		}, 500);
		
	}	
	
	function cmsEvents() {
		
		
		
	}
	
	function checkoutEvents() {
		
        $('#open-onestepcheckout-authentification').on('click', function(){
            $('.onestepcheckout-authentification-overlay,#onestepcheckout-authentification').stop(true, true).fadeIn();
            return false;
        });

        var status = $('.create_account_checkbox').attr('checked');
        if (!status) {
            $('.create_account_checkbox').click();
        };
        
        $('#onestepcheckout-login-forgot-link').on('click', function() {
        	$('#onestepcheckout-login-form').stop(true, true).fadeOut(function() {
        		$('#onestepcheckout-forgot-password-form').stop(true, true).fadeIn();
        	});
        });
        
        $('.onestepcheckout-forgot-password-back').on('click', function() {
        	$('#onestepcheckout-forgot-password-success, #onestepcheckout-forgot-password-form').stop(true, true).fadeOut(function() {
        		$('#onestepcheckout-login-form').stop(true, true).fadeIn();
        	});
        });
        
        $('#close-onestepcheckout-authentification').on('click', function(event) {
        	if(event) event.preventDefault();
        	$('#onestepcheckout-authentification, .onestepcheckout-authentification-overlay').stop(true, true).fadeOut();
        });
    
	}
	
	function footerEvents() {
		
		$('.menu-footer h4').on('click', function() {
			$('.menu-footer ul').not($(this).parent().find('ul')).stop(true, true).slideUp();
			$(this).parent().find('ul').stop(true, true).slideToggle();
		});
		
	}
	
})(jQuery);
