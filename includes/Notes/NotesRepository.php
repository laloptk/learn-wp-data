<?php

namespace LearnWPData\Notes;

use Exception;
use LearnWPData\Framework\BaseRepository;

defined('ABSPATH') || exit;

/**
 * NotesRepository
 *
 * Handles CRUD operations for the wp_lwpd_notes table.
 * Extends BaseRepository for generic CRUD behavior.
 */
class NotesRepository extends BaseRepository
{
    /**
     * Constructor.
     *
     * Registers a filter to adjust the $wpdb->insert/$wpdb->update format
     * for numeric fields (like user_id) in this table.
     */
    public function __construct()
    {
        // Register format filter only once
        if (!has_filter('learnwpdata_repository_build_format', 'notes_repo_format_filter')) {
            add_filter(
                'learnwpdata_repository_build_format',
                [$this, 'notes_format_filter'],
                10,
                3
            );
        }

        parent::__construct();
    }

    /**
     * Returns the fully-qualified table name for notes.
     *
     * @return string Table name with WP prefix.
     */
    protected function get_table_name(): string
    {
        return $this->wpdb->prefix . 'lwpd_notes';
    }

    /**
     * Sanitizes and validates allowed note fields.
     *
     * - user_id must be a valid non-zero integer
     * - title must be plain text, non-empty
     * - content allows only safe HTML tags
     * - status must be either 'active' or 'archived'
     *
     * @param array $args Raw user-provided fields.
     * @return array Sanitized and validated data ready for DB.
     * @throws Exception If any field is invalid.
     */
    protected function sanitize_data(array $args): array
    {
        $sanitized_args = $args;

        foreach ($args as $key => $value) {
            switch ($key) {
                case 'user_id':
                    // Validate and sanitize user_id
                    $sanitized_args['user_id'] = absint($value);
                    if ($sanitized_args['user_id'] === 0) {
                        throw new Exception('The user_id must be a valid non-zero integer.');
                    }
                    break;

                case 'title':
                    // Sanitize and validate title
                    $sanitized_args['title'] = sanitize_text_field($value);
                    if ($sanitized_args['title'] === '') {
                        throw new Exception('Title cannot be empty.');
                    }
                    break;

                case 'content':
                    // Sanitize content (allow only safe HTML)
                    $sanitized_args['content'] = wp_kses_post($value);
                    break;

                case 'status':
                    // Sanitize and validate status
                    $sanitized_args['status'] = sanitize_text_field($value ?? 'active');
                    if (!in_array($sanitized_args['status'], ['active', 'archived'], true)) {
                        throw new Exception('Invalid status value. Must be active or archived.');
                    }
                    break;

                default:
                    // Ignore any extra fields silently
                    unset($sanitized_args[$key]);
            }
        }

        return $sanitized_args;
    }

    /**
     * Filter callback for adjusting the $format array
     * used in wpdb->insert() / wpdb->update().
     *
     * This ensures numeric fields like user_id use %d instead of %s.
     *
     * @param array  $format Default format array (%s for all fields).
     * @param array  $data   Sanitized data being saved.
     * @param string $table  Table name being written to.
     * @return array Adjusted format array.
     */
    public function notes_format_filter(array $format, array $data, string $table): array
    {
        // Apply only for the lwpd_notes table
        if ($table === $this->get_table_name()) {
            $keys = array_keys($data);

            foreach ($keys as $index => $key) {
                if ($key === 'user_id') {
                    $format[$index] = '%d'; // force user_id as an integer
                }
            }
        }

        return $format;
    }
}
