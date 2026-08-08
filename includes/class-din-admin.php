<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DIN_Admin {

	public function init() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_init', array( $this, 'handle_save_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'admin_notices', array( $this, 'show_github_update_notice' ) );
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

	public function show_github_update_notice() {
		if ( ! current_user_can( 'update_plugins' ) ) {
			return;
		}

		$transient       = get_site_transient( 'update_plugins' );
		$plugin_basename = plugin_basename( ITSE_NAVBAR_DIR . 'itse-navbar.php' );

		$update_info = null;
		if ( isset( $transient->response[ $plugin_basename ] ) ) {
			$update_info = $transient->response[ $plugin_basename ];
		} elseif ( isset( $transient->response['itse-navbar/itse-navbar.php'] ) ) {
			$update_info = $transient->response['itse-navbar/itse-navbar.php'];
		}

		if ( $update_info && ! empty( $update_info->new_version ) ) {
			$update_url = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' . urlencode( $plugin_basename ) ), 'upgrade-plugin_' . $plugin_basename );
			echo '<div class="notice notice-warning is-dismissible" style="padding: 12px 18px; border-left: 4px solid #2271b1; background: #fff; margin-top: 15px;">';
			echo '<p style="font-size: 15px; margin: 0; display: flex; align-items: center; gap: 10px;">';
			echo '🚀 <strong>A new version (v' . esc_html( $update_info->new_version ) . ') is available for ITSE Features & Navbar!</strong> ';
			echo '<a href="' . esc_url( $update_url ) . '" class="button button-primary">Update Now</a>';
			echo '<a href="' . esc_url( $update_info->url ) . '" target="_blank" class="button button-secondary">View Release Notes</a>';
			echo '</p>';
			echo '</div>';
		}
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
			'din_enable',
			'din_hide_theme_header',
			'din_desktop_menu',
			'din_mobile_menu',
			'din_nav_menu',
			'din_logo_url',
			'din_logo_width',
			'din_offer_enable',
			'din_offer_text',
			'din_offer_bg',
			'din_offer_text_color',
			'din_offer_link',
			'din_show_globe',
			'din_show_cart',
			'din_cart_shortcode',
			'din_cart_url',
			'din_show_account',
			'din_account_url',
			'din_island_bg',
			'din_island_radius',
			'din_island_border',
			'din_sticky',
		);

		foreach ( $options as $option_name ) {
			register_setting( 'din_settings_group', $option_name );
		}
	}

	public function handle_save_settings() {
		if ( isset( $_POST['din_save_settings'] ) && check_admin_referer( 'din_settings_action', 'din_settings_nonce' ) ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			update_option( 'din_enable', isset( $_POST['din_enable'] ) ? 1 : 0 );
			update_option( 'din_hide_theme_header', isset( $_POST['din_hide_theme_header'] ) ? 1 : 0 );
			update_option( 'din_desktop_menu', isset( $_POST['din_desktop_menu'] ) ? intval( $_POST['din_desktop_menu'] ) : 0 );
			update_option( 'din_mobile_menu', isset( $_POST['din_mobile_menu'] ) ? intval( $_POST['din_mobile_menu'] ) : 0 );
			update_option( 'din_logo_url', isset( $_POST['din_logo_url'] ) ? esc_url_raw( $_POST['din_logo_url'] ) : '' );
			update_option( 'din_logo_width', isset( $_POST['din_logo_width'] ) ? intval( $_POST['din_logo_width'] ) : 140 );
			update_option( 'din_offer_enable', isset( $_POST['din_offer_enable'] ) ? 1 : 0 );
			update_option( 'din_offer_text', isset( $_POST['din_offer_text'] ) ? wp_kses_post( $_POST['din_offer_text'] ) : '' );
			update_option( 'din_offer_bg', isset( $_POST['din_offer_bg'] ) ? sanitize_text_field( $_POST['din_offer_bg'] ) : '#2d3e18' );
			update_option( 'din_offer_text_color', isset( $_POST['din_offer_text_color'] ) ? sanitize_text_field( $_POST['din_offer_text_color'] ) : '#ffffff' );
			update_option( 'din_offer_link', isset( $_POST['din_offer_link'] ) ? esc_url_raw( $_POST['din_offer_link'] ) : '' );
			update_option( 'din_show_globe', isset( $_POST['din_show_globe'] ) ? 1 : 0 );
			update_option( 'din_show_cart', isset( $_POST['din_show_cart'] ) ? 1 : 0 );
			update_option( 'din_cart_shortcode', isset( $_POST['din_cart_shortcode'] ) ? sanitize_text_field( $_POST['din_cart_shortcode'] ) : '[fk_cart_menu]' );
			update_option( 'din_cart_url', isset( $_POST['din_cart_url'] ) ? esc_url_raw( $_POST['din_cart_url'] ) : '' );
			update_option( 'din_show_account', isset( $_POST['din_show_account'] ) ? 1 : 0 );
			update_option( 'din_account_url', isset( $_POST['din_account_url'] ) ? esc_url_raw( $_POST['din_account_url'] ) : '' );
			update_option( 'din_island_bg', isset( $_POST['din_island_bg'] ) ? sanitize_text_field( $_POST['din_island_bg'] ) : '#ffffff' );
			update_option( 'din_island_radius', isset( $_POST['din_island_radius'] ) ? sanitize_text_field( $_POST['din_island_radius'] ) : '50px' );
			update_option( 'din_island_border', isset( $_POST['din_island_border'] ) ? sanitize_text_field( $_POST['din_island_border'] ) : '1px solid rgba(0, 0, 0, 0.06)' );
			update_option( 'din_sticky', isset( $_POST['din_sticky'] ) ? 1 : 0 );

			add_settings_error( 'din_messages', 'din_message', __( 'ITSE Features Settings Saved Successfully!', 'itse-navbar' ), 'updated' );
		}
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$menus           = wp_get_nav_menus();
		$wc_account_page = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
		$wc_cart_page    = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '#cart';
		?>
		<div class="wrap din-admin-wrap">
			<h1><span class="dashicons dashicons-menu-alt3" style="font-size: 30px; width: 30px; height: 30px; vertical-align: middle; margin-right: 8px;"></span> ITSE Features & Navbar Settings</h1>
			<p class="description">Customize your website header logo, select separate Desktop & Mobile menus, offer banner, and WooCommerce/FunnelKit action icons.</p>

			<?php settings_errors( 'din_messages' ); ?>

			<form method="post" action="" class="din-form">
				<?php wp_nonce_field( 'din_settings_action', 'din_settings_nonce' ); ?>

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
							<th scope="row">Offer Background Color</th>
							<td>
								<input type="text" name="din_offer_bg" value="<?php echo esc_attr( get_option( 'din_offer_bg', '#2d3e18' ) ); ?>" class="din-color-picker" />
							</td>
						</tr>
						<tr>
							<th scope="row">Offer Text Color</th>
							<td>
								<input type="text" name="din_offer_text_color" value="<?php echo esc_attr( get_option( 'din_offer_text_color', '#ffffff' ) ); ?>" class="din-color-picker" />
							</td>
						</tr>
						<tr>
							<th scope="row">Offer Link URL</th>
							<td>
								<input type="url" name="din_offer_link" value="<?php echo esc_attr( get_option( 'din_offer_link', '' ) ); ?>" class="large-text" placeholder="https://example.com/shop" />
							</td>
						</tr>
					</table>
				</div>

				<div class="din-card">
					<h2><span class="dashicons dashicons-cart"></span> Action Icons (Right Header)</h2>
					<table class="form-table">
						<tr>
							<th scope="row">Show Language / Globe Icon</th>
							<td>
								<label class="din-switch">
									<input type="checkbox" name="din_show_globe" value="1" <?php checked( 1, get_option( 'din_show_globe', 1 ) ); ?> />
									<span class="din-slider"></span>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row">Show Cart Icon</th>
							<td>
								<label class="din-switch">
									<input type="checkbox" name="din_show_cart" value="1" <?php checked( 1, get_option( 'din_show_cart', 1 ) ); ?> />
									<span class="din-slider"></span>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row">FunnelKit / Cart Shortcode</th>
							<td>
								<input type="text" name="din_cart_shortcode" value="<?php echo esc_attr( get_option( 'din_cart_shortcode', '[fk_cart_menu]' ) ); ?>" class="large-text" placeholder="[fk_cart_menu]" />
								<p class="description">Default: <code>[fk_cart_menu]</code> (FunnelKit Cart Shortcode). Automatically renders FunnelKit native cart icon & slide drawer trigger.</p>
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
							<th scope="row">Navbar Background (Color / Gradient / Code)</th>
							<td>
								<input type="text" name="din_island_bg" value="<?php echo esc_attr( get_option( 'din_island_bg', '#ffffff' ) ); ?>" class="large-text" placeholder="#ffffff or linear-gradient(135deg, #1e293b, #0f172a)" />
								<p class="description">Paste any CSS background code! Examples: <code>#ffffff</code>, <code>rgba(255,255,255,0.9)</code>, or <code>linear-gradient(135deg, #7c9c38 0%, #2d3e18 100%)</code>.</p>
							</td>
						</tr>
						<tr>
							<th scope="row">Navbar Border Radius</th>
							<td>
								<input type="text" name="din_island_radius" value="<?php echo esc_attr( get_option( 'din_island_radius', '50px' ) ); ?>" class="regular-text" placeholder="50px" />
								<p class="description">Enter custom radius code. Examples: <code>50px</code>, <code>20px</code>, <code>50%</code>, or <code>24px 24px 0 0</code>.</p>
							</td>
						</tr>
						<tr>
							<th scope="row">Navbar Border Code</th>
							<td>
								<input type="text" name="din_island_border" value="<?php echo esc_attr( get_option( 'din_island_border', '1px solid rgba(0, 0, 0, 0.06)' ) ); ?>" class="large-text" placeholder="1px solid rgba(0, 0, 0, 0.06)" />
								<p class="description">Enter CSS border style code. Examples: <code>1px solid rgba(0,0,0,0.06)</code>, <code>2px solid #7c9c38</code>, or <code>none</code>.</p>
							</td>
						</tr>
					</table>
				</div>

				<p class="submit">
					<input type="submit" name="din_save_settings" class="button button-primary button-hero" value="Save ITSE Features Settings" />
				</p>
			</form>
		</div>
		<?php
	}
}
