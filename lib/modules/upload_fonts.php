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
	class sv_webfontloader_upload_fonts extends sv_webfontloader{
		const section_title							= 'Webfontloader Upload';
		
		private $filter								= array(
			'svg'									=> 'image/svg+xml',
			'woff'									=> 'application/octet-stream',
			'woff2'									=> 'application/octet-stream',
			'eot'									=> 'application/vnd.ms-fontobject',
			'ttf'									=> 'application/x-font-ttf',
			'otf'									=> 'application/font-sfnt'
		);
		
		public function __construct(){
			add_action('admin_init', array($this, 'admin_init'));
			add_action('init', array($this, 'init'));
		}
		public function admin_init(){
			add_filter('upload_mimes', array($this, 'upload_mimes'));
			$this->load_settings();
		}
		public function init(){
			if(!is_admin()){
				$this->load_settings();
			}
		}
		public function get_settings(){
			return $this->s;
		}
		public function load_settings(){
			// Uploaded Fonts
			$this->s					= static::$settings->create($this)
				->set_section_name(__('Font Upload',$this->get_module_name()))
				->set_section_description('')
				->set_ID('uploaded_fonts')
				->set_title(__('Uploaded Fonts', $this->get_module_name()))
				->load_type('multi_upload')
				->set_callback(array($this,'fonts_list'))
				->set_filter(array_keys($this->filter));
		}
		public function upload_mimes($mime_types = array()){
			// @todo: make sure setting upload mimes is affecting current form only
			return array_merge($mime_types,$this->filter);
		}
		public function fonts_list($setting): string{
			$form				= $setting->form();
			
			ob_start();
			require($this->get_path('lib/tpl/backend_upload.php'));
			$form .= ob_get_contents();
			ob_end_clean();
			
			return $form;
		}
	}