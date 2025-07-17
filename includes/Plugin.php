<?php

namespace LearnWPData;

use LearnWPData\Notes\NotesTable;

defined('ABSPATH') || exit;

/**
 * Main plugin bootstrapper.
 */
class Plugin {

    /**
     * Initializes the plugin by registering activation hooks.
     *
     * @param string $plugin_file Full path to the main plugin file.
     */
    public static function init(string $plugin_file): void 
    {
        // Register activation hook for table creation
        register_activation_hook($plugin_file, [self::class, 'activate']);
    }

    /**
     * Runs on plugin activation.
     *
     * Creates the custom database tables.
     *
     * @return void
     */
    public static function activate(): void 
    {
        $notes_table = new NotesTable();
        $notes_table->install_table();
    }
}