<?php
/**
 * Atom: WordPress TinyMCE Editor
 *
 * Expects:
 * - $context['name']    → field name (e.g., note_content)
 * - $context['content'] → initial content (default empty)
 * - $context['settings'] → wp_editor settings array (optional)
 */

wp_editor(
    $context['content'] ?? '',
    $context['name'], // This becomes the HTML id
    $context['settings'] ?? [
        'textarea_name' => $context['name'],   // Required to save correctly
        'textarea_rows' => 10,
        'media_buttons' => false,
        'teeny'         => true,   // Minimal toolbar
    ]
);
