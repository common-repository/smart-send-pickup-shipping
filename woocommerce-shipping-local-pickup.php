<?php	
	/*
	Plugin Name: Smart Send Shipping
	Plugin URI: http://smartsend.dk/integrationer/woocommerce
	Description: Table rate shipping methods with Post Danmark, GLS, SwipBox and Bring pickup points. Listed in a dropdown sorted by distance from shipping adress.
	Author: Smart Send ApS
	Author URI: http://www.smartsend.dk
	Version: 1.0.5

	Copyright: (c) 2014 Smart Send ApS (email : kontakt@smartsend.dk)
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
	
	This module and all files are subject to the GNU General Public License v3.0
	that is bundled with this package in the file license.txt.
	It is also available through the world-wide-web at this URL:
	http://www.gnu.org/licenses/gpl-3.0.html
	If you did not receive a copy of the license and are unable to
	obtain it through the world-wide-web, please send an email
	to license@smartsend.dk so we can send you a copy immediately.

	DISCLAIMER
	Do not edit or add to this file if you wish to upgrade the plugin to newer
	versions in the future. If you wish to customize the plugin for your
	needs please refer to http://www.smartsend.dk
	*/
	 
	/**
	 * Check if WooCommerce is active
	 */
	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	 
		function Smartsend_Shipping_SwipBox() {
			if ( ! class_exists( 'WC_Smartsend_Shipping_SwipBox' ) ) {
				class WC_Smartsend_Shipping_SwipBox extends WC_Shipping_Method {
					/**
					 * Constructor for your shipping class
					 *
					 * @access public
					 * @return void
					 */
					public $PrimaryClass ;
					public $SwipBox_GUID = '';
					public $SwipBox_NOR = '';
					
					public function __construct() {
						$this->id                 = 'SwipBox'; // Id for your shipping method. Should be uunique.
						$this->method_title       = __( 'SwipBox' );  
						$this->method_description = __( 'SwipBox pakkestationer.' ); 				
						$this->table_rate_option    = 'SwipBox_table_rate';
						$this->PrimaryClass = new Smartsend_Shipping_PrimaryClass();
						$this->init();
					}
	 
					/**
					 * Init your settings
					 *
					 * @access public
					 * @return void
					 */
					function init() {
	
						// Load the settings.
						$this->init_form_fields();
						$this->init_settings();
			
						// Define user set variables
					
					
						$this->shipping_description		= $this->get_option( 'shipping_description' );
						$this->enabled		= $this->get_option( 'enabled' );
						$this->title 		= $this->get_option( 'title' );
						//$this->cost_per_order = $this->get_option( 'cost_per_order' );
						$this->min_amount 	= $this->get_option( 'min_amount', 0 );
						$this->availability = 'specific';//$this->get_option( 'availability' );
						$this->countries 	= array('DK');//$this->get_option( 'countries' );
						$this->requires		= $this->get_option( 'requires' );
						$this->SwipBox_GUID = $this->get_option( 'GUID' );
						$this->SwipBox_NOR  = $this->get_option( 'NOR' );
						$this->apply_when 	= $this->get_option( 'apply_when' );
						$this->greatMax 	= $this->get_option( 'greatMax' );
						$this->type         = $this->get_option( 'type' );
						$this->tax_status   = $this->get_option( 'tax_status' );
						$this->min_order    = $this->get_option( 'min_order' );
						$this->max_order    = $this->get_option( 'max_order' );
						$this->shipping_rate= $this->get_option( 'shipping_rate' );
					
						// Actions
						add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
						add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_table_rates' ) );
				
						// Load Table rates
						$this->get_table_rates();
					}
					
					
					/**
					 * Initialise Gateway Settings Form Fields
					 */
					function init_form_fields() {
						$this->form_fields = array(
							'enabled' => array(
								'title' 		=> __( 'Enable/Disable', 'woocommerce' ),
								'type' 			=> 'checkbox',
								'label' 		=> __( 'Enable this shipping method', 'woocommerce' ),
								'default' 		=> 'yes'
							),
							'title' => array(
								'title' 		=> __( 'Title', 'woocommerce' ),
								'type' 			=> 'text',
								'description' 	=> __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
								'default'		=> __( 'SwipBox', 'woocommerce' ),
								'desc_tip'		=> true,
							),
							'shipping_description' => array(
								'title' 		=> __( 'Shipping Description', 'woocommerce' ),
								'type' 			=> 'text',
								'description' 	=> __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
								'default'		=> __( 'SwipBox  Frontend description', 'woocommerce' ),
								'desc_tip'		=> true,
								),
							'GUID' => array(
								'title' 		=> __( 'GUID', 'woocommerce' ),
								'type' 			=> 'text',
								'description' 	=> __( 'You may set your own GUID or leave empty', 'woocommerce' ),
								'default'		=> __( '', 'woocommerce' ),
								'desc_tip'		=> true,
							),
							'NOR' => array(
								'title' 		=> __( 'Number Of Results', 'woocommerce' ),
								'type' 			=> 'text',
								'description' 	=> __( 'This controls the number of store pickup locations in dropbox.', 'woocommerce' ),
								'default'		=> __( '', 'woocommerce' ),
								'desc_tip'		=> true,
							),
							'pickup_style' => array(
								'title'     => __( 'Pickup point dropdown style', 'woocommerce' ),
								'type'      => 'select',
								'default'   => '4',
								'options'   => array(
									'1' => __( '#NAME, #STREET', 'woocommerce' ),
									'2'    => __( '#NAME, #STREET, #ZIP', 'woocommerce' ),
									'3'    => __( '#NAME, #STREET, #CITY', 'woocommerce' ),
									'4'    => __( '#NAME, #STREET, #ZIP #CITY', 'woocommerce' ),
								),
							),
							'apply_when' => array(
								'title'     => __( 'Calculate Discounts?', RPTR_CORE_TEXT_DOMAIN ),
								'type'      => 'select',
								'default'   => 'before',
								'description'  => __( 'This controls if the shipping is calculated before any applied discounts or after they are applied.', RPTR_CORE_TEXT_DOMAIN ),
								'desc_tip'     => true,
								'options'   => array(
									'before' => __( 'Before Discount', RPTR_CORE_TEXT_DOMAIN ),
									'after'    => __( 'After Discount', RPTR_CORE_TEXT_DOMAIN ),
								),
							),
							'greatMax' => array(
								'title'     => __( 'Greater than Max', RPTR_CORE_TEXT_DOMAIN ),
								'description'  => __( 'This will determine how to handle values over the largest max value in the tables.', RPTR_CORE_TEXT_DOMAIN ),
								'desc_tip'     => true,
								'type'      => 'select',
								'default'   => 'maxship',
								'options'   => array(
									'maxship'    => __( 'Use Max Shipping', RPTR_CORE_TEXT_DOMAIN ),
									'ignore' 	=> __( 'Ignore Value', RPTR_CORE_TEXT_DOMAIN ),
						
								),
							),
							'tax_status' => array(
								'title'     => __( 'Tax Status', RPTR_CORE_TEXT_DOMAIN ),
								'type'      => 'select',
								'default'   => 'taxable',
								'options'   => array(
									'taxable' => __( 'Taxable', RPTR_CORE_TEXT_DOMAIN ),
									'none'    => __( 'None', RPTR_CORE_TEXT_DOMAIN ),
								),
							),
							'domestic_shipping_table' => array(
								'type'      => 'shipping_table'
								),
							);
						
					} // End init_form_fields()
	 
					/**
					 * calculate_shipping function.
					 *
					 * @access public
					 * @param mixed $package
					 * @return void
					 */
					function calculate_shipping( $package = array() ) {
							$this->PrimaryClass->calculate_shipping($package = array(),$this);
					}
	
							/**
					 * validate_additional_costs_field function.
					 *
					 * @access public
					 * @param mixed   $key
					 * @return void
					 */
					function validate_shipping_table_field( $key ) {
						return false;
					}			
					
					function generate_shipping_table_html() {
						return $this->PrimaryClass->generate_shipping_table_html($this);
					}
		
					/**
					 * process_table_rates function.
					 *
					 * @access public
					 * @return void
					 */
					function process_table_rates() {
						$this->PrimaryClass->process_table_rates($this);
					}

					/**
					 * save_default_costs function.
					 *
					 * @access public
					 * @param mixed   $values
					 * @return void
					 */
					function save_default_costs( $fields ) {
						return $this->PrimaryClass->save_default_costs($fields);
					}

					/**
					 * get_table_rates function.
					 *
					 * @access public
					 * @return void
					 */
					function get_table_rates() {
						$this->table_rates = array_filter( (array) get_option( $this->table_rate_option ) );
						if(empty($this->table_rates))
						$this->table_rates[] = Array ('minO' =>'1' ,'maxO' =>'100000' ,'shippingO' => 7.00 );
					}

				}
			}
		}
	
		add_action( 'woocommerce_shipping_init', 'Smartsend_Shipping_SwipBox' );
	 
		
		function Smartsend_Shipping_add_SwipBox( $methods ) {
			$methods[] = 'WC_Smartsend_Shipping_SwipBox';
			return $methods;
		}
	 
		add_filter( 'woocommerce_shipping_methods', 'Smartsend_Shipping_add_SwipBox' );
		
		
	/************Start GLS method ******************************************/
		function Smartsend_Shipping_GLS() {
			if ( ! class_exists( 'WC_Smartsend_Shipping_GLS' ) ) {
				class WC_Smartsend_Shipping_GLS extends WC_Shipping_Method {
					public $GLS_NOR = '';
					public $PrimaryClass ;
					public function __construct() {
						$this->id                 = 'GLS'; 
						$this->method_title       = __( 'GLS' );  
						$this->method_description = __( 'GLS PakkeStation' ); 				
						$this->table_rate_option    = 'GLS_table_rate';
						$this->PrimaryClass = new Smartsend_Shipping_PrimaryClass();
						$this->init();
					}
	
					
					function init() {
						$this->init_form_fields();
						$this->init_settings();
		
						// Define user set variables
				
						$this->shipping_description		= $this->get_option( 'shipping_description' );
						$this->enabled		= $this->get_option( 'enabled' );
						$this->title 		= $this->get_option( 'title' );
						$this->availability = 'specific';
						$this->countries 	= array('DK');
						$this->requires		= $this->get_option( 'requires' );
						$this->GLS_NOR =  $this->get_option( 'NOR' );
						$this->apply_when 	= $this->get_option( 'apply_when' );
						$this->greatMax 	= $this->get_option( 'greatMax' );
						$this->type       = $this->get_option( 'type' );
						$this->tax_status   = $this->get_option( 'tax_status' );
						$this->min_order    = $this->get_option( 'min_order' );
						$this->max_order    = $this->get_option( 'max_order' );
						$this->shipping_rate  = $this->get_option( 'shipping_rate' );
				
						// Actions
						add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
						add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_table_rates' ) );
			
						// Load Table rates
						$this->get_table_rates();
					}
			
					function init_form_fields() {
						$this->form_fields = array(
							'enabled' => array(
								'title' 		=> __( 'Enable/Disable', 'woocommerce' ),
								'type' 			=> 'checkbox',
								'label' 		=> __( 'Enable this shipping method', 'woocommerce' ),
								'default' 		=> 'yes'
							),
							'title' => array(
								'title' 		=> __( 'Title', 'woocommerce' ),
								'type' 			=> 'text',
								'description' 	=> __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
								'default'		=> __( 'GLS', 'woocommerce' ),
								'desc_tip'		=> true,
							),
							'shipping_description' => array(
								'title' 		=> __( 'Shipping Description', 'woocommerce' ),
								'type' 			=> 'text',
								'description' 	=> __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
								'default'		=> __( 'GLS Frontend description', 'woocommerce' ),
								'desc_tip'		=> true,
								),
							'NOR' => array(
								'title' 		=> __( 'Number Of Results', 'woocommerce' ),
								'type' 			=> 'text',
								'description' 	=> __( 'This controls the number of store pickup locations in dropbox.', 'woocommerce' ),
								'default'		=> __( '', 'woocommerce' ),
								'desc_tip'		=> true,
							),
							'pickup_style' => array(
								'title'     => __( 'Pickup point dropdown style', 'woocommerce' ),
								'type'      => 'select',
								'default'   => '4',
								'options'   => array(
									'1' => __( '#NAME, #STREET', 'woocommerce' ),
									'2'    => __( '#NAME, #STREET, #ZIP', 'woocommerce' ),
									'3'    => __( '#NAME, #STREET, #CITY', 'woocommerce' ),
									'4'    => __( '#NAME, #STREET, #ZIP #CITY', 'woocommerce' ),
								),
							),
							'apply_when' => array(
								'title'     => __( 'Calculate Discounts?', RPTR_CORE_TEXT_DOMAIN ),
								'type'      => 'select',
								'default'   => 'before',
								'description'  => __( 'This controls if the shipping is calculated before any applied discounts or after they are applied.', RPTR_CORE_TEXT_DOMAIN ),
								'desc_tip'     => true,
								'options'   => array(
									'before' => __( 'Before Discount', RPTR_CORE_TEXT_DOMAIN ),
									'after'    => __( 'After Discount', RPTR_CORE_TEXT_DOMAIN ),
								),
							),
							'greatMax' => array(
								'title'     => __( 'Greater than Max', RPTR_CORE_TEXT_DOMAIN ),
								'description'  => __( 'This will determine how to handle values over the largest max value in the tables.', RPTR_CORE_TEXT_DOMAIN ),
								'desc_tip'     => true,
								'type'      => 'select',
								'default'   => 'maxship',
								'options'   => array(
									'maxship'    => __( 'Use Max Shipping', RPTR_CORE_TEXT_DOMAIN ),
									'ignore' 	=> __( 'Ignore Value', RPTR_CORE_TEXT_DOMAIN ),
					
								),
							),
							'tax_status' => array(
								'title'     => __( 'Tax Status', RPTR_CORE_TEXT_DOMAIN ),
								'type'      => 'select',
								'default'   => 'taxable',
								'options'   => array(
									'taxable' => __( 'Taxable', RPTR_CORE_TEXT_DOMAIN ),
									'none'    => __( 'None', RPTR_CORE_TEXT_DOMAIN ),
								),
							),
							'domestic_shipping_table' => array(
								'type'      => 'shipping_table'
								),
							);
					
					} // End init_form_fields()
	 
					function calculate_shipping( $package = array() ) {
						$this->PrimaryClass->calculate_shipping($package = array(),$this);
					}
	
					function validate_shipping_table_field( $key ) {
						return false;
					}			
					
					function generate_shipping_table_html() {
						return $this->PrimaryClass->generate_shipping_table_html($this);
					}
		
					function process_table_rates() {
						$this->PrimaryClass->process_table_rates($this);
					}

					function save_default_costs( $fields ) {
						return $this->PrimaryClass->save_default_costs($fields);
					}

					function get_table_rates() {
						$this->table_rates = array_filter( (array) get_option( $this->table_rate_option ) );
						if(empty($this->table_rates))
						$this->table_rates[] = Array ('minO' =>'1' ,'maxO' =>'100000' ,'shippingO' => 6.00 );
					}
		
				}
			}
		}
	
		add_action( 'woocommerce_shipping_init', 'Smartsend_Shipping_GLS' );
		
		function Smartsend_Shipping_add_GLS( $methods ) {
			$methods[] = 'WC_Smartsend_Shipping_GLS';
			return $methods;
		}
	 
		add_filter( 'woocommerce_shipping_methods', 'Smartsend_Shipping_add_GLS' );
		
	/********************* end gls method **********************************************************/
		
		
		
		
	/************Start PostDanmark method ******************************************/
		function Smartsend_Shipping_PostDanmark() {
			if ( ! class_exists( 'WC_Smartsend_Shipping_PostDanmark' ) ) {
				class WC_Smartsend_Shipping_PostDanmark extends WC_Shipping_Method {
					public $PostDanmark_GUID = '';
					public $PostDanmark_NOR = '';
					public $PrimaryClass ;
					public function __construct() {
						$this->id                 = 'PostDanmark'; 
						$this->method_title       = __( 'PostDanmark' );  
						$this->method_description = __( 'PostDanmark afhentningssted' ); 				
						$this->table_rate_option    = 'PostDanmark_table_rate';
						$this->PrimaryClass = new Smartsend_Shipping_PrimaryClass();
						$this->init();
					}
	
					
					function init() {
						$this->init_form_fields();
						$this->init_settings();
			
						// Define user set variables
					
						$this->shipping_description		= $this->get_option( 'shipping_description' );
						$this->enabled		= $this->get_option( 'enabled' );
						$this->title 		= $this->get_option( 'title' );
						$this->availability = 'specific';
						$this->countries 	= array('DK');
						$this->requires		= $this->get_option( 'requires' );
						$this->PostDanmark_GUID = $this->get_option('GUID');
						$this->PostDanmark_NOR = $this->get_option('NOR');
						$this->apply_when 	= $this->get_option( 'apply_when' );
						$this->greatMax 	= $this->get_option( 'greatMax' );
						$this->type       = $this->get_option( 'type' );
						$this->tax_status   = $this->get_option( 'tax_status' );
						$this->min_order    = $this->get_option( 'min_order' );
						$this->max_order    = $this->get_option( 'max_order' );
						$this->shipping_rate  = $this->get_option( 'shipping_rate' );
					
						// Actions
						add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
						add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_table_rates' ) );
				
						// Load Table rates
						$this->get_table_rates();
					}
				
					function init_form_fields() {
						$this->form_fields = array(
							'enabled' => array(
								'title' 		=> __( 'Enable/Disable', 'woocommerce' ),
								'type' 			=> 'checkbox',
								'label' 		=> __( 'Enable this shipping method', 'woocommerce' ),
								'default' 		=> 'yes'
							),
							'title' => array(
								'title' 		=> __( 'Title', 'woocommerce' ),
								'type' 			=> 'text',
								'description' 	=> __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
								'default'		=> __( 'PostDanmark', 'woocommerce' ),
								'desc_tip'		=> true,
							),
							'shipping_description' => array(
								'title' 		=> __( 'Shipping Description', 'woocommerce' ),
								'type' 			=> 'text',
								'description' 	=> __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
								'default'		=> __( 'PostDanmark Frontend description', 'woocommerce' ),
								'desc_tip'		=> true,
							),
							'GUID' => array(
								'title' 		=> __( 'GUID', 'woocommerce' ),
								'type' 			=> 'text',
								'description' 	=> __( 'You may set your own GUID or leave empty', 'woocommerce' ),
								'default'		=> __( '', 'woocommerce' ),
								'desc_tip'		=> true,
							),
							'NOR' => array(
								'title' 		=> __( 'Number Of Results', 'woocommerce' ),
								'type' 			=> 'text',
								'description' 	=> __( 'This controls the number of store pickup locations in dropbox.', 'woocommerce' ),
								'default'		=> __( '', 'woocommerce' ),
								'desc_tip'		=> true,
							),
							'pickup_style' => array(
								'title'     => __( 'Pickup point dropdown style', 'woocommerce' ),
								'type'      => 'select',
								'default'   => '4',
								'options'   => array(
									'1' => __( '#NAME, #STREET', 'woocommerce' ),
									'2'    => __( '#NAME, #STREET, #ZIP', 'woocommerce' ),
									'3'    => __( '#NAME, #STREET, #CITY', 'woocommerce' ),
									'4'    => __( '#NAME, #STREET, #ZIP #CITY', 'woocommerce' ),
								),
							),
							'apply_when' => array(
								'title'     => __( 'Calculate Discounts?', RPTR_CORE_TEXT_DOMAIN ),
								'type'      => 'select',
								'default'   => 'before',
								'description'  => __( 'This controls if the shipping is calculated before any applied discounts or after they are applied.', RPTR_CORE_TEXT_DOMAIN ),
								'desc_tip'     => true,
								'options'   => array(
									'before' => __( 'Before Discount', RPTR_CORE_TEXT_DOMAIN ),
									'after'    => __( 'After Discount', RPTR_CORE_TEXT_DOMAIN ),
								),
							),
							'greatMax' => array(
								'title'     => __( 'Greater than Max', RPTR_CORE_TEXT_DOMAIN ),
								'description'  => __( 'This will determine how to handle values over the largest max value in the tables.', RPTR_CORE_TEXT_DOMAIN ),
								'desc_tip'     => true,
								'type'      => 'select',
								'default'   => 'maxship',
								'options'   => array(
									'maxship'    => __( 'Use Max Shipping', RPTR_CORE_TEXT_DOMAIN ),
									'ignore' 	=> __( 'Ignore Value', RPTR_CORE_TEXT_DOMAIN ),
						
								),
							),
							'tax_status' => array(
								'title'     => __( 'Tax Status', RPTR_CORE_TEXT_DOMAIN ),
								'type'      => 'select',
								'default'   => 'taxable',
								'options'   => array(
									'taxable' => __( 'Taxable', RPTR_CORE_TEXT_DOMAIN ),
									'none'    => __( 'None', RPTR_CORE_TEXT_DOMAIN ),
								),
							),
							'domestic_shipping_table' => array(
								'type'      => 'shipping_table'
								),
						);
						
					} // End init_form_fields()
	 
					function calculate_shipping( $package = array() ) {
						$this->PrimaryClass->calculate_shipping($package = array(),$this);
					}
	
					function validate_shipping_table_field( $key ) {
						return false;
					}			
					
					function generate_shipping_table_html() {
						return $this->PrimaryClass->generate_shipping_table_html($this);
					}
		
					function process_table_rates() {
						$this->PrimaryClass->process_table_rates($this);
					}

					function save_default_costs( $fields ) {
						return $this->PrimaryClass->save_default_costs($fields);
					}

					function get_table_rates() {
						$this->table_rates = array_filter( (array) get_option( $this->table_rate_option ) );
						if(empty($this->table_rates))
						$this->table_rates[] = Array ('minO' =>'1' ,'maxO' =>'100000' ,'shippingO' => 5.00 );
					}
		
				}
			}
		}
	
		add_action( 'woocommerce_shipping_init', 'Smartsend_Shipping_PostDanmark' );
		
		function Smartsend_Shipping_add_PostDanmark( $methods ) {
			$methods[] = 'WC_Smartsend_Shipping_PostDanmark';
			return $methods;
		}
	 
		add_filter( 'woocommerce_shipping_methods', 'Smartsend_Shipping_add_PostDanmark' );
		
	/********************* end PostDanmark method **********************************************************/
	
	
	/************Start Bring method ******************************************/
		function Smartsend_Shipping_Bring() {
			if ( ! class_exists( 'WC_Smartsend_Shipping_Bring' ) ) {
				class WC_Smartsend_Shipping_Bring extends WC_Shipping_Method {
					public $bring_NOR = '';
					public $PrimaryClass ;
					public function __construct() {
						$this->id                 = 'Bring'; 
						$this->method_title       = __( 'Bring' );  
						$this->method_description = __( 'Bring Hente Selv' ); 				
						$this->table_rate_option    = 'Bring_table_rate';
						$this->PrimaryClass = new Smartsend_Shipping_PrimaryClass(); 
						$this->init();
					}
	
					
					function init() {
						$this->init_form_fields();
						$this->init_settings();
	
						// Define user set variables
			
						$this->shipping_description		= $this->get_option( 'shipping_description' );
						$this->enabled		= $this->get_option( 'enabled' );
						$this->title 		= $this->get_option( 'title' );
						$this->availability = 'specific';
						$this->countries 	= array('DK');
						$this->requires		= $this->get_option( 'requires' );
						$this->bring_NOR    = $this->get_option( 'NOR' );
						$this->apply_when 	= $this->get_option( 'apply_when' );
						$this->greatMax 	= $this->get_option( 'greatMax' );
						$this->type       = $this->get_option( 'type' );
						$this->tax_status   = $this->get_option( 'tax_status' );
						$this->min_order    = $this->get_option( 'min_order' );
						$this->max_order    = $this->get_option( 'max_order' );
						$this->shipping_rate  = $this->get_option( 'shipping_rate' );
			
						// Actions
						add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
						add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_table_rates' ) );
		
						// Load Table rates
						$this->get_table_rates();
					}
				
					function init_form_fields() {
						$this->form_fields = array(
							'enabled' => array(
								'title' 		=> __( 'Enable/Disable', 'woocommerce' ),
								'type' 			=> 'checkbox',
								'label' 		=> __( 'Enable this shipping method', 'woocommerce' ),
								'default' 		=> 'yes'
							),
							'title' => array(
								'title' 		=> __( 'Title', 'woocommerce' ),
								'type' 			=> 'text',
								'description' 	=> __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
								'default'		=> __( 'Bring', 'woocommerce' ),
								'desc_tip'		=> true,
							),
							'shipping_description' => array(
								'title' 		=> __( 'Shipping Description', 'woocommerce' ),
								'type' 			=> 'text',
								'description' 	=> __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
								'default'		=> __( 'Bring Frontend description', 'woocommerce' ),
								'desc_tip'		=> true,
							),
							'NOR' => array(
								'title' 		=> __( 'Number Of Results', 'woocommerce' ),
								'type' 			=> 'text',
								'description' 	=> __( 'This controls the number of store pickup locations in dropbox.', 'woocommerce' ),
								'default'		=> __( '', 'woocommerce' ),
								'desc_tip'		=> true,
							),
							'pickup_style' => array(
								'title'     => __( 'Pickup point dropdown style', 'woocommerce' ),
								'type'      => 'select',
								'default'   => '4',
								'options'   => array(
									'1' => __( '#NAME, #STREET', 'woocommerce' ),
									'2'    => __( '#NAME, #STREET, #ZIP', 'woocommerce' ),
									'3'    => __( '#NAME, #STREET, #CITY', 'woocommerce' ),
									'4'    => __( '#NAME, #STREET, #ZIP #CITY', 'woocommerce' ),
								),
							),
							'apply_when' => array(
								'title'     => __( 'Calculate Discounts?', RPTR_CORE_TEXT_DOMAIN ),
								'type'      => 'select',
								'default'   => 'before',
								'description'  => __( 'This controls if the shipping is calculated before any applied discounts or after they are applied.', RPTR_CORE_TEXT_DOMAIN ),
								'desc_tip'     => true,
								'options'   => array(
									'before' => __( 'Before Discount', RPTR_CORE_TEXT_DOMAIN ),
									'after'    => __( 'After Discount', RPTR_CORE_TEXT_DOMAIN ),
								),
							),
							'greatMax' => array(
								'title'     => __( 'Greater than Max', RPTR_CORE_TEXT_DOMAIN ),
								'description'  => __( 'This will determine how to handle values over the largest max value in the tables.', RPTR_CORE_TEXT_DOMAIN ),
								'desc_tip'     => true,
								'type'      => 'select',
								'default'   => 'maxship',
								'options'   => array(
									'maxship'    => __( 'Use Max Shipping', RPTR_CORE_TEXT_DOMAIN ),
									'ignore' 	=> __( 'Ignore Value', RPTR_CORE_TEXT_DOMAIN ),
					
								),
							),
							'tax_status' => array(
								'title'     => __( 'Tax Status', RPTR_CORE_TEXT_DOMAIN ),
								'type'      => 'select',
								'default'   => 'taxable',
								'options'   => array(
									'taxable' => __( 'Taxable', RPTR_CORE_TEXT_DOMAIN ),
									'none'    => __( 'None', RPTR_CORE_TEXT_DOMAIN ),
								),
							),
							'domestic_shipping_table' => array(
								'type'      => 'shipping_table'
								),
			
							);
					
					} // End init_form_fields()
 
					function calculate_shipping( $package = array() ) {
						$this->PrimaryClass->calculate_shipping($package = array(),$this);
					}

					function validate_shipping_table_field( $key ) {
						return false;
					}			
				
					function generate_shipping_table_html() {
						return $this->PrimaryClass->generate_shipping_table_html($this);
					}
	
					function process_table_rates() {
						$this->PrimaryClass->process_table_rates($this);
					}

					function save_default_costs( $fields ) {
						return $this->PrimaryClass->save_default_costs($fields);
					}

					function get_table_rates() {
						$this->table_rates = array_filter( (array) get_option( $this->table_rate_option ) );
						if(empty($this->table_rates))
						$this->table_rates[] = Array ('minO' =>'1' ,'maxO' =>'100000' ,'shippingO' => 8.00 );
					}
	
				}
			}
		}
	
		add_action( 'woocommerce_shipping_init', 'Smartsend_Shipping_Bring' );
		
		function Smartsend_Shipping_add_Bring( $methods ) {
			$methods[] = 'WC_Smartsend_Shipping_Bring';
			return $methods;
		}
	 
		add_filter( 'woocommerce_shipping_methods', 'Smartsend_Shipping_add_Bring' );
		
	/********************* end Bring method **********************************************************/
	
		
	/************Start All pickup Points method ******************************************/
		function Smartsend_Shipping_PickupPoints() {
			if ( ! class_exists( 'WC_Smartsend_Shipping_PickupPoints' ) ) {
				class WC_Smartsend_Shipping_PickupPoints extends WC_Shipping_Method {
					public $PickupPoints_NOR = '';
					public $PrimaryClass ;
					public function __construct() {
						$this->id                 = 'PickupPoints'; 
						$this->method_title       = __( 'PickupPoints' );  
						$this->method_description = __( 'Closest pickup point' ); 				
						$this->table_rate_option    = 'PickupPoints_table_rate';
						$this->PrimaryClass = new Smartsend_Shipping_PrimaryClass(); 
						$this->init();
					}
	
					
					function init() {
						$this->init_form_fields();
						$this->init_settings();
	
						// Define user set variables
			
						$this->shipping_description		= $this->get_option( 'shipping_description' );
						$this->enabled		= $this->get_option( 'enabled' );
						$this->title 		= $this->get_option( 'title' );
						$this->availability = 'specific';
						$this->countries 	= array('DK');
						$this->requires		= $this->get_option( 'requires' );
						$this->PickupPoints_NOR    = $this->get_option( 'NOR' );
						$this->apply_when 	= $this->get_option( 'apply_when' );
						$this->greatMax 	= $this->get_option( 'greatMax' );
						$this->type       = $this->get_option( 'type' );
						$this->tax_status   = $this->get_option( 'tax_status' );
						$this->min_order    = $this->get_option( 'min_order' );
						$this->max_order    = $this->get_option( 'max_order' );
						$this->shipping_rate  = $this->get_option( 'shipping_rate' );
						$this->active_pickup_PostDanmark  = $this->get_option( 'active_pickup_PostDanmark' );
						$this->active_pickup_SwipBox  = $this->get_option( 'active_pickup_SwipBox' );
						$this->active_pickup_Bring  = $this->get_option( 'active_pickup_Bring' );
			
						// Actions
						add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
						add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_table_rates' ) );
		
						// Load Table rates
						$this->get_table_rates();
					}
				
					function init_form_fields() {
						$this->form_fields = array(
							'enabled' => array(
								'title' 		=> __( 'Enable/Disable', 'woocommerce' ),
								'type' 			=> 'checkbox',
								'label' 		=> __( 'Enable this shipping method', 'woocommerce' ),
								'default' 		=> 'yes'
							),
							'active_pickup_PostDanmark' => array(
								'title'         => __( 'Activate/Deactivate', 'woocommerce' ),
								'label'          => __( 'Add Post Danmark pickup points', 'woocommerce' ),
								'default'       => 'yes',
								'type'          => 'checkbox',
								'checkboxgroup' => 'start1'
							),
							'active_pickup_SwipBox' => array(
								'label'          => __( 'Add SwipBox pickup points', 'woocommerce' ),
								'default'       => 'yes',
								'type'          => 'checkbox',
								'checkboxgroup' => 'start1'
							),
							'active_pickup_Bring' => array(
								'label'          => __( 'Add Bring pickup points', 'woocommerce' ),
								'default'       => 'yes',
								'type'          => 'checkbox',
								'checkboxgroup' => 'start1'
							),
							'title' => array(
								'title' 		=> __( 'Title', 'woocommerce' ),
								'type' 			=> 'text',
								'description' 	=> __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
								'default'		=> __( 'PickupPoints', 'woocommerce' ),
								'desc_tip'		=> true,
							),
							'shipping_description' => array(
								'title' 		=> __( 'Shipping Description', 'woocommerce' ),
								'type' 			=> 'text',
								'description' 	=> __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
								'default'		=> __( 'PickupPoints Frontend description', 'woocommerce' ),
								'desc_tip'		=> true,
							),
							'NOR' => array(
								'title' 		=> __( 'Number Of Results', 'woocommerce' ),
								'type' 			=> 'text',
								'description' 	=> __( 'This controls the number of store pickup locations in dropbox.', 'woocommerce' ),
								'default'		=> __( '', 'woocommerce' ),
								'desc_tip'		=> true,
							),
							'pickup_style' => array(
								'title'     => __( 'Pickup point dropdown style', 'woocommerce' ),
								'type'      => 'select',
								'default'   => '5',
								'options'   => array(
									'1' => __( '#NAME, #STREET', 'woocommerce' ),
									'2'    => __( '#NAME, #STREET, #ZIP', 'woocommerce' ),
									'3'    => __( '#NAME, #STREET, #CITY', 'woocommerce' ),
									'4'    => __( '#NAME, #STREET, #ZIP #CITY', 'woocommerce' ),
									'5'    => __( '#NAME, #STREET, #ZIP #CITY (#type)', 'woocommerce' ),
								),
							),
							'apply_when' => array(
								'title'     => __( 'Calculate Discounts?', RPTR_CORE_TEXT_DOMAIN ),
								'type'      => 'select',
								'default'   => 'before',
								'description'  => __( 'This controls if the shipping is calculated before any applied discounts or after they are applied.', RPTR_CORE_TEXT_DOMAIN ),
								'desc_tip'     => true,
								'options'   => array(
									'before' => __( 'Before Discount', RPTR_CORE_TEXT_DOMAIN ),
									'after'    => __( 'After Discount', RPTR_CORE_TEXT_DOMAIN ),
								),
							),
							'greatMax' => array(
								'title'     => __( 'Greater than Max', RPTR_CORE_TEXT_DOMAIN ),
								'description'  => __( 'This will determine how to handle values over the largest max value in the tables.', RPTR_CORE_TEXT_DOMAIN ),
								'desc_tip'     => true,
								'type'      => 'select',
								'default'   => 'maxship',
								'options'   => array(
									'maxship'    => __( 'Use Max Shipping', RPTR_CORE_TEXT_DOMAIN ),
									'ignore' 	=> __( 'Ignore Value', RPTR_CORE_TEXT_DOMAIN ),
					
								),
							),
							'tax_status' => array(
								'title'     => __( 'Tax Status', RPTR_CORE_TEXT_DOMAIN ),
								'type'      => 'select',
								'default'   => 'taxable',
								'options'   => array(
									'taxable' => __( 'Taxable', RPTR_CORE_TEXT_DOMAIN ),
									'none'    => __( 'None', RPTR_CORE_TEXT_DOMAIN ),
								),
							),
							'domestic_shipping_table' => array(
								'type'      => 'shipping_table'
								),
			
							);
					
					} // End init_form_fields()
 
					function calculate_shipping( $package = array() ) {
						$this->PrimaryClass->calculate_shipping($package = array(),$this);
					}

					function validate_shipping_table_field( $key ) {
						return false;
					}			
				
					function generate_shipping_table_html() {
						return $this->PrimaryClass->generate_shipping_table_html($this);
					}
	
					function process_table_rates() {
						$this->PrimaryClass->process_table_rates($this);
					}

					function save_default_costs( $fields ) {
						return $this->PrimaryClass->save_default_costs($fields);
					}

					function get_table_rates() {
						$this->table_rates = array_filter( (array) get_option( $this->table_rate_option ) );
						if(empty($this->table_rates))
						$this->table_rates[] = Array ('minO' =>'1' ,'maxO' =>'1000' ,'shippingO' => 9.00 );
					}
	
				}
			}
		}
	
		add_action( 'woocommerce_shipping_init', 'Smartsend_Shipping_PickupPoints' );
		
		function Smartsend_Shipping_add_PickupPoints( $methods ) {
			$methods[] = 'WC_Smartsend_Shipping_PickupPoints';
			return $methods;
		}
	 
		add_filter( 'woocommerce_shipping_methods', 'Smartsend_Shipping_add_PickupPoints' );
		
	/********************* end Pickup Points method **********************************************************/
		
		function show_description($shipping_description) {
			echo "<p class='description_testship'>$shipping_description</p>";
		}
		
