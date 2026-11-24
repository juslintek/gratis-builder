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
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_styles']);
        add_action('enqueue_block_editor_assets', [__CLASS__, 'enqueue_editor_sidebar']);
    }

    /**
     * Enqueue the Gutenberg sidebar plugin script on the block editor.
     *
     * Registered via `enqueue_block_editor_assets`.  Passes all
     * gratis-builder/* patterns to the JS via wp_localize_script so the
     * sidebar can render cards and insert content without an extra REST call.
     */
    public static function enqueue_editor_sidebar(): void {
        if (!current_user_can('edit_posts')) {
            return;
        }

        wp_enqueue_script(
            'gratis-builder-sidebar',
            GRATIS_BUILDER_URL . 'assets/js/editor-sidebar.js',
            ['wp-plugins', 'wp-editor', 'wp-element', 'wp-blocks', 'wp-data', 'wp-i18n'],
            GRATIS_BUILDER_VERSION,
            true
        );

        // Collect all registered gratis-builder patterns with their content.
        $registry = WP_Block_Patterns_Registry::get_instance();
        $patterns = [];
        foreach ($registry->get_all_registered() as $pattern) {
            if (0 === strpos((string) ($pattern['name'] ?? ''), 'gratis-builder/')) {
                $patterns[] = [
                    'slug'    => str_replace('gratis-builder/', '', (string) $pattern['name']),
                    'title'   => (string) ($pattern['title'] ?? ''),
                    'content' => (string) ($pattern['content'] ?? ''),
                ];
            }
        }

        wp_localize_script('gratis-builder-sidebar', 'gratisBuilderPatterns', $patterns);
    }

    /**
     * Enqueue admin-only inline CSS for the pattern browser UI.
     *
     * @param string $hook Current admin page hook suffix.
     */
    public static function enqueue_admin_styles(string $hook): void {
        // Only load on GRATIS Builder pages.
        if (strpos($hook, 'gratis-builder') === false && $hook !== 'toplevel_page_gratis-builder') {
            return;
        }

        // Category → gradient colour map.
        $cat_gradients = [
            'hero'         => 'linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%)',
            'features'     => 'linear-gradient(135deg,#0ea5e9 0%,#06b6d4 100%)',
            'testimonials' => 'linear-gradient(135deg,#f59e0b 0%,#ef4444 100%)',
            'pricing'      => 'linear-gradient(135deg,#10b981 0%,#059669 100%)',
            'faq'          => 'linear-gradient(135deg,#8b5cf6 0%,#ec4899 100%)',
            'cta'          => 'linear-gradient(135deg,#f97316 0%,#ef4444 100%)',
            'team'         => 'linear-gradient(135deg,#14b8a6 0%,#0ea5e9 100%)',
            'contact'      => 'linear-gradient(135deg,#64748b 0%,#334155 100%)',
            'default'      => 'linear-gradient(135deg,#2271b1 0%,#135e96 100%)',
        ];

        $cat_css = '';
        foreach ($cat_gradients as $cat => $gradient) {
            $cat_css .= '.gb-thumb-' . esc_attr($cat) . '{background:' . $gradient . ';}';
        }

        $css = '
/* ── GRATIS Builder: pattern card grid ─────────────────────────────────── */
.gb-pattern-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:20px;margin-top:20px;}
.gb-pattern-card{
    border:1px solid #c3c4c7;
    border-radius:8px;
    overflow:hidden;
    background:#fff;
    box-shadow:0 1px 3px rgba(0,0,0,.07);
    transition:box-shadow 220ms ease,transform 220ms ease;
    display:flex;
    flex-direction:column;
}
.gb-pattern-card:hover{
    box-shadow:0 4px 16px rgba(0,0,0,.13);
    transform:translateY(-2px);
}
/* ── Thumbnail ─────────────────────────────────────────────────────────── */
.gb-thumb{
    position:relative;
    height:120px;
    background:linear-gradient(135deg,#2271b1 0%,#135e96 100%);
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
}
.gb-thumb::before{
    content:"";
    position:absolute;
    inset:0;
    background:repeating-linear-gradient(
        45deg,rgba(255,255,255,.04) 0px,rgba(255,255,255,.04) 2px,
        transparent 2px,transparent 10px
    );
}
.gb-thumb-label{
    position:relative;
    font-size:13px;
    font-weight:700;
    color:#fff;
    text-shadow:0 1px 3px rgba(0,0,0,.4);
    letter-spacing:.04em;
    text-align:center;
    padding:0 12px;
    line-height:1.3;
}
' . $cat_css . '
/* ── Card header ───────────────────────────────────────────────────────── */
.gb-card-header{padding:10px 14px;border-bottom:1px solid #e0e0e0;}
.gb-card-title-row{display:flex;align-items:flex-start;justify-content:space-between;gap:8px;}
.gb-card-title{font-weight:600;font-size:14px;color:#1e1e1e;}
.gb-card-desc{color:#646970;font-size:12px;margin-top:3px;line-height:1.4;}
/* ── Category pills ────────────────────────────────────────────────────── */
.gb-pills{display:flex;flex-wrap:wrap;gap:4px;}
.gb-pill{
    font-size:10px;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.07em;
    color:#2271b1;
    background:#e8f0fb;
    border-radius:4px;
    padding:2px 7px;
    white-space:nowrap;
    transition:background 150ms,color 150ms;
}
.gb-pill:hover{background:#2271b1;color:#fff;}
/* ── Preview iframe ────────────────────────────────────────────────────── */
.gb-preview-iframe{width:100%;height:300px;border:0;background:#f6f7f7;display:block;flex:1;}
/* ── Card footer ───────────────────────────────────────────────────────── */
.gb-card-footer{
    padding:10px 14px;
    border-top:1px solid #e0e0e0;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:8px;
    background:#fafafa;
}
.gb-card-footer .button-primary{min-width:72px;}
/* ── Viewport toolbar ──────────────────────────────────────────────────── */
.gb-viewport-bar{display:flex;align-items:center;gap:8px;margin-bottom:16px;flex-wrap:wrap;}
.gb-viewport-bar .gb-viewport-btn{transition:background 150ms,color 150ms,border-color 150ms;}
.gb-viewport-btn.is-active{background:#2271b1!important;color:#fff!important;border-color:#2271b1!important;}
';
        wp_add_inline_style('wp-admin', $css);
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
     * Render the pattern browser with sandboxed iframe previews and category labels.
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

        // Responsive preview toolbar.
        echo '<div class="gb-viewport-bar">';
        echo '<span style="font-size:13px;color:#646970;margin-right:4px;">' . esc_html__('Preview size:', 'gratis-builder') . '</span>';
        $viewports = [
            'desktop' => ['label' => '🖥 Desktop', 'width' => '1440'],
            'tablet'  => ['label' => '📱 Tablet',  'width' => '768'],
            'mobile'  => ['label' => '📲 Mobile',  'width' => '375'],
        ];
        foreach ($viewports as $key => $vp) {
            echo '<button type="button" class="button gb-viewport-btn' . ($key === 'desktop' ? ' is-active' : '') . '" data-width="' . esc_attr($vp['width']) . '">'
                . esc_html($vp['label']) . '</button>';
        }
        echo '</div>';

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

        // Gradient map: first matching category slug wins.
        $thumb_gradient_map = [
            'hero'         => 'gb-thumb-hero',
            'features'     => 'gb-thumb-features',
            'testimonials' => 'gb-thumb-testimonials',
            'pricing'      => 'gb-thumb-pricing',
            'faq'          => 'gb-thumb-faq',
            'cta'          => 'gb-thumb-cta',
            'team'         => 'gb-thumb-team',
            'contact'      => 'gb-thumb-contact',
        ];

        echo '<div class="gb-pattern-grid">';
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

            // Collect category slugs + labels for pills and thumbnail colour.
            $cat_slugs  = [];
            $cat_labels = [];
            foreach ((array) ($pattern['categories'] ?? []) as $cat_slug) {
                if ('gratis-builder' === $cat_slug) {
                    continue;
                }
                $cat_slugs[] = $cat_slug;
                $registered  = WP_Block_Pattern_Categories_Registry::get_instance()->get_registered($cat_slug);
                $cat_labels[] = $registered['label'] ?? ucwords(str_replace('-', ' ', $cat_slug));
            }

            // Pick thumbnail gradient class from first recognised category slug.
            $thumb_class = 'gb-thumb-default';
            foreach ($cat_slugs as $cs) {
                foreach ($thumb_gradient_map as $key => $class) {
                    if (strpos($cs, $key) !== false) {
                        $thumb_class = $class;
                        break 2;
                    }
                }
            }

            $title        = (string) ($pattern['title'] ?? $slug);
            $copy_content = (string) wp_json_encode(['content' => (string) ($pattern['content'] ?? '')]);

            echo '<div class="gb-pattern-card">';

            // Gradient thumbnail.
            echo '<div class="gb-thumb ' . esc_attr($thumb_class) . '">';
            echo '<span class="gb-thumb-label">' . esc_html($title) . '</span>';
            echo '</div>';

            // Card header: title row + pills.
            echo '<div class="gb-card-header">';
            echo '<div class="gb-card-title-row">';
            echo '<span class="gb-card-title">' . esc_html($title) . '</span>';
            if (!empty($cat_labels)) {
                echo '<div class="gb-pills">';
                foreach ($cat_labels as $cat_label) {
                    echo '<span class="gb-pill">' . esc_html($cat_label) . '</span>';
                }
                echo '</div>';
            }
            echo '</div>';
            if (!empty($pattern['description'])) {
                echo '<div class="gb-card-desc">' . esc_html((string) $pattern['description']) . '</div>';
            }
            echo '</div>';

            // Sandboxed iframe preview.
            echo '<iframe class="gb-preview-iframe" src="' . esc_url($preview_url) . '" sandbox="allow-same-origin" loading="lazy"'
                . ' title="' . esc_attr($title) . '"></iframe>';

            // Card footer: Insert button.
            echo '<div class="gb-card-footer">';
            echo '<span style="font-size:11px;color:#8c8f94;">' . esc_html($slug) . '</span>';
            echo '<button type="button" class="button button-primary gb-insert-btn"'
                . ' data-pattern="' . esc_attr($slug) . '"'
                . ' data-content="' . esc_attr($copy_content) . '">'
                . esc_html__('Insert', 'gratis-builder') . '</button>';
            echo '</div>';

            echo '</div>';
        }
        echo '</div>';

        // Inline JS for viewport toggle + Insert (copy to clipboard).
        ?>
<script>
(function () {
    'use strict';

    // Viewport toggle.
    document.querySelectorAll('.gb-viewport-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var w = btn.dataset.width + 'px';
            document.querySelectorAll('.gb-preview-iframe').forEach(function (f) {
                f.style.width = w;
            });
            document.querySelectorAll('.gb-viewport-btn').forEach(function (b) {
                b.classList.remove('is-active');
            });
            btn.classList.add('is-active');
        });
    });

    // Insert: copy block markup to clipboard.
    document.querySelectorAll('.gb-insert-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var data = JSON.parse(btn.dataset.content || '{}');
            var content = data.content || '';
            if (!content) { return; }
            navigator.clipboard.writeText(content).then(function () {
                var orig = btn.textContent;
                btn.textContent = '✓ Copied!';
                setTimeout(function () { btn.textContent = orig; }, 2000);
            }).catch(function () {
                // Fallback: show an alert with the content truncated.
                window.prompt('Copy pattern markup (Ctrl+C / Cmd+C):', content.substring(0, 200));
            });
        });
    });
}());
</script>
        <?php
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
