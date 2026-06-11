<?php
/**
 * Plugin Name: GRATIS Builder
 * Description: Free AI-ready page builder extending Gutenberg with patterns, block parsing, and programmatic editing REST API.
 * Version: 1.0.0
 * Author: GRATIS Theme
 * License: GPL-2.0-or-later
 * Requires at least: 6.4
 * Requires PHP: 8.0
 */

defined('ABSPATH') || exit;

define('GRATIS_BUILDER_VERSION', '1.0.0');
define('GRATIS_BUILDER_PATH', plugin_dir_path(__FILE__));

require_once GRATIS_BUILDER_PATH . 'includes/class-rest-api.php';
require_once GRATIS_BUILDER_PATH . 'includes/class-patterns.php';
require_once GRATIS_BUILDER_PATH . 'includes/class-cache-integration.php';

add_action('init', function() {
    // Register block category
    add_filter('block_categories_all', function($categories) {
        array_unshift($categories, [
            'slug' => 'gratis-builder',
            'title' => 'GRATIS Builder',
            'icon' => 'layout',
        ]);
        return $categories;
    });

    // Register patterns
    Gratis_Builder_Patterns::register();
    require_once GRATIS_BUILDER_PATH . "includes/class-directory.php";
    Gratis_Builder_Directory::register();

    // Initialize cache-manager integration (if cache-manager is active)
    if (did_action('gratis_cache_loaded') || class_exists('Gratis\Cache\Plugin')) {
        Gratis_Builder_Cache_Integration::init();
    }
});

add_action('rest_api_init', function() {
    Gratis_Builder_REST_API::register_routes();
});
