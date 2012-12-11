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

}