/*-----------------------------------------------------------------------------------------------------------------------
 * 					Add Store Pick up loaction on chechout page	
 *----------------------------------------------------------------------------------------------------------------------*/		
		
		$x = get_option( 'woocommerce_pickup_display_mode1', 1 );
		if($x==1)
		add_filter( 'woocommerce_review_order_before_payment' , 'Smartsend_Shipping_custom_store_pickup_field');
		else
		add_filter( 'woocommerce_review_order_after_cart_contents' , 'Smartsend_Shipping_custom_store_pickup_field');
	
		function Smartsend_Shipping_custom_store_pickup_field( $fields ) {
		
			if(!isset($_REQUEST['post_data'])) return false;
		
			parse_str($_REQUEST['post_data'],$request);
			$shipping_method = $request['shipping_method'][0];
		
			if(isset($request['ship_to_different_address']) && $request['ship_to_different_address']){
				$address_1 = $request['shipping_address_1'];
				$address_2 = $request['shipping_address_2'];
				$city = $request['shipping_city'];
				$zip = $request['shipping_postcode'];
			}else{
				$address_1 = $request['billing_address_1'];
				$address_2 = $request['billing_address_2'];
				$city = $request['billing_city'];
				$zip = $request['billing_postcode'];
			}
		
			$pickup_loc = '';
			$display_selectbox = false;
			if(!empty($shipping_method)){
				switch( $shipping_method ){
				
				CASE 'SwipBox': 
					$display_selectbox = true;
					$pickup_loc = Smartsend_Shipping_SwipBox_API($address_1,$address_2,$city,$zip);
				break;
				CASE 'GLS':
					$display_selectbox = true;
					$pickup_loc = Smartsend_Shipping_GLS_API($address_1,$address_2,$city,$zip);
				break;
				CASE 'PostDanmark': 
					$display_selectbox = true;
					$pickup_loc = Smartsend_Shipping_PostDanmark_API($address_1,$address_2,$city,$zip);
				break;
				CASE 'Bring': 
					$display_selectbox = true;
					$pickup_loc = Smartsend_Shipping_Bring_API($address_1,$address_2,$city,$zip);
				break;
				CASE 'PickupPoints':
					$display_selectbox = true;
					$unserialize_data = $pickup_loc = $pickup_all = array();
					$WC_PickupPoints  = new WC_Smartsend_Shipping_PickupPoints();
					$NOR = ($WC_PickupPoints->PickupPoints_NOR!= '') ? $WC_PickupPoints->PickupPoints_NOR : 10; 
						
					if($WC_PickupPoints->active_pickup_SwipBox == 'yes') $pickup_all[] = Smartsend_Shipping_SwipBox_API($address_1,$address_2,$city,$zip,$NOR);
					if($WC_PickupPoints->active_pickup_PostDanmark == 'yes') $pickup_all[] = Smartsend_Shipping_PostDanmark_API($address_1,$address_2,$city,$zip,$NOR);
					if($WC_PickupPoints->active_pickup_Bring == 'yes') $pickup_all[] = Smartsend_Shipping_Bring_API($address_1,$address_2,$city,$zip,$NOR);
					
					if(!empty($pickup_all) && is_array($pickup_all)){
						foreach ($pickup_all as $all_values){
							foreach($all_values as $picIndex => $picValue) {
								$unserialize_data[] = unserialize($picIndex);
						}
						}
						foreach ($unserialize_data as $key => $row) {
						    $mid[$key]  = $row['distance'];
						}
						if(is_array($mid)) {
							array_multisort($mid, SORT_ASC, $unserialize_data);
							$unserialize_data = array_slice($unserialize_data,0,$NOR);
						}
						
						if( isset($unserialize_data) && is_array($unserialize_data)){
							foreach( $unserialize_data as $unindex => $unvalue ) {
								$method_style = $WC_PickupPoints->get_option('pickup_style');
								$key_pair = array('id'=>$unvalue['id'], 'type'=>$unvalue['type'], 'method_style'=>$method_style, 'company'=>$unvalue['company'], 'street'=>$unvalue['street'], 'zip'=>$unvalue['zip'],'city'=>$unvalue['city'],'country'=>'DK');
								$pickup_loc[serialize($key_pair)] = Smartsend_Pickup_Point_Style($key_pair,$method_style);
							}
						}
					}
					
				break;
				}
			}
			//echo "<pre>";
			//print_r($unserialize_data);
			if($display_selectbox){
			?>
			<div class="selectstore"> <?php echo __(get_option('woocommerce_pickup_display_dropdown_legend', 'Vælg udleveringssted'),'woocommerce'); echo ' ('.$shipping_method.')'?>
				<select name="store_pickup" >
				<option value=""><?php echo __(get_option('woocommerce_pickup_display_dropdown_text', 'Klik og vælg udleveringssted'),'woocommerce')?></option>
					<?php foreach($pickup_loc as $picIndex => $picValue) {
						   
						?>
					<option value='<?php echo $picIndex?>'><?php echo $picValue?></option>
					<?php }?>
			</select>
			</div>
		<?php
			}
	
		}
		
