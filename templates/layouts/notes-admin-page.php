<div class="wrap">
    <?php if (!empty($context['page_title'])) : ?>
        <h1><?php echo esc_html($context['page_title']); ?></h1>
    <?php endif; ?>

    <?php
    // ✅ Show notices if any
    if (!empty($context['notices'])) {
        learnwpdata_render_template('molecules/notice-list.php', [
            'notices' => $context['notices'],
        ]);
    }
    
    // ✅ Show the form organism
    learnwpdata_render_template('organisms/notes-form-section.php', 
    !empty($context['edit_note']) ? $context['edit_note'] : []
    );

    // ✅ Sanitize $_GET['page'] for hidden field
    $current_page_slug = isset($_GET['page']) ? sanitize_key($_GET['page']) : 'learnwpdata-admin';

    // ✅ Sanitize $context['search'] input (text field)
    $search_value = isset($context['search']) ? sanitize_text_field($context['search']) : '';

    learnwpdata_render_template('molecules/search-box.php', [
        'placeholder' => __('Search notes…', 'learnwpdata'),
        'value'       => $search_value,
        'name'        => 's',
        'submit_text' => __('Search', 'learnwpdata'),
        'hidden'      => [
            'page' => $current_page_slug
        ],
    ]);

    // ✅ Validate $context['notes'] as array fallback
    learnwpdata_render_template('organisms/notes-table-section.php', [
        'notes' => is_array($context['notes'] ?? null) ? $context['notes'] : [],
    ]);

    // ✅ Generate safe base URL
    $base_url = add_query_arg([
        'page' => $current_page_slug,
        's'    => $search_value,
    ], admin_url('admin.php'));

    // ✅ Validate pagination inputs
    $total        = isset($context['total']) ? (int) $context['total'] : 0;
    $per_page     = isset($context['per_page']) ? (int) $context['per_page'] : 10;
    $current_page = isset($context['page']) ? max(1, (int) $context['page']) : 1;

    learnwpdata_render_template('molecules/pagination.php', [
        'total'        => $total,
        'per_page'     => $per_page,
        'current_page' => $current_page,
        'base_url'     => $base_url
    ]);
    ?>
</div>
