<?php
namespace sv_100;

/**
 * @author			straightvisions GmbH
 * @package			sv_100
 * @copyright		2017 straightvisions GmbH
 * @link			https://straightvisions.com
 * @since			1.0
 * @license			See license.txt or https://straightvisions.com
 */

class sv_webfontloader_upload_fonts extends sv_webfontloader {
	private $filter								= array(
		'svg'									=> 'image/svg+xml',
		'woff'									=> 'application/octet-stream',
		'woff2'									=> 'application/octet-stream',
		'eot'									=> 'application/vnd.ms-fontobject',
		'ttf'									=> 'application/x-font-ttf',
		'otf'									=> 'application/font-sfnt'
	);

	public function __construct() {

	}

	public function init() {
		// Section Info
		$this->set_section_title( 'Webfontloader Upload' );
		$this->set_section_desc( __( 'Please reload page after new fonts have been uploaded.', $this->get_module_name() ) );
		$this->set_section_type( 'settings' );

		// Loads Settings
		$this->load_settings();

		// Action Hooks
		add_filter( 'mime_types', array( $this, 'mime_types' ));
		add_filter('wp_check_filetype_and_ext', array( $this, 'wp_check_filetype_and_ext'), 10, 4);
	}
	/*
	 * since WP 5.0.1, file ext and mime type must match.
	 * As there are different mime types possible for some extensions,
	 * we need to allow multiple mime types per file extension.
	 */
	public function wp_check_filetype_and_ext($check, $file, $filename, $mimes){
		if ( empty( $check['ext'] ) && empty( $check['type'] ) ) {
			// Adjust to your needs!
			$secondary_mime = [ 'ttf' => 'application/font-sfnt' ];

			// Run another check, but only for our secondary mime and not on core mime types.
			remove_filter( 'wp_check_filetype_and_ext', array( $this, 'wp_check_filetype_and_ext'), 99, 4 );
			$check = wp_check_filetype_and_ext( $file, $filename, $secondary_mime );
			add_filter( 'wp_check_filetype_and_ext',array( $this, 'wp_check_filetype_and_ext'), 99, 4 );
		}
		return $check;
	}
	public function load_settings() {
		// Uploaded Fonts
		$this->s['uploaded_fonts']					= static::$settings->create( $this )
			->set_ID( 'uploaded_fonts' )
			->set_title( __( 'Uploaded Fonts', $this->get_module_name() ) )
			->load_type( 'multi_upload' )
			->set_callback( array( $this, 'fonts_list' ) )
			->set_filter( array_keys( $this->filter ) );
	}

	public function mime_types( $mime_types = array() ) {
		// @todo: make sure setting upload mimes is affecting current form only
		return array_merge( $mime_types, $this->filter );
	}

	public function fonts_list( $setting ): string {
		$form				= $setting->form();

		ob_start();
		require_once( $this->get_parent()->get_path( 'lib/tpl/backend_upload.php' ) );
		$form .= ob_get_contents();
		ob_end_clean();

		return $form;
	}
}