/******************************** API FUNCTIONS *********************************************/
		function Smartsend_Shipping_SwipBox_API($address_1,$address_2,$city,$zip,$NOR=null){
			$pickup_loc = array();
			require_once('api/class.swipbox.php');
			$WC_SwipBox  = new WC_Smartsend_Shipping_SwipBox();
			$swipboxtest = ($WC_SwipBox->SwipBox_GUID!= '') ? false : true ;
		
			$swipbox = new Smartsend_Shipping_Swipbox($swipboxtest);
			$SWIPparameters = array(
				'address_1' 	=> $address_1, 		//Mandatory - alphanumeric(100)
				'address_2' 	=> $address_2, 					//Optional 	- alphanumeric(100)				
				'city' 			=> $city, 			//Mandatory - alphanumeric(100)
				'zip' 			=> $zip, 					//Mandatory - alphanumeric(6)
				'country' 		=> 'DK', 					//Mandatory - alphanumeric(100)
				'parcel_size' 	=> 1, 						//Mandatory - int - 1: small, 2: medium, 3:large, 99: oversized1, 100: oversized2
				'station_type'  => null, 					//Optional 	- int - 1: normal station, 2 overflow station, 101+ (used for searching specific sized oversize station)
				);
			if($WC_SwipBox->SwipBox_GUID!= '') $swipbox->_guid = $WC_SwipBox->SwipBox_GUID;
			
			if(!is_null($NOR)) 
			$swipbox->_amount = $NOR;
			else
			if($WC_SwipBox->SwipBox_NOR!= '') $swipbox->_amount = $WC_SwipBox->SwipBox_NOR;
		
			$swip = $swipbox->find_nearest($SWIPparameters) ;
			$swipbox_data = json_decode($swip['output'],true);
			$method_style = $WC_SwipBox->get_option('pickup_style');
			if( isset($swipbox_data) && is_array($swipbox_data) && isset($swipbox_data['stations']) ){
				foreach( $swipbox_data['stations'] as $value ) {
					$key_pair = array('id'=>$value['pick_up_id'], 'distance'=>(float)$value['distance'], 'type'=>'SwipBox', 'method_style'=>$method_style, 'company'=>$value['pick_name'], 'street'=>$value['pick_adr_1'], 'zip'=>$value['pick_zip'],'city'=>$value['pick_city'],'country'=>'DK');
					$pickup_loc[serialize($key_pair)] = Smartsend_Pickup_Point_Style($key_pair,$method_style);
				}
			}
			return $pickup_loc;
		}
		
		function Smartsend_Shipping_GLS_API($address_1,$address_2,$city,$zip,$NOR=null){
			$pickup_loc = array();
			require_once('api/class.Gls.php');
			$WC_GLS  = new WC_Smartsend_Shipping_GLS();
			$Gls = new Smartsend_Shipping_Gls;
			
			if(!is_null($NOR)) 
				$glsAmount = $NOR;
			else
				$glsAmount = ($WC_GLS->GLS_NOR!= '') ? $WC_GLS->GLS_NOR : 5;
				$GLS_data = $Gls->SearchNearestParcelShops($address_1,$zip,$glsAmount);
		
			$method_style = $WC_GLS->get_option('pickup_style');
			if( isset($GLS_data) && is_array($GLS_data)){
				foreach( $GLS_data as $value ) {
					$key_pair = array('id'=>$value->Number, 'type'=>'GLS', 'method_style'=>$method_style, 'company'=>$value->CompanyName, 'street'=>$value->Streetname, 'zip'=>$value->ZipCode,'city'=>$value->CityName,'country'=>'DK');
					$pickup_loc[serialize($key_pair)] = Smartsend_Pickup_Point_Style($key_pair,$method_style);
				}
			}
			return $pickup_loc;
		}
		
		function Smartsend_Shipping_PostDanmark_API($address_1,$address_2,$city,$zip,$NOR=null){
			$pickup_loc = array();
			require_once('api/class.postdanmark.php');
			$WC_PostDanmark  = new WC_Smartsend_Shipping_PostDanmark();
			$postest = ($WC_PostDanmark->PostDanmark_GUID!= '') ? false : true ;
		
			$Postdanmark = new Smartsend_Shipping_Postdanmark($postest);
			$POSTparameters = array(
				'streetName' 	=> $address_1.' '.$address_2, 	//Optional - string
				'streetNumber' 	=> null, 				//Optional 	- string		
				'city' 			=> $city, 		//Optional 	- string
				'postalCode' 	=> $zip, 				//Optional 	- string
				'countryCode' 	=> 'DK', 				//Mandatory - string(2) - ISO 3166-1
			);
		
			if($WC_PostDanmark->PostDanmark_GUID!= '') $Postdanmark->_consumerId = $WC_PostDanmark->PostDanmark_GUID;
			
			if(!is_null($NOR)) 
			$Postdanmark->_amount = $NOR;
			else
			if($WC_PostDanmark->PostDanmark_NOR!= '') $Postdanmark->_amount = $WC_PostDanmark->PostDanmark_NOR;
		
			$pdk = $Postdanmark->findNearestByAddress($POSTparameters);
			$Postdanmark_data = json_decode($pdk['output'],true);
			$method_style = $WC_PostDanmark->get_option('pickup_style');
			
			if( isset($Postdanmark_data) && is_array($Postdanmark_data) && isset($Postdanmark_data['servicePointInformationResponse']) && isset($Postdanmark_data['servicePointInformationResponse']['servicePoints']) && is_array($Postdanmark_data['servicePointInformationResponse']['servicePoints']) ){
				foreach( $Postdanmark_data['servicePointInformationResponse']['servicePoints'] as $value ) {
					$key_pair = array('id'=>$value['servicePointId'],'distance'=>$value['routeDistance']/1000, 'type'=>'PostDanmark', 'method_style'=>$WC_PostDanmark, 'company'=>$value['name'], 'street'=>$value['visitingAddress']['streetName'], 'zip'=>$value['visitingAddress']['postalCode'],'city'=>$value['visitingAddress']['city'],'country'=>'DK');
					$pickup_loc[serialize($key_pair)] = Smartsend_Pickup_Point_Style($key_pair,$method_style);
				}
			}
			return $pickup_loc;
		}
		
		function Smartsend_Shipping_Bring_API($address_1,$address_2,$city,$zip,$NOR=null){
			$pickup_loc = array();
			require_once('api/class.bring.php');
			$WC_Bring  = new WC_Smartsend_Shipping_Bring();
			$bringBox = new Smartsend_Shipping_Bring;
			
			if(!is_null($NOR)) 
			$bringBox->_responsenumber = $NOR;
			else
			if($WC_Bring->bring_NOR!= '') $bringBox->_responsenumber = $WC_Bring->bring_NOR;
			
			$bring = $bringBox->findByAddress('DK', $zip, urlencode($address_1.' '.$address_2), '');
			$bring_data = json_decode($bring["output"])->pickupPoint;
			$method_style = $WC_Bring->get_option('pickup_style');
			if( isset($bring_data) && is_array($bring_data)){
				foreach( $bring_data as $value ) {
					$key_pair = array('id'=>$value->id, 'distance'=>$value->distanceInKm, 'type'=>'Bring', 'method_style'=>$method_style, 'company'=>$value->name, 'street'=>$value->address, 'zip'=>$value->postalCode,'city'=>$value->city,'country'=>'DK');
					$pickup_loc[serialize($key_pair)] = Smartsend_Pickup_Point_Style($key_pair,$method_style);
				}
			}
			return $pickup_loc;
		}
		
		function Smartsend_Pickup_Point_Style($pickarray,$style_type){
			if(is_array($pickarray)){
				switch ($style_type){
					CASE '1':
						$dropdown = $pickarray['company'].', '.$pickarray['street']; 
					BREAK;
					CASE '2':
						$dropdown = $pickarray['company'].', '.$pickarray['street'].', '.$pickarray['zip']; 
					BREAK;
					CASE '3':
						$dropdown = $pickarray['company'].', '.$pickarray['street'].', '.$pickarray['city']; 
					BREAK;
					CASE '4':
						$dropdown = $pickarray['company'].', '.$pickarray['street'].', '.$pickarray['zip'].' '.$pickarray['city']; 
					BREAK;
					CASE '5':
						$dropdown = $pickarray['company'].', '.$pickarray['street'].', '.$pickarray['zip'].' '.$pickarray['city'].' ('.$pickarray['type'].')'; 
					BREAK;
				}
			}
			return $dropdown;
		}
	
