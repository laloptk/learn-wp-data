<select 
    name="<?php echo esc_attr($name); ?>" 
    id="<?php echo esc_attr($name); ?>"
>
    <?php foreach ($options as $value => $label) : ?>
        <option value="<?php echo esc_attr($value); ?>" 
            <?php selected($value, $selected ?? ''); ?>>
            <?php echo esc_html($label); ?>
        </option>
    <?php endforeach; ?>
</select>
