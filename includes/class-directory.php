<?php
defined('ABSPATH') || exit;

/**
 * Directory/marketplace features for gratis-builder.
 * Listing CPT with location, category, rating. What paid directory themes charge $79+ for.
 */
class Gratis_Builder_Directory {
    public static function register(): void {
        register_post_type('gratis_listing', [
            'labels'       => ['name' => __('Listings', 'gratis-builder'), 'singular_name' => __('Listing', 'gratis-builder')],
            'public'       => true,
            'show_in_rest' => true,
            'menu_icon'    => 'dashicons-location-alt',
            'supports'     => ['title', 'editor', 'thumbnail', 'custom-fields', 'excerpt'],
            'has_archive'  => true,
            'rewrite'      => ['slug' => 'listings'],
        ]);

        register_taxonomy('gratis_listing_category', 'gratis_listing', [
            'labels'       => ['name' => __('Categories', 'gratis-builder')],
            'hierarchical' => true,
            'public'       => true,
            'show_in_rest' => true,
            'rewrite'      => ['slug' => 'listing-category'],
        ]);

        register_taxonomy('gratis_listing_location', 'gratis_listing', [
            'labels'       => ['name' => __('Locations', 'gratis-builder')],
            'hierarchical' => true,
            'public'       => true,
            'show_in_rest' => true,
        ]);

        $auth = static function ($allowed, string $meta_key, $object_id = 0): bool {
            unset($allowed, $meta_key);
            $post_id = (int) $object_id;
            return $post_id > 0 ? current_user_can('edit_post', $post_id) : current_user_can('edit_posts');
        };
        register_post_meta('gratis_listing', '_address', ['type' => 'string', 'single' => true, 'show_in_rest' => true, 'sanitize_callback' => 'sanitize_text_field', 'auth_callback' => $auth]);
        register_post_meta('gratis_listing', '_lat', ['type' => 'number', 'single' => true, 'show_in_rest' => true, 'sanitize_callback' => static fn($v) => (float) $v, 'auth_callback' => $auth]);
        register_post_meta('gratis_listing', '_lng', ['type' => 'number', 'single' => true, 'show_in_rest' => true, 'sanitize_callback' => static fn($v) => (float) $v, 'auth_callback' => $auth]);
        register_post_meta('gratis_listing', '_rating', ['type' => 'number', 'single' => true, 'show_in_rest' => true, 'sanitize_callback' => static fn($v) => max(0.0, min(5.0, (float) $v)), 'auth_callback' => $auth]);
        register_post_meta('gratis_listing', '_website', ['type' => 'string', 'single' => true, 'show_in_rest' => true, 'sanitize_callback' => 'esc_url_raw', 'auth_callback' => $auth]);
        register_post_meta('gratis_listing', '_phone', ['type' => 'string', 'single' => true, 'show_in_rest' => true, 'sanitize_callback' => 'sanitize_text_field', 'auth_callback' => $auth]);

        // Frontend submission shortcode and POST handler.
        add_shortcode('gratis-submit-listing', [__CLASS__, 'submissionForm']);
        add_action('template_redirect', [__CLASS__, 'handleSubmission']);

        // REST API for search/filter.
        add_action('rest_api_init', function () {
            register_rest_route('gratis-builder/v1', '/listings', [
                'methods' => 'GET',
                'callback' => [__CLASS__, 'searchListings'],
                'permission_callback' => '__return_true',
            ]);
        });
    }

