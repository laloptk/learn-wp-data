<?php 

/*
 * Plugin Name: LearnWPData
 * Text Domain: learn-wp-data
 */

defined('ABSPATH') || exit;

define( 'LEARNWPDATA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LEARNWPDATA_PLUGIN_DIR', __DIR__ );

require_once LEARNWPDATA_PLUGIN_DIR . '/includes/helpers-template.php';

require LEARNWPDATA_PLUGIN_DIR .'/vendor/autoload.php';

use LearnWPData\Plugin;
use LearnWPData\Notes\NotesController;
use LearnWPData\Notes\NotesRepository;
use LearnWPData\Notes\Admin\NotesAdminPage;

Plugin::init(__FILE__);

/**
 * Hook REST API routes on rest_api_init
 */
add_action('rest_api_init', function () {

    // 1️⃣ Create a repository instance
    $repo = new NotesRepository();

    // 2️⃣ Create the controller, injecting the repo
    $controller = new NotesController($repo);

    // 3️⃣ Register the REST routes
    $controller->register_routes();
});

new NotesAdminPage(new NotesRepository());

function learnwpdata_register_blocks() {
    register_block_type(__DIR__ . '/build/blocks/single-note');
    register_block_type(__DIR__ . '/build/blocks/notes-list');
}
add_action('init', 'learnwpdata_register_blocks');
