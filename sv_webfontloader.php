<?php
	namespace sv_100;
	
	/**
	 * @version         1.00
	 * @author			straightvisions
	 * @package			sv_100
	 * @copyright		2019 straightvisions GmbH
	 * @link			https://straightvisions.com
	 * @since			1.0
	 * @license			See license.txt or https://straightvisions.com
	 */
	
	class sv_webfontloader extends init {
		public function __construct() {
		
		}
		
		public function init() {
			// Translates the module
			load_theme_textdomain( $this->get_module_name(), $this->get_path( 'languages' ) );
			
			// Module Info
			$this->set_module_title( 'SV Webfontloader' );
			$this->set_module_desc( __( 'Upload and manage fonts.', $this->get_module_name() ) );
			
			// Section Info
			$this->set_section_title( __( 'Fonts', $this->get_module_name() ) );
			$this->set_section_desc( __( 'Upload and manage fonts.', $this->get_module_name() ) );
			$this->set_section_type( 'settings' );
			$this->get_root()->add_section( $this );
			
			$this->load_modules()->load_settings();
			
			// Action Hooks & Filter
			add_action( 'wp_head', array( $this, 'load_fonts' ) );
		}
		
		protected function load_modules(): sv_webfontloader {
			require_once( $this->get_path( 'lib/modules/filetype_manager.php' ) );
			
			$this->filetype_manager = new sv_webfontloader_filetype_manager();
			$this->filetype_manager->set_root( $this->get_root( ));
			$this->filetype_manager->set_parent( $this );
			$this->filetype_manager->init();
			
			return $this;
		}
		
		protected function load_settings(): sv_webfontloader {
			$this->s['fonts'] =
				static::$settings->create( $this )
								 ->set_ID( 'fonts' )
								 ->set_title( __( 'Add a new font', $this->get_module_name() ) )
								 ->load_type( 'group' );
			
			$this->s['fonts']
				->run_type()
				->add_child( $this )
				->set_ID( 'entry_label' )
				->set_title( __( 'Font Label', $this->get_module_name() ) )
				->set_description( __( 'A label to differentiate your uploaded fonts.', $this->get_module_name() ) )
				->load_type( 'text' )
				->set_placeholder( __( 'Label', $this->get_module_name() ) );
			
			$this->s['fonts']
				->run_type()
				->add_child( $this )
				->set_ID( 'family' )
				->set_title( __( 'Font family', $this->get_module_name() ) )
				->set_description( __( 'The name of the font family, that this font belongs too.', $this->get_module_name() ) )
				->load_type( 'text' )
				->set_placeholder( __( 'Name', $this->get_module_name() ) );
			
			$this->s['fonts']
				->run_type()
				->add_child( $this )
				->set_ID( 'active' )
				->set_title( __( 'Active', $this->get_module_name() ) )
				->set_description( __( 'Activate or deactivate this font.', $this->get_module_name() ) )
				->load_type( 'checkbox' );
			
			$this->s['fonts']
				->run_type()
				->add_child( $this )
				->set_ID( 'italic' )
				->set_title( __( 'Italic', $this->get_module_name() ) )
				->set_description( __( 'Is the font italic?', $this->get_module_name() ) )
				->load_type( 'checkbox' );
			
			$this->s['fonts']
				->run_type()
				->add_child( $this )
				->set_ID( 'weight' )
				->set_title( __( 'Font weight', $this->get_module_name() ) )
				->set_description( __( 'Select the font weight.', $this->get_module_name() ) )
				->load_type( 'select' )
				->set_options( array(
					100 => 100,
					200 => 200,
					300 => 300,
					400 => 400,
					500 => 500,
					600 => 600,
					700 => 700,
					800 => 800,
					900 => 900
					) );
			
			$this->s['fonts']
				->run_type()
				->add_child( $this )
				->set_ID( 'file_ttf' )
				->set_title( __( 'TrueType (.ttf)', $this->get_module_name() ) )
				->set_description( __( 'Select or drag-and-drop your .ttf file here.', $this->get_module_name() ) )
				->load_type( 'upload' )
				->run_type()->set_allowed_filetypes(array('.ttf'));
			
			$this->s['fonts']
				->run_type()
				->add_child( $this )
				->set_ID( 'file_otf' )
				->set_title( __( 'OpenType (.otf)', $this->get_module_name() ) )
				->set_description( __( 'Select or drag-and-drop your .otf file here.', $this->get_module_name() ) )
				->load_type( 'upload' )
				->run_type()->set_allowed_filetypes(array('.otf'));
			
			$this->s['fonts']
				->run_type()
				->add_child( $this )
				->set_ID( 'file_woff' )
				->set_title( __( 'Web Open Font Format (.woff)', $this->get_module_name() ) )
				->set_description( __( 'Select or drag-and-drop your .woff file here.', $this->get_module_name() ) )
				->load_type( 'upload' )
				->run_type()->set_allowed_filetypes(array('.woff'));
			
			$this->s['fonts']
				->run_type()
				->add_child( $this )
				->set_ID( 'file_woff2' )
				->set_title( __( 'Web Open Font Format 2.0 (.woff2)', $this->get_module_name() ) )
				->set_description( __( 'Select or drag-and-drop your .woff2 file here.', $this->get_module_name() ) )
				->load_type( 'upload' )
				->run_type()->set_allowed_filetypes(array('.woff2'));
			
			return $this;
		}
		
		public function load_fonts() {
			$fonts = $this->s['fonts']->run_type()->get_data();
			
			if ( $fonts && is_array( $fonts ) && count( $fonts ) > 0 ) {
				echo '<style data-sv_100_module="' . $this->get_prefix( 'fonts' ) . '">';
				
				foreach ( $fonts as $font ) {
					if ( $font['active'] === '1' ) {
						$output 		= array();
						$output[]		= '@font-face {';
						$output[]		= "\t" . 'font-family: "' . $font['family'] . '";';
						$output[]		= "\t" . 'font-display: fallback;';
						
						// Font Weight
						$output[]		= "\t" . 'font-weight: ' . $font['weight'] . ';';
						
						// Font Style
						if ( $font['italic'] ) {
							$output[] 	= "\t" . 'font-style: italic;';
						}
						
						// Source Files
						$urls			= array();
						
						// TrueType .ttf
						if ( isset( $font['file_ttf'] ) && ! empty( $font['file_ttf'] ) ) {
							$urls[]		= "\t" . 'src: url("' . wp_get_attachment_url( $font['file_ttf']['file'] )  . '") format("truetype");';
						}
						
						// OpenType .otf
						if ( isset( $font['file_otf'] ) && ! empty( $font['file_otf'] ) ) {
							$urls[]		= "\t" . 'src: url("' . wp_get_attachment_url( $font['file_otf']['file'] ) . '") format("opentype");';
						}
						
						// Web Open Font Format .woff
						if ( isset( $font['file_woff'] ) && ! empty( $font['file_woff'] ) ) {
							$urls[]		= "\t" . 'src: url("' . wp_get_attachment_url( $font['file_woff']['file'] ) . '") format("woff");';
						}
						
						// Web Open Font Format 2.0 .woff2
						if ( isset( $font['file_woff2'] ) && ! empty( $font['file_woff2'] ) ) {
							$urls[]		= "\t" . 'src: url("' . wp_get_attachment_url( $font['file_woff2']['file'] ) . '") format("woff2");';
						}

						$output[]		= implode( "\n", $urls );
						$output[]		= '}' . "\n\n";
						
						echo implode( "\n", $output );
					}
				}
				
				echo '</style>';
			}
		}
	}