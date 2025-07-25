<?php if (!empty($notices)) : ?>
    <?php foreach ($notices as $notice) : ?>
        <?php 
        learnwpdata_render_template('atoms/notice.php', [
            'type'    => $notice['type'] ?? 'notice-info',
            'message' => $notice['message'] ?? '',
        ]); 
        ?>
    <?php endforeach; ?>
<?php endif; ?>
