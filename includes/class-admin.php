<?php
/**
 * Admin UI for GRATIS Builder.
 *
 * Provides:
 *  - A pattern browser with sandboxed iframe previews.
 *  - Pattern category management (custom categories stored in an option and
 *    merged into the registered pattern categories).
 *  - AI generation settings (per-user rate limit + window).
 *
 * @package Gratis\Builder
 */

defined('ABSPATH') || exit;

class Gratis_Builder_Admin {

    /** Option storing custom pattern categories as [ slug => label ]. */
    const CATEGORIES_OPTION = 'gratis_builder_pattern_categories';

    /** Settings group/page slug. */
    const PAGE = 'gratis-builder';

    /**
     * Wire up admin hooks.
     */
    public static function init(): void {
        add_action('admin_menu', [__CLASS__, 'register_menu']);
        add_action('admin_init', [__CLASS__, 'register_settings']);
        add_action('admin_post_gratis_builder_save_category', [__CLASS__, 'handle_save_category']);
        add_action('admin_post_gratis_builder_delete_category', [__CLASS__, 'handle_delete_category']);
        add_action('admin_post_gratis_builder_preview', [__CLASS__, 'render_preview']);
    }

    /**
     * Custom categories defined by the site admin.
     *
     * @return array<string,string> slug => label
     */
    public static function get_custom_categories(): array {
        $stored = get_option(self::CATEGORIES_OPTION, []);
        return is_array($stored) ? $stored : [];
    }

    /**
     * Register the admin menu and submenus.
     */
    public static function register_menu(): void {
        add_menu_page(
            __('GRATIS Builder', 'gratis-builder'),
            __('GRATIS Builder', 'gratis-builder'),
            'manage_options',
            self::PAGE,
            [__CLASS__, 'render_patterns_page'],
            'dashicons-layout',
            58
        );

        add_submenu_page(
            self::PAGE,
            __('Patterns', 'gratis-builder'),
            __('Patterns', 'gratis-builder'),
            'manage_options',
            self::PAGE,
            [__CLASS__, 'render_patterns_page']
        );

        add_submenu_page(
            self::PAGE,
            __('Pattern Categories', 'gratis-builder'),
            __('Categories', 'gratis-builder'),
            'manage_options',
            'gratis-builder-categories',
            [__CLASS__, 'render_categories_page']
        );

        add_submenu_page(
            self::PAGE,
            __('AI Settings', 'gratis-builder'),
            __('AI Settings', 'gratis-builder'),
            'manage_options',
            'gratis-builder-settings',
            [__CLASS__, 'render_settings_page']
        );
    }

    /**
     * Register AI rate-limit settings via the Settings API.
     */
    public static function register_settings(): void {
        register_setting(
            'gratis_builder_ai',
            Gratis_Builder_AI_Generator::RATE_LIMIT_OPTION,
            [
                'type'              => 'integer',
                'sanitize_callback' => 'absint',
                'default'           => Gratis_Builder_AI_Generator::DEFAULT_LIMIT,
            ]
        );

        register_setting(
            'gratis_builder_ai',
            Gratis_Builder_AI_Generator::RATE_WINDOW_OPTION,
            [
                'type'              => 'integer',
                'sanitize_callback' => 'absint',
                'default'           => Gratis_Builder_AI_Generator::DEFAULT_WINDOW,
            ]
        );
    }

    /* ------------------------------------------------------------------ *
     * Pattern browser + sandboxed preview
     * ------------------------------------------------------------------ */

