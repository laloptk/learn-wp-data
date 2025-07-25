<h2><?php esc_html_e('Add New Note', 'learnwpdata'); ?></h2>

<form method="post">
    <?php wp_nonce_field('learnwpdata_save_note', 'learnwpdata_notes_nonce'); ?>

    <table class="form-table">
        <?php
        // Title row
        learnwpdata_render_template('molecules/form-row.php', [
            'name' => 'note_title',
            'label' => __('Title', 'learnwpdata'),
            'description' => __('Enter a short descriptive title for your note.', 'learnwpdata'),
            'atom_template' => 'atoms/input-text.php',
            'atom_vars' => [
                'name' => 'note_title',
                'required' => true,
            ],
        ]);

        // Content row
        learnwpdata_render_template('molecules/form-row.php', [
            'name' => 'note_content',
            'label' => __('Content', 'learnwpdata'),
            'description' => __('Write the content of your note.', 'learnwpdata'),
            'atom_template' => 'atoms/textarea.php',
            'atom_vars' => [
                'name' => 'note_content',
                'rows' => 5,
                'required' => true,
            ],
        ]);

        // Status row
        learnwpdata_render_template('molecules/form-row.php', [
            'name'         => 'note_status',
            'label'        => __('Status', 'learnwpdata'),
            'description'  => __('Choose the note status.', 'learnwpdata'),
            'atom_template'=> 'atoms/select.php',
            'atom_vars'    => [
                'name'     => 'note_status',
                'options'  => [
                    'draft'     => __('Draft', 'learnwpdata'),
                    'published' => __('Published', 'learnwpdata'),
                    'archived'  => __('Archived', 'learnwpdata'),
                    'active'    => __('Active', 'learnwpdata'),
                ],
                'selected' => 'draft',
            ],
        ]);
        ?>

    </table>

    <?php learnwpdata_render_template('atoms/submit-button.php', [
        'label' => __('Save Note', 'learnwpdata'),
    ]); ?>
</form>

<hr>
