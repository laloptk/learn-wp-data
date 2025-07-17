<?php

namespace LearnWPData\Notes;

use \Exception;
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

        $notes_table = new NotesTable();
        $this->table_name = $notes_table->get_table_name();
    }

    /**
     * Inserts a new note.
     *
     * @param array $args Associative array with keys: user_id, title, content, status.
     * @throws Exception if data is invalid or DB insert fails.
     */
    public function insert_note( array $args ): void
    {
        $data = $this->get_sanitized_data( $args );

        $result = $this->wpdb->insert(
            $this->table_name,
            $data,
            ['%d', '%s', '%s', '%s', '%s', '%s'] // placeholders for each field
        );

        if ( $result === false ) {
            throw new Exception('Failed to insert note into database.');
        }
    }

    /**
     * Sanitizes and validates note data.
     *
     * - user_id must be a valid integer
     * - title is plain text
     * - content allows safe HTML
     * - status must be "active" or "archived"
     *
     * @param array $args Raw input data.
     * @return array Sanitized and validated data ready for DB insert.
     * @throws Exception if validation fails.
     */
    protected function get_sanitized_data( array $args ): array
    {
        // Validate and sanitize user_id
        $user_id = absint( $args['user_id'] ?? 0 );
        if ( $user_id === 0 ) {
            throw new Exception('The user_id must be a valid non-zero integer.');
        }

        // Sanitize title
        $title = sanitize_text_field( $args['title'] ?? '' );
        if ( $title === '' ) {
            throw new Exception('Title cannot be empty.');
        }

        // Sanitize content (allow safe HTML)
        $content = wp_kses_post( $args['content'] ?? '' );

        // Sanitize and validate status
        $status = sanitize_text_field( $args['status'] ?? 'active' );
        if ( ! in_array( $status, ['active', 'archived'], true ) ) {
            throw new Exception('Invalid status value. Must be active or archived.');
        }

        // Add timestamps automatically
        $now = current_time( 'mysql' );

        return [
            'user_id'    => $user_id,
            'title'      => $title,
            'content'    => $content,
            'status'     => $status,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    /**
     * Get a single note by its ID.
     *
     * Always returns the row regardless of its status, so the caller can inspect it.
     *
     * @param int $id Must be a valid non-zero integer.
     * @return array|null Returns the note as an associative array, or null if not found.
     * @throws \Exception If the note ID is invalid.
     */
    public function get_note_by_id(int $id): ?array
    {
        $id = absint($id);
        if ($id === 0) {
            throw new \Exception('The note ID must be a valid non-zero integer.');
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
     * Unlike get_note_by_id(), this explicitly filters by 'active' or 'archived'.
     *
     * @param int $user_id Must be a valid non-zero integer.
     * @param string $status Must be 'active' or 'archived'.
     * @return array List of notes as associative arrays.
     * @throws \Exception If user_id or status is invalid.
     */
    public function get_notes_by_user(int $user_id, string $status = 'active'): array
    {
        $user_id = absint($user_id);
        if ($user_id === 0) {
            throw new \Exception('The user_id must be a valid non-zero integer.');
        }

        $status = sanitize_text_field($status);
        if (!in_array($status, ['active', 'archived'], true)) {
            throw new \Exception('Invalid status filter. Must be active or archived.');
        }

        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE user_id = %d AND status = %s",
            $user_id,
            $status
        );

        return $this->wpdb->get_results($sql, ARRAY_A);
    }

    public function soft_delete_note(int $id): bool
    {
        $id = absint($id);
        if ($id === 0) {
            throw new \Exception('The note ID must be a positive integer.');
        }

        $result = $this->wpdb->update(
            $this->table_name,
            [
                'status'     => 'archived',
                'updated_at' => current_time('mysql'),
            ],
            [ 'id' => $id ],
            [ '%s', '%s' ],
            [ '%d' ]
        );

        if ($result === false) {
            throw new \Exception("Soft deleting the note failed due to a database error.");
        }

        // Returns true if row updated, false if no row matched (ID not found)
        return $result > 0;
    }

    public function hard_delete_note(int $id): bool
    {
        $id = absint($id);
        if ($id === 0) {
            throw new \Exception('The note ID must be a positive integer.');
        }

        $result = $this->wpdb->delete(
            $this->table_name,
            [ 'id' => $id ],
            [ '%d' ]
        );

        if ($result === false) {
            throw new \Exception("Soft deleting the note failed due to a database error.");
        }

        // Returns true if row updated, false if no row matched (ID not found)
        return $result > 0;
    }
}
