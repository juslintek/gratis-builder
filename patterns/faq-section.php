<?php

defined('ABSPATH') || exit;
return [
    'slug' => 'faq-section',
    'title' => 'FAQ Section',
    'description' => 'Frequently asked questions with expandable details blocks.',
    'categories' => ['featured', 'text'],
    'keywords' => ['faq', 'questions', 'accordion', 'help'],
    'content' => '<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|80","bottom":"var:preset|spacing|80","left":"var:preset|spacing|30","right":"var:preset|spacing|30"}},"color":{"background":"#0F172A"}},"layout":{"type":"constrained","contentSize":"700px"}} -->
<div class="wp-block-group alignfull has-background" style="background-color:#0F172A;padding-top:var(--wp--preset--spacing--80);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--80);padding-left:var(--wp--preset--spacing--30)">
<!-- wp:heading {"textAlign":"center","style":{"typography":{"fontSize":"clamp(1.8rem,1.2rem+2vw,2.8rem)","fontWeight":"700"},"color":{"text":"#F8FAFC"},"spacing":{"margin":{"bottom":"48px"}}}} -->
<h2 class="has-text-align-center has-text-color" style="color:#F8FAFC;margin-bottom:48px;font-size:clamp(1.8rem,1.2rem+2vw,2.8rem);font-weight:700">Frequently asked questions</h2>
<!-- /wp:heading -->

<!-- wp:details {"style":{"spacing":{"padding":{"top":"20px","bottom":"20px"}},"border":{"bottom":{"width":"1px","color":"#334155"}},"typography":{"fontSize":"16px","fontWeight":"500"},"color":{"text":"#F8FAFC"}}} -->
<details class="wp-block-details has-text-color" style="border-bottom-color:#334155;border-bottom-width:1px;color:#F8FAFC;padding-top:20px;padding-bottom:20px;font-size:16px;font-weight:500"><summary>Is it really free?</summary><!-- wp:paragraph {"style":{"color":{"text":"rgba(255,255,255,0.7)"},"typography":{"fontSize":"15px"},"spacing":{"margin":{"top":"12px"}}}} -->
<p class="has-text-color" style="color:rgba(255,255,255,0.7);margin-top:12px;font-size:15px">Yes! The core plugin with all patterns and AI endpoints is completely free and open source under GPL-2.0.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details {"style":{"spacing":{"padding":{"top":"20px","bottom":"20px"}},"border":{"bottom":{"width":"1px","color":"#334155"}},"typography":{"fontSize":"16px","fontWeight":"500"},"color":{"text":"#F8FAFC"}}} -->
<details class="wp-block-details has-text-color" style="border-bottom-color:#334155;border-bottom-width:1px;color:#F8FAFC;padding-top:20px;padding-bottom:20px;font-size:16px;font-weight:500"><summary>Does it work with any theme?</summary><!-- wp:paragraph {"style":{"color":{"text":"rgba(255,255,255,0.7)"},"typography":{"fontSize":"15px"},"spacing":{"margin":{"top":"12px"}}}} -->
<p class="has-text-color" style="color:rgba(255,255,255,0.7);margin-top:12px;font-size:15px">It works best with block themes that use theme.json, but patterns will render in any WordPress theme with Gutenberg support.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details {"style":{"spacing":{"padding":{"top":"20px","bottom":"20px"}},"border":{"bottom":{"width":"1px","color":"#334155"}},"typography":{"fontSize":"16px","fontWeight":"500"},"color":{"text":"#F8FAFC"}}} -->
<details class="wp-block-details has-text-color" style="border-bottom-color:#334155;border-bottom-width:1px;color:#F8FAFC;padding-top:20px;padding-bottom:20px;font-size:16px;font-weight:500"><summary>What is the AI/MCP integration?</summary><!-- wp:paragraph {"style":{"color":{"text":"rgba(255,255,255,0.7)"},"typography":{"fontSize":"15px"},"spacing":{"margin":{"top":"12px"}}}} -->
<p class="has-text-color" style="color:rgba(255,255,255,0.7);margin-top:12px;font-size:15px">The plugin exposes REST API endpoints that let AI agents parse, edit, and build pages programmatically using the Model Context Protocol.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details {"style":{"spacing":{"padding":{"top":"20px","bottom":"20px"}},"border":{"bottom":{"width":"1px","color":"#334155"}},"typography":{"fontSize":"16px","fontWeight":"500"},"color":{"text":"#F8FAFC"}}} -->
<details class="wp-block-details has-text-color" style="border-bottom-color:#334155;border-bottom-width:1px;color:#F8FAFC;padding-top:20px;padding-bottom:20px;font-size:16px;font-weight:500"><summary>Will it slow down my site?</summary><!-- wp:paragraph {"style":{"color":{"text":"rgba(255,255,255,0.7)"},"typography":{"fontSize":"15px"},"spacing":{"margin":{"top":"12px"}}}} -->
<p class="has-text-color" style="color:rgba(255,255,255,0.7);margin-top:12px;font-size:15px">No. It uses only native Gutenberg blocks — zero JavaScript on the frontend, no render-blocking resources, no database queries on page load.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->
</div>
<!-- /wp:group -->',
];
