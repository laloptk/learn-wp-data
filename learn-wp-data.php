<?php 

/*
 * Plugin Name: LearnWPData
 * Text Domain: learn-wp-data
 */

defined('ABSPATH') || exit;

require __DIR__.'/vendor/autoload.php';

use LearnWPData\Plugin;
//use LearnWPData\Notes\NotesRepository;

Plugin::init(__FILE__);

/*$repo = new NotesRepository();
$repo->insert(['user_id' => 41, 'title' => 'Test', 'content' => '...', 'status' => 'active']);
$repo->update(8, ['title' => 'Updated']);
$repo->soft_delete(1);
$repo->delete(2);*/