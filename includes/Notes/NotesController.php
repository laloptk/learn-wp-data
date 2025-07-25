<?php

namespace LearnWPData\Notes;

use LearnWPData\Notes\NotesRepository;
use WP_REST_Controller;
use WP_REST_Response;
use WP_Error;

/**
 * NotesController
 *
 * Handles REST API routes for Notes.
 * 
 * - Controller = thin HTTP layer (permissions, HTTP codes)
 * - Repository = domain/data layer (sanitization, validation, DB ops)
 *
 * This is designed as a **learning resource**, so it’s intentionally explicit.
 */
class NotesController extends WP_REST_Controller {

    protected NotesRepository $repo;

    public function __construct( NotesRepository $repo ) {
        // Core REST namespace & base path
        $this->namespace = 'learnwpdata/v1';
        $this->rest_base = 'notes';
        $this->repo = $repo;
    }

    /**
     * Register all routes for this controller.
     */
    public function register_routes() {

        // Collection-level routes (GET list, POST create)
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => 'GET',
                    'callback'            => [ $this, 'get_items' ],
                    'permission_callback' => [ $this, 'get_items_permissions_check' ],
                    'args'                => $this->get_collection_params(),
                ],
                [
                    'methods'             => 'POST',
                    'callback'            => [ $this, 'create_item' ],
                    'permission_callback' => [ $this, 'create_item_permissions_check' ],
                    'args'                => $this->get_endpoint_args_for_item_schema(true),
                ],
            ]
        );

        // Single-item routes (GET, PUT/PATCH, DELETE)
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)',
            [
                [
                    'methods'             => 'GET',
                    'callback'            => [ $this, 'get_item' ],
                    'permission_callback' => [ $this, 'get_item_permissions_check' ],
                ],
                [
                    'methods'             => 'PUT, PATCH',
                    'callback'            => [ $this, 'update_item' ],
                    'permission_callback' => [ $this, 'update_item_permissions_check' ],
                    'args'                => $this->get_endpoint_args_for_item_schema(false),
                ],
                [
                    'methods'             => 'DELETE',
                    'callback'            => [ $this, 'delete_item' ],
                    'permission_callback' => [ $this, 'delete_item_permissions_check' ],
                ],
            ]
        );
    }

    /**
     * GET /notes (list)
     * Returns a paginated collection with headers like WP Core.
     */
    public function get_items( $request ) {

        // Collect filters from request
        $args = [
            'page'     => $request->get_param('page'),
            'per_page' => $request->get_param('per_page'),
            'search'   => $request->get_param('search'),
            'status'   => $request->get_param('status'),
            'order'    => $request->get_param('order'),
            'orderby'  => $request->get_param('orderby'),
        ];

        // Fetch paginated rows from repo
        $notes = $this->repo->all($args);

        // Fetch total count for same filters (needed for pagination headers)
        $total = $this->repo->count_all($args);

        // Prepare API-ready items
        $response_items = [];
        foreach ($notes as $note) {
            $response_items[] = $this->prepare_item_for_response($note, $request);
        }

        // Wrap in WP_REST_Response
        $response = rest_ensure_response($response_items);

        // Add pagination headers (like WP Core /wp/v2/posts)
        $per_page    = $args['per_page'] ?: 10;
        $total_pages = $per_page > 0 ? ceil($total / $per_page) : 1;

        $response->header('X-WP-Total', (int) $total);
        $response->header('X-WP-TotalPages', (int) $total_pages);

        return $response;
    }

    /**
     * GET /notes/{id}
     * Fetch a single note.
     */
    public function get_item($request) {
        $id = (int) $request->get_param('id');

        try {
            $item = $this->repo->read($id);
        } catch (\Exception $e) {
            // If repo throws, translate to REST error
            return new WP_Error(
                'rest_note_not_found',
                $e->getMessage(),
                ['status' => 404]
            );
        }

        return rest_ensure_response($this->prepare_item_for_response($item, $request));
    }

    /**
     * POST /notes
     * Create a new note.
     */
    public function create_item( $request ) {

        // 1️⃣ Minimal HTTP-level validation: title must be present
        $title = $request->get_param('title');
        if ( empty( $title ) ) {
            return new WP_Error(
                'rest_missing_title',
                __( 'The title field is required.' ),
                [ 'status' => 400 ]
            );
        }

        // 2️⃣ Collect raw data (repo will sanitize/validate)
        $data = [
            'user_id' => get_current_user_id(),
            'title'   => $title,
            'content' => $request->get_param('content'),
            'status'  => $request->get_param('status') ?? 'draft',
        ];

        try {
            // 3️⃣ Insert via repo → returns new ID
            $created_id = $this->repo->insert($data);

            // 4️⃣ Fetch the just-created note
            $note = $this->repo->read($created_id);

            // 5️⃣ Return 201 Created with note resource
            return new WP_REST_Response(
                $this->prepare_item_for_response($note, $request),
                201
            );

        } catch ( \Exception $e ) {
            // Repo throws on invalid data or DB failure
            return new WP_Error(
                'rest_note_create_failed',
                $e->getMessage(),
                [ 'status' => 400 ]
            );
        }
    }

    /**
     * PUT/PATCH /notes/{id}
     * Update an existing note.
     */
    public function update_item($request) {
        $id = (int) $request->get_param('id');

        // Check note exists
        $existing = $this->repo->read($id);
        if (!$existing) {
            return new WP_Error('rest_note_not_found', __('Note not found.'), ['status' => 404]);
        }

        // Collect raw data (repo will sanitize & validate)
        $data = [
            'title'   => $request->get_param('title')   ?? $existing->title,
            'content' => $request->get_param('content') ?? $existing->content,
            'status'  => $request->get_param('status')  ?? $existing->status,
        ];

        try {
            // Let repo validate + save
            $this->repo->update($id, $data);

            // Fetch updated note
            $note = $this->repo->read($id);

            return rest_ensure_response($this->prepare_item_for_response($note, $request));

        } catch (\Exception $e) {
            return new WP_Error(
                'rest_note_update_failed',
                $e->getMessage(),
                [ 'status' => 400 ]
            );
        }
    }

    /**
     * DELETE /notes/{id}
     */
    public function delete_item($request) {
        $id = (int) $request->get_param('id');

        $existing = $this->repo->read($id);
        if (!$existing) {
            return new WP_Error('rest_note_not_found', __('Note not found.'), ['status' => 404]);
        }

        $deleted = $this->repo->delete($id);
        if (!$deleted) {
            return new WP_Error('rest_note_delete_failed', __('Failed to delete note.'), ['status' => 500]);
        }

        return rest_ensure_response([
            'deleted'  => true,
            'previous' => $this->prepare_item_for_response($existing, $request),
        ]);
    }

    /**
     * Schema describing a Note resource.
     *
     * - Helps WordPress REST API discoverability
     * - Shows types + enum values in /wp-json index
     */
    public function get_item_schema() {

        return [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'note',
            'type'       => 'object',

            'properties' => [
                'id' => [
                    'description' => __('Unique identifier for the note.'),
                    'type'        => 'integer',
                    'readonly'    => true,
                ],
                'author' => [
                    'description' => __('ID of the user who created the note.'),
                    'type'        => 'integer',
                    'readonly'    => true,
                ],
                'title' => [
                    'description' => __('Title of the note.'),
                    'type'        => 'string',
                    'required'    => true,
                ],
                'content' => [
                    'description' => __('Content of the note.'),
                    'type'        => 'string',
                ],
                'status' => [
                    'description' => __('Status of the note.'),
                    'type'        => 'string',
                    'enum'        => ['draft', 'archived', 'active'],
                    'default'     => 'draft',
                ],
                'created_at' => [
                    'description' => __('Date the note was created.'),
                    'type'        => 'string',
                    'format'      => 'date-time',
                    'readonly'    => true,
                ],
                'updated_at' => [
                    'description' => __('Date the note was last updated.'),
                    'type'        => 'string',
                    'format'      => 'date-time',
                    'readonly'    => true,
                ],
            ],
        ];
    }

    /**
     * Collection query params (pagination, search, filters)
     */
    public function get_collection_params() {
        return [
            'page' => [
                'description'        => __('Current page of the collection.'),
                'type'               => 'integer',
                'default'            => 1,
                'minimum'            => 1,
                'sanitize_callback'  => 'absint',
            ],
            'per_page' => [
                'description'        => __('Number of items per page.'),
                'type'               => 'integer',
                'default'            => 10,
                'minimum'            => 1,
                'maximum'            => 100,
                'sanitize_callback'  => 'absint',
            ],
            'search' => [
                'description'        => __('Limit results to those matching a string in the title or content.'),
                'type'               => 'string',
                'sanitize_callback'  => 'sanitize_text_field',
            ],
            'status' => [
                'description'        => __('Limit results to a specific note status.'),
                'type'               => 'string',
                'enum'               => ['draft', 'archived', 'active'],
                'sanitize_callback'  => 'sanitize_text_field',
            ],
            'orderby' => [
                'description'        => __('Sort collection by object attribute.'),
                'type'               => 'string',
                'default'            => 'created_at',
                'enum'               => ['created_at', 'updated_at', 'title'],
                'sanitize_callback'  => 'sanitize_text_field',
            ],
            'order' => [
                'description'        => __('Order sort attribute ascending or descending.'),
                'type'               => 'string',
                'default'            => 'desc',
                'enum'               => ['asc', 'desc'],
                'sanitize_callback'  => 'sanitize_text_field',
            ],
        ];
    }

    /**
     * Formats a DB row into an API response shape.
     */
    public function prepare_item_for_response($item, $request) {
        $data = [];

        foreach ($item as $key => $val) {
            switch ($key) {
                case 'id':
                    $data['id'] = (int) $val;
                    break;
                case 'user_id':
                    $data['author'] = (int) $val;
                    break;
                case 'title':
                    $data['title'] = $val;
                    break;
                case 'content':
                    $data['content'] = $val;
                    break;
                case 'status':
                    $data['status'] = $val;
                    break;
                case 'created_at':
                    $data['created_at'] = mysql_to_rfc3339($val);
                    break;
                case 'updated_at':
                    $data['updated_at'] = mysql_to_rfc3339($val);
                    break;
            }
        }

        // HATEOAS _links section
        $data['_links'] = [
            'self' => [
                [
                    'href' => rest_url(sprintf(
                        '%s/%s/%d',
                        $this->namespace,
                        $this->rest_base,
                        $item['id']
                    )),
                ],
            ],
            'collection' => [
                [
                    'href' => rest_url(sprintf(
                        '%s/%s',
                        $this->namespace,
                        $this->rest_base
                    )),
                ],
            ],
            'author' => [
                [
                    'href' => rest_url('wp/v2/users/' . $item['user_id']),
                ],
            ],
        ];

        return $data;
    }

    /* ====== Minimal permission callbacks ====== */

    public function get_items_permissions_check( $request ) {
        // Minimal: require any logged-in user
        // Improvement: allow public read if desired, or filter by own notes only
        return is_user_logged_in();
    }

    public function get_item_permissions_check( $request ) {
        // Minimal: require any logged-in user
        // Improvement: restrict to author/admin
        return is_user_logged_in();
    }

    public function create_item_permissions_check( $request ) {
        // Minimal: reuse default WP capability
        // Improvement: custom 'create_notes' capability
        return current_user_can( 'edit_posts' );
    }

    public function update_item_permissions_check( $request ) {
        // Minimal: allow anyone who can edit_posts
        // Improvement: only author/admin OR custom 'edit_notes'
        return current_user_can( 'edit_posts' );
    }

    public function delete_item_permissions_check( $request ) {
        // Minimal: allow anyone who can delete_posts
        // Improvement: only author/admin OR custom 'delete_notes'
        return current_user_can( 'delete_posts' );
    }
}
