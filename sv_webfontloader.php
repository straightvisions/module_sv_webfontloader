<?php
	namespace sv100;

	class sv_webfontloader extends init {
		public function init() {
			$this->set_module_title( __( 'SV Webfontloader', 'sv100' ) )
				 ->set_module_desc( __( 'Upload and manage fonts.', 'sv100' ) )
				 ->load_modules()
				->set_section_title( $this->get_module_title() )
				->set_section_desc( $this->get_module_desc() )
				->set_section_type( 'settings' )
				->set_section_template_path()
				->set_section_order(5000)
				->get_root()
				->add_section( $this );

			// Action Hooks & Filter
			if(is_admin()){
				add_action('enqueue_block_editor_assets', array($this, 'gutenberg_fonts'), 9999);
			}else{
				add_action( 'wp_head', array( $this, 'load_fonts' ) );
			}
		}

		public function gutenberg_fonts(){
			wp_add_inline_style('sv_core_gutenberg_style', $this->load_fonts(true));
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
			$this->get_setting( 'fonts' )
				 ->set_title( __( 'Add a new font', 'sv100' ) )
				 ->load_type( 'group' );
			
			$this->get_setting( 'fonts' )
				 ->run_type()
				 ->add_child()
				 ->set_ID( 'entry_label' )
				 ->set_title( __( 'Font label', 'sv100' ) )
				 ->set_description( __( 'A label to differentiate your uploaded fonts.', 'sv100' ) )
				 ->load_type( 'text' )
				 ->set_placeholder( __( 'Label', 'sv100' ) );
			
			$this->get_setting( 'fonts' )
				 ->run_type()
				 ->add_child()
				 ->set_ID( 'family' )
				 ->set_title( __( 'Font family', 'sv100' ) )
				 ->set_description( __( 'The name of the font family.', 'sv100' ) )
				 ->load_type( 'text' )
				 ->set_placeholder( __( 'Name', 'sv100' ) );
			
			$this->get_setting( 'fonts' )
				 ->run_type()
				 ->add_child()
				 ->set_ID( 'active' )
				 ->set_title( __( 'Active', 'sv100' ) )
				 ->set_description( __( 'Activate or deactivate this font.', 'sv100' ) )
				 ->load_type( 'checkbox' );
			
			$this->get_setting( 'fonts' )
				 ->run_type()
				 ->add_child()
				 ->set_ID( 'italic' )
				 ->set_title( __( 'Italic', 'sv100' ) )
				 ->set_description( __( 'Is the font italic?', 'sv100' ) )
				 ->load_type( 'checkbox' );
			
			$this->get_setting( 'fonts' )
				 ->run_type()
				 ->add_child()
				 ->set_ID( 'weight' )
				 ->set_title( __( 'Font weight', 'sv100' ) )
				 ->set_description( __( 'Select the font weight.', 'sv100' ) )
				 ->load_type( 'select' )
				 ->set_options(
				 	array(
						100 => 100,
						200 => 200,
						300 => 300,
						400 => 400,
						500 => 500,
						600 => 600,
						700 => 700,
						800 => 800,
						900 => 900
					)
				 );
			
			$this->get_setting( 'fonts' )
				 ->run_type()
				 ->add_child()
				 ->set_ID( 'file_ttf' )
				 ->set_title( __( 'TrueType (.ttf)', 'sv100' ) )
				 ->set_description( __( 'Select or drag-and-drop your .ttf file here.', 'sv100' ) )
				 ->load_type( 'upload' )
				 ->run_type()
				 ->set_allowed_filetypes( array( '.ttf' ) );
			
			$this->get_setting( 'fonts' )
				 ->run_type()
				 ->add_child()
				 ->set_ID( 'file_otf' )
				 ->set_title( __( 'OpenType (.otf)', 'sv100' ) )
				 ->set_description( __( 'Select or drag-and-drop your .otf file here.', 'sv100' ) )
				 ->load_type( 'upload' )
				 ->run_type()
				 ->set_allowed_filetypes( array( '.otf' ) );
			
			$this->get_setting( 'fonts' )
				 ->run_type()
				 ->add_child()
				 ->set_ID( 'file_woff' )
				 ->set_title( __( 'Web Open Font Format (.woff)', 'sv100' ) )
				 ->set_description( __( 'Select or drag-and-drop your .woff file here.', 'sv100' ) )
				 ->load_type( 'upload' )
				 ->run_type()
				 ->set_allowed_filetypes( array( '.woff' ) );
			
			$this->get_setting( 'fonts' )
				 ->run_type()
				 ->add_child()
				 ->set_ID( 'file_woff2' )
				 ->set_title( __( 'Web Open Font Format 2.0 (.woff2)', 'sv100' ) )
				 ->set_description( __( 'Select or drag-and-drop your .woff2 file here.', 'sv100' ) )
				 ->load_type( 'upload' )
				 ->run_type()
				 ->set_allowed_filetypes( array( '.woff2' ) );
			
			return $this;
		}
		
		public function get_font_by_label( string $label ): array {
			$output = array();
			$fonts 	= $this->get_setting( 'fonts' )->get_data();

			// sv100_sv_webfontloader_get_font_by_label
			$fonts = apply_filters($this->get_prefix(__FUNCTION__), $fonts ? $fonts : array());

			if ( count($fonts) > 0 ) {
				foreach ( $fonts as $font ) {
					if ( $font['entry_label'] === $label ) {
						return $font;
					}
				}
			}
			
			return $output;
		}
		
		public function load_fonts(bool $return=false) {
			$before = '';
			$css = '';
			$after = '';

			$fonts = $this->get_setting( 'fonts' )->get_data();
			
			if ( $fonts && is_array( $fonts ) && count( $fonts ) > 0 ) {

				$data_types = array(
					/*'file_ttf'		=> 'font/ttf',
					'file_otf'			=> 'font/otf',
					'file_woff'			=> 'font/woff',*/
					'file_woff2'		=> 'font/woff2'
				);

				// Preloading critical fonts maximize pagespeed
				// only preload woff2 to avoid browser loading old standard fonts if not needed
				// @todo: allow user to select fonts as critical for preload
				foreach ( $fonts as $font ) {
					foreach( $data_types as $d => $t ) {
						if ( isset( $font[ $d ] ) ) {
							$before .= '<link rel="preload" as="font" href="'
								 . wp_get_attachment_url( $font[ $d ]['file'] )
								 . '" type="' . $t . '" crossorigin />';
						}
					}
				}

				$before .= '<style data-sv100_module="' . $this->get_prefix( 'fonts' ) . '">';
				
				foreach ( $fonts as $font ) {
					if ( $font['active'] === '1' ) {
						$output 		= array();
						$output[]		= '@font-face {';
						$output[]		= "\t" . 'font-family: "' . $font['family'] . '";';
						$output[]		= "\t" . 'font-display: swap;'; // @todo: make this a usersetting, default "swap" for best pagespeed
						
						// Font Weight
						$output[]		= "\t" . 'font-weight: ' . $font['weight'] . ';';
						
						// Font Style
						if ( isset( $font['italic'] ) && $font['italic'] == 1 ) {
							$output[] 	= "\t" . 'font-style: italic;'.$font['italic'];
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

						$css .= implode( "\n", $output );
					}
				}
				
				$after .= '</style>';
			}

			if(!$return){
				echo $before.$css.$after;
			}else{
				return $css;
			}
		}

		// Returns an array font labels of all available fonts in Webfontloader
		public function get_font_options(): array {
			$fonts = array( '' => __( 'choose...', 'sv100' ) );

			if ( $this->get_setting( 'fonts' )->get_data() ){
				$font_array = $this->get_setting( 'fonts' )->get_data();
			} else {
				$font_array = array();
			}

			$font_array = apply_filters( $this->get_prefix(), $font_array );

			if ( count( $font_array ) > 0 ) {
				foreach( $font_array as $font ) {
					$fonts[ $font['entry_label'] ] = $font['entry_label'];
				}
			}
			
			return $fonts;
		}
	}