/********************************END OF API FUNCTIONS *********************************************/		
		
	add_action( 'woocommerce_checkout_update_order_meta', 'Smartsend_Shipping_store_pickup_field_update_order_meta' );
		function Smartsend_Shipping_store_pickup_field_update_order_meta( $order_id ) {  
			if ( $_POST[ 'store_pickup' ] ){
				update_post_meta( $order_id, 'store_pickup',  $_POST[ 'store_pickup' ] );
			}
		}
	
		#Process the checkout and validate store location
		add_action('woocommerce_checkout_process', 'Smartsend_Shipping_pickup_checkout_field_process');
		function Smartsend_Shipping_pickup_checkout_field_process() {
			global $woocommerce;
			// Check if set, if its not set add an error. This one is only requite for companies
			if ( $_POST['billing_country'] == "DK" || $_POST['shipping_country'] == "DK" )
				if (isset($_POST['store_pickup']) && $_POST['store_pickup']=='') 
					$woocommerce->add_error( __(get_option('woocommerce_pickup_display_dropdown_error', 'Vælg venligst et udleveringssted på listen.'),'woocommerce') );
		}
	
		# Show selected pickup location in customer's myaccount section
		add_action( 'woocommerce_order_details_after_order_table', 'Smartsend_Shipping_display_store_order_details' );
		function Smartsend_Shipping_display_store_order_details($order  ) {
			$store_pickup = get_post_custom($order->id);
			$store_pickup = @unserialize($store_pickup['store_pickup'][0]);
			if(!is_array($store_pickup)) $store_pickup = unserialize($store_pickup);
		
			if(!empty($store_pickup)){
				echo '<br/>
				<b>'.__('Selected store location','woocommerce').':</b>
				<br/>';
				echo ' ID: ' . $store_pickup['id'] .'<br/>'.
				$store_pickup['company'] .'<br/>'.
				$store_pickup['street'] .'<br/>';
				if(isset($store_pickup['method_style'])){
					switch ($store_pickup['method_style']){
						CASE '2':
						 echo $store_pickup['zip'] ;
					    BREAK;
					    CASE '3':
						 echo $store_pickup['city'] ;
					    BREAK;
					    CASE '4':
						 echo $store_pickup['zip'] .' '. $store_pickup['city'] ;
					    BREAK;
					    CASE '5':
						 echo $store_pickup['zip'] .' '. $store_pickup['city']. ' ('.$store_pickup['type'].')' ;
					    BREAK;
					}
				}else
				{
					 echo $store_pickup['zip'] .' '. $store_pickup['city'] ;
				} 
				echo "<br/><br/>";
			}
		}
		
		# add selected pickup location info with emails
		add_action( 'woocommerce_email_after_order_table', 'Smartsend_Shipping_display_store_order_details' );

		# hide custom field data in admin orders section
		add_filter('is_protected_meta', 'Smartsend_Shipping_my_is_protected_meta_filter', 10, 2);
		function Smartsend_Shipping_my_is_protected_meta_filter($protected, $meta_key) {
			return $meta_key == 'store_pickup' ? true : $protected;
		}
	
		#Add a custom setting in shipping section
		add_filter( 'woocommerce_shipping_settings', 'Smartsend_Shipping_add_order_number_start_setting' );
		function Smartsend_Shipping_add_order_number_start_setting( $settings ) {
		  $updated_settings = array();
		  foreach ( $settings as $section ) {
			if ( isset( $section['id'] ) && 'woocommerce_ship_to_countries' == $section['id'] &&
			   isset( $section['type'] ) && 'select' == $section['type'] ) {
			  $updated_settings[] = array(
						'title'   => __( 'Store Location Display Mode', 'woocommerce' ),
						'desc'    => __( 'This controls display postion of store location dropdown on checkout page.', 'woocommerce' ),
						'id'      => 'woocommerce_pickup_display_mode1',
						'default' => '1',
						'type'    => 'radio',
						'options' => array(
							'0'     => __( 'Above the "Your Order" section on Checkout page', 'woocommerce' ),
							'1'      => __( 'Below the "Your Order" section on Checkout page', 'woocommerce' ),
						),
						'autoload'        => false,
						'desc_tip'        =>  true,
						'show_if_checked' => 'option',
					);
				
				$updated_settings[] = array(
						'title'   => __( 'Dropdown legend', 'woocommerce' ),
						'desc'    => __( 'This is the legend of the dropdown containing the pickup points.', 'woocommerce' ),
						'id'      => 'woocommerce_pickup_display_dropdown_legend',
						'default' => 'Vælg udleveringssted', //Choose Store Location
						'type'    => 'text',
						'desc_tip'        =>  true,
						'show_if_checked' => 'option',
					);
					
				$updated_settings[] = array(
						'title'   => __( 'Dropdown text', 'woocommerce' ),
						'desc'    => __( 'This is what will be shown in the first row of the dropdown containing the pickup points.', 'woocommerce' ),
						'id'      => 'woocommerce_pickup_display_dropdown_text',
						'default' => 'Klik og vælg udleveringssted', //Select pickup location
						'type'    => 'text',
						'desc_tip'        =>  true,
						'show_if_checked' => 'option',
					);
				$updated_settings[] = array(
						'title'   => __( 'Dropdown text', 'woocommerce' ),
						'desc'    => __( 'This is the error message shown if no pickup point is selected.', 'woocommerce' ),
						'id'      => 'woocommerce_pickup_display_dropdown_error',
						'default' => 'Vælg venligst et udleveringssted på listen.', //Please select the store pickup loaction!
						'type'    => 'text',
						'desc_tip'        =>  true,
						'show_if_checked' => 'option',
					);
			}
			$updated_settings[] = $section;
		  }
	
		  return $updated_settings;
		}
	
	
		# Show selected pickup location on the order edit page(woocommerce_admin_order_data_after_order_details)
		add_action( 'woocommerce_admin_order_data_after_billing_address', 'Smartsend_Shipping_my_custom_checkout_field_display_admin_order_meta', 10, 1 );
		function Smartsend_Shipping_my_custom_checkout_field_display_admin_order_meta($order){
			$store_pickup = get_post_custom($order->id);
			if(!empty($store_pickup ['store_pickup'][0])){
				$store_pickup = unserialize($store_pickup['store_pickup'][0]);
				if(!is_array($store_pickup)) $store_pickup = unserialize($store_pickup);
				
				$type = '';
				if(!empty($store_pickup['method_style']) && $store_pickup['method_style'] == 5 )
				$type = ' ('.$store_pickup['type'].')';
				
				echo '<p><strong>'.__('Selected store location','woocommerce').':</strong><br/> 
				ID: ' . $store_pickup['id'] .'<br/>'.
				 $store_pickup['company'] .'<br/>'.
				 $store_pickup['street'] .'<br/>'.
				 $store_pickup['zip'] .' '. $store_pickup['city'] . $type . 
				'</p>';
			}
		}
	
	
	
	}
	

