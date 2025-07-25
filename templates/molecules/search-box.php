<?php
/**
 * Molecule: Search Box
 *
 * Uses:
 * - input-text atom
 * - submit-button atom
 *
 * Expects:
 * - $placeholder → placeholder text
 * - $value       → current search query
 * - $name        → input name (default 's')
 * - $submit_text → button text
 * - $hidden      → optional array of hidden fields (e.g. ['page' => 'learnwpdata-admin'])
 */
?>

<form method="get" class="notes-search-box">
    <?php if (!empty($hidden) && is_array($hidden)) : ?>
        <?php foreach ($hidden as $key => $val) : ?>
            <input type="hidden" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($val); ?>">
        <?php endforeach; ?>
    <?php endif; ?>

    <label class="screen-reader-text" for="search-input">
        <?php echo esc_html($placeholder ?? __('Search', 'learnwpdata')); ?>
    </label>

    <?php learnwpdata_render_template('atoms/input-text.php', [
        'name'        => $name ?? 's',
        'value'       => $value ?? '',
        'placeholder' => $placeholder ?? __('Search notes…', 'learnwpdata'),
        'class'       => 'regular-text'
    ]); ?>

    <?php learnwpdata_render_template('atoms/submit-button.php', [
        'label' => $submit_text ?? __('Search', 'learnwpdata'),
        'class' => 'button'
    ]); ?>
</form>
