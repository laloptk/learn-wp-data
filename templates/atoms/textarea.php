<textarea 
    name="<?php echo esc_attr($name); ?>" 
    id="<?php echo esc_attr($name); ?>"
    class="large-text"
    rows="<?php echo esc_attr($rows ?? 5); ?>"
    <?php echo !empty($required) ? 'required' : ''; ?>
><?php echo esc_textarea($value ?? ''); ?></textarea>
