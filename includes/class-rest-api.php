<?php
defined('ABSPATH') || exit;

class Gratis_Builder_REST_API {
    public static function register_routes() {
        $ns = 'gratis-builder/v1';

        register_rest_route($ns, '/parse-blocks', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'parse_blocks'],
            'permission_callback' => [__CLASS__, 'check_edit'],
            'args' => ['content' => ['required' => true, 'type' => 'string']],
        ]);

        register_rest_route($ns, '/serialize-blocks', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'serialize_blocks'],
            'permission_callback' => [__CLASS__, 'check_edit'],
            'args' => ['blocks' => ['required' => true, 'type' => 'array']],
        ]);

        register_rest_route($ns, '/update-block', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'update_block'],
            'permission_callback' => [__CLASS__, 'check_edit'],
            'args' => [
                'post_id' => ['required' => true, 'type' => 'integer'],
                'path' => ['required' => true, 'type' => 'string', 'description' => 'Block path like "0.innerBlocks.2"'],
                'attrs' => ['type' => 'object'],
                'content' => ['type' => 'string'],
                'replace' => ['type' => 'boolean', 'default' => false],
            ],
        ]);

        register_rest_route($ns, '/batch-update', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'batch_update'],
            'permission_callback' => [__CLASS__, 'check_edit'],
            'args' => [
                'post_id' => ['required' => true, 'type' => 'integer'],
                'operations' => ['required' => true, 'type' => 'array'],
            ],
        ]);

        register_rest_route($ns, '/validate-blocks', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'validate_blocks'],
            'permission_callback' => [__CLASS__, 'check_edit'],
            'args' => ['content' => ['required' => true, 'type' => 'string']],
        ]);

        register_rest_route($ns, '/patterns', [
            'methods'             => 'GET',
            'callback'            => [__CLASS__, 'list_patterns'],
            'permission_callback' => '__return_true',
            'args'                => [
                'category' => ['type' => 'string', 'sanitize_callback' => 'sanitize_key'],
                'search'   => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            ],
        ]);
    }

    public static function check_edit() {
        return current_user_can('edit_posts');
    }

    /**
     * GET /wp-json/gratis-builder/v1/patterns
     *
     * Returns registered gratis-builder/* patterns (slug, title, description,
     * categories) with optional ?category= and ?search= filtering.
     */
    public static function list_patterns(WP_REST_Request $request): WP_REST_Response {
        $category = sanitize_key((string) $request->get_param('category'));
        $search   = strtolower(trim(sanitize_text_field((string) $request->get_param('search'))));
        $registry = WP_Block_Patterns_Registry::get_instance();
        $patterns = [];
        foreach ($registry->get_all_registered() as $pattern) {
            if (0 !== strpos((string) ($pattern['name'] ?? ''), 'gratis-builder/')) {
                continue;
            }
            $cats  = array_map('strval', (array) ($pattern['categories'] ?? []));
            $title = (string) ($pattern['title'] ?? '');
            $desc  = (string) ($pattern['description'] ?? '');
            $slug  = str_replace('gratis-builder/', '', (string) $pattern['name']);

            if ('' !== $category && !in_array($category, $cats, true)) {
                continue;
            }
            if ('' !== $search && false === strpos(strtolower($slug . ' ' . $title . ' ' . $desc), $search)) {
                continue;
            }

            $patterns[] = [
                'slug'        => $slug,
                'title'       => $title,
                'description' => $desc,
                'categories'  => $cats,
            ];
        }
        return new WP_REST_Response($patterns, 200);
    }

    public static function parse_blocks($request) {
        $content = $request->get_param('content');
        $blocks = parse_blocks($content);
        return rest_ensure_response(self::blocks_to_tree($blocks));
    }

    public static function serialize_blocks($request) {
        $blocks = $request->get_param('blocks');
        $html = serialize_blocks($blocks);
        return rest_ensure_response(['content' => $html]);
    }

    public static function update_block($request) {
        $post_id = $request->get_param('post_id');
        $path = $request->get_param('path');
        $attrs = $request->get_param('attrs');
        $content = $request->get_param('content');
        $replace = $request->get_param('replace');

        $post = get_post($post_id);
        if (!$post) return new WP_Error('not_found', 'Post not found', ['status' => 404]);

        $blocks = parse_blocks($post->post_content);
        $ref = &self::get_block_ref($blocks, $path);
        if ($ref === null) return new WP_Error('invalid_path', 'Block path not found', ['status' => 400]);

        if ($replace && $content) {
            $new_blocks = parse_blocks($content);
            $ref = $new_blocks[0] ?? $ref;
        } else {
            if ($attrs) $ref['attrs'] = array_merge($ref['attrs'] ?? [], $attrs);
            if ($content) $ref['innerHTML'] = $content;
        }

        $new_content = serialize_blocks($blocks);
        wp_update_post(['ID' => $post_id, 'post_content' => $new_content]);

        return rest_ensure_response(['success' => true, 'block' => self::block_to_node($ref, $path)]);
    }

    public static function batch_update($request) {
        $post_id = $request->get_param('post_id');
        $operations = $request->get_param('operations');

        $post = get_post($post_id);
        if (!$post) return new WP_Error('not_found', 'Post not found', ['status' => 404]);

        $blocks = parse_blocks($post->post_content);
        $results = [];

        foreach ($operations as $op) {
            $path = $op['path'] ?? '';
            $ref = &self::get_block_ref($blocks, $path);
            if ($ref === null) { $results[] = ['path' => $path, 'error' => 'not found']; continue; }
            if (!empty($op['attrs'])) $ref['attrs'] = array_merge($ref['attrs'] ?? [], $op['attrs']);
            if (!empty($op['content'])) $ref['innerHTML'] = $op['content'];
            $results[] = ['path' => $path, 'success' => true];
        }

        $new_content = serialize_blocks($blocks);
        wp_update_post(['ID' => $post_id, 'post_content' => $new_content]);

        return rest_ensure_response(['results' => $results]);
    }

    public static function validate_blocks($request) {
        $content = $request->get_param('content');
        $blocks = parse_blocks($content);
        $issues = [];
        self::validate_recursive($blocks, '', $issues);
        return rest_ensure_response([
            'valid' => empty($issues),
            'block_count' => count($blocks),
            'issues' => $issues,
        ]);
    }

    private static function validate_recursive($blocks, $prefix, &$issues) {
        foreach ($blocks as $i => $block) {
            $path = $prefix ? "{$prefix}.{$i}" : (string)$i;
            if (!empty($block['blockName'])) {
                $type = WP_Block_Type_Registry::get_instance()->get_registered($block['blockName']);
                if (!$type) $issues[] = ['path' => $path, 'issue' => "Unknown block type: {$block['blockName']}"];
            }
            if (!empty($block['innerBlocks'])) {
                self::validate_recursive($block['innerBlocks'], "{$path}.innerBlocks", $issues);
            }
        }
    }

    private static function &get_block_ref(&$blocks, $path) {
        $parts = explode('.', $path);
        $ref = &$blocks;
        foreach ($parts as $part) {
            if (is_numeric($part)) $ref = &$ref[(int)$part];
            elseif ($part === 'innerBlocks') $ref = &$ref['innerBlocks'];
            else { $null = null; return $null; }
        }
        return $ref;
    }

    private static function blocks_to_tree($blocks, $prefix = '') {
        $tree = [];
        foreach ($blocks as $i => $block) {
            $path = $prefix ? "{$prefix}.{$i}" : (string)$i;
            $tree[] = self::block_to_node($block, $path);
        }
        return $tree;
    }

    private static function block_to_node($block, $path) {
        $node = [
            'path' => $path,
            'blockName' => $block['blockName'] ?? null,
            'attrs' => $block['attrs'] ?? [],
        ];
        if (!empty($block['innerHTML'])) {
            $text = wp_strip_all_tags($block['innerHTML']);
            $node['textContent'] = mb_substr(trim($text), 0, 200);
        }
        if (!empty($block['innerBlocks'])) {
            $node['innerBlocks'] = self::blocks_to_tree($block['innerBlocks'], "{$path}.innerBlocks");
        }
        return $node;
    }
}
