<h2><?php esc_html_e('Add New Note', 'learnwpdata'); ?></h2>

<form method="post" action="<?php echo esc_url(admin_url('admin.php?page=learnwpdata-admin')); ?>">
    <?php wp_nonce_field('learnwpdata_save_note', 'learnwpdata_notes_nonce'); ?>
    
    <input type="hidden" name="note_id" value="<?php echo esc_attr($context['id'] ?? 0); ?>">

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
                'value' => isset($context['title']) ? $context['title'] : '',
                'required' => true,
            ],
        ]);

        // Content row with TinyMCE
        learnwpdata_render_template('molecules/form-row.php', [
            'name'         => 'note_content',
            'label'        => __('Content', 'learnwpdata'),
            'description'  => __('Write the content of your note using the rich editor.', 'learnwpdata'),
            'atom_template'=> 'atoms/editor.php',
            'atom_vars'    => [
                'name'    => 'note_content',
                'content' => isset($context['content']) ? $context['content'] : '', // Prefill if editing
                'settings'=> [
                    'textarea_name' => 'note_content',
                    'textarea_rows' => 10,
                    'media_buttons' => false,
                    'teeny'         => true,
                ],
            ],
        ]);

        // Status row
        $selected_status = isset($context['status']) ? $context['status'] : 'draft';
        learnwpdata_render_template('molecules/form-row.php', [
            'name'         => 'note_status',
            'label'        => __('Status', 'learnwpdata'),
            'description'  => __('Choose the note status.', 'learnwpdata'),
            'atom_template'=> 'atoms/select.php',
            'atom_vars'    => [
                'name'     => 'note_status',
                'options'  => [
                    'draft'     => __('Draft', 'learnwpdata'),
                    'archived'  => __('Archived', 'learnwpdata'),
                    'active'    => __('Active', 'learnwpdata'),
                ],
                'selected' => $selected_status,
            ],
        ]);
        ?>

    </table>

    <?php learnwpdata_render_template('atoms/submit-button.php', [
        'label' => __('Save Note', 'learnwpdata'),
    ]); ?>
</form>

<hr>
