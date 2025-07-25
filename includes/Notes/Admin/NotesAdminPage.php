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

            $title   = sanitize_text_field($_POST['note_title'] ?? '');
            $content = sanitize_textarea_field($_POST['note_content'] ?? '');

            if ($title && $content) {
                $this->repo->create([
                    'title'   => $title,
                    'content' => $content,
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

