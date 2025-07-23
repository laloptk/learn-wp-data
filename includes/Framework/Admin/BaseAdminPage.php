<?php
namespace LearnWPData\Framework\Admin;

defined('ABSPATH') || exit;

/**
 * BaseAdminPage
 * 
 * Abstract base class for admin pages.
 * 
 * - Handles menu registration
 * - Handles enqueue logic
 * - Allows concrete classes to define their own rendering & assets
 */
abstract class BaseAdminPage {

    protected string $menu_slug;
    protected string $menu_title;
    protected string $capability = 'manage_options';
    protected string $icon = 'dashicons-admin-generic';
    protected int $position = 25;

    public function __construct() {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    abstract public function render_page(): void;

    protected function enqueue_page_assets(): void {
        // Default empty; override in child classes
    }

    public function register_menu(): void {
        add_menu_page(
            $this->menu_title,
            $this->menu_title,
            $this->capability,
            $this->menu_slug,
            [$this, 'render_page'],
            $this->icon,
            $this->position
        );
    }

    public function enqueue_assets(string $hook_suffix): void {
        if ($hook_suffix !== 'toplevel_page_' . $this->menu_slug) {
            return;
        }
        $this->enqueue_page_assets();
    }
}
