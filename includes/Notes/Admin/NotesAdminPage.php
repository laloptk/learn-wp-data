<?php
namespace LearnWPData\Notes\Admin;

use LearnWPData\Framework\Admin\BaseAdminPage;
use LearnWPData\Notes\NotesRepository;

defined('ABSPATH') || exit;

/**
 * NotesAdminPage
 * 
 * Concrete admin page for the Notes module.
 */
class NotesAdminPage extends BaseAdminPage {

    protected NotesRepository $repo;

    public function __construct(NotesRepository $repo) {
        $this->menu_slug  = 'learnwpdata-admin';
        $this->menu_title = __('LearnWPData', 'learnwpdata');
        $this->icon       = 'dashicons-database';
        $this->repo       = $repo;
        parent::__construct();
    }

    public function render_page(): void {
        $notices = [];

        // ✅ Handle deletion with nonce check
        if (
            isset($_GET['delete'], $_GET['_wpnonce']) &&
            current_user_can('manage_options')
        ) {
            $delete_id = absint($_GET['delete']);
            if (!wp_verify_nonce($_GET['_wpnonce'], 'learnwpdata_delete_note_' . $delete_id)) {
                $notices[] = [
                    'type'    => 'notice-error',
                    'message' => __('Invalid nonce. Could not delete note.', 'learnwpdata'),
                ];
            } elseif ($delete_id > 0) {
                $this->repo->delete($delete_id);
                $notices[] = [
                    'type'    => 'notice-success',
                    'message' => __('Note deleted successfully.', 'learnwpdata'),
                ];
            }
        }

        // ✅ Handle create/update
        $notices = array_merge($notices, $this->handle_form_submission());

        // ✅ Filters from query
        $search   = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $page     = isset($_GET['paged']) ? max(1, (int) $_GET['paged']) : 1;
        $per_page = 10;

        // ✅ Fetch notes
        $notes = $this->repo->all([
            'search'   => $search,
            'page'     => $page,
            'per_page' => $per_page,
        ]);

        $total = $this->repo->count_all([
            'search' => $search,
        ]);

        $edit_note = null;
        if (isset($_GET['edit'])) {
            $edit_note_id = (int) $_GET['edit'];
            $edit_note = $this->repo->read($edit_note_id);
        }

        // ✅ Render layout
        learnwpdata_render_template('layouts/notes-admin-page.php', [
            'page_title'      => __('LearnWPData Notes', 'learnwpdata'),
            'notices'         => $notices,
            'notes'           => $notes,
            'search'          => $search,
            'total'           => $total,
            'per_page'        => $per_page,
            'page'            => $page,
            'edit_note'       => $edit_note,
        ]);
    }

    protected function enqueue_page_assets(): void {
        // Enqueue WP's TinyMCE + styles
        wp_enqueue_editor();
    }

    private function handle_form_submission() {
        $notices = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['learnwpdata_notes_nonce'])) {

            if (!wp_verify_nonce($_POST['learnwpdata_notes_nonce'], 'learnwpdata_save_note')) {
                $notices[] = [
                    'type'    => 'notice-error',
                    'message' => __('Security check failed.', 'learnwpdata'),
                ];
                return $notices;
            }

            // The repo is going to sanitize the $_POST values
            if ($_POST['note_title'] && $_POST['note_content']) {
                $note_values = [
                    'title'   => $_POST['note_title'],
                    'content' => $_POST['note_content'],
                    'status'  => $_POST['note_status'],
                ];

                $note_id = isset($_POST['note_id']) ? absint($_POST['note_id']) : 0;
                
                if($note_id === 0) {
                    $this->repo->insert($note_values);
                } else {
                    $this->repo->update($note_id, $note_values);
                }

                $notices[] = [
                    'type'    => 'notice-success',
                    'message' => __('Note saved successfully!', 'learnwpdata'),
                ];
            } else {
                $notices[] = [
                    'type'    => 'notice-error',
                    'message' => __('Both title and content are required.', 'learnwpdata'),
                ];
            }
        }

        return $notices;
    }
}

