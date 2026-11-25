<?php

defined('ABSPATH') || exit;
return [
    'slug' => 'hero-dark-centered',
    'title' => 'Hero — Dark Centered',
    'description' => 'Full-width dark hero with centered headline, subtitle, and dual CTA buttons.',
    'categories' => ['featured', 'banner'],
    'keywords' => ['hero', 'banner', 'cta', 'dark'],
    'content' => '<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|80","bottom":"var:preset|spacing|80","left":"var:preset|spacing|30","right":"var:preset|spacing|30"}},"color":{"background":"#0F172A"}},"layout":{"type":"constrained","contentSize":"760px"}} -->
<div class="wp-block-group alignfull has-background" style="background-color:#0F172A;padding-top:var(--wp--preset--spacing--80);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--80);padding-left:var(--wp--preset--spacing--30)">
<!-- wp:heading {"textAlign":"center","level":1,"style":{"typography":{"fontSize":"clamp(2.5rem,1.5rem+4vw,4.5rem)","fontWeight":"800","letterSpacing":"-0.03em"},"color":{"text":"#F8FAFC"}}} -->
<h1 class="has-text-align-center has-text-color" style="color:#F8FAFC;font-size:clamp(2.5rem,1.5rem+4vw,4.5rem);font-weight:800;letter-spacing:-0.03em">Build faster with AI-powered blocks</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"18px","lineHeight":"1.7"},"color":{"text":"rgba(255,255,255,0.7)"},"spacing":{"margin":{"top":"24px","bottom":"40px"}}}} -->
<p class="has-text-align-center has-text-color" style="color:rgba(255,255,255,0.7);margin-top:24px;margin-bottom:40px;font-size:18px;line-height:1.7">Create stunning pages in minutes with our free Gutenberg-native builder. No lock-in, no premium walls.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"blockGap":"16px"}}} -->
<div class="wp-block-buttons">
<!-- wp:button {"style":{"border":{"radius":"8px"},"spacing":{"padding":{"top":"14px","bottom":"14px","left":"28px","right":"28px"}},"typography":{"fontWeight":"600"}},"backgroundColor":"primary"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-primary-background-color has-background wp-element-button" style="border-radius:8px;padding-top:14px;padding-right:28px;padding-bottom:14px;padding-left:28px;font-weight:600">Get Started Free</a></div>
<!-- /wp:button -->

<!-- wp:button {"style":{"border":{"radius":"8px","width":"1px","color":"rgba(255,255,255,0.3)"},"spacing":{"padding":{"top":"14px","bottom":"14px","left":"28px","right":"28px"}},"typography":{"fontWeight":"600"},"color":{"text":"#F8FAFC"}},"backgroundColor":"transparent"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-text-color has-transparent-background-color has-background wp-element-button" style="border-radius:8px;border-color:rgba(255,255,255,0.3);border-width:1px;color:#F8FAFC;padding-top:14px;padding-right:28px;padding-bottom:14px;padding-left:28px;font-weight:600">View Patterns</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->',
];
