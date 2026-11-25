<?php

defined('ABSPATH') || exit;
return [
    'slug' => 'pricing-table',
    'title' => 'Pricing Table',
    'description' => '3-tier pricing table with highlighted recommended plan.',
    'categories' => ['featured'],
    'keywords' => ['pricing', 'plans', 'table', 'subscription'],
    'content' => '<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|80","bottom":"var:preset|spacing|80","left":"var:preset|spacing|30","right":"var:preset|spacing|30"}},"color":{"background":"#0F172A"}},"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group alignfull has-background" style="background-color:#0F172A;padding-top:var(--wp--preset--spacing--80);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--80);padding-left:var(--wp--preset--spacing--30)">
<!-- wp:heading {"textAlign":"center","style":{"typography":{"fontSize":"clamp(1.8rem,1.2rem+2vw,2.8rem)","fontWeight":"700"},"color":{"text":"#F8FAFC"},"spacing":{"margin":{"bottom":"16px"}}}} -->
<h2 class="has-text-align-center has-text-color" style="color:#F8FAFC;margin-bottom:16px;font-size:clamp(1.8rem,1.2rem+2vw,2.8rem);font-weight:700">Simple, transparent pricing</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","style":{"color":{"text":"rgba(255,255,255,0.6)"},"spacing":{"margin":{"bottom":"48px"}}}} -->
<p class="has-text-align-center has-text-color" style="color:rgba(255,255,255,0.6);margin-bottom:48px">No hidden fees. Cancel anytime.</p>
<!-- /wp:paragraph -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"24px"}}}} -->
<div class="wp-block-columns">
<!-- wp:column {"style":{"spacing":{"padding":{"top":"32px","bottom":"32px","left":"28px","right":"28px"}},"border":{"radius":"16px","width":"1px","color":"#334155"}}} -->
<div class="wp-block-column" style="border-color:#334155;border-width:1px;border-radius:16px;padding-top:32px;padding-right:28px;padding-bottom:32px;padding-left:28px">
<!-- wp:paragraph {"style":{"typography":{"fontSize":"14px","fontWeight":"600","textTransform":"uppercase","letterSpacing":"0.1em"},"color":{"text":"rgba(255,255,255,0.5)"}}} -->
<p class="has-text-color" style="color:rgba(255,255,255,0.5);font-size:14px;font-weight:600;letter-spacing:0.1em;text-transform:uppercase">Starter</p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"48px","fontWeight":"800"},"color":{"text":"#F8FAFC"},"spacing":{"margin":{"top":"8px","bottom":"24px"}}}} -->
<h3 class="has-text-color" style="color:#F8FAFC;margin-top:8px;margin-bottom:24px;font-size:48px;font-weight:800">Free</h3>
<!-- /wp:heading -->
<!-- wp:list {"style":{"color":{"text":"rgba(255,255,255,0.7)"},"typography":{"fontSize":"15px","lineHeight":"2.2"},"spacing":{"margin":{"bottom":"32px"}}}} -->
<ul class="has-text-color" style="color:rgba(255,255,255,0.7);margin-bottom:32px;font-size:15px;line-height:2.2"><li>5 pages</li><li>All patterns</li><li>Community support</li></ul>
<!-- /wp:list -->
<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"width":100,"style":{"border":{"radius":"8px"},"color":{"text":"#F8FAFC"}},"backgroundColor":"transparent","className":"is-style-outline"} -->
<div class="wp-block-button has-custom-width wp-block-button__width-100 is-style-outline"><a class="wp-block-button__link has-text-color has-transparent-background-color has-background wp-element-button" style="border-radius:8px;color:#F8FAFC">Get Started</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->
</div>
<!-- /wp:column -->

