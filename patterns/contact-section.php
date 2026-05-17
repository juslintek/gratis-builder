<?php
return [
    'slug' => 'contact-section',
    'title' => 'Contact Section',
    'description' => 'Contact information with email, location, and social links.',
    'categories' => ['featured', 'text'],
    'keywords' => ['contact', 'email', 'address', 'social'],
    'content' => '<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|80","bottom":"var:preset|spacing|80","left":"var:preset|spacing|30","right":"var:preset|spacing|30"}},"color":{"background":"#0F172A"}},"layout":{"type":"constrained","contentSize":"900px"}} -->
<div class="wp-block-group alignfull has-background" style="background-color:#0F172A;padding-top:var(--wp--preset--spacing--80);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--80);padding-left:var(--wp--preset--spacing--30)">
<!-- wp:heading {"textAlign":"center","style":{"typography":{"fontSize":"clamp(1.8rem,1.2rem+2vw,2.8rem)","fontWeight":"700"},"color":{"text":"#F8FAFC"},"spacing":{"margin":{"bottom":"12px"}}}} -->
<h2 class="has-text-align-center has-text-color" style="color:#F8FAFC;margin-bottom:12px;font-size:clamp(1.8rem,1.2rem+2vw,2.8rem);font-weight:700">Get in touch</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","style":{"color":{"text":"rgba(255,255,255,0.6)"},"spacing":{"margin":{"bottom":"48px"}}}} -->
<p class="has-text-align-center has-text-color" style="color:rgba(255,255,255,0.6);margin-bottom:48px">We\'d love to hear from you. Reach out anytime.</p>
<!-- /wp:paragraph -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"48px"}}}} -->
<div class="wp-block-columns">
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"16px","fontWeight":"600"},"color":{"text":"#F8FAFC"},"spacing":{"margin":{"bottom":"8px"}}}} -->
<h3 class="has-text-color" style="color:#F8FAFC;margin-bottom:8px;font-size:16px;font-weight:600">📧 Email</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"style":{"color":{"text":"rgba(255,255,255,0.7)"},"typography":{"fontSize":"15px"}}} -->
<p class="has-text-color" style="color:rgba(255,255,255,0.7);font-size:15px">hello@example.com</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"16px","fontWeight":"600"},"color":{"text":"#F8FAFC"},"spacing":{"margin":{"bottom":"8px"}}}} -->
<h3 class="has-text-color" style="color:#F8FAFC;margin-bottom:8px;font-size:16px;font-weight:600">📍 Location</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"style":{"color":{"text":"rgba(255,255,255,0.7)"},"typography":{"fontSize":"15px"}}} -->
<p class="has-text-color" style="color:rgba(255,255,255,0.7);font-size:15px">Vilnius, Lithuania</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"16px","fontWeight":"600"},"color":{"text":"#F8FAFC"},"spacing":{"margin":{"bottom":"8px"}}}} -->
<h3 class="has-text-color" style="color:#F8FAFC;margin-bottom:8px;font-size:16px;font-weight:600">🔗 Social</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"style":{"color":{"text":"#60a5fa"},"typography":{"fontSize":"15px"}}} -->
<p class="has-text-color" style="color:#60a5fa;font-size:15px">GitHub · Twitter · Discord</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->',
];
