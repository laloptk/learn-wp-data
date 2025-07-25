<?php
/**
 * Atom: WordPress TinyMCE Editor
 *
 * Expects:
 * - $name    → field name (e.g., note_content)
 * - $content → initial content (default empty)
 * - $settings → wp_editor settings array (optional)
 */

wp_editor(
    $content ?? '',
    $name, // This becomes the HTML id
    $settings ?? [
        'textarea_name' => $name,   // Required to save correctly
        'textarea_rows' => 10,
        'media_buttons' => false,
        'teeny'         => true,   // Minimal toolbar
    ]
);
