<textarea 
    name="<?php echo esc_attr($context['name']); ?>" 
    id="<?php echo esc_attr($context['name']); ?>"
    class="large-text"
    rows="<?php echo esc_attr($context['rows'] ?? 5); ?>"
    <?php echo !empty($context['required']) ? 'required' : ''; ?>
><?php echo esc_textarea($context['value'] ?? ''); ?></textarea>
