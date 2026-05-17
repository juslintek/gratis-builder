<?php
return [
    'slug' => 'cta-banner',
    'title' => 'CTA Banner',
    'description' => 'Full-width call-to-action banner with gradient background.',
    'categories' => ['featured', 'call-to-action'],
    'keywords' => ['cta', 'banner', 'call to action', 'conversion'],
    'content' => '<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70","left":"var:preset|spacing|30","right":"var:preset|spacing|30"}},"color":{"gradient":"linear-gradient(135deg,#1e3a5f 0%,#0f172a 100%)"}},"layout":{"type":"constrained","contentSize":"700px"}} -->
<div class="wp-block-group alignfull has-background" style="background:linear-gradient(135deg,#1e3a5f 0%,#0f172a 100%);padding-top:var(--wp--preset--spacing--70);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--30)">
<!-- wp:heading {"textAlign":"center","style":{"typography":{"fontSize":"clamp(1.6rem,1rem+2vw,2.5rem)","fontWeight":"700"},"color":{"text":"#F8FAFC"},"spacing":{"margin":{"bottom":"16px"}}}} -->
<h2 class="has-text-align-center has-text-color" style="color:#F8FAFC;margin-bottom:16px;font-size:clamp(1.6rem,1rem+2vw,2.5rem);font-weight:700">Ready to build something amazing?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"color":{"text":"rgba(255,255,255,0.7)"},"typography":{"fontSize":"17px"},"spacing":{"margin":{"bottom":"32px"}}}} -->
<p class="has-text-align-center has-text-color" style="color:rgba(255,255,255,0.7);margin-bottom:32px;font-size:17px">Start building for free. No credit card required.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button {"style":{"border":{"radius":"8px"},"spacing":{"padding":{"top":"16px","bottom":"16px","left":"32px","right":"32px"}},"typography":{"fontSize":"16px","fontWeight":"600"}},"backgroundColor":"primary"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-primary-background-color has-background wp-element-button" style="border-radius:8px;padding-top:16px;padding-right:32px;padding-bottom:16px;padding-left:32px;font-size:16px;font-weight:600">Get Started Free →</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->',
];
