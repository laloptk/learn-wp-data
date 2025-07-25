<div class="wrap">
    <?php if (!empty($page_title)) : ?>
        <h1><?php echo esc_html($page_title); ?></h1>
    <?php endif; ?>

    <?php
    // Show notices if any
    if (!empty($notices)) {
        learnwpdata_render_template('molecules/notice-list.php', [
            'notices' => $notices,
        ]);
    }

    // Show the form organism
    learnwpdata_render_template('organisms/notes-form-section.php', []);

    learnwpdata_render_template('molecules/search-box.php', [
        'placeholder' => __('Search notes…', 'learnwpdata'),
        'value'       => $search ?? '',
        'name'        => 's',
        'submit_text' => __('Search', 'learnwpdata'),
        'hidden'      => [
            'page' => $_GET['page'] ?? 'learnwpdata-admin'
        ],
    ]);

    // Show the table organism
    learnwpdata_render_template('organisms/notes-table-section.php', [
        'notes' => $notes ?? [],
    ]);
    ?>
</div>
