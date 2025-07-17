<?php 

/*
 * Plugin Name: LearnWPData
 * Text Domain: learn-wp-data
 */

defined('ABSPATH') || exit;

require __DIR__.'/vendor/autoload.php';

use LearnWPData\Plugin;

Plugin::init(__FILE__);