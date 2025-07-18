<?php

namespace LearnWPData\Framework;

defined('ABSPATH') || exit; // Security: prevent direct access

use Exception;
use wpdb;

/**
 * BaseRepository
 *
 * Abstract class providing generic CRUD operations for any custom table.
 * Concrete repositories must implement:
 * - get_table_name() → returns the fully qualified table name
 * - sanitize_data() → table-specific sanitization and validation rules
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
     * @return bool True on success.
     * @throws Exception If sanitization fails or DB insert fails.
     */
    public function insert(array $data): bool
    {
        $sanitized = $this->sanitize_data($data);
        $format    = $this->build_format($sanitized);

        $result = $this->wpdb->insert(
            $this->get_table_name(),
            $sanitized,
            $format
        );

        if ($result === false) {
            throw new Exception("Insert failed in {$this->get_table_name()}.");
        }

        return true;
    }

    /**
     * Read a single row by its primary ID.
     *
     * @param int $id Record ID.
     * @return array|null Associative array if found, or null if no row.
     * @throws Exception If the ID is invalid.
     */
    public function read(int $id): ?array
    {
        $id  = $this->validate_id($id);

        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$this->get_table_name()} WHERE id = %d LIMIT 1",
            $id
        );

        $row = $this->wpdb->get_row($sql, ARRAY_A);

        return $row ?: null;
    }

    /**
     * Update a row by its primary ID.
     *
     * @param int   $id   Record ID.
     * @param array $data Fields to update (will be sanitized).
     * @return bool True if row updated, false if no rows affected.
     * @throws Exception If ID invalid, sanitization fails, or DB error.
     */
    public function update(int $id, array $data): bool
    {
        $id        = $this->validate_id($id);
        $sanitized = $this->sanitize_data($data);
        $format    = $this->build_format($sanitized);

        $result = $this->wpdb->update(
            $this->get_table_name(),
            $sanitized,
            ['id' => $id],
            $format,
            ['%d']
        );

        if ($result === false) {
            throw new Exception("Update failed in {$this->get_table_name()}.");
        }

        return $result > 0;
    }

    /**
     * Hard delete a row by its primary ID.
     *
     * @param int $id Record ID.
     * @return bool True if deleted, false if no rows found.
     * @throws Exception If ID invalid or DB error.
     */
    public function delete(int $id): bool
    {
        $id = $this->validate_id($id);

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
     * This is a wrapper around update() that sets a `status = archived`
     * and updates the timestamp.
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
     * Returns the table name.
     * Must be implemented by the concrete repository.
     *
     * @return string Fully qualified table name.
     */
    abstract protected function get_table_name(): string;

    /**
     * Sanitizes and validates table-specific data.
     * Must be implemented by the concrete repository.
     *
     * @param array $data Raw user-provided data.
     * @return array Sanitized and validated data ready for DB use.
     * @throws Exception If invalid data.
     */
    abstract protected function sanitize_data(array $data): array;

    /**
     * Validates a primary ID.
     *
     * @param int $id Record ID.
     * @return int Validated and sanitized ID.
     * @throws Exception If invalid.
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
     * Builds a wpdb->insert()/update() format array for the given data.
     *
     * Default behavior assumes all values are strings ("%s").
     * Concrete repositories or filters can adjust numeric fields dynamically.
     *
     * @param array $data Sanitized data being saved.
     * @return array Format array matching the order of $data.
     */
    protected function build_format(array $data): array
    {
        // Default: all columns as %s
        $default_format = array_fill(0, count($data), '%s');

        /**
         * Filter the format array for insert/update queries.
         *
         * @param array  $default_format Default array of %s formats.
         * @param array  $data           Sanitized data being saved.
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
