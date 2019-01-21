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
	class sv_webfontloader_icon_fonts extends sv_webfontloader{
		public function __construct(){
			$this->set_section_title('Webfontloader Icons');
			$this->set_section_desc('Activate Icon Fonts');
			$this->set_section_type('settings');

			add_action('admin_init', array($this, 'admin_init'));
			add_action('init', array($this, 'init'));
		}
		public function admin_init(){
			$this->load_settings();
		}
		public function init(){
			if(!is_admin()){
				$this->load_settings();

				if($this->s['dashicons']->run_type()->get_data() == '1') {
					$this->add_style(false, 'icon_fonts/dashicons/dashicons.css', false);
				}
			}
		}
		public function load_settings(){
			// Icon Fonts
			wp_dequeue_style('dashicons');

			$this->s['dashicons']					= static::$settings->create($this)
				->set_ID('dashicons')
				->set_title(__('Dashicons', $this->get_module_name()))
				->set_description(__('Load Dashicons from WordPress in Frontend.', $this->get_module_name()))
				->load_type('checkbox');
		}
	}