/********************* Primary Class Start**********************************************************/	
class Smartsend_Shipping_PrimaryClass {
	
	public function calculate_shipping($package = array(),$x){
		global $woocommerce;

			$x->rate = array();

			$shipping_rates = get_option( $x->table_rate_option );
			if(empty($shipping_rates)) $shipping_rates = $x->table_rates;
			
			$totalPrice = $woocommerce->cart->cart_contents_total;

			$totalPrice = (float) $totalPrice;

			$virtualPrice = 0;
			$shipping_cost = 0;

			$discount_total = 0.00;

			foreach ( $woocommerce->cart->get_cart() as $item ) {
				if ( ! $item['data']->is_virtual() ){
					$shipping_cost += $item['data']->get_price() * $item['quantity'];
				} else {
					$virtualPrice += $item['data']->get_price() * $item['quantity'];
				}

			}

			if ( ! empty( $woocommerce->cart->applied_coupons ) ) {
				foreach ( $woocommerce->cart->applied_coupons as $key => $code ) {
					$coupon = new WC_Coupon( $code );

					$couponAmount = (float) $coupon->amount;

					switch ( $coupon->type ) {
						case "fixed_cart" :

							if ( $couponAmount > $totalPrice )
								$couponAmount = $totalPrice;

							$discount_total = (float) $discount_total - $couponAmount;
						break;

						case "percent" :
							$percent_discount = (float) round( ( $totalPrice * ( $couponAmount * 0.01 ) ) );

							if ( $percent_discount > $totalPrice )
								$percent_discount = $totalPrice;

							$discount_total = (float) $discount_total - $percent_discount;
						break;
					}
				}
			}
			
			if( $x->get_option( 'apply_when' ) == "after"  && !empty($discount_total) )
				$shipping_cost = $totalPrice + $discount_total;

			$price = (float) $shipping_cost; //Sets the Price that we will calculate the shipping
			$shipping_costs = -1;
			$theFirst = 0;

			$greatMax = $x->get_option( 'greatMax' );
				if(!empty($shipping_rates)){
				foreach ( $shipping_rates as $rates ) {
					if ( ( (float) $price < (float) $rates['minO'] )  && ( $theFirst == 0 ) ) {
						$theFirst = 1;
						break;
					}

					if ( ( (float) $price >= (float) $rates['minO']) && ( (float) $price <= (float) $rates['maxO'] ) ) {
						$shipping_costs = (float) $rates['shippingO'];
						break;
					}
					if( $greatMax == 'maxship' ) 
						$shipping_costs = (float) $rates['shippingO'];
				}
				}

			if ( $shipping_costs <> -1 ) {
				$rate = array(
					'id'        => $x->id,
					'label'     => $x->title,
					'cost'      => $shipping_costs,
					'calc_tax'  => 'per_order'
				);

				$x->add_rate( $rate );
			}
	}
	
	
	function process_table_rates($x) {
			// Save the rates
			$table_rate_minO  = array();
			$table_rate_maxO  = array();
			$table_rate_shippingO = array();
			$table_rates = array();

			if ( isset( $_POST[ $x->id . '_minO'] ) ) $table_rate_minO = array_map( 'woocommerce_clean', $_POST[ $x->id . '_minO'] );
			if ( isset( $_POST[ $x->id . '_maxO'] ) )  $table_rate_maxO  = array_map( 'woocommerce_clean', $_POST[ $x->id . '_maxO'] );
			if ( isset( $_POST[ $x->id . '_shippingO'] ) )   $table_rate_shippingO   = array_map( 'woocommerce_clean', $_POST[ $x->id . '_shippingO'] );

			// Get max key
			$values = $table_rate_shippingO;
			ksort( $values );
			$value = end( $values );
			$key = key( $values );

			for ( $i = 0; $i <= $key; $i++ ) {
				if ( isset( $table_rate_minO[ $i ] ) && isset( $table_rate_maxO[ $i ] ) && isset( $table_rate_shippingO[ $i ] ) ) {

					$table_rate_minO[$i] = @number_format( $table_rate_minO[$i], 2,  '.', '' );
					$table_rate_maxO[$i] = @number_format( $table_rate_maxO[$i], 2,  '.', '' );
					$table_rate_shippingO[$i] = number_format( $table_rate_shippingO[$i], 2,  '.', '' );

					if ( $table_rate_minO[$i] > $table_rate_maxO[$i] ) {   // Swap Min and Max Values
						$tempMin = $table_rate_minO[$i];
						$table_rate_minO[$i] = $table_rate_maxO[$i];
						$table_rate_maxO[$i] = $tempMin;
					}



					// Add to table rates array
					$table_rates[ $i ] = array(
						'minO'    => $table_rate_minO[ $i ],
						'maxO'    => $table_rate_maxO[ $i ],
						'shippingO' => $table_rate_shippingO[ $i ],
					);
				}
			}

			$orderby = "minO"; //change this to whatever key you want from the array

			$sortArray = array();

			foreach ( $table_rates as $the_rates ) {
				foreach ( $the_rates as $key=>$value ) {
					if ( !isset( $sortArray[$key] ) ) {
						$sortArray[$key] = array();
					}
					$sortArray[$key][] = $value;
				}
			}

			if( !empty($sortArray) )
				array_multisort( $sortArray[$orderby], SORT_ASC, $table_rates );

			update_option( $x->table_rate_option, $table_rates );

			$x->get_table_rates();
		}

		
		function save_default_costs( $fields ) {
			$default_minO = woocommerce_clean( $_POST['default_minO'] );
			$default_maxO  = woocommerce_clean( $_POST['default_maxO'] );
			$default_shippingO  = woocommerce_clean( $_POST['default_shippingO'] );

			$fields['minO'] = $default_minO;
			$fields['maxO']  = $default_maxO;
			$fields['shippingO']  = $default_shippingO;

			return $fields;
		}
		
		
					
