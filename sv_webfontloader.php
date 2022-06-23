<?php
	namespace sv100;

	class sv_webfontloader extends init {
		public function init() {
			$this->set_module_title( __( 'SV Webfontloader', 'sv100' ) )
				->set_module_desc( __( 'Upload and manage fonts.', 'sv100' ) )
				->load_modules()
				->set_css_cache_active()
				->set_section_title( $this->get_module_title() )
				->set_section_desc( $this->get_module_desc() )
				->set_section_template_path()
				->register_scripts()
				->set_section_order(600)
				->set_section_icon('<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M22 0h-20v6h1.999c0-1.174.397-3 2.001-3h4v16.874c0 1.174-.825 2.126-2 2.126h-1v2h9.999v-2h-.999c-1.174 0-2-.952-2-2.126v-16.874h4c1.649 0 2.02 1.826 2.02 3h1.98v-6z"/></svg>')
				->get_root()
				->add_section( $this );

			// Prefetch
			if(!is_admin()){
				add_action( 'wp_head', array( $this, 'load_fonts' ) );
			}
		}
		public function theme_json_update_data(){
			$theme_json     = $this->theme_json_get_data();
			$fonts          = $this->get_setting( 'fonts' )->get_data();

			if(!$fonts || !is_array($fonts) || count($fonts) === 0){
				return $theme_json;
			}

			if (!is_dir($this->get_active_theme_path().'fonts/')) {
				// dir doesn't exist, make it
				mkdir($this->get_active_theme_path().'fonts/', 0755, true);
			}

			$theme_json['settings']['typography']['fontFamilies']   = array();

			foreach($fonts as $font){
				if ( $font['active'] !== '1') {
					continue;
				}

				$path_src   = get_attached_file( $font['file_woff2']['file'] );

				if(!file_exists($path_src)){
					continue;
				}

				$path_new   = 'fonts/'.pathinfo($path_src, PATHINFO_FILENAME).'.'.pathinfo($path_src, PATHINFO_EXTENSION );

				copy($path_src,$this->get_active_theme_path().$path_new );

				if(!isset($theme_json['settings']['typography']['fontFamilies'][$font['slug']])){
					$theme_json['settings']['typography']['fontFamilies'][$font['slug']]   = array(
						'slug'              => $font['slug'],
						'name'              => $font['entry_label'],
						'fontFamily'        => $font['family']
					);
				}

				$theme_json['settings']['typography']['fontFamilies'][$font['slug']]['fontFace'][]  =
					array(
						'fontFamily'         => $font['family'],
						'fontWeight'         => $font['weight'],
						'fontStyle'          => $font['italic'] ? 'italic' : 'normal',
						'fontStretch'        => 'normal',
						'src'                => array('file:./'.$path_new)
					);
			}

			return $theme_json;
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
			     ->set_ID( 'slug' )
			     ->set_title( __( 'Slug', 'sv100' ) )
			     ->set_description( __( 'The slug of the font family.', 'sv100' ) )
			     ->load_type( 'id' )
			     ->set_placeholder( __( 'Slug', 'sv100' ) );
			
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