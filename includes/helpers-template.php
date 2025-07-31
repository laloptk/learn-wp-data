<?php 

function learnwpdata_render_template($template_path, $context) {
    include LEARNWPDATA_PLUGIN_DIR . '/templates/' . $template_path;
}