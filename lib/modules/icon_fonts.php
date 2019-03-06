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

class sv_webfontloader_icon_fonts extends sv_webfontloader {
	public function __construct() {

	}

	public function init() {
		$this->set_section_title( 'Webfontloader Icons' );
		$this->set_section_desc( 'Activate Icon Fonts' );
		$this->set_section_type( 'settings' );

		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ), 99 );
		$this->load_settings();

		if(!is_admin()){
			if($this->s['dashicons']->run_type()->get_data() == '1') {
				// Loads Styles
				static::$scripts->create( $this )
					->set_ID( $this->get_prefix() )
					->set_path('lib/icon_fonts/dashicons/dashicons.css');
			}
		}
	}
	public function wp_enqueue_scripts(){
		wp_dequeue_style('dashicons');
	}
	public function load_settings(){
		// Icon Fonts
		$this->s['dashicons']					= static::$settings->create($this)
		                                                            ->set_ID('dashicons')
		                                                            ->set_title(__('Dashicons', $this->get_module_name()))
		                                                            ->set_description(__('Load Dashicons from WordPress in Frontend.', $this->get_module_name()))
		                                                            ->load_type('checkbox');
	}
}