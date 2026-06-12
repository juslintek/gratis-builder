<?php
defined('ABSPATH') || exit;

/**
 * Integrates gratis-builder with cache-manager.
 * Emits events when builder artifacts change, registers cache namespaces.
 */
class Gratis_Builder_Cache_Integration {

    public static function init(): void {
        // Register builder cache namespaces with cache-manager
        add_action('gratis_cache_register_namespaces', [__CLASS__, 'register_namespaces']);

        // Emit events when patterns/templates are modified via REST API
        add_action('rest_after_insert_wp_block', [__CLASS__, 'on_block_saved'], 10, 2);
        add_action('delete_wp_block', [__CLASS__, 'on_block_deleted']);

        // Hook into file change detection
        add_action('gratis_cache_files_changed', [__CLASS__, 'on_files_changed']);

        // Provide debug metadata
        add_filter('gratis_cache_debug_meta', [__CLASS__, 'add_debug_meta']);
    }

    public static function register_namespaces(array &$namespaces): void {
        $namespaces = array_merge($namespaces, [
            'builder',
            'builder-artifacts',
            'generated-patterns',
            'generated-templates',
            'generated-assets',
            'preview-cache',
        ]);
    }

    public static function on_block_saved(\WP_Post $post, \WP_REST_Request $request): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Required hook signature.
        do_action('gratis_cache_purge_namespace', 'builder');
        do_action('gratis_cache_purge_namespace', 'generated-patterns');
        do_action('gratis_cache_event', 'builder.block_saved', [
            'post_id' => $post->ID,
            'title'   => $post->post_title,
        ]);
    }

    public static function on_block_deleted(int $post_id): void {
        do_action('gratis_cache_purge_namespace', 'builder');
        do_action('gratis_cache_event', 'builder.block_deleted', ['post_id' => $post_id]);
    }

    public static function on_files_changed(array $changed): void {
        $builder_changes = array_filter($changed, fn($path) =>
            str_contains($path, 'gratis-builder/patterns') ||
            str_contains($path, 'gratis-builder/includes')
        );

        if (!empty($builder_changes)) {
            do_action('gratis_cache_purge_namespace', 'builder');
            do_action('gratis_cache_purge_namespace', 'generated-patterns');
            do_action('gratis_cache_event', 'builder.files_changed', [
                'count' => count($builder_changes),
            ]);
        }
    }

    public static function add_debug_meta(array $meta): array {
        $patterns_dir = GRATIS_BUILDER_PATH . 'patterns/';
        $pattern_count = is_dir($patterns_dir) ? count(glob($patterns_dir . '*.php')) : 0;

        $meta['builder'] = [
            'version'        => GRATIS_BUILDER_VERSION,
            'patterns_dir'   => $pattern_count . ' pattern files',
            'reusable_blocks' => wp_count_posts('wp_block')->publish ?? 0,
        ];
        return $meta;
    }
}
