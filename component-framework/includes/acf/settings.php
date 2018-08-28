<?php
class oxygen_acf_integration_settings
{
	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $options;

	/**
	 * Start up
	 */
	public function __construct()
	{
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}

	/**
	 * Add options page
	 */
	public function add_plugin_page()
	{
		add_submenu_page( 'ct_dashboard_page', __( 'Advanced Custom Fields Integration Settings', 'oxygen-acf' ), __( 'ACF Settings', 'oxygen-acf' ), 'manage_options', 'oxygen_acf_settings', array( $this, 'settings_page' ) );
	}

	/**
	 * Options page callback
	 */
	public function settings_page()
	{
		// Set class property
		$this->options = get_option( 'oxygen_acf_option' );
		?>
		<div class="wrap">
			<h1><?php _e( 'Advanced Custom Fields Integration Settings', 'oxygen-acf' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'oxygen_acf_option_group' );
				do_settings_sections( 'oxygen_acf_setting_admin' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register and add settings
	 */
	public function page_init()
	{
		register_setting(
			'oxygen_acf_option_group', // Option group
			'oxygen_acf_option', // Option name
			array( $this, 'sanitize' ) // Sanitize
		);

		add_settings_section(
			'setting_section_id', // ID
			'', // Title
			array( $this, 'print_section_info' ), // Callback
			'oxygen_acf_setting_admin' // Page
		);

		add_settings_field(
			'google_maps_key',
			__( 'Google Maps API Key', 'oxygen-acf' ),
			array( $this, 'google_maps_key_callback' ),
			'oxygen_acf_setting_admin',
			'setting_section_id'
		);
	}

	public function sanitize( $input )
	{
		$new_input = array();

		if( isset( $input['google_maps_key'] ) )
			$new_input['google_maps_key'] = sanitize_text_field( $input['google_maps_key'] );

		return $new_input;
	}

	/**
	 * Print the Section text
	 */
	public function print_section_info()
	{
		_e( 'Enter your settings below:', 'oxygen-acf' );
	}


	/**
	 * Get the settings option array and print one of its values
	 */
	public function google_maps_key_callback()
	{
		printf(
			'<input type="text" id="google_maps_key" name="oxygen_acf_option[google_maps_key]" value="%s" />',
			isset( $this->options['google_maps_key'] ) ? esc_attr( $this->options['google_maps_key']) : ''
		);
	}
}

if( is_admin() )
	$oxygen_acf_integration_settings = new oxygen_acf_integration_settings();
