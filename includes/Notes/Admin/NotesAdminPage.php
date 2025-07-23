<?php
namespace LearnWPData\Notes\Admin;

use LearnWPData\Framework\Admin\BaseAdminPage;

defined('ABSPATH') || exit;

/**
 * NotesAdminPage
 * 
 * Concrete admin page for the Notes module.
 */
class NotesAdminPage extends BaseAdminPage {

    public function __construct() {
        $this->menu_slug  = 'learnwpdata-admin';
        $this->menu_title = __('LearnWPData', 'learnwpdata');
        $this->icon       = 'dashicons-database';
        parent::__construct();
    }

    public function render_page(): void {
        echo '<div id="learnwpdata-admin-root"></div>';
    }

    protected function enqueue_page_assets(): void {
        $asset_file = plugin_dir_url(__FILE__) . '../../../build/admin.js';

        wp_enqueue_script(
            'learnwpdata-admin',
            $asset_file,
            ['wp-element'], // React provided by WP
            '1.0.0',
            true
        );

        wp_localize_script('learnwpdata-admin', 'LearnWPDataConfig', [
            'restUrl' => esc_url_raw(rest_url('learnwpdata/v1/')),
            'nonce'   => wp_create_nonce('wp_rest'),
        ]);
    }
}

