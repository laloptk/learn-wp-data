<input 
    type="text"
    name="<?php echo esc_attr($context['name']); ?>"
    id="<?php echo esc_attr($context['name']); ?>"
    value="<?php echo esc_attr($context['value'] ?? ''); ?>"
    class="regular-text"
    <?php echo !empty($context['required']) ? 'required' : ''; ?>
/>