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
        $notices = $this->handle_form_submission();
        $notes   = $this->repo->all();

        learnwpdata_render_template(
            'layouts/notes-admin-page.php', 
            [
                'page_title' => __('LearnWPData Notes', 'learnwpdata'),
                'notices'    => $notices,
                'notes'      => $notes,
            ]
        );
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

            var_dump($_POST['note_status']);

            // The repo is going to sanitize the $_POST values
            if ($_POST['note_title'] && $_POST['note_content']) {
                $this->repo->insert([
                    'title'   => $_POST['note_title'],
                    'content' => $_POST['note_content'],
                    'status'  => $_POST['note_status'],
                ]);

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

