/**
 * Dynamic Island ITSE Navbar Frontend Script
 */
(function ($) {
	'use strict';

	$(document).ready(function () {
		var $body = $('body');
		var $mobileToggle = $('.din-mobile-toggle');
		var $drawerClose = $('.din-drawer-close');
		var $overlay = $('#dinMobileOverlay');

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

		// Mobile Accordion Submenu Toggle
		$('.din-mobile-menu li').each(function () {
			var $li = $(this);
			var $subMenu = $li.children('ul.sub-menu');

			if ($subMenu.length > 0) {
				$li.addClass('din-has-children');

				// Create Accordion Dropdown Arrow Button
				var $toggleBtn = $(
					'<button type="button" class="din-submenu-toggle" aria-label="Toggle Submenu">' +
						'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
							'<polyline points="6 9 12 15 18 9"></polyline>' +
						'</svg>' +
					'</button>'
				);

				// Wrap link and toggle button in a header row if needed
				var $link = $li.children('a');
				if ($link.length) {
					$link.after($toggleBtn);
				} else {
					$li.prepend($toggleBtn);
				}

				// Toggle Accordion on Arrow Click
				$toggleBtn.on('click', function (e) {
					e.preventDefault();
					e.stopPropagation();
					$li.toggleClass('din-menu-open');
					$subMenu.stop(true, true).slideToggle(250);
				});

				// If link is empty or "#", toggle on link click as well
				$link.on('click', function (e) {
					var href = $(this).attr('href');
					if (!href || href === '#' || href === 'javascript:void(0);') {
						e.preventDefault();
						$toggleBtn.trigger('click');
					}
				});
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
