<?php
namespace sv_100;

/**
 * @version         1.00
 * @author			straightvisions GmbH
 * @package			sv_100
 * @copyright		2017 straightvisions GmbH
 * @link			https://straightvisions.com
 * @since			1.0
 * @license			See license.txt or https://straightvisions.com
 */

class sv_webfontloader extends init {
	private $vendors							= '';
	private $s_fields							= array(
		'family_name'							=> 'text',
		'italic'								=> 'checkbox',
		'weight'								=> 'select',
		'active'								=> 'checkbox',
	);
	private $s_titles							= array();
	private $s_descriptions						= array();
	private $s_options							= array();

	public function __construct() {

	}

	public function init() {
		// Module Info
		$this->set_module_title( 'SV Webfontloader' );
		$this->set_module_desc( __( 'This module gives the ability to upload & manage webfonts.', $this->get_module_name() ) );

		// Section Info
		$this->set_section_title( 'Webfontloader' );
		$this->set_section_desc( 'Configure Fonts previously uploaded.' );
		$this->set_section_type( 'settings' );

		// Loads Scripts
		static::$scripts->create( $this )
		                ->set_path( 'lib/js/backend.js' )
		                ->set_is_backend()
		                ->set_type( 'js' );

		// Font Settings
		$this->s_titles['family_name']			= __( 'Family Name', $this->get_module_name() );
		$this->s_titles['italic']				= __( 'italic', $this->get_module_name() );
		$this->s_titles['weight']				= __( 'Font Weight', $this->get_module_name() );
		$this->s_titles['active']				= __( 'active', $this->get_module_name() );

		$this->s_descriptions['family_name']	= __( 'Font Family Name, e.g. for CSS', $this->get_module_name() );
		$this->s_descriptions['italic']			= __( 'If this font is italic version, activate this setting.', $this->get_module_name() );
		$this->s_descriptions['weight']			= __( 'Please select font weight.', $this->get_module_name() );
		$this->s_descriptions['active']			= __( 'Only active fonts will be loaded.', $this->get_module_name() );

		$this->s_options['weight']				= array(
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

		// Includes
		require_once( $this->get_path( 'lib/modules/upload_fonts.php' ) );

		// Upload Settings
		$this->upload_fonts						= new sv_webfontloader_upload_fonts();
		$this->upload_fonts->set_root( $this->get_root( ));
		$this->upload_fonts->set_parent( $this );
		$this->upload_fonts->init();

		require_once($this->get_path('lib/modules/icon_fonts.php'));

		$this->icon_fonts						= new sv_webfontloader_icon_fonts();
		$this->icon_fonts->set_root($this->get_root());
		$this->icon_fonts->set_parent($this);
		$this->icon_fonts->init();

		// Action Hooks
		add_action( 'wp_head', array( $this, 'wp_head' ) );

		// Section Info
		$this->get_root()->add_section( $this );
		$this->get_root()->add_section( $this->upload_fonts );
		$this->get_root()->add_section( $this->icon_fonts );

		// Loads Settings
		$this->load_settings();
	}
	private function font_settings() {
		$fonts									= $this->upload_fonts->get_settings()['uploaded_fonts']->run_type()->get_data();

		if($fonts){
			foreach($fonts as $font){
				// group by filename without ext
				$url							= wp_get_attachment_url($font->ID);
				$filename						= basename($url);
				$fileparts						= explode('.',$filename);
				if(is_array($fileparts)) {
					$name = $fileparts[0];
					$ext = explode('.', $filename)[count($fileparts) - 1];
				}else{
					$name = $filename;
					$ext = 'no_ext';
				}

				if(!isset($this->s[$name])){
					$this->s[$name]		= array();
				}

				$this->s[$name]['url'][$ext]	= $url;
			}
			$this->font_sub_settings();
		}
	}

	private function font_sub_settings() {
		// create sub settings
		if(count($this->s) > 0) {
			foreach($this->s as $name => $data) {
				foreach($this->s_fields as $field_id => $field_type){
					$s = static::$settings->create($this)
						->set_ID('font_' . $name . '_' . $field_id)
						->set_title($this->s_titles[$field_id])
						->set_description($this->s_descriptions[$field_id].'<br />'.__('Filetypes available: ', $this->get_module_name()).implode(',', array_keys($this->s[$name]['url'])));

					if(isset($this->s_options[$field_id])){
						$s->set_options($this->s_options[$field_id]);
					}

					$s->load_type($field_type);
					$this->s[$name]['settings'][$field_id] = $s;
				}
			}
		}
	}

	public function load_settings() {
		$this->font_settings();
	}

	public function load_custom_fonts() {
		$formats								= array(
			'eot'								=> '',
			'woff'								=> 'woff',
			'woff2'								=> 'woff2',
			'ttf'								=> 'truetype',
			'otf'								=> 'opentype',
			'svg'								=> 'svg',
		);
		$names									= array();

		if($this->s) {
			echo '<style data-sv_100_module="'.$this->get_module_name().'">';
			foreach ($this->s as $name => $data) {
				if ($data['settings']['active']->run_type()->get_data() == 1) {
					$names[$family_name]		= $family_name		= $data['settings']['family_name']->run_type()->get_data();
					$f = array("\n");
					$f[] = '@font-face {';
					$f[] = 'font-family: "' . $family_name . '";';
					$f[] = 'font-display: fallback;';


					// src
					$urls						= $data['url'];
					asort($urls);
					foreach ($urls as $ext => $url) {
						$f[] = 'src:url("' . $url . '")' .
							((strlen($formats[$ext]) > 0) ? ' format("' . $formats[$ext] . '");' : ';');
					}

					// weight
					$f[] = 'font-weight: ' . $data['settings']['weight']->run_type()->get_data() . ';';

					// italic
					if ($data['settings']['italic']->run_type()->get_data() == 1) {
						$f[] = 'font-style: italic;';
					}

					$f[] = '}';
					echo implode("\n", $f);
				}
			}
			echo '</style>';

			$this->vendors .= 'custom: { families: ["' . implode('","', $names) . '"] }';
		}
	}

	public function wp_head() {
		$this->load_custom_fonts();
		// we load typekit in head, but async, so there is no pagespeed penality while it got loaded as fast as possible
		// to prevent flash of unstyled text (FOUT), some CSS is inserted, too.

		/* // we need to upgrade this snippet if we want to allow loading vendor fonts
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
		*/
		if(strlen($this->vendors) > 0){
			echo '
				<script data-sv_100_module="'.$this->get_module_name().'">
					WebFontConfig =
					{
					'.$this->vendors.'
					}
					;
				</script>
			';

			/* // this reduces pagespeed score, so deactivated
			echo '
				<style data-sv_100_module="'.$this->get_module_name().'">
					html:not(.wf-inactive):not(.wf-active) *{
						opacity:0 !important;
					}
					html *{
						transition: opacity 0.5s linear;
					}
				</style>
			';
			*/
		}
	}
}