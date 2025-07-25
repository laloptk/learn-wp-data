<tr>
    <th scope="row">
        <?php if (!empty($label)) : ?>
            <label for="<?php echo esc_attr($name ?? ''); ?>">
                <?php echo esc_html($label); ?>
            </label>
        <?php endif; ?>
    </th>
    <td>
        <?php 
        // Render the inner atom
        if (!empty($atom_template)) {
            learnwpdata_render_template($atom_template, $atom_vars ?? []);
        }
        ?>
        <?php if (!empty($description)) : ?>
            <p class="description"><?php echo esc_html($description); ?></p>
        <?php endif; ?>
    </td>
</tr>