<?php
class EDD_multilingual{

    function __construct(){
        add_action('plugins_loaded', array($this, 'init'), 20);
    }

    function init() {
        if (!defined('ICL_SITEPRESS_VERSION') || !defined('EDD_PLUGIN_DIR')) {
            add_action('admin_notices', array(&$this, 'error_no_plugins'));
        } elseif (!is_admin()) {
            $this->translate_pages();
        }
    }

    function error_no_plugins() {
        ?>
        <div class="error">
            <p><strong><?php printf(__('EDD multilingual only works when %s and %s are installed and active.', 'edd_multilingual'), 'WPML', 'Easy Digital Downloads'); ?></strong></p>
        </div>
        <?php
    }

    function translate_pages() {
        global $sitepress, $edd_options;
        // Only process this when we are viewing a non-default language
        if ($sitepress->get_default_language() != $sitepress->get_current_language()) {
            // Re-read settings because EDD reads them before WPML has hooked onto the filters
            $edd_options = edd_get_settings();
            // Translate the ID of selected pages
            $edd_options['purchase_page'] = icl_object_id($edd_options['purchase_page'], 'page', true);
            $edd_options['success_page'] = icl_object_id($edd_options['success_page'], 'page', true);
        }
    }

}
