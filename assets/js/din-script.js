/**
 * Dynamic Island Navbar Frontend Script
 */
(function ($) {
	'use strict';

	$(document).ready(function () {
		var $body = $('body');
		var $mobileToggle = $('.din-mobile-toggle');
		var $drawerClose = $('.din-drawer-close');
		var $overlay = $('#dinMobileOverlay');
		var $mobileDrawer = $('#dinMobileDrawer');

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

		// FunnelKit & WooCommerce Cart Trigger Integration
		$('.din-cart-btn').on('click', function (e) {
			var $this = $(this);

			// Check if FunnelKit Cart Drawer button or event exists on page
			if (typeof fk_cart_drawer !== 'undefined' || $('.fk-cart-open-btn, [data-fk-cart-toggle]').length > 0) {
				var $fkBtn = $('.fk-cart-open-btn, [data-fk-cart-toggle]').first();
				if ($fkBtn.length) {
					e.preventDefault();
					$fkBtn.trigger('click');
					return false;
				}
			}

			// If URL is #cart or hash, prevent default page jump
			var href = $this.attr('href');
			if (href === '#' || href === '#cart') {
				e.preventDefault();
				// Trigger custom WooCommerce slide cart if present
				$(document.body).trigger('wc_fragment_refresh');
			}
		});
	});
})(jQuery);
