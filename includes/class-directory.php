<?php
defined('ABSPATH') || exit;

/**
 * Directory/marketplace features for gratis-builder.
 * Listing CPT with location, category, rating. What paid directory themes charge $79+ for.
 */
class Gratis_Builder_Directory {
    public static function register(): void {
        register_post_type('gratis_listing', [
            'labels'       => ['name' => 'Listings', 'singular_name' => 'Listing'],
            'public'       => true,
            'show_in_rest' => true,
            'menu_icon'    => 'dashicons-location-alt',
            'supports'     => ['title', 'editor', 'thumbnail', 'custom-fields', 'excerpt'],
            'has_archive'  => true,
            'rewrite'      => ['slug' => 'listings'],
        ]);

        register_taxonomy('listing_category', 'gratis_listing', [
            'labels'       => ['name' => 'Categories'],
            'hierarchical' => true,
            'public'       => true,
            'show_in_rest' => true,
            'rewrite'      => ['slug' => 'listing-category'],
        ]);

        register_taxonomy('listing_location', 'gratis_listing', [
            'labels'       => ['name' => 'Locations'],
            'hierarchical' => true,
            'public'       => true,
            'show_in_rest' => true,
        ]);

        register_post_meta('gratis_listing', '_address', ['type' => 'string', 'single' => true, 'show_in_rest' => true]);
        register_post_meta('gratis_listing', '_lat', ['type' => 'number', 'single' => true, 'show_in_rest' => true]);
        register_post_meta('gratis_listing', '_lng', ['type' => 'number', 'single' => true, 'show_in_rest' => true]);
        register_post_meta('gratis_listing', '_rating', ['type' => 'number', 'single' => true, 'show_in_rest' => true]);
        register_post_meta('gratis_listing', '_website', ['type' => 'string', 'single' => true, 'show_in_rest' => true]);
        register_post_meta('gratis_listing', '_phone', ['type' => 'string', 'single' => true, 'show_in_rest' => true]);

        // Frontend submission shortcode
        add_shortcode('gratis-submit-listing', [__CLASS__, 'submissionForm']);

        // REST API for search/filter
        register_rest_route('gratis-builder/v1', '/listings', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'searchListings'],
            'permission_callback' => '__return_true',
        ]);
    }

    public static function submissionForm(): string {
        if (!is_user_logged_in()) return '<p>Please <a href="' . wp_login_url() . '">log in</a> to submit a listing.</p>';
        return '<form method="post" class="gratis-listing-form">
            ' . wp_nonce_field('gratis_listing', '_gln', true, false) . '
            <p><label>Title</label><input type="text" name="listing_title" required></p>
            <p><label>Description</label><textarea name="listing_desc"></textarea></p>
            <p><label>Address</label><input type="text" name="listing_address"></p>
            <p><label>Website</label><input type="url" name="listing_website"></p>
            <p><label>Phone</label><input type="tel" name="listing_phone"></p>
            <button type="submit" name="gratis_submit_listing">Submit Listing</button>
        </form>';
    }

    public static function searchListings(\WP_REST_Request $request): \WP_REST_Response {
        $args = ['post_type' => 'gratis_listing', 'posts_per_page' => 20, 'post_status' => 'publish'];
        if ($q = $request->get_param('q')) $args['s'] = $q;
        if ($cat = $request->get_param('category')) {
            $args['tax_query'] = [['taxonomy' => 'listing_category', 'field' => 'slug', 'terms' => $cat]];
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
