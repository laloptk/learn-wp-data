<?php 

/*
 * Plugin Name: LearnWPData
 * Text Domain: learn-wp-data
 */

defined('ABSPATH') || exit;

require __DIR__.'/vendor/autoload.php';

use LearnWPData\Plugin;
use LearnWPData\Notes\NotesController;
use LearnWPData\Notes\NotesRepository;

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