    public static function handleSubmission(): void {
        if (!isset($_POST['gratis_submit_listing']) || !is_user_logged_in()) {
            return;
        }
        $nonce = isset($_POST['_gln']) ? sanitize_text_field(wp_unslash($_POST['_gln'])) : '';
        if ('' === $nonce || !wp_verify_nonce($nonce, 'gratis_listing')) {
            return;
        }

        // Nonce and login verified above.
        $title   = isset($_POST['listing_title']) ? sanitize_text_field(wp_unslash($_POST['listing_title'])) : '';
        $desc    = isset($_POST['listing_desc']) ? sanitize_textarea_field(wp_unslash($_POST['listing_desc'])) : '';
        $address = isset($_POST['listing_address']) ? sanitize_text_field(wp_unslash($_POST['listing_address'])) : '';
        $website = isset($_POST['listing_website']) ? esc_url_raw(wp_unslash($_POST['listing_website'])) : '';
        $phone   = isset($_POST['listing_phone']) ? sanitize_text_field(wp_unslash($_POST['listing_phone'])) : '';

        $back = wp_get_referer() ?: home_url('/');
        if ('' === $title) {
            wp_safe_redirect(add_query_arg('gratis_listing', 'error', $back));
            exit;
        }

        $id = wp_insert_post([
            'post_type'    => 'gratis_listing',
            'post_status'  => 'pending',
            'post_author'  => get_current_user_id(),
            'post_title'   => $title,
            'post_content' => $desc,
            'meta_input'   => [
                '_address' => $address,
                '_website' => $website,
                '_phone'   => $phone,
            ],
        ]);

        $flag = is_wp_error($id) ? 'error' : 'submitted';
        wp_safe_redirect(add_query_arg('gratis_listing', $flag, $back));
        exit;
    }

    public static function submissionForm(): string {
        if (!is_user_logged_in()) {
            return '<p>' . esc_html__('Please', 'gratis-builder') . ' <a href="' . esc_url(wp_login_url()) . '">' . esc_html__('log in', 'gratis-builder') . '</a> ' . esc_html__('to submit a listing.', 'gratis-builder') . '</p>';
        }
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display-only status flag from PRG redirect.
        $status = isset($_GET['gratis_listing']) ? sanitize_key(wp_unslash($_GET['gratis_listing'])) : '';
        $notice = '';
        if ('submitted' === $status) {
            $notice = '<p class="gratis-listing-notice" role="status">' . esc_html__('Thank you! Your listing has been submitted for review.', 'gratis-builder') . '</p>';
        } elseif ('error' === $status) {
            $notice = '<p class="gratis-listing-notice gratis-listing-error" role="alert">' . esc_html__('Could not submit listing. Please check your entries and try again.', 'gratis-builder') . '</p>';
        }
        return $notice . '<form method="post" class="gratis-listing-form">
            ' . wp_nonce_field('gratis_listing', '_gln', true, false) . '
            <p><label>' . esc_html__('Title', 'gratis-builder') . '</label><input type="text" name="listing_title" required></p>
            <p><label>' . esc_html__('Description', 'gratis-builder') . '</label><textarea name="listing_desc"></textarea></p>
            <p><label>' . esc_html__('Address', 'gratis-builder') . '</label><input type="text" name="listing_address"></p>
            <p><label>' . esc_html__('Website', 'gratis-builder') . '</label><input type="url" name="listing_website"></p>
            <p><label>' . esc_html__('Phone', 'gratis-builder') . '</label><input type="tel" name="listing_phone"></p>
            <button type="submit" name="gratis_submit_listing">' . esc_html__('Submit Listing', 'gratis-builder') . '</button>
        </form>';
    }

    public static function searchListings(\WP_REST_Request $request): \WP_REST_Response {
        $args = ['post_type' => 'gratis_listing', 'posts_per_page' => 20, 'post_status' => 'publish'];
        $q    = sanitize_text_field((string) $request->get_param('q'));
        if ('' !== $q) {
            $args['s'] = $q;
        }
        $cat = sanitize_key((string) $request->get_param('category'));
        if ('' !== $cat) {
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- directory category filter.
            $args['tax_query'] = [['taxonomy' => 'gratis_listing_category', 'field' => 'slug', 'terms' => $cat]];
        }
        $posts = get_posts($args);
        $data = array_map(fn($p) => [
            'id' => $p->ID, 'title' => $p->post_title, 'excerpt' => $p->post_excerpt,
            'address' => get_post_meta($p->ID, '_address', true),
            'rating' => (float) get_post_meta($p->ID, '_rating', true),
            'url' => get_permalink($p),
        ], $posts);
        return new \WP_REST_Response($data);
    }
}
