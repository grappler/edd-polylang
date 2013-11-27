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
        $edd_options['purchase_page'] = icl_object_id($edd_options['purchase_page'], 'page', true);
        $edd_options['success_page'] = icl_object_id($edd_options['success_page'], 'page', true);

		// Synchronize sales and earnings between translations
		add_filter('update_post_metadata', array($this, 'synchronize_download_totals'), 10, 5);
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

}
