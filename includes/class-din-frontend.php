<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DIN_Frontend {

	public function init() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_action( 'wp_head', array( $this, 'output_theme_override_css' ), 999 );
		add_action( 'wp_body_open', array( $this, 'render_dynamic_island_header' ), 1 );
		
		add_action( 'get_header', array( $this, 'ensure_header_rendered' ), 1 );
		add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'cart_count_fragment' ) );
	}

	private static $rendered = false;

	public function ensure_header_rendered() {
		if ( ! self::$rendered ) {
			add_action( 'wp_footer', array( $this, 'render_dynamic_island_header_fallback' ), 1 );
		}
	}

	public function enqueue_frontend_assets() {
		if ( ! get_option( 'din_enable', 1 ) ) {
			return;
		}

		wp_enqueue_style(
			'din-frontend-style',
			ITSE_NAVBAR_URL . 'assets/css/din-style.css',
			array(),
			ITSE_NAVBAR_VERSION
		);

		wp_enqueue_script(
			'din-frontend-script',
			ITSE_NAVBAR_URL . 'assets/js/din-script.js',
			array( 'jquery' ),
			ITSE_NAVBAR_VERSION,
			true
		);

		$dynamic_styles = $this->generate_inline_styles();
		wp_add_inline_style( 'din-frontend-style', $dynamic_styles );
	}

	private function generate_inline_styles() {
		$island_bg     = get_option( 'din_island_bg', '#ffffff' );
		$island_radius = get_option( 'din_island_radius', 50 );
		$logo_width    = get_option( 'din_logo_width', 140 );
		$offer_bg      = get_option( 'din_offer_bg', '#2d3e18' );
		$offer_color   = get_option( 'din_offer_text_color', '#ffffff' );

		return "
			:root {
				--din-island-bg: {$island_bg};
				--din-island-radius: {$island_radius}px;
				--din-logo-width: {$logo_width}px;
				--din-offer-bg: {$offer_bg};
				--din-offer-color: {$offer_color};
			}
		";
	}

	public function output_theme_override_css() {
		if ( ! get_option( 'din_enable', 1 ) || ! get_option( 'din_hide_theme_header', 1 ) ) {
			return;
		}

		echo '<style id="din-theme-override-css">
			header:not(.din-header-wrap),
			#masthead,
			.site-header,
			.ast-main-header-wrap,
			.ast-mobile-header-wrap,
			.elementor-location-header,
			#site-header,
			.header-main {
				display: none !important;
			}
		</style>';
	}

	public function render_dynamic_island_header() {
		if ( self::$rendered || ! get_option( 'din_enable', 1 ) ) {
			return;
		}
		self::$rendered = true;

		$sticky_class = get_option( 'din_sticky', 1 ) ? 'din-sticky' : '';
		$offer_enable = get_option( 'din_offer_enable', 1 );
		$offer_text   = get_option( 'din_offer_text', '✨ BUY 3 TO SAVE 20% ✨' );
		$offer_link   = get_option( 'din_offer_link', '' );
		$logo_url     = get_option( 'din_logo_url', '' );
		?>

		<div class="din-header-wrap <?php echo esc_attr( $sticky_class ); ?>">
			<?php if ( $offer_enable && ! empty( $offer_text ) ) : ?>
				<div class="din-offer-bar">
					<?php if ( ! empty( $offer_link ) ) : ?>
						<a href="<?php echo esc_url( $offer_link ); ?>" class="din-offer-link">
							<?php echo wp_kses_post( $offer_text ); ?>
						</a>
					<?php else : ?>
						<span class="din-offer-text"><?php echo wp_kses_post( $offer_text ); ?></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="din-container">
				<nav class="din-island-nav" aria-label="<?php esc_attr_e( 'Main Navigation', 'itse-navbar' ); ?>">
					<!-- Left: Logo (Image) -->
					<div class="din-nav-left">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="din-logo-link">
							<?php if ( ! empty( $logo_url ) ) : ?>
								<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="din-logo-img" />
							<?php else : ?>
								<span class="din-site-title"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
							<?php endif; ?>
						</a>
					</div>

					<!-- Center: Desktop Menu -->
					<div class="din-nav-center">
						<?php $this->render_nav_menu( 'desktop' ); ?>
					</div>

					<!-- Right: Action Icons & Mobile Toggle -->
					<div class="din-nav-right">
						<?php if ( get_option( 'din_show_globe', 1 ) ) : ?>
							<a href="#" class="din-action-btn din-globe-btn" title="<?php esc_attr_e( 'Language / Currency', 'itse-navbar' ); ?>">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
									<circle cx="12" cy="12" r="10"></circle>
									<line x1="2" y1="12" x2="22" y2="12"></line>
									<path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
								</svg>
							</a>
						<?php endif; ?>

						<?php if ( get_option( 'din_show_cart', 1 ) ) : ?>
							<?php $this->render_cart_icon(); ?>
						<?php endif; ?>

						<?php if ( get_option( 'din_show_account', 1 ) ) : ?>
							<?php
							$account_url = get_option( 'din_account_url', '' );
							if ( empty( $account_url ) ) {
								$account_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
							}
							?>
							<a href="<?php echo esc_url( $account_url ); ?>" class="din-action-btn din-account-btn" title="<?php esc_attr_e( 'My Account', 'itse-navbar' ); ?>">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
									<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
									<circle cx="12" cy="7" r="4"></circle>
								</svg>
							</a>
						<?php endif; ?>

						<!-- Hamburger Button on Far Right in Mobile View -->
						<button type="button" class="din-mobile-toggle" aria-label="<?php esc_attr_e( 'Open Menu', 'itse-navbar' ); ?>">
							<svg class="din-icon-menu" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<line x1="3" y1="12" x2="21" y2="12"></line>
								<line x1="3" y1="6" x2="21" y2="6"></line>
								<line x1="3" y1="18" x2="21" y2="18"></line>
							</svg>
						</button>
					</div>
				</nav>
			</div>
		</div>

		<!-- Mobile Left Slide-In Off-Canvas Drawer -->
		<div class="din-mobile-overlay" id="dinMobileOverlay"></div>
		<aside class="din-mobile-drawer" id="dinMobileDrawer" aria-label="<?php esc_attr_e( 'Mobile Navigation', 'itse-navbar' ); ?>">
			<div class="din-drawer-header">
				<div class="din-drawer-logo">
					<?php if ( ! empty( $logo_url ) ) : ?>
						<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
					<?php else : ?>
						<span class="din-site-title"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
					<?php endif; ?>
				</div>
				<!-- Close Icon Button inside Mobile Drawer Tray -->
				<button type="button" class="din-drawer-close" aria-label="<?php esc_attr_e( 'Close Menu', 'itse-navbar' ); ?>">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<line x1="18" y1="6" x2="6" y2="18"></line>
						<line x1="6" y1="6" x2="18" y2="18"></line>
					</svg>
				</button>
			</div>

			<div class="din-drawer-body">
				<?php $this->render_nav_menu( 'mobile' ); ?>
			</div>

			<div class="din-drawer-footer">
				<a href="<?php echo esc_url( $account_url ); ?>" class="din-drawer-account-link">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
						<circle cx="12" cy="7" r="4"></circle>
					</svg>
					<span><?php is_user_logged_in() ? esc_html_e( 'My Account', 'itse-navbar' ) : esc_html_e( 'Login / Register', 'itse-navbar' ); ?></span>
				</a>
			</div>
		</aside>
		<?php
	}

	public function render_dynamic_island_header_fallback() {
		if ( ! self::$rendered ) {
			$this->render_dynamic_island_header();
		}
	}

	private function render_nav_menu( $type = 'desktop' ) {
		if ( 'mobile' === $type ) {
			$menu_id = get_option( 'din_mobile_menu', 0 );
			if ( ! $menu_id ) {
				$menu_id = get_option( 'din_desktop_menu', get_option( 'din_nav_menu', 0 ) );
			}
			$container_class = 'din-mobile-menu';
		} else {
			$menu_id         = get_option( 'din_desktop_menu', get_option( 'din_nav_menu', 0 ) );
			$container_class = 'din-desktop-menu';
		}

		if ( $menu_id && wp_get_nav_menu_object( $menu_id ) ) {
			wp_nav_menu(
				array(
					'menu'        => $menu_id,
					'container'   => 'ul',
					'menu_class'  => 'din-menu ' . $container_class,
					'fallback_cb' => array( $this, 'render_fallback_menu' ),
					'depth'       => 3,
				)
			);
		} else {
			$this->render_fallback_menu( $container_class );
		}
	}

	public function render_fallback_menu( $container_class = 'din-desktop-menu' ) {
		?>
		<ul class="din-menu <?php echo esc_attr( $container_class ); ?>">
			<li class="menu-item current-menu-item"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'itse-navbar' ); ?></a></li>
			<?php if ( class_exists( 'WooCommerce' ) ) : ?>
				<li class="menu-item"><a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Store', 'itse-navbar' ); ?></a></li>
			<?php endif; ?>
			<li class="menu-item"><a href="#about"><?php esc_html_e( 'About Us', 'itse-navbar' ); ?></a></li>
			<li class="menu-item"><a href="#services"><?php esc_html_e( 'Services', 'itse-navbar' ); ?></a></li>
			<li class="menu-item"><a href="#contact"><?php esc_html_e( 'Contact', 'itse-navbar' ); ?></a></li>
		</ul>
		<?php
	}

	private function render_cart_icon() {
		$count    = 0;
		$cart_url = get_option( 'din_cart_url', '' );

		if ( class_exists( 'WooCommerce' ) && WC()->cart ) {
			$count = WC()->cart->get_cart_contents_count();
			if ( empty( $cart_url ) ) {
				$cart_url = wc_get_cart_url();
			}
		}

		if ( empty( $cart_url ) ) {
			$cart_url = '#cart';
		}

		$fk_trigger_class = 'fk-cart-toggle fk-drawer-toggle fk-side-cart-toggle';
		?>
		<a href="<?php echo esc_url( $cart_url ); ?>" class="din-action-btn din-cart-btn <?php echo esc_attr( $fk_trigger_class ); ?>" title="<?php esc_attr_e( 'Cart', 'itse-navbar' ); ?>">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
				<circle cx="9" cy="21" r="1"></circle>
				<circle cx="20" cy="21" r="1"></circle>
				<path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
			</svg>
			<span class="din-cart-badge <?php echo $count > 0 ? 'has-items' : ''; ?>"><?php echo esc_html( $count ); ?></span>
		</a>
		<?php
	}

	public function cart_count_fragment( $fragments ) {
		$count    = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
		$badge_html = '<span class="din-cart-badge ' . ( $count > 0 ? 'has-items' : '' ) . '">' . esc_html( $count ) . '</span>';
		$fragments['span.din-cart-badge'] = $badge_html;
		return $fragments;
	}
}