<!-- wp:column {"style":{"spacing":{"padding":{"top":"32px","bottom":"32px","left":"28px","right":"28px"}},"border":{"radius":"16px","width":"2px","color":"#22C55E"},"color":{"background":"#1B2336"}}} -->
<div class="wp-block-column has-background" style="border-color:#22C55E;border-width:2px;border-radius:16px;background-color:#1B2336;padding-top:32px;padding-right:28px;padding-bottom:32px;padding-left:28px">
<!-- wp:paragraph {"style":{"typography":{"fontSize":"14px","fontWeight":"600","textTransform":"uppercase","letterSpacing":"0.1em"},"color":{"text":"#22C55E"}}} -->
<p class="has-text-color" style="color:#22C55E;font-size:14px;font-weight:600;letter-spacing:0.1em;text-transform:uppercase">Pro ⭐</p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"48px","fontWeight":"800"},"color":{"text":"#F8FAFC"},"spacing":{"margin":{"top":"8px","bottom":"24px"}}}} -->
<h3 class="has-text-color" style="color:#F8FAFC;margin-top:8px;margin-bottom:24px;font-size:48px;font-weight:800">$19<span style="font-size:16px;font-weight:400">/mo</span></h3>
<!-- /wp:heading -->
<!-- wp:list {"style":{"color":{"text":"rgba(255,255,255,0.7)"},"typography":{"fontSize":"15px","lineHeight":"2.2"},"spacing":{"margin":{"bottom":"32px"}}}} -->
<ul class="has-text-color" style="color:rgba(255,255,255,0.7);margin-bottom:32px;font-size:15px;line-height:2.2"><li>Unlimited pages</li><li>All patterns + AI tools</li><li>Priority support</li><li>Custom blocks</li></ul>
<!-- /wp:list -->
<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"width":100,"style":{"border":{"radius":"8px"}},"backgroundColor":"primary"} -->
<div class="wp-block-button has-custom-width wp-block-button__width-100"><a class="wp-block-button__link has-primary-background-color has-background wp-element-button" style="border-radius:8px">Start Free Trial</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->
</div>
<!-- /wp:column -->

<!-- wp:column {"style":{"spacing":{"padding":{"top":"32px","bottom":"32px","left":"28px","right":"28px"}},"border":{"radius":"16px","width":"1px","color":"#334155"}}} -->
<div class="wp-block-column" style="border-color:#334155;border-width:1px;border-radius:16px;padding-top:32px;padding-right:28px;padding-bottom:32px;padding-left:28px">
<!-- wp:paragraph {"style":{"typography":{"fontSize":"14px","fontWeight":"600","textTransform":"uppercase","letterSpacing":"0.1em"},"color":{"text":"rgba(255,255,255,0.5)"}}} -->
<p class="has-text-color" style="color:rgba(255,255,255,0.5);font-size:14px;font-weight:600;letter-spacing:0.1em;text-transform:uppercase">Enterprise</p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"48px","fontWeight":"800"},"color":{"text":"#F8FAFC"},"spacing":{"margin":{"top":"8px","bottom":"24px"}}}} -->
<h3 class="has-text-color" style="color:#F8FAFC;margin-top:8px;margin-bottom:24px;font-size:48px;font-weight:800">$49<span style="font-size:16px;font-weight:400">/mo</span></h3>
<!-- /wp:heading -->
<!-- wp:list {"style":{"color":{"text":"rgba(255,255,255,0.7)"},"typography":{"fontSize":"15px","lineHeight":"2.2"},"spacing":{"margin":{"bottom":"32px"}}}} -->
<ul class="has-text-color" style="color:rgba(255,255,255,0.7);margin-bottom:32px;font-size:15px;line-height:2.2"><li>Everything in Pro</li><li>White-label</li><li>Dedicated support</li><li>Custom development</li></ul>
<!-- /wp:list -->
<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"width":100,"style":{"border":{"radius":"8px"},"color":{"text":"#F8FAFC"}},"backgroundColor":"transparent","className":"is-style-outline"} -->
<div class="wp-block-button has-custom-width wp-block-button__width-100 is-style-outline"><a class="wp-block-button__link has-text-color has-transparent-background-color has-background wp-element-button" style="border-radius:8px;color:#F8FAFC">Contact Sales</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->',
];
