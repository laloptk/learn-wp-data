<?php

namespace LearnWPData\Framework;

defined('ABSPATH') || exit; // Security: prevent direct access

/**
 * Abstract BaseTable class
 *
 * Provides a reusable foundation for creating and installing custom
 * WordPress database tables. Child classes should define their
 * own `$slug` and implement `get_schema()` to return the table SQL.
 *
 * Responsibilities:
 * - Centralize table prefixing with `$wpdb->prefix`
 * - Provide a generic install process using dbDelta()
 * - Trigger hooks before and after table installation
 */
abstract class BaseTable
{
    /**
     * WordPress database handler.
     *
     * @var \wpdb
     */
    protected \wpdb $wpdb;

    /**
     * Table slug without prefix.
     * Must be set in the child class (e.g. 'lwpd_notes').
     *
     * @var string|null
     */
    protected ?string $slug = null;

    /**
     * BaseTable constructor.
     *
     * Initializes the $wpdb instance for database operations.
     */
    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    /**
     * Installs (or updates) the table schema using dbDelta().
     *
     * Triggers hooks before and after the installation process.
     *
     * @return void
     * @throws \Exception If slug is missing.
     */
    public function install_table(): void
    {
        $table_name = $this->get_table_name();

        /**
         * Fires before the table installation begins.
         *
         * @param string $table_name Full table name including prefix.
         */
        do_action('learnwpdata_before_table_install', $table_name);

        $sql = $this->get_schema();

        // Ensure WP upgrade functions are loaded for dbDelta
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Creates or updates the table schema
        dbDelta($sql);

        /**
         * Fires after the table installation is complete.
         *
         * @param string $table_name Full table name including prefix.
         */
        do_action('learnwpdata_after_table_install', $table_name);
    }

    /**
     * Returns the full table name with the WordPress prefix.
     *
     * @return string Full prefixed table name.
     * @throws \Exception If slug is missing or invalid.
     */
    public function get_table_name(): string
    {
        if (empty($this->slug) || !is_string($this->slug)) {
            throw new \Exception(
                sprintf(
                    '%s: $slug must be defined in the child class as a non-empty string.',
                    static::class
                )
            );
        }

        return $this->wpdb->prefix . $this->slug;
    }

    /**
     * Returns the CREATE TABLE SQL statement for dbDelta().
     *
     * Must include the full table name (with prefix) via get_table_name().
     *
     * @return string SQL statement.
     */
    abstract protected function get_schema(): string;
}
