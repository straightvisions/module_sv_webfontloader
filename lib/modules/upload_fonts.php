<?php
namespace sv_100;

/**
 * @author			Matthias Reuter
 * @package			sv_100
 * @copyright		2017 Matthias Reuter
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
		add_filter( 'upload_mimes', array( $this, 'upload_mimes' ) );
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

	public function upload_mimes( $mime_types = array() ) {
		// @todo: make sure setting upload mimes is affecting current form only
		return array_merge( $mime_types, $this->filter );
	}

	public function fonts_list( $setting ): string {
		$form				= $setting->form();

		ob_start();
		require( $this->get_file_path( 'lib/tpl/backend_upload.php' ) );
		$form .= ob_get_contents();
		ob_end_clean();

		return $form;
	}
}