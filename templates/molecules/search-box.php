<?php
/**
 * Molecule: Search Box
 *
 * Uses:
 * - input-text atom
 * - submit-button atom
 *
 * Expects:
 * - $context['placeholder'] → placeholder text
 * - $context['value']       → current search query
 * - $context['name']        → input name (default 's')
 * - $context['submit_text'] → button text
 * - $context['hidden']      → optional array of hidden fields (e.g. ['page' => 'learnwpdata-admin'])
 */

$placeholder = $context['placeholder'] ?? __('Search', 'learnwpdata');
$value       = $context['value'] ?? '';
$name        = $context['name'] ?? 's';
$submit_text = $context['submit_text'] ?? __('Search', 'learnwpdata');
$hidden      = $context['hidden'] ?? [];
?>

<form method="get" class="notes-search-box">
    <?php if (!empty($hidden) && is_array($hidden)) : ?>
        <?php foreach ($hidden as $key => $val) : ?>
            <input type="hidden" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($val); ?>">
        <?php endforeach; ?>
    <?php endif; ?>

    <label class="screen-reader-text" for="search-input">
        <?php echo esc_html($placeholder); ?>
    </label>

    <?php learnwpdata_render_template('atoms/input-text.php', [
        'name'        => $name,
        'value'       => $value,
        'placeholder' => $placeholder,
        'class'       => 'regular-text'
    ]); ?>

    <?php learnwpdata_render_template('atoms/submit-button.php', [
        'label' => $submit_text,
        'class' => 'button'
    ]); ?>
</form>
