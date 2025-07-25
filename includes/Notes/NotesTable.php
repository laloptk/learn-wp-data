<?php

namespace LearnWPData\Notes;

use LearnWPData\Framework\BaseTable;

defined('ABSPATH') || exit; // Security: prevent direct access

/**
 * NotesTable class
 *
 * Defines the schema and installation logic for the user notes table.
 * Extends BaseTable to leverage prefixing, dbDelta installation, and hooks.
 */
class NotesTable extends BaseTable
{
    /**
     * Table slug without prefix.
     *
     * Full table name will be wp_lwpd_notes (prefix handled by BaseTable).
     *
     * @var string
     */
    protected ?string $slug = 'lwpd_notes';

    /**
     * Returns the CREATE TABLE SQL statement for dbDelta().
     *
     * Includes:
     * - `id` primary key
     * - `user_id` (FK reference to wp_users.ID)
     * - `title` and `content` for note data
     * - `status` ENUM for active/archived
     * - `created_at` and `updated_at` timestamps
     * - `user_status` index for faster filtering by user/status
     *
     * @return string SQL statement for dbDelta().
     * @throws \Exception If table name cannot be resolved.
     */
    protected function get_schema(): string
    {
        // Fetch full prefixed table name (BaseTable will validate $slug)
        $table_name = $this->get_table_name();

        // Get WordPress DB collation for compatibility
        $collate = $this->wpdb->collate;

        // Return full CREATE TABLE SQL for dbDelta()
        return "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            title VARCHAR(255) NOT NULL,
            content LONGTEXT NOT NULL,
            status ENUM('active', 'archived', 'draft') NOT NULL DEFAULT 'draft',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_status (user_id, status)
        ) DEFAULT CHARSET=utf8mb4 COLLATE={$collate}";
    }
}
