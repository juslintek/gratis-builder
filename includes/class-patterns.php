<?php
defined('ABSPATH') || exit;

class Gratis_Builder_Patterns {
    public static function register() {
        register_block_pattern_category('gratis-builder', ['label' => 'GRATIS Builder']);

        $patterns_dir = GRATIS_BUILDER_PATH . 'patterns/';
        if (!is_dir($patterns_dir)) return;

        foreach (glob($patterns_dir . '*.php') as $file) {
            $pattern = require $file;
            if (is_array($pattern) && !empty($pattern['slug'])) {
                register_block_pattern('gratis-builder/' . $pattern['slug'], [
                    'title' => $pattern['title'] ?? $pattern['slug'],
                    'description' => $pattern['description'] ?? '',
                    'categories' => array_merge(['gratis-builder'], $pattern['categories'] ?? []),
                    'content' => $pattern['content'],
                    'keywords' => $pattern['keywords'] ?? [],
                ]);
            }
        }
    }
}
