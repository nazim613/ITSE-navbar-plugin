<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ITSE_Navbar_Updater {

	private $file;
	private $plugin;
	private $username;
	private $repository;
	private $github_response;

	public function __construct( $file ) {
		$this->file       = $file;
		$this->plugin     = plugin_basename( $file );
		$this->username   = 'nazim613';
		$this->repository = 'ITSE-navbar-plugin';
	}

	public function init() {
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ), 20 );
		add_filter( 'site_transient_update_plugins', array( $this, 'check_update' ), 20 );
		add_filter( 'plugins_api', array( $this, 'plugin_popup' ), 20, 3 );
		add_filter( 'upgrader_post_install', array( $this, 'after_install' ), 10, 3 );

		add_action( 'admin_init', array( $this, 'auto_refresh_on_admin_pages' ) );
		add_action( 'admin_init', array( $this, 'force_check_trigger' ) );
	}

	public function auto_refresh_on_admin_pages() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		global $pagenow;
		if ( in_array( $pagenow, array( 'plugins.php', 'update-core.php', 'admin.php' ), true ) ) {
			$cache = get_transient( 'itse_github_release_cache' );
			if ( false === $cache ) {
				// Cache expired or missing -> force check GitHub live
				$this->get_repository_info( true );
				delete_site_transient( 'update_plugins' );
			}
		}
	}

	public function force_check_trigger() {
		if ( isset( $_GET['itse_force_check'] ) && current_user_can( 'manage_options' ) ) {
			delete_transient( 'itse_github_release_cache' );
			delete_site_transient( 'update_plugins' );
			wp_clean_plugins_cache( true );
			wp_redirect( remove_query_arg( 'itse_force_check' ) );
			exit;
		}
	}

	private function get_repository_info( $force = false ) {
		if ( ! $force && null !== $this->github_response ) {
			return;
		}

		$cache = get_transient( 'itse_github_release_cache' );
		if ( ! $force && false !== $cache && is_array( $cache ) ) {
			$this->github_response = $cache;
			return;
		}

		$request_uri = sprintf( 'https://api.github.org/repos/%s/%s/releases/latest', $this->username, $this->repository );

		$args = array(
			'timeout' => 12,
			'headers' => array(
				'Accept'     => 'application/vnd.github.v3+json',
				'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url(),
			),
		);

		$response = wp_remote_get( $request_uri, $args );

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( is_array( $body ) && ! empty( $body['tag_name'] ) ) {
			$this->github_response = $body;
			set_transient( 'itse_github_release_cache', $body, 600 ); // Cache for 10 minutes
		}
	}

	public function check_update( $transient ) {
		if ( ! is_object( $transient ) ) {
			$transient = new stdClass();
		}

		$this->get_repository_info();

		if ( empty( $this->github_response['tag_name'] ) ) {
			return $transient;
		}

		$latest_version = ltrim( $this->github_response['tag_name'], 'v' );
		
		// Installed version check
		$current_version = ITSE_NAVBAR_VERSION;
		if ( isset( $transient->checked[ $this->plugin ] ) ) {
			$current_version = $transient->checked[ $this->plugin ];
		}

		if ( version_compare( $current_version, $latest_version, '<' ) ) {
			$download_link = $this->github_response['zipball_url'];
			if ( ! empty( $this->github_response['assets'][0]['browser_download_url'] ) ) {
				$download_link = $this->github_response['assets'][0]['browser_download_url'];
			}

			$obj               = new stdClass();
			$obj->id           = 'itse-navbar';
			$obj->slug         = 'itse-navbar';
			$obj->plugin       = $this->plugin;
			$obj->new_version  = $latest_version;
			$obj->url          = 'https://github.com/' . $this->username . '/' . $this->repository;
			$obj->package      = $download_link;
			$obj->requires     = '5.0';
			$obj->tested       = '6.7';
			$obj->requires_php = '7.4';

			if ( ! isset( $transient->response ) || ! is_array( $transient->response ) ) {
				$transient->response = array();
			}

			$transient->response[ $this->plugin ]                          = $obj;
			$transient->response['itse-navbar/itse-navbar.php']            = $obj;
			$transient->response['itse-navbar/dynamic-island-navbar.php'] = $obj;

			// CRITICAL FOR WORDPRESS CORE: Unset from no_update array so WP displays the update notice
			if ( isset( $transient->no_update[ $this->plugin ] ) ) {
				unset( $transient->no_update[ $this->plugin ] );
			}
			if ( isset( $transient->no_update['itse-navbar/itse-navbar.php'] ) ) {
				unset( $transient->no_update['itse-navbar/itse-navbar.php'] );
			}
			if ( isset( $transient->no_update['itse-navbar/dynamic-island-navbar.php'] ) ) {
				unset( $transient->no_update['itse-navbar/dynamic-island-navbar.php'] );
			}
		}

		return $transient;
	}

	public function plugin_popup( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || empty( $args->slug ) || 'itse-navbar' !== $args->slug ) {
			return $result;
		}

		$this->get_repository_info();

		if ( empty( $this->github_response ) ) {
			return $result;
		}

		$latest_version = ltrim( $this->github_response['tag_name'], 'v' );
		$download_link  = $this->github_response['zipball_url'];
		if ( ! empty( $this->github_response['assets'][0]['browser_download_url'] ) ) {
			$download_link = $this->github_response['assets'][0]['browser_download_url'];
		}

		$res               = new stdClass();
		$res->name         = 'ITSE Features & Navbar';
		$res->slug         = 'itse-navbar';
		$res->version      = $latest_version;
		$res->author       = '<a href="https://github.com/' . $this->username . '">ITSE</a>';
		$res->homepage     = 'https://github.com/' . $this->username . '/' . $this->repository;
		$res->requires     = '5.0';
		$res->tested       = '6.7';
		$res->downloaded   = 100;
		$res->last_updated = isset( $this->github_response['published_at'] ) ? $this->github_response['published_at'] : '';
		$res->sections     = array(
			'description' => 'Automatic GitHub updates for ITSE Features & Navbar.',
			'changelog'   => isset( $this->github_response['body'] ) ? nl2br( esc_html( $this->github_response['body'] ) ) : 'Check GitHub Release notes.',
		);
		$res->download_link = $download_link;

		return $res;
	}

	public function after_install( $response, $hook_extra, $result ) {
		global $wp_filesystem;

		if ( isset( $hook_extra['plugin'] ) && ( $hook_extra['plugin'] === $this->plugin || false !== strpos( $hook_extra['plugin'], 'itse-navbar' ) ) ) {
			$proper_folder_name = 'itse-navbar';
			$target_destination = WP_PLUGIN_DIR . '/' . $proper_folder_name;

			if ( isset( $result['destination'] ) && $result['destination'] !== $target_destination ) {
				$wp_filesystem->move( $result['destination'], $target_destination );
				$result['destination'] = $target_destination;
			}
		}

		return $result;
	}
}
