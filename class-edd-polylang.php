<?php
class EDD_Polylang{

	function __construct(){
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	function init() {

		// Sanity check
		if ( ! defined( 'POLYLANG_VERSION' ) || ! defined( 'EDD_VERSION' ) && version_compare( EDD_VERSION, '2.3.0', '>=' ) ) {
			add_action( 'admin_notices', array( &$this, 'error_no_plugins' ) );
			return;
		}

		$edd_options['purchase_page']         = 'purchase_page';
		$edd_options['success_page']          = 'success_page';
		$edd_options['failure_page']          = 'failure_page';
		$edd_options['purchase_history_page'] = 'purchase_history_page';

		foreach( $edd_options as $key ) {
			add_filter( 'edd_get_option_' . $key, array( $this, 'option_per_lang' ), 10, 2 );
		}

		// Save order language and send email notifications in the correct language
		add_action( 'edd_insert_payment', array( &$this, 'save_payment_language' ), 10, 2);

		add_action( 'edd_payment_view_details', array( $this, 'display_payment_language' ) );

		//pll_register_string( 'edd_settings', 'purchase_receipt', 'edd', 'true');

	}

	// Error message if there are missing plugins
	function error_no_plugins() {
		?>
		<div class="error">
			<p><strong>
				<?php printf(
					__('EDD Polylang only works when %s and %s are installed and active.', 'edd_polylang'),
					'Polylang',
					'Easy Digital Downloads'
				); ?>
			</strong></p>
		</div>
		<?php
	}

	public function option_per_lang( $value ) {
		return pll_get_post( $value );
	}

	// Save the language the order was made in
	function save_payment_language( $payment, $payment_data ) {
		pll_set_post_language( $payment, pll_current_language() );
	}

	function display_payment_language( $payment_id ){ ?>
		<div class="column-container">
			<div class="column">
				<strong><?php _e( 'Language:', 'edd-polylang' ); ?></strong>&nbsp;
				<input type="text" name="edd-payment-language" value="<?php echo esc_attr( pll_get_post_language( $payment_id, 'name' ) ); ?>" class="medium-text"/>
			</div>
		</div>
<?php
	}

}
