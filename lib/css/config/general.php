<?php
	$fonts  = $module->get_setting( 'fonts' )->get_data();
	$css    = '';
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
				$output[] 	= "\t" . 'font-style: italic;';
			}

			// Source Files
			$urls			= array();

			// Web Open Font Format 2.0 .woff2
			if ( isset( $font['file_woff2'] ) && ! empty( $font['file_woff2'] ) ) {
				$urls[]		= "\t" . 'src: url("' . wp_get_attachment_url( $font['file_woff2']['file'] ) . '") format("woff2");';
			}

			$output[]		= implode( "\n", $urls );
			$output[]		= '}' . "\n\n";

			$css .= implode( "\n", $output );
		}
	}

	echo $css;