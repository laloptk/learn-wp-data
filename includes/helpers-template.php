<?php 

function learnwpdata_render_template($template_path, $vars) {
    extract($vars);
    include LEARNWPDATA_PLUGIN_DIR . '/templates/' . $template_path;
}