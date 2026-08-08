/**
 * Dynamic Island ITSE Navbar Frontend Script
 */
(function ($) {
	'use strict';

	$(document).ready(function () {
		var $body = $('body');
		var $headerWrap = $('.din-header-wrap');
		var $mobileToggle = $('.din-mobile-toggle');
		var $drawerClose = $('.din-drawer-close');
		var $overlay = $('#dinMobileOverlay');

		// Handle Scroll Morphing to Dynamic Island Pill Header
		function handleScrollMorph() {
			var scrollTop = $(window).scrollTop();
			if (scrollTop > 20) {
				$headerWrap.addClass('din-scrolled');
			} else {
				$headerWrap.removeClass('din-scrolled');
			}
		}

		$(window).on('scroll load resize', function () {
			handleScrollMorph();
		});
		handleScrollMorph();

		// Toggle Language Switcher Popover
		$(document).on('click', '.din-globe-btn', function (e) {
			e.preventDefault();
			e.stopPropagation();
			var $wrapper = $(this).closest('.din-globe-wrapper');
			$wrapper.toggleClass('din-lang-open');
		});

		// Language Option Selection Active Class & GTranslate Trigger Handler
		$(document).on('click', '.din-lang-option', function (e) {
			e.preventDefault();
			var langCode = $(this).attr('data-lang') || 'en';

			$('.din-lang-option').removeClass('active');
			$(this).addClass('active');
			$('.din-globe-wrapper').removeClass('din-lang-open');

			// Trigger GTranslate
			triggerGTranslate(langCode);
		});

		function triggerGTranslate(langCode) {
			var langPair = 'en|' + langCode;

			// Method 1: Global doGTranslate function
			if (typeof window.doGTranslate === 'function') {
				window.doGTranslate(langPair);
				return;
			}
			if (typeof doGTranslate === 'function') {
				doGTranslate(langPair);
				return;
			}

			// Method 2: Select element in GTranslate widget
			var $gtSelect = $('.gt_selector, select.gtranslate_select, #gtranslate_selector, .gtranslate_wrapper select');
			if ($gtSelect.length) {
				$gtSelect.val(langPair).trigger('change');
				return;
			}

			// Method 3: GTranslate glink
			var $glink = $('.glink[data-lang="' + langCode + '"], a.glink[lang="' + langCode + '"]');
			if ($glink.length) {
				$glink[0].click();
				return;
			}

			// Method 4: Set googtrans cookie & reload
			var host = window.location.hostname;
			document.cookie = "googtrans=/en/" + langCode + "; path=/; domain=" + host;
			document.cookie = "googtrans=/en/" + langCode + "; path=/;";
			location.reload();
		}

		// Close Language Popover when clicking outside
		$(document).on('click', function (e) {
			if (!$(e.target).closest('.din-globe-wrapper').length) {
				$('.din-globe-wrapper').removeClass('din-lang-open');
			}
		});

		// Open Left Mobile Drawer
		function openDrawer() {
			$body.addClass('din-drawer-open');
			$overlay.addClass('active');
		}

		// Close Left Mobile Drawer
		function closeDrawer() {
			$body.removeClass('din-drawer-open');
			$overlay.removeClass('active');
		}

		$mobileToggle.on('click', function (e) {
			e.preventDefault();
			openDrawer();
		});

		$drawerClose.on('click', function (e) {
			e.preventDefault();
			closeDrawer();
		});

		$overlay.on('click', function () {
			closeDrawer();
		});

		// Close on ESC Key
		$(document).on('keydown', function (e) {
			if (e.key === 'Escape' || e.keyCode === 27) {
				closeDrawer();
			}
		});

		// Accordion Submenu SlideToggle Handler
		$(document).on('click', '.din-mobile-menu .din-submenu-toggle', function (e) {
			e.preventDefault();
			e.stopPropagation();
			var $btn = $(this);
			var $li = $btn.closest('li');
			var $subMenu = $li.children('ul.sub-menu');

			$li.toggleClass('din-menu-open');
			$subMenu.stop(true, true).slideToggle(220);
		});

		// Fallback: If no button exists yet, attach to parent items with children
		$('.din-mobile-menu li.menu-item-has-children, .din-mobile-menu li.din-has-children').each(function () {
			var $li = $(this);
			var $subMenu = $li.children('ul.sub-menu');

			if ($subMenu.length > 0 && $li.find('.din-submenu-toggle').length === 0) {
				var $toggleBtn = $(
					'<button type="button" class="din-submenu-toggle" aria-label="Toggle Submenu">' +
						'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#374151" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">' +
							'<polyline points="6 9 12 15 18 9"></polyline>' +
						'</svg>' +
					'</button>'
				);

				var $link = $li.children('a');
				if ($link.length) {
					$link.after($toggleBtn);
				} else {
					$li.prepend($toggleBtn);
				}
			}
		});

		// If link is empty or "#", toggle on link click
		$(document).on('click', '.din-mobile-menu li.menu-item-has-children > a, .din-mobile-menu li.din-has-children > a', function (e) {
			var href = $(this).attr('href');
			if (!href || href === '#' || href === 'javascript:void(0);') {
				e.preventDefault();
				$(this).siblings('.din-submenu-toggle').trigger('click');
			}
		});

		// FunnelKit & WooCommerce Cart Trigger Integration
		$('.din-cart-btn').on('click', function (e) {
			var $this = $(this);

			if (typeof fk_cart_drawer !== 'undefined' || $('.fk-cart-open-btn, [data-fk-cart-toggle]').length > 0) {
				var $fkBtn = $('.fk-cart-open-btn, [data-fk-cart-toggle]').first();
				if ($fkBtn.length) {
					e.preventDefault();
					$fkBtn.trigger('click');
					return false;
				}
			}

			var href = $this.attr('href');
			if (href === '#' || href === '#cart') {
				e.preventDefault();
				$(document.body).trigger('wc_fragment_refresh');
			}
		});
	});
})(jQuery);
