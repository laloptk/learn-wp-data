<h2><?php esc_html_e('Existing Notes', 'learnwpdata'); ?></h2>

<table class="widefat striped">
    <thead>
        <tr>
            <th><?php esc_html_e('Title', 'learnwpdata'); ?></th>
            <th><?php esc_html_e('Content', 'learnwpdata'); ?></th>
            <th><?php esc_html_e('Created At', 'learnwpdata'); ?></th>
        </tr>
    </thead>

    <tbody>
        <?php if (empty($notes)) : ?>
            <tr>
                <td colspan="3">
                    <?php esc_html_e('No notes found.', 'learnwpdata'); ?>
                </td>
            </tr>
        <?php else : ?>
            <?php foreach ($notes as $note) : ?>
                <tr>
                    <td><?php echo esc_html($note['title']); ?></td>
                    <td><?php echo esc_html($note['content']); ?></td>
                    <td><?php echo esc_html($note['created_at']); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
