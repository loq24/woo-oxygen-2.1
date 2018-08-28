<?php 

/**
 * Header Builder Row Left component
 *
 * @since 2.0
 * @author Ilya K.
 */

Class Oxy_Header_Builder_Row_Left extends CT_Component {

	function __construct( $options ) {

		// run initialization
		$this->init( $options );

		// Remove component button
		remove_action("ct_toolbar_fundamentals_list", array( $this, "component_button" ) );
		
		// Add shortcodes
		add_shortcode( $this->options['tag'], array( $this, 'add_shortcode' ) );

		for ( $i = 2; $i <= 16; $i++ ) {
			add_shortcode( $this->options['tag'] . "_" . $i, array( $this, 'add_shortcode' ) );
		}
	}


	/**
	 * Add a [oxy_header_left] shortcode to WordPress
	 *
	 * @since 2.0
	 */

	function add_shortcode( $atts, $content ) {

		$options = $this->set_options( $atts );

		ob_start();
		
		?><div id="<?php echo esc_attr($options['selector']); ?>" class="<?php echo esc_attr($options['classes']); ?>"><?php echo do_shortcode( $content ); ?></div><?php

		return ob_get_clean();
	}
}


// Create inctance
$oxy_header_left = new Oxy_Header_Builder_Row_Left( array( 
			'name' 		=> __('Row Left', 'oxygen'),
			'tag' 		=> 'oxy_header_left',
			'params' 	=> array(),
			'advanced' 	=> array(
					"positioning" => array(
						"values" => array(
							)
					)
				)
		)
	);