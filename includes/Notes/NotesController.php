<?php 

namespace LearnWPData\Notes;

use LearnWPData\Notes\NotesRepository;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class NotesController extends WP_REST_Controller {
    public function __construct( NotesRepository $repo ) {
        $this->namespace = 'learnwpdata/v1';
        $this->rest_base = 'notes';
        $this->repo = $repo;
    }

    public function register_routes() {
        register_rest_route(
            $this->namespace, 
            '/' . $this->rest_base,
             [
                [
                    'methods'             => 'GET',
                    'callback'            => [ $this, 'get_items' ],
                    'permission_callback' => [ $this, 'get_items_permissions_check' ],
                    'args'                => $this->get_collection_params(), // optional for pagination etc.
                ],
                [
                    'methods'             => 'POST',
                    'callback'            => [ $this, 'create_item' ],
                    'permission_callback' => [ $this, 'create_item_permissions_check' ],
                    'args'                => $this->get_endpoint_args_for_item_schema( true ), 
                ],
            ]
        );

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
                    'args'                => $this->get_endpoint_args_for_item_schema( false ),
                ],
                [
                    'methods'             => 'DELETE',
                    'callback'            => [ $this, 'delete_item' ],
                    'permission_callback' => [ $this, 'delete_item_permissions_check' ],
                ],
            ]
        );
    }

    public function get_item($request) {
        $id = (int) $request->get_param( 'id' );
        $item = $this->repo->read( $id );

        if ( ! $item ) {
            return new \WP_Error(
                'rest_note_not_found',
                __( 'Note not found.' ),
                [ 'status' => 404 ]
            );
        }

        $response = $this->prepare_item_for_response( $item, $request );

        return rest_ensure_response( $response );
    }

    public function update_item( $request ) {
        $id = (int) $request->get_param( 'id' );

        // 1. Check if the note exists
        $existing = $this->repo->read( $id );
        if ( ! $existing ) {
            return new \WP_Error(
                'rest_note_not_found',
                __( 'Note not found.' ),
                [ 'status' => 404 ]
            );
        }

        // 2. Merge existing + new values
        $data = [
            'title'   => $request->get_param( 'title' )   ?? $existing->title,
            'content' => $request->get_param( 'content' ) ?? $existing->content,
            'status'  => $request->get_param( 'status' )  ?? $existing->status,
        ];

        // 3. Sanitize each field
        $data['title']   = sanitize_text_field( $data['title'] );
        $data['content'] = sanitize_textarea_field( $data['content'] );
        $data['status']  = sanitize_key( $data['status'] ); // valid key-like strings

        // 4. Optional: validate status against allowed values
        $allowed_statuses = [ 'draft', 'published', 'archived' ];
        if ( ! in_array( $data['status'], $allowed_statuses, true ) ) {
            return new \WP_Error(
                'rest_invalid_status',
                __( 'Invalid status provided.' ),
                [ 'status' => 400 ]
            );
        }

        // 5. Update in DB
        $updated = $this->repo->update( $id, $data );

        if ( ! $updated ) {
            return new \WP_Error(
                'rest_note_update_failed',
                __( 'Failed to update note.' ),
                [ 'status' => 400 ]
            );
        }

        // 6. Fetch updated note
        $note = $this->repo->read( $id );

        // 7. Format + return
        $response = $this->prepare_item_for_response( $note, $request );
        return rest_ensure_response( $response );
    }


    public function prepare_item_for_response( $item, $request ) {
        $data = [];

        foreach($item as $key => $val) {
            $data = [];
            
            switch ( $key ) {
                case 'id':
                    $data['id'] = (int) $val;
                    break;

                case 'user_id':
                    $data['author'] = (int) $val; // rename for API clarity
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
                    $data['created_at'] = mysql_to_rfc3339( $val );
                    break;

                case 'updated_at':
                    $data['updated_at'] = mysql_to_rfc3339( $val );
                    break;

                default:
                    // Ignore unexpected keys
                    break;
            }
        }

        // Add HATEOAS _links section
        $data['_links'] = [
            'self' => [
                [
                    'href' => rest_url( sprintf(
                        '%s/%s/%d',
                        $this->namespace,
                        $this->rest_base,
                        $item['id']
                    ) )
                ]
            ],
            'collection' => [
                [
                    'href' => rest_url( sprintf(
                        '%s/%s',
                        $this->namespace,
                        $this->rest_base
                    ) )
                ]
            ],
            'author' => [
                [
                    'href' => rest_url( 'wp/v2/users/' . $item['user_id'] )
                ]
            ],
        ];

        return $data;
    }
}

