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
	class sv_webfontloader extends init{
		static $scripts_loaded						= false;
		private $vendors							= '';
		private $filter								= array(
			'svg'									=> 'image/svg+xml',
			'woff'									=> 'application/octet-stream',
			'woff2'									=> 'application/octet-stream',
			'eot'									=> 'application/vnd.ms-fontobject',
			'ttf'									=> 'application/x-font-ttf',
			'otf'									=> 'application/font-sfnt'
		);
		private static $s_fields					= array(
			'family_name'							=> 'text',
			'italic'								=> 'checkbox',
			'weight'								=> 'select',
			'active'								=> 'checkbox',
		);
		private static $s_titles					= array();
		private static $s_descriptions				= array();
		private static $s_options					= array();
		private static $s_fonts_upload				= false;
		private static $s_fonts						= array();

		public function __construct($path,$url){
			$this->path								= $path;
			$this->url								= $url;
			$this->name								= get_class($this);
			
			static::$s_titles['family_name']		= __('Family Name', $this->get_module_name());
			static::$s_titles['italic']				= __('italic', $this->get_module_name());
			static::$s_titles['weight']				= __('Font Weight', $this->get_module_name());
			static::$s_titles['active']				= __('active', $this->get_module_name());
			
			static::$s_descriptions['family_name']	= __('Font Family Name, e.g. for CSS', $this->get_module_name());
			static::$s_descriptions['italic']		= __('If this font is italic version, activate this setting.', $this->get_module_name());
			static::$s_descriptions['weight']		= __('Please select font weight.', $this->get_module_name());
			static::$s_descriptions['active']		= __('Only active fonts will be loaded.', $this->get_module_name());
			
			static::$s_options['weight']			= array(
				'100'								=> '100',
				'200'								=> '200',
				'300'								=> '300',
				'400'								=> '400',
				'500'								=> '500',
				'600'								=> '600',
				'700'								=> '700',
				'800'								=> '800',
				'900'								=> '900',
			);
			
			add_action('admin_init', array($this, 'admin_init'));
			add_action('init', array($this, 'init'));
			
			$this->module_enqueue_scripts();
		}
		public function admin_init(){
			// Uploaded Fonts
			static::$s_fonts_upload					= static::$settings->create($this);
			static::$s_fonts_upload->set_section('uploaded_fonts');
			static::$s_fonts_upload->set_section_name(__('Font Upload',$this->get_module_name()));
			static::$s_fonts_upload->set_section_description('');
			static::$s_fonts_upload->set_ID('uploaded_fonts');
			static::$s_fonts_upload->set_title(__('Uploaded Fonts', $this->get_module_name()));
			static::$s_fonts_upload->load_type('upload');
			static::$s_fonts_upload->set_callback(array($this,'fonts_list'));
			static::$s_fonts_upload->set_filter(array_keys($this->filter));
			
			$fonts									= static::$s_fonts_upload->run_type()->get_data();
			if($fonts){
				foreach($fonts as $font){
					// group by filename without ext
					$filename						= basename(get_attached_file($font->ID));
					$fileparts						= explode('.',$filename);
					if(is_array($fileparts)) {
						$name = $fileparts[0];
						$ext = explode('.', $filename)[count($fileparts) - 1];
					}else{
						$name = $filename;
						$ext = 'no_ext';
					}
					
					if(!isset(static::$s_fonts[$name])){
						static::$s_fonts[$name]		= array();
					}
					
					static::$s_fonts[$name]['ext'][]	= $ext;
				}
				
				// create sub settings
				if(count(static::$s_fonts) > 0) {
					foreach(static::$s_fonts as $name => $data) {
						foreach(static::$s_fields as $field_id => $field_type){
							$s = static::$settings->create($this);
							$s->set_section_group($name);
							$s->set_section_name($name);
							$s->set_section_description(__('Filetypes available: ', $this->get_module_name()).implode(',', static::$s_fonts[$name]['ext']));
							$s->set_ID('font_' . $name . '_' . $field_id);
							$s->set_title(static::$s_titles[$field_id]);
							$s->set_description(static::$s_descriptions[$field_id]);
							
							if(isset(static::$s_options[$field_id])){
								$s->set_options(static::$s_options[$field_id]);
							}
							
							$s->load_type($field_type);
							static::$s_fonts[$name]['settings'][$field_id] = $s;
						}
					}
				}
			}
		}
		public function init(){
			add_filter('upload_mimes', array($this, 'upload_mimes'));
			add_action('wp_head', array($this, 'wp_head'));
			add_action('admin_menu', array($this, 'menu'));
			add_action('admin_enqueue_scripts', array($this, 'acp_style'));
		}
		public function fonts_list($setting): string{
			$form				= $setting->form();
			
			ob_start();
			require($this->get_path('lib/tpl/backend_upload.php'));
			$form .= ob_get_contents();
			ob_end_clean();
			
			return $form;
		}
		public function upload_mimes($mime_types = array()){
			return $this->filter;
		}
		public function menu(){
			add_submenu_page(
				'sv_wp_admin',																	// parent slug
				__('Webfontloader', $this->get_module_name()),											// page title
				__('Webfontloader', $this->get_module_name()),											// menu title
				'manage_options',																// capability
				$this->get_module_name(),																// menu slug
				function(){ require_once($this->get_path('lib/tpl/backend.php')); }				// callable function
			);
		}
		public function font_weight_select_options($val = ''){
			$output										= '';
			$i											= 1;
			while($i <= 9){
				$output									.= '<option'.(($val == intval($i.'00')) ? ' selected="selected"' : '').'>'.$i.'00</option>';
				$i++;
			}
			return $output;
		}
		public function setting_callback_fonts_mapping($args){
			$fonts										= $this->get_custom_fonts_grouped();
			echo '<div><strong>'.$args['description'].'</strong></div>';
			if(is_array($fonts) && count($fonts) > 0){
				echo '<table>';
				echo '<tr>
					<th>'.__('Font File', $this->get_module_name()).'</th>
					<th>'.__('CSS Font Name', $this->get_module_name()).'</th>
					<th>'.__('Italic', $this->get_module_name()).'</th>
					<th>'.__('Font Weight', $this->get_module_name()).'</th>
					<th>'.__('Load', $this->get_module_name()).'</th>
				</tr>';
				$active						= 0;
				foreach($fonts as $font => $extensions){
					echo '<tr>';
					echo '<td><strong>'.$font.'</strong><br/>'.implode(',',$extensions).'</td>';
					echo '<td><input placeholder="'.$font.'" name="'.$this->get_module_name().'_'.$args['setting_id'].'['.$font.'][name]" id="'.$args['setting_id'].'" type="text" value="'.get_option($this->get_module_name().'_'.$args['setting_id'])[$font]['name'].'" /></td>';
					echo '<td><input name="'.$this->get_module_name().'_'.$args['setting_id'].'['.$font.'][italic]" id="'.$args['setting_id'].'" type="checkbox" value="1" '.(
					
					get_option($this->get_module_name().'_'.$args['setting_id'])[$font]['italic'] ?
					' checked="checked"' :
					''
					
					).'/></td>';
					echo '<td>
						<select name="'.$this->get_module_name().'_'.$args['setting_id'].'['.$font.'][weight]" id="'.$args['setting_id'].'">
							'.$this->font_weight_select_options(get_option($this->get_module_name().'_'.$args['setting_id'])[$font]['weight']).'
						</select>
						</td>';
					echo '<td><input name="'.$this->get_module_name().'_'.$args['setting_id'].'['.$font.'][active]" id="'.$args['setting_id'].'" type="checkbox" value="1" '.(
					
					get_option($this->get_module_name().'_'.$args['setting_id'])[$font]['active'] ?
					' checked="checked"' :
					''
					
					).'/></td>';
					echo '</tr>';
					
					if(get_option($this->get_module_name().'_'.$args['setting_id'])[$font]['active']){
						$active++;
					}
				}
				echo '</table>';
				echo '<p>You have <strong>'.$active.'</strong> fonts activated.</p>';
			}
		}
		public function get_custom_fonts(){
			// custom/local fonts
			if(!$this->custom_fonts){
				try{
					if($this->get_path('lib/fonts/')){
						$this->custom_fonts				= array_diff(scandir($this->get_path('lib/fonts/')), array('..', '.'));
						if(count($this->custom_fonts) > 0){
							return $this->custom_fonts;
						}else{
							return false;
						}
					}
				}catch(Exception $e){
					//echo $e->getMessage();
				}
			}else{
				return $this->custom_fonts;
			}
		}
		public function get_custom_fonts_grouped(){
			if(!$this->custom_fonts_grouped) {
				if($this->get_custom_fonts()) {
					$this->custom_fonts_grouped									= array();
					foreach ($this->get_custom_fonts() as $font) {
						$font_parts												= explode('.',$font);
						if(!isset($this->custom_fonts_grouped[$font_parts[0]])){
							$this->custom_fonts_grouped[$font_parts[0]]			= array();
						}
						$this->custom_fonts_grouped[$font_parts[0]][]			= $font_parts[1];
					}
				}else{
					return false;
				}
			}
			asort($this->custom_fonts_grouped);
			return $this->custom_fonts_grouped;
		}
		public function load_custom_fonts(){
			$formats								= array(
				'eot'								=> '',
				'woff'								=> 'woff',
				'woff2'								=> 'woff2',
				'ttf'								=> 'truetype',
				'otf'								=> 'opentype',
				'svg'								=> 'svg',
			);
			if($this->get_custom_fonts() && get_option($this->get_module_name().'_fonts_mapping') && is_array(get_option($this->get_module_name().'_fonts_mapping')) && count(get_option($this->get_module_name().'_fonts_mapping')) > 0){
				$groups								= $this->get_custom_fonts_grouped();
				if($groups) {
					$font_settings						= get_option($this->get_module_name().'_fonts_mapping');
					echo '<style id="'.$this->get_module_name().'">';
					foreach ($groups as $group => $extensions) {
						if (isset($font_settings[$group]) && isset($font_settings[$group]['active'])) {
							$names[$name] = $name = '"' . $font_settings[$group]['name'] . '"';
							$f = array("\n");
							$f[] = '@font-face {';
							$f[] = 'font-family: ' . $name . ';';
							
							// src
							foreach ($extensions as $ext) {
								$f[] = 'src:url("' . $this->get_url('lib/fonts/' . $group.'.'.$ext) . '")' .
									((strlen($formats[$ext]) > 0) ? ' format("' . $formats[$ext] . '");' : ';');
							}
							
							// weight
							$f[] = 'font-weight: ' . $font_settings[$group]['weight'] . ';';
							
							// italic
							if (isset($font_settings[$group]['italic'])) {
								$f[] = 'font-style: italic;';
							}
							
							$f[] = '}';
							echo implode("\n", $f);
						}
					}
					echo '</style>';
					
					$this->vendors .= 'custom: { families: [' . implode(',', $names) . '] }';
				}
			}
		}
		public function register($wp_customize){
			$wp_customize->add_setting($this->get_module_name().'_typekit', array(
				'default'							=> '',
				'transport'							=> 'refresh',
			));
			$wp_customize->add_setting($this->get_module_name().'_google', array(
				'default'							=> '',
				'transport'							=> 'refresh',
			));
			$wp_customize->add_setting($this->get_module_name().'_fontawesome', array(
				'default'							=> '',
				'transport'							=> 'refresh',
			));
			$wp_customize->add_section('sv_100_fonts', array(
				'title'								=> __('Fonts', 'sv_100'),
				'priority'							=> 30,
			));
			$wp_customize->add_control(new WP_Customize_Control($wp_customize, $this->get_module_name().'_typekit', array(
				'label'								=> __('Typekit Kit ID', 'sv_100'),
				'section'							=> 'sv_100_fonts',
				'settings'							=> $this->get_module_name().'_typekit',
				'description'						=> __('Enter the Typekit Kit ID to load your font kit.', 'sv_100'),
			)));
			$wp_customize->add_control(new WP_Customize_Control($wp_customize, $this->get_module_name().'_google', array(
				'label'								=> __('Google Font', 'sv_100'),
				'section'							=> 'sv_100_fonts',
				'settings'							=> $this->get_module_name().'_google',
				'description'						=> __("Example: ['Droid Sans', 'Droid Serif:bold']", 'sv_100'),
			)));
			$wp_customize->add_control(new JT_Customize_Control_Checkbox_Multiple($wp_customize, $this->get_module_name().'_fontawesome', array(
				'label'								=> __('Font Awesome', 'sv_100'),
				'section'							=> 'sv_100_fonts',
				'settings'							=> $this->get_module_name().'_fontawesome',
				'description'						=> __('', 'sv_100'),
				'choices'							=> array(
					'solid'							=> __('Solid', 'sv_100'),
					'regular'						=> __('Regular', 'sv_100'),
					'brands'						=> __('Brands', 'sv_100'),
				)
			)));
		}
		public function wp_head(){
			return;
			$this->load_custom_fonts();
			// we load typekit in head, but async, so there is no pagespeed penality while it got loaded as fast as possible
			// to prevent flash of unstyled text (FOUT), some CSS is inserted, too.
			
			if(get_theme_mod($this->get_module_name().'_typekit', false)){
				$this->vendors						.= ' typekit: { id : "'.get_theme_mod($this->get_module_name().'_typekit', '').'" }';
			}
			if(get_theme_mod($this->get_module_name().'_google', false)){
				$this->vendors						.= ' google: { families: '.get_theme_mod($this->get_module_name().'_google', '').' } ';
			}
			
			$fontawesome							= get_theme_mod($this->get_module_name().'_fontawesome', false);
			if($fontawesome && strlen($fontawesome) > 0){
				$fontawesome						= explode(',', $fontawesome);
				if($fontawesome && is_array($fontawesome) && count($fontawesome) > 0){
					if(count($fontawesome) === 3){
						echo '<script defer src="https://use.fontawesome.com/releases/v5.0.8/js/all.js" integrity="sha384-SlE991lGASHoBfWbelyBPLsUlwY1GwNDJo3jSJO04KZ33K2bwfV9YBauFfnzvynJ" crossorigin="anonymous"></script>';
					}else{
						foreach($fontawesome as $fa_part){
							echo '<script defer src="https://use.fontawesome.com/releases/v5.0.8/js/fontawesome.js" integrity="sha384-7ox8Q2yzO/uWircfojVuCQOZl+ZZBg2D2J5nkpLqzH1HY0C1dHlTKIbpRz/LG23c" crossorigin="anonymous"></script>';
							if($fa_part == 'solid'){
								echo '<script defer src="https://use.fontawesome.com/releases/v5.0.8/js/solid.js" integrity="sha384-+Ga2s7YBbhOD6nie0DzrZpJes+b2K1xkpKxTFFcx59QmVPaSA8c7pycsNaFwUK6l" crossorigin="anonymous"></script>';
							}
							if($fa_part == 'regular'){
								echo '<script defer src="https://use.fontawesome.com/releases/v5.0.8/js/regular.js" integrity="sha384-t7yHmUlwFrLxHXNLstawVRBMeSLcXTbQ5hsd0ifzwGtN7ZF7RZ8ppM7Ldinuoiif" crossorigin="anonymous"></script>';
							}
							if($fa_part == 'brands'){
								echo '<script defer src="https://use.fontawesome.com/releases/v5.0.8/js/brands.js" integrity="sha384-sCI3dTBIJuqT6AwL++zH7qL8ZdKaHpxU43dDt9SyOzimtQ9eyRhkG3B7KMl6AO19" crossorigin="anonymous"></script>';
							}
						}
					}
				}
			}
			
			if(strlen($this->vendors) > 0){
				echo '
					<script data-sv_100_module="'.$this->get_module_name().'">
						WebFontConfig =
						{ '.$this->vendors.' }
						;
					</script>
					<style data-sv_100_module="'.$this->get_module_name().'">
						html:not(.wf-active) *{
							opacity:0 !important;
						}
						html *{
							opacity:1 !important;
							transition: all 1s linear;
						}
					</style>
				';
			}
		}
		public function customize_register(){
			require_once($this->get_path('lib/modules/multiple_checkboxes.php'));
		}
	}
?>