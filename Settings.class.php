<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'ewdotpSettings' ) ) {
/**
 * Class to handle configurable settings for Order Tracking
 * @since 3.0.0
 */
class ewdotpSettings {

	public $order_information_options = array();

	public $sales_rep_options = array();

	public $status_options = array();

	public $currency_options = array();

	public $email_options = array();

	/**
	 * Default values for settings
	 * @since 3.0.0
	 */
	public $defaults = array();

	/**
	 * Stored values for settings
	 * @since 3.0.0
	 */
	public $settings = array();

	public function __construct() {

		add_action( 'init', array( $this, 'set_defaults' ) );

		add_action( 'init', array( $this, 'set_field_options' ), 11 );

		add_action( 'init', array( $this, 'load_settings_panel' ), 12 );
	}

	/**
	 * Load the plugin's default settings
	 * @since 3.0.0
	 */
	public function set_defaults() {

		$order_information_defaults = array(
			'order_number',
			'order_name',
			'order_status',
			'order_updated',
			'order_notes',
		);

		$this->defaults = array(

			'order-information'					=> $order_information_defaults,

			'date-format'						=> _x( 'd-m-Y H:i:s', 'Default date format for display. Available options here https://www.php.net/manual/en/datetime.format.php', 'order-tracking' ),
			'form-instructions'					=> __( 'Enter the order number you would like to track in the form below.', 'order-tracking' ),

			'access-role'						=> 'manage_options',
			'tracking-graphic'					=> 'default',

			'google-maps-api-key'				=> 'AIzaSyBFLmQU4VaX-T67EnKFtos7S7m_laWn6L4',

			'email-messages'					=> array(),

			'label-retrieving-results'			=> __( 'Retrieving Results...', 'order-tracking' ),
			'label-customer-order-thank-you'	=> __( 'Thank you. Your order number is:', 'order-tracking' ),
		);

		$this->defaults = apply_filters( 'ewd_otp_defaults', $this->defaults, $this );
	}

	/**
	 * Put all of the available possible select options into key => value arrays
	 * @since 3.0.0
	 */
	public function set_field_options() {
		global $ewd_otp_controller;

		$this->currency_options = array(
			'AUD' => __( 'Australian Dollar', 'order-tracking'),
			'BRL' => __( 'Brazilian Real', 'order-tracking'),
			'CAD' => __( 'Canadian Dollar', 'order-tracking'),
			'CZK' => __( 'Czech Koruna', 'order-tracking'),
			'DKK' => __( 'Danish Krone', 'order-tracking'),
			'EUR' => __( 'Euro', 'order-tracking'),
			'HKD' => __( 'Hong Kong Dollar', 'order-tracking'),
			'HUF' => __( 'Hungarian Forint', 'order-tracking'),
			'ILS' => __( 'Israeli New Sheqel', 'order-tracking'),
			'JPY' => __( 'Japanese Yen', 'order-tracking'),
			'MYR' => __( 'Malaysian Ringgit', 'order-tracking'),
			'MXN' => __( 'Mexican Peso', 'order-tracking'),
			'NOK' => __( 'Norwegian Krone', 'order-tracking'),
			'NZD' => __( 'New Zealand Dollar', 'order-tracking'),
			'PHP' => __( 'Philippine Peso', 'order-tracking'),
			'PLN' => __( 'Polish Zloty', 'order-tracking'),
			'GBP' => __( 'Pound Sterling', 'order-tracking'),
			'RUB' => __( 'Russian Ruble', 'order-tracking'),
			'SGD' => __( 'Singapore Dollar', 'order-tracking'),
			'SEK' => __( 'Swedish Krona', 'order-tracking'),
			'CHF' => __( 'Swiss Franc', 'order-tracking'),
			'TWD' => __( 'Taiwan New Dollar', 'order-tracking'),
			'THB' => __( 'Thai Baht', 'order-tracking'),
			'TRY' => __( 'Turkish Lira', 'order-tracking'),
			'USD' => __( 'U.S. Dollar', 'order-tracking'),
		);

		$this->order_information_options = array(
			'order_number'			=> __( 'Order Number', 'order-tracking' ),
			'order_name'			=> __( 'Name', 'order-tracking' ),
			'order_status'			=> __( 'Status', 'order-tracking' ),
			'order_location'		=> __( 'Location', 'order-tracking' ),
			'order_updated'			=> __( 'Updated Date', 'order-tracking' ),
			'order_notes'			=> __( 'Notes', 'order-tracking' ),
			'customer_notes'		=> __( 'Customer Notes', 'order-tracking' ),
			'order_graphic'			=> __( 'Status Graphic', 'order-tracking' ),
			'order_map'				=> __( 'Tracking Map', 'order-tracking' ),
			'customer_name'			=> __( 'Customer Name', 'order-tracking' ),
			'customer_email'		=> __( 'Customer Email', 'order-tracking' ),
			'sales_rep_first_name'	=> __( 'Sales Rep First Name', 'order-tracking' ),
			'sales_rep_last_name'	=> __( 'Sales Rep Last Name', 'order-tracking' ),
			'sales_rep_email'		=> __( 'Sales Rep Email', 'order-tracking' ),
		);

		$statuses = ewd_otp_decode_infinite_table_setting( $this->get_setting( 'statuses' ) );

		foreach ( $statuses as $status ) {

			$this->status_options[ $status->status ] = $status->status;
		}

		$emails = ewd_otp_decode_infinite_table_setting( $this->get_setting( 'email-messages' ) );

		foreach ( $emails as $email ) { 

			$this->email_options[ $email->id ] = $email->name;
		}

		if ( post_type_exists( 'uwpm_mail_template' ) ) {

			$this->email_options[-1] = '';
			
			$args = array(
				'post_type'		=> 'uwpm_mail_template',
				'numberposts'	=> -1
			);

			$uwpm_emails = get_posts( $args );

			foreach ( $uwpm_emails as $uwpm_email ) { 

				$email_id = $uwpm_email->ID * -1;

				$this->email_options[ $email_id ] = $uwpm_email->post_title;
			}
		}

		$args = array(
			'sales_reps_per_page'	=> -1
		);

		$sales_reps = $ewd_otp_controller->sales_rep_manager->get_matching_sales_reps( $args );

		foreach ( $sales_reps as $sales_rep ) {
			
			$this->sales_rep_options[ $sales_rep->id ] = $sales_rep->first_name . ' ' . $sales_rep->last_name; 
		}
	}

	/**
	 * Get a setting's value or fallback to a default if one exists
	 * @since 3.0.0
	 */
	public function get_setting( $setting ) { 

		if ( empty( $this->settings ) ) {
			$this->settings = get_option( 'ewd-otp-settings' );
		}
		
		if ( ! empty( $this->settings[ $setting ] ) ) {
			return apply_filters( 'ewd-otp-settings-' . $setting, $this->settings[ $setting ] );
		}

		if ( ! empty( $this->defaults[ $setting ] ) ) { 
			return apply_filters( 'ewd-otp-settings-' . $setting, $this->defaults[ $setting ] );
		}

		return apply_filters( 'ewd-otp-settings-' . $setting, null );
	}

	/**
	 * Set a setting to a particular value
	 * @since 3.0.0
	 */
	public function set_setting( $setting, $value ) {

		$this->settings[ $setting ] = $value;
	}

	/**
	 * Save all settings, to be used with set_setting
	 * @since 3.0.0
	 */
	public function save_settings() {
		
		update_option( 'ewd-otp-settings', $this->settings );
	}

	/**
	 * Load the admin settings page
	 * @since 3.0.0
	 * @sa https://github.com/NateWr/simple-admin-pages
	 */
	public function load_settings_panel() {

		global $ewd_otp_controller;

		require_once( EWD_OTP_PLUGIN_DIR . '/lib/simple-admin-pages/simple-admin-pages.php' );
		$sap = sap_initialize_library(
			$args = array(
				'version'       => '2.6.13',
				'lib_url'       => EWD_OTP_PLUGIN_URL . '/lib/simple-admin-pages/',
				'theme'			=> 'purple',
			)
		);
		
		$sap->add_page(
			'submenu',
			array(
				'id'            => 'ewd-otp-settings',
				'title'         => __( 'Settings', 'order-tracking' ),
				'menu_title'    => __( 'Settings', 'order-tracking' ),
				'parent_menu'	=> 'ewd-otp-orders',
				'description'   => '',
				'capability'    => $this->get_setting( 'access-role' ),
				'default_tab'   => 'ewd-otp-basic-tab',
			)
		);

		$sap->add_section(
			'ewd-otp-settings',
			array(
				'id'				=> 'ewd-otp-basic-tab',
				'title'				=> __( 'Basic', 'order-tracking' ),
				'is_tab'			=> true,
				'rank'				=> 1,
				'tutorial_yt_id'	=> 'v8t0Z06Y_XY',
				)
		);

		$sap->add_section(
			'ewd-otp-settings',
			array(
				'id'            => 'ewd-otp-general',
				'title'         => __( 'General', 'order-tracking' ),
				'tab'	        => 'ewd-otp-basic-tab',
			)
		);

		$sap->add_setting(
			'ewd-otp-settings',
			'ewd-otp-general',
			'textarea',
			array(
				'id'			=> 'custom-css',
				'title'			=> __( 'Custom CSS', 'order-tracking' ),
				'description'	=> __( 'You can add custom CSS styles to your appointment booking page in the box above.', 'order-tracking' ),			
			)
		);

		$sap->add_setting(
			'ewd-otp-settings',
			'ewd-otp-general',
			'checkbox',
			array(
				'id'            => 'order-information',
				'title'         => __( 'Order Information Displayed', 'order-tracking' ),
				'description'   => __( 'What information should be displayed for your orders?', 'order-tracking' ), 
				'options'       => $this->order_information_options
			)
		);

		$sap->add_setting(
			'ewd-otp-settings',
			'ewd-otp-general',
			'toggle',
			array(
				'id'			=> 'hide-blank-fields',
				'title'			=> __( 'Hide Blank Fields', 'order-tracking' ),
				'description'	=> __( 'Should fields which don\'t have a value (ex. customer name, custom fields) be hidden if they\'re empty?', 'order-tracking' )
			)
		);

		$sap->add_setting(
			'ewd-otp-settings',
			'ewd-otp-general',
			'textarea',
			array(
				'id'			=> 'form-instructions',
				'title'			=> __( 'Form Instructions', 'order-tracking' ),
				'description'	=> __( 'The instructions that will display above the order form.', 'order-tracking' ),			
			)
		);

		$sap->add_setting(
			'ewd-otp-settings',
			'ewd-otp-general',
			'text',
			array(
				'id'            => 'date-format',
				'title'         => __( 'Date/Time Format', 'order-tracking' ),
				'description'	=> __( 'The format to use when displaying dates. Possible options can be: <a href="https://www.php.net/manual/en/datetime.format.php">found here</a>', 'order-tracking' )
			)
		);

		$sap->add_setting(
			'ewd-otp-settings',
			'ewd-otp-general',
			'radio',
			array(
				'id'			=> 'email-frequency',
				'title'			=> __( 'Order Email Frequency', 'order-tracking' ),
				'description'	=> __( 'How often should emails be sent to customers about the status of their orders?', 'order-tracking' ),
				'options'		=> array(
					'change'		=> __( 'On Change', 'order-tracking' ),
					'creation'		=> __( 'On Creation', 'order-tracking' ),
					'never'			=> __( 'Never', 'order-tracking' ),
				)
			)
		);

		$sap->add_setting(
			'ewd-otp-settings',
			'ewd-otp-general',
			'toggle',
			array(
				'id'			=> 'disable-ajax-loading',
				'title'			=> __( 'Disable AJAX Reloads', 'order-tracking' ),
				'description'	=> __( 'Should the use of AJAX to display search results without reloading the page be disabled?', 'order-tracking' )
			)
		);

		$sap->add_setting(
			'ewd-otp-settings',
			'ewd-otp-general',
			'toggle',
			array(
				'id'			=> 'new-window',
				'title'			=> __( 'New Window', 'order-tracking' ),
				'description'	=> __( 'Should search results open in a new window? (Doesn\'t work with AJAX reloads)', 'order-tracking' )
			)
		);

		$sap->add_setting(
			'ewd-otp-settings',
			'ewd-otp-general',
			'toggle',
			array(
				'id'			=> 'display-print-button',
				'title'			=> __( 'Display "Print" Button', 'order-tracking' ),
				'description'	=> __( 'Should a "Print" button be added to tracking form results, so that visitors can print their order information more easily?', 'order-tracking' )
			)
		);

		$sap->add_setting(
			'ewd-otp-settings',
			'ewd-otp-general',
			'toggle',
			array(
				'id'			=> 'email-verification',
				'title'			=> __( 'Email Verification', 'order-tracking' ),
				'description'	=> __( 'Do visitors need to also enter the email address associated with an order to be able to view order information?', 'order-tracking' )
			)
		);

		$sap->add_setting(
			'ewd-otp-settings',
			'ewd-otp-general',
			'text',
			array(
				'id'          => 'google-maps-api-key',
				'title'       => __( 'Google Maps API Key', 'order-tracking' ),
				'description' => sprintf(
					__( 'If you\'re displaying a map with a map of your order locations ( using the "Tracking Map" checkbox of the "Order Information" setting above), Google requires an API key to use their maps. %sGet an API key%s.', 'order-tracking' ),
					'<a href="https://developers.google.com/maps/documentation/javascript/get-api-key">',
					'</a>'
				),
				'conditional_on'		=> 'order-information',
				'conditional_on_value'	=> 'order_map'
			)
		);

		$sap->add_setting(
			'ewd-otp-settings',
			'ewd-otp-general',
			'text',
			array(
				'id'            => 'tracking-page-url',
				'title'         => __( 'Status Tracking URL', 'order-tracking' ),
				'description'	=> __( 'The URL of your tracking page, required if you want to include a tracking link in your message body, on the WooCommerce order page, etc..', 'order-tracking' )
			)
		);

		$sap->add_setting(
			'ewd-otp-settings',
			'ewd-otp-general',
			'toggle',
			array(
				'id'          => 'use-wp-timezone',
				'title'       => __( 'Use WP Timezone', 'order-tracking' ),
				'description' => __( 'By default, the timestamp on status updates uses your server\'s timezone. Enabling this will make it display (in the admin and on the front-end tracking page) using the timezone you have set in your WordPress General Settings instead. ', 'order-tracking' )
			)
		);

		$sap->add_section(
			'ewd-otp-settings',
			array(
				'id'					=> 'ewd-otp-statuses-tab',
				'title'					=> __( 'Statuses', 'order-tracking' ),
				'is_tab'				=> true,
				'rank'					=> 3,
				'tutorial_yt_id'		=> 'ih7qJEuOgPY',
				)
		);

		$sap->add_section(
			'ewd-otp-settings',
			array(
				'id'            => 'ewd-otp-statuses',
				'title'         => __( 'Statuses', 'order-tracking' ),
				'tab'	        => 'ewd-otp-statuses-tab',
			)
		);

		$statuses_description = __( 'Statuses let your customers know the current status of their order.', 'order-tracking' ) . '<br />';
		
		$sap->add_setting(
			'ewd-otp-settings',
			'ewd-otp-statuses',
			'infinite_table',
			array(
				'id'			=> 'statuses',
				'title'			=> __( 'Statuses', 'order-tracking' ),
				'add_label'		=> __( '+ ADD', 'order-tracking' ),
				'del_label'		=> __( 'Delete', 'order-tracking' ),
				'description'	=> $statuses_description,
				'fields'		=> array(
					'status' => array(
						'type' 		=> 'text',
						'label' 	=> 'Status',
						'required' 	=> true
					),
					'percentage' => array(
						'type' 		=> 'text',
						'label' 	=> '&#37; Complete',
						'required' 	=> false
					),
					'email' => array(
						'type' 			=> 'select',
						'label' 		=> __( 'Email', 'order-tracking' ),
						'options' 		=> $this->email_options
					),
					'internal' => array(
						'type' 		=> 'select',
						'label' 	=> __( 'Internal Status', 'order-tracking' ),
						'options' 	=> array(
							'no'		=> __( 'No', 'order-tracking' ),
							'yes'		=> __( 'Yes', 'order-tracking' ),
						)
					)
				)
			)
		);

		$sap->add_section(
			'ewd-otp-settings',
			array(
				'id'				=> 'ewd-otp-emails-tab',
				'title'				=> __( 'Emails', 'order-tracking' ),
				'is_tab'			=> true,
				'rank'				=> 5,
				'tutorial_yt_id'	=> 'IDi__KeytMQ',
				)
		);

		$sap->add_section(
			'ewd-otp-settings',
			array(
				'id'            => 'ewd-otp-emails',
				'title'         => __( 'Emails', 'order-tracking' ),
				'tab'	        => 'ewd-otp-emails-tab',
			)
		);

		$emails_description = __( 'What should be in the messages sent to users? You can put [order-name], [order-number], [order-status], [order-notes], [customer-notes] and [order-time] into the message, to include current order name, order number, order status, public order notes or the time the order was updated.', 'order-tracking' ) . '<br />';
		$emails_description .= __( 'You can also use [tracking-link], [customer-name], [customer-number], [customer-id], [sales-rep], [sales-rep-number] or the slug of a custom field enclosed in square brackets to include those fields in the email.', 'order-tracking' );
		
		$sap->add_setting(
			'ewd-otp-settings',
			'ewd-otp-emails',
			'infinite_table',
			array(
				'id'			=> 'email-messages',
				'title'			=> __( 'Email Messages', 'order-tracking' ),
				'add_label'		=> __( '+ ADD', 'order-tracking' ),
				'del_label'		=> __( 'Delete', 'order-tracking' ),
				'description'	=> $emails_description,
				'fields'		=> array(
					'id' => array(
						'type' 		=> 'hidden',
						'label' 	=> 'ID',
						'required' 	=> true
					),
					'name' => array(
						'type' 		=> 'text',
						'label' 	=> 'Name',
						'required' 	=> true
					),
					'subject' => array(
						'type' 		=> 'text',
						'label' 	=> 'Subject',
						'required' 	=> true
					),
					'message' => array(
						'type' 		=> 'textarea',
						'label' 	=> 'Message',
						'required' 	=> true
					)
				)
			)
		);

		$sap->add_setting(
			'ewd-otp-settings',
			'ewd-otp-emails',
			'text',
			array(
				'id'            => 'admin-email',
				'title'         => __( 'Admin Email', 'order-tracking' ),
				'description'	=> __( 'What email should customer note and customer order notifications be sent to, if they\'ve been set in the "Premium" area of the "Options" tab? Leave blank to use the WordPress admin email address.', 'order-tracking' ),
				'small'			=> true
			)
		);

		/**
	     * Premium options preview only
	     */
	    // "Premium" Tab
	    $sap->add_section(
	      'ewd-otp-settings',
	      array(
	        'id'					=> 'ewd-otp-premium-tab',
	        'title'					=> __( 'Premium', 'order-tracking' ),
	        'is_tab'				=> true,
			'rank'					=> 2,
			'tutorial_yt_id'		=> 'DDQO1Wkahf0',
	        'show_submit_button'	=> $this->show_submit_button( 'premium' )
	      )
	    );
	    $sap->add_section(
	      'ewd-otp-settings',
	      array(
	        'id'       => 'ewd-otp-premium-tab-body',
	        'tab'      => 'ewd-otp-premium-tab',
	        'callback' => $this->premium_info( 'premium' )
	      )
	    );
	
	    // "Locations" Tab
	    $sap->add_section(
	      'ewd-otp-settings',
	      array(
	        'id'					=> 'ewd-otp-locations-tab',
	        'title'					=> __( 'Locations', 'order-tracking' ),
	        'is_tab'				=> true,
			'rank'					=> 4,
			'tutorial_yt_id'		=> 'hptAmlqQ4G0',
	        'show_submit_button'	=> $this->show_submit_button( 'locations' )
	      )
	    );
	    $sap->add_section(
	      'ewd-otp-settings',
	      array(
	        'id'       => 'ewd-otp-locations-tab-body',
	        'tab'      => 'ewd-otp-locations-tab',
	        'callback' => $this->premium_info( 'locations' )
	      )
	    );

	    // "Payments" Tab
	    $sap->add_section(
	      'ewd-otp-settings',
	      array(
	        'id'					=> 'ewd-otp-payments-tab',
	        'title'					=> __( 'Payments', 'order-tracking' ),
	        'is_tab'				=> true,
			'rank'					=> 7,
			'tutorial_yt_id'		=> 'oDt9BGvVdtQ',
	        'show_submit_button'	=> $this->show_submit_button( 'payments' )
	      )
	    );
	    $sap->add_section(
	      'ewd-otp-settings',
	      array(
	        'id'       => 'ewd-otp-payments-tab-body',
	        'tab'      => 'ewd-otp-payments-tab',
	        'callback' => $this->premium_info( 'payments' )
	      )
	    );
	
	    // "WooCommerce" Tab
	    $sap->add_section(
	      'ewd-otp-settings',
	      array(
	        'id'					=> 'ewd-otp-woocommerce-tab',
	        'title'					=> __( 'WooCommerce', 'order-tracking' ),
	        'is_tab'				=> true,
			'rank'					=> 6,
			'tutorial_yt_id'		=> 'zWTGldvnnc8',
	        'show_submit_button'	=> $this->show_submit_button( 'woocommerce' )
	      )
	    );
	    $sap->add_section(
	      'ewd-otp-settings',
	      array(
	        'id'       => 'ewd-otp-woocommerce-tab-body',
	        'tab'      => 'ewd-otp-woocommerce-tab',
	        'callback' => $this->premium_info( 'woocommerce' )
	      )
	    );	    
	
	    // "Zendesk" Tab
	    $sap->add_section(
	      'ewd-otp-settings',
	      array(
	        'id'					=> 'ewd-otp-zendesk-tab',
	        'title'					=> __( 'Zendesk', 'order-tracking' ),
	        'is_tab'				=> true,
			'rank'					=> 10,
			'tutorial_yt_id'		=> 'r00ewZ8l0z8',
	        'show_submit_button'	=> $this->show_submit_button( 'zendesk' )
	      )
	    );
	    $sap->add_section(
	      'ewd-otp-settings',
	      array(
	        'id'       => 'ewd-otp-zendesk-tab-body',
	        'tab'      => 'ewd-otp-zendesk-tab',
	        'callback' => $this->premium_info( 'zendesk' )
	      )
	    );
	
	    // "Labelling" Tab
	    $sap->add_section(
	      'ewd-otp-settings',
	      array(
	        'id'					=> 'ewd-otp-labelling-tab',
	        'title'					=> __( 'Labelling', 'order-tracking' ),
	        'is_tab'				=> true,
			'rank'					=> 9,
			'tutorial_yt_id'		=> 'oGimPjCPTdU',
	        'show_submit_button'	=> $this->show_submit_button( 'labelling' )
	      )
	    );
	    $sap->add_section(
	      'ewd-otp-settings',
	      array(
	        'id'       => 'ewd-otp-labelling-tab-body',
	        'tab'      => 'ewd-otp-labelling-tab',
	        'callback' => $this->premium_info( 'labelling' )
	      )
	    );
	
	    // "Styling" Tab
	    $sap->add_section(
	      'ewd-otp-settings',
	      array(
	        'id'					=> 'ewd-otp-styling-tab',
	        'title'					=> __( 'Styling', 'order-tracking' ),
	        'is_tab'				=> true,
			'rank'					=> 8,
			'tutorial_yt_id'		=> 'c75VcMG11a8',
	        'show_submit_button'	=> $this->show_submit_button( 'styling' )
	      )
	    );
	    $sap->add_section(
	      'ewd-otp-settings',
	      array(
	        'id'       => 'ewd-otp-styling-tab-body',
	        'tab'      => 'ewd-otp-styling-tab',
	        'callback' => $this->premium_info( 'styling' )
	      )
	    );

		$sap = apply_filters( 'ewd_otp_settings_page', $sap, $this );

		$sap->add_admin_menus();

	}

	public function show_submit_button( $permission_type = '' ) {
		global $ewd_otp_controller;

		if ( $ewd_otp_controller->permissions->check_permission( $permission_type ) ) {
			return true;
		}

		return false;
	}

	public function premium_info( $section_and_perm_type ) {
		global $ewd_otp_controller;

		$is_premium_user = $ewd_otp_controller->permissions->check_permission( $section_and_perm_type );
		$is_helper_installed = defined( 'EWDPH_PLUGIN_FNAME' ) && is_plugin_active( EWDPH_PLUGIN_FNAME );

		if ( $is_premium_user || $is_helper_installed ) {
			return false;
		}

		$content = '';

		$premium_features = '
			<p><strong>' . __( 'The premium version also gives you access to the following features:', 'order-tracking' ) . '</strong></p>
			<ul class="ewd-otp-dashboard-new-footer-one-benefits">
				<li>' . __( 'Create & Assign Orders to Sales Reps', 'order-tracking' ) . '</li>
				<li>' . __( 'Create & Tie Orders to Customers', 'order-tracking' ) . '</li>
				<li>' . __( 'Custom Fields', 'order-tracking' ) . '</li>
				<li>' . __( 'WooCommerce Order Integration', 'order-tracking' ) . '</li>
				<li>' . __( 'Advanced Display & Styling Options', 'order-tracking' ) . '</li>
				<li>' . __( 'Front-End Customer Order Form', 'order-tracking' ) . '</li>
				<li>' . __( 'Import/Export Orders', 'order-tracking' ) . '</li>
				<li>' . __( 'Set Up Status Locations', 'order-tracking' ) . '</li>
				<li>' . __( 'Email Support', 'order-tracking' ) . '</li>
			</ul>
			<div class="ewd-otp-dashboard-new-footer-one-buttons">
				<a class="ewd-otp-dashboard-new-upgrade-button" href="https://www.etoilewebdesign.com/license-payment/?Selected=OTP&Quantity=1&utm_source=otp_settings&utm_content=' . $section_and_perm_type . '" target="_blank">' . __( 'UPGRADE NOW', 'order-tracking' ) . '</a>
			</div>
		';

		switch ( $section_and_perm_type ) {

			case 'premium':

				$content = '
					<div class="ewd-otp-settings-preview">
						<h2>' . __( 'Premium', 'order-tracking' ) . '<span>' . __( 'Premium', 'order-tracking' ) . '</span></h2>
						<p>' . __( 'The premium options let you change the tracking graphic, configure notification emails, customize the order form and more.', 'order-tracking' ) . '</p>
						<div class="ewd-otp-settings-preview-images">
							<img src="' . EWD_OTP_PLUGIN_URL . '/assets/img/premium-screenshots/premium1.png" alt="OTP premium screenshot one">
							<img src="' . EWD_OTP_PLUGIN_URL . '/assets/img/premium-screenshots/premium2.png" alt="OTP premium screenshot two">
						</div>
						' . $premium_features . '
					</div>
				';

				break;

			case 'locations':

				$content = '
					<div class="ewd-otp-settings-preview">
						<h2>' . __( 'Locations', 'order-tracking' ) . '<span>' . __( 'Premium', 'order-tracking' ) . '</span></h2>
						<p>' . __( 'You can create locations, which can be assigned to orders, so your customers know exactly where their orders are. You can also specify latitude and longitude coordinates for each location, allowing it to display on a map on the tracking page.', 'order-tracking' ) . '</p>
						<div class="ewd-otp-settings-preview-images">
							<img src="' . EWD_OTP_PLUGIN_URL . '/assets/img/premium-screenshots/locations.png" alt="OTP locations screenshot">
						</div>
						' . $premium_features . '
					</div>
				';

				break;

			case 'payments':

				$content = '
					<div class="ewd-otp-settings-preview">
						<h2>' . __( 'Payments', 'order-tracking' ) . '<span>' . __( 'Premium', 'order-tracking' ) . '</span></h2>
						<p>' . __( 'The payment options let you enable and configure the ability to accept payment for orders via PayPal.', 'order-tracking' ) . '</p>
						<div class="ewd-otp-settings-preview-images">
							<img src="' . EWD_OTP_PLUGIN_URL . '/assets/img/premium-screenshots/payments.png" alt="OTP payments screenshot">
						</div>
						' . $premium_features . '
					</div>
				';

				break;

			case 'woocommerce':

				$content = '
					<div class="ewd-otp-settings-preview">
						<h2>' . __( 'WooCommerce', 'order-tracking' ) . '<span>' . __( 'Premium', 'order-tracking' ) . '</span></h2>
						<p>' . __( 'The WooCommerce options let you enable and configure the ability to have the plugin automatically create a corresponding order every time a new order is placed on your site via WooCommerce.', 'order-tracking' ) . '</p>
						<div class="ewd-otp-settings-preview-images">
							<img src="' . EWD_OTP_PLUGIN_URL . '/assets/img/premium-screenshots/woocommerce1.png" alt="OTP woocommerce screenshot one">
							<img src="' . EWD_OTP_PLUGIN_URL . '/assets/img/premium-screenshots/woocommerce2.png" alt="OTP woocommerce screenshot two">
						</div>
						' . $premium_features . '
					</div>
				';

				break;

			case 'zendesk':

				$content = '
					<div class="ewd-otp-settings-preview">
						<h2>' . __( 'Sendesk', 'order-tracking' ) . '<span>' . __( 'Premium', 'order-tracking' ) . '</span></h2>
						<p>' . __( 'This lets you enable Zendesk integration, so, every time you get a new ticket in Zendesk, it creates an order in the plugin.', 'order-tracking' ) . '</p>
						<div class="ewd-otp-settings-preview-images">
							<img src="' . EWD_OTP_PLUGIN_URL . '/assets/img/premium-screenshots/zendesk.png" alt="OTP zendesk screenshot">
						</div>
						' . $premium_features . '
					</div>
				';

				break;

			case 'labelling':

				$content = '
					<div class="ewd-otp-settings-preview">
						<h2>' . __( 'Labelling', 'order-tracking' ) . '<span>' . __( 'Premium', 'order-tracking' ) . '</span></h2>
						<p>' . __( 'The labelling options let you change the wording of the different labels that appear on the front end of the plugin. You can use this to translate them, customize the wording for your purpose, etc.', 'order-tracking' ) . '</p>
						<div class="ewd-otp-settings-preview-images">
							<img src="' . EWD_OTP_PLUGIN_URL . '/assets/img/premium-screenshots/labelling1.png" alt="OTP labelling screenshot one">
							<img src="' . EWD_OTP_PLUGIN_URL . '/assets/img/premium-screenshots/labelling2.png" alt="OTP labelling screenshot two">
						</div>
						' . $premium_features . '
					</div>
				';

				break;

			case 'styling':

				$content = '
					<div class="ewd-otp-settings-preview">
						<h2>' . __( 'Styling', 'order-tracking' ) . '<span>' . __( 'Premium', 'order-tracking' ) . '</span></h2>
						<p>' . __( 'The styling options let you modify the color, font size, font family, border, margin and padding of the various elements found in your tracking forms and orders.', 'order-tracking' ) . '</p>
						<div class="ewd-otp-settings-preview-images">
							<img src="' . EWD_OTP_PLUGIN_URL . '/assets/img/premium-screenshots/styling.png" alt="OTP styling screenshot">
						</div>
						' . $premium_features . '
					</div>
				';

				break;
		}

		return function() use ( $content ) {

			echo wp_kses_post( $content );
		};
	}

	/**
	 * Create a set of default statuses and emails if none exist
	 * @since 3.0.0
	 */
	public function create_default_statuses_and_emails() {

		$statuses = ewd_otp_decode_infinite_table_setting( $this->get_setting( 'statuses' ) );

		if ( ! empty( $statuses ) ) { return; }

		$emails = array(
			array(
				'id'			=> 1,
				'name'			=> __( 'Default', 'order-tracking' ),
				'subject'		=> __( 'Order Status Update', 'order-tracking' ),
				'message'		=> __( 'Hello [order-name], You have an update for your order [order-number]!', 'order-tracking' )
			)
		);

		$this->set_setting( 'email-messages', json_encode( $emails ) );

		$statuses = array(
			array(
				'status'		=> __( 'Pending Payment', 'order-tracking' ),
				'percentage'	=> '25',
				'email'			=> 1,
				'internal'		=> 'no',
			),
			array(
				'status'		=> __( 'Processing', 'order-tracking' ),
				'percentage'	=> '50',
				'email'			=> 1,
				'internal'		=> 'no',
			),
			array(
				'status'		=> __( 'On Hold', 'order-tracking' ),
				'percentage'	=> '50',
				'email'			=> 1,
				'internal'		=> 'no',
			),
			array(
				'status'		=> __( 'Completed', 'order-tracking' ),
				'percentage'	=> '100',
				'email'			=> 1,
				'internal'		=> 'no',
			),
			array(
				'status'		=> __( 'Cancelled', 'order-tracking' ),
				'percentage'	=> '0',
				'email'			=> 1,
				'internal'		=> 'no',
			),
			array(
				'status'		=> __( 'Refunded', 'order-tracking' ),
				'percentage'	=> '0',
				'email'			=> 1,
				'internal'		=> 'no',
			),
			array(
				'status'		=> __( 'Failed', 'order-tracking' ),
				'percentage'	=> '0',
				'email'			=> 1,
				'internal'		=> 'no',
			),
		);


		$this->set_setting( 'statuses', json_encode( $statuses ) );

		$this->save_settings();
	}

	/**
	 * Load all custom fields 
	 * @since 3.0.0
	 */
	public function get_custom_fields() {
		
		$custom_fields = is_array( get_option( 'ewd-otp-custom-fields' ) ) ? get_option( 'ewd-otp-custom-fields' ) : array();

		return $custom_fields;
	}

	/**
	 * Load the custom fields related to orders
	 * @since 3.0.0
	 */
	public function get_order_custom_fields() {
		
		$custom_fields = is_array( get_option( 'ewd-otp-custom-fields' ) ) ? get_option( 'ewd-otp-custom-fields' ) : array();

		$return_fields = array();

		foreach ( $custom_fields as $custom_field ){

			if ( $custom_field->function == 'orders' ) { $return_fields[] = $custom_field; }
		}

		return $return_fields;
	}

	/**
	 * Load the custom fields related to customers
	 * @since 3.0.0
	 */
	public function get_customer_custom_fields() {
		
		$custom_fields = is_array( get_option( 'ewd-otp-custom-fields' ) ) ? get_option( 'ewd-otp-custom-fields' ) : array();

		$return_fields = array();

		foreach ( $custom_fields as $custom_field ){

			if ( $custom_field->function == 'customers' ) { $return_fields[] = $custom_field; }
		}

		return $return_fields;
	}

	/**
	 * Load the custom fields related to sales reps
	 * @since 3.0.0
	 */
	public function get_sales_rep_custom_fields() {
		
		$custom_fields = is_array( get_option( 'ewd-otp-custom-fields' ) ) ? get_option( 'ewd-otp-custom-fields' ) : array();

		$return_fields = array();

		foreach ( $custom_fields as $custom_field ){

			if ( $custom_field->function == 'sales_reps' ) { $return_fields[] = $custom_field; }
		}

		return $return_fields;
	}
}
} // endif;
