<input 
    type="text"
    name="<?php echo esc_attr($name); ?>"
    id="<?php echo esc_attr($name); ?>"
    value="<?php echo esc_attr($value ?? ''); ?>"
    class="regular-text"
    <?php echo !empty($required) ? 'required' : ''; ?>
/>