<?php 

/*
 * Plugin Name: LearnWPData
 * Text Domain: learn-wp-data
 */

defined('ABSPATH') || exit;

require __DIR__.'/vendor/autoload.php';

use LearnWPData\Plugin;

Plugin::init(__FILE__);

$repo = new \LearnWPData\Notes\NotesRepository();

$repo->insert_note([
    'user_id' => 20, // bad: string + XSS attempt
    'title'   => '   <b>My   Awesome Note!!!</b>   ', // bad: HTML + extra whitespace
    'content' => '<script>alert("XSS")</script><p>This <em>content</em> has <a href="http://evil.com">malicious link</a></p>', // bad: unsafe tags
    'status'  => 'active', // bad: wrong case + extra spaces
]);