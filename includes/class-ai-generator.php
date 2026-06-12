<?php
/**
 * AI layout generation endpoint for GRATIS Builder.
 *
 * Exposes POST /wp-json/gratis-builder/v1/generate. Generation is routed
 * through the gratis-ai ModelRouter when available, with a deterministic
 * pattern-assembly fallback so the endpoint always returns valid block
 * markup. Requests are rate limited per user using a configurable window.
 *
 * @package Gratis\Builder
 */

defined('ABSPATH') || exit;

class Gratis_Builder_AI_Generator {

    /** Option storing the maximum requests allowed per window (0 = unlimited). */
    const RATE_LIMIT_OPTION = 'gratis_builder_ai_rate_limit';

    /** Option storing the rate-limit window length in seconds. */
    const RATE_WINDOW_OPTION = 'gratis_builder_ai_rate_window';

    /** Default number of generations allowed per window. */
    const DEFAULT_LIMIT = 20;

    /** Default rate-limit window length in seconds. */
    const DEFAULT_WINDOW = HOUR_IN_SECONDS;

    /**
     * Register the REST route.
     */
    public static function register_routes(): void {
        register_rest_route('gratis-builder/v1', '/generate', [
            'methods'             => 'POST',
            'callback'            => [__CLASS__, 'generate'],
            'permission_callback' => [__CLASS__, 'check_permission'],
            'args'                => [
                'prompt'     => [
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_textarea_field',
                ],
                'style'      => [
                    'type'              => 'string',
                    'default'           => '',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'max_tokens' => [
                    'type'    => 'integer',
                    'default' => 1500,
                ],
            ],
        ]);
    }

    /**
     * Only logged-in users who can edit content may generate layouts.
     */
    public static function check_permission(): bool {
        return is_user_logged_in() && current_user_can('edit_posts');
    }

    /**
     * Handle a generation request.
     *
     * @param WP_REST_Request $request Request instance.
     * @return WP_REST_Response|WP_Error
     */
    public static function generate($request) {
        $user_id = get_current_user_id();

        $limit_check = self::check_rate_limit($user_id);
        if (is_wp_error($limit_check)) {
            return $limit_check;
        }

        $prompt = trim((string) $request->get_param('prompt'));
        if ('' === $prompt) {
            return new WP_Error('empty_prompt', __('Prompt cannot be empty.', 'gratis-builder'), ['status' => 400]);
        }

        $style      = (string) $request->get_param('style');
        $max_tokens = (int) $request->get_param('max_tokens');
        $max_tokens = max(256, min($max_tokens, 4000));

        $result = self::build_layout($prompt, $style, $max_tokens);

        // Count this request against the user's allowance.
        self::increment_rate_limit($user_id);

        $blocks      = parse_blocks($result['content']);
        $block_count = count(array_filter($blocks, static fn($b) => !empty($b['blockName'])));

        return rest_ensure_response([
            'content'     => $result['content'],
            'source'      => $result['source'],
            'block_count' => $block_count,
            'rate_limit'  => self::rate_limit_status($user_id),
        ]);
    }

    /**
     * Build block markup for the given prompt.
     *
     * @param string $prompt     User request.
     * @param string $style      Optional style hint.
     * @param int    $max_tokens Token budget for the model.
     * @return array{content:string,source:string}
     */
    private static function build_layout(string $prompt, string $style, int $max_tokens): array {
        if (class_exists('\\Gratis\\AI\\ModelRouter')) {
            try {
                $provider = \Gratis\AI\ModelRouter::resolve();
                if ($provider->isAvailable()) {
                    $full     = self::system_prompt($style) . "\n\nUser request: " . $prompt;
                    $response = $provider->complete($full, ['max_tokens' => $max_tokens]);
                    $markup   = self::extract_block_markup((string) $response);
                    if ('' !== $markup && 0 !== strpos((string) $response, 'Error:')) {
                        return ['content' => $markup, 'source' => 'ai:' . $provider->name()];
                    }
                }
            } catch (\Throwable $e) {
                // Fall through to the deterministic fallback below.
                unset($e);
            }
        }

        return ['content' => self::fallback_layout($prompt), 'source' => 'patterns'];
    }

    /**
     * System prompt instructing the model to emit valid Gutenberg markup.
     */
    private static function system_prompt(string $style): string {
        $prompt  = 'You are a WordPress block (Gutenberg) layout generator. ';
        $prompt .= 'Respond ONLY with valid Gutenberg block markup (HTML comments like <!-- wp:group --> wrapping standard core blocks). ';
        $prompt .= 'Do not include explanations, Markdown code fences, or any text outside the block markup. ';
        $prompt .= 'Use core blocks only (group, heading, paragraph, buttons, button, columns, column, image, list). ';
        if ('' !== $style) {
            $prompt .= 'Visual style preference: ' . $style . '. ';
        }
        return $prompt;
    }

    /**
     * Extract a contiguous run of block markup from a model response.
     *
     * Strips Markdown code fences and trims anything before the first block
     * comment / after the last closing comment.
     */
    private static function extract_block_markup(string $response): string {
        $response = trim($response);

        // Remove ```html ... ``` or ``` ... ``` fences.
        if (0 === strpos($response, '```')) {
            $response = (string) preg_replace('/^```[a-zA-Z]*\s*/', '', $response);
            $response = (string) preg_replace('/```\s*$/', '', $response);
            $response = trim($response);
        }

        $start = strpos($response, '<!-- wp:');
        if (false === $start) {
            return '';
        }
        $end = strrpos($response, '-->');
        if (false === $end) {
            return '';
        }

        $markup = substr($response, $start, ($end - $start) + 3);

        // Reject markup that contains no recognizable core block.
        $blocks = parse_blocks($markup);
        $has    = false;
        foreach ($blocks as $block) {
            if (!empty($block['blockName'])) {
                $has = true;
                break;
            }
        }

        return $has ? $markup : '';
    }

    /**
     * Assemble a layout from registered patterns whose keywords match the
     * prompt. Always returns at least one section.
     */
    private static function fallback_layout(string $prompt): string {
        $registry = WP_Block_Patterns_Registry::get_instance();
        $all      = $registry->get_all_registered();
        $words    = array_filter(
            preg_split('/[^a-z0-9]+/', strtolower($prompt)) ?: [],
            static fn($w) => strlen($w) > 3
        );

        $selected = [];
        foreach ($all as $pattern) {
            if (0 !== strpos((string) ($pattern['name'] ?? ''), 'gratis-builder/')) {
                continue;
            }
            $haystack = strtolower(
                ($pattern['title'] ?? '') . ' ' .
                ($pattern['description'] ?? '') . ' ' .
                implode(' ', (array) ($pattern['keywords'] ?? []))
            );
            foreach ($words as $word) {
                if (false !== strpos($haystack, $word)) {
                    $selected[$pattern['name']] = $pattern['content'];
                    break;
                }
            }
        }

        if (empty($selected)) {
            foreach (['gratis-builder/hero-dark-centered', 'gratis-builder/cta-banner'] as $name) {
                $pattern = $registry->get_registered($name);
                if ($pattern) {
                    $selected[$name] = $pattern['content'];
                }
            }
        }

        return implode("\n\n", $selected);
    }

    /* ------------------------------------------------------------------ *
     * Rate limiting
     * ------------------------------------------------------------------ */

    /**
     * Configured number of generations allowed per window (0 = unlimited).
     */
    public static function get_limit(): int {
        $limit = (int) get_option(self::RATE_LIMIT_OPTION, self::DEFAULT_LIMIT);
        /** Filter the per-user AI generation limit. */
        return (int) apply_filters('gratis_builder_ai_rate_limit', $limit);
    }

    /**
     * Configured rate-limit window length in seconds.
     */
    public static function get_window(): int {
        $window = (int) get_option(self::RATE_WINDOW_OPTION, self::DEFAULT_WINDOW);
        if ($window < 1) {
            $window = self::DEFAULT_WINDOW;
        }
        /** Filter the AI generation rate-limit window in seconds. */
        return (int) apply_filters('gratis_builder_ai_rate_window', $window);
    }

    /**
     * Transient key holding a user's request bucket.
     */
    private static function transient_key(int $user_id): string {
        return 'gratis_builder_ai_rl_' . $user_id;
    }

    /**
     * Return a WP_Error when the user has exhausted their allowance.
     *
     * @return true|WP_Error
     */
    private static function check_rate_limit(int $user_id) {
        $limit = self::get_limit();
        if ($limit <= 0) {
            return true;
        }

        $data = get_transient(self::transient_key($user_id));
        if (is_array($data) && ($data['count'] ?? 0) >= $limit) {
            $retry = isset($data['expires']) ? max(0, (int) $data['expires'] - time()) : self::get_window();
            return new WP_Error(
                'rate_limited',
                sprintf(
                    /* translators: 1: request limit, 2: window seconds, 3: seconds until reset. */
                    __('AI generation rate limit reached (%1$d per %2$d seconds). Try again in %3$d seconds.', 'gratis-builder'),
                    $limit,
                    self::get_window(),
                    $retry
                ),
                ['status' => 429, 'retry_after' => $retry]
            );
        }

        return true;
    }

    /**
     * Record one generation against the user's allowance.
     */
    private static function increment_rate_limit(int $user_id): void {
        $limit = self::get_limit();
        if ($limit <= 0) {
            return;
        }

        $window = self::get_window();
        $key    = self::transient_key($user_id);
        $data   = get_transient($key);

        if (!is_array($data) || !isset($data['expires']) || $data['expires'] <= time()) {
            $data = ['count' => 0, 'expires' => time() + $window];
        }

        $data['count'] = (int) ($data['count'] ?? 0) + 1;
        $ttl           = max(1, (int) $data['expires'] - time());

        set_transient($key, $data, $ttl);
    }

    /**
     * Current remaining allowance for a user, for inclusion in responses.
     *
     * @return array<string,int|bool>
     */
    private static function rate_limit_status(int $user_id): array {
        $limit = self::get_limit();
        if ($limit <= 0) {
            return ['limit' => 0, 'remaining' => -1, 'unlimited' => true];
        }

        $data  = get_transient(self::transient_key($user_id));
        $used  = is_array($data) ? (int) ($data['count'] ?? 0) : 0;
        $reset = is_array($data) && isset($data['expires']) ? max(0, (int) $data['expires'] - time()) : 0;

        return [
            'limit'           => $limit,
            'remaining'       => max(0, $limit - $used),
            'window'          => self::get_window(),
            'resets_in'       => $reset,
            'unlimited'       => false,
        ];
    }
}
