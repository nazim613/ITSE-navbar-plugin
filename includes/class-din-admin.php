<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DIN_Admin {

	public function init() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	public function add_settings_page() {
		// Top-level menu in Admin Dashboard titled "ITSE Features"
		add_menu_page(
			__( 'ITSE Features', 'itse-navbar' ),
			__( 'ITSE Features', 'itse-navbar' ),
			'manage_options',
			'itse-features',
			array( $this, 'render_settings_page' ),
			'dashicons-menu-alt3',
			30
		);

		// Also keep a submenu link under Settings for convenience
		add_options_page(
			__( 'ITSE Features', 'itse-navbar' ),
			__( 'ITSE Features', 'itse-navbar' ),
			'manage_options',
			'itse-features',
			array( $this, 'render_settings_page' )
		);
	}

	public function enqueue_admin_assets( $hook ) {
		if ( 'toplevel_page_itse-features' !== $hook && 'settings_page_itse-features' !== $hook ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		wp_enqueue_style(
			'din-admin-style',
			ITSE_NAVBAR_URL . 'assets/css/din-admin.css',
			array(),
			ITSE_NAVBAR_VERSION
		);

		wp_enqueue_script(
			'din-admin-script',
			ITSE_NAVBAR_URL . 'assets/js/din-admin.js',
			array( 'jquery' ),
			ITSE_NAVBAR_VERSION,
			true
		);
	}

	public function register_settings() {
		$options = array(
			'din_enable'            => 1,
			'din_hide_theme_header' => 1,
			'din_desktop_menu'      => 0,
			'din_mobile_menu'       => 0,
			'din_nav_menu'          => 0, // Fallback legacy
			'din_logo_url'          => '',
			'din_logo_width'        => 140,
			'din_offer_enable'      => 1,
			'din_offer_text'        => '✨ BUY 3 TO SAVE 20% ✨',
			'din_offer_bg'          => '#2d3e18',
			'din_offer_text_color'  => '#ffffff',
			'din_offer_link'        => '',
			'din_show_globe'        => 1,
			'din_show_cart'         => 1,
			'din_cart_url'          => '',
			'din_show_account'      => 1,
			'din_account_url'       => '',
			'din_island_bg'         => '#ffffff',
			'din_island_radius'     => 50,
			'din_sticky'            => 1,
		);

		foreach ( $options as $option_name => $default_value ) {
			register_setting( 'din_settings_group', $option_name );
		}
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$menus = wp_get_nav_menus();
		$wc_account_page = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
		$wc_cart_page    = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '#cart';
		?>
		<div class="wrap din-admin-wrap">
			<h1><span class="dashicons dashicons-menu-alt3" style="font-size: 30px; width: 30px; height: 30px; vertical-align: middle; margin-right: 8px;"></span> ITSE Features & Navbar Settings</h1>
			<p class="description">Customize your website header logo, select separate Desktop & Mobile menus, offer banner, and WooCommerce/FunnelKit action icons.</p>

			<form method="post" action="options.php" class="din-form">
				<?php settings_fields( 'din_settings_group' ); ?>
				<?php do_settings_sections( 'din_settings_group' ); ?>

				<div class="din-card">
					<h2><span class="dashicons dashicons-admin-generic"></span> General Settings</h2>
					<table class="form-table">
						<tr>
							<th scope="row">Enable ITSE Navbar</th>
							<td>
								<label class="din-switch">
									<input type="checkbox" name="din_enable" value="1" <?php checked( 1, get_option( 'din_enable', 1 ) ); ?> />
									<span class="din-slider"></span>
								</label>
								<p class="description">Turn on/off the Dynamic Island header across your site.</p>
							</td>
						</tr>
						<tr>
							<th scope="row">Hide Default Theme Header</th>
							<td>
								<label class="din-switch">
									<input type="checkbox" name="din_hide_theme_header" value="1" <?php checked( 1, get_option( 'din_hide_theme_header', 1 ) ); ?> />
									<span class="din-slider"></span>
								</label>
								<p class="description">Automatically hides default theme header (`header`, `.site-header`, Astra header) using CSS/JS.</p>
							</td>
						</tr>
						<tr>
							<th scope="row">Sticky Floating Header</th>
							<td>
								<label class="din-switch">
									<input type="checkbox" name="din_sticky" value="1" <?php checked( 1, get_option( 'din_sticky', 1 ) ); ?> />
									<span class="din-slider"></span>
								</label>
								<p class="description">Keep navbar floating at top when scrolling down.</p>
							</td>
						</tr>
					</table>
				</div>

				<div class="din-card">
					<h2><span class="dashicons dashicons-menu"></span> Navigation Menus Selection</h2>
					<table class="form-table">
						<tr>
							<th scope="row">Desktop Navigation Menu</th>
							<td>
								<select name="din_desktop_menu" class="regular-text">
									<option value="0"><?php esc_html_e( '-- Select Desktop Menu --', 'itse-navbar' ); ?></option>
									<?php foreach ( $menus as $menu ) : ?>
										<?php $selected_desktop = get_option( 'din_desktop_menu', get_option( 'din_nav_menu', 0 ) ); ?>
										<option value="<?php echo esc_attr( $menu->term_id ); ?>" <?php selected( $selected_desktop, $menu->term_id ); ?>>
											<?php echo esc_html( $menu->name ); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<p class="description">Select the WordPress menu to display on <strong>Desktop screens</strong>.</p>
							</td>
						</tr>
						<tr>
							<th scope="row">Mobile Navigation Menu</th>
							<td>
								<select name="din_mobile_menu" class="regular-text">
									<option value="0"><?php esc_html_e( '-- Select Mobile Menu (or Same as Desktop) --', 'itse-navbar' ); ?></option>
									<?php foreach ( $menus as $menu ) : ?>
										<option value="<?php echo esc_attr( $menu->term_id ); ?>" <?php selected( get_option( 'din_mobile_menu', 0 ), $menu->term_id ); ?>>
											<?php echo esc_html( $menu->name ); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<p class="description">Select the WordPress menu to display inside the <strong>Mobile Off-Canvas Left Drawer</strong>.</p>
							</td>
						</tr>
					</table>
				</div>

				<div class="din-card">
					<h2><span class="dashicons dashicons-format-image"></span> Header Logo (Image)</h2>
					<table class="form-table">
						<tr>
							<th scope="row">Header Logo Image</th>
							<td>
								<div class="din-logo-preview-wrap">
									<?php $logo_url = get_option( 'din_logo_url', '' ); ?>
									<img id="din-logo-preview" src="<?php echo esc_url( $logo_url ); ?>" style="max-height: 50px; <?php echo empty( $logo_url ) ? 'display:none;' : ''; ?>" />
									<input type="hidden" id="din_logo_url" name="din_logo_url" value="<?php echo esc_attr( $logo_url ); ?>" />
									<button type="button" class="button button-secondary" id="din-upload-logo-btn">Upload / Select Logo Image</button>
									<button type="button" class="button button-link-delete" id="din-remove-logo-btn" style="<?php echo empty( $logo_url ) ? 'display:none;' : ''; ?>">Remove Logo</button>
								</div>
								<p class="description">Upload logo in image format (PNG, SVG, JPG, WebP). Displayed on both Desktop and Mobile navbar left side.</p>
							</td>
						</tr>
						<tr>
							<th scope="row">Logo Width (px)</th>
							<td>
								<input type="number" name="din_logo_width" value="<?php echo esc_attr( get_option( 'din_logo_width', 140 ) ); ?>" min="40" max="400" class="small-text" /> px
							</td>
						</tr>
					</table>
				</div>

				<div class="din-card">
					<h2><span class="dashicons dashicons-megaphone"></span> Announcement / Offer Bar</h2>
					<table class="form-table">
						<tr>
							<th scope="row">Enable Offer Bar</th>
							<td>
								<label class="din-switch">
									<input type="checkbox" name="din_offer_enable" value="1" <?php checked( 1, get_option( 'din_offer_enable', 1 ) ); ?> />
									<span class="din-slider"></span>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row">Offer Text</th>
							<td>
								<input type="text" name="din_offer_text" value="<?php echo esc_attr( get_option( 'din_offer_text', '✨ BUY 3 TO SAVE 20% ✨' ) ); ?>" class="large-text" />
							</td>
						</tr>
						<tr>
							<th scope="row">Offer Link (Optional)</th>
							<td>
								<input type="url" name="din_offer_link" value="<?php echo esc_attr( get_option( 'din_offer_link', '' ) ); ?>" class="large-text" placeholder="https://..." />
							</td>
						</tr>
						<tr>
							<th scope="row">Background Color</th>
							<td>
								<input type="text" name="din_offer_bg" value="<?php echo esc_attr( get_option( 'din_offer_bg', '#2d3e18' ) ); ?>" class="din-color-picker" />
							</td>
						</tr>
						<tr>
							<th scope="row">Text Color</th>
							<td>
								<input type="text" name="din_offer_text_color" value="<?php echo esc_attr( get_option( 'din_offer_text_color', '#ffffff' ) ); ?>" class="din-color-picker" />
							</td>
						</tr>
					</table>
				</div>

				<div class="din-card">
					<h2><span class="dashicons dashicons-cart"></span> Action Icons (Cart, Account, Globe)</h2>
					<table class="form-table">
						<tr>
							<th scope="row">Show Globe / Language Icon</th>
							<td>
								<label class="din-switch">
									<input type="checkbox" name="din_show_globe" value="1" <?php checked( 1, get_option( 'din_show_globe', 1 ) ); ?> />
									<span class="din-slider"></span>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row">Show WooCommerce / FunnelKit Cart Icon</th>
							<td>
								<label class="din-switch">
									<input type="checkbox" name="din_show_cart" value="1" <?php checked( 1, get_option( 'din_show_cart', 1 ) ); ?> />
									<span class="din-slider"></span>
								</label>
								<p class="description">Displays FunnelKit / WooCommerce cart icon with count badge. In mobile view, cart icon is placed to the left of the menu icon on the right.</p>
							</td>
						</tr>
						<tr>
							<th scope="row">Custom Cart URL / Trigger</th>
							<td>
								<input type="text" name="din_cart_url" value="<?php echo esc_attr( get_option( 'din_cart_url', '' ) ); ?>" class="large-text" placeholder="<?php echo esc_attr( $wc_cart_page ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row">Show Account / Profile Icon</th>
							<td>
								<label class="din-switch">
									<input type="checkbox" name="din_show_account" value="1" <?php checked( 1, get_option( 'din_show_account', 1 ) ); ?> />
									<span class="din-slider"></span>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row">Custom Profile URL</th>
							<td>
								<input type="url" name="din_account_url" value="<?php echo esc_attr( get_option( 'din_account_url', '' ) ); ?>" class="large-text" placeholder="<?php echo esc_attr( $wc_account_page ); ?>" />
							</td>
						</tr>
					</table>
				</div>

				<div class="din-card">
					<h2><span class="dashicons dashicons-update"></span> GitHub Automatic Updates</h2>
					<table class="form-table">
						<tr>
							<th scope="row">GitHub Repository</th>
							<td>
								<a href="https://github.com/nazim613/ITSE-navbar-plugin" target="_blank" rel="noopener">
									<strong>nazim613/ITSE-navbar-plugin</strong>
								</a>
								<p class="description">This plugin automatically checks GitHub for new releases and shows 1-click update notifications inside WordPress Admin!</p>
							</td>
						</tr>
						<tr>
							<th scope="row">Current Version</th>
							<td>
								<span class="badge" style="background: #2271b1; color: #fff; padding: 4px 10px; border-radius: 12px; font-weight: 600;">v<?php echo esc_html( ITSE_NAVBAR_VERSION ); ?></span>
							</td>
						</tr>
						<tr>
							<th scope="row">Check Updates</th>
							<td>
								<a href="<?php echo esc_url( add_query_arg( 'itse_force_check', '1' ) ); ?>" class="button button-secondary">
									<span class="dashicons dashicons-update" style="vertical-align: text-bottom; margin-right: 4px;"></span> Check GitHub Updates Now
								</a>
								<p class="description">Click to clear WordPress cache and instantly fetch latest release from GitHub!</p>
							</td>
						</tr>
					</table>
				</div>

				<div class="din-card">
					<h2><span class="dashicons dashicons-art"></span> Dynamic Island Aesthetic & Styling</h2>
					<table class="form-table">
						<tr>
							<th scope="row">Navbar Background Color</th>
							<td>
								<input type="text" name="din_island_bg" value="<?php echo esc_attr( get_option( 'din_island_bg', '#ffffff' ) ); ?>" class="din-color-picker" />
							</td>
						</tr>
						<tr>
							<th scope="row">Navbar Border Radius (px)</th>
							<td>
								<input type="number" name="din_island_radius" value="<?php echo esc_attr( get_option( 'din_island_radius', 50 ) ); ?>" min="0" max="100" class="small-text" /> px
							</td>
						</tr>
					</table>
				</div>

				<?php submit_button( 'Save ITSE Features Settings' ); ?>
			</form>
		</div>
		<?php
	}
}