    /**
     * Render the pattern browser with sandboxed iframe previews.
     */
    public static function render_patterns_page(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'gratis-builder'));
        }

        $registry = WP_Block_Patterns_Registry::get_instance();
        $patterns = [];
        foreach ($registry->get_all_registered() as $pattern) {
            if (0 === strpos((string) ($pattern['name'] ?? ''), 'gratis-builder/')) {
                $patterns[] = $pattern;
            }
        }

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('GRATIS Builder Patterns', 'gratis-builder') . '</h1>';
        echo '<p>' . esc_html(
            sprintf(
                /* translators: %d: number of registered patterns. */
                _n('%d pattern registered.', '%d patterns registered.', count($patterns), 'gratis-builder'),
                count($patterns)
            )
        ) . '</p>';

        if (empty($patterns)) {
            echo '<p>' . esc_html__('No GRATIS Builder patterns are registered yet.', 'gratis-builder') . '</p>';
            echo '</div>';
            return;
        }

        echo '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:20px;margin-top:20px;">';
        foreach ($patterns as $pattern) {
            $slug        = str_replace('gratis-builder/', '', (string) $pattern['name']);
            $preview_url = add_query_arg(
                [
                    'action'   => 'gratis_builder_preview',
                    'pattern'  => $slug,
                    '_wpnonce' => wp_create_nonce('gratis_builder_preview_' . $slug),
                ],
                admin_url('admin-post.php')
            );

            echo '<div style="border:1px solid #c3c4c7;border-radius:6px;overflow:hidden;background:#fff;">';
            echo '<div style="padding:10px 14px;border-bottom:1px solid #e0e0e0;">';
            echo '<strong>' . esc_html((string) ($pattern['title'] ?? $slug)) . '</strong>';
            if (!empty($pattern['description'])) {
                echo '<div style="color:#646970;font-size:12px;margin-top:2px;">' . esc_html((string) $pattern['description']) . '</div>';
            }
            echo '</div>';
            // Sandboxed iframe — scripts disabled, same-origin only for styles.
            echo '<iframe src="' . esc_url($preview_url) . '" sandbox="allow-same-origin" loading="lazy" title="' . esc_attr((string) ($pattern['title'] ?? $slug)) . '" style="width:100%;height:300px;border:0;background:#fff;"></iframe>';
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';
    }

    /**
     * Render a single pattern inside a minimal standalone document, served to
     * the sandboxed iframe.
     */
    public static function render_preview(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to preview patterns.', 'gratis-builder'));
        }

        $slug  = isset($_GET['pattern']) ? sanitize_key(wp_unslash($_GET['pattern'])) : '';
        $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';

        if ('' === $slug || !wp_verify_nonce($nonce, 'gratis_builder_preview_' . $slug)) {
            wp_die(esc_html__('Invalid preview request.', 'gratis-builder'));
        }

        $registry = WP_Block_Patterns_Registry::get_instance();
        $pattern  = $registry->get_registered('gratis-builder/' . $slug);
        if (!$pattern) {
            wp_die(esc_html__('Pattern not found.', 'gratis-builder'));
        }

        $rendered = do_blocks((string) $pattern['content']);

        header('Content-Type: text/html; charset=utf-8');
        header('X-Frame-Options: SAMEORIGIN');

        echo '<!DOCTYPE html><html><head><meta charset="utf-8">';
        echo '<meta name="viewport" content="width=device-width,initial-scale=1">';
        echo '<style>body{margin:0;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;line-height:1.5;color:#1e1e1e;}img{max-width:100%;height:auto;}</style>';
        echo '</head><body>';
        echo wp_kses_post($rendered);
        echo '</body></html>';
        exit;
    }

    /* ------------------------------------------------------------------ *
     * Category management
     * ------------------------------------------------------------------ */

    /**
     * Render the category management screen.
     */
    public static function render_categories_page(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'gratis-builder'));
        }

        $custom = self::get_custom_categories();

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Pattern Categories', 'gratis-builder') . '</h1>';

        if (isset($_GET['updated'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Categories updated.', 'gratis-builder') . '</p></div>';
        }

        echo '<h2>' . esc_html__('Custom Categories', 'gratis-builder') . '</h2>';
        if (empty($custom)) {
            echo '<p>' . esc_html__('No custom categories yet. Add one below.', 'gratis-builder') . '</p>';
        } else {
            echo '<table class="widefat striped" style="max-width:600px;">';
            echo '<thead><tr><th>' . esc_html__('Label', 'gratis-builder') . '</th><th>' . esc_html__('Slug', 'gratis-builder') . '</th><th></th></tr></thead><tbody>';
            foreach ($custom as $slug => $label) {
                echo '<tr>';
                echo '<td>' . esc_html((string) $label) . '</td>';
                echo '<td><code>' . esc_html((string) $slug) . '</code></td>';
                echo '<td>';
                echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="margin:0;">';
                echo '<input type="hidden" name="action" value="gratis_builder_delete_category">';
                echo '<input type="hidden" name="slug" value="' . esc_attr((string) $slug) . '">';
                wp_nonce_field('gratis_builder_delete_category');
                echo '<button type="submit" class="button button-small button-link-delete">' . esc_html__('Delete', 'gratis-builder') . '</button>';
                echo '</form>';
                echo '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        }

        echo '<h2 style="margin-top:24px;">' . esc_html__('Add Category', 'gratis-builder') . '</h2>';
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        echo '<input type="hidden" name="action" value="gratis_builder_save_category">';
        wp_nonce_field('gratis_builder_save_category');
        echo '<table class="form-table"><tbody>';
        echo '<tr><th scope="row"><label for="gb-cat-label">' . esc_html__('Label', 'gratis-builder') . '</label></th>';
        echo '<td><input name="label" id="gb-cat-label" type="text" class="regular-text" required></td></tr>';
        echo '<tr><th scope="row"><label for="gb-cat-slug">' . esc_html__('Slug', 'gratis-builder') . '</label></th>';
        echo '<td><input name="slug" id="gb-cat-slug" type="text" class="regular-text" placeholder="' . esc_attr__('auto-generated from label if blank', 'gratis-builder') . '"></td></tr>';
        echo '</tbody></table>';
        submit_button(__('Add Category', 'gratis-builder'));
        echo '</form>';
        echo '</div>';
    }

    /**
     * Persist a new (or updated) custom category.
     */
    public static function handle_save_category(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Permission denied.', 'gratis-builder'));
        }
        check_admin_referer('gratis_builder_save_category');

        $label = isset($_POST['label']) ? sanitize_text_field(wp_unslash($_POST['label'])) : '';
        $slug  = isset($_POST['slug']) ? sanitize_key(wp_unslash($_POST['slug'])) : '';

        if ('' === $slug && '' !== $label) {
            $slug = sanitize_key($label);
        }

        if ('' !== $slug && '' !== $label) {
            $custom        = self::get_custom_categories();
            $custom[$slug] = $label;
            update_option(self::CATEGORIES_OPTION, $custom);
        }

        wp_safe_redirect(add_query_arg('updated', '1', admin_url('admin.php?page=gratis-builder-categories')));
        exit;
    }

    /**
     * Delete a custom category.
     */
    public static function handle_delete_category(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Permission denied.', 'gratis-builder'));
        }
        check_admin_referer('gratis_builder_delete_category');

        $slug = isset($_POST['slug']) ? sanitize_key(wp_unslash($_POST['slug'])) : '';
        if ('' !== $slug) {
            $custom = self::get_custom_categories();
            unset($custom[$slug]);
            update_option(self::CATEGORIES_OPTION, $custom);
        }

        wp_safe_redirect(add_query_arg('updated', '1', admin_url('admin.php?page=gratis-builder-categories')));
        exit;
    }

    /* ------------------------------------------------------------------ *
     * AI settings
     * ------------------------------------------------------------------ */

    /**
     * Render the AI generation settings screen.
     */
    public static function render_settings_page(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'gratis-builder'));
        }

        $limit  = (int) get_option(Gratis_Builder_AI_Generator::RATE_LIMIT_OPTION, Gratis_Builder_AI_Generator::DEFAULT_LIMIT);
        $window = (int) get_option(Gratis_Builder_AI_Generator::RATE_WINDOW_OPTION, Gratis_Builder_AI_Generator::DEFAULT_WINDOW);

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('GRATIS Builder — AI Settings', 'gratis-builder') . '</h1>';
        echo '<p>' . esc_html__('Configure rate limiting for the AI layout generation endpoint (POST /wp-json/gratis-builder/v1/generate).', 'gratis-builder') . '</p>';
        echo '<form method="post" action="options.php">';
        settings_fields('gratis_builder_ai');
        echo '<table class="form-table"><tbody>';
        echo '<tr><th scope="row"><label for="gb-rate-limit">' . esc_html__('Requests per window', 'gratis-builder') . '</label></th>';
        echo '<td><input name="' . esc_attr(Gratis_Builder_AI_Generator::RATE_LIMIT_OPTION) . '" id="gb-rate-limit" type="number" min="0" value="' . esc_attr((string) $limit) . '" class="small-text"> ';
        echo '<span class="description">' . esc_html__('Maximum AI generations per user per window. Set to 0 for unlimited.', 'gratis-builder') . '</span></td></tr>';
        echo '<tr><th scope="row"><label for="gb-rate-window">' . esc_html__('Window (seconds)', 'gratis-builder') . '</label></th>';
        echo '<td><input name="' . esc_attr(Gratis_Builder_AI_Generator::RATE_WINDOW_OPTION) . '" id="gb-rate-window" type="number" min="1" value="' . esc_attr((string) $window) . '" class="small-text"> ';
        echo '<span class="description">' . esc_html__('Length of the rate-limit window in seconds (3600 = 1 hour).', 'gratis-builder') . '</span></td></tr>';
        echo '</tbody></table>';
        submit_button();
        echo '</form>';
        echo '</div>';
    }
}
