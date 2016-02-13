<?php
class EDD_Polylang{

	public function __construct(){
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	public function init() {

		// Check if EDD v2.2.3 and Polylang are installed
		if ( ! defined( 'POLYLANG_VERSION' ) || ! defined( 'EDD_VERSION' ) || version_compare( EDD_VERSION, '2.3', '<' ) ) {
			add_action( 'admin_notices', array( $this, 'error_no_plugins' ) );
			return;
		}

		// Return correct EDD page id for current language
		$edd_options['purchase_page']         = 'purchase_page';
		$edd_options['success_page']          = 'success_page';
		$edd_options['failure_page']          = 'failure_page';
		$edd_options['purchase_history_page'] = 'purchase_history_page';

		foreach( $edd_options as $key ) {
			add_filter( 'edd_get_option_' . $key, array( $this, 'option_per_lang' ), 10, 2 );
		}

		// Save order language in the correct language
		add_action( 'edd_insert_payment', array( &$this, 'save_payment_language' ), 10, 2);

		// Display language in order
		add_action( 'edd_payment_view_details', array( $this, 'display_payment_language' ) );

		// Define EDD settings to translate
		add_filter( 'plugins_loaded', array( $this, 'define_edd_strings' ), 20 );

		// Translate EDD Strings
		foreach( $this->edd_strings() as $key => $string) {
			add_filter( 'edd_get_option_' . $key, array( $this, 'translate_string' ), 10, 2 );
		}

	}

	// Error message if there are missing plugins
	public function error_no_plugins() {
		?>
		<div class="error">
			<p><strong>
				<?php printf(
					__('EDD Polylang only works when %s and %s are installed and active.', 'edd_polylang'),
					'Polylang',
					'Easy Digital Downloads v2.3'
				); ?>
			</strong></p>
		</div>
		<?php
	}

	public function option_per_lang( $value ) {
		$page_id = (pll_get_post( $value ) > 0) ? pll_get_post( $value ) : $value;
		return $page_id;
	}

	public function translate_string( $value ) {
		return pll__( $value );
	}

	// Save the language the order was made in
	public function save_payment_language( $payment, $payment_data ) {
		pll_set_post_language( $payment, pll_current_language() );
	}

	public function display_payment_language( $payment_id ){ ?>
		<div class="column-container">
			<div class="column">
				<strong><?php _e( 'Language:', 'edd-polylang' ); ?></strong>&nbsp;
				<input type="text" name="edd-payment-language" value="<?php echo esc_attr( pll_get_post_language( $payment_id, 'name' ) ); ?>" class="medium-text"/>
			</div>
		</div>
<?php
	}

	public function edd_strings() {

		$settings = array(
			'currency'                  => __( 'Currency', 'edd-polylang' ),
			'currency_position'         => __( 'Currency Position', 'edd-polylang' ),
			'thousands_separator'       => __( 'Thousands Separator', 'edd-polylang' ),
			'decimal_separator'         => __( 'Decimal Separator', 'edd-polylang' ),
			'from_name'                 => __( 'From Name', 'edd-polylang' ),
			'from_email'                => __( 'From Email', 'edd-polylang' ),
			'purchase_subject'          => __( 'Purchase Email Subject', 'edd-polylang' ),
			'purchase_heading'          => __( 'Purchase Email Heading', 'edd-polylang' ),
			'purchase_receipt'          => __( 'Purchase Receipt', 'edd-polylang' ),
			'sale_notification_subject' => __( 'Sale Notification Subject', 'edd-polylang' ),
			'sale_notification'         => __( 'Sale Notification', 'edd-polylang' ),
			'agree_label'               => __( 'Agree to Terms Label', 'edd-polylang' ),
			'agree_text'                => __( 'Agreement Text', 'edd-polylang' ),
			'checkout_label'            => __( 'Complete Purchase Text', 'edd-polylang' ),
			'add_to_cart_text'          => __( 'Add to Cart Text', 'edd-polylang' ),
		);

		return $settings;
	}

	public function define_edd_strings() {

		$settings = $this->edd_strings();

		$multiline_settings = array(
			'purchase_receipt',
			'sale_notification',
			'agree_text'
		);

		foreach( $settings as $key => $string ){
			$multiline = false;
			if( in_array( $key, $multiline_settings ) ) {
				$multiline = true;
			}
			$setting = edd_get_option( $key );
			pll_register_string( $string, $setting, 'Easy Digital Downloads', $multiline );
		}

	}

}