	  function generate_shipping_table_html($x) {
			global $woocommerce;
			ob_start();
			?>
			<tr valign="top">
				<th scope="row" class="titledesc"><?php _e( 'Costs', RPTR_CORE_TEXT_DOMAIN ); ?>:</th>
				<td class="forminp" id="<?php echo $x->id; ?>_table_rates">
					<table class="shippingrows widefat" cellspacing="0">
						<thead>
							<tr>
								<th class="check-column"><input type="checkbox"></th>
								<th><?php _e( 'Min Price', RPTR_CORE_TEXT_DOMAIN ); ?> <a class="tips" data-tip="<?php _e( 'Min price for this shipping rate.', RPTR_CORE_TEXT_DOMAIN ); ?>">[?]</a></th>
								<th><?php _e( 'Max Price', RPTR_CORE_TEXT_DOMAIN ); ?> <a class="tips" data-tip="<?php _e( 'Max price for this shipping rate.', RPTR_CORE_TEXT_DOMAIN ); ?>">[?]</a></th>
								<th><?php _e( 'Shipping Fee', RPTR_CORE_TEXT_DOMAIN ); ?> <a class="tips" data-tip="<?php _e( 'Shipping price for this price range.', RPTR_CORE_TEXT_DOMAIN ); ?>">[?]</a></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th colspan="4"><a href="#" class="add button" style="margin-left: 24px"><?php _e( '+ Add Rate', RPTR_CORE_TEXT_DOMAIN ); ?></a> <a href="#" class="remove button"><?php _e( 'Delete selected rates', RPTR_CORE_TEXT_DOMAIN ); ?></a></th>
							</tr>
						</tfoot>
						<tbody class="table_rates">

						<?php
			$i = -1;
			if ( $x->table_rates ) {
				foreach ( $x->table_rates as $class => $rate ) {
					$i++;
					echo '<tr class="table_rate">
										<th class="check-column"><input type="checkbox" name="select" /></th>
										<td><input type="number" step="any" min="0" value="' . esc_attr( $rate['minO'] ) . '" name="' . esc_attr( $x->id .'_minO[' . $i . ']' ) . '" style="width: 90%" class="' . esc_attr( $x->id .'field[' . $i . ']' ) . '" placeholder="'.__( '0.00', RPTR_CORE_TEXT_DOMAIN ).'" size="4" /></td>
										<td><input type="number" step="any" min="0" value="' . esc_attr( $rate['maxO'] ) . '" name="' . esc_attr( $x->id .'_maxO[' . $i . ']' ) . '" style="width: 90%" class="' . esc_attr( $x->id .'field[' . $i . ']' ) . '" placeholder="'.__( '0.00', RPTR_CORE_TEXT_DOMAIN ).'" size="4" /></td>
										<td><input type="number" step="any" min="0" value="' . esc_attr( $rate['shippingO'] ) . '" name="' . esc_attr( $x->id .'_shippingO[' . $i . ']' ) . '" style="width: 90%" class="' . esc_attr( $x->id .'field[' . $i . ']' ) . '" placeholder="'.__( '0.00', RPTR_CORE_TEXT_DOMAIN ).'" size="4" /></td>
									</tr>';
				}
			}
			?>
						</tbody>
					</table>


					<script type="text/javascript">
						jQuery(function() {
							jQuery('#<?php echo $x->id; ?>_table_rates').on( 'click', 'a.add', function(){
								var size = jQuery('#<?php echo $x->id; ?>_table_rates tbody .table_rate').size();
								var previous = size - 1;
								jQuery('<tr class="table_rate">\
									<th class="check-column"><input type="checkbox" name="select" /></th>\
									<td><input type="number" step="any" min="0" name="<?php echo $x->id; ?>_minO[' + size + ']" style="width: 90%" class="<?php echo $x->id; ?>field[' + size + ']" placeholder="0.00" size="4" /></td>\
									<td><input type="number" step="any" min="0" name="<?php echo $x->id; ?>_maxO[' + size + ']" style="width: 90%" class="<?php echo $x->id; ?>field[' + size + ']" placeholder="0.00" size="4" /></td>\
									<td><input type="number" step="any" min="0" name="<?php echo $x->id; ?>_shippingO[' + size + ']" style="width: 90%" class="<?php echo $x->id; ?>field[' + size + ']" placeholder="0.00" size="4" /></td>\
								</tr>').appendTo('#<?php echo $x->id; ?>_table_rates table tbody');

								return false;
							});

							// Remove row
							jQuery('#<?php echo $x->id; ?>_table_rates').on( 'click', 'a.remove', function(){
								var answer = confirm("<?php _e( 'Delete the selected rates?', RPTR_CORE_TEXT_DOMAIN ); ?>")
									if (answer) {
										jQuery('#<?php echo $x->id; ?>_table_rates table tbody tr th.check-column input:checked').each(function(i, el){
										jQuery(el).closest('tr').remove();
									});
								}
								return false;
							});
						});
					</script>
				</td>
			</tr>

        <input type="hidden" id="hdn1" value="yes" />
		<?php
			return ob_get_clean();
		}
		
}
/********************* End Of primaryClass **********************************************************/
?>
