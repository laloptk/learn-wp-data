<tr>
    <th scope="row">
        <?php if (!empty($context['label'])) : ?>
            <label for="<?php echo esc_attr($context['name'] ?? ''); ?>">
                <?php echo esc_html($context['label']); ?>
            </label>
        <?php endif; ?>
    </th>
    <td>
        <?php 
        // Render the inner atom
        if (!empty($context['atom_template'])) {
            learnwpdata_render_template($context['atom_template'], $context['atom_vars'] ?? []);
        }
        ?>
        <?php if (!empty($context['description'])) : ?>
            <p class="description"><?php echo esc_html($context['description']); ?></p>
        <?php endif; ?>
    </td>
</tr>