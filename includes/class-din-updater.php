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
		add_filter( 'site_transient_update_plugins', array( $this, 'check_update' ) );
		add_filter( 'plugins_api', array( $this, 'plugin_popup' ), 20, 3 );
		add_filter( 'upgrader_post_install', array( $this, 'after_install' ), 10, 3 );
	}

	private function get_repository_info() {
		if ( null !== $this->github_response ) {
			return;
		}

		$request_uri = sprintf( 'https://api.github.org/repos/%s/%s/releases/latest', $this->username, $this->repository );

		$args = array(
			'timeout' => 10,
			'headers' => array(
				'Accept'     => 'application/vnd.github.v3+json',
				'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url(),
			),
		);

		$response = wp_remote_get( $request_uri, $args );

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return;
		}

		$this->github_response = json_decode( wp_remote_retrieve_body( $response ), true );
	}

	public function check_update( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$this->get_repository_info();

		if ( empty( $this->github_response['tag_name'] ) ) {
			return $transient;
		}

		$latest_version  = ltrim( $this->github_response['tag_name'], 'v' );
		$current_version = ITSE_NAVBAR_VERSION;

		if ( version_compare( $current_version, $latest_version, '<' ) ) {
			$download_link = $this->github_response['zipball_url'];
			if ( ! empty( $this->github_response['assets'][0]['browser_download_url'] ) ) {
				$download_link = $this->github_response['assets'][0]['browser_download_url'];
			}

			$obj              = new stdClass();
			$obj->slug        = 'itse-navbar';
			$obj->plugin      = $this->plugin;
			$obj->new_version = $latest_version;
			$obj->url         = 'https://github.com/' . $this->username . '/' . $this->repository;
			$obj->package     = $download_link;

			$transient->response[ $this->plugin ] = $obj;
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

		if ( isset( $hook_extra['plugin'] ) && $hook_extra['plugin'] === $this->plugin ) {
			$install_directory = plugin_dir_path( $this->file );
			$wp_filesystem->move( $result['destination'], $install_directory );
			$result['destination'] = $install_directory;
		}

		return $result;
	}
}
