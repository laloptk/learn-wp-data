<h2><?php esc_html_e('Existing Notes', 'learnwpdata'); ?></h2>

<table class="widefat striped">
    <thead>
        <tr>
            <th><?php esc_html_e('Title', 'learnwpdata'); ?></th>
            <th><?php esc_html_e('Content', 'learnwpdata'); ?></th>
            <th><?php esc_html_e('Status', 'learnwpdata'); ?></th>
            <th><?php esc_html_e('Created At', 'learnwpdata'); ?></th>
            <th><?php esc_html_e('Actions', 'learnwpdata'); ?></th>
        </tr>
    </thead>

    <tbody>
        <?php if (empty($notes)) : ?>
            <tr>
                <td colspan="5"><?php esc_html_e('No notes found.', 'learnwpdata'); ?></td>
            </tr>
        <?php else : ?>
            <?php foreach ($notes as $note) : 
                $edit_url = add_query_arg([
                    'page' => $_GET['page'],
                    'edit' => $note['id'],
                ], admin_url('admin.php'));

                $delete_url = wp_nonce_url(
                    add_query_arg([
                        'page' => $_GET['page'],
                        'delete' => $note['id'],
                    ], admin_url('admin.php')),
                    'delete_note_' . $note['id']
                );
            ?>
                <tr>
                    <td><?php echo esc_html($note['title']); ?></td>
                    <td><?php echo esc_html(wp_trim_words($note['content'], 15)); ?></td>
                    <td><?php echo esc_html(ucfirst($note['status'] ?? 'draft')); ?></td>
                    <td><?php echo esc_html($note['created_at']); ?></td>
                    <td>
                        <a href="<?php echo esc_url($edit_url); ?>"><?php esc_html_e('Edit', 'learnwpdata'); ?></a> | 
                        <a href="<?php echo esc_url($delete_url); ?>" 
                           onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this note?', 'learnwpdata'); ?>');">
                           <?php esc_html_e('Delete', 'learnwpdata'); ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php if (!empty($pagination_html)) echo $pagination_html; ?>
