<?php

namespace LearnWPData\Framework;

defined('ABSPATH') || exit; // ✅ Security: prevent direct access

use Exception;
use wpdb;

/**
 * BaseRepository
 *
 * Abstract class providing generic CRUD operations for any custom table.
 *
 * ✅ Key responsibilities:
 * - Provides **common CRUD**: insert, read, update, delete, soft_delete
 * - Delegates **table-specific logic** (table name + sanitization rules) to child classes
 * - Wraps wpdb for safer + consistent usage
 *
 * Concrete repositories must implement:
 * - get_table_name() → returns the fully qualified table name
 * - sanitize_data() → table-specific sanitization and validation rules
 *
 * ❗ Learner Note:
 * This is the *Domain/Data Layer*. 
 * Controllers should call this with **raw data**. 
 * The repository enforces all data validation and sanitization.
 */
abstract class BaseRepository
{
    /**
     * WordPress database instance.
     * 
     * @var wpdb
     */
    protected wpdb $wpdb;

    /**
     * Constructor.
     *
     * Initializes the global $wpdb instance.
     * 
     * ❗ Why? 
     * wpdb is WP’s low-level DB wrapper, providing prepare(), insert(), update().
     */
    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    /**
     * Insert a new row into the table.
     *
     * @param array $data Raw data to be sanitized and inserted.
     * 
     * @return int Auto-increment ID of the inserted row.
     * 
     * @throws Exception 
     *  - If sanitization fails (repo will throw)
     *  - If the DB insert itself fails (wpdb->insert returns false)
     * 
     * ✅ Why return the ID?
     * - wpdb->insert() returns only boolean success.
     * - The actual new primary key is in $wpdb->insert_id.
     */
    public function insert(array $data): int
    {
        // ✅ Let child repo sanitize + validate before hitting DB
        $sanitized = $this->sanitize_data($data);

        // ✅ Build SQL format string array (allows numeric overrides)
        $format    = $this->build_format($sanitized);

        // ✅ Perform the insert query
        $result = $this->wpdb->insert(
            $this->get_table_name(),
            $sanitized,
            $format
        );

        // ❌ If insert failed at DB level
        if ($result === false) {
            throw new Exception("Insert failed in {$this->get_table_name()}.");
        }

        // ✅ Return the auto-increment ID instead of just boolean
        return (int) $this->wpdb->insert_id;
    }

    /**
     * Read a single row by its primary ID.
     *
     * @param int $id Record ID.
     * 
     * @return array|null Associative array if found, or null if no row exists.
     * 
     * @throws Exception 
     *  - If the provided ID is invalid (0 or negative)
     *
     * ✅ Why return null vs. throw?
     * - Reading a missing row is **not an exceptional case**, it’s valid to return null.
     */
    public function read(int $id): ?array
    {
        // ✅ Validate ID (must be >0)
        $id  = $this->validate_id($id);

        // ✅ Prepare query safely
        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$this->get_table_name()} WHERE id = %d LIMIT 1",
            $id
        );

        // ✅ get_row returns object or null; ARRAY_A forces associative array
        $row = $this->wpdb->get_row($sql, ARRAY_A);

        return $row ?: null;
    }

    /**
     * Update a row by its primary ID.
     *
     * @param int   $id   Record ID.
     * @param array $data Fields to update (will be sanitized).
     * 
     * @return bool True if row updated, false if no rows affected (same data or missing ID).
     * 
     * @throws Exception 
     *  - If ID invalid
     *  - If sanitization fails
     *  - If DB query fails
     */
    public function update(int $id, array $data): bool
    {
        $id        = $this->validate_id($id);

        // ✅ Let child repo sanitize & validate new data
        $sanitized = $this->sanitize_data($data);

        // ✅ Build SQL format for update
        $format    = $this->build_format($sanitized);

        // ✅ Perform update
        $result = $this->wpdb->update(
            $this->get_table_name(),
            $sanitized,        // SET ...
            ['id' => $id],     // WHERE id = X
            $format,           // format for SET values
            ['%d']             // format for WHERE id
        );

        // ❌ wpdb returns false on DB error
        if ($result === false) {
            throw new Exception("Update failed in {$this->get_table_name()}.");
        }

        // ✅ True if updated at least 1 row
        // 0 means no change (fields identical)
        return $result > 0;
    }

    /**
     * Hard delete a row by its primary ID.
     *
     * @param int $id Record ID.
     * 
     * @return bool True if deleted, false if no rows matched.
     * 
     * @throws Exception If ID invalid or DB error.
     * 
     * ✅ Hard vs. soft delete:
     * - Hard delete physically removes the row from DB.
     * - Soft delete would just mark it "archived".
     */
    public function delete(int $id): bool
    {
        $id = $this->validate_id($id);

        // ✅ Perform DELETE
        $result = $this->wpdb->delete(
            $this->get_table_name(),
            ['id' => $id],
            ['%d']
        );

        if ($result === false) {
            throw new Exception("Delete failed in {$this->get_table_name()}.");
        }

        return $result > 0;
    }

    /**
     * Soft delete a row by its primary ID.
     *
     * Instead of removing the row, mark it as 'archived'
     * and update the updated_at timestamp.
     *
     * ✅ Why soft delete?
     * - Keeps history
     * - Allows restoring if needed
     *
     * @param int $id Record ID.
     * @return bool True if updated, false if no rows affected.
     * @throws Exception If ID invalid or DB error.
     */
    public function soft_delete(int $id): bool
    {
        return $this->update($id, [
            'status'     => 'archived',
            'updated_at' => current_time('mysql'),
        ]);
    }

    /**
     * get_table_name()
     *
     * Concrete repo must return its fully qualified table name.
     * 
     * Example: wp_lwpd_notes
     */
    abstract protected function get_table_name(): string;

    /**
     * sanitize_data()
     *
     * Concrete repo must sanitize and validate data fields for its table.
     * 
     * Example:
     * - Check title not empty
     * - Validate enum status
     * - Sanitize HTML content
     * 
     * Must throw Exception if any invalid data is found.
     */
    abstract protected function sanitize_data(array $data): array;

    /**
     * validate_id()
     *
     * Ensures the provided primary key is valid (>0).
     * 
     * @param int $id Record ID.
     * @return int Sanitized positive ID.
     * @throws Exception If ID is zero or negative.
     */
    protected function validate_id(int $id): int
    {
        $id = absint($id);

        if ($id === 0) {
            throw new Exception('The ID must be a positive integer.');
        }

        return $id;
    }

    /**
     * build_format()
     *
     * Builds the wpdb->insert()/update() format array for the given data.
     *
     * Default: all values treated as strings ("%s").
     * 
     * ✅ Why override format?
     * - For numeric fields (e.g. IDs, counts), you want `%d` instead of `%s`.
     * - Child repos can filter this dynamically.
     *
     * @param array $data Sanitized data being saved.
     * @return array Format array matching the order of $data.
     */
    protected function build_format(array $data): array
    {
        // Default: assume all fields are strings
        $default_format = array_fill(0, count($data), '%s');

        /**
         * Filter the format array for insert/update queries.
         *
         * - Default: all `%s`
         * - Child repo can override certain fields like user_id → `%d`
         *
         * @param array  $default_format Default %s for each field.
         * @param array  $data           Sanitized data to be saved.
         * @param string $table_name     Current table name.
         */
        return apply_filters(
            'learnwpdata_repository_build_format',
            $default_format,
            $data,
            $this->get_table_name()
        );
    }
}
