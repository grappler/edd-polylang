<?php
class EDD_multilingual{

    function __construct(){
        add_action('plugins_loaded', array($this, 'init'), 20);
    }

    function init() {
        global $sitepress, $edd_options;

        // Sanity check
        if (!defined('ICL_SITEPRESS_VERSION') || !defined('EDD_PLUGIN_DIR')) {
            add_action('admin_notices', array(&$this, 'error_no_plugins'));
            return;
        }

        // Save order language and send email notifications in the correct language
        add_action('edd_insert_payment', array(&$this, 'save_payment_language'), 10, 2);
        if (is_admin() && isset($_GET['edd-action']) && $_GET['edd-action'] == 'email_links') {
            $lang = get_post_meta(intval($_GET['purchase_id']), 'wpml_language', true);
            if (!empty($lang)) {
                $sitepress->switch_lang($lang);
            }
        }

        // Re-read settings because EDD reads them before WPML has hooked onto the filters
        $edd_options = edd_get_settings();

        // Translate post_id for pages in options
        $edd_options['purchase_page'] 	= icl_object_id($edd_options['purchase_page'], 'page', true);
        $edd_options['success_page'] 	= icl_object_id($edd_options['success_page'], 'page', true);
        $edd_options['failure_page'] 	= icl_object_id($edd_options['failure_page'], 'page', true);

        // Crowdfunding plugin
        if (class_exists('ATCF_CrowdFunding')) {
	        $edd_options['faq_page'] 			= icl_object_id($edd_options['faq_page'], 'page', true);
	        $edd_options['submit_page'] 		= icl_object_id($edd_options['submit_page'], 'page', true);
	        $edd_options['submit_success_page'] = icl_object_id($edd_options['submit_success_page'], 'page', true);
	        $edd_options['profile_page'] 		= icl_object_id($edd_options['profile_page'], 'page', true);
	        $edd_options['login_page'] 			= icl_object_id($edd_options['login_page'], 'page', true);
	        $edd_options['register_page'] 		= icl_object_id($edd_options['register_page'], 'page', true);
        }

		// Synchronize sales and earnings between translations
		add_filter('update_post_metadata', array($this, 'synchronize_download_totals'), 10, 5);
		add_action('edd_tools_after', array($this, 'recalculate_show_link'));
		add_action('wp_ajax_edd_recalculate', array($this, 'recalculate_download_totals'));

		// Add back the flags to downloads manager
		add_filter('edd_download_columns', array($sitepress, 'add_posts_management_column'));
    }

    // Error message if there are missing plugins
    function error_no_plugins() {
        ?>
        <div class="error">
            <p><strong><?php printf(__('EDD multilingual only works when %s and %s are installed and active.', 'edd_multilingual'), 'WPML', 'Easy Digital Downloads'); ?></strong></p>
        </div>
        <?php
    }

    // Save the language the order was made in
    function save_payment_language($payment, $payment_data) {
        global $sitepress;
        update_post_meta($payment, 'wpml_language', $sitepress->get_current_language());
    }

	function synchronize_download_totals($null, $object_id, $meta_key, $meta_value, $prev_value ) {
		global $sitepress;

		if (in_array($meta_key, array('_edd_download_sales', '_edd_download_earnings'))) {
			remove_filter('update_post_metadata', array($this, 'synchronize_download_totals'), 10, 5);
			$languages = icl_get_languages('skip_missing=0');
			foreach ($languages as $lang) {
				if ($lang['language_code'] != $sitepress->get_current_language()) {
					$post_id = icl_object_id($object_id, 'download', false, $lang['language_code']);
					update_post_meta($post_id, $meta_key, $meta_value);
				}
			}
			add_filter('update_post_metadata', array($this, 'synchronize_download_totals'), 10, 5);
		}
		return null;
	}

	function recalculate_show_link() {
?>
<div id="edd_ml_recalculate">
	<a class="button" href="#recalculate">Recalculate totals</a>
</div>
<script type="text/javascript">
jQuery(function($){
	$('#edd_ml_recalculate a').click(function() {
		$('#edd_ml_recalculate a').attr('disabled', 'disabled');
		$.post(ajaxurl, {action:"edd_recalculate"},function(){
			$('#edd_ml_recalculate a').removeAttr('disabled');
			$('#edd_ml_recalculate').append('<span>done!</span>');
			setTimeout(function() {
				$('#edd_ml_recalculate span').fadeOut('slow');
			}, 1000);
		});
		return false;
	});
});
</script>
<?php
	}

	function recalculate_download_totals() {
		global $wpdb, $sitepress;

		$wpdb->query("UPDATE $wpdb->postmeta SET meta_value = '0' WHERE meta_key IN ('_edd_download_earnings', '_edd_download_sales')");
		$logs = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_type = 'edd_log'");
		foreach ($logs as $log) {
			$payment_id = get_post_meta( $log->ID, '_edd_log_payment_id', true );
			$lang = get_post_meta( $payment_id, 'wpml_language', true);
			if (empty($lang)) {
				$lang = $sitepress->get_default_language();
			}
			if (get_post($payment_id)) {
				$cart_items = edd_get_payment_meta_cart_details( $payment_id );
				$amount     = 0;
				if ( is_array( $cart_items ) ) {
					foreach ( $cart_items as $item ) {
						$sitepress->switch_lang($lang);
						edd_increase_earnings($item['id'], $item['price']);
						edd_increase_purchase_count($item['id']);
						$sitepress->switch_lang();
					}
				}
			}
		}

		die('1');
	}

}
