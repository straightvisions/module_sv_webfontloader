<?php
	namespace sv100;
	
	/**
	 * @version		 4.000
	 * @author			straightvisions GmbH
	 * @package			sv100
	 * @copyright		2019 straightvisions GmbH
	 * @link			https://straightvisions.com
	 * @since			1.000
	 * @license			See license.txt or https://straightvisions.com
	 */
	
	
	class sv_webfontloader_filetype_manager extends sv_webfontloader {
		protected $filetypes	= array(
			'svg'				=> 'image/svg+xml',
			'woff'				=> 'application/octet-stream',
			'woff2'				=> 'application/octet-stream',
			'eot'				=> 'application/vnd.ms-fontobject',
			'ttf'				=> 'application/x-font-ttf',
			'otf'				=> 'application/font-sfnt'
		);
		
		public function init() {
			// Action Hooks
			add_filter( 'mime_types', array( $this, 'mime_types' ) );
			add_filter( 'wp_check_filetype_and_ext', array( $this, 'wp_check_filetype_and_ext' ), 10, 3 );
		}
		
		/*
		 * since WP 5.0.1, file ext and mime type must match.
		 * As there are different mime types possible for some extensions,
		 * we need to allow multiple mime types per file extension.
		 */
		public function wp_check_filetype_and_ext( $check, $file, $filename ) {
			if ( empty( $check['ext'] ) && empty( $check['type'] ) ) {
				// Adjust to your needs!
				$secondary_mime = [ 'ttf' => 'application/font-sfnt' ];
				
				// Run another check, but only for our secondary mime and not on core mime types.
				remove_filter( 'wp_check_filetype_and_ext', array( $this, 'wp_check_filetype_and_ext'), 99, 4 );
				
				$check = wp_check_filetype_and_ext( $file, $filename, $secondary_mime );
				
				add_filter( 'wp_check_filetype_and_ext', array( $this, 'wp_check_filetype_and_ext' ), 99, 3 );
			}
			
			return $check;
		}
		
		public function mime_types( $mime_types = array() ) {
			return array_merge( $mime_types, $this->filetypes );
		}
	}