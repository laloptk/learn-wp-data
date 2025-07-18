<?php

namespace LearnWPData\Notes;

use Exception;
use wpdb;

defined('ABSPATH') || exit;

/**
 * NotesRepository
 *
 * Handles CRUD for wp_lwpd_notes table.
 */
class NotesRepository
{
    protected wpdb $wpdb;
    protected string $table_name;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;

        $notes_table     = new NotesTable();
        $this->table_name = $notes_table->get_table_name();
    }

    /**
     * Inserts a new note.
     *
     * @param array $args Must include: user_id (int), title (string), content (string),
     *                    and optionally status ('active' or 'archived').
     * @throws Exception if validation fails or DB insert fails.
     */
    public function insert_note(array $args): void
    {
        $now = current_time('mysql');

        // Sanitize and validate user-provided fields
        $data = $this->get_sanitized_data($args);

        // Always add timestamps
        $data['created_at'] = $now;
        $data['updated_at'] = $now;

        // Generate correct format dynamically
        $format = $this->build_format($data);

        $result = $this->wpdb->insert(
            $this->table_name,
            $data,
            $format
        );

        if ($result === false) {
            throw new Exception('Failed to insert note into database.');
        }
    }

    /**
     * Updates an existing note by ID with only the allowed fields.
     *
     * @param int $id Note ID.
     * @param array $args Keys may include: title, content, status.
     * @return bool True if row updated, false if no rows affected.
     * @throws Exception if ID invalid, no valid fields provided, or DB error.
     */
    public function update_note(int $id, array $args): bool
    {
        $id = absint($id);
        if ($id === 0) {
            throw new Exception('The note ID must be a positive integer.');
        }

        // Only allow updating specific fields
        $allowed_update_fields = ['title', 'content', 'status'];
        $filtered_args         = array_intersect_key($args, array_flip($allowed_update_fields));

        if (empty($filtered_args)) {
            throw new Exception('No valid fields provided for update.');
        }

        // Sanitize/validate allowed fields
        $data = $this->get_sanitized_data($filtered_args);

        // Always bump updated_at
        $data['updated_at'] = current_time('mysql');

        // Generate format dynamically
        $format = $this->build_format($data);

        $result = $this->wpdb->update(
            $this->table_name,
            $data,
            ['id' => $id],
            $format,
            ['%d'] // where_format
        );

        if ($result === false) {
            throw new Exception('Failed to update note in database.');
        }

        return $result > 0;
    }

    /**
     * Get a single note by its ID (regardless of status).
     *
     * @param int $id Must be a valid non-zero integer.
     * @return array|null Note row as associative array, or null if not found.
     * @throws Exception If ID is invalid.
     */
    public function get_note_by_id(int $id): ?array
    {
        $id = absint($id);
        if ($id === 0) {
            throw new Exception('The note ID must be a valid non-zero integer.');
        }

        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d LIMIT 1",
            $id
        );

        $result = $this->wpdb->get_row($sql, ARRAY_A);

        return $result ?: null;
    }

    /**
     * Get all notes for a user filtered by status.
     *
     * @param int $user_id Must be a valid non-zero integer.
     * @param string $status Must be 'active' or 'archived'.
     * @return array List of notes as associative arrays.
     * @throws Exception If user_id invalid or status invalid.
     */
    public function get_notes_by_user(int $user_id, string $status = 'active'): array
    {
        $user_id = absint($user_id);
        if ($user_id === 0) {
            throw new Exception('The user_id must be a valid non-zero integer.');
        }

        $status = sanitize_text_field($status);
        if (!in_array($status, ['active', 'archived'], true)) {
            throw new Exception('Invalid status filter. Must be active or archived.');
        }

        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE user_id = %d AND status = %s",
            $user_id,
            $status
        );

        return $this->wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Soft delete a note (set status to archived).
     *
     * @param int $id Note ID.
     * @return bool True if updated, false if no rows found.
     * @throws Exception on DB error or invalid ID.
     */
    public function soft_delete_note(int $id): bool
    {
        $id = absint($id);
        if ($id === 0) {
            throw new Exception('The note ID must be a positive integer.');
        }

        $result = $this->wpdb->update(
            $this->table_name,
            [
                'status'     => 'archived',
                'updated_at' => current_time('mysql'),
            ],
            ['id' => $id],
            ['%s', '%s'],
            ['%d']
        );

        if ($result === false) {
            throw new Exception("Soft deleting the note failed due to a database error.");
        }

        return $result > 0;
    }

    /**
     * Hard delete a note (remove row completely).
     *
     * @param int $id Note ID.
     * @return bool True if deleted, false if no rows found.
     * @throws Exception on DB error or invalid ID.
     */
    public function hard_delete_note(int $id): bool
    {
        $id = absint($id);
        if ($id === 0) {
            throw new Exception('The note ID must be a positive integer.');
        }

        $result = $this->wpdb->delete(
            $this->table_name,
            ['id' => $id],
            ['%d']
        );

        if ($result === false) {
            throw new Exception("Hard deleting the note failed due to a database error.");
        }

        return $result > 0;
    }

    /**
     * Sanitizes and validates allowed note fields.
     *
     * @param array $args Raw fields (user_id, title, content, status).
     * @return array Sanitized and validated data ready for DB use.
     * @throws Exception if any field is invalid.
     */
    protected function get_sanitized_data(array $args): array
    {
        $sanitized_args = $args;

        foreach ($args as $key => $value) {
            switch ($key) {
                case 'user_id':
                    $sanitized_args['user_id'] = absint($sanitized_args['user_id']);
                    if ($sanitized_args['user_id'] === 0) {
                        throw new Exception('The user_id must be a valid non-zero integer.');
                    }
                    break;

                case 'title':
                    $sanitized_args['title'] = sanitize_text_field($sanitized_args['title']);
                    if ($sanitized_args['title'] === '') {
                        throw new Exception('Title cannot be empty.');
                    }
                    break;
 
                case 'content':
                    $sanitized_args['content'] = wp_kses_post($sanitized_args['content']);
                    break;

                case 'status':
                    $sanitized_args['status'] = sanitize_text_field($sanitized_args['status'] ?? 'active');
                    if (!in_array($sanitized_args['status'], ['active', 'archived'], true)) {
                        throw new Exception('Invalid status value. Must be active or archived.');
                    }
                    break;
            }
        }

        return $sanitized_args;
    }

    /**
     * Builds a format array for wpdb->insert() or update() based on data keys.
     *
     * user_id => %d, everything else => %s
     *
     * @param array $data Data array to generate format for.
     * @return array Format array matching keys order.
     */
    protected function build_format(array $data): array
    {
        return array_map(
            fn($key) => $key === 'user_id' ? '%d' : '%s',
            array_keys($data)
        );
    }
}

