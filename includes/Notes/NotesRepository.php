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
 *
 * - Responsible for ALL data sanitization & validation.
 * - Controller passes raw data; repo enforces the schema rules.
 * - Throws Exception when invalid data or DB failure occurs.
 */
class NotesRepository extends BaseRepository
{
    public function __construct()
    {
        // ✅ Register a custom format filter to adjust wpdb->insert/wpdb->update format
        // This ensures numeric fields like user_id use %d instead of %s.
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
     * all()
     *
     * Fetch a *collection* of notes with optional filters,
     * sorting, and pagination.
     *
     * @param array $args Filters: page, per_page, search, status, order, orderby
     * @return array[] Array of rows from the DB.
     */
    public function all($args = [])
    {
        global $wpdb;

        $table  = $this->get_table_name();
        $sql    = "SELECT * FROM {$table}";
        $where  = [];   // dynamic WHERE conditions
        $params = [];   // values for prepare()

        // ✅ Filter by status if provided
        if (!empty($args['status'])) {
            $where[]  = "status = %s";
            $params[] = $args['status'];
        }

        // ✅ Filter by search term (matches title OR content)
        if (!empty($args['search'])) {
            $where[]  = "(title LIKE %s OR content LIKE %s)";
            $like     = '%' . $wpdb->esc_like($args['search']) . '%';
            $params[] = $like;
            $params[] = $like;
        }

        // ✅ Combine all WHERE clauses if any
        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        // ✅ Sorting: only allow known columns (avoid SQL injection)
        $allowed_orderby = ['created_at', 'updated_at', 'title'];
        $orderby = (!empty($args['orderby']) && in_array($args['orderby'], $allowed_orderby, true))
            ? $args['orderby']
            : 'created_at';

        // ✅ Default to DESC unless explicitly asc
        $order = (isset($args['order']) && strtolower($args['order']) === 'asc') ? 'ASC' : 'DESC';
        $sql  .= " ORDER BY {$orderby} {$order}";

        // ✅ Pagination
        $page     = !empty($args['page']) ? max(1, (int) $args['page']) : 1; // page >=1
        $per_page = !empty($args['per_page']) ? min(100, (int) $args['per_page']) : 10; // hard cap 100 per page
        $offset   = ($page - 1) * $per_page;

        // Add LIMIT/OFFSET regardless of filtering
        $sql .= $wpdb->prepare(" LIMIT %d OFFSET %d", $per_page, $offset);

        // ✅ If WHERE has params, we need to rebuild SQL with placeholders
        if ($params) {
            $sql = $wpdb->prepare(
                "SELECT * FROM {$table} WHERE " . implode(" AND ", $where) . " ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d",
                ...array_merge($params, [$per_page, $offset])
            );
        }

        // Returns an array of associative arrays
        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * count_all()
     *
     * Returns the total number of notes matching the same filters as all(),
     * but WITHOUT pagination.
     *
     * 🚨 Learner note:
     * Yes, this repeats the WHERE-building from `all()`.
     * In a DRY refactor, we’d extract a private `build_where_sql()` helper
     * and reuse it for both `all()` and `count_all()`.
     * 
     * But here we keep it explicit so you can read each method in isolation.
     *
     * @param array $args Same filters as all()
     * @return int Total number of matching rows.
     */
    public function count_all($args = [])
    {
        global $wpdb;

        $table  = $this->get_table_name();
        $sql    = "SELECT COUNT(*) FROM {$table}";
        $where  = [];
        $params = [];

        // Same WHERE logic as `all()`
        if (!empty($args['status'])) {
            $where[]  = "status = %s";
            $params[] = $args['status'];
        }

        if (!empty($args['search'])) {
            $where[]  = "(title LIKE %s OR content LIKE %s)";
            $like     = '%' . $wpdb->esc_like($args['search']) . '%';
            $params[] = $like;
            $params[] = $like;
        }

        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        if ($params) {
            $sql = $wpdb->prepare($sql, ...$params);
        }

        // get_var returns a single scalar value
        return (int) $wpdb->get_var($sql);
    }

    /**
     * get_table_name()
     *
     * Returns the fully-qualified table name for notes.
     * 
     * @return string Table name with WP prefix.
     */
    protected function get_table_name(): string
    {
        // Example: wp_lwpd_notes
        return $this->wpdb->prefix . 'lwpd_notes';
    }

    /**
     * sanitize_data()
     *
     * - Enforces business rules for notes.
     * - Cleans HTML, validates enum values, etc.
     * - Throws Exception if any field is invalid.
     *
     * Controller passes raw request params,
     * repo ensures they’re safe and valid.
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
                    // Must be a valid non-zero integer
                    $sanitized_args['user_id'] = absint($value);
                    if ($sanitized_args['user_id'] === 0) {
                        throw new Exception('The user_id must be a valid non-zero integer.');
                    }
                    break;

                case 'title':
                    // Plain text, must not be empty
                    $sanitized_args['title'] = sanitize_text_field($value);
                    if ($sanitized_args['title'] === '') {
                        throw new Exception('Title cannot be empty.');
                    }
                    break;

                case 'content':
                    // Allows only safe HTML tags
                    $sanitized_args['content'] = wp_kses_post($value);
                    break;

                case 'status':
                    // Sanitize & validate against allowed statuses
                    $sanitized_args['status'] = sanitize_text_field($value ?? 'draft');
                    if (!in_array($sanitized_args['status'], ['draft', 'archived', 'active'], true)) {
                        throw new Exception('Invalid status value. Must be draft, archived, or active.');
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
     * notes_format_filter()
     *
     * Filter callback for adjusting the $format array used in wpdb->insert()/update().
     * 
     * By default, WPDB treats all values as strings (%s).
     * But for numeric fields (like user_id), we want an integer format (%d).
     *
     * @param array  $format Default format array (%s for all fields).
     * @param array  $data   Sanitized data being saved.
     * @param string $table  Table name being written to.
     * @return array Adjusted format array.
     */
    public function notes_format_filter(array $format, array $data, string $table): array
    {
        // Apply only for this specific notes table
        if ($table === $this->get_table_name()) {
            $keys = array_keys($data);

            foreach ($keys as $index => $key) {
                if ($key === 'user_id') {
                    // Force user_id as integer format for SQL
                    $format[$index] = '%d';
                }
            }
        }

        return $format;
    }
}
