/* global wp */
/* GRATIS Builder — Gutenberg sidebar plugin (Iteration 19).
 *
 * Registers a sidebar panel in the block editor that lists all gratis-builder
 * patterns and lets the user insert them directly into the post — no
 * clipboard paste required.
 */
( function () {
    'use strict';

    var el                    = wp.element.createElement;
    var useState              = wp.element.useState;
    var __                    = wp.i18n.__;
    var registerPlugin        = wp.plugins.registerPlugin;
    var PluginSidebar         = wp.editor.PluginSidebar;
    var PluginSidebarMoreMenuItem = wp.editor.PluginSidebarMoreMenuItem;

    /**
     * Main sidebar component.
     *
     * Reads patterns injected via wp_localize_script (gratisBuilderPatterns).
     * Renders a viewport-toggle row + one card per pattern with an Insert
     * button that parses the pattern's block markup and inserts it at the
     * current editor selection.
     */
    function GratisBuilderSidebar() {
        var patterns = window.gratisBuilderPatterns || [];
        var vpState  = useState( 'desktop' );
        var vp       = vpState[0];
        var setVp    = vpState[1];

        // Viewport labels (for the toggle buttons only — informational in
        // the sidebar context; the editor canvas controls its own width).
        var viewports = [ 'desktop', 'tablet', 'mobile' ];

        return el(
            wp.element.Fragment,
            null,

            /* ---- "More menu" item ---------------------------------------- */
            el(
                PluginSidebarMoreMenuItem,
                { target: 'gratis-builder-sidebar', icon: 'layout' },
                __( 'GRATIS Patterns', 'gratis-builder' )
            ),

            /* ---- Sidebar panel ------------------------------------------- */
            el(
                PluginSidebar,
                {
                    name  : 'gratis-builder-sidebar',
                    title : __( 'GRATIS Builder', 'gratis-builder' ),
                    icon  : 'layout',
                },

                el( 'div', { style: { padding: '12px' } },

                    /* Viewport toggle row */
                    el( 'div', {
                        style: {
                            display       : 'flex',
                            gap           : '6px',
                            marginBottom  : '12px',
                            flexWrap      : 'wrap',
                        },
                    },
                        viewports.map( function ( v ) {
                            return el( 'button', {
                                key       : v,
                                className : 'button' + ( vp === v ? ' button-primary' : '' ),
                                onClick   : function () { setVp( v ); },
                                style     : { fontSize: '11px', padding: '3px 8px' },
                            }, v.charAt( 0 ).toUpperCase() + v.slice( 1 ) );
                        } )
                    ),

                    /* Pattern cards */
                    patterns.length === 0
                        ? el( 'p', {
                            style: { color: '#888', fontSize: '13px' },
                        }, __( 'No GRATIS patterns registered.', 'gratis-builder' ) )
                        : patterns.map( function ( p ) {
                            return el( 'div', {
                                key  : p.slug,
                                style: {
                                    border        : '1px solid #ddd',
                                    borderRadius  : '6px',
                                    marginBottom  : '12px',
                                    overflow      : 'hidden',
                                },
                            },

                                /* Card header thumbnail */
                                el( 'div', {
                                    style: {
                                        background     : 'linear-gradient(135deg,#6366f1,#8b5cf6)',
                                        height         : '60px',
                                        display        : 'flex',
                                        alignItems     : 'center',
                                        justifyContent : 'center',
                                        color          : '#fff',
                                        fontWeight     : 700,
                                        fontSize       : '12px',
                                        padding        : '0 10px',
                                        textAlign      : 'center',
                                    },
                                }, p.title ),

                                /* Card footer: slug + insert button */
                                el( 'div', {
                                    style: {
                                        padding        : '8px 10px',
                                        display        : 'flex',
                                        justifyContent : 'space-between',
                                        alignItems     : 'center',
                                    },
                                },
                                    el( 'span', {
                                        style: { fontSize: '11px', color: '#888' },
                                    }, p.slug ),

                                    el( 'button', {
                                        className : 'button button-primary',
                                        style     : { fontSize: '11px', padding: '3px 10px' },
                                        onClick   : function () {
                                            var blocks = wp.blocks.parse( p.content );
                                            wp.data
                                                .dispatch( 'core/block-editor' )
                                                .insertBlocks( blocks );
                                        },
                                    }, __( 'Insert', 'gratis-builder' ) )
                                )
                            );
                        } )
                )
            )
        );
    }

    registerPlugin( 'gratis-builder', {
        render : GratisBuilderSidebar,
        icon   : 'layout',
    } );
}() );
