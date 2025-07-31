<select 
    name="<?php echo esc_attr($context['name']); ?>" 
    id="<?php echo esc_attr($context['name']); ?>"
>
    <?php foreach ($context['options'] as $value => $label) : ?>
        <option value="<?php echo esc_attr($value); ?>" 
            <?php selected($value, $context['selected'] ?? ''); ?>>
            <?php echo esc_html($label); ?>
        </option>
    <?php endforeach; ?>
